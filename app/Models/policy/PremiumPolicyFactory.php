<?php

namespace App\Models\Policy;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PremiumPolicyFactory extends Factory {
	protected $table = 'premium_policy';
	protected $pk_sequence_name = 'premium_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Date/Time'),
										20 => ('Shift Differential'),
										30 => ('Meal/Break'),
										40 => ('Callback'),
										50 => ('Minimum Shift Time'),
										90 => ('Holiday'),
										100 => ('Advanced'),
									);
				break;
			case 'pay_type':
				//How to calculate flat rate. Base it off the DIFFERENCE between there regular hourly rate
				//and the premium. So the PS Account could be postitive or negative amount
				$retval = array(
										10 => ('Pay Multiplied By Factor'),
										20 => ('Pay + Premium'), //This is the same a Flat Hourly Rate (Absolute)
										30 => ('Flat Hourly Rate (Relative to Wage)'), //This is a relative rate based on their hourly rate.
									);
				break;
			case 'branch_selection_type':
				$retval = array(
										10 => ('All Branches'),
										20 => ('Only Selected Branches'),
										30 => ('All Except Selected Branches'),
									);
				break;
			case 'department_selection_type':
				$retval = array(
										10 => ('All Departments'),
										20 => ('Only Selected Departments'),
										30 => ('All Except Selected Departments'),
									);
				break;
			case 'job_group_selection_type':
				$retval = array(
										10 => ('All Job Groups'),
										20 => ('Only Selected Job Groups'),
										30 => ('All Except Selected Job Groups'),
									);
				break;
			case 'job_selection_type':
				$retval = array(
										10 => ('All Jobs'),
										20 => ('Only Selected Jobs'),
										30 => ('All Except Selected Jobs'),
									);
				break;
			case 'job_item_group_selection_type':
				$retval = array(
										10 => ('All Task Groups'),
										20 => ('Only Selected Task Groups'),
										30 => ('All Except Selected Task Groups'),
									);
				break;
			case 'job_item_selection_type':
				$retval = array(
										10 => ('All Tasks'),
										20 => ('Only Selected Tasks'),
										30 => ('All Except Selected Tasks'),
									);
				break;

			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1030-name' => ('Name'),

										'-1040-pay_type' => ('Pay Type'),
										'-1040-rate' => ('Rate'),
										'-1050-accrual_rate' => ('Accrual Rate'),

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
								'type',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
										'pay_type_id' => 'PayType',
										'pay_type' => FALSE,
										'start_date' => 'StartDate',
										'end_date' => 'EndDate',
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'daily_trigger_time' => 'DailyTriggerTime',
										'weekly_trigger_time' => 'WeeklyTriggerTime',
										'sun' => 'Sun',
										'mon' => 'Mon',
										'tue' => 'Tue',
										'wed' => 'Wed',
										'thu' => 'Thu',
										'fri' => 'Fri',
										'sat' => 'Sat',
										'include_partial_punch' => 'IncludePartialPunch',
										'maximum_no_break_time' => 'MaximumNoBreakTime',
										'minimum_break_time' => 'MinimumBreakTime',
										'minimum_time_between_shift' => 'MinimumTimeBetweenShift',
										'minimum_first_shift_time' => 'MinimumFirstShiftTime',
										'minimum_shift_time' => 'MinimumShiftTime',
										'minimum_time' => 'MinimumTime',
										'maximum_time' => 'MaximumTime',
										'include_meal_policy' => 'IncludeMealPolicy',
										'include_break_policy' => 'IncludeBreakPolicy',
										'wage_group_id' => 'WageGroup',
										'rate' => 'Rate',
										'accrual_rate' => 'AccrualRate',
										'accrual_policy_id' => 'AccrualPolicyID',
										'pay_stub_entry_account_id' => 'PayStubEntryAccountId',
										'pay_stub_entry_account' => FALSE,
										'branch' => 'Branch',
										'branch_selection_type_id' => 'BranchSelectionType',
										'branch_selection_type' => FALSE,
										'exclude_default_branch' => 'ExcludeDefaultBranch',
										'department' => 'Department',
										'department_selection_type_id' => 'DepartmentSelectionType',
										'department_selection_type' => FALSE,
										'exclude_default_department' => 'ExcludeDefaultDepartment',
										'job_group' => 'JobGroup',
										'job_group_selection_type_id' => 'JobGroupSelectionType',
										'job_group_selection_type' => FALSE,
										'job' => 'Job',
										'job_selection_type_id' => 'JobSelectionType',
										'job_selection_type' => FALSE,
										'job_item_group' => 'JobItemGroup',
										'job_item_group_selection_type_id' => 'JobItemGroupSelectionType',
										'job_item_group_selection_type' => FALSE,
										'job_item' => 'JobItem',
										'job_item_selection_type_id' => 'JobItemSelectionType',
										'job_item_selection_type' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = new CompanyListFactory();
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return TRUE;
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
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getPayType() {
		if ( isset($this->data['pay_type_id']) ) {
			return $this->data['pay_type_id'];
		}

		return FALSE;
	}
	function setPayType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('pay_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'pay_type_id',
											$value,
											('Incorrect Pay Type'),
											$this->getOptions('pay_type')) ) {

			$this->data['pay_type_id'] = $value;

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

		if ( $epoch == '' ){
			$epoch = NULL;
		}

		if 	(
				$epoch == NULL
				OR
				$this->Validator->isDate(		'start_date',
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

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'start_time',
												$epoch,
												('Incorrect Start time'))
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

		if 	(	$epoch == ''
				OR
				$this->Validator->isDate(		'end_time',
												$epoch,
												('Incorrect End time'))
			) {

			$this->data['end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getDailyTriggerTime() {
		if ( isset($this->data['daily_trigger_time']) ) {
			return (int)$this->data['daily_trigger_time'];
		}

		return FALSE;
	}
	function setDailyTriggerTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'daily_trigger_time',
													$int,
													('Incorrect Daily Trigger Time')) ) {
			$this->data['daily_trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWeeklyTriggerTime() {
		if ( isset($this->data['weekly_trigger_time']) ) {
			return (int)$this->data['weekly_trigger_time'];
		}

		return FALSE;
	}
	function setWeeklyTriggerTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'weekly_trigger_time',
													$int,
													('Incorrect weekly Trigger Time')) ) {
			$this->data['weekly_trigger_time'] = $int;

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


	function getIncludePartialPunch() {
		if ( isset($this->data['include_partial_punch']) ) {
			return $this->fromBool( $this->data['include_partial_punch'] );
		}

		return FALSE;
	}
	function setIncludePartialPunch($bool) {
		$this->data['include_partial_punch'] = $this->toBool($bool);

		return TRUE;
	}

	function getMaximumNoBreakTime() {
		if ( isset($this->data['maximum_no_break_time']) ) {
			return (int)$this->data['maximum_no_break_time'];
		}

		return FALSE;
	}
	function setMaximumNoBreakTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	( $int == 0
				OR $this->Validator->isNumeric(		'maximum_no_break_time',
													$int,
													('Incorrect Maximum Time Without Break')) ) {
			$this->data['maximum_no_break_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumBreakTime() {
		if ( isset($this->data['minimum_break_time']) ) {
			return (int)$this->data['minimum_break_time'];
		}

		return FALSE;
	}
	function setMinimumBreakTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$int == 0
				OR $this->Validator->isNumeric(		'minimum_break_time',
													$int,
													('Incorrect Minimum Break Time')) ) {
			$this->data['minimum_break_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumTimeBetweenShift() {
		if ( isset($this->data['minimum_time_between_shift']) ) {
			return (int)$this->data['minimum_time_between_shift'];
		}

		return FALSE;
	}
	function setMinimumTimeBetweenShift($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	( $int == 0
				OR $this->Validator->isNumeric(		'minimum_time_between_shift',
													$int,
													('Incorrect Minimum Time Between Shifts')) ) {
			$this->data['minimum_time_between_shift'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumFirstShiftTime() {
		if ( isset($this->data['minimum_first_shift_time']) ) {
			return (int)$this->data['minimum_first_shift_time'];
		}

		return FALSE;
	}
	function setMinimumFirstShiftTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$int == 0
				OR $this->Validator->isNumeric(		'minimum_first_shift_time',
													$int,
													('Incorrect Minimum First Shift Time')) ) {
			$this->data['minimum_first_shift_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumShiftTime() {
		if ( isset($this->data['minimum_shift_time']) ) {
			return (int)$this->data['minimum_shift_time'];
		}

		return FALSE;
	}
	function setMinimumShiftTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$int == 0
				OR $this->Validator->isNumeric(		'minimum_shift_time',
													$int,
													('Incorrect Minimum Shift Time')) ) {
			$this->data['minimum_shift_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}


	function getMinimumTime() {
		if ( isset($this->data['minimum_time']) ) {
			return (int)$this->data['minimum_time'];
		}

		return FALSE;
	}
	function setMinimumTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'minimum_time',
													$int,
													('Incorrect Minimum Time')) ) {
			$this->data['minimum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumTime() {
		if ( isset($this->data['maximum_time']) ) {
			return (int)$this->data['maximum_time'];
		}

		return FALSE;
	}
	function setMaximumTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'maximum_time',
													$int,
													('Incorrect Maximum Time')) ) {
			$this->data['maximum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getIncludeMealPolicy() {
		if ( isset($this->data['include_meal_policy']) ) {
			return $this->fromBool( $this->data['include_meal_policy'] );
		}

		return FALSE;
	}
	function setIncludeMealPolicy($bool) {
		$this->data['include_meal_policy'] = $this->toBool($bool);

		return TRUE;
	}

	function getIncludeBreakPolicy() {
		if ( isset($this->data['include_break_policy']) ) {
			return $this->fromBool( $this->data['include_break_policy'] );
		}

		return FALSE;
	}
	function setIncludeBreakPolicy($bool) {
		$this->data['include_break_policy'] = $this->toBool($bool);

		return TRUE;
	}

	function getWageGroup() {
		if ( isset($this->data['wage_group_id']) ) {
			return $this->data['wage_group_id'];
		}

		return FALSE;
	}
	function setWageGroup($id) {
		$id = trim($id);

		$wglf = new WageGroupListFactory();

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'wage_group',
													$wglf->getByID($id),
													('Wage Group is invalid')
													) ) {

			$this->data['wage_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRate() {
		if ( isset($this->data['rate']) ) {
			return $this->data['rate'];
		}

		return FALSE;
	}
	function setRate($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isFloat(		'rate',
												$int,
												('Incorrect Rate')) ) {
			$this->data['rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualRate() {
		if ( isset($this->data['accrual_rate']) ) {
			return $this->data['accrual_rate'];
		}

		return FALSE;
	}
	function setAccrualRate($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isFloat(		'accrual_rate',
												$int,
												('Incorrect Accrual Rate')) ) {
			$this->data['accrual_rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicyID() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return $this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = new AccrualPolicyListFactory();

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'accrual_policy',
													$aplf->getByID($id),
													('Accrual Policy is invalid')
													) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubEntryAccountId() {
		if ( isset($this->data['pay_stub_entry_account_id']) ) {
			return $this->data['pay_stub_entry_account_id'];
		}

		return FALSE;
	}
	function setPayStubEntryAccountId($id) {
		$id = trim($id);

		Debug::text('Entry Account ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$psealf = new PayStubEntryAccountListFactory();

		if (
				$this->Validator->isResultSetWithRows(	'pay_stub_entry_account_id',
														$psealf->getById($id),
														('Invalid Pay Stub Account')
														) ) {
			$this->data['pay_stub_entry_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
	function getHourlyRate( $original_hourly_rate ) {
		Debug::text(' Getting Premium Rate based off Hourly Rate: '. $original_hourly_rate, __FILE__, __LINE__, __METHOD__, 10);
		$rate = 0;

		switch ( $this->getPayType() ) {
			case 10: //Pay Factor
				//Since they are already paid for this time with regular or OT, minus 1 from the rate
				$rate = ( $original_hourly_rate * ( $this->getRate() - 1) );
				break;
			case 20: //Pay Plus Premium
				$rate = $this->getRate();
				break;
			case 30: //Flat Hourly Rate
				//Get the difference between the employees current wage and the premium wage.
				$rate = $this->getRate() - $original_hourly_rate;
				break;
		}

		return Misc::MoneyFormat($rate, FALSE);
	}

	/*

	 Branch/Department/Job/Task differential functions

	*/
	function getBranchSelectionType() {
		if ( isset($this->data['branch_selection_type_id']) ) {
			return $this->data['branch_selection_type_id'];
		}

		return FALSE;
	}
	function setBranchSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'branch_selection_type',
											$value,
											('Incorrect Branch Selection Type'),
											$this->getOptions('branch_selection_type')) ) {

			$this->data['branch_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExcludeDefaultBranch() {
		if ( isset($this->data['exclude_default_branch']) ) {
			return $this->fromBool( $this->data['exclude_default_branch'] );
		}

		return FALSE;
	}
	function setExcludeDefaultBranch($bool) {
		$this->data['exclude_default_branch'] = $this->toBool($bool);

		return TRUE;
	}

	function getBranch() {
		$lf = new PremiumPolicyBranchListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getBranch();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setBranch($ids) {
		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($ids, 'Setting Branch IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyBranchListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getBranch();
					Debug::text('Branch ID: '. $obj->getBranch() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new BranchListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyBranchFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setBranch( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'branch',
														$f->Validator->isValid(),
														('Selected Branch is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getDepartmentSelectionType() {
		if ( isset($this->data['department_selection_type_id']) ) {
			return $this->data['department_selection_type_id'];
		}

		return FALSE;
	}
	function setDepartmentSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'department_selection_type',
											$value,
											('Incorrect Department Selection Type'),
											$this->getOptions('department_selection_type')) ) {

			$this->data['department_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExcludeDefaultDepartment() {
		if ( isset($this->data['exclude_default_department']) ) {
			return $this->fromBool( $this->data['exclude_default_department'] );
		}

		return FALSE;
	}
	function setExcludeDefaultDepartment($bool) {
		$this->data['exclude_default_department'] = $this->toBool($bool);

		return TRUE;
	}

	function getDepartment() {
		$lf = new PremiumPolicyDepartmentListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getDepartment();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setDepartment($ids) {
		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyDepartmentListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getDepartment();
					Debug::text('Department ID: '. $obj->getDepartment() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new DepartmentListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyDepartmentFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setDepartment( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'department',
														$f->Validator->isValid(),
														('Selected Department is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}




	function getJobGroupSelectionType() {
		if ( isset($this->data['job_group_selection_type_id']) ) {
			return $this->data['job_group_selection_type_id'];
		}

		return FALSE;
	}
	function setJobGroupSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_group_selection_type',
											$value,
											('Incorrect Job Group Selection Type'),
											$this->getOptions('job_group_selection_type')) ) {

			$this->data['job_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobGroup() {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		$lf = new PremiumPolicyJobGroupListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getJobGroup();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setJobGroup($ids) {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyJobGroupListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getJobGroup();
					Debug::text('Job Group ID: '. $obj->getJobGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new JobGroupListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyJobGroupFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'job_group',
														$f->Validator->isValid(),
														('Selected Job Group is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getJobSelectionType() {
		if ( isset($this->data['job_selection_type_id']) ) {
			return $this->data['job_selection_type_id'];
		}

		return FALSE;
	}
	function setJobSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_selection_type',
											$value,
											('Incorrect Job Selection Type'),
											$this->getOptions('job_selection_type')) ) {

			$this->data['job_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		$lf = new PremiumPolicyJobListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getjob();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setJob($ids) {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyJobListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getjob();
					Debug::text('job ID: '. $obj->getJob() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new JobListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyJobFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setJob( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'job',
														$f->Validator->isValid(),
														('Selected Job is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getJobItemGroupSelectionType() {
		if ( isset($this->data['job_item_group_selection_type_id']) ) {
			return $this->data['job_item_group_selection_type_id'];
		}

		return FALSE;
	}
	function setJobItemGroupSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_item_group_selection_type',
											$value,
											('Incorrect Task Group Selection Type'),
											$this->getOptions('job_item_group_selection_type')) ) {

			$this->data['job_item_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItemGroup() {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		$lf = new PremiumPolicyJobItemGroupListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getJobItemGroup();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setJobItemGroup($ids) {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyJobItemGroupListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getJobItemGroup();
					Debug::text('Job Item Group ID: '. $obj->getJobItemGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new JobItemGroupListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyJobItemGroupFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobItemGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'job_item_group',
														$f->Validator->isValid(),
														('Selected Task Group is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getJobItemSelectionType() {
		if ( isset($this->data['job_item_selection_type_id']) ) {
			return $this->data['job_item_selection_type_id'];
		}

		return FALSE;
	}
	function setJobItemSelectionType($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'job_item_selection_type',
											$value,
											('Incorrect Task Selection Type'),
											$this->getOptions('job_item_selection_type')) ) {

			$this->data['job_item_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		$lf = new PremiumPolicyJobItemListFactory();
		$lf->getByPremiumPolicyId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getJobItem();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setJobItem($ids) {
		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL ) {
			return FALSE;
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new PremiumPolicyJobItemListFactory();
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getJobItem();
					Debug::text('Job Item ID: '. $obj->getJobItem() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$lf_b = new JobItemListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = new PremiumPolicyJobItemFactory();
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobItem( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'job',
														$f->Validator->isValid(),
														('Selected JobItem is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isActive( $in_epoch, $out_epoch = NULL ) {
		if ( $out_epoch == '' ) {
			$out_epoch = $in_epoch;
		}

		//Debug::text(' In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		for( $i=$in_epoch; $i <= $out_epoch; $i+=86400 ) {
			if ( $this->isActiveDate($i) == TRUE AND $this->isActiveDayOfWeek($i) == TRUE ) {
				//Debug::text('Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);

				return TRUE;
			}
		}

		Debug::text('NOT Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//Check if this premium policy is restricted by time.
	//If its not, we can apply it to non-punched hours.
	function isTimeRestricted() {
		//If time restrictions account for over 23.5 hours, then we assume
		//that this policy is not time restricted at all.
		$time_diff = abs( $this->getEndTime() - $this->getStartTime() );
		if ( $time_diff > 0 AND $time_diff < (23.5*3600) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isHourRestricted() {
		if ( $this->getDailyTriggerTime() > 0 OR $this->getWeeklyTriggerTime() > 0 ) {
			return TRUE;
		}

		return FALSE;
	}

	function getPartialPunchTotalTime( $in_epoch, $out_epoch, $total_time ) {
		$retval = $total_time;

		if ( $this->isActiveTime( $in_epoch, $out_epoch )
				AND $this->getIncludePartialPunch() == TRUE
				AND ( $this->getStartTime() > 0 OR $this->getEndTime() > 0 ) ) {
			Debug::text(' Checking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);

			Debug::text(' Raw Start TimeStamp('.$this->getStartTime(TRUE).'): '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' Raw End TimeStamp: '. TTDate::getDate('DATE+TIME', $this->getEndTime() )  , __FILE__, __LINE__, __METHOD__, 10);
			$start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $in_epoch);
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), $in_epoch);

			//Check if end timestamp is before start, if it is, move end timestamp to next day.
			if ( $end_time_stamp < $start_time_stamp ) {
				Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
				$end_time_stamp = $end_time_stamp + 86400;
			}

			$retval = 0;
			for( $i=($start_time_stamp-86400); $i <= ($end_time_stamp+86400); $i+=86400 ) {
				$tmp_start_time_stamp = $i;
				$tmp_end_time_stamp = $tmp_start_time_stamp + ($end_time_stamp - $start_time_stamp);

				if ( $this->isActiveTime( $tmp_start_time_stamp, $tmp_end_time_stamp ) == TRUE ) {
					$retval += TTDate::getTimeOverLapDifference( $tmp_start_time_stamp, $tmp_end_time_stamp, $in_epoch, $out_epoch );
					Debug::text(' Calculating partial time against Start TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .' End TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp) .' Total: '. $retval  , __FILE__, __LINE__, __METHOD__, 10);
				} else {
					Debug::text(' Not Active on this day: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::text(' Partial Punch Total Time : '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	//Check if this time is within the start/end time.
	function isActiveTime( $in_epoch, $out_epoch ) {
		Debug::text(' Checking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);

		Debug::text(' Raw Start TimeStamp('.$this->getStartTime(TRUE).'): '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' Raw End TimeStamp: '. TTDate::getDate('DATE+TIME', $this->getEndTime() )  , __FILE__, __LINE__, __METHOD__, 10);
		$start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $in_epoch); //Base the end time on day of the in_epoch.
		$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), $in_epoch); //Base the end time on day of the in_epoch.

		//Check if end timestamp is before start, if it is, move end timestamp to next day.
		if ( $end_time_stamp < $start_time_stamp ) {
			Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
			$end_time_stamp = $end_time_stamp + 86400;
		}

		Debug::text(' Start TimeStamp: '. TTDate::getDate('DATE+TIME', $start_time_stamp) .' End TimeStamp: '. TTDate::getDate('DATE+TIME', $end_time_stamp)  , __FILE__, __LINE__, __METHOD__, 10);
		//Check to see if start/end time stamps are not set or are equal, we always return TRUE if they are.
		if ( $start_time_stamp == ''
				OR $end_time_stamp == ''
				OR $start_time_stamp == $end_time_stamp ) {
			Debug::text(' Start/End time not set, assume it always matches.', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		} else {
			//If the premium policy start/end time spans midnight, there could be multiple windows to check
			//where the premium policy applies, make sure we check all windows.
			for( $i=($start_time_stamp-86400); $i <= ($end_time_stamp+86400); $i+=86400 ) {
				$tmp_start_time_stamp = $i;
				$tmp_end_time_stamp = $tmp_start_time_stamp + ($end_time_stamp - $start_time_stamp);

				if ( $this->isActive( $tmp_start_time_stamp, $tmp_end_time_stamp ) == TRUE ) {
					//Debug::text(' Checking against Start TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .' End TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp)  , __FILE__, __LINE__, __METHOD__, 10);
					if ( $this->getIncludePartialPunch() == TRUE AND TTDate::isTimeOverLap( $in_epoch, $out_epoch, $tmp_start_time_stamp, $tmp_end_time_stamp) == TRUE ) {
						//When dealing with partial punches, any overlap whatsoever activates the policy.
						Debug::text(' Partial Punch Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} elseif ( $in_epoch >= $tmp_start_time_stamp AND $in_epoch <= $tmp_end_time_stamp ) {
						//Non partial punches, they must punch in within the time window.
						Debug::text(' Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					}
				} else {
					Debug::text(' Not Active on this day: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::text(' NOT Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//Check if this date is within the effective date range
	function isActiveDate( $epoch ) {
		//Debug::text(' Checking for Active Date: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		$epoch = TTDate::getBeginDayEpoch( $epoch );

		if ( $this->getStartDate() == '' AND $this->getEndDate() == '') {
			return TRUE;
		}

		if ( $epoch >= (int)$this->getStartDate()
				AND ( $epoch <= (int)$this->getEndDate() OR $this->getEndDate() == '' ) ) {
			return TRUE;
		}

		return FALSE;
	}

	//Check if this day of the week is active
	function isActiveDayOfWeek($epoch) {
		//Debug::text(' Checking for Active Day of Week.', __FILE__, __LINE__, __METHOD__, 10);
		$day_of_week = strtolower(date('D', $epoch));

		switch ($day_of_week) {
			case 'sun':
				if ( $this->getSun() == TRUE ) {
					return TRUE;
				}
				break;
			case 'mon':
				if ( $this->getMon() == TRUE ) {
					return TRUE;
				}
				break;
			case 'tue':
				if ( $this->getTue() == TRUE ) {
					return TRUE;
				}
				break;
			case 'wed':
				if ( $this->getWed() == TRUE ) {
					return TRUE;
				}
				break;
			case 'thu':
				if ( $this->getThu() == TRUE ) {
					return TRUE;
				}
				break;
			case 'fri':
				if ( $this->getFri() == TRUE ) {
					return TRUE;
				}
				break;
			case 'sat':
				if ( $this->getSat() == TRUE ) {
					return TRUE;
				}
				break;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this premium policy.
			$udtlf = new UserDateTotalListFactory();
			$udtlf->getByPremiumTimePolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											('This premium policy is in use'));
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getBranchSelectionType() === FALSE ) {
			$this->setBranchSelectionType(10); //All
		}
		if ( $this->getDepartmentSelectionType() === FALSE ) {
			$this->setDepartmentSelectionType(10); //All
		}
		if ( $this->getJobGroupSelectionType() === FALSE ) {
			$this->setJobGroupSelectionType(10); //All
		}
		if ( $this->getJobSelectionType() === FALSE ) {
			$this->setJobSelectionType(10); //All
		}
		if ( $this->getJobItemGroupSelectionType() === FALSE ) {
			$this->setJobItemGroupSelectionType(10); //All
		}
		if ( $this->getJobItemSelectionType() === FALSE ) {
			$this->setJobItemSelectionType(10); //All
		}

		if ( $this->getWageGroup() === FALSE ) {
			$this->setWageGroup( 0 );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
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
						case 'start_time':
						case 'end_time':
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
						case 'type':
						case 'pay_type':
						case 'branch_selection_type':
						case 'department_selection_type':
						case 'job_group_selection_type':
						case 'job_selection_type':
						case 'job_item_group_selection_type':
						case 'job_item_selection_type':
							$function = 'get'. str_replace('_','', $variable);
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() );
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Premium Policy'), NULL, $this->getTable(), $this );
	}
}
?>
