<?php

namespace App\Models\Company;
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: CompanyUserCountFactory.class.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */

/**
 * @package Module_Company
 */
class CompanyUserCountFactory extends Factory {
	protected $table = 'company_user_count';
	protected $pk_sequence_name = 'company_user_count_id_seq'; //PK Sequence name
	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date'))
			) {

			if 	(	$epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date'));
			}


		}

		return FALSE;
	}

	function getActiveUsers() {
		if ( isset($this->data['active_users']) ) {
			return $this->data['active_users'];
		}

		return FALSE;
	}
	function setActiveUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'active_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['active_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getInActiveUsers() {
		if ( isset($this->data['inactive_users']) ) {
			return $this->data['inactive_users'];
		}

		return FALSE;
	}
	function setInActiveUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'inactive_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['inactive_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDeletedUsers() {
		if ( isset($this->data['deleted_users']) ) {
			return $this->data['deleted_users'];
		}

		return FALSE;
	}
	function setDeletedUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'deleted_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['deleted_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		//$this->removeCache( $this->getId() );

		return TRUE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
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
