<?php

namespace App\Models\Holiday;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class HolidayFactory extends Factory {
	protected $table = 'holidays';
	protected $pk_sequence_name = 'holidays_id_seq'; //PK Sequence name

	protected $holiday_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-name' => ('Name'),
										'-1020-date_stamp' => ('Date'),

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
								'name',
								'date_stamp',
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
										'holiday_policy_id' => 'HolidayPolicyID',
										'date_stamp' => 'DateStamp',
										'name' => 'Name',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getHolidayPolicyObject() {
		if ( is_object($this->holiday_policy_obj) ) {
			return $this->holiday_policy_obj;
		} else {

			$hplf = new HolidayPolicyListFactory();
			$hplf->getById( $this->getHolidayPolicyID() );

			if ( $hplf->getRecordCount() == 1 ) {
				$this->holiday_policy_obj = $hplf->getCurrent();

				return $this->holiday_policy_obj;
			}

			return FALSE;
		}
	}

        function getID() {
		if ( isset($this->data['id']) ) {
			return $this->data['id'];
		}

		return FALSE;
	}
	function getHolidayPolicyID() {
		if ( isset($this->data['holiday_policy_id']) ) {
			return $this->data['holiday_policy_id'];
		}

		return FALSE;
	}
	function setHolidayPolicyID($id) {
		$id = trim($id);

		$hplf = new HolidayPolicyListFactory();

		if (
				$this->Validator->isResultSetWithRows(	'holiday_policy',
													$hplf->getByID($id),
													('Holiday Policy is invalid')
													) ) {

			$this->data['holiday_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueDateStamp($date_stamp) {
		$ph = array(
					'policy_id' => $this->getHolidayPolicyID(),
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		$query = 'select id from '. $this->getTable() .'
					where holiday_policy_id = ?
						AND date_stamp = ?
						AND deleted=0';
		$date_stamp_id = $this->db->GetOne($query, $ph);
		Debug::Arr($date_stamp_id,'Unique Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__,10);

		if ( $date_stamp_id === FALSE ) {
			return TRUE;
		} else {
			if ($date_stamp_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												('Incorrect date'))
					AND
						$this->Validator->isTrue(		'date_stamp',
														$this->isUniqueDateStamp($epoch),
														('Date is already in use by another Holiday'))

			) {

			if 	( $epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												('Incorrect date'));
			}
		}

		return FALSE;
	}

	function isUniqueName($name) {
		//BindDate() causes a deprecated error if date_stamp is not set, so just return TRUE so we can throw a invalid date error elsewhere instead.
		//This also causes it so we can never have a invalid date and invalid name validation errors at the same time.
		if ( $this->getDateStamp() == '' ) {
			return TRUE;
		}

		//When a holiday gets moved back/forward due to falling on weekend, it can throw off the check to see if the holiday
		//appears in the same year. For example new years 01-Jan-2011 gets moved to 31-Dec-2010, its in the same year
		//as the previous New Years day or 01-Jan-2010, so this check fails.
		//
		//I think this can only happen with New Years, or other holidays that fall within two days of the new year.
		//So exclude the first three days of the year to allow for weekend adjustments.
		$ph = array(
					'policy_id' => $this->getHolidayPolicyID(),
					'name' => $name,
					'start_date1' => $this->db->BindDate( TTDate::getBeginYearEpoch( $this->getDateStamp() )+(86400*3) ),
					'end_date1' => $this->db->BindDate( TTDate::getEndYearEpoch( $this->getDateStamp() ) ),
					'start_date2' => $this->db->BindDate( $this->getDateStamp()-(86400*15) ),
					'end_date2' => $this->db->BindDate( $this->getDateStamp()+(86400*15) ),
					);

		$query = 'select id from '. $this->getTable() .'
					where holiday_policy_id = ?
						AND name = ?
						AND
							(
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							OR
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							)
						AND deleted=0';
		$name_id = DB::select($query, $ph);

		if ($name_id === FALSE ) {
            $name_id = 0;
        }else{
            $name_id = current(get_object_vars($name_id[0]));
        }
		Debug::Arr($name_id,'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);

		if (	$this->Validator->isLength(	'name',
											$name,
											('Name is invalid'),
											2,50)
					AND
						$this->Validator->isTrue(		'name',
														$this->isUniqueName($name),
														('Name is already in use in this year, or within 30 days'))

						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getHolidayTime( $user_id ) {
		if ( $this->getHolidayPolicyObject()->getType() == 30  ) {
			return $this->getAverageTime( $user_id );
		} else {
			return $this->getHolidayPolicyObject()->getMinimumTime();
		}
	}

	function getAverageTime( $user_id ) {
		$udtlf = new UserDateTotalListFactory();

		//Check if Min and Max time is the same, if so we can skip any averaging.
		if ( $this->getHolidayPolicyObject()->getMinimumTime() > 0
				AND $this->getHolidayPolicyObject()->getMaximumTime() > 0
				AND $this->getHolidayPolicyObject()->getMinimumTime() == $this->getHolidayPolicyObject()->getMaximumTime() ) {
			Debug::text('Min and Max times are equal.', __FILE__, __LINE__, __METHOD__,10);
			return $this->getHolidayPolicyObject()->getMinimumTime();
		}

		if ( $this->getHolidayPolicyObject()->getAverageTimeWorkedDays() == TRUE ) {
			Debug::text('Using worked days only...', __FILE__, __LINE__, __METHOD__,10);
			$last_days_worked_count = $udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate($user_id, ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getAverageTimeDays() * 86400) ), $this->getDateStamp()-86400  );
		} else {
			$last_days_worked_count = $this->getHolidayPolicyObject()->getAverageDays();
		}
		Debug::text('Average time over days:'. $last_days_worked_count, __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getHolidayPolicyObject()->getIncludeOverTime() == TRUE ) {
			Debug::text('Including OverTime!', __FILE__, __LINE__, __METHOD__,10);
			$total_seconds_worked = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_id, ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getAverageTimeDays() * 86400) ), $this->getDateStamp()-86400 );
		} else {
			Debug::text('NOT Including OverTime!', __FILE__, __LINE__, __METHOD__,10);
			$total_seconds_worked = $udtlf->getRegularTimeSumByUserIDAndStartDateAndEndDate( $user_id, ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getAverageTimeDays() * 86400) ), $this->getDateStamp()-86400 );
		}

		if ( $this->getHolidayPolicyObject()->getIncludePaidAbsenceTime() == TRUE ) {
			Debug::text('Including Paid Absence Time!', __FILE__, __LINE__, __METHOD__,10);
			$total_seconds_worked += $udtlf->getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $user_id, ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getAverageTimeDays() * 86400) ), $this->getDateStamp()-86400 );
		} else {
			Debug::text('NOT Including Paid Absence Time!', __FILE__, __LINE__, __METHOD__,10);
		}

		if ( $last_days_worked_count > 0 ) {
			$avg_seconds_worked_per_day = bcdiv($total_seconds_worked, $last_days_worked_count);
			Debug::text('AVG hours worked per day:'. TTDate::getHours( $avg_seconds_worked_per_day ), __FILE__, __LINE__, __METHOD__,10);
		} else {
			$avg_seconds_worked_per_day = 0;
		}

		if ( $this->getHolidayPolicyObject()->getMaximumTime() > 0
				AND $avg_seconds_worked_per_day > $this->getHolidayPolicyObject()->getMaximumTime() ) {
			$avg_seconds_worked_per_day = $this->getHolidayPolicyObject()->getMaximumTime();
			Debug::text('AVG hours worked per day exceeds maximum regulars hours per day, setting to:'. ($avg_seconds_worked_per_day / 60) / 60, __FILE__, __LINE__, __METHOD__,10);
		}

		if ( $avg_seconds_worked_per_day < $this->getHolidayPolicyObject()->getMinimumTime() ) {
			$avg_seconds_worked_per_day = $this->getHolidayPolicyObject()->getMinimumTime();
			Debug::text('AVG hours worked per day is less then minimum regulars hours per day, setting to:'. ($avg_seconds_worked_per_day / 60) / 60, __FILE__, __LINE__, __METHOD__,10);
		}

		//Round to nearest 15mins.
		if ( (int)$this->getHolidayPolicyObject()->getRoundIntervalPolicyID() != 0
				AND is_object($this->getHolidayPolicyObject()->getRoundIntervalPolicyObject() ) ) {
			$avg_seconds_worked_per_day = TTDate::roundTime($avg_seconds_worked_per_day, $this->getHolidayPolicyObject()->getRoundIntervalPolicyObject()->getInterval(), $this->getHolidayPolicyObject()->getRoundIntervalPolicyObject()->getRoundType() );
			Debug::text('Rounding Stat Time To: '. $avg_seconds_worked_per_day, __FILE__, __LINE__, __METHOD__,10);
		} else {
			Debug::text('NOT Rounding Stat Time!', __FILE__, __LINE__, __METHOD__,10);
		}

		return $avg_seconds_worked_per_day;
	}

	function isEligible( $user_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		//$this->getHolidayPolicyObject();

		$ulf = new UserListFactory();
		$user_obj = $ulf->getById($user_id)->getCurrent();

		$slf = new ScheduleListFactory();
		$udtlf = new UserDateTotalListFactory();

		//Make sure the employee has been employed long enough according to labor standards
		//Also make sure that the employee hasn't been terminated on or before the holiday.
		if ( $user_obj->getHireDate() <= ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getMinimumEmployedDays() * 86400 ) )
				AND ( $user_obj->getTerminationDate() == '' OR ( $user_obj->getTerminationDate() != '' AND $user_obj->getTerminationDate() > $this->getDateStamp() )  ) ) {
			Debug::text('Employee has been employed long enough!', __FILE__, __LINE__, __METHOD__,10);

			if ( $this->getHolidayPolicyObject()->getType() == 20 OR $this->getHolidayPolicyObject()->getType() == 30 ) {
				if ( $this->getHolidayPolicyObject()->getWorkedScheduledDays() == 1 //Scheduled Days
						AND $this->getHolidayPolicyObject()->getMinimumWorkedDays() > 0 AND $this->getHolidayPolicyObject()->getMinimumWorkedPeriodDays() > 0 ) {
					Debug::text('aUsing scheduled days!', __FILE__, __LINE__, __METHOD__,10);
					$slf->getByUserIdAndTypeAndDirectionFromDate($user_obj->getId(), 10, 'before', $this->getDateStamp(), $this->getHolidayPolicyObject()->getMinimumWorkedPeriodDays() );

					if ( $slf->getRecordCount() > 0 ) {
						//Get user_date_ids
						foreach( $slf as $s_obj ) {
							$scheduled_user_date_ids_before[] = $s_obj->getUserDateID();
						}
						//Debug::Arr($scheduled_user_date_ids_before, 'Scheduled UserDateIDs Before: ', __FILE__, __LINE__, __METHOD__,10);
					}
				} else {
					Debug::text('aUsing calendar days, NOT scheduled days!', __FILE__, __LINE__, __METHOD__,10);
				}

				if ( $this->getHolidayPolicyObject()->getWorkedAfterScheduledDays() == 1 //Scheduled Days
						AND $this->getHolidayPolicyObject()->getMinimumWorkedAfterDays() > 0 AND $this->getHolidayPolicyObject()->getMinimumWorkedAfterPeriodDays() > 0 ) {
					$slf->getByUserIdAndTypeAndDirectionFromDate($user_obj->getId(), 10, 'after', $this->getDateStamp(), $this->getHolidayPolicyObject()->getMinimumWorkedAfterPeriodDays() );
					Debug::text('bUsing scheduled days!', __FILE__, __LINE__, __METHOD__,10);
					if ( $slf->getRecordCount() > 0 ) {
						//Get user_date_ids
						foreach( $slf as $s_obj ) {
							$scheduled_user_date_ids_after[] = $s_obj->getUserDateID();
						}
						//Debug::Arr($scheduled_user_date_ids_after, 'Scheduled UserDateIDs After: ', __FILE__, __LINE__, __METHOD__,10);
					}
				} else {
					Debug::text('bUsing calendar days, NOT scheduled days!', __FILE__, __LINE__, __METHOD__,10);
				}

				$worked_before_days_count = 0;
				if ( $this->getHolidayPolicyObject()->getMinimumWorkedDays() > 0 AND $this->getHolidayPolicyObject()->getMinimumWorkedPeriodDays() > 0 ) {
					if ( isset($scheduled_user_date_ids_before) AND $this->getHolidayPolicyObject()->getWorkedScheduledDays() == 1 ) { //Scheduled Days
						$worked_before_days_count = $udtlf->getDaysWorkedByUserIDAndUserDateIDs($user_obj->getId(), $scheduled_user_date_ids_before );
					} elseif ( $this->getHolidayPolicyObject()->getWorkedScheduledDays() == 2 ) {  //Holiday Week Days
						//Start/End date should reflect weeks, no days here.
						$worked_before_days_count = $udtlf->getDaysWorkedByUserIDAndStartDateAndEndDateAndDayOfWeek($user_obj->getId(), ( $this->getDateStamp() - ( ($this->getHolidayPolicyObject()->getMinimumWorkedPeriodDays()*7) * 86400 ) ), $this->getDateStamp()-86400, TTDate::getDayOfWeek( $this->getDateStamp() ) );
					} else { //Calendar Days
						$worked_before_days_count = $udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate($user_obj->getId(), ( $this->getDateStamp() - ( $this->getHolidayPolicyObject()->getMinimumWorkedPeriodDays() * 86400) ), $this->getDateStamp()-86400  );
					}
				}
				Debug::text('Employee has worked the prior: '. $worked_before_days_count .' days (Must be at least: '. $this->getHolidayPolicyObject()->getMinimumWorkedDays() .')', __FILE__, __LINE__, __METHOD__,10);

				$worked_after_days_count = 0;
				if ( $this->getHolidayPolicyObject()->getMinimumWorkedAfterDays() > 0 AND $this->getHolidayPolicyObject()->getMinimumWorkedAfterPeriodDays() > 0 ) {
					if ( isset($scheduled_user_date_ids_after) AND $this->getHolidayPolicyObject()->getWorkedAfterScheduledDays() == 1 ) { //Scheduled Days
						$worked_after_days_count = $udtlf->getDaysWorkedByUserIDAndUserDateIDs($user_obj->getId(), $scheduled_user_date_ids_after );
					} else { //Calendar Days
						$worked_after_days_count = $udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate($user_obj->getId(), $this->getDateStamp()+86400, ( $this->getDateStamp() + ( $this->getHolidayPolicyObject()->getMinimumWorkedAfterPeriodDays() * 86400) ) );
					}
				}
				Debug::text('Employee has worked the following: '. $worked_after_days_count .' days (Must be at least: '. $this->getHolidayPolicyObject()->getMinimumWorkedAfterDays() .')', __FILE__, __LINE__, __METHOD__,10);

				//Make sure employee has worked for a portion of those days.
				if ( $worked_before_days_count >= $this->getHolidayPolicyObject()->getMinimumWorkedDays()
						AND $worked_after_days_count >= $this->getHolidayPolicyObject()->getMinimumWorkedAfterDays() ) {
					Debug::text('Employee has worked enough prior and following days!', __FILE__, __LINE__, __METHOD__,10);

					return TRUE;
				} else {
					Debug::text('Employee has NOT worked enough days prior or following the holiday!', __FILE__, __LINE__, __METHOD__,10);
				}
			} else {
				Debug::text('Standard Holiday Policy type, returning TRUE', __FILE__, __LINE__, __METHOD__,10);
				return TRUE;
			}
		} else {
			Debug::text('Employee has NOT been employed long enough!', __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->Validator->hasError('date_stamp') == FALSE AND $this->getDateStamp() == '' ) {
			$this->Validator->isTrue(		'date_stamp',
											FALSE,
											('Date is invalid'));
		}

		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
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
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, ('Holiday'), NULL, $this->getTable(), $this );
	}

}
?>
