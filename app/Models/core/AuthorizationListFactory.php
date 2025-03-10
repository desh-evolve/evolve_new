<?php

namespace App\Models\Core;

use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Request\RequestFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class AuthorizationListFactory extends AuthorizationFactory implements IteratorAggregate {

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
					':id' => (int)$id,
					);

		$query = '
					select 	*
					from	'. $this->table .'
					where	id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'created_date' => 'asc' );
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
					from 	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON (a.created_by = uf.id )
					where	uf.company_id = :company_id
						AND	a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByObjectTypeAndObjectId($object_type_id, $object_id, $where = NULL, $order = NULL) {
		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $object_id == '') {
			return FALSE;
		}

		$ph = array(
					':object_type_id' => $object_type_id,
					':object_id' => $object_id,
					);

		/*
		$key = Option::getByValue($object_type, $this->getOptions('object_type') );
		if ($key !== FALSE) {
			$object_type_id = $key;
		}
		*/
		$query = '
					select 	*
					from	'. $this->table .'
					where	object_type_id = :object_type_id
						AND object_id = :object_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByObjectTypeAndObjectIdAndCreatedBy($object_type_id, $object_id, $created_by, $where = NULL, $order = NULL) {
		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $object_id == '') {
			return FALSE;
		}

		if ( $created_by == '') {
			return FALSE;
		}

		$ph = array(
					':object_type_id' => $object_type_id,
					':object_id' => $object_id,
					':created_by' => $created_by,
					);

		/*
		$key = Option::getByValue($object_type, $this->getOptions('object_type') );
		if ($key !== FALSE) {
			$object_type_id = $key;
		}
		*/
		$query = '
					select 	*
					from	'. $this->table .'
					where	object_type_id = :object_type_id
						AND object_id = :object_id
						AND created_by = :created_by
						AND deleted = 0';
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

		$additional_order_fields = array();

		$sort_column_aliases = array();

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'created_date' => 'desc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['created_date']) ) {
				$order = Misc::prependArray( array('created_date' => 'desc'), $order );
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$pptsvf = new PayPeriodTimeSheetVerifyListFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							CASE WHEN a.object_type_id = 90 THEN pptsvf.user_id ELSE ud.user_id END as user_id,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $rf->getTable() .' as rf ON ( a.object_type_id in (1010,1020,1030,1040,1100) AND a.object_id = rf.id )
						LEFT JOIN '. $udf->getTable() .' as ud ON ( rf.user_date_id = ud.id )
						LEFT JOIN '. $pptsvf->getTable() .' as pptsvf ON ( a.object_type_id = 90 AND a.object_id = pptsvf.id )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	y.company_id = :company_id';
		$user_id_column = 'a.created_by';
		if ( isset($filter_data['object_type_id']) AND in_array( $filter_data['object_type_id'], array(1010,1020,1030,1040,1100) ) ) { //Requests
			$user_id_column = 'ud.user_id';
		} elseif ( isset($filter_data['object_type_id']) AND in_array( $filter_data['object_type_id'], array(90) ) ) { //TimeSheet
			$user_id_column = 'pptsvf.user_id';
		}
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND '. $user_id_column .' in ('. implode(',', $filter_data['permission_children_ids']) .') ';
		}

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. implode(',', $filter_data['id']) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. implode(',', $filter_data['exclude_id']) .') ';
		}
		if ( isset($filter_data['object_type_id']) AND isset($filter_data['object_type_id'][0]) AND !in_array(-1, (array)$filter_data['object_type_id']) ) {
			$query  .=	' AND a.object_type_id in ('. $this->getListSQL($filter_data['object_type_id'], $ph) .') ';
		}
		if ( isset($filter_data['object_id']) AND isset($filter_data['object_id'][0]) AND !in_array(-1, (array)$filter_data['object_id']) ) {
			$query  .=	' AND a.object_id in ('. $this->getListSQL($filter_data['object_id'], $ph) .') ';
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

}
?>
