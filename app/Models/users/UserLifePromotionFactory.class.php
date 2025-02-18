<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserLifePromotionFactory
 *
 * @author Thusitha
 */
class UserLifePromotionFactory  extends Factory {
    //put your code here
    
    protected $table = 'user_life_circle';
    protected $pk_sequence_name = 'user_life_circle_id_seq'; //PK Sequence name
    
    
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
        
        
        
          
           
         
        function getCurrentDesignation() {
		if ( isset($this->data['current_designation']) ) {
			return $this->data['current_designation'];
		}

		return FALSE;
	}
        
        
        function setCurrentDesignation($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('current_designation', $value, TTi18n::gettext('Current designation is too long'), 1, 250)) {
                    $this->data['current_designation'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
    
        
       
           
        function getNewDesignation() {
		if ( isset($this->data['new_designation']) ) {
			return $this->data['new_designation'];
		}

		return FALSE;
	}
        
        
        function setNewDesignation($value) {
		$value = trim($value);

		if ($value == '' OR $this->Validator->isLength('new_designation', $value, TTi18n::gettext('Current designation is too long'), 1, 250)) {
                    $this->data['new_designation'] = $value;
                    return FALSE;
		}

		return FALSE;
	}  
        
        
       
	function getCurrentSalary() {
		if ( isset($this->data['current_salary']) ) {
			return $this->data['current_salary'];
		}

		return FALSE;
	}
	function setCurrentSalary($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('current_salary',
											$wage,
											TTi18n::gettext('Please specify a Current Salary'))
				AND
				$this->Validator->isFloat(	'current_salary',
											$wage,
											TTi18n::gettext('Incorrect Current Salary'))
				AND
				$this->Validator->isLength(	'current_salary',
											$wage,
											TTi18n::gettext('Current Salary has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'current_salary',
											$wage,
											TTi18n::gettext('Current Salary has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'current_salary',
											$wage,
											TTi18n::gettext('Current Salary has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['current_salary'] = $wage;

			return TRUE;
		}

		return FALSE;
	}

    
       
        
        
         
       
	function getNewSalary() {
		if ( isset($this->data['new_salary']) ) {
			return $this->data['new_salary'];
		}

		return FALSE;
	}
	function setNewSalary($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('new_salary',
											$wage,
											TTi18n::gettext('Please specify a new salary'))
				AND
				$this->Validator->isFloat(	'new_salary',
											$wage,
											TTi18n::gettext('Incorrect new salary'))
				AND
				$this->Validator->isLength(	'new_salary',
											$wage,
											TTi18n::gettext('new salary has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'new_salary',
											$wage,
											TTi18n::gettext('new salary has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'new_salary',
											$wage,
											TTi18n::gettext('new salary has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['new_salary'] = $wage;

			return TRUE;
		}

		return FALSE;
	}

        
        
        
        
	function getEffectiveDate() {
		if ( isset($this->data['effective_date']) ) {
			return $this->data['effective_date'];
		}

		return FALSE;
	}
        
        
	function setEffectiveDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'effective_date',
												$epoch,
												TTi18n::gettext('Effective date is invalid.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['effective_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}
        
        
        
}
