<?php

namespace App\Models\Users;
use App\Models\Core\Factory; 

class UserDefaultCompanyDeductionFactory extends Factory {
	protected $table = 'user_default_company_deduction';
	protected $pk_sequence_name = 'user_default_company_deduction_id_seq'; //PK Sequence name
	
	function getUserDefault() {
		if ( isset($this->data['user_default_id']) ) {
			return $this->data['user_default_id'];
		}

		return FALSE;
	}
	function setUserDefault($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$udlf = TTnew( 'UserDefaultListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'user_default',
														$udlf->getByID($id),
														TTi18n::gettext('Employee Default settings is invalid')
													) ) {

			$this->data['user_default_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getCompanyDeduction() {
		if ( isset($this->data['company_deduction_id']) ) {
			return $this->data['company_deduction_id'];
		}

		return FALSE;
	}
	function setCompanyDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$cdlf = TTnew( 'CompanyDeductionListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'company_deduction',
														$cdlf->getByID($id),
														TTi18n::gettext('Deduction is invalid')
													) ) {

			$this->data['company_deduction_id'] = $id;

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
