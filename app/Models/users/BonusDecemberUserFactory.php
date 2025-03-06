<?php

namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class BonusDecemberUserFactory  extends Factory {
    //put your code here
    
    	protected $table = 'bonus_december_user';
	protected $pk_sequence_name = 'bonus_december_user_id_seq'; //PK Sequence name
        
        
        var $user_obj = NULL;
        var $bonus_december_obj = NULL;
        
      
        
        function getBonusDecemberObject() {
		if ( is_object($this->bonus_december_obj) ) {
			return $this->bonus_december_obj;
		} else {
			$bdlf = TTnew( 'BonusDecemberListFactory' );
			$bdlf->getById( $this->getBonusDecember() );
			if ( $bdlf->getRecordCount() == 1 ) {
				$this->bonus_december_obj = $bdlf->getCurrent();
				return $this->bonus_december_obj;
			}

			return FALSE;
		}
	}

        
        
        
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

     
              
        function getBonusDecember() {
		if ( isset($this->data['bonus_december_id']) ) {
			return $this->data['bonus_december_id'];
		}

		return FALSE;
	}
          
	function setBonusDecember($id) {
		$id = trim($id);

		$bdlf = TTnew( 'BonusDecemberListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows( 'bonus',
															$bdlf->getByID($id),
															('Invalid December Bonus')
															) ) {
			$this->data['bonus_december_id'] = $id;

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
        
        
        function getWage() {
		if ( isset($this->data['wage']) ) {
			return $this->data['wage'];
		}

		return FALSE;
	}
	function setWage($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('wage',
											$wage,
											('Please specify a wage'))
				AND
				$this->Validator->isFloat(	'wage',
											$wage,
											('Incorrect Wage'))
				AND
				$this->Validator->isLength(	'wage',
											$wage,
											('Wage has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'wage',
											$wage,
											('Wage has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'wage',
											$wage,
											('Wage has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['wage'] = $wage;

			return TRUE;
		}

		return FALSE;
	}
        
        
        function getServicePeriods() {
		if ( isset($this->data['service_periods']) ) {
			return (int)$this->data['service_periods'];
		}

		return FALSE;
	}
	function setServicePeriods($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		if (	$value == ''
				OR
				(
				$this->Validator->isNumeric(	'service_periods',
												$value,
												('service periods must only be digits'))
				
				) ) {
			$this->data['service_periods'] = $value;

			return TRUE;
		}

		return FALSE;
	}
        
        
        function getKppMark() {
		if ( isset($this->data['kpp_mark']) ) {
			return $this->data['kpp_mark'];
		}

		return FALSE;
	}
	function setKppMark($kppmarks) {
		$kppmarks = trim($kppmarks);

		//Pull out only digits and periods.
		$kppmarks = $this->Validator->stripNonFloat($kppmarks);

		if (
				
				$this->Validator->isFloat(	'kpp_mark',
											$kppmarks,
											('Incorrect KPP'))
				
				
				) {

			$this->data['kpp_mark'] = $kppmarks;

			return TRUE;
		}

		return FALSE;
	}
        
        
                
        function getBonusAmount() {
		if ( isset($this->data['bonus_amount']) ) {
			return $this->data['bonus_amount'];
		}

		return FALSE;
	}
	function setBonusAmount($wage) {
		$wage = trim($wage);

		//Pull out only digits and periods.
		$wage = $this->Validator->stripNonFloat($wage);

		if (
				$this->Validator->isNotNull('bonus_amount',
											$wage,
											('Please specify a Bonus'))
				AND
				$this->Validator->isFloat(	'bonus_amount',
											$wage,
											('Incorrect Bonus'))
				AND
				$this->Validator->isLength(	'bonus_amount',
											$wage,
											('Bonus has too many digits'),
											0,
											21) //Need to include decimal.
				AND
				$this->Validator->isLengthBeforeDecimal(	'bonus_amount',
											$wage,
											('Bonus has too many digits before the decimal'),
											0,
											16)
				AND
				$this->Validator->isLengthAfterDecimal(	'bonus_amount',
											$wage,
											('Bonus has too many digits after the decimal'),
											0,
											4)
				) {

			$this->data['bonus_amount'] = $wage;

			return TRUE;
		}

		return FALSE;
	}
        
        


        
}
