<?php

class SystemSettingListFactory extends SystemSettingFactory implements IteratorAggregate {

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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getAllArray() {
		$id = 'all';

		$retarr = $this->getCache($id);
		if ( $retarr === FALSE ) {
			$sslf = new SystemSettingListFactory();
			$sslf->getAll();
			if ( $sslf->getRecordCount() > 0 ) {
				foreach( $sslf as $ss_obj ) {
					$retarr[$ss_obj->getName()] = $ss_obj->getValue();
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
