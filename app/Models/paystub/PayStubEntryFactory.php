<?php

namespace App\Models\PayStub;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;

class PayStubEntryFactory extends Factory {
	protected $table = 'pay_stub_entry';
	protected $pk_sequence_name = 'pay_stub_entry_id_seq'; //PK Sequence name

	protected $pay_stub_entry_account_obj = NULL;
	protected $pay_stub_obj = NULL;
	function getPayStubEntryAccountObject() {
		if ( is_object($this->pay_stub_entry_account_obj) ) {
			return $this->pay_stub_entry_account_obj;
		} else {
			$psealf = new PayStubEntryAccountListFactory();
			$psealf->getByID( $this->getPayStubEntryNameID() );
			if ( $psealf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_obj = $psealf->getCurrent();
				return $this->pay_stub_entry_account_obj;
			}

			return FALSE;
		}
	}

	function getPayStubObject() {
		if ( is_object($this->pay_stub_obj) ) {
			return $this->pay_stub_obj;
		} else {
			$pslf = new PayStubListFactory();
			$pslf->getByID( $this->getPayStub() );
			if ( $pslf->getRecordCount() > 0 ) {
				$this->pay_stub_obj = $pslf->getCurrent();
				return $this->pay_stub_obj;
			}

			return FALSE;
		}
	}

	function getPayStub() {
		if ( isset($this->data['pay_stub_id']) ) {
			return $this->data['pay_stub_id'];
		}

		return FALSE;
	}
	function setPayStub($id) {
		$id = trim($id);

		$pslf = new PayStubListFactory();

		if ( $this->Validator->isResultSetWithRows(	'pay_stub',
													$pslf->getByID($id),
													('Invalid Pay Stub')
													) ) {
			$this->data['pay_stub_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubEntryNameId() {
		if ( isset($this->data['pay_stub_entry_name_id']) ) {
			return $this->data['pay_stub_entry_name_id'];
		}

		return FALSE;
	}
	function setPayStubEntryNameId($id) {
		$id = trim($id);

		Debug::text('Entry Account ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

		//$psenlf = new PayStubEntryNameListFactory();
		$psealf = new PayStubEntryAccountListFactory();
		$result = $psealf->getById($id);

		if (  $this->Validator->isResultSetWithRows(	'pay_stub_entry_name_id',
														$result,
														('Invalid Entry Account Id')
														) ) {
			Debug::text('TRUE: '. $id .' matches result: '. $result->getCurrent()->getId() , __FILE__, __LINE__, __METHOD__,10);
			$this->data['pay_stub_entry_name_id'] = $result->getCurrent()->getId();

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubAmendment() {
		if ( isset($this->data['pay_stub_amendment_id']) ) {
			return $this->data['pay_stub_amendment_id'];
		}

		return FALSE;
	}
	function setPayStubAmendment($id) {
		$id = trim($id);

		Debug::text('PS Amendment ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

		$psalf = new PayStubAmendmentListFactory();
		$result = $psalf->getById($id);

		if (  $this->Validator->isResultSetWithRows(	'pay_stub_amendment_id',
														$result,
														('Invalid Pay Stub Amendment Id')
														) ) {
			//Debug::text('TRUE: '. $id .' -: '. $result->getCurrent()->getId() , __FILE__, __LINE__, __METHOD__,10);
			$this->data['pay_stub_amendment_id'] = $result->getCurrent()->getId();

			return TRUE;
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

		if ($value == NULL OR $value == '') {
			return FALSE;
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
				)
			) {
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

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		Debug::text('Rate: '. $value, __FILE__, __LINE__, __METHOD__,10);

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
				)
			) {
			//Must round to 2 decimals otherwise discreptancy can occur when generating pay stubs.
			//$this->data['units'] = Misc::MoneyFormat( $value, FALSE );
			$this->data['units'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function getYTDUnits() {
		if ( isset($this->data['ytd_units']) ) {
			return $this->data['ytd_units'];
		}

		return NULL;
	}
	function setYTDUnits($value) {
		$value = trim($value);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		Debug::text('YTD Units: '. $value .' Name: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

		if (  $this->Validator->isFloat(				'ytd_units',
														$value,
														('Invalid YTD Units')
														) ) {
			$this->data['ytd_units'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableCalculateYTD() {
		if ( isset($this->enable_calc_ytd) ) {
			return $this->enable_calc_ytd;
		}

		return FALSE;
	}
	function setEnableCalculateYTD($bool) {
		$this->enable_calc_ytd = $bool;

		return TRUE;
	}

	function getEnableCalculateAccrualBalance() {
		if ( isset($this->enable_calc_accrual_balance) ) {
			return $this->enable_calc_accrual_balance;
		}

		return FALSE;
	}
	function setEnableCalculateAccrualBalance($bool) {
		$this->enable_calc_accrual_balance = $bool;

		return TRUE;
	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return NULL;
	}
	function setAmount($value) {
		$value = trim($value);

		//PHP v5.3.5 has a bug that it converts large values with 0's on the end into scientific notation.
		Debug::text('Amount: '. $value .' Name: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

		//if ($value == NULL OR $value == '' OR $value < 0) {
		//Allow negative values for things like minusing vacation accural?
		if ($value == NULL OR $value == '' ) {
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
			) {
			$this->data['amount'] = Misc::MoneyFormat( $value, FALSE );

			return TRUE;
		}

		return FALSE;
	}
	function getYTDAmount() {
		if ( isset($this->data['ytd_amount']) ) {
			return $this->data['ytd_amount'];
		}

		return NULL;
	}
	function setYTDAmount($value) {
		$value = trim($value);

		if ($value == NULL OR $value == '') {
			return FALSE;
		}

		Debug::text('YTD Amount: '. $value .' Name: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

		if (  $this->Validator->isFloat(				'ytd_amount',
														$value,
														('Invalid YTD Amount')
														) ) {
			$this->data['ytd_amount'] = round( $value,2);

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
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

	function preSave() {
		Debug::text('Pay Stub ID: '. $this->getPayStub() .' Calc YTD: '. (int)$this->getEnableCalculateYTD(), __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getYTDAmount() == FALSE ) {
			$this->setYTDAmount( 0 );
		}

		if ( $this->getYTDUnits() == FALSE ) {
			$this->setYTDUnits( 0 );
		}

		/*
		if ( 	$this->getPayStub()
				AND
				(
					( $this->getYTDAmount() == FALSE AND $this->getYTDUnits() == FALSE )
					OR
					$this->getEnableCalculateYTD() == TRUE
				) ) {
			Debug::text('Calculating YTD values...' , __FILE__, __LINE__, __METHOD__,10);

			//Calculate things like YTD values
			$pslf = new PayStubListFactory();
			$ps = $pslf->getById( $this->getPayStub() )->getCurrent();

			$pself = new PayStubEntryListFactory();

			//if ( $this->getPayStubEntryNameId() == 24 ) { //Vacation accural
			//Debug::text('aaCalculating YTD values...:  for Vacation Accrual....', __FILE__, __LINE__, __METHOD__,10);
			if ( $this->getPayStubEntryAccountObject() != FALSE
					AND $this->getPayStubEntryAccountObject()->getType() == 50 ) {
				//Accurals don't re-start after year boundary.
				Debug::text('aaCalculating Balance (NOT YTD) values for Accrual....', __FILE__, __LINE__, __METHOD__,10);
				$ytd_values = $pself->getAmountSumByUserIdAndEntryNameIdAndDate( $ps->getUser(), $this->getPayStubEntryNameId(), $ps->getPayPeriodObject()->getEndDate(), $this->getId() );
				//BUG: When re-calculating old pay stubs the balances don't
				//take in to account other entries of the same PSE account on the same pay stub.
				// 5.00   5.00
				//-5.00  -5.00
				//$ytd_values = $pself->getAmountSumByUserIdAndEntryNameIdAndDate( $ps->getUser(), $this->getPayStubEntryNameId(), $ps->getPayPeriodObject()->getEndDate(), 0 );
			} else {
				//$ytd_values = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndYear( $ps->getUser(), $this->getPayStubEntryNameId(), $ps->getPayPeriodStartDate() );
				$ytd_values = $pself->getYTDAmountSumByUserIdAndEntryNameIdAndDate( $ps->getUser(), $this->getPayStubEntryNameId(), $ps->getPayPeriodObject()->getTransactionDate(), $this->getId() );
			}

			Debug::text('aCalculating YTD values...: Amount: '. $ytd_values['amount'] .' PS Entry Name ID: '. $this->getPayStubEntryNameId() , __FILE__, __LINE__, __METHOD__,10);

			$this->setYTDAmount( $ytd_values['amount'] + $this->getAmount() );
			$this->setYTDUnits( $ytd_values['units'] + $this->getUnits() );

			Debug::text('bCalculating YTD values...: Amount: '. $this->getYTDAmount() , __FILE__, __LINE__, __METHOD__,10);
		} else {
			Debug::text('NOT Calculating YTD values... YTD Amount: '. $this->getYTDAmount() .' YTD Units: '. $this->getYTDUnits() , __FILE__, __LINE__, __METHOD__,10);
		}
		*/

		return TRUE;
	}

	function Validate() {
		//Calc YTD values if they aren't already done.
		if ( $this->getYTDAmount() == NULL OR $this->getYTDUnits() == NULL ) {
			$this->preSave();
		}

		//Make sure rate * units = amount

		if ( $this->getAmount() === NULL ) {
			//var_dump( $this->getAmount() );
			$this->Validator->isTrue(		'amount',
											FALSE,
											('Invalid Amount'));
		}

		if ( $this->getPayStubEntryNameId() == '' ) {
			Debug::text('PayStubEntryNameID is NULL: ' , __FILE__, __LINE__, __METHOD__,10);
			$this->Validator->isTrue(		'pay_stub_entry_name_id',
											FALSE,
											('Invalid Entry Account Id'));
		}

		/*
		//Allow just units to be set. For cases like Gross Pay Units.
		//Make sure Units isn't set if Rate is
		if ( $this->getRate() != NULL AND $this->getUnits() == NULL ) {
			$this->Validator->isTrue(		'units',
											FALSE,
											('Invalid Units'));
		}

		if ( $this->getUnits() != NULL AND $this->getRate() == NULL ) {
			$this->Validator->isTrue(		'rate',
											FALSE,
											('Invalid Rate'));
		}
		*/

		/*
		//FIXME: For some reason the calculation done here has one less decimal digit then
        //the calculation done in Wage::getOverTime2Wage().
		if ( $this->getRate() !== NULL AND $this->getUnits() !== NULL
				AND ( $this->getRate() * $this->getUnits() ) != $this->getAmount() ) {
			Debug::text('Validate: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Amount: '. $this->getAmount() .' Calc: Rate: '. $this->getRate() .' Units: '. $this->getUnits() .' Total: '. ( $this->getRate() * $this->getUnits() ), __FILE__, __LINE__, __METHOD__,10);
			$this->Validator->isTrue(		'amount',
											FALSE,
											('Invalid Amount, calculation is incorrect.'));
		}
		*/
		//Make sure YTD values are set
		//YTD could be 0 though if we "cancel" out a entry like vacation accrual.
		if ( $this->getYTDAmount() === NULL ) {
			Debug::text('getYTDAmount is NULL: ' , __FILE__, __LINE__, __METHOD__,10);
			//var_dump( $this );

			$this->Validator->isTrue(		'ytd_amount',
											FALSE,
											('Invalid YTD Amount'));

		}

		if ( $this->getYTDUnits() === NULL ) {
			$this->Validator->isTrue(		'ytd_units',
											FALSE,
											('Invalid YTD Units'));

		}

		return TRUE;
	}

	function postSave() {
		//If this entry is based off pay stub amendment, mark
		//PS amendment as "ACTIVE" status.
		//Once PS is paid, mark them as PAID.

		//If Pay Stub Account is attached to an accrual, handle that now.
		//Only calculate accrual if this is a new pay stub entry, not if we're
		//editing one, so we don't duplicate the accrual entry.
		//
		// **Handle this in PayStubFactory instead.
		//
		/*
		//This all handled in PayStubFactory::addEntry() now.
		if ( $this->getEnableCalculateAccrualBalance() == TRUE
				AND $this->getPayStubEntryAccountObject() != FALSE
				AND $this->getPayStubEntryAccountObject()->getAccrual() != FALSE
				AND $this->getPayStubEntryAccountObject()->getAccrual() != 0
				) {
			Debug::text('Pay Stub Account is linked to an accrual...' , __FILE__, __LINE__, __METHOD__,10);

			if ( $this->getPayStubEntryAccountObject()->getType() == 10 ) {
				$amount = $this->getAmount()*-1; //This is an earning... Reduce accrual
			} elseif ( $this->getPayStubEntryAccountObject()->getType() == 20 ) {
				$amount = $this->getAmount(); //This is a employee deduction, add to accrual.
			}
			Debug::text('Amount: '. $amount , __FILE__, __LINE__, __METHOD__,10);

			if ( $amount != 0 ) {
				//Add entry to do the opposite to the accrual.
				$psef = new PayStubEntryFactory();
				$psef->setPayStub( $this->getPayStub() );
				$psef->setPayStubEntryNameId( $this->getPayStubEntryAccountObject()->getAccrual() );
				$psef->setAmount( $amount );

				return $psef->Save();
			}
		} else {
			Debug::text('Pay Stub Account is NOT linked to an accrual...' , __FILE__, __LINE__, __METHOD__,10);
		}
		*/

		return TRUE;
	}
}
?>
