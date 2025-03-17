<?php

namespace App\Models\PayStub;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PayStubEntryAccountLinkFactory extends Factory {
	protected $table = 'pay_stub_entry_account_link';
	protected $pk_sequence_name = 'pay_stub_entry_account_link_id_seq'; //PK Sequence name

	var $company_obj = NULL;
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

	function getTotalGross() {
		if ( isset($this->data['total_gross']) ) {
			return $this->data['total_gross'];
		}

		return FALSE;
	}
	function setTotalGross($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'total_gross',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['total_gross'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        function getTotalAdditions() {
		if ( isset($this->data['total_addtion']) ) {
			return $this->data['total_addtion'];
		}

		return FALSE;
	}
	function setTotalAdditions($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'total_addtion',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['total_addtion'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        

	function getTotalEmployeeDeduction() {
		if ( isset($this->data['total_employee_deduction']) ) {
			return $this->data['total_employee_deduction'];
		}

		return FALSE;
	}
	function setTotalEmployeeDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'total_employee_deduction',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['total_employee_deduction'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalEmployerDeduction() {
		if ( isset($this->data['total_employer_deduction']) ) {
			return $this->data['total_employer_deduction'];
		}

		return FALSE;
	}
	function setTotalEmployerDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'total_employer_deduction',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['total_employer_deduction'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalNetPay() {
		if ( isset($this->data['total_net_pay']) ) {
			return $this->data['total_net_pay'];
		}

		return FALSE;
	}
	function setTotalNetPay($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'total_net_pay',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['total_net_pay'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRegularTime() {
		if ( isset($this->data['regular_time']) ) {
			return $this->data['regular_time'];
		}

		return FALSE;
	}
	function setRegularTime($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'regular_time',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['regular_time'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEmployeeCPP() {
		if ( isset($this->data['employee_cpp']) ) {
			return $this->data['employee_cpp'];
		}

		return FALSE;
	}
	function setEmployeeCPP($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'employee_cpp',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['employee_cpp'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getEmployeeEI() {
		if ( isset($this->data['employee_ei']) ) {
			return $this->data['employee_ei'];
		}

		return FALSE;
	}
	function setEmployeeEI($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'employee_ei',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['employee_ei'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMonthlyAdvance() {
		if ( isset($this->data['monthly_advance']) ) {
			return $this->data['monthly_advance'];
		}

		return FALSE;
	}
	function setMonthlyAdvance($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'monthly_advance',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['monthly_advance'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMonthlyAdvanceDeduction() {
		if ( isset($this->data['monthly_advance_deduction']) ) {
			return $this->data['monthly_advance_deduction'];
		}

		return FALSE;
	}
	function setMonthlyAdvanceDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$psealf = new PayStubEntryAccountListFactory();

		if (
				( $id == '' OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'monthly_advance_deduction',
														$psealf->getByID($id),
														('Pay Stub Account is invalid')
													) ) {

			$this->data['monthly_advance_deduction'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayStubEntryAccountIDToTypeIDMap() {
		$retarr = array(
						$this->getTotalGross() => 10,
						$this->getTotalEmployeeDeduction() => 20,
						$this->getTotalEmployerDeduction() => 30,
						);

		return $retarr;
	}

	function postSave() {
		$this->removeCache( $this->getCompanyObject()->getId() );

		return TRUE;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('Pay Stub Account Links'), NULL, $this->getTable() );
	}
}
?>
