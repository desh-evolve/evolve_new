<?php

namespace App\Models\Department;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;
use App\Models\Users\UserListFactory;

class DepartmentBranchUserFactory extends Factory {
	protected $table = 'department_branch_user';
	protected $pk_sequence_name = 'department_branch_user_id_seq'; //PK Sequence name
	function getDepartmentBranch() {
		return $this->data['department_branch_id'];
	}
	function setDepartmentBranch($id) {
		$id = trim($id);
		
		$dblf = new DepartmentBranchListFactory();
		
		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'department_branch',
															$dblf->getByID($id),
															('Department Branch is invalid')
															) ) {
			$this->data['department_branch_id'] = $id;
		
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
