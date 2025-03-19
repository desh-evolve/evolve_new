<?php

namespace App\Models\Policy;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PremiumPolicyDepartmentListFactory extends PremiumPolicyDepartmentFactory implements IteratorAggregate {

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

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPremiumPolicyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppf->getTable() .' as b
					where	b.id = a.premium_policy_id
						AND a.premium_policy_id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPremiumPolicyIdArray($id) {
		$ppdlf = new PremiumPolicyDepartmentListFactory();

		$ppdlf->getByPremiumPolicyId($id);

		foreach ($ppdlf->rs as $obj) {
			$ppdlf->data = (array) $obj;
			$obj = $ppdlf;
			$list[$obj->getPremiumPolicy()] = NULL;
		}

		if ( isset($list) ) {
			return $list;
		}

		return array();
	}
}
?>
