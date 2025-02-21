<?php

namespace App\Models\Company;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class AllowanceListFactory  extends AllowanceFactory implements IteratorAggregate {

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

	function getByUserId($user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
			':user_id' => $user_id,
		);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :user_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

        
    function getByUserIdAndPayperiodsId($user_id,$payperiod_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
			':user_id' => $user_id,
			':payperiod_id'=>$payperiod_id,
		);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :user_id
                        AND payperiod_id = :payperiod_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
}
