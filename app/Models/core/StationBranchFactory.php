<?php

class StationBranchFactory extends Factory {
	protected $table = 'station_branch';
	protected $pk_sequence_name = 'station_branch_id_seq'; //PK Sequence name

	var $branch_obj = NULL;

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

	function getBranchObject() {
		if ( is_object($this->branch_obj) ) {
			return $this->branch_obj;
		} else {
			$blf = new BranchListFactory();
			$blf->getById( $this->getBranch() );
			if ( $blf->getRecordCount() == 1 ) {
				$this->branch_obj = $blf->getCurrent();
				return $this->branch_obj;
			}

			return FALSE;
		}
	}
	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return $this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		$blf = new BranchListFactory();

		if ( $this->Validator->isResultSetWithRows(	'branch',
													$blf->getByID($id),
													TTi18n::gettext('Selected Branch is invalid')
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

	function addLog( $log_action ) {
		$b_obj = $this->getBranchObject();
		if ( is_object($b_obj) ) {
			return TTLog::addEntry( $this->getStation(), $log_action, TTi18n::getText('Branch').': '. $b_obj->getName() , NULL, $this->getTable() );
		}

		return FALSE;
	}
}
?>
