<?php

namespace App\Models\Schedule;
use App\Models\Core\Factory;
use App\Models\Core\TTLog;
use App\Models\Users\UserListFactory;

class RecurringScheduleUserFactory extends Factory {
	protected $table = 'recurring_schedule_user';
	protected $pk_sequence_name = 'recurring_schedule_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function getRecurringScheduleControl() {
		if ( isset($this->data['recurring_schedule_control_id']) ) {
			return $this->data['recurring_schedule_control_id'];
		}

		return FALSE;
	}
	function setRecurringScheduleControl($id) {
		$id = trim($id);

		$rsclf = new RecurringScheduleControlListFactory();

		if (
			  $this->Validator->isNumeric(	'recurring_schedule_control_id',
											$id,
											('Recurring Schedule is invalid')
			/*
			$this->Validator->isResultSetWithRows(			'recurring_schedule',
															$rsclf->getByID($id),
															('Recurring Schedule is invalid')
			*/
															) ) {
			$this->data['recurring_schedule_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory(); 
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $id != 0

				AND $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Selected Employee is invalid')
															)
/*
				AND	$this->Validator->isTrue(		'user',
													$this->isUniqueUser($id),
													('Selected Employee is already assigned to another Pay Period')
													)
*/
			) {

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

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getRecurringScheduleControl(), $log_action, ('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
