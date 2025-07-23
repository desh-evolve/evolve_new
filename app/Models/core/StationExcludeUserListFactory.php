<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class StationExcludeUserListFactory extends StationExcludeUserFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable();
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}
		$this->data = $this->rs;
		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByStationId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$sf = new StationFactory();

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $sf->getTable() .' as b
					where	b.id = a.station_id
						AND a.station_id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		$this->data = $this->rs;
		return $this;
	}

	function getByStationIdArray($id) {
		$siulf = new StationExcludeUserListFactory();

		$siulf->getByStationId($id);

		foreach ($siulf->rs as $obj) {
			$siulf->data = (array)$obj;
			$list[$siulf->getStation()] = NULL;
		}

		if ( isset($list) ) {
			return $list;
		}

		return array();
	}
}
?>
