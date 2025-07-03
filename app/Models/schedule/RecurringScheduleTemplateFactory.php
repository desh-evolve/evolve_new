<?php

namespace App\Models\Schedule;

use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTLog;
use App\Models\Department\DepartmentListFactory;
use App\Models\Policy\AbsencePolicyFactory;
use App\Models\Policy\SchedulePolicyListFactory;

class RecurringScheduleTemplateFactory extends Factory {
	protected $table = 'recurring_schedule_template';
	protected $pk_sequence_name = 'recurring_schedule_template_id_seq'; //PK Sequence name

	protected $schedule_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-week' => ('Week'),

										'-1101-sun' => ('S'),
										'-1102-mon' => ('M'),
										'-1103-tue' => ('T'),
										'-1104-wed' => ('W'),
										'-1105-thu' => ('T'),
										'-1106-fri' => ('F'),
										'-1107-sat' => ('S'),

										'-1200-start_time' => ('In'),
										'-1210-end_time' => ('Out'),

										'-1220-schedule_policy' => ('Schedule Policy'),

										'-1230-branch' => ('Branch'),
										'-1240-department' => ('Department'),
										'-1250-job' => ('Job'),
										'-1260-job_item' => ('Task'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'week',
								'sun',
								'mon',
								'tue',
								'wed',
								'thu',
								'fri',
								'sat',
								'start_time',
								'end_time',
								'schedule_policy',
								'branch',
								'department',
								'job',
								'job_item',
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
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'status_id' => FALSE,
										'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',
										'week' => 'Week',
										'sun' => 'Sun',
										'mon' => 'Mon',
										'tue' => 'Tue',
										'wed' => 'Wed',
										'thu' => 'Thu',
										'fri' => 'Fri',
										'sat' => 'Sat',
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'total_time' => 'TotalTime',
										'schedule_policy_id' => 'SchedulePolicyID',
										'branch_id' => 'Branch',
										'department_id' => 'Department',
										'job_id' => 'Job',
										'job_item_id' => 'JobItem',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getSchedulePolicyObject() {
		if ( is_object($this->schedule_policy_obj) ) {
			return $this->schedule_policy_obj;
		} else {
			$splf = new SchedulePolicyListFactory();
			$splf->getById( $this->getSchedulePolicyID() );
			if ( $splf->getRecordCount() > 0 ) {
				$this->schedule_policy_obj = $splf->getCurrent();
				return $this->schedule_policy_obj;
			}

			return FALSE;
		}
	}

	function getRecurringScheduleTemplateControl() {
		if ( isset($this->data['recurring_schedule_template_control_id']) ) {
			return $this->data['recurring_schedule_template_control_id'];
		}

		return FALSE;
	}
	function setRecurringScheduleTemplateControl($id) {
		$id = trim($id);

		$rstclf = new RecurringScheduleTemplateControlListFactory();

		if ( $this->Validator->isResultSetWithRows(	'recurring_schedule_template_control',
													$rstclf->getByID($id),
													('Recurring Schedule Template Control is invalid')
													) ) {

			$this->data['recurring_schedule_template_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getWeek() {
		if ( isset($this->data['week']) ) {
			return (int)$this->data['week'];
		}

		return FALSE;
	}
	function setWeek($int) {
		$int = trim($int);

		if 	(	$int > 0
				AND
				$this->Validator->isNumeric(		'week'.$this->getLabelID(),
													$int,
													('Week is invalid')) ) {
			$this->data['week'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getSun() {
		if ( isset($this->data['sun']) ) {
			return $this->fromBool( $this->data['sun'] );
		}

		return FALSE;
	}
	function setSun($bool) {
		$this->data['sun'] = $this->toBool($bool);

		return TRUE;
	}

	function getMon() {
		if ( isset($this->data['mon']) ) {
			return $this->fromBool( $this->data['mon'] );
		}

		return FALSE;
	}
	function setMon($bool) {
		$this->data['mon'] = $this->toBool($bool);

		return TRUE;
	}
	function getTue() {
		if ( isset($this->data['tue']) ) {
			return $this->fromBool( $this->data['tue'] );
		}

		return FALSE;
	}
	function setTue($bool) {
		$this->data['tue'] = $this->toBool($bool);

		return TRUE;
	}
	function getWed() {
		if ( isset($this->data['wed']) ) {
			return $this->fromBool( $this->data['wed'] );
		}

		return FALSE;
	}
	function setWed($bool) {
		$this->data['wed'] = $this->toBool($bool);

		return TRUE;
	}
	function getThu() {
		if ( isset($this->data['thu']) ) {
			return $this->fromBool( $this->data['thu'] );
		}

		return FALSE;
	}
	function setThu($bool) {
		$this->data['thu'] = $this->toBool($bool);

		return TRUE;
	}
	function getFri() {
		if ( isset($this->data['fri']) ) {
			return $this->fromBool( $this->data['fri'] );
		}

		return FALSE;
	}
	function setFri($bool) {
		$this->data['fri'] = $this->toBool($bool);

		return TRUE;
	}
	function getSat() {
		if ( isset($this->data['sat']) ) {
			return $this->fromBool( $this->data['sat'] );
		}

		return FALSE;
	}
	function setSat($bool) {
		$this->data['sat'] = $this->toBool($bool);

		return TRUE;
	}

	function getStartTime( $raw = FALSE ) {
		if ( isset($this->data['start_time']) ) {
			if ( $raw === TRUE) {
				return $this->data['start_time'];
			} else {
				return TTDate::strtotime( $this->data['start_time'] );
			}
		}

		return FALSE;
	}
    
	function setStartTime($epoch) {
		$epoch = trim($epoch);

		Debug::Text('Start Time: '. $epoch, __FILE__, __LINE__, __METHOD__,10);

		if 	(	$this->Validator->isDate(		'start_time'.$this->getLabelID(),
												$epoch,
												('Incorrect In time'))

			) {

			$this->data['start_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndTime( $raw = FALSE ) {
		if ( isset($this->data['end_time']) ) {
			if ( $raw === TRUE) {
				return $this->data['end_time'];
			} else {
				return TTDate::strtotime( $this->data['end_time'] );
			}
		}

		return FALSE;
	}
	function setEndTime($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'end_time'.$this->getLabelID(),
												$epoch,
												('Incorrect Out time'))

			) {

			$this->data['end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTime() {
		if ( $this->getSchedulePolicyObject() != FALSE ) {
			if ( $this->getSchedulePolicyObject()->getMealPolicyObject() != FALSE ) {
				if ( $this->getSchedulePolicyObject()->getMealPolicyObject()->getType() == 10
						OR $this->getSchedulePolicyObject()->getMealPolicyObject()->getType() == 20 ) {
					$total_time = ( $this->getEndTime() - $this->getStartTime() );

					if ( $total_time > $this->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime() ) {
						$total_time -= $this->getSchedulePolicyObject()->getMealPolicyObject()->getAmount();
					}

					return $total_time;
				}
			}
		}

		$total_time = ( $this->getEndTime() - $this->getStartTime() );

		return $total_time;
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

		$splf = new SchedulePolicyListFactory();

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

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return $this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		$blf = new BranchListFactory();

		Debug::text('Branch ID: '. $id ,__FILE__, __LINE__, __METHOD__, 10);

		//-1 is for user default branch.
		if (  $id == 0 OR $id == -1
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

		$dlf = new DepartmentListFactory();

		//-1 is for user default department.
		if (  $id == 0 OR $id == -1
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

		if (  $id == NULL
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

		if (  $id == NULL
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

	function isActiveShiftDay( $epoch ) {
		$day_of_week = strtolower( date('D', $epoch) );
		if ( isset( $this->data[$day_of_week] ) ) {
			return $this->fromBool( $this->data[$day_of_week] );
		}

		return FALSE;
	}

	function getShifts( $start_date, $end_date, &$holiday_data = array(), &$branch_options = array(), &$department_options = array(), &$shifts = array(), &$shifts_index = array() ) {
		//Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

		$recurring_schedule_control_start_date = TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') );
		//Debug::text('Recurring Schedule Control Start Date: '. TTDate::getDate('DATE+TIME', $recurring_schedule_control_start_date),__FILE__, __LINE__, __METHOD__, 10);

		$current_template_week = $this->getColumn('remapped_week');
		$max_week = $this->getColumn('max_week');
		//Debug::text('Template Week: '. $current_template_week .' Max Week: '. $this->getColumn('max_week') .' ReMapped Week: '. $this->getColumn('remapped_week') ,__FILE__, __LINE__, __METHOD__, 10);

		if ( $recurring_schedule_control_start_date == ''  ) {
			return FALSE;
		}

		//Get week of start_date
		$start_date_week = TTDate::getBeginWeekEpoch( $recurring_schedule_control_start_date, 0 ); //Start week on Sunday to match Recurring Schedule.
		//Debug::text('Week of Start Date: '. $start_date_week ,__FILE__, __LINE__, __METHOD__, 10);

		$apf = new AbsencePolicyFactory();
		$absence_policy_paid_type_options = $apf->getOptions('paid_type');

		for ( $i=$start_date; $i <= $end_date; $i+=(86400+43200)) {
			//Handle DST by adding 12hrs to the date to get the mid-day epoch, then forcing it back to the beginning of the day.
			$i = TTDate::getBeginDayEpoch( $i );

			if ( ( $this->getColumn('hire_date') != '' AND $i <= $this->getColumn('hire_date') )
					OR ( $this->getColumn('termination_date') != '' AND $i > $this->getColumn('termination_date') )
					) {
				//Debug::text('Skipping due to Hire/Termination date: User ID: '. $this->getColumn('user_id') .' I: '. $i .' Hire Date: '. $this->getColumn('hire_date') .' Termination Date: '. $this->getColumn('termination_date') ,__FILE__, __LINE__, __METHOD__, 10);
				continue;
			}

			//This needs to take into account weeks spanning January 1st of each year. Where the week goes from 53 to 1.
			//Rather then use the week of the year, calculate the weeks between the recurring schedule start date and now.
			$current_week = round( ( TTDate::getBeginWeekEpoch( $i, 0 ) - $start_date_week ) / ( 604800 ) ); //Find out which week we are on based on the recurring schedule start date. Use round due to DST the week might be 6.9 or 7.1, so we need to round to the nearest full week.
			//Debug::text('I: '. $i .' User ID: '. $this->getColumn('user_id') .' Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week .' Start Week: '. $start_date_week,__FILE__, __LINE__, __METHOD__, 10);

			$template_week = ( $current_week % $max_week ) + 1;
			//Debug::text('Template Week: '. $template_week .' Max Week: '. $max_week,__FILE__, __LINE__, __METHOD__, 10);

			if ( $template_week == $current_template_week ) {
				//Debug::text('Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week,__FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('&nbsp;Template Week: '. $template_week .' Max Week: '. $max_week,__FILE__, __LINE__, __METHOD__, 10);

				if ( $this->isActiveShiftDay( $i ) ) {
					//Debug::text('&nbsp;&nbsp;Active Shift on this day...',__FILE__, __LINE__, __METHOD__, 10);
					$iso_date_stamp = TTDate::getISODateStamp( $i );

					$start_time = TTDate::getTimeLockedDate( $this->getStartTime(), $i );
					$end_time = TTDate::getTimeLockedDate( $this->getEndTime(), $i );
					if ( $end_time < $start_time ) {
						//Spans the day boundary, add 86400 to end_time
						$end_time = $end_time + 86400;
						//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Schedule spans day boundary, bumping endtime to next day: ',__FILE__, __LINE__, __METHOD__, 10);
					}

					if ( isset($shifts_index[$iso_date_stamp][$this->getColumn('user_id')]) ) {
						//User has previous recurring schedule shifts, check for overlap.
						//Loop over each employees shift for this day and check for conflicts
						foreach( $shifts_index[$iso_date_stamp][$this->getColumn('user_id')] as $shift_key ) {
							if ( isset($shifts[$iso_date_stamp][$shift_key]) ) {
								//Must use parseDateTime() when called from the API due to date formats that strtotime() fails on.
								if ( TTDate::isTimeOverLap( ( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['start_date']) : $shifts[$iso_date_stamp][$shift_key]['start_date'], ( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['end_date']) : $shifts[$iso_date_stamp][$shift_key]['end_date'], $start_time, $end_time ) == TRUE ) {
									//Debug::text('&nbsp;&nbsp;Found overlapping recurring schedules! User ID: '. $this->getColumn('user_id') .' Start Time: '. $start_time,__FILE__, __LINE__, __METHOD__, 10);
									continue 2;
								}
							}
						}
						unset($shift_key);
					}

					//This check has to occurr after the committed schedule check, otherwise no committed schedules will appear.
					if ( ( $this->getColumn('recurring_schedule_control_start_date') != '' AND $i < TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') ) )
							OR ( $this->getColumn('recurring_schedule_control_end_date') != '' AND $i > TTDate::strtotime( $this->getColumn('recurring_schedule_control_end_date') ) ) ) {
						//Debug::text('Skipping due to Recurring Schedule Start/End date: ID: '. $this->getColumn('id') .' User ID: '. $this->getColumn('user_id') .' I: '. $i .' Start Date: '. $this->getColumn('recurring_schedule_control_start_date') .' ('. TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') ) .') End Date: '. $this->getColumn('recurring_schedule_control_end_date') ,__FILE__, __LINE__, __METHOD__, 10);
						continue;
					}

					//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Date: '. TTDate::getDate('DATE+TIME', $start_time) .' End Date: '. TTDate::getDate('DATE+TIME', $end_time),__FILE__, __LINE__, __METHOD__, 10);

					$status_id = 10; //Working
					$absence_policy_id = FALSE;
					$absence_policy_type_id = FALSE;
					$absence_policy = NULL;

					if ( isset($holiday_data[$iso_date_stamp]) ) {
						//We have to assume they are eligible, because we really won't know
						//if they will have worked enough days or not. We could assume they
						//work whatever their schedule is, but chances are they will be eligible then anyways.
						//Debug::text('&nbsp;&nbsp;Found Holiday on this day...',__FILE__, __LINE__, __METHOD__, 10);
						$status_id = $holiday_data[$iso_date_stamp]['status_id'];
						if ( isset($holiday_data[$iso_date_stamp]['absence_policy_id']) ) {
							$absence_policy_id = $holiday_data[$iso_date_stamp]['absence_policy_id'];
							$absence_policy_type_id = $holiday_data[$iso_date_stamp]['type_id'];
							$absence_policy = $holiday_data[$iso_date_stamp]['absence_policy'];
						}
					}

					$hourly_rate = Misc::MoneyFormat( $this->getColumn('user_wage_hourly_rate'), FALSE );

					if ( $absence_policy_id > 0
							AND in_array( $absence_policy_type_id, $absence_policy_paid_type_options ) == FALSE ) {
						//UnPaid Absence.
						$total_time_wage = Misc::MoneyFormat(0);
					} else {
						$total_time_wage = Misc::MoneyFormat( bcmul( TTDate::getHours( $this->getTotalTime() ), $hourly_rate ), FALSE );
					}

					//Debug::text('I: '. $i .' User ID: '. $this->getColumn('user_id') .' Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week .' Start Time: '. TTDate::getDate('DATE+TIME', $start_time ),__FILE__, __LINE__, __METHOD__, 10);
					$shifts[$iso_date_stamp][$this->getColumn('user_id').$start_time] = array(
														'pay_period_id' => FALSE,
														'user_id' => $this->getColumn('user_id'),
														'user_created_by' => $this->getColumn('user_created_by'),
														'user_full_name' => Misc::getFullName( $this->getColumn('first_name'), NULL, $this->getColumn('last_name'), FALSE, FALSE ),
														'first_name' => $this->getColumn('first_name'),
														'last_name' => $this->getColumn('last_name'),
														'title_id' => $this->getColumn('title_id'),
														'title' => $this->getColumn('title'),
														'group_id' => $this->getColumn('group_id'),
														'group' => $this->getColumn('group'),
														'default_branch_id' => $this->getColumn('default_branch_id'),
														'default_branch' => $this->getColumn('default_branch'),
														//'default_branch' => Option::getByKey($this->getColumn('default_branch_id'), $branch_options, NULL ),
														'default_department_id' => $this->getColumn('default_department_id'),
														'default_department' => $this->getColumn('default_department'),
														//'default_department' =>  Option::getByKey($this->getColumn('default_department_id'), $department_options, NULL ),

														'job_id' => $this->getJob(),
														'job' => $this->getColumn('job'),
														'job_status_id' => $this->getColumn('job_status_id'),
														'job_manual_id' => $this->getColumn('job_manual_id'),
														'job_branch_id' => $this->getColumn('job_branch_id'),
														'job_department_id' => $this->getColumn('job_department_id'),
														'job_group_id' => $this->getColumn('job_group_id'),
														'job_item_id' => $this->getJobItem(),
														'job_item' => $this->getColumn('job_item'),

														'type_id' => 20, //Recurring
														'status_id' => $status_id,

														'date_stamp' => TTDate::getAPIDate('DATE', $start_time ),
														'start_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $start_time ) : $start_time,
														'end_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $end_time ) : $end_time,
														'start_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $start_time ) : $start_time,
														'end_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $end_time ) : $end_time,

														//These are no longer used.
														//'raw_start_time' => TTDate::getDate('DATE+TIME', $start_time ),
														//'raw_end_time' => TTDate::getDate('DATE+TIME', $end_time ),

														'total_time' => $this->getTotalTime(),

														'hourly_rate' => $hourly_rate,
														'total_time_wage' => $total_time_wage,

														'schedule_policy_id' => $this->getSchedulePolicyID(),
														'absence_policy_id' => $absence_policy_id,
														'absence_policy' => $absence_policy,
														'branch_id' => $this->getColumn('schedule_branch_id'),
														'branch' => $this->getColumn('schedule_branch'),
														//'branch' => Option::getByKey($this->getColumn('schedule_branch_id'), $branch_options, NULL ),
														'department_id' => $this->getColumn('schedule_department_id'),
														'department' => $this->getColumn('schedule_department'),
														//'department' =>  Option::getByKey($this->getColumn('schedule_department_id'), $department_options, NULL ),

														'created_by_id' => $this->getColumn('recurring_schedule_control_created_by'), //Whoever created the recurring schedule control object is consider the owner.
														);
					$shifts_index[$iso_date_stamp][$this->getColumn('user_id')][] = $this->getColumn('user_id').$start_time;

					unset($start_time, $end_time);
				} else {
					//Debug::text('&nbsp;&nbsp;NOT active shift on this day... ID: '. $this->getColumn('id') .' User ID: '. $this->getColumn('user_id') .' Start Time: '. TTDate::getDate('DATE+TIME', $i),__FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		if ( isset($shifts) ) {
			//Debug::Arr($shifts, 'Template Shifts: ',__FILE__, __LINE__, __METHOD__, 10);
			return $shifts;
		}

		return FALSE;
	}
/*
	function getObjectAsArray( $raw_dates = FALSE ) {
		//array( 'date_stamp' => array( 0 => ALLDATA 1=> ALLDATA ) );

		//Calculate offset for day of the week
		$data = array(
						'week' => $this->getWeek(),
						'days' => array(
										'sun' => $this->getSun(),
										'mon' => $this->getMon(),
										'tue' => $this->getTue(),
										'wed' => $this->getWed(),
										'thu' => $this->getThu(),
										'fri' => $this->getFri(),
										'sat' => $this->getSat(),
										),
						'status_id' => 10, //Working
						'start_time' => $this->getStartTime( $raw_dates ), //Get Raw value
						'end_time' => $this->getEndTime( $raw_dates ),
						'total_time' => $this->getTotalTime(),
						'schedule_policy_id' => $this->getSchedulePolicyID(),
						'branch_id' => $this->getBranch(),
						'department_id' => $this->getDepartment(),
						'job_id' => $this->getJob(),
						'job_item_id' => $this->getJobItem(),
					);

		return $data;
	}
*/
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'start_time':
						case 'end_time':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'total_time': //Ignore this field when setting data.
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status_id':
							$data[$variable] = 10; //Working: Always working when its a recurring schedule.
							break;
						case 'start_time':
						case 'end_time':
							//$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
							$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
							//Need to include the raw_start_time,raw_end_time columns that are in EPOCH format so getShiftsByStartDateAndEndDate() can convert them as needed.
							$data['raw_'.$variable] = $this->$function();
							break;
						case 'sun':
						case 'mon':
						case 'tue':
						case 'wed':
						case 'thu':
						case 'fri':
						case 'sat':
							//For backwards compatibility, put all days inside the "days" array, AS WELL as in their own column for the API to use.
							if ( method_exists( $this, $function ) ) {
								$data['days'][$variable] = $this->$function();
							}
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function preSave() {
		if ( $this->getEndTime() < $this->getStartTime() ) {
			Debug::Text('EndTime spans midnight boundary! Increase by 24hrs ', __FILE__, __LINE__, __METHOD__,10);
			$this->setEndTime( $this->getEndTime() + 86400 ); //End time spans midnight, add 24hrs.
		}
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getRecurringScheduleTemplateControl(), $log_action, ('Recurring Schedule Week').': '. $this->getWeek(), NULL, $this->getTable(), $this );
	}
}
?>
