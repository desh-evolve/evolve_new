<?php

namespace App\Models\Core;

use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;

class AuthorizationFactory extends Factory {
	protected $table = 'authorizations';
	protected $pk_sequence_name = 'authorizations_id_seq'; //PK Sequence name

	protected $obj_handler = NULL;
	protected $hierarchy_parent_arr = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
										//10 => 'default_schedule',
										//20 => 'schedule_amendment',
										//30 => 'shift_amendment',
										//40 => 'pay_stub_amendment',

										//52 => 'request_vacation',
										//54 => 'request_missed_punch',
										//56 => 'request_edit_punch',
										//58 => 'request_absence',
										//59 => 'request_schedule',
										90 => 'timesheet',

										//50 => 'request', //request_other
										1010 => 'request_punch',
										1020 => 'request_punch_adjust',
										1030 => 'request_absence',
										1040 => 'request_schedule',
										1100 => 'request_other',
									);
				break;
			case 'columns':
				$retval = array(

										'-1010-created_by' => ('Name'),
										'-1020-created_date' => ('Date'),
										'-1030-authorized' => ('Authorized'),
										//'-1100-object_type' => ('Object Type'),

										//'-2020-updated_by' => ('Updated By'),
										//'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'created_by',
								'created_date',
								'authorized',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'object_type_id' => 'ObjectType',
										'object_type' => FALSE,
										'object_id' => 'Object',
										'authorized' => 'Authorized',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCurrentUserID( $user_id = NULL ) {
		$user_id = trim($user_id);

		if ( empty($user_id) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$user_id = $current_user->getID();
			} else {
				return FALSE;
			}
		}

		return $user_id;
	}

	function getHierarchyParentArray( $user_id = NULL ) {
		if ( is_array($this->hierarchy_parent_arr) ) {
			return $this->hierarchy_parent_arr;
		} else {
			$user_id = $this->getCurrentUserID( $user_id );

			$this->getObjectHandler()->getByID( $this->getObject() );
			$current_obj = $this->getObjectHandler()->getCurrent();
			$object_user_id = $current_obj->getUser();

			if ( $object_user_id > 0 ) {
				Debug::Text(' Authorizing User ID: '. $user_id , __FILE__, __LINE__, __METHOD__,10);
				Debug::Text(' Object User ID: '. $object_user_id , __FILE__, __LINE__, __METHOD__,10);

				$ulf = new UserListFactory();
				$company_id = $ulf->getById( $object_user_id )->getCurrent()->getCompany();
				Debug::Text(' Company ID: '. $company_id , __FILE__, __LINE__, __METHOD__,10);

				$hlf = new HierarchyListFactory();
				$this->hierarchy_parent_arr = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $object_user_id, $this->getObjectType(), FALSE);

				Debug::Arr($this->hierarchy_parent_arr, ' Parent Arr: ', __FILE__, __LINE__, __METHOD__,10);
				return $this->hierarchy_parent_arr;
			} else {
				Debug::Text(' Could not find Object User ID: '. $user_id , __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return FALSE;
	}

	//This will return false if it can't find a hierarchy, or if its at the top level (1) and can't find a higher level.
	function getNextHierarchyLevel() {
		$retval = FALSE;

		$user_id = $this->getCurrentUserID();
		$parent_arr = $this->getHierarchyParentArray( $user_id );
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array($user_id, $level_parent_arr) ) {
					break;
				}
				$retval = $level;
			}
		}

		if ( $retval < 1 ) {
			Debug::Text(' ERROR, hierarchy level goes past 1... This shouldnt happen...', __FILE__, __LINE__, __METHOD__,10);
			$retval = FALSE;
		}

		return $retval;
	}
	function isValidParent() {
		$user_id = $this->getCurrentUserID();
		$parent_arr = $this->getHierarchyParentArray( $user_id );
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			krsort($parent_arr);
			foreach( $parent_arr as $level => $level_parent_arr ) {
				if ( in_array($user_id, $level_parent_arr) ) {
					return TRUE;
				}
			}
		}

		Debug::Text(' Authorizing User is not a parent of the object owner: ', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	function isFinalAuthorization() {
		$user_id = $this->getCurrentUserID();
		$parent_arr = $this->getHierarchyParentArray( $user_id );
		if ( is_array($parent_arr) AND count($parent_arr) > 0 ) {
			//Check that level 1 parent exists
			if ( isset($parent_arr[1]) 	AND in_array( $user_id, $parent_arr[1] ) ) {
				Debug::Text(' Final Authorization!', __FILE__, __LINE__, __METHOD__,10);
				return TRUE;
			}
		}

		Debug::Text(' NOT Final Authorization!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {

			switch ( $this->getObjectType() ) {
				case 90: //TimeSheet
					$this->obj_handler = new PayPeriodTimeSheetVerifyListFactory(); 
					break;
				case 50: //Requests
				case 1010:
				case 1020:
				case 1030:
				case 1040:
				case 1100:
					$this->obj_handler = new RequestListFactory(); 
					break;
			}

			return $this->obj_handler;
		}
	}

	function getObjectType() {
		return $this->data['object_type_id'];
	}
	function setObjectType($type) {
		$type = trim($type);

		// i18n: passing 3rd param as false because object_type options do not use gettext
		$key = Option::getByValue($type, $this->getOptions('object_type'), false );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getObject() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObject($id) {
		$id = trim($id);

		if (	$this->Validator->isResultSetWithRows(	'object',
														$this->getObjectHandler()->getByID($id),
														('Object ID is invalid')
														) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAuthorized() {
		return $this->fromBool( $this->data['authorized'] );
	}
	function setAuthorized($bool) {
		$this->data['authorized'] = $this->toBool($bool);

		return true;
	}

	function clearHistory() {
		Debug::text('Clearing Authorization History For Type: '. $this->getObjectType() .' ID: '. $this->getObject(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getObjectType() === FALSE OR $this->getObject() === FALSE ) {
			Debug::text('Clearing Authorization History FAILED!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$alf = new AuthorizationListFactory();
		$alf->getByObjectTypeAndObjectId( $this->getObjectType(), $this->getObject() );
		foreach( $alf->rs as $authorization_obj ) {
			$alf->data = (array)$authorization_obj;
			$alf->setDeleted(TRUE);
			$alf->Save();
		}

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() === FALSE
				AND $this->isFinalAuthorization() === FALSE
				AND $this->isValidParent() === FALSE ) {
			$this->Validator->isTrue(		'parent',
											FALSE,
											('User authorizing this object is not a parent of it'));

			return FALSE;
		}
		return TRUE;
	}

	function preSave() {
		//Debug::Text(' Calling preSave!: ', __FILE__, __LINE__, __METHOD__,10);
		$this->StartTransaction();

		return TRUE;
	}

	function postSave() {
		//Debug::Text(' Post Save: ', __FILE__, __LINE__, __METHOD__,10);
		if ( $this->getDeleted() == FALSE ) {
			$is_final_authorization = $this->isFinalAuthorization();

			//Get user_id of object.
			$this->getObjectHandler()->getByID( $this->getObject() );
			$current_obj = $this->getObjectHandler()->getCurrent();

			if ( $this->getAuthorized() === TRUE ) {
				if ( $is_final_authorization === TRUE ) {
					Debug::Text('  Approving Authorization... Final Authorizing Object: '. $this->getObject() .' - Type: '. $this->getObjectType(), __FILE__, __LINE__, __METHOD__,10);
					$current_obj->setAuthorizationLevel( 1 );
					$current_obj->setStatus(50); //Active/Authorized
					$current_obj->setAuthorized(TRUE);
				} else {
					Debug::text('  Approving Authorization, moving to next level up...', __FILE__, __LINE__, __METHOD__,10);
					$current_level = $current_obj->getAuthorizationLevel();
					if ( $current_level > 1 ) { //Highest level is 1, so no point in making it less than that.

						//Get the next level above the current user doing the authorization, in case they have dropped down a level or two.
						$next_level = $this->getNextHierarchyLevel();
						if ( $next_level !== FALSE AND $next_level < $current_level ) {
							Debug::text('  Current Level: '. $current_level .' Moving Up To Level: '. $next_level, __FILE__, __LINE__, __METHOD__,10);
							$current_obj->setAuthorizationLevel( $next_level );
						}
					}
					unset( $current_level, $next_level );
				}
			} else {
				Debug::text('  Declining Authorization...', __FILE__, __LINE__, __METHOD__,10);
				$current_obj->setStatus(55); //'AUTHORIZATION DECLINED'
			}

			if ( $current_obj->isValid() ) {
				Debug::text('  Object Valid...', __FILE__, __LINE__, __METHOD__,10);
				//Return true if object saved correctly.
				$retval = $current_obj->Save();

				if ( $retval === TRUE ) {
					$this->CommitTransaction();
					return TRUE;
				} else {
					$this->FailTransaction();
				}
			} else {
				//Always fail the transaction if we get this far.
				//This stops authorization entries from being inserted.
				$this->FailTransaction();
			}

			$this->CommitTransaction(); //preSave() starts the transaction
			return FALSE;
		}

		$this->CommitTransaction(); //preSave() starts the transaction

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

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'object_type':
							Debug::text('  Object Type...', __FILE__, __LINE__, __METHOD__,10);
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getColumn('user_id'), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ($this->getAuthorized() === TRUE ) {
			$authorized =  ('True');
		} else {
			$authorized =  ('False');
		}
		return TTLog::addEntry( $this->getId(), $log_action,  ('Authorization Object Type').': '.$this->getObjectType() .' '. ('Authorized').': '. $authorized, NULL , $this->getTable() );
	}
}
?>
