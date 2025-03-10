<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PermissionListFactory extends PermissionFactory implements IteratorAggregate {
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
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndPermissionControlId($company_id, $permission_control_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'permission_control_id' => $permission_control_id,
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where 	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndPermissionControlIdAndSectionAndName($company_id, $permission_control_id, $section, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'permission_control_id' => $permission_control_id,
					'section' => $section,
					//'name' => $name, //Allow a list of names.
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where 	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND a.section = ?
						AND a.name in ('. $this->getListSQL($name, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getAllPermissionsByCompanyIdAndUserId($company_id, $user_id) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$query = '
					select  a.*,
							b.level as level
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $puf->getTable() .' as c
					where b.id = a.permission_control_id
						AND b.id = c.permission_control_id
						AND b.company_id = ?
						AND	c.user_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

/*
	function getByUserIdAndSectionAndName($user_id,$section, $name, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'section' => $section,
					'name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND section = ?
						AND name = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndSectionAndName($company_id,$user_id,$section, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'section' => $section,
					'name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	company_id = ?
						AND user_id = ?
						AND section = ?
						AND name = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserId($company_id,$user_id,$where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	company_id = ?
						AND user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getBySectionAndNameAndUserIdAndCompanyId($section, $name, $user_id, $company_id) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'section' => $section,
					'name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND	user_id in (-1, ? )
						AND section = ?
						AND name = ?
						AND deleted = 0
					ORDER BY company_id DESC, user_id DESC
					LIMIT 1';

		Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,9);

		$this->rs = DB::select($query, $ph);

		return $this;
	}
*/
}
?>
