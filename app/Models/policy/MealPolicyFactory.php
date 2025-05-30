<?php

namespace App\Models\Policy;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Core\UserDateTotalListFactory;

class MealPolicyFactory extends Factory {
	protected $table = 'meal_policy';
	protected $pk_sequence_name = 'meal_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => ('Auto-Deduct'),
										15 => ('Auto-Add'),
										20 => ('Normal')
									);
				break;
			case 'auto_detect_type':
				$retval = array(
										10 => ('Time Window'),
										20 => ('Punch Time'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1020-name' => ('Name'),
										'-1030-amount' => ('Meal Time'),
										'-1040-trigger_time' => ('Active After'),

										'-1050-auto_detect_type' => ('Auto Detect Meals By'),
										//'-1060-start_window' => ('Start Window'),
										//'-1070-window_length' => ('Window Length'),
										//'-1080-minimum_punch_time' => ('Minimum Punch Time'),
										//'-1090-maximum_punch_time' => ('Maximum Punch Time'),

										'-1100-include_lunch_punch_time' => ('Include Lunch Punch'),

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
								'type',
								'amount',
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
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
										'trigger_time' => 'TriggerTime',
										'amount' => 'Amount',
										'auto_detect_type_id' => 'AutoDetectType',
										'auto_detect_type' => FALSE,
										'start_window' => 'StartWindow',
										'window_length' => 'WindowLength',
										'minimum_punch_time' => 'MinimumPunchTime',
										'maximum_punch_time' => 'MaximumPunchTime',
										'include_lunch_punch_time' => 'IncludeLunchPunchTime',
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

			return FALSE;
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

	function getTriggerTime() {
		if ( isset($this->data['trigger_time']) ) {
			return (int)$this->data['trigger_time'];
		}

		return FALSE;
	}
	function setTriggerTime($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'trigger_time',
													$int,
													('Incorrect Trigger Time')) ) {
			$this->data['trigger_time'] = $int;

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
	function setAmount($value) {
		$value = trim($value);

		if 	(	$this->Validator->isNumeric(		'amount',
													$value,
													('Incorrect Deduction Amount')) ) {

			$this->data['amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAutoDetectType() {
		if ( isset($this->data['auto_detect_type_id']) ) {
			return $this->data['auto_detect_type_id'];
		}

		return FALSE;
	}
	function setAutoDetectType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('auto_detect_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'auto_detect_type',
											$value,
											('Incorrect Auto-Detect Type'),
											$this->getOptions('auto_detect_type')) ) {

			$this->data['auto_detect_type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getStartWindow() {
		if ( isset($this->data['start_window']) ) {
			return $this->data['start_window'];
		}

		return FALSE;
	}
	function setStartWindow($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'start_window',
													$value,
													('Incorrect Start Window')) ) {

			$this->data['start_window'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWindowLength() {
		if ( isset($this->data['window_length']) ) {
			return $this->data['window_length'];
		}

		return FALSE;
	}
	function setWindowLength($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'window_length',
													$value,
													('Incorrect Window Length')) ) {

			$this->data['window_length'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumPunchTime() {
		if ( isset($this->data['minimum_punch_time']) ) {
			return $this->data['minimum_punch_time'];
		}

		return FALSE;
	}
	function setMinimumPunchTime($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'minimum_punch_time',
													$value,
													('Incorrect Minimum Punch Time')) ) {

			$this->data['minimum_punch_time'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumPunchTime() {
		if ( isset($this->data['maximum_punch_time']) ) {
			return $this->data['maximum_punch_time'];
		}

		return FALSE;
	}
	function setMaximumPunchTime($value) {
		$value = (int)trim($value);

		if 	(	$value == 0
				OR
				$this->Validator->isNumeric(		'maximum_punch_time',
													$value,
													('Incorrect Maximum Punch Time')) ) {

			$this->data['maximum_punch_time'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/*
		This takes into account any lunch punches when calculating the meal policy.
		If enabled for:
			Auto-Deduct:	It will only deduct the amount that is not taken in lunch time.
							So if they auto-deduct 60mins, and an employee takes 30mins of lunch,
							it will deduct the remaining 30mins to equal 60mins. If they don't
							take any lunch, it deducts the full 60mins.
			Auto-Include:	It will include the amount taken in lunch time, up to the amount given.
							So if they auto-include 30mins and an employee takes a 60min lunch
							only 30mins will be included, and 30mins is automatically deducted
							as a regular lunch punch.
							If they don't take a lunch, it doesn't include any time.

		If not enabled for:
		  Auto-Deduct: Always deducts the amount.
		  Auto-Inlcyde: Always includes the amount.
	*/
	function getIncludeLunchPunchTime() {
		if ( isset($this->data['include_lunch_punch_time']) ) {
			return $this->fromBool( $this->data['include_lunch_punch_time'] );
		}

		return FALSE;
	}
	function setIncludeLunchPunchTime($bool) {
		$this->data['include_lunch_punch_time'] = $this->toBool($bool);

		return TRUE;
	}

	function Validate() {
		if ( $this->getDeleted() == TRUE ){
			//Check to make sure there are no hours using this meal policy.
			$udtlf = new UserDateTotalListFactory(); 
			$udtlf->getByMealPolicyId( $this->getId() );
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											('This meal policy is in use'));

			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getAutoDetectType() == FALSE ) {
			$this->setAutoDetectType( 10 );
		}
		return TRUE;
	}

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
						case 'type':
						case 'auto_detect_type':
							$function = 'get'.str_replace('_','',$variable);
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Meal Policy'), NULL, $this->getTable(), $this );
	}
}
?>
