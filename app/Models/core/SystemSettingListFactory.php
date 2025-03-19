<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class SystemSettingListFactory extends SystemSettingFactory implements IteratorAggregate {

	public function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
			//$this->rs = DB::select($query);
		} else {
			//$this->rs = DB::select($query);
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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByName($name, $where = NULL, $order = NULL) {
		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					':name' => $name,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	name = :name
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getAllArray() {
		$id = 'all';

		$retarr = $this->getCache($id);
		if ( $retarr === FALSE ) {
			$sslf = new SystemSettingListFactory();
			$sslf->getAll();
			if ( $sslf->getRecordCount() > 0 ) {
				foreach( $sslf->rs as $ss_obj ) {
					$sslf->data = (array)$ss_obj;
					$retarr[$sslf->getName()] = $sslf->getValue();
				}

				$this->saveCache($retarr,$id);

				return $retarr;
			} else {
				return FALSE;
			}
		}

		return $retarr;
	}
}
?>
