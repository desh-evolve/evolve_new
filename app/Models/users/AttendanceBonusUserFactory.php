<?php


namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class AttendanceBonusUserFactory    extends Factory{
    //put your code here
    
            
    	protected $table = 'bonus_attendance_user';
	protected $pk_sequence_name = 'bonus_attendance_user_id_seq'; //PK Sequence name
        
        var $bonus_attendance_obj = NULL;
        var $user_obj = NULL;
        
        
         
       function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
   
        
        
     function getBonusAttendanceObject() {
		if ( is_object($this->$bonus_attendance_obj) ) {
			return $this->$bonus_attendance_obj;
		} else {
			$balf = TTnew( 'AttendanceBonusListFactory' );
			$balf->getById( $this->getBonusAttendance() );
			if ( $bdlf->getRecordCount() == 1 ) {
				$this->$bonus_attendance_obj = $balf->getCurrent();
				return $this->$bonus_attendance_obj;
			}

			return FALSE;
		}
	}
        
        
        
            
      
        function getBonusAttendance() {
		if ( isset($this->data['bonus_attendance_id']) ) {
			return $this->data['bonus_attendance_id'];
		}

		return FALSE;
	}
          
	function setBonusAttendance($id) {
		$id = trim($id);

		$ablf = TTnew( 'AttendanceBonusListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows( 'bonus',
										$ablf->getByID($id),
										('Invalid Attendance Bonus')
									) ) {
			$this->data['bonus_attendance_id'] = $id;

			return TRUE;
		}

		return FALSE;
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
															('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
        
        
                
        function getNopay() {
		if ( isset($this->data['nopay']) ) {
			return $this->data['nopay'];
		}

		return FALSE;
	}
	function setNopay($nopay) {
		$nopay = trim($nopay);

		//Pull out only digits and periods.
		$nopay = $this->Validator->stripNonFloat($nopay);

		if (
				
				$this->Validator->isFloat(	'nopay',
											$nopay,
											('Incorrect Nopay'))
				
				
				) {

			$this->data['nopay'] = $nopay;

			return TRUE;
		}

		return FALSE;
	}
        
        
      
        function getLeaveBalance() {
		if ( isset($this->data['leave_balance']) ) {
			return $this->data['leave_balance'];
		}

		return FALSE;
	}
        
        
	function setLeaveBalance($leave_balance) {
		$leave_balance = trim($leave_balance);

		//Pull out only digits and periods.
		$leave_balance = $this->Validator->stripNonFloat($leave_balance);

		if (
				
				$this->Validator->isFloat(	'leave_balance',
											$leave_balance,
											('Incorrect Leave Balance'))
				
				
				) {

			$this->data['leave_balance'] = $leave_balance;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        
                        
        function getBonusAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}
        
        
	function setBonusAmount($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('amount',
											$wage,
											('Please specify a Bonus'))
				AND
				$this->Validator->isFloat(	'amount',
											$wage,
											('Incorrect Bonus'))
				AND
				$this->Validator->isLength(	'amount',
											$wage,
											('Bonus has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'amount',
											$wage,
											('Bonus has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'amount',
											$wage,
											('Bonus has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['amount'] = $wage;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
}
