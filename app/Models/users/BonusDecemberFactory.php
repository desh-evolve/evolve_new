<?php

namespace App\Models\Users;

use App\Models\Company\CompanyListFactory;
use App\Models\Core\Factory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class BonusDecemberFactory  extends Factory{
    //put your code here
    
    	protected $table = 'bonus_december';
	protected $pk_sequence_name = 'bonus_december_id_seq'; //PK Sequence name
        
        
        
        
      	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory(); 

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
  
        
        
        function getYNumber() {
		if ( isset($this->data['y_number']) ) {
			return $this->data['y_number'];
		}

		return FALSE;
	}
        
	function setYNumber($ynumber) {
		$ynumber = trim($ynumber);

		//Pull out only digits and periods.
		$ynumber = $this->Validator->stripNonFloat($ynumber);

		if (
				$this->Validator->isNotNull('y_number',
											$ynumber,
											('Please specify a Y number'))
				AND
				$this->Validator->isFloat(	'y_number',
											$ynumber,
											('Incorrect Y number'))
				
				) {

			$this->data['y_number'] = $ynumber;

			return TRUE;
		}

		return FALSE;
	}

        
            

	function getStartDate() {
		if ( isset($this->data['start_date']) ) {
			return $this->data['start_date'];
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'start_date',
												$epoch,
												('Start date is invalid.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['start_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
                    

	function getEndDate() {
		if ( isset($this->data['end_date']) ) {
			return $this->data['end_date'];
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		if 	(	( $epoch !== FALSE AND $epoch == '' )
				OR $this->Validator->isDate(	'end_date',
												$epoch,
												('End date is invalid.')) ) {

			//Allow for negative epochs, for birthdates less than 1960's
			$this->data['end_date'] = ( $epoch != 0 AND $epoch != '' ) ? TTDate::getMiddleDayEpoch( $epoch ) : '' ; //Allow blank birthdate.

			return TRUE;
		}

		return FALSE;
	}

        
        
        
}
