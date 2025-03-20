<?php

namespace App\Models\PayStub;
use IteratorAggregate;

class PayStubMiddlePayListFactory extends PayStubMiddlePayFactory implements IteratorAggregate {
    //put your code here
    
    
    	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}

		return $this;
	}
        
        
        
        function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
        
        
        function getByPayPeriodsIdAndUserId($pay_period_id,$user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}
                
                if ( $pay_period_id == '') {
			return FALSE;
		}

		$strict = TRUE;
		if ( $order == NULL ) {
			$strict = FALSE;

			$order = array( 'a.pay_period_id' => 'asc', 'abs(a.amount)' => 'asc', 'a.id' => 'asc' );
		}


		$ph = 	array(
					':pay_period_id' => $pay_period_id,
                    ':user_id'=>$user_id,
				);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.pay_period_id = :pay_period_id
						AND a.user_id = :user_id
						AND a.deleted = 0
					';

               
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

        
        
}
