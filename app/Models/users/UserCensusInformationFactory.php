<?php

namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class UserCensusInformationFactory  extends Factory {
    //put your code here
    
    protected $table = 'user_census';
    protected $pk_sequence_name = 'user_census_id_seq'; //PK Sequence name
    
    
    	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
                    
                    case 'gender':
				$retval = array(
						   5 => TTi18n::gettext('Unspecified'),
						   10 => TTi18n::gettext('Male'),
						   20 => TTi18n::gettext('Female'),
									);
                        
                        break;
                
                }
                
                return $retval;
        }
        
    
    
        
        
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
        
        
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
        function getDependant() {
		if ( isset($this->data['dependant']) ) {
			return $this->data['dependant'];
		}

		return FALSE;
	}
        
        
        function setDependant($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('dependant', $value, TTi18n::gettext('Dependant Name is too long'), 1, 2048)) {
                    $this->data['dependant'] = $value;
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
        
        
        function setName($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('name', $value, TTi18n::gettext('Name is too long'), 1, 250)) {
                    $this->data['name'] = $value;
                    return FALSE;
		}

		return FALSE;
	}      
        

        
        function getRelationship() {
		if ( isset($this->data['relationship']) ) {
			return $this->data['relationship'];
		}

		return FALSE;
	}
        
        
        function setRelationship($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('relationship', $value, TTi18n::gettext('Relationship is too long'), 1, 50)) {
                    $this->data['relationship'] = $value;
                    return FALSE;
		}

		return FALSE;
	}
        
        
        
             

	function getBirthDate() {
		if ( isset($this->data['dob']) ) {
			return $this->data['dob'];
		}

		return FALSE;
	}
	function setBirthDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'dob',
												$epoch,
												TTi18n::gettext('Birth date is invalid, try specifying the year with four digits.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['dob'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
        
        
        function getNic() {
		if ( isset($this->data['nic']) ) {
			return $this->data['nic'];
		}

		return FALSE;
	}
                        
	function setNic($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('nic', $value, TTi18n::gettext('NIC is too long'), 1, 2048)) {
                    $this->data['nic'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
        
        
        function getGender() {
		if ( isset($this->data['gender']) ) {
			return $this->data['gender'];
		}

		return FALSE;
	}
	function setGender($gender) {
		$gender = trim($gender);

		if ( $this->Validator->inArrayKey(	'gender',
											$gender,
											TTi18n::gettext('Invalid gender'),
											$this->getOptions('gender') ) ) {

			$this->data['gender'] = $gender;

			return TRUE;
		}

		return FALSE;
	}
        
}
