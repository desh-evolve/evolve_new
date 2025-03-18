<?php

namespace App\Models\Department;

use App\Models\Company\BranchListFactory;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class DepartmentBranchFactory extends Factory {
	protected $table = 'department_branch';
	protected $pk_sequence_name = 'department_branch_id_seq'; //PK Sequence name
	function getDepartment() {
		return $this->data['department_id'];
	}
	function setDepartment($id) {
		$id = trim($id);
		
		$dlf = new DepartmentListFactory();
		
		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$dlf->getByID($id),
															('Company is invalid')
															) ) {
			$this->data['department_id'] = $id;
		
			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		return $this->data['branch_id'];
	}
	function setBranch($id) {
		$id = trim($id);
		
		$blf = new BranchListFactory(); 
				
		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$blf->getByID($id),
															('Company is invalid')
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




}
?>
