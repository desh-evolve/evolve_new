<?php

namespace App\Models\Accrual;

use App\Models\Company\BranchFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Department\DepartmentFactory;
use App\Models\Policy\AccrualPolicyFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserGroupFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserTitleFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class AccrualListFactory extends AccrualFactory implements IteratorAggregate {

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
			//$this->rs = DB::select($query);
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

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
			':id' => $id,
			':company_id' => $company_id
		);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	a.id = :id
						AND b.company_id = :company_id
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id
		);

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyID($company_id, $user_id, $accrual_policy_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id,
			':accrual_policy_id' => $accrual_policy_id,
		);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.accrual_policy_id = :accrual_policy_id
						AND ( a.user_date_total_id IS NULL OR ( a.user_date_total_id IS NOT NULL AND c.deleted = 0 AND d.deleted = 0) )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}




        function getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatusForLeave($company_id, $user_id, $accrual_policy_id,$type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id,
			':accrual_policy_id' => $accrual_policy_id,
			':type_id' => $type_id,
		);

		$query = '
					select 	sum(a.amount) as amount,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.accrual_policy_id = :accrual_policy_id
                                                AND a.type_id in (70,30,75)
						AND ( a.user_date_total_id IS NULL OR ( a.user_date_total_id IS NOT NULL AND c.deleted = 0 AND d.deleted = 0) )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );



		$this->rs = DB::select($query, $ph);

		return $this;
	}




        function getByCompanyIdAndUserIdAndAccrualPolicyIdAndStatus($company_id, $user_id, $accrual_policy_id,$type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id,
			':accrual_policy_id' => $accrual_policy_id,
			':type_id' => $type_id,
		);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.accrual_policy_id = :accrual_policy_id
                        AND a.type_id = :type_id
						AND ( a.user_date_total_id IS NULL OR ( a.user_date_total_id IS NOT NULL AND c.deleted = 0 AND d.deleted = 0) )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );



		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyIDAndTimeStampAndAmount($company_id, $user_id, $accrual_policy_id, $time_stamp, $amount, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $time_stamp == '') {
			return FALSE;
		}

		if ( $amount == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id,
			':accrual_policy_id' => $accrual_policy_id,
            ':time_stamp' => Carbon::createFromTimestamp($time_stamp)->toDateTimeString(),

			':amount' => $amount,
		);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.accrual_policy_id = :accrual_policy_id
						AND a.time_stamp = :time_stamp
						AND a.amount = :amount
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyIDAndTypeIDAndTimeStamp($company_id, $user_id, $accrual_policy_id, $type_id, $time_stamp, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $time_stamp == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
			':company_id' => $company_id,
			':accrual_policy_id' => $accrual_policy_id,
			':type_id' => $type_id,
            ':time_stamp' => Carbon::createFromTimestamp($time_stamp)->toDateTimeString(),
		);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = :user_id
						AND b.company_id = :company_id
						AND a.accrual_policy_id = :accrual_policy_id
						AND a.type_id = :type_id
						AND a.time_stamp = :time_stamp
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdAndAccrualPolicyIDAndUserDateTotalID($user_id, $accrual_policy_id, $user_date_total_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $user_date_total_id == '') {
			return FALSE;
		}

		$ph = array(
			':user_id' => $user_id,
			':accrual_policy_id' => $accrual_policy_id,
			':user_date_total_id' => $user_date_total_id,
		);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :user_id
						AND accrual_policy_id = :accrual_policy_id
						AND user_date_total_id = :user_date_total_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}


	function getByUserIdAndUserDateTotalID($user_id, $user_date_total_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $user_date_total_id == '') {
			return FALSE;
		}

		$ph = array(
			':user_id' => $user_id,
			':user_date_total_id' => $user_date_total_id,
		);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :user_id
						AND user_date_total_id = :user_date_total_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getOrphansByUserId($user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$apf = new AccrualPolicyFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
			':user_id' => $user_id,
		);

		//Make sure we check if user_date rows are deleted where user_date_total rows are not.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
					LEFT JOIN '. $apf->getTable() .' as d ON a.accrual_policy_id = d.id
					where	a.user_id = :user_id
						AND (
								( b.id is NULL OR b.deleted = 1 )
								OR
								( b.deleted = 0 AND ( c.id is NULL OR c.deleted = 1) )
							)
						AND ( a.type_id = 10 OR a.type_id = 20 OR ( a.type_id = 75 AND d.type_id = 30 ) )
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getSumByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$udtf = new UserDateTotalFactory();

		$ph = array(
			':user_id' => $user_id,
			':accrual_policy_id' => $accrual_policy_id,
		);

		$query = '
					select 	sum(amount) as amount
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					where	a.user_id = :user_id
						AND a.accrual_policy_id = :accrual_policy_id
						AND ( (a.user_date_total_id is NOT NULL AND b.id is NOT NULL)
								OR a.user_date_total_id IS NULL AND b.id is NULL )
						AND a.deleted = 0';

		$total = DB::select($query, $ph);


		if (empty($total) || $total == FALSE ) {
			$total = 0;
		}
		Debug::text('Balance: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}



	function getByAccrualPolicyId($accrual_policy_id, $where = NULL, $order = NULL) {
		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$ph = array(
			':accrual_policy_id' => $accrual_policy_id,
		);

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.accrual_policy_id = :accrual_policy_id
						AND a.deleted = 0
						AND b.deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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

		$additional_order_fields = array( 'accrual_policy','accrual_policy_type_id','date_stamp' );
		$sort_column_aliases = array(
			'accrual_policy_type' => 'accrual_policy_type_id',
			'type' => 'type_id',
		);
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'accrual_policy_id' => 'asc', 'date_stamp' => 'desc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$apf = new AccrualPolicyFactory();

		$ph = array(
			':company_id' => $company_id,
		);

		$query = '
					select 	a.*,
							ab.name as accrual_policy,
							ab.type_id as accrual_policy_type_id,
							CASE WHEN udf.date_stamp is NOT NULL THEN udf.date_stamp ELSE a.time_stamp END as date_stamp,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as group,
							f.id as title_id,
							f.name as title
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $apf->getTable() .' as ab ON ( a.accrual_policy_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $udtf->getTable() .' as udtf ON a.user_date_total_id = udtf.id
						LEFT JOIN '. $udf->getTable() .' as udf ON udtf.user_date_id = udf.id

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )

					where	b.company_id = :company_id
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.user_id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['accrual_policy_id']) AND isset($filter_data['accrual_policy_id'][0]) AND !in_array(-1, (array)$filter_data['accrual_policy_id']) ) {
			$query  .=	' AND a.accrual_policy_id in ('. $this->getListSQL($filter_data['accrual_policy_id'], $ph) .') ';
		}

		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND b.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
			$query  .=	' AND b.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
		}
		if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
			$query  .=	' AND b.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
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
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByAccrualByUserIdAndTypeIdAndDate($user_id,$type_id,$date_stamp, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

        if ( $date_stamp == '') {
			return FALSE;
		}

		$ph = array(
			':time_stamp' => $date_stamp,
			':user_id' => $user_id,
			':type_id' => $type_id,
		);

		$uf = new UserFactory();


		$query = "SELECT * FROM ". $this->getTable() ." as a
                         WHERE DATE_FORMAT(a.time_stamp,'%Y-%m-%d') = :time_stamp
                         AND a.user_id = :user_id
                         AND a.type_id = :type_id
                         AND a.deleted = 0";
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}


    function getByAccrualPolicyIdAndStartDateAndEndDate($company_id, $user_id,$accrual_policy_id,$type_id, $start_date, $end_date){

        if ( $company_id == '') {
			return FALSE;
		}


        if ( $user_id == '') {
			return FALSE;
		}


        if ( $accrual_policy_id == '') {
			return FALSE;
		}

        if ( $start_date == '') {
			return FALSE;
		}


        if ( $end_date == '') {
			return FALSE;
		}


		$ph = array(
			':company_id' => $company_id,
			':user_id' => $user_id,
			':accrual_policy_id' => $accrual_policy_id,
			':type_id' => $type_id,
		);

		$uf = new UserFactory();


		$query = 'select 	sum(a.amount) as amount
			from	'. $this->getTable() .' as a '
				.'inner join '. $uf->getTable() .' as b on  b.id = a.user_id'
				.' where b.company_id = :company_id '
				.' and a.user_id =  :user_id'
				.' and a.accrual_policy_id = :accrual_policy_id'
				.' and a.type_id = :type_id'
				." and a.time_stamp  between '".$start_date."' and '".$end_date."'"
				.' and a.deleted = 0';


        //$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;

    }

}
?>
