<?php

namespace App\Models\Holiday;

use App\Models\Company\CompanyGenericMapFactory;
use App\Models\Core\Misc;
use App\Models\Policy\HolidayPolicyFactory;
use App\Models\Policy\PolicyGroupFactory;
use App\Models\Policy\PolicyGroupUserFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;
use Carbon\Carbon;

class HolidayListFactory extends HolidayFactory implements IteratorAggregate {

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

	function getByIDAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$hpf = new HolidayPolicyFactory();

		$ph = array( 	':company_id' => $company_id,
						':id' => $id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $hpf->getTable() .' as b ON a.holiday_policy_id = b.id
					where	b.company_id = :company_id
						AND a.id = :id
						AND ( a.deleted = 0 AND b.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByIdAndHolidayPolicyID($id, $holiday_policy_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					':holiday_policy_id' => $holiday_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
						AND holiday_policy_id = :holiday_policy_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByHolidayPolicyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array();

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	holiday_policy_id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByCompanyIdAndHolidayPolicyId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$hpf = new HolidayPolicyFactory();

		$ph = array( ':company_id' => $company_id );

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $hpf->getTable() .' as b ON a.holiday_policy_id = b.id
					where	b.company_id = :company_id
						AND a.holiday_policy_id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByPolicyGroupUserId($user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.type_id' => 'asc', 'c.trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$hpf = new HolidayPolicyFactory();

		$ph = array(
					':user_id' => $user_id,
					);

		$query = '
					select 	d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $hpf->getTable() .' as c,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND b.holiday_policy_id = c.id
						AND c.id = d.holiday_policy_id
						AND a.user_id = :user_id
						AND ( c.deleted = 0 AND d.deleted = 0 AND b.deleted = 0)
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPolicyGroupUserIdAndDate($user_id, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.type_id' => 'asc', 'c.trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$hpf = new HolidayPolicyFactory();
		$cgmf = new CompanyGenericMapFactory();


		$ph = array(
					':user_id' => $user_id,
					':date' => Carbon::parse( $date )->toDateString(),
					);

		$query = '
					select 	d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $hpf->getTable() .' as c,
							'. $cgmf->getTable() .' as z,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND ( b.id = z.object_id AND z.company_id = b.company_id AND z.object_type_id = 180)
						AND z.map_id = d.holiday_policy_id
						AND d.holiday_policy_id = c.id
						AND a.user_id = :user_id
						AND d.date_stamp = :date
						AND ( c.deleted = 0 AND d.deleted = 0 AND b.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPolicyGroupUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.date_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$hpf = new HolidayPolicyFactory();
		$cgmf = new CompanyGenericMapFactory();


		$ph = array(
					':start_date' => Carbon::parse( $start_date )->toDateString(),
					':end_date' => Carbon::parse( $end_date )->toDateString(),
					);

		//Query was: distinct(d.*) but MySQL doesnt like that.
		$query = '
					select 	distinct d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $hpf->getTable() .' as c,
							'. $cgmf->getTable() .' as z,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND ( b.id = z.object_id AND z.company_id = b.company_id AND z.object_type_id = 180)
						AND z.map_id = d.holiday_policy_id
						
						AND d.holiday_policy_id = c.id

						AND d.date_stamp >= :start_date
						AND d.date_stamp <= :end_date
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND ( c.deleted = 0 AND d.deleted=0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndStartDateAndEndDate($company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.date_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$hpf = new HolidayPolicyFactory();
		$cgmf = new CompanyGenericMapFactory();


		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $start_date )->toDateString(),
					':end_date' => Carbon::parse( $end_date )->toDateString(),
					);

		//Query was: distinct(d.*) but MySQL doesnt like that.
		$query = '
					select 	distinct d.*
					from 	'. $pguf->getTable() .' as a,
							'. $pgf->getTable() .' as b,
							'. $hpf->getTable() .' as c,
							'. $cgmf->getTable() .' as z,
							'. $this->getTable() .' as d
					where 	a.policy_group_id = b.id
						AND ( b.id = z.object_id AND z.company_id = b.company_id AND z.object_type_id = 180)
						AND z.map_id = d.holiday_policy_id
						AND d.holiday_policy_id = c.id
						AND b.company_id = :company_id
						AND d.date_stamp >= :start_date
						AND d.date_stamp <= :end_date
						AND ( c.deleted = 0 AND d.deleted=0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getArrayByPolicyGroupUserId($user_id, $start_date, $end_date) {
		$hlf = new HolidayListFactory();
		$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $user_id, $start_date, $end_date);

		if ( $hlf->getRecordCount() > 0 ) {
			foreach($hlf->rs as $h_obj) {
				$hlf->data = (array)$h_obj;
				$h_obj = $hlf;
				$list[$h_obj->getDateStamp()] = $h_obj->getName();
			}

			return $list;
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

		$additional_order_fields = array();

		$sort_column_aliases = array();

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'desc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			if ( !isset($order['date_stamp']) ) {
				$order = Misc::prependArray( array('date_stamp' => 'desc'), $order );
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$hpf = new HolidayPolicyFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					':company_id' => $company_id,
					);
		
		$query = '
					select 	distinct a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $hpf->getTable() .' as hpf ON ( a.holiday_policy_id = hpf.id AND hpf.deleted = 0 )
						LEFT JOIN '. $cgmf->getTable() .' as cgmf ON ( cgmf.company_id = hpf.company_id AND cgmf.object_type_id = 180 AND cgmf.map_id = a.holiday_policy_id )
						LEFT JOIN '. $pgf->getTable() .' as pgf ON ( pgf.id = cgmf.object_id AND pgf.deleted = 0 )
						LEFT JOIN '. $pguf->getTable() .' as pguf ON ( pguf.policy_group_id = pgf.id AND pgf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	hpf.company_id = :company_id
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
		if ( isset($filter_data['holiday_policy_id']) AND isset($filter_data['holiday_policy_id'][0]) AND !in_array(-1, (array)$filter_data['holiday_policy_id']) ) {
			$query  .=	' AND a.holiday_policy_id in ('. $this->getListSQL($filter_data['holiday_policy_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND pguf.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[':name'] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE :name';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND a.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND a.date_stamp <= :end_date';
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
