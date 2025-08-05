<?php

namespace App\Models\Request;

use App\Models\Core\AuthorizationListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Message\MessageControlFactory;

class RequestFactory extends Factory {
	protected $table = 'request';
	protected $pk_sequence_name = 'request_id_seq'; //PK Sequence name

	var $user_date_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Missed Punch'), 				//request_punch
										20 => ('Punch Adjustment'), 			//request_punch_adjust
										30 => ('Absence (incl. Vacation)'), 	//request_absence
										40 => ('Schedule Adjustment'), 		//request_schedule
										100 => ('Other'), 					//request_other
									);
				break;
			case 'status':
				$retval = array(
										10 => ('INCOMPLETE'),
										20 => ('OPEN'),
										30 => ('PENDING'), //Used to be "Pending Authorizion"
										40 => ('AUTHORIZATION OPEN'),
										50 => ('AUTHORIZED'), //Used to be "Active"
										55 => ('DECLINED'), //Used to be "AUTHORIZATION DECLINED"
										60 => ('DISABLED')
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => ('First Name'),
										'-1020-last_name' => ('Last Name'),
										'-1060-title' => ('Title'),
										'-1070-user_group' => ('Group'),
										'-1080-default_branch' => ('Branch'),
										'-1090-default_department' => ('Department'),

										'-1110-date_stamp' => ('Date'),
										'-1120-status' => ('Status'),
										'-1130-type' => ('Type'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp','status','type'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'type',
								'date_stamp',
								'status'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_date_id' => 'UserDateID',
										'user_id' => FALSE,

										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,

										'date_stamp' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'hierarchy_type_id' => 'HierarchyTypeId',
										'status_id' => 'Status',
										'status' => FALSE,
										'authorized' => 'Authorized',
										'authorization_level' => 'AuthorizationLevel',
										'message' => 'Message',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserDateObject() {
		if ( is_object( $this->user_date_obj ) ) {
			return $this->user_date_obj;
		} else {
			$udlf = new UserDateListFactory();
			$udlf->getById( $this->getUserDateID() );
			if ( $udlf->getRecordCount() > 0 ) {
				$this->user_date_obj = $udlf->getCurrent();
				return $this->user_date_obj;
			}

			return FALSE;
		}
	}

	//Used for authorizationFactory
	function getUserObject() {
		if ( is_object($this->getUserDateObject()) ) {
			return $this->getUserDateObject()->getUserObject();
		}

		return FALSE;
	}

	//Used for authorizationFactory
	function getUser() {
		if ( is_object($this->getUserDateObject()) ) {
			return $this->getUserDateObject()->getUser();
		}

		return FALSE;
	}

	function setUserDate($user_id, $date) {
		$user_date_id = UserDateFactory::findOrInsertUserDate( $user_id, $date );
		Debug::text(' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_date_id != '' ) {
			$this->setUserDateID( $user_date_id );
			return TRUE;
		}
		Debug::text(' No User Date ID found', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function getUserDateID() {
		if ( isset($this->data['user_date_id']) ) {
			return $this->data['user_date_id'];
		}

		return FALSE;
	}
	function setUserDateID($id = NULL) {
		$id = trim($id);

		$udlf = new UserDateListFactory(); 

		if (  $this->Validator->isResultSetWithRows(	'user_date',
														$udlf->getByID($id),
														('Date/Time is incorrect or pay period does not exist for this date. Please create a pay period schedule if you have not done so already')
														) ) {
			$this->data['user_date_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Convert hierarchy type_ids back to request type_ids.
	function getTypeIdFromHierarchyTypeId( $type_id ) {
		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = ( $request_type_id >= 1000 AND $request_type_id < 2000 ) ? (int)$request_type_id-1000 : (int)$request_type_id;
			}
		} else {
			$retval = ( $request_type_id >= 1000 AND $request_type_id < 2000 ) ? (int)$type_id-1000 : (int)$type_id;
			Debug::text('Hierarchy Type ID: '. $type_id .' Request Type ID: '. $retval, __FILE__, __LINE__, __METHOD__,10);
		}

		return $retval;
	}
	function getHierarchyTypeId( $type_id = NULL ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		//Make sure we support an array of type_ids.
		if ( is_array($type_id) ) {
			foreach( $type_id as $request_type_id ) {
				$retval[] = (int)$request_type_id+1000;
			}
		} else {
			$retval = (int)$type_id+1000;
			Debug::text('Request Type ID: '. $type_id .' Hierarchy Type ID: '. $retval, __FILE__, __LINE__, __METHOD__,10);
		}

		return $retval;
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

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'status',
											$value,
											('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		if ( isset($this->data['authorized']) AND $this->data['authorized'] !== NULL) {
			return $this->fromBool( $this->data['authorized'] );
		}

		return NULL;
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return true;
	}

	function getAuthorizationLevel() {
		if ( isset($this->data['authorization_level']) ) {
			return $this->data['authorization_level'];
		}

		return FALSE;
	}
	function setAuthorizationLevel($value) {
		$value = (int)trim( $value );

		if ( $value < 0 ) {
			$value = 0;
		}

		if ( $this->Validator->isNumeric(	'authorization_level',
											$value,
											('Incorrect authorization level') ) ) {

			$this->data['authorization_level'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMessage() {
		if ( isset($this->tmp_data['message']) ) {
			return $this->tmp_data['message'];
		}

		return FALSE;
	}
	function setMessage($text) {
		$text = trim($text);

		if 	(	$this->Validator->isLength(		'message',
												$text,
												('Invalid message length'),
												5,
												1024) ) {

			$this->tmp_data['message'] = htmlspecialchars( $text );

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if (	$this->isNew() == TRUE
				AND $this->Validator->hasError('message') == FALSE
				AND $this->getMessage() == FALSE ) {
			$this->Validator->isTRUE(		'message',
											FALSE,
											('Invalid message length') );
		}

		if ( $this->getUserDateID() == FALSE ) {
			$this->Validator->isTRUE(		'user_date',
											FALSE,
											('Date/Time is incorrect or pay period does not exist for this date. Please create a pay period schedule if you have not done so already') );
		}

		//Check to make sure this user has superiors to send a request too, otherwise we can't save the request.
		$hlf = new HierarchyListFactory(); 
		$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
		Debug::Arr($request_parent_level_user_ids, 'Check for Superiors: ', __FILE__, __LINE__, __METHOD__,10);

		if ( !is_array($request_parent_level_user_ids) OR count($request_parent_level_user_ids) == 0 ) {
			$this->Validator->isTRUE(		'message',
											FALSE,
											('No supervisors are assigned to you at this time, please try again later') );
		}

		return TRUE;
	}

	function preSave() {
		//If this is a new request, find the current authorization level to assign to it.
		if ( $this->isNew() == TRUE ) {

			if ( is_object( $this->getUserObject() ) ) {
				$hlf = new HierarchyListFactory();
				$hierarchy_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID(), $this->getHierarchyTypeId(), FALSE);
			}

			$hierarchy_highest_level = 99;
			if ( isset($hierarchy_arr) AND is_array( $hierarchy_arr ) ) {
				Debug::Arr($hierarchy_arr, ' Hierarchy Array: ', __FILE__, __LINE__, __METHOD__,10);
				$keys = array_keys($hierarchy_arr);
				$hierarchy_highest_level = end($keys);
				// $hierarchy_highest_level = end( array_keys( $hierarchy_arr ) ) ;
				Debug::Text(' Setting hierarchy level to: '. $hierarchy_highest_level, __FILE__, __LINE__, __METHOD__,10);
			}
			$this->setAuthorizationLevel( $hierarchy_highest_level );
		}

		if ( $this->getAuthorized() == TRUE ) {
			$this->setAuthorizationLevel( 0 );
		}

		//Remove date_stamp variable so we can generate a proper update SQL query automatically.
		unset($this->data['date_stamp']);

		return TRUE;
	}

	function postSave() {
		//Save message here after we have the request_id.
		if ( $this->getMessage() !== FALSE ) {
			$mcf = new MessageControlFactory(); 
			$mcf->StartTransaction();

			$hlf = new HierarchyListFactory();
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getHierarchyTypeId(), TRUE, FALSE ); //Request - Immediate parents only.
			Debug::Arr($request_parent_level_user_ids, 'Sending message to current direct Superiors: ', __FILE__, __LINE__, __METHOD__,10);

			$mcf = new MessageControlFactory();
			$mcf->setFromUserId( $this->getUser() );
			$mcf->setToUserId( $request_parent_level_user_ids );
			$mcf->setObjectType( 50 ); //Messages don't break out request types like hierarchies do.
			$mcf->setObject( $this->getID() );
			$mcf->setParent( 0 );
			$mcf->setSubject( Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. ('request from') .': '. $this->getUserObject()->getFullName(TRUE) );
			$mcf->setBody( $this->getMessage() );

			if ( $mcf->isValid() ) {
				$mcf->Save();

				$mcf->CommitTransaction();
			} else {
				$mcf->FailTransaction();
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('Delete authorization history for this request...'. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
			$alf = new AuthorizationListFactory(); 
			$alf->getByObjectTypeAndObjectId( $this->getHierarchyTypeId(), $this->getId() );
			foreach( $alf->rs as $authorization_obj ) {
				$alf->data = (array)$authorization_obj;
				$authorization_obj = $alf;
				Debug::Text('Deleting authorization ID: '. $authorization_obj->getID(), __FILE__, __LINE__, __METHOD__,10);
				$authorization_obj->setDeleted(TRUE);
				$authorization_obj->Save();
			}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			if ( isset($data['user_id']) AND $data['user_id'] != ''
					AND isset($data['date_stamp']) AND $data['date_stamp'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] , __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__,10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__,10);
			}

			if ( isset($data['status_id']) AND $data['status_id'] == '' ) {
				unset($data['status_id']);
				$this->setStatus( 30 ); //Pending authorization
			}
			if ( isset($data['user_date_id']) AND $data['user_date_id'] == '' ) {
				unset($data['user_date_id']);
			}

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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'message': //Message is attached in the message factory, so we can't return it here.
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::parseDateTime( $this->getColumn( 'date_stamp' ) ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('Request - Type').': '. Option::getByKey( $this->getType(), $this->getOptions('type') ), NULL, $this->getTable(), $this );
	}
}
?>
