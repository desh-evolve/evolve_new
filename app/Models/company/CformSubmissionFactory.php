<?php

namespace App\Models\Company;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class CformSubmissionFactory extends Factory {
	protected $table = 'cform_submission';
	protected $pk_sequence_name = 'cform_submission_id_seq'; //PK Sequence name
        
        function getAll1($payperiod, $epfNo, $type) {
            
		if ( $payperiod == '') {
			return FALSE;
		}

		if ( $epfNo == '') {
			return FALSE;
		}
 
	 	$query = ' select 	* from 	'. $this->table .' where company_id = '.$epfNo.' and pay_period = '.$payperiod.' and type = '."'$type'";
		 
		$this->rs = DB::select($query);
		$this->data = $this->rs;
		return $this;
	}
        
	function getPayPeriod() {
		return $this->data['pay_period'];
	}
        
	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															('Company is invalid')
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
												('Incorrect date'))
			) {

			if 	(	$epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												('Incorrect date'));
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
												('Incorrect value')) ) {

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
												('Incorrect value')) ) {

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
												('Incorrect value')) ) {

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
