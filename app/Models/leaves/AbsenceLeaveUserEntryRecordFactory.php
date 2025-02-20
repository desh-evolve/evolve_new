<?php

namespace App\Models\Leaves;
use App\Models\Core\Factory;

class AbsenceLeaveUserEntryRecordFactory extends Factory {
	protected $table = 'absence_leave_user_record';
	protected $pk_sequence_name = 'absence_leave_user_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('Enabled'),
										20 => TTi18n::gettext('Disabled'),
									);
				break;
			
                }
		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
			$variable_function_map = array(
											'id' => 'ID',
											'user_id' => 'UserId',
											'absence_policy_id' => 'AbsencePolicyId',
											'type_id' => FALSE,
											'absence_leave_user_id' => 'AbsenceLeaveUserId',
											'time_stamp' => 'TimeStamp',
											'amount' => 'WageGroup', 
                                                                                        'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
                  
        function getUserId() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUserId($id) {
		$id = trim($id);
                
		$uf = TTnew( 'UserListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'user_id',
											$uf->getById($id),
											TTi18n::gettext('User is invalid'),
											2,50)
						) {

			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
                 
        function getAbsencePlicyId() {
		if ( isset($this->data['absence_policy_id']) ) {
			return $this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyId($id) {
		$id = trim($id);
                
		$alf = TTnew( 'AbsencePolicyListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'absence_policy_id',
											$alf->getById($id),
											TTi18n::gettext('Absence Policy  is invalid'),
											2,50)
						) {

			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}


	function getAbsenceLeaveId() {
		if ( isset($this->data['absence_leave_id']) ) {
			return $this->data['absence_leave_id'];
		}

		return FALSE;
	}
	function setAbsenceLeaveId($id) {
		$id = trim($id);
                
		$alf = TTnew( 'AbsenceLeaveListFactory' );
		if (	$this->Validator->isResultSetWithRows(	'absence_leave_id',
											$alf->getById($id),
											TTi18n::gettext('Absence Leave is invalid'),
											2,50)
						) {

			$this->data['absence_leave_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUserDateId() {
		if ( isset($this->data['user_date_id']) ) {
			return $this->data['user_date_id'];
		}

		return FALSE;
	}
	function setUserDateId($id) {
		$id = trim($id); 
                if(true){
			$this->data['user_date_id'] = $id;

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

		if 	(	$this->Validator->isDate(		'time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect time stamp'))

			) {

			$this->data['time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}       
        
        
	function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
	function setAmount($amount) {
		$amount = trim($amount);
                        
		if (	$this->Validator->isFloat(	'amount',
                                                $amount,
                                                TTi18n::gettext('Amount is invalid') )
						) {

			$this->data['amount'] = $amount;

			return TRUE;
		}

		return FALSE;
	}
                        
	function getLengthServiceToSec( $lngt, $l_unit ) {
            $retVal = null;
            switch ($l_unit){
                case '10': //day
                    $retVal = $lngt*24*60*60;
                    break;
                case '20': //week
                    $retVal = $lngt*7*24*60*60;
                    break;
                case '30': //month
                    $retVal = $lngt*30*24*60*60;
                    break;
                case '20': //year
                    $retVal = $lngt*365*24*60*60;
                    break;
                case '20': //hour
                    $retVal = $lngt*24;
                    break;
                default :
                    return FALSE;
            }
            
            return $retVal; 
	}
        
	function getHourlyRate( $hourly_rate ) {
		if ( $this->getType() == 20 ) { //Unpaid
			$rate = 0;
		} elseif( $this->getType() == 30 ) { //Dock
			$rate = $this->getRate()*-1;
		} else {
			$rate = $this->getRate();
		}
		return bcmul( $hourly_rate, $rate );
	}
                
	function Validate() {
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this OT policy.
			$udtlf = TTnew( 'UserDateTotalListFactory' );
			$udtlf->getByAbsencePolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This absence policy is in use'));

			}
		}

		return TRUE;
	}

//	function preSave() {
//		if ( $this->getWageGroup() === FALSE ) {
//			$this->setWageGroup( 0 );
//		}
//
//		return TRUE;
//	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Absence Policy'), NULL, $this->getTable(), $this );
	}
}
?>
