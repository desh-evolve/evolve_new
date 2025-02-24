<?php

namespace App\Models\Core;

use App\Models\Users\UserListFactory;

class PermissionUserFactory extends Factory {
	protected $table = 'permission_user';
	protected $pk_sequence_name = 'permission_user_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function getPermissionControl() {
		return $this->data['permission_control_id'];
	}

	function setPermissionControl($id) {
		$id = trim($id);

		$pclf = new PermissionControlListFactory();

		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'permission_control',
															$pclf->getByID($id),
															TTi18n::gettext('Permission Group is invalid')
															) ) {
			$this->data['permission_control_id'] = $id;

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
	function isUniqueUser($id) {
		$pclf = new PermissionControlListFactory();

		$ph = array(
					'id' => $id,
					);

		$query = 'select a.id from '. $this->getTable() .' as a, '. $pclf->getTable() .' as b where a.permission_control_id = b.id AND a.user_id = ? AND b.deleted=0';
		$user_id = $this->db->GetOne($query, $ph);
		Debug::Arr($user_id,'Unique User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id === FALSE ) {
			return TRUE;
		}

		return FALSE;
	}
	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $id != 0
				AND $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Selected Employee is invalid')
															)
				AND	$this->Validator->isTrue(		'user',
													$this->isUniqueUser($id),
													TTi18n::gettext('Selected Employee is already assigned to another Permission Group')
													)
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
			return TTLog::addEntry( $this->getPermissionControl(), $log_action, TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
