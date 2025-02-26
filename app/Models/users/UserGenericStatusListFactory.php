<?php

namespace App\Models\Users;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class UserGenericStatusListFactory extends UserGenericStatusFactory implements IteratorAggregate {

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

	function getByUserId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
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

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->SelectLimit($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByUserIdAndBatchId($user_id, $batch_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $batch_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'label' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'batch_id' => $batch_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND batch_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->SelectLimit($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getStatusCountArrayByUserIdAndBatchId($user_id, $batch_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $batch_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'batch_id' => $batch_id,
					);

		$query = '
					select 	status_id,count(*) as total
					from	'. $this->getTable() .'
					where	user_id = ?
						AND batch_id = ?
						AND deleted = 0
					GROUP BY status_id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$result = $this->db->GetArray($query, $ph);

		$total = 0;
		foreach( $result as $row ) {
			$total = $total + $row['total'];
		}
		$retarr['total'] = $total;

		$retarr['status'] = array(
								10 => array('total' => 0, 'percent' => 0),
								20 => array('total' => 0, 'percent' => 0),
								30 => array('total' => 0, 'percent' => 0),
								);
								
		foreach( $result as $row ) {
			$retarr['status'][$row['status_id']] = array('total' => $row['total'], 'percent' => round( ($row['total'] / $total) * 100, 1 ) );
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

}
?>
