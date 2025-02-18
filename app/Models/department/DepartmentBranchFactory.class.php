<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: DepartmentBranchFactory.class.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
/*
CREATE TABLE department_branch (
    id serial NOT NULL,
    branch_id integer DEFAULT 0 NOT NULL,
    department_id integer DEFAULT 0 NOT NULL
) WITHOUT OIDS;
*/

/**
 * @package Module_Department
 */
class DepartmentBranchFactory extends Factory {
	protected $table = 'department_branch';
	protected $pk_sequence_name = 'department_branch_id_seq'; //PK Sequence name
	function getDepartment() {
		return $this->data['department_id'];
	}
	function setDepartment($id) {
		$id = trim($id);
		
		$dlf = TTnew( 'DepartmentListFactory' );
		
		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$dlf->getByID($id),
															TTi18n::gettext('Company is invalid')
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
		
		$blf = TTnew( 'BranchListFactory' );
		
		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$blf->getByID($id),
															TTi18n::gettext('Company is invalid')
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
