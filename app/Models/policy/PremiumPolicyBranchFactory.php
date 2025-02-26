<?php

namespace App\Models\Policy;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PremiumPolicyBranchFactory extends Factory {
	protected $table = 'premium_policy_branch';
	protected $pk_sequence_name = 'premium_policy_branch_id_seq'; //PK Sequence name

	protected $branch_obj = NULL;

	function getBranchObject() {
		if ( is_object($this->branch_obj) ) {
			return $this->branch_obj;
		} else {
			$lf = TTnew( 'BranchListFactory' );
			$lf->getById( $this->getBranch() );
			if ( $lf->getRecordCount() == 1 ) {
				$this->branch_obj = $lf->getCurrent();
				return $this->branch_obj;
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

		$pplf = TTnew( 'PremiumPolicyListFactory' );

		if (	$id == 0
				OR
				$this->Validator->isNumeric(	'premium_policy',
													$id,
													TTi18n::gettext('Selected Premium Policy is invalid')
/*
				$this->Validator->isResultSetWithRows(	'premium_policy',
													$pplf->getByID($id),
													TTi18n::gettext('Selected Premium Policy is invalid')
*/
															)
			) {

			$this->data['premium_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return $this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		$blf = TTnew( 'BranchListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'branch',
													$blf->getByID($id),
													TTi18n::gettext('Selected Branch is invalid')
													) ) {
			$this->data['branch_id'] = $id;

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
		$obj = $this->getBranchObject();
		if ( is_object($obj) ) {
			return TTLog::addEntry( $this->getPremiumPolicy(), $log_action,  TTi18n::getText('Branch').': '. $obj->getName(), NULL, $this->getTable() );
		}
	}
}
?>
