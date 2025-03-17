<?php

namespace App\Models\Policy;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;

class RoundIntervalPolicyFactory extends Factory {
	protected $table = 'round_interval_policy';
	protected $pk_sequence_name = 'round_interval_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	//Just need relations for each actual Punch Type


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'round_type':
				$retval = array(
										10 => ('Down'),
										20 => ('Average'),
										30 => ('Up')
									);
				break;

			case 'punch_type':
				$retval = array(
										10 => ('All Punches'),
										20 => ('All In (incl. Lunch)'),
										30 => ('All Out (incl. Lunch)'),
										40 => ('In'),
										50 => ('Out'),
										60 => ('Lunch - In'),
										70 => ('Lunch - Out'),
										80 => ('Break - In'),
										90 => ('Break - Out'),
										100 => ('Lunch Total'),
										110 => ('Break Total'),
										120 => ('Day Total'),
									);
				break;
			case 'punch_type_relation':
				$retval = array(
										40 => array(10,20),
										50 => array(10,30,120),
										60 => array(10,20,100),
										70 => array(10,30),
										80 => array(10,20,110),
										90 => array(10,30),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-punch_type' => ('Punch Type'),
										'-1020-round_type' => ('Round Type'),
										'-1030-name' => ('Name'),
										'-1030-round_interval' => ('Interval'),

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
								'punch_type',
								'round_type',
								'name',
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
										'round_type_id' => 'RoundType',
										'round_type' => FALSE,
										'punch_type_id' => 'PunchType',
										'punch_type' => FALSE,
										'round_interval' => 'Interval',
										'grace' => 'Grace',
										'strict' => 'Strict',
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

	function getPunchTypeFromPunchStatusAndType($status, $type) {
		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		switch($type) {
			case 10: //Normal
				if ( $status == 10 ) { //In
					$punch_type = 40;
				} else {
					$punch_type = 50;
				}
				break;
			case 20: //Lunch
				if ( $status == 10 ) { //In
					$punch_type = 60;
				} else {
					$punch_type = 70;
				}
				break;
			case 30: //Break
				if ( $status == 10 ) { //In
					$punch_type = 80;
				} else {
					$punch_type = 90;
				}
				break;
		}

		return $punch_type;
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

	function getRoundType() {
		if ( isset($this->data['round_type_id']) ) {
			return $this->data['round_type_id'];
		}

		return FALSE;
	}
	function setRoundType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('round_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'round_type',
											$value,
											('Incorrect Round Type'),
											$this->getOptions('round_type')) ) {

			$this->data['round_type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getPunchType() {
		if ( isset($this->data['punch_type_id']) ) {
			return $this->data['punch_type_id'];
		}

		return FALSE;
	}
	function setPunchType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('punch_type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'punch_type',
											$value,
											('Incorrect Punch Type'),
											$this->getOptions('punch_type')) ) {

			$this->data['punch_type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getInterval() {
		if ( isset($this->data['round_interval']) ) {
			return $this->data['round_interval'];
		}

		return FALSE;
	}
	function setInterval($value) {
		$value = trim($value);

		if 	(	$this->Validator->isNumeric(		'interval',
													$value,
													('Incorrect Interval')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['round_interval'] = $value;
			$this->data['round_interval'] = TTDate::roundTime($value, 60, 20);


			return TRUE;
		}

		return FALSE;
	}

	function getGrace() {
		if ( isset($this->data['grace']) ) {
			return $this->data['grace'];
		}

		return FALSE;
	}
	function setGrace($value) {
		$value = trim($value);

		if 	(	$this->Validator->isNumeric(		'grace',
													$value,
													('Incorrect grace value')) ) {

			//If someone is using hour parse format ie: 0.12 we need to round to the nearest
			//minute other wise it'll be like 7mins and 23seconds messing up rounding.
			//$this->data['grace'] = $value;
			$this->data['grace'] = TTDate::roundTime($value, 60, 20);

			return TRUE;
		}

		return FALSE;
	}

	function getStrict() {
		return $this->fromBool( $this->data['strict'] );
	}
	function setStrict($bool) {
		$this->data['strict'] = $this->toBool($bool);

		return true;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
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
						case 'punch_type':
						case 'round_type':
							$function = 'get'.str_replace('_', '', $variable);
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Round Interval Policy'), NULL, $this->getTable(), $this );
	}
}
?>
