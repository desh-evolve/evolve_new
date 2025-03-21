<?php

namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use Illuminate\Support\Facades\DB;

class UserIdentificationFactory extends Factory {
	protected $table = 'user_identification';
	protected $pk_sequence_name = 'user_identification_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											5 	=> ('Password History'), //Web interface password history
											10 	=> ('iButton'),
											20	=> ('USB Fingerprint'),
											//25	=> ('LibFingerPrint'),
											30	=> ('Barcode'), //For barcode readers and USB proximity card readers.
											40	=> ('Proximity Card'), //Mainly for proximity cards on timeclocks.
											100	=> ('TimeClock FingerPrint'), //TimeClocks
									);
				break;

		}

		return $retval;
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

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

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
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	/*
		For fingerprints,
			10 = Fingerprint 1	Pass 0.
			11 = Fingerprint 1	Pass 1.
			12 = Fingerprint 1	Pass 2.

			20 = Fingerprint 2	Pass 0.
			21 = Fingerprint 2	Pass 1.
			...
	*/
	function getNumber() {
		if ( isset($this->data['number']) ) {
			return $this->data['number'];
		}

		return FALSE;
	}
	function setNumber($value) {
		$value = trim($value);

		//Pull out only digits
		$value = $this->Validator->stripNonNumeric($value);

		if (	$this->Validator->isFloat(	'number',
											$value,
											('Incorrect Number')) ) {

			$this->data['number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueValue($user_id, $type_id, $value) {
		$ph = array(
					':user_id' => (int)$user_id,
					':type_id' => (int)$type_id,
					':value' => (string)$value,
					);

		$uf = new UserFactory();

		$query = 'select a.id
					from '. $this->getTable() .' as a,
						'. $uf->getTable() .' as b
					where a.user_id = b.id
						AND b.company_id = ( select z.company_id from '. $uf->getTable() .' as z where z.id = :user_id and z.deleted = 0 )
						AND a.type_id = :type_id
						AND a.value = :value
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$id = DB::select($query, $ph);

		if ($id === FALSE ) {
            $id = 0;
        }else{
            $id = current(get_object_vars($id[0]));
        }
		//Debug::Arr($id,'Unique Value: '. $value, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
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

		if (
				$this->Validator->isLength(			'value',
													$value,
													('Value is too short or too long'),
													1,
													32000)
			) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExtraValue() {
		if ( isset($this->data['extra_value']) ) {
			return $this->data['extra_value'];
		}

		return FALSE;
	}
	function setExtraValue($value) {
		$value = trim($value);

		if (
				$this->Validator->isLength(			'extra_value',
													$value,
													('Extra Value is too long'),
													1,
													256000)
			) {

			$this->data['extra_value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getValue() == FALSE ) {
				$this->Validator->isTRUE(			'value',
													FALSE,
													('Value is not defined') );

		} else {
			$this->Validator->isTrue(		'value',
											$this->isUniqueValue( $this->getUser(), $this->getType(), $this->getValue() ),
											('Value is already in use, please enter a different one'));
		}
		return TRUE;
	}

	function preSave() {
		if (  $this->getNumber() == '' ) {
			$this->setNumber( 0 );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function addLog( $log_action ) {
		//Don't do detail logging for this, as it will store entire figerprints in the log table.
		return TTLog::addEntry( $this->getId(), $log_action, ('Employee Identification - Employee'). ': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. ('Type') . ': '. Option::getByKey($this->getType(), $this->getOptions('type') ) , NULL, $this->getTable() );
	}
}
?>
