<?php

namespace App\Models\Accrual;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Policy\AccrualPolicyFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;

class AccrualBalanceFactory extends Factory {
	protected $table = 'accrual_balance';
	protected $pk_sequence_name = 'accrual_balance_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
					'-1010-first_name' => ('First Name'),
					'-1020-last_name' => ('Last Name'),

					'-1030-accrual_policy' => ('Accrual Policy'),
					'-1040-accrual_policy_type' => ('Accrual Policy Type'),
					'-1050-balance' => ('Balance'),

					'-1090-title' => ('Title'),
					'-1099-group' => ('Group'),
					'-1100-default_branch' => ('Branch'),
					'-1110-default_department' => ('Department'),

					'-2000-created_by' => ('Created By'),
					'-2010-created_date' => ('Created Date'),
					'-2020-updated_by' => ('Updated By'),
					'-2030-updated_date' => ('Updated Date'),
				);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('accrual_policy','balance'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
					'first_name',
					'last_name',
					'accrual_policy',
					'accrual_policy_type',
					'balance'
				);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
				'user_id' => 'User',
				'first_name' => FALSE,
				'last_name' => FALSE,
				'accrual_policy_id' => 'AccrualPolicyID',
				'accrual_policy' => FALSE,
				'accrual_policy_type_id' => FALSE,
				'accrual_policy_type' => FALSE,
				'default_branch' => FALSE,
				'default_department' => FALSE,
				'group' => FALSE,
				'title' => FALSE,
				'balance' => 'Balance',
			);
			return $variable_function_map;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'user', $ulf->getByID($id), ('Invalid User') ) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualPolicyID() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return $this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = new AccrualPolicyListFactory();

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows( 'accrual_policy', $aplf->getByID($id), ('Accrual Policy is invalid') ) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBalance() {
		if ( isset($this->data['balance']) ) {
			return $this->data['balance'];
		}

		return FALSE;
	}
	function setBalance($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	($this->Validator->isNumeric( 'balance', $int, ('Incorrect Balance'))) {
			$this->data['balance'] = $int;

			return TRUE;
		}

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

	static function calcBalance( $user_id, $accrual_policy_id = NULL ) {
		$profiler = Factory::getProfiler();
		$profiler->startTimer( "AccrualBalanceFactory::calcBalance()");

		$alf = new AccrualListFactory();
		$balance = $alf->getSumByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id);
		Debug::text('Balance for User ID: '. $user_id .' Accrual Policy ID: '. $accrual_policy_id .' Balance: '. $balance, __FILE__, __LINE__, __METHOD__, 10);

		$ablf = new AccrualBalanceListFactory();
		$ablf->getByUserIdAndAccrualPolicyId( $user_id, $accrual_policy_id);
		Debug::text('Found balance records to delete: '. $ablf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ablf->getRecordCount() > 0) {
			foreach($ablf->rs as $ab_obj) {
				$ablf->data = (array)$ab_obj;
				$ablf->Delete();
			}
		}

		Debug::text('Setting new balance to: '. $balance, __FILE__, __LINE__, __METHOD__, 10);
		$ab = new AccrualBalanceFactory();
		$ab->setUser( $user_id );
		$ab->setAccrualPolicyId( $accrual_policy_id );
		$ab->setBalance( $balance );

		$profiler->stopTimer( "AccrualBalanceFactory::calcBalance()");

		if ( $ab->isValid() ) {
			return $ab->Save();
		}

		Debug::text('Setting new balance failed for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			$apf = new AccrualPolicyFactory();

			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy':
						case 'accrual_policy_type_id':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'accrual_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $apf->getOptions( 'type' ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), FALSE, $permission_children_ids, $include_columns );
			//Accrual Balances are only created/modified by the system.
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

}
?>
