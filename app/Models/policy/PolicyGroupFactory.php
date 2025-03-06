<?php

namespace App\Models\Policy;

use App\Models\Company\CompanyGenericMapFactory;
use App\Models\Company\CompanyGenericMapListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class PolicyGroupFactory extends Factory {
	protected $table = 'policy_group';
	protected $pk_sequence_name = 'policy_group_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1000-name' => ('Name'),
										'-1100-total_users' => ('Employees'),

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
								'total_users',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'user',
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
										'user' => 'User',
										'total_users' => FALSE,
										'over_time_policy' => 'OverTimePolicy',
										'round_interval_policy' => 'RoundIntervalPolicy',
										'premium_policy' => 'PremiumPolicy',
										'meal_policy' => 'MealPolicy',
										'break_policy' => 'BreakPolicy',
										'holiday_policy' => 'HolidayPolicy',
										'accrual_policy' => 'AccrualPolicy',
										'exception_policy_control_id' => 'ExceptionPolicyControlID',
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
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        //By thilini 2018-03-21 for aqua Fresh
        function getId() {
		if ( isset($this->data['id']) ) {
			return $this->data['id'];
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
											('Name is invalid'),
											2,50)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		$pgulf = TTnew( 'PolicyGroupUserListFactory' );
		$pgulf->getByPolicyGroupId( $this->getId() );
		foreach ($pgulf as $obj) {
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

		if ( is_array($ids) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pgulf = TTnew( 'PolicyGroupUserListFactory' );
				$pgulf->getByPolicyGroupId( $this->getId() );

				$tmp_ids = array();
				foreach ($pgulf as $obj) {
					$id = $obj->getUser();
					Debug::text('Policy ID: '. $obj->getPolicyGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			$ulf = TTnew( 'UserListFactory' );
			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$pguf = TTnew( 'PolicyGroupUserFactory' );
					$pguf->setPolicyGroup( $this->getId() );
					$pguf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ($this->Validator->isTrue(		'user',
															$pguf->Validator->isValid(),
															('Selected employee is invalid or already assigned to another policy group').' ('. $obj->getFullName() .')' )) {
							$pguf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 110, $this->getID() );
	}
	function setOverTimePolicy($ids) {
		Debug::text('Setting OverTime Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 110, $this->getID(), $ids );
	}

	function getPremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 120, $this->getID() );
	}
	function setPremiumPolicy($ids) {
		Debug::text('Setting Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 120, $this->getID(), $ids );
	}

	function getRoundIntervalPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 130, $this->getID() );
	}
	function setRoundIntervalPolicy($ids) {
		Debug::text('Setting Round Interval Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 130, $this->getID(), $ids );
	}

	function getAccrualPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 140, $this->getID() );
	}
	function setAccrualPolicy($ids) {
		Debug::text('Setting Accrual Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 140, $this->getID(), $ids );
	}

	function getMealPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 150, $this->getID() );
	}
	function setMealPolicy($ids) {
		Debug::text('Setting Meal Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 150, $this->getID(), $ids );
	}

	function getBreakPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 160, $this->getID() );
	}
	function setBreakPolicy($ids) {
		Debug::text('Setting Break Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 160, $this->getID(), $ids );
	}

	function getHolidayPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 180, $this->getID() );
	}
	function setHolidayPolicy($ids) {
		Debug::text('Setting Holiday Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 180, $this->getID(), (array)$ids );
	}

	function getExceptionPolicyControlID() {
		if ( isset($this->data['exception_policy_control_id']) ) {
			return $this->data['exception_policy_control_id'];
		}

		return FALSE;
	}
	function setExceptionPolicyControlID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$epclf = TTnew( 'ExceptionPolicyControlListFactory' );

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'exception_policy',
														$epclf->getByID($id),
														('Exception Policy is invalid')
													) ) {

			$this->data['exception_policy_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Policy Group from User Defaults...'. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
			$udf = TTnew( 'UserDefaultFactory' );

			$query = 'update '. $udf->getTable() .' set policy_group_id = 0 where company_id = '. (int)$this->getCompany() .' AND policy_group_id = '. (int)$this->getId();
			$this->db->Execute($query);
		}

		return TRUE;
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Policy Group'), NULL, $this->getTable(), $this );
	}
}
?>
