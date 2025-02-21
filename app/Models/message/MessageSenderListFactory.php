<?php

namespace App\Models\Message;
use IteratorAggregate;

class MessageSenderListFactory extends MessageSenderFactory implements IteratorAggregate {

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

	function getByCompanyIdAndId($company_id, $id, $where = NULL, $order = NULL) {
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
							AND a.id in ('. $this->getListSQL($id, $ph) .')
							AND a.deleted = 0
					';
		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndRecipientId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$mrf = new MessageRecipientFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Ignore deleted message_sender rows, as the sender could have deleted the original message.
		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $mrf->getTable() .' as b ON a.id = b.message_sender_id
						LEFT JOIN '. $uf->getTable() .' as c ON a.user_id = c.id
					WHERE
							c.company_id = ?
							AND b.id in ('. $this->getListSQL($id, $ph) .')
							AND ( b.deleted = 0 )
					';
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

	function getByCompanyIdAndObjectTypeAndObjectAndNotUser($company_id, $object_type_id, $object_id, $user_id = 0, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $object_id == '') {
			return FALSE;
		}


		$uf = new UserFactory();
		$mcf = new MessageControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'object_type_id' => $object_type_id,
					'object_id' => $object_id,
					'user_id' => (int)$user_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $mcf->getTable() .' as b ON a.message_control_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON a.user_id = c.id
					WHERE
							c.company_id = ?
							AND ( b.object_type_id = ? AND b.object_id = ? )
							AND a.user_id != ?
							AND ( b.deleted = 0 AND c.deleted = 0 )
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

}
?>
