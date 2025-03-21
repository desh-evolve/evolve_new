<?php

namespace App\Models\Other;

use App\Models\Core\Debug;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class ROEListFactory extends ROEFactory implements IteratorAggregate {

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

		$ph = array();

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = :company_id
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND a.deleted = 0 AND b.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getLastROEByUserId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'last_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		if ( $limit == NULL ) {
			$limit = 1;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}


		$ph = array(
					':id' => $id,
					':start_date' => $start_date,
					':end_date' => $end_date,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :id
						AND last_date >= :start_date
						AND last_date <= :end_date
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getIsModifiedByUserIdAndStartDateAndEndDateAndDate($user_id, $start_date, $end_date, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					':user_id' => $user_id,
					':start_date' => $start_date,
					':end_date' => $end_date,
					':created_date' => $date,
					':updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
							user_id = :user_id
						AND last_date >= :start_date
						AND last_date <= :end_date
						AND
							( created_date >= :created_date OR updated_date >= :updated_date )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('ROE rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}
		Debug::text('ROE rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

}
?>
