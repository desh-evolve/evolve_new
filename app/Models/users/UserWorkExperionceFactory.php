<?php

namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class UserWorkExperionceFactory extends Factory {
    	protected $table = 'user_work_experionce';
	protected $pk_sequence_name = 'user_work_experionce_id_seq'; //PK Sequence name
        
        
        
        
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
        
        
        
        
           
         
        function getCompanyName() {
		if ( isset($this->data['company_name']) ) {
			return $this->data['company_name'];
		}

		return FALSE;
	}
        
        
        function setCompanyName($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('company_name', $value, TTi18n::gettext('Company name is too long'), 1, 250)) {
                    $this->data['company_name'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
        
                      

	function getFromDate() {
		if ( isset($this->data['from_date']) ) {
			return $this->data['from_date'];
		}

		return FALSE;
	}
        
        
	function setFromDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'from_date',
												$epoch,
												TTi18n::gettext('From date is invalid.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['from_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        
                            

	function getToDate() {
		if ( isset($this->data['to_date']) ) {
			return $this->data['to_date'];
		}

		return FALSE;
	}
        
        
	function setToDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'to_date',
												$epoch,
												TTi18n::gettext('To date is invalid.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['to_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}
        
        
             
         
        function getDepartment() {
		if ( isset($this->data['department']) ) {
			return $this->data['department'];
		}

		return FALSE;
	}
        
        
        function setDepartment($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('department', $value, TTi18n::gettext('Department is too long'), 1, 250)) {
                    $this->data['department'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
         
        
        
              
         
        function getDesignation() {
		if ( isset($this->data['designation']) ) {
			return $this->data['designation'];
		}

		return FALSE;
	}
        
        
        function setDesignation($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('designation', $value, TTi18n::gettext('Designation is too long'), 1, 250)) {
                    $this->data['designation'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
        
        
             
       function getRemarks() {
		if ( isset($this->data['remaks']) ) {
			return $this->data['remaks'];
		}

		return FALSE;
	}
        
        
        function setRemarks($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('remaks', $value, TTi18n::gettext('Remarks is too long'), 1, 250)) {
                    $this->data['remaks'] = $value;
                    return FALSE;
		}

		return FALSE;
	} 
        
        
        
}

