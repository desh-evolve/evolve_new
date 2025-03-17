<?php

namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class UserEducationFactory  extends Factory{
    //put your code here
    
    
    	protected $table = 'user_education';
	protected $pk_sequence_name = 'user_education_id_seq'; //PK Sequence name
        
        
        
        function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
                    
                    case 'column':
				$retval = array(
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
        
        
        
         
        function getQualificationName() {
		if ( isset($this->data['qualification_name']) ) {
			return $this->data['qualification_name'];
		}

		return FALSE;
	}
        
        
        function setQualificationName($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('qualification_name', $value, ('Qualification name is too long'), 1, 250)) {
                    $this->data['qualification_name'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
        
       function getInstitute() {
		if ( isset($this->data['institute']) ) {
			return $this->data['institute'];
		}

		return FALSE;
	}
        
        
        function setInstitute($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('institute', $value, ('Institute name is too long'), 1, 250)) {
                    $this->data['institute'] = $value;
                    return FALSE;
		}

		return FALSE;
	} 
        
        
        
               

	function getYear() {
		if ( isset($this->data['year']) ) {
			return $this->data['year'];
		}

		return FALSE;
	}
        
        
	function setYear($year) {
			if ($value == '' OR $this->Validator->isLength('year', $value, ('Year  is too long'), 1, 20)) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['year'] =  $year ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
        
          
       function getRemarks() {
		if ( isset($this->data['remarks']) ) {
			return $this->data['remarks'];
		}

		return FALSE;
	}
        
        
        function setRemarks($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('remarks', $value, ('Remarks is too long'), 1, 250)) {
                    $this->data['remarks'] = $value;
                    return FALSE;
		}

		return FALSE;
	} 
        
    
}
