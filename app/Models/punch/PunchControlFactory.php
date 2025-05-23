<?php

namespace App\Models\Punch;

use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Users\UserListFactory;

class PunchControlFactory extends Factory {
	protected $table = 'punch_control';
	protected $pk_sequence_name = 'punch_control_id_seq'; //PK Sequence name

	protected $tmp_data = NULL;
	protected $old_user_date_ids = array();
	protected $shift_data = NULL;

	protected $user_date_obj = NULL;
	protected $pay_period_schedule_obj = NULL;
	protected $job_obj = NULL;
	protected $job_item_obj = NULL;
	protected $meal_policy_obj = NULL;
	protected $punch_obj = NULL;

	protected $plf = NULL;
	protected $is_total_time_calculated = FALSE;

	// added by desh(2024-04-30)------------------------
	protected $calc_system_total_time;
	protected $calc_weekly_system_total_time;
	protected $calc_exception;
	protected $premature_exception;
	protected $calc_user_date_total;
	protected $calc_user_date_id;
	protected $calc_total_time;
	protected $strict_job_validiation;
	//--------------------------------------------------
	
	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'user_date_id' => 'UserDateID',
											'branch_id' => 'Branch',
											'department_id' => 'Department',
											'job_id' => 'Job',
											'job_item_id' => 'JobItem',
											'quantity' => 'Quantity',
											'bad_quantity' => 'BadQuantity',
											'total_time' => 'TotalTime',
											'actual_total_time' => 'ActualTotalTime',
											'meal_policy_id' => 'MealPolicyID',
											'note' => 'Note',
											'other_id1' => 'OtherID1',
											'other_id2' => 'OtherID2',
											'other_id3' => 'OtherID3',
											'other_id4' => 'OtherID4',
											'other_id5' => 'OtherID5',
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getUserDateObject( $id = NULL ) {
		if ( $id == '' AND is_object( $this->user_date_obj ) ) {
			return $this->user_date_obj;
		} else {
			if ( $id == '' ) {
				$id = $this->getUserDateID();
			}

			$udlf = new UserDateListFactory();
			$udlf->getById( $id );
			if ( $udlf->getRecordCount() > 0 ) {
				$this->user_date_obj = $udlf->getCurrent();
				return $this->user_date_obj;
			}

			return FALSE;
		}
	}

	function getPLFByPunchControlID() {
		if ( $this->plf == NULL AND $this->getID() != FALSE ) {
			$this->plf = new PunchListFactory();
			$this->plf->getByPunchControlID( $this->getID() );
		}

		return $this->plf;
	}

	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if ( $this->getUser() > 0 ) {
				$ppslf = new PayPeriodScheduleListFactory();
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			}

			return FALSE;
		}
	}

	function getShiftData() {
		if ( $this->shift_data == NULL AND is_object( $this->getPunchObject() ) AND $this->getUser() > 0 ) {
			if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
				$this->shift_data = $this->getPayPeriodScheduleObject()->getShiftData( NULL, $this->getUser(), $this->getPunchObject()->getTimeStamp(), 'nearest_shift', $this );
			} else {
				Debug::Text('No pay period schedule found for user ID: '. $this->getUser(), __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $this->shift_data;
	}

	function getJobObject() {
		if ( is_object($this->job_obj) ) {
			return $this->job_obj;
		} else {
			$jlf = new JobListFactory();
			$jlf->getById( $this->getJob() );
			if ( $jlf->getRecordCount() > 0 ) {
				$this->job_obj = $jlf->getCurrent();
				return $this->job_obj;
			}

			return FALSE;
		}
	}

	function getJobItemObject() {
		if ( is_object($this->job_item_obj) ) {
			return $this->job_item_obj;
		} else {
			$jilf = new JobItemListFactory();
			$jilf->getById( $this->getJobItem() );
			if ( $jilf->getRecordCount() > 0 ) {
				$this->job_item_obj = $jilf->getCurrent();
				return $this->job_item_obj;
			}

			return FALSE;
		}
	}

	function getPunchObject() {
		if ( is_object($this->punch_obj) ) {
			return $this->punch_obj;
		}

		return FALSE;
	}
	function setPunchObject($obj) {
		if ( is_object($obj) ) {
			$this->punch_obj = $obj;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		$user_id = FALSE;
		if ( is_object( $this->getPunchObject() ) AND $this->getPunchObject()->getUser() != FALSE ) {
			$user_id = $this->getPunchObject()->getUser();
		} elseif ( is_object( $this->getUserDateObject() ) ) {
			$user_id = $this->getUserDateObject()->getUser();
		}

		return $user_id;
	}

	//This must be called after PunchObject() has been set and before isValid() is called.
	function findUserDate() {
		/*
			Issues to consider:
				** Timezones, if one employee is in PST and the payroll administrator/pay period is in EST, if the employee
				** punches in at 11:00PM PST, its actually 2AM EST on the next day, so which day does the time get assigned to?
				** Use the employees preferred timezone to determine the proper date, otherwise if we use the PP schedule timezone it may
				** be a little confusing to employees because they may punch in on one day and have the time appears under different day.

				1. Employee punches out at 11:00PM, then is called in early at 4AM to start a new shift.
				Don't want to pair these punches.

				2. Employee starts 11:00PM shift late at 1:00AM the next day. Works until 7AM, then comes in again
				at 11:00PM the same day and works until 4AM, then 4:30AM to 7:00AM. The 4AM-7AM punches need to be paired on the same day.

				3. Ambulance EMT works 36hours straight in a single punch.

				*Perhaps we should handle lunch punches and normal punches differently? Lunch punches have
				a different "continuous time setting then normal punches.

				*Change daily continuous time to:
				* Group (Normal) Punches: X hours before midnight to punches X hours after midnight
				* Group (Lunch/Break) Punches: X hours before midnight to punches X hours after midnight
				*	Normal punches X hours after midnight group to punches X hours before midnight.
				*	Lunch/Break punches X hours after midnight group to punches X hours before midnight.

				OR, what if we change continuous time to be just the gap between punches that cause
					a new day to start? Combine this with daily cont. time so we know what the window
					is for punches to begin the gap search. Or we can always just search for a previous
					punch Xhrs before the current punch.
					- Do we look back to a In punch, or look back to an Out punch though? I think an Out Punch.
						What happens if they forgot to punch out though?
					Logic:
						If this is an Out punch:
							Find previous punch back to maximum shift time to find an In punch to pair it with.
						Else, if this is an In punch:
							Find previous punch back to maximum shift time to find an Out punch to combine it with.
							If out punch is found inside of new_shift trigger time, we place this punch on the previous day.
							Else: we place this punch on todays date.


				* Minimum time between punches to cause a new shift to start: Xhrs (default: 4hrs)
					new_day_trigger_time
					Call it: Minimum time-off that triggers new shift:
						Minimum Time-Off Between Shifts:
				* Maximum shift time: Xhrs (for ambulance service) default to 16 or 24hrs?
					This is essentially how far back we look for In punch to pair out punches with.
					maximum_shift_length
					- Add checks to ensure that no punch pair exceeds the maximum_shift_length
		*/

		/*
		 This needs to be able to run before Validate is called, so we can validate the pay period schedule.
		*/
		if ( $this->getUserDateID() == FALSE ) {
			$this->setUserDate( $this->getUser(), $this->getPunchObject()->getTimeStamp() );
		}

		Debug::Text(' Finding User Date ID: '. TTDate::getDate('DATE+TIME', $this->getPunchObject()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__,10);
		$shift_data = $this->getShiftData();

		/*echo '<pre>'; print_r($shift_data);

		foreach ($shift_data['punches'] as $key => $value) {
			echo '<br>'.date('Y-m-d H:i:s', $value['time_stamp']);
		}

		echo '<br>first_in'.date('Y-m-d H:i', $shift_data['first_in']['time_stamp']); 
		echo '<br>last_out'.date('Y-m-d H:i', $shift_data['last_out']['time_stamp']);
		echo '<br>';*/


		if ( is_array($shift_data) ) { 

			//echo 'getShiftAssignedDay...'.$this->getPayPeriodScheduleObject()->getShiftAssignedDay();
			
			switch ( $this->getPayPeriodScheduleObject()->getShiftAssignedDay() ) {
				default:
				case 10: //Day they start on
				case 40: //Split at midnight
					if ( !isset($shift_data['first_in']['time_stamp']) ) {
						$shift_data['first_in']['time_stamp'] = $shift_data['last_out']['time_stamp'];
					}

					//echo '<br>first_in...'.$shift_data['first_in']['time_stamp'];
					//echo '<br>first_in date...'.date('Y-m-d H:i', $shift_data['first_in']['time_stamp']).'<br>';

					//Can't use the First In user_date_id because it may need to be changed when editing a punch.
					Debug::Text('Assign Shifts to the day they START on... Date: '. TTDate::getDate('DATE', $shift_data['first_in']['time_stamp']) , __FILE__, __LINE__, __METHOD__,10);
					$user_date_id = UserDateFactory::findOrInsertUserDate( $this->getUser(), $shift_data['first_in']['time_stamp'] );
					break;
				case 20: //Day they end on
					if ( !isset($shift_data['last_out']['time_stamp']) ) {
						$shift_data['last_out']['time_stamp'] = $shift_data['first_in']['time_stamp'];
					}
					Debug::Text('Assign Shifts to the day they END on... Date: '. TTDate::getDate('DATE', $shift_data['last_out']['time_stamp']) , __FILE__, __LINE__, __METHOD__,10);
					$user_date_id = UserDateFactory::findOrInsertUserDate( $this->getUser(), $shift_data['last_out']['time_stamp'] );
					break;
				case 30: //Day with most time worked
					Debug::Text('Assign Shifts to the day they WORK MOST on... Date: '. TTDate::getDate('DATE', $shift_data['day_with_most_time']) , __FILE__, __LINE__, __METHOD__,10);
					$user_date_id = UserDateFactory::findOrInsertUserDate( $this->getUser(), $shift_data['day_with_most_time'] );
					break;
			}

			if ( isset($user_date_id) AND $user_date_id > 0 ) {
				Debug::Text('Found UserDateID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
				return $this->setUserDateID( $user_date_id );
			}
		}

		Debug::Text('No shift data to use to find UserDateID, using timestamp only: '. TTDate::getDate('DATE+TIME', $this->getPunchObject()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	function setUserDate($user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date );
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_date_id != '' ) {
			$this->setUserDateID( $user_date_id );
			return TRUE;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getOldUserDateID() {
		if ( isset($this->tmp_data['old_user_date_id']) ) {
			return $this->tmp_data['old_user_date_id'];
		}

		return FALSE;
	}
	function setOldUserDateID($id) {
		Debug::Text(' Setting Old User Date ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$this->tmp_data['old_user_date_id'] = $id;

		return TRUE;
	}

	function getUserDateID() {
		if ( isset($this->data['user_date_id']) ) {
			return $this->data['user_date_id'];
		}

		return FALSE;
	}

	function setUserDateID( $id ) {
		$id = trim($id);

		$udlf = new UserDateListFactory();
		if (  $this->Validator->isResultSetWithRows(	'user_date',
														$udlf->getByID($id),
														('Invalid User Date ID')
														) ) {

			if ( $this->getUserDateID() !== $id AND $this->getOldUserDateID() != $this->getUserDateID() ) {
				Debug::Text(' Setting Old User Date ID... Current Old ID: '. (int)$this->getOldUserDateID() .' Current ID: '. (int)$this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
				$this->setOldUserDateID( $this->getUserDateID() );
			}

			$this->data['user_date_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return $this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$blf = new BranchListFactory();

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'branch',
														$blf->getByID($id),
														('Branch does not exist')
														) ) {
			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return $this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$dlf = new DepartmentListFactory(); 

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'department',
														$dlf->getByID($id),
														('Department does not exist')
														) ) {
			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		if ( isset($this->data['job_id']) ) {
			return $this->data['job_id'];
		}

		return FALSE;
	}
	function setJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jlf = new JobListFactory();
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job',
														$jlf->getByID($id),
														('Job does not exist')
														) ) {
			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return $this->data['job_item_id'];
		}

		return FALSE;
	}
	function setJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jilf = new JobItemListFactory();
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item',
														$jilf->getByID($id),
														('Job Item does not exist')
														) ) {
			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getQuantity() {
		if ( isset($this->data['quantity']) ) {
			return (float)$this->data['quantity'];
		}

		return FALSE;
	}
	function setQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}

		if 	(	$val == 0
				OR
				$this->Validator->isFloat(			'quantity',
													$val,
													('Incorrect quantity')) ) {
			$this->data['quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getBadQuantity() {
		if ( isset($this->data['bad_quantity']) ) {
			return (float)$this->data['bad_quantity'];
		}

		return FALSE;
	}
	function setBadQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}

		if 	(	$val == 0
				OR
				$this->Validator->isFloat(			'bad_quantity',
													$val,
													('Incorrect bad quantity')) ) {
			$this->data['bad_quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTime() {
		if ( isset($this->data['total_time']) ) {
			return (int)$this->data['total_time'];
		}
		return FALSE;
	}
	function setTotalTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'total_time',
													$int,
													('Incorrect total time')) ) {
			$this->data['total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getActualTotalTime() {
		if ( isset($this->data['actual_total_time']) ) {
			return (int)$this->data['actual_total_time'];
		}
		return FALSE;
	}
	function setActualTotalTime($int) {
		$int = (int)$int;

		if ( $int < 0 ) {
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'actual_total_time',
													$int,
													('Incorrect actual total time')) ) {
			$this->data['actual_total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMealPolicyID() {
		if ( isset($this->data['meal_policy_id']) ) {
			return $this->data['meal_policy_id'];
		}

		return FALSE;
	}
	function setMealPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$mplf = new MealPolicyListFactory(); 

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'meal_policy',
														$mplf->getByID($id),
														('Meal Policy is invalid')
													) ) {

			$this->data['meal_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}
	}
	function setNote($val) {
		$val = trim($val);

		if 	(	$val == ''
				OR
				$this->Validator->isLength(		'note',
												$val,
												('Note is too short or too long'),
												0,
												1024) ) {

			$this->data['note'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		if ( isset($this->data['other_id1']) ) {
			return $this->data['other_id1'];
		}

		return FALSE;
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											('Other ID 1 is invalid'),
											1,255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		if ( isset($this->data['other_id2']) ) {
			return $this->data['other_id2'];
		}

		return FALSE;
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											('Other ID 2 is invalid'),
											1,255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		if ( isset($this->data['other_id3']) ) {
			return $this->data['other_id3'];
		}

		return FALSE;
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											('Other ID 3 is invalid'),
											1,255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		if ( isset($this->data['other_id4']) ) {
			return $this->data['other_id4'];
		}

		return FALSE;
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											('Other ID 4 is invalid'),
											1,255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		if ( isset($this->data['other_id5']) ) {
			return $this->data['other_id5'];
		}

		return FALSE;
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											('Other ID 5 is invalid'),
											1,255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function calcTotalTime( $force = TRUE ) {
		if ( $force == TRUE OR $this->is_total_time_calculated == FALSE ) {
			$this->is_total_time_calculated == TRUE;

			$plf = new PunchListFactory();
			$plf->getByPunchControlId( $this->getId() );
			//Make sure punches are in In/Out pairs before we bother calculating.
			if ( $plf->getRecordCount() > 0 AND ( $plf->getRecordCount() % 2 ) == 0 ) {
				Debug::text(' Found Punches to calculate.', __FILE__, __LINE__, __METHOD__,10);
				$in_pair = FALSE;
				$schedule_obj = NULL;
				foreach( $plf->rs as $punch_obj ) {
					$plf->data = (array)$punch_obj;
					$punch_obj = $plf;
					//Check for proper in/out pairs
					//First row should be an Out status (reverse ordering)
					Debug::text(' Punch: Status: '. $punch_obj->getStatus() .' TimeStamp: '. $punch_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
					if ( $punch_obj->getStatus() == 20 ) {
						Debug::text(' Found Out Status, starting pair: ', __FILE__, __LINE__, __METHOD__,10);
						$out_stamp = $punch_obj->getTimeStamp();
						$out_actual_stamp = $punch_obj->getActualTimeStamp();
						$in_pair = TRUE;
					} elseif ( $in_pair == TRUE ) {
						$punch_obj->setScheduleID( $punch_obj->findScheduleID( NULL, $this->getUser() ) ); //Find Schedule Object for this Punch
						$schedule_obj = $punch_obj->getScheduleObject();
                                                
                                                $punch_time =$punch_obj->getTimeStamp();
                                                
                                                /*
                                                $date_time_obj = new DateTime();
                                                $date_time_obj->setTimestamp($punch_time);
                                                
                                                
                                                $oficial_in_time = '8:00';
                                                
                                                 
                                                
                                                $punch_full_time_stamp = TTDate::parseDateTime($date_time_obj->format('d/m/Y').' '.$oficial_in_time);
                                                
                                                //echo $punch_full_time_stamp;
                                                //echo ' '.$punch_obj->getTimeStamp();
                                                // exit();
                                                
                                               // $check_time_stamp = $date_time_obj->format('Y-m-d')
                                                 $in_stamp =0;
                                                 
                                                 if($punch_full_time_stamp > $punch_obj->getTimeStamp())
                                                 {
                                                        $in_stamp = $punch_full_time_stamp;
                                                        $in_actual_stamp = $punch_full_time_stamp;
                                                 }
                                                 else{
                                                     $in_stamp = $punch_obj->getTimeStamp();
                                                     $in_actual_stamp = $punch_obj->getActualTimeStamp();
                                                 }
                                                */
						     $in_stamp = $punch_obj->getTimeStamp();
                                                     $in_actual_stamp = $punch_obj->getActualTimeStamp();
                                                
						$in_actual_stamp = $punch_obj->getActualTimeStamp();
						//Got a pair... Totaling.
						Debug::text(' Found a pair... Totaling: ', __FILE__, __LINE__, __METHOD__,10);
						if ( $out_stamp != '' AND $in_stamp != '' ) {
							$total_time = $out_stamp - $in_stamp;
                                                        
                                               
						}
						if ( $out_actual_stamp != '' AND $in_actual_stamp != '' ) {
							$actual_total_time = $out_actual_stamp - $in_actual_stamp;
						}
					}
				}
                                
                                
                                

				if ( isset($total_time) ) {
					Debug::text(' Setting TotalTime...', __FILE__, __LINE__, __METHOD__,10);

					$this->setTotalTime( $total_time );
					$this->setActualTotalTime( $actual_total_time );

					return TRUE;
				}
			} else {
				Debug::text(' No Punches to calculate, or punches arent in pairs. Set total to 0', __FILE__, __LINE__, __METHOD__,10);
				$this->setTotalTime( 0 );
				$this->setActualTotalTime( 0 );

				return TRUE;
			}
		}

		return FALSE;
	}

	function changePreviousPunchType() {
		Debug::text(' Previous Punch to Lunch/Break...', __FILE__, __LINE__, __METHOD__,10);

		if ( is_object( $this->getPunchObject() ) ) {
			if ( $this->getPunchObject()->getType() == 20 AND $this->getPunchObject()->getStatus() == 10 ) {
				Debug::text(' bbPrevious Punch to Lunch...', __FILE__, __LINE__, __METHOD__,10);

				$shift_data = $this->getShiftData();

				if ( isset($shift_data['previous_punch_key'])
						AND isset($shift_data['punches'][$shift_data['previous_punch_key']])
						AND $shift_data['punches'][$shift_data['previous_punch_key']]['type_id'] != 20 ) {
					$previous_punch_arr = $shift_data['punches'][$shift_data['previous_punch_key']];

					Debug::text(' Previous Punch ID: '. $previous_punch_arr['id'], __FILE__, __LINE__, __METHOD__,10);

					if ( $this->getPunchObject()->inMealPolicyWindow( $this->getPunchObject()->getTimeStamp(), $previous_punch_arr['time_stamp'] ) == TRUE ) {
						Debug::text(' Previous Punch needs to change to Lunch...', __FILE__, __LINE__, __METHOD__,10);

						$plf = new PunchListFactory();
						$plf->getById( $previous_punch_arr['id'] );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text(' Modifying previous punch...', __FILE__, __LINE__, __METHOD__,10);

							$pf = $plf->getCurrent();
							$pf->setType( 20 ); //Lunch
							//If we start re-rounding this punch we have to recalculate the total for the previous punch_control too.
							//$p_obj->setTimeStamp( $p_obj->getTimeStamp() ); //Re-round timestamp now that its a lunch punch.
							if ( $pf->Save( FALSE ) == TRUE ) {
								Debug::text(' Returning TRUE!', __FILE__, __LINE__, __METHOD__,10);

								return TRUE;
							}

						}

					}

				}
			} elseif ( $this->getPunchObject()->getType() == 30 AND $this->getPunchObject()->getStatus() == 10 ) {
				Debug::text(' bbPrevious Punch to Break...', __FILE__, __LINE__, __METHOD__,10);

				$shift_data = $this->getShiftData();

				if ( isset($shift_data['previous_punch_key'])
						AND isset($shift_data['punches'][$shift_data['previous_punch_key']])
						AND $shift_data['punches'][$shift_data['previous_punch_key']]['type_id'] != 30 ) {
					$previous_punch_arr = $shift_data['punches'][$shift_data['previous_punch_key']];

					Debug::text(' Previous Punch ID: '. $previous_punch_arr['id'], __FILE__, __LINE__, __METHOD__,10);

					if ( $this->getPunchObject()->inBreakPolicyWindow( $this->getPunchObject()->getTimeStamp(), $previous_punch_arr['time_stamp'] ) == TRUE ) {
						Debug::text(' Previous Punch needs to change to Break...', __FILE__, __LINE__, __METHOD__,10);

						$plf = new PunchListFactory();
						$plf->getById( $previous_punch_arr['id'] );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text(' Modifying previous punch...', __FILE__, __LINE__, __METHOD__,10);

							$pf = $plf->getCurrent();
							$pf->setType( 30 ); //Break
							//If we start re-rounding this punch we have to recalculate the total for the previous punch_control too.
							//$p_obj->setTimeStamp( $p_obj->getTimeStamp() ); //Re-round timestamp now that its a lunch punch.
							if ( $pf->Save( FALSE ) == TRUE ) {
								Debug::text(' Returning TRUE!', __FILE__, __LINE__, __METHOD__,10);

								return TRUE;
							}

						}

					}

				}
			}

		}

		Debug::text(' Returning false!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getEnableCalcSystemTotalTime() {
		if ( isset($this->calc_system_total_time) ) {
			return $this->calc_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcSystemTotalTime($bool) {
		$this->calc_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcWeeklySystemTotalTime() {
		if ( isset($this->calc_weekly_system_total_time) ) {
			return $this->calc_weekly_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcWeeklySystemTotalTime($bool) {
		$this->calc_weekly_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcException() {
		if ( isset($this->calc_exception) ) {
			return $this->calc_exception;
		}

		return FALSE;
	}
	function setEnableCalcException($bool) {
		$this->calc_exception = $bool;

		return TRUE;
	}

	function getEnablePreMatureException() {
		if ( isset($this->premature_exception) ) {
			return $this->premature_exception;
		}

		return FALSE;
	}
	function setEnablePreMatureException($bool) {
		$this->premature_exception = $bool;

		return TRUE;
	}

	function getEnableCalcUserDateTotal() {
		if ( isset($this->calc_user_date_total) ) {
			return $this->calc_user_date_total;
		}

		return FALSE;
	}
	function setEnableCalcUserDateTotal($bool) {
		$this->calc_user_date_total = $bool;

		return TRUE;
	}
	function getEnableCalcUserDateID() {
		if ( isset($this->calc_user_date_id) ) {
			return $this->calc_user_date_id;
		}

		return FALSE;
	}
	function setEnableCalcUserDateID($bool) {
		$this->calc_user_date_id = $bool;

		return TRUE;
	}

	function getEnableCalcTotalTime() {
		if ( isset($this->calc_total_time) ) {
			return $this->calc_total_time;
		}

		return FALSE;
	}
	function setEnableCalcTotalTime($bool) {
		$this->calc_total_time = $bool;

		return TRUE;
	}

	function getEnableStrictJobValidation() {
		if ( isset($this->strict_job_validiation) ) {
			return $this->strict_job_validiation;
		}

		return FALSE;
	}
	function setEnableStrictJobValidation($bool) {
		$this->strict_job_validiation = $bool;

		return TRUE;
	}

	function Validate() {
		Debug::text('Validating...', __FILE__, __LINE__, __METHOD__,10);

		//Call this here so getShiftData can get the correct total time, before we call findUserDate.
		if ( $this->getEnableCalcTotalTime() == TRUE ) {
			$this->calcTotalTime();
		}
		
		if ( is_object( $this->getPunchObject() ) ) {
			$this->findUserDate();
		}
		Debug::text('User Date Id: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getUserDateObject() == FALSE OR $this->getUserDateObject()->getPayPeriodObject() == FALSE ) {
			$this->Validator->isTRUE(	'pay_period',
										FALSE,
										('Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule if you have not done so already') );
		} elseif ( $this->getUserDateObject() == FALSE OR $this->getUserDateObject()->getPayPeriodObject()->getIsLocked() == TRUE ) {
			$this->Validator->isTRUE(	'pay_period',
										FALSE,
										('Pay Period is Currently Locked') );
		}

		//Skip these checks if they are deleting a punch.
		if ( is_object( $this->getPunchObject() ) AND $this->getPunchObject()->getDeleted() == FALSE ) {
			$plf = $this->getPLFByPunchControlID();
			if ( $plf !== NULL AND ( ( $this->isNew() AND $plf->getRecordCount() == 2 ) OR $plf->getRecordCount() > 2 ) ) {
				//('Punch Control can not have more than two punches. Please use the Add Punch button instead')
				//They might be trying to insert a punch inbetween two others?
				$this->Validator->isTRUE(	'punch_control',
											FALSE,
											('Time conflicts with another punch on this day (c)'));
			}

			$shift_data = $this->getShiftData();
			if ( is_array($shift_data) ) {
				foreach ( $shift_data['punches'] as $punch_data ) {
					//Make sure there aren't two In punches, or two Out punches in the same pair.
					//This fixes the bug where if you have an In punch, then click the blank cell below it
					//to add a new punch, but change the status from Out to In instead.
					if ( isset($punches[$punch_data['punch_control_id']][$punch_data['status_id']]) ) {
						if ( $punch_data['status_id'] == 10 ) {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('In punches cannot occur twice in the same punch pair, you may want to make this an out punch instead'));
						} else {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('Out punches cannot occur twice in the same punch pair, you may want to make this an in punch instead'));
						}
					}


					//Debug::text(' Current Punch Object: ID: '. $this->getPunchObject()->getId() .' TimeStamp: '. $this->getPunchObject()->getTimeStamp() .' Status: '. $this->getPunchObject()->getStatus(), __FILE__, __LINE__, __METHOD__,10);
					//Debug::text(' Looping Punch Object: ID: '. $punch_data['id'] .' TimeStamp: '. $punch_data['time_stamp'] .' Status: '.$punch_data['status_id'], __FILE__, __LINE__, __METHOD__,10);

					//Check for another punch that matches the timestamp and status.
					if ( $this->getPunchObject()->getID() != $punch_data['id'] ) {
						if ( $this->getPunchObject()->getTimeStamp() == $punch_data['time_stamp'] AND $this->getPunchObject()->getStatus() == $punch_data['status_id'] ) {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('Time and status match that of another punch, this could be due to rounding (a)') );
						}
					}

					//Check for another punch that matches the timestamp and NOT status in the SAME punch pair.
					if ( $this->getPunchObject()->getID() != $punch_data['id'] AND $this->getID() == $punch_data['punch_control_id'] ) {
						if ( $this->getPunchObject()->getTimeStamp() == $punch_data['time_stamp'] AND $this->getPunchObject()->getStatus() != $punch_data['status_id'] ) {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('Time matches another punch in the same punch pair, this could be due to rounding (b)') );
						}
					}

					$punches[$punch_data['punch_control_id']][$punch_data['status_id']] = $punch_data;
				}
				unset($punch_data);

				if ( isset($punches[$this->getID()]) ) {
					Debug::text('Current Punch ID: '. $this->getPunchObject()->getId() .' Punch Control ID: '. $this->getID() .' Status: '. $this->getPunchObject()->getStatus(), __FILE__, __LINE__, __METHOD__,10);
					//Debug::Arr($punches, 'Punches Arr: ', __FILE__, __LINE__, __METHOD__,10);
					
					if ( $this->getPunchObject()->getStatus() == 10 AND isset($punches[$this->getID()][20]) AND $this->getPunchObject()->getTimeStamp() > $punches[$this->getID()][20]['time_stamp'] ) {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('In punches cannot occur after an out punch, in the same punch pair'));
					} elseif ( $this->getPunchObject()->getStatus() == 20 AND isset($punches[$this->getID()][10]) AND $this->getPunchObject()->getTimeStamp() < $punches[$this->getID()][10]['time_stamp'] ) {
							$this->Validator->isTRUE(	'time_stamp',
														FALSE,
														('Out punches cannot occur before an in punch, in the same punch pair'));
					} else {
						Debug::text('bPunch does not match any other punch pair.', __FILE__, __LINE__, __METHOD__,10);

						$punch_neighbors = Misc::getArrayNeighbors( $punches, $this->getID(), 'both');
						//Debug::Arr($punch_neighbors, ' Punch Neighbors: ', __FILE__, __LINE__, __METHOD__,10);

						if ( isset($punch_neighbors['next']) AND isset($punches[$punch_neighbors['next']]) ) {
							Debug::text('Found Next Punch...', __FILE__, __LINE__, __METHOD__,10);
							if ( ( isset($punches[$punch_neighbors['next']][10]) AND $this->getPunchObject()->getTimeStamp() > $punches[$punch_neighbors['next']][10]['time_stamp'] )
										OR ( isset($punches[$punch_neighbors['next']][20]) AND $this->getPunchObject()->getTimeStamp() > $punches[$punch_neighbors['next']][20]['time_stamp'] ) ) {
								$this->Validator->isTRUE(	'time_stamp',
															FALSE,
															('Time conflicts with another punch on this day').' (a)');
							}
						}

						if ( isset($punch_neighbors['prev']) AND isset($punches[$punch_neighbors['prev']]) ) {
							Debug::text('Found prev Punch...', __FILE__, __LINE__, __METHOD__,10);
							if (
								( isset($punches[$punch_neighbors['prev']][10]) AND $this->getPunchObject()->getTimeStamp() < $punches[$punch_neighbors['prev']][10]['time_stamp'] ) OR 
								( isset($punches[$punch_neighbors['prev']][20]) AND $this->getPunchObject()->getTimeStamp() < $punches[$punch_neighbors['prev']][20]['time_stamp'] )
							) {
								$this->Validator->isTRUE( 'time_stamp', FALSE, ('Time conflicts with another punch on this day').' (b)');
							}
						}
					}

					//Check to make sure punches don't exceed maximum shift time.
					$maximum_shift_time = $plf->getPayPeriodMaximumShiftTime( $this->getPunchObject()->getUser() );
					Debug::text('Maximum shift time: '. $maximum_shift_time, __FILE__, __LINE__, __METHOD__,10);
					if ( $shift_data['total_time'] > $maximum_shift_time ) {
						$this->Validator->isTRUE(	'time_stamp',
													FALSE,
													('Punch exceeds maximum shift time of') .' '. TTDate::getTimeUnit( $maximum_shift_time )  .' '. ('hrs set for this pay period schedule') );
					}
				}
				unset($punches);
			}
		}

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL AND $this->getEnableStrictJobValidation() == TRUE ) {
			if ( $this->getJob() > 0 ) {
				$jlf = new JobListFactory();
				$jlf->getById( $this->getJob() );
				if ( $jlf->getRecordCount() > 0 ) {
					$j_obj = $jlf->getCurrent();

					if ( is_object( $this->getUserDateObject() ) AND $j_obj->isAllowedUser( $this->getUserDateObject()->getUser() ) == FALSE ) {
						$this->Validator->isTRUE(	'job',
													FALSE,
													('Employee is not assigned to this job') );
					}

					if ( $j_obj->isAllowedItem( $this->getJobItem() ) == FALSE ) {
						$this->Validator->isTRUE(	'job_item',
													FALSE,
													('Task is not assigned to this job') );
					}
				}
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getBranch() === FALSE ) {
			$this->setBranch(0);
		}

		if ( $this->getDepartment() === FALSE ) {
			$this->setDepartment(0);
		}

		if ( $this->getJob() === FALSE ) {
			$this->setJob(0);
		}

		if ( $this->getJobItem() === FALSE ) {
			$this->setJobItem(0);
		}

		if ( $this->getQuantity() === FALSE ) {
			$this->setQuantity(0);
		}

		if ( $this->getBadQuantity() === FALSE ) {
			$this->setBadQuantity(0);
		}
/*
		if ( $this->getUserDateID() == FALSE AND is_object( $this->getPunchObject() ) ) {
			$this->setUserDate( $this->getUser(), $this->getPunchObject()->getTimeStamp() );
		}
*/
		//Set Job default Job Item if required.
		if ( $this->getJob() != FALSE AND $this->getJobItem() == '' ) {
			Debug::text(' Job is set ('.$this->getJob().'), but no task is... Using default job item...', __FILE__, __LINE__, __METHOD__,10);

			if ( is_object( $this->getJobObject() ) ){
				Debug::text(' Default Job Item: '. $this->getJobObject()->getDefaultItem(), __FILE__, __LINE__, __METHOD__,10);
				$this->setJobItem( $this->getJobObject()->getDefaultItem() );
			}
		}

		if ( $this->getEnableCalcTotalTime() == TRUE ) {
			$this->calcTotalTime();
		}

		if ( is_object( $this->getPunchObject() ) ) {
			$this->findUserDate();
		}
		
		//Check to see if timesheet is verified, if so unverify it on modified punch.
		//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
		if ( is_object( $this->getPayPeriodScheduleObject() )
				AND $this->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
			//Find out if timesheet is verified or not.
			$pptsvlf = new PayPeriodTimeSheetVerifyListFactory(); 
			$pptsvlf->getByPayPeriodIdAndUserId( $this->getUserDateObject()->getPayPeriod(), $this->getUser() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				//Pay period is veriferied, delete all records and make log entry.
				Debug::text('Pay Period is verified, deleting verification records: '. $pptsvlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
				foreach( $pptsvlf->rs as $pptsv_obj ) {
					$pptsvlf->data = (array)$pptsv_obj;
					$pptsv_obj = $pptsvlf;
					if ( is_object( $this->getPunchObject() ) ) {
						TTLog::addEntry( $pptsv_obj->getId(), 500,  ('TimeSheet Modified After Verification').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. ('Punch').': '. TTDate::getDate('DATE+TIME', $this->getPunchObject()->getTimeStamp() ) , NULL, $pptsvlf->getTable() );
					}
					$pptsv_obj->setDeleted( TRUE );
					if ( $pptsv_obj->isValid() ) {
						$pptsv_obj->Save();
					}
				}
			}
		}

		$this->changePreviousPunchType();

		return TRUE;
	}

	function calcUserDate() {
		if ( $this->getEnableCalcUserDateID() == TRUE ) {
			Debug::Text(' Calculating User Date ID...', __FILE__, __LINE__, __METHOD__,10);

			$shift_data = $this->getShiftData();
			if ( is_array($shift_data) ) {
				$user_date_id = $this->getUserDateID(); //preSave should already be called before running this function.

				if ( isset($user_date_id) AND $user_date_id > 0 AND isset($shift_data['punch_control_ids']) AND is_array($shift_data['punch_control_ids']) ) {
					Debug::Text('Assigning all punch_control_ids to User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

					$this->old_user_date_ids[] = $user_date_id;
					$this->old_user_date_ids[] = $this->getOldUserDateID();

					foreach( $shift_data['punch_control_ids'] as $punch_control_id ) {
						$pclf = new PunchControlListFactory();
						$pclf->getById( $punch_control_id );
						if ( $pclf->getRecordCount() == 1 ) {
							$pc_obj = $pclf->getCurrent();
							if ( $pc_obj->getUserDateID() != $user_date_id ) {
								Debug::Text(' Saving Punch Control ID: '. $punch_control_id .' with new User Date Total ID: '. $user_date_id , __FILE__, __LINE__, __METHOD__,10);

								$this->old_user_date_ids[] = $pc_obj->getUserDateID();
								$pc_obj->setUserDateID( $user_date_id );
								$pc_obj->setEnableCalcUserDateTotal( TRUE );
								$pc_obj->Save();
							} else {
								Debug::Text(' NOT Saving Punch Control ID, as User Date ID didnt change: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);
							}
						}
					}
					//Debug::Arr($this->old_user_date_ids, 'aOld User Date IDs: ', __FILE__, __LINE__, __METHOD__,10);

					return TRUE;
				}
			}
		}

		return FALSE;
	}

	function calcUserDateTotal() {
		if ( $this->getEnableCalcUserDateTotal() == TRUE ) {
			Debug::Text(' Calculating User Date Total...', __FILE__, __LINE__, __METHOD__,10);

			//Add a row to the user date total table, as "worked" hours.
			//Edit if it already exists and is not set as override.
			$udtlf = new UserDateTotalListFactory(); 
			$udtlf->getByUserDateIdAndPunchControlId( $this->getUserDateID(), $this->getId() );
			Debug::text(' Checking for Conflicting User Date Total Records, count: '. $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text(' Found Conflicting User Date Total Records, removing them before re-calc', __FILE__, __LINE__, __METHOD__,10);
				foreach($udtlf->rs as $udt_obj) {
					$udtlf->data = (array)$udt_obj;
					$udt_obj = $udtlf;
					if ( $udt_obj->getOverride() == FALSE ) {
						Debug::text(' bFound Conflicting User Date Total Records, removing them before re-calc', __FILE__, __LINE__, __METHOD__,10);
						$udt_obj->Delete();
					}
				}
			}

			Debug::text(' cFound Conflicting User Date Total Records, removing them before re-calc: PreMature: '. (int)$this->getEnablePreMatureException(), __FILE__, __LINE__, __METHOD__,10);
			if ( $this->getDeleted() == FALSE ) {
				Debug::text(' Calculating Total Time for day. Punch Control ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
				$udtf = new UserDateTotalFactory();
				$udtf->setUserDateID( $this->getUserDateID() );
				$udtf->setPunchControlID( $this->getId() );
				$udtf->setStatus( 20 ); //Worked
				$udtf->setType( 10 ); //Total

				$udtf->setBranch( $this->getBranch() );
				$udtf->setDepartment( $this->getDepartment() );

				$udtf->setJob( $this->getJob() );
				$udtf->setJobItem( $this->getJobItem() );
				$udtf->setQuantity( $this->getQuantity() );
				$udtf->setBadQuantity( $this->getBadQuantity() );

				$udtf->setTotalTime( $this->getTotalTime() );
				$udtf->setActualTotalTime( $this->getActualTotalTime() );

				//Let smartReCalculate handle calculating totals/exceptions.
				if ( $udtf->isValid() ) {
					$udtf->Save();
				}
			}
		}

		return FALSE;
	}

	//This function handles when th UI wants to drag and drop punches around the time sheet.
	//$action = 0 (Copy), 1 (Move)
	//$position = -1 (Before), 0 (Overwrite), 1 (After)
	//$dst_status_id = 10 (In), 20 (Out), this is the status of the row the punch is being dragged too, or the resulting status_id in *most* (not all) cases.
	//					It is really only needed when using the overwrite position setting, and dragging a punch to a blank cell. Other than that it can be left NULL.
	static function dragNdropPunch( $company_id, $src_punch_id, $dst_punch_id, $dst_status_id = NULL, $position = 0, $action = 0, $dst_date = NULL ) {
		/*
			Operations to handle:
				- Moving punch from Out to In, or In to Out in same punch pair, this is ALWAYS a move, and not a copy.
				- Move punch from one pair to another in the same day, this can be a copy or move.
					- Check moving AND copying Out punch from one punch pair to In in another on the same day. ie: In 8:00AM, Out 1:00PM, Out 5:00PM. Move the 1PM punch to pair with 5PM.
				- Move punch from one day to another, inserting inbetween other punches if necessary.
				- Move punch from one day to another without any other punches.


				- Inserting BEFORE on a dst_punch_id that is an In punch doesn't do any splitting.
				- Inserting AFTER on a dst_punch_id that is on a Out punch doesn't do any splitting.
				- Overwriting should just take the punch time and overwrite the existing punch time.
				- The first thing this function does it check if there are two punches assigned to the punch control of the destination punch, if there is, it splits the punches
					across two punch_controls, it then attaches the src_punch_id to the same punch_control_id as the dst_punch_id.
				- If no dst_punch_id is specified, assume copying to a blank cell, just copy the punch to that date along with the punch_control?
		*/
		$dst_date = TTDate::getMiddleDayEpoch( $dst_date );
		Debug::text('Src Punch ID: '. $src_punch_id .' Dst Punch ID: '. $dst_punch_id .' Dst Status ID: '. $dst_status_id .' Position: '. $position .' Action: '. $action .' Dst Date: '. $dst_date, __FILE__, __LINE__, __METHOD__,10);

		$retval = FALSE;

		//Get source and destination punch objects.
		$plf = new PunchListFactory();
		$plf->StartTransaction();

		$plf->getByCompanyIDAndId( $company_id, $src_punch_id );
		if ( $plf->getRecordCount() == 1 ) {
			$src_punch_obj = $plf->getCurrent();
			$src_punch_date = TTDate::getMiddleDayEpoch( $src_punch_obj->getPunchControlObject()->getUserDateObject()->getDateStamp() );
			Debug::text('Found SRC punch ID: '. $src_punch_id .' Source Punch Date: '. $src_punch_date , __FILE__, __LINE__, __METHOD__,10);

			//If we are moving the punch, we need to delete the source punch first so it doesn't conflict with the new punch.
			//Especially if we are just moving a punch to fill a gap in the same day.
			//If the punch being moved is in the same day, or within the same punch pair, we don't want to delete the source punch, instead we just modify
			//the necessary bits later on. So we need to short circuit the move functionality when copying/moving punches within the same day.
			if (
					( $action == 1 AND $src_punch_id != $dst_punch_id AND $src_punch_date != $dst_date )
					OR
					( $action == 1 AND $src_punch_id != $dst_punch_id AND $src_punch_date == $dst_date )
					//OR
					//( $action == 0 AND $src_punch_id != $dst_punch_id AND $src_punch_date == $dst_date ) //Since we have dst_status_id, we don't need to force-move punches even though the user selected copy.
				) { //Move
				Debug::text('Deleting original punch...: '. $src_punch_id, __FILE__, __LINE__, __METHOD__,10);
				$src_punch_obj->setUser( $src_punch_obj->getPunchControlObject()->getUserDateObject()->getUser() );
				$src_punch_obj->setDeleted(TRUE);

				//These aren't doing anything because they aren't acting on the PunchControl object?
				$src_punch_obj->setEnableCalcTotalTime( TRUE );
				$src_punch_obj->setEnableCalcSystemTotalTime( TRUE );
				$src_punch_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
				$src_punch_obj->setEnableCalcUserDateTotal( TRUE );
				$src_punch_obj->setEnableCalcException( TRUE );
				$src_punch_obj->Save(FALSE); //Keep object around for later.
			} else {
				Debug::text('NOT Deleting original punch, either in copy mode or condition is not met...', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( $src_punch_id == $dst_punch_id OR $dst_punch_id == '' ) {
				//Assume we are just moving a punch within the same punch pair, unless a new date is specfied.
				//However if we're simply splitting an existing punch pair, like dragging the Out punch from an In/Out pair into its own separate pair.
				if ( $src_punch_date != $dst_date OR $src_punch_date == $dst_date AND $dst_punch_id == '' ) {
					Debug::text('aCopying punch to new day...', __FILE__, __LINE__, __METHOD__,10);
					//Moving punch to a new date.
					//Copy source punch to proper location by destination punch.
					$src_punch_control_obj = $src_punch_obj->getPunchControlObject();

					$src_punch_obj->setId( FALSE );
					$src_punch_obj->setPunchControlId( (int)$src_punch_control_obj->getNextInsertId() );
					$src_punch_obj->setDeleted(FALSE); //Just in case it was marked deleted by the MOVE action.

					$new_time_stamp = TTDate::getTimeLockedDate($src_punch_obj->getTimeStamp(), $dst_date );
					Debug::text('SRC TimeStamp: '. TTDate::getDate('DATE+TIME', $src_punch_obj->getTimeStamp() ).' DST TimeStamp: '. TTDate::getDate('DATE+TIME', $new_time_stamp ), __FILE__, __LINE__, __METHOD__,10);

					$src_punch_obj->setTimeStamp( $new_time_stamp, FALSE );
					$src_punch_obj->setActualTimeStamp( $new_time_stamp );
					$src_punch_obj->setOriginalTimeStamp( $new_time_stamp );
					if ( $dst_status_id != '' ) {
						$src_punch_obj->setStatus( $dst_status_id ); //Change the status to fit in the proper place.
					}
					if ( $src_punch_obj->isValid() == TRUE ) {
						$insert_id = $src_punch_obj->Save( FALSE );

						$src_punch_control_obj->shift_data = NULL; //Need to clear the shift data so its obtained from the DB again, otherwise shifts will appear on strange days.
						$src_punch_control_obj->user_date_obj = NULL; //Need to clear user_date_obj from cache so a new one is obtained.
						$src_punch_control_obj->setId( $src_punch_obj->getPunchControlID() );
						$src_punch_control_obj->setPunchObject( $src_punch_obj );

						if ( $src_punch_control_obj->isValid() == TRUE ) {
							Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

							//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
							//in the case of a drag & drop or something.
							$src_punch_control_obj->setEnableStrictJobValidation( TRUE );
							$src_punch_control_obj->setEnableCalcUserDateID( TRUE );
							$src_punch_control_obj->setEnableCalcTotalTime( TRUE );
							$src_punch_control_obj->setEnableCalcSystemTotalTime( TRUE );
							$src_punch_control_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
							$src_punch_control_obj->setEnableCalcUserDateTotal( TRUE );
							$src_punch_control_obj->setEnableCalcException( TRUE );
							if ( $src_punch_control_obj->isValid() == TRUE ) {
								if ( $src_punch_control_obj->Save( TRUE, TRUE ) == TRUE ) {
									//Return newly inserted punch_id, so Flex can base other actions on it.
									//$retval = TRUE;
									$retval = $insert_id;
								}
							}
						}
					}
				} else {
					Debug::text('Copying punch within same pair/day...', __FILE__, __LINE__, __METHOD__,10);
					//Moving punch within the same punch pair.
					$src_punch_obj->setStatus( $src_punch_obj->getNextStatus() ); //Change just the punch status.
					//$src_punch_obj->setDeleted(FALSE); //Just in case it was marked deleted by the MOVE action.
					if ( $src_punch_obj->isValid() == TRUE ) {
						//Return punch_id, so Flex can base other actions on it.
						$retval = $src_punch_obj->Save( FALSE );
						//$retval = TRUE;
					}
				}
			} else {
				Debug::text('bCopying punch to new day...', __FILE__, __LINE__, __METHOD__,10);
				$plf->getByCompanyIDAndId($company_id, $dst_punch_id );
				if ( $plf->getRecordCount() == 1 ) {
					Debug::text('Found DST punch ID: '. $dst_punch_id, __FILE__, __LINE__, __METHOD__,10);
					$dst_punch_obj = $plf->getCurrent();
					$dst_punch_control_obj = $dst_punch_obj->getPunchControlObject();

					$is_punch_control_split = FALSE;
					if ( $position == 0 ) { //Overwrite
						//All we need to do is update the time of the destination punch.
						$punch_obj = $dst_punch_obj;
					} else { //Before or After
						//Determine if the destination punch needs to split from another punch
						if ( ( $position == -1 AND $dst_punch_obj->getStatus() == 20 )
								OR ( $position == 1 AND $dst_punch_obj->getStatus() == 10 ) ) { //Before on Out punch, After on In Punch,
							Debug::text('Need to split destination punch out to its own Punch Control row...', __FILE__, __LINE__, __METHOD__,10);
							$is_punch_control_split = PunchControlFactory::splitPunchControl( $dst_punch_obj->getPunchControlID() );

							//Once a split occurs, we need to re-get the destination punch as the punch_control_id may have changed.
							//We could probably optimize this to only occur when the destination punch is an In punch, as the
							//Out punch is always the one to be moved to a new punch_control_id
							if ( $src_punch_obj->getStatus() != $dst_punch_obj->getStatus() ) {
								$plf->getByCompanyIDAndId($company_id, $dst_punch_id );
								if ( $plf->getRecordCount() == 1 ) {
									$dst_punch_obj = $plf->getCurrent();
									Debug::text('Found DST punch ID: '. $dst_punch_id .' Punch Control ID: '. $dst_punch_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__,10);
								}
							}

							$punch_control_id = $dst_punch_obj->getPunchControlID();
						} else {
							Debug::text('No Need to split destination punch, simply add a new punch/punch_control all on its own.', __FILE__, __LINE__, __METHOD__,10);
							//Check to see if the src and dst punches are the same status though.
							$punch_control_id = (int)$dst_punch_control_obj->getNextInsertId();
						}

						//Take the source punch and base our new punch on that.
						$punch_obj = $src_punch_obj;

						//Copy source punch to proper location by destination punch.
						$punch_obj->setId( FALSE );
						$punch_obj->setDeleted(FALSE); //Just in case it was marked deleted by the MOVE action.
						$punch_obj->setPunchControlId( $punch_control_id );
					}

					$new_time_stamp = TTDate::getTimeLockedDate($src_punch_obj->getTimeStamp(), $dst_punch_obj->getTimeStamp() );
					Debug::text('SRC TimeStamp: '. TTDate::getDate('DATE+TIME', $src_punch_obj->getTimeStamp() ).' DST TimeStamp: '. TTDate::getDate('DATE+TIME', $new_time_stamp ), __FILE__, __LINE__, __METHOD__,10);

					$punch_obj->setTimeStamp( $new_time_stamp, FALSE );
					$punch_obj->setActualTimeStamp( $new_time_stamp );
					$punch_obj->setOriginalTimeStamp( $new_time_stamp );

					//Need to take into account copying a Out punch and inserting it BEFORE another Out punch in a punch pair.
					//In this case a split needs to occur, and the status needs to stay the same.
					//Status also needs to stay the same when overwriting an existing punch.
					Debug::text('Punch Status: '. $punch_obj->getStatus()  .' DST Punch Status: '. $dst_punch_obj->getStatus() .' Split Punch Control: '. (int)$is_punch_control_split , __FILE__, __LINE__, __METHOD__,10);
					if ( ( $position != 0 AND $is_punch_control_split == FALSE AND $punch_obj->getStatus() == $dst_punch_obj->getStatus() AND $punch_obj->getPunchControlID() == $dst_punch_obj->getPunchControlID() ) ) {
						Debug::text('Changing punch status to opposite: '. $dst_punch_obj->getNextStatus(), __FILE__, __LINE__, __METHOD__,10);
						$punch_obj->setStatus( $dst_punch_obj->getNextStatus() ); //Change the status to fit in the proper place.
					}
					if ( $punch_obj->isValid() == TRUE ) {
						$insert_id = $punch_obj->Save( FALSE );

						$dst_punch_control_obj->shift_data = NULL; //Need to clear the shift data so its obtained from the DB again, otherwise shifts will appear on strange days, or cause strange conflicts.
						$dst_punch_control_obj->setID( $punch_obj->getPunchControlID() );
						$dst_punch_control_obj->setPunchObject( $punch_obj );

						if ( $dst_punch_control_obj->isValid() == TRUE ) {
							Debug::Text(' Punch Control is valid, saving...: ', __FILE__, __LINE__, __METHOD__,10);

							//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
							//in the case of a drag & drop or something.
							$dst_punch_control_obj->setEnableStrictJobValidation( TRUE );
							$dst_punch_control_obj->setEnableCalcUserDateID( TRUE );
							$dst_punch_control_obj->setEnableCalcTotalTime( TRUE );
							$dst_punch_control_obj->setEnableCalcSystemTotalTime( TRUE );
							$dst_punch_control_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
							$dst_punch_control_obj->setEnableCalcUserDateTotal( TRUE );
							$dst_punch_control_obj->setEnableCalcException( TRUE );
							if ( $dst_punch_control_obj->isValid() == TRUE ) {
								if ( $dst_punch_control_obj->Save( TRUE, TRUE ) == TRUE ) { //Force isNew() lookup.
									//Return newly inserted punch_id, so Flex can base other actions on it.
									$retval = $insert_id;
									//$retval = TRUE;
								}
							}
						}
					}
				}
			}
		}

		if ( $retval == FALSE ) {
			$plf->FailTransaction();
		}
		//$plf->FailTransaction();
		$plf->CommitTransaction();

		Debug::text('Returning: '. (int)$retval, __FILE__, __LINE__, __METHOD__,10);
		return $retval;
	}

	//When passed a punch_control_id, if it has two punches assigned to it, a new punch_control_id row is created and the punches are split between the two.
	static function splitPunchControl( $punch_control_id ) {
		$retval = FALSE;
		if ( $punch_control_id != '' ) {
			$plf = new PunchListFactory();
			$plf->StartTransaction();
			$plf->getByPunchControlID( $punch_control_id, NULL, array( 'time_stamp' => 'desc' ) ); //Move out punch to new punch_control_id.
			if ( $plf->getRecordCount() == 2 ) {
				$pclf = new PunchControlListFactory();
				$new_punch_control_id = (int)$pclf->getNextInsertId();
				Debug::text(' Punch Control ID: '. $punch_control_id .' only has two punches assigned, splitting... New Punch Control ID: '. $new_punch_control_id, __FILE__, __LINE__, __METHOD__,10);
				$i = 0;
				foreach( $plf->rs as $p_obj ) {
					$plf->data = (array)$p_obj;
					$p_obj = $plf;
					if ( $i == 0 ) {
						//First punch (out)
						//Get the PunchControl Object before we change to the new punch_control_id
						$pc_obj = $p_obj->getPunchControlObject();

						$p_obj->setPunchControlId( $new_punch_control_id );
						if ( $p_obj->isValid() == TRUE ) {
							$p_obj->Save( FALSE );

							$pc_obj->setId( $new_punch_control_id );
							$pc_obj->setPunchObject( $p_obj );

							if ( $pc_obj->isValid() == TRUE ) {
								Debug::Text(' Punch Control is valid, saving Punch ID: '. $p_obj->getID() .' To new Punch Control ID: '. $new_punch_control_id, __FILE__, __LINE__, __METHOD__,10);

								//We need to calculate new total time for the day and exceptions because we are never guaranteed that the gaps will be filled immediately after
								//in the case of a drag & drop or something.
								$pc_obj->setEnableStrictJobValidation( TRUE );
								$pc_obj->setEnableCalcUserDateID( TRUE );
								$pc_obj->setEnableCalcTotalTime( TRUE );
								$pc_obj->setEnableCalcSystemTotalTime( FALSE ); //Do this for In punch only.
								$pc_obj->setEnableCalcWeeklySystemTotalTime( FALSE ); //Do this for In punch only.
								$pc_obj->setEnableCalcUserDateTotal( TRUE );
								$pc_obj->setEnableCalcException( TRUE );
								$retval = $pc_obj->Save( TRUE, TRUE ); //Force isNew() lookup.
							}
						}
					} else {
						//Second punch (in), need to recalculate user_date_total for this one to clear the total time, as well as recalculate the entire week
						//for system totals so those are updated as well.
						Debug::text(' ReCalculating total time for In punch...', __FILE__, __LINE__, __METHOD__,10);
						$pc_obj = $p_obj->getPunchControlObject();
						$pc_obj->setEnableStrictJobValidation( TRUE );
						$pc_obj->setEnableCalcUserDateID( TRUE );
						$pc_obj->setEnableCalcTotalTime( TRUE );
						$pc_obj->setEnableCalcSystemTotalTime( TRUE );
						$pc_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
						$pc_obj->setEnableCalcUserDateTotal( TRUE );
						$pc_obj->setEnableCalcException( TRUE );
						$retval = $pc_obj->Save();
					}

					$i++;
				}
			} else {
				Debug::text(' Punch Control ID: '. $punch_control_id .' only has one punch assigned, doing nothing...', __FILE__, __LINE__, __METHOD__,10);
			}

			//$plf->FailTransaction();
			$plf->CommitTransaction();
		}

		return $retval;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		$this->calcUserDate();
		$this->calcUserDateTotal();

		if ( $this->getEnableCalcSystemTotalTime() == TRUE ) {
			$this->old_user_date_ids[] = $this->getUserDateID();
			if ( $this->getUser() > 0 ) {
				UserDateTotalFactory::smartReCalculate( $this->getUser(), $this->old_user_date_ids, $this->getEnableCalcException(), $this->getEnablePreMatureException() );
			}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'user_date_id': //Ignore any user_date_id, as we will figure it out on our own based on the time_stamp and pay period settings ($pcf->setEnableCalcUserDateID(TRUE))
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'total_time':  //Ignore total time, as its calculated later anyways, so if its set here it will cause a validation error.
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('Punch Control - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ), NULL, $this->getTable(), $this );
	}
}
?>
