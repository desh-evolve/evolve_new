<?php

namespace App\Models\Cron;
use IteratorAggregate;

class CronJobListFactory extends CronJobFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

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

	function getByIdAndStatus($id, $status_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'status_id' => $status_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND status_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByName($name, $where = NULL, $order = NULL) {
		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	name = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getMostRecentlyRun() {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0
					ORDER BY last_run_date DESC
					LIMIT 1';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query);

		return $this;
	}

	function getArrayByListFactory($lf) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName(TRUE);
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

}
?>
