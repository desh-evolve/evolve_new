<?php

namespace App\Models\PayStub;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PayStubEntryAccountListFactory extends PayStubEntryAccountFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0
					ORDER BY ps_order ASC';
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

		if ( is_array($id) ) {
			$this->rs = FALSE;
		} else {
			$this->rs = $this->getCache($id);
		}

		if ( $this->rs === FALSE ) {
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

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
			':company_id' => $company_id,
		);

		$query = '
			select 	*
			from	'. $this->getTable() .'
			where	company_id = :company_id
				AND deleted = 0
			ORDER BY ps_order ASC
		';
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
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND id = :id
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndAccrualId($company_id, $accrual_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $accrual_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':accrual_id' => $accrual_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND accrual_pay_stub_entry_account_id = :accrual_id
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTypeAndFuzzyName($company_id, $type_id, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
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
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getHighestOrderByCompanyIdAndTypeId($company_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':company_id2' => $company_id,
					':type_id' => $type_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where 	company_id = :company_id
						AND id = (
								select id
									from '. $this->getTable() .'
									where company_id = :company_id2
										AND type_id = :type_id
										AND deleted = 0
									ORDER BY ps_order DESC
									LIMIT 1
						)
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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
					ORDER BY ps_order ASC';
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

		$additional_order_fields = array(
										'type_id'
										 );

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 'status' => 'status_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'a.type_id' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE records go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('a.status_id' => 'asc'), $order );
			}

			//Always sort by type, ps_order after other columns
			if ( !isset($order['type_id']) ) {
				$order['a.type_id'] = 'asc';
			}

			if ( !isset($order['ps_order']) ) {
				$order['ps_order'] = 'asc';
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
		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[':name'] = strtolower(trim($filter_data['name']));
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

		Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE, $abbreviate_type = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		$list = array();


		$type_options  = $this->getOptions('type');
		if ( $abbreviate_type == TRUE ) {
			foreach( $type_options as $key => $val ) {
				$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction'), array('EE', 'ER', 'Ded'), $val);
			}
			unset($key, $val);
		}

		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$obj = $lf;
			$list[$obj->getID()] = $type_options[$obj->getType()] .' - '. $obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getByIdArray($id, $include_blank = TRUE) {
		if ( $id == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getById($id);

		if ( $include_blank == TRUE ) {
			$entry_name_list[0] = '--';
		}

		$type_options  = $this->getOptions('type');

		foreach ($psealf->rs as $entry_name) {
			$psealf->data = (array)$entry_name;
			$entry_name = $psealf;
			$entry_name_list[$entry_name->getID()] = $type_options[$entry_name->getType()] .' - '. $entry_name->getName();
		}

		return $entry_name_list;
	}

	function getByCompanyIdAndStatusIdAndTypeIdArray($company_id, $status_id, $type_id, $include_blank = TRUE, $abbreviate_type = TRUE ) {
		if ( $type_id == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyIdAndStatusIdAndTypeId( $company_id, $status_id, $type_id );
		//$psenlf->getByTypeId($type_id);

		$entry_name_list = array();

		if ( $include_blank == TRUE ) {
			$entry_name_list[0] = '--';
		}

		$type_options  = $this->getOptions('type');
		if ( $abbreviate_type == TRUE ) {
			foreach( $type_options as $key => $val ) {
				$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction'), array('EE', 'ER', 'Ded'), $val);
			}
			unset($key, $val);
		}

		foreach ($psealf->rs as $entry_name) {
			$psealf->data = (array)$entry_name;
			$entry_name = $psealf;
			$entry_name_list[$entry_name->getID()] = $type_options[$entry_name->getType()] .' - '. $entry_name->getName();
		}

		return $entry_name_list;
	}

	function getByTypeArrayByCompanyIdAndStatusId($company_id, $status_id) {

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyIdAndStatusId( $company_id, $status_id);

		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $company_id );
		if ( $pseallf->getRecordCount() == 0 ) {
			return FALSE;
		}

		$psea_type_map = $pseallf->getCurrent()->getPayStubEntryAccountIDToTypeIDMap();

		if ( $psealf->getRecordCount() > 0 ) {
			foreach ($psealf->rs as $psea_obj) {
				$psealf->data = (array)$psea_obj;
				$psea_obj = $psealf;
				$entry_name_list[$psea_obj->getType()][] = $psea_obj->getId();
			}

			foreach( $entry_name_list[40] as $key => $entry_name_id ) {
				if ( isset($psea_type_map[$entry_name_id]) ) {
					$tmp_entry_name_list[$entry_name_id] = $entry_name_list[$psea_type_map[$entry_name_id]];
				}
			}

			return $tmp_entry_name_list;
		}

		return FALSE;
	}

}
?>
