<?php

namespace App\Models\Core;

use App\Models\Department\DepartmentListFactory;

class StationDepartmentFactory extends Factory {
	protected $table = 'station_department';
	protected $pk_sequence_name = 'station_department_id_seq'; //PK Sequence name

	var $department_obj = NULL;

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

	function getDepartmentObject() {
		if ( is_object($this->department_obj) ) {
			return $this->department_obj;
		} else {
			$dlf = new DepartmentListFactory();
			$dlf->getById( $this->getDepartment() );
			if ( $dlf->getRecordCount() == 1 ) {
				$this->department_obj = $dlf->getCurrent();
				return $this->department_obj;
			}

			return FALSE;
		}
	}
	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return $this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		$dlf = new DepartmentListFactory();

		if ( $this->Validator->isResultSetWithRows(	'department',
													$dlf->getByID($id),
													('Selected Department is invalid')
													) ) {
			$this->data['department_id'] = $id;

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
		$d_obj = $this->getDepartmentObject();
		if ( is_object($d_obj) ) {
			return TTLog::addEntry( $this->getStation(), $log_action, ('Department').': '. $d_obj->getName() , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
