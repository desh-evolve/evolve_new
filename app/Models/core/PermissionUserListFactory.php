<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PermissionUserListFactory extends PermissionUserFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable();
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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndNotPermissionControlId($company_id, $user_id, $permission_control_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		$pcf = new PermissionControlFactory();

		$ph = array(
					':company_id' => $company_id,
					':permission_control_id' => $permission_control_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.permission_control_id = b.id
						AND b.company_id = :company_id
						AND a.permission_control_id != :permission_control_id
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND b.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPermissionControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where 	a.permission_control_id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPermissionControlIdAndUserID($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					':user_id' => $user_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.permission_control_id = :id
						AND a.user_id = :user_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}


	function getByPermissionControlIdArray($id) {
		$pculf = new PermissionControlUserListFactory();

		$pculf->getByPayPermissionControlId($id);

		foreach ($pculf->rs as $user) {
			$pculf->data = (array)$user;
			$user_list[$pculf->getUser()] = NULL;
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return array();
	}
}
?>
