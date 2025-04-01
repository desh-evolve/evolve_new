<?php

namespace App\Models\Core;

use App\Models\Company\CompanyListFactory;
use Illuminate\Support\Facades\DB;

class OtherFieldFactory extends Factory {
	protected $table = 'other_field';
	protected $pk_sequence_name = 'other_field_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											2  => ('Company'),
											4  => ('Branch'),
											5  => ('Department'),
											10  => ('Employee'),
											15  => ('Punch'),
											20  => ('Job'),
											30  => ('Task'),
											50  => ('Client'),
											55  => ('Client Contact'),
											//57  => ('Client Payment'),
											60  => ('Product'),
											70  => ('Invoice'),
											80  => ('Document'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => ('Type'),
										'-1020-other_id1' => ('Other ID1'),
										'-1020-other_id2' => ('Other ID2'),
										'-1020-other_id3' => ('Other ID3'),
										'-1020-other_id4' => ('Other ID4'),
										'-1020-other_id5' => ('Other ID5'),

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
								'type',
								'other_id1',
								'other_id2',
								'other_id3',
								'other_id4',
								'other_id5',
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
										'type_id' => 'Type',
										'type' => FALSE,
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'other_id6' => 'OtherID6',
										'other_id7' => 'OtherID7',
										'other_id8' => 'OtherID8',
										'other_id9' => 'OtherID9',
										'other_id10' => 'OtherID10',
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

	function isUniqueType($type) {
		$ph = array(
					':company_id' => (int)$this->getCompany(),
					':type_id' => (int)$type,
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = :company_id
						AND type_id = :type_id
						AND deleted = 0';
        $type_id = DB::select($query, $ph);

        $type_id = !empty($type_id) ? $type_id[0]->id : null;
        // if ($type_id === FALSE ) {
        //     $type_id = 0;
        // }else{
        //     $type_id = current(get_object_vars($type_id[0]));
        // }

        if (empty($type_id) || $type_id === FALSE ) {
            $type_id = 0;
        }else{
            $type_id = current(get_object_vars($type_id[0]));
        }


		Debug::Arr($type_id,'Unique Type: '. $type, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($type_id) || $type_id === FALSE ) {
			return TRUE;
		} else {
			if ( $type_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);
		Debug::text('Attempting to set Type To: '. $type , __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->Validator->inArrayKey(	'type_id',
											$type,
											('Incorrect Type'),
											$this->getOptions('type') )
					AND
						$this->Validator->isTrue(		'type_id',
														$this->isUniqueType($type),
														('Type already exists'))

											) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		return $this->data['other_id1'];
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											('Other ID1 is invalid'),
											1,255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		return $this->data['other_id2'];
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											('Other ID2 is invalid'),
											1,255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		return $this->data['other_id3'];
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											('Other ID3 is invalid'),
											1,255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		return $this->data['other_id4'];
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											('Other ID4 is invalid'),
											1,255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		return $this->data['other_id5'];
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											('Other ID5 is invalid'),
											1,255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID6() {
		return $this->data['other_id6'];
	}
	function setOtherID6($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id6',
											$value,
											('Other ID6 is invalid'),
											1,255) ) {

			$this->data['other_id6'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID7() {
		return $this->data['other_id7'];
	}
	function setOtherID7($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id7',
											$value,
											('Other ID7 is invalid'),
											1,255) ) {

			$this->data['other_id7'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID8() {
		return $this->data['other_id8'];
	}
	function setOtherID8($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id8',
											$value,
											('Other ID8 is invalid'),
											1,255) ) {

			$this->data['other_id8'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID9() {
		return $this->data['other_id9'];
	}
	function setOtherID9($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id9',
											$value,
											('Other ID9 is invalid'),
											1,255) ) {

			$this->data['other_id9'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID10() {
		return $this->data['other_id10'];
	}
	function setOtherID10($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id10',
											$value,
											('Other ID10 is invalid'),
											1,255) ) {

			$this->data['other_id10'] = $value;

			return TRUE;
		}

		return FALSE;
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
							$function = 'get'.$variable;
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
		return TTLog::addEntry( $this->getId(), $log_action,  ('Other Fields'), NULL, $this->getTable(), $this );
	}
}
?>
