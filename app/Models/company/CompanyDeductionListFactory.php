<?php

/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
namespace App\Models\Company;

use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class CompanyDeductionListFactory extends CompanyDeductionFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0
					ORDER BY calculation_order ASC';
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

		if ( is_array($id) ) {
			$this->rs = FALSE;
		} else {
			$this->rs = $this->getCache($id);
		}

		if ( empty($this->rs) || $this->rs === FALSE ) {

			$ph = array();

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where	id in ('. $this->getListSQL($id, $ph) .')
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = DB::select($query, $ph);

			if ( !is_array($id) ) {
				$this->saveCache($this->rs,$id);
			}
		}
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'calculation_order' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIdAndName($company_id, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND lower(name) LIKE lower(:name)
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByIdAndCompanyId($ids, $company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $ids == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND id in ('. $this->getListSQL($ids, $ph) .')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIdAndId($company_id, $ids, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $ids == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND id in ('. $this->getListSQL($ids, $ph) .')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByTypeId($type_id, $where = NULL, $order = NULL) {
		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIdAndTypeId($company_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	company_id = :company_id
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIdAndStatusId($company_id, $status_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	company_id = :company_id
						AND status_id in ('. $this->getListSQL($status_id, $ph) .')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIdAndStatusIdAndTypeId($company_id, $status_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	company_id = :company_id
						AND status_id in ('. $this->getListSQL($status_id, $ph) .')
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY calculation_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByIdArray($id, $include_blank = TRUE) {
		if ( $id == '') {
			return FALSE;
		}

		$psenlf = new PayStubEntryNameListFactory(); 
		$psenlf->getById($id);

		if ( $include_blank == TRUE ) {
			$entry_name_list[0] = '--';
		}

		$type_options  = $this->getOptions('type');

		foreach ($psenlf->rs as $entry_name) {
			$psenlf->data = (array)$entry_name;
			$entry_name_list[$psenlf->getID()] = $type_options[$psenlf->getType()] .' - '. $psenlf->getDescription();
		}

		return $entry_name_list;
	}

	function getByCompanyIdAndStatusIdArray($company_id, $status_id, $include_blank = TRUE) {
		if ( $status_id == '') {
			return FALSE;
		}

		$cdlf = new CompanyDeductionListFactory();
		$cdlf->getByCompanyIdAndStatusId( $company_id, $status_id );
		//$psenlf->getByTypeId($type_id);

		$list = array();
		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($cdlf->rs as $obj) {
			$cdlf->data = (array)$obj;
			$list[$cdlf->getID()] = $cdlf->getName();
		}

		return $list;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $sort_prefix = FALSE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		$use_names = FALSE;

		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[$lf->getId()] = $lf->getName();
		}

		if ( isset($list) ) {
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

		$additional_order_fields = array('status_id');

		$sort_column_aliases = array(
									'status' => 'status_id',
									'type' => 'type_id',
									'calculation' => 'calculation_id',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'type_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
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

		$ph = array(
					':company_id' => $company_id,
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
					where	a.company_id = :company_id
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
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['calculation_id']) AND isset($filter_data['calculation_id'][0]) AND !in_array(-1, (array)$filter_data['calculation_id']) ) {
			$query  .=	' AND a.calculation_id in ('. $this->getListSQL($filter_data['calculation_id'], $ph) .') ';
		}
		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[':name'] = '%' . strtolower(trim($filter_data['name'])) . '%';
			$query  .=	' AND lower(a.name) LIKE :name ';
		}
		if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
			$query  .=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
		}
		if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
			$query  .=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
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
		$this->data = $this->rs;
		return $this;
	}
}
?>
