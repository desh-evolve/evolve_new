<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2408 $
 * $Id: MessageListFactory.class.php 2408 2009-02-04 01:03:51Z ipso $
 * $Date: 2009-02-03 17:03:51 -0800 (Tue, 03 Feb 2009) $
 */

/**
 * @package Module_Message
 */
class MessageRecipientListFactory extends MessageRecipientFactory implements IteratorAggregate {

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

	function getByCompanyIdAndMessageControlId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					WHERE
							b.company_id = ?
							AND a.message_control_id in ('. $this->getListSQL($id, $ph) .')
							AND a.deleted = 0
					';
		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					WHERE
							b.company_id = ?
							AND a.user_id = ?
							AND a.id in ('. $this->getListSQL($id, $ph) .')
							AND a.deleted = 0
					';
		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndMessageSenderId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					WHERE
							b.company_id = ?
							AND a.user_id = ?
							AND a.message_sender_id in ('. $this->getListSQL($id, $ph) .')
							AND a.deleted = 0
					';
		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserIdAndMessageSenderIdAndStatus($company_id, $user_id, $id, $status_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'status_id' => $status_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					WHERE
							b.company_id = ?
							AND a.user_id = ?
							AND a.status_id = ?
							AND a.message_sender_id in ('. $this->getListSQL($id, $ph) .')
							AND a.deleted = 0
					';
		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

}
?>
