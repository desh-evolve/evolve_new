<?php

namespace App\Models\Policy;

use App\Models\Company\CompanyGenericMapFactory;
use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PolicyGroupListFactory extends PolicyGroupFactory implements IteratorAggregate {

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

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIds($ids, $where = NULL, $order = NULL) {
		if ( $ids == '') {
			return FALSE;
		}
/*
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
*/
		$pguf = new PolicyGroupUserFactory();

		$ph = array();

		$query = '
					select 	a.*,
							b.user_id as user_id
					from	'. $this->getTable() .' as a,
							'. $pguf->getTable() .' as b
					where 	a.id = b.policy_group_id
						AND b.user_id in  ('. $this->getListSQL($ids, $ph) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
	}

	function getByCompanyIdAndUserId($company_id, $user_ids, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_ids == '') {
			return FALSE;
		}
/*
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
*/
		$pguf = new PolicyGroupUserFactory();

		$ph = array( 'company_id' => $company_id );

		$query = '
					select 	a.*,
							b.user_id as user_id
					from	'. $this->getTable() .' as a,
							'. $pguf->getTable() .' as b
					where 	a.id = b.policy_group_id
						AND a.company_id = ? ';

		if ( $user_ids AND is_array($user_ids) AND isset($user_ids[0]) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .') ';
		}

		$query .= '	AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
	}

	function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array();
		if ( $order == NULL ) {
			//$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	distinct a.*
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $pguf->getTable() .' as b ON a.id = b.policy_group_id
						LEFT JOIN '. $cgmf->getTable() .' as c ON ( a.id = c.object_id AND c.company_id = a.company_id AND c.object_type_id = 130)
						LEFT JOIN '. $cgmf->getTable() .' as d ON ( a.id = d.object_id AND d.company_id = a.company_id AND d.object_type_id = 110)
						LEFT JOIN '. $cgmf->getTable() .' as e ON ( a.id = e.object_id AND e.company_id = a.company_id AND e.object_type_id = 120)
						LEFT JOIN '. $cgmf->getTable() .' as f ON ( a.id = f.object_id AND f.company_id = a.company_id AND f.object_type_id = 140)
					where	a.company_id = ?
					';

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. implode(',', $filter_data['id']) .') ';
		}
		if ( isset($filter_data['exception_policy_control_id']) AND isset($filter_data['exception_policy_control_id'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_control_id']) ) {
			$query  .=	' AND a.exception_policy_control_id in ('. $this->getListSQL($filter_data['exception_policy_control_id'], $ph) .') ';
		}
		if ( isset($filter_data['holiday_policy_id']) AND isset($filter_data['holiday_policy_id'][0]) AND !in_array(-1, (array)$filter_data['holiday_policy_id']) ) {
			$query  .=	' AND a.holiday_policy_id in ('. $this->getListSQL($filter_data['holiday_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_policy_id']) AND isset($filter_data['user_policy_id'][0]) AND !in_array(-1, (array)$filter_data['user_policy_id']) ) {
			$query  .=	' AND b.user_policy_id in ('. $this->getListSQL($filter_data['user_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['round_interval_policy_id']) AND isset($filter_data['round_interval_policy_id'][0]) AND !in_array(-1, (array)$filter_data['round_interval_policy_id']) ) {
			$query  .=	' AND c.map_id in ('. $this->getListSQL($filter_data['round_interval_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['over_time_policy_id']) AND isset($filter_data['over_time_policy_id'][0]) AND !in_array(-1, (array)$filter_data['over_time_policy_id']) ) {
			$query  .=	' AND d.map_id in ('. $this->getListSQL($filter_data['over_time_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['premium_policy_id']) AND isset($filter_data['premium_policy_id'][0]) AND !in_array(-1, (array)$filter_data['premium_policy_id']) ) {
			$query  .=	' AND e.map_id in ('. $this->getListSQL($filter_data['premium_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['accrual_policy_id']) AND isset($filter_data['accrual_policy_id'][0]) AND !in_array(-1, (array)$filter_data['accrual_policy_id']) ) {
			$query  .=	' AND f.map_id in ('. implode(',', $filter_data['accrual_policy_id']) .') ';
		}

		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}


	function getByCompanyIdArray($company_id, $include_blank = TRUE) {

		$pglf = new PolicyGroupListFactory();
		$pglf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($pglf as $pg_obj) {
			$list[$pg_obj->getID()] = $pg_obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getUserToPolicyGroupMapArrayByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$retarr[$obj->getColumn('user_id')] = $obj->getId();
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array( 'total_users' );

		$sort_column_aliases = array(
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	distinct a.*,
							(select count(*) from '. $pguf->getTable() .' as pguf_tmp where pguf_tmp.policy_group_id = a.id ) as total_users,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $pguf->getTable() .' as b ON a.id = b.policy_group_id
						LEFT JOIN '. $cgmf->getTable() .' as c ON ( a.id = c.object_id AND c.company_id = a.company_id AND c.object_type_id = 130)
						LEFT JOIN '. $cgmf->getTable() .' as d ON ( a.id = d.object_id AND d.company_id = a.company_id AND d.object_type_id = 110)
						LEFT JOIN '. $cgmf->getTable() .' as e ON ( a.id = e.object_id AND e.company_id = a.company_id AND e.object_type_id = 120)
						LEFT JOIN '. $cgmf->getTable() .' as f ON ( a.id = f.object_id AND f.company_id = a.company_id AND f.object_type_id = 140)
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. implode(',', $filter_data['permission_children_ids']) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. implode(',', $filter_data['id']) .') ';
		}
		if ( isset($filter_data['exception_policy_control']) AND isset($filter_data['exception_policy_control'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_control']) ) {
			$query  .=	' AND a.exception_policy_control_id in ('. $this->getListSQL($filter_data['exception_policy_control'], $ph) .') ';
		}
		if ( isset($filter_data['holiday_policy']) AND isset($filter_data['holiday_policy'][0]) AND !in_array(-1, (array)$filter_data['holiday_policy']) ) {
			$query  .=	' AND a.holiday_policy_id in ('. $this->getListSQL($filter_data['holiday_policy'], $ph) .') ';
		}
		if ( isset($filter_data['user']) AND isset($filter_data['user'][0]) AND !in_array(-1, (array)$filter_data['user']) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user'], $ph) .') ';
		}
		if ( isset($filter_data['round_interval_policy']) AND isset($filter_data['round_interval_policy'][0]) AND !in_array(-1, (array)$filter_data['round_interval_policy']) ) {
			$query  .=	' AND c.map_id in ('. $this->getListSQL($filter_data['round_interval_policy'], $ph) .') ';
		}
		if ( isset($filter_data['over_time_policy']) AND isset($filter_data['over_time_policy'][0]) AND !in_array(-1, (array)$filter_data['over_time_policy']) ) {
			$query  .=	' AND d.map_id in ('. $this->getListSQL($filter_data['over_time_policy'], $ph) .') ';
		}
		if ( isset($filter_data['premium_policy']) AND isset($filter_data['premium_policy'][0]) AND !in_array(-1, (array)$filter_data['premium_policy']) ) {
			$query  .=	' AND e.map_id in ('. $this->getListSQL($filter_data['premium_policy'], $ph) .') ';
		}
		if ( isset($filter_data['accrual_policy']) AND isset($filter_data['accrual_policy'][0]) AND !in_array(-1, (array)$filter_data['accrual_policy']) ) {
			$query  .=	' AND f.map_id in ('. $this->getListSQL($filter_data['accrual_policy'], $ph) .') ';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE ?';
		}
		if ( isset($filter_data['created_by']) AND isset($filter_data['created_by'][0]) AND !in_array(-1, (array)$filter_data['created_by']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['created_by'], $ph) .') ';
		}
		if ( isset($filter_data['updated_by']) AND isset($filter_data['updated_by'][0]) AND !in_array(-1, (array)$filter_data['updated_by']) ) {
			$query  .=	' AND a.updated_by in ('. $this->getListSQL($filter_data['updated_by'], $ph) .') ';
		}

		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

}
?>
