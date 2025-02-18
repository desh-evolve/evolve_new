<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/

namespace App\Models\Accrual;
use Illuminate\Support\Facades\Log;

/*
 * $Revision: 4265 $
 * $Id: AccrualBalanceFactory.class.php 4265 2011-02-18 00:49:20Z ipso $
 * $Date: 2011-02-17 16:49:20 -0800 (Thu, 17 Feb 2011) $
 */

/**
 * @package Module_Accrual
 */
class AccrualBalanceFactory extends Factory {
	protected $table = 'accrual_balance';
	protected $pk_sequence_name = 'accrual_balance_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1030-accrual_policy' => TTi18n::gettext('Accrual Policy'),
										'-1040-accrual_policy_type' => TTi18n::gettext('Accrual Policy Type'),
										'-1050-balance' => TTi18n::gettext('Balance'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
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

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
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
				$this->Validator->isResultSetWithRows(	'accrual_policy',
													$aplf->getByID($id),
													TTi18n::gettext('Accrual Policy is invalid')
													) ) {

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

		if 	(	$this->Validator->isNumeric(		'balance',
													$int,
													TTi18n::gettext('Incorrect Balance'))
				) {
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
		global $profiler;

		$profiler->startTimer( "AccrualBalanceFactory::calcBalance()");

		$alf = new AccrualListFactory();
		$balance = $alf->getSumByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id);
		Debug::text('Balance for User ID: '. $user_id .' Accrual Policy ID: '. $accrual_policy_id .' Balance: '. $balance, __FILE__, __LINE__, __METHOD__, 10);

		$ablf = new AccrualBalanceListFactory();
		$ablf->getByUserIdAndAccrualPolicyId( $user_id, $accrual_policy_id);
		Debug::text('Found balance records to delete: '. $ablf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ablf->getRecordCount() > 0) {
			foreach($ablf as $ab_obj) {
				$ab_obj->Delete();
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
