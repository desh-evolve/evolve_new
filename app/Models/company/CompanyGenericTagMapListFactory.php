<?php

namespace App\Models\Company;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class CompanyGenericTagMapListFactory extends CompanyGenericTagMapFactory implements IteratorAggregate {

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

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndObjectType($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array( ':company_id' => $company_id);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = :company_id
						AND a.object_type_id in ('. $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndObjectTypeAndObjectID($company_id, $object_type_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}



		$additional_order_fields = array( 'cgtf.name' );

		if ( $order == NULL ) {
			$order = array( 'cgtf.name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$cgtf = new CompanyGenericTagFactory();

		$ph = array( ':company_id' => $company_id);

		//This should be a list of just distinct
		$query = '
					select
							a.*,
							cgtf.name as name
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $cgtf->getTable() .' as cgtf ON ( a.object_type_id = cgtf.object_type_id AND a.tag_id = cgtf.id AND cgtf.company_id = :company_id)
					where
						a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND a.object_id in ('. $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
/*
	function getByCompanyIDAndObjectTypeAndTagID($company_id, $object_type_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array( ':company_id' => $company_id);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = :company_id
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND a.map_id in ('. $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndObjectTypeAndObjectIDAndTagID($company_id, $object_type_id, $id, $map_id,  $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array( ':company_id' => $company_id);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = :company_id
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND a.object_id in ('. $this->getListSQL($id, $ph) .')
						AND a.map_id in ('. $this->getListSQL($map_id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndObjectTypeAndObjectIDAndNotTagID($company_id, $object_type_id, $id, $map_id,  $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array( ':company_id' => $company_id);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = :company_id
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND a.object_id in ('. $this->getListSQL($id, $ph) .')
						AND a.map_id not in ('. $this->getListSQL($map_id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
*/
	function getByObjectType($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.object_type_id in ('. $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByObjectTypeAndObjectID($object_type_id, $id, $where = NULL, $order = NULL) {
		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.object_type_id in ('.  $this->getListSQL($object_type_id, $ph) .')
						AND a.object_id in ('.  $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getArrayByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$list[] = $obj->getColumn('name');
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	static function getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $object_id ) {
		$cgtmlf = new CompanyGenericTagMapListFactory();

		$lf = $cgtmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );
		return $cgtmlf->getArrayByListFactory( $lf );
	}

	static function getStringByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $object_id ) {
		$cgtmlf = new CompanyGenericTagMapListFactory();

		$lf = $cgtmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );
		return implode(',', (array)$cgtmlf->getArrayByListFactory( $lf ) );
	}

}
?>