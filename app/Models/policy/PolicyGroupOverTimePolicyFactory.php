<?php

namespace App\Models\Policy;
use App\Models\Core\Factory;

use App\Models\Core\TTi18n;

class PolicyGroupOverTimePolicyFactory extends Factory {
	protected $table = 'policy_group_over_time_policy';
	protected $pk_sequence_name = 'policy_group_over_time_policy_id_seq'; //PK Sequence name
	function getPolicyGroup() {
		if ( isset($this->data['policy_group_id']) ) {
			return $this->data['policy_group_id'];
		}

		return FALSE;
	}
	function setPolicyGroup($id) {
		$id = trim($id);

		$pglf = new PolicyGroupListFactory();

		if ( $this->Validator->isResultSetWithRows(	'policy_group',
															$pglf->getByID($id),
															('Policy Group is invalid')
															) ) {
			$this->data['policy_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getOverTimePolicy() {
		if ( isset($this->data['over_time_policy_id']) ) {
			return $this->data['over_time_policy_id'];
		}
	}
	function setOverTimePolicy($id) {
		$id = trim($id);

		$otplf = new OverTimePolicyListFactory();

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'over_time_policy',
													$otplf->getByID($id),
													('Selected Overtime Policy is invalid')
															)
			) {

			$this->data['over_time_policy_id'] = $id;

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
}
?>
