<?php

namespace App\Models\Users; 
use IteratorAggregate;

class UserIdentificationListFactory extends UserIdentificationFactory implements IteratorAggregate {

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

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
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

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByTypeIdAndValue($type_id, $value, $order = NULL) {
		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value == '') {
			return FALSE;
		}

		$ph = array(
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
					where	a.type_id = ?
						AND a.value = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND ( a.deleted = 0  AND b.deleted = 0) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
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

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTypeId($company_id, $type_id, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.type_id' => 'asc', 'a.number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND b.status_id = 10
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTypeIdAndDateAndValidUserIDs($company_id, $type_id, $date = NULL, $valid_user_ids = array(), $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.type_id' => 'asc', 'a.number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND b.status_id = 10
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
				';

		if ( ( isset($date) AND $date > 0) OR ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) ) {
			$query .= ' AND ( ';

			if ( isset($date) AND $date > 0 ) {
				//Append the same date twice for created and updated.
				$ph[] = (int)$date;
				$ph[] = (int)$date;
				$query  .=	' 	( a.created_date >= ? OR a.updated_date >= ? ) ';
			}

			//Valid USER IDs is an "OR", so if any IDs are specified they should *always* be included ,regardless of the $date variable.
			if ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) {
				if ( isset($date) AND $date > 0 ) {
					$query .= ' OR ';
				}
				$query  .=	' a.user_id in ('. $this->getListSQL($valid_user_ids, $ph) .') ';
			}

			$query .= ' ) ';
		}

		$query .= ' AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTypeIdAndValue($company_id, $type_id, $value, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.type_id = ?
						AND a.value = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserId($user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndTypeId($user_id, $type_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'user_id' => 'asc', 'type_id' => 'asc', 'number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndTypeIdAndNumber($user_id, $type_id, $number, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $number === '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'type_id' => $type_id,
					'number' => $number,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id = ?
						AND number = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndTypeIdAndNumber($company_id, $user_id, $type_id, $number, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $number === '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'type_id' => $type_id,
					'number' => $number,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND a.user_id = ?
						AND a.type_id = ?
						AND a.number = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndTypeIdAndValue($user_id, $type_id, $value, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value === '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id = ?
						AND value = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getIsModifiedByUserIdAndDate($user_id, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND
							( created_date >= ? OR updated_date >= ? )
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User Identification rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}
		Debug::text('User Identification rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	b.company_id = ?
						AND
							( a.created_date >= ? OR a.updated_date >= ? )
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User Identification rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}
		Debug::text('User Identification rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( empty($user_id) ) {
			return FALSE;
		}

		if ( empty($company_id) ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND a.company_id = ?
						AND	b.user_id = ?
						AND b.deleted = 0';
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}
}
?>