<?php

namespace App\Models\PayPeriod;

use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class PayPeriodScheduleUserListFactory extends PayPeriodScheduleUserFactory implements IteratorAggregate {

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

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND b.company_id = :company_id
						AND a.pay_period_schedule_id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndUserID($company_id, $id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND b.company_id = :company_id
						AND a.pay_period_schedule_id = :id
						AND a.user_id in ( '. $this->getListSQL($user_id, $ph) .' )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodScheduleId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND pay_period_schedule_id = :id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodScheduleIdAndUserID($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':id' => $id,
					':user_id' => $user_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND pay_period_schedule_id = :id
						AND user_id = :user_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodScheduleIdArray($id) {
		$ppsulf = new PayPeriodScheduleUserListFactory();

		$ppsulf->getByPayPeriodScheduleId($id);

		foreach ($ppsulf->rs as $user) {
			$ppsulf->data = (array) $user;
			$user = $ppsulf;
			$user_list[$user->getUser()] = NULL;
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return array();
	}
}
?>
