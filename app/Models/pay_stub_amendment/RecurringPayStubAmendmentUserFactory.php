<?php

namespace App\Models\PayStubAmendment;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class RecurringPayStubAmendmentUserFactory extends Factory {
	protected $table = 'recurring_ps_amendment_user';
	protected $pk_sequence_name = 'recurring_ps_amendment_user_id_seq'; //PK Sequence name
	function getRecurringPayStubAmendment() {
		return $this->data['recurring_ps_amendment_id'];
	}
	function setRecurringPayStubAmendment($id) {
		$id = trim($id);

		$rpsalf = TTnew( 'RecurringPayStubAmendmentListFactory' );

		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'recurring_ps_amendment',
															$rpsalf->getByID($id),
															('Recurring PS Amendment is invalid')
															) ) {
			$this->data['recurring_ps_amendment_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == -1
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('User is invalid')
															) ) {
			$this->data['user_id'] = $id;

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
