<?php

namespace App\Models\Users; 
use IteratorAggregate;

class UserReportDataListFactory extends UserReportDataFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
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
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndId($user_id, $id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndScript($user_id, $script, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'is_default' => 'desc', 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'script' => $script,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND script = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndScriptAndDefault($user_id, $script, $default = TRUE, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'script' => $script,
					'default' => $this->toBool($default),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND script = ?
						AND is_default = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndScriptArray($user_id, $script, $include_blank = TRUE) {

		$ugdlf = new UserGenericDataListFactory();
		$ugdlf->getByUserIdAndScript($user_id, $script);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($ugdlf as $ugd_obj) {
			if ( $ugd_obj->getDefault() == TRUE ) {
				$default = ' (Default)';
			} else {
				$default = NULL;
			}
			$list[$ugd_obj->getID()] = $ugd_obj->getName().$default;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	/*

		Company List Functions

	*/
	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND user_id is NULL
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
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
						AND user_id is NULL
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndScript($company_id, $script, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					'script' => $script
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND user_id is NULL
						AND script = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndScriptAndDefault($company_id, $script, $default = TRUE, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					'script' => $script,
					'default' =>  $this->toBool($default)
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND user_id is NULL
						AND script = ?
						AND is_default = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

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
		if ( $order == NULL ) {
			$order = array('a.name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
					where	a.company_id = ?
					';

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['script']) AND isset($filter_data['script'][0]) AND !in_array(-1, (array)$filter_data['script']) ) {
			$query  .=	' AND a.script in ('. $this->getListSQL($filter_data['script'], $ph) .') ';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE ?';
		}

		//if ( isset($filter_data['name']) AND isset($filter_data['name'][0]) AND !in_array(-1, (array)$filter_data['name']) ) {
		//	$query  .=	' AND lower( a.name ) in ('. $this->getListSQL( array_map('strtolower', (array)$filter_data['name']), $ph) .') ';
		//}

		if ( isset($filter_data['is_default']) ) {
			$ph[] = $this->toBool($filter_data['is_default']);
			$query  .=	' AND a.is_default = ? ';
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

	function getByCompanyIdAndScriptArray($company_id, $script, $include_blank = TRUE) {

		$ugdlf = new UserGenericDataListFactory();
		$ugdlf->getByUserIdAndScript($company_id, $script);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($ugdlf as $ugd_obj) {
			if ( $ugd_obj->getDefault() == TRUE ) {
				$default = ' (Default)';
			} else {
				$default = NULL;
			}
			$list[$ugd_obj->getID()] = $ugd_obj->getName().$default;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
}
?>
