<?php

namespace App\Models\Accrual;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Leaves\LeaveRequestListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Core\TTLog;

class AccrualFactory extends Factory {
	protected $table = 'accrual';
	protected $pk_sequence_name = 'accrual_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	protected $system_type_ids = array(10,20,75); //These all special types reserved for system use only.

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
					10 => ('Banked'), //System
					20 => ('Used'), //System
					30 => ('Awarded'),
					40 => ('Un-Awarded'),
					50 => ('Gift'),
					55 => ('Paid Out'),
					60 => ('Rollover Adjustment'),
					70 => ('Initial Balance'),
					75 => ('Accrual Policy'), //System
					80 => ('Other')
				);
				break;
			case 'system_type':
				$retval = array_intersect_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'user_type':
				$retval = array_diff_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'columns':
				$retval = array(
					'-1010-first_name' => ('First Name'),
					'-1020-last_name' => ('Last Name'),

					'-1030-accrual_policy' => ('Accrual Policy'),
					'-1040-type' => ('Type'),
					//'-1050-time_stamp' => ('Date'),
					'-1050-date_stamp' => ('Date'), //Date stamp is combination of time_stamp and user_date.date_stamp columns.
					'-1060-amount' => ('Amount'),

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
				$retval = Misc::arrayIntersectByKey( array('accrual_policy', 'type', 'date_stamp', 'amount'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
					'first_name',
					'last_name',
					'accrual_policy',
					'type',
					'amount',
					'date_stamp',
					'leave_request_id'
				);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
			'id' => 'ID',
			'user_id' => 'User',
			'first_name' => FALSE,
			'last_name' => FALSE,
			'default_branch' => FALSE,
			'default_department' => FALSE,
			'group' => FALSE,
			'title' => FALSE,
			'accrual_policy_id' => 'AccrualPolicyID',
			'accrual_policy' => FALSE,
			'type_id' => 'Type',
			'type' => FALSE,
			'user_date_total_id' => 'UserDateTotalID',
			'date_stamp' => FALSE,
			'time_stamp' => 'TimeStamp',
			'amount' => 'Amount',
			'deleted' => 'Deleted',
        );
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															('Invalid Employee')
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
				$this->Validator->isResultSetWithRows(	'accrual_policy_id',
													$aplf->getByID($id),
													('Accrual Policy is invalid')
													) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function isSystemType() {
		if ( in_array( $this->getType(), $this->system_type_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function getUserDateTotalID() {
		if ( isset($this->data['user_date_total_id']) ) {
			return $this->data['user_date_total_id'];
		}
	}
	function setUserDateTotalID($id) {
		$id = trim($id);
		
		$udtlf = new UserDateTotalListFactory();

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'user_date_total',
															$udtlf->getByID($id),
															('User Date Total ID is invalid')
															) ) {
			$this->data['user_date_total_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['time_stamp'];
			} else {
				return TTDate::strtotime( $this->data['time_stamp'] );
			}
		}

		return FALSE;
	}
	function setTimeStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'times_tamp',
												$epoch,
												('Incorrect time stamp'))

			) {

			$this->data['time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

        function getLeaveRequestId()
        {
            if ( isset($this->data['leave_requset_id']) ) {
			return $this->data['leave_requset_id'];
		}

		return FALSE;
            
        }
        
        function setLeaveRequestId($id)
        {
            $id = (int)trim($id);
            
            	if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$lrlf = new LeaveRequestListFactory();

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'leave_requset_id',
													$lrlf->getByID($id),
													('Leave Request is invalid')
													) ) {
                    
                                                                                                

			$this->data['leave_requset_id'] = $id;

			return TRUE;
		}

		return FALSE;
            
        }
        
        
        
	function isValidAmount($amount) {
		Debug::text('Type: '. $this->getType() .' Amount: '. $amount , __FILE__, __LINE__, __METHOD__, 10);
		//Based on type, set Amount() pos/neg
		switch ( $this->getType() ) {
			case 10: // Banked
			case 30: // Awarded
			case 50: // Gifted
				if ( $amount >= 0 ) {
					return TRUE;
				}
				break;
			case 20: // Used
			case 55: // Paid Out
			case 40: // Un Awarded
				if ( $amount <= 0 ) {
					return TRUE;
				}
				break;
			default:
				return TRUE;
				break;
		}

		return FALSE;

	}

	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
	function setAmount($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'amount',
													$int,
													('Incorrect Amount'))
				AND
				$this->Validator->isTrue(		'amount',
													$this->isValidAmount($int),
													('Amount does not match type, try using a negative or positive value instead'))
				) {
			$this->data['amount'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableCalcBalance() {
		if ( isset($this->calc_balance) ) {
			return $this->calc_balance;
		}

		return FALSE;
	}
	function setEnableCalcBalance($bool) {
		$this->calc_balance = $bool;

		return TRUE;
	}

	function Validate() {
		if ( $this->getAccrualPolicyID() == FALSE OR $this->getAccrualPolicyID() == 0 ) {
			$this->Validator->isTrue(		'accrual_policy_id',
											FALSE,
											('Please select an accrual policy'));

		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getTimeStamp() == FALSE ) {
			$this->setTimeStamp( TTDate::getTime() );
		}

		//Delete duplicates before saving.
		//Or orphaned entries on Sum'ing?
		//Would have to do it on view as well though.
		if ( $this->getUserDateTotalID() !== FALSE AND $this->getUserDateTotalID() !== 0 ) {
			$alf = new AccrualListFactory();
			$alf->getByUserIdAndAccrualPolicyIDAndUserDateTotalID( $this->getUser(), $this->getAccrualPolicyID(), $this->getUserDateTotalID() );
			Debug::text('Found Duplicate Records: '. (int)$alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $alf->getRecordCount() > 0 ) {
				foreach($alf as $a_obj ) {
					$a_obj->Delete();
				}
			}
		}

		return TRUE;
	}

	function postSave() {
		//Calculate balance
		if ( $this->getEnableCalcBalance() == TRUE ) {
			Debug::text('Calculating Balance is enabled! ', __FILE__, __LINE__, __METHOD__, 10);
			AccrualBalanceFactory::calcBalance( $this->getUser(), $this->getAccrualPolicyID() );
		}

		return TRUE;
	}

	static function deleteOrphans($user_id) {
		Debug::text('Attempting to delete Orphaned Records for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
		//Remove orphaned entries
		$alf = new AccrualListFactory();
		$alf->getOrphansByUserId( $user_id );
		Debug::text('Found Orphaned Records: '. $alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $alf->getRecordCount() > 0 ) {
			foreach( $alf as $a_obj ) {
				Debug::text('Orphan Record ID: '. $a_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$accrual_policy_ids[] = $a_obj->getAccrualPolicyId();
				$a_obj->Delete();
			}

			//ReCalc balances
			if ( isset($accrual_policy_ids) ) {
				foreach($accrual_policy_ids as $accrual_policy_id) {
					AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );
				}
			}

		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'date_stamp': //This is a combination of the time_stamp and user_date.date_stamp columns.
							$data[$variable] = TTDate::getAPIDate( 'DATE', strtotime( $this->getColumn( $variable ) ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		Debug::Arr($data, 'Data Object: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getId(), $log_action, ('Accrual') .' - '. ('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) .' '. ('Type') .': '. Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. ('Date') .': '.  TTDate::getDate('DATE', $this->getTimeStamp() ) .' '. ('Total Time') .': '. TTDate::getTimeUnit( $this->getAmount() ), NULL, $this->getTable(), $this );
		}

		return FALSE;
	}

}
?>
