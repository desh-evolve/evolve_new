<?php

namespace App\Models\Users;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use Illuminate\Support\Facades\DB;
use App\Models\Company\CompanyListFactory;

class UserTitleFactory extends Factory {
	protected $table = 'user_title';
	protected $pk_sequence_name = 'user_title_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1000-name' => ('Name'),

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
								'created_by',
								'created_date',
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

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
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
					':company_id' => $this->getCompany(),
					':name' => $name,
					);

		$query = 'select id from '. $this->table .'
					where company_id = :company_id
						AND name = :name
						AND deleted = 0';
		$name_id = DB::select($query, $ph);

		if (empty($name_id) || $name_id== FALSE ) {
            $name_id = 0;
        }else{
            $name_id = current(get_object_vars($name_id[0]));
        }
		Debug::Arr($name_id,'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($name_id) || $name_id== FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

        
  //***** FL ADDED FOR CHILD FUND (OCCUPATION CLASSIFICATION ID)*************************/
        
	
        
	function getClassificationId() {
		return $this->data['cl_name_id'];
	}
	function SetClassificationId($clId) {
		$value = metaphone( trim($clId) );

		if 	( $clId != '' ) {
			$this->data['cl_name_id'] = $clId;

			return TRUE;
		}

		return FALSE;
	}
        //***** END FL ADDED FOR CHILD FUND (OCCUPATION CLASSIFICATION ID)*************************/
        
        
	function getName() {
		return $this->data['name'];
	}
	function setName($name) {
		$name = trim($name);

		if 	(	$this->Validator->isLength(		'name',
												$name,
												('Name is too short or too long'),
												2,
												100)
					AND
						$this->Validator->isTrue(		'name',
														$this->isUniqueName($name),
														('Title already exists'))

												) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign title from employees: '. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
			//Unassign hours from this job.
			$uf = new UserFactory();
			$udf = new UserDefaultFactory();

			$query = 'update '. $uf->getTable() .' set title_id = 0 where company_id = '. (int)$this->getCompany() .' AND title_id = '. (int)$this->getId();
			DB::select($query);

			$query = 'update '. $udf->getTable() .' set title_id = 0 where company_id = '. (int)$this->getCompany() .' AND title_id = '. (int)$this->getId();
			DB::select($query);
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
		return TTLog::addEntry( $this->getId(), $log_action, ('Employee Title'), NULL, $this->getTable(), $this );
	}
}
?>
