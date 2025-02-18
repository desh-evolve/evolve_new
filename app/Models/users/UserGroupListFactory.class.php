<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4265 $
 * $Id: UserGroupListFactory.class.php 4265 2011-02-18 00:49:20Z ipso $
 * $Date: 2011-02-17 16:49:20 -0800 (Thu, 17 Feb 2011) $
 */

/**
 * @package Module_Users
 */
class UserGroupListFactory extends UserGroupFactory implements IteratorAggregate {
	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->SelectLimit($query);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page);
		}

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => (int)$id,
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

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND id = ?
						AND deleted = 0
				';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdArray( $id ) {
		$this->getFastTreeObject()->setTree( $id );

		$children = $this->getFastTreeObject()->getAllChildren(NULL, 'RECURSE');

		if ( $children !== FALSE ) {
			$uglf = new UserGroupListFactory();

			foreach ($children as $object_id => $level ) {

				if ( $object_id !== 0 ) {
					$obj = $uglf->getById ( $object_id )->getCurrent();

					$nodes[] = array(
									'id' => $object_id,
									'name' => $obj->getName(),
									'level' => $level
									);
				}

			}

			if ( isset($nodes) ) {
				return $nodes;
			}
		}

		return FALSE;
	}
        
        //FL Added Function get the name rosen
        static function getNameById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$lf = new UserGroupListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();
			return $obj->getName();
		}

		return FALSE;
	}

	function getByCompanyIdAndGroupIdAndSubGroupsArray($id, $group_id, $include_sub_groups = TRUE ) {
		$this->getFastTreeObject()->setTree( $id );

		if ( $include_sub_groups == TRUE ) {
			$recurse = 'RECURSE';
		} else {
			$recurse = FALSE;
		}

		$children = $this->getFastTreeObject()->getAllChildren( $group_id, $recurse);

		if ( $children !== FALSE ) {
			foreach ($children as $object_id => $level ) {
				$nodes[] = $object_id;
			}

			if ( isset($nodes) ) {
				return $nodes;
			}
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getArrayByNodes($nodes, $include_blank = TRUE, $sort_prefix = FALSE ) {
		if ( !is_array( $nodes ) ) {
			return FALSE;
		}

		$prefix = NULL;
		$i=0;
		foreach($nodes as $node) {
			if ( $sort_prefix == TRUE ) {
				$prefix = '-'.str_pad( $i, 4, 0, STR_PAD_LEFT).'-';
			}

			$retarr[$prefix.$node['id']] = $node['text'];

			$i++;
		}


		if ( isset($retarr) ) {
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
					'company_id' => $company_id,
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
					where	a.company_id = ?
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

		if ( isset($filter_data['parent_id']) AND isset($filter_data['parent_id'][0]) AND !in_array(-1, (array)$filter_data['parent_id']) ) {
			$query  .=	' AND a.parent_id in ('. $this->getListSQL($filter_data['parent_id'], $ph) .') ';
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
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

}
?>
