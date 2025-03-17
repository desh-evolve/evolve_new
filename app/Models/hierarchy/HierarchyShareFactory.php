<?php

namespace App\Models\Hierarchy;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class HierarchyShareFactory extends Factory {
	protected $table = 'hierarchy_share';
	protected $pk_sequence_name = 'hierarchy_share_id_seq'; //PK Sequence name
	function getHierarchyControl() {
		return $this->data['hierarchy_control_id'];
	}
	function setHierarchyControl($id) {
		$id = trim($id);
		
		$hclf = new HierarchyControlListFactory();
		
		if ( $this->Validator->isResultSetWithRows(	'hierarchy_control',
															$hclf->getByID($id),
															('Hierarchy control is invalid')
															) ) {
			$this->data['hierarchy_control_id'] = $id;
		
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
		
		if ( $this->Validator->isResultSetWithRows(	'user',
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
