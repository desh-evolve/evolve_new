<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: HierarchyShareFactory.class.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
/*
CREATE TABLE hierarchy_share (
    id serial NOT NULL,
    hierarchy_control_id integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL
) WITHOUT OIDS;
*/

/**
 * @package Module_Hierarchy
 */
class HierarchyShareFactory extends Factory {
	protected $table = 'hierarchy_share';
	protected $pk_sequence_name = 'hierarchy_share_id_seq'; //PK Sequence name
	function getHierarchyControl() {
		return $this->data['hierarchy_control_id'];
	}
	function setHierarchyControl($id) {
		$id = trim($id);
		
		$hclf = TTnew( 'HierarchyControlListFactory' );
		
		if ( $this->Validator->isResultSetWithRows(	'hierarchy_control',
															$hclf->getByID($id),
															TTi18n::gettext('Hierarchy control is invalid')
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
		
		$ulf = TTnew( 'UserListFactory' );
		
		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('User is invalid')
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
