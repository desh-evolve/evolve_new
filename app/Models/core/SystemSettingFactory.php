<?php

namespace App\Models\Core;
use Illuminate\Support\Facades\Log;

class SystemSettingFactory extends Factory {
	protected $table = 'system_setting';
	protected $pk_sequence_name = 'system_setting_id_seq'; //PK Sequence name
	function isUniqueName($name) {
		$ph = array(
					':name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where name = :name';
		$name_id = DB::select($query, $ph);

		if (empty($name_id) || $name_id === FALSE ) {
            $name_id = 0;
        }else{
            $name_id = current(get_object_vars($name_id[0]));
        }
		Debug::Arr($name_id,'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( empty($name_id) || $name_id === FALSE ) {
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
	function setName($value) {
		$value = trim($value);
		if (	$this->Validator->isLength(	'name',
											$value,
											('Name is too short or too long'),
											1,250)
				AND
						$this->Validator->isTrue(		'name',
														$this->isUniqueName($value),
														('Name already exists')
														)

						) {

			$this->data['name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getValue() {
		if ( isset($this->data['value']) ) {
			return $this->data['value'];
		}

		return FALSE;
	}
	function setValue($value) {
		$value = trim($value);
		if (	$this->Validator->isLength(	'value',
											$value,
											('Value is too short or too long'),
											1,4096)
						) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( 'all' );

		return TRUE;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  ('System Setting - Name').': '. $this->getName() .' '. ('Value').': '. $this->getValue(), NULL, $this->getTable() );
	}
}
?>
