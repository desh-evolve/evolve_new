<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: PolicyGroupRoundIntervalPolicyFactory.class.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */

/**
 * @package Module_Policy
 */
class PolicyGroupRoundIntervalPolicyFactory extends Factory {
	protected $table = 'policy_group_round_interval_policy';
	protected $pk_sequence_name = 'policy_group_round_interval_policy_id_seq'; //PK Sequence name
	function getPolicyGroup() {
		if ( isset($this->data['policy_group_id']) ) {
			return $this->data['policy_group_id'];
		}

		return FALSE;
	}
	function setPolicyGroup($id) {
		$id = trim($id);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'policy_group',
															$pglf->getByID($id),
															TTi18n::gettext('Policy Group is invalid')
															) ) {
			$this->data['policy_group_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRoundIntervalPolicy() {
		if ( isset($this->data['round_interval_policy_id']) ) {
			return $this->data['round_interval_policy_id'];
		}
	}
	function setRoundInterValPolicy($id) {
		$id = trim($id);

		$riplf = TTnew( 'RoundIntervalPolicyListFactory' );

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'round_inteval_policy',
													$riplf->getByID($id),
													TTi18n::gettext('Selected Interval Rounding Policy is invalid')
															)
			) {

			$this->data['round_interval_policy_id'] = $id;

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
