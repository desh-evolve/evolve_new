<?php

namespace App\Models\Department;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class DepartmentBranchListFactory extends DepartmentBranchFactory implements IteratorAggregate {

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

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
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

		return $this;
	}

	function getByBranchId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	branch_id = :id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndBranchId($id, $branch_id, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $branch_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':branch_id' => $branch_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	branch_id = :branch_id
						AND	id = :id
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByDepartmentId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	department_id = :id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndDepartmentId($id, $department_id, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $department_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':department_id' => $department_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	department_id = :department_id
						AND	id = :id
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByDepartmentIdAndBranchId($department_id, $branch_id, $order = NULL) {
		if ( $department_id == '' ) {
			return FALSE;
		}

		if ( $branch_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':department_id' => $department_id,
					':branch_id' => $branch_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	department_id = :department_id
						AND	branch_id = :branch_id
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

/*
	function getByBranchIdArray($branch_id) {

		$blf = new BranchListFactory();
		$blf->getByCompanyId($company_id);

		$branch_list[0] = '--';

		foreach ($blf->rs as $branch) {
			$blf->data = (array) $branch;
			$branch = $blf;
			$branch_list[$branch->getID()] = $branch->getName();
		}

		return $branch_list;
	}
*/
}
?>
