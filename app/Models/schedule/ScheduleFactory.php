<?php

namespace App\Models\Schedule;
use App\Models\Core\Factory; 

class ScheduleFactory extends Factory {
	protected $table = 'schedule';
	protected $pk_sequence_name = 'schedule_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $schedule_policy_obj = NULL;
	protected $absence_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Committed'),
										20 => ('Recurring')
									);
				break;
			case 'status':
				$retval = array(
										10 => ('Working'),
										20 => ('Absent'),
										//Not displayed on schedules, used to overwrite recurring schedule if we want to change a 8AM-5PM recurring schedule
										//with a 6PM-11PM schedule? Although this can be done with an absence shift as well...
										//90 => ('Hidden'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => ('First Name'),
										'-1002-last_name' => ('Last Name'),
										'-1005-user_status' => ('Employee Status'),
										'-1010-title' => ('Title'),
										'-1039-group' => ('Group'),
										'-1040-default_branch' => ('Default Branch'),
										'-1050-default_department' => ('Default Department'),
										'-1160-branch' => ('Branch'),
										'-1170-department' => ('Department'),
										'-1200-status' => ('Status'),
										'-1210-schedule_policy_id' => ('Schedule Policy'),
										'-1220-start_time' => ('Start Time'),
										'-1230-end_time' => ('End Time'),
										'-1240-total_time' => ('Total Time'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);

				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$retval['-1180-job'] = ('Job');
					$retval['-1190-job_item'] = ('Task');
				}
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'status',
								'start_time',
								'end_time',
								'total_time',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
			case 'group_columns': //Columns available for grouping on the schedule.
				$retval = array(
								'title',
								'group',
								'default_branch',
								'default_department',
								'branch',
								'department',
								);

				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$retval[] = 'job';
					$retval[] = 'job_item';

				}
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',

										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_status_id' => FALSE,
										'user_status' => FALSE,
										'group_id' => FALSE,
										'group' => FALSE,
										'title_id' => FALSE,
										'title' => FALSE,
										'default_branch_id' => FALSE,
										'default_branch' => FALSE,
										'default_department_id' => FALSE,
										'default_department' => FALSE,

										'date_stamp' => FALSE,
										'user_date_id' => 'UserDateID',
										'pay_period_id' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'schedule_policy_id' => FALSE,
										'schedule_policy' => FALSE,
										'start_date' => FALSE,
										'end_date' => FALSE,
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'schedule_policy_id' => 'SchedulePolicyID',
										'absence_policy_id' => 'AbsencePolicyID',
										'branch_id' => 'Branch',
										'branch' => FALSE,
										'department_id' => 'Department',
										'department' => FALSE,
										'job_id' => 'Job',
										'job' => FALSE,
										'job_item_id' => 'JobItem',
										'job_item' => FALSE,
										'total_time' => 'TotalTime',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	 }

	function getSchedulePolicyObject() {
		if ( is_object($this->schedule_policy_obj) ) {
			return $this->schedule_policy_obj;
		} else {
			$splf = TTnew( 'SchedulePolicyListFactory' );
			$splf->getById( $this->getSchedulePolicyID() );
			if ( $splf->getRecordCount() > 0 ) {
				$this->schedule_policy_obj = $splf->getCurrent();
				return $this->schedule_policy_obj;
			}

			return FALSE;
		}
	}

	function getAbsencePolicyObject() {
		if ( is_object($this->absence_policy_obj) ) {
			return $this->absence_policy_obj;
		} else {
			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$aplf->getById( $this->getAbsencePolicyID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->absence_policy_obj = $aplf->getCurrent();
			}

			return $this->absence_policy_obj;
		}
	}

	function getUserDateObject() {
		if ( is_object($this->user_date_obj) ) {
			return $this->user_date_obj;
		} else {
			$udlf = TTnew( 'UserDateListFactory' );
			$udlf->getById( $this->getUserDateID() );
			if ( $udlf->getRecordCount() > 0 ) {
				$this->user_date_obj = $udlf->getCurrent();
				return $this->user_date_obj;
			}

			return FALSE;
		}
	}

	function getUser() {
		if ( isset($this->tmp_data['user_id']) ) {
			return $this->tmp_data['user_id'];
		}
	}
	function setUser($id) {
		$id = (int)trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id > 0 AND
				$this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															('Invalid User')
															) ) {
			$this->tmp_data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function findUserDate($user_id, $epoch) {
		//Get pay period start/continuous time
		//FIXME: Add proper schedule support for new_day_trigger_time.
		/*
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getByUserId( $user_id );
		if ( $ppslf->getRecordCount() == 1 ) {
			$pps_obj = $ppslf->getCurrent();
			Debug::Text(' Pay Period Schedule Maximum Shift Time: '. $pps_obj->getMaximumShiftTime(), __FILE__, __LINE__, __METHOD__,10);

			$plf = TTnew( 'PunchListFactory' );
			$plf->getFirstPunchByUserIDAndEpoch( $user_id, $epoch, $pps_obj->getMaximumShiftTime() );

			if ( $plf->getRecordCount() > 0 ) {
				$p_obj = $plf->getCurrent();
				if ( ( $epoch - $p_obj->getTimeStamp() ) <= $pps_obj->getMaximumShiftTime() ) {
					$user_date_id = $p_obj->getPunchControlObject()->getUserDateID();
					Debug::text(' User Date ID found: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

					$this->setUserDateID( $user_date_id, TRUE );
					return TRUE;
				}
			}

		}
		*/

		return $this->setUserDate( $user_id, $epoch );
	}

	function setUserDate($user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date);
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_date_id != '' ) {
			$this->setUserDateID( $user_date_id );
			return TRUE;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getUserDateID() {
		if ( isset($this->data['user_date_id']) ) {
			return (int)$this->data['user_date_id'];
		}

		return FALSE;
	}

	function setUserDateID($id, $skip_check = FALSE ) {
		$id = (int)trim($id);

		$udlf = TTnew( 'UserDateListFactory' );

		if (  	$skip_check == TRUE
				OR
				(
					$id > 0
					AND
					$this->Validator->isResultSetWithRows(	'user_date',
															$udlf->getByID($id),
															('Date/Time is incorrect or pay period does not exist for this date. Please create a pay period schedule if you have not done so already')
															)
				)
				) {
			$this->data['user_date_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getStartTime( $raw = FALSE ) {
		if ( isset($this->data['start_time']) ) {
			return TTDate::strtotime( $this->data['start_time'] );
		}

		return FALSE;
	}
	function setStartTime($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'start_time',
												$epoch,
												('Incorrect start time'))

			) {

			$this->data['start_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndTime( $raw = FALSE ) {
		if ( isset($this->data['end_time']) ) {
			return TTDate::strtotime( $this->data['end_time'] );
		}

		return FALSE;
	}
	function setEndTime($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'end_time',
												$epoch,
												('Incorrect end time'))

			) {

			$this->data['end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}


	function calcTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			$total_time = ( $this->getEndTime() - $this->getStartTime() );

			if ( $this->getSchedulePolicyObject() != FALSE ) {
				if ( $this->getSchedulePolicyObject()->getMealPolicyObject() != FALSE ) {
					if ( $this->getSchedulePolicyObject()->getMealPolicyObject()->getType() == 10
							OR $this->getSchedulePolicyObject()->getMealPolicyObject()->getType() == 20 ) {

						if ( $total_time > $this->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime() ) {
							$total_time -= $this->getSchedulePolicyObject()->getMealPolicyObject()->getAmount();
						}
					}
				}
			}

			return $total_time;
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


	function getSchedulePolicyID() {
		if ( isset($this->data['schedule_policy_id']) ) {
			return $this->data['schedule_policy_id'];
		}

		return FALSE;
	}
	function setSchedulePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$splf = TTnew( 'SchedulePolicyListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'schedule_policy',
														$splf->getByID($id),
														('Schedule Policy is invalid')
													) ) {

			$this->data['schedule_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return $this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy',
														$aplf->getByID($id),
														('Invalid Absence Policy ID')
														) ) {
			$this->data['absence_policy_id'] = $id;

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

		$blf = TTnew( 'BranchListFactory' );

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

		$dlf = TTnew( 'DepartmentListFactory' );

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
			$jlf = TTnew( 'JobListFactory' );
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
			$id = NULL;
		}

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jilf = TTnew( 'JobItemListFactory' );
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

	function inSchedule( $epoch ) {
		if ( $epoch >= $this->getStartTime() AND $epoch <= $this->getEndTime() ) {
			Debug::text('aWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		} elseif ( $this->inStartWindow( $epoch ) OR $this->inStopWindow( $epoch ) )  {
			Debug::text('bWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}

		return FALSE;
	}

	function inStartWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if (	$this->getSchedulePolicyObject() !== FALSE
				AND
				(
					$epoch >= ( $this->getStartTime() - $this->getSchedulePolicyObject()->getStartStopWindow() )
					AND
					$epoch <= ( $this->getStartTime() + $this->getSchedulePolicyObject()->getStartStopWindow() )
				)
			) {
			Debug::text(' Within Start/Stop window: '. $this->getSchedulePolicyObject()->getStartStopWindow() , __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		} else {
			Debug::text(' NOT Within Start/Stop window.', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		return FALSE;
	}

	function inStopWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if (	$this->getSchedulePolicyObject() !== FALSE
				AND
				(
					$epoch >= ( $this->getEndTime() - $this->getSchedulePolicyObject()->getStartStopWindow() )
					AND
					$epoch <= ( $this->getEndTime() + $this->getSchedulePolicyObject()->getStartStopWindow() )
				)

			) {
			Debug::text(' Within Start/Stop window: '. $this->getSchedulePolicyObject()->getStartStopWindow() , __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		} else {
			Debug::text(' NOT Within Start/Stop window.', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		return FALSE;
	}

	function mergeScheduleArray($schedule_shifts, $recurring_schedule_shifts) {
		//Debug::text('Merging Schedule, and Recurring Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		$ret_arr = $schedule_shifts;

		//Debug::Arr($schedule_shifts, '(c) Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($recurring_schedule_shifts) AND count($recurring_schedule_shifts) > 0 ) {
			foreach( $recurring_schedule_shifts as $date_stamp => $day_shifts_arr ) {
				//Debug::text('----------------------------------', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp). ' Epoch: '. $date_stamp , __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($schedule_shifts[$date_stamp], 'Date Arr: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $day_shifts_arr as $key => $shift_arr ) {

					if ( isset($ret_arr[$date_stamp]) ) {
						//Debug::text('Already Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp) , __FILE__, __LINE__, __METHOD__, 10);

						//Loop through each shift on this day, and check for overlaps
						//Only include the recurring shift if ALL times DO NOT overlap
						$overlap = 0;
						foreach( $ret_arr[$date_stamp] as $tmp_shift_arr ) {
							if ( TTDate::isTimeOverLap( $shift_arr['start_time'], $shift_arr['end_time'], $tmp_shift_arr['start_time'], $tmp_shift_arr['end_time']) ) {
								//Debug::text('Times OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']) , __FILE__, __LINE__, __METHOD__, 10);
								$overlap++;
							} else {
								//Debug::text('Times DO NOT OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']) , __FILE__, __LINE__, __METHOD__, 10);
							}
						}

						if ( $overlap == 0 ) {
							//Debug::text('NO Times OverLap, using recurring schedule: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']) , __FILE__, __LINE__, __METHOD__, 10);
							$ret_arr[$date_stamp][] = $shift_arr;
						}
					} else {
						//Debug::text('No Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp) , __FILE__, __LINE__, __METHOD__, 10);
						$ret_arr[$date_stamp][] = $shift_arr;
					}
				}
			}
		}

		return $ret_arr;
	}

	function getScheduleArray( $filter_data )  {
		global $current_user, $current_user_prefs;

		//Get all schedule data by general filter criteria.
		Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !isset($filter_data['start_date']) OR $filter_data['start_date'] == '' ) {
			return FALSE;
		}

		if ( !isset($filter_data['end_date']) OR $filter_data['end_date'] == '' ) {
			return FALSE;
		}

		$filter_data['start_date'] = TTDate::getBeginDayEpoch( $filter_data['start_date'] );
		$filter_data['end_date'] = TTDate::getEndDayEpoch( $filter_data['end_date'] );

		//$blf = TTnew( 'BranchListFactory' );
		//$branch_options = $blf->getByCompanyIdArray( $current_user->getCompany(), FALSE );
		$branch_options = array(); //No longer needed, use SQL instead.

		//$dlf = TTnew( 'DepartmentListFactory' );
		//$department_options = $dlf->getByCompanyIdArray( $current_user->getCompany(), FALSE );
		$department_options = array(); //No longer needed, use SQL instead.

		$apf = TTnew( 'AbsencePolicyFactory' );
		$absence_policy_paid_type_options = $apf->getOptions('paid_type');

		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
		Debug::text('Found Scheduled Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($absence_policy_paid_type_options, 'Paid Absences: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $slf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $slf->getRecordCount(), NULL, ('Processing Committed Shifts...') );

			foreach( $slf as $s_obj ) {
				//Debug::text('Schedule ID: '. $s_obj->getId() .' User ID: '. $s_obj->getColumn('user_id') .' Start Time: '. $s_obj->getStartTime(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $s_obj->getAbsencePolicyID() > 0 AND is_object($s_obj->getAbsencePolicyObject()) ) {
					$absence_policy_name = (string)$s_obj->getAbsencePolicyObject()->getName();
				} elseif ( $s_obj->getAbsencePolicyID() > 0 ) {
					$absence_policy_name = 'N/A';
				} else {
					$absence_policy_name = FALSE;
				}

				$hourly_rate = Misc::MoneyFormat( $s_obj->getColumn('user_wage_hourly_rate'), FALSE );

				if ( $s_obj->getAbsencePolicyID() > 0
						AND  is_object($s_obj->getAbsencePolicyObject())
						AND in_array( $s_obj->getAbsencePolicyObject()->getType(), $absence_policy_paid_type_options ) == FALSE ) {
					//UnPaid Absence.
					$total_time_wage = Misc::MoneyFormat(0);
				} else {
					$total_time_wage = Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), $hourly_rate ), FALSE );
				}

				$iso_date_stamp = TTDate::getISODateStamp($s_obj->getStartTime());
				$schedule_shifts[$iso_date_stamp][$s_obj->getColumn('user_id').$s_obj->getStartTime()] = array(
													'id' => (int)$s_obj->getID(),
													'pay_period_id' => (int)$s_obj->getColumn('pay_period_id'),
													'user_id' => (int)$s_obj->getColumn('user_id'),
													'user_created_by' => (int)$s_obj->getColumn('user_created_by'),
													'user_full_name' => Misc::getFullName( $s_obj->getColumn('first_name'), NULL, $s_obj->getColumn('last_name'), FALSE, FALSE ),
													'first_name' => $s_obj->getColumn('first_name'),
													'last_name' => $s_obj->getColumn('last_name'),
													'title_id' => $s_obj->getColumn('title_id'),
													'title' => $s_obj->getColumn('title'),
													'group_id' => $s_obj->getColumn('group_id'),
													'group' => $s_obj->getColumn('group'),
													'default_branch_id' => $s_obj->getColumn('default_branch_id'),
													'default_branch' => $s_obj->getColumn('default_branch'),
													//'default_branch' => Option::getByKey($s_obj->getColumn('default_branch_id'), $branch_options, NULL ),
													'default_department_id' => $s_obj->getColumn('default_department_id'),
													'default_department' => $s_obj->getColumn('default_department'),
													//'default_department' =>  Option::getByKey($s_obj->getColumn('default_department_id'), $department_options, NULL ),

													'job_id' => $s_obj->getColumn('job_id'),
													'job' => $s_obj->getColumn('job'),
													'job_status_id' => $s_obj->getColumn('job_status_id'),
													'job_manual_id' => $s_obj->getColumn('job_manual_id'),
													'job_branch_id' => $s_obj->getColumn('job_branch_id'),
													'job_department_id' => $s_obj->getColumn('job_department_id'),
													'job_group_id' => $s_obj->getColumn('job_group_id'),
													'job_item_id' => $s_obj->getColumn('job_item_id'),
													'job_item' => $s_obj->getColumn('job_item'),

													'type_id' => 10, //Committed
													'status_id' => (int)$s_obj->getStatus(),

													'date_stamp' => TTDate::getAPIDate( 'DATE', strtotime( $s_obj->getColumn('date_stamp') ) ),
													'start_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $s_obj->getStartTime() ) : $s_obj->getStartTime(),
													'end_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $s_obj->getEndTime() ) : $s_obj->getEndTime(),
													'start_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $s_obj->getStartTime() ) : $s_obj->getStartTime(),
													'end_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $s_obj->getEndTime() ) : $s_obj->getEndTime(),

													'total_time' => $s_obj->getTotalTime(),

													'hourly_rate' => $hourly_rate,
													'total_time_wage' => $total_time_wage,

													'schedule_policy_id' => (int)$s_obj->getSchedulePolicyID(),
													'absence_policy_id' => (int)$s_obj->getAbsencePolicyID(),
													'absence_policy' => $absence_policy_name,
													'branch_id' => (int)$s_obj->getBranch(),
													'branch' => $s_obj->getColumn('branch'),
													//'branch' => Option::getByKey($s_obj->getBranch(), $branch_options, NULL ),
													'department_id' => (int)$s_obj->getDepartment(),
													'department' => $s_obj->getColumn('department'),
													//'department' => Option::getByKey($s_obj->getDepartment(), $department_options, NULL ),

													'created_by_id' => $s_obj->getCreatedBy(),
												);
				$schedule_shifts_index[$iso_date_stamp][$s_obj->getColumn('user_id')][] = $s_obj->getColumn('user_id').$s_obj->getStartTime();
				unset($absence_policy_name);

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $slf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//Debug::Arr($schedule_shifts, 'Committed Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($schedule_shifts_index, 'Committed Schedule Shifts Index: ', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$schedule_shifts = array();
		}
		unset($slf);

		//Get holidays
		//FIXME: What if there are two holiday policies, one that defaults to working, and another that defaults to not working, and they are assigned
		//to two different groups of employees? For that matter what if the holiday policy isn't assigned to a specific user at all.
		$holiday_data = array();
		$hlf = TTnew( 'HolidayListFactory' );
		$hlf->getByCompanyIdAndStartDateAndEndDate( $current_user->getCompany(), $filter_data['start_date'], $filter_data['end_date'] );
		Debug::text('Found Holiday Rows: '. $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach( $hlf as $h_obj ) {
			if ( is_object( $h_obj->getHolidayPolicyObject() ) AND is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject() ) ) {
				$holiday_data[TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus(), 'absence_policy_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyID(), 'type_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getType(), 'absence_policy' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getName() );
			} else {
				$holiday_data[TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => 10 ); //Working
			}
		}
		unset($hlf);

		$recurring_schedule_shifts = array();
		$recurring_schedule_shifts_index = array();

		//If they just want to see absence shifts, ignore recurring schedules completely.
		if ( !(isset($filter_data['status_id']) AND in_array( 20, (array)$filter_data['status_id'] )) ) {
			$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' );
			$rstlf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
			Debug::text('Found Recurring Schedule Template Rows: '. $rstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $rstlf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $rstlf->getRecordCount(), NULL, ('Processing Recurring Shifts...') );

				foreach( $rstlf as $rst_obj ) {
					//Debug::text('Recurring Schedule Template ID: '. $rst_obj->getID() , __FILE__, __LINE__, __METHOD__, 10);
					$rst_obj->getShifts( $filter_data['start_date'], $filter_data['end_date'], $holiday_data, $branch_options, $department_options, $schedule_shifts, $schedule_shifts_index );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $rstlf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
			} else {
				Debug::text('DID NOT find Recurring Schedule for this time period: ', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('Skipping recurring schedule check due to status_id filter...', __FILE__, __LINE__, __METHOD__, 10);
		}
		//Debug::Arr($schedule_shifts, 'Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
		unset($schedule_shifts_index, $recurring_schedule_shifts_index);

		if ( isset($schedule_shifts) ) {
			return $schedule_shifts;
		}

		return FALSE;
	}

	function getEnableReCalculateDay() {
		if ( isset($this->recalc_day) ) {
			return $this->recalc_day;
		}

		return FALSE;
	}
	function setEnableReCalculateDay($bool) {
		$this->recalc_day = $bool;

		return TRUE;
	}

	function getEnableOverwrite() {
		if ( isset($this->overwrite) ) {
			return $this->overwrite;
		}

		return FALSE;
	}
	function setEnableOverwrite($bool) {
		$this->overwrite = $bool;

		return TRUE;
	}

	function handleDayBoundary() {
		Debug::Arr($this->getStartTime(), 'Start Time: '. TTDate::getDate('DATE+TIME', $this->getStartTime()), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($this->getEndTime(), 'End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()), __FILE__, __LINE__, __METHOD__, 10);

		//This used to be done in Validate, but needs to be done in preSave too.
		//Allow 12:00AM to 12:00AM schedules for a total of 24hrs.
		if ( $this->getEndTime() <= $this->getStartTime() ) {
			Debug::Text('EndTime spans midnight boundary! Increase by 24hrs ', __FILE__, __LINE__, __METHOD__,10);
			$this->setEndTime( $this->getEndTime() + 86400 ); //End time spans midnight, add 24hrs.
		}

		return TRUE;
	}

	//Write all the schedules shifts for a given week.
	function writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day = 0, $bottom_border = FALSE) {
		$week_of_year = TTDate::getWeek( strtotime($week_date_stamps[0]), $start_week_day);
		//Debug::Text('Max Week Shifts: '. (int)$max_week_data[$week_of_year]['shift'], __FILE__, __LINE__, __METHOD__,10);
		//Debug::Text('Max Week Branches: '. count($max_week_data[$week_of_year]['branch']), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Text('Max Week Departments: '. count($max_week_data[$week_of_year]['department']), __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Week Of Year: '. $week_of_year, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($max_week_data, 'max_week_data: ', __FILE__, __LINE__, __METHOD__,10);

		$week_data_array = NULL;

		if ( !isset($max_week_data[$week_of_year]['labels']) ) {
			$max_week_data[$week_of_year]['labels'] = 0;
		}

		if ( $group_schedule == TRUE ) {
			$min_rows_multiplier = 2;
		} else {
			$min_rows_multiplier = 1;
		}

		if ( isset($max_week_data[$week_of_year]['shift']) ) {
			$min_rows_per_day = ($max_week_data[$week_of_year]['shift']*$min_rows_multiplier) + $max_week_data[$week_of_year]['labels'];
			Debug::Text('Shift Total: '. $max_week_data[$week_of_year]['shift'], __FILE__, __LINE__, __METHOD__,10);
		} else {
			$min_rows_per_day = $min_rows_multiplier + $max_week_data[$week_of_year]['labels'];
		}
		Debug::Text('aMin Rows Per Day: '. $min_rows_per_day .' Labels: '. $max_week_data[$week_of_year]['labels'], __FILE__, __LINE__, __METHOD__,10);
		//print_r($this->schedule_shifts);

		//Prepare data so we can write it out line by line, left to right.
		$shift_counter = 0;
		foreach( $week_date_stamps as $week_date_stamp ) {
			Debug::Text('Week Date Stamp: ('.$week_date_stamp.')'. TTDate::getDate('DATE+TIME', strtotime($week_date_stamp)), __FILE__, __LINE__, __METHOD__,10);

			$rows_per_day = 0;
			if ( isset($this->schedule_shifts[$week_date_stamp]) ) {
				foreach( $this->schedule_shifts[$week_date_stamp] as $branch => $department_schedule_shifts ) {
					if ( $branch != '--' ) {
						$tmp_week_data_array[$week_date_stamp][] = array('type' => 'branch', 'date_stamp' => $week_date_stamp, 'label' => $branch );
						$rows_per_day++;
					}

					foreach( $department_schedule_shifts as $department => $tmp_schedule_shifts ) {
						if ( $department != '--' ) {
							$tmp_week_data_array[$week_date_stamp][] = array('type' => 'department', 'label' => $department );
							$rows_per_day++;
						}

						foreach( $tmp_schedule_shifts as $schedule_shift ) {
							if ( $group_schedule == TRUE ) {
								$tmp_week_data_array[$week_date_stamp][] = array('type' => 'user_name', 'label' => $schedule_shift['user_full_name'], 'shift' => $shift_counter );
								if ( $schedule_shift['status_id'] == 10 ) {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'shift', 'label' => TTDate::getDate('TIME', $schedule_shift['start_time'] ) .' - '. TTDate::getDate('TIME', $schedule_shift['end_time'] ), 'shift' => $shift_counter );
								} else {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'absence', 'label' => $schedule_shift['absence_policy'], 'shift' => $shift_counter );
								}
								$rows_per_day += 2;
							} else {
								if ( $schedule_shift['status_id'] == 10 ) {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'shift', 'label' => TTDate::getDate('TIME', $schedule_shift['start_time'] ) .' - '. TTDate::getDate('TIME', $schedule_shift['end_time'] ), 'shift' => $shift_counter );
								} else {
									$tmp_week_data_array[$week_date_stamp][] = array('type' => 'absence', 'label' => $schedule_shift['absence_policy'], 'shift' => $shift_counter );
								}
								$rows_per_day++;
							}
							$shift_counter++;
						}
					}
				}
			}

			if ( $rows_per_day < $min_rows_per_day ) {
				for($z=$rows_per_day; $z < $min_rows_per_day; $z++) {
					$tmp_week_data_array[$week_date_stamp][] = array('type' => 'blank', 'label' => NULL );
				}
			}
		}
		//print_r($tmp_week_data_array);

		for($x=0; $x < $min_rows_per_day; $x++ ) {
			foreach( $week_date_stamps as $week_date_stamp ) {
				if ( isset($tmp_week_data_array[$week_date_stamp][0]) ) {
					$week_data_array[] = $tmp_week_data_array[$week_date_stamp][0];
					array_shift($tmp_week_data_array[$week_date_stamp]);
				}
			}
		}
		unset($tmp_week_data_array);
		//print_r($week_data_array);

		//Render PDF here
		$border = 'LR';
		$i=0;
		$total_cells = count($week_data_array);

		foreach( $week_data_array as $key => $data ) {
			if ( $i % 7 == 0 ) {
				$pdf->Ln();
			}

			$pdf->setTextColor(0,0,0); //Black
			switch( $data['type'] ) {
				case 'branch':
					$pdf->setFillColor(200,200,200);
					$pdf->SetFont('freesans','B',8);
					break;
				case 'department':
					$pdf->setFillColor(220,220,220);
					$pdf->SetFont('freesans','B',8);
					break;
				case 'user_name':
					if ( $data['shift'] % 2 == 0 ) {
						$pdf->setFillColor(240,240,240);
					} else {
						$pdf->setFillColor(255,255,255);
					}
					$pdf->SetFont('freesans','B',8);
					break;
				case 'shift':
					if ( $data['shift'] % 2 == 0 ) {
						$pdf->setFillColor(240,240,240);
					} else {
						$pdf->setFillColor(255,255,255);
					}
					$pdf->SetFont('freesans','',8);
					break;
				case 'absence':
					$pdf->setTextColor(255,0,0);
					if ( $data['shift'] % 2 == 0 ) {
						$pdf->setFillColor(240,240,240);
					} else {
						$pdf->setFillColor(255,255,255);
					}
					$pdf->SetFont('freesans','I',8);
					break;
				case 'blank':
					$pdf->setFillColor(255,255,255);
					$pdf->SetFont('freesans','',8);
					break;
			}

			if ( $bottom_border == TRUE AND $i >= ($total_cells-7) ) {
				$border = 'LRB';
			}

			$pdf->Cell($cell_width, 15, $data['label'], $border, 0, 'C', 1);
			$pdf->setTextColor(0,0,0); //Black

			$i++;
		}

		$pdf->Ln();

		return TRUE;
	}

	//function getSchedule( $company_id, $user_ids, $start_date, $end_date, $start_week_day = 0, $group_schedule = FALSE ) {
	function getSchedule( $filter_data, $start_week_day = 0, $group_schedule = FALSE ) {
		global $current_user, $current_user_prefs;

		//Individual is one schedule per employee, or all on one schedule.
		if (!is_array($filter_data) ) {
			return FALSE;
		}

		$current_epoch = time();

		//Debug::Text('Start Date: '. TTDate::getDate('DATE', $start_date) .' End Date: '. TTDate::getDate('DATE', $end_date) , __FILE__, __LINE__, __METHOD__,10);
		Debug::text(' Start Date: '. TTDate::getDate('DATE+TIME', $filter_data['start_date']) .' End Date: '. TTDate::getDate('DATE+TIME', $filter_data['end_date']) .' Start Week Day: '. $start_week_day, __FILE__, __LINE__, __METHOD__,10);

		$pdf = new TTPDF('L', 'pt', 'Letter');

		$left_margin = 20;
		$top_margin = 20;
		$pdf->setMargins($left_margin,$top_margin);
		$pdf->SetAutoPageBreak(TRUE, 30);
		//$pdf->SetAutoPageBreak(FALSE);
		$pdf->SetFont('freesans','',10);

		$border = 0;
		$adjust_x = 0;
		$adjust_y = 0;

		if ( $group_schedule == FALSE ) {
			$valid_schedules = 0;

			$sf = TTnew( 'ScheduleFactory' );
			$tmp_schedule_shifts = $sf->getScheduleArray( $filter_data );
			//Re-arrange array by user_id->date
			if ( is_array($tmp_schedule_shifts) ) {
				foreach( $tmp_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
					foreach ( $day_schedule_shifts as $day_schedule_shift ) {
						$raw_schedule_shifts[$day_schedule_shift['user_id']][$day_epoch][] = $day_schedule_shift;
					}
				}
			}
			unset($tmp_schedule_shifts);
			//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__,10);

			if ( is_array($raw_schedule_shifts) ) {
				foreach( $raw_schedule_shifts as $user_id => $day_schedule_shifts ) {

					foreach( $day_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
						foreach ( $day_schedule_shifts as $day_schedule_shift ) {
							//Debug::Arr($day_schedule_shift, 'aDay Schedule Shift: ', __FILE__, __LINE__, __METHOD__,10);
							$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;

							if ( isset($schedule_shift_totals[$day_epoch]['total_shifts']) ) {
								$schedule_shift_totals[$day_epoch]['total_shifts']++;
							} else {
								$schedule_shift_totals[$day_epoch]['total_shifts'] = 1;
							}

							//$week_of_year = TTDate::getWeek( strtotime($day_epoch) );
							$week_of_year = TTDate::getWeek( strtotime($day_epoch), $start_week_day );
							if ( !isset($schedule_shift_totals[$day_epoch]['labels']) ) {
								$schedule_shift_totals[$day_epoch]['labels'] = 0;
							}
							if ( $day_schedule_shift['branch'] != '--'
									AND !isset($schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']]) ) {
								$schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']] = TRUE;
								$schedule_shift_totals[$day_epoch]['labels']++;
							}
							if ( $day_schedule_shift['department'] != '--'
									AND !isset($schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']]) ) {
								$schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']] = TRUE;
								$schedule_shift_totals[$day_epoch]['labels']++;
							}

							if ( !isset($max_week_data[$week_of_year]['shift']) ) {
								Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to 0', __FILE__, __LINE__, __METHOD__,10);
								$max_week_data[$week_of_year]['shift'] = 1;
								$max_week_data[$week_of_year]['labels'] = 0;
							}

							if ( isset($max_week_data[$week_of_year]['shift'])
									AND ($schedule_shift_totals[$day_epoch]['total_shifts']+$schedule_shift_totals[$day_epoch]['labels']) > ($max_week_data[$week_of_year]['shift']+$max_week_data[$week_of_year]['labels']) ) {
								Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to: '.  $schedule_shift_totals[$day_epoch]['total_shifts'] .' Labels: '. $schedule_shift_totals[$day_epoch]['labels'], __FILE__, __LINE__, __METHOD__,10);
								$max_week_data[$week_of_year]['shift'] = $schedule_shift_totals[$day_epoch]['total_shifts'];
								$max_week_data[$week_of_year]['labels'] = $schedule_shift_totals[$day_epoch]['labels'];
							}

							//Debug::Arr($schedule_shift_totals, ' Schedule Shift Totals: ', __FILE__, __LINE__, __METHOD__,10);
							//Debug::Arr($max_week_data, ' zMaxWeekData: ', __FILE__, __LINE__, __METHOD__,10);
						}
					}

					if ( isset($tmp_schedule_shifts) ) {
						//Sort Branches/Departments first
						foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
							ksort($day_tmp_schedule_shift);
							$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

							foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
								ksort($tmp_schedule_shifts[$day_epoch][$branch]);
							}
						}

						//Sort each department by start time.
						foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
							foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
								foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
									$department_schedule_shift = Sort::multiSort( $department_schedule_shift, 'start_time' );

									$this->schedule_shifts[$day_epoch][$branch][$department] = $department_schedule_shift;
								}
							}
						}
					}
					unset($day_tmp_schedule_shift, $department_schedule_shifts, $department_schedule_shift, $tmp_schedule_shifts, $branch, $department);

					$calendar_array = TTDate::getCalendarArray($filter_data['start_date'], $filter_data['end_date'], $start_week_day );
					//var_dump($calendar_array);

					if ( !is_array($calendar_array) OR !isset($this->schedule_shifts) OR !is_array($this->schedule_shifts) ) {
						continue; //Skip to next user.
					}

					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByIdAndCompanyId( $user_id, $current_user->getCompany() );
					if ( $ulf->getRecordCount() != 1 ) {
						continue;
					} else {
						$user_obj = $ulf->getCurrent();

						$pdf->AddPage();

						$pdf->setXY( 670, $top_margin);
						$pdf->SetFont('freesans','',10);
						$pdf->Cell(100,15, TTDate::getDate('DATE+TIME', $current_epoch ), $border, 0, 'R');

						$pdf->setXY( $left_margin, $top_margin);
						$pdf->SetFont('freesans','B',25);
						$pdf->Cell(0,25, $user_obj->getFullName(). ' - '. ('Schedule'), $border, 0, 'C');
						$pdf->Ln();
					}

					$pdf->SetFont('freesans','B',16);
					$pdf->Cell(0,15, TTDate::getDate('DATE', $filter_data['start_date']) .' - '. TTDate::getDate('DATE', $filter_data['end_date']), $border, 0, 'C');
					//$pdf->Ln();
					$pdf->Ln();
					$pdf->Ln();

					$pdf->SetFont('freesans','',8);

					$cell_width = floor(($pdf->GetPageWidth()-($left_margin*2))/7);
					$cell_height = 100;

					$i=0;
					$total_days = count($calendar_array)-1;
					$boader = 1;
					foreach( $calendar_array as $calendar ) {
						if ( $i == 0 ) {
							//Calendar Header
							$pdf->SetFont('freesans','B',8);
							$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );

							foreach( $calendar_header as $header_name ) {
								$pdf->Cell($cell_width,15,$header_name, 1, 0, 'C');
							}

							$pdf->Ln();
							unset($calendar_header, $header_name);
						}

						$month_name = NULL;
						if ( $i == 0 OR $calendar['isNewMonth'] == TRUE ) {
							$month_name = $calendar['month_name'];
						}

						if ( ($i > 0 AND $i % 7 == 0) ) {
							$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day);
							unset($week_date_stamps);
						}

						$pdf->SetFont('freesans','B',8);
						$pdf->Cell($cell_width/2, 15, $month_name, 'LT', 0, 'L');
						$pdf->Cell($cell_width/2, 15, $calendar['day_of_month'], 'RT', 0, 'R');

						$week_date_stamps[] = $calendar['date_stamp'];

						$i++;
					}

					$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day, TRUE);

					$valid_schedules++;

					unset($this->schedule_shifts, $calendar_array, $week_date_stamps, $max_week_data, $day_epoch, $day_schedule_shifts, $day_schedule_shift, $schedule_shift_totals);
				}
			}
			unset($raw_schedule_shifts);
		} else {
			$valid_schedules = 1;

			$sf = TTnew( 'ScheduleFactory' );
			$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );
			if ( is_array($raw_schedule_shifts) ) {
				foreach( $raw_schedule_shifts as $day_epoch => $day_schedule_shifts ) {
					foreach ( $day_schedule_shifts as $day_schedule_shift ) {
						//Debug::Arr($day_schedule_shift, 'bDay Schedule Shift: ', __FILE__, __LINE__, __METHOD__,10);
						$tmp_schedule_shifts[$day_epoch][$day_schedule_shift['branch']][$day_schedule_shift['department']][] = $day_schedule_shift;

						if ( isset($schedule_shift_totals[$day_epoch]['total_shifts']) ) {
							$schedule_shift_totals[$day_epoch]['total_shifts']++;
						} else {
							$schedule_shift_totals[$day_epoch]['total_shifts'] = 1;
						}

						//$week_of_year = TTDate::getWeek( strtotime($day_epoch) );
						$week_of_year = TTDate::getWeek( strtotime($day_epoch), $start_week_day );
						Debug::text(' Date: '. TTDate::getDate('DATE', strtotime($day_epoch)) .' Week: '. $week_of_year .' TMP: '. TTDate::getWeek( strtotime('20070721'), $start_week_day ), __FILE__, __LINE__, __METHOD__,10);
						if ( !isset($schedule_shift_totals[$day_epoch]['labels']) ) {
							$schedule_shift_totals[$day_epoch]['labels'] = 0;
						}
						if ( $day_schedule_shift['branch'] != '--'
								AND !isset($schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']]) ) {
							$schedule_shift_totals[$day_epoch]['branch'][$day_schedule_shift['branch']] = TRUE;
							$schedule_shift_totals[$day_epoch]['labels']++;
						}
						if ( $day_schedule_shift['department'] != '--'
								AND !isset($schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']]) ) {
							$schedule_shift_totals[$day_epoch]['department'][$day_schedule_shift['branch']][$day_schedule_shift['department']] = TRUE;
							$schedule_shift_totals[$day_epoch]['labels']++;
						}

						if ( !isset($max_week_data[$week_of_year]['shift']) ) {
							Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to 0', __FILE__, __LINE__, __METHOD__,10);
							$max_week_data[$week_of_year]['shift'] = 1;
							$max_week_data[$week_of_year]['labels'] = 0;
						}

						if ( isset($max_week_data[$week_of_year]['shift'])
								AND ($schedule_shift_totals[$day_epoch]['total_shifts']+$schedule_shift_totals[$day_epoch]['labels']) > ($max_week_data[$week_of_year]['shift']+$max_week_data[$week_of_year]['labels']) ) {
							Debug::text('Date: '. $day_epoch .' Week: '. $week_of_year .' Setting Max Week shift to: '.  $schedule_shift_totals[$day_epoch]['total_shifts'] .' Labels: '. $schedule_shift_totals[$day_epoch]['labels'], __FILE__, __LINE__, __METHOD__,10);
							$max_week_data[$week_of_year]['shift'] = $schedule_shift_totals[$day_epoch]['total_shifts'];
							$max_week_data[$week_of_year]['labels'] = $schedule_shift_totals[$day_epoch]['labels'];
						}
					}
				}
			}
			//print_r($tmp_schedule_shifts);
			//print_r($max_week_data);

			if ( isset($tmp_schedule_shifts) ) {
				//Sort Branches/Departments first
				foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
					ksort($day_tmp_schedule_shift);
					$tmp_schedule_shifts[$day_epoch] = $day_tmp_schedule_shift;

					foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
						ksort($tmp_schedule_shifts[$day_epoch][$branch]);
					}
				}

				//Sort each department by start time.
				foreach ( $tmp_schedule_shifts as $day_epoch => $day_tmp_schedule_shift ) {
					foreach ( $day_tmp_schedule_shift as $branch => $department_schedule_shifts ) {
						foreach ( $department_schedule_shifts as $department => $department_schedule_shift ) {
							$department_schedule_shift = Sort::multiSort( $department_schedule_shift, 'last_name' );
							$this->schedule_shifts[$day_epoch][$branch][$department] = $department_schedule_shift;
						}
					}
				}
			}
			//Debug::Arr($this->schedule_shifts, 'Schedule Shifts: ', __FILE__, __LINE__, __METHOD__,10);

			$calendar_array = TTDate::getCalendarArray($filter_data['start_date'], $filter_data['end_date'], $start_week_day );
			//var_dump($calendar_array);

			if ( !is_array($calendar_array) OR !isset($this->schedule_shifts) OR !is_array($this->schedule_shifts) ) {
				return FALSE;
			}

			$pdf->AddPage();

			$pdf->setXY( 670, $top_margin);
			$pdf->SetFont('freesans','',10);
			$pdf->Cell(100,15, TTDate::getDate('DATE+TIME', $current_epoch ), $border, 0, 'R');

			$pdf->setXY( $left_margin, $top_margin);

			$pdf->SetFont('freesans','B',25);
			$pdf->Cell(0,25,'Employee Schedule', $border, 0, 'C');
			$pdf->Ln();

			$pdf->SetFont('freesans','B',10);
			$pdf->Cell(0,15, TTDate::getDate('DATE', $filter_data['start_date']) .' - '. TTDate::getDate('DATE', $filter_data['end_date']), $border, 0, 'C');
			$pdf->Ln();
			$pdf->Ln();

			$pdf->SetFont('freesans','',8);

			$cell_width = floor(($pdf->GetPageWidth()-($left_margin*2))/7);
			$cell_height = 100;

			$i=0;
			$total_days = count($calendar_array)-1;
			$boader = 1;
			foreach( $calendar_array as $calendar ) {
				if ( $i == 0 ) {
					//Calendar Header
					$pdf->SetFont('freesans','B',8);
					$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );

					foreach( $calendar_header as $header_name ) {
						$pdf->Cell($cell_width,15,$header_name, 1, 0, 'C');
					}

					$pdf->Ln();
					unset($calendar_header, $header_name);
				}

				$month_name = NULL;
				if ( $i == 0 OR $calendar['isNewMonth'] == TRUE ) {
					$month_name = $calendar['month_name'];
				}

				if ( ($i > 0 AND $i % 7 == 0) ) {
					$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day);
					unset($week_date_stamps);
				}

				$pdf->SetFont('freesans','B',8);
				$pdf->Cell($cell_width/2, 15, $month_name, 'LT', 0, 'L');
				$pdf->Cell($cell_width/2, 15, $calendar['day_of_month'], 'RT', 0, 'R');

				$week_date_stamps[] = $calendar['date_stamp'];

				$i++;
			}

			$this->writeWeekSchedule( $pdf, $cell_width, $week_date_stamps, $max_week_data, $left_margin, $group_schedule, $start_week_day, TRUE);
		}

		if ( $valid_schedules > 0 ) {
			$output = $pdf->Output('','S');
			return $output;
		}

		return FALSE;
	}

	function Validate() {
		Debug::Text('User Date ID: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);

		$this->handleDayBoundary();

		//Check to make sure EnableOverwrite isn't enabled when editing an existing record.
		if ( $this->isNew() == FALSE AND $this->getEnableOverwrite() == TRUE ) {
			Debug::Text('Overwrite enabled when editing existing record, disabling overwrite.', __FILE__, __LINE__, __METHOD__,10);
			$this->setEnableOverwrite( FALSE );
		}

		if ( $this->getUserDateObject() == FALSE OR !is_object( $this->getUserDateObject() ) ) {
			Debug::Text('UserDateID is INVALID! ID: '. $this->getUserDateID(), __FILE__, __LINE__, __METHOD__,10);
			$this->Validator->isTrue(		'user_date',
											FALSE,
											('Date/Time is incorrect or pay period does not exist for this date. Please create a pay period schedule if you have not done so already'));
		}

		if ( is_object( $this->getUserDateObject() ) AND $this->getUserDateObject()->getPayPeriodObject()->getIsLocked() == TRUE ) {
			$this->Validator->isTrue(		'user_date',
											FALSE,
											('Pay Period is Currently Locked'));
		}

		//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
		if ( $this->getEnableOverwrite() == FALSE AND $this->getDeleted() == FALSE AND is_object( $this->getUserDateObject() ) ) {
			Debug::Text('User Date ID: '. $this->getUserDateID() .' User ID: '. $this->getUserDateObject()->getUser(), __FILE__, __LINE__, __METHOD__,10);
			//Make sure we're not conflicting with any other schedule shifts.
			$slf = TTnew( 'ScheduleListFactory' );
			$conflicting_schedule_shift_obj = $slf->getConflictingByUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), $this->getStartTime(), $this->getEndTime() );

			if ( is_object($conflicting_schedule_shift_obj) ) {
				$conflicting_schedule_shift_obj = $conflicting_schedule_shift_obj->getCurrent();

				if ( $conflicting_schedule_shift_obj->isNew() === FALSE
						AND $conflicting_schedule_shift_obj->getId() != $this->getId() ) {
					Debug::text('Conflicting Schedule Shift ID:'. $conflicting_schedule_shift_obj->getId() .' Schedule Shift ID: '. $this->getId() , __FILE__, __LINE__, __METHOD__, 10);
					$this->Validator->isTrue(		'start_time',
													FALSE,
													('Conflicting start time'));
				}
			}
		} else {
			Debug::text('Not checking for conflicts... UserDateObject: '. (int)is_object( $this->getUserDateObject() ) , __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function preSave() {
		$this->handleDayBoundary();

		if ( $this->getTotalTime() == FALSE ) {
			$this->setTotalTime( $this->calcTotalTime() );
		}

		if ( $this->getStatus() == 10 ) {
			$this->setAbsencePolicyID( NULL );
		} elseif ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Default to working.
		}

		if ( $this->getEnableOverwrite() == TRUE AND $this->isNew() == TRUE ) {
			//Delete any conflicting schedule shift before saving.
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getConflictingByUserIdAndStartDateAndEndDate( $this->getUserDateObject()->getUser(), $this->getStartTime(), $this->getEndTime() );
			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text('Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__,10);
				//Delete shifts.
				foreach( $slf as $s_obj ) {
					Debug::Text('Deleting Schedule Shift ID: '. $s_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$s_obj->setDeleted(TRUE);
					if ( $s_obj->isValid() ) {
						$s_obj->Save();
					}
				}
			} else {
				Debug::Text('NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return TRUE;
	}

	function postSave() {
		if ( $this->getEnableReCalculateDay() == TRUE ) {
			//Calculate total time. Mainly for docked.
			//Calculate entire week as Over Schedule (Weekly) OT policy needs to be reapplied if the schedule changes.
			UserDateTotalFactory::smartReCalculate( $this->getUserDateObject()->getUser(), $this->getUserDateID(), TRUE, FALSE );
			//UserDateTotalFactory::reCalculateDay( $this->getUserDateID(), TRUE, FALSE );
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {

			//We need to set the UserDate as soon as possible.
			if ( isset($data['user_id']) AND $data['user_id'] != ''
					AND isset($data['date_stamp']) AND $data['date_stamp'] != ''
					AND isset($data['start_time']) AND $data['start_time'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] .' Start Time: '. $data['start_time'] , __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'].' '.$data['start_time'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__,10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( isset($data['overwrite']) ) {
				$this->setEnableOverwrite( TRUE );
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'total_time': //If they try to specify total time, just skip it, as it gets calculated later anyways.
							break;
						case 'start_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..Setting start time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__,10);

								if ( isset($data['date_stamp']) AND $data['date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting start time... "'. $data['date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__,10);
									$this->$function( TTDate::parseDateTime( $data['date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto start_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									//When start_time is provided as a full timestamp. Happens with audit log detail.
									Debug::text(' aaSetting start time...: '. $data[$key], __FILE__, __LINE__, __METHOD__,10);
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' aaaSetting start time...: '. $this->getUserDateObject()->getDateStamp(), __FILE__, __LINE__, __METHOD__,10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting start time...', __FILE__, __LINE__, __METHOD__,10);
								}
							}
							break;
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..xSetting end time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__,10);

								if ( isset($data['date_stamp']) AND $data['date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting end time... "'. $data['date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__,10);
									$this->$function( TTDate::parseDateTime( $data['date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto end_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aaSetting end time...: '. $data[$key], __FILE__, __LINE__, __METHOD__,10);
									//When end_time is provided as a full timestamp. Happens with audit log detail.
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' bbbSetting end time... "'. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__,10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting end time...', __FILE__, __LINE__, __METHOD__,10);
								}
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setTotalTime( $this->calcTotalTime() ); //Calculate total time immediately after. This is required for proper audit logging too.
			$this->setEnableReCalculateDay(TRUE); //This is needed for Absence schedules to carry over to the timesheet.
			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'schedule_policy_id':
						case 'schedule_policy':
						case 'pay_period_id':
						case 'branch':
						case 'department':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', strtotime( $this->getColumn( 'date_stamp' ) ) );
							break;
						case 'start_date':
							//$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getStartTime() ); //Include both date+time
                                                    $data[$variable] =  $this->getStartTime() ;
							break;
						case 'end_date':
							//$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getEndTime() ); //Include both date+time
                                                    $data[$variable] =  $this->getEndTime() ;
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() ); //Just include time, so Mass Edit sees similar times without dates
							}
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
		return TTLog::addEntry( $this->getId(), $log_action, ('Schedule'), NULL, $this->getTable(), $this );
	}
}
?>