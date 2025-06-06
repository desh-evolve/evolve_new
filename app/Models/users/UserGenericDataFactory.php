<?php

namespace App\Models\Users;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTi18n;
use Illuminate\Support\Facades\DB;

class UserGenericDataFactory extends Factory {
	protected $table = 'user_generic_data';
	protected $pk_sequence_name = 'user_generic_data_id_seq'; //PK Sequence name

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'user_id' => 'User',
										'script' => 'Script',
										'name' => 'Name',
										'is_default' => 'Default',
										'data' => 'Data',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
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
			$ugdlf = new UserGenericDataListFactory();
			if ( $this->getUser() == FALSE ) {
				$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			} else {
				$ugdlf->getByUserIdAndScriptAndDefault( $this->getUser(), $this->getScript(), TRUE );
			}
			if ( $ugdlf->getRecordCount() > 0 ) {
				foreach( $ugdlf->rs as $ugd_obj ) {
					$ugdlf->data = (array)$ugd_obj;
					$ugd_obj = $ugdlf;
					Debug::Text('Removing Default Flag From: '. $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$ugd_obj->setDefault(FALSE);
					if ( $ugd_obj->isValid() ) {
						$ugd_obj->Save();
					}
				}
			}
		}

		return TRUE;
	}
/*
	//Disable this for now, as it bombards the log with messages that are mostly useless.
	function addLog( $log_action ) {
		if ( $this->getUser() == FALSE AND $this->getDefault() == TRUE ) {
			//Bypass logging on Company Default Save.
			return TRUE;
		}

		return TTLog::addEntry( $this->getId(), $log_action, ('Employee/Company Generic Data'), NULL, $this->getTable() );
	}
*/

	static function handleScriptName( $script_name ) {
		return str_replace('//', '/', $script_name);
	}

	static function getSearchFormData( $saved_search_id, $sort_column ) {
		global $current_company, $current_user;

		$retarr = array();

		$ugdlf = new UserGenericDataListFactory();
		if ( isset($saved_search_id) AND $saved_search_id != 0 AND $saved_search_id != '' ) {
			$ugdlf->getByUserIdAndId( $current_user->getId(), $saved_search_id );
		} else {
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), self::handleScriptName( $_SERVER['SCRIPT_NAME'] ) );
		}

		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugd_obj = $ugdlf->getCurrent();
			Debug::Text('Found Search Criteria for Saved Search ID: '. $ugd_obj->getId() .' Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);

			$retarr['saved_search_id'] = $ugd_obj->getId();
			$retarr['filter_data'] = $ugd_obj->getData();
			//Debug::Arr($retarr['filter_data'], 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);
			unset($ugd_obj);

			Debug::Text('aSort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
			if ( $sort_column == '' AND isset($retarr['filter_data']['sort_column']) AND $retarr['filter_data']['sort_column'] != '') {
				$retarr['sort_column'] = Misc::trimSortPrefix($retarr['filter_data']['sort_column']);
				$retarr['sort_order'] = $retarr['filter_data']['sort_order'];
				Debug::Text('bSort Column: '. $retarr['sort_column'], __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $retarr;
	}


	static function searchFormDataHandler( $action, $filter_data, $redirect_url ) {
		global $current_company, $current_user;

		if ( $action == '' ) {
			return FALSE;
		}

		if ( !is_array($filter_data) ) {
			return FALSE;
		}

		$saved_search_id = FALSE;

		$ugdlf = new UserGenericDataListFactory();
		$ugdf = new UserGenericDataFactory();
		if ( $action == 'search_form_update' OR $action == 'search_form_save' ) {
			Debug::Text('Save Report!', __FILE__, __LINE__, __METHOD__,10);

			if ( $action == 'search_form_update' AND isset($filter_data['saved_search_id']) AND $filter_data['saved_search_id'] != '' AND $filter_data['saved_search_id'] != 0 ) {
				$ugdlf->getByUserIdAndId( $current_user->getId(), $filter_data['saved_search_id'] );
				if ( $ugdlf->getRecordCount() > 0 ) {
					$ugdf = $ugdlf->getCurrent();
				}
				$ugdf->setID( $filter_data['saved_search_id'] );
			}

			$ugdf->setCompany( $current_company->getId() );
			$ugdf->setUser( $current_user->getId() );
			$ugdf->setScript( self::handleScriptName( $_SERVER['SCRIPT_NAME'] ) );

			if ( isset($filter_data['saved_search_name']) AND $filter_data['saved_search_name'] != '' ) {
				$ugdf->setName( $filter_data['saved_search_name'] );
			}

			$ugdf->setData( $filter_data );
			$ugdf->setDefault( FALSE );
		} elseif ( $action == 'search_form_clear' OR $action == 'search_form_search' ) {
			Debug::Text('Search!', __FILE__, __LINE__, __METHOD__,10);

			//When they click search it saves the criteria as the default, so it always loads from then on.
			//Unless cleared.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), self::handleScriptName( $_SERVER['SCRIPT_NAME'] ), TRUE );
			if ( $ugdlf->getRecordCount() > 0 ) {
				$ugdf = $ugdlf->getCurrent();
				$saved_search_id = $filter_data['saved_search_id'] = $ugdf->getId();
			}
			$ugdf->setCompany( $current_company->getId() );
			$ugdf->setUser( $current_user->getId() );
			$ugdf->setScript( self::handleScriptName( $_SERVER['SCRIPT_NAME'] ) );
			$ugdf->setName( ('-Default-') );
			$ugdf->setData( $filter_data );
			$ugdf->setDefault( TRUE );
		} elseif ( isset($filter_data['saved_search_id']) AND $filter_data['saved_search_id'] != '' ) {
			$ugdlf->getByUserIdAndId( $current_user->getId(), $filter_data['saved_search_id'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				$ugd_obj = $ugdlf->getCurrent();

				$ugd_obj->setDeleted(TRUE);
				$ugd_obj->Save();
			}

			Redirect::Page( $redirect_url );

			return TRUE;
		}

		if ( is_object($ugdf) AND $ugdf->isValid() ) {
			$ugf_id = $ugdf->Save();

			if ( is_numeric($ugf_id) ) {
				$saved_search_id = $ugf_id;
			} elseif ( $ugf_id === TRUE ) {
				$saved_search_id = $filter_data['saved_search_id'];
			}
			unset($ugf_id);
		}

		return $saved_search_id;
	}

	static function getReportFormData( $saved_search_id ) {
		global $current_company, $current_user;

		$retarr = array();

		$ugdlf = new UserGenericDataListFactory();
		if ( isset($saved_search_id) AND $saved_search_id != 0 AND $saved_search_id != '' ) {
			$ugdlf->getByUserIdAndId( $current_user->getId(), $saved_search_id );
		} else {
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), self::handleScriptName( $_SERVER['SCRIPT_NAME'] ) );
		}

		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugd_obj = $ugdlf->getCurrent();
			Debug::Text('Found Search Criteria for Saved Search ID: '. $ugd_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

			$retarr['saved_search_id'] = $ugd_obj->getId();
			$retarr['filter_data'] = $ugd_obj->getData();
			//Debug::Arr($retarr['filter_data'], 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);
			unset($ugd_obj);
		}

		return $retarr;
	}

	static function reportFormDataHandler( $action, $filter_data, $generic_data,  $redirect_url ) {
		global $current_company, $current_user;

		if ( $action == '' ) {
			return FALSE;
		}

		if ( !is_array($generic_data) ) {
			return FALSE;
		}

		$saved_report_id = FALSE;

		$ugdlf = new UserGenericDataListFactory();
		$ugdf = new UserGenericDataFactory();
		if ( $action == 'save' OR $action == 'update' ) {
			Debug::Text('Save Report!', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($generic_data['id']) AND $generic_data['id'] != '' AND $generic_data['id'] != 0 ) {
				$ugdlf->getByUserIdAndId( $current_user->getId(), $generic_data['id'] );
				if ( $ugdlf->getRecordCount() > 0 ) {
					$ugdf = $ugdlf->getCurrent();
				}
				$ugdf->setID( $generic_data['id'] );
			}

			$ugdf->setCompany( $current_company->getId() );
			$ugdf->setUser( $current_user->getId() );
			$ugdf->setScript( self::handleScriptName( $_SERVER['SCRIPT_NAME'] ) );

			if ( isset($generic_data['name']) AND $generic_data['name'] != '' ) {
				$ugdf->setName( $generic_data['name'] );
			}

			$ugdf->setData( $filter_data );
			if ( isset($generic_data['is_default']) ) {
				$ugdf->setDefault( TRUE );
			}
		} elseif ( $action == 'delete' AND isset($generic_data['id']) AND $generic_data['id'] != '' ) {
			$ugdlf->getByUserIdAndId( $current_user->getId(), $generic_data['id'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				$ugd_obj = $ugdlf->getCurrent();

				$ugd_obj->setDeleted(TRUE);
				$ugd_obj->Save();
			}

			Redirect::Page( $redirect_url );

			return TRUE;
		}

		if ( is_object($ugdf) AND $ugdf->isValid() ) {
			$ugf_id = $ugdf->Save();

			if ( is_numeric($ugf_id) ) {
				$saved_report_id = $ugf_id;
			} elseif ( $ugf_id === TRUE ) {
				$saved_report_id = $generic_data['id'];
			}
			unset($ugf_id);
		}

		return $saved_report_id;
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
