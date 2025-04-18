<?php

namespace App\Models\PayPeriod;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Holiday\HolidayListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceFactory;
use Illuminate\Support\Facades\DB;

class PayPeriodScheduleFactory extends Factory {
	protected $table = 'pay_period_schedule';
	protected $pk_sequence_name = 'pay_period_schedule_id_seq'; //PK Sequence name

	protected $create_initial_pay_periods = FALSE;
	protected $enable_create_initial_pay_periods = TRUE;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				/*
				 * Set type to MANUAL to disable PP schedule. Rather then add a status_id field.
				 */
				$retval = array(
											5 => ('Manual'),
											10  => ('Weekly (52/year)'),
											20  => ('Bi-Weekly (26/year)'),
											30  => ('Semi-Monthly (24/year)'),
											//40  => ('Monthly + Advance'), //Handled with monthly PP schedule and Tax / Deduction to automatically enter advance each month. Advances are paid manually.
											50  => ('Monthly (12/year)') //Must have this here, for ROEs
										);
				break;
			case 'start_week_day':
				$retval = array(
											0 => ('Sunday-Saturday'),
											1 => ('Monday-Sunday'),
											2 => ('Tuesday-Monday'),
											3 => ('Wednesday-Tuesday'),
											4 => ('Thursday-Wednesday'),
											5 => ('Friday-Thursday'),
											6 => ('Saturday-Friday'),
										);
				break;
			case 'shift_assigned_day':
				$retval = array(
											10 => ('Day They Start On'),
											20 => ('Day They End On'),
											30 => ('Day w/Most Time Worked'),
											40 => ('Each Day (Split at Midnight)'),
										);
				break;
			case 'transaction_date':
				for ($i=1; $i <= 31; $i++) {
					$retval[$i] = $i;
				}
				break;
			case 'transaction_date_business_day':
				$retval = array(
											//Adjust Transaction Date To:
											0 => ('No'),
											1 => ('Yes - Previous Business Day'),
											2 => ('Yes - Next Business Day'),
											3 => ('Yes - Closest Business Day'),
										);
				break;
			case 'timesheet_verify_type':
				$retval = array(
											10 => ('Disabled'),
											20 => ('Employee Only'),
											30 => ('Superior Only'),
											40 => ('Employee & Superior'),
										);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1020-name' => ('Name'),
										'-1030-description' => ('Description'),
										'-1040-total_users' => ('Employees'),
										'-1050-start_week_day' => ('Overtime Week'),
										'-1060-shift_assigned_day' => ('Assign Shifts To'),
										'-1070-time_zone' => ('TimeZone'),
										'-1080-new_day_trigger_time' => ('Minimum Time Off Between Shifts'),
										'-1090-maximum_shift_time' => ('Maximum Shift Time'),

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
								'type',
								'name',
								'description',
								'total_users',
								);
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'type_id' => 'Type',
										'type' => FALSE,
										'start_week_day_id' => 'StartWeekDay',
										'start_week_day' => FALSE,
										'shift_assigned_day_id' => 'ShiftAssignedDay',
										'shift_assigned_day' => FALSE,
										'name' => 'Name',
										'description' => 'Description',
										'start_day_of_week' => 'StartDayOfWeek',
										'transaction_date' => 'TransactionDate',

										'primary_day_of_month' => 'PrimaryDayOfMonth',
										'secondary_day_of_month' => 'SecondaryDayOfMonth',
										'primary_transaction_day_of_month' => 'PrimaryTransactionDayOfMonth',
										'secondary_transaction_day_of_month' => 'SecondaryTransactionDayOfMonth',

										'transaction_date_bd' => 'TransactionDateBusinessDay',
										'anchor_date' => 'AnchorDate',
										'day_start_time' => 'DayStartTime',
										'time_zone' => 'TimeZone',
										'day_continuous_time' => 'ContinuousTime',
										'new_day_trigger_time' => 'NewDayTriggerTime',
										'maximum_shift_time' => 'MaximumShiftTime',
										'annual_pay_periods' => 'AnnualPayPeriods',
										'timesheet_verify_type_id' => 'TimeSheetVerifyType',
										'timesheet_verify_before_end_date' => 'TimeSheetVerifyBeforeEndDate',
										'timesheet_verify_before_transaction_date' => 'TimeSheetVerifyBeforeTransactionDate',
										//TimeSheet verification email notices are no longer required, as its handled with exceptions now.
										//'timesheet_verify_notice_before_transaction_date' => 'TimeSheetVerifyNoticeBeforeTransactionDate',
										//'timesheet_verify_notice_email' => 'TimeSheetVerifyNoticeEmail',
										'total_users' => FALSE,
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		return $this->data['company_id'];
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

	function getType() {
		//Have to return the KEY because it should always be a drop down box.
		//return Option::getByKey($this->data['status_id'], $this->getOptions('status') );
		return $this->data['type_id'];
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getStartWeekDay() {
		if ( isset($this->data['start_week_day_id']) ) {
			return $this->data['start_week_day_id'];
		}

		return FALSE;
	}
	function setStartWeekDay($val) {
		$val = trim($val);

		$key = Option::getByValue($val, $this->getOptions('start_week_day') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_week_day',
											$val,
											('Incorrect Start Week Day'),
											$this->getOptions('start_week_day')) ) {

			$this->data['start_week_day_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getShiftAssignedDay() {
		if ( isset($this->data['shift_assigned_day_id']) ) {
			return $this->data['shift_assigned_day_id'];
		}

		return FALSE;
	}
	function setShiftAssignedDay($val) {
		$val = trim($val);

		if ( $this->Validator->inArrayKey(	'shift_assigned_day_id',
											$val,
											('Incorrect Shift Assigned Day'),
											$this->getOptions('shift_assigned_day')) ) {

			$this->data['shift_assigned_day_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					':company_id' => $this->getCompany(),
					':name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = :company_id AND name = :name AND deleted=0';
		$pay_period_schedule_id = DB::select($query, $ph);
		
        if (empty($pay_period_schedule_id) || $pay_period_schedule_id == FALSE ) {
            $pay_period_schedule_id = 0;
        }else{
            $pay_period_schedule_id = current(get_object_vars($pay_period_schedule_id[0]));
        }

		Debug::Arr($pay_period_schedule_id,'Unique Pay Period Schedule ID: '. $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($pay_period_schedule_id) || $pay_period_schedule_id === FALSE ) {
			return TRUE;
		} else {
			if ($pay_period_schedule_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		return $this->data['name'];
	}
	function setName($name) {
		$name = trim($name);

		if (	$this->Validator->isLength(	'name',
											$name,
											('Name is invalid'),
											2,50)
				AND	$this->Validator->isTrue(	'name',
												$this->isUniqueName($name),
												('Name is already in use')
												)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$description == ''
				OR
				$this->Validator->isLength(	'description',
											$description,
											('Description is invalid'),
											2,255) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getStartDayOfWeek( $raw = FALSE) {
		if ( isset($this->data['start_day_of_week']) ) {
			return $this->data['start_day_of_week'];
		}

		return FALSE;
	}
	function setStartDayOfWeek($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfWeekArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_day_of_week',
											$val,
											('Incorrect start day of week'),
											TTDate::getDayOfWeekArray() ) ) {

			$this->data['start_day_of_week'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionDate() {
		if ( isset($this->data['transaction_date']) ) {
			return $this->data['transaction_date'];
		}

		return FALSE;
	}
	function setTransactionDate($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfWeekArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( $val == 0
				OR $this->Validator->inArrayKey(	'transaction_date',
											$val,
											('Incorrect transaction date'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['transaction_date'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function convertLastDayOfMonth( $val ) {
		if ( $val == -1 ) {
			return 31;
		}

		return $val;
	}

	function getPrimaryDayOfMonth() {
		if ( isset($this->data['primary_day_of_month']) ) {
			return $this->data['primary_day_of_month'];
		}

		return FALSE;
	}
	function setPrimaryDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( 	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'primary_day_of_month',
											$val,
											('Incorrect primary day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['primary_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getSecondaryDayOfMonth() {
		if ( isset($this->data['secondary_day_of_month']) ) {
			return $this->data['secondary_day_of_month'];
		}

		return FALSE;
	}
	function setSecondaryDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( 	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'secondary_day_of_month',
											$val,
											('Incorrect secondary day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['secondary_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getPrimaryTransactionDayOfMonth() {
		if ( isset($this->data['primary_transaction_day_of_month']) ) {
			return $this->data['primary_transaction_day_of_month'];
		}

		return FALSE;
	}
	function setPrimaryTransactionDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( 	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'primary_transaction_day_of_month',
											$val,
											('Incorrect primary transaction day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['primary_transaction_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getSecondaryTransactionDayOfMonth() {
		if ( isset($this->data['secondary_transaction_day_of_month']) ) {
			return $this->data['secondary_transaction_day_of_month'];
		}

		return FALSE;
	}
	function setSecondaryTransactionDayOfMonth($val) {
		$val = trim($val);

		$key = Option::getByValue($val, TTDate::getDayOfMonthArray() );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( 	( $val == -1 OR $val == '' OR $val == 0 )
				OR $this->Validator->inArrayKey(	'secondary_transaction_day_of_month',
											$val,
											('Incorrect secondary transaction day of month'),
											TTDate::getDayOfMonthArray() ) ) {

			$this->data['secondary_transaction_day_of_month'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionDateBusinessDay() {
		if ( isset($this->data['transaction_date_bd']) ) {
			return (int)$this->data['transaction_date_bd'];
		}
		return FALSE;
	}
	function setTransactionDateBusinessDay($int) {
		$int = (int)$int;

		if ( $this->Validator->inArrayKey(	'transaction_date_bd',
											$int,
											('Incorrect transaction date adjustment'),
											$this->getOptions('transaction_date_business_day') ) ) {

			$this->data['transaction_date_bd'] = $int;

			return TRUE;
		}

		return FALSE;
	}
/*
	function getTransactionDateBusinessDay() {
		if ( isset($this->data['transaction_date_bd']) ) {
			return $this->fromBool( $this->data['transaction_date_bd'] );
		}

		return FALSE;
	}
	function setTransactionDateBusinessDay($bool) {
		$this->data['transaction_date_bd'] = $this->toBool($bool);

		return true;
	}
*/
	function getAnchorDate( $raw = FALSE ) {
		if ( isset($this->data['anchor_date']) ) {
			if ( $raw === TRUE ) {
				return $this->data['anchor_date'];
			} else {
				return strtotime( $this->data['anchor_date'] );
			}
		}

		return FALSE;
	}
	function setAnchorDate($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'anchor_date',
												$epoch,
												('Incorrect start date')) ) {

			$this->data['anchor_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getDayStartTime() {
		if ( isset($this->data['day_start_time']) ) {
			return (int)$this->data['day_start_time'];
		}
		return FALSE;
	}
	function setDayStartTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'day_start_time',
													$int,
													('Incorrect day start time')) ) {
			$this->data['day_start_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeZoneOptions() {
		$upf = new UserPreferenceFactory();

		return $upf->getOptions('time_zone');
	}
	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		if ( $this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											('Incorrect time zone'),
											Misc::trimSortPrefix( $this->getTimeZoneOptions() ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function setOriginalTimeZone() {
		if ( isset($this->original_time_zone) ) {
			return TTDate::setTimeZone( $this->original_time_zone );
		}

		return FALSE;
	}
	function setPayPeriodTimeZone() {
		$this->original_time_zone = TTDate::getTimeZone();

		return TTDate::setTimeZone( $this->getTimeZone() );
	}
/*
	//Continuous time from the first punch of the day to the last
	//So if continuous time is set to 18hrs, and someone punches in for the first time at
	//11pm. All punches from 11pm + 18hrs are considered for the same day.
	function getContinuousTime() {
		if ( isset($this->data['day_continuous_time']) ) {
			return (int)$this->data['day_continuous_time'];
		}
		return FALSE;
	}
	function setContinuousTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'continuous_time',
													$int,
													('Incorrect continuous time')) ) {
			$this->data['day_continuous_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
*/
	//
	// Instead of daily continuous time, use minimum time-off between shifts that triggers a new day to start.
	//
	function getNewDayTriggerTime() {
		if ( isset($this->data['new_day_trigger_time']) ) {
			return (int)$this->data['new_day_trigger_time'];
		}
		return FALSE;
	}
	function setNewDayTriggerTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'new_day_trigger_time',
													$int,
													('Incorrect Minimum Time-Off Between Shifts')) ) {
			$this->data['new_day_trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumShiftTime() {
		if ( isset($this->data['maximum_shift_time']) ) {
			return (int)$this->data['maximum_shift_time'];
		}
		return FALSE;
	}
	function setMaximumShiftTime($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'maximum_shift_time',
													$int,
													('Incorrect Maximum Shift Time')) ) {
			$this->data['maximum_shift_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAnnualPayPeriods() {
		if ( isset($this->data['annual_pay_periods']) ) {
			return (int)$this->data['annual_pay_periods'];
		}
		return FALSE;
	}
	function setAnnualPayPeriods($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'annual_pay_periods',
													$int,
													('Incorrect Annual Pay Periods')) ) {
			$this->data['annual_pay_periods'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyType() {
		if ( isset($this->data['timesheet_verify_type_id']) ) {
			return $this->data['timesheet_verify_type_id'];
		}

		return TRUE;
	}
	function setTimeSheetVerifyType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'timesheet_verify_type_id',
											$type,
											('Incorrect TimeSheet Verification Type'),
											$this->getOptions('timesheet_verify_type')) ) {

			$this->data['timesheet_verify_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyBeforeEndDate() {
		if ( isset($this->data['timesheet_verify_before_end_date']) ) {
			return Misc::removeTrailingZeros( round( TTDate::getDays( (int)$this->data['timesheet_verify_before_end_date'] ), 3 ), 0 );
		}
		return FALSE;
	}
	function setTimeSheetVerifyBeforeEndDate($int) {
		//$int = (int)$int; // Do not cast to INT, need to support partial days.

		if 	(	$this->Validator->isNumeric(		'timesheet_verify_before_end_date',
													$int,
													('Incorrect value for timesheet verification before/after end date')) ) {
			$this->data['timesheet_verify_before_end_date'] = ($int*86400);

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyBeforeTransactionDate() {
		if ( isset($this->data['timesheet_verify_before_transaction_date']) ) {
			return Misc::removeTrailingZeros( round( TTDate::getDays( (int)$this->data['timesheet_verify_before_transaction_date'] ), 3 ), 0 );
		}
		return FALSE;
	}
	function setTimeSheetVerifyBeforeTransactionDate($int) {
		//$int = $int; // Do not cast to INT, need to support partial days.

		if 	(	$this->Validator->isNumeric(		'timesheet_verify_before_transaction_date',
													$int,
													('Incorrect value for timesheet verification before/after transaction date')) ) {
			$this->data['timesheet_verify_before_transaction_date'] = ($int*86400); //Convert to seconds to support partial days. Do not cast to INT!

			return TRUE;
		}

		return FALSE;
	}

	//Notices are no longer required with TimeSheet not verified exception.
	function getTimeSheetVerifyNoticeBeforeTransactionDate() {
		if ( isset($this->data['timesheet_verify_notice_before_transaction_date']) ) {
			return (int)$this->data['timesheet_verify_notice_before_transaction_date'];
		}
		return FALSE;
	}
	function setTimeSheetVerifyNoticeBeforeTransactionDate($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'timesheet_verify_notice_before_transaction_date',
													$int,
													('Incorrect value for timesheet verification notice before/after transaction date')) ) {
			$this->data['timesheet_verify_notice_before_transaction_date'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeSheetVerifyNoticeEmail() {
		if ( isset($this->data['timesheet_verify_notice_email']) ) {
			return $this->fromBool( $this->data['timesheet_verify_notice_email'] );
		}

		return FALSE;
	}
	function setTimeSheetVerifyNoticeEmail($bool) {
		$this->data['timesheet_verify_notice_email'] = $this->toBool($bool);

		return true;
	}

	function getUser() {
		$ppsulf = new PayPeriodScheduleUserListFactory();
		$ppsulf->getByPayPeriodScheduleId( $this->getId() );
		foreach ($ppsulf->rs as $pay_period_schedule) {
			$ppsulf->data = (array)$pay_period_schedule;
			$pay_period_schedule = $ppsulf;
			$user_list[] = $pay_period_schedule->getUser();
		}

		if ( isset($user_list) ) {
			return $user_list;
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
				$ppsulf = new PayPeriodScheduleUserListFactory();
				$ppsulf->getByPayPeriodScheduleId( $this->getId() );

				$user_ids = array();
				foreach ($ppsulf->rs as $pay_period_schedule) {
					$ppsulf->data = (array)$pay_period_schedule;
					$pay_period_schedule = $ppsulf;
					$user_id = $pay_period_schedule->getUser();
					Debug::text('Schedule ID: '. $pay_period_schedule->getPayPeriodSchedule() .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($user_id, $ids) ) {
						Debug::text('Deleting User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
						$pay_period_schedule->Delete();
					} else {
						//Save user ID's that need to be updated.
						Debug::text('NOT Deleting User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
						$user_ids[] = $user_id;
					}
				}
			}

			//Insert new mappings.
			$ulf = new UserListFactory();

			foreach ($ids as $id) {
				if ( $id != '' AND isset($user_ids) AND !in_array($id, $user_ids) ) {
					$ppsuf = new PayPeriodScheduleUserFactory();
					$ppsuf->setPayPeriodSchedule( $this->getId() );
					$ppsuf->setUser( $id );

					$user_obj = $ulf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'user',
														$ppsuf->Validator->isValid(),
														('Selected Employee is already assigned to another Pay Period').' ('. $user_obj->getFullName() .')' )) {
						$ppsuf->save();
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function getTransactionBusinessDay( $epoch ) {
		Debug::Text('Epoch: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		$holiday_epochs = array();

		$user_ids = $this->getUser();

		if ( count($user_ids) > 0 ) {
			$hlf = new HolidayListFactory();
			$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $user_ids, $epoch-(86400*14), $epoch+(86400*2) );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach( $hlf->rs as $h_obj ) {
					$hlf->data = (array)$h_obj;
					$h_obj = $hlf;
					Debug::Text('Found Holiday Epoch: '. TTDate::getDate('DATE+TIME', $h_obj->getDateStamp() ) .' Name: '. $h_obj->getName() , __FILE__, __LINE__, __METHOD__, 10);
					$holiday_epochs[] = $h_obj->getDateStamp();
				}

				//Debug::Arr($holiday_epochs, 'Holiday Epochs: ', __FILE__, __LINE__, __METHOD__, 10);
			}

			$epoch = TTDate::getNearestWeekDay( $epoch, $this->getTransactionDateBusinessDay(), $holiday_epochs );
		}

		return $epoch;
	}

	function getNextPayPeriod($end_date = NULL) {
		if ( !$this->Validator->isValid() ) {
			return FALSE;
		}

		//Manual Pay Period Schedule, skip repeating...
		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		$pplf = new PayPeriodListFactory();

		//Debug::text('PP Schedule ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('PP Schedule Name: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('PP Schedule Type ('.$this->getType().'): '. Option::getByKey($this->getType(), $this->getOptions('type') ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Anchor Date: '. $this->getAnchorDate() ." - ". TTDate::getDate('DATE+TIME', $this->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Primary Date: '. $this->getPrimaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Secondary Date: '. $this->getSecondaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);

		$last_pay_period_is_new = FALSE;
		if ( $end_date != '' AND $end_date != 0 ) {
			Debug::text('End Date is set: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
			$last_pay_period_end_date = $end_date;
		} else {
			Debug::text('Checking for Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10);
			//Get the last pay period schedule in the database.
			$pplf->getByPayPeriodScheduleId( $this->getId(), NULL, NULL, NULL, array('start_date' => 'desc') );
			$last_pay_period = $pplf->getCurrent();
			if ( $last_pay_period->isNew() ) {
				$last_pay_period_is_new = TRUE;

				Debug::text('No Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10);

				//Do this so a rollover doesn't happen while we're calculating.
				//$last_pay_period_end_date = TTDate::getTime();
				//This causes the pay period schedule to jump ahead one month. So set this to be beginning of the month.
				$last_pay_period_end_date = TTDate::getBeginMonthEpoch();
			} else {
				Debug::text('Previous pay periods found... ID: '. $last_pay_period->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$last_pay_period_end_date = $last_pay_period->getEndDate();
			}
			unset($last_pay_period, $pplf);
		}
		Debug::text('aLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

		//FIXME: This breaks having pay periods with different daily start times.
		//However, without it, I think DST breaks pay periods.
		//$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date + 1 ) - 86400;
		$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date - (86400/2) );
		Debug::text('bLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getDayStartTime() != 0 ) {
			Debug::text('Daily Start Time is set, adjusting Last Pay Period End Date by: '. TTDate::getHours( $this->getDayStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
			//Next adjust last_pay_period_end_date (which becomes the start date) to DayStartTime because then there could be a gap if they
			//change this mid-schedule. The End Date will take care of it after the first pay period.
			$last_pay_period_end_date = TTDate::getTimeLockedDate( TTDate::getBeginDayEpoch($last_pay_period_end_date) + $this->getDayStartTime(), $last_pay_period_end_date);
			Debug::text('cLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);
		}

		$insert_pay_period = 1; //deprecate primary pay periods.
		switch ( $this->getType() ) {
			case 10: //Weekly
			case 20: //Bi-Weekly
				$last_pay_period_end_day_of_week = TTDate::getDayOfWeek( $last_pay_period_end_date );
				Debug::text('Last Pay Period End Day Of Week: '. $last_pay_period_end_day_of_week .' Start Day Of Week: '. $this->getStartDayOfWeek(), __LINE__, __METHOD__, 10);
				if ( $last_pay_period_end_day_of_week != $this->getStartDayOfWeek() ) {
					Debug::text('zTmp Pay Period End Date: '. 'next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), __FILE__, __LINE__, __METHOD__, 10);
					//$tmp_pay_period_end_date = strtotime('next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), $last_pay_period_end_date )-1;
					$tmp_pay_period_end_date = strtotime('next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek(), FALSE ), $last_pay_period_end_date );

					//strtotime doesn't keep time when using "next", it resets it to midnight on the day, so we need to adjust for that.
					$tmp_pay_period_end_date = TTDate::getTimeLockedDate( TTDate::getBeginDayEpoch($tmp_pay_period_end_date) + $this->getDayStartTime(), $tmp_pay_period_end_date)-1;
				} else {
					$tmp_pay_period_end_date = $last_pay_period_end_date;

					//This should fix a bug where if they are creating a new pay period schedule
					//starting on Monday with the anchor date of 01-Jul-08, it would start on 01-Jul-08 (Tue)
					//rather moving back to the Monday.
					if ( TTDate::getDayOfMonth( $tmp_pay_period_end_date ) != TTDate::getDayOfMonth( $tmp_pay_period_end_date+1 ) ) {
						Debug::text('Right on day boundary, minus an additional second to account for difference...', __FILE__, __LINE__, __METHOD__, 10);
						$tmp_pay_period_end_date--;
					}
				}
				Debug::text('aTmp Pay Period End Date: '. TTDate::getDate('DATE+TIME', $tmp_pay_period_end_date) .' ('.$tmp_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);

				$start_date = $tmp_pay_period_end_date+1;

				if ( $this->getType() == 10 ) { //Weekly
					$tmp_pay_period_end_date = TTDate::getMiddleDayEpoch($start_date) + (86400*7); //Add one week
				} elseif ( $this->getType() == 20 ) { //Bi-Weekly
					$tmp_pay_period_end_date = TTDate::getMiddleDayEpoch($start_date) + (86400*14); //Add two weeks
				}

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = TTDate::getBeginDayEpoch( $tmp_pay_period_end_date )-1;
				$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getMiddleDayEpoch($end_date) + ($this->getTransactionDate()*86400) );

				break;
			case 30: //Semi-monthly
				$tmp_last_pay_period_end_day_of_month = TTDate::getDayOfMonth( $last_pay_period_end_date+1 );
				Debug::text('bLast Pay Period End Day Of Month: '. $tmp_last_pay_period_end_day_of_month, __FILE__, __LINE__, __METHOD__, 10);

				if ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) {
					$insert_pay_period = 1;
					$primary = TRUE;
				} elseif ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) {
					$insert_pay_period = 2;
					$primary = FALSE;
				} else {
					Debug::text('Finding if Primary or Secondary is closest...', __FILE__, __LINE__, __METHOD__, 10);

					$primary_date_offset = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) - $last_pay_period_end_date;
					$secondary_date_offset = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) - $last_pay_period_end_date;
					Debug::text('Primary Date Offset: '. TTDate::getDays( $primary_date_offset ) .' Secondary Date Offset: '. TTDate::getDays( $secondary_date_offset ), __FILE__, __LINE__, __METHOD__, 10);

					if ( $primary_date_offset <= $secondary_date_offset ) {
						$insert_pay_period = 1;
						$primary = TRUE;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
					} else {
						$insert_pay_period = 2;
						$primary = FALSE;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) );
					}
					$last_pay_period_end_date = TTDate::getBeginDayEpoch( $last_pay_period_end_date );
				}
				unset($tmp_last_pay_period_end_day_of_month);
				Debug::text('cLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .') Primary: '. (int)$primary, __FILE__, __LINE__, __METHOD__, 10);

				$start_date = $last_pay_period_end_date+1;

				if ( $primary == TRUE ) {
					$end_date = TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, NULL, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) ) -1;
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch($end_date), NULL, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );
				} else {
					$end_date = TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) ) -1;
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch($end_date), NULL, $this->convertLastDayOfMonth( $this->getSecondaryTransactionDayOfMonth() ) ) );
				}

				break;
			case 50: //Monthly
				$start_date = $last_pay_period_end_date+1;

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = TTDate::getDateOfNextDayOfMonth( TTDate::getBeginDayEpoch( $start_date+(86400+3600) ), NULL, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
				$end_date = TTDate::getBeginDayEpoch( TTDate::getBeginMinuteEpoch($end_date) )-1;

				$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( $end_date, NULL, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );

				break;
		}

		if (  $this->getDayStartTime() != 0 ) {
			Debug::text('Daily Start Time is set, adjusting End Date by: '. TTDate::getHours( $this->getDayStartTime() ) .' Start Date: '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);

			//We already account for DayStartTime in weekly/bi-weekly start_date cases above, so skip applying it again here.
			if ( $this->getType() != 10 AND $this->getType() != 20 ) {
				$start_date = $start_date + $this->getDayStartTime();
			}
			$end_date = $end_date + $this->getDayStartTime();

			//Need to do this, otherwise transaction date could be earlier then end date.
			$transaction_date = $transaction_date + $this->getDayStartTime();
		}

		Debug::text('aStart Date('. $start_date .'): '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('aEnd Date('. $end_date .'): '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('aPay Date('. $transaction_date .'): '. TTDate::getDate('DATE+TIME', $transaction_date), __FILE__, __LINE__, __METHOD__, 10);


		//Handle last day of the month flag for primary and secondary dates here
		if ( ( $this->getType() == 30
					AND (
						( $insert_pay_period == 1
						AND ( $this->getPrimaryDayOfMonth() == 31
							OR $this->getPrimaryDayOfMonth() == -1 )
						)
						OR ( $insert_pay_period == 2
							AND ( $this->getSecondaryDayOfMonth() == 31
								OR $this->getSecondaryDayOfMonth() == -1 )
							)
						)
			 )
			 OR
			 (
				$this->getType() == 50 AND ( $this->getPrimaryDayOfMonth() == 31 OR $this->getPrimaryDayOfMonth() == -1 )
			 ) ) {

			Debug::text('Last day of the month set for start date: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getDayStartTime() > 0 ) {
				//Minus one day, THEN add daily start time, otherwise it will go past the month boundary
				$end_date = (TTDate::getEndMonthEpoch($end_date)-86400) + ( $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			} else {
				$end_date = TTDate::getEndMonthEpoch($end_date) + ( $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			}
		}

		//Handle "last day of the month" for transaction dates.
		if ( $this->getPrimaryDayOfMonth() == 31 OR $this->getPrimaryDayOfMonth() == -1 ) {
			//Debug::text('LDOM set for Primary: ', __FILE__, __LINE__, __METHOD__, 10);
			$transaction_date = TTDate::getEndMonthEpoch($transaction_date);
		}

		//Handle "always business day" flag for transaction dates here.
		if ( $this->getTransactionDateBusinessDay() == TRUE ) {
			$transaction_date = $this->getTransactionBusinessDay($transaction_date);
		}

		if ( $transaction_date < $end_date ) {
			$transaction_date = $end_date;
		}

		Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Pay Date: '. TTDate::getDate('DATE+TIME', $transaction_date), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text("<br><br>\n\n", __FILE__, __LINE__, __METHOD__, 10);

		$this->next_start_date = $start_date;
		$this->next_end_date = $end_date;
		$this->next_transaction_date = $transaction_date;

		//Its a primary pay period
		if ($insert_pay_period == 1) {
			$this->next_primary = TRUE;
		} else {
			$this->next_primary = FALSE;
		}

		return TRUE;
	}

	function createNextPayPeriod($end_date = NULL, $offset = NULL) {
		if ( $end_date == NULL OR $end_date == '' ) {
			$end_date = NULL;
		}

		if ( $offset == NULL OR $offset == '' ) {
			$offset = 86400; //24hrs
		}

		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		Debug::text('Current TimeZone: '. TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);
		//Handle timezones in this function rather then getNextPayPeriod()
		//Because if we set the timezone back to the original in that function, it
		//gets written to the database in the "original" timezone, not the proper timezone.
		$this->setPayPeriodTimeZone();
		Debug::text('Pay Period TimeZone: '. TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);

		Debug::text('End Date ('. $end_date.'): '. TTDate::getDate('DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__,10);

		$this->getNextPayPeriod($end_date);

		Debug::text('Next pay period starts: '. TTDate::getDate('DATE+TIME', $this->getNextStartDate() ), __FILE__, __LINE__, __METHOD__,10);

		//If the start date is within 24hrs of now, insert the next pay period.
		if ( $this->getNextStartDate() <= ( TTDate::getTime() + $offset ) ) {
			Debug::text('Insert new pay period. Start Date: '. $this->getNextStartDate() .' End Date: '. $this->getNextEndDate() , __FILE__, __LINE__, __METHOD__,10);

			$ppf = new PayPeriodFactory();
			$ppf->setCompany( $this->getCompany() );
			$ppf->setPayPeriodSchedule( $this->getId() );
			$ppf->setStatus(10);
			$ppf->setStartDate( $this->getNextStartDate() );
			$ppf->setEndDate( $this->getNextEndDate() );
			$ppf->setTransactionDate( $this->getNextTransactionDate() );

			$ppf->setPrimary( $this->getNextPrimary() );
			$ppf->setEnableImportData( TRUE ); //Import punches when creating new pay periods.
			if ( $ppf->isValid() ) {
				$new_pay_period_id = $ppf->Save();
				Debug::text('New Pay Period ID: '. $new_pay_period_id, __FILE__, __LINE__, __METHOD__,10);

				if ( $new_pay_period_id != '' ) {
					$this->setOriginalTimeZone();

					return TRUE;
				} else {
					Debug::text('aSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__,10);
				}
			} else {
				Debug::text('bSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__,10);
			}

		} else {
			Debug::text('***NOT inserting or changing status of new pay period yet, not within offset.', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->setOriginalTimeZone();

		return FALSE;
	}


	function getNextStartDate() {
		if ( isset($this->next_start_date) ) {
			return $this->next_start_date;
		}

		return FALSE;
	}

	function getNextEndDate() {
		if ( isset($this->next_end_date) ) {
			return $this->next_end_date;
		}

		return FALSE;
	}

	function getNextTransactionDate() {
		if ( isset($this->next_transaction_date) ) {
			return $this->next_transaction_date;
		}

		return FALSE;
	}

	function getNextAdvanceEndDate() {
		if ( isset($this->next_advance_end_date) ) {
			return $this->next_advance_end_date;
		}

		return FALSE;
	}

	function getNextAdvanceTransactionDate() {
		if ( isset($this->next_advance_transaction_date) ) {
			return $this->next_advance_transaction_date;
		}

		return FALSE;
	}

	function getNextPrimary() {
		if ( isset($this->next_primary) ) {
			return $this->next_primary;
		}

		return FALSE;
	}

	//Pay period number functionality is deprecated, it causes too many problems
	//for little or no benefit. Its also impossible to properly handle in custom situations where pay periods
	//may be adjusted.
	function getCurrentPayPeriodNumber($epoch = NULL, $end_date_epoch = NULL) {
		//EPOCH MUST BE TRANSACTION DATE!!!
		//End Date Epoch must be END DATE of pay period

		//Don't return pay period number if its a manual schedule.
		if ( $this->getType() == 5 ) {
			return FALSE;
		}

		//FIXME: Turn this query in to a straight count(*) query for even more speed.
		if ($epoch == NULL OR $epoch == '') {
			$epoch = TTDate::getTime();
		}
		//Debug::text('Epoch: '. TTDate::getDate('DATE+TIME',$epoch) .' - End Date Epoch: '. TTDate::getDate('DATE+TIME',$end_date_epoch) , __FILE__, __LINE__, __METHOD__, 10);

/*
		//FIXME: If a company starts with TimeTrex half way through the year, this will be incorrect.
		//Because it only counts pay periods that exist, not pay periods that WOULD have existed.
		$pplf = new PayPeriodListFactory();
		$pplf->getByPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate( $this->getId(), TTDate::getBeginYearEpoch( $epoch ), $epoch );
		$retval = $pplf->getRecordCount();

		Debug::text('Current Pay Period: '. $retval , __FILE__, __LINE__, __METHOD__, 10);
*/


		//Half Fixed method here. We cache the results so to speed it up, but there still might be a faster way to do this.
		//FIXME: Perhaps some type of hybrid system like the above unless they have less then a years worth of
		//pay periods, then use this method below?
		$id = $this->getId().$epoch.$end_date_epoch;

		$retval = $this->getCache($id);

		if ( empty($retval) || $retval === FALSE ) {
			//FIXME: I'm sure there is a quicker way to do this.
			$next_transaction_date = 0;
			$next_end_date = $end_date_epoch;
			$end_year_epoch = TTDate::getEndYearEpoch( $epoch );
			$i=0;

			while ( $next_transaction_date <= $end_year_epoch AND $i < 100 ) {
				//Debug::text('I: '. $i .' Looping: Transaction Date: '. TTDate::getDate('DATE+TIME',$next_transaction_date) .' - End Year Epoch: '. TTDate::getDate('DATE+TIME',$end_year_epoch) , __FILE__, __LINE__, __METHOD__, 10);
				$this->getNextPayPeriod( $next_end_date );

				$next_transaction_date = $this->getNextTransactionDate();
				$next_end_date = $this->getNextEndDate();

				if ( $next_transaction_date <= $end_year_epoch ) {
					$i++;
				}
			}

			Debug::text('i: '. $i , __FILE__, __LINE__, __METHOD__, 10);

			$retval = $this->getAnnualPayPeriods() - $i;
			Debug::text('Current Pay Period: '. $retval , __FILE__, __LINE__, __METHOD__, 10);

			//Cache results
			$this->saveCache($retval,$id);
		}

		return $retval;
	}

	function calcAnnualPayPeriods() {
		switch ( $this->getType() ) {
			case 5:
				//We need the annual number of pay periods calculated for manual pay period schedules if we
				//are to have any hope of calculating taxes correctly.
				//Get all the pay periods, take the first day, last day, and the total number to figure out an average
				//number of days per period.
				//Alternatively have them manually specify the number, but this required adding a field to the table.
				$retval = FALSE;

				if ( $this->getId() > 0 ) {
					$pplf = new PayPeriodListFactory();
					$retarr = $pplf->getFirstStartDateAndLastEndDateByPayPeriodScheduleId( $this->getId() );
					if ( is_array($retarr) AND isset($retarr['first_start_date']) AND isset($retarr['last_end_date']) ) {
						$retarr['first_start_date'] = TTDate::strtotime( $retarr['first_start_date'] );
						$retarr['last_end_date']= TTDate::strtotime( $retarr['last_end_date'] );

						$days_per_period = ( ( $retarr['last_end_date'] - $retarr['first_start_date'] ) / $retarr['total']) / 86400;
						$retval = floor(365 / round( $days_per_period ) );
						Debug::text('First Start Date: '. TTDate::getDate('DATE+TIME', $retarr['first_start_date']) .' Last End Date: '. TTDate::getDate('DATE+TIME', $retarr['last_end_date']) .' Total PP: '. $retarr['total'] .' Average Days/Period: '. $days_per_period .'('. round($days_per_period).') Annual Pay Periods: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
					}
					unset($pplf, $retarr);
				}

				break;
			case 10:
				//Needs to take into account years, where 53 weeks may occur.
				//Will need to get the day weeks start on and the year for this to calculate properly.
				//I believe 2015 is the next time this will occur.
				$retval = 52;
				break;
			case 20:
				$retval = 26;
				break;
			case 30:
				$retval = 24; //Semi-monthly
				break;
			case 40:
				$retval = 12; //Monthly + advance, deductions only once per month
				break;
			case 50:
				$retval = 12;
				break;
			default:
				return FALSE;
				break;
		}

		return $retval;
	}

	//Returns shift data according to the pay period schedule criteria for use
	//in determining which day punches belong to.
	function getShiftData( $user_date_id = NULL, $user_id = NULL, $epoch = NULL, $filter = NULL, $tmp_punch_control_obj = NULL, $maximum_shift_time = NULL ) {
		global $profiler;
		$profiler->startTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		if ( is_numeric($user_date_id) AND $user_date_id > 0 ) {
			$user_id = $epoch = NULL;
		}

		if ( $user_date_id == '' AND $user_id == '' AND $epoch == '' ) {
			return FALSE;
		}

		if ( $maximum_shift_time === NULL ) {
			$maximum_shift_time = $this->getMaximumShiftTime();
			//echo '<br>maximum_shift_time....'.date('H:i:s', $maximum_shift_time); echo '<br>';
		}

		//Debug::text('User Date ID: '. $user_date_id .' User ID: '. $user_id .' TimeStamp: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		$new_shift_trigger_time = $this->getNewDayTriggerTime();

		$plf = new PunchListFactory();
		if ( $user_date_id != '' ) {
			$plf->getByUserDateId( $user_date_id );
		} else {
			//Get punches by time stamp.
			$punch_control_id = 0;
			if ( is_object( $tmp_punch_control_obj ) ) {
				$punch_control_id = $tmp_punch_control_obj->getId();
			}
			$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch, $punch_control_id, $maximum_shift_time );
			unset($punch_control_id);
		}

		Debug::text('Punch Rows: '. $plf->getRecordCount() .' UserID: '. $user_id .' Date: '. TTDate::getDate('DATE+TIME', $epoch) .'('.$epoch.') MaximumShiftTime: '. $maximum_shift_time .' Filter: '. $filter, __FILE__, __LINE__, __METHOD__, 10);

		if ( $plf->getRecordCount() > 0 ) {
			$shift = 0;
			$i = 0;
			$nearest_shift_id = 0;
			$nearest_punch_difference = FALSE;
			$prev_punch_obj = FALSE;
			foreach( $plf->rs as $p_obj ) {
				$plf->data = (array)$p_obj;
				$p_obj = $plf;
				//Debug::text('Shift: '. $shift .' Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

				//If we're editing a punch, we need to use the object passed to this function instead of the one
				//from the database.

				if ( $epoch == NULL ) { //If user_date_id is passed without epoch, set epoch to the first punch we find.
					$epoch = $p_obj->getTimeStamp();
				}

				if ( isset($prev_punch_arr) AND $p_obj->getTimeStamp() > $prev_punch_arr['time_stamp'] ) {
					$shift_data[$shift]['previous_punch_key'] = $i-1;
					if ( $shift_data[$shift]['previous_punch_key'] < 0 ) {
						$shift_data[$shift]['previous_punch_key'] = NULL;
					}
				}

				//Determine if a non-saved PunchControl object was passed, and if so, match the IDs to use that instead.
				if ( is_object($tmp_punch_control_obj) AND $p_obj->getPunchControlID() == $tmp_punch_control_obj->getId() ) {
					Debug::text('Passed non-saved punch control object that matches, using that instead... Using ID: '. (int)$tmp_punch_control_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
					$punch_control_obj = $tmp_punch_control_obj;
				} else {
					$punch_control_obj = $p_obj->getPunchControlObject();
				}

				//Can't use PunchControl object total_time because the record may not be saved yet when editing
				//an already existing punch.
				//When editing, simply pass the existing PunchControl object to this function so we can
				//use it instead of the one in the database perhaps?
				$total_time = $punch_control_obj->getTotalTime();

				//We can't skip records with total_time == 0, because then when deleting one of the two
				//punches in a pair, the remaining punch is ignored and causing punches to jump around between days in some cases.
				if ( $i > 0 AND isset($shift_data[$shift]['last_out'])
						AND ( $p_obj->getStatus() == 10 OR $p_obj->getStatus() == $prev_punch_arr['status_id'] )) {
					//Debug::text('Checking for new shift... This Control ID: '.$p_obj->getPunchControlID() .' Last Out Control ID: '. $shift_data[$shift]['last_out']['punch_control_id'] , __FILE__, __LINE__, __METHOD__, 10);
					//Assume that if two punches are assigned to the same punch_control_id are the same shift, even if the time between
					//them exceeds the new_shift_trigger_time. This helps fix the bug where you could add a In punch then add a Out
					//punch BEFORE the In punch as long as it was more than the Maximum Shift Time before the In Punch.
					//ie: Add: In Punch 10-Dec-09 @ 8:00AM, Add: Out Punch 09-Dec-09 @ 5:00PM.
					//Basically it just helps the validation checks to determine the error.
					//If shifts are split at midnight, new_shift_trigger_time must be 0, so the "split" punch can occur at midnight.
					if ( $p_obj->getPunchControlID() != $shift_data[$shift]['last_out']['punch_control_id']
							AND ( $p_obj->getTimeStamp() - $shift_data[$shift]['last_out']['time_stamp']) >= $new_shift_trigger_time ) {
						$shift++;
					}
				}

				if ( !isset($shift_data[$shift]['total_time']) ) {
					$shift_data[$shift]['total_time'] = 0;
				}

				$punch_day_epoch = TTDate::getBeginDayEpoch( $p_obj->getTimeStamp() );
				if ( !isset($shift_data[$shift]['total_time_per_day'][$punch_day_epoch]) ) {
					$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] = 0;
				}

				//Determine which shift is closest to the given epoch.
				$punch_difference_from_epoch = abs($epoch-$p_obj->getTimeStamp());
				if ( $nearest_punch_difference === FALSE OR $punch_difference_from_epoch <= $nearest_punch_difference ) {
					Debug::text('Nearest Shift Determined to be: '. $shift .' Nearest Punch Diff: '. (int)$nearest_punch_difference .' Punch Diff: '. $punch_difference_from_epoch, __FILE__, __LINE__, __METHOD__, 10);

					//If two punches have the same timestamp, use the shift that matches the passed punch control object, which is usually the one we are currently editing...
					//This is for splitting shifts at exactly midnight.
					if ( $punch_difference_from_epoch != $nearest_punch_difference
							OR ( $punch_difference_from_epoch == $nearest_punch_difference AND ( is_object( $tmp_punch_control_obj ) AND $tmp_punch_control_obj->getId() == $p_obj->getPunchControlID() ) ) ) {
						//Debug::text('Found two punches with the same timestamp... Tmp Punch Control: '.$tmp_punch_control_obj->getId() .' Punch Control: '. $p_obj->getPunchControlID() , __FILE__, __LINE__, __METHOD__, 10);
						$nearest_shift_id = $shift;
						$nearest_punch_difference = $punch_difference_from_epoch;
					}
				}

				$punch_arr = array(
									'id' => $p_obj->getId(),
									'punch_control_id' => $p_obj->getPunchControlId(),
									'user_date_id' => $punch_control_obj->getUserDateID(),
									'time_stamp' => $p_obj->getTimeStamp(),
									'status_id' => $p_obj->getStatus(),
									'type_id' => $p_obj->getType(),
									);

				$shift_data[$shift]['punches'][] = $punch_arr;
				$shift_data[$shift]['punch_control_ids'][] = $p_obj->getPunchControlId();
				if ( $punch_control_obj->getUserDateID() != FALSE ) {
					$shift_data[$shift]['user_date_ids'][] = $punch_control_obj->getUserDateID();
				}

				if ( !isset($shift_data[$shift]['span_midnight']) ) {
					$shift_data[$shift]['span_midnight'] = FALSE;
				}
				if ( !isset($shift_data[$shift]['first_in']) AND $p_obj->getStatus() == 10 ) {
					//Debug::text('First In -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['first_in'] = $punch_arr;
				} elseif ( $p_obj->getStatus() == 20 ) {
					//Debug::text('Last Out -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['last_out'] = $punch_arr;

					//Debug::text('Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
					$shift_data[$shift]['total_time'] += $total_time;

					//Check to see if the previous punch was on a different day then the current punch.
					if ( isset($prev_punch_arr) AND is_array($prev_punch_arr)
							AND ( $p_obj->getStatus() == 20 AND $prev_punch_arr['status_id'] != 20 )
							AND TTDate::doesRangeSpanMidnight( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp() ) == TRUE ) {
						Debug::text('Punch pair DOES span midnight', __FILE__, __LINE__, __METHOD__, 10);
						$shift_data[$shift]['span_midnight'] = TRUE;

						$total_time_for_each_day_arr = TTDate::calculateTimeOnEachDayBetweenRange( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp() );
						if ( is_array( $total_time_for_each_day_arr ) ) {
							foreach( $total_time_for_each_day_arr as $begin_day_epoch => $day_total_time ) {
								if ( !isset($shift_data[$shift]['total_time_per_day'][$begin_day_epoch]) ) {
									$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] = 0;
								}
								$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] += $day_total_time;
							}
						}
						unset($total_time_for_each_day_arr,$begin_day_epoch,$day_total_time, $prev_day_total_time);
					} else {
						$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] += $total_time;
					}
				}

				$prev_punch_arr = $punch_arr;
				$i++;
			}

			//Debug::Arr($shift_data, 'aShift Data:', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($shift_data) ) {
				//Loop through each shift to determine the day with the most time.
				foreach( $shift_data as $tmp_shift_key => $tmp_shift_data ) {
					krsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by day first
					arsort($shift_data[$tmp_shift_key]['total_time_per_day']); //Sort by total time per day.
					reset($shift_data[$tmp_shift_key]['total_time_per_day']);
					$shift_data[$tmp_shift_key]['day_with_most_time'] = key($shift_data[$tmp_shift_key]['total_time_per_day']);

					$shift_data[$tmp_shift_key]['punch_control_ids'] = array_unique( $shift_data[$tmp_shift_key]['punch_control_ids'] );
					if ( isset($shift_data[$tmp_shift_key]['user_date_ids']) ) {
						$shift_data[$tmp_shift_key]['user_date_ids'] = array_unique( $shift_data[$tmp_shift_key]['user_date_ids'] );
					}
				}
				unset($tmp_shift_key, $tmp_shift_data);

				if ( $filter == 'first_shift' ) {
					//Only return first shift.
					$shift_data = $shift_data[0];
				} elseif( $filter == 'last_shift' ) {
					//Only return last shift.
					$shift_data = $shift_data[$shift];
				} elseif ( $filter == 'nearest_shift' ) {
					$shift_data = $shift_data[$nearest_shift_id];
					//Check to make sure the nearest shift is within the new shift trigger time of EPOCH.
					if ( isset($shift_data['first_in']['time_stamp']) ) {
						$first_in = $shift_data['first_in']['time_stamp'];
					} elseif ( isset($shift_data['last_out']['time_stamp']) ) {
						$first_in = $shift_data['last_out']['time_stamp'];
					}

					if ( isset($shift_data['last_out']['time_stamp']) ) {
						$last_out = $shift_data['last_out']['time_stamp'];
					} elseif ( isset($shift_data['first_in']['time_stamp']) ) {
						$last_out = $shift_data['first_in']['time_stamp'];
					}

					//The check below must occur so if the user attempts to add an In punch that occurs AFTER the Out punch, this function
					//still returns the shift data, so the validation checks can occur in PunchControl factory.
					if ( $first_in > $last_out ) {
						//It appears that the first in punch has occurred after the OUT punch, so swap first_in and last_out, so we don't return FALSE in this case.
						list( $first_in, $last_out ) = array( $last_out, $first_in );
					}

					if ( TTDate::isTimeOverLap($epoch, $epoch, ($first_in-$new_shift_trigger_time), ($last_out+$new_shift_trigger_time) ) == FALSE ) {
						Debug::Text('Nearest shift is outside the new shift trigger time... Epoch: '. $epoch .' First In: '. $first_in .' Last Out: '. $last_out .' New Shift Trigger: '. $new_shift_trigger_time, __FILE__, __LINE__, __METHOD__, 10);

						return FALSE;
					}
					unset($first_in,$last_out);
				}

				$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

				//Debug::Arr($shift_data, 'bShift Data:', __FILE__, __LINE__, __METHOD__, 10);
				return $shift_data;
			}
		}

		$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		return FALSE;
	}

	function getEnableInitialPayPeriods() {
		if ( isset($this->enable_create_initial_pay_periods) ) {
			return $this->enable_create_initial_pay_periods;
		}

		return FALSE;
	}

	function setEnableInitialPayPeriods( $val ) {
		$this->enable_create_initial_pay_periods = (bool)$val;

		return TRUE;
	}

	function getCreateInitialPayPeriods() {
		if ( isset($this->create_initial_pay_periods) ) {
			return $this->create_initial_pay_periods;
		}

		return FALSE;
	}

	function setCreateInitialPayPeriods( $val ) {
		$this->create_initial_pay_periods = (bool)$val;

		return TRUE;
	}

	function preSave() {
		$this->StartTransaction();

		if ( $this->isNew() == TRUE ) {
			$this->setCreateInitialPayPeriods( TRUE );
		}

		if ( $this->getShiftAssignedDay() == FALSE ) {
			$this->setShiftAssignedDay( 10 ); //Day shifts start on
		} elseif ( $this->getShiftAssignedDay() == 40 ) { //Split at midnight
			$this->setNewDayTriggerTime( 0 ); //Minimum Time-off between shifts must be 0 in these cases.
		}

		if ( $this->getType() != 5 ) { //If schedule is other then manual, automatically calculate annual pay periods
			$this->setAnnualPayPeriods( $this->calcAnnualPayPeriods() );
		}

		if ( $this->getDeleted() == TRUE ) {
			//Delete pay periods assigned to this schedule.
			$pplf = new PayPeriodListFactory();
			$pplf->getByPayPeriodScheduleId( $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				Debug::text('Delete Pay Periods: '. $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				foreach( $pplf->rs as $pp_obj ) {
					$pplf->data = (array)$pp_obj;
					$pp_obj = $pplf;
					$pp_obj->setDeleted(TRUE);
					$pp_obj->Save();
				}
			}

		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			return TRUE;
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getEnableInitialPayPeriods() == TRUE AND $this->getCreateInitialPayPeriods() == TRUE ) {
			$ppslf = new PayPeriodScheduleListFactory();
			$pay_period_schedule_obj = $ppslf->getById( $this->getId() )->getCurrent();

			$pay_period_schedule_obj->createNextPayPeriod( $pay_period_schedule_obj->getAnchorDate() );
			Debug::text('New Pay Period Schdule, creating pay periods start from ('.$pay_period_schedule_obj->getAnchorDate().'): '. TTDate::getDate('DATE+TIME', $pay_period_schedule_obj->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10);

			//Create pay periods up until now, at most 260. (5yrs of weekly ones)
			for($i=0; $i <= 260; $i++ ) {
				if ( $pay_period_schedule_obj->createNextPayPeriod() == FALSE ) {
					Debug::text('createNextPayPeriod returned false, stopping loop.', __FILE__, __LINE__, __METHOD__, 10);
					break;
				}
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			//Remove from User Defaults.
			$udlf = new UserDefaultListFactory();
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				foreach( $udlf->rs as $udf_obj ) {
					$udlf->data = (array)$udf_obj;
					$udf_obj = $udlf;
					$udf_obj->setPayPeriodSchedule( 0 );
					if ( $udf_obj->isValid() ) {
						$udf_obj->Save();
					}
				}
			}
		}

		$this->CommitTransaction();

		return TRUE;
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						/*
						case 'new_day_trigger_time':
						case 'maximum_shift_time':
							Debug::text('Raw Time Unit: '. $data[$key] .' Parsing To: '. TTDate::parseTimeUnit( $data[$key] ), __FILE__, __LINE__, __METHOD__, 10);

							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseTimeUnit( $data[$key] ) );
							}
							break;
						*/
						case 'anchor_date':
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
						case 'total_users':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_week_day':
							$data[$variable] = Option::getByKey( $this->getStartWeekDay(), $this->getOptions( $variable ) );
							break;
						case 'shift_assigned_day':
							$data[$variable] = Option::getByKey( $this->getShiftAssignedDay(), $this->getOptions( $variable ) );
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
		return TTLog::addEntry( $this->getId(), $log_action, ('Pay Period Schedule'), NULL, $this->getTable(), $this );
	}

}
?>
