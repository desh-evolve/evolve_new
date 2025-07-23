<?php

namespace App\Models\Company;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class CompanyGenericMapListFactory extends CompanyGenericMapFactory implements IteratorAggregate {

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
		$this->data = $this->rs;
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
		$this->data = $this->rs;
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
		$this->data = $this->rs;
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
		$this->data = $this->rs;
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

		$ph = array( ':company_id' => $company_id);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = :company_id
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND a.object_id in ('. $this->getListSQL($id, $ph) .')
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
		
		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIDAndObjectTypeAndMapID($company_id, $object_type_id, $id, $where = NULL, $order = NULL) {
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
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIDAndObjectTypeAndObjectIDAndMapID($company_id, $object_type_id, $id, $map_id,  $where = NULL, $order = NULL) {
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
		$this->data = $this->rs;
		return $this;
	}

	function getByCompanyIDAndObjectTypeAndObjectIDAndNotMapID($company_id, $object_type_id, $id, $map_id,  $where = NULL, $order = NULL) {
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
		$this->data = $this->rs;
		return $this;
	}

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
		$this->data = $this->rs;
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
		$this->data = $this->rs;
		return $this;
	}

	function getArrayByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getMapId();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	static function getArrayByCompanyIDAndObjectTypeIDAndObjectID( $company_id, $object_type_id, $object_id ) {
		$cgmlf = new CompanyGenericMapListFactory();

		$lf = $cgmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );
		return $cgmlf->getArrayByListFactory( $lf );
	}
}
?>