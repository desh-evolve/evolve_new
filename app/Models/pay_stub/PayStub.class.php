<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: PayStub.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Module_Pay_Stub
 */
class PayStub extends PayStubFactory {
	protected $tmp_data = NULL;

	function childConstruct() {
		$this->StartTransaction();

		return TRUE;
	}


	function Done() {
		Debug::Arr($this->tmp_data, 'Pay Stub TMP Data: ' , __FILE__, __LINE__, __METHOD__,10);
		//Call pre-save() first, so calculates the totals.
		$this->setEnableCalcTotal(TRUE);
		$this->preSave();

		if ( $this->Validate() ) {
			$this->CommitTransaction();
			//$this->FailTransaction();
			return TRUE;
		}

		$this->FailTransaction(); //Fails Transaction
		$this->CommitTransaction(); //Rollback occurs here. This is important when looping over many employees that may have a pay stub that fails.

		return FALSE;
	}
}
?>
