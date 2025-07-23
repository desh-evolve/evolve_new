<?php

namespace App\Models\Core;

use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class LogDetailListFactory extends LogDetailFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}
		$this->data = $this->rs;
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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$lf = new LogFactory();

		$ph = array(
					':id' => $id,
					':company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $lf->getTable() .' as lf on a.system_log_id = lf.id
						LEFT JOIN  '. $uf->getTable() .' as uf on lf.user_id = uf.id
					where	a.id = :id
						AND uf.company_id = :company_id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getBySystemLogIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.field' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$lf = new LogFactory();

		$ph = array(
					':id' => $id,
					':company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $lf->getTable() .' as lf on a.system_log_id = lf.id
						LEFT JOIN  '. $uf->getTable() .' as uf on lf.user_id = uf.id
					where	a.system_log_id = :id
						AND uf.company_id = :company_id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

}
?>
