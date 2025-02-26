<?php


namespace App\Models\Users;
use App\Models\Core\Factory;
use App\Models\Core\TTi18n;

class AttendanceBonusFactory   extends Factory{
    //put your code here
    
    
        
    	protected $table = 'bonus_attendance';
	protected $pk_sequence_name = 'bonus_attendance_id_seq'; //PK Sequence name
        
         var $bonus_december_obj = NULL;
         var $company_obj= NULL;
         
         
               
        
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
										TTi18n::gettext('Invalid December Bonus')
									) ) {
			$this->data['bonus_december_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
          
        
        
       	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getById( $this->getCompany() );
			if ( $clf->getRecordCount() == 1 ) {
				$this->company_obj = $clf->getCurrent();

				return $this->company_obj;
			}

			return FALSE;
		}
	}
 
                
      	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
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
		$kppmarks = trim($year);

		//Pull out only digits and periods.
		

		if ( $year!='') {

			$this->data['year'] = $year;

			return TRUE;
		}

		return FALSE;
	}
        
        
        
        
}
