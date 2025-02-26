<?php

namespace App\Models\Leaves;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class AbsenceLeaveUserEntryFactory extends Factory {
	protected $table = 'absence_leave_user_entry';
	protected $pk_sequence_name = 'absence_leave_user_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('Enabled'),
										20 => TTi18n::gettext('Disabled'),
									);
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Tax'),
										20 => TTi18n::gettext('Deduction'),
										30 => TTi18n::gettext('Other'),
									);
				break;
			case 'calculation':
				$retval = array(
										10 => TTi18n::gettext('Percent'),
										15 => TTi18n::gettext('Advanced Percent'),
										17 => TTi18n::gettext('Advanced Percent (Range Bracket)'),
										18 => TTi18n::gettext('Advanced Percent (Tax Bracket)'),
										19 => TTi18n::gettext('Advanced Percent (Tax Bracket Alt.)'),
										20 => TTi18n::gettext('Fixed Amount'),
										30 => TTi18n::gettext('Fixed Amount (Range Bracket)'),

										//Accrual/YTD formulas. - This requires custom Withdraw From/Deposit To accrual feature in PS account.
										//50 => TTi18n::gettext('Accrual/YTD Percent'),
										52 => TTi18n::gettext('Fixed Amount (w/Target)'),

										//US - Custom Formulas
										80 => TTi18n::gettext('US - Advance EIC Formula'),

										//Canada - Custom Formulas CPP and EI
										90 => TTi18n::gettext('Canada - CPP Formula'),
										91 => TTi18n::gettext('Canada - EI Formula'),

										//Federal
										100 => TTi18n::gettext('Federal Income Tax Formula'),

										//Province/State
										200 => TTi18n::gettext('Province/State Income Tax Formula'),

										//Sub-State/Tax Area
										300 => TTi18n::gettext('District/County Income Tax Formula'),
									);
				break;
			case 'length_of_service_unit':
				$retval = array(
										10 => TTi18n::gettext('Day(s)'),
										20 => TTi18n::gettext('Week(s)'),
										30 => TTi18n::gettext('Month(s)'),
										40 => TTi18n::gettext('Year(s)'),
										50 => TTi18n::gettext('Hour(s)'),
									);
				break;
			case 'account_amount_type':
				$retval = array(
										10 => TTi18n::gettext('Amount'),
										20 => TTi18n::gettext('Units/Hours'),
										30 => TTi18n::gettext('YTD Amount'),
										40 => TTi18n::gettext('YTD Units/Hours'),
									);
				break;
			case 'us_eic_filing_status': //EIC certificate
				$retval = array(
														10 => TTi18n::gettext('Single or Head of Household'),
														20 => TTi18n::gettext('Married - Without Spouse Filing'),
														30 => TTi18n::gettext('Married - With Spouse Filing'),

									);
				break;
			case 'federal_filing_status': //US
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married'),
									);
				break;
			case 'state_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married - Spouse Works'),
														30 => TTi18n::gettext('Married - Spouse does not Work'),
														40 => TTi18n::gettext('Head of Household'),
									);
				break;
			case 'state_ga_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married - Filing Separately'),
														30 => TTi18n::gettext('Married - Joint One Income'),
														40 => TTi18n::gettext('Married - Joint Two Incomes'),
														50 => TTi18n::gettext('Head of Household'),
									);
				break;
			case 'state_nj_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Rate "A"'),
														20 => TTi18n::gettext('Rate "B"'),
														30 => TTi18n::gettext('Rate "C"'),
														40 => TTi18n::gettext('Rate "D"'),
														50 => TTi18n::gettext('Rate "E"'),
									);
				break;
			case 'state_nc_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married or Qualified Widow(er)'),
														30 => TTi18n::gettext('Head of Household'),
									);
				break;
			case 'state_ma_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Regular'),
														20 => TTi18n::gettext('Head of Household'),
														30 => TTi18n::gettext('Blind'),
														40 => TTi18n::gettext('Head of Household and Blind')
									);
				break;
			case 'state_al_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Status "S" Claiming $1500'),
														20 => TTi18n::gettext('Status "M" Claiming $3000'),
														30 => TTi18n::gettext('Status "0"'),
														40 => TTi18n::gettext('Head of Household'),
														50 => TTi18n::gettext('Status "MS"')
									);
				break;
			case 'state_ct_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Status "A"'),
														20 => TTi18n::gettext('Status "B"'),
														30 => TTi18n::gettext('Status "C"'),
														40 => TTi18n::gettext('Status "D"'),
														//50 => TTi18n::gettext('Status "E"'), //Doesn't exist.
														60 => TTi18n::gettext('Status "F"'),
									);
				break;
			case 'state_wv_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Standard'),
														20 => TTi18n::gettext('Optional Two Earners'),
									);
				break;
			case 'state_me_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married'),
														30 => TTi18n::gettext('Married with 2 incomes'),
									);
				break;
			case 'state_de_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married (Filing Jointly)'),
														30 => TTi18n::gettext('Married (Filing Separately)'),
									);
				break;
			case 'state_dc_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married (Filing Jointly)'),
														30 => TTi18n::gettext('Married (Filing Separately)'),
														40 => TTi18n::gettext('Head of Household'),
									);
				break;
			case 'state_la_filing_status':
				$retval = array(
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married (Filing Jointly)'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-type' => TTi18n::gettext('Type'),
										'-1030-name' => TTi18n::gettext('Name'),
										'-1040-calculation' => TTi18n::gettext('Calculation'),

										'-1040-start_date' => TTi18n::gettext('Start Date'),
										'-1040-end_Date_date' => TTi18n::gettext('End Date'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
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
                        case 'basis_employment':
				$retval = array(
										3 => TTi18n::gettext('Permanent (With Probation)'),
										4 => TTi18n::gettext('Permanent'),
										6 => TTi18n::gettext('Consultant'),
										1 => TTi18n::gettext('Contract'),
										2 => TTi18n::gettext('Assignment'),
									);
				break;
                        case 'leave_applicable':
				$retval = array(
										//1 => TTi18n::gettext('Hire Date'),
										1 => TTi18n::gettext('Appointment Date'),
										2 => TTi18n::gettext('2nd Calender Year'), 
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
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

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
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status'] = $status;

			return FALSE;
		}

		return FALSE;
	}
        
        
	function getAbsenceLeaveUserId() {
		if ( isset($this->data['absence_leave_user_id']) ) {
			return $this->data['absence_leave_user_id'];
		}

		return FALSE;
	}
	function setAbsenceLeaveUserId($id) {
		$id = trim($id);
                
		$alul = TTnew( 'AbsenceLeaveUserListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'absence_leave_user_id',
											$alul->getById($id),
											TTi18n::gettext('invalid absence uleave user id'),
											2,50)
						) {

			$this->data['absence_leave_user_id'] = $id;

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
											TTi18n::gettext('Name is invalid'),
											2,50)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getUserId() {
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
											TTi18n::gettext('User is invalid'),
											2,50)
						) {

			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
	function setDeletedInverse() { 
		$this->data['deleted'] = 0;
		$this->data['deleted_by'] = 0;
		$this->data['deleted_date'] = 0;

		return true;  
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
											TTi18n::gettext('Absence Leave is invalid'),
											2,50)
						) {

			$this->data['absence_leave_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePlicyId() {
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
											TTi18n::gettext('Absence Policy  is invalid'),
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
											TTi18n::gettext('Incorrect Basis of Employment'),
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
											TTi18n::gettext('Incorrect Basis of Employment'),
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
													TTi18n::gettext('Minimum length of service is invalid')) ) {

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
													TTi18n::gettext('Minimum length of service is invalid')) ) {

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
											TTi18n::gettext('Incorrect minimum length of service unit'),
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
													TTi18n::gettext('Maximum length of service is invalid')) ) {

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
													TTi18n::gettext('Maximum length of service is invalid')) ) {

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
											TTi18n::gettext('Incorrect maximum length of service unit'),
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
                                                TTi18n::gettext('Amount is invalid') )
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
                                                TTi18n::gettext('Leave Date is invalid') )
						) {

			$this->data['leave_date_year'] = $amount;

			return TRUE;
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
												TTi18n::gettext('Incorrect Rate')) ) {
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
													TTi18n::gettext('Wage Group is invalid')
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
												TTi18n::gettext('Incorrect Accrual Rate')) ) {
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
													TTi18n::gettext('Accrual Policy is invalid')
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
														TTi18n::gettext('Invalid Pay Stub Account')
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
											TTi18n::gettext('This absence policy is in use'));

			}
		}

		return TRUE;
	}

//	function preSave() {
//		if ( $this->getWageGroup() === FALSE ) {
//			$this->setWageGroup( 0 );
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
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Absence Policy'), NULL, $this->getTable(), $this );
	}
}
?>
