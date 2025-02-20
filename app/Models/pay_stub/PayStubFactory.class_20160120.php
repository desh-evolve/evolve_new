<?php

/*********************************************************************************

 * TimeTrex is a Payroll and Time Management program developed by

 * TimeTrex Payroll Services Copyright (C) 2003 - 2012 TimeTrex Payroll Services.

 *

 * This program is free software; you can redistribute it and/or modify it under

 * the terms of the GNU Affero General Public License version 3 as published by

 * the Free Software Foundation with the addition of the following permission

 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED

 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE

 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.

 *

 * This program is distributed in the hope that it will be useful, but WITHOUT

 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS

 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more

 * details.

 *

 * You should have received a copy of the GNU Affero General Public License along

 * with this program; if not, see http://www.gnu.org/licenses or write to the Free

 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA

 * 02110-1301 USA.

 *

 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite

 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.

 *

 * The interactive user interfaces in modified source and object code versions

 * of this program must display Appropriate Legal Notices, as required under

 * Section 5 of the GNU Affero General Public License version 3.

 *

 * In accordance with Section 7(b) of the GNU Affero General Public License

 * version 3, these Appropriate Legal Notices must retain the display of the

 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably

 * feasible for technical reasons, the Appropriate Legal Notices must display

 * the words "Powered by TimeTrex".

 ********************************************************************************/

/*

 * $Revision: 5457 $

 * $Id: PayStubFactory.class.php 5457 2011-11-04 20:49:58Z ipso $

 * $Date: 2011-11-04 13:49:58 -0700 (Fri, 04 Nov 2011) $

 */






 namespace App\Models\PayStub;
 use App\Models\Core\Factory;
 require_once( 'Numbers/Words.php');

class PayStubFactory extends Factory {

	protected $table = 'pay_stub';

	protected $pk_sequence_name = 'pay_stub_id_seq'; //PK Sequence name



	protected $tmp_data = array('previous_pay_stub' => NULL, 'current_pay_stub' => NULL );

	protected $is_unique_pay_stub = NULL;



	protected $pay_period_obj = NULL;

	protected $currency_obj = NULL;

	protected $user_obj = NULL;

	protected $pay_stub_entry_account_link_obj = NULL;



	protected $pay_stub_entry_accounts_obj = NULL;





	function _getFactoryOptions( $name, $country = NULL ) {



		$retval = NULL;

		switch( $name ) {

			case 'filtered_status':

				$retval = Option::getByArray( array(25,40), $this->getOptions('status') );

				break;

			case 'status':

				$retval = array(

										10 => TTi18n::gettext('NEW'),

										20 => TTi18n::gettext('LOCKED'),

										25 => TTi18n::gettext('Open'),

										30 => TTi18n::gettext('Pending Transaction'),

										40 => TTi18n::gettext('Paid')

									);

				break;

			case 'export_type':

				$retval = array();

				$retval += array( '00' => TTi18n::gettext('-- Direct Deposit --') );

				$retval += $this->getOptions('export_eft');

				$retval += array(

										'01' => '',

										'02' => TTi18n::gettext('-- Laser Cheques --') );

				$retval += $this->getOptions('export_cheque');

				break;

			case 'export_eft':

				$retval = array(

										//EFT formats must start with "eft_"

										'-1010-eft_ACH' => TTi18n::gettext('United States - ACH (94-Byte)'),

										'-1020-eft_1464' => TTi18n::gettext('Canada - EFT (CPA 005/1464-Byte)'),

										'-1030-eft_105' => TTi18n::gettext('Canada - EFT (105-Byte)'),

										'-1040-eft_HSBC' => TTi18n::gettext('Canada - HSBC EFT-PC (CSV)'),

										'-1050-eft_BEANSTREAM' => TTi18n::gettext('Beanstream (CSV)'),

									);

				break;

			case 'export_cheque':

				$retval = array(

										//Cheque formats must start with "cheque_"

										'-2010-cheque_9085' =>   TTi18n::gettext('NEBS #9085'),

										'-2020-cheque_9209p' =>  TTi18n::gettext('NEBS #9209P'),

										'-2030-cheque_dlt103' => TTi18n::gettext('NEBS #DLT103'),

										'-2040-cheque_dlt104' => TTi18n::gettext('NEBS #DLT104'),

										'-2050-cheque_cr_standard_form_1' => TTi18n::gettext('Costa Rica - Std Form 1'),

										'-2060-cheque_cr_standard_form_2' => TTi18n::gettext('Costa Rica - Std Form 2'),

									);

				break;

			case 'columns':

				$retval = array(

										'-1000-first_name' => TTi18n::gettext('First Name'),

										'-1002-last_name' => TTi18n::gettext('Last Name'),

										'-1005-user_status' => TTi18n::gettext('Employee Status'),

										'-1010-title' => TTi18n::gettext('Title'),

										'-1020-user_group' => TTi18n::gettext('Group'),

										'-1030-default_branch' => TTi18n::gettext('Default Branch'),

										'-1040-default_department' => TTi18n::gettext('Default Department'),

										'-1050-city' => TTi18n::gettext('City'),

										'-1060-province' => TTi18n::gettext('Province/State'),

										'-1070-country' => TTi18n::gettext('Country'),

										'-1080-currency' => TTi18n::gettext('Currency'),

										//'-1080-pay_period' => TTi18n::gettext('Pay Period'),



										'-1140-status' => TTi18n::gettext('Status'),

										'-1170-start_date' => TTi18n::gettext('Start Date'),

										'-1180-end_date' => TTi18n::gettext('End Date'),

										'-1190-transaction_date' => TTi18n::gettext('Transaction Date'),



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

								'first_name',

								'last_name',

								'status',

								'start_date',

								'end_date',

								'transaction_date',

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

										'city' => FALSE,

										'province' => FALSE,

										'country' => FALSE,

										'currency' => FALSE,



										'pay_period_id' => 'PayPeriod',

										//'pay_period' => FALSE,

										'currency_id' => 'Currency',

										'currency' => FALSE,

										'currency_rate' => 'CurrencyRate',

										'start_date' => 'StartDate',

										'end_date' => 'EndDate',

										'transaction_date' => 'TransactionDate',

										'status_id' => 'Status',

										'status' => FALSE,

										'status_date' => 'StatusDate',

										'status_by' => 'StatusBy',

										'tainted' => 'Tainted',

										'temp' => 'Temp',

										'deleted' => 'Deleted',

										);

		return $variable_function_map;

	}



	function getPayPeriodObject() {

		if ( is_object($this->pay_period_obj) ) {

			return $this->pay_period_obj;

		} else {

			$pplf = TTnew( 'PayPeriodListFactory' );



			$pplf->getById( $this->getPayPeriod() );

			if ( $pplf->getRecordCount() > 0 ) {

				$this->pay_period_obj = $pplf->getCurrent();

				return $this->pay_period_obj;

			}

		}



		return FALSE;

	}



	function getCurrencyObject() {

		if ( is_object($this->currency_obj) ) {

			return $this->currency_obj;

		} else {

			$clf = TTnew( 'CurrencyListFactory' );



			$clf->getById( $this->getCurrency() );

			if ( $clf->getRecordCount() > 0 ) {

				$this->currency_obj = $clf->getCurrent();

				return $this->currency_obj;

			}

		}



		return FALSE;

	}



	function getUserObject() {

		if ( is_object($this->user_obj) ) {

			return $this->user_obj;

		} else {

			$ulf = TTnew( 'UserListFactory' );

			$ulf->getById( $this->getUser() );

			if ( $ulf->getRecordCount() > 0 ) {

				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;

			}

		}



		return FALSE;

	}



	function getUser() {

		if ( isset($this->data['user_id']) ) {

			return $this->data['user_id'];

		}



		return FALSE;

	}

	function setUser($id) {

		$id = trim($id);



		$ulf = TTnew( 'UserListFactory' );



		if ( $this->Validator->isResultSetWithRows(	'user',

															$ulf->getByID($id),

															TTi18n::gettext('Invalid User')

															) ) {

			$this->data['user_id'] = $id;



			return TRUE;

		}



		return FALSE;

	}



	function getPayPeriod() {

		if ( isset($this->data['pay_period_id']) ) {

			return $this->data['pay_period_id'];

		}



		return FALSE;

	}

	function setPayPeriod($id) {

		$id = trim($id);



		$pplf = TTnew( 'PayPeriodListFactory' );



		if (  $this->Validator->isResultSetWithRows(	'pay_period',

														$pplf->getByID($id),

														TTi18n::gettext('Invalid Pay Period')

														) ) {

			$this->data['pay_period_id'] = $id;



			return TRUE;

		}



		return FALSE;

	}



	function getCurrency() {

		if ( isset($this->data['currency_id']) ) {

			return $this->data['currency_id'];

		}



		return FALSE;

	}

	function setCurrency($id) {

		$id = trim($id);



		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__,10);

		$culf = TTnew( 'CurrencyListFactory' );



		$old_currency_id = $this->getCurrency();



		if (

				$this->Validator->isResultSetWithRows(	'currency',

														$culf->getByID($id),

														TTi18n::gettext('Invalid Currency')

													) ) {



			$this->data['currency_id'] = $id;



			if ( $culf->getRecordCount() == 1

					AND ( $this->isNew() OR $old_currency_id != $id ) ) {

				$this->setCurrencyRate( $culf->getCurrent()->getReverseConversionRate() );

			}



			return TRUE;

		}



		return FALSE;

	}



	function getCurrencyRate() {

		if ( isset($this->data['currency_rate']) ) {

			return $this->data['currency_rate'];

		}



		return FALSE;

	}

	function setCurrencyRate( $value ) {

		$value = trim($value);



		//Pull out only digits and periods.

		$value = $this->Validator->stripNonFloat($value);



		if (	$this->Validator->isFloat(	'currency_rate',

											$value,

											TTi18n::gettext('Incorrect Currency Rate')) ) {



			$this->data['currency_rate'] = $value;



			return TRUE;

		}



		return FALSE;

	}



	function isValidStartDate($epoch) {

		if ( is_object( $this->getPayPeriodObject() ) AND

				( $epoch >= $this->getPayPeriodObject()->getStartDate() AND $epoch < $this->getPayPeriodObject()->getEndDate() ) ) {

			return TRUE;

		}



		return FALSE;

	}



	function getStartDate( $raw = FALSE ) {

		if ( isset($this->data['start_date']) ) {

			if ( $raw === TRUE ) {

				return $this->data['start_date'];

			} else {

				//return $this->db->UnixTimeStamp( $this->data['start_date'] );

				//strtotime is MUCH faster than UnixTimeStamp

				//Must use ADODB for times pre-1970 though.

				return TTDate::strtotime( $this->data['start_date'] );

			}

		}



		return FALSE;

	}

	function setStartDate($epoch) {

		$epoch = trim($epoch);



		if 	(	$this->Validator->isDate(		'start_date',

												$epoch,

												TTi18n::gettext('Incorrect start date'))

				AND

				$this->Validator->isTrue(		'start_date',

												$this->isValidStartDate($epoch),

												TTi18n::gettext('Conflicting start date'))



			) {



			//$this->data['start_date'] = $epoch;

			$this->data['start_date'] = TTDate::getDBTimeStamp($epoch, FALSE);



			return TRUE;

		}



		return FALSE;

	}



	function isValidEndDate($epoch) {

		if ( is_object( $this->getPayPeriodObject() ) AND

				( $epoch <= $this->getPayPeriodObject()->getEndDate() AND $epoch >= $this->getPayPeriodObject()->getStartDate() ) ) {

			return TRUE;

		}



		return FALSE;

	}



	function getEndDate( $raw = FALSE ) {

		if ( isset($this->data['end_date']) ) {

			if ( $raw === TRUE ) {

				return $this->data['end_date'];

			} else {

				//In cases where you set the date, then immediately read it again, it will return -1 unless do this.

				return TTDate::strtotime( $this->data['end_date'] );

			}

		}



		return FALSE;

	}

	function setEndDate($epoch) {

		$epoch = trim($epoch);



		if 	(	$this->Validator->isDate(		'end_date',

												$epoch,

												TTi18n::gettext('Incorrect end date'))

				AND

				$this->Validator->isTrue(		'end_date',

												$this->isValidEndDate($epoch),

												TTi18n::gettext('Conflicting end date'))



			) {



			//$this->data['end_date'] = $epoch;

			$this->data['end_date'] = TTDate::getDBTimeStamp($epoch, FALSE);



			return TRUE;

		}



		return FALSE;

	}



	function isValidTransactionDate($epoch) {

		Debug::Text('Epoch: '. $epoch .' ( '. TTDate::getDate('DATE+TIME', $epoch) .' ) Pay Stub End Date: '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) , __FILE__, __LINE__, __METHOD__,10);

		if ( $epoch >= $this->getEndDate() ) {

			return TRUE;

		}



		return FALSE;

	}



	function getTransactionDate( $raw = FALSE ) {

		//Debug::Text('Transaction Date: '. $this->data['transaction_date'] .' - '. TTDate::getDate('DATE+TIME', $this->data['transaction_date']) , __FILE__, __LINE__, __METHOD__,10);

		if ( isset($this->data['transaction_date']) ) {

			if ( $raw === TRUE ) {

				return $this->data['transaction_date'];

			} else {

				return TTDate::strtotime( $this->data['transaction_date'] );

			}

		}



		return FALSE;

	}

	function setTransactionDate($epoch) {

		$epoch = trim($epoch);



		if 	(	$this->Validator->isDate(		'transaction_date',

												$epoch,

												TTi18n::gettext('Incorrect transaction date'))

			) {



			$this->data['transaction_date'] = TTDate::getDBTimeStamp($epoch, FALSE);



			return TRUE;

		}



		return FALSE;

	}



	function getStatus() {

		return $this->data['status_id'];

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



			$this->setStatusDate();

			$this->setStatusBy();



			$this->data['status_id'] = $status;



			return FALSE;

		}



		return FALSE;

	}



	function getStatusDate() {

		return $this->data['status_date'];

	}

	function setStatusDate($epoch = NULL) {

		$epoch = trim($epoch);



		if ($epoch == NULL) {

			$epoch = TTDate::getTime();

		}



		if 	(	$this->Validator->isDate(		'status_date',

												$epoch,

												TTi18n::gettext('Incorrect Date')) ) {



			$this->data['status_date'] = $epoch;



			return TRUE;

		}



		return FALSE;



	}



	function getStatusBy() {

		return $this->data['status_by'];

	}

	function setStatusBy($id = NULL) {

		$id = trim($id);



		if ( empty($id) ) {

			global $current_user;



			if ( is_object($current_user) ) {

				$id = $current_user->getID();

			} else {

				return FALSE;

			}

		}



		$ulf = TTnew( 'UserListFactory' );



		if ( $this->Validator->isResultSetWithRows(	'created_by',

													$ulf->getByID($id),

													TTi18n::gettext('Incorrect User')

													) ) {

			$this->data['status_by'] = $id;



			return TRUE;

		}



		return FALSE;

	}



	function getTainted() {

		if ( isset($this->data['tainted']) ) {

			return $this->fromBool( $this->data['tainted'] );

		}



		return FALSE;

	}

	function setTainted($bool) {

		$this->data['tainted'] = $this->toBool($bool);



		return true;

	}



	function getTemp() {

		if ( isset($this->data['temp']) ) {

			return $this->fromBool( $this->data['temp'] );

		}



		return FALSE;

	}

	function setTemp($bool) {

		$this->data['temp'] = $this->toBool($bool);



		return TRUE;

	}



	function isUniquePayStub() {

		if ( $this->getTemp() == TRUE ) {

			return TRUE;

		}



		if ( $this->is_unique_pay_stub === NULL ) {

			$ph = array(

						'pay_period_id' => (int)$this->getPayPeriod(),

						'user_id' => (int)$this->getUser(),

						);



			$query = 'select id from '. $this->getTable() .' where pay_period_id = ? AND user_id = ? AND deleted = 0';

			$pay_stub_id = $this->db->GetOne($query, $ph);



			if ( $pay_stub_id === FALSE ) {

				$this->is_unique_pay_stub = TRUE;

			} else {

				if ($pay_stub_id == $this->getId() ) {

					$this->is_unique_pay_stub = TRUE;

				} else {

					$this->is_unique_pay_stub = FALSE;

				}

			}

		}



		return $this->is_unique_pay_stub;

	}



	function setDefaultDates() {

		Debug::text(' NOT Advance!!: ', __FILE__, __LINE__, __METHOD__,10);

		$start_date = $this->getPayPeriodObject()->getStartDate();

		$end_date = $this->getPayPeriodObject()->getEndDate();

		$transaction_date = $this->getPayPeriodObject()->getTransactionDate();



		Debug::Text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date), __FILE__, __LINE__, __METHOD__,10);

		Debug::Text('End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__,10);

		Debug::Text('Transaction Date: '. TTDate::getDate('DATE+TIME', $transaction_date), __FILE__, __LINE__, __METHOD__,10);



		$this->setStartDate( $start_date);

		$this->setEndDate( $end_date );

		$this->setTransactionDate( $transaction_date );



		Debug::Text('bTransaction Date: '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;

	}



	function getEnableProcessEntries() {

		if ( isset($this->process_entries) ) {

			return $this->process_entries;

		}



		return FALSE;

	}

	function setEnableProcessEntries($bool) {

		$this->process_entries = (bool)$bool;



		return TRUE;

	}



	function getEnableCalcYTD() {

		if ( isset($this->calc_ytd) ) {

			return $this->calc_ytd;

		}



		return FALSE;

	}

	function setEnableCalcYTD($bool) {

		$this->calc_ytd = (bool)$bool;



		return TRUE;

	}



	function getEnableLinkedAccruals() {

		if ( isset($this->linked_accruals) ) {

			return $this->linked_accruals;

		}



		return TRUE;

	}

	function setEnableLinkedAccruals($bool) {

		$this->linked_accruals = (bool)$bool;



		return TRUE;

	}



	static function CalcDifferences( $pay_stub_id1, $pay_stub_id2, $ps_amendment_date = NULL ) {

		//PayStub 1 is new.

		//PayStub 2 is old.

		if ( $pay_stub_id1 == '' ) {

			return FALSE;

		}



		if ( $pay_stub_id2 == '' ) {

			return FALSE;

		}



		if ( $pay_stub_id1 == $pay_stub_id2 ) {

			return FALSE;

		}



		Debug::Text('Calculating the differences between Pay Stub: '. $pay_stub_id1 .' And: '. $pay_stub_id2, __FILE__, __LINE__, __METHOD__,10);



		$pslf = TTnew( 'PayStubListFactory' );



		$pslf->StartTransaction();



		$pslf->getById( $pay_stub_id1 );

		if ( $pslf->getRecordCount() > 0 ) {

			$pay_stub1_obj = $pslf->getCurrent();

		} else {

			Debug::Text('Pay Stub1 does not exist: ', __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}



		$pslf->getById( $pay_stub_id2 );

		if ( $pslf->getRecordCount() > 0 ) {

			$pay_stub2_obj = $pslf->getCurrent();

		} else {

			Debug::Text('Pay Stub2 does not exist: ', __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}



		if ($pay_stub1_obj->getUser() != $pay_stub2_obj->getUser() ) {

			Debug::Text('Pay Stubs are from different users!', __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}



		if ( $ps_amendment_date == NULL OR $ps_amendment_date == '' ) {

			Debug::Text('PS Amendment Date not set, trying to figure it out!', __FILE__, __LINE__, __METHOD__,10);

			//Take a guess at the end of the newest open pay period.

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

			$ppslf->getByUserId( $pay_stub2_obj->getUser() );

			if ( $ppslf->getRecordCount() > 0 ) {

				Debug::Text('Found Pay Period Schedule, ID: '. $ppslf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__,10);

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pplf->getByPayPeriodScheduleIdAndTransactionDate( $ppslf->getCurrent()->getId(), time(), NULL, array('a.transaction_date' => 'DESC' ) );

				if ( $pplf->getRecordCount() > 0 ) {

					Debug::Text('Using Pay Period End Date.', __FILE__, __LINE__, __METHOD__,10);

					$ps_amendment_date = TTDate::getBeginDayEpoch( $pplf->getCurrent()->getEndDate() );



				}

			} else {

				Debug::Text('Using Today.', __FILE__, __LINE__, __METHOD__,10);

				$ps_amendment_date = time();

			}

		}

		Debug::Text('Using Date: '. TTDate::getDate('DATE+TIME', $ps_amendment_date), __FILE__, __LINE__, __METHOD__,10);



		//Only do Earnings for now.

		//Get all earnings, EE/ER deduction PS entries.

		$pay_stub1_entry_ids = NULL;

		$pay_stub1_entries = TTnew( 'PayStubEntryListFactory' );

		$pay_stub1_entries->getByPayStubIdAndType( $pay_stub1_obj->getId(), array(10,20,30) );

		if ( $pay_stub1_entries->getRecordCount() > 0 ) {

			Debug::Text('Pay Stub1 Entries DO exist: ', __FILE__, __LINE__, __METHOD__,10);



			foreach( $pay_stub1_entries as $pay_stub1_entry_obj ) {

				$pay_stub1_entry_ids[] = $pay_stub1_entry_obj->getPayStubEntryNameId();

			}

		} else {

			Debug::Text('Pay Stub1 Entries does not exist: ', __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}

		Debug::Arr( $pay_stub1_entry_ids, 'Pay Stub1 Entry IDs: ', __FILE__, __LINE__, __METHOD__,10);



		//var_dump($pay_stub1_entry_ids);



		$pay_stub2_entry_ids = NULL;

		$pay_stub2_entries = TTnew( 'PayStubEntryListFactory' );

		$pay_stub2_entries->getByPayStubIdAndType( $pay_stub2_obj->getId(), array(10,20,30) );

		if ( $pay_stub2_entries->getRecordCount() > 0 ) {

			Debug::Text('Pay Stub2 Entries DO exist: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $pay_stub2_entries as $pay_stub2_entry_obj ) {

				$pay_stub2_entry_ids[] = $pay_stub2_entry_obj->getPayStubEntryNameId();

			}

		} else {

			Debug::Text('Pay Stub2 Entries does not exist: ', __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}

		Debug::Arr( $pay_stub1_entry_ids, 'Pay Stub2 Entry IDs: ', __FILE__, __LINE__, __METHOD__,10);





		$pay_stub_entry_ids = array_unique( array_merge($pay_stub1_entry_ids, $pay_stub2_entry_ids) );

		Debug::Arr( $pay_stub_entry_ids, 'Pay Stub Entry Differences: ', __FILE__, __LINE__, __METHOD__,10);

		//var_dump($pay_stub_entry_ids);



		$pself = TTnew( 'PayStubEntryListFactory' );

		if ( count($pay_stub_entry_ids) > 0 ) {

			foreach( $pay_stub_entry_ids as $pay_stub_entry_id) {

				Debug::Text('Entry ID: '. $pay_stub_entry_id, __FILE__, __LINE__, __METHOD__,10);

				$pay_stub1_entry_arr = $pself->getSumByPayStubIdAndEntryNameIdAndNotPSAmendment( $pay_stub1_obj->getId(), $pay_stub_entry_id);



				$pay_stub2_entry_arr = $pself->getSumByPayStubIdAndEntryNameIdAndNotPSAmendment( $pay_stub2_obj->getId(), $pay_stub_entry_id);

				Debug::Text('Pay Stub1 Amount: '. $pay_stub1_entry_arr['amount'] .' Pay Stub2 Amount: '. $pay_stub2_entry_arr['amount'], __FILE__, __LINE__, __METHOD__,10);



				if ( $pay_stub1_entry_arr['amount'] != $pay_stub2_entry_arr['amount'] ) {

					$amount_diff = bcsub($pay_stub1_entry_arr['amount'], $pay_stub2_entry_arr['amount'], 2);

					$units_diff = abs( bcsub($pay_stub1_entry_arr['units'], $pay_stub2_entry_arr['units'], 2) );

					Debug::Text('FOUND DIFFERENCE of: Amount: '. $amount_diff .' Units: '. $units_diff, __FILE__, __LINE__, __METHOD__,10);



					//Generate PS Amendment.

					$psaf = TTnew( 'PayStubAmendmentFactory' );

					$psaf->setUser( $pay_stub1_obj->getUser() );

					$psaf->setStatus( 'ACTIVE' );

					$psaf->setType( 10 );

					$psaf->setPayStubEntryNameId( $pay_stub_entry_id );



					if ( $units_diff > 0 ) {

						//Re-calculate amount when units are involved, due to rounding issues.

						$unit_rate = Misc::MoneyFormat( bcdiv($amount_diff, $units_diff) );

						$amount_diff = Misc::MoneyFormat( bcmul( $unit_rate, $units_diff ) );

						Debug::Text('bFOUND DIFFERENCE of: Amount: '. $amount_diff .' Units: '. $units_diff .' Unit Rate: '. $unit_rate , __FILE__, __LINE__, __METHOD__,10);



						$psaf->setRate( $unit_rate );

						$psaf->setUnits( $units_diff );

						$psaf->setAmount( $amount_diff );

					} else {

						$psaf->setAmount( $amount_diff );

					}



					$psaf->setDescription( 'Adjustment from Pay Period Ending: '. TTDate::getDate('DATE', $pay_stub2_obj->getEndDate() ) );



					$psaf->setEffectiveDate( TTDate::getBeginDayEpoch( $ps_amendment_date ) );



					if ( $psaf->isValid() ) {

						$psaf->Save();

					}



					unset($amount_diff, $units_diff, $unit_rate);

				} else {

					Debug::Text('No DIFFERENCE!', __FILE__, __LINE__, __METHOD__,10);

				}

			}

		}



		$pslf->CommitTransaction();



		return TRUE;

	}



	function reCalculatePayStubYTD( $pay_stub_id ) {

		//Make sure the entire pay stub object is loaded before calling this.

		if ( $pay_stub_id != '' ) {

			Debug::text('Attempting to recalculate pay stub YTD for pay stub id:'. $pay_stub_id, __FILE__, __LINE__, __METHOD__,10);

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $pay_stub_id );



			if ( $pslf->getRecordCount() == 1 ) {

				$pay_stub = $pslf->getCurrent();



				$pay_stub->loadPreviousPayStub();



				if ( $pay_stub->loadCurrentPayStubEntries() == TRUE ) {



					$pay_stub->setEnableProcessEntries(TRUE);

					$pay_stub->processEntries();



					if ( $pay_stub->isValid() == TRUE ) {

						Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__,10);

						$pay_stub->Save();



						return TRUE;

					}

				} else {

					Debug::text('Failed loading current pay stub entries.', __FILE__, __LINE__, __METHOD__,10);

				}

			}

		}



		return FALSE;

	}



	function reCalculateYTD() {

		Debug::Text('ReCalculating YTD on all newer pay stubs...', __FILE__, __LINE__, __METHOD__,10);

		//Get all pay stubs NEWER then this one.

		$pslf = TTnew( 'PayStubListFactory' );



		//Because this recalculates YTD amounts and accruals which span years, we need to recalculate ALL newer pay stubs.

		//$pslf->getByUserIdAndStartDateAndEndDate( $this->getUser() , $this->getTransactionDate(), TTDate::getEndYearEpoch( $this->getTransactionDate() ) );

		$pslf->getByUserIdAndStartDateAndEndDate( $this->getUser() , $this->getTransactionDate(), time()+(365*86400) );

		$total_pay_stubs = $pslf->getRecordCount();

		if ( $total_pay_stubs > 0 ) {

			$pslf->StartTransaction();



			foreach($pslf as $ps_obj ) {

				$this->reCalculatePayStubYTD( $ps_obj->getId() );

			}



			$pslf->CommitTransaction();

		} else {

			Debug::Text('No Newer Pay Stubs found!', __FILE__, __LINE__, __METHOD__,10);

		}



		return TRUE;

	}





	function preSave() {

		/*

		if ( $this->getEnableProcessEntries() == TRUE ) {

			Debug::Text('Processing PayStub Entries...', __FILE__, __LINE__, __METHOD__,10);



			$this->processEntries();

			//$this->savePayStubEntries();

		} else {

			Debug::Text('NOT Processing PayStub Entries...', __FILE__, __LINE__, __METHOD__,10);

		}

		*/



		return TRUE;

	}



	function Validate() {

		//Make sure we're not submitted two pay stubs for the same pay period per user.

		//Unless the pay period type of Monthly + Advance

		/*

		$pplf = TTnew( 'PayPeriodListFactory' );

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

		$pay_period_type = $ppslf->getById( $pplf->getById( $this->getPayPeriod() )->getCurrent()->getPayPeriodSchedule() )->getCurrent()->getType();

		Debug::Text('Pay Period Type: '. $pay_period_type, __FILE__, __LINE__, __METHOD__,10);

		*/



		if ( $this->getEnableProcessEntries() == TRUE ) {

			$this->ValidateEntries();

		} else {

			Debug::Text('Validating PayStub...', __FILE__, __LINE__, __METHOD__,10);

			//We could re-check these after processEntries are validated,

			//but that might duplicate the error messages?

			if ( $this->isUniquePayStub() == FALSE ) {

				Debug::Text('Unique Pay Stub...', __FILE__, __LINE__, __METHOD__,10);

				$this->Validator->isTrue(		'user',

												FALSE,

												TTi18n::gettext('Invalid unique User and/or Pay Period') );

			}



			if ( $this->getStartDate() == FALSE ) {

					$this->Validator->isDate(		'start_date',

													$this->getStartDate(),

													TTi18n::gettext('Incorrect start date'));

			}

			if ( $this->getEndDate() == FALSE ) {

					$this->Validator->isDate(		'end_date',

													$this->getEndDate(),

													TTi18n::gettext('Incorrect end date'));

			}

			if ( $this->getTransactionDate() == FALSE ) {

					$this->Validator->isDate(		'transaction_date',

													$this->getTransactionDate(),

													TTi18n::gettext('Incorrect transaction date'));

			}



			if ( $this->isValidTransactionDate( $this->getTransactionDate() ) == FALSE ) {

					$this->Validator->isTrue(		'transaction_date',

													FALSE,

													TTi18n::gettext('Transaction date is before pay period end date'));

			}

		}



		return TRUE;

	}



	function ValidateEntries() {

		Debug::Text('Validating PayStub Entries...', __FILE__, __LINE__, __METHOD__,10);



		//Do Pay Stub Entry checks here

		if ( $this->isNew() == FALSE ) {

			//Make sure the pay stub math adds up.

			Debug::Text('Validate: checkEarnings...', __FILE__, __LINE__, __METHOD__,10);

			$this->Validator->isTrue(		'earnings',

											$this->checkNoEarnings(),

											TTi18n::gettext('No Earnings, employee may not have any hours for this pay period, or their wage may not be set') );



			$this->Validator->isTrue(		'earnings',

											$this->checkEarnings(),

											TTi18n::gettext('Earnings don\'t match gross pay') );





			Debug::Text('Validate: checkDeductions...', __FILE__, __LINE__, __METHOD__,10);

			$this->Validator->isTrue(		'deductions',

											$this->checkDeductions(),

											TTi18n::gettext('Deductions don\'t match total deductions') );



			Debug::Text('Validate: checkNetPay...', __FILE__, __LINE__, __METHOD__,10);

			$this->Validator->isTrue(		'net_pay',

											$this->checkNetPay(),

											TTi18n::gettext('Net Pay doesn\'t match earnings or deductions') );

		}



		return $this->Validator->isValid();

	}



	function postSave() {

		$this->removeCache( $this->getId() );



		if ( $this->getEnableProcessEntries() == TRUE ) {

			$this->savePayStubEntries();

		}



		//This needs to be run even if entries aren't being processed,

		//for things like marking the pay stub paid or not.

		$this->handlePayStubAmendmentStatuses();



		if ( $this->getDeleted() == TRUE ) {

			Debug::Text('Deleting Pay Stub, re-calculating YTD ', __FILE__, __LINE__, __METHOD__,10);

			$this->setEnableCalcYTD( TRUE );

		}



		if ( $this->getEnableCalcYTD() == TRUE ) {

			$this->reCalculateYTD();

		}



		return TRUE;

	}



	function handlePayStubAmendmentStatuses() {

		//Mark all PS amendments as 'PAID' if this status is paid.

		//Mark as NEW if the PS is deleted?

		if ( $this->getStatus() == 40 ) {

			$ps_amendment_status_id = 55; //PAID

		} else {

			$ps_amendment_status_id = 52; //INUSE

		}



		//Loop through each entry in current pay stub, if they have

		//a PS amendment ID assigned to them, change the status.

		if ( is_array( $this->tmp_data['current_pay_stub'] ) ) {

			foreach( $this->tmp_data['current_pay_stub'] as $entry_arr ) {

				if ( isset($entry_arr['pay_stub_amendment_id']) AND $entry_arr['pay_stub_amendment_id'] != '' ) {

					Debug::Text('aFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__,10);



					$ps_amendment_ids[] = $entry_arr['pay_stub_amendment_id'];

				}

			}



			unset($entry_arr);

		} elseif ( $this->getStatus() != 10 ) {

			//Instead of loading the current pay stub entries, just run a query instead.

			$pself = TTnew( 'PayStubEntryListFactory' );

			$pself->getByPayStubId( $this->getId() );



			foreach($pself as $pay_stub_entry_obj) {

				if ( $pay_stub_entry_obj->getPayStubAmendment() != FALSE ) {

					Debug::Text('bFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__,10);



					$ps_amendment_ids[] = $pay_stub_entry_obj->getPayStubAmendment();

				}

			}

		}



		if ( isset($ps_amendment_ids) AND is_array($ps_amendment_ids) ) {

			Debug::Text('cFound PS Amendments to change status on...', __FILE__, __LINE__, __METHOD__,10);



			foreach ( $ps_amendment_ids as $ps_amendment_id ) {

				//Set PS amendment status to match Pay stub.

				$psalf = TTnew( 'PayStubAmendmentListFactory' );

				$psalf->getById( $ps_amendment_id );

				if ( $psalf->getRecordCount() == 1 ) {

					Debug::Text('Changing Status of PS Amendment: '. $ps_amendment_id , __FILE__, __LINE__, __METHOD__,10);

					$ps_amendment_obj = $psalf->getCurrent();

					$ps_amendment_obj->setStatus( $ps_amendment_status_id );

					if ( $ps_amendment_obj->isValid() ) {

						$ps_amendment_obj->Save();

					} else {

						Debug::Text('Changing Status of PS Amendment FAILED!: '. $ps_amendment_id , __FILE__, __LINE__, __METHOD__,10);

					}

					unset($ps_amendment_obj);

				}

				unset($psalf);

			}

			unset($ps_amendment_ids);

		}



		return TRUE;

	}



	/*





		Functions used in adding PayStub entries.





	*/

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



	function getPayStubEntryAccountsArray() {

		if ( is_array($this->pay_stub_entry_accounts_obj) ) {

			//Debug::text('Returning Cached data...' , __FILE__, __LINE__, __METHOD__,10);

			return $this->pay_stub_entry_accounts_obj;

		} else {

			$psealf = TTnew( 'PayStubEntryAccountListFactory' );

			$psealf->getByCompanyId( $this->getUserObject()->getCompany() );

			if ( $psealf->getRecordCount() > 0 ) {

				foreach(  $psealf as $psea_obj ) {

					$this->pay_stub_entry_accounts_obj[$psea_obj->getId()] = array(

						'type_id' => $psea_obj->getType(),

						'accrual_pay_stub_entry_account_id' => $psea_obj->getAccrual()

						);

				}



				//Debug::Arr($this->pay_stub_entry_accounts_obj, ' Pay Stub Entry Accounts ('.count($this->pay_stub_entry_accounts_obj).'): ' , __FILE__, __LINE__, __METHOD__,10);

				return $this->pay_stub_entry_accounts_obj;

			}



			Debug::text('Returning FALSE...' , __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}

	}



	function getPayStubEntryAccountArray( $id ) {

		if ( $id == '' ) {

			return FALSE;

		}



		//Debug::text('ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

		$psea = $this->getPayStubEntryAccountsArray();



		if ( isset($psea[$id]) ) {

			return $psea[$id];

		}



		Debug::text('Returning FALSE...' , __FILE__, __LINE__, __METHOD__,10);

		return FALSE;

	}



	function getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, $type_ids = NULL, $ps_account_ids = NULL) {

		Debug::text('PS Entries: '. $ps_entries .' Type ID: '. $type_ids .' PS Account ID: '. $ps_account_ids, __FILE__, __LINE__, __METHOD__,10);



		if ( strtolower($ps_entries) == 'current' ) {

			$entries = $this->tmp_data['current_pay_stub'];

		} elseif ( strtolower($ps_entries) == 'previous' ) {

			$entries = $this->tmp_data['previous_pay_stub']['entries'];

		} elseif ( strtolower($ps_entries) == 'previous+ytd_adjustment' ) {

			$entries = $this->tmp_data['previous_pay_stub']['entries'];

			//Include any YTD adjustment PS amendments in the current entries as if they occurred in the previous pay stub.

			//This so we can account for the first pay stub having a YTD adjustment that exceeds a wage base amount, so no amount is calculated.

			if ( is_array($this->tmp_data['current_pay_stub']) ) {

				foreach( $this->tmp_data['current_pay_stub'] as $current_entry_arr ) {

					if ( isset($current_entry_arr['ytd_adjustment']) AND $current_entry_arr['ytd_adjustment'] === TRUE ) {

						Debug::Text('Found YTD Adjustment in current pay stub when calculating previous pay stub amounts... Amount: '. $current_entry_arr['amount'] , __FILE__, __LINE__, __METHOD__,10);

						//Debug::Arr($current_entry_arr, 'Found YTD Adjustment in current pay stub when calculating previous pay stub amounts...' , __FILE__, __LINE__, __METHOD__,10);

						$entries[] = $current_entry_arr;

					}

				}

				unset($current_entry_arr);

			}

		}

		//Debug::Arr( $entries, 'Sum Entries Array: ', __FILE__, __LINE__, __METHOD__,10);



		if ( !is_array($entries) ) {

			Debug::text('Returning FALSE...' , __FILE__, __LINE__, __METHOD__,10);

			return FALSE;

		}



		if ( $type_ids != '' AND !is_array($type_ids) ) {

			$type_ids = array($type_ids);

		}



		if ( $ps_account_ids != '' AND !is_array($ps_account_ids) ) {

			$ps_account_ids = array($ps_account_ids);

		}



		$retarr = array(

				'units' => 0,

				'amount' => 0,

				'ytd_units' => 0,

				'ytd_amount' => 0,

			);



		foreach( $entries as $key => $entry_arr ) {

			if ( $type_ids != '' AND is_array( $type_ids ) ) {

				foreach( $type_ids as $type_id ) {

					if ( isset($entry_arr['pay_stub_entry_type_id']) AND $type_id == $entry_arr['pay_stub_entry_type_id'] AND $entry_arr['pay_stub_entry_type_id'] != 50 ) {

						if ( isset($entry_arr['ytd_adjustment']) AND $entry_arr['ytd_adjustment'] === TRUE ) {

							//If a PS amendment makes a YTD adjustment, we need to treat it as a regular PS amendment

							//affecting the 'amount' instead of the 'ytd_amount', otherwise it will double up YTD amounts.

							//There are two issues at hand, doubling up YTD amounts, and not counting YTD adjustments

							//towards getting YTD amounts on the current pay stub for things like calculating

							//Wage Base/Maximum contributions.

							//Also, we need to make sure that these amounts aren't included in Tax/Deduction calculations

							//for this pay stub. But ARE calculated in this pay stub if they affect accruals.

							$retarr['ytd_amount'] = bcadd( $retarr['ytd_amount'], $entry_arr['amount'] );

							$retarr['ytd_units'] = bcadd( $retarr['ytd_units'], $entry_arr['units'] );

						} else {

							$retarr['amount'] = bcadd( $retarr['amount'], $entry_arr['amount'] );

							$retarr['units'] = bcadd( $retarr['units'], $entry_arr['units'] );

							$retarr['ytd_amount'] = bcadd( $retarr['ytd_amount'], $entry_arr['ytd_amount'] );

							$retarr['ytd_units'] = bcadd( $retarr['ytd_units'], $entry_arr['ytd_units'] );

						}

					} else {

						//Debug::text('Type ID: '. $type_id .' does not match: '. $entry_arr['pay_stub_entry_type_id'] , __FILE__, __LINE__, __METHOD__,10);

					}

				}

			} elseif ( $ps_account_ids != '' AND is_array($ps_account_ids) ) {

				foreach( $ps_account_ids as $ps_account_id ) {

					if ( isset($entry_arr['pay_stub_entry_account_id']) AND $ps_account_id == $entry_arr['pay_stub_entry_account_id']) {

						if ( isset($entry_arr['ytd_adjustment']) AND $entry_arr['ytd_adjustment'] === TRUE AND $entry_arr['pay_stub_entry_type_id'] != 50 ) {

							$retarr['ytd_amount'] = bcadd( $retarr['ytd_amount'], $entry_arr['amount'] );

							$retarr['ytd_units'] = bcadd( $retarr['ytd_units'], $entry_arr['units'] );

						} else {

							$retarr['amount'] = bcadd( $retarr['amount'], $entry_arr['amount'] );

							$retarr['units'] = bcadd( $retarr['units'], $entry_arr['units'] );

							$retarr['ytd_amount'] = bcadd( $retarr['ytd_amount'], $entry_arr['ytd_amount'] );

							$retarr['ytd_units'] = bcadd( $retarr['ytd_units'], $entry_arr['ytd_units'] );

						}

					}

				}

			}

		}



		//Debug::Arr($retarr, 'SumByEntries RetArr: ', __FILE__, __LINE__, __METHOD__,10);

		return $retarr;

	}



	function loadCurrentPayStubEntries() {

		Debug::Text('aLoading current pay stub entries, Pay Stub ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getId() != '' ) {

			//Get pay stub entries

			$pself = TTnew( 'PayStubEntryListFactory' );

			$pself->getByPayStubId( $this->getID() );

			Debug::Text('bLoading current pay stub entries, Pay Stub ID: '. $this->getId() .' Record Count: '. $pself->getRecordCount() , __FILE__, __LINE__, __METHOD__,10);



			if ( $pself->getRecordCount() > 0 ) {

				$this->tmp_data['current_pay_stub'] = NULL;



				foreach( $pself as $pse_obj ) {

					//Get PSE account type, group by that.

					$psea_arr = $this->getPayStubEntryAccountArray( $pse_obj->getPayStubEntryNameId() );

					if ( is_array( $psea_arr) ) {

						$type_id = $psea_arr['type_id'];

					} else {

						$type_id = NULL;

					}



					//Skip total entries

					if ( $type_id != 40 ) {

						$pse_arr[] = array(

							'id' => $pse_obj->getId(),

							'pay_stub_entry_type_id' => $type_id,

							'pay_stub_entry_account_id' => $pse_obj->getPayStubEntryNameId(),

							'pay_stub_amendment_id' => $pse_obj->getPayStubAmendment(),

							'rate' => $pse_obj->getRate(),

							'units' => $pse_obj->getUnits(),

							'amount' => $pse_obj->getAmount(),

							//'ytd_units' => $pse_obj->getYTDUnits(),

							//'ytd_amount' => $pse_obj->getYTDAmount(),

							//Don't load YTD values, they need to be recalculated.

							'ytd_units' => NULL,

							'ytd_amount' => NULL,

							'description' => $pse_obj->getDescription(),

							);

					}

					unset($type_id, $psea_obj);

				}



				//Debug::Arr($pse_arr, 'RetArr: ', __FILE__, __LINE__, __METHOD__,10);

				if ( isset( $pse_arr ) ) {

					$retarr['entries'] = $pse_arr;



					$this->tmp_data['current_pay_stub'] = $retarr['entries'];



					Debug::Text('Loading current pay stub entries success!', __FILE__, __LINE__, __METHOD__,10);

					return TRUE;

				}

			}



		}

		Debug::Text('Loading current pay stub entries failed!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;

	}



	function loadPreviousPayStub() {

		if ( $this->getUser() == FALSE OR $this->getStartDate() == FALSE ) {

			return FALSE;

		}



		//Grab last pay stub so we can use it for YTD calculations on this pay stub.

		$pslf = TTnew( 'PayStubListFactory' );

		$pslf->getLastPayStubByUserIdAndStartDate( $this->getUser(), $this->getStartDate() );

		if ( $pslf->getRecordCount() > 0 ) {

			$ps_obj = $pslf->getCurrent();

			Debug::text('Loading Data from Pay Stub ID: '. $ps_obj->getId() , __FILE__, __LINE__, __METHOD__,10);



			$retarr = array(

							'id' => $ps_obj->getId(),

							'start_date' => $ps_obj->getStartDate(),

							'end_date' => $ps_obj->getEndDate(),

							'transaction_date' => $ps_obj->getTransactionDate(),

							'entries' => NULL,

							);



			//

			//If previous pay stub is in a different year, only carry forward the accrual accounts.

			//

			$new_year = FALSE;

			if ( TTDate::getYear( $this->getTransactionDate() ) != TTDate::getYear( $ps_obj->getTransactionDate() ) ) {

				Debug::text('Pay Stub Years dont match!...' , __FILE__, __LINE__, __METHOD__,10);

				$new_year = TRUE;

			}



			//Get pay stub entries

			$pself = TTnew( 'PayStubEntryListFactory' );

			$pself->getByPayStubId( $ps_obj->getID() );

			if ( $pself->getRecordCount() > 0 ) {

				foreach( $pself as $pse_obj ) {

					//Get PSE account type, group by that.

					$psea_arr = $this->getPayStubEntryAccountArray( $pse_obj->getPayStubEntryNameId() );

					if ( is_array( $psea_arr) ) {

						$type_id = $psea_arr['type_id'];

					} else {

						$type_id = NULL;

					}



					//If we're just starting a new year, only carry over

					//accrual balances, reset all YTD entries.

					if ( $new_year == FALSE OR $type_id == 50 ) {

						$pse_arr[] = array(

							'id' => $pse_obj->getId(),

							'pay_stub_entry_type_id' => $type_id,

							'pay_stub_entry_account_id' => $pse_obj->getPayStubEntryNameId(),

							'pay_stub_amendment_id' => $pse_obj->getPayStubAmendment(),

							'rate' => $pse_obj->getRate(),

							'units' => $pse_obj->getUnits(),

							'amount' => $pse_obj->getAmount(),

							'ytd_units' => $pse_obj->getYTDUnits(),

							'ytd_amount' => $pse_obj->getYTDAmount(),

							);

					}

					unset($type_id, $psea_obj);

				}



				if ( isset( $pse_arr ) ) {

					$retarr['entries'] = $pse_arr;



					$this->tmp_data['previous_pay_stub'] = $retarr;



					return TRUE;

				}

			}

		}



		Debug::text('Returning FALSE...' , __FILE__, __LINE__, __METHOD__,10);

		return FALSE;

	}



	function addEntry( $pay_stub_entry_account_id, $amount, $units = NULL, $rate = NULL, $description = NULL, $ps_amendment_id = NULL, $ytd_amount = NULL, $ytd_units = NULL, $ytd_adjustment = FALSE ) {

		Debug::text('Add Entry: PSE Account ID: '. $pay_stub_entry_account_id .' Amount: '. $amount .' YTD Amount: '. $ytd_amount .' Pay Stub Amendment Id: '. $ps_amendment_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $pay_stub_entry_account_id == '' ) {

			return FALSE;

		}



		//Round amount to 2 decimal places.

		//So any totaling is proper after this point, because it gets rounded to two decimal places

		//in PayStubEntryFactory too.

		$amount = round( $amount, 2 );

		$ytd_amount = round( $ytd_amount, 2 );



		if ( is_numeric( $amount ) ) {

			$psea_arr = $this->getPayStubEntryAccountArray( $pay_stub_entry_account_id );

			if ( is_array( $psea_arr) ) {

				$type_id = $psea_arr['type_id'];

			} else {

				$type_id = NULL;

			}



			$retarr = array(

				'pay_stub_entry_type_id' => $type_id,

				'pay_stub_entry_account_id' => $pay_stub_entry_account_id,

				'pay_stub_amendment_id' => $ps_amendment_id,

				'rate' => $rate,

				'units' => $units,

				'amount' => $amount, //PHP v5.3.5 has a bug that it converts large values with 0's on the end into scientific notation.

				'ytd_units' => $ytd_units,

				'ytd_amount' => $ytd_amount,

				'description' => $description,

				'ytd_adjustment' => $ytd_adjustment,

				);



			$this->tmp_data['current_pay_stub'][] = $retarr;



			//Check if this pay stub account is linked to an accrual account.

			//Make sure the PSE account does not match the PSE Accrual account,

			//because we don't want to get in to an infinite loop.

			//Also don't touch the accrual account if the amount is 0.

			//This happens mostly when AddUnUsedEntries is called.

			if ( $this->getEnableLinkedAccruals() == TRUE

					AND $amount > 0

					AND $psea_arr['accrual_pay_stub_entry_account_id'] != ''

					AND $psea_arr['accrual_pay_stub_entry_account_id'] != 0

					AND $psea_arr['accrual_pay_stub_entry_account_id'] != $pay_stub_entry_account_id

					AND $ytd_adjustment == FALSE ) {



				Debug::text('Add Entry: PSE Account Links to Accrual Account!: '. $pay_stub_entry_account_id .' Accrual Account ID: '. $psea_arr['accrual_pay_stub_entry_account_id'] .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__,10);



				if ( $type_id == 10 ) {

					$tmp_amount = $amount*-1; //This is an earning... Reduce accrual

				} elseif ( $type_id == 20 ) {

					$tmp_amount = $amount; //This is a employee deduction, add to accrual.

				} else {

					$tmp_amount = 0;

				}

				Debug::text('Amount: '. $tmp_amount , __FILE__, __LINE__, __METHOD__,10);



				return $this->addEntry( $psea_arr['accrual_pay_stub_entry_account_id'], $tmp_amount, NULL, NULL, NULL, NULL, NULL, NULL);

			}



			return TRUE;

		}



		Debug::text('Returning FALSE', __FILE__, __LINE__, __METHOD__,10);



		$this->Validator->isTrue(		'entry',

										FALSE,

										TTi18n::gettext('Invalid Pay Stub entry'));



		return FALSE;

	}



	function processEntries() {

		Debug::Text('Processing PayStub ('. count($this->tmp_data['current_pay_stub']) .') Entries...', __FILE__, __LINE__, __METHOD__,10);

		///Debug::Arr($this->tmp_data['current_pay_stub'], 'Current Entries...', __FILE__, __LINE__, __METHOD__,10);



		$this->deleteEntries( FALSE ); //Delete only total entries

		$this->addUnUsedYTDEntries();

		$this->addEarningSum();

		$this->addDeductionSum();

		$this->addEmployerDeductionSum();

		$this->addNetPay();



		return TRUE;

	}



	function markPayStubEntriesForYTDCalculation( &$pay_stub_arr, $clear_out_ytd = TRUE ) {

		if ( !is_array($pay_stub_arr) ) {

			return FALSE;

		}



		Debug::Text('Marking which entries are to have YTD calculated on!', __FILE__, __LINE__, __METHOD__,10);



		$trace_pay_stub_entry_account_id = array();



		//Loop over the array in reverse

		$pay_stub_arr = array_reverse( $pay_stub_arr, TRUE );

		foreach( $pay_stub_arr as $current_key => $val ) {

			if ( !isset($trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']]) ) {

				$trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']] = 0;

			} else {

				$trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']]++;

			}



			$pay_stub_arr[$current_key]['calc_ytd'] = $trace_pay_stub_entry_account_id[$pay_stub_arr[$current_key]['pay_stub_entry_account_id']];

			//Order here matters in cases for pay stubs with multiple accrual entries.

			//Because if the YTD amount is:

			// -800.00

			//    0.00

			//    0.00

			//We may end up clearing out the only YTD value that is of use.



			//CLEAR_OUT_YTD is used for backwards compat, so old pay stubs that calculated YTD

			//Only duplicate PS entries get zero'd out.

			if ( $clear_out_ytd == TRUE AND $pay_stub_arr[$current_key]['calc_ytd'] > 0 ) {

				//Clear out YTD entries so the sum() function can calculate them properly.

				//This is for backwards compat.

				$pay_stub_arr[$current_key]['ytd_amount'] = 0;

				$pay_stub_arr[$current_key]['ytd_units'] = 0;

			}

		}

		$pay_stub_arr = array_reverse( $pay_stub_arr, TRUE );



		//Debug::Arr($pay_stub_arr, 'Copy Marked Entries ', __FILE__, __LINE__, __METHOD__,10);



		return TRUE;

	}



	function calcPayStubEntriesYTD() {

		if ( !is_array($this->tmp_data['current_pay_stub']) ) {

			return FALSE;

		}



		Debug::Text('Calculating Pay Stub Entry YTD values!', __FILE__, __LINE__, __METHOD__,10);



		$this->markPayStubEntriesForYTDCalculation( $this->tmp_data['previous_pay_stub']['entries'] );

		$this->markPayStubEntriesForYTDCalculation( $this->tmp_data['current_pay_stub'], FALSE ); //Dont clear out YTD values.



		//Debug::Arr($this->tmp_data['current_pay_stub'], 'Before YTD calculation', __FILE__, __LINE__, __METHOD__,10);



		//addUnUsedYTDEntries() should be called before this



		//Go through each pay stub entry, and if there is no entry of the same

		//PSE account id, calc YTD. If there is a duplicate PSE account id,

		//only calculate the YTD on the LAST one.

		foreach( $this->tmp_data['current_pay_stub'] as $key => $entry_arr ) {

			//If YTD is already set, don't recalculate it, because it could be a PS amendment YTD adjustment.

			//Keep in mind this makes it so if a YTD adjustment is set it will show up in the YTD column, and if there

			//is a second PSE account of the same, its YTD will show up too.

			//So this is the ONLY time YTD values should show up for the duplicate PSE accounts on the same PS.

			if ( $entry_arr['calc_ytd'] == 0 ) {

				//Debug::Text('Calculating YTD on PSE account: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

				$current_pay_stub_sum = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $entry_arr['pay_stub_entry_account_id'] );

				$previous_pay_stub_sum = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $entry_arr['pay_stub_entry_account_id'] );



				Debug::Text('Key: '. $key .' Previous YTD Amount: '. $previous_pay_stub_sum['ytd_amount'] .' Current Amount: '. $current_pay_stub_sum['amount'] .' Current YTD Amount: '. $current_pay_stub_sum['ytd_amount'], __FILE__, __LINE__, __METHOD__,10);

				$this->tmp_data['current_pay_stub'][$key]['ytd_amount'] = bcadd( $previous_pay_stub_sum['ytd_amount'], bcadd( $current_pay_stub_sum['amount'], $current_pay_stub_sum['ytd_amount'] ), 2 );

				$this->tmp_data['current_pay_stub'][$key]['ytd_units'] = bcadd( $previous_pay_stub_sum['ytd_units'], bcadd( $current_pay_stub_sum['units'], $current_pay_stub_sum['ytd_units'] ), 4 );

			} elseif ( $this->tmp_data['current_pay_stub'][$key]['ytd_amount'] == '' ) {

				//Debug::Text('Setting YTD on PSE account: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

				$this->tmp_data['current_pay_stub'][$key]['ytd_amount'] = 0;

				$this->tmp_data['current_pay_stub'][$key]['ytd_units'] = 0;

			}

		}



		//Debug::Arr($this->tmp_data['current_pay_stub'], 'After YTD calculation', __FILE__, __LINE__, __METHOD__,10);



		return TRUE;

	}



	function savePayStubEntries() {

		if ( !is_array($this->tmp_data['current_pay_stub']) ) {

			return FALSE;

		}



		//Cant add entries to a new paystub, since the pay_stub_id isn't set yet.

		if ( $this->isNew() == TRUE ) {

			return FALSE;

		}



		$this->calcPayStubEntriesYTD();



		//Debug::Arr($this->tmp_data['current_pay_stub'], 'Current Pay Stub Entries: ', __FILE__, __LINE__, __METHOD__,10);



		foreach( $this->tmp_data['current_pay_stub'] as $pse_arr ) {

			if ( isset($pse_arr['pay_stub_entry_account_id']) AND isset($pse_arr['amount']) ) {

				Debug::Text('Current Pay Stub ID: '. $this->getId() .' Adding Pay Stub Entry for: '. $pse_arr['pay_stub_entry_account_id'] .' Amount: '. $pse_arr['amount'] .' YTD Amount: '. $pse_arr['ytd_amount'] .' YTD Units: '. $pse_arr['ytd_units'], __FILE__, __LINE__, __METHOD__,10);

				$psef = TTnew( 'PayStubEntryFactory' );

				$psef->setPayStub( $this->getId() );

				$psef->setPayStubEntryNameId( $pse_arr['pay_stub_entry_account_id'] );

				$psef->setRate( $pse_arr['rate'] );

				$psef->setUnits( $pse_arr['units'] );

				$psef->setAmount( $pse_arr['amount'] );

				$psef->setYTDAmount( $pse_arr['ytd_amount'] );

				$psef->setYTDUnits( $pse_arr['ytd_units'] );



				$psef->setDescription( $pse_arr['description'] );

				if ( is_numeric( $pse_arr['pay_stub_amendment_id'] ) AND $pse_arr['pay_stub_amendment_id'] > 0 ) {

					$psef->setPayStubAmendment( $pse_arr['pay_stub_amendment_id'] );

				}



				$psef->setEnableCalculateYTD( FALSE );



				if ( $psef->isValid() == FALSE OR $psef->Save() == FALSE ) {

					Debug::Text('Adding Pay Stub Entry failed!', __FILE__, __LINE__, __METHOD__,10);



					$this->Validator->isTrue(		'entry',

													FALSE,

													TTi18n::gettext('Invalid Pay Stub entry'));

					return FALSE;

				}

			}

		}



		return TRUE;

	}



	function deleteEntries( $all_entries = FALSE ) {

		//Delete any entries from the pay stub, so they can be re-created.

		$pself = TTnew( 'PayStubEntryListFactory' );



		if ( $all_entries == TRUE ) {

			$pself->getByPayStubIdAndType( $this->getId(), 40 );

		} else {

			$pself->getByPayStubId( $this->getId() );

		}



		foreach( $pself as $pay_stub_entry_obj ) {

			Debug::Text('Deleting Pay Stub Entry: '. $pay_stub_entry_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

			$del_ps_entry_ids[] = $pay_stub_entry_obj->getId();

		}

		if ( isset($del_ps_entry_ids) ) {

			$pself->bulkDelete( $del_ps_entry_ids );

		}

		unset($pay_stub_entry_obj, $del_ps_entry_ids);



		return TRUE;

	}



	function addUnUsedYTDEntries() {

		Debug::Text('Adding Unused Entries ', __FILE__, __LINE__, __METHOD__,10);

		//This has to happen ABOVE the total entries... So Gross pay and stuff

		//takes them in to account when doing YTD totals

		//

		//Find out which prior entries have been made and carry any YTD entries forward with 0 amounts

		if ( isset($this->tmp_data['previous_pay_stub']) AND is_array( $this->tmp_data['previous_pay_stub']['entries']	) ) {

			//Debug::Arr($this->tmp_data['current_pay_stub'], 'Current Pay Stub Entries:', __FILE__, __LINE__, __METHOD__,10);



			foreach( $this->tmp_data['previous_pay_stub']['entries'] as $key => $entry_arr ) {

				//See if current pay stub entries have previous pay stub entries.

				//Skip total entries, as they will be greated after anyways.

				if ( $entry_arr['pay_stub_entry_type_id'] != 40

						AND Misc::inArrayByKeyAndValue( $this->tmp_data['current_pay_stub'], 'pay_stub_entry_account_id', $entry_arr['pay_stub_entry_account_id'] ) == FALSE ) {

					Debug::Text('Adding UnUsed Entry: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

					$this->addEntry( $entry_arr['pay_stub_entry_account_id'], 0, 0 );

				} else {

					Debug::Text('NOT Adding already existing Entry: '. $entry_arr['pay_stub_entry_account_id'], __FILE__, __LINE__, __METHOD__,10);

				}

			}

		}



		return TRUE;

	}



	function addEarningSum() {

		$sum_arr = $this->getEarningSum();

		Debug::Text('Sum: '. $sum_arr['amount'], __FILE__, __LINE__, __METHOD__,10);

		if ($sum_arr['amount'] > 0) {

			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $sum_arr['amount'], $sum_arr['units'], NULL, NULL, NULL, $sum_arr['ytd_amount'] );

		}

		unset($sum_arr);



		return TRUE;

	}



	function addDeductionSum() {

		$sum_arr = $this->getDeductionSum();

		if ( isset($sum_arr['amount']) ) { //Allow negative amounts for adjustment purposes

			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $sum_arr['amount'], $sum_arr['units'], NULL, NULL, NULL, $sum_arr['ytd_amount'] );

		}

		unset($sum_arr);



		return TRUE;

	}



	function addEmployerDeductionSum() {

		$sum_arr = $this->getEmployerDeductionSum();

		if ( isset($sum_arr['amount']) ) { //Allow negative amounts for adjustment purposes

			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $sum_arr['amount'], $sum_arr['units'], NULL, NULL, NULL, $sum_arr['ytd_amount'] );

		}

		unset($sum_arr);



		return TRUE;

	}



	function addNetPay() {

		$earning_sum_arr = $this->getEarningSum();

		$deduction_sum_arr = $this->getDeductionSum();



		if ( $earning_sum_arr['amount'] > 0 ) {

			Debug::Text('Earning Sum is greater than 0.', __FILE__, __LINE__, __METHOD__,10);



			$net_pay_amount = bcsub( $earning_sum_arr['amount'], $deduction_sum_arr['amount'] );

			$net_pay_ytd_amount = bcsub( $earning_sum_arr['ytd_amount'], $deduction_sum_arr['ytd_amount'] );



			$this->addEntry( $this->getPayStubEntryAccountLinkObject()->getTotalNetPay(), $net_pay_amount, NULL,  NULL, NULL, NULL, $net_pay_ytd_amount );

		}

		unset($net_pay_amount, $net_pay_ytd_amount, $earning_sum_arr, $deduction_sum_arr );



		Debug::Text('Earning Sum is 0 or less. ', __FILE__, __LINE__, __METHOD__,10);



		return TRUE;

	}



	function getEarningSum() {

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 10);

		Debug::Text('Earnings Sum ('. $this->getId() .'): '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		return $retarr;

	}



	function getDeductionSum() {

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 20);

		Debug::Text('Deduction Sum: '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		return $retarr;

	}



	function getEmployerDeductionSum() {

		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', 30);

		Debug::Text('Employer Deduction Sum: '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		return $retarr;

	}



	function getGrossPay() {

		if ( (int)$this->getPayStubEntryAccountLinkObject()->getTotalGross() == 0 ) {

			return FALSE;

		}



		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getTotalGross() );

		Debug::Text('Gross Pay: '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		if ( $retarr['amount'] == '' ) {

			$retarr['amount'] = 0;

		}



		return $retarr['amount'];

	}



	function getDeductions() {

		if ( (int)$this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction() == 0 ) {

			return FALSE;

		}



		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction() );

		Debug::Text('Deductions: '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		if ( $retarr['amount'] == '' ) {

			$retarr['amount'] = 0;

		}



		return $retarr['amount'];

	}



	function getNetPay() {

		if ( (int)$this->getPayStubEntryAccountLinkObject()->getTotalNetPay() == 0 ) {

			return FALSE;

		}



		$retarr = $this->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current' , NULL, $this->getPayStubEntryAccountLinkObject()->getTotalNetPay() );

		Debug::Text('Net Pay: '. $retarr['amount'], __FILE__, __LINE__, __METHOD__,10);



		if ( $retarr['amount'] == '' ) {

			$retarr['amount'] = 0;

		}



		return $retarr['amount'];

	}



	function checkNoEarnings() {

		$earnings = $this->getEarningSum();

		if ($earnings == FALSE OR $earnings['amount'] <= 0 ) {

			return FALSE;

		}



		return TRUE;

	}



	//Returns TRUE unless Amount explicitly does not match Gross Pay

	//use checkNoEarnings to see if any earnings exist or not.

	function checkEarnings() {

		$earnings = $this->getEarningSum();

		if ( isset($earnings['amount']) AND $earnings['amount'] != $this->getGrossPay() ) {

			return FALSE;

		}



		return TRUE;

	}



	function checkDeductions() {

		$deductions = $this->getDeductionSum();

		//Don't check for false here, as advance pay stubs may not have any deductions.

		if ( $deductions['amount'] != $this->getDeductions() ) {

			return FALSE;

		}



		return TRUE;

	}



	function checkNetPay() {

		$net_pay = $this->getNetPay();

		//$tmp_net_pay = number_format($this->getGrossPay() - ( $this->getDeductions() + $this->getAdvanceDeduction() ),2, '.', '');

		$tmp_net_pay = bcsub($this->getGrossPay(), $this->getDeductions() );

		Debug::Text('aCheck Net Pay: Net Pay: '. $net_pay .' Tmp Net Pay: '. $tmp_net_pay, __FILE__, __LINE__, __METHOD__,10);



		//Gotta take precision in to account.

		/*

		$epsilon = 0.00001;

		if (abs($net_pay - $tmp_net_pay) < $epsilon) {

			return TRUE;

		}

		*/



		if ($net_pay == $tmp_net_pay) {

			return TRUE;

		}



		Debug::Text('Check Net Pay: Returning false', __FILE__, __LINE__, __METHOD__,10);

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

						case 'transaction_date':

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



	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {

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

						case 'city':

						case 'province':

						case 'country':

						case 'currency':

							$data[$variable] = $this->getColumn( $variable );

							break;

						case 'user_status':

							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );

							break;

						case 'status':

							$function = 'get'.$variable;

							if ( method_exists( $this, $function ) ) {

								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );

							}

							break;

						case 'start_date':

						case 'end_date':

						case 'transaction_date':

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

			$this->getPermissionColumns( &$data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( &$data, $include_columns );

		}



		return $data;

	}























































	/*



		Below here are functions for generating PDF pay stubs and exporting pay stub data to other

		formats such as cheques, or EFT file formats.



	*/



	function exportPayStub( $pslf = NULL, $export_type = NULL ) {

		global $current_company;



		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );

		}



		if ( get_class( $pslf ) !== 'PayStubListFactory' ) {

			return FALSE;

		}



		if ( $export_type == '' ) {

			return FALSE;

		}



		if ( $pslf->getRecordCount() > 0 ) {



			Debug::Text('aExporting...', __FILE__, __LINE__, __METHOD__,10);

			switch (strtolower($export_type)) {

				case 'eft_hsbc':

				case 'eft_1464':

				case 'eft_105':

				case 'eft_ach':

				case 'eft_beanstream':

					//Get file creation number

					$ugdlf = TTnew( 'UserGenericDataListFactory' );

					$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), 'PayStubFactory', TRUE );

					if ( $ugdlf->getRecordCount() > 0 ) {

						$ugd_obj = $ugdlf->getCurrent();

						$setup_data = $ugd_obj->getData();

					} else {

						$ugd_obj = TTnew( 'UserGenericDataFactory' );

					}



					Debug::Text('bExporting...', __FILE__, __LINE__, __METHOD__,10);

					//get User Bank account info

					$balf = TTnew( 'BankAccountListFactory' );

					$balf->getCompanyAccountByCompanyId( $current_company->getID() );

					if ( $balf->getRecordCount() > 0 ) {

						$company_bank_obj = $balf->getCurrent();

						//Debug::Arr($company_bank_obj,'Company Bank Object', __FILE__, __LINE__, __METHOD__,10);

					}



					if ( isset( $setup_data['file_creation_number'] ) ) {

						$setup_data['file_creation_number']++;

					} else {

						//Start at a high number, in attempt to eliminate conflicts.

						$setup_data['file_creation_number'] = 500;

					}

					Debug::Text('bFile Creation Number: '. $setup_data['file_creation_number'], __FILE__, __LINE__, __METHOD__,10);



					//Increment file creation number in DB

					if ( $ugd_obj->getId() == '' ) {

							$ugd_obj->setID( $ugd_obj->getId() );

					}

					$ugd_obj->setCompany( $current_company->getId() );

					$ugd_obj->setScript( 'PayStubFactory' );

					$ugd_obj->setName( 'PayStubFactory' );

					$ugd_obj->setData( $setup_data );

					$ugd_obj->setDefault( TRUE );

					if ( $ugd_obj->isValid() ) {

							$ugd_obj->Save();

					}



					$eft = new EFT();

					$eft->setFileFormat( str_replace('eft_', '', $export_type ) );



					$eft->setOriginatorID( $current_company->getOriginatorID() );

					$eft->setFileCreationNumber( $setup_data['file_creation_number'] );

					$eft->setDataCenter( $current_company->getDataCenterID() );

					$eft->setOriginatorShortName( $current_company->getShortName() );



					$psealf = TTnew( 'PayStubEntryAccountListFactory' );

					foreach ($pslf as $key => $pay_stub_obj) {

						Debug::Text('Looping over Pay Stub... ID: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);



						//Get pay stub entries.

						$pself = TTnew( 'PayStubEntryListFactory' );

						$pself->getByPayStubId( $pay_stub_obj->getId() );



						$prev_type = NULL;

						$description_subscript_counter = 1;

						foreach ($pself as $pay_stub_entry) {

							$description_subscript = NULL;



							//$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();

							$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



							if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

								$type = $pay_stub_entry_name_obj->getType();

							}



							//var_dump( $pay_stub_entry->getDescription() );

							if ( $pay_stub_entry->getDescription() !== NULL

									AND $pay_stub_entry->getDescription() !== FALSE

									AND strlen($pay_stub_entry->getDescription()) > 0) {

								$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																		'description' => $pay_stub_entry->getDescription() );



								$description_subscript = $description_subscript_counter;



								$description_subscript_counter++;

							}



							if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

								$pay_stub_entries[$type][] = array(

															'id' => $pay_stub_entry->getId(),

															'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

															'type' => $pay_stub_entry_name_obj->getType(),

															'name' => $pay_stub_entry_name_obj->getName(),

															'display_name' => $pay_stub_entry_name_obj->getName(),

															'rate' => $pay_stub_entry->getRate(),

															'units' => $pay_stub_entry->getUnits(),

															'ytd_units' => $pay_stub_entry->getYTDUnits(),

															'amount' => $pay_stub_entry->getAmount(),

															'ytd_amount' => $pay_stub_entry->getYTDAmount(),



															'description' => $pay_stub_entry->getDescription(),

															'description_subscript' => $description_subscript,



															'created_date' => $pay_stub_entry->getCreatedDate(),

															'created_by' => $pay_stub_entry->getCreatedBy(),

															'updated_date' => $pay_stub_entry->getUpdatedDate(),

															'updated_by' => $pay_stub_entry->getUpdatedBy(),

															'deleted_date' => $pay_stub_entry->getDeletedDate(),

															'deleted_by' => $pay_stub_entry->getDeletedBy()

															);

							}



							$prev_type = $pay_stub_entry_name_obj->getType();

						}



						if ( isset($pay_stub_entries) ) {

							$pay_stub = array(

												'id' => $pay_stub_obj->getId(),

												'display_id' => str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT),

												'user_id' => $pay_stub_obj->getUser(),

												'pay_period_id' => $pay_stub_obj->getPayPeriod(),

												'start_date' => $pay_stub_obj->getStartDate(),

												'end_date' => $pay_stub_obj->getEndDate(),

												'transaction_date' => $pay_stub_obj->getTransactionDate(),

												'status' => $pay_stub_obj->getStatus(),

												'entries' => $pay_stub_entries,



												'created_date' => $pay_stub_obj->getCreatedDate(),

												'created_by' => $pay_stub_obj->getCreatedBy(),

												'updated_date' => $pay_stub_obj->getUpdatedDate(),

												'updated_by' => $pay_stub_obj->getUpdatedBy(),

												'deleted_date' => $pay_stub_obj->getDeletedDate(),

												'deleted_by' => $pay_stub_obj->getDeletedBy()

											);

							unset($pay_stub_entries);



							//Get User information

							$ulf = TTnew( 'UserListFactory' );

							$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



							//Get company information

							$clf = TTnew( 'CompanyListFactory' );

							$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



							//get User Bank account info

							$balf = TTnew( 'BankAccountListFactory' );

							$user_bank_obj = $balf->getUserAccountByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getId() );

							if ( $user_bank_obj->getRecordCount() > 0 ) {

								$user_bank_obj = $user_bank_obj->getCurrent();

							} else {

								continue;

							}



							$record = new EFT_Record();

							$record->setType('C');



							$amount = $pay_stub['entries'][40][0]['amount'];

							$record->setCPACode(200);



							$record->setAmount( $amount );

							unset($amount);



							$record->setDueDate( TTDate::getBeginDayEpoch($pay_stub_obj->getTransactionDate()) );

							//$record->setDueDate( strtotime("24-Sep-99") );



							$record->setInstitution( $user_bank_obj->getInstitution() );

							$record->setTransit( $user_bank_obj->getTransit() );

							$record->setAccount( $user_bank_obj->getAccount() );

							$record->setName( $user_obj->getFullName() );



							$record->setOriginatorShortName( $company_obj->getShortName() );

							$record->setOriginatorLongName( substr($company_obj->getName(),0,30) );

							$record->setOriginatorReferenceNumber( 'TT'.$pay_stub_obj->getId() );



							if ( isset($company_bank_obj) AND is_object($company_bank_obj) ) {

								$record->setReturnInstitution( $company_bank_obj->getInstitution() );

								$record->setReturnTransit( $company_bank_obj->getTransit() );

								$record->setReturnAccount( $company_bank_obj->getAccount() );

							}



							$eft->setRecord( $record );



							$this->getProgressBarObject()->set( NULL, $key );

						}

					}



					$eft->compile();

					$output = $eft->getCompiledData();

					break;

				case 'cheque_9085':

				case 'cheque_9209p':

				case 'cheque_dlt103':

				case 'cheque_dlt104':

				case 'cheque_cr_standard_form_1':

				case 'cheque_cr_standard_form_2':

					$border = 0;

					$show_background = 0;



					$pdf = new TTPDF();

					$pdf->setMargins(0,0,0,0);

					$pdf->SetAutoPageBreak(FALSE);

					$pdf->SetFont('freeserif','',10);



					$psealf = TTnew( 'PayStubEntryAccountListFactory' );



					$i=0;

					foreach ($pslf as $pay_stub_obj) {

						//Get pay stub entries.

						$pself = TTnew( 'PayStubEntryListFactory' );

						$pself->getByPayStubId( $pay_stub_obj->getId() );



						$pay_stub_entries = NULL;

						$prev_type = NULL;

						$description_subscript_counter = 1;

						foreach ($pself as $pay_stub_entry) {

							$description_subscript = NULL;



							//$pay_stub_entry_name_obj = $psenlf->getById( $pay_stub_entry->getPayStubEntryNameId() ) ->getCurrent();

							$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



							//Use this to put the total for each type at the end of the array.

							if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

								$type = $pay_stub_entry_name_obj->getType();

							}

							//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



							//var_dump( $pay_stub_entry->getDescription() );

							if ( $pay_stub_entry->getDescription() !== NULL

									AND $pay_stub_entry->getDescription() !== FALSE

									AND strlen($pay_stub_entry->getDescription()) > 0) {

								$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																		'description' => $pay_stub_entry->getDescription() );



								$description_subscript = $description_subscript_counter;



								$description_subscript_counter++;

							}



							$amount_words = str_pad( ucwords( Numbers_Words::toWords( floor($pay_stub_entry->getAmount()),"en_US") ).' ', 65, "-", STR_PAD_RIGHT );

							//echo "Amount: ". floor($pay_stub_entry->getAmount()) ." - Words: ". $amount_words ."<br>\n";

							//var_dump($amount_words);

							if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

								$pay_stub_entries[$type][] = array(

															'id' => $pay_stub_entry->getId(),

															'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

															'type' => $pay_stub_entry_name_obj->getType(),

															'name' => $pay_stub_entry_name_obj->getName(),

															'display_name' => $pay_stub_entry_name_obj->getName(),

															'rate' => $pay_stub_entry->getRate(),

															'units' => $pay_stub_entry->getUnits(),

															'ytd_units' => $pay_stub_entry->getYTDUnits(),

															'amount' => $pay_stub_entry->getAmount(),

															'amount_padded' => str_pad($pay_stub_entry->getAmount(),12,'*', STR_PAD_LEFT),

															'amount_words' => $amount_words,

															'amount_cents' => Misc::getAfterDecimal($pay_stub_entry->getAmount()),

															'ytd_amount' => $pay_stub_entry->getYTDAmount(),



															'description' => $pay_stub_entry->getDescription(),

															'description_subscript' => $description_subscript,



															'created_date' => $pay_stub_entry->getCreatedDate(),

															'created_by' => $pay_stub_entry->getCreatedBy(),

															'updated_date' => $pay_stub_entry->getUpdatedDate(),

															'updated_by' => $pay_stub_entry->getUpdatedBy(),

															'deleted_date' => $pay_stub_entry->getDeletedDate(),

															'deleted_by' => $pay_stub_entry->getDeletedBy()

															);

							}

							unset($amount_words);

							//Only for net pay, make a total YTD of Advance plus Net.

							/*

							if ( $type == 40 ) {

								$pay_stub_entries[$type][0]['ytd_net_plus_advance'] =

							}

							*/



							$prev_type = $pay_stub_entry_name_obj->getType();

						}



						//Get User information

						$ulf = TTnew( 'UserListFactory' );

						$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



						//Get company information

						$clf = TTnew( 'CompanyListFactory' );

						$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



						if ( $user_obj->getCountry() == 'CA' ) {

							$date_format = 'd/m/Y';

						} else {

							$date_format = 'm/d/Y';

						}

						$pay_stub = array(

											'id' => $pay_stub_obj->getId(),

											'display_id' => str_pad($pay_stub_obj->getId(),15,0, STR_PAD_LEFT),

											'user_id' => $pay_stub_obj->getUser(),

											'pay_period_id' => $pay_stub_obj->getPayPeriod(),

											'start_date' => $pay_stub_obj->getStartDate(),

											'end_date' => $pay_stub_obj->getEndDate(),

											'transaction_date' => $pay_stub_obj->getTransactionDate(),

											'transaction_date_display' => date( $date_format, $pay_stub_obj->getTransactionDate() ),

											'status' => $pay_stub_obj->getStatus(),

											'entries' => $pay_stub_entries,

											'tainted' => $pay_stub_obj->getTainted(),



											'created_date' => $pay_stub_obj->getCreatedDate(),

											'created_by' => $pay_stub_obj->getCreatedBy(),

											'updated_date' => $pay_stub_obj->getUpdatedDate(),

											'updated_by' => $pay_stub_obj->getUpdatedBy(),

											'deleted_date' => $pay_stub_obj->getDeletedDate(),

											'deleted_by' => $pay_stub_obj->getDeletedBy()

										);

						unset($pay_stub_entries);



						Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



						//Get Pay Period information

						$pplf = TTnew( 'PayPeriodListFactory' );

						$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



						$pp_start_date = $pay_period_obj->getStartDate();

						$pp_end_date = $pay_period_obj->getEndDate();

						$pp_transaction_date = $pay_period_obj->getTransactionDate();



						//Get pay period numbers

						$ppslf = TTnew( 'PayPeriodScheduleListFactory' );

						$pay_period_schedule_obj = $ppslf->getById( $pay_period_obj->getPayPeriodSchedule() )->getCurrent();



						$pay_period_data = array(

												'start_date' => TTDate::getDate('DATE', $pp_start_date ),

												'end_date' => TTDate::getDate('DATE', $pp_end_date ),

												'transaction_date' => TTDate::getDate('DATE', $pp_transaction_date ),

												//'pay_period_number' => $pay_period_schedule_obj->getCurrentPayPeriodNumber( $pay_period_obj->getTransactionDate(), $pay_period_obj->getEndDate() ),

												'annual_pay_periods' => $pay_period_schedule_obj->getAnnualPayPeriods()

												);



						$pdf->AddPage();



						switch ( $export_type ) {

							case 'cheque_9085':

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_9085.jpg',0,0,210,300);

								}



								$pdf->setXY( Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(42, $adjust_y) );

								$pdf->Cell(100,5, $pay_stub['entries'][40][0]['amount_words'], $border, 0, 'L');

								$pdf->Cell(15,5, $pay_stub['entries'][40][0]['amount_cents'] .'/100', $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(130, $adjust_x), Misc::AdjustXY(50, $adjust_y) );

								$pdf->Cell(38,5, $pay_stub['transaction_date_display'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(175, $adjust_x),Misc::AdjustXY(50, $adjust_y));

								$pdf->Cell(23,5, ' '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(55, $adjust_y) );

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(60, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getAddress1() .' '. $user_obj->getAddress2() ,$border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(17, $adjust_x),  Misc::AdjustXY(65, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '.$user_obj->getPostalCode() ,$border, 0, 'L');





								//Cheque Stub

								$stub_2_offset = 95;



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x), Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y) );

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								break;

							case 'cheque_9209p':

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_9209P.jpg',0,0,210,300);

								}



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x),Misc::AdjustXY(42, $adjust_y));

								$pdf->Cell(100,10, $pay_stub['entries'][40][0]['amount_words'], $border, 0, 'L');

								$pdf->Cell(15,10, $pay_stub['entries'][40][0]['amount_cents'] .'/100', $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(25, $adjust_y));

								$pdf->Cell(10,10, TTi18n::gettext('Date:').' ', $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(182, $adjust_x),Misc::AdjustXY(25, $adjust_y));

								$pdf->Cell(25,10, $pay_stub['transaction_date_display'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(42, $adjust_y));

								$pdf->Cell(35,10, $pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(57, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(62, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getAddress1() .' '. $user_obj->getAddress2() ,$border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(67, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '.$user_obj->getPostalCode() ,$border, 0, 'L');





								//Cheque Stub

								$stub_2_offset = 100;



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								break;

							case 'cheque_dlt103':

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_dlt103.jpg',0,0,210,300);

								}



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x),Misc::AdjustXY(54, $adjust_y));

								$pdf->Cell(100,10, $pay_stub['entries'][40][0]['amount_words'], $border, 0, 'L');

								$pdf->Cell(15,10, $pay_stub['entries'][40][0]['amount_cents'] .'/100', $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(33, $adjust_y));

								$pdf->Cell(10,10, TTi18n::gettext('Date:').' ', $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(182, $adjust_x),Misc::AdjustXY(33, $adjust_y));

								$pdf->Cell(25,10, $pay_stub['transaction_date_display'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(46, $adjust_y));

								$pdf->Cell(35,10, $pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(46, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');





								//Cheque Stub

								$stub_2_offset = 100;



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');

								break;

							case 'cheque_dlt104':

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_dlt104.jpg',0,0,210,300);

								}



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x),Misc::AdjustXY(52, $adjust_y));

								$pdf->Cell(100,10, $pay_stub['entries'][40][0]['amount_words'], $border, 0, 'L');

								$pdf->Cell(15,10, $pay_stub['entries'][40][0]['amount_cents'] .'/100', $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(33, $adjust_y));

								$pdf->Cell(10,10, TTi18n::gettext('Date:').' ', $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(182, $adjust_x),Misc::AdjustXY(33, $adjust_y));

								$pdf->Cell(25,10, $pay_stub['transaction_date_display'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(172, $adjust_x),Misc::AdjustXY(43, $adjust_y));

								$pdf->Cell(35,10, $pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'C');



								$pdf->setXY(Misc::AdjustXY(25, $adjust_x), Misc::AdjustXY(48, $adjust_y));

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');





								//Cheque Stub

								$stub_2_offset = 100;



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: '). $pay_stub_obj->getCurrencyObject()->getSymbol() . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');

								break;

							case 'cheque_cr_standard_form_1':

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_9085.jpg',0,0,210,300);

								}



								$pdf->setXY( Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(41, $adjust_y) );

								$pdf->Cell(100,5, $pay_stub['entries'][40][0]['amount_words'] . TTi18n::gettext(' and ') .  $pay_stub['entries'][40][0]['amount_cents'] .'/100 *****', $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(100, $adjust_x), Misc::AdjustXY(23, $adjust_y) );

								$pdf->Cell(38,5, $pay_stub['transaction_date_display'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(136, $adjust_x),Misc::AdjustXY(32, $adjust_y));

								$pdf->Cell(24,5, '  $' .$pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(33, $adjust_y) );

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');





								//Cheque Stub

								$stub_2_offset = 95;



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, $user_obj->getFullName(), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x), Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Identification #:').' '. $pay_stub['display_id'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x), Misc::AdjustXY(110, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(110+$stub_2_offset, $adjust_y) );

								$pdf->Cell(50,5, TTi18n::gettext('Pay Start Date:').' '. TTDate::getDate('DATE', $pay_stub['start_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(115+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Pay End Date:').' '. TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');

								$pdf->setXY(Misc::AdjustXY(160, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(50,5, TTi18n::gettext('Payment Date:').' '. TTDate::getDate('DATE', $pay_stub['transaction_date'] ), $border, 0, 'L');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: $') . $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(15, $adjust_x),Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->Cell(40,5, TTi18n::gettext('Net Pay: $'). $pay_stub['entries'][40][0]['amount'], $border, 0, 'L');



								//Signature lines



								$pdf->setXY( Misc::AdjustXY(7, $adjust_x), Misc::AdjustXY(250, $adjust_y) );



								$border = 0;

								$pdf->Cell(40,5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');

								$pdf->Cell(60,5, '_____________________________' , $border, 0, 'L');

								$pdf->Cell(40,5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');

								$pdf->Cell(60,5, '_____________________________' , $border, 0, 'L');



								$pdf->Ln();

								$pdf->Cell(40,5, '', $border, 0, 'R');

								$pdf->Cell(60,5, $user_obj->getFullName() , $border, 0, 'C');



								$pdf->Ln();

								$pdf->Cell(147,5, '', $border, 0, 'R');

								$pdf->Cell(60,5, '_____________________________' , $border, 0, 'C');



								$pdf->Ln();

								$pdf->Cell(140,5, '', $border, 0, 'R');

								$pdf->Cell(60,5, TTi18n::gettext('(print name)'), $border, 0, 'C');



								break;



							case 'cheque_cr_standard_form_2':

								$pdf_created_date = time();

								$adjust_x = 0;

								$adjust_y = -5;



								if ( $show_background == 1 ) {

									$pdf->Image(Environment::getBasePath().'interface/images/nebs_cheque_9085.jpg',0,0,210,300);

								}



								$pdf->setXY( Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(41, $adjust_y) );

								$pdf->Cell(100,5, $pay_stub['entries'][40][0]['amount_words'] . TTi18n::gettext(' and ') .  $pay_stub['entries'][40][0]['amount_cents'] .'/100 *****', $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(100, $adjust_x), Misc::AdjustXY(23, $adjust_y) );

								$pdf->Cell(38,5, $pay_stub['transaction_date_display'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(136, $adjust_x),Misc::AdjustXY(32, $adjust_y));

								$pdf->Cell(24,5, '$  ' .$pay_stub['entries'][40][0]['amount_padded'], $border, 0, 'L');



								$pdf->setXY(Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(33, $adjust_y) );

								$pdf->Cell(100,5, $user_obj->getFullName(), $border, 0, 'L');



								//Cheque Stub

								$stub_2_offset = 110;



								$pdf->SetFont('','U',14);

								$pdf->setXY(Misc::AdjustXY(65, $adjust_x), Misc::AdjustXY(100, $adjust_y));

								$pdf->Cell(75,5, TTi18n::gettext('Recipient Copy:'), $border, 0, 'C');



								$pdf->SetFont('','',10);

								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(110, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Date of Issue:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(130,5, TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(110+$stub_2_offset, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Date of Issue:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(130,5, TTDate::getDate('DATE+TIME', $pdf_created_date ), $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(120, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Recipient:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(110,5, $user_obj->getFullName(), $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(120+$stub_2_offset, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Recipient:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(130,5, $user_obj->getFullName(), $border, 0, 'J');



								//Earnings

								$pdf->setXY(Misc::AdjustXY(75, $adjust_x),Misc::AdjustXY(130, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Amount:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(100,5, ' $'. $pay_stub['entries'][40][0]['amount'], $border, 0, 'J');

								$pdf->setXY(Misc::AdjustXY(75, $adjust_x),Misc::AdjustXY(130+$stub_2_offset, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Amount:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(100,5, ' $'. $pay_stub['entries'][40][0]['amount'], $border, 0, 'J');



								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(140, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Regarding:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(100,5, TTi18n::gettext('Payment from') .' '. TTDate::getDate('DATE', $pay_stub['start_date'] ).' '. TTi18n::gettext('to').' '.TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'J');

								$pdf->setXY(Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY(140+$stub_2_offset, $adjust_y));

								$pdf->SetFont('','B',10);

								$pdf->Cell(30,5, TTi18n::gettext('Regarding:'), $border, 0, 'J');

								$pdf->SetFont('','',10);

								$pdf->Cell(100,5, TTi18n::gettext('Payment from') .' '. TTDate::getDate('DATE', $pay_stub['start_date'] ).' '. TTi18n::gettext('to').' '.TTDate::getDate('DATE', $pay_stub['end_date'] ), $border, 0, 'J');



								$pdf->SetFont('','U',14);

								$pdf->setXY(Misc::AdjustXY(65, $adjust_x), Misc::AdjustXY(210, $adjust_y));

								$pdf->Cell(75,5, $company_obj->getName().' '.TTi18n::gettext('Copy:'), $border, 0, 'C');



								$pdf->setXY( Misc::AdjustXY(30, $adjust_x), Misc::AdjustXY(260, $adjust_y) );



								$column_widths = array(

										'generated_by' => 25,

										'signed_by' => 25,

										'received_by' => 35,

										'date' => 35,

										'sin_ssn' => 35,

										);



								$line_h = 4;

								$cell_h_min = $cell_h_max = $line_h * 4;



								$pdf->SetFont('','',8);

								$pdf->setFillColor(255,255,255);

								$pdf->MultiCell( $column_widths['generated_by'], $line_h, TTi18n::gettext('Generated By'). "\n\n\n " , 1, 'C', 1, 0);

								$pdf->MultiCell( $column_widths['signed_by'], $line_h, TTi18n::gettext('Signed By'). "\n\n\n " , 1, 'C', 1, 0);

								$pdf->MultiCell( $column_widths['received_by'], $line_h, TTi18n::gettext('Received By') . "\n\n\n " , 'T,L,B', 'C', 1, 0);

								$pdf->MultiCell( $column_widths['date'], $line_h, TTi18n::gettext('Date') . "\n\n\n ", 'T,B', 'C', 1, 0);

								$pdf->MultiCell( $column_widths['sin_ssn'], $line_h, TTi18n::gettext('SIN / SSN') . "\n\n\n " , 'T,R,B', 'C', 1, 0);

								$pdf->Ln();

								$pdf->SetFont('','',10);



								break;

						}



						$this->getProgressBarObject()->set( NULL, $i );



						$i++;

					}



					$output = $pdf->Output('','S');



					break;

			}

		}



		if ( isset($output) ) {

			return $output;

		}



		return FALSE;

	}



	function getPayStub( $pslf = NULL, $hide_employer_rows = TRUE ) {

		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );

		}



		if ( get_class( $pslf ) !== 'PayStubListFactory' ) {

			return FALSE;

		}



		$border = 0;



		if ( $pslf->getRecordCount() > 0 ) {



			//$pdf = new TTPDF('P','mm','Letter');
		
			$pdf = new TTPDF('L','mm','A5');//ARSP EDIT--> I CHANGE THIS CODE FOR CHILDFUND
//			$pdf = new TTPDF('P','mm','A4');//----@widanage change code here----17.04.2013

			$pdf->setMargins(0,0);

			//$pdf->SetAutoPageBreak(TRUE, 30);

			$pdf->SetAutoPageBreak(FALSE);

			$pdf->SetFont('freeserif','',10);

			//$pdf->SetFont('FreeSans','',10);



			$i=0;

			foreach ($pslf as $pay_stub_obj) {

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );



				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



				//Get Pay Period information

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



				//Use Pay Stub dates, not Pay Period dates.

				$pp_start_date = $pay_stub_obj->getStartDate();

				$pp_end_date = $pay_stub_obj->getEndDate();

				$pp_transaction_date = $pay_stub_obj->getTransactionDate();



				//Get User information

				$ulf = TTnew( 'UserListFactory' );

				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



				//Get company information

				$clf = TTnew( 'CompanyListFactory' );

				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



				//Change locale to users own locale.

				TTi18n::setCountry( $user_obj->getCountry() );

				TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );

				TTi18n::setLocale();



				//

				// Pay Stub Header

				//

				$pdf->AddPage();



				$adjust_x = 20;

				$adjust_y = 5;



				//Logo

				$pdf->Image( $company_obj->getLogoFileName() ,Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



				//Company name/address

				$pdf->SetFont('','B',12);

				$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				$pdf->Cell(75,4,$company_obj->getName(), $border, 0, 'C');



				$pdf->SetFont('','',9);

				$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

				$pdf->Cell(75,4,$company_obj->getAddress1().' '.$company_obj->getAddress2(), $border, 0, 'C');



				$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				$pdf->Cell(75,4,$company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode()), $border, 0, 'C');



				//Pay Period info

				$pdf->SetFont('','',9);

				$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				$pdf->Cell(30,3,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'R');

				$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				$pdf->Cell(30,3,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'R');

				

				$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				$pdf->Cell(30,3,TTi18n::gettext('Payment Date:').' ', $border, 0, 'R');



				$pdf->SetFont('','B',9);

				$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				$pdf->Cell(20,3, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'R');

				$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				$pdf->Cell(20,3, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'R');

				$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				$pdf->Cell(20,3, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'R');







//-------@widanage add code from footer----17.04.2013------

				$pdf->setLineWidth( 1 );

				$pdf->SetFont('','B',12);

				

				$pdf->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				$pdf->Cell(10, 3, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');

				

				$pdf->SetFont('','B',10);

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(17, $adjust_y) );
			
				$pdf->Cell(10, 3, $user_obj->getFirstName() .' '.$user_obj->getMiddleName() .' '.$user_obj->getLastName() .'  ( Emp. #'.$user_obj->getEmployeeNumber().') ', $border, 0, 'L');
				// $pdf->Cell(10, 3, $user_obj->getFullName() .'  ( Emp. #'.$user_obj->getEmployeeNumber().') ', $border, 0, 'L');

//-------@widanage add code from footer----17.04.2013------

				

				

				//Line

				$pdf->setLineWidth( 0.75 );
                                $adjust_y = $adjust_y-4;//fl added for rosen 
				$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y), Misc::AdjustXY(193, $adjust_y), Misc::AdjustXY(27, $adjust_y) );



				$pdf->SetFont('','B',12);
                                $adjust_y = $adjust_y-2;//fl added for rosen 
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				$pdf->Cell(175, 3, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line

				$pdf->setLineWidth( 0.75 );
                                
                                $adjust_y = $adjust_y-2;//fl added for rosen 
				$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y), Misc::AdjustXY(197, $adjust_y), Misc::AdjustXY(37, $adjust_y) );



				$pdf->setLineWidth( 0.25 );



				//Get pay stub entries.

				$pself = TTnew( 'PayStubEntryListFactory' );

				$pself->getByPayStubId( $pay_stub_obj->getId() );

				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);



				$max_widths = array( 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 );

				$prev_type = NULL;

				$description_subscript_counter = 1;

				foreach ($pself as $pay_stub_entry) {



					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);

					$description_subscript = NULL;



					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



					//Use this to put the total for each type at the end of the array.

					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

						$type = $pay_stub_entry_name_obj->getType();

					}

					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



					if ( $pay_stub_entry->getDescription() !== NULL

							AND $pay_stub_entry->getDescription() !== FALSE

							AND strlen($pay_stub_entry->getDescription()) > 0) {

						$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																'description' => $pay_stub_entry->getDescription() );



						$description_subscript = $description_subscript_counter;



						$description_subscript_counter++;

					}



					//If type if 40 (a total) and the amount is 0, skip it.

					//This if the employee has no deductions at all, it won't be displayed

					//on the pay stub.

					if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

						$pay_stub_entries[$type][] = array(

													'id' => $pay_stub_entry->getId(),

													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

													'type' => $pay_stub_entry_name_obj->getType(),

													'name' => $pay_stub_entry_name_obj->getName(),

													'display_name' => $pay_stub_entry_name_obj->getName(),

													'rate' => $pay_stub_entry->getRate(),

													'units' => $pay_stub_entry->getUnits(),

													'ytd_units' => $pay_stub_entry->getYTDUnits(),

													'amount' => $pay_stub_entry->getAmount(),

													'ytd_amount' => $pay_stub_entry->getYTDAmount(),



													'description' => $pay_stub_entry->getDescription(),

													'description_subscript' => $description_subscript,



													'created_date' => $pay_stub_entry->getCreatedDate(),

													'created_by' => $pay_stub_entry->getCreatedBy(),

													'updated_date' => $pay_stub_entry->getUpdatedDate(),

													'updated_by' => $pay_stub_entry->getUpdatedBy(),

													'deleted_date' => $pay_stub_entry->getDeletedDate(),

													'deleted_by' => $pay_stub_entry->getDeletedBy()

													);



						//Calculate maximum widths of numeric values.

						$width_units = strlen( $pay_stub_entry->getUnits() );

						if ( $width_units > $max_widths['units'] ) {

							$max_widths['units'] = $width_units;

						}



						$width_rate = strlen( $pay_stub_entry->getRate() );

						if ( $width_rate > $max_widths['rate'] ) {

							$max_widths['rate'] = $width_rate;

						}



						$width_amount = strlen( $pay_stub_entry->getAmount() );

						if ( $width_amount > $max_widths['amount'] ) {

							$max_widths['amount'] = $width_amount;

						}



						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );

						if ( $width_amount > $max_widths['ytd_amount'] ) {

							$max_widths['ytd_amount'] = $width_ytd_amount;

						}



						unset($width_rate, $width_units, $width_amount, $width_ytd_amount);

					}



					$prev_type = $pay_stub_entry_name_obj->getType();

				}



				//There should always be pay stub entries for a pay stub.

				if ( !isset( $pay_stub_entries) ) {

					continue;

				}

				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__,10);

				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__,10);



				$block_adjust_y = 30;



				//

				//Earnings

				//

				if ( isset($pay_stub_entries[10]) ) {

					//$column_widths['ytd_amount'] = ( $max_widths['ytd_amount']*2 < 25 ) ? 25 : $max_widths['ytd_amount']*2;

					$column_widths['amount'] = ( $max_widths['amount']*2 < 20 ) ? 20 : $max_widths['amount']*2;

					//$column_widths['rate'] = ( $max_widths['rate']*2 < 5 ) ? 5 : $max_widths['rate']*2;

					//$column_widths['units'] = ( $max_widths['units']*2 < 17 ) ? 17 : $max_widths['units']*2;

					$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);

					//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__,10);



					//Earnings Header

					$pdf->SetFont('','B',10);
                                        
					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell( $column_widths['name'], 20 ,TTi18n::gettext('Earnings'), $border, 0, 'L');

					///$pdf->Cell( $column_widths['rate'], 5,TTi18n::gettext('Rate'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['units'], 5,TTi18n::gettext('Hrs/Units'), $border, 0, 'R');

					$pdf->Cell( $column_widths['amount'], 20 ,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 15;



					$pdf->SetFont('','',9);

					foreach( $pay_stub_entries[10] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 10 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}


                                                        
                                                        $adjust_y = $adjust_y-2;//fl added for rosen 
							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //68

							//$pdf->Cell( $column_widths['rate'], 5, TTi18n::formatNumber( $pay_stub_entry['rate'], TRUE ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						} else {

							//Total

							$pdf->SetFont('','B',9);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount'])-$column_widths['units'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY( (175-(1+$column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //90

							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141

							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name'], 5, $pay_stub_entry['name'], $border, 0, 'L');

							//$pdf->Cell( $column_widths['rate'], 5, '', $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				// Deductions

				//

				if ( isset($pay_stub_entries[20]) ) {

					$max_deductions = count($pay_stub_entries[20]);

					//$two_column_threshold = 4;
					$two_column_threshold = 15;//ARSP CHANGE THIS VALUE OTHERWISE IF DEDUCTION LIST MORE THEN 3 IT WILL BE MOVE RIGHT SIDE



					//Deductions Header

					$block_adjust_y = $block_adjust_y + 4;



					$pdf->SetFont('','B',9);

                                     $adjust_y = $adjust_y-2;//fl added for rosen 
					if ( $max_deductions > $two_column_threshold ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);


                                                            
						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );
                                                    
						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

						//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 4,TTi18n::gettext('Deductions'), $border, 0, 'L');

					}



					//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',9);

					$x=0;

					$max_block_adjust_y = 0;

					foreach( $pay_stub_entries[20] as $pay_stub_entry ) {

						//Start with the right side.

						//if ( $x < floor($max_deductions / 2) ) {

						if ( $x < floor($max_deductions) ) {//-----@widanage change 17.04.2013---

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 20 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > $two_column_threshold ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, Misc::TruncateString( $pay_stub_entry['name'], $column_widths['name']/1.7, 0, TRUE ) . $subscript, $border, 0, 'L');

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

							Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__,10);

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',9);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),4, $pay_stub_entry['name'], $border, 0, 'L'); //110

							$pdf->Cell( $column_widths['amount'], 4, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 4;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns

					if ( $max_deductions > $two_column_threshold ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				if ( isset($pay_stub_entries[40][0]) ) {

					$block_adjust_y = $block_adjust_y + 5;



					//Net Pay entry

					$pdf->SetFont('','B',9);

                                            

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

					$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 4, $pay_stub_entries[40][0]['name'], $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'],4, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'] ), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'] ), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 4;

				}



				//

				//Employer Contributions

				//

				if ( isset($pay_stub_entries[30]) AND $hide_employer_rows != TRUE ) {

					$max_deductions = count($pay_stub_entries[30]);

					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',9);

					if ( $max_deductions > 2 ) {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

						$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					}



					//ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',9);

					$x=0;

					$max_block_adjust_y = 0;



					foreach( $pay_stub_entries[30] as $pay_stub_entry ) {

						//Start with the right side.
                                                //ARSP EDIT--> I CHANGE SOME CODE --> if ( $x < floor($max_deductions / 2) )
						if ( $x < floor($max_deductions) ) {

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 30 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}


                                                        //ARSP EDIT --> I CHANGE THIS VALUE --> if ( $max_deductions > 2 ) {
							if ( $max_deductions > 5 ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //38

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //128

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount']), $border, 0, 'R');

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',9);



							//ARSP-->$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//ARSP-->$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 4;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns
                                        
                                        //ARSP EDIT--> I CHANGE THIS VALUE --> if ( $max_deductions > 2 )
					if ( $max_deductions > 5 ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}


//FL Commented for adjust the payslip for A5 page type (commented unused )
				//

				//Accruals PS accounts

				//

				if ( isset($pay_stub_entries[50]) ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('Balance'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[50] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 50 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'],5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				//Accrual Policy Balances

				//

				$ablf = TTnew( 'AccrualBalanceListFactory' );

				$ablf->getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_obj->getId(), $user_obj->getCompany(), TRUE );

				if ( $ablf->getRecordCount() > 0 ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);



					$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );



					$accrual_time_header_start_x = $pdf->getX();

					$accrual_time_header_start_y = $pdf->getY();



					$pdf->Cell(70,5,TTi18n::gettext('Accrual Time Balances as of ').TTDate::getDate('DATE', time() ) , $border, 0, 'L');

					$pdf->Cell(25,5,TTi18n::gettext('Balance (hrs)'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

					$box_height = 5;



					$pdf->SetFont('','',10);

					foreach( $ablf as $ab_obj ) {

						$balance = $ab_obj->getBalance();

						if ( !is_numeric( $balance ) ) {

							$balance = 0;

						}



						$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						$pdf->Cell(70,5, $ab_obj->getColumn('name'), $border, 0, 'L');

						$pdf->Cell(25,5, TTi18n::formatNumber( TTDate::getHours( $balance ) ), $border, 0, 'R');



						$block_adjust_y = $block_adjust_y + 5;

						$box_height = $box_height + 5;

						unset($balance);

					}

					$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 95, $box_height );



					unset($accrual_time_header_start_x, $accrual_time_header_start_y, $box_height);

				}



//END FL Commented for adjust the payslip for A5 page type (commented unused )

				//

				//Descriptions

				//

				/*if ( isset($pay_stub_entry_descriptions) AND count($pay_stub_entry_descriptions) > 0 ) {



					//Description Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell(175,5,TTi18n::gettext('Notes'), $border, 0, 'L');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',8);

					$x=0;

					foreach( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {

						if ( $x % 2 == 0 ) {

							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						} else {

							$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						}



						//$pdf->Cell(173,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');

						$pdf->Cell(85,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');



						if ( $x % 2 != 0 ) {

							$block_adjust_y = $block_adjust_y + 5;

						}

						$x++;

					}

				}*/

				unset($x, $pay_stub_entry_descriptions, $pay_stub_entry_description);



				//

				// Pay Stub Footer

				//



				$block_adjust_y = 90;

				//Line

				$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y, $adjust_y) );



				//Non Negotiable

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+3, $adjust_y) );

				//$pdf->Cell(175, 5, TTi18n::gettext('NON NEGOTIABLE'), $border, 0, 'C', 0);



				//Employee Address

				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+9, $adjust_y) );

				//$pdf->Cell(60, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'C', 0);

				//$pdf->SetFont('','',10);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+14, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getFullName() .' (#'.$user_obj->getEmployeeNumber().')', $border, 0, 'C', 0);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+19, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getAddress1(), $border, 0, 'C', 0);

				//$address2_adjust_y = 0;

				/*if ( $user_obj->getAddress2() != '' ) {

					$address2_adjust_y = 5;

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24, $adjust_y) );

					$pdf->Cell(60, 5, $user_obj->getAddress2(), $border, 0, 'C', 0);

				}*/

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24+$address2_adjust_y, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 1, 'C', 0);



				//Pay Period - Balance - ID

				$net_pay_amount = 0;

				if ( isset($pay_stub_entries[40][0]) ) {

					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], TRUE );

				}



				if ( isset($pay_stub_entries[65]) AND count($pay_stub_entries[65]) > 0 ) {

					$net_pay_label = TTi18n::gettext('Balance');

				} else {

					$net_pay_label = TTi18n::gettext('Net Pay');

				}



				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY($block_adjust_y+17, $adjust_y) );

				//$pdf->Cell(100, 5, $net_pay_label.': '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', 0);



				if ( $pay_stub_obj->getTainted() == TRUE ) {

					$tainted_flag = 'T';

				} else {

					$tainted_flag = '';

				}

				//$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY($block_adjust_y+30, $adjust_y) );

				//$pdf->Cell(50, 5, TTi18n::gettext('Identification #:').' '. str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT).$tainted_flag, $border, 1, 'L', 0);

				unset($net_pay_amount, $tainted_flag);



				//Line

				$pdf->setLineWidth( 0.75 );

				//ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+35, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y+35, $adjust_y) );
                                $pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+62, $adjust_y), Misc::AdjustXY(205, $adjust_y), Misc::AdjustXY($block_adjust_y+62, $adjust_y) );



				$pdf->SetFont('','', 6);
                                
                                //ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+ 38, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+65, $adjust_y) );

				$pdf->Cell(175, 1, TTi18n::gettext('Pay Stub Generated by').' '. APPLICATION_NAME , $border, 0, 'C', 0);



				unset($pay_stub_entries, $pay_period_number);



				$this->getProgressBarObject()->set( NULL, $pslf->getCurrentRow() );



				$i++;

			}



			$output = $pdf->Output('','S');

		}



		TTi18n::setMasterLocale();



		if ( isset($output) ) {

			return $output;

		}



		return FALSE;

	}


        
        
        
        
        //---------------------------------MAIL---------------------------------
        
        /*
         * 
         * ARSP EDIT ---> ADD NEW CODE
         * THIS CODE ADDED BY ME
         * THIS FUNCTION USE TO SEND EVERY PERSON PAYSLIP VIA E-MAIL
         *  
         */
        function mail( $pslf = NULL, $hide_employer_rows = TRUE ) {    


		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );

		}



		if ( get_class( $pslf ) != 'PayStubListFactory' ) {

			return FALSE;

		}



		$border = 0;



		if ( $pslf->getRecordCount() > 0 ) {
                    
					$success_mail = 0;
                    //echo $pslf->getRecordCount();
//                    exit();
                    
                    $mail_body_array=array();//ARSP ADD--> 
                    $empty_employee_email = array();//ARSP ADD--> 
                    

			//$pdf = new TTPDF('P','mm','Letter');

			//@ARSP-->$pdf = new TTPDF('L','mm','A5');//----@widanage change code here----17.04.2013

			//@ARSP-->$pdf->setMargins(0,0);

			//$pdf->SetAutoPageBreak(TRUE, 30);

			//$pdf->$pdf->SetAutoPageBreak(FALSE);

			//$pdf->$pdf->SetFont('freeserif','',10);

			//$pdf->SetFont('FreeSans','',10);


                        
			$i=0;

			foreach ($pslf as $pay_stub_obj) {
                            
                            
                            $mail_body_array =  null;//ARSP ADD--> 

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );



				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



				//Get Pay Period information

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



				//Use Pay Stub dates, not Pay Period dates.

				$pp_start_date = $pay_stub_obj->getStartDate();

				$pp_end_date = $pay_stub_obj->getEndDate();

				$pp_transaction_date = $pay_stub_obj->getTransactionDate();



				//Get User information

				$ulf = TTnew( 'UserListFactory' );

				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



				//Get company information

				$clf = TTnew( 'CompanyListFactory' );

				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



				//Change locale to users own locale.

				TTi18n::setCountry( $user_obj->getCountry() );

				TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );

				TTi18n::setLocale();



				//

				// Pay Stub Header

				//

				//@ARSP-->$pdf->AddPage();



				//@ARSP-->$adjust_x = 20;

				//@ARSP-->$adjust_y = 10;



				//Logo
				//@ARSP-->$pdf->Image( $company_obj->getLogoFileName() ,Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
                                $mail_body_array['company_logo'] = $company_obj->getLogoFileName();



				//Company name/address

				//@ARSP-->$pdf->SetFont('','B',14);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getName(), $border, 0, 'C');
                                $mail_body_array['company_name'] = $company_obj->getName();



				//@ARSP-->$pdf->SetFont('','',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getAddress1().' '.$company_obj->getAddress2(), $border, 0, 'C');
                                $mail_body_array['company_address'] = $company_obj->getAddress1().' '.$company_obj->getAddress2();



				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                $mail_body_array['company_city'] = $company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode());



				//Pay Period info

				//@ARSP-->$pdf->SetFont('','',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'R');

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'R');

				

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'R');



				//@ARSP-->$pdf->SetFont('','B',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'R');
                                $mail_body_array['pay_start_date'] = TTDate::getDate('DATE', $pp_start_date );//ARSP NEW
				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'R');
                                $mail_body_array['pay_end_date'] = TTDate::getDate('DATE', $pp_end_date );//ARSP NEW
				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'R');
                                $mail_body_array['payment_date'] = TTDate::getDate('DATE', $pp_transaction_date );//ARSP NEW



//-------@widanage add code from footer----17.04.2013------

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->SetFont('','B',12);

				

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				//@ARSP-->$pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');

				

				//@ARSP-->$pdf->SetFont('','B',12);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				//@ARSP-->$pdf->Cell(10, 5, $user_obj->getFullName() .'  ( Emp. #'.$user_obj->getEmployeeNumber().') ', $border, 0, 'L');
                                $mail_body_array['employee_full_name'] = $user_obj->getFullName();//ARSP NEW
                                $mail_body_array['employee_number'] = $user_obj->getEmployeeNumber();//ARSP NEW
                                $mail_body_array['employee_work_email'] = $user_obj->getWorkEmail();//ARSP NEW
                                
//-------@widanage add code from footer----17.04.2013------

				

				

				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(27, $adjust_y) );



				//@ARSP-->$pdf->SetFont('','B',14);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				//@ARSP-->$pdf->Cell(175, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(37, $adjust_y) );



				//@ARSP-->$pdf->setLineWidth( 0.25 );



				//Get pay stub entries.

				$pself = TTnew( 'PayStubEntryListFactory' );

				$pself->getByPayStubId( $pay_stub_obj->getId() );

				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);



				$max_widths = array( 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 );

				$prev_type = NULL;

				$description_subscript_counter = 1;

				foreach ($pself as $pay_stub_entry) {



					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);

					$description_subscript = NULL;



					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



					//Use this to put the total for each type at the end of the array.

					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

						$type = $pay_stub_entry_name_obj->getType();

					}

					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



					if ( $pay_stub_entry->getDescription() !== NULL

							AND $pay_stub_entry->getDescription() !== FALSE

							AND strlen($pay_stub_entry->getDescription()) > 0) {

						$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																'description' => $pay_stub_entry->getDescription() );



						$description_subscript = $description_subscript_counter;



						$description_subscript_counter++;

					}



					//If type if 40 (a total) and the amount is 0, skip it.

					//This if the employee has no deductions at all, it won't be displayed

					//on the pay stub.

					if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

						$pay_stub_entries[$type][] = array(

													'id' => $pay_stub_entry->getId(),

													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

													'type' => $pay_stub_entry_name_obj->getType(),

													'name' => $pay_stub_entry_name_obj->getName(),

													'display_name' => $pay_stub_entry_name_obj->getName(),

													'rate' => $pay_stub_entry->getRate(),

													'units' => $pay_stub_entry->getUnits(),

													'ytd_units' => $pay_stub_entry->getYTDUnits(),

													'amount' => $pay_stub_entry->getAmount(),

													'ytd_amount' => $pay_stub_entry->getYTDAmount(),



													'description' => $pay_stub_entry->getDescription(),

													'description_subscript' => $description_subscript,



													'created_date' => $pay_stub_entry->getCreatedDate(),

													'created_by' => $pay_stub_entry->getCreatedBy(),

													'updated_date' => $pay_stub_entry->getUpdatedDate(),

													'updated_by' => $pay_stub_entry->getUpdatedBy(),

													'deleted_date' => $pay_stub_entry->getDeletedDate(),

													'deleted_by' => $pay_stub_entry->getDeletedBy()

													);



						//Calculate maximum widths of numeric values.

						$width_units = strlen( $pay_stub_entry->getUnits() );

						if ( $width_units > $max_widths['units'] ) {

							$max_widths['units'] = $width_units;

						}



						$width_rate = strlen( $pay_stub_entry->getRate() );

						if ( $width_rate > $max_widths['rate'] ) {

							$max_widths['rate'] = $width_rate;

						}



						$width_amount = strlen( $pay_stub_entry->getAmount() );

						if ( $width_amount > $max_widths['amount'] ) {

							$max_widths['amount'] = $width_amount;

						}



						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );

						if ( $width_amount > $max_widths['ytd_amount'] ) {

							$max_widths['ytd_amount'] = $width_ytd_amount;

						}



						unset($width_rate, $width_units, $width_amount, $width_ytd_amount);

					}



					$prev_type = $pay_stub_entry_name_obj->getType();

				}



				//There should always be pay stub entries for a pay stub.

				if ( !isset( $pay_stub_entries) ) {

					continue;

				}

				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__,10);

				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__,10);



				$block_adjust_y = 30;



				//

				//Earnings

				//

				if ( isset($pay_stub_entries[10]) ) {

					//$column_widths['ytd_amount'] = ( $max_widths['ytd_amount']*2 < 25 ) ? 25 : $max_widths['ytd_amount']*2;

					$column_widths['amount'] = ( $max_widths['amount']*2 < 20 ) ? 20 : $max_widths['amount']*2;

					//$column_widths['rate'] = ( $max_widths['rate']*2 < 5 ) ? 5 : $max_widths['rate']*2;

					//$column_widths['units'] = ( $max_widths['units']*2 < 17 ) ? 17 : $max_widths['units']*2;

					$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);

					//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__,10);


					//Earnings Header

					//@ARSP-->$pdf->SetFont('','B',10);

					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( $column_widths['name'], 20 ,TTi18n::gettext('Earnings'), $border, 0, 'L');


					///$pdf->Cell( $column_widths['rate'], 5,TTi18n::gettext('Rate'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['units'], 5,TTi18n::gettext('Hrs/Units'), $border, 0, 'R');

					//@ARSP-->$pdf->Cell( $column_widths['amount'], 20 ,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 15;



					//@ARSP-->$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[10] as $pay_stub_entry ) {
                                            



						if ( $pay_stub_entry['type'] == 10 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //68
                                                        $mail_body_array['earning_title'][] = $pay_stub_entry['name']. $subscript;

							//$pdf->Cell( $column_widths['rate'], 5, TTi18n::formatNumber( $pay_stub_entry['rate'], TRUE ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['earning_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['earning_title_and_amount'][$pay_stub_entry['name']. $subscript] = $pay_stub_entry['amount'];
                                                        

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');


						} else {

							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount'])-$column_widths['units'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY( (175-(1+$column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //90

							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141

							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( $column_widths['name'], 5, $pay_stub_entry['name'], $border, 0, 'L');
                                                        $mail_body_array['earning_total_title'][] = $pay_stub_entry['name']. $subscript;

							//$pdf->Cell( $column_widths['rate'], 5, '', $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['earning_total_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['earning_total_title_and_amount'][$pay_stub_entry['name']] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');
                                                    

						}

                                                       


						$block_adjust_y = $block_adjust_y + 5;

					}
                                           // print_r( $mail_body_array);
                                           // exit(); 

				}



				//

				// Deductions

				//

				if ( isset($pay_stub_entries[20]) ) {

					$max_deductions = count($pay_stub_entries[20]);

					$two_column_threshold = 4;



					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					if ( $max_deductions > $two_column_threshold ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

						//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					}



					//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;

					foreach( $pay_stub_entries[20] as $pay_stub_entry ) {

						//Start with the right side.

						//if ( $x < floor($max_deductions / 2) ) {

						if ( $x < floor($max_deductions) ) {//-----@widanage change 17.04.2013---

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 20 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > $two_column_threshold ) {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, Misc::TruncateString( $pay_stub_entry['name'], $column_widths['name']/1.7, 0, TRUE ) . $subscript, $border, 0, 'L');
                                                                $mail_body_array['deduction_title'][] = $pay_stub_entry['name'].$subscript;
                                                                

							} else {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');
                                                                $mail_body_array['deduction_title'][] = $pay_stub_entry['name'].$subscript;

							}

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['deduction_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['deduction_title_and_amount'][$pay_stub_entry['name'].$subscript] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

							Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__,10);

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110
                                                        $mail_body_array['deduction_total_title'][] = $pay_stub_entry['name'];

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['deduction_total_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['deduction_total_title_and_amount'][$pay_stub_entry['name']] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}


					//Draw line to separate the two columns

					if ( $max_deductions > $two_column_threshold ) {

						//@ARSP-->$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}
                                




				if ( isset($pay_stub_entries[40][0]) ) {

					$block_adjust_y = $block_adjust_y + 5;



					//Net Pay entry

					//@ARSP-->$pdf->SetFont('','B',10);



					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');
                                        $mail_body_array['net_pay_title'][] = $pay_stub_entries[40][0]['name'];

					//@ARSP-->$pdf->Cell( $column_widths['amount'],5, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'] ), $border, 0, 'R');
                                        $mail_body_array['net_pay_amount'][] = $pay_stub_entries[40][0]['amount'];
                                        $mail_body_array['net_pay_title_and_amount'][$pay_stub_entries[40][0]['name']] = $pay_stub_entries[40][0]['amount'];

					//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'] ), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

				}
                                
//                                        print_r($mail_body_array);
//                                        exit();




				//

				//Employer Contributions

				//

				if ( isset($pay_stub_entries[30]) AND $hide_employer_rows != TRUE ) {

					$max_deductions = count($pay_stub_entries[30]);

					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					if ( $max_deductions > 2 ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

						//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					}



					//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;



					foreach( $pay_stub_entries[30] as $pay_stub_entry ) {

						//Start with the right side.

						if ( $x < floor($max_deductions / 2) ) {

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 30 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > 2 ) {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //38
                                                            
                                                                $mail_body_array['employer_deduction_title'][] = $pay_stub_entry['name'].$subscript;

							} else {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //128
                                                            
                                                                $mail_body_array['employer_deduction_title'][] = $pay_stub_entry['name'].$subscript;

							}

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount']), $border, 0, 'R');
                                                        $mail_body_array['employer_deduction_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['employer_deduction_title_and_amount'][$pay_stub_entry['name'].$subscript] = $pay_stub_entry['amount'];

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//@ARSP-->$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//@ARSP-->$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');
                                                        $mail_body_array['employer_deduction_total_title'][] = $pay_stub_entry['name'].$subscript;

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['employer_deduction_total_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['employer_deduction_total_title_and_amount'][$pay_stub_entry['name'].$subscript] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns

					if ( $max_deductions > 2 ) {

						//@ARSP-->$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				//

				//Accruals PS accounts

				//

				if ( isset($pay_stub_entries[50]) ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');

					//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('Balance'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[50] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 50 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'],5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				//Accrual Policy Balances

				//

				$ablf = TTnew( 'AccrualBalanceListFactory' );

				$ablf->getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_obj->getId(), $user_obj->getCompany(), TRUE );

				if ( $ablf->getRecordCount() > 0 ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);



					//@ARSP-->$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );



					$accrual_time_header_start_x = $pdf->getX();

					$accrual_time_header_start_y = $pdf->getY();



					//@ARSP-->$pdf->Cell(70,5,TTi18n::gettext('Accrual Time Balances as of ').TTDate::getDate('DATE', time() ) , $border, 0, 'L');

					//@ARSP-->$pdf->Cell(25,5,TTi18n::gettext('Balance (hrs)'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

					$box_height = 5;



					//@ARSP-->$pdf->SetFont('','',10);

					foreach( $ablf as $ab_obj ) {

						$balance = $ab_obj->getBalance();

						if ( !is_numeric( $balance ) ) {

							$balance = 0;

						}



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell(70,5, $ab_obj->getColumn('name'), $border, 0, 'L');

						//@ARSP-->$pdf->Cell(25,5, TTi18n::formatNumber( TTDate::getHours( $balance ) ), $border, 0, 'R');



						$block_adjust_y = $block_adjust_y + 5;

						$box_height = $box_height + 5;

						unset($balance);

					}

					//@ARSP-->$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 95, $box_height );



					unset($accrual_time_header_start_x, $accrual_time_header_start_y, $box_height);

				}





				//

				//Descriptions

				//

				/*if ( isset($pay_stub_entry_descriptions) AND count($pay_stub_entry_descriptions) > 0 ) {



					//Description Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell(175,5,TTi18n::gettext('Notes'), $border, 0, 'L');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',8);

					$x=0;

					foreach( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {

						if ( $x % 2 == 0 ) {

							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						} else {

							$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						}



						//$pdf->Cell(173,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');

						$pdf->Cell(85,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');



						if ( $x % 2 != 0 ) {

							$block_adjust_y = $block_adjust_y + 5;

						}

						$x++;

					}

				}*/

				unset($x, $pay_stub_entry_descriptions, $pay_stub_entry_description);



				//

				// Pay Stub Footer

				//



				$block_adjust_y = 90;

				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y, $adjust_y) );



				//Non Negotiable

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+3, $adjust_y) );

				//$pdf->Cell(175, 5, TTi18n::gettext('NON NEGOTIABLE'), $border, 0, 'C', 0);



				//Employee Address

				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+9, $adjust_y) );

				//$pdf->Cell(60, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'C', 0);

				//$pdf->SetFont('','',10);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+14, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getFullName() .' (#'.$user_obj->getEmployeeNumber().')', $border, 0, 'C', 0);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+19, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getAddress1(), $border, 0, 'C', 0);

				//$address2_adjust_y = 0;

				/*if ( $user_obj->getAddress2() != '' ) {

					$address2_adjust_y = 5;

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24, $adjust_y) );

					$pdf->Cell(60, 5, $user_obj->getAddress2(), $border, 0, 'C', 0);

				}*/

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24+$address2_adjust_y, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 1, 'C', 0);



				//Pay Period - Balance - ID

				$net_pay_amount = 0;

				if ( isset($pay_stub_entries[40][0]) ) {

					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], TRUE );

				}



				if ( isset($pay_stub_entries[65]) AND count($pay_stub_entries[65]) > 0 ) {

					$net_pay_label = TTi18n::gettext('Balance');

				} else {

					$net_pay_label = TTi18n::gettext('Net Pay');

				}



				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY($block_adjust_y+17, $adjust_y) );

				//$pdf->Cell(100, 5, $net_pay_label.': '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', 0);



				if ( $pay_stub_obj->getTainted() == TRUE ) {

					$tainted_flag = 'T';

				} else {

					$tainted_flag = '';

				}

				//$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY($block_adjust_y+30, $adjust_y) );

				//$pdf->Cell(50, 5, TTi18n::gettext('Identification #:').' '. str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT).$tainted_flag, $border, 1, 'L', 0);

				unset($net_pay_amount, $tainted_flag);



				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+35, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y+35, $adjust_y) );



				//@ARSP-->$pdf->SetFont('','', 6);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+38, $adjust_y) );

				//@ARSP-->$pdf->Cell(175, 1, TTi18n::gettext('Pay Stub Generated by').' '. APPLICATION_NAME , $border, 0, 'C', 0);



				unset($pay_stub_entries, $pay_period_number);



				$this->getProgressBarObject()->set( NULL, $pslf->getCurrentRow() );

                                
                                if($mail_body_array['employee_work_email'] == NULL || $mail_body_array['employee_work_email'] == "" )
                                {
                                    $empty_employee_email['mail'][] = $mail_body_array['employee_full_name'];
                                }
                                else
                                {
                                    $mail = $this->sendMailToEmployee($mail_body_array);//ARSP ADD EMAIL FUNCTION
                                    if($mail)
                                    {
                                        $success_mail = $success_mail + 1;
                                        //echo "Mail Send Success full";
                                    }
                                    else
                                    {
                                        $sending_failed_email[] = $mail_body_array['employee_work_email'];
                                        echo "Error !!! Mail Sending Fail";
                                    }                                    
                                    
                                }


				$i++;
                                
                                

			}
						echo "There are $success_mail email(s) send successfully.";
						
                        echo "<p/>Empty Employee Email Data : <br/>";
						if(count($empty_employee_email) >0)
						{
							foreach($empty_employee_email AS $key)
							{
								echo $key."<br/>";
							}
						}
						else
						{
							echo "There are no any Empty Email Id";
						}
                        //print_r($empty_employee_email);
						



			//$output = $pdf->Output('','S');

		}
                
                


		TTi18n::setMasterLocale();


//
//		if ( isset($output) ) {
//
//			return $output;
//
//		}



		//return FALSE;

	}       

        //---------------------------------MAIL---------------------------------
        
        
        
        /*
         * 
         * ARSP EDIT ---> ADD NEW CODE
         * THIS CODE ADDED BY ME
         * THIS IS MAIN MAIL FUNCTION TO SEND MAIL
         * EMPLOYEE SALARY DETALIS ARE PRINT IN TO THE EMAIL B0DY
         * 
         */
        function sendMailToEmployee($mail_body_array)
        {
//            print_r($mail_body_array);
//            exit();
            //$to  = $mail_body_array[]; // note the comma
            $to  = $mail_body_array['employee_work_email']; // note the comma
//			echo $to;
//			exit();
            

            // subject
            $subject = 'Salary Slip For  '.$mail_body_array['pay_end_date'];

            
            // message
            $message = '
                            <html>
                                <head>
                                  <title>STATEMENT OF EARNING AND DEDUCTION </title>
                                </head>
                                <body>
                                <div style="width:70%; margin:10 auto;border:1px solid #666666" align="left">
                                            


<table border="0" cellspacing="0" cellpading="0">
  <tr>
    <td width="80" height="45">&nbsp;</td>
    <td ><div dir="ltr" data-font-name="g_font_15_0" data-canvas-width="240.01600715303422">
      <div align="center">
        <h1>'.$mail_body_array['company_name'].'</h1>
      </div>
    </div>
    <div dir="ltr" data-font-name="g_font_10_0" data-canvas-width="215.90667310118673"></div></td>
  </tr>
  <tr>
    <td >&nbsp;</td>
    <td><div align="center">'.$mail_body_array['company_address'].'</div></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><div align="center">'.$mail_body_array['company_city'].'</div></td>
  </tr>
</table>

                                  <p>&nbsp;</p>  
                                  <center>
                                  <p><h2><font color="#0099FF"><SPAN style="BACKGROUND-COLOR: #F2F2F2">Statement of Earnings And Deductions.</SPAN></font></h2></p>
                                  </center>
                                  <hr />
                                 
                                       <table>
                                            <tr>
                                                <td><strong>Name</td>  
                                                <td>:</td>
                                                <td>'.$mail_body_array['employee_full_name'].'</strong></td>
                                            </tr>                                            
                                            <tr>
                                                <td><strong>Employee No</td>    
                                                <td>:</td>
                                                <td>'.$mail_body_array['employee_number'].'</strong></td>                                                                               
                                            </tr>        
                                      </table>      
                                      <hr />
                                       <table>
                                            <tr>
                                                <td><strong>Pay Start Date : </td>  
                                                <td>'.$mail_body_array['pay_start_date'].'</strong></td>                                             
                                            <tr>
                                                <td><strong>Pay End Date : </td>      
                                                <td>'.$mail_body_array['pay_end_date'].'</strong></td>                                                                               
                                            </tr>        
                                            <tr>
                                                <td><strong>Payment Date : </td>      
                                                <td>'.$mail_body_array['payment_date'].'</strong></td>                                                                               
                                            </tr>      
                                       </table>
                                       <hr />
                                       <p></p>    
                                       
                                       <table border="0" cellspacing="0" cellpading="0">
                                            <tr>
                                              <td width="80%" bgcolor="#F1F1F1"><h3>Earnings</h3></td>
                                              <td bgcolor="#F1F1F1"><h3>Amount</h3></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>';                                 
            
            
            //earning title and amount
            foreach($mail_body_array['earning_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td>&nbsp;&nbsp;&nbsp;'.$key.'</td>
                                <td><div align="right">'.number_format( $value , 2).'</div></td>
                             </tr>';   
            }
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            
            //earning total title and total amount
            foreach($mail_body_array['earning_total_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td><strong>'.$key.'</strong></td>
                                <td><strong><div align="right">'.number_format( $value , 2).'</div></strong></td>
                             </tr>';   
            }            
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            $message .= '<tr>
                            <td bgcolor="#F1F1F1"></td>
                            <td bgcolor="#F1F1F1"></td>
                        </tr>';
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';            
            
            $message .= '<tr>
                         <td bgcolor="#F1F1F1"><h3>Deductions</h3></td>
                         <td bgcolor="#F1F1F1"></td>
                         </tr>'; 
            
            //deduction title and amount
            foreach($mail_body_array['deduction_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td>&nbsp;&nbsp;&nbsp;'.$key.'</td>
                                <td><div align="right">'.number_format( $value , 2).'</div></td>
                             </tr>';   
            }            
            
            //deduction_total_title_and_amount
            foreach($mail_body_array['deduction_total_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td><strong>'.$key.'</strong></td>
                                <td><div align="right"><strong>'.number_format( $value , 2).'</div></strong></td>
                             </tr>';   
            }
            
            
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';  
            
            $message .= '<tr>
                            <td bgcolor="#F1F1F1"></td>
                            <td bgcolor="#F1F1F1"></td>
                        </tr>'; 
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';            
            
            //net_pay_title_and_amount
            foreach($mail_body_array['net_pay_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td bgcolor="#CBCBCB"><h3>'.$key.'</h3></td>
                                <td bgcolor="#CBCBCB"><h3><div align="right">'.number_format( $value , 2).'</div></h3></td>
                             </tr>';   
            }
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';
            $message .= '<tr>
                            <td bgcolor="#F1F1F1"></td>
                            <td bgcolor="#F1F1F1"></td>
                        </tr>';
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';       
            
            
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>'; 
            $message .= '<tr>
                            <td><hr /></td>
                            <td><hr /></td>
                        </tr>';             
            
            $message .= '<tr>
                         <td bgcolor="#F1F1F1"><h3>Employer Contributions</h3></td>
                         <td bgcolor="#F1F1F1"></td>
                         </tr>'; 
            

            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';         
 
            $message .= '<tr>
                            <td></td>
                            <td></td>
                        </tr>';              
            
            //employer_deduction_title_and_amount
            foreach($mail_body_array['employer_deduction_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td>&nbsp;&nbsp;&nbsp;'.$key.'</td>
                                <td><div align="right">'.number_format( $value , 2).'</div></td>
                             </tr>';   
            }            
            
            //employer_deduction_total_title_and_amount
            foreach($mail_body_array['employer_deduction_total_title_and_amount'] as $key => $value )
            {
                $message .= '<tr>
                                <td><strong>'.$key.'</strong></td>
                                <td><div align="right"><strong>'.number_format( $value , 2).'</div></strong></td>
                             </tr>';   
            }  
            
            

                    
            $message .=   '</table>
                           <hr />
                           </div>
                           <div align="left"><h5><font color="#999999"><SPAN style="BACKGROUND-COLOR: #F2F2F2">Payslip Generated By Evolve</SPAN></font><h5></div>
                           
                           </body>
                           </html>';

            // To send HTML mail, the Content-type header must be set
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            
            
            // Additional headers
            //$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
            $headers .= 'From: Childfund No-Reply<Do-NotReply@evolve-sl.com>' . "\r\n";
            //$headers .= 'Cc: birthdaycheck@example.com' . "\r\n";
            //$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

            // Mail it
            $mail = mail($to, $subject, $message, $headers);       
            if($mail)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
                
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        


	/*
	 *ARSP EDIT -->ADD NEW CODE FOR GENERATE 3 PAY SLIP PER PAGE
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */	


        //ARSP EDIT --> NEW CODE COPY FROM getPayStub same code for create 3 pay slip per one page
        function getThreePaySlipPerPage( $pslf = NULL, $hide_employer_rows = TRUE ) {   
            

		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );
		}



		if ( get_class( $pslf ) != 'PayStubListFactory' ) {

			return FALSE;

		}



		$border = 0;



		if ( $pslf->getRecordCount() > 0 ) {



			//$pdf = new TTPDF('P','mm','Letter');

			$pdf = new TTPDF('P','mm','A4');//----@widanage change code here----17.04.2013

			$pdf->setMargins(0,0);

			//$pdf->SetAutoPageBreak(TRUE, 30);

			$pdf->SetAutoPageBreak(FALSE);

			$pdf->SetFont('freeserif','',10);

			//$pdf->SetFont('FreeSans','',10);

    
                        
                        
                        
			$i=0;
                        
                        $page_no = 1;//ARSP ADD
			foreach ($pslf as $pay_stub_obj) {

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );



				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



				//Get Pay Period information

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



				//Use Pay Stub dates, not Pay Period dates.

				$pp_start_date = $pay_stub_obj->getStartDate();

				$pp_end_date = $pay_stub_obj->getEndDate();

				$pp_transaction_date = $pay_stub_obj->getTransactionDate();



				//Get User information

				$ulf = TTnew( 'UserListFactory' );

				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



				//Get company information

				$clf = TTnew( 'CompanyListFactory' );

				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



				//Change locale to users own locale.

				TTi18n::setCountry( $user_obj->getCountry() );

				TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );

				TTi18n::setLocale();



				//

				// Pay Stub Header

				//

                                
//##############################################################################
                                
                                if($page_no % 3 == 0)//3rd Pay slip starting position
                                {                                    
                                    $adjust_x = 143;                                    
                                }
                                if($page_no % 3 == 1)//1st Pay slip starting position
                                {
                                    $adjust_x = 3;                                    
                                }
                                if($page_no % 3 == 2)//2rd Pay slip starting position
                                {
                                    $adjust_x = 73;                                    
                                }                               

				$adjust_y = 5;  
                                
                                
                                
                                
                                
                                
                                
                                if( $page_no == 1 )
                                {   
                                    $pdf->addPage();
                                    $adjust_x1 = 20;

                                    $adjust_y1 = 5;


                                    //Logo

                                    $pdf->Image($company_obj->getLogoFileName(), Misc::AdjustXY(0, $adjust_x1 + 0), Misc::AdjustXY(1, $adjust_y1 + 0), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



                                    //LINE
                                    $pdf->setLineWidth(0.5 );
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(17, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(17, $adjust_y));



                                    //Company name/address

                                    $pdf->SetFont('', 'B', 14);

                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(0, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getName(), $border, 0, 'C');



                                    $pdf->SetFont('', '', 10);

                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(6, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getAddress1() . ' ' . $company_obj->getAddress2(), $border, 0, 'C');



                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(10, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getCity() . ', ' . $company_obj->getProvince() . ' ' . strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                    
                                    
                                    //CONFIDENTIAL
                                    $pdf->SetFont('','B',12);			

                                    $pdf->setXY( Misc::AdjustXY(195, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

                                    $pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');
                                    
                                    
                                    
                                    
				//Line

				//$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(15, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(15, $adjust_y) );



				$pdf->SetFont('','B',10);

				$pdf->setXY( Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(18, $adjust_y1) );

				$pdf->Cell(75, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line  After "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
                                $pdf->setLineWidth(0.5 );
                                $pdf->SetDrawColor(200, 200, 200);
                                $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(23, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(23, $adjust_y));

                                 


                                    //
                                    //Footer
                                    //
                                    
                                    
                                    //ARSP EDIT--> I ADDED FOOTER HERE (ORIGINAL PLACE IS DOWN)
                                    //Line
                                    //$pdf->setLineWidth( 1 );
                                    //$pdf->Line( Misc::AdjustXY(0, 3), Misc::AdjustXY(280,-5), Misc::AdjustXY(270, -15), Misc::AdjustXY(280,-5) );
                                    $pdf->Line(Misc::AdjustXY(0, 200), Misc::AdjustXY(280, $adjust_y), Misc::AdjustXY(5, $adjust_y), Misc::AdjustXY(280, $adjust_y));



                                    $pdf->SetY(-15);
                                    // Set font
                                    $pdf->SetFont('', 'I', 8);
                                    // Page number
                                    $pdf->Cell(0, 10, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

                                    $pdf->SetFont('', '', 6);

                                    //$pdf->setXY( Misc::AdjustXY(70, 0), Misc::AdjustXY(0, -15) );
                                    $pdf->setXY(Misc::AdjustXY(0, 0), Misc::AdjustXY(0, -15));
                                    $pdf->Cell(0, 10, TTi18n::gettext('Pay Stub Generated by') . ' ' . APPLICATION_NAME, $border, 0, 'C', 0);
                                    
                                    
                                    
                                    
                                    
                                    //ARSP EDIT --> DRAW VIRTICAL LINE 1
                                    // center of ellipse
                                    
                                    $xc = 70;
                                    $yc = 78;


                                    // X Y axis
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);

                                    //ARSP EDIT --> DRAW VIRTICAL LINE 2
                                    
                                    $xc = 140;
                                    $yc = 78;

                                    // X Y axis
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    
                                }
                                
                                
                                
//##############################################################################
                                
                                
                                
                                
                                
                                
				//Logo

				//$pdf->Image( $company_obj->getLogoFileName() ,Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



				//Company name/address

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//$pdf->Cell(75,5,$company_obj->getName(), $border, 0, 'C');



				//ARSP-->$pdf->SetFont('','',10);

				//ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

				//ARSP-->$pdf->Cell(75,5,$company_obj->getAddress1().' '.$company_obj->getAddress2(), $border, 0, 'C');



				//ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//ARSP-->$pdf->Cell(75,5,$company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode()), $border, 0, 'C');



				//Pay Period info

				$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
                                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(34, $adjust_y) );
                                
                                //$pdf->Cell(30,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'R');
				$pdf->Cell(10,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y) );
                                
                                //$pdf->Cell(30,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'R');
				$pdf->Cell(10,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'L');

				
                                //$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(40, $adjust_y) );

                                //$pdf->Cell(30,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'R');    
				$pdf->Cell(10,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'L');



				$pdf->SetFont('','B',8);

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(34, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(37, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(20, $adjust_x), Misc::AdjustXY(40, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'L');







//-------@widanage add code from footer----17.04.2013------

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->SetFont('','B',12);

				

				//ARSP-->$pdf->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				//ARSP-->$pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');

				

				$pdf->SetFont('','B',11);//ARSP EDIT--> I CHANGED FONT SIZE

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(25, $adjust_y) );

				$pdf->Cell(10, 5, $user_obj->getFullName(), $border, 0, 'L');
                                
                                
				$pdf->SetFont('','B',10);//ARSP EDIT--> I CHANGED FONT SIZE

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				$pdf->Cell(10, 5, 'Emp No : '.$user_obj->getEmployeeNumber(), $border, 0, 'L');
                                

//-------@widanage add code from footer----17.04.2013------

				

				

				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(27, $adjust_y) );



				//ARSP-->$pdf->SetFont('','B',14);

				//ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				//ARSP-->$pdf->Cell(175, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(37, $adjust_y) );



				//ARSP-->$pdf->setLineWidth( 0.25 );



				//Get pay stub entries.

				$pself = TTnew( 'PayStubEntryListFactory' );

				$pself->getByPayStubId( $pay_stub_obj->getId() );

				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);



				$max_widths = array( 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 );

				$prev_type = NULL;

				$description_subscript_counter = 1;

				foreach ($pself as $pay_stub_entry) {



					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);

					$description_subscript = NULL;



					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



					//Use this to put the total for each type at the end of the array.

					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

						$type = $pay_stub_entry_name_obj->getType();

					}

					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



					if ( $pay_stub_entry->getDescription() !== NULL

							AND $pay_stub_entry->getDescription() !== FALSE

							AND strlen($pay_stub_entry->getDescription()) > 0) {

						$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																'description' => $pay_stub_entry->getDescription() );



						$description_subscript = $description_subscript_counter;



						$description_subscript_counter++;

					}



					//If type if 40 (a total) and the amount is 0, skip it.

					//This if the employee has no deductions at all, it won't be displayed

					//on the pay stub.

					if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

						$pay_stub_entries[$type][] = array(

													'id' => $pay_stub_entry->getId(),

													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

													'type' => $pay_stub_entry_name_obj->getType(),

													'name' => $pay_stub_entry_name_obj->getName(),

													'display_name' => $pay_stub_entry_name_obj->getName(),

													'rate' => $pay_stub_entry->getRate(),

													'units' => $pay_stub_entry->getUnits(),

													'ytd_units' => $pay_stub_entry->getYTDUnits(),

													'amount' => $pay_stub_entry->getAmount(),

													'ytd_amount' => $pay_stub_entry->getYTDAmount(),



													'description' => $pay_stub_entry->getDescription(),

													'description_subscript' => $description_subscript,



													'created_date' => $pay_stub_entry->getCreatedDate(),

													'created_by' => $pay_stub_entry->getCreatedBy(),

													'updated_date' => $pay_stub_entry->getUpdatedDate(),

													'updated_by' => $pay_stub_entry->getUpdatedBy(),

													'deleted_date' => $pay_stub_entry->getDeletedDate(),

													'deleted_by' => $pay_stub_entry->getDeletedBy()

													);



						//Calculate maximum widths of numeric values.

						$width_units = strlen( $pay_stub_entry->getUnits() );

						if ( $width_units > $max_widths['units'] ) {

							$max_widths['units'] = $width_units;

						}



						$width_rate = strlen( $pay_stub_entry->getRate() );

						if ( $width_rate > $max_widths['rate'] ) {

							$max_widths['rate'] = $width_rate;

						}



						$width_amount = strlen( $pay_stub_entry->getAmount() );

						if ( $width_amount > $max_widths['amount'] ) {

							$max_widths['amount'] = $width_amount;

						}



						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );

						if ( $width_amount > $max_widths['ytd_amount'] ) {

							$max_widths['ytd_amount'] = $width_ytd_amount;

						}



						unset($width_rate, $width_units, $width_amount, $width_ytd_amount);

					}



					$prev_type = $pay_stub_entry_name_obj->getType();

				}



				//There should always be pay stub entries for a pay stub.

				if ( !isset( $pay_stub_entries) ) {

					continue;

				}

				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__,10);

				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__,10);



				$block_adjust_y = 39;// ARSP CHANGE VALUE 30 to 35



				//

				//Earnings

				//

				if ( isset($pay_stub_entries[10]) ) {

					//$column_widths['ytd_amount'] = ( $max_widths['ytd_amount']*2 < 25 ) ? 25 : $max_widths['ytd_amount']*2;

					$column_widths['amount'] = ( $max_widths['amount']*2 < 20 ) ? 20 : $max_widths['amount']*2;

					//$column_widths['rate'] = ( $max_widths['rate']*2 < 5 ) ? 5 : $max_widths['rate']*2;

					//$column_widths['units'] = ( $max_widths['units']*2 < 17 ) ? 17 : $max_widths['units']*2;
                                        
                                        
					//ARSP-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);
					$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);

					//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__,10);



					//Earnings Header

					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell( $column_widths['name'], 20 ,TTi18n::gettext('Earnings'), $border, 0, 'L');

					///$pdf->Cell( $column_widths['rate'], 5,TTi18n::gettext('Rate'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['units'], 5,TTi18n::gettext('Hrs/Units'), $border, 0, 'R');
                                        

					$pdf->Cell( $column_widths['amount'], 20 ,TTi18n::gettext('Amount'), $border, 0,  'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 15;



					$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[10] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 10 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //68

							//$pdf->Cell( $column_widths['rate'], 5, TTi18n::formatNumber( $pay_stub_entry['rate'], TRUE ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						} else {

							//Total

							$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount'])-$column_widths['units'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY( (175-(1+$column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //90

							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141

							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name'], 5, $pay_stub_entry['name'], $border, 0, 'L');

							//$pdf->Cell( $column_widths['rate'], 5, '', $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				// Deductions

				//

				if ( isset($pay_stub_entries[20]) ) {

					$max_deductions = count($pay_stub_entries[20]);

					//$two_column_threshold = 4;
					$two_column_threshold = 15;//ARSP EDIT--> I CHANE THIS VALUE. OTHERWISE DEDUCTION ALIGNMENT WILL MOVE IF DEDUCTION TITLE MORE THEN 3.



					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					if ( $max_deductions > $two_column_threshold ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

						//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					} else {

						//ARSP-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);          
						$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					}



					//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;

					foreach( $pay_stub_entries[20] as $pay_stub_entry ) {

						//Start with the right side.

						//if ( $x < floor($max_deductions / 2) ) {

						if ( $x < floor($max_deductions) ) {//-----@widanage change 17.04.2013---

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 20 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > $two_column_threshold ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, Misc::TruncateString( $pay_stub_entry['name'], $column_widths['name']/1.7, 0, TRUE ) . $subscript, $border, 0, 'L');

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

							Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__,10);

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110                                                        
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns
					

					if ( $max_deductions > $two_column_threshold ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				if ( isset($pay_stub_entries[40][0]) ) {

					$block_adjust_y = $block_adjust_y + 5;



					//Net Pay entry

					$pdf->SetFont('','B',10);



					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                			//ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');
					$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'],5, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'] ), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'] ), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

				}



				//

				//Employer Contributions

				//

				if ( isset($pay_stub_entries[30]) AND $hide_employer_rows != TRUE ) {

					$max_deductions = count($pay_stub_entries[30]);

					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					if ( $max_deductions > 2 ) {
							
						//ARSP-->$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);		
						$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

						$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					} else {
                                                
                                                //ARSO-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);
						$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					}



					//ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;



					foreach( $pay_stub_entries[30] as $pay_stub_entry ) {

						//Start with the right side.
						
						//ARSP EDIT --> I HIDE SOME CODE
						//if ( $x < floor($max_deductions / 2) ) 
						if ( $x < floor($max_deductions) ) {

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 30 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}


							//ARSP --> I CHANGE TIHI VALUE if -->( $max_deductions > 2 ) 
							if ( $max_deductions > 5 ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //38

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //128

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount']), $border, 0, 'R');

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',10);


                                                        //ARSP-->$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111
														
							//ARSP-->$pdf->line(Misc::AdjustXY( (65-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(65-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

                                                        //ARSP-->$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141
														
							//ARSP-->$pdf->line(Misc::AdjustXY( 65-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(65, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns
					
					//ARSP -->I CHANE THIS VALUE --> if ( $max_deductions > 2 ) 
					if ( $max_deductions > 5 ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				//

				//Accruals PS accounts

				//

				if ( isset($pay_stub_entries[50]) ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');
					$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('Balance'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[50] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 50 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                                                        
                                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'],5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				//Accrual Policy Balances

				//

				$ablf = TTnew( 'AccrualBalanceListFactory' );

				$ablf->getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_obj->getId(), $user_obj->getCompany(), TRUE );

				if ( $ablf->getRecordCount() > 0 ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);



					$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );



					$accrual_time_header_start_x = $pdf->getX();

					$accrual_time_header_start_y = $pdf->getY();



					$pdf->Cell(70,5,TTi18n::gettext('Accrual Time Balances as of ').TTDate::getDate('DATE', time() ) , $border, 0, 'L');

					$pdf->Cell(25,5,TTi18n::gettext('Balance (hrs)'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

					$box_height = 5;



					$pdf->SetFont('','',10);

					foreach( $ablf as $ab_obj ) {

						$balance = $ab_obj->getBalance();

						if ( !is_numeric( $balance ) ) {

							$balance = 0;

						}



						$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						$pdf->Cell(70,5, $ab_obj->getColumn('name'), $border, 0, 'L');

						$pdf->Cell(25,5, TTi18n::formatNumber( TTDate::getHours( $balance ) ), $border, 0, 'R');



						$block_adjust_y = $block_adjust_y + 5;

						$box_height = $box_height + 5;

						unset($balance);

					}

					$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 95, $box_height );



					unset($accrual_time_header_start_x, $accrual_time_header_start_y, $box_height);

				}





				//

				//Descriptions

				//

				/*if ( isset($pay_stub_entry_descriptions) AND count($pay_stub_entry_descriptions) > 0 ) {



					//Description Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell(175,5,TTi18n::gettext('Notes'), $border, 0, 'L');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',8);

					$x=0;

					foreach( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {

						if ( $x % 2 == 0 ) {

							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						} else {

							$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						}



						//$pdf->Cell(173,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');

						$pdf->Cell(85,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');



						if ( $x % 2 != 0 ) {

							$block_adjust_y = $block_adjust_y + 5;

						}

						$x++;

					}


				}*/

				unset($x, $pay_stub_entry_descriptions, $pay_stub_entry_description);



				//

				// Pay Stub Footer

				//



				$block_adjust_y = 90;

				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y, $adjust_y) );



				//Non Negotiable

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+3, $adjust_y) );

				//$pdf->Cell(175, 5, TTi18n::gettext('NON NEGOTIABLE'), $border, 0, 'C', 0);



				//Employee Address

				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+9, $adjust_y) );

				//$pdf->Cell(60, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'C', 0);

				//$pdf->SetFont('','',10);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+14, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getFullName() .' (#'.$user_obj->getEmployeeNumber().')', $border, 0, 'C', 0);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+19, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getAddress1(), $border, 0, 'C', 0);

				//$address2_adjust_y = 0;

				/*if ( $user_obj->getAddress2() != '' ) {

					$address2_adjust_y = 5;

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24, $adjust_y) );

					$pdf->Cell(60, 5, $user_obj->getAddress2(), $border, 0, 'C', 0);

				}*/

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24+$address2_adjust_y, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 1, 'C', 0);



				//Pay Period - Balance - ID

				$net_pay_amount = 0;

				if ( isset($pay_stub_entries[40][0]) ) {

					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], TRUE );

				}



				if ( isset($pay_stub_entries[65]) AND count($pay_stub_entries[65]) > 0 ) {

					$net_pay_label = TTi18n::gettext('Balance');

				} else {

					$net_pay_label = TTi18n::gettext('Net Pay');

				}



				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY($block_adjust_y+17, $adjust_y) );

				//$pdf->Cell(100, 5, $net_pay_label.': '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', 0);



				if ( $pay_stub_obj->getTainted() == TRUE ) {

					$tainted_flag = 'T';

				} else {

					$tainted_flag = '';

				}

				//$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY($block_adjust_y+30, $adjust_y) );

				//$pdf->Cell(50, 5, TTi18n::gettext('Identification #:').' '. str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT).$tainted_flag, $border, 1, 'L', 0);

				unset($net_pay_amount, $tainted_flag);



				//Line

//				$pdf->setLineWidth( 1 );
//
//				$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+35, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y+35, $adjust_y) );
//
//
//
//				$pdf->SetFont('','', 6);
//
//				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+38, $adjust_y) );
//                                
//                                
//				$pdf->Cell(175, 1, TTi18n::gettext('Pay Stub Generated by').' '. APPLICATION_NAME , $border, 0, 'C', 0);



				unset($pay_stub_entries, $pay_period_number);



				$this->getProgressBarObject()->set( NULL, $pslf->getCurrentRow() );



				$i++;
                                
                                //ARSP ADD CODE        
                                if( $page_no % 3 == 0 )
                                {
                                    
                                    $pdf->addPage();
                                    
                                    
                                    
                                    $adjust_x1 = 20;

                                    $adjust_y1 = 5;


                                    //Logo

                                    $pdf->Image($company_obj->getLogoFileName(), Misc::AdjustXY(0, $adjust_x1 + 0), Misc::AdjustXY(1, $adjust_y1 + 0), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



                                    
                                    //LINE
                                    $pdf->setLineWidth( 0.5 );
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(17, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(17, $adjust_y));



                                    //Company name/address

                                    $pdf->SetFont('', 'B', 14);

                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(0, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getName(), $border, 0, 'C');



                                    $pdf->SetFont('', '', 10);

                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(6, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getAddress1() . ' ' . $company_obj->getAddress2(), $border, 0, 'C');



                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(10, $adjust_y1));

                                    $pdf->Cell(75, 5, $company_obj->getCity() . ', ' . $company_obj->getProvince() . ' ' . strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                    
                                    
                                    //CONFIDENTIAL
                                    $pdf->SetFont('','B',12);			

                                    $pdf->setXY( Misc::AdjustXY(195, 3), Misc::AdjustXY(10, $adjust_y) );

                                    $pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');
                                    



                                    $pdf->SetFont('','B',10);

                                    $pdf->setXY( Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(18, $adjust_y1) );

                                    $pdf->Cell(75, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



                                    //Line  After "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
                                    $pdf->setLineWidth( 0.5 );
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(23, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(23, $adjust_y));














                                    //ARSP EDIT--> I ADDED FOOTER HERE (ORIGINAL PLACE IS DOWN)
                                    //Line
                                    //$pdf->setLineWidth( 1 );
                //                                    $pdf->Line( Misc::AdjustXY(0, 3), Misc::AdjustXY(280,-5), Misc::AdjustXY(270, -15), Misc::AdjustXY(280,-5) );
                                    $pdf->Line(Misc::AdjustXY(0, 200), Misc::AdjustXY(280, $adjust_y), Misc::AdjustXY(5, $adjust_y), Misc::AdjustXY(280, $adjust_y));



                                    $pdf->SetY(-15);
                                    // Set font
                                    $pdf->SetFont('', 'I', 8);
                                    // Page number
                                    $pdf->Cell(0, 10, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

                                    $pdf->SetFont('', '', 6);

                                    //$pdf->setXY( Misc::AdjustXY(70, 0), Misc::AdjustXY(0, -15) );
                                    $pdf->setXY(Misc::AdjustXY(0, 0), Misc::AdjustXY(0, -15));
                                    $pdf->Cell(0, 10, TTi18n::gettext('Pay Stub Generated by') . ' ' . APPLICATION_NAME, $border, 0, 'C', 0);
                                    
                                    
                                    
                                    //
                                    //Draw 2 Virtical Lines
                                    //
                                                                        
                                    //ARSP EDIT --> DRAW VIRTICAL LINE 1
                                    // center of ellipse
                                    $xc = 70;
                                    $yc = 78;

                                    // X Y axis
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);

                                    //ARSP EDIT --> DRAW VIRTICAL LINE 2
                                    $xc = 140;
                                    $yc = 78;

                                    // X Y axis
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    
                                }
                                    
                                $page_no++;//ARSP ADD CODE                                
                                
                                
                                //$page_no++;//ARSP EDIT

			}



			$output = $pdf->Output('','S');

		}



		TTi18n::setMasterLocale();



		if ( isset($output) ) {

			return $output;

		}



		return FALSE;

	}   




 
        //
        //ARSP NOTE --> FOUR PAYSLIP PER PAGE
        //
        //
        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
        function getFourPaySlipPerPageLandscape( $pslf = NULL, $hide_employer_rows = TRUE ) {   
            

		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );
		}



		if ( get_class( $pslf ) != 'PayStubListFactory' ) {

			return FALSE;

		}



		$border = 0;



		if ( $pslf->getRecordCount() > 0 ) {



			//$pdf = new TTPDF('P','mm','Letter');

			$pdf = new TTPDF('L','mm','A4');//----@widanage change code here----17.04.2013

			$pdf->setMargins(0,0);

			//$pdf->SetAutoPageBreak(TRUE, 30);

			$pdf->SetAutoPageBreak(FALSE);

			$pdf->SetFont('freeserif','',10);

			//$pdf->SetFont('FreeSans','',10);

    
                        
                        
                        
			$i=0;
                        
                        $page_no = 1;//ARSP ADD
			foreach ($pslf as $pay_stub_obj) {
                            $txt = '';

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );



				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



				//Get Pay Period information

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



				//Use Pay Stub dates, not Pay Period dates.

				$pp_start_date = $pay_stub_obj->getStartDate();

				$pp_end_date = $pay_stub_obj->getEndDate();

				$pp_transaction_date = $pay_stub_obj->getTransactionDate();



				//Get User information

				$ulf = TTnew( 'UserListFactory' );

				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();
//                                print_r($user_obj->getDefaultBranch());
//                                exit();


				//Get company information

				$clf = TTnew( 'CompanyListFactory' );

				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



				//Change locale to users own locale.

				TTi18n::setCountry( $user_obj->getCountry() );

				TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );

				TTi18n::setLocale();



				//

				// Pay Stub Header

				//

                                
//##############################################################################
                                
                                if($page_no % 4 == 0)//4th Pay slip starting position
                                {                                    
                                    $adjust_x = 213;                                    
                                }
                                if($page_no % 4 == 1)//1st Pay slip starting position
                                {
                                    $adjust_x = 3;                                    
                                }
                                if($page_no % 4 == 2)//2rd Pay slip starting position
                                {
                                    $adjust_x = 73;                                    
                                }                               
                                if($page_no % 4 == 3)//3rd Pay slip starting position
                                {
                                    $adjust_x = 143;                                    
                                }                               

				$adjust_y = 5;  
                                
                                
                                
                                
                                
                                
                                
                                if( $page_no == 1 )
                                {   
                                    $pdf->addPage();
                                    $adjust_x1 = 20;

                                    $adjust_y1 = 5;


                                    
                                    //Logo
//                                    $pdf->Image($company_obj->getLogoFileName(), Misc::AdjustXY(0, $adjust_x1 + 0), Misc::AdjustXY(1, $adjust_y1 + 0), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



                                    //Line  BEFORE "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
//                                    $pdf->setLineWidth(0.5 );
//                                    $pdf->SetDrawColor(200, 200, 200);
//                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(17, $adjust_y), Misc::AdjustXY(297, $adjust_y), Misc::AdjustXY(17, $adjust_y));//ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



                                    //Company name/address

//                                    $pdf->SetFont('', 'B', 14);
//
//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(0, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getName(), $border, 0, 'C');



//                                    $pdf->SetFont('', '', 10);
//
//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(6, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getAddress1() . ' ' . $company_obj->getAddress2(), $border, 0, 'C');



//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(10, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getCity() . ', ' . $company_obj->getProvince() . ' ' . strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                    
                                    
                                    //CONFIDENTIAL
//                                    $pdf->SetFont('','B',12);			
//
//                                    $pdf->setXY( Misc::AdjustXY(195, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
//
//                                    $pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');
                                    
                                    
                                    
                                    
				//Line

				//$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(15, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(15, $adjust_y) );



//				$pdf->SetFont('','B',10);
//
//				$pdf->setXY( Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(18, $adjust_y1) );
//
//				$pdf->Cell(75, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line  After "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
//                                $pdf->setLineWidth(0.5 );
//                                $pdf->SetDrawColor(200, 200, 200);
//                                $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(23, $adjust_y), Misc::AdjustXY(297, $adjust_y), Misc::AdjustXY(23, $adjust_y));//ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

                                 


                                    //
                                    //Footer
                                    //
                                    
                                    
                                    //ARSP EDIT--> I ADDED FOOTER HERE (ORIGINAL PLACE IS DOWN)
                                    //Line
                                    //$pdf->setLineWidth( 1 );
                                    //$pdf->Line( Misc::AdjustXY(0, 3), Misc::AdjustXY(280,-5), Misc::AdjustXY(270, -15), Misc::AdjustXY(280,-5) );
                                    $pdf->Line(Misc::AdjustXY(0, 200), Misc::AdjustXY(280, $adjust_y), Misc::AdjustXY(5, $adjust_y), Misc::AdjustXY(280, $adjust_y));



                                    //$pdf->SetY(-11);
                                    $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(20, $adjust_y + 100) );
                                    // Set font
                                    $pdf->SetFont('', 'I', 8);
                                    // Page number
                                    $pdf->Cell(302, 159, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

                                    //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON
//                                    $pdf->SetFont('', '', 6);
//                                    //$pdf->setXY( Misc::AdjustXY(70, 0), Misc::AdjustXY(0, -15) );
//                                    $pdf->setXY(Misc::AdjustXY(0, 0), Misc::AdjustXY(0, -15));
//                                    $pdf->Cell(0, 10, TTi18n::gettext('Pay Stub Generated by') . ' ' . APPLICATION_NAME, $border, 0, 'C', 0);
                                    
                                    
                                    
                                    //ARSP EDIT --> DRAW VIRTICAL LINE 1
                                    // center of ellipse
                                    
                                    $xc = 70;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT


                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);

                                    //ARSP EDIT --> DRAW VIRTICAL LINE 2
                                    
                                    $xc = 140;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT

                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                                    //3RD LINE
                                    $xc = 210;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT

                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                }
                                
                                
                                
//##############################################################################
                                
                                
                                
                                
                                
                                
				//Logo

				//$pdf->Image( $company_obj->getLogoFileName() ,Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);

                                //Logo
                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                $pdf->Image($company_obj->getLogoFileName(), Misc::AdjustXY(0, $adjust_x + 0), Misc::AdjustXY(1, $adjust_y + 0), 10, 8, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);


				//Company name/address

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//$pdf->Cell(75,5,$company_obj->getName(), $border, 0, 'C');

                                
				//ARSP-->$pdf->SetFont('','',10);

				//ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

				//ARSP-->$pdf->Cell(75,5,$company_obj->getAddress1().' '.$company_obj->getAddress2(), $border, 0, 'C');



				//ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//ARSP-->$pdf->Cell(75,5,$company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode()), $border, 0, 'C');

                                
                                
                
                
               
                                
                                $pdf->SetFont('', 'B', 7.5);
                                // set some text for example
//                                $txt .= $company_obj->getName()."\n";
//                                $txt .= $company_obj->getAddress1() . ' ' . $company_obj->getAddress2()."\n";                                
//                                $txt .= $company_obj->getCity() . ',' .' ' . strtoupper($company_obj->getPostalCode());//ARSP NOTE --> I REMOVE THE PROVINCE FOR THUNDER & NEON

                                
                                
                                if($user_obj->getDefaultBranch() == '' or $user_obj->getDefaultBranch() == NULL)
                                {
                                    $txt .= $company_obj->getName()."\n";
                                    $txt .= $company_obj->getAddress1() . ' ' . $company_obj->getAddress2()."\n";                                
                                    $txt .= $company_obj->getCity() . ',' .' ' . strtoupper($company_obj->getPostalCode());//ARSP NOTE --> I REMOVE THE PROVINCE FOR THUNDER & NEON                                    
                                }
                                else
                                {
                                    $blf = TTnew( 'BranchListFactory' );
                                    $blf->getById($user_obj->getDefaultBranch());
                                    
                                    foreach ($blf as $temp)
                                    {
                                    $txt .= $temp->getNameById($user_obj->getDefaultBranch())."\n";
                                    $txt .= $temp->getAddress1() . ' ' . $temp->getAddress2()."\n";                                
                                    $txt .= $temp->getCity() . ',' .' ' . strtoupper($temp->getPostalCode());//ARSP NOTE --> I REMOVE THE PROVINCE FOR THUNDER & NEON                                   
                                    }                                     
                                }
                                
                                
                                
                                // set cell padding
                                //$pdf->setCellPaddings(2, 0, 0, 0);

                                // Address box
                                // MultiCell   ($w, $h,  $txt,    $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
                                $pdf->MultiCell(61, 3, $txt, 0,         'C',         0,      0,     Misc::AdjustXY(6, $adjust_x), Misc::AdjustXY(0, $adjust_y),  true);
                                
                                
                                
                                
                                
                                
                                
                                
                                //Company name/address
                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON                                
//                                $pdf->SetFont('', 'B', 8);
//
//                                $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(0, $adjust_y));
//
//                                $pdf->Cell(75, 5, $company_obj->getName(), $border, 0, 'C');
//                                
//
//                                //Company name/address Line 1
//                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
//                                $pdf->SetFont('', '', 8);
//
//                                $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(4, $adjust_y));
//
//                                $pdf->Cell(75, 5, $company_obj->getAddress1() . ' ' . $company_obj->getAddress2(), $border, 0, 'C');
//
//
//                                //Company name/address Line 2
//                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
//                                $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(7, $adjust_y));
//
//                                $pdf->Cell(75, 5, $company_obj->getCity() . ', ' . strtoupper($company_obj->getPostalCode()), $border, 0, 'C');

                                
                                                                
                                                                
                                //CONFIDENTIAL
                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                $pdf->SetFont('','B',6);			

                                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(12, $adjust_y) );

                                $pdf->Cell(65, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');




                                
				//Line
				$pdf->setLineWidth( 0.5 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(17, $adjust_y), Misc::AdjustXY(0, $adjust_y), Misc::AdjustXY(17, $adjust_y) );
                                $pdf->Line($adjust_x + 64,$adjust_y + 17 , $adjust_x, $adjust_y+ 17);

                                
                                
//                                    //3RD LINE
//                                    $xc = 210;
//                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT
//
//                                    // X Y axis
//                                    //$pdf->SetDrawColor(200, 200, 200);
//                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    
                                    
                                    
                                    

				$pdf->SetFont('','B',8);

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(16.5, $adjust_y) );

				$pdf->Cell(65, 5, TTi18n::gettext('Pay Slip Summary'), $border, 0, 'C', 0);



				//Line  After "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
                                $pdf->setLineWidth(0.5 );
                                
                                //$pdf->SetDrawColor(200, 200, 200);
                                
                                //$pdf->Line(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(21, $adjust_y), Misc::AdjustXY(0, $adjust_y), Misc::AdjustXY(21, $adjust_y));//ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON
                                 $pdf->Line($adjust_x + 64,$adjust_y + 21 , $adjust_x, $adjust_y+ 21);
                                
                                
                                
                                // EMPLOYEE NAME AND EMPLOYE NUMBER DETAIS
                                 
                                 
				$pdf->SetFont('','B',9);//ARSP EDIT--> I CHANGED FONT SIZE

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(23, $adjust_y) );

				$pdf->Cell(10, 5, $user_obj->getFullName(), $border, 0, 'L');
                                
                                
				$pdf->SetFont('','B',9);//ARSP EDIT--> I CHANGED FONT SIZE

				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y) );

				$pdf->Cell(10, 5, 'Emp No : '.$user_obj->getEmployeeNumber(), $border, 0, 'L');

                                
                                
                                 
				//Pay Period info

				$pdf->SetFont('','',7);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
                                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(32, $adjust_y) );
                                
                                //$pdf->Cell(30,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'R');
				$pdf->Cell(10,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(35, $adjust_y) );
                                
                                //$pdf->Cell(30,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'R');
				$pdf->Cell(10,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'L');

				
                                //$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(38, $adjust_y) );

                                //$pdf->Cell(30,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'R');    
				$pdf->Cell(10,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'L');



				$pdf->SetFont('','B',7);

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(32, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(35, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'L');

                                //$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(10, $adjust_y) );
				$pdf->setXY( Misc::AdjustXY(17, $adjust_x), Misc::AdjustXY(38, $adjust_y) );

                                //$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'R');
				$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'L');







//-------@widanage add code from footer----17.04.2013------

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->SetFont('','B',12);

				

				//ARSP-->$pdf->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(17, $adjust_y) );//ARSP NOTE --> CHANGE 165 TO

				//ARSP-->$pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');

				

                                

//-------@widanage add code from footer----17.04.2013------

				

				

				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(27, $adjust_y) );



				//ARSP-->$pdf->SetFont('','B',14);

				//ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				//ARSP-->$pdf->Cell(175, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(37, $adjust_y) );



				//ARSP-->$pdf->setLineWidth( 0.25 );



				//Get pay stub entries.

				$pself = TTnew( 'PayStubEntryListFactory' );

				$pself->getByPayStubId( $pay_stub_obj->getId() );

				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);



				$max_widths = array( 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 );

				$prev_type = NULL;

				$description_subscript_counter = 1;

				foreach ($pself as $pay_stub_entry) {



					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);

					$description_subscript = NULL;



					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



					//Use this to put the total for each type at the end of the array.

					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

						$type = $pay_stub_entry_name_obj->getType();

					}

					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



					if ( $pay_stub_entry->getDescription() !== NULL

							AND $pay_stub_entry->getDescription() !== FALSE

							AND strlen($pay_stub_entry->getDescription()) > 0) {

						$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																'description' => $pay_stub_entry->getDescription() );



						$description_subscript = $description_subscript_counter;



						$description_subscript_counter++;

					}



					//If type if 40 (a total) and the amount is 0, skip it.

					//This if the employee has no deductions at all, it won't be displayed

					//on the pay stub.

					if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

						$pay_stub_entries[$type][] = array(

													'id' => $pay_stub_entry->getId(),

													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

													'type' => $pay_stub_entry_name_obj->getType(),

													'name' => $pay_stub_entry_name_obj->getName(),

													'display_name' => $pay_stub_entry_name_obj->getName(),

													'rate' => $pay_stub_entry->getRate(),

													'units' => $pay_stub_entry->getUnits(),

													'ytd_units' => $pay_stub_entry->getYTDUnits(),

													'amount' => $pay_stub_entry->getAmount(),

													'ytd_amount' => $pay_stub_entry->getYTDAmount(),



													'description' => $pay_stub_entry->getDescription(),

													'description_subscript' => $description_subscript,



													'created_date' => $pay_stub_entry->getCreatedDate(),

													'created_by' => $pay_stub_entry->getCreatedBy(),

													'updated_date' => $pay_stub_entry->getUpdatedDate(),

													'updated_by' => $pay_stub_entry->getUpdatedBy(),

													'deleted_date' => $pay_stub_entry->getDeletedDate(),

													'deleted_by' => $pay_stub_entry->getDeletedBy()

													);



						//Calculate maximum widths of numeric values.

						$width_units = strlen( $pay_stub_entry->getUnits() );

						if ( $width_units > $max_widths['units'] ) {

							$max_widths['units'] = $width_units;

						}



						$width_rate = strlen( $pay_stub_entry->getRate() );

						if ( $width_rate > $max_widths['rate'] ) {

							$max_widths['rate'] = $width_rate;

						}



						$width_amount = strlen( $pay_stub_entry->getAmount() );

						if ( $width_amount > $max_widths['amount'] ) {

							$max_widths['amount'] = $width_amount;

						}



						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );

						if ( $width_amount > $max_widths['ytd_amount'] ) {

							$max_widths['ytd_amount'] = $width_ytd_amount;

						}



						unset($width_rate, $width_units, $width_amount, $width_ytd_amount);

					}



					$prev_type = $pay_stub_entry_name_obj->getType();

				}



				//There should always be pay stub entries for a pay stub.

				if ( !isset( $pay_stub_entries) ) {

					continue;

				}

				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__,10);

				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__,10);



				$block_adjust_y = 39;// ARSP CHANGE VALUE 30 to 35



				//

				//Earnings

				//

				if ( isset($pay_stub_entries[10]) ) {

					//$column_widths['ytd_amount'] = ( $max_widths['ytd_amount']*2 < 25 ) ? 25 : $max_widths['ytd_amount']*2;

					$column_widths['amount'] = ( $max_widths['amount']*2 < 20 ) ? 20 : $max_widths['amount']*2;

					//$column_widths['rate'] = ( $max_widths['rate']*2 < 5 ) ? 5 : $max_widths['rate']*2;

					//$column_widths['units'] = ( $max_widths['units']*2 < 17 ) ? 17 : $max_widths['units']*2;
                                        
                                        
					//ARSP-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);
					$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);

					//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__,10);



					//Earnings Header

					$pdf->SetFont('','B',8);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell( $column_widths['name'], 13 ,TTi18n::gettext('Earnings'), $border, 0, 'L');
                                                                            //20 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON
					///$pdf->Cell( $column_widths['rate'], 5,TTi18n::gettext('Rate'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['units'], 5,TTi18n::gettext('Hrs/Units'), $border, 0, 'R');
                                        

					$pdf->Cell( $column_widths['amount'], 13 ,TTi18n::gettext('Amount'), $border, 0,  'R');
                                                                            //20 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON
					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 8;//15 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','',8);

					foreach( $pay_stub_entries[10] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 10 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //68

							//$pdf->Cell( $column_widths['rate'], 5, TTi18n::formatNumber( $pay_stub_entry['rate'], TRUE ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						} else {

							//Total

							$pdf->SetFont('','B',8);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount'])-$column_widths['units'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY( (175-(1+$column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //90

							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141

							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							$pdf->Cell( $column_widths['name'], 5, $pay_stub_entry['name'], $border, 0, 'L');

							//$pdf->Cell( $column_widths['rate'], 5, '', $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 3.5;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

					}

				}



				//

				// Deductions

				//

				if ( isset($pay_stub_entries[20]) ) {

					$max_deductions = count($pay_stub_entries[20]);

					$two_column_threshold = 14;//ARSP NOTE--> I CHANGED THIS VALUE DEFAULT '4',  OTHERWISE DEDUCTION VALUES WILL SHIFT 



					//Deductions Header

					$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','B',8);

					if ( $max_deductions > $two_column_threshold ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

						//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					} else {

						//ARSP-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);          
						$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					}



					//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 3.5;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','',8);

					$x=0;

					$max_block_adjust_y = 0;

					foreach( $pay_stub_entries[20] as $pay_stub_entry ) {

						//Start with the right side.

						//if ( $x < floor($max_deductions / 2) ) {

						if ( $x < floor($max_deductions) ) {//-----@widanage change 17.04.2013---

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 20 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > $two_column_threshold ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, Misc::TruncateString( $pay_stub_entry['name'], $column_widths['name']/1.7, 0, TRUE ) . $subscript, $border, 0, 'L');

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

							Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__,10);

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',8);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110                                                        
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 3.5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns

					if ( $max_deductions > $two_column_threshold ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				if ( isset($pay_stub_entries[40][0]) ) {

					$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					//Net Pay entry

					$pdf->SetFont('','B',8);



					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                			//ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');
					$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'],5, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'] ), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'] ), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

				}



				//

				//Employer Contributions

				//

				if ( isset($pay_stub_entries[30]) AND $hide_employer_rows != TRUE ) {

					$max_deductions = count($pay_stub_entries[30]);

					//Deductions Header

					$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON
                                        
                                        



					$pdf->SetFont('','B',8);

					if ( $max_deductions > 2 ) {
                                            
                                                
						//ARSP -->$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);
                                                $column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);
                                                

						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

						$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');
                                                

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//ARSP -->$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//ARSP -->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					} else {
                                                
                                                //ARSP-->$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);
						$column_widths['name'] = 65-($column_widths['ytd_amount']+$column_widths['amount']);



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');
                                                
                                                //ARSP --> I ADD THIS CODE HERE. IF $max_deductions < 2  EMPLOYER CONTRIBUTION HEADER WILL BE SHOW
                                                $pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					}



					//ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 3.5;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','',8);

					$x=0;

					$max_block_adjust_y = 0;
                                        
                                        //print_r($pay_stub_entries[30]);
                                        //exit();



					foreach( $pay_stub_entries[30] as $pay_stub_entry ) {

						//Start with the right side.
                                            
                                                //ARSP EDIT --> I HIDE THIS SOME CODE
                                                //if ( $x < floor($max_deductions / 2) ) 
						if ( $x < floor($max_deductions) ) {

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 30 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}

                                                        
                                                        
                                                        //ARSP EDIT--> I CHANGE THIS VALUE BEFORE VALUE --> ($max_deductions > 5) 
                                                        //IF MAX DEDUCTION MORE THAN 5 IT WILL BE MOVE RIGHT SIDE
							if ( $max_deductions > 5 ) {

								$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //38  
                                                                

							} else {

								$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //128

							}

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount']), $border, 0, 'R');

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							$pdf->SetFont('','B',8);


                                                        //ARSP-->$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111
							//ARSP-->$pdf->line(Misc::AdjustXY( (65-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(65-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

                                                        //ARSP-->$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141
							$pdf->line(Misc::AdjustXY( 65-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(65, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 3.5;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns
                                        
                                        //ARSP EDIT--> I CHANGE THIS VALUE. BEFORE --> ($max_deductions > 2)
					if ( $max_deductions > 6 ) {

						$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				//

				//Accruals PS accounts

				//

				if ( isset($pay_stub_entries[50]) ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','B',8);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');
					$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');

					$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('Balance'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 3.5;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','',8);

					foreach( $pay_stub_entries[50] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 50 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

                                                        
                                                        //ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');
							$pdf->Cell( 65-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'],5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

					}

				}



				//

				//Accrual Policy Balances

				//

				$ablf = TTnew( 'AccrualBalanceListFactory' );

				$ablf->getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_obj->getId(), $user_obj->getCompany(), TRUE );

				if ( $ablf->getRecordCount() > 0 ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 4;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->SetFont('','B',7);//8 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON



					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );



					$accrual_time_header_start_x = $pdf->getX();

					$accrual_time_header_start_y = $pdf->getY();



					$pdf->Cell(64,5,TTi18n::gettext('Accrual Time Balances as of ').TTDate::getDate('DATE', time() ) , $border, 0, 'L');

					$pdf->Cell(1,5,TTi18n::gettext('Balance (hrs)'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 3;

					$box_height = 5;



					$pdf->SetFont('','',7);//8 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

					foreach( $ablf as $ab_obj ) {

						$balance = $ab_obj->getBalance();

						if ( !is_numeric( $balance ) ) {

							$balance = 0;

						}



						$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						$pdf->Cell(64,5, $ab_obj->getColumn('name'), $border, 0, 'L');

						$pdf->Cell(1,5, TTi18n::formatNumber( TTDate::getHours( $balance ) ), $border, 0, 'R');



						$block_adjust_y = $block_adjust_y + 3;//5 ARSP NOTE --> I CHANGED THIS CODE FOR THUNDER & NEON

						$box_height = $box_height + 3;

						unset($balance);

					}

					$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 65, $box_height );



					unset($accrual_time_header_start_x, $accrual_time_header_start_y, $box_height);

				}





				//

				//Descriptions

				//

				/*if ( isset($pay_stub_entry_descriptions) AND count($pay_stub_entry_descriptions) > 0 ) {



					//Description Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell(175,5,TTi18n::gettext('Notes'), $border, 0, 'L');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',8);

					$x=0;

					foreach( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {

						if ( $x % 2 == 0 ) {

							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						} else {

							$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						}



						//$pdf->Cell(173,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');

						$pdf->Cell(85,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');



						if ( $x % 2 != 0 ) {

							$block_adjust_y = $block_adjust_y + 5;

						}

						$x++;

					}

				}*/

				unset($x, $pay_stub_entry_descriptions, $pay_stub_entry_description);



				//

				// Pay Stub Footer

				//



				$block_adjust_y = 90;

				//Line

				//ARSP-->$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y, $adjust_y) );



				//Non Negotiable

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+3, $adjust_y) );

				//$pdf->Cell(175, 5, TTi18n::gettext('NON NEGOTIABLE'), $border, 0, 'C', 0);



				//Employee Address

				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+9, $adjust_y) );

				//$pdf->Cell(60, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'C', 0);

				//$pdf->SetFont('','',10);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+14, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getFullName() .' (#'.$user_obj->getEmployeeNumber().')', $border, 0, 'C', 0);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+19, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getAddress1(), $border, 0, 'C', 0);

				//$address2_adjust_y = 0;

				/*if ( $user_obj->getAddress2() != '' ) {

					$address2_adjust_y = 5;

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24, $adjust_y) );

					$pdf->Cell(60, 5, $user_obj->getAddress2(), $border, 0, 'C', 0);

				}*/

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24+$address2_adjust_y, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 1, 'C', 0);



				//Pay Period - Balance - ID

				$net_pay_amount = 0;

				if ( isset($pay_stub_entries[40][0]) ) {

					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], TRUE );

				}



				if ( isset($pay_stub_entries[65]) AND count($pay_stub_entries[65]) > 0 ) {

					$net_pay_label = TTi18n::gettext('Balance');

				} else {

					$net_pay_label = TTi18n::gettext('Net Pay');

				}



				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY($block_adjust_y+17, $adjust_y) );

				//$pdf->Cell(100, 5, $net_pay_label.': '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', 0);



				if ( $pay_stub_obj->getTainted() == TRUE ) {

					$tainted_flag = 'T';

				} else {

					$tainted_flag = '';

				}

				//$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY($block_adjust_y+30, $adjust_y) );

				//$pdf->Cell(50, 5, TTi18n::gettext('Identification #:').' '. str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT).$tainted_flag, $border, 1, 'L', 0);

                                

                                
                                /*
                                 * FOOTER
                                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                 */
                                
                                $pdf->SetFont('', '', 6);
                                $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(65, $adjust_y + 95));
                                $pdf->Cell(65,80, TTi18n::gettext('Pay Stub Generated by') . ' ' . APPLICATION_NAME, $border, 0, 'C', 0);
                                
                                
                                
                                
                                
                                
                                
				unset($net_pay_amount, $tainted_flag);



				//Line

//				$pdf->setLineWidth( 1 );
//
//				$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+35, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y+35, $adjust_y) );
//
//
//
//				$pdf->SetFont('','', 6);
//
//				$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+38, $adjust_y) );
//                                
//                                
//				$pdf->Cell(175, 1, TTi18n::gettext('Pay Stub Generated by').' '. APPLICATION_NAME , $border, 0, 'C', 0);



				unset($pay_stub_entries, $pay_period_number);



				$this->getProgressBarObject()->set( NULL, $pslf->getCurrentRow() );



				$i++;
                                
                                //ARSP ADD CODE        
                                if( $page_no % 4 == 0 )
                                {
                                    
                                    $pdf->addPage();
                                    
                                    
                                    
                                    $adjust_x1 = 20;

                                    $adjust_y1 = 5;


                                    //Logo

//                                    $pdf->Image($company_obj->getLogoFileName(), Misc::AdjustXY(0, $adjust_x1 + 0), Misc::AdjustXY(1, $adjust_y1 + 0), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);



                                    
                                    //LINE
//                                    $pdf->setLineWidth( 0.5 );
//                                    $pdf->SetDrawColor(200, 200, 200);
//                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(17, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(17, $adjust_y));



                                    //Company name/address

//                                    $pdf->SetFont('', 'B', 14);
//
//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(0, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getName(), $border, 0, 'C');



//                                    $pdf->SetFont('', '', 10);
//
//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(6, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getAddress1() . ' ' . $company_obj->getAddress2(), $border, 0, 'C');



//                                    $pdf->setXY(Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(10, $adjust_y1));
//
//                                    $pdf->Cell(75, 5, $company_obj->getCity() . ', ' . $company_obj->getProvince() . ' ' . strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                    
                                    
                                    //CONFIDENTIAL
//                                    $pdf->SetFont('','B',12);			
//
//                                    $pdf->setXY( Misc::AdjustXY(195, 3), Misc::AdjustXY(10, $adjust_y) );
//
//                                    $pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');
                                    



//                                    $pdf->SetFont('','B',10);
//
//                                    $pdf->setXY( Misc::AdjustXY(50, $adjust_x1), Misc::AdjustXY(18, $adjust_y1) );
//
//                                    $pdf->Cell(75, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



                                    //Line  After "STATEMENT OF EARNINGS AND DEDUCTIONS" Line
//                                    $pdf->setLineWidth( 0.5 );
//                                    $pdf->SetDrawColor(200, 200, 200);
//                                    $pdf->Line(Misc::AdjustXY(0, 0), Misc::AdjustXY(23, $adjust_y), Misc::AdjustXY(210, $adjust_y), Misc::AdjustXY(23, $adjust_y));














                                    //ARSP EDIT--> I ADDED FOOTER HERE (ORIGINAL PLACE IS DOWN)
                                    //Line
                                    //$pdf->setLineWidth( 1 );
                //                                    $pdf->Line( Misc::AdjustXY(0, 3), Misc::AdjustXY(280,-5), Misc::AdjustXY(270, -15), Misc::AdjustXY(280,-5) );
                                    $pdf->Line(Misc::AdjustXY(0, 200), Misc::AdjustXY(280, $adjust_y), Misc::AdjustXY(5, $adjust_y), Misc::AdjustXY(280, $adjust_y));



                                    $pdf->SetY(-10);
                                    // Set font
                                    $pdf->SetFont('', 'I', 8);
                                    // Page number
                                    $pdf->Cell(305, 10, 'Page ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

                                    // ARSP NOTE --> I HIDE THIS COD EFOR THUNDER & NEON
//                                    $pdf->SetFont('', '', 6);
//
//                                    //$pdf->setXY( Misc::AdjustXY(70, 0), Misc::AdjustXY(0, -15) );
//                                    $pdf->setXY(Misc::AdjustXY(0, 0), Misc::AdjustXY(0, -15));
//                                    $pdf->Cell(0, 10, TTi18n::gettext('Pay Stub Generated by') . ' ' . APPLICATION_NAME, $border, 0, 'C', 0);
                                    
                                    
                                    
                                    //
                                    //Draw 2 Virtical Lines
                                    //
                                                                        
                                    //ARSP EDIT --> DRAW VIRTICAL LINE 1
                                    // center of ellipse
                                    $xc = 70;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT

                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);

                                    //ARSP EDIT --> DRAW VIRTICAL LINE 2
                                    $xc = 140;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT

                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                    //ARSP EDIT --> DRAW VIRTICAL LINE 3
                                    $xc = 210;
                                    $yc = 50;//ARSP NOTE --> VIRTICAL LINE ADJUSTMENT

                                    // X Y axis
                                    //$pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Line($xc, $yc - 50, $xc, $yc + 207);
                                    
                                    
                                    
                                }
                                    
                                $page_no++;//ARSP ADD CODE                                
                                
                                
                                //$page_no++;//ARSP EDIT

			}



			$output = $pdf->Output('','S');

		}



		TTi18n::setMasterLocale();



		if ( isset($output) ) {

			return $output;

		}



		return FALSE;

	}   





	
	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE ARRAY TO PDF PORTRAIT
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */	
	
    function Array2PDF($data, $columns = NULL, $current_user, $current_company,$transactionstart_date, $transaction_end_date, $payperiod_string)
        {
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            


            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               


                
                
                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(),
                                                  'start_date'   => $transactionstart_date,    
                                                  'end_date'     => $transaction_end_date,
                                                  'payperiod_string'     => $payperiod_string,
                    
                                                );
												
				$pdf = TTnew( 'PayStubMyPdfHeaderFooter' );								
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 44, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage();
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 19;		
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(44, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="1" cellspacing="0" cellpadding="0" width="113%">
                        <tr style="background-color:#CCCCCC;text-align:center;">';
                $html = $html.'<td width = "3%">#</td>';
                
                $pdf->SetFont('', 'B');    
                foreach( $columns as $column_name )
                {                    
                    $html = $html.'<td style ="text-align:center:justify;font-weight:bold;" >'.$column_name.'</td>';                      
                }
                $html=  $html.'</tr>';
                
                $pdf->SetFont('','',10);  
  
                $x=1;   
                foreach( $data as $rows ) 
                {                    
                        if($x % 2 == 0)
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        }
                        else
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        }                    
                    
                    $html = $html.'<td>'.$x++.'</td>';
                    
                    foreach ($columns as $column_key => $column_name ) 
                    {
                        if ( isset($rows[$column_key])  && $rows[$column_key] != "")
                        {
                            $html = $html.'<td>'.$rows[$column_key].'</td>'; 
                        }
                        
                        else
                        {
                            $html = $html.'<td>'.'--'.'</td>';
                        }
                    }
                    $html=  $html.'</tr>';          
                }
				
				
				//SUM ROW
                $html=  $html.'<tr style ="background-color:#CCCCCC;text-align:justify;" >';
                $html = $html.'<td width = "3%"></td>';	
							
                foreach($columns as $column_key1=>$column_value)
                {
                    $checked=0;
                    foreach( $last_row as $key=>$value)
                    {
                        if($key == $column_key1 && isset($value) != "")
                        {
                            $html = $html.'<td style ="center;text-align:center:justify;font-weight:bold;" >'.$value.'</td>'; 
                            $checked=1;
                        }
                    }
                    
                    if($checked != 1)
                    {
                        $html = $html.'<td style ="text-align:center:justify;font-weight:bold;">--</td>';
                    }                        
                }
                $html=  $html.'</tr>';					
				
				
				
				                        
                $html=  $html.'</table>';        
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
				$output = $pdf->Output('','S');
                        
                //exit;  
				
				if ( isset($output) )
				{
					return $output;				
				}
				
				return FALSE;              
                                
            }

        }
		
		
	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE ARRAY TO PDF LANDSCAPE
	 *
	 *PAGE ORIENTATION IS LANDSCAPE
	 */			
		
    function Array2PDFLandscape($data, $columns = NULL, $current_user, $current_company,$transactionstart_date, $transaction_end_date, $payperiod_string, $filt_list='')
        {
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            
//            var_dump($filt_list); die;
            //exit();
    
            


            if ( is_array($data) AND count($data) > 0 AND is_array($columns) AND count($columns) > 0 )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               


                
                
                
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(),
                                                  'start_date'   => $transactionstart_date,    
                                                  'end_date'     => $transaction_end_date,
                                                  'payperiod_string'     => $payperiod_string,
                                                  'departments' => $filt_list['dept'],
                                                  'groups' => $filt_list['group'],
                    
                                                );
												
				$pdf = TTnew( 'PayStubMyPdfHeaderFooterLandscape' );								
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, 37, PDF_MARGIN_RIGHT);//44
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM)

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('L','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 9 ;//19		
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(37, $adjust_y) );//44
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $html = '<table border="1" cellspacing="0" cellpadding="0" width="105%">
                        <tr style="background-color:#CCCCCC;text-align:center;">';
                $html = $html.'<td width = "2%">#</td>';
                
                $pdf->SetFont('', 'B');    
                foreach( $columns as $column_name )
                {                    
                    $html = $html.'<td style ="text-align:center:justify;font-weight:bold;" >'.$column_name.'</td>';                      
                }
                $html=  $html.'</tr>';
                
                $pdf->SetFont('','',6);//10  
  
                $x=1;   
                foreach( $data as $rows ) 
                {                    
                        if($x % 2 == 0)
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="#EEEEEE" nobr="true">';
                        }
                        else
                        {
                            $html=  $html.'<tr style ="text-align:center" bgcolor="WHITE" nobr="true">';
                        }                    
                    
                    $html = $html.'<td>'.$x++.'</td>';
                    
                    foreach ($columns as $column_key => $column_name ) 
                    {
                        if ( isset($rows[$column_key]) && $rows[$column_key] != ""  )
                        {
                            $html = $html.'<td>'.$rows[$column_key].'</td>'; 
                        }
                        
                        else
                        {
                            $html = $html.'<td>'.'--'.'</td>';
                        }
                    }
                    $html=  $html.'</tr>';          
                } 
				
				
				
				//SUM ROW
                $html=  $html.'<tr style ="background-color:#CCCCCC;text-align:justify;" >';
                $html = $html.'<td width = "2%"></td>';	
							
                foreach($columns as $column_key1=>$column_value)
                {
                    $checked=0;
                    foreach( $last_row as $key=>$value)
                    {
                        if($key == $column_key1 && isset($value) != "")
                        {
                            $html = $html.'<td style ="center;text-align:center:justify;font-weight:bold;" >'.$value.'</td>'; 
                            $checked=1;
                        }
                    }
                    
                    if($checked != 1)
                    {
                        $html = $html.'<td style ="text-align:center:justify;font-weight:bold;">--</td>';
                    }                        
                }
                $html=  $html.'</tr>';				
				
				
				
				
							
				
				
				//End of table                       
                $html=  $html.'</table>';        
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                unset($_SESSION['header_data']);
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
				$output = $pdf->Output('','S');
                        
                //exit;  
				
				if ( isset($output) )
				{
					return $output;				
				}
				
				return FALSE;              
                                
            }

        }	
	






	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE FORM C
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */			
		
    function FormC($data, $include_user_ids , $current_user, $current_company, $payperiod_string, $branch_id_only)
        {
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            


            if ( is_array($data) AND count($include_user_ids) > 0  )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               


                
                
                
                $_SESSION['header_data'] = array();// first we have to create session array then we can add new element into the session array
                if($branch_id_only != "" || $branch_id_only != NULL)
                {
                    $blf = TTnew( 'BranchListFactory' );
                    $blf->getById($branch_id_only);

                    foreach ($blf as $temp)
                    {
                        //echo $test1->getName();
                        $_SESSION['header_data'] = array( 
                                                          //'image_path'   => $current_company->getLogoFileName(),
                                                          'company_name' => $temp->getNameById($branch_id_only),
                                                          'address1'     => $temp->getAddress1(),
                                                          'address2'     => $temp->getAddress2(),
                                                          'city'         => $temp->getCity(),
                                                          'province'     => $temp->getProvince(),
                                                          'postal_code'  => $temp->getPostalCode(),
                                                          'phone'        => $temp->getWorkPhone(),
                                                          'fax'          => $temp->getFaxPhone(),
                                                          'email'        => $current_user->getWorkEmail(),                                                     
                                                          'payperiod_string'     => $payperiod_string,
                                                          'epf_registration_no'  => $current_company->getOriginatorID()    

                                                        );                    

                        //$data = array('name' => $wage->getId());
                        //print_r($test1->getName());                
                    }                
                }            
                else
                {
                    $_SESSION['header_data'] = array( 
                                                      //'image_path'   => $current_company->getLogoFileName(),
                                                      'company_name' => $current_company->getName(),
                                                      'address1'     => $current_company->getAddress1(),
                                                      'address2'     => $current_company->getAddress2(),
                                                      'city'         => $current_company->getCity(),
                                                      'province'     => $current_company->getProvince(),
                                                      'postal_code'  => $current_company->getPostalCode(),
                                                      'phone'        => $current_user->getWorkPhone(),
                                                      'fax'          => $current_user->getFaxPhone(),
                                                      'email'        => $current_user->getWorkEmail(),                                                     
                                                      'payperiod_string'     => $payperiod_string,
                                                      'epf_registration_no'  => $current_company->getOriginatorID()    

                                                    );                
                }
                $pdf = TTnew( 'PayStubListFactoryFormC' );			
				
//--------------------------------- Get Contribution values --------------------  
                
                $ulf = TTnew( 'UserListFactory' );     
                $contributions = 0.0;  
                foreach( $data as $row1 ) 
                {
                    $employer1= 0.0;
                    $employee1 = 0.0;
                    $total1 = 0.0;                    
                    
                    $user_id = $row1['user_id']; 
                    $regular_salary = $row1['1']; //in this case $row['1'] is a regular time earning value
                    $user_obj = $ulf->getById( $user_id )->getCurrent();//get user object  
                    
                    $employer1= ((int)$regular_salary * 15) / 100; //calculate 15 persentage of regular earning
                    $employee1 = ((int)$regular_salary * 10) / 100;//calculate 10 persentage of regular earning
                    
                    //$total = number_format(($employer + $employee ), 2);
                    $total1 = (float)$employer1 + (float)$employee1;
                    $contributions = (float)$contributions + (float)$total1;
                     
                } 
                array_push($_SESSION['header_data'],number_format($contributions, 2));//ADD NEW ELEMENT INTO THE SESSION ARRAY.

//--------------------------------- Get Contribution values --------------------  				
				
				
				
				
				
				
				
									
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                //$pdf->SetMargins(PDF_MARGIN_LEFT, 44, PDF_MARGIN_RIGHT);
                $pdf->SetMargins(10, 71.5, 2);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 9;		
                
                $pdf->setXY( Misc::AdjustXY(1, $adjust_x), Misc::AdjustXY(71.5, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $pdf->SetFont('times','',10);                
                
                $html = '<table border="1" cellspacing="0" cellpadding="2" width="100%">';  
                
            
                $ulf = TTnew( 'UserListFactory' );     
                
                //$contributions = 0.0;
                $total_employer = 0.0;
                $total_employee = 0.0;        
                $i=1;   
                foreach( $data as $row ) 
                {
                    $epf_membership_no = "";
                    $nic = "";
                    $employer= 0.0;
                    $employee = 0.0;
                    $total = 0.0;
                    
                    $user_id = $row['user_id']; 
                    $full_name =  $row['full_name'];
                    
                    $regular_salary = $row['1']; //in this case $row['1'] is a regular time earning value
                    $user_obj = $ulf->getById( $user_id )->getCurrent();//get user object  
                    
                    $employer= ((int)$regular_salary * 15) / 100; //calculate 12 persentage of regular earning
                    $employee = ((int)$regular_salary * 10) / 100;//calculate 8 persentage of regular earning
                    
                    $total_employer = (float)$total_employer + (float)$employer;
                    $total_employee = (float)$total_employee + (float)$employee;
                    
                    
                    //$total = number_format(($employer + $employee ), 2);
                    $total = (float)$employer + (float)$employee;
                    //$contributions = (float)$contributions + (float)$total;
                    
                    
                    $epf_membership_no = $user_obj->getEmployeeNumber(); //get EPF REGISTRATION NO  
                    $nic = $user_obj->getNic();//get NIC    
                    
                    if($i % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="#EEEEEE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="WHITE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    
                    
                    $html =  $html.'
                                    <td width= "28%" >'.$full_name.'</td>
                                    <td width= "11%" align="center">'.$nic.'</td>
                                    <td width= "7%" align="center">'.$epf_membership_no.'</td>
                                    <td width= "14%" align="right">'.number_format($total, 2).'</td>
                                    <td width= "12%" align="right">'.number_format($employer, 2).'</td>
                                    <td width= "12%" align="right">'.number_format($employee, 2).'</td>
                                    <td width= "15%" align="right">'.number_format($regular_salary, 2).'</td>
                             </tr>';  
                    $i++;
                     
                }     
				
				
				//LAST ROW (TOTAL VALUES)
                $html=  $html.'<tr style ="border-bottom: solid 3px black;">';
                $html =  $html.'
                                <td width= "28%"></td>
                                <td width= "11%"></td>
                                <td width= "7%"></td>
                                <td width= "14%" style ="text-align:right:justify;font-weight:bold;" bgcolor="#CCCCCC">'.number_format($contributions, 2).'</td>
                                <td width= "12%" style ="text-align:right:justify;font-weight:bold;" bgcolor="#CCCCCC">'.number_format($total_employer, 2).'</td>
                                <td width= "12%" style ="text-align:right:justify;font-weight:bold;" bgcolor="#CCCCCC">'.number_format($total_employee, 2).'</td>
                                <td width= "15%"></td>
                         </tr>';                 
                
                $html=  $html.'</table>';  				
				
				
				
				
				 
      
                        
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                
                        
                //Close and output PDF document
                //$pdf->Output('example_006.pdf', 'I');
				$output = $pdf->Output('','S');
				unset($_SESSION['header_data']);
                        
                //exit;  
				
				if ( isset($output) )
				{
					return $output;				
				}
				
				return FALSE;              
                                
            }

        }	


        
	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE FORM C 6 Months Report 
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */	         
        function FormCSixMonth($data, $include_user_ids , $current_user, $current_company, $pay_period_year, $set)
        {
            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";   

            
            //ARSP EDIT --->
            //print_r($data);// we have to print selected column values and then can decide which array index is Regular Time. in this case ( [1] => Regular Time ) array key value '1' is the regular earning salarry
            //exit();
            
            if ( is_array($data) AND count($include_user_ids) > 0 )
            {                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                    //exit();
                }                
                
                                
                /*
                 * $rows ARRAY IS EACH EMPLOYEE 6 MONTHS DATA SO WE NEED TIO GOUP BY THE ARRAY BY 'user_id'
                 * 
                 */
                
                $group = array();
                foreach ( $data as $value )
                {                  
                    $group[$value['user_id']][] = $value;
                }
                
                //GET THE TOTAL NUMBER OF THE SLECTED EMPLOYES
                $total_noof_employee = count($group);   
                
                
                $_SESSION['header_data'] = array();// first we have to create session array then we can add new element into the session array
                $_SESSION['header_data'] = array( 
                                                  //'image_path'   => $current_company->getLogoFileName(),
                                                  'employer_name'=> $current_user->getFullName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(),
                                                  'phone'        => $current_user->getWorkPhone(),
                                                  'fax'          => $current_user->getFaxPhone(),
                                                  'email'        => $current_user->getWorkEmail(),                                                     
                                                  'pay_period_year'     => $pay_period_year,
                                                  'epf_registration_no'  => $current_company->getOriginatorID(),
                                                  'set'                  => $set,
                                                  'total_employee'       => $total_noof_employee
                                                 );
                
                

                $pdf = TTnew( 'PayStubListFactoryFormCSixMonth' ); 
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetMargins(10, 39, 2);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                // $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                $pdf->SetAutoPageBreak(TRUE, 40.5);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('l','mm','A3');                
                
                //
                //TABLE START HERE
                //
 
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 10;
                
                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(39, $adjust_y) );                
                                           
                // create some HTML content
                $pdf->SetFont('times','',9);                
                
                $html = '<table border="1" cellspacing="0" cellpadding="0" width="97%">';            
                
                $total_employer = 0.0;
                $total_employee = 0.0;           
                
                $page_wise_total_contribution = 0.0;
                $page_wise_total_earning1 = 0.0;
                $page_wise_contribution1 = 0.0;
                $page_wise_total_earning2 = 0.0;
                $page_wise_contribution2 = 0.0;
                $page_wise_total_earning3 = 0.0;
                $page_wise_contribution3 = 0.0;
                $page_wise_total_earning4 = 0.0;
                $page_wise_contribution4 = 0.0;
                $page_wise_total_earning5 = 0.0;
                $page_wise_contribution5 = 0.0;
                $page_wise_total_earning6 = 0.0;
                $page_wise_contribution6 = 0.0;         
                
                
                $page = 0;
                $i=1;   
                foreach( $group as $row ) 
                {                    
                    $final_array = array();
                    $total_contribution = 0;
                    foreach($row as $data)
                    {
                        $sub_string = substr($data['pay_period'],17, 2);
                        $final_array[$sub_string]['pay_period'] = $data['pay_period'];
                        $final_array[$sub_string]['regular_salary'] = $data[1];
                        $final_array[$sub_string]['contribution'] = $data[18];//This is Contribution is ETF so we have to find the ETF array index, in this case '[30] => ETF - 3%' is the ETF. ##### Go to this function "function Array2PDFLandscape()" and print the $columns value and find the deduction data                        
                        
                        $total_contribution =  $total_contribution + $data[18];//This is Contribution is ETF so we have to find the ETF array index, in this case '[30] => ETF - 3%' is the ETF         
                    }

                    
                    $epf_membership_no = "";
                    $nic = "";                    
                    $user_id = $row[0]['user_id']; 
                    $first_name = $row[0]['first_name'];
                    $full_name =  $row[0]['last_name'].' '.$first_name[0].' '.$row[0]['middle_initial'] ;
                    
                    $ulf = TTnew( 'UserListFactory' );     
                    $user_obj = $ulf->getById( $user_id )->getCurrent();//get user object  
                    
                    $epf_membership_no = $user_obj->getEmployeeNumber(); //get EPF REGISTRATION NO  but child fund client ask employee number same for the  EPF registration nuymbeer
                    $nic = $user_obj->getNic();//get NIC    

                    
                    if($set == 2)
                    {
                        $total_earning1 = $final_array['07']['regular_salary'];
                        $contribution1  = $final_array['07']['contribution'];
                        $total_earning2 = $final_array['08']['regular_salary'];
                        $contribution2  = $final_array['08']['contribution'];
                        $total_earning3 = $final_array['09']['regular_salary'];
                        $contribution3  = $final_array['09']['contribution'];
                        $total_earning4 = $final_array['10']['regular_salary'];
                        $contribution4  = $final_array['10']['contribution'];
                        $total_earning5 = $final_array['11']['regular_salary'];
                        $contribution5  = $final_array['11']['contribution'];
                        $total_earning6 = $final_array['12']['regular_salary'];
                        $contribution6  = $final_array['12']['contribution'];                         

                        $page_wise_total_contribution = $page_wise_total_contribution + $total_contribution;
                        $page_wise_total_earning1     = $page_wise_total_earning1 + $total_earning1;
                        $page_wise_contribution1      = $page_wise_contribution1 + $contribution1;
                        $page_wise_total_earning2     = $page_wise_total_earning2 + $total_earning2;
                        $page_wise_contribution2      = $page_wise_contribution2 + $contribution2;
                        $page_wise_total_earning3     = $page_wise_total_earning3 + $total_earning3;
                        $page_wise_contribution3      = $page_wise_contribution3 + $contribution3;
                        $page_wise_total_earning4     = $page_wise_total_earning4 + $total_earning4;
                        $page_wise_contribution4      = $page_wise_contribution4 + $contribution4;
                        $page_wise_total_earning5     = $page_wise_total_earning5 + $total_earning5;
                        $page_wise_contribution5      = $page_wise_contribution5 + $contribution5;
                        $page_wise_total_earning6     = $page_wise_total_earning6 + $total_earning6;
                        $page_wise_contribution6      = $page_wise_contribution6 + $contribution6;        
                    }
                    
                    
                    if($set == 1)
                    {
                        $total_earning1 = $final_array['01']['regular_salary'];
                        $contribution1  = $final_array['01']['contribution'];
                        $total_earning2 = $final_array['02']['regular_salary'];
                        $contribution2  = $final_array['02']['contribution'];
                        $total_earning3 = $final_array['03']['regular_salary'];
                        $contribution3  = $final_array['03']['contribution'];
                        $total_earning4 = $final_array['04']['regular_salary'];
                        $contribution4  = $final_array['04']['contribution'];
                        $total_earning5 = $final_array['05']['regular_salary'];
                        $contribution5  = $final_array['05']['contribution'];
                        $total_earning6 = $final_array['06']['regular_salary'];
                        $contribution6  = $final_array['06']['contribution'];   
                        
                        $page_wise_total_contribution = $page_wise_total_contribution + $total_contribution;
                        $page_wise_total_earning1     = $page_wise_total_earning1 + $total_earning1;
                        $page_wise_contribution1      = $page_wise_contribution1 + $contribution1;
                        $page_wise_total_earning2     = $page_wise_total_earning2 + $total_earning2;
                        $page_wise_contribution2      = $page_wise_contribution2 + $contribution2;
                        $page_wise_total_earning3     = $page_wise_total_earning3 + $total_earning3;
                        $page_wise_contribution3      = $page_wise_contribution3 + $contribution3;
                        $page_wise_total_earning4     = $page_wise_total_earning4 + $total_earning4;
                        $page_wise_contribution4      = $page_wise_contribution4 + $contribution4;
                        $page_wise_total_earning5     = $page_wise_total_earning5 + $total_earning5;
                        $page_wise_contribution5      = $page_wise_contribution5 + $contribution5;
                        $page_wise_total_earning6     = $page_wise_total_earning6 + $total_earning6;
                        $page_wise_contribution6      = $page_wise_contribution6 + $contribution6;                          
                    }
                    
                    
                    if($i % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="#EEEEEE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="WHITE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    
                    
                    $html =  $html.'
                                    <td width= "14%" >'.$full_name.'</td>
                                    <td width= "4.5%" align="center">'.$epf_membership_no.'</td>    
                                    <td width= "6.5%" align="center">'.$nic.'</td>                                    
                                    <td width= "6%" align="right">'.number_format($total_contribution, 2).'</td>
                                    <td width= "6.5%" align="right">'.number_format($total_earning1, 2).'</td>
                                    <td width= "5.5%" align="right">'.number_format($contribution1, 2).'</td>
                                    <td width= "6.5%" align="right">'.number_format($total_earning2, 2).'</td>
                                        
                                    <td width= "5.5%" align="right">'.number_format($contribution2, 2).'</td>
                                    <td width= "6.5%" align="right">'.number_format($total_earning3, 2).'</td>
                                    <td width= "5.5%" align="right">'.number_format($contribution3, 2).'</td>

                                    <td width= "6.5%" align="right">'.number_format($total_earning4, 2).'</td>
                                    <td width= "5.5%" align="right">'.number_format($contribution4, 2).'</td>
                                    <td width= "6.5%" align="right">'.number_format($total_earning5, 2).'</td>

                                    <td width= "5.5%" align="right">'.number_format($contribution5, 2).'</td>
                                    <td width= "6.5%" align="right">'.number_format($total_earning6, 2).'</td>
                                    <td width= "5.5%" align="right">'.number_format($contribution6, 2).'</td>

                             </tr>';  
                    
                    
                    /*
                     * *******************************************************************************************************
                     * Print the Page-Wise Total for end of the every page 
                     * 
                     * *************************************ARSP NOTE*********************************************************
                     * EVERY PAGE TABLE ROW MUST BE 30 OTHER WISE TOTAL IS WRONG SO MAKE SURE 30 OR LESS THAN 30 ROWS PER PAGE
                     * *******************************************************************************************************
                     */                    
                    
                    //Total Table
                    if($i % 30 == 0 || ($total_noof_employee == $i )) //Print the Page-Wise Total for end of the every page 
                    {                        
                        $page++;
                        $html=  $html.'<tr style ="border-bottom: solid 3px black;">';
                        $html =  $html.'
                                        <td width= "14%"></td>
                                        <td width= "4.5%"></td>
                                        <td width= "6.5%"></td>
                                        <td width= "6%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_contribution, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning1, 2).'</td>
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution1, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning2, 2).'</td>
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution2, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning3, 2).'</td>
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution3, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning4, 2).'</td>
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution4, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning5, 2).'</td>
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution5, 2).'</td>
                                        <td width= "6.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_total_earning6, 2).'</td>                                  
                                        <td width= "5.5%" style ="text-align:right:justify; font-size: 8pt;" bgcolor="#CCCCCC">'.number_format($page_wise_contribution6, 2).'</td>
                                 </tr>';  
                        
                        $html=  $html;   
                    }
                    
                    
                    if($i % 30 == 0 )
                    {
                        $page_wise_total_contribution = 0.0;
                        $page_wise_total_earning1 = 0.0;
                        $page_wise_contribution1 = 0.0;
                        $page_wise_total_earning2 = 0.0;
                        $page_wise_contribution2 = 0.0;
                        $page_wise_total_earning3 = 0.0;
                        $page_wise_contribution3 = 0.0;
                        $page_wise_total_earning4 = 0.0;
                        $page_wise_contribution4 = 0.0;
                        $page_wise_total_earning5 = 0.0;
                        $page_wise_contribution5 = 0.0;
                        $page_wise_total_earning6 = 0.0;
                        $page_wise_contribution6 = 0.0;                          
                    }                   
                    
                    $i++;
                     
                }
                
                $html=  $html.'</table>';  
                
                $pdf->writeHTML($html, true, false, false, false, '');               
                        
                //Close and output PDF document
                ob_clean();//if any TCPDF error like this --> TCPDF ERROR: Some data has already been output to browser, can't send PDF file' occurred use this code
                $pdf->Output('example_006.pdf', 'I');
                unset($_SESSION['header_data']);                        
                exit;   
                
            }            
        }           

        
        
        /*
         * 
         * ARSP EDIT ---> ADD NEW CODE
         * THIS CODE ADDED BY ME
         * THIS FUNCTION USE TO GET THE TOTAL ORGANIZATION SUMMARY CHILD FUND
         *  
         */
        function TotalOrganizationPaySummary( $pslf = NULL, $hide_employer_rows = TRUE, $current_company, $payperiod_string , $total_employees) {  
            
            



		if ( !is_object($pslf) AND $this->getId() != '' ) {

			$pslf = TTnew( 'PayStubListFactory' );

			$pslf->getById( $this->getId() );

		}



		if ( get_class( $pslf ) != 'PayStubListFactory' ) {

			return FALSE;

		}



		$border = 0;
                
//            echo "TEST";
//            print_r($pslf);
//            exit();


		if ( $pslf->getRecordCount() > 0 ) {

                    
                    $mail_body_array=array();//ARSP ADD--> 
                    $empty_employee_email = array();//ARSP ADD--> 
                    

			//$pdf = new TTPDF('P','mm','Letter');

			//@ARSP-->$pdf = new TTPDF('L','mm','A5');//----@widanage change code here----17.04.2013

			//@ARSP-->$pdf->setMargins(0,0);

			//$pdf->SetAutoPageBreak(TRUE, 30);

			//$pdf->$pdf->SetAutoPageBreak(FALSE);

			//$pdf->$pdf->SetFont('freeserif','',10);

			//$pdf->SetFont('FreeSans','',10);


                        
			$i=0;

			foreach ($pslf as $pay_stub_obj) {
                            
                            
                            $mail_body_array =  null;//ARSP ADD--> 

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );



				Debug::text($i .'. Pay Stub Transaction Date: '. $pay_stub_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__,10);



				//Get Pay Period information

				$pplf = TTnew( 'PayPeriodListFactory' );

				$pay_period_obj = $pplf->getById( $pay_stub_obj->getPayPeriod() )->getCurrent();



				//Use Pay Stub dates, not Pay Period dates.

				$pp_start_date = $pay_stub_obj->getStartDate();

				$pp_end_date = $pay_stub_obj->getEndDate();

				$pp_transaction_date = $pay_stub_obj->getTransactionDate();



				//Get User information

				$ulf = TTnew( 'UserListFactory' );

				$user_obj = $ulf->getById( $pay_stub_obj->getUser() )->getCurrent();



				//Get company information

				$clf = TTnew( 'CompanyListFactory' );

				$company_obj = $clf->getById( $user_obj->getCompany() )->getCurrent();



				//Change locale to users own locale.

				TTi18n::setCountry( $user_obj->getCountry() );

				TTi18n::setLanguage( $user_obj->getUserPreferenceObject()->getLanguage() );

				TTi18n::setLocale();



				//

				// Pay Stub Header

				//

				//@ARSP-->$pdf->AddPage();



				//@ARSP-->$adjust_x = 20;

				//@ARSP-->$adjust_y = 10;



				//Logo
				//@ARSP-->$pdf->Image( $company_obj->getLogoFileName() ,Misc::AdjustXY(0, $adjust_x+0 ),Misc::AdjustXY(1, $adjust_y+0 ), 50, 12, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
                                $mail_body_array['company_logo'] = $company_obj->getLogoFileName();



				//Company name/address

				//@ARSP-->$pdf->SetFont('','B',14);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getName(), $border, 0, 'C');
                                $mail_body_array['company_name'] = $company_obj->getName();



				//@ARSP-->$pdf->SetFont('','',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(6, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getAddress1().' '.$company_obj->getAddress2(), $border, 0, 'C');
                                $mail_body_array['company_address'] = $company_obj->getAddress1().' '.$company_obj->getAddress2();



				//@ARSP-->$pdf->setXY( Misc::AdjustXY(50, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(75,5,$company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode()), $border, 0, 'C');
                                $mail_body_array['company_city'] = $company_obj->getCity().', '.$company_obj->getProvince() .' '. strtoupper($company_obj->getPostalCode());



				//Pay Period info

				//@ARSP-->$pdf->SetFont('','',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Pay Start Date:').' ', $border, 0, 'R');

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Pay End Date:').' ', $border, 0, 'R');

				

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(30,5,TTi18n::gettext('Payment Date:').' ', $border, 0, 'R');



				//@ARSP-->$pdf->SetFont('','B',10);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(0, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_start_date ) , $border, 0, 'R');
                                $mail_body_array['pay_start_date'] = TTDate::getDate('DATE', $pp_start_date );//ARSP NEW
				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(5, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_end_date ) , $border, 0, 'R');
                                $mail_body_array['pay_end_date'] = TTDate::getDate('DATE', $pp_end_date );//ARSP NEW
				//@ARSP-->$pdf->setXY( Misc::AdjustXY(155, $adjust_x), Misc::AdjustXY(10, $adjust_y) );

				//@ARSP-->$pdf->Cell(20,5, TTDate::getDate('DATE', $pp_transaction_date ) , $border, 0, 'R');
                                $mail_body_array['payment_date'] = TTDate::getDate('DATE', $pp_transaction_date );//ARSP NEW



//-------@widanage add code from footer----17.04.2013------

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->SetFont('','B',12);

				

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(165, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				//@ARSP-->$pdf->Cell(10, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'R');

				

				//@ARSP-->$pdf->SetFont('','B',12);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(17, $adjust_y) );

				//@ARSP-->$pdf->Cell(10, 5, $user_obj->getFullName() .'  ( Emp. #'.$user_obj->getEmployeeNumber().') ', $border, 0, 'L');
                                $mail_body_array['employee_full_name'] = $user_obj->getFullName();//ARSP NEW
                                $mail_body_array['employee_number'] = $user_obj->getEmployeeNumber();//ARSP NEW
                                $mail_body_array['employee_work_email'] = $user_obj->getWorkEmail();//ARSP NEW
                                
//-------@widanage add code from footer----17.04.2013------

				

				

				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(27, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(27, $adjust_y) );



				//@ARSP-->$pdf->SetFont('','B',14);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(29, $adjust_y) );

				//@ARSP-->$pdf->Cell(175, 5, TTi18n::gettext('STATEMENT OF EARNINGS AND DEDUCTIONS'), $border, 0, 'C', 0);



				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(37, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY(37, $adjust_y) );



				//@ARSP-->$pdf->setLineWidth( 0.25 );



				//Get pay stub entries.

				$pself = TTnew( 'PayStubEntryListFactory' );

				$pself->getByPayStubId( $pay_stub_obj->getId() );

				Debug::text('Pay Stub Entries: '. $pself->getRecordCount()  , __FILE__, __LINE__, __METHOD__,10);



				$max_widths = array( 'units' => 0, 'rate' => 0, 'amount' => 0, 'ytd_amount' => 0 );

				$prev_type = NULL;

				$description_subscript_counter = 1;

				foreach ($pself as $pay_stub_entry) {
                                    




					//Debug::text('Pay Stub Entry Account ID: '.$pay_stub_entry->getPayStubEntryNameId()  , __FILE__, __LINE__, __METHOD__,10);

					$description_subscript = NULL;



					$pay_stub_entry_name_obj = $psealf->getById( $pay_stub_entry->getPayStubEntryNameId() )->getCurrent();



					//Use this to put the total for each type at the end of the array.

					if ( $prev_type == 40 OR $pay_stub_entry_name_obj->getType() != 40 ) {

						$type = $pay_stub_entry_name_obj->getType();

					}

					//Debug::text('Pay Stub Entry Name ID: '. $pay_stub_entry_name_obj->getId() .' Type ID: '. $pay_stub_entry_name_obj->getType() .' Type: '. $type, __FILE__, __LINE__, __METHOD__,10);



					if ( $pay_stub_entry->getDescription() !== NULL

							AND $pay_stub_entry->getDescription() !== FALSE

							AND strlen($pay_stub_entry->getDescription()) > 0) {

						$pay_stub_entry_descriptions[] = array( 'subscript' => $description_subscript_counter,

																'description' => $pay_stub_entry->getDescription() );



						$description_subscript = $description_subscript_counter;



						$description_subscript_counter++;

					}



					//If type if 40 (a total) and the amount is 0, skip it.

					//This if the employee has no deductions at all, it won't be displayed

					//on the pay stub.

					if ( $type != 40 OR ( $type == 40 AND $pay_stub_entry->getAmount() != 0 ) ) {

						$pay_stub_entries[$type][] = array(

													'id' => $pay_stub_entry->getId(),

													'pay_stub_entry_name_id' => $pay_stub_entry->getPayStubEntryNameId(),

													'type' => $pay_stub_entry_name_obj->getType(),

													'name' => $pay_stub_entry_name_obj->getName(),

													'display_name' => $pay_stub_entry_name_obj->getName(),

													'rate' => $pay_stub_entry->getRate(),

													'units' => $pay_stub_entry->getUnits(),

													'ytd_units' => $pay_stub_entry->getYTDUnits(),

													'amount' => $pay_stub_entry->getAmount(),

													'ytd_amount' => $pay_stub_entry->getYTDAmount(),



													'description' => $pay_stub_entry->getDescription(),

													'description_subscript' => $description_subscript,



													'created_date' => $pay_stub_entry->getCreatedDate(),

													'created_by' => $pay_stub_entry->getCreatedBy(),

													'updated_date' => $pay_stub_entry->getUpdatedDate(),

													'updated_by' => $pay_stub_entry->getUpdatedBy(),

													'deleted_date' => $pay_stub_entry->getDeletedDate(),

													'deleted_by' => $pay_stub_entry->getDeletedBy()

													);



						//Calculate maximum widths of numeric values.

						$width_units = strlen( $pay_stub_entry->getUnits() );

						if ( $width_units > $max_widths['units'] ) {

							$max_widths['units'] = $width_units;

						}



						$width_rate = strlen( $pay_stub_entry->getRate() );

						if ( $width_rate > $max_widths['rate'] ) {

							$max_widths['rate'] = $width_rate;

						}



						$width_amount = strlen( $pay_stub_entry->getAmount() );

						if ( $width_amount > $max_widths['amount'] ) {

							$max_widths['amount'] = $width_amount;

						}



						$width_ytd_amount = strlen( $pay_stub_entry->getYTDAmount() );

						if ( $width_amount > $max_widths['ytd_amount'] ) {

							$max_widths['ytd_amount'] = $width_ytd_amount;

						}



						unset($width_rate, $width_units, $width_amount, $width_ytd_amount);

					}



					$prev_type = $pay_stub_entry_name_obj->getType();

				}
                                


				//There should always be pay stub entries for a pay stub.

				if ( !isset( $pay_stub_entries) ) {

					continue;

				}

				//Debug::Arr($pay_stub_entries, 'Pay Stub Entries...', __FILE__, __LINE__, __METHOD__,10);

				//Debug::Arr($max_widths, 'Maximum Widths: ', __FILE__, __LINE__, __METHOD__,10);



				$block_adjust_y = 30;
                                
                                
                                
                                $total_organization_pay_summary['number_of_employees'] = $i + 1 ;



				//

				//Earnings

				//

				if ( isset($pay_stub_entries[10]) ) {

					//$column_widths['ytd_amount'] = ( $max_widths['ytd_amount']*2 < 25 ) ? 25 : $max_widths['ytd_amount']*2;

					$column_widths['amount'] = ( $max_widths['amount']*2 < 20 ) ? 20 : $max_widths['amount']*2;

					//$column_widths['rate'] = ( $max_widths['rate']*2 < 5 ) ? 5 : $max_widths['rate']*2;

					//$column_widths['units'] = ( $max_widths['units']*2 < 17 ) ? 17 : $max_widths['units']*2;

					$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']+$column_widths['rate']+$column_widths['units']);

					//Debug::Arr($column_widths, 'Column Widths: ', __FILE__, __LINE__, __METHOD__,10);


					//Earnings Header

					//@ARSP-->$pdf->SetFont('','B',10);

					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( $column_widths['name'], 20 ,TTi18n::gettext('Earnings'), $border, 0, 'L');


					///$pdf->Cell( $column_widths['rate'], 5,TTi18n::gettext('Rate'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['units'], 5,TTi18n::gettext('Hrs/Units'), $border, 0, 'R');

					//@ARSP-->$pdf->Cell( $column_widths['amount'], 20 ,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 15;



					//@ARSP-->$pdf->SetFont('','',10);   
                                        
                                        //ARSP TEST EARNING ARRAY MAP
                                        //echo "ARSP ------></p>";
                                        //print_r($pay_stub_entries);
                                        //echo "----------------<p/>";
                                        //print_r($pay_stub_entries[10][0]['id1']);
                                        //exit();                                        
                                                
                                                
                                        //foreach( $pay_stub_entries[10] as $pay_stub_entry ) {
                                        //     print_r($pay_stub_entry);
                                        //     echo "<p/>";
                                        //}
                                        //exit();
                                        
                                        

					foreach( $pay_stub_entries[10] as $pay_stub_entry ) {
                                            



						if ( $pay_stub_entry['type'] == 10 ) { //$pay_stub_entry['type'] == 10 mean ONLY EARNING

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //68
                                                        $mail_body_array['earning_title'][] = $pay_stub_entry['name']. $subscript;
                                                        
                                                
                                                        
                                                        /*
                                                         * @childfund
                                                         * ARSP EDIT --> ALL EARNING TITLE AND VALUES @childfund
                                                         * 
                                                         */
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL EANING TITLE. INITIAL VALUE I PUT 0
                                                         * IF TITLE IS NOT THERE IN THAT ARRAY THEN I ADDED TO THE MULTIDIAMENTION ARRAY
                                                         */
                                                        if($total_organization_pay_summary['earning'][$pay_stub_entry['name'].$subscript] == NULL || $total_organization_pay_summary['earning'][$pay_stub_entry['name'].$subscript] == '')
                                                        {
                                                            $total_organization_pay_summary['earning'][$pay_stub_entry['name'].$subscript] = 0;
                                                        }
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL EARNING VALUES
                                                         * IT CHECCK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                        foreach ($total_organization_pay_summary['earning'] as $key => $value) 
                                                        {
                                                            if($key == $pay_stub_entry['name'].$subscript)
                                                            {
                                                                $total_organization_pay_summary['earning'][$pay_stub_entry['name'].$subscript] = $value + $pay_stub_entry['amount'];
                                                            }                                                    
                                                        }
                                                                
                                                                

							//$pdf->Cell( $column_widths['rate'], 5, TTi18n::formatNumber( $pay_stub_entry['rate'], TRUE ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['earning_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['earning_title_and_amount'][$pay_stub_entry['name']. $subscript] = $pay_stub_entry['amount'];
                                                        

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');


						} else {

							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount'])-$column_widths['units'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY( (175-(1+$column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //90

							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141

							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( $column_widths['name'], 5, $pay_stub_entry['name'], $border, 0, 'L');
                                                        $mail_body_array['earning_total_title'][] = $pay_stub_entry['name']. $subscript;
                                                        
                                                        
                                                        
                                                        
                                                        
                                                        /*
                                                         * @childfund
                                                         * ARSP EDIT --> ALL EARNING TOTAL TITLE AND VALUES @childfund
                                                         * 
                                                         */
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL EANING TITLE. INITIAL VALUE I PUT 0
                                                         * IF TITLE IS NOT THERE IN THAT ARRAY THEN I ADDED TO THE MULTIDIAMENTION ARRAY
                                                         */
                                                        if($total_organization_pay_summary['earning_total'][$pay_stub_entry['name'].$subscript] == NULL || $total_organization_pay_summary['earning_total'][$pay_stub_entry['name']. $subscript] == '')
                                                        {
                                                            $total_organization_pay_summary['earning_total'][$pay_stub_entry['name'].$subscript] = 0;
                                                        }
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL EARNING VALUES
                                                         * IT CHECCK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                        foreach ($total_organization_pay_summary['earning_total'] as $key => $value) 
                                                        {
                                                            if($key == $pay_stub_entry['name'].$subscript)
                                                            {
                                                                $total_organization_pay_summary['earning_total'][$pay_stub_entry['name'].$subscript] = $value + $pay_stub_entry['amount'];
                                                            }                                                    
                                                        }
                                                        
                                                        
                                                        
                                                        

							//$pdf->Cell( $column_widths['rate'], 5, '', $border, 0, 'R');

							//$pdf->Cell( $column_widths['units'], 5, TTi18n::formatNumber( $pay_stub_entry['units'], TRUE ), $border, 0, 'R');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['earning_total_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['earning_total_title_and_amount'][$pay_stub_entry['name']] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');
                                                    

						}

                                                       


						$block_adjust_y = $block_adjust_y + 5;

					}
                                            //print_r( $mail_body_array);
                                            //exit(); 

				}



				//

				// Deductions

				//

				if ( isset($pay_stub_entries[20]) ) {

					$max_deductions = count($pay_stub_entries[20]);

					$two_column_threshold = 4;



					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					if ( $max_deductions > $two_column_threshold ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

						//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Deductions'), $border, 0, 'L');

					}



					//$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;

					foreach( $pay_stub_entries[20] as $pay_stub_entry ) {

						//Start with the right side.

						//if ( $x < floor($max_deductions / 2) ) {

						if ( $x < floor($max_deductions) ) {//-----@widanage change 17.04.2013---

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 20 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > $two_column_threshold ) {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, Misc::TruncateString( $pay_stub_entry['name'], $column_widths['name']/1.7, 0, TRUE ) . $subscript, $border, 0, 'L');
                                                                $mail_body_array['deduction_title'][] = $pay_stub_entry['name'].$subscript;
                                                                
                                                                
                                                                
                                                                
                                                                
                                                                /*
                                                                 * @childfund
                                                                 * ARSP EDIT --> ALL DEDUCTION TOTAL TITLE AND VALUES @childfund
                                                                 * 
                                                                 */

                                                                /*
                                                                 * ARSP EDIT --> I ADD THIS CODE
                                                                 * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                                 * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                                 */
                                                                if($total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] == NULL || $total_organization_pay_summary['deduction'][$pay_stub_entry['name']. $subscript] == '')
                                                                {
                                                                    $total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] = 0;
                                                                }
                                                
                                                                
                                                                
                                                                
                                                                
                                                                

							} else {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');
                                                                $mail_body_array['deduction_title'][] = $pay_stub_entry['name'].$subscript;
                                                                
                                                                
                                                                
                                                                
                                                                /*
                                                                 * @childfund
                                                                 * ARSP EDIT --> ALL DEDUCTION TOTAL TITLE AND VALUES @childfund
                                                                 * 
                                                                 */

                                                                /*
                                                                 * ARSP EDIT --> I ADD THIS CODE
                                                                 * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                                 * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                                 */
                                                                if($total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] == NULL || $total_organization_pay_summary['deduction'][$pay_stub_entry['name']. $subscript] == '')
                                                                {
                                                                    $total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] = 0;
                                                                }
                                                                
                                                                

							}

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        
                                                        
                                                        
                                                 
                                                        /*
                                                         * @childfund
                                                         * ARSP EDIT --> ALL DEDUCTION TOTAL TITLE AND VALUES @childfund
                                                         * 
                                                         */
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                         * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                         */
                                                        if($total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] == NULL || $total_organization_pay_summary['deduction'][$pay_stub_entry['name']. $subscript] == '')
                                                        {
                                                            $total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] = 0;
                                                        }
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION VALUES
                                                         * IT CHECCK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                        foreach ($total_organization_pay_summary['deduction'] as $key => $value) 
                                                        {
                                                            if($key == $pay_stub_entry['name'].$subscript)
                                                            {
                                                                $total_organization_pay_summary['deduction'][$pay_stub_entry['name'].$subscript] = $value + $pay_stub_entry['amount'];
                                                            }                                                    
                                                        }                                                         
                                                        
                                                        
                                                        
                                                        
                                                        
                                                        
                                                        $mail_body_array['deduction_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['deduction_title_and_amount'][$pay_stub_entry['name'].$subscript] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

							Debug::Text('Y Adjustments: '. $adjust_y .' Block: '. $block_adjust_y, __FILE__, __LINE__, __METHOD__,10);

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L'); //110
                                                        $mail_body_array['deduction_total_title'][] = $pay_stub_entry['name'];
                                                        
                                                        
                                                        
                                                         /*
                                                         * @childfund
                                                         * ARSP EDIT --> DEDUCTION TOTAL TITLE AND VALUES @childfund
                                                         * 
                                                         */
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                         * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                         */
                                                        if($total_organization_pay_summary['deduction_total'][$pay_stub_entry['name']] == NULL || $total_organization_pay_summary['deduction_total'][$pay_stub_entry['name']] == '')
                                                        {
                                                            $total_organization_pay_summary['deduction_total'][$pay_stub_entry['name'].$subscript] = 0;
                                                        }
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION VALUES
                                                         * IT CHECCK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                        foreach ($total_organization_pay_summary['deduction_total'] as $key => $value) 
                                                        {
                                                            if($key == $pay_stub_entry['name'])
                                                            {
                                                                $total_organization_pay_summary['deduction_total'][$pay_stub_entry['name']] = $value + $pay_stub_entry['amount'];
                                                            }                                                    
                                                        }                                                               
                                                        
                                                        
                                                        
                                                        
                                                        

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');
                                                        $mail_body_array['deduction_total_amount'][] = $pay_stub_entry['amount'];
                                                        $mail_body_array['deduction_total_title_and_amount'][$pay_stub_entry['name']] = $pay_stub_entry['amount'];

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}


					//Draw line to separate the two columns

					if ( $max_deductions > $two_column_threshold ) {

						//@ARSP-->$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}
                                




				if ( isset($pay_stub_entries[40][0]) ) {

					$block_adjust_y = $block_adjust_y + 5;



					//Net Pay entry

					//@ARSP-->$pdf->SetFont('','B',10);



					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5, $pay_stub_entries[40][0]['name'], $border, 0, 'L');
                                        $mail_body_array['net_pay_title'][] = $pay_stub_entries[40][0]['name'];
                                        
                                        
                                        
                                                         /*
                                                         * @childfund
                                                         * ARSP EDIT --> DEDUCTION TOTAL TITLE AND VALUES @childfund
                                                         * 
                                                         */
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                         * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                         */
                                                        if($total_organization_pay_summary['net_pay'][$pay_stub_entries[40][0]['name']] == NULL || $total_organization_pay_summary['net_pay'][$pay_stub_entries[40][0]['name']] == '')
                                                        {
                                                            $total_organization_pay_summary['net_pay'][$pay_stub_entries[40][0]['name']] = 0;
                                                        }
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION VALUES
                                                         * IT CHECCK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                        foreach ($total_organization_pay_summary['net_pay'] as $key => $value) 
                                                        {
                                                            if($key == $pay_stub_entries[40][0]['name'])
                                                            {
                                                                $total_organization_pay_summary['net_pay'][$pay_stub_entries[40][0]['name']] = $value + $pay_stub_entries[40][0]['amount'];
                                                            }                                                    
                                                        } 
                                                        
                                                        

					//@ARSP-->$pdf->Cell( $column_widths['amount'],5, TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'] ), $border, 0, 'R');
                                        $mail_body_array['net_pay_amount'][] = $pay_stub_entries[40][0]['amount'];
                                        $mail_body_array['net_pay_title_and_amount'][$pay_stub_entries[40][0]['name']] = $pay_stub_entries[40][0]['amount'];

					//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entries[40][0]['ytd_amount'] ), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

				}
                                
//                                        print_r($mail_body_array);
//                                        exit();




				//

				//Employer Contributions

				//


				if ( isset($pay_stub_entries[30]) AND $hide_employer_rows != TRUE ) {

					$max_deductions = count($pay_stub_entries[30]);

					//Deductions Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					if ( $max_deductions > 2 ) {

						$column_widths['name'] = 85-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

						//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

						//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					} else {

						$column_widths['name'] = 175-($column_widths['ytd_amount']+$column_widths['amount']);



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell( $column_widths['name'], 5,TTi18n::gettext('Employer Contributions'), $border, 0, 'L');

					}



					//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('YTD Amount'), $border, 0, 'R');



					$block_adjust_y = $tmp_block_adjust_y = $top_block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					$x=0;

					$max_block_adjust_y = 0;



					foreach( $pay_stub_entries[30] as $pay_stub_entry ) {

						//Start with the right side.

						if ( $x < floor($max_deductions / 2) ) {

							$tmp_adjust_x = 90;

						} else {

							if ( $tmp_block_adjust_y != 0 ) {

								$block_adjust_y = $tmp_block_adjust_y;

								$tmp_block_adjust_y = 0;

							}

							$tmp_adjust_x = 0;

						}



						if ( $pay_stub_entry['type'] == 30 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							if ( $max_deductions > 2 ) {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $tmp_adjust_x+$adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //38
                                                            
                                                            
                                                                /*
                                                                * @childfund
                                                                * ARSP EDIT --> EMPLOYER CONTRIBUTION TITLE @childfund
                                                                * 
                                                                */

                                                               /*
                                                                * ARSP EDIT --> I ADD THIS CODE
                                                                * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                                * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                                */
                                                               if($total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name']] == NULL || $total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name']] == '')
                                                               {
                                                                   $total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name'].$subscript] = 0;
                                                               }
                                                               
                                                               

							} else {

								//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

								//@ARSP-->$pdf->Cell( $column_widths['name']-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L'); //128
                                                            
                                                            
                                                            
                                                                /*
                                                                * @childfund
                                                                * ARSP EDIT --> EMPLOYER CONTRIBUTION TITLE @childfund
                                                                * 
                                                                */

                                                               /*
                                                                * ARSP EDIT --> I ADD THIS CODE
                                                                * THIS CODE CAN STORE ALL DEDUCTION TITLE. INITIAL VALUE I PUT 0
                                                                * IF TITLE IS NOT THERE IN TO THAT ARRAY THEN I ADDED IN TO THE MULTIDIAMENTION ARRAY
                                                                */
                                                               if($total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name']] == NULL || $total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name']] == '')
                                                               {
                                                                   $total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name'].$subscript] = 0;
                                                               }
                                                               

							}

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount']), $border, 0, 'R');
                                                        
                                                        
                                                        
                                                        /*
                                                         * ARSP EDIT --> I ADD THIS CODE
                                                         * THIS CODE CAN STORE ALL DEDUCTION VALUES
                                                         * IT CHECK KEY VALUES OF THE ARRAY THEN UPDATE
                                                         */
                                                         foreach ($total_organization_pay_summary['employer_deduction_title'] as $key => $value) 
                                                         {
                                                             if($key == $pay_stub_entry['name'])
                                                             {
                                                                 $total_organization_pay_summary['employer_deduction_title'][$pay_stub_entry['name']] = $value + $pay_stub_entry['amount'];
                                                             }                                                    
                                                         } 
                                                         
                                                        
                                                        

						} else {

							$block_adjust_y = $max_block_adjust_y + 0;



							//Total

							//@ARSP-->$pdf->SetFont('','B',10);



							//@ARSP-->$pdf->line(Misc::AdjustXY( (175-($column_widths['ytd_amount'])-$column_widths['amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175-(1+$column_widths['ytd_amount']), $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //111

							//@ARSP-->$pdf->line(Misc::AdjustXY( 175-$column_widths['ytd_amount'], $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y), Misc::AdjustXY(175, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) ); //141



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']),5, $pay_stub_entry['name'], $border, 0, 'L');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'], 5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

						if ( $block_adjust_y > $max_block_adjust_y ) {

							$max_block_adjust_y = $block_adjust_y;

						}



						$x++;

					}



					//Draw line to separate the two columns

					if ( $max_deductions > 2 ) {

						//@ARSP-->$pdf->Line( Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $top_block_adjust_y-5, $adjust_y), Misc::AdjustXY(88, $adjust_x), Misc::AdjustXY( $max_block_adjust_y-5, $adjust_y) );

					}



					unset($x, $max_deductions, $tmp_adjust_x, $max_block_adjust_y, $tmp_block_adjust_y, $top_block_adjust_y);

				}



				//

				//Accruals PS accounts

				//

				if ( isset($pay_stub_entries[50]) ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);

					//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount']), 5,TTi18n::gettext('Accruals'), $border, 0, 'L');

					//@ARSP-->$pdf->Cell( $column_widths['amount'], 5,TTi18n::gettext('Amount'), $border, 0, 'R');

					//$pdf->Cell( $column_widths['ytd_amount'], 5,TTi18n::gettext('Balance'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','',10);

					foreach( $pay_stub_entries[50] as $pay_stub_entry ) {



						if ( $pay_stub_entry['type'] == 50 ) {

							if ( $pay_stub_entry['description_subscript'] != '' ) {

								$subscript = '['.$pay_stub_entry['description_subscript'].']';

							} else {

								$subscript = NULL;

							}



							//@ARSP-->$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

							//@ARSP-->$pdf->Cell( 175-($column_widths['amount']+$column_widths['ytd_amount'])-2, 5, $pay_stub_entry['name'] . $subscript, $border, 0, 'L');

							//@ARSP-->$pdf->Cell( $column_widths['amount'], 5, TTi18n::formatNumber( $pay_stub_entry['amount'] ), $border, 0, 'R');

							//$pdf->Cell( $column_widths['ytd_amount'],5, TTi18n::formatNumber( $pay_stub_entry['ytd_amount'] ), $border, 0, 'R');

						}



						$block_adjust_y = $block_adjust_y + 5;

					}

				}



				//

				//Accrual Policy Balances

				//

				$ablf = TTnew( 'AccrualBalanceListFactory' );

				$ablf->getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_obj->getId(), $user_obj->getCompany(), TRUE );

				if ( $ablf->getRecordCount() > 0 ) {

					//Accrual Header

					$block_adjust_y = $block_adjust_y + 5;



					//@ARSP-->$pdf->SetFont('','B',10);



					//@ARSP-->$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );



					$accrual_time_header_start_x = $pdf->getX();

					$accrual_time_header_start_y = $pdf->getY();



					//@ARSP-->$pdf->Cell(70,5,TTi18n::gettext('Accrual Time Balances as of ').TTDate::getDate('DATE', time() ) , $border, 0, 'L');

					//@ARSP-->$pdf->Cell(25,5,TTi18n::gettext('Balance (hrs)'), $border, 0, 'R');



					$block_adjust_y = $block_adjust_y + 5;

					$box_height = 5;



					//@ARSP-->$pdf->SetFont('','',10);

					foreach( $ablf as $ab_obj ) {

						$balance = $ab_obj->getBalance();

						if ( !is_numeric( $balance ) ) {

							$balance = 0;

						}



						//@ARSP-->$pdf->setXY( Misc::AdjustXY(40, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						//@ARSP-->$pdf->Cell(70,5, $ab_obj->getColumn('name'), $border, 0, 'L');

						//@ARSP-->$pdf->Cell(25,5, TTi18n::formatNumber( TTDate::getHours( $balance ) ), $border, 0, 'R');



						$block_adjust_y = $block_adjust_y + 5;

						$box_height = $box_height + 5;

						unset($balance);

					}

					//@ARSP-->$pdf->Rect( $accrual_time_header_start_x, $accrual_time_header_start_y, 95, $box_height );



					unset($accrual_time_header_start_x, $accrual_time_header_start_y, $box_height);

				}





				//

				//Descriptions

				//

				/*if ( isset($pay_stub_entry_descriptions) AND count($pay_stub_entry_descriptions) > 0 ) {



					//Description Header

					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','B',10);

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y) );

					$pdf->Cell(175,5,TTi18n::gettext('Notes'), $border, 0, 'L');



					$block_adjust_y = $block_adjust_y + 5;



					$pdf->SetFont('','',8);

					$x=0;

					foreach( $pay_stub_entry_descriptions as $pay_stub_entry_description ) {

						if ( $x % 2 == 0 ) {

							$pdf->setXY( Misc::AdjustXY(2, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						} else {

							$pdf->setXY( Misc::AdjustXY(90, $adjust_x), Misc::AdjustXY( $block_adjust_y, $adjust_y) );

						}



						//$pdf->Cell(173,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');

						$pdf->Cell(85,5, '['.$pay_stub_entry_description['subscript'].'] '.$pay_stub_entry_description['description'], $border, 0, 'L');



						if ( $x % 2 != 0 ) {

							$block_adjust_y = $block_adjust_y + 5;

						}

						$x++;

					}

				}*/

				unset($x, $pay_stub_entry_descriptions, $pay_stub_entry_description);



				//

				// Pay Stub Footer

				//



				$block_adjust_y = 90;

				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y, $adjust_y) );



				//Non Negotiable

				//$pdf->SetFont('','B',14);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+3, $adjust_y) );

				//$pdf->Cell(175, 5, TTi18n::gettext('NON NEGOTIABLE'), $border, 0, 'C', 0);



				//Employee Address

				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+9, $adjust_y) );

				//$pdf->Cell(60, 5, TTi18n::gettext('CONFIDENTIAL'), $border, 0, 'C', 0);

				//$pdf->SetFont('','',10);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+14, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getFullName() .' (#'.$user_obj->getEmployeeNumber().')', $border, 0, 'C', 0);

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+19, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getAddress1(), $border, 0, 'C', 0);

				//$address2_adjust_y = 0;

				/*if ( $user_obj->getAddress2() != '' ) {

					$address2_adjust_y = 5;

					$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24, $adjust_y) );

					$pdf->Cell(60, 5, $user_obj->getAddress2(), $border, 0, 'C', 0);

				}*/

				//$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+24+$address2_adjust_y, $adjust_y) );

				//$pdf->Cell(60, 5, $user_obj->getCity() .', '. $user_obj->getProvince() .' '. $user_obj->getPostalCode(), $border, 1, 'C', 0);



				//Pay Period - Balance - ID

				$net_pay_amount = 0;

				if ( isset($pay_stub_entries[40][0]) ) {

					$net_pay_amount = TTi18n::formatNumber( $pay_stub_entries[40][0]['amount'], TRUE );

				}



				if ( isset($pay_stub_entries[65]) AND count($pay_stub_entries[65]) > 0 ) {

					$net_pay_label = TTi18n::gettext('Balance');

				} else {

					$net_pay_label = TTi18n::gettext('Net Pay');

				}



				//$pdf->SetFont('','B',12);

				//$pdf->setXY( Misc::AdjustXY(75, $adjust_x), Misc::AdjustXY($block_adjust_y+17, $adjust_y) );

				//$pdf->Cell(100, 5, $net_pay_label.': '. $pay_stub_obj->getCurrencyObject()->getSymbol() . $net_pay_amount . ' ' . $pay_stub_obj->getCurrencyObject()->getISOCode(), $border, 1, 'R', 0);



				if ( $pay_stub_obj->getTainted() == TRUE ) {

					$tainted_flag = 'T';

				} else {

					$tainted_flag = '';

				}

				//$pdf->SetFont('','',8);

				//$pdf->setXY( Misc::AdjustXY(125, $adjust_x), Misc::AdjustXY($block_adjust_y+30, $adjust_y) );

				//$pdf->Cell(50, 5, TTi18n::gettext('Identification #:').' '. str_pad($pay_stub_obj->getId(),12,0, STR_PAD_LEFT).$tainted_flag, $border, 1, 'L', 0);
                    
                                
                                
                                
           
                                //ARSP EDIT --> ADD NEW CODE FOR TOTAL ORGANIZATION PAY SUMMARY
                                //
                                // ADD HERE
                                //
                                
                                
                                
                                
                                
				unset($net_pay_amount, $tainted_flag);



				//Line

				//@ARSP-->$pdf->setLineWidth( 1 );

				//@ARSP-->$pdf->Line( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+35, $adjust_y), Misc::AdjustXY(185, $adjust_y), Misc::AdjustXY($block_adjust_y+35, $adjust_y) );



				//@ARSP-->$pdf->SetFont('','', 6);

				//@ARSP-->$pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY($block_adjust_y+38, $adjust_y) );

				//@ARSP-->$pdf->Cell(175, 1, TTi18n::gettext('Pay Stub Generated by').' '. APPLICATION_NAME , $border, 0, 'C', 0);

                                
                                


                                
				unset($pay_stub_entries, $pay_period_number);



				$this->getProgressBarObject()->set( NULL, $pslf->getCurrentRow() );

                                
//                                if($mail_body_array['employee_work_email'] == NULL || $mail_body_array['employee_work_email'] == "" )
//                                {
//                                    $empty_employee_email['mail'][] = $mail_body_array['employee_full_name'];
//                                }
//                                else
//                                {
//                                    $mail = $this->sendMailToEmployee($mail_body_array);//ARSP ADD EMAIL FUNCTION
//                                    if($mail)
//                                    {
//                                        
//                                        echo "Mail Send Success ful";
//                                    }
//                                    else
//                                    {
//                                        $sending_failed_email[] = $mail_body_array['employee_work_email'];
//                                        echo "Error !!! Mail Sending Fail";
//                                    }                                    
//                                    
//                                }


				$i++;
                                
                                

			}
                        
                        //ARSPO ADDD --> @childfund
                        //echo "Employee Total Details --  : <br/>";
                        //print_r($total_organization_pay_summary);
                        $output = $this->GenerateTotalOrganizationPaySummaryPdf($total_organization_pay_summary, $current_company, $payperiod_string, $total_employees);
                        
                        if ( isset($output) )
                        {
                            return $output;
                        }
                        
                        return FALSE;

		}
	}
        
        
        
	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE Bank Transfer Details For CHILD FUND
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */	   

        function GenerateTotalOrganizationPaySummaryPdf($total_organization_pay_summary, $current_company, $payperiod_string, $total_employees)
        {
//            print_r($total_organization_pay_summary);
//            exit();            

                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(),
                                                  //'start_date'   => $transactionstart_date,    
                                                  //'end_date'     => $transaction_end_date,
                                                  'payperiod_string'     => $payperiod_string                    
                                                );
                

                
                $pdf = TTnew( 'PayStubTotalOrganizationPaySummary' );	
                
									
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                //$pdf->SetMargins(PDF_MARGIN_LEFT, 44, PDF_MARGIN_RIGHT);
                $pdf->SetMargins(10, 49, 2);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');
                
                //Table border
                //$pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 40;		
                
                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(43, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $pdf->SetFont('times','',10);                
                
                //START -->ADD TABLE CODE HERE
                        

                // create some HTML content
                
                $html = '<table border="0" cellspacing="0" cellpadding="5" width = "90%" >';  
                
                $html = $html.'<tr>';
                $html = $html.'<td  bgcolor="#CBCBCB"><h2>Earning</h2></td>';  
                $html = $html.'<td  bgcolor="#CBCBCB" width="30%" align="right"><h2>Amount(Rs)</h2></td>';  
                $html=  $html.'</tr>';
                
                $i = 0;
                foreach( $total_organization_pay_summary['earning'] as $key => $value )
                {
                    if($i % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="WHITE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="#EEEEEE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }                    
    
                    $html = $html.'<td>-'.$key.'</td>';     
                    $html = $html.'<td align="right">'.number_format($value , 2).'</td>';                    
                    $html=  $html.'</tr>';
                    
                    $i++;
                    
                }
                
                foreach( $total_organization_pay_summary['earning_total'] as $key => $value )
                {
                    //$row_header[] = $column_name;
                    $html = $html.'<tr>';       
                    $html = $html.'<td><b>'.$key.'</b></td>';     
                    $html = $html.'<td align="right"><b>'.number_format($value, 2).'</b></td>';                        
                    $html=  $html.'</tr>';
                }

//------------------------Deduction-----------------------------------

                
                $html=  $html.'<tr>';
                $html = $html.'<td colspan="2"></td>';   
                $html=  $html.'</tr>';                
                
                
                $html = $html.'<tr>';
                $html = $html.'<td colspan="2" bgcolor="#CBCBCB" ><h2>Deduction</h2></td>';   
                $html=  $html.'</tr>';
                
                $j = 0;
                foreach( $total_organization_pay_summary['deduction'] as $key => $value )
                {                                        
                    if($j % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="WHITE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="#EEEEEE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }      
     
                    $html = $html.'<td>-'.$key.'</td>';     
                    $html = $html.'<td align="right">'.number_format($value ,2).'</td>';                        
                    $html=  $html.'</tr>';
                    
                    $j++;
                }
                
                foreach( $total_organization_pay_summary['deduction_total'] as $key => $value )
                {
                    //$row_header[] = $column_name;
                    $html = $html.'<tr>';       
                    $html = $html.'<td><b>'.$key.'</b></td>';     
                    $html = $html.'<td align="right"><b>'.number_format( $value, 2).'</b></td>';                        
                    $html=  $html.'</tr>';
                }

//------------------------Net PAy-----------------------------------                

                $html=  $html.'<tr>';
                $html = $html.'<td colspan="2"></td>';   
                $html=  $html.'</tr>'; 
                
                
                foreach( $total_organization_pay_summary['net_pay'] as $key => $value )
                {
                    //$row_header[] = $column_name;
                    $html = $html.'<tr>';       
                    $html = $html.'<td bgcolor="#CBCBCB"><h1>'.$key.'</h1></td>';     
                    $html = $html.'<td bgcolor="#CBCBCB" align="right"><h1>'.number_format($value, 2).'</h1></td>';                        
                    $html=  $html.'</tr>';
                }

//-----------------------------------Employer Contributions (EPF,ETF), No of Emplolyees----------------------
                
                
                $html=  $html.'<tr>';
                $html = $html.'<td colspan="2"></td>';   
                $html=  $html.'</tr>';                 
                
                //Employer Contributions
                foreach ($total_organization_pay_summary['employer_deduction_title'] as $key => $value)
                {
                    //Employer ContributionsEPF & ETF
                    $html = $html.'<tr>';       
                    $html = $html.'<td bgcolor="#E2E2E2">'.$key.'</td>';     
                    $html = $html.'<td bgcolor="#E2E2E2" align="right">'.number_format( $value , 2).'</td>';                        
                    $html=  $html.'</tr>';                     
                    
                } 
                
                //No of Emoployees
                $html = $html.'<tr>';       
                $html = $html.'<td bgcolor="#E2E2E2">No of Employees</td>';     
                $html = $html.'<td bgcolor="#E2E2E2" align="right">'.$total_employees.'</td>';                        
                $html=  $html.'</tr>';    
                
                
//                $html=  $html.'<tr>';
//                $html = $html.'<td colspan="2"></td>';   
//                $html=  $html.'</tr>';                 
                
                $html=  $html.'</table>';
                
                $pdf->writeHTML($html, true, false, true, false, '');
                
                //------------------------------------
                
                $pdf->SetFont('times','B',9);     
                
                $html = '<table border="0" cellspacing="0" cellpadding="0" width = "100%" >';  
                
                $html=  $html.'<tr>';
                $html = $html.'<td colspan="3"></td>';   
                $html=  $html.'</tr>';                 
                
                
                $html = $html.'<tr nobr="true">';       
                $html = $html.'<td align="center">-----------------------<br/>Prepared/checked by<br/>Human Resource <br/>Manager</td>';     
                $html = $html.'<td align="center">-----------------------<br/>Recommending Approval<br/>Finance Manager</td>';     
                $html = $html.'<td align="center">-----------------------<br/>Approval<br/>National Director</td>';
                $html=  $html.'</tr>'; 
                
        
                $html=  $html.'</table>';
                
                
                
                $pdf->writeHTML($html, true, false, true, false, '');


                        
                
                        
                //Close and output PDF document
		$output = $pdf->Output('','S');
                //exit();
		unset($_SESSION['header_data']);

                
		if ( isset($output) )
		{
                    return $output;				
		}
				
		return FALSE;             
                                                        

        }               

	

	/*
	 *ARSP EDIT -->ADD NEW CODE FOR CREATE Bank Transfer Details For CHILD FUND
	 *
	 *PAGE ORIENTATION IS PORTRAIT
	 */	   

        function BankTransfer($data, $include_user_ids , $current_user, $current_company, $payperiod_string)
        {            
        
            /* ARSP EDIT NOTE***************************************************
             * 
             * NOTE THIS :- HOW TO GET GET PAY PERIOD
             * 
             *  ARSP EDIT --> HOW TO GET PAYPERIOD FROM "$data" ARRAY 
             *  IF PRINT "$data" ARRAY IT WILL SHOW (NET PAY/ GROSS SALARY / BASIC SALARY etc..) THESE KEY VALUES ARE NUMBER  
             *  SO WE HAVE TO FIND OUT THE WHAT IS THE KEY VALUES 
             *  WE HAVE TO PRINT THIS COLUMN VALUE --> "print_r($columns)" TO THIS FUNCTION --> "Array2PdfPortrait()" [HINT :ADD START OF THE FUNCTION]
             * 
             *  INTERFACE CHANGES --> I ADDED 2 NEW TEXT FIELDS TO THE Bank Account DETAILS
             *  PATH : evolvepayroll\templates\report\BankTransferSummary.tpl 
             * 
             *  DATABASE CHANGES --> I ADDED 2 EXTRA FIELDS TO THE "bank_account" TABLE 
             *  1.bank_name , Varchar , 100
             *  2.bank_branch , Varchar , 100 
             * 
             *  NEW FUNCTION --> I ADDED 2 NEW FUNCTION TO GET & SET THE "bank_name" ,"bank_branch"  FROM  
             *  PATH : evolvepayroll\classes\modules\users\BankAccountFactory.class.php
             * 
             *  ADD SOME NEW CODE
             *  PATH --> C:\xampp\htdocs\evolvepayroll\interface\bank_account\EditBankAccount.php 
             *  1. $baf->setBankName( $bank_data['bank_name'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
             *	2. $baf->setBankBranch( $bank_data['bank_branch'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
             * 
             *  'account' => $bank_account->getSecureAccount(),//ARSP EDIT --> I Hide THIS CODE REASON- WE CANT SEE ORIGINAL ACCOUNT NUMBER
	     *	'account' => $bank_account->getAccount(),//ARSP EDIT --> I ADD NEW CODE SHOW ONLY ORIGINAL ACCOUNT NUMBER
             *  'bank_name' => $bank_account->getBankName(),//ARSP EDIT --> I ADD NEW CODE FOR BANK NAME
             *  'bank_branch' => $bank_account->getBankBranch(),//ARSP EDIT --> I ADD NEW CODE FOR BANK BRANCH NAME
             *
             *
             * 
             * *****************************************************************
             */

        

            $ignore_last_row = TRUE;
            $include_header = TRUE;
            $eol = "\n";
            


            if ( is_array($data) AND count($include_user_ids) > 0 )
            {
                
                if ( $ignore_last_row === TRUE )
                {
                    $last_row = array_pop($data);//ARSP EDIT --> THIS FUNCTION USE TO REMOVE THE LAST ELEMENT OF THEAT ARRAY
                }               

              
                $_SESSION['header_data'] = array( 
                                                  'image_path'   => $current_company->getLogoFileName(),
                                                  'company_name' => $current_company->getName(),
                                                  'address1'     => $current_company->getAddress1(),
                                                  'address2'     => $current_company->getAddress2(),
                                                  'city'         => $current_company->getCity(),
                                                  'province'     => $current_company->getProvince(),
                                                  'postal_code'  => $current_company->getPostalCode(),
                                                  //'start_date'   => $transactionstart_date,    
                                                  //'end_date'     => $transaction_end_date,
                                                  'payperiod_string'     => $payperiod_string                    
                                                );
                

                
                $pdf = TTnew( 'PayStubBankTransfer' );	
                
									
                
                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                //$pdf->SetMargins(PDF_MARGIN_LEFT, 44, PDF_MARGIN_RIGHT);
                $pdf->SetMargins(10, 49, 2);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // add a page
                $pdf->AddPage('p','mm','A4');
                
                //Table border
                $pdf->setLineWidth( 0.20 );
                
                //set table position
                $adjust_x = 10;		
                
                $pdf->setXY( Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(49, $adjust_y) );
				           
                
                //TABLE CODE HERE
                        
                //Header
                // create some HTML content
                $pdf->SetFont('times','',8);                
                
                $html = '<table border="1" cellspacing="0" cellpadding="2" width="108%">';  
                
            
                $ulf = TTnew( 'UserListFactory' );                     
                $balf = TTnew( 'BankAccountListFactory' );        
                
      
                $i=1;   
                foreach( $data as $row ) 
                {                                   
                    $user_id = $row['user_id']; 
                    $full_name =  $row['full_name'];
                    $employee_number = $row['employee_number'];
                    
                    $net_pay = $row['13']; //in this case $row['13'] is a Net Pay
                    $bank_obj = $balf->getByUserId($user_id)->getCurrent();//get bank object  
                    
                    $bank_code = $bank_obj->getTransit();
                    $bank_name = $bank_obj->getBankName();
                    $bank_branch = $bank_obj->getBankBranch();
                    $bank_account_no = $bank_obj->getAccount();                    
                                        
                    
                    if($i % 2 == 0)
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="#EEEEEE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    else
                    {
                        $html=  $html.'<tr style ="text-align:" bgcolor="WHITE" nobr="true">';//nobr="true" use to No breake the every page end of table row
                    }
                    
                    
                    $html =  $html.'
                                    <td width="4%" align="center">'.$i.'</td>
                                    <td width="8%"  align="left">'.$bank_code.'</td>
                                    <td width="18%" align="left">'.$bank_name.'</td>
                                    <td             align="left">'.$bank_branch.'</td>
                                    <td width="13%" align="left">'.$bank_account_no.'</td>
                                    <td width="6%" align="center">'.$employee_number.'</td>
                                    <td width="20%" align="left">'.$full_name.'</td>
                                    <td width="10%" align="right">'.number_format($net_pay, 2).'</td>     
                             </tr>';  
                   
                    
                    $i++;
                     
                }
                
                $html=  $html.'</table>';  				
				
         
                // output the HTML content
                $pdf->writeHTML($html, true, false, true, false, '');
                        
                
                        
                //Close and output PDF document
		$output = $pdf->Output('','S');
		unset($_SESSION['header_data']);

                
		if ( isset($output) )
		{
                    return $output;				
		}
				
		return FALSE;             
            }

        }



		
	function addLog( $log_action ) {

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Stub'), NULL, $this->getTable(), $this );

	}		
	

}

?>

