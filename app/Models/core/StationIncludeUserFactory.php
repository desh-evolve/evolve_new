<?php

namespace App\Models\Core;

class StationIncludeUserFactory extends Factory {
	protected $table = 'station_include_user';
	protected $pk_sequence_name = 'station_include_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function getStation() {
		if ( isset($this->data['station_id']) ) {
			return $this->data['station_id'];
		}
	}
	function setStation($id) {
		$id = trim($id);

		$slf = new StationListFactory();

		if (	$id == 0
				OR
				$this->Validator->isNumeric(	'station',
													$id,
													TTi18n::gettext('Selected Station is invalid')

/*
				$this->Validator->isResultSetWithRows(	'station',
													$slf->getByID($id),
													TTi18n::gettext('Selected Station is invalid')
*/
															)
			) {

			$this->data['station_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$ulf->getById( $this->getIncludeUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
	function getIncludeUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setIncludeUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'include_user',
													$ulf->getByID($id),
													TTi18n::gettext('Selected User is invalid')
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

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getStation(), $log_action, TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
