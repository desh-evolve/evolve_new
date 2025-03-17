<?php

namespace App\Models\Policy;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PremiumPolicyDepartmentFactory extends Factory {
	protected $table = 'premium_policy_department';
	protected $pk_sequence_name = 'premium_policy_department_id_seq'; //PK Sequence name

	protected $department_obj = NULL;

	function getDepartmentObject() {
		if ( is_object($this->department_obj) ) {
			return $this->department_obj;
		} else {
			$lf = new DepartmentListFactory();
			$lf->getById( $this->getDepartment() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->department_obj = $lf->getCurrent();
				return $this->department_obj;
			}

			return FALSE;
		}
	}

	function getPremiumPolicy() {
		if ( isset($this->data['premium_policy_id']) ) {
			return $this->data['premium_policy_id'];
		}
	}
	function setPremiumPolicy($id) {
		$id = trim($id);

		$pplf = new PremiumPolicyListFactory();

		if (	$id == 0
				OR
				$this->Validator->isNumeric(	'premium_policy',
													$id,
													('Selected Premium Policy is invalid')
/*
				$this->Validator->isResultSetWithRows(	'premium_policy',
													$pplf->getByID($id),
													('Selected Premium Policy is invalid')
*/
															)
			) {

			$this->data['premium_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return $this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		$dlf = new DepartmentListFactory();

		if ( $this->Validator->isResultSetWithRows(	'department',
													$dlf->getByID($id),
													('Selected Department is invalid')
													) ) {
			$this->data['department_id'] = $id;

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
		$obj = $this->getDepartmentObject();
		if ( is_object($obj) ) {
			return TTLog::addEntry( $this->getPremiumPolicy(), $log_action,  ('Department').': '. $obj->getName(), NULL, $this->getTable() );
		}
	}
}
?>
