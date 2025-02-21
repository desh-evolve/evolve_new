<?php

namespace App\Models\Policy;
use IteratorAggregate;

class PolicyGroupRoundIntervalPolicyListFactory extends PolicyGroupRoundIntervalPolicyFactory implements IteratorAggregate {

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

	function getByPolicyGroupId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$pgf = new PolicyGroupFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pgf->getTable() .' as b
					where	b.id = a.policy_group_id
						AND a.policy_group_id = ?
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByPolicyGroupIdArray($id) {
		$pgriplf = new PolicyGroupRoundIntervalPolicyListFactory();

		$pgriplf->getByPolicyGroupId($id);

		foreach ($pgriplf as $obj) {
			$list[$obj->getRoundIntervalPolicy()] = NULL;
		}

		if ( isset($list) ) {
			return $list;
		}

		return array();
	}
}
?>
