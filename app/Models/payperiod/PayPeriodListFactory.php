<?php

namespace App\Models\PayPeriod;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTDate;
use App\Models\PayStub\PayStubFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;
use Carbon\Carbon;

class PayPeriodListFactory extends PayPeriodFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted=0';
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
		if ( $id == '' ) {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						':id' => $id,
						);

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where	id = :id
							AND deleted=0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = DB::select($query, $ph);

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByIdList($ids, $where = NULL, $order = NULL) {
		if ( $ids == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	a.pay_period_schedule_id = b.id
						AND a.id in ( '. $this->getListSQL($ids, $ph) .' )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdListArray($ids, $where = NULL, $order = NULL, $enable_names = TRUE ) {
		if ( $ids == '' ) {
			return FALSE;
		}

		$result = $this->getByIdList($ids, $where, $order);

		foreach($result as $pay_period) {
			$pay_period_schedule_id[$pay_period->getPayPeriodScheduleObject()->getId()] = $pay_period->getPayPeriodScheduleObject()->getName();
		}

		$use_names = FALSE;
		if ( $enable_names == TRUE AND isset($pay_period_schedule_id) AND count($pay_period_schedule_id) > 1 ) {
			$use_names = TRUE;
		}

		$pay_period_schedule_name = NULL;
		foreach($result as $pay_period) {
			//Debug::Text('Pay Period: '. $pay_period->getId() , __FILE__, __LINE__, __METHOD__,10);
			/*
			if ( $use_names == TRUE ) {
				$pay_period_schedule_name = '('.$pay_period->getPayPeriodScheduleObject()->getName().') ';
			}
			*/
			//$pay_period_list[$pay_period->getId()] = $pay_period_schedule_name . TTDate::getDate('DATE', $pay_period->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period->getEndDate() );
			$pay_period_list[$pay_period->getId()] = $pay_period->getName($use_names);
		}

		if ( isset($pay_period_list) ) {
			return $pay_period_list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $sort_prefix = FALSE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		Debug::Text('Total Rows: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		$use_names = FALSE;

		//Get all pay period schedules, if more than one pay period schedule is in use, include PP schedule name.
		$pay_period_schedule_id = array();
		$i=0;
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$obj = $lf;
			if ( !isset($pay_period_schedule_id[$obj->getPayPeriodSchedule()]) ) {
				$pay_period_schedule_id[$obj->getPayPeriodSchedule()] = TRUE;
				$i++;
			}

			if ( $i >= 2 ) {
				$use_names = TRUE;
				break;
			}
		}

		$prefix = NULL;
		$i=0;
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$obj = $lf;
			if ( $sort_prefix == TRUE ) {
				$prefix = '-'.str_pad( $i, 4, 0, STR_PAD_LEFT).'-';
			}

			$list[$prefix.$obj->getID()] = $obj->getName( $use_names );

			$i++;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getByPayPeriodScheduleId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :id
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByCompanyIdAndStatus($company_id, $status_ids, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
			':company_id' => $company_id,
		);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b

					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = :company_id
						AND a.status_id in ( '. implode(', ', $status_ids) .' )
						AND a.deleted=0 AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}



    function getByCompanyIdAndStatusForHrProcess($company_id, $status_ids, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b

					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = :company_id
						AND a.status_id in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0 AND b.deleted=0 AND a.is_hr_process= 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}


	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND id = :id
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndEndDate($company_id, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $end_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_date )->toDateTimeString(),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND start_date <= :start_date
						AND end_date > :end_date
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTransactionDate($company_id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = :company_id
						AND a.end_date <= :start_date
						AND a.transaction_date > :end_date
						AND a.deleted=0
						AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTransactionStartDateAndTransactionEndDate($company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $start_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = :company_id
						AND a.transaction_date >= :start_date
						AND a.transaction_date <= :end_date
						AND a.deleted=0 AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserId($user_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':id' => $user_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = :id
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => Carbon::parse( $start_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_date )->toDateTimeString(),
					);

		//No pay period
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = :user_id
						AND a.start_date >= :start_date
						AND a.end_date <= :end_date
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	//Gets all pay periods that start or end between the two dates. Ideal for finding all pay periods that affect a given week.
	function getByUserIdAndOverlapStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => Carbon::parse( $start_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_date )->toDateTimeString(),
					':start_date2' => Carbon::parse( $start_date )->toDateTimeString(),
					':end_date2' => Carbon::parse( $end_date )->toDateTimeString(),
					':start_date3' => Carbon::parse( $start_date )->toDateTimeString(),
					':end_date3' => Carbon::parse( $end_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = :user_id
						AND
						(
							( a.start_date >= :start_date AND a.start_date <= :end_date )
							OR
							( a.end_date >= :start_date2 AND a.end_date <= :end_date2 )
							OR
							( a.start_date <= :start_date3 AND a.end_date >= :end_date3 )
						)
						AND ( a.deleted=0 AND b.deleted=0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		Debug::Arr($ph, 'Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdAndEndDate($user_id, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' OR $end_date <= 0 ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => Carbon::parse( $end_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_date )->toDateTimeString(),
					);

		//No pay period
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = :user_id
						AND a.start_date <= :start_date
						AND a.end_date >= :end_date
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdAndTransactionDate($user_id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = :user_id
						AND a.start_date <= :start_date
						AND a.transaction_date > :end_date
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate($id, $start_transaction_date, $end_transaction_date, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_transaction_date == '' ) {
			return FALSE;
		}

		if ( $end_transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array(
					':id' => $id,
					':start_date' => Carbon::parse( $start_transaction_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_transaction_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a

					where 	a.pay_period_schedule_id = :id
						AND a.transaction_date >= :start_date
						AND a.transaction_date <= :end_date
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate($company_id, $id, $start_transaction_date, $end_transaction_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_transaction_date == '' ) {
			return FALSE;
		}

		if ( $end_transaction_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $start_transaction_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $end_transaction_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where 	ppsf.company_id = :company_id
						AND a.transaction_date >= :start_date
						AND a.transaction_date <= :end_date
						AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndAnyDate($company_id, $id, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $date )->toDateTimeString(),
					':end_date' => Carbon::parse( $date )->toDateTimeString(),
					':transaction_date' => Carbon::parse( $date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where 	ppsf.company_id = :company_id
						AND ( a.start_date >= :start_date OR a.end_date >= :end_date OR a.transaction_date >= :transaction_date )
						AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getThisPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate($company_id, $id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		//ID can be blank/NULL, which means we search all pay_period schedules.
		if ( $date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':start_date' => Carbon::parse( $date )->toDateTimeString(),
					':end_date' => Carbon::parse( $date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where ppsf.company_id = :company_id
						AND a.start_date <= :start_date
						AND a.end_date >= :end_date ';

		if ( isset($id[0]) AND !in_array(-1, (array)$id) ) {
			$query .= '
							AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' ) ';
		}

		$query .= '		AND ( a.deleted = 0 AND ppsf.deleted = 0)';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getLastPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate($company_id, $id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		//ID can be blank/NULL, which means we search all pay_period schedules.
		if ( $date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					':end_date' => Carbon::parse( $date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
					( 	select
							b.pay_period_schedule_id,
							max(b.start_date) as start_date
						FROM '. $this->getTable() .' as b
						LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( b.pay_period_schedule_id = ppsf.id )
						where ppsf.company_id = :company_id
							AND b.end_date < :end_date
							AND ( b.deleted = 0 AND ppsf.deleted = 0 )
						GROUP BY b.pay_period_schedule_id
					) as pp2

					where a.pay_period_schedule_id = pp2.pay_period_schedule_id
						AND a.start_date = pp2.start_date ';

		if ( isset($id[0]) AND !in_array(-1, (array)$id) ) {
			$query .= '
							AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' ) ';
		}

		$query .= '		AND ( a.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodScheduleIdAndTransactionDate($id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array(
					':id' => $id,
					':start_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					':end_date' => Carbon::parse( $transaction_date )->toDateTimeString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a

					where 	a.pay_period_schedule_id = :id
						AND a.start_date <= :start_date
						AND a.transaction_date > :end_date
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPayPeriodEndDateByUserIdAndTransactionDate($user_id, $transaction_date = NULL ) {
		if ($transaction_date == '' ) {
			$transaction_date = TTDate::getTime();
		}

		$pay_period_obj = $this->getByUserIdAndTransactionDate( $user_id, $transaction_date )->getCurrent();

		if ( $pay_period_obj->getAdvanceTransactionDate() !== FALSE
				AND $pay_period_obj->getAdvanceTransactionDate() > TTDate::getTime() ) {
			$epoch = $pay_period_obj->getAdvanceEndDate();
		} else {
			$epoch = $pay_period_obj->getEndDate();
		}

		return $epoch;
	}

	function getPreviousPayPeriodById($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$pplf = new PayPeriodListFactory();
		$pay_period_obj = $pplf->getById($id)->getCurrent();
		$pay_period_schedule_id = $pay_period_obj->getPayPeriodSchedule();

		if ( $pay_period_schedule_id == '' ) {
			return FALSE;
		}

		//FIXME: Use date instead of ID, incase someone edits the dates.
		$ph = array(
					':pay_period_schedule_id' => $pay_period_schedule_id,
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = :pay_period_schedule_id
						AND id < :id
						AND deleted=0
					ORDER BY id desc
					LIMIT 1';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByStatus($status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$ph = array(
					':status_id' => $status,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'

					where	status_id = :status_id
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdListAndNotStatus($user_ids, $status_ids, $where = NULL, $order = NULL) {
		/*
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}
		*/

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where 	a.pay_period_schedule_id in
						( select distinct(x.pay_period_schedule_id)
							from
									'. $ppsuf->getTable() .' as x,
									'. $ppsf->getTable() .' as z
							where x.user_id in ( '. $this->getListSQL($user_ids, $ph) .' )
								AND z.deleted=0)
						AND a.status_id not in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdListAndNotStatusAndStartDateAndEndDate($user_ids, $status_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		/*
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}
		*/

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime() + (86400 * 355); //Only check ahead one year of open pay periods.
		}

		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array();

		$user_ids_sql = $this->getListSQL($user_ids, $ph);

		$ph[':end_date'] = Carbon::parse( $end_date )->toDateTimeString();
		$ph[':start_date'] = Carbon::parse( $start_date )->toDateTimeString();

		//Start Date arg should be greater then pay period END DATE.
		//So recurring PS amendments start_date can fall anywhere in the pay period and still get applied.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where 	a.pay_period_schedule_id in
						( select distinct(x.pay_period_schedule_id)
							from
									'. $ppsuf->getTable() .' as x,
									'. $ppsf->getTable() .' as z
							where x.user_id in ( '. $user_ids_sql .' )
								AND z.deleted=0)
						AND a.end_date >= :end_date
						AND a.start_date <= :start_date
						AND a.status_id not in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getFirstStartDateAndLastEndDateByPayPeriodScheduleId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array();
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = 'select 	min(start_date) as first_start_date,
							max(end_date) as last_end_date,
							count(*) as total
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = :id
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$retarr = DB::select($query, $ph);

		return $retarr;
	}

	function getYearsArrayByCompanyId($company_id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	distinct(extract(year from a.transaction_date))
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = :company_id
						AND a.deleted=0
						AND b.deleted=0
					ORDER by extract(year from a.transaction_date) desc
					';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		//$this->rs = DB::select($query);
		//return $this;

		$year_arr = DB::select($query, $ph);
		foreach($year_arr as $year) {
			$retarr[$year] = $year;
		}

		return $retarr;
	}

	function getPayPeriodsWithPayStubsByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$uf = new UserFactory();
		$psf = new PayStubFactory();

		//Make sure just one row per pay period is returned.

		/*
		//This is way too slow on older versions of PGSQL.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = ?
						AND ( a.deleted = 0 )
						AND EXISTS ( select id from '. $psf->getTable() .' as b WHERE a.id = b.pay_period_id AND b.deleted = 0)';
		*/
		$query = '	select 	distinct a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '.  $psf->getTable() .' as b on ( a.id = b.pay_period_id )
					where	a.company_id = :id
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	//Get last 6mths worth of pay periods and prepare a JS array so they can be highlighted in the calendar.
	function getJSCalendarPayPeriodArray( $include_all_pay_period_schedules = FALSE ) {
		global $current_company, $current_user;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_object($current_user) ) {
			return FALSE;
		}

		if ( $include_all_pay_period_schedules == TRUE ) {
			$cache_id = 'JSCalendarPayPeriodArray_'.$current_company->getId().'_0';
		} else {
			$cache_id = 'JSCalendarPayPeriodArray_'.$current_company->getId().'_'.$current_user->getId();
		}

		$retarr = $this->getCache($cache_id);
		if ( $retarr === FALSE ) {
			$pplf = new PayPeriodListFactory();
			if ( $include_all_pay_period_schedules == TRUE ) {
				$pplf->getByCompanyId( $current_company->getId(), 13);
			} else {
				$pplf->getByUserId( $current_user->getId(), 13);
			}

			$retarr = FALSE;
			if ( $pplf->getRecordCount() > 0 ) {
				foreach( $pplf->rs as $pp_obj) {
					$pplf->data = (array)$pp_obj;
					$pp_obj = $pplf;
					//$retarr['start_date'][] = TTDate::getDate('Ymd', $pp_obj->getStartDate() );
					$retarr['end_date'][] = TTDate::getDate('Ymd', $pp_obj->getEndDate() );
					$retarr['transaction_date'][] = TTDate::getDate('Ymd', $pp_obj->getTransactionDate() );
				}
			}

			$this->saveCache( $retarr, $cache_id);
		}

		return $retarr;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('status_id','type_id','pay_period_schedule');

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'transaction_date' => 'desc', 'end_date' => 'desc', 'start_date' => 'desc', 'pay_period_schedule_id' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['transaction_date']) ) {
				$order['transaction_date'] = 'desc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$ppsf = new PayPeriodScheduleFactory();
		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.name as pay_period_schedule,
							b.type_id as type_id,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $ppsf->getTable() .' as b ON ( a.pay_period_schedule_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = :company_id
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_schedule_id']) AND isset($filter_data['pay_period_schedule_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_schedule_id']) ) {
			$query  .=	' AND a.pay_period_schedule_id in ('. $this->getListSQL($filter_data['pay_period_schedule_id'], $ph) .') ';
		}

		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND b.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[':name'] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(b.name) LIKE :name';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateTimeString();
			$query  .=	' AND a.start_date >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateTimeString();
			$query  .=	' AND a.start_date <= :end_date';
		}

		if ( isset($filter_data['created_by']) AND isset($filter_data['created_by'][0]) AND !in_array(-1, (array)$filter_data['created_by']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['created_by'], $ph) .') ';
		}
		if ( isset($filter_data['updated_by']) AND isset($filter_data['updated_by'][0]) AND !in_array(-1, (array)$filter_data['updated_by']) ) {
			$query  .=	' AND a.updated_by in ('. $this->getListSQL($filter_data['updated_by'], $ph) .') ';
		}

		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

}
?>
