<?php

namespace App\Models\PayStub;

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
