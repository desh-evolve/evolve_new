<?php

namespace App\Models\Policy;

use App\Models\Company\CompanyGenericMapFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Schedule\RecurringScheduleTemplateFactory;
use App\Models\Schedule\ScheduleFactory;
use Illuminate\Support\Facades\DB;

class SchedulePolicyFactory extends Factory {
	protected $table = 'schedule_policy';
	protected $pk_sequence_name = 'schedule_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $meal_policy_obj = NULL;
	protected $break_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1020-name' => ('Name'),
										'-1030-meal_policy' => ('Meal Policy'),
										'-1040-absence_policy' => ('Absence Policy'),
										'-1050-over_time_policy' => ('Overtime Policy'),
										'-1060-start_stop_window' => ('Window'),

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
								'meal_policy',
								'start_stop_window',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'meal_policy_id' => 'MealPolicyID',
										'meal_policy' => FALSE,
										'over_time_policy_id' => 'OverTimePolicyID',
										'over_time_policy' => FALSE,
										'absence_policy_id' => 'AbsencePolicyID',
										'absence_policy' => FALSE,
										'break_policy_id' => 'BreakPolicy',
										//'break_policy' => FALSE,
										'start_stop_window' => 'StartStopWindow',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	 }

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = new CompanyListFactory();
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getMealPolicyObject() {
		if ( is_object($this->meal_policy_obj) ) {
			return $this->meal_policy_obj;
		} else {
			$mplf = new MealPolicyListFactory();
			$mplf->getById( $this->getMealPolicyID() );
			if ( $mplf->getRecordCount() > 0 ) {
				$this->meal_policy_obj = $mplf->getCurrent();
				return $this->meal_policy_obj;
			}

			return FALSE;
		}
	}

	function getBreakPolicyObject( $break_policy_id ) {
		if ( $break_policy_id == '' ) {
			return FALSE;
		}

		Debug::Text('Client Contact ID: '. $break_policy_id .' Client ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__,10);

		if ( isset($this->break_policy_obj[$break_policy_id])
			AND is_object($this->break_policy_obj[$break_policy_id]) ) {
			return $this->break_policy_obj[$break_policy_id];
		} else {
			$bplf = new BreakPolicyListFactory();
			$bplf->getById( $break_policy_id );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->break_policy_obj[$break_policy_id] = $bplf->getCurrent();
				return $this->break_policy_obj[$break_policy_id];
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

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
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

	function getMealPolicyID() {
		if ( isset($this->data['meal_policy_id']) ) {
			return $this->data['meal_policy_id'];
		}

		return FALSE;
	}
	function setMealPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$mplf = new MealPolicyListFactory();

		if ( $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'meal_policy',
														$mplf->getByID($id),
														('Meal Policy is invalid')
													) ) {

			$this->data['meal_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBreakPolicy() {
		return CompanyGenericMapFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 165, $this->getID() );
	}
	function setBreakPolicy($ids) {
		Debug::text('Setting Break Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 165, $this->getID(), $ids );
	}

	function getOverTimePolicyID() {
		if ( isset($this->data['over_time_policy_id']) ) {
			return $this->data['over_time_policy_id'];
		}

		return FALSE;
	}
	function setOverTimePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$otplf = new OverTimePolicyListFactory();

		if (  $id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'over_time_policy',
														$otplf->getByID($id),
														('Invalid Overtime Policy ID')
														) ) {
			$this->data['over_time_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return $this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$aplf = new AbsencePolicyListFactory();

		if (
				$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy',
														$aplf->getByID($id),
														('Invalid Absence Policy ID')
														) ) {
			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStartStopWindow() {
		if ( isset($this->data['start_stop_window']) ) {
			return (int)$this->data['start_stop_window'];
		}
		return FALSE;
	}
	function setStartStopWindow($int) {
		$int = (int)$int;

		if 	(	$this->Validator->isNumeric(		'start_stop_window',
													$int,
													('Incorrect Start/Stop window')) ) {
			$this->data['start_stop_window'] = $int;

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
			Debug::Text('UnAssign Schedule Policy from Schedule/Recurring Schedules...'. $this->getId(), __FILE__, __LINE__, __METHOD__,10);
			$sf = new ScheduleFactory(); 
			$rstf = new RecurringScheduleTemplateFactory();

			$query = 'update '. $sf->getTable() .' set schedule_policy_id = 0 where schedule_policy_id = '. (int)$this->getId();
			DB::select($query);

			$query = 'update '. $rstf->getTable() .' set schedule_policy_id = 0 where schedule_policy_id = '. (int)$this->getId();
			DB::select($query);
		}

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
						case 'meal_policy':
						case 'absence_policy':
							$data[$variable] = $this->getColumn($variable);
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Schedule Policy'), NULL, $this->getTable(), $this );
	}
}
?>
