<?php

namespace App\Models\PayStub;
use App\Models\Core\Factory;

class PayStubMiddlePayFactory extends Factory {
    //put your code here
    
    protected $table = 'pay_stub_middle_pay';
   protected $pk_sequence_name = 'pay_stub_middle_pay_id_seq'; //PK Sequence name
   
   
   protected $pay_period_obj = NULL;
   protected $user_obj = NULL;
   
   
   function _getFactoryOptions( $name, $country = NULL ) {



		$retval = NULL;
                
   }
   
   
   
   function _getVariableToFunctionMap( $data ) {

		$variable_function_map = array(

										'id' => 'ID',

                                                                                'pay_period_id' => 'Pay Periods',
										'user_id' => 'User',
                                                                                'amount' => 'Amount',
                    
                    );
                
                return $variable_function_map;
   }
   
   
   
   
	function getPayPeriodObject() {

		if ( is_object($this->pay_period_obj) ) {

			return $this->pay_period_obj;

		} else {

			$pplf = TTnew( 'PayPeriodListFactory' );



			$pplf->getById( $this->getPayPeriod() );

			if ( $pplf->getRecordCount() > 0 ) {

				$this->pay_period_obj = $pplf->getCurrent();

				return $this->pay_period_obj;

			}

		}



		return FALSE;

	}


        
        
        	function getUserObject() {

		if ( is_object($this->user_obj) ) {

			return $this->user_obj;

		} else {

			$ulf = TTnew( 'UserListFactory' );

			$ulf->getById( $this->getUser() );

			if ( $ulf->getRecordCount() > 0 ) {

				$this->user_obj = $ulf->getCurrent();

				return $this->user_obj;

			}

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



		if ( $this->Validator->isResultSetWithRows(	'user',

															$ulf->getByID($id),

															TTi18n::gettext('Invalid User')

															) ) {

			$this->data['user_id'] = $id;



			return TRUE;

		}



		return FALSE;

	}

function getPayPeriod() {

		if ( isset($this->data['pay_period_id']) ) {

			return $this->data['pay_period_id'];

		}



		return FALSE;

	}

	function setPayPeriod($id) {

		$id = trim($id);



		$pplf = TTnew( 'PayPeriodListFactory' );



		if (  $this->Validator->isResultSetWithRows(	'pay_period',

														$pplf->getByID($id),

														TTi18n::gettext('Invalid Pay Period')

														) ) {

			$this->data['pay_period_id'] = $id;



			return TRUE;

		}



		return FALSE;

	}

	function getAmount() {

		if ( isset($this->data['amount']) ) {

			return $this->data['amount'];

		}

		return FALSE;

	}
        
        
       function setAmount($amount) {

		$amount = trim($amount);

			return $this->data['amount']=$amount;

		

		return TRUE;

	}
        
                    
}
