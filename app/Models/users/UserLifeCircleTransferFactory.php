<?php


namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class UserLifeCircleTransferFactory   extends Factory {
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
        
        
        function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

        
        
        
         
          
	function getCurrentDepartment() {
		if ( isset($this->data['current_department']) ) {
			return $this->data['current_department'];
		}

		return FALSE;
	}
        
        
	function setCurrentDepartment($id) {
		$id = trim($id);

		$ulf = new DepartmentListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'current_department',
															$ulf->getByID($id),
															('Invalid Department')
															) ) {
			$this->data['current_department'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        
           
          
	function getNewDepartment() {
		if ( isset($this->data['new_department']) ) {
			return $this->data['new_department'];
		}

		return FALSE;
	}
        
        
	function setNewDepartment($id) {
		$id = trim($id);

		$ulf = new DepartmentListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'new_department',
															$ulf->getByID($id),
															('Invalid New Department')
															) ) {
			$this->data['new_department'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
                

	function getTransferDate() {
		if ( isset($this->data['transfer_date']) ) {
			return $this->data['transfer_date'];
		}

		return FALSE;
	}
	function setTransferDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'transfer_date',
												$epoch,
												('Transfer date is invalid, try specifying the year with four digits.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['transfer_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
    
}
