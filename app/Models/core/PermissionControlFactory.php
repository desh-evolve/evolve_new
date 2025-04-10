<?php

namespace App\Models\Core;

use App\Models\Company\CompanyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\DB;

class PermissionControlFactory extends Factory {
	protected $table = 'permission_control';
	protected $pk_sequence_name = 'permission_control_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $tmp_previous_user_ids = array();

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'preset':
				$pf = new PermissionFactory();
				$retval = $pf->getOptions('preset');
				break;
			case 'level':
				$retval = array(
										1 => 1,
										2 => 2,
										3 => 3,
										4 => 4,
										5 => 5,
										6 => 6,
										7 => 7,
										8 => 8,
										9 => 9,
										10 => 10,
										11 => 11,
										12 => 12,
										13 => 13,
										14 => 14,
										15 => 15,
										16 => 16,
										17 => 17,
										18 => 18,
										19 => 19,
										20 => 20,
										21 => 21,
										22 => 22,
										23 => 23,
										24 => 24,
										25 => 25,
							);
				break;
			case 'columns':
				$retval = array(
										'-1000-name' => ('Name'),
										'-1010-description' => ('Description'),
										'-1020-level' => ('Level'),
										'-1030-total_users' => ('Employees'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('name','description','level'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'level',
								'total_users',
								'updated_by',
								'updated_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'name' => 'Name',
										'description' => 'Description',
										'level' => 'Level',
										'total_users' => FALSE,
										'user' => 'User',
										'permission' => 'Permission',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = new CompanyListFactory();
			$clf->getById( $this->getCompany() );
			if ( $clf->getRecordCount() > 0 ) {
				$this->company_obj = $clf->getCurrent();
				return $this->company_obj;
			}

			return FALSE;
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

		$clf = new CompanyListFactory();

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					':company_id' => (int)$this->getCompany(),
					':name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = :company_id AND name = :name AND deleted=0';
		// $permission_control_id = $this->db->GetOne($query, $ph);
        $permission_control_id = DB::select($query, $ph);
        if (empty($permission_control_id)) {
            $permission_control_id = 0;
        }else{
            $permission_control_id = current(get_object_vars($permission_control_id[0]));
        }

		Debug::Arr($permission_control_id,'Unique Permission Control ID: '. $permission_control_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $permission_control_id === FALSE ) {
			return TRUE;
		} else {
			if ($permission_control_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		return $this->data['name'];
	}
	function setName($name) {
		$name = trim($name);

		if (	$this->Validator->isLength(	'name',
											$name,
											('Name is invalid'),
											2,50)
				AND	$this->Validator->isTrue(	'name',
												$this->isUniqueName($name),
												('Name is already in use')
												)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$description == ''
				OR $this->Validator->isLength(	'description',
											$description,
											('Description is invalid'),
											1,255) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}


	function getLevel() {
		if ( isset($this->data['level']) ) {
			return (int)$this->data['level'];
		}

		return FALSE;
	}
	function setLevel($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'level',
											$value,
											('Incorrect Level'),
											$this->getOptions('level')) ) {

			$this->data['level'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		$pulf = new PermissionUserListFactory();
		$pulf->getByPermissionControlId( $this->getId() );
		foreach ($pulf->rs as $obj) {
			$pulf->data = (array)$obj;
			$list[] = $pulf->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		Debug::text('Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if (is_array($ids) and count($ids) > 0) {
			//Remove any of the selected employees from other permission control objects first.
			//So there we can switch employees from one group to another in a single action.
			$pulf = new PermissionUserListFactory();
			//$pculf->getByCompanyIdAndUserId( $this->getCompany(), $ids );
			$pulf->getByCompanyIdAndUserIdAndNotPermissionControlId( $this->getCompany(), $ids, (int)$this->getId() );
			if ( $pulf->getRecordCount() > 0 ) {
				Debug::text('Found User IDs assigned to another Permission Group, unassigning them!', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $pulf->rs as $pu_obj ) {
					$pulf->data = (array)$pu_obj;
					$pulf->Delete();
				}
			}
			unset($pulf, $pu_obj);

			$tmp_ids = array();

			$pf = new PermissionFactory();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pulf = new PermissionUserListFactory();
				$pulf->getByPermissionControlId( $this->getId() );

				$tmp_ids = array();
				foreach ($pulf->rs as $obj) {
					$pulf->data = (array)$obj;
					$id = $pulf->getUser();
					Debug::text('Permission Control ID: '. $pulf->getPermissionControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$this->tmp_previous_user_ids[] = $id;
						$pulf->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $pulf);
			}

			//Insert new mappings.
			$ulf = new UserListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					//Remove users from any other permission control object
					//first, otherwise there is a gab where an employee has
					//no permissions, this is especially bad for administrators
					//who are currently logged in.
					$puf = new PermissionUserFactory();
					$puf->setPermissionControl( $this->getId() );
					$puf->setUser( $id );

					$obj = $ulf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'user',
														$puf->Validator->isValid(),
														('Selected employee is invalid, or already assigned to another permission group').' ('. $obj->getFullName() .')' )) {
						$puf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getPermissionOptions() {
		$product_edition = $this->getCompanyObject()->getProductEdition();

		$retval = array();

		$pf = new PermissionFactory();
		$sections = $pf->getOptions('section');
		$names = $pf->getOptions('name');
		if ( is_array($names) ) {
			foreach ($names as $section => $permission_arr) {
				if ( ( $pf->isIgnore( $section, NULL, $product_edition ) == FALSE ) ) {
					foreach($permission_arr as $name => $display_name) {
						if ( $pf->isIgnore( $section, $name, $product_edition ) == FALSE ) {
							if ( isset($sections[$section]) ) {
								$retval[$section][$name] = 0;
							}
						}
					}
				}
			}
		}

		return $retval;
	}

	function getPermission() {
		$plf = new PermissionListFactory();
		$plf->getByCompanyIdAndPermissionControlId( $this->getCompany(), $this->getId() );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::Text('Found Permissions: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			foreach($plf->rs as $p_obj) {
				$plf->data = (array)$p_obj;
				$current_permissions[$plf->getSection()][$plf->getName()] = $plf->getValue();
			}

			return $current_permissions;
		}

		return FALSE;
	}
	function setPermission( $permission_arr, $old_permission_arr = array() ) {
		if ( $this->getId() == FALSE ) {
			return FALSE;
		}

		if ( defined('TIMETREX_API') AND TIMETREX_API == TRUE ) {
			//If we do the permission diff it messes up the HTML interface.
			if ( !is_array($old_permission_arr) OR ( is_array($old_permission_arr) AND count($old_permission_arr) == 0 ) ) {
				$old_permission_arr = $this->getPermission();
				Debug::Text(' Old Permissions: '. count($old_permission_arr), __FILE__, __LINE__, __METHOD__,10);
			}

			$permission_options = $this->getPermissionOptions();
			//Debug::Arr($permission_options, ' Permission Options: '. count($permission_options), __FILE__, __LINE__, __METHOD__,10);

			$permission_arr = Misc::arrayMergeRecursiveDistinct( (array)$permission_options, (array)$permission_arr );
			Debug::Text(' New Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($permission_arr, ' Final Permissions: '. count($permission_arr), __FILE__, __LINE__, __METHOD__,10);
		}

		$pf = new PermissionFactory();

		//Don't Delete all previous permissions, do that in the Permission class.
		if ( isset($permission_arr) AND is_array($permission_arr) AND count($permission_arr) > 0 ) {
			foreach ($permission_arr as $section => $permissions) {
				Debug::Text('  Section: '. $section, __FILE__, __LINE__, __METHOD__,10);

				foreach ($permissions as $name => $value) {
					Debug::Text('     Name: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__,10);
					if ( 	(
							!isset($old_permission_arr[$section][$name])
								OR (isset($old_permission_arr[$section][$name]) AND $value != $old_permission_arr[$section][$name] )
							)
							AND $pf->isIgnore( $section, $name, $this->getCompanyObject()->getProductEdition() ) == FALSE
							) {

						if ( $value == 0 OR $value == 1 ) {
							Debug::Text('    Modifying/Adding Permission: '. $name .' - Value: '. $value, __FILE__, __LINE__, __METHOD__,10);
							$tmp_pf = new PermissionFactory();
							$tmp_pf->setCompany( $this->getCompanyObject()->getId() );
							$tmp_pf->setPermissionControl( $this->getId() );
							$tmp_pf->setSection( $section );
							$tmp_pf->setName( $name );
							$tmp_pf->setValue( (int)$value );

							if ( $tmp_pf->isValid() ) {
								$tmp_pf->save();
							}
						}
					} else {
						Debug::Text('     Permission didnt change... Skipping', __FILE__, __LINE__, __METHOD__,10);
					}
				}
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getLevel() == '' OR $this->getLevel() == 0 ) {
			$this->setLevel( 1 );
		}

		return TRUE;
	}

	function postSave() {
		$pf = new PermissionFactory();

		$clear_cache_user_ids = array_merge( (array)$this->getUser(), (array)$this->tmp_previous_user_ids);
		foreach( $clear_cache_user_ids as $user_id ) {
			$pf->clearCache( $user_id, $this->getCompany() );
		}
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
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
						case 'total_users':
							$data[$variable] = $this->getColumn( $variable );
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Permission Group: '). $this->getName(), NULL, $this->getTable(), $this );
	}
}
?>
