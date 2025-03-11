<?php

namespace App\Models\PayStubAmendment;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Users\UserListFactory;

class PayStubAmendmentFactory extends Factory {
	protected $table = 'pay_stub_amendment';
	protected $pk_sequence_name = 'pay_stub_amendment_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;
	var $pay_stub_entry_name_obj = NULL;
	var $percent_amount_entry_name_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'filtered_status':
				//Select box options;
				$status_options_filter = array(50);
				if ( $this->getStatus() == 55 ) {
					$status_options_filter = array(55);
				} elseif ( $this->getStatus() == 52 ) {
					$status_options_filter = array(52);
				}

				$retval = Option::getByArray( $status_options_filter, $this->getOptions('status') );
				break;
			case 'status':
				$retval = array(
										10 => ('NEW'),
										20 => ('OPEN'),
										30 => ('PENDING AUTHORIZATION'),
										40 => ('AUTHORIZATION OPEN'),
										50 => ('ACTIVE'),
										52 => ('IN USE'),
										55 => ('PAID'),
										60 => ('DISABLED')
									);
				break;
			case 'type':
				$retval = array(
											10 => ('Fixed'),
											20 => ('Percent')
										);
				break;
			case 'pay_stub_account_type':
				$retval = array(10,20,30,50,60,65);
				break;
			case 'percent_pay_stub_account_type':
				$retval = array(10,20,30,40,50,60,65);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => ('First Name'),
										'-1002-last_name' => ('Last Name'),
										'-1005-user_status' => ('Employee Status'),
										'-1010-title' => ('Title'),
										'-1020-user_group' => ('Group'),
										'-1030-default_branch' => ('Default Branch'),
										'-1040-default_department' => ('Default Department'),

										'-1110-status' => ('Status'),
										'-1120-type' => ('Type'),
										'-1130-pay_stub_entry_name' => ('Account'),
										'-1140-effective_date' => ('Effective Date'),
										'-1150-amount' => ('Amount'),
										'-1160-rate' => ('Rate'),
										'-1170-units' => ('Units'),
										'-1180-description' => ('Description'),
										'-1190-ytd_adjustment' => ('YTD Adjustment'),

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
								'status',
								'pay_stub_entry_name',
								'effective_date',
								'amount',
								'description',
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
										'user_id' => 'User',

										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_status_id' => FALSE,
										'user_status' => FALSE,
										'group_id' => FALSE,
										'user_group' => FALSE,
										'title_id' => FALSE,
										'title' => FALSE,
										'default_branch_id' => FALSE,
										'default_branch' => FALSE,
										'default_department_id' => FALSE,
										'default_department' => FALSE,

										'pay_stub_entry_name_id' => 'PayStubEntryNameId',
										'pay_stub_entry_name' => FALSE,
										'recurring_ps_amendment_id' => 'RecurringPayStubAmendmentId',
										'effective_date' => 'EffectiveDate',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'rate' => 'Rate',
										'units' => 'Units',
										'amount' => 'Amount',
										'percent_amount' => 'PercentAmount',
										'percent_amount_entry_name_id' => 'PercentAmountEntryNameId',
										'ytd_adjustment' => 'YTDAdjustment',
										'description' => 'Description',
										'authorized' => 'Authorized',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getPayStubEntryNameObject() {
		if ( is_object($this->pay_stub_entry_name_obj) ) {
			return $this->pay_stub_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getByID( $this->getPayStubEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_name_obj = $psealf->getCurrent();
				return $this->pay_stub_entry_name_obj;
			}

			return FALSE;
		}
	}

	function getPercentAmountEntryNameObject() {
		if ( is_object($this->percent_amount_entry_name_obj) ) {
			return $this->percent_amount_entry_name_obj;
		} else {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' );
			$psealf->getByID( $this->getPercentAmountEntryNameId() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->percent_amount_entry_name_obj = $psealf->getCurrent();
				return $this->percent_amount_entry_name_obj;
			}

			return FALSE;
		}
	}

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubEntryNameId() {
		if ( isset($this->data['pay_stub_entry_name_id']) ) {
			return (int)$this->data['pay_stub_entry_name_id'];
		}

		return FALSE;
	}
	function setPayStubEntryNameId($id) {
		$id = trim($id);

		//$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$result = $psealf->getById( $id );
		//Debug::Arr($result, 'Result: ID: '. $id .' Rows: '. $result->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if (  $this->Validator->isResultSetWithRows(	'pay_stub_entry_name_id',
														$result,
														('Invalid Pay Stub Account')
														) ) {

			$this->data['pay_stub_entry_name_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function setName($name) {
		$name = trim($name);

		$psenlf = TTnew( 'PayStubEntryNameListFactory' );
		$result = $psenlf->getByName($name);

		if (  $this->Validator->isResultSetWithRows(	'name',
														$result,
														('Invalid Entry Name')
														) ) {

			$this->data['pay_stub_entry_name_id'] = $result->getCurrent()->getId();

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringPayStubAmendmentId() {
		if ( isset($this->data['recurring_ps_amendment_id']) ) {
			return (int)$this->data['recurring_ps_amendment_id'];
		}

		return FALSE;
	}
	function setRecurringPayStubAmendmentId($id) {
		$id = trim($id);

		$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' );
		$rpsalf->getById( $id );
		//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
		//$result = $rpsalf->getById( $id )->getCurrent();

		if (	( $id == NULL OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'recurring_ps_amendment_id',
														$rpsalf,
														('Invalid Recurring Pay Stub Amendment ID')
														) ) {

			$this->data['recurring_ps_amendment_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEffectiveDate() {
		return $this->data['effective_date'];
	}
	function setEffectiveDate($epoch) {
		$epoch = trim($epoch);

		//Adjust effective date, because we won't want it to be a
		//day boundary and have issues with pay period start/end dates.
		//Although with employees in timezones that differ from the pay period timezones, there can still be issues.
		$epoch = TTDate::getMiddleDayEpoch( $epoch );

		if 	(	$this->Validator->isDate(		'effective_date',
												$epoch,
												('Incorrect effective date')) ) {

			$this->data['effective_date'] = $epoch;

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

	function getRate() {
		if ( isset($this->data['rate']) ) {
			return $this->data['rate'];
		}

		return NULL;
	}
	function setRate($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if ($value == 0 OR $value == '') {
			$value = NULL;
		}

		if (	empty($value) OR
				(
				$this->Validator->isFloat(				'rate',
														$value,
														('Invalid Rate') )
				AND
				$this->Validator->isLength(				'rate',
											$value,
											('Rate has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal('rate',
											$value,
											('Rate has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'rate',
											$value,
											('Rate has too many digits after the decimal'),
											0,
											4)
				)
			) {
			Debug::text('Setting Rate to: '. $value, __FILE__, __LINE__, __METHOD__,10);
			//Must round to 2 decimals otherwise discreptancy can occur when generating pay stubs.
			//$this->data['rate'] = Misc::MoneyFormat( $value, FALSE );
			$this->data['rate'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUnits() {
		if ( isset($this->data['units']) ) {
			return $this->data['units'];
		}

		return NULL;
	}
	function setUnits($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if ($value == 0 OR $value == '') {
			$value = NULL;
		}

		if (	empty($value) OR
				(
				$this->Validator->isFloat(				'units',
														$value,
														('Invalid Units') )
				AND
				$this->Validator->isLength(				'units',
											$value,
											('Units has too many digits'),
											0,
											21) //Need to include decimal
				AND
				$this->Validator->isLengthBeforeDecimal('units',
											$value,
											('Units has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'units',
											$value,
											('Units has too many digits after the decimal'),
											0,
											4)
				)
			) {
			//Must round to 2 decimals otherwise discreptancy can occur when generating pay stubs.
			//$this->data['units'] = Misc::MoneyFormat( $value, FALSE );
			$this->data['units'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubId() {
		//Find which pay period this effective date belongs too
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getByUserIdAndEndDate( $this->getUser(), $this->getEffectiveDate() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();
			Debug::text('Found Pay Period ID: '. $pp_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

			//Percent PS amendments can't work on advances.
			$pslf = TTnew( 'PayStubListFactory' );
			$pslf->getByUserIdAndPayPeriodIdAndAdvance( $this->getUser(), $pp_obj->getId(), FALSE );
			if ( $pslf->getRecordCount() > 0 ) {
				$ps_obj = $pslf->getCurrent();
				Debug::text('Found Pay Stub for this effective date: '. $ps_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

				return $ps_obj->getId();
			}
		}

		return FALSE;
	}

	function getPayStubEntryAmountSum( $pay_stub_obj, $ids ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		if ( !is_array($ids) ) {
			return FALSE;
		}

		$pself = TTnew( 'PayStubEntryListFactory' );

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

		$type_amount_arr['amount'] = 0;
		if ( isset($type_ids) ) {
			//$type_amount_arr = $pself->getSumByPayStubIdAndType( $pay_stub_id, $type_ids );
			$type_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', $type_ids );
		}

		$amount_arr['amount'] = 0;
		if ( count($ids) > 0 ) {
			//Still other IDs left to total.
			//$amount_arr = $pself->getAmountSumByPayStubIdAndEntryNameID( $pay_stub_id, $ids );
			$amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $ids );
		}

		$retval = bcadd($type_amount_arr['amount'], $amount_arr['amount'] );

		Debug::text('Type Amount: '. $type_amount_arr['amount'] .' Regular Amount: '. $amount_arr['amount'] .' Total: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getCalculatedAmount( $pay_stub_obj ) {
		if ( !is_object($pay_stub_obj) ) {
			return FALSE;
		}

		if ( $this->getType() == 10 ) {
			//Fixed
			return $this->getAmount();
		} else {
			//Percent
			if ( $this->getPercentAmountEntryNameId() != '' ) {
				$ps_amendment_percent_amount = $this->getPayStubEntryAmountSum( $pay_stub_obj, array($this->getPercentAmountEntryNameId()) );

				$pay_stub_entry_account = $pay_stub_obj->getPayStubEntryAccountArray( $this->getPercentAmountEntryNameId() );
				if ( isset($pay_stub_entry_account['type_id']) AND $pay_stub_entry_account['type_id'] == 50 ) {
					//Get balance amount from previous pay stub so we can include that in our percent calculation.
					$previous_pay_stub_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, array($this->getPercentAmountEntryNameId()) );

					$ps_amendment_percent_amount = bcadd( $ps_amendment_percent_amount, $previous_pay_stub_amount_arr['ytd_amount']);
					Debug::text('Pay Stub Amendment is a Percent of an Accrual, add previous pay stub accrual balance to amount: '. $previous_pay_stub_amount_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);
				}
				unset($pay_stub_entry_account, $previous_pay_stub_amount_arr);

				Debug::text('Pay Stub Amendment Total Amount: '. $ps_amendment_percent_amount .' Percent Amount: '. $this->getPercentAmount(), __FILE__, __LINE__, __METHOD__,10);
				if ( $ps_amendment_percent_amount != 0 AND $this->getPercentAmount() != 0 ) { //Allow negative values.
					$amount = bcmul($ps_amendment_percent_amount, bcdiv($this->getPercentAmount(), 100) );

					return $amount;
				}
			}
		}

		return FALSE;
	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return NULL;
	}
	function setAmount($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		Debug::text('Amount: '. $value .' Name: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		if (  $this->Validator->isFloat(				'amount',
														$value,
														('Invalid Amount') )
				AND
				$this->Validator->isLength(				'amount',
											$value,
											('Amount has too many digits'),
											0,
											21) //Need to include decimal
				AND
				$this->Validator->isLengthBeforeDecimal('amount',
											$value,
											('Amount has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'amount',
											$value,
											('Amount has too many digits after the decimal'),
											0,
											4)
			) {
			$this->data['amount'] = Misc::MoneyFormat( $value, FALSE );

			return TRUE;
		}

		return FALSE;
	}

	function getPercentAmount() {
		if ( isset($this->data['percent_amount']) ) {
			return $this->data['percent_amount'];
		}

		return NULL;
	}
	function setPercentAmount($value) {
		$value = trim($value);

		Debug::text('Amount: '. $value .' Name: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		if (  $this->Validator->isFloat(				'percent_amount',
														$value,
														('Invalid Percent')
														) ) {
			$this->data['percent_amount'] = round( $value, 2);

			return TRUE;
		}
		return FALSE;
	}

	function getPercentAmountEntryNameId() {
		if ( isset($this->data['percent_amount_entry_name_id']) ) {
			return $this->data['percent_amount_entry_name_id'];
		}

		return FALSE;
	}
	function setPercentAmountEntryNameId($id) {
		$id = trim($id);

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getById( $id );
		//Not sure why we tried to use $result here, as if the ID passed is NULL, it causes a fatal error.
		//$result = $psealf->getById( $id )->getCurrent();

		if (	( $id == NULL OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'percent_amount_entry_name',
														$psealf,
														('Invalid Percent Of')
														) ) {

			$this->data['percent_amount_entry_name_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($text) {
		$text = trim($text);

		if 	(	strlen($text) == 0
				OR
				$this->Validator->isLength(		'description',
												$text,
												('Invalid Description Length'),
												2,
												100) ) {

			$this->data['description'] = htmlspecialchars( $text );

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		if ( isset($this->data['authorized']) ) {
			return $this->fromBool( $this->data['authorized'] );
		}

		return FALSE;
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return true;
	}

	function getYTDAdjustment() {
		if ( isset($this->data['ytd_adjustment']) ) {
			return $this->fromBool( $this->data['ytd_adjustment'] );
		}

		return FALSE;
	}
	function setYTDAdjustment($bool) {
		$this->data['ytd_adjustment'] = $this->toBool($bool);

		return true;
	}

	static function releaseAllAccruals($user_id, $effective_date = NULL) {
		Debug::Text('Release 100% of all accruals!', __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $effective_date == '' ) {
			$effective_date = TTDate::getTime();
		}
		Debug::Text('Effective Date: '. TTDate::getDate('DATE+TIME', $effective_date), __FILE__, __LINE__, __METHOD__,10);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
		} else {
			return FALSE;
		}

		//Get all PSE acccount accruals
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getByCompanyIdAndStatusIdAndTypeId( $user_obj->getCompany(), 10, 50);
		if ( $psealf->getRecordCount() > 0 ) {
			$ulf->StartTransaction();
			foreach( $psealf as $psea_obj ) {
				//Get PSE account that affects this accrual.
				//What if there are two accounts? It takes the first one in the list.
				$psealf_tmp = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf_tmp->getByCompanyIdAndAccrualId( $user_obj->getCompany(), $psea_obj->getId() );
				if ( $psealf_tmp->getRecordCount() > 0 ) {
					$release_account_id = $psealf_tmp->getCurrent()->getId();

					$psaf = TTnew( 'PayStubAmendmentFactory' );
					$psaf->setStatus( 50 ); //Active
					$psaf->setType( 20 ) ; //Percent
					$psaf->setUser( $user_obj->getId() );
					$psaf->setPayStubEntryNameId( $release_account_id );
					$psaf->setPercentAmount(100);
					$psaf->setPercentAmountEntryNameId( $psea_obj->getId() );
					$psaf->setEffectiveDate( $effective_date );
					$psaf->setDescription('Release Accrual Balance');

					if ( $psaf->isValid() ) {
						Debug::Text('Release Accrual Is Valid!!: ', __FILE__, __LINE__, __METHOD__,10);
						$psaf->Save();
					}
				} else {
					Debug::Text('No Release Account for this Accrual!!', __FILE__, __LINE__, __METHOD__,10);
				}
			}

			//$ulf->FailTransaction();
			$ulf->CommitTransaction();
		} else {
			Debug::Text('No Accruals to release...', __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
	}

	function preSave() {
		//Authorize all pay stub amendments until we decide they will actually go through an authorization process
		if ( $this->getAuthorized() == FALSE ) {
			$this->setAuthorized(TRUE);
		}

		/*
		//Handle YTD adjustments just like any other amendment.
		if ( $this->getYTDAdjustment() == TRUE
				AND $this->getStatus() != 55
				AND $this->getStatus() != 60) {
			Debug::Text('Calculating Amount...', __FILE__, __LINE__, __METHOD__,10);
			$this->setStatus( 52 );
		}
		*/

		//If amount isn't set, but Rate and units are, calc amount for them.
		if ( ( $this->getAmount() == NULL OR $this->getAmount() == 0 OR $this->getAmount() == '' )
				AND $this->getRate() !== NULL AND $this->getUnits() !== NULL
				AND $this->getRate() != 0 AND $this->getUnits() != 0
				AND $this->getRate() != '' AND $this->getUnits() != ''
				) {
			Debug::Text('Calculating Amount...', __FILE__, __LINE__, __METHOD__,10);
			$this->setAmount( bcmul( $this->getRate(), $this->getUnits(), 4 ) );
		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getType() == 10 ) {
			//If rate and units are set, and not amount, calculate the amount for us.
			if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL AND $this->getAmount() == NULL ) {
				$this->preSave();
			}

			//Make sure rate * units = amount
			if ( $this->getAmount() === NULL ) {
				Debug::Text('Amount is NULL...', __FILE__, __LINE__, __METHOD__,10);
				$this->Validator->isTrue(		'amount',
												FALSE,
												('Invalid Amount'));
			}

			//Make sure amount is sane given the rate and units.
			if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL
					AND $this->getRate() != 0 AND $this->getUnits() != 0
					AND $this->getRate() != '' AND $this->getUnits() != ''
					AND ( Misc::MoneyFormat( bcmul( $this->getRate(), $this->getUnits() ), FALSE) ) != Misc::MoneyFormat( $this->getAmount(), FALSE ) ) {
				Debug::text('Validate: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Amount: '. $this->getAmount() .' Calc: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Total: '. Misc::MoneyFormat( bcmul( $this->getRate(), $this->getUnits() ), FALSE), __FILE__, __LINE__, __METHOD__,10);
				$this->Validator->isTrue(		'amount',
												FALSE,
												('Invalid Amount, calculation is incorrect'));
			}
		} else {

		}

		//FIXME: Make sure effective date isn't in a CLOSED pay period?

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {
					$function = 'set'.$function;
					switch( $key ) {
						case 'effective_date':
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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'user_group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'pay_stub_entry_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'effective_date':
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
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('Pay Stub Amendment - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '.  ('Amount').': '. $this->getAmount(), NULL, $this->getTable(), $this );
	}
}
?>
