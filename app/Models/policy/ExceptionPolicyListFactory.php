<?php

namespace App\Models\Policy;

use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class ExceptionPolicyListFactory extends ExceptionPolicyFactory implements IteratorAggregate {

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
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $epcf->getTable() .' as b
					where
						a.exception_policy_control_id = b.id
						AND b.company_id = ?
						AND a.id = ?
						AND ( a.deleted = 0 AND b.deleted=0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $epcf->getTable() .' as b
					where
						a.exception_policy_control_id = b.id
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted=0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByExceptionPolicyControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
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
					where	exception_policy_control_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByPolicyGroupUserIdAndTypeAndActive($user_id, $type_id, $active = TRUE, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.type_id' => 'asc', 'c.trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'user_id' => $user_id,
					'active' => $this->toBool( $active ),
					);

		$query = '
					select 	d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $epcf->getTable() .' as c,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND b.exception_policy_control_id = c.id
						AND c.id = d.exception_policy_control_id
						AND a.user_id = ?
						AND d.active = ?
						AND d.type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND ( b.deleted = 0 AND c.deleted=0 AND d.deleted=0)
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPolicyGroupUserIdAndActive($user_id, $active = TRUE, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.type_id' => 'asc', 'c.trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'user_id' => $user_id,
					'active' => $this->toBool( $active ),
					);

		$query = '
					select 	d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $epcf->getTable() .' as c,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND b.exception_policy_control_id = c.id
						AND c.id = d.exception_policy_control_id
						AND a.user_id = ?
						AND d.active = ?
						AND ( b.deleted = 0 AND c.deleted=0 AND d.deleted=0)
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
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

		$additional_order_fields = array('type_id');

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			//$order = array( 'type_id' => 'asc');
			$order = array( 'id' => 'asc'); //Type ID is the wrong order, but ID is proper.
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['type_id']) ) {
				$order = Misc::prependArray( array('type_id' => 'asc'), $order );
			}
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
						LEFT JOIN '. $epcf->getTable() .' as b ON ( a.exception_policy_control_id = b.id AND b.deleted = 0 )
					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['exception_policy_control_id']) AND isset($filter_data['exception_policy_control_id'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_control_id']) ) {
			$query  .=	' AND a.exception_policy_control_id in ('. $this->getListSQL($filter_data['exception_policy_control_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
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
