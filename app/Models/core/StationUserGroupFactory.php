<?php

namespace App\Models\Core;

use App\Models\Users\UserGroupListFactory;

class StationUserGroupFactory extends Factory {
	protected $table = 'station_user_group';
	protected $pk_sequence_name = 'station_user_group_id_seq'; //PK Sequence name

	var $group_obj = NULL;

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
													('Selected Station is invalid')
/*
				$this->Validator->isResultSetWithRows(	'station',
													$slf->getByID($id),
													('Selected Station is invalid')
*/
															)
			) {

			$this->data['station_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getGroupObject() {
		if ( is_object($this->group_obj) ) {
			return $this->group_obj;
		} else {
			$uglf = new UserGroupListFactory(); 
			$uglf->getById( $this->getGroup() );
			if ( $uglf->getRecordCount() == 1 ) {
				$this->group_obj = $uglf->getCurrent();
				return $this->group_obj;
			}

			return FALSE;
		}
	}
	function getGroup() {
		if ( isset($this->data['group_id']) ) {
			return $this->data['group_id'];
		}

		return FALSE;
	}
	function setGroup($id) {
		$id = trim($id);

		$uglf = new UserGroupListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'group',
													$uglf->getByID($id),
													('Selected Group is invalid')
													) ) {
			$this->data['group_id'] = $id;

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
		$g_obj = $this->getGroupObject();
		if ( is_object($g_obj) ) {
			return TTLog::addEntry( $this->getStation(), $log_action, ('Group').': '. $g_obj->getName() , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
