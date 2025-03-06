<?php

namespace App\Models\Policy;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class OverTimePolicyFactory extends Factory {
	protected $table = 'over_time_policy';
	protected $pk_sequence_name = 'over_time_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	//Use the ordering of Type_ID
	//We basically convert all types to Daily OT prior to calculation.
	//Daily time always takes precedence, because more then 12hrs in a day deserves double time.
	//Then Weekly time
	//Then Bi Weekly
	//Then Day Of Week

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Daily'),
										20 => ('Weekly'),
										30 => ('Bi-Weekly'), //Need to recalculate two weeks ahead, instead of just one.
										40 => ('Sunday'),
										50 => ('Monday'),
										60 => ('Tuesday'),
										70 => ('Wednesday'),
										80 => ('Thursday'),
										90 => ('Friday'),
										100 => ('Saturday'),
										150 => ('2 Or More Days Consecutively Worked'),
										151 => ('3 Or More Days Consecutively Worked'),
										152 => ('4 Or More Days Consecutively Worked'),
										153 => ('5 Or More Days Consecutively Worked'),
										154 => ('6 Or More Days Consecutively Worked'),
										155 => ('7 Or More Days Consecutively Worked'),
										180 => ('Poya Holiday'),
                                                                                190 => ('Statutory Holiday'),
										200 => ('Over Schedule (Daily) / No Schedule'),
										210 => ('Over Schedule (Weekly) / No Schedule')
									);
				break;
			case 'calculation_order':
				$retval = array(
										10 => 90, //Daily
										20 => 200, //Weekly
										30 => 300, //Bi-Weekly
										40 => 20, //Sunday
										50 => 30, //Monday
										60 => 40, //Tuesday
										70 => 50, //Wednesday
										80 => 60, //Thursday
										90 => 70, //Friday
										100 => 80, //Saturday
										150 => 86, //After 2-Days Consecutive Worked
										151 => 85, //After 3-Days Consecutive Worked
										152 => 84, //After 4-Days Consecutive Worked
										153 => 83, //After 5-Days Consecutive Worked
										154 => 82, //After 6-Days Consecutive Worked
										155 => 81, //After 7-Days Consecutive Worked
										180 => 5, //Poya Holiday
                                                                                190 => 10, //S.Holiday        
										200 => 100, //Over Schedule (Daily) / No Schedule
										210 => 210, //Over Schedule (Weekly) / No Schedule
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1020-name' => ('Name'),

										'-1030-trigger_time' => ('Active After'),
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
								'type',
								'name',
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
										'trigger_time' => 'TriggerTime',
										'rate' => 'Rate',
										'wage_group_id' => 'WageGroup',
										'accrual_rate' => 'AccrualRate',
										'accrual_policy_id' => 'AccrualPolicyID',
										'pay_stub_entry_account_id' => 'PayStubEntryAccountId',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}
        
        function getId() {
		if ( isset($this->data['id']) ) {
			return $this->data['id'];
		}

		return FALSE;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
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
		$clf = TTnew( 'CompanyListFactory' );

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

			return FALSE;
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

	function getTriggerTime() {
		if ( isset($this->data['trigger_time']) ) {
			return (int)$this->data['trigger_time'];
		}

		return FALSE;
	}
	function setTriggerTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'trigger_time',
													$int,
													('Incorrect Trigger Time')) ) {
			$this->data['trigger_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
        //thilini for aqua fresh 2018-03-19
        function getMaxTime() {
		if ( isset($this->data['max_time']) ) {
			return (int)$this->data['max_time'];
		}

		return FALSE;
	}
        
        function setMaxTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if ($this->Validator->isNumeric('max_time',
			$int,
			('Incorrect Max Time')) ) {
			$this->data['max_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
        //end

	function getHourlyRate( $hourly_rate ) {
		return bcmul( $hourly_rate, $this->getRate() );
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

	function getWageGroup() {
		if ( isset($this->data['wage_group_id']) ) {
			return $this->data['wage_group_id'];
		}

		return FALSE;
	}
	function setWageGroup($id) {
		$id = trim($id);

		$wglf = TTnew( 'WageGroupListFactory' );

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

		$aplf = TTnew( 'AccrualPolicyListFactory' );

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

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );

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

	function Validate() {
		if ( $this->getDeleted() == TRUE ){
			//Check to make sure there are no hours using this OT policy.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByOverTimePolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											('This overtime policy is in use'));

			}
		}

		return TRUE;
	}

	function preSave() {
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
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('OverTime Policy'), NULL, $this->getTable(), $this );
	}
}
?>
