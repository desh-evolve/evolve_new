<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AllowanceFactory
 *
 * @author Thusitha
 */
class AllowanceFactory extends Factory {
	protected $table = 'allowance_data';
	protected $pk_sequence_name = 'allowance_data_id_seq'; //PK Sequence name

	var $user_obj = NULL;
        
        
        
        
        
        
        
	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory();
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj = $ulf->getCurrent();
				return $this->user_obj;
			}

			return FALSE;
		}
	}
	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

      
        
        	function getPayPeriodObject() {
		if ( is_object($this->pay_period_obj) ) {
			return $this->pay_period_obj;
		} else {
			$pplf = new PayPeriodListFactory();
			$this->pay_period_obj = $pplf->getById( $this->getPayPeriod() )->getCurrent();

			return $this->pay_period_obj;
		}
	}
        
        
        function getPayPeriod() {
		if ( isset($this->data['payperiod_id']) ) {
			return $this->data['payperiod_id'];
		}

		return FALSE;
	}
	function setPayPeriod($id = NULL) {
		$id = trim($id);

		if ( $id == NULL ) {
			$id = $this->findPayPeriod();
		}

		$pplf = new PayPeriodListFactory();

		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		if (
				$id == FALSE
				OR
				$this->Validator->isResultSetWithRows(	'payperiod_id',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Pay Period')
														) ) {
			$this->data['payperiod_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

        
        
	function getWorkedDays() {
		if ( isset($this->data['worked_days']) ) {
			return $this->data['worked_days'];
		}

		return FALSE;
	}
	function setWorkedDays($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'worked_days',
													$int,
													TTi18n::gettext('Incorrect Worked Days'))
				
				) {
			$this->data['worked_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}
     
        
                
	function getLateDays() {
		if ( isset($this->data['late_days']) ) {
			return $this->data['late_days'];
		}

		return FALSE;
	}
	function setLateDays($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'late_days',
													$int,
													TTi18n::gettext('Incorrect Late Days'))
				
				) {
			$this->data['late_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}
       
        
        function getNopayDays() {
		if ( isset($this->data['nopay_days']) ) {
			return $this->data['nopay_days'];
		}

		return FALSE;
	}
        
	function setNopayDays($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'nopay_days',
													$int,
													TTi18n::gettext('Incorrect nopay Days'))
				
				) {
			$this->data['nopay_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}
       
        
                
        function getFulldayLeaveDays() {
		if ( isset($this->data['fullday_leave_days']) ) {
			return $this->data['fullday_leave_days'];
		}

		return FALSE;
	}
        
	function setFulldayLeaveDays($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'fullday_leave_days',
													$int,
													TTi18n::gettext('Incorrect Fullday leave days'))
				
				) {
			$this->data['fullday_leave_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}
       
        
        
        function getHalfdayLeaveDays() {
		if ( isset($this->data['halfday_leave_days']) ) {
			return $this->data['halfday_leave_days'];
		}

		return FALSE;
	}
        
	function setHalfdayLeaveDays($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'halfday_leave_days',
													$int,
													TTi18n::gettext('Incorrect Fullday leave days'))
				
				) {
			$this->data['halfday_leave_days'] = $int;

			return TRUE;
		}

		return FALSE;
	}
       
}
