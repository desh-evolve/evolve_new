<?php

namespace App\Models\Hierarchy;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Users\UserListFactory;

class HierarchyControlFactory extends Factory {
	protected $table = 'hierarchy_control';
	protected $pk_sequence_name = 'hierarchy_control_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$hotlf = new HierarchyObjectTypeListFactory();
				$retval = $hotlf->getOptions('object_type');
				break;
			case 'short_object_type':
				$hotlf = new HierarchyObjectTypeListFactory();
				$retval = $hotlf->getOptions('short_object_type');
				break;
			case 'columns':
				$retval = array(
										'-1010-name' => ('Name'),
										'-1020-description' => ('Description'),
										'-1030-superiors' => ('Superiors'),
										'-1030-subordinates' => ('Subordinates'),
										'-1050-object_type_display' => ('Objects'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'description',
								'superiors',
								'subordinates',
								'object_type_display'
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
										'company_id' => 'Company',
										'name' => 'Name',
										'description' => 'Description',
										'superiors' => FALSE,
										'subordinates' => FALSE,
										'object_type' => 'ObjectType',
										'object_type_display' => FALSE,
										'user' => 'User',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Invalid Company')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND name = ? AND deleted = 0';
		$hierarchy_control_id = $this->db->GetOne($query, $ph);
		Debug::Arr($hierarchy_control_id,'Unique Hierarchy Control ID: '. $hierarchy_control_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $hierarchy_control_id === FALSE ) {
			return TRUE;
		} else {
			if ($hierarchy_control_id == $this->getId() ) {
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
											2,250)
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
											1,250) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getObjectTypeDisplay() {
		$object_type_ids = $this->getObjectType();
		$object_types = $this->getOptions('short_object_type');

		$retval = array();
		foreach( $object_type_ids as $object_type_id ) {
			$retval[] = Option::getByKey( $object_type_id, $object_types );
		}
		sort($retval); //Maintain consistent order.

		return implode(',', $retval );
	}

	function getObjectType() {
		$hotlf = new HierarchyObjectTypeListFactory();
		$hotlf->getByHierarchyControlId( $this->getId() );

		foreach ($hotlf->rs as $object_type) {
			$hotlf->data = (array)$object_type;
			$object_type = $hotlf;
			$object_type_list[] = $object_type->getObjectType();
		}

		if ( isset($object_type_list) ) {
			return $object_type_list;
		}

		return FALSE;
	}

	function setObjectType($ids) {
		if ( is_array($ids) AND count($ids) > 0 ) {
			$tmp_ids = array();
			Debug::Arr($ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new HierarchyObjectTypeListFactory();
				$lf_a->getByHierarchyControlId( $this->getId() );
				Debug::text('Existing Object Type Rows: '. $lf_a->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$obj = $lf_a;
					//$id = $obj->getId();
					$id = $obj->getObjectType(); //Need to use object_types rather than row IDs.
					Debug::text('Hierarchy Object Type ID: '. $obj->getId() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: Object Type: '. $id .' ID: '. $obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting: Object Type: '. $id .' ID: '. $obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new HierarchyObjectTypeFactory();
					$f->setHierarchyControl( $this->getId() );
					$f->setObjectType( $id );

					if ($this->Validator->isTrue(		'object_type',
														$f->Validator->isValid(),
														('Object type is already assigned to another hierarchy'))) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		$this->Validator->isTrue(		'object_type',
										FALSE,
										('At least one object must be selected'));

		return FALSE;
	}

	function getUser() {
		$hulf = new HierarchyUserListFactory();
		$hulf->getByHierarchyControlID( $this->getId() );
		foreach ($hulf->rs as $obj) {
			$hulf->data = (array)$obj;
			$obj = $hulf;
			$list[] = $obj->getUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setUser($ids) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		Debug::text('Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$hulf = new HierarchyUserListFactory();
				$hulf->getByHierarchyControlID( $this->getId() );

				foreach ($hulf->rs as $obj) {
					$hulf->data = (array)$obj;
					$obj = $hulf;
					$id = $obj->getUser();
					Debug::text('HierarchyControl ID: '. $obj->getHierarchyControl() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$ulf = new UserListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$huf = new HierarchyUserFactory();
					$huf->setHierarchyControl( $this->getId() );
					$huf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ($this->Validator->isTrue(		'user',
															$huf->Validator->isValid(),
															('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')' )) {
							$huf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function Validate() {
		//When the user changes just the hierarchy objects, we need to loop through ALL users and confirm no conflicting hierarchies exist.
		//Only do this for existing hierarchies and ones that are already valid up to this point.
		if ( !$this->isNew() AND $this->Validator->isValid() == TRUE ) {

			$user_ids = $this->getUser();
			if ( is_array( $user_ids ) ) {
				$huf = new HierarchyUserFactory();
				$huf->setHierarchyControl( $this->getID() );

				foreach( $user_ids as $user_id ) {
					if ( $huf->isUniqueUser( $user_id ) == FALSE ) {
						$ulf = new UserListFactory();
						$ulf->getById( $user_id );
						if ( $ulf->getRecordCount() > 0 ) {
							$obj = $ulf->getCurrent();
							('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')';
							if ( $this->Validator->isTrue(		'user',
																$huf->isUniqueUser( $user_id, $this->getID() ),
																('Selected subordinate is invalid or already assigned to another hierarchy with the same objects ').' ('. $obj->getFullName() .')' )) {
							}
						} else {
							('Selected subordinate is invalid or already assigned to another hierarchy with the same object. User ID: '. $user_id);
						}
					}
				}
			}
		}

		return TRUE;
	}

	function postSave() {
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
						case 'superiors':
						case 'subordinates':
							$data[$variable] = $this->getColumn($variable);
							break;
						case 'object_type_display':
							$data[$variable] = $this->getObjectTypeDisplay();
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
		return TTLog::addEntry( $this->getId(), $log_action, ('Hierarchy'), NULL, $this->getTable(), $this );
	}
}
?>
