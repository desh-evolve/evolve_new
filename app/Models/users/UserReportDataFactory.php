<?php

namespace App\Models\Users;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use Illuminate\Support\Facades\DB;

class UserReportDataFactory extends Factory {
	protected $table = 'user_report_data';
	protected $pk_sequence_name = 'user_report_data_id_seq'; //PK Sequence name

	protected $user_obj = NULL;
	protected $obj_handler = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-name' => ('Name'),
										'-1020-description' => ('Description'),
										'-1030-script_name' => ('Report'),
										'-1040-is_default' => ('Default'),

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
								'script_name',
								'name',
								'description',
								'is_default',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'description',
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
										'user_id' => 'User',
										'script' => 'Script',
										'script_name' => FALSE,
										'name' => 'Name',
										'is_default' => 'Default',
										'description' => 'Description',
										'data' => 'Data',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {
			$class = $this->getScript();
			if ( class_exists( $class, TRUE ) ) {
				$this->obj_handler = new $class();
				return $this->obj_handler;
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

		if ( $this->Validator->isResultSetWithRows(			'company',
															$clf->getByID($id),
															('Invalid Company')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getScript() {
		if ( isset($this->data['script']) ) {
			return $this->data['script'];
		}

		return FALSE;
	}
	function setScript($value) {
		//Strip out double slashes, as sometimes those occur and they cause the saved settings to not appear.
		$value = self::handleScriptName( trim($value) );
		if (	$this->Validator->isLength(	'script',
											$value,
											('Invalid script'),
											1,250)
						) {

			$this->data['script'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		//Allow no user_id to be set yet, as that would be company generic data.

		if ( $this->getScript() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $this->getCompany(),
					':script' => $this->getScript(),
					':name' => strtolower( $name ),
					);

		$query = 'select id from '. $this->getTable() .'
					where
						company_id = :company_id
						AND script = :script
						AND lower(name) = :name ';
		if (  $this->getUser() != '' ) {
			$query .= ' AND user_id = '. (int)$this->getUser();
		} else {
			$query .= ' AND user_id is NULL ';
		}

		$query .= ' AND deleted = 0';
		$name_id = DB::select($query, $ph);

		if (empty($name_id) || $name_id== FALSE ) {
            $name_id = 0;
        }else{
            $name_id = current(get_object_vars($name_id[0]));
        }
		Debug::Arr($name_id,'Unique Name: '. $name , __FILE__, __LINE__, __METHOD__,10);

		if ( empty($name_id) || $name_id== FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											('Invalid name'),
											1,100)
				AND
				$this->Validator->isTrue(		'name',
												$this->isUniqueName($name),
												('Name already exists'))

						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getDefault() {
		if ( isset($this->data['is_default']) ) {
			return $this->fromBool( $this->data['is_default'] );
		}

		return FALSE;
	}
	function setDefault($bool) {
		$this->data['is_default'] = $this->toBool($bool);

		return TRUE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if (	$this->Validator->isLength(	'description',
											$description,
											('Description is invalid'),
											0,1024) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getData() {
		return unserialize( $this->data['data'] );
	}
	function setData($value) {
		$value = serialize($value);

		$this->data['data'] = $value;

		return TRUE;
	}

	function Validate() {
		if ( $this->getName() == '' ) {
			$this->Validator->isTRUE(	'name',
										FALSE,
										('Invalid name'));
		}

		return TRUE;
	}
	function preSave() {
		if ( $this->getDefault() == TRUE ) {
			//Remove default flag from all other entries.
			$urdlf = new UserReportDataListFactory();
			if ( $this->getUser() == FALSE ) {
				$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			} else {
				$urdlf->getByUserIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			}
			if ( $urdlf->getRecordCount() > 0 ) {
				foreach( $urdlf->rs as $urd_obj ) {
					$urdlf->data = (array)$urd_obj;
					$urd_obj = $urdlf;
					Debug::Text('Removing Default Flag From: '. $urd_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$urd_obj->setDefault(FALSE);
					if ( $urd_obj->isValid() ) {
						$urd_obj->Save();
					}
				}
			}
		}

		return TRUE;
	}

	function addLog( $log_action ) {
		if ( $this->getUser() == FALSE AND $this->getDefault() == TRUE ) {
			//Bypass logging on Company Default Save.
			return TRUE;
		}

		return TTLog::addEntry( $this->getId(), $log_action, ('Saved Report Data'), NULL, $this->getTable() );
	}


	static function handleScriptName( $script_name ) {
		return str_replace('//', '/', $script_name);
	}

	//Support setting created_by,updated_by especially for importing data.
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
						case 'script_name':
							$report_obj = $this->getObjectHandler();
							if ( is_object($report_obj ) ) {
								$data[$variable] = $report_obj->title;
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

}
?>
