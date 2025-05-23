<?php

namespace App\Models\Hierarchy;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class HierarchyLevelListFactory extends HierarchyLevelFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where 	deleted = 0
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
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$hcf = new HierarchyControlFactory();

		$ph = array(
					':id' => $id,
					':company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $hcf->getTable() .' as b ON a.hierarchy_control_id = b.id
					where	a.id = :id
						AND b.company_id = :company_id
						AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByHierarchyControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = :id
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByHierarchyControlIdAndUserId($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					':id' => $id,
					':user_id' => $user_id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = :id
						AND user_id = :user_id
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByHierarchyControlIdAndUserIdAndExcludeID($id, $user_id, $exclude_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('level' => 'asc', 'user_id' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					':id' => (int)$id,
					':user_id' => (int)$user_id,
					':exclude_id' => (int)$exclude_id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	hierarchy_control_id = :id
						AND user_id = :user_id
						AND id != :exclude_id
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getLevelsByHierarchyControlIdAndUserId( $id, $user_id ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					':idb' => $id,
					':user_id' => $user_id,
					);

		$query = '
					select 	distinct(level)
					from	'. $this->getTable() .'
					where	hierarchy_control_id = :id
						AND level >= (
										select 	level
										from	'. $this->getTable() .'
										where	hierarchy_control_id = :idb
											AND user_id = :user_id
											AND deleted = 0
										LIMIT 1
									 )
						AND deleted = 0
					ORDER BY level ASC
				';

        $retarr = DB::select($query, $ph);

		return $retarr;

	}

	function getLevelsByUserIdAndObjectTypeID( $user_id, $object_type_id = 50 ) { //Requests
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$ph = array(
					':user_id' => $user_id,
					);

		$query = '
				select 	distinct (x.level) as level
				from	'. $this->getTable() .' as x,
						'. $hcf->getTable() .' as y,
					(
								select 	a.hierarchy_control_id,a.level
								from	'. $this->getTable() .' as a
									LEFT JOIN '. $hotf->getTable() .' as b ON a.hierarchy_control_id = b.hierarchy_control_id
								where a.user_id = :user_id
									AND b.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
									AND a.deleted = 0
					) as z
				where
					x.hierarchy_control_id = y.id
					AND x.hierarchy_control_id = z.hierarchy_control_id
					AND x.level >= z.level
					AND ( x.deleted = 0 AND y.deleted = 0 )
				ORDER BY x.level asc
				';

		$rs = DB::select($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			//The retarr key is the value that will be displayed to the user when switching levels on the authorization page,
			//so we need to start that from 1 and increasing sequentially, regardless of what the actual hierarchy level is.
			$i=1;
			foreach( $rs as $row ) {
				$retarr[$i] = $row['level'];
				$i++;
			}

			return $retarr;
		}

		return FALSE;
	}

	function getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $user_id, $object_type_id = 50 ) { //Requests
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$ph = array(
					':user_id' => $user_id,
					);

		//Include object_type_ids for each hierarchy_control_id, because we need to do additional filtering by hierarchy_control_id, level, object_type_ids
		$query = '
				select
						x.hierarchy_control_id as hierarchy_control_id,
						x.level as level,
						z.object_type_id as object_type_id
				from	'. $this->getTable() .' as x,
						'. $hcf->getTable() .' as y,
					(
								select 	a.hierarchy_control_id,a.level,b.object_type_id
								from	'. $this->getTable() .' as a
									LEFT JOIN '. $hotf->getTable() .' as b ON a.hierarchy_control_id = b.hierarchy_control_id
								where a.user_id = :user_id
									AND b.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
									AND a.deleted = 0
					) as z
				where
					x.hierarchy_control_id = y.id
					AND x.hierarchy_control_id = z.hierarchy_control_id
					AND x.level >= z.level
					AND ( x.deleted = 0 AND y.deleted = 0 )
				ORDER BY x.level asc
				';

		$rs = DB::select($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			foreach( $rs as $row ) {
				$hierarchy_to_level_map[$row['hierarchy_control_id']][] = (int)$row['level'];
				$hierarchy_to_object_type_map[$row['hierarchy_control_id']][] = (int)$row['object_type_id'];
			}
			//Debug::Arr($hierarchy_to_level_map, ' Hierarchy To Level Map: ', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($hierarchy_to_object_type_map, ' Hierarchy To Object Type Map: ', __FILE__, __LINE__, __METHOD__,10);

			//Take each hierarchy_control and level element and convert it into virtual levels, where the first level (regardless of what it is in the actual hierarchy)
			//is always virtual_level 1, so the supervisor can see all necessary requests that are waiting on them at level 1. Dropping down any other levels
			//is looking and requests waiting on OTHER supervisors.
			//Track the last level for each hierarchy, so we know when to include all requests that may be higher than that level, so if the hierarchy is changed
			//and levels are taken out, requests don't sit in limbo forever.
			foreach( $hierarchy_to_level_map as $hierarchy_control_id => $level_arr ) {
				//Unique each level arr so we don't start creating extra virtual levels when multiple superiors are at the same level.
				//This fixes a bug where if there were 5 superiors at the same level, 5 virtual levels would be created.
				$level_arr = array_unique($level_arr);
				$i=1;
				foreach( $level_arr as $level ) {
					if ( $level == end($hierarchy_to_level_map[$hierarchy_control_id]) ) {
						$last_level = TRUE;
					} else {
						$last_level = FALSE;
					}

					$retarr[$i][] = array('hierarchy_control_id' => $hierarchy_control_id, 'level' => $level, 'last_level' => $last_level, 'object_type_id' => array_unique( $hierarchy_to_object_type_map[$hierarchy_control_id] ) );

					$i++;
				}
			}

			Debug::Arr($retarr, ' Final Hierarchy To Level Map: ', __FILE__, __LINE__, __METHOD__,10);
			return $retarr;
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
			$order = array( 'level' => 'asc');
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['level']) ) {
				$order['level'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$hcf = new HierarchyControlFactory();

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
						LEFT JOIN '. $hcf->getTable() .' as b ON ( a.hierarchy_control_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = :company_id
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['hierarchy_control_id']) AND isset($filter_data['hierarchy_control_id'][0]) AND !in_array(-1, (array)$filter_data['hierarchy_control_id']) ) {
			$query  .=	' AND a.hierarchy_control_id in ('. $this->getListSQL($filter_data['hierarchy_control_id'], $ph) .') ';
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

/*
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

			$this->rs = DB::select($query, $ph);

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
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
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
*/
}
?>
