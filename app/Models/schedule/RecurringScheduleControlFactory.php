<?php

namespace App\Models\Schedule;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTLog;
use App\Models\Users\UserListFactory;

class RecurringScheduleControlFactory extends Factory {
	protected $table = 'recurring_schedule_control';
	protected $pk_sequence_name = 'recurring_schedule_control_id_seq'; //PK Sequence name

	protected $recurring_schedule_template_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-first_name' => ('First Name'),
										'-1020-last_name' => ('Last Name'),

										'-1030-recurring_schedule_template_control' => ('Template'),
										'-1040-recurring_schedule_template_control_description' => ('Description'),
										'-1050-start_date' => ('Start Date'),
										'-1060-end_date' => ('End Date'),
										'-1070-auto_fill' => ('Auto Pilot'),

										'-1090-title' => ('Title'),
										'-1099-user_group' => ('Group'),
										'-1100-default_branch' => ('Branch'),
										'-1110-default_department' => ('Department'),

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
								'first_name',
								'last_name',
								'recurring_schedule_template_control',
								'recurring_schedule_template_control_description',
								'start_date',
								'end_date',
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
										'company_id' => 'Company',
										'user_id' => FALSE,
										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',
										'recurring_schedule_template_control' => FALSE,
										'recurring_schedule_template_control_description' => FALSE,
										'start_week' => 'StartWeek',
										'start_date' => 'StartDate',
										'end_date' => 'EndDate',
										'auto_fill' => 'AutoFill',
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory(); 

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
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

		if ( $this->Validator->isResultSetWithRows(	'recurring_schedule_template_control_id',
													$rstclf->getByID($id),
													('Recurring Schedule Template is invalid')
													) ) {

			$this->data['recurring_schedule_template_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStartWeek() {
		if ( isset($this->data['start_week']) ) {
			return (int)$this->data['start_week'];
		}

		return FALSE;
	}
	function setStartWeek($int) {
		$int = trim($int);

		if 	(	$int > 0
				AND
				$this->Validator->isNumeric(		'week',
													$int,
													('Week is invalid')) ) {
			$this->data['start_week'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getStartDate( $raw = FALSE ) {
		if ( isset($this->data['start_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['start_date'];
			} else {
				return TTDate::strtotime( $this->data['start_date'] );
			}
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'start_date',
												$epoch,
												('Incorrect start date'))
			) {

			$this->data['start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndDate( $raw = FALSE ) {
		if ( isset($this->data['end_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['end_date'];
			} else {
				return TTDate::strtotime( $this->data['end_date'] );
			}
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		$epoch = trim($epoch);

		if ( $epoch == '' ){
			$epoch = NULL;
		}

		if 	(	$epoch == NULL
				OR
				$this->Validator->isDate(		'end_date',
												$epoch,
												('Incorrect end date'))
			) {

			$this->data['end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getAutoFill() {
		if ( isset($this->data['auto_fill']) ) {
			return $this->fromBool( $this->data['auto_fill'] );
		}

		return FALSE;
	}
	function setAutoFill($bool) {
		$this->data['auto_fill'] = $this->toBool($bool);

		return true;
	}

	function getUser() {
		$rsulf = new RecurringScheduleUserListFactory();
		$rsulf->getByRecurringScheduleControlId( $this->getId() );
		foreach ($rsulf->rs as $obj) {
			$rsulf->data = (array)$obj;
			$obj = $rsulf;
			$list[] = $obj->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$rsulf = new RecurringScheduleUserListFactory();
				$rsulf->getByRecurringScheduleControlId( $this->getId() );

				$tmp_ids = array();
				foreach ($rsulf->rs as $obj) {
					$rsulf->data = (array)$obj;
					$obj = $rsulf;
					$id = $obj->getUser();
					Debug::text('Recurring Schedule ID: '. $obj->getRecurringScheduleControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$ulf = new UserListFactory(); 

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						$rsuf = new RecurringScheduleUserFactory();
						$rsuf->setRecurringScheduleControl( $this->getId() );
						$rsuf->setUser( $id );

						if ( $this->Validator->isTrue(		'user',
															$rsuf->Validator->isValid(),
															('Selected Employee is invalid').' ('. $obj->getFullName() .')' )) {
							$rsuf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function reMapWeek( $current, $start, $max ) {
		 return ((($current-1)+$max-($start-1))%$max) + 1;
	}

	function ReMapWeeks($week_arr) {
		//We should be able to re-map weeks with simple math:
		//For example:
		//  Start Week = 3, Max Week = 5
		// If template week is less then start week, we add the start week.
		// If template week is greater or equal then start week, we minus the 1-start_week.
		//  Template Week 1 -- 1 + 3(start week)   = ReMapped Week 4
		//  Template Week 2 -- 2 + 3               = ReMapped Week 5
		//  Template Week 3 -- 3 - 2(start week-1) = ReMapped Week 1
		//  Template Week 4 -- 4 - 2               = ReMapped Week 2
		//  Template Week 5 -- 5 - 2               = ReMapped Week 3

		//Remaps weeks based on start week
		Debug::text('Start Week: '.  $this->getStartWeek(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getStartWeek() > 1 AND in_array( $this->getStartWeek(), $week_arr) ) {
			Debug::text('Weeks DO need reordering: ', __FILE__, __LINE__, __METHOD__, 10);
			$max_week = count($week_arr);

			$i=1;
			foreach( $week_arr as $key => $val ) {
				$new_val = $key - ($this->getStartWeek()-1);

				if ( $key < $this->getStartWeek() ) {
					$new_val = $new_val + $max_week;
				}

				$arr[$new_val] = $key;

				$i++;
			}
			//var_dump($arr);
			return $arr;
		}

		Debug::text('Weeks do not need reordering: ', __FILE__, __LINE__, __METHOD__, 10);

		return $week_arr;
	}

	//Used when taking recurring schedules and committing shifts for them in maintenance jobs.
	function getShiftsByStartDateAndEndDate($start_date, $end_date) {

		//Make sure timezone isn't in the time format. Because recurring schedules
		//are timezone agnostic. 7:00AM in PST is also 7:00AM in EST.
		//This causes an issue where the previous users timezone carries over to the next
		//users timezone, causing errors.
		//TTDate::setTimeFormat('g:i A');

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $start_date < $this->getStartDate() ) {
			$start_date = $this->getStartDate();
		}

		if ( $this->getEndDate(TRUE) != NULL AND $end_date > $this->getEndDate() ) {
			$end_date = $this->getEndDate();
		}
		Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .'('.$start_date.') End Date: '. TTDate::getDate('DATE+TIME', $end_date) .'('.$end_date.')', __FILE__, __LINE__, __METHOD__, 10);

		//Get week data
		$rstlf = new RecurringScheduleTemplateListFactory();
		$rstlf->getByRecurringScheduleTemplateControlId( $this->getRecurringScheduleTemplateControl() )->getCurrent();
		$max_week = 1;
		$weeks = array();
		if ( $rstlf->getRecordCount() > 0 ) {
			foreach($rstlf->rs as $rst_obj) {
				$rstlf->data = (array)$rst_obj;
				$rst_obj = $rstlf;
				//Debug::text('Week: '. $rst_obj->getWeek(), __FILE__, __LINE__, __METHOD__, 10);
				$template_week_rows[$rst_obj->getWeek()][] = $rst_obj->getObjectAsArray();

				$weeks[$rst_obj->getWeek()] = $rst_obj->getWeek();

				if ( $rst_obj->getWeek() > $max_week ) {
					$max_week = $rst_obj->getWeek();
				}
			}
		}

		$weeks = $this->ReMapWeeks( $weeks );

		//Get week of start_date
		$start_date_week = TTDate::getBeginWeekEpoch( $this->getStartDate(), 0 ); //Start week on Sunday to match Recurring Schedule.
		//Debug::text('Week of Start Date: '. $start_date_week .' Date: '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) ,__FILE__, __LINE__, __METHOD__, 10);

		//Since we add 43200 to each iteration (even though its removed right after), we need to add 43200 to the end_date as well so we loop the
		//proper amount of times, otherwise schedules may be added too late.
		for ( $i=$start_date; $i <= ($end_date+43200); $i+=(86400+43200)) {
			//Handle DST by adding 12hrs to the date to get the mid-day epoch, then forcing it back to the beginning of the day.
			$i = TTDate::getBeginDayEpoch( $i );

			//This needs to take into account weeks spanning January 1st of each year. Where the week goes from 53 to 1.
			//Rather then use the week of the year, calculate the weeks between the recurring schedule start date and now.
			$current_week = round( ( TTDate::getBeginWeekEpoch( $i, 0 ) - $start_date_week ) / ( 604800 ) ); //Find out which week we are on based on the recurring schedule start date. Use round due to DST the week might be 6.9 or 7.1, so we need to round to the nearest full week.
			//Debug::text('I: '. $i .' User ID: '. $this->getColumn('user_id') .' Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week .' Start Week: '. $start_date_week,__FILE__, __LINE__, __METHOD__, 10);

			$template_week = ( $current_week % $max_week ) + 1;
			//Debug::text('Template Week: '. $template_week .' Max Week: '. $max_week,__FILE__, __LINE__, __METHOD__, 10);

			$day_of_week = strtolower( date('D', $i) );
			//Debug::text('Day Of Week: '. $day_of_week,__FILE__, __LINE__, __METHOD__, 10);

			if ( isset($weeks[$template_week] ) ) {
				$mapped_template_week = $weeks[$template_week];
				//Debug::text('&nbsp;&nbsp;Mapped Template Week: '. $mapped_template_week,__FILE__, __LINE__, __METHOD__, 10);

				if ( isset($template_week_rows[$mapped_template_week]) ) {
					//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Starting Looping...!',__FILE__, __LINE__, __METHOD__, 10);

					foreach( $template_week_rows[$mapped_template_week] as $template_week_arr ) {
						if ( $template_week_arr['days'][$day_of_week] == TRUE ) {
							//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Found Scheduled Time: Start Time: '. TTDate::getDate('DATE+TIME', TTDate::getTimeLockedDate( $template_week_arr['start_time'], $i ) ),__FILE__, __LINE__, __METHOD__, 10);

							$start_time = TTDate::getTimeLockedDate( $template_week_arr['raw_start_time'], $i );
							$end_time = TTDate::getTimeLockedDate( $template_week_arr['raw_end_time'], $i );
							if ( $end_time < $start_time ) {
								//Spans the day boundary, add 86400 to end_time
								$end_time = $end_time + 86400;
								//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Schedule spans day boundary, bumping endtime to next day: ',__FILE__, __LINE__, __METHOD__, 10);
							}
							//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Date: '. TTDate::getDate('DATE+TIME', $start_time) .' End Date: '. TTDate::getDate('DATE+TIME', $end_time),__FILE__, __LINE__, __METHOD__, 10);

							//$shifts[TTDate::getBeginDayEpoch($i)][] = array(
							$shifts[TTDate::getISODateStamp($i)][] = array(
																'status_id' => $template_week_arr['status_id'],
																'start_time' => $start_time,
																'raw_start_time' => TTDate::getDate('DATE+TIME', $start_time ),
																'end_time' => $end_time,
																'raw_end_time' => TTDate::getDate('DATE+TIME', $end_time ),
																'total_time' => $template_week_arr['total_time'],
																'schedule_policy_id' => $template_week_arr['schedule_policy_id'],
																'branch_id' => $template_week_arr['branch_id'],
																'department_id' => $template_week_arr['department_id'],
																'job_id' => $template_week_arr['job_id'],
																'job_item_id' => $template_week_arr['job_item_id']
																);
							unset($start_time, $end_time);
						} else {
							//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;aSkipping!',__FILE__, __LINE__, __METHOD__, 10);
						}
					}

				} else {
					//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;bSkipping!',__FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				//Debug::text('&nbsp;&nbsp;cSkipping!',__FILE__, __LINE__, __METHOD__, 10);
			}
		}

		//var_dump($shifts);
		if ( isset($shifts) ) {
			return $shifts;
		}

		return FALSE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'start_date':
						case 'end_date':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
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
		//
		//When using the Recurring Schedule view, it returns the user list for every single row and runs out of memory at about 1000 rows.
		//Need to make the 'user' column explicitly defined instead perhaps?
		//
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'recurring_schedule_template_control':
						case 'recurring_schedule_template_control_description':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->$function() ) );
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Recurring Schedule'), NULL, $this->getTable(), $this );
	}
}
?>
