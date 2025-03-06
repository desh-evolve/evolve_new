<?php

namespace App\Models\Leaves;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class AbsenceLeaveUserFactory extends Factory {
	protected $table = 'absence_leave_user';
	protected $pk_sequence_name = 'absence_leave_user_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => ('Enabled'),
										20 => ('Disabled'),
									);
				break;
			case 'type':
				$retval = array(
										10 => ('Tax'),
										20 => ('Deduction'),
										30 => ('Other'),
									);
				break;
			case 'calculation':
				$retval = array(
										10 => ('Percent'),
										15 => ('Advanced Percent'),
										17 => ('Advanced Percent (Range Bracket)'),
										18 => ('Advanced Percent (Tax Bracket)'),
										19 => ('Advanced Percent (Tax Bracket Alt.)'),
										20 => ('Fixed Amount'),
										30 => ('Fixed Amount (Range Bracket)'),

										//Accrual/YTD formulas. - This requires custom Withdraw From/Deposit To accrual feature in PS account.
										//50 => ('Accrual/YTD Percent'),
										52 => ('Fixed Amount (w/Target)'),

										//US - Custom Formulas
										80 => ('US - Advance EIC Formula'),

										//Canada - Custom Formulas CPP and EI
										90 => ('Canada - CPP Formula'),
										91 => ('Canada - EI Formula'),

										//Federal
										100 => ('Federal Income Tax Formula'),

										//Province/State
										200 => ('Province/State Income Tax Formula'),

										//Sub-State/Tax Area
										300 => ('District/County Income Tax Formula'),
									);
				break;
			case 'length_of_service_unit':
				$retval = array(
										10 => ('Day(s)'),
										20 => ('Week(s)'),
										30 => ('Month(s)'),
										40 => ('Year(s)'),
										50 => ('Hour(s)'),
									);
				break;
			case 'account_amount_type':
				$retval = array(
										10 => ('Amount'),
										20 => ('Units/Hours'),
										30 => ('YTD Amount'),
										40 => ('YTD Units/Hours'),
									);
				break;
			case 'us_eic_filing_status': //EIC certificate
				$retval = array(
														10 => ('Single or Head of Household'),
														20 => ('Married - Without Spouse Filing'),
														30 => ('Married - With Spouse Filing'),

									);
				break;
			case 'federal_filing_status': //US
				$retval = array(
														10 => ('Single'),
														20 => ('Married'),
									);
				break;
			case 'state_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married - Spouse Works'),
														30 => ('Married - Spouse does not Work'),
														40 => ('Head of Household'),
									);
				break;
			case 'state_ga_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married - Filing Separately'),
														30 => ('Married - Joint One Income'),
														40 => ('Married - Joint Two Incomes'),
														50 => ('Head of Household'),
									);
				break;
			case 'state_nj_filing_status':
				$retval = array(
														10 => ('Rate "A"'),
														20 => ('Rate "B"'),
														30 => ('Rate "C"'),
														40 => ('Rate "D"'),
														50 => ('Rate "E"'),
									);
				break;
			case 'state_nc_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married or Qualified Widow(er)'),
														30 => ('Head of Household'),
									);
				break;
			case 'state_ma_filing_status':
				$retval = array(
														10 => ('Regular'),
														20 => ('Head of Household'),
														30 => ('Blind'),
														40 => ('Head of Household and Blind')
									);
				break;
			case 'state_al_filing_status':
				$retval = array(
														10 => ('Status "S" Claiming $1500'),
														20 => ('Status "M" Claiming $3000'),
														30 => ('Status "0"'),
														40 => ('Head of Household'),
														50 => ('Status "MS"')
									);
				break;
			case 'state_ct_filing_status':
				$retval = array(
														10 => ('Status "A"'),
														20 => ('Status "B"'),
														30 => ('Status "C"'),
														40 => ('Status "D"'),
														//50 => ('Status "E"'), //Doesn't exist.
														60 => ('Status "F"'),
									);
				break;
			case 'state_wv_filing_status':
				$retval = array(
														10 => ('Standard'),
														20 => ('Optional Two Earners'),
									);
				break;
			case 'state_me_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married'),
														30 => ('Married with 2 incomes'),
									);
				break;
			case 'state_de_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married (Filing Jointly)'),
														30 => ('Married (Filing Separately)'),
									);
				break;
			case 'state_dc_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married (Filing Jointly)'),
														30 => ('Married (Filing Separately)'),
														40 => ('Head of Household'),
									);
				break;
			case 'state_la_filing_status':
				$retval = array(
														10 => ('Single'),
														20 => ('Married (Filing Jointly)'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => ('Status'),
										'-1020-type' => ('Type'),
										'-1030-name' => ('Name'),
										'-1040-calculation' => ('Calculation'),

										'-1040-start_date' => ('Start Date'),
										'-1040-end_Date_date' => ('End Date'),

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
								'status',
								'type',
								'name',
								'calculation',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'calculation_id',
								'country',
								'province',
								'district',
								'company_value1',
								'company_value2',
								'user_value1',
								'user_value2',
								'user_value3',
								'user_value4',
								'user_value5',
								'user_value6',
								'user_value7',
								'user_value8',
								'user_value9',
								'user_value10',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								'country',
								'province',
								'district'
								);
				break;
                /*case 'basis_employment':
				$retval = array(
										3 => ('Permanent (With Probation)'),
										4 => ('Permanent'),
										6 => ('Consultant'),
										1 => ('Contract'),
										2 => ('Assignment'),
									);
				break;*/
				case 'basis_employment':
				$retval = array(
								1 => ('Contract'),
								2 => ('Training'),
								3 => ('Permanent (With Probation)'),
								4 => ('Permanent (Confirmed)'),
								5 => ('Resign'),
							);
				break;
                case 'leave_applicable':
				$retval = array(
										//1 => ('Hire Date'),
										1 => ('Appointment Date'),
										2 => ('2nd Calender Year'), 
									);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'company_1id' => 'Company',
											'type_id' => 'Type',
											'type' => FALSE,
											'name' => 'Name',
											'rate' => 'Rate',
											'wage_group_id' => 'WageGroup',
											'accrual_rate' => 'AccrualRate',
											'accrual_policy_id' => 'AccrualPolicyID',
											'accrual_policy' => FALSE,
											'pay_stub_entry_account_id' => 'PayStubEntryAccountId',
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
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

	function isPaid() {
		if ( in_array( $this->getType(), $this->getOptions('paid_type') ) ) {
			return TRUE;
		}
		return FALSE;
	}
        
        function getStatus() {
		return (int)$this->data['status'];
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

			$this->data['status'] = $status;

			return FALSE;
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

	function geUserId() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUserId($id) {
		$id = trim($id);
                
		$uf = TTnew( 'UserListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'user_id',
											$uf->getById($id),
											('User is invalid'),
											2,50)
						) {

			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsenceLeaveId() {
		if ( isset($this->data['absence_leave_id']) ) {
			return $this->data['absence_leave_id'];
		}

		return FALSE;
	}
	function setAbsenceLeaveId($id) {
		$id = trim($id);
                
		$alf = TTnew( 'AbsenceLeaveListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'absence_leave_id',
											$alf->getById($id),
											('Absence Leave is invalid'),
											2,50)
						) {

			$this->data['absence_leave_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyId() {
		if ( isset($this->data['absence_policy_id']) ) {
			return $this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyId($id) {
		$id = trim($id);
                
		$alf = TTnew( 'AbsencePolicyListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'absence_policy_id',
											$alf->getById($id),
											('Absence Policy  is invalid'),
											2,50)
						) {

			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

        function getBasisEmployment() {
		return (int)$this->data['basis_employment'];
	}
	function setBasisEmployment($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('basis_employment') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'basis_employment',
											$status,
											('Incorrect Basis of Employment'),
											$this->getOptions('basis_employment')) ) {

			$this->data['basis_employment'] = $status;

			return FALSE;
		}

		return FALSE;
	}

        function getLeaveApplicable() {
		return (int)$this->data['leave_applicable'];
	}
	function setLeaveApplicable($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('leave_applicable') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'leave_applicable',
											$status,
											('Incorrect Basis of Employment'),
											$this->getOptions('leave_applicable')) ) {

			$this->data['leave_applicable'] = $status;

			return FALSE;
		}

		return FALSE;
	}
        
        
	function getMinimumLengthOfServiceDays() {
		if ( isset($this->data['minimum_length_of_service_days']) ) {
			return (int)$this->data['minimum_length_of_service_days'];
		}

		return FALSE;
	}
	function setMinimumLengthOfServiceDays($int) {
		$int = (int)trim($int);

		Debug::text('aLength of Service Days: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'minimum_length_of_service',
													$int,
													('Minimum length of service is invalid')) ) {

			$this->data['minimum_length_of_service_days'] = bcmul( $int, $this->length_of_service_multiplier[$this->getMinimumLengthOfServiceUnit()], 4);

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumLengthOfService() {
		if ( isset($this->data['minimum_length_of_service']) ) {
			return (int)$this->data['minimum_length_of_service'];
		}

		return FALSE;
	}
	function setMinimumLengthOfService($int) {
		$int = (int)trim($int);

		Debug::text('bLength of Service: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'minimum_length_of_service',
													$int,
													('Minimum length of service is invalid')) ) {

			$this->data['minimum_length_of_service'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumLengthOfServiceUnit() {
		if ( isset($this->data['minimum_length_of_service_unit_id']) ) {
			return $this->data['minimum_length_of_service_unit_id'];
		}

		return FALSE;
	}
	function setMinimumLengthOfServiceUnit($value) {
		$value = trim($value);

		if ( $value == ''
				OR $this->Validator->inArrayKey(	'minimum_length_of_service_unit_id',
											$value,
											('Incorrect minimum length of service unit'),
											$this->getOptions('length_of_service_unit')) ) {

			$this->data['minimum_length_of_service_unit_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumLengthOfServiceDays() {
		if ( isset($this->data['maximum_length_of_service_days']) ) {
			return (int)$this->data['maximum_length_of_service_days'];
		}

		return FALSE;
	}
	function setMaximumLengthOfServiceDays($int) {
		$int = (int)trim($int);

		Debug::text('aLength of Service Days: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'maximum_length_of_service',
													$int,
													('Maximum length of service is invalid')) ) {

			$this->data['maximum_length_of_service_days'] = bcmul( $int, $this->length_of_service_multiplier[$this->getMaximumLengthOfServiceUnit()], 4);

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumLengthOfService() {
		if ( isset($this->data['maximum_length_of_service']) ) {
			return (int)$this->data['maximum_length_of_service'];
		}

		return FALSE;
	}
	function setMaximumLengthOfService($int) {
		$int = (int)trim($int);

		Debug::text('bLength of Service: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'maximum_length_of_service',
													$int,
													('Maximum length of service is invalid')) ) {

			$this->data['maximum_length_of_service'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumLengthOfServiceUnit() {
		if ( isset($this->data['maximum_length_of_service_unit_id']) ) {
			return $this->data['maximum_length_of_service_unit_id'];
		}

		return FALSE;
	}
	function setMaximumLengthOfServiceUnit($value) {
		$value = trim($value);

		if ( $value == ''
				OR $this->Validator->inArrayKey(	'maximum_length_of_service_unit_id',
											$value,
											('Incorrect maximum length of service unit'),
											$this->getOptions('length_of_service_unit')) ) {

			$this->data['maximum_length_of_service_unit_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

        
	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
	function setAmount($amount) {
		$amount = trim($amount);
                        
		if (	$this->Validator->isFloat(	'amount',
                                                $amount,
                                                ('Leave day is invalid') )
						) {

			$this->data['amount'] = $amount;

			return TRUE;
		}

		return FALSE;
	}
        
        
	function getLeaveDateYear() {
		if ( isset($this->data['leave_date_year']) ) {
			return $this->data['leave_date_year'];
		}

		return FALSE;
	}
	function setLeaveDateYear($amount) {
		$amount = trim($amount);
                        
		if (	$this->Validator->isNotNull(	'leave_date_year',
                                                $amount,
                                                ('Leave Date is invalid') )
						) {

			$this->data['leave_date_year'] = $amount;

			return TRUE;
		}

		return FALSE;
	}
        
        function getUser() {
                        $udlf = TTnew( 'AbsenceLeaveUserEntryListFactory' );
                        $udlf->getByAbsenceUserId( $this->getId() );
                        foreach ($udlf as $obj) {
                                $list[$obj->getUserId()] = array('user_id' => $obj->getUserId(),'id'=>$obj->getId());
                        }

                        if ( isset($list) ) {
                                return $list;
                        }

                        return FALSE;
                }
        
        function SetUser() {
                        $udlf = TTnew( 'AbsenceLeaveUserEntryListFactory' );
                        $udlf->getByAbsenceUserId( $this->getId() );
                        foreach ($udlf as $obj) {
                                $list[] = $obj->getUserId();
                        }

                        if ( isset($list) ) {
                                return $list;
                        }

                        return FALSE;
                }
        
	function getLengthServiceToSec( $lngt, $l_unit ) {
            $retVal = null;
            switch ($l_unit){
                case '10': //day
                    $retVal = $lngt*24*60*60;
                    break;
                case '20': //week
                    $retVal = $lngt*7*24*60*60;
                    break;
                case '30': //month
                    $retVal = $lngt*30*24*60*60;
                    break;
                case '20': //year
                    $retVal = $lngt*365*24*60*60;
                    break;
                case '20': //hour
                    $retVal = $lngt*24;
                    break;
                default :
                    return FALSE;
            }
            
            return $retVal; 
	}
        
	function getHourlyRate( $hourly_rate ) {
		if ( $this->getType() == 20 ) { //Unpaid
			$rate = 0;
		} elseif( $this->getType() == 30 ) { //Dock
			$rate = $this->getRate()*-1;
		} else {
			$rate = $this->getRate();
		}
		return bcmul( $hourly_rate, $rate );
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

		if (	$id == NULL
				OR
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
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this OT policy.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByAbsencePolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											('This absence policy is in use'));

			}
		}

		return TRUE;
	}

//	function preSave() {
//		if ( $this->getWageGroup() === FALSE ) {
////			$this->setWageGroup( 0 );
//		}
//
//		return TRUE;
//	}

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
						case 'accrual_policy':
							$data[$variable] = $this->getColumn( $variable );
							break;
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Absence Policy'), NULL, $this->getTable(), $this );
	}
}
?>
