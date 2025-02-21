<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\PayStub\PayStubEntryAccountListFactory;

class CompanyDeductionPayStubEntryAccountFactory extends Factory {
	protected $table = 'company_deduction_pay_stub_entry_account';
	protected $pk_sequence_name = 'company_deduction_pay_stub_entry_account_id_seq'; //PK Sequence name

	protected $pay_stub_entry_account_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Include'),
										20 => TTi18n::gettext('Exclude'),
									);
				break;

		}

		return $retval;
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

	function getCompanyDeduction() {
		if ( isset($this->data['company_deduction_id']) ) {
			return $this->data['company_deduction_id'];
		}

		return FALSE;
	}
	function setCompanyDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$cdlf = new CompanyDeductionListFactory();

		if (	$id != 0
				OR
				$this->Validator->isResultSetWithRows(	'company_deduction',
														$cdlf->getByID($id),
														TTi18n::gettext('Tax / Deduction is invalid')
													) ) {

			$this->data['company_deduction_id'] = $id;

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
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return FALSE;
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
				$this->Validator->isResultSetWithRows(	'pay_stub_entry_account',
														$psealf->getByID($id),
														TTi18n::gettext('Pay Stub Account is invalid')
													) ) {

			$this->data['pay_stub_entry_account_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function addLog( $log_action ) {
		$obj = $this->getPayStubEntryAccountObject();
		if ( is_object($obj) ) {
			$type = Option::getByKey($this->getType(), Misc::TrimSortPrefix( $this->getOptions('type') ) );
			return TTLog::addEntry( $this->getCompanyDeduction(), $log_action,  $type .' '. TTi18n::getText('Pay Stub Account').': '. $obj->getName(), NULL, $this->getTable() );
		}
	}
}
?>
