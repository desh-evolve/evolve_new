<?php

namespace App\Models\Company;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Users\UserDeductionFactory;
use App\Models\Users\UserListFactory;

use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Core\Environment;
use App\Models\Core\TTLog;

use App\Models\Core\TTDate;
use Illuminate\Support\Facades\DB;

use DateTime;

class CompanyDeductionFactory extends Factory {
	protected $table = 'company_deduction';
	protected $pk_sequence_name = 'company_deduction_id_seq'; //PK Sequence name

	var $pay_stub_entry_account_link_obj = NULL;
	var $pay_stub_entry_account_obj = NULL;

	var $country_calculation_ids = array('100','200','300');
	var	$province_calculation_ids = array('200','300');
	var $district_calculation_ids = array('300');
	var $calculation_id_fields = array(
										'10' => '10',
										'15' => '15',
										'17' => '17',
										'18' => '18',
										'19' => '19',
										'20' => '20',
										'30' => '30',

										'52' => '52',

										'80' => '80',

										'100' => '',
										'100-CA' => '100-CA',
										'100-US' => '100-US',
										'100-CR' => '100-CR',

										'200' => '',
										'200-CA-BC' => '200-CA',
										'200-CA-AB' => '200-CA',
										'200-CA-SK' => '200-CA',
										'200-CA-MB' => '200-CA',
										'200-CA-QC' => '200-CA',
										'200-CA-ON' => '200-CA',
										'200-CA-NL' => '200-CA',
										'200-CA-NB' => '200-CA',
										'200-CA-NS' => '200-CA',
										'200-CA-PE' => '200-CA',
										'200-CA-NT' => '200-CA',
										'200-CA-YT' => '200-CA',
										'200-CA-NU' => '200-CA',

										'200-US-AL' => '200-US-AL',
										'200-US-AK' => '',
										'200-US-AZ' => '200-US-AZ',
										'200-US-AR' => '200-US-OH',
										'200-US-CA' => '200-US',
										'200-US-CO' => '200-US-WI',
										'200-US-CT' => '200-US-CT',
										'200-US-DE' => '200-US-DE',
										'200-US-DC' => '200-US-DC',
										'200-US-FL' => '',
										'200-US-GA' => '200-US-GA',
										'200-US-HI' => '200-US-WI',
										'200-US-ID' => '200-US-WI',
										'200-US-IL' => '200-US-IL',
										'200-US-IN' => '200-US-IN',
										'200-US-IA' => '200-US-OH',
										'200-US-KS' => '200-US-WI',
										'200-US-KY' => '200-US-OH',
										'200-US-LA' => '200-US-LA',
										'200-US-ME' => '200-US-ME',
										'200-US-MD' => '200-US-MD', //Has district taxes too
										'200-US-MA' => '200-US-MA',
										'200-US-MI' => '200-US-OH',
										'200-US-MN' => '200-US-WI',
										'200-US-MS' => '200-US',
										'200-US-MO' => '200-US',
										'200-US-MT' => '200-US-OH',
										'200-US-NE' => '200-US-WI',
										'200-US-NV' => '',
										'200-US-NH' => '',
										'200-US-NM' => '200-US-WI',
										'200-US-NJ' => '200-US-NJ',
										'200-US-NY' => '200-US',
										'200-US-NC' => '200-US-NC',
										'200-US-ND' => '200-US-WI',
										'200-US-OH' => '200-US-OH',
										'200-US-OK' => '200-US-WI',
										'200-US-OR' => '200-US-WI',
										'200-US-PA' => '200-US-PA',
										'200-US-RI' => '200-US-WI',
										'200-US-SC' => '200-US-OH',
										'200-US-SD' => '',
										'200-US-TN' => '',
										'200-US-TX' => '',
										'200-US-UT' => '200-US-WI',
										'200-US-VT' => '200-US-WI',
										'200-US-VA' => '200-US-VA',
										'200-US-WA' => '',
										'200-US-WV' => '200-US-WV',
										'200-US-WI' => '200-US-WI',
										'200-US-WY' => '',

										'300-US-AL' => '300-US-PERCENT',
										'300-US-AK' => '300-US-PERCENT',
										'300-US-AZ' => '300-US-PERCENT',
										'300-US-AR' => '300-US-PERCENT',
										'300-US-CA' => '300-US-PERCENT',
										'300-US-CO' => '300-US-PERCENT',
										'300-US-CT' => '300-US-PERCENT',
										'300-US-DE' => '300-US-PERCENT',
										'300-US-DC' => '300-US-PERCENT',
										'300-US-FL' => '300-US-PERCENT',
										'300-US-GA' => '300-US-PERCENT',
										'300-US-HI' => '300-US-PERCENT',
										'300-US-ID' => '300-US-PERCENT',
										'300-US-IL' => '300-US-PERCENT',
										'300-US-IN' => '300-US-IN',
										'300-US-IA' => '300-US-PERCENT',
										'300-US-KS' => '300-US-PERCENT',
										'300-US-KY' => '300-US-PERCENT',
										'300-US-LA' => '300-US-PERCENT',
										'300-US-ME' => '300-US-PERCENT',
										'300-US-MD' => '300-US-MD',
										'300-US-MA' => '300-US-PERCENT',
										'300-US-MI' => '300-US-PERCENT',
										'300-US-MN' => '300-US-PERCENT',
										'300-US-MS' => '300-US-PERCENT',
										'300-US-MO' => '300-US-PERCENT',
										'300-US-MT' => '300-US-PERCENT',
										'300-US-NE' => '300-US-PERCENT',
										'300-US-NV' => '300-US-PERCENT',
										'300-US-NH' => '300-US-PERCENT',
										'300-US-NM' => '300-US-PERCENT',
										'300-US-NJ' => '300-US-PERCENT',
										'300-US-NY' => '300-US',
										'300-US-NC' => '300-US-PERCENT',
										'300-US-ND' => '300-US-PERCENT',
										'300-US-OH' => '300-US-PERCENT',
										'300-US-OK' => '300-US-PERCENT',
										'300-US-OR' => '300-US-PERCENT',
										'300-US-PA' => '300-US-PERCENT',
										'300-US-RI' => '300-US-PERCENT',
										'300-US-SC' => '300-US-PERCENT',
										'300-US-SD' => '300-US-PERCENT',
										'300-US-TN' => '300-US-PERCENT',
										'300-US-TX' => '300-US-PERCENT',
										'300-US-UT' => '300-US-PERCENT',
										'300-US-VT' => '300-US-PERCENT',
										'300-US-VA' => '300-US-PERCENT',
										'300-US-WA' => '300-US-PERCENT',
										'300-US-WV' => '300-US-PERCENT',
										'300-US-WI' => '300-US-PERCENT',
										'300-US-WY' => '300-US-PERCENT',
										);

	protected $length_of_service_multiplier = array(
										0 => 0,
										10 => 1,
										20 => 7,
										30 => 30.4167,
										40 => 365.25,
										50 => 0.04166666666666666667, //1/24th of a day.
									);

	protected $account_amount_type_map = array(
										10 => 'amount',
										20 => 'units',
										30 => 'ytd_amount',
										40 => 'ytd_units',
									);

	protected $account_amount_type_ps_entries_map = array(
										10 => 'current',
										20 => 'current',
										30 => 'previous+ytd_adjustment',
										40 => 'previous+ytd_adjustment',
									);

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
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
										'start_date' => 'StartDate',
										'end_date' => 'EndDate',
										'minimum_length_of_service_unit_id' => 'MinimumLengthOfServiceUnit', //Must go before minimum_length_of_service_days, for calculations to not fail.
										'minimum_length_of_service_days' => 'MinimumLengthOfServiceDays',
										'minimum_length_of_service' => 'MinimumLengthOfService',
										'maximum_length_of_service_unit_id' => 'MaximumLengthOfServiceUnit', //Must go before maximum_length_of_service_days, for calculations to not fail.
										'maximum_length_of_service_days' => 'MaximumLengthOfServiceDays',
										'maximum_length_of_service' => 'MaximumLengthOfService',
										'minimum_user_age' => 'MinimumUserAge',
										'maximum_user_age' => 'MaximumUserAge',
										'calculation_id' => 'Calculation',
										'calculation' => FALSE,
										'calculation_order' => 'CalculationOrder',
										'country' => 'Country',
										'province' => 'Province',
										'district' => 'District',
										'company_value1' => 'CompanyValue1',
										'company_value2' => 'CompanyValue2',
										'user_value1' => 'UserValue1',
										'user_value2' => 'UserValue2',
										'user_value3' => 'UserValue3',
										'user_value4' => 'UserValue4',
										'user_value5' => 'UserValue5',
										'user_value6' => 'UserValue6',
										'user_value7' => 'UserValue7',
										'user_value8' => 'UserValue8',
										'user_value9' => 'UserValue9',
										'user_value10' => 'UserValue10',
										'pay_stub_entry_account_id' => 'PayStubEntryAccount',
										'lock_user_value1' => 'LockUserValue1',
										'lock_user_value2' => 'LockUserValue2',
										'lock_user_value3' => 'LockUserValue3',
										'lock_user_value4' => 'LockUserValue4',
										'lock_user_value5' => 'LockUserValue5',
										'lock_user_value6' => 'LockUserValue6',
										'lock_user_value7' => 'LockUserValue7',
										'lock_user_value8' => 'LockUserValue8',
										'lock_user_value9' => 'LockUserValue9',
										'lock_user_value10' => 'LockUserValue10',
										'include_account_amount_type_id' => 'IncludeAccountAmountType',
										'include_pay_stub_entry_account' => 'IncludePayStubEntryAccount',
										'exclude_account_amount_type_id' => 'ExcludeAccountAmountType',
										'exclude_pay_stub_entry_account' => 'ExcludePayStubEntryAccount',
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = new PayStubEntryAccountLinkListFactory();
			$pseallf->getByCompanyId( $this->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryAccountObject() {
		if ( is_object($this->pay_stub_entry_account_obj) ) {
			return $this->pay_stub_entry_account_obj;
		} else {
			$psealf = new PayStubEntryAccountListFactory();
			$psealf->getById( $this->getPayStubEntryAccount() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_obj = $psealf->getCurrent();
				return $this->pay_stub_entry_account_obj;
			}

			return FALSE;
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

	function getStatus() {
		return (int)$this->data['status_id'];
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

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
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

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					':company_id' => $this->getCompany(),
					':name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = :company_id AND  name = :name AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
        $id = DB::selectOne($query, $ph);

        if (!$id) {
            $id = 0;
        } else {
            $id = current(get_object_vars($id));
        }

		Debug::Arr($id,'Unique Pay Stub Account: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($id) || $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
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
	function setName($value) {
		$value = trim($value);

		if 	(
					$this->Validator->isLength(		'name',
													$value,
													('Name is too short or too long'),
													2,
													100)
				AND
				$this->Validator->isTrue(				'name',
														$this->isUniqueName($value),
														('Name is already in use')
													)
													) {

			$this->data['name'] = $value;

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
	// function setStartDate($epoch) {
	// 	dd($epoch);
	// 	$epoch = trim($epoch);

	// 	if ( $epoch == '' ){
	// 		$epoch = NULL;
	// 	}

	// 	if 	(
	// 			$epoch == NULL
	// 			OR
	// 			$this->Validator->isDate(		'start_date',
	// 											$epoch,
	// 											('Incorrect start date'))
	// 		) {

	// 		$this->data['start_date'] = $epoch;

	// 		return TRUE;
	// 	}

	// 	return FALSE;
	// }

	function setStartDate($epoch) {
    // Trim whitespace (if input is a string)
    $epoch = is_string($epoch) ? trim($epoch) : $epoch;

    // Handle empty values
    if ($epoch === '' || $epoch === null) {
        $this->data['start_date'] = null;
        return true;
    }

    // If already a Unix timestamp (numeric), convert to MySQL datetime
    if (is_numeric($epoch)) {
        $mysqlDate = date('Y-m-d H:i:s', $epoch);
        $this->data['start_date'] = $mysqlDate;
        return true;
    }

    // If it's a date string, validate and format for MySQL
    try {
        $dateTime = new DateTime($epoch);
        $mysqlDate = $dateTime->format('Y-m-d H:i:s');
        $this->data['start_date'] = $mysqlDate;
        return true;
    } catch (Exception $e) {
        $this->Validator->Error('start_date', 'Incorrect start date');
        return false;
    }
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
	// function setEndDate($epoch) {
	// 	$epoch = trim($epoch);

	// 	if ( $epoch == '' ){
	// 		$epoch = NULL;
	// 	}

	// 	if 	(	$epoch == NULL
	// 			OR
	// 			$this->Validator->isDate(		'end_date',
	// 											$epoch,
	// 											('Incorrect end date'))
	// 		) {

	// 		$this->data['end_date'] = $epoch;

	// 		return TRUE;
	// 	}

	// 	return FALSE;
	// }

	function setEndDate($epoch) {
    // Trim whitespace (if input is a string)
    $epoch = is_string($epoch) ? trim($epoch) : $epoch;

    // Handle empty values
    if ($epoch === '' || $epoch === null) {
        $this->data['end_date'] = null;
        return true;
    }

    // If already a Unix timestamp (numeric), convert to MySQL datetime
    if (is_numeric($epoch)) {
        $mysqlDate = date('Y-m-d H:i:s', $epoch);
        $this->data['end_date'] = $mysqlDate;
        return true;
    }

    // If it's a date string, validate and format for MySQL
    try {
        $dateTime = new DateTime($epoch);
        $mysqlDate = $dateTime->format('Y-m-d H:i:s');
        $this->data['end_date'] = $mysqlDate;
        return true;
    } catch (Exception $e) {
        $this->Validator->Error('end_date', 'Incorrect end date');
        return false;
    }
}

	//Check if this date is within the effective date range
	function isActiveDate( $epoch ) {
		$epoch = TTDate::getBeginDayEpoch( $epoch );

		if ( $this->getStartDate() == '' AND $this->getEndDate() == '' ) {
			return TRUE;
		}

		if ( $epoch >= (int)$this->getStartDate()
				AND ( $epoch <= (int)$this->getEndDate() OR $this->getEndDate() == '' ) ) {
			Debug::text('Within Start/End Date.', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('Outside Start/End Date.', __FILE__, __LINE__, __METHOD__, 10);

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

	function getMinimumUserAge() {
		if ( isset($this->data['minimum_user_age']) ) {
			return (float)$this->data['minimum_user_age'];
		}

		return FALSE;
	}
	function setMinimumUserAge($int) {
		$int = (float)trim($int);

		Debug::text('Minimum User Age: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'minimum_user_age',
													$int,
													('Minimum employee age is invalid')) ) {

			$this->data['minimum_user_age'] = $int;

			return TRUE;
		}

		return FALSE;
	}



        function setBasisOfEmployment($int) {

            $int= (int)$int;

            if 	($int>0){

			$this->data['basis_of_employment']=$int;
                        return TRUE;
            }


		return FALSE;
	}


        function getBasisOfEmployment() {
		if ( isset($this->data['basis_of_employment']) ) {
			return (float)$this->data['basis_of_employment'];
		}

		return FALSE;
	}

	function getMaximumUserAge() {
		if ( isset($this->data['maximum_user_age']) ) {
			return (float)$this->data['maximum_user_age'];
		}

		return FALSE;
	}
	function setMaximumUserAge($int) {
		$int = (float)trim($int);

		Debug::text('Maximum User Age: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'maximum_user_age',
													$int,
													('Maximum employee age is invalid')) ) {

			$this->data['maximum_user_age'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkedTimeByUserIdAndEndDate( $user_id, $end_date = NULL ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udtlf = new UserDateTotalListFactory();
		$retval = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_id, 1, $end_date );

		Debug::Text('Worked Seconds: '. (int)$retval .' Before: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__,10);

		return $retval;
	}

	function isActiveLengthOfService( $u_obj, $epoch ) {
		if ( $this->getMinimumLengthOfServiceUnit() == 50 OR $this->getMaximumLengthOfServiceUnit() == 50 ) {
			//Hour based length of service, get users hours up until this period.
			$worked_time = TTDate::getHours( $this->getWorkedTimeByUserIdAndEndDate( $u_obj->getId(), $epoch ) );
			Debug::Text('&nbsp;&nbsp;Worked Time: '. $worked_time .'hrs', __FILE__, __LINE__, __METHOD__,10);
		}

		$employed_days = TTDate::getDays( ($epoch-$u_obj->getHireDate()) );
		Debug::Text('&nbsp;&nbsp;Employed Days: '. $employed_days, __FILE__, __LINE__, __METHOD__,10);

		$minimum_length_of_service_result = FALSE;
		$maximum_length_of_service_result = FALSE;
		//Check minimum length of service
		if ( $this->getMinimumLengthOfService() == 0
				OR ( $this->getMinimumLengthOfServiceUnit() == 50 AND $worked_time >= $this->getMinimumLengthOfService() )
				OR ( $this->getMinimumLengthOfServiceUnit() != 50 AND $employed_days >= $this->getMinimumLengthOfServiceDays() ) ) {
			$minimum_length_of_service_result = TRUE;
		}

		//Check maximum length of service.
		if ( $this->getMaximumLengthOfService() == 0
				OR ( $this->getMaximumLengthOfServiceUnit() == 50 AND $worked_time <= $this->getMaximumLengthOfService() )
				OR ( $this->getMaximumLengthOfServiceUnit() != 50 AND $employed_days <= $this->getMaximumLengthOfServiceDays() ) ) {
			$maximum_length_of_service_result = TRUE;
		}

		Debug::Text('&nbsp;&nbsp; Min Result: : '. (int)$minimum_length_of_service_result .' Max Result: '. (int)$maximum_length_of_service_result, __FILE__, __LINE__, __METHOD__,10);

		if ( $minimum_length_of_service_result == TRUE AND $maximum_length_of_service_result == TRUE ) {
			return TRUE;
		}

		return FALSE;
	}

	function isActiveUserAge( $u_obj, $epoch ) {
		$user_age = TTDate::getYearDifference( $u_obj->getBirthDate(), $epoch );
		Debug::Text('User Age: '. $user_age .' Min: '. $this->getMinimumUserAge() .' Max: '. $this->getMaximumUserAge(), __FILE__, __LINE__, __METHOD__,10);

		if ( ( $this->getMinimumUserAge() == 0 OR $user_age >= $this->getMinimumUserAge() ) AND ( $this->getMaximumUserAge() == 0 OR $user_age <= $this->getMaximumUserAge() ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isCountryCalculationID( $calculation_id ) {
		if ( in_array($calculation_id, $this->country_calculation_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}
	function isProvinceCalculationID( $calculation_id ) {
		if ( in_array($calculation_id, $this->province_calculation_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}
	function isDistrictCalculationID( $calculation_id ) {
		if ( in_array($calculation_id, $this->district_calculation_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}


	function getCombinedCalculationID( $calculation_id = NULL, $country = NULL, $province = NULL ) {
		if ( $calculation_id == '' ) {
			$calculation_id = $this->getCalculation();
		}

		if ( $country == '' ) {
			$country = $this->getCountry();
		}

		if ( $province == '' ) {
			$province = $this->getProvince();
		}

		Debug::Text('Calculation ID: '. $calculation_id .' Country: '. $country .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

		if ( in_array($calculation_id , $this->country_calculation_ids )
				AND in_array($calculation_id, $this->province_calculation_ids ) ) {
			$id = $calculation_id.'-'.$country.'-'.$province;
		} elseif ( in_array($calculation_id, $this->country_calculation_ids ) ) {
			$id = $calculation_id.'-'.$country;
		} else {
			$id = $calculation_id;
		}

		if ( isset($this->calculation_id_fields[$id]) ) {
			$retval = $this->calculation_id_fields[$id];
		} else {
			$retval = FALSE;
		}

		Debug::Text('Retval: '. $retval, __FILE__, __LINE__, __METHOD__,10);

		return $retval;
	}
	function getCalculation() {
		if ( isset($this->data['calculation_id']) ) {
			return $this->data['calculation_id'];
		}

		return FALSE;
	}
	function setCalculation($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('calculation') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'calculation',
											$value,
											('Incorrect Calculation'),
											$this->getOptions('calculation')) ) {

			$this->data['calculation_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getCalculationOrder() {
		if ( isset($this->data['calculation_order']) ) {
			return $this->data['calculation_order'];
		}

		return FALSE;
	}
	function setCalculationOrder($value) {
		$value = trim($value);

		if ( $this->Validator->isNumeric(		'calculation_order',
												$value,
												('Invalid Calculation Order')
										) ) {


			$this->data['calculation_order'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}
	function setCountry($country) {
		$country = trim($country);

		$cf = new CompanyFactory();

		if (	$country == ''
				OR
				$this->Validator->inArrayKey(	'country',
												$country,
												('Invalid Country'),
												$cf->getOptions('country') ) ) {

			$this->data['country'] = $country;

			return TRUE;
		}

		return FALSE;
	}

	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}
	function setProvince($province) {
		$province = trim($province);

		Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

		$cf = new CompanyFactory();
		$options_arr = $cf->getOptions('province');
		if ( isset($options_arr[$this->getCountry()]) ) {
			$options = $options_arr[$this->getCountry()];
		} else {
			$options = array();
		}

		if (	$province == ''
				OR
				$this->Validator->inArrayKey(	'province',
												$province,
												('Invalid Province/State'),
												$options ) ) {

			$this->data['province'] = $province;

			return TRUE;
		}

		return FALSE;
	}

	//Used for getting district name on W2's
	function getDistrictName() {
		$retval = NULL;

		if ( strtolower($this->getDistrict()) == 'all'
				OR strtolower($this->getDistrict()) == '00' ) {
			if ( $this->getUserValue5() != '' ) {
				$retval = $this->getUserValue5();
			}
		} else {
			$retval = $this->getDistrict();
		}

		return $retval;
	}
	function getDistrict() {
		if ( isset($this->data['district']) ) {
			return $this->data['district'];
		}

		return FALSE;
	}
	function setDistrict($district) {
		$district = trim($district);

		Debug::Text('Country: '. $this->getCountry() .' District: '. $district, __FILE__, __LINE__, __METHOD__,10);

		$cf = new CompanyFactory();
		$options_arr = $cf->getOptions('district');
		if ( isset($options_arr[$this->getCountry()][$this->getProvince()]) ) {
			$options = $options_arr[$this->getCountry()][$this->getProvince()];
		} else {
			$options = array();
		}

		if (	( $district == '' OR $district == '00' )
				OR
				$this->Validator->inArrayKey(	'district',
												$district,
												('Invalid District'),
												$options ) ) {

			$this->data['district'] = $district;

			return TRUE;
		}

		return FALSE;
	}

	function getCompanyValue1() {
		if ( isset($this->data['company_value1']) ) {
			return $this->data['company_value1'];
		}

		return FALSE;
	}
	function setCompanyValue1($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'company_value1',
												$value,
												('Company Value 1 is too short or too long'),
												1,
												20) ) {

			$this->data['company_value1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getCompanyValue2() {
		if ( isset($this->data['company_value2']) ) {
			return $this->data['company_value2'];
		}

		return FALSE;
	}
	function setCompanyValue2($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'company_value2',
												$value,
												('Company Value 2 is too short or too long'),
												1,
												20) ) {

			$this->data['company_value2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1() {
		if ( isset($this->data['user_value1']) ) {
			return $this->data['user_value1'];
		}

		return FALSE;
	}
	function setUserValue1($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value1',
												$value,
												('User Value 1 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue2() {
		if ( isset($this->data['user_value2']) ) {
			return $this->data['user_value2'];
		}

		return FALSE;
	}
	function setUserValue2($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value2',
												$value,
												('User Value 2 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue3() {
		if ( isset($this->data['user_value3']) ) {
			return $this->data['user_value3'];
		}

		return FALSE;
	}
	function setUserValue3($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value3',
												$value,
												('User Value 3 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue4() {
		if ( isset($this->data['user_value4']) ) {
			return $this->data['user_value4'];
		}

		return FALSE;
	}
	function setUserValue4($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value4',
												$value,
												('User Value 4 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue5() {
		if ( isset($this->data['user_value5']) ) {
			return $this->data['user_value5'];
		}

		return FALSE;
	}
	function setUserValue5($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value5',
												$value,
												('User Value 5 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue6() {
		if ( isset($this->data['user_value6']) ) {
			return $this->data['user_value6'];
		}

		return FALSE;
	}
	function setUserValue6($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value6',
												$value,
												('User Value 6 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value6'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue7() {
		if ( isset($this->data['user_value7']) ) {
			return $this->data['user_value7'];
		}

		return FALSE;
	}
	function setUserValue7($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value7',
												$value,
												('User Value 7 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value7'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue8() {
		if ( isset($this->data['user_value8']) ) {
			return $this->data['user_value8'];
		}

		return FALSE;
	}
	function setUserValue8($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value8',
												$value,
												('User Value 8 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value8'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue9() {
		if ( isset($this->data['user_value9']) ) {
			return $this->data['user_value9'];
		}

		return FALSE;
	}
	function setUserValue9($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value9',
												$value,
												('User Value 9 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value9'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue10() {
		if ( isset($this->data['user_value10']) ) {
			return $this->data['user_value10'];
		}

		return FALSE;
	}
	function setUserValue10($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value10',
												$value,
												('User Value 10 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value10'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1Options() {
		//Debug::Text('Calculation: '. $this->getCalculation(), __FILE__, __LINE__, __METHOD__,10);
		switch ( $this->getCalculation() ) {
			case 100:
				//Debug::Text('Country: '. $this->getCountry(), __FILE__, __LINE__, __METHOD__,10);
				if ( $this->getCountry() == 'CA' ) {
				} elseif ( $this->getCountry() == 'US' ) {
					//$options = $this->federal_filing_status_options;
					$options = $this->getOptions('federal_filing_status');
				}

				break;
			case 200:
				//Debug::Text('Country: '. $this->getCountry(), __FILE__, __LINE__, __METHOD__,10);
				//Debug::Text('Province: '. $this->getProvince(), __FILE__, __LINE__, __METHOD__,10);
				if ( $this->getCountry() == 'CA' ) {
				} elseif ( $this->getCountry() == 'US' ) {
					$state_options_var = strtolower('state_'. $this->getProvince() .'_filing_status_options');
					//Debug::Text('Specific State Variable Name: '. $state_options_var, __FILE__, __LINE__, __METHOD__,10);
					if ( isset( $this->$state_options_var ) ) {
						//Debug::Text('Specific State Options: ', __FILE__, __LINE__, __METHOD__,10);
						//$options = $this->$state_options_var;
						$options = $this->getOptions($state_options_var);
					} elseif ( $this->getProvince() == 'IL' ) {
						$options = FALSE;
					} else {
						//Debug::Text('Default State Options: ', __FILE__, __LINE__, __METHOD__,10);
						//$options = $this->state_filing_status_options;
						$options = $this->getOptions('state_filing_status');
					}
				}

				break;
		}

		if ( isset($options) ) {
			return $options;
		}

		return FALSE;
	}

	function getPayStubEntryAccount() {
		if ( isset($this->data['pay_stub_entry_account_id']) ) {
			return $this->data['pay_stub_entry_account_id'];
		}

		return FALSE;
	}
	function setPayStubEntryAccount($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'pay_stub_entry_account',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['pay_stub_entry_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getLockUserValue1() {
		return $this->fromBool( $this->data['lock_user_value1'] );
	}
	function setLockUserValue1($bool) {
		$this->data['lock_user_value1'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue2() {
		return $this->fromBool( $this->data['lock_user_value2'] );
	}
	function setLockUserValue2($bool) {
		$this->data['lock_user_value2'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue3() {
		return $this->fromBool( $this->data['lock_user_value3'] );
	}
	function setLockUserValue3($bool) {
		$this->data['lock_user_value3'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue4() {
		return $this->fromBool( $this->data['lock_user_value4'] );
	}
	function setLockUserValue4($bool) {
		$this->data['lock_user_value4'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue5() {
		return $this->fromBool( $this->data['lock_user_value5'] );
	}
	function setLockUserValue5($bool) {
		$this->data['lock_user_value5'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue6() {
		return $this->fromBool( $this->data['lock_user_value6'] );
	}
	function setLockUserValue6($bool) {
		$this->data['lock_user_value6'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue7() {
		return $this->fromBool( $this->data['lock_user_value7'] );
	}
	function setLockUserValue7($bool) {
		$this->data['lock_user_value7'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue8() {
		return $this->fromBool( $this->data['lock_user_value8'] );
	}
	function setLockUserValue8($bool) {
		$this->data['lock_user_value8'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue9() {
		return $this->fromBool( $this->data['lock_user_value9'] );
	}
	function setLockUserValue9($bool) {
		$this->data['lock_user_value9'] = $this->toBool($bool);

		return true;
	}

	function getLockUserValue10() {
		return $this->fromBool( $this->data['lock_user_value10'] );
	}
	function setLockUserValue10($bool) {
		$this->data['lock_user_value10'] = $this->toBool($bool);

		return true;
	}

	function getAccountAmountTypeMap( $id ) {
		if ( isset( $this->account_amount_type_map[$id]) ) {
			return $this->account_amount_type_map[$id];
		}

		Debug::text('Unable to find Account Amount mapping... ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		return 'amount'; //Default to amount.
	}

	function getAccountAmountTypePSEntriesMap( $id ) {
		if ( isset( $this->account_amount_type_ps_entries_map [$id]) ) {
			return $this->account_amount_type_ps_entries_map[$id];
		}

		Debug::text('Unable to find Account Amount PS Entries mapping... ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		return 'current'; //Default to current entries.
	}


	function getIncludeAccountAmountType() {
		if ( isset($this->data['include_account_amount_type_id']) ) {
			return $this->data['include_account_amount_type_id'];
		}

		return FALSE;
	}
	function setIncludeAccountAmountType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'include_account_amount_type_id',
											$value,
											('Incorrect include account amount type'),
											$this->getOptions('account_amount_type')) ) {

			$this->data['include_account_amount_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getIncludePayStubEntryAccount() {
		$cache_id = 'include_pay_stub_entry-'. $this->getId();
		$list = $this->getCache( $cache_id );
		if ( empty($list) || $list === FALSE ) {
			//Debug::text('Caching Include IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$cdpsealf = new CompanyDeductionPayStubEntryAccountListFactory();
			$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 10 );

			$list = NULL;
			foreach ($cdpsealf->rs as $obj) {
				$cdpsealf->data = (array)$obj;
				$list[] = $cdpsealf->getPayStubEntryAccount();
			}
			$this->saveCache( $list, $cache_id);
		} else {
			//Debug::text('Reading Cached Include IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		}
		//Debug::Arr($list, 'Include IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($list) AND is_array($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setIncludePayStubEntryAccount($ids) {
		Debug::text('Setting Include IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$cdpsealf = new CompanyDeductionPayStubEntryAccountListFactory();
				$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 10 );

				foreach ($cdpsealf->rs as $obj) {
					$cdpsealf->data = (array)$obj;
					$id = $cdpsealf->getPayStubEntryAccount();
					Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$cdpsealf->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $cdpsealf);
			}

			//Insert new mappings.
			$psealf = new PayStubEntryAccountListFactory();

			foreach ($ids as $id) {
				if ( $id != FALSE AND isset($ids) AND !in_array($id, $tmp_ids) ) {
					$cdpseaf = new CompanyDeductionPayStubEntryAccountFactory();
					$cdpseaf->setCompanyDeduction( $this->getId() );
					$cdpseaf->setType(10); //Include
					$cdpseaf->setPayStubEntryAccount( $id );

					$obj = $psealf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'include_pay_stub_entry_account',
														$cdpseaf->Validator->isValid(),
														('Include Pay Stub Account is invalid').' ('. $obj->getName() .')' )) {
						$cdpseaf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getExcludeAccountAmountType() {
		if ( isset($this->data['exclude_account_amount_type_id']) ) {
			return $this->data['exclude_account_amount_type_id'];
		}

		return FALSE;
	}
	function setExcludeAccountAmountType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'exclude_account_amount_type_id',
											$value,
											('Incorrect exclude account amount type'),
											$this->getOptions('account_amount_type')) ) {

			$this->data['exclude_account_amount_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExcludePayStubEntryAccount() {
		$cache_id = 'exclude_pay_stub_entry-'. $this->getId();
		$list = $this->getCache( $cache_id );
		if ( empty($list) || $list === FALSE ) {
			//Debug::text('Caching Exclude IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$cdpsealf = new CompanyDeductionPayStubEntryAccountListFactory();
			$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 20 );

			$list = NULL;
			foreach ($cdpsealf->rs as $obj) {
				$cdpsealf->data = (array)$obj;
				$list[] = $cdpsealf->getPayStubEntryAccount();
			}

			$this->saveCache( $list, $cache_id);
		} else {
			//Debug::text('Reading Cached Exclude IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($list) AND is_array($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setExcludePayStubEntryAccount($ids) {
		Debug::text('Setting Exclude IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		//if ( is_array($ids) and count($ids) > 0) {
		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$cdpsealf = new CompanyDeductionPayStubEntryAccountListFactory();
				$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 20 );

				$tmp_ids = array();
				foreach ($cdpsealf->rs as $obj) {
					$cdpsealf->data = (array)$obj;
					$id = $cdpsealf->getPayStubEntryAccount();
					Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$cdpsealf->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $cdpsealf);
			}

			//Insert new mappings.
			//$lf = new UserListFactory();
			$psealf = new PayStubEntryAccountListFactory();

			foreach ($ids as $id) {
				if ( $id != FALSE AND isset($ids) AND !in_array($id, $tmp_ids) ) {
					$cdpseaf = new CompanyDeductionPayStubEntryAccountFactory();
					$cdpseaf->setCompanyDeduction( $this->getId() );
					$cdpseaf->setType(20); //Include
					$cdpseaf->setPayStubEntryAccount( $id );

					$obj = $psealf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'exclude_pay_stub_entry_account',
														$cdpseaf->Validator->isValid(),
														('Exclude Pay Stub Account is invalid').' ('. $obj->getName() .')' )) {
						$cdpseaf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getUser() {
		$udlf = new UserDeductionListFactory();
		$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );
		foreach ($udlf->rs as $obj) {
			$udlf->data = (array)$obj;
			$list[] = $udlf->getUser();
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
				$udlf = new UserDeductionListFactory();
				$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );

				$tmp_ids = array();
				foreach ($udlf->rs as $obj) {
					$udlf->data = (array)$obj;
					$id = $udlf->getUser();
					Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$udlf->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $udlf);
			}

			//Insert new mappings.
			$ulf = new UserListFactory();
			foreach ($ids as $id) {
				if ( $id != FALSE AND isset($ids) AND !in_array($id, $tmp_ids) ) {
					$udf = new UserDeductionFactory();
					$udf->setUser( $id );
					$udf->setCompanyDeduction( $this->getId() );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ($this->Validator->isTrue(		'user',
															$udf->Validator->isValid(),
															('Selected employee is invalid').' ('. $obj->getFullName() .')' )) {
							$udf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getExpandedPayStubEntryAccountIDs( $ids ) {
		//Debug::Arr($ids, 'Total Gross ID: '. $this->getPayStubEntryAccountLinkObject()->getTotalGross() .' IDs:', __FILE__, __LINE__, __METHOD__,10);
		$ids = (array)$ids;

		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids);
		if ( $total_gross_key !== FALSE ) {
			$type_ids[] = 10;
			$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset($ids[$total_gross_key]);
		}
		unset($total_gross_key);

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids);
		if ( $total_employee_deduction_key !== FALSE ) {
			$type_ids[] = 20;
			unset($ids[$total_employee_deduction_key]);
		}
		unset($total_employee_deduction_key);

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids);
		if ( $total_employer_deduction_key !== FALSE ) {
			$type_ids[] = 30;
			unset($ids[$total_employer_deduction_key]);
		}
		unset($total_employer_deduction_key);

		$psea_ids_from_type_ids = array();
		if ( isset($type_ids) ) {
			$psealf = new PayStubEntryAccountListFactory();
			$psea_ids_from_type_ids = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $this->getCompany(), array(10,20), $type_ids, FALSE );
			if ( is_array( $psea_ids_from_type_ids ) ) {
				$psea_ids_from_type_ids = array_keys( $psea_ids_from_type_ids );
			}
		}

		$retval = array_unique( array_merge( $ids, $psea_ids_from_type_ids ) );

		//Debug::Arr($retval, 'Retval: ', __FILE__, __LINE__, __METHOD__,10);
		return $retval;

	}

	//Combines include account IDs/Type IDs and exclude account IDs/Type Ids
	//and outputs just include account ids.
	function getCombinedIncludeExcludePayStubEntryAccount( $include_ids, $exclude_ids ) {
		$ret_include_ids = $this->getExpandedPayStubEntryAccountIDs( $include_ids );
		$ret_exclude_ids = $this->getExpandedPayStubEntryAccountIDs( $exclude_ids );

		$retarr = array_diff( $ret_include_ids, $ret_exclude_ids );

		//Debug::Arr($retarr, 'Retarr: ', __FILE__, __LINE__, __METHOD__,10);
		return $retarr;
	}

	function getPayStubEntryAmountSum( $pay_stub_obj, $ids, $ps_entries = 'current', $return_value = 'amount' ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		if ( !is_array($ids) ) {
			return FALSE;
		}

		$pself = new PayStubEntryListFactory();

		//Get Linked accounts so we know which IDs are totals.
		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids);
		if ( $total_gross_key !== FALSE ) {
			$type_ids[] = 10;
			$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset($ids[$total_gross_key]);
		}
		unset($total_gross_key);

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids);
		if ( $total_employee_deduction_key !== FALSE ) {
			$type_ids[] = 20;
			unset($ids[$total_employee_deduction_key]);
		}
		unset($total_employee_deduction_key);

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids);
		if ( $total_employer_deduction_key !== FALSE ) {
			$type_ids[] = 30;
			unset($ids[$total_employer_deduction_key]);
		}
		unset($total_employer_deduction_key);

		$type_amount_arr[$return_value] = 0;
		if ( isset($type_ids) ) {
			$type_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, $type_ids );
		}

		$amount_arr[$return_value] = 0;
		if ( count($ids) > 0 ) {
			//Still other IDs left to total.
			$amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, NULL, $ids );
		}

		$retval = bcadd($type_amount_arr[$return_value], $amount_arr[$return_value] );

		Debug::text('Type Amount: '. $type_amount_arr[$return_value] .' Regular Amount: '. $amount_arr[$return_value] .' Total: '. $retval .' Return Value: '. $return_value .' PS Entries: '. $ps_entries, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getCalculationPayStubAmount( $pay_stub_obj ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		$include_ids = $this->getIncludePayStubEntryAccount();
		$exclude_ids = $this->getExcludePayStubEntryAccount();

		//This totals up the includes, and minuses the excludes.
		$include = $this->getPayStubEntryAmountSum( $pay_stub_obj, $include_ids, $this->getAccountAmountTypePSEntriesMap( $this->getIncludeAccountAmountType() ), $this->getAccountAmountTypeMap( $this->getIncludeAccountAmountType() ) );
		$exclude = $this->getPayStubEntryAmountSum( $pay_stub_obj, $exclude_ids, $this->getAccountAmountTypePSEntriesMap( $this->getExcludeAccountAmountType() ), $this->getAccountAmountTypeMap( $this->getExcludeAccountAmountType() ) );
		Debug::text('Include Amount: '. $include .' Exclude Amount: '. $exclude, __FILE__, __LINE__, __METHOD__, 10);

		//Allow negative values to be returned, as we need to do calculation on accruals and such that may be negative values.
		$amount = bcsub( $include, $exclude);

		Debug::text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

		return $amount;
	}

	function getCalculationYTDAmount( $pay_stub_obj ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		//This totals up the includes, and minuses the excludes.
		$include_ids = $this->getIncludePayStubEntryAccount();
		$exclude_ids = $this->getExcludePayStubEntryAccount();

		//Use current YTD amount because if we only include previous pay stub YTD amounts we won't include YTD adjustment PS amendments on the current PS.
		$include = $this->getPayStubEntryAmountSum( $pay_stub_obj, $include_ids, 'previous+ytd_adjustment', 'ytd_amount' );
		$exclude = $this->getPayStubEntryAmountSum( $pay_stub_obj, $exclude_ids, 'previous+ytd_adjustment', 'ytd_amount' );

		$amount = bcsub( $include, $exclude);

		if ( $amount < 0 ) {
			$amount = 0;
		}

		Debug::text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

		return $amount;
	}

	function getJavaScriptArrays() {
		$output = 'var fields = '. Misc::getJSArray( $this->calculation_id_fields, 'fields', TRUE );

		$output .= 'var country_calculation_ids = '. Misc::getJSArray( $this->country_calculation_ids );
		$output .= 'var province_calculation_ids = '. Misc::getJSArray( $this->province_calculation_ids );
		$output .= 'var district_calculation_ids = '. Misc::getJSArray( $this->district_calculation_ids );

		return $output;
	}

	static function getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, $type_id, $name ) {
		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyIdAndTypeAndFuzzyName( $company_id, $type_id, $name );
		if ( $psealf->getRecordCount() > 0 ) {
			return $psealf->getCurrent()->getId();
		}

		return FALSE;
	}

	static function addPresets($company_id) {
		if ( $company_id == '' ) {
			Debug::text('Company ID: '. $company_id , __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$clf = new CompanyListFactory();
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() > 0 ) {
			$company_obj = $clf->getCurrent();
			$country = $company_obj->getCountry();
			$province = $company_obj->getProvince();
		} else {
			Debug::text('bCompany ID: '. $company_id , __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Get PayStub Link accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $company_id );
		if  ( $pseallf->getRecordCount() > 0 ) {
			$psea_obj = $pseallf->getCurrent();
		} else {
			Debug::text('cCompany ID: '. $company_id , __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.php');
		$cdf = new CompanyDeductionFactory();
		$cdf->StartTransaction();

		/*
										10 => 'Percent',
										15 => 'Advanced Percent',
										20 => 'Fixed Amount',

										//Federal
										100 => 'Federal Income Tax Formula',

										//Province/State
										200 => 'Province/State Income Tax Formula',
										210 => 'Province/State UI Formula',
		*/

		Debug::text('Country: '. $country , __FILE__, __LINE__, __METHOD__, 10);
		switch (strtolower($country)) {
			case 'ca':
				$pd_obj = new PayrollDeduction( $country, 'BC' ); //Pick default province for now.
				$pd_obj->setDate( time() );

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Federal Income Tax' );
				$cdf->setCalculation( 100 );
				$cdf->setCalculationOrder( 100 );
				$cdf->setCountry( 'CA' );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, '%Federal Income%') );
				$cdf->setUserValue1( $pd_obj->getBasicFederalClaimCodeAmount() );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$exclude_ids = array(
										self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'Union'),
										);
					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );
					//var_dump($exclude_ids);
					$cdf->setExcludePayStubEntryAccount( $exclude_ids );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Additional Income Tax' );
				$cdf->setCalculation( 20 );
				$cdf->setCalculationOrder( 105 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, '%Additional Income Tax%') );
				$cdf->setUserValue1( 0 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'CPP - Employee' );
				$cdf->setCalculation( 90 ); // CPP Formula
				$cdf->setMinimumUserAge( 18 );
				$cdf->setMaximumUserAge( 70 );

				$cdf->setCalculationOrder( 80 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'CPP') );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'CPP - Employer' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 85 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, '%CPP - Employer%') );
				$cdf->setUserValue1( 100 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'CPP') ) );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'EI - Employee' );
				$cdf->setCalculation( 91 ); //EI Formula
				$cdf->setCalculationOrder( 90 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'EI') );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'EI - Employer' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 95 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, '%EI - Employer%') );
				$cdf->setUserValue1( 140 ); //2006

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'EI') ) );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'WCB - Employer' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 95 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, '%WCB%') );
				$cdf->setUserValue1( 0.00 ); //Default
				$cdf->setUserValue2( 0 ); //Annual Wage Base: WCB has this, but can differ between rates/classifications.
				$cdf->setUserValue3( 0 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 20 ); //Deduction
				$cdf->setName( 'Vacation Accrual' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 50 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 50, 'Vacation Accrual') );
				$cdf->setUserValue1( 4 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );
					$exclude_ids = array(
										self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Vacation Accrual Release'),
										self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Vacation Time'),
										);
					$cdf->setExcludePayStubEntryAccount( $exclude_ids );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 20 ); //Deduction
				$cdf->setName( 'Vacation Release' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 51 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Vacation Accrual Release') );
				$cdf->setUserValue1( 4 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );
					$exclude_ids = array(
										self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Vacation Accrual Release'),
										self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Vacation Time'),
										);
					$cdf->setExcludePayStubEntryAccount( $exclude_ids );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				break;
			case 'us':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Federal Income Tax' );
				$cdf->setCalculation( 100 );
				$cdf->setCalculationOrder( 100 );
				$cdf->setCountry( 'US' );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, '%Federal Income%') );
				$cdf->setUserValue1( 10 ); //Single
				$cdf->setUserValue2( 1 ); //0 Allowances

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				/*
				//Repealed as of 31-Dec-2010.
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Advance Earned Income Credit (EIC)' );
				$cdf->setCalculation( 80 );
				$cdf->setCalculationOrder( 105 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, '%Advance EIC%') );
				$cdf->setUserValue1( 10 ); //Single

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				*/

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Federal Unemployment Insurance - Employer' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 80 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, 'Fed. Unemployment Ins.') );
				$cdf->setUserValue1( 0.80 ); //2009
				$cdf->setUserValue2( 7000 );
				$cdf->setUserValue3( 0 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Social Security - Employee' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 80 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'Social Security (FICA)') );
				$cdf->setUserValue1( 4.2 ); //2011, differ from employer rate.
				$cdf->setUserValue2( 106800 );
				$cdf->setUserValue3( 0 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Social Security - Employer' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 85 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, 'Social Security%') );
				$cdf->setUserValue1( 6.2 ); //2011, differ from employee rate.
				$cdf->setUserValue2( 106800 );
				$cdf->setUserValue3( 0 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Medicare - Employee' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 90 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'Medicare') );
				$cdf->setUserValue1( 1.45 ); //2009

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( 'Medicare - Employer' );
				$cdf->setCalculation( 10 );
				$cdf->setCalculationOrder( 95 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 30, 'Medicare') );
				$cdf->setUserValue1( 100 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					//$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));
					$cdf->setIncludePayStubEntryAccount( array( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'Medicare') ) );

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				break;
			case 'cr':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( ('Income Tax') );
				$cdf->setCalculation( 100 );
				$cdf->setCalculationOrder( 100 );
				$cdf->setCountry( 'CR' );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, '%Federal Income%') );
				$cdf->setUserValue1( 10 ); //Single
				$cdf->setUserValue2( 0 ); //0 Allowances

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
				   		$cdf->Save();
					}
				}

				break;
		}

		$pd_obj = new PayrollDeduction( $country, $province );
		$pd_obj->setDate( time() );

		Debug::text('Province/State: '. $province , __FILE__, __LINE__, __METHOD__, 10);
		switch (strtolower($province)) {
			//Canada
			case 'ab':
			case 'bc':
			case 'sk':
			case 'mb':
			case 'qc':
			case 'on':
			case 'nl':
			case 'nb':
			case 'ns':
			case 'pe':
			case 'nt':
			case 'yt':
			case 'nu':
				$provincial_claim_amount = $pd_obj->getBasicProvinceClaimCodeAmount();
				break;

			//US
			case 'al':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ak':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance - Employer' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 34600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance - Employee' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 34600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ar':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 12000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'az':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 7000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Job Training' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Employee Training') );
				$cdf->setUserValue1( 0.10 ); //2011
				$cdf->setUserValue2( 7000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				break;
			case 'ca':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Disability Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 180 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'State Disability Ins.') );
				$cdf->setUserValue1( 1.20 ); //2011
				$cdf->setUserValue2( 93316 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0 ); //2011
				$cdf->setUserValue2( 7000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Employee Training' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Employee Training') );
				$cdf->setUserValue1( 0.10 ); //2011
				$cdf->setUserValue2( 7000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'co':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 10000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ct':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 15000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'dc':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'de':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 10500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'fl':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 7000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ga':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'hi':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 34200 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ia':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 24700 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'id':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 33300 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'il':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins. - Employer') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 12740 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'in':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ks':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ky':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'la':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 7700 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ma':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 14000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'md':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'me':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 12000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'mi':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'mn':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 27000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'mo':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 13000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ms':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 14000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'mt':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 26300 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'nc':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 19700 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'nd':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 25500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;

			case 'nh':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 12000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ne':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'nj':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance: Employee' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 29600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance: Company' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 29600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'nm':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 21900 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'nv':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 26600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ny':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0 ); //2011
				$cdf->setUserValue2( 8500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Reemployment Service Fund' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Reemployment') );
				$cdf->setUserValue1( 0.075 ); //2011
				$cdf->setUserValue2( 8500 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Disability Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 180 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'State Disability Ins.') );
				$cdf->setUserValue1( 0.50 ); //2011
				$cdf->setUserValue2( 6240 ); //Max $0.60/week

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				break;
			case 'oh':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ok':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 18600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'or':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Insurance') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 32300 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'pa':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ri':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Employment Security' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 19000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'sc':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 10000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'sd':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 11000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'tn':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'tx':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}

				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Employee Training' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Employee Training') );
				$cdf->setUserValue1( 0.10 ); //2011
				$cdf->setUserValue2( 9000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'ut':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 28600 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'va':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 8000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'vt':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 13000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;

			case 'wa':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 37300 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'wi':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 13000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'wv':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 12000 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;
			case 'wy':
				$cdf = new CompanyDeductionFactory();
				$cdf->setCompany( $company_id );
				$cdf->setStatus( 10 ); //Enabled
				$cdf->setType( 10 ); //Tax
				$cdf->setName( strtoupper($province).' - Unemployment Insurance' );
				$cdf->setCalculation( 15 );
				$cdf->setCalculationOrder( 185 );
				$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 30, 'State Unemployment Ins.') );
				$cdf->setUserValue1( 0.00 ); //2011
				$cdf->setUserValue2( 22300 );

				if ( $cdf->isValid() ) {
					$cdf->Save(FALSE);

					$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

					unset($exclude_ids);

					if ( $cdf->isValid() ) {
						$cdf->Save();
					}
				}
				break;

		}

		if ( $country == 'CA' ) {
			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $company_id );
			$cdf->setStatus( 10 ); //Enabled
			$cdf->setType( 10 ); //Tax
			$cdf->setName( strtoupper($province) .' - Provincial Income Tax' );
			$cdf->setCalculation( 200 );
			$cdf->setCalculationOrder( 110 );
			$cdf->setCountry( 'CA' );
			$cdf->setProvince( strtoupper($province) );
			$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, '%Provincial Income%') );
			$cdf->setUserValue1( $provincial_claim_amount );

			if ( $cdf->isValid() ) {
				$cdf->Save(FALSE);

				$exclude_ids = array(
									//Not proper way to do it with CPP/EI
									//self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 'CPP'),
									//self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 'EI'),
									self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 20, 'Union'),
									);
				$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );
				$cdf->setExcludePayStubEntryAccount( $exclude_ids );

				unset($exclude_ids);

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}
		} elseif ( $country == 'US' ) {
			$cdf = new CompanyDeductionFactory();
			$cdf->setCompany( $company_id );
			$cdf->setStatus( 10 ); //Enabled
			$cdf->setType( 10 ); //Tax
			$cdf->setName( 'State Income Tax' );
			$cdf->setCalculation( 200 );
			$cdf->setCalculationOrder( 200 );
			$cdf->setCountry( 'US' );
			$cdf->setProvince( strtoupper($province) );
			$cdf->setPayStubEntryAccount( self::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, '%State Income%') );

			//FIXME: Not all states are the same. Need to customize UserValues for each one.
			$cdf->setUserValue1( 10 ); //Single
			$cdf->setUserValue2( 1 ); //0 Allowances

			if ( $cdf->isValid() ) {
				$cdf->Save(FALSE);

				$cdf->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ) );

				unset($exclude_ids);

				if ( $cdf->isValid() ) {
					$cdf->Save();
				}
			}
		}

		$cdf->CommitTransaction();
		//$cdf->FailTransaction();

		return TRUE;
	}

	function preSave() {

		//Set Length of service in days.
		$this->setMinimumLengthOfServiceDays( $this->getMinimumLengthOfService() );
		$this->setMaximumLengthOfServiceDays( $this->getMaximumLengthOfService() );

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( 'include_pay_stub_entry-'. $this->getId() );
		$this->removeCache( 'exclude_pay_stub_entry-'. $this->getId() );

		if ( $this->getDeleted() == TRUE ) {
			//Check if any users are assigned to this, if so, delete mappings.
			$udlf = new UserDeductionListFactory();

			$udlf->StartTransaction();
			$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );
			if ( $udlf->getRecordCount() ) {
				foreach( $udlf->rs as $ud_obj ) {
					$udlf->data = (array)$ud_obj;
					$ud_obj = $udlf;
					
					$ud_obj->setDeleted(TRUE);
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
			}
			$udlf->CommitTransaction();
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
						case 'start_date':
						case 'end_date':
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
						case 'status':
						case 'type':
						case 'calculation':
							$function = 'get'.$variable;
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Tax / Deduction'), NULL, $this->getTable(), $this );
	}
}
?>
