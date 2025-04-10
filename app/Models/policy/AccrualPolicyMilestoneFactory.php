<?php

namespace App\Models\Policy;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class AccrualPolicyMilestoneFactory extends Factory {
	protected $table = 'accrual_policy_milestone';
	protected $pk_sequence_name = 'accrual_policy_milestone_id_seq'; //PK Sequence name

	protected $accrual_policy_obj = NULL;

	protected $length_of_service_multiplier = array(
										0  => 0,
										10 => 1,
										20 => 7,
										30 => 30.4167,
										40 => 365.25,
										50 => 0.04166666666666666667, // 1/24th of a day.
									);

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'length_of_service_unit':
				$retval = array(
										10 => ('Day(s)'),
										20 => ('Week(s)'),
										30 => ('Month(s)'),
										40 => ('Year(s)'),
										50 => ('Hour(s)'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-length_of_service' => ('Length Of Service'),
										'-1020-length_of_service_unit' => ('Units'),
										'-1030-accrual_rate' => ('Accrual Rate'),
										'-1050-maximum_time' => ('Maximum Time'),
										'-1050-rollover_time' => ('Rollover Time'),

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
								'length_of_service',
								'length_of_service_unit',
								'accrual_rate',
								'maximum_time',
								'rollover_time',
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
											'accrual_policy_id' => 'AccrualPolicy',
											'length_of_service_days' => 'LengthOfServiceDays',
											'length_of_service' => 'LengthOfService',
											'length_of_service_unit_id' => 'LengthOfServiceUnit',
											//'length_of_service_unit' => FALSE,
											'accrual_rate' => 'AccrualRate',
											'maximum_time' => 'MaximumTime',
											'minimum_time' => 'MinimumTime',
											'rollover_time' => 'RolloverTime',
											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getAccrualPolicyObject() {
		if ( is_object($this->accrual_policy_obj) ) {
			return $this->accrual_policy_obj;
		} else {
			$aplf = new AccrualPolicyListFactory();
			$aplf->getById( $this->getAccrualPolicyID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->accrual_policy_obj = $aplf->getCurrent();
				return $this->accrual_policy_obj;
			}

			return FALSE;
		}
	}

	function getAccrualPolicy() {
		if ( isset($this->data['accrual_policy_id']) ) {
			return $this->data['accrual_policy_id'];
		}

		return FALSE;
	}
	function setAccrualPolicy($id) {
		$id = trim($id);

		$aplf = new AccrualPolicyListFactory();

		if ( $this->Validator->isResultSetWithRows(	'accrual_policy',
													$aplf->getByID($id),
													('Accrual Policy is invalid')
													) ) {

			$this->data['accrual_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getLengthOfServiceDays() {
		if ( isset($this->data['length_of_service_days']) ) {
			return (int)$this->data['length_of_service_days'];
		}

		return FALSE;
	}
	function setLengthOfServiceDays($int) {
		$int = (int)trim($int);

		Debug::text('aLength of Service Days: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'length_of_service'.$this->getLabelID(),
													$int,
													('Length of service is invalid')) ) {

			$this->data['length_of_service_days'] = bcmul( $int, $this->length_of_service_multiplier[$this->getLengthOfServiceUnit()], 4);

			return TRUE;
		}

		return FALSE;
	}

	function getLengthOfService() {
		if ( isset($this->data['length_of_service']) ) {
			return (int)$this->data['length_of_service'];
		}

		return FALSE;
	}
	function setLengthOfService($int) {
		$int = (int)trim($int);

		Debug::text('bLength of Service: '. $int, __FILE__, __LINE__, __METHOD__, 10);

		if 	(	$int >= 0
				AND
				$this->Validator->isFloat(			'length_of_service'.$this->getLabelID(),
													$int,
													('Length of service is invalid')) ) {

			$this->data['length_of_service'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getLengthOfServiceUnit() {
		if ( isset($this->data['length_of_service_unit_id']) ) {
			return $this->data['length_of_service_unit_id'];
		}

		return FALSE;
	}
	function setLengthOfServiceUnit($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('length_of_service_unit') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'length_of_service_unit_id'.$this->getLabelID(),
											$value,
											('Incorrect Length of service unit'),
											$this->getOptions('length_of_service_unit')) ) {

			$this->data['length_of_service_unit_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getAccrualRate() {
		if ( isset($this->data['accrual_rate']) ) {
			return $this->data['accrual_rate'];
		}

		return FALSE;
	}
	function setAccrualRate($int) {
		$int = trim($int);

		if 	(	$int > 0
				AND
				$this->Validator->isNumeric(		'accrual_rate'.$this->getLabelID(),
													$int,
													('Incorrect Accrual Rate')) ) {
			$this->data['accrual_rate'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMaximumTime() {
		if ( isset($this->data['maximum_time']) ) {
			return (int)$this->data['maximum_time'];
		}

		return FALSE;
	}
	function setMaximumTime($int) {
		$int = trim($int);

		if 	(	$int == 0
				OR
				$this->Validator->isNumeric(		'maximum_time'.$this->getLabelID(),
													$int,
													('Incorrect Maximum Time')) ) {
			$this->data['maximum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMinimumTime() {
		if ( isset($this->data['minimum_time']) ) {
			return (int)$this->data['minimum_time'];
		}

		return FALSE;
	}
	function setMinimumTime($int) {
		$int = trim($int);

		if 	(	$int == 0
				OR
				$this->Validator->isNumeric(		'minimum_time'.$this->getLabelID(),
													$int,
													('Incorrect Minimum Time')) ) {
			$this->data['minimum_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getRolloverTime() {
		if ( isset($this->data['rollover_time']) ) {
			return (int)$this->data['rollover_time'];
		}

		return FALSE;
	}
	function setRolloverTime($int) {
		$int = trim($int);

		if 	(	$int == 0
				OR
				$this->Validator->isNumeric(		'rollover_time'.$this->getLabelID(),
													$int,
													('Incorrect Rollover Time')) ) {
			$this->data['rollover_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function preSave() {
		//Set Length of service in days.
		$this->setLengthOfServiceDays( $this->getLengthOfService() );

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
						/*
						 //This is not displayed anywhere that needs it in text rather then from the options.
						case 'length_of_service_unit':
							//$function = 'getLengthOfServiceUnit';
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->getLengthOfServiceUnit(), $this->getOptions( $variable ) );
							}
							break;
						*/
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
		return TTLog::addEntry( $this->getAccrualPolicy(), $log_action,  ('Accrual Policy Milestone') .' (ID: '. $this->getID() .')' , NULL, $this->getTable(), $this );
	}
}
?>
