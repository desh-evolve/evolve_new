<?php

namespace App\Models\Users;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class UserEducationListFactory extends UserEducationFactory  implements IteratorAggregate {
    //put your code here



    	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}

		return $this;
	}





        function getById($id) {
		if ( $id == '') {
			return FALSE;
		}


			$ph = array(
						':id' => $id,
						);

			$query = '
						select 	*
						from 	'. $this->getTable() .'
						where	id = :id
							AND deleted = 0';

			$this->rs = DB::select($query, $ph);



		return $this;
	}




      function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();


		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b

					where 	a.user_id = b.id
						AND b.company_id = :company_id
						AND a.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}


}
