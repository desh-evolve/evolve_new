<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: UserDefaultListFactory.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Module_Users
 */
class UserDefaultListFactory extends UserDefaultFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'company_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->SelectLimit($query);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page);
		}

		return $this;
	}

	function getById($id) {
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
						from 	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			//$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			$this->saveCache($this->rs,$id);
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

		if ( $order == NULL ) {
			$order = array( 'id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}
}
?>
