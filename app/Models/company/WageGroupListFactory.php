<?php
namespace App\Models\Company;

use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class WageGroupListFactory extends WageGroupFactory implements IteratorAggregate {

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

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
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
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = :company_id
						AND	id = :id
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
/*
	function getByCompanyIdArray($company_id) {

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId($company_id);

		$title_list[0] = '--';

		foreach ($utlf as $title_obj) {
			$title_list[$title_obj->getID()] = $title_obj->getName();
		}

		return $title_list;
	}
*/
	function getArrayByListFactory($lf, $include_blank = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = ('-Default-');
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
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

		$additional_order_fields = array();
		if ( $order == NULL ) {
			$order = array( 'name' => 'asc');
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
		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[':name'] = '%' . strtolower(trim($filter_data['name'])) . '%';
			$query  .=	' AND lower(a.name) LIKE :name';
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
