<?php

namespace App\Models\Hierarchy;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class HierarchyObjectTypeListFactory extends HierarchyObjectTypeFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
				';
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
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByHierarchyControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = ?
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndObjectTypeId($id, $object_type_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			//$order = array('b.last_name' => 'asc');
			$strict_order = FALSE;
		}

		$cache_id = $id.$object_type_id;

		$hcf = new HierarchyControlFactory();
		$hotf = new HierarchyObjectTypeFactory();

		$this->rs = $this->getCache($cache_id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						'object_type_id' => $object_type_id,
						);

			$query = '
						select 	*
						from	'. $this->getTable() .' as a,
								'. $hcf->getTable() .' as b,
								'. $hotf->getTable() .' as c

						where	a.hierarchy_control_id = b.id
							AND a.hierarchy_control_id = c.hierarchy_control_id
							AND b.company_id = ?
							AND c.object_type_id = ?
							AND b.deleted = 0
					';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order, $strict_order );

			$this->rs = $this->db->Execute($query, $ph);

			$this->saveCache($this->rs,$cache_id);
		}

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			//$order = array('b.last_name' => 'asc');
			$strict_order = FALSE;
		}

		$hcf = new HierarchyControlFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a,
							'. $hcf->getTable() .' as b

					where	a.hierarchy_control_id = b.id
						AND b.company_id = ?
						AND b.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}


	function getByCompanyIdArray($id) {

		$hotlf = new HierarchyObjectTypeListFactory();
		$hotlf->getByCompanyId( $id ) ;

		$object_type = array();
		foreach ($hotlf as $object_type) {
			$object_types[] = $object_type->getObjectType();
		}

		return $object_types;
	}
}
?>
