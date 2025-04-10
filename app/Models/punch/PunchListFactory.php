<?php

namespace App\Models\Punch;

use App\Models\Company\BranchFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\StationFactory;
use App\Models\Core\TTDate;
use App\Models\Core\UserDateFactory;
use App\Models\Department\DepartmentFactory;
use App\Models\PayPeriod\PayPeriodFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserGroupFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleFactory;
use App\Models\Users\UserWageFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;
use Carbon\Carbon;

class PunchListFactory extends PunchFactory implements IteratorAggregate {
	//Helper function get to maximum shift time from pay period schedule.
	function getPayPeriodMaximumShiftTime( $user_id, $maximum_shift_time = NULL ) {
		if ( !is_numeric( $maximum_shift_time) ) {
			//Get pay period start/maximum shift time time
			$ppslf = new PayPeriodScheduleListFactory();
			$ppslf->getByUserId( $user_id );
			if ( $ppslf->getRecordCount() == 1 ) {
				$pps_obj = $ppslf->getCurrent();
				$maximum_shift_time = $pps_obj->getMaximumShiftTime();
				//Debug::Text(' aPay Period Schedule Maximum Shift Time: '. $maximum_shift_time, __FILE__, __LINE__, __METHOD__,10);
			} else {
				//Debug::Text(' bPay Period Schedule Not Found! Using 4hrs as default', __FILE__, __LINE__, __METHOD__,10);
				$maximum_shift_time = 3600*16;
			}
		}
		Debug::Text(' cPay Period Schedule Maximum Shift Time: '. $maximum_shift_time, __FILE__, __LINE__, __METHOD__,10);

		return $maximum_shift_time;
	}

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
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPunchControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'time_stamp' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	punch_control_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByIdAndCompanyId( $id, $company_id ) {
		return $this->getByCompanyIDAndId($company_id, $id);
	}
	function getByCompanyIDAndId($company_id, $id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();
		$udf = new UserDateFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		//We can't have extra columns displayed here as this is the function called before a delete, and if extra columns exist it will create a SQL error on getEmptyRecordSet()
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c,
							'. $uf->getTable() .' as d
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = d.id
						AND d.company_id = :company_id
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getAPIByIdAndCompanyId($id, $company_id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();
		$udf = new UserDateFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		//This function returns additional columns needed by the API to Mass Edit punches, however it CAN NOT be used to deleted
		//data, as the additional records cause a syntax error.
		$query = '
					select 	a.*,
							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.note as note,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							c.pay_period_id as pay_period_id
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c,
							'. $uf->getTable() .' as d
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = d.id
						AND d.company_id = :company_id
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPunchControlIdAndStatusId($punch_control_id, $status_id, $where = NULL, $order = NULL) {
		if ( $punch_control_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':punch_control_id' => $punch_control_id,
					':status_id' => $status_id
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.id = :punch_control_id
						AND a.status_id = :status_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateId($user_date_id, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = :user_date_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateIdAndTypeId($user_date_id, $type_id, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					':type_id' => $type_id
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = :user_date_id
						AND a.type_id = :type_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.type_id, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateIdAndStatusId($user_date_id, $status_id, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					':status_id' => $status_id
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = :user_date_id
						AND a.status_id = :status_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateIdAndStatusIdAndTypeId($user_date_id, $status_id, $type_id, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					':status_id' => $status_id,
					':type_id' => $type_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = :user_date_id
						AND a.status_id = :status_id
						AND a.type_id = :type_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateIdAndNotPunchId($user_date_id, $punch_id, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		if ( $punch_id == '') {
			$punch_id = 0;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					':punch_id' => $punch_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = :user_date_id
						AND a.id != :punch_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getNextPunchByUserIdAndEpoch($user_id, $epoch, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':time_stamp' => Carbon::parse($epoch)->toDateTimeString(),
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = :user_id
						AND a.time_stamp >= :time_stamp
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0)
					ORDER BY a.time_stamp asc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getShiftPunchesByUserIDAndEpoch( $user_id, $epoch, $punch_control_id = 0, $maximum_shift_time = NULL ) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		if ( $maximum_shift_time == '' ) {
			$maximum_shift_time = $this->getPayPeriodMaximumShiftTime( $user_id, $maximum_shift_time );
		}

		//Make sure that we get all punches surrounding the EPOCH within the maximum shift time,
		//We also need to take into account punch pairs, for example:
		// Punch Pair: 10-Mar-09 @ 11:30PM -> 11-Mar-09 @ 2:30PM. If the maximum shift time ends at 11:45PM
		// we need to include the out punch as well.
		$start_time_stamp = ($epoch - $maximum_shift_time);
		$end_time_stamp = ($epoch + $maximum_shift_time);

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date_stamp' => Carbon::parse( $start_time_stamp )->toDateString(),
					':end_date_stamp' => Carbon::parse( $end_time_stamp )->toDateString(),
					':start_time_stamp2' => Carbon::parse( $start_time_stamp )->toDateTimeString(),
					':end_time_stamp2' => Carbon::parse( $end_time_stamp )->toDateTimeString(),
					);

		//Order by a.punch_control_id is extremely important here so two punches at the same time, one paired already, and one not paired don't come in the wrong order, and cause getShiftData() to handle them incorrectly.
		//This query with sub-selects performs horribly with MySQL v5.0 once the punch table grows over 500,000 rows or so.
		/*
		$query = '
					select a.*
					from '. $this->getTable() .' as a
					where
						(
						a.punch_control_id in (
													select '. (int)$punch_control_id .'
													UNION ALL
													select 	x.punch_control_id
													from 	'. $this->getTable() .' as x,
															'. $pcf->getTable() .' as y,
															'. $udf->getTable() .' as z
													where	x.punch_control_id = y.id
														AND y.user_date_id = z.id
														AND z.user_id = ?
														AND z.date_stamp >= ?
														AND z.date_stamp <= ?
														AND x.time_stamp >= ?
														AND x.time_stamp <= ?
														AND ( x.deleted = 0 AND y.deleted=0 AND z.deleted=0 )

												)
						)
					AND a.deleted = 0
					ORDER BY a.time_stamp asc, a.punch_control_id, a.status_id asc
					';
		*/

		//This query removes the sub-query and is optimized for MySQL.
		$query = '
					select distinct a.*
					from '. $this->getTable() .' as a
					INNER JOIN
						(
							select '. (int)$punch_control_id .' as punch_control_id
							UNION ALL
							select 	x.punch_control_id
							from 	'. $this->getTable() .' as x
							LEFT JOIN '. $pcf->getTable() .' as y ON x.punch_control_id = y.id
							LEFT JOIN '. $udf->getTable() .' as z ON y.user_date_id = z.id
							where z.user_id = :user_id
								AND z.date_stamp >= :start_date_stamp
								AND z.date_stamp <= :end_date_stamp
								AND x.time_stamp >= :start_time_stamp2
								AND x.time_stamp <= :end_time_stamp2
								AND ( x.deleted = 0 AND y.deleted=0 AND z.deleted=0 )

						) as z ON a.punch_control_id = z.punch_control_id
					WHERE a.deleted = 0
					ORDER BY a.time_stamp asc, a.punch_control_id, a.status_id asc
					';


		//$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getFirstPunchByUserIDAndEpoch( $user_id, $epoch, $maximum_shift_time = NULL ) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		if ( $maximum_shift_time == '' ) {
			$maximum_shift_time = $this->getPayPeriodMaximumShiftTime( $user_id, $maximum_shift_time );
		}

		$user_date_stamp = TTDate::getBeginDayEpoch( $epoch - $maximum_shift_time );

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':user_date_stamp' => Carbon::parse($user_date_stamp)->toDateString(),
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = :user_id
						AND c.date_stamp = :user_date_stamp
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					ORDER BY a.time_stamp asc, a.status_id asc
					LIMIT 1
					';
		//$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByUserIdAndEpoch($user_id, $epoch, $order = NULL) {
		Debug::Text(' User ID: '. $user_id .' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$maximum_shift_time = $this->getPayPeriodMaximumShiftTime( $user_id );

		$begin_day_epoch = TTDate::getBeginDayEpoch( $epoch );
		$start_time = $epoch - $maximum_shift_time;

		Debug::Text(' Start Time: '. TTDate::getDate('DATE+TIME', $start_time ), __FILE__, __LINE__, __METHOD__,10);

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => Carbon::parse($start_time)->toDateString(),
					':end_date' => Carbon::parse($epoch)->toDateString(),
					':start_time' => Carbon::parse($start_time)->toDateTimeString(),
					':end_time' => Carbon::parse($epoch)->toDateTimeString(),
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...

		//Include date_stamp filter on user_date table as this greatly speeds up the query when its not already cached.
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = :user_id
						AND c.date_stamp >= :start_date
						AND c.date_stamp <= :end_date
						AND a.time_stamp >= :start_time
						AND a.time_stamp <= :end_time
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					ORDER BY a.time_stamp desc, a.status_id asc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );
		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByUserIdAndEpochAndNotPunchIDAndMaximumShiftTime($user_id, $epoch, $punch_id, $maximum_shift_time = NULL, $order = NULL) {
		Debug::Text(' User ID: '. $user_id .' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__,10);
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '' OR $epoch <= 0 ) {
			return FALSE;
		}

		$maximum_shift_time = $this->getPayPeriodMaximumShiftTime( $user_id, $maximum_shift_time );
		$start_time = $epoch - $maximum_shift_time;

		Debug::Text(' Start Time: '. TTDate::getDate('DATE+TIME', $start_time ), __FILE__, __LINE__, __METHOD__,10);

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':start_date' => date('Y-m-d', $start_time),
					':end_date' => date('Y-m-d', $epoch),
					':start_time' => date('Y-m-d H:i:s', $start_time),
					':end_time' => date('Y-m-d H:i:s', $epoch),
					':punch_id' => (int)$punch_id,
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = :user_id
						AND c.date_stamp >= :start_date
						AND c.date_stamp <= :end_date
						AND a.time_stamp >= :start_time
						AND a.time_stamp <= :end_time
						AND a.id != :punch_id
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					ORDER BY a.time_stamp desc, a.status_id asc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );
		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByUserDateIdAndStatusAndTypeAndEpoch($user_date_id, $status_id, $type_id, $epoch, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_date_id' => $user_date_id,
					':status_id' => $status_id,
					':type_id' => $type_id,
					':time_stamp' => Carbon::parse($epoch)->toDateTimeString(),
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND b.user_date_id = :user_date_id
						AND a.status_id = :status_id
						AND a.type_id = :type_id
						AND a.time_stamp <= :time_stamp
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp desc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByUserIdAndStatusAndTypeAndEpoch($user_id, $status_id, $type_id, $epoch, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':user_id' => $user_id,
					':status_id' => $status_id,
					':type_id' => $type_id,
					':time_stamp' => Carbon::parse($epoch)->toDateTimeString(),
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = :user_id
						AND a.status_id = :status_id
						AND a.type_id = :type_id
						AND a.time_stamp <= :time_stamp
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp desc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByPunchId($punch_id, $order = NULL) {
		if ( $punch_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':punch_id' => $punch_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND	b.user_date_id = (select z.user_date_id from '. $pcf->getTable() .' as z where z.id = b.punch_control_id)
						AND a.id = :punch_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByPunchControlId($punch_control_id, $order = NULL) {
		if ( $punch_control_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':punch_control_id' => $punch_control_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	a.punch_control_id = b.id
						AND b.id = :punch_control_id
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIDAndUserIdAndStartDateAndEndDate($company_id, $user_id, $start_date, $end_date) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();
		$udf = new UserDateFactory();

		$ph = array(
					':company_id' => $company_id,
					':user_id' => $user_id,
					':start_date' => Carbon::parse( $start_date )->toDateString(),
					':end_date' => Carbon::parse( $end_date )->toDateString()
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		$query = '
					select 	a.*,
							c.date_stamp as user_date_stamp,
							b.note as note
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c,
							'. $uf->getTable() .' as d
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = d.id
						AND d.company_id = :company_id
						AND	c.user_id = :user_id
						AND c.date_stamp >= :start_date
						AND c.date_stamp <= :end_date
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					ORDER BY a.time_stamp asc, a.status_id desc, a.punch_control_id asc
					';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getPreviousPunchByCompanyId($company_id, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	c.user_id as user_id,

					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c,
							'. $uf->getTable() .' as d
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = d.id
						AND d.company_id = :company_id
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					GROUP BY c.user_id
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getLastPunchByUserId($user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uf = new UserFactory();

		$ph = array(
					':user_id' => $user_id,
					);

		//Status order matters, because if its a.status_id desc, OUT comes first, but if the last
		//punch doesn't have OUT yet, it defaults to IN
		// with a.status_id asc...
		$query = '
					select 	a.*

					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c,
							'. $uf->getTable() .' as d
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id = d.id
						AND d.id = :user_id
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					ORDER BY a.time_stamp desc
					LIMIT 1
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByPayPeriodId($pay_period_id, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					':pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.pay_period_id = :pay_period_id
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
					';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
/*
	function getReportByPayPeriodIDListAndUserIdListAndBranchIdAndDepartmentId($pay_period_ids, $user_ids, $branch_id, $department_id, $order = NULL) {
		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $pay_period_ids == '') {
			return FALSE;
		}

		if ( $branch_id == '') {
			return FALSE;
		}

		if ( $department_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();

		$ph = array();

		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as punch_time_stamp,
							a.actual_time_stamp as punch_actual_time_stamp,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.pay_period_id as pay_period_id
					from 	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $udf->getTable() .' as c
					where 	a.punch_control_id = b.id
						AND b.user_date_id = c.id
						AND c.user_id in ('. $this->getListSQL($user_ids, $ph) .')
					';

		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, $branch_id) ) {
			$query .= ' AND b.branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND isset($department_id[0]) AND !in_array(-1, $department_id) ) {
			$query .= ' AND b.department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}
		if ( $pay_period_ids != '' AND isset($pay_period_ids[0]) AND !in_array(-1, (array)$pay_period_ids) ) {
			$query .= ' AND c.pay_period_id in ('. $this->getListSQL($pay_period_ids, $ph) .') ';
		}

		$query .= '
					AND (a.deleted = 0 AND b.deleted =0 AND c.deleted = 0)
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = DB::select($query, $ph);

		return $this;
	}
*/
	function getReportByPayPeriodIDListAndUserIdListAndBranchIdAndDepartmentIdAndJobIdListAndJobItemIdList($pay_period_ids, $user_ids, $branch_id, $department_id, $job_ids, $job_item_ids, $order = NULL) {
		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $pay_period_ids == '') {
			return FALSE;
		}

		if ( $branch_id == '') {
			return FALSE;
		}

		if ( $department_id == '') {
			return FALSE;
		}

		if ( $job_ids == '') {
			return FALSE;
		}

		if ( $job_item_ids == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
		}

		$ph = array();

		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as punch_time_stamp,
							a.actual_time_stamp as punch_actual_time_stamp,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.pay_period_id as pay_period_id
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON a.user_date_id = c.id
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as d ON b.job_id = d.id';
		}

		$query .= '
					where c.user_id in ('. $this->getListSQL($user_ids, $ph) .')
					';

		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, $branch_id) ) {
			$query .= ' AND b.branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND isset($department_id[0]) AND !in_array(-1, $department_id) ) {
			$query .= ' AND b.department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}
		if ( $pay_period_ids != '' AND isset($pay_period_ids[0]) AND !in_array(-1, (array)$pay_period_ids) ) {
			$query .= ' AND c.pay_period_id in ('. $this->getListSQL($pay_period_ids, $ph) .') ';
		}
		if ( $job_ids != '' AND isset($job_ids[0]) AND !in_array(-1, (array)$job_ids) ) {
			$query .= ' AND b.job_id in ('. $this->getListSQL($job_ids, $ph) .') ';
		}
		if ( $job_item_ids != '' AND isset($job_item_ids[0]) AND !in_array(-1, (array)$job_item_ids) ) {
			$query .= ' AND b.job_item_id in ('. $this->getListSQL($job_item_ids, $ph) .') ';
		}

		$query .= '
					AND (a.deleted = 0 AND b.deleted =0 AND c.deleted = 0)
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getReportByStartDateAndEndDateAndUserIdListAndBranchIdAndDepartmentIdAndJobIdListAndJobItemIdList($start_date, $end_date, $user_ids, $branch_id, $department_id, $job_ids, $job_item_ids, $order = NULL) {
		if ( $start_date == '') {
			return FALSE;
		}
		if ( $end_date == '') {
			return FALSE;
		}

		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $branch_id == '') {
			return FALSE;
		}

		if ( $department_id == '') {
			return FALSE;
		}

		if ( $job_ids == '') {
			return FALSE;
		}

		if ( $job_item_ids == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
		}

		$ph = array(
					':start_date' => Carbon::parse($start_date)->toDateString(),
					':end_date' => Carbon::parse($end_date)->toDateString(),
					);

		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as punch_time_stamp,
							a.actual_time_stamp as punch_actual_time_stamp,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.pay_period_id as pay_period_id,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date

					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = c.user_id
																			and z.effective_date <= c.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as d ON b.job_id = d.id';
		}

		$query .= '
					where c.date_stamp >= :start_date
						AND c.date_stamp <= :end_date
					';
		if ( $user_ids != '' AND isset($user_ids[0]) AND !in_array(-1, $user_ids) ) {
			$query .= ' AND c.user_id in ('. $this->getListSQL($user_ids, $ph) .') ';
		}
		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, $branch_id) ) {
			$query .= ' AND b.branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND isset($department_id[0]) AND !in_array(-1, $department_id) ) {
			$query .= ' AND b.department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}
		if ( $job_ids != '' AND isset($job_ids[0]) AND !in_array(-1, (array)$job_ids) ) {
			$query .= ' AND b.job_id in ('. $this->getListSQL($job_ids, $ph) .') ';
		}
		if ( $job_item_ids != '' AND isset($job_item_ids[0]) AND !in_array(-1, (array)$job_item_ids) ) {
			$query .= ' AND b.job_item_id in ('. $this->getListSQL($job_item_ids, $ph) .') ';
		}

		$query .= '
					AND (a.deleted = 0 AND b.deleted =0 AND c.deleted = 0)
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp','time_stamp','type_id','status_id','branch_id','department_id','default_branch_id','default_department_id','group_id','title_id');
		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = FALSE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['user_id']) ) {
			$filter_data['id'] = $filter_data['user_id'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset($filter_data['include_job_ids']) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset($filter_data['job_group_ids']) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset($filter_data['job_item_ids']) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uwf = new UserWageFactory();
		$sf = new StationFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as time_stamp,
							a.actual_time_stamp as actual_time_stamp,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							c.pay_period_id as pay_period_id,

							e.type_id as station_type_id,
							e.station_id as station_station_id,
							e.source as station_source,
							e.description as station_description,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date ';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						x.name as job_name,
						x.description as job_description,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id
							LEFT JOIN '. $sf->getTable() .' as e ON a.station_id = e.id
							LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = c.user_id
																			and z.effective_date <= c.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc LiMiT 1)
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON b.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON b.job_item_id = y.id';
		}

		$query .= '	WHERE d.company_id = :company_id';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND d.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND d.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND d.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query  .=	' AND b.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query  .=	' AND b.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_status_id']) AND isset($filter_data['punch_status_id'][0]) AND !in_array(-1, (array)$filter_data['punch_status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['punch_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_type_id']) AND isset($filter_data['punch_type_id'][0]) AND !in_array(-1, (array)$filter_data['punch_type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['punch_type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}


		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND b.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND b.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND x.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}


		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND c.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND c.date_stamp <= :end_date';
		}

		$query .= 	'
						AND (a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($ph,'PlaceHolders:', __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getPunchSummaryReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc','uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$sf = new StationFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jgf = new JobGroupFactory();
			$jif = new JobItemFactory();
			$jigf = new JobItemGroupFactory();
		}

		$ph = array( ':company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as punch_time_stamp,
							a.actual_time_stamp as punch_actual_time_stamp,
							a.created_date as punch_created_date,
							a.updated_date as punch_updated_date,

							pcf.user_date_id as user_date_id,
							pcf.branch_id as branch_id,
							pcf.department_id as department_id,
							pcf.job_id as job_id,
							pcf.job_item_id as job_item_id,
							pcf.quantity as quantity_id,
							pcf.bad_quantity as bad_quantity_id,
							pcf.total_time as total_time,
							pcf.actual_total_time as actual_total_time,
							pcf.meal_policy_id as meal_policy_id,
							pcf.other_id1 as other_id1,
							pcf.other_id2 as other_id2,
							pcf.other_id3 as other_id3,
							pcf.other_id4 as other_id4,
							pcf.other_id5 as other_id5,
							pcf.note as note,

							b.user_id as user_id,
							b.date_stamp as date_stamp,
							b.pay_period_id as pay_period_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

							bf.name as branch,
							df.name as department,
							a.status_id as status_id,
							a.type_id as type_id,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							z.hourly_rate as hourly_rate,
							z.labor_burden_percent as labor_burden_percent,

							sf.type_id as station_type_id,
							sf.station_id as station_station_id,
							sf.source as station_source,
							sf.description as station_description';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						jf.name as job,
						jf.description as job_description,
						jf.status_id as job_status_id,
						jf.manual_id as job_manual_id,
						jf.branch_id as job_branch_id,
						jbf.name as job_branch,
						jf.department_id as job_department_id,
						jdf.name as job_department,
						jf.group_id as job_group_id,
						jgf.name as job_group,
						jf.other_id1 as job_other_id1,
						jf.other_id2 as job_other_id2,
						jf.other_id3 as job_other_id3,
						jf.other_id4 as job_other_id4,
						jf.other_id5 as job_other_id5,
						jif.name as job_item,
						jif.description as job_item_description,
						jif.manual_id as job_item_manual_id,
						jif.group_id as job_item_group_id,
						jigf.name as job_item_group
						';
		}

		$query .= ' from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.punch_control_id = pcf.id
					LEFT JOIN '. $udf->getTable() .' as b ON pcf.user_date_id = b.id
					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON pcf.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON pcf.department_id = df.id

					LEFT JOIN '. $sf->getTable() .' as sf ON a.station_id = sf.id

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = 0
																			and z.deleted = 0
																			order by z.effective_date desc limit 1) ';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as jf ON pcf.job_id = jf.id
						LEFT JOIN '. $jif->getTable() .' as jif ON pcf.job_item_id = jif.id
						LEFT JOIN '. $bf->getTable() .' as jbf ON jf.branch_id = jbf.id
						LEFT JOIN '. $df->getTable() .' as jdf ON jf.department_id = jdf.id
						LEFT JOIN '. $jgf->getTable() .' as jgf ON jf.group_id = jgf.id
						LEFT JOIN '. $jigf->getTable() .' as jigf ON jif.group_id = jigf.id
						';
		}

		$query .= ' where 	uf.company_id = :company_id ';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND uf.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['include_user_id']) AND isset($filter_data['include_user_id'][0]) AND !in_array(-1, (array)$filter_data['include_user_id']) ) {
			$query  .=	' AND uf.id in ('. $this->getListSQL($filter_data['include_user_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_user_id']) AND isset($filter_data['exclude_user_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_user_id']) ) {
			$query  .=	' AND uf.id not in ('. $this->getListSQL($filter_data['exclude_user_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND uf.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_group_id']) AND isset($filter_data['user_group_id'][0]) AND !in_array(-1, (array)$filter_data['user_group_id']) ) {
			if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
			}
			$query  .=	' AND uf.group_id in ('. $this->getListSQL($filter_data['user_group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND uf.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND uf.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_title_id']) AND isset($filter_data['user_title_id'][0]) AND !in_array(-1, (array)$filter_data['user_title_id']) ) {
			$query  .=	' AND uf.title_id in ('. $this->getListSQL($filter_data['user_title_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query  .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query  .=	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query .= 	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND b.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND b.date_stamp <= :end_date';
		}

		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 AND pcf.deleted = 0 )
					';

		$query .= $this->getSortSQL( $order, FALSE );
		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getAPIActiveShiftReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp','time_stamp','type_id','status_id','branch','department','default_branch','default_department','group','title');

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = FALSE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
/*
		if ( isset($filter_data['user_id']) ) {
			$filter_data['id'] = $filter_data['user_id'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
*/
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}
/*
		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset($filter_data['include_job_ids']) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset($filter_data['job_group_ids']) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset($filter_data['job_item_ids']) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}
*/
		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uwf = new UserWageFactory();
		$sf = new StationFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select
							a.id as id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as time_stamp,
							a.actual_time_stamp as actual_time_stamp,
							a.original_time_stamp as original_time_stamp,

							a.created_by as created_by,
							a.created_date as created_date,
							a.updated_by as updated_by,
							a.updated_date as updated_date,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							j.name as branch,
							b.department_id as department_id,
							k.name as department,
							b.job_id as job_id,
							r.name as job,
							b.job_item_id as job_item_id,
							s.name as job_item,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							c.pay_period_id as pay_period_id,

							d.first_name as first_name,
							d.last_name as last_name,
							d.status_id as user_status_id,
							d.group_id as group_id,
							g.name as "group",
							d.title_id as title_id,
							h.name as title,
							d.default_branch_id as default_branch_id,
							e.name as default_branch,
							d.default_department_id as default_department_id,
							f.name as default_department,
							d.created_by as user_created_by,

							l.type_id as station_type_id,
							l.station_id as station_station_id,
							l.source as station_source,
							l.description as station_description,

							w.id as user_wage_id,
							w.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						r.name as job_name,
						r.status_id as job_status_id,
						r.manual_id as job_manual_id,
						r.branch_id as job_branch_id,
						r.department_id as job_department_id,
						r.group_id as job_group_id';
		}

		$query .= '
					from 	(
								select tmp2_d.id, max(tmp2_a.id) as punch_id, max(tmp2_a.time_stamp) as max_punch_time_stamp
								from 	'. $this->getTable() .' as tmp2_a
								LEFT JOIN '. $pcf->getTable() .' as tmp2_b ON tmp2_a.punch_control_id = tmp2_b.id
								LEFT JOIN '. $udf->getTable() .' as tmp2_c ON tmp2_b.user_date_id = tmp2_c.id
								LEFT JOIN '. $uf->getTable() .' as tmp2_d ON tmp2_c.user_id = tmp2_d.id
								WHERE tmp2_d.company_id = :company_id';

								if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
									$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
									$query  .=	' AND tmp2_c.date_stamp >= :start_date';
								}
								if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
									$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
									$query  .=	' AND tmp2_c.date_stamp <= :end_date';
								}

								$query .= '
									AND tmp2_a.time_stamp is not null
									AND ( tmp2_a.deleted = 0 AND tmp2_b.deleted = 0 AND tmp2_c.deleted = 0 )
								group by tmp2_d.id
							) as tmp_a
							LEFT JOIN '. $this->getTable() .' as a ON tmp_a.punch_id = a.id
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( b.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( b.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $sf->getTable() .' as l ON a.station_id = l.id

							LEFT JOIN '. $uwf->getTable() .' as w ON w.id = (select w.id
																		from '. $uwf->getTable() .' as w
																		where w.user_id = c.user_id
																			and w.effective_date <= c.date_stamp
																			and w.deleted = 0
																			order by w.effective_date desc LiMiT 1)

							LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
							LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as r ON b.job_id = r.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as s ON b.job_item_id = s.id';
		}

		$ph[':company_id'] = $company_id;
		$query .= '	WHERE d.company_id = :company_id';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND d.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND d.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND d.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['branch_id']) AND isset($filter_data['branch_id'][0]) AND !in_array(-1, (array)$filter_data['branch_id']) ) {
			$query  .=	' AND b.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND b.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}


		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND b.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND b.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND r.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND c.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND c.date_stamp <= :end_date';
		}

		$query .= 	'
						AND (a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
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

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp','time_stamp','type_id','status_id','branch','department','default_branch','default_department','group','title');

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 'station_station_id' => 'l.station_id',
									 'station_type' => 'l.type_id',
									 'station_source' => 'l.source',
									 'station_description' => 'l.description',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = FALSE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
/*
		if ( isset($filter_data['user_id']) ) {
			$filter_data['id'] = $filter_data['user_id'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
*/
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

/*
		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset($filter_data['include_job_ids']) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset($filter_data['job_group_ids']) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset($filter_data['job_item_ids']) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}
*/
		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$uwf = new UserWageFactory();
		$sf = new StationFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select
							a.id as id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as time_stamp,
							a.actual_time_stamp as actual_time_stamp,
							a.original_time_stamp as original_time_stamp,

							a.created_by as created_by,
							a.created_date as created_date,
							a.updated_by as updated_by,
							a.updated_date as updated_date,

							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							j.name as branch,
							b.department_id as department_id,
							k.name as department,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.quantity as quantity_id,
							b.bad_quantity as bad_quantity_id,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							c.pay_period_id as pay_period_id,

							d.first_name as first_name,
							d.last_name as last_name,
							d.status_id as user_status_id,
							d.group_id as group_id,
							g.name as "group",
							d.title_id as title_id,
							h.name as title,
							d.default_branch_id as default_branch_id,
							e.name as default_branch,
							d.default_department_id as default_department_id,
							f.name as default_department,
							d.created_by as user_created_by,

							l.id as station_id,
							l.type_id as station_type_id,
							l.station_id as station_station_id,
							l.source as station_source,
							l.description as station_description,

							w.id as user_wage_id,
							w.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',						
						r.name as job,
						r.name as job_name,
						r.status_id as job_status_id,
						r.manual_id as job_manual_id,
						r.branch_id as job_branch_id,
						r.department_id as job_department_id,
						r.group_id as job_group_id,
						s.name as job_item';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( b.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( b.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $sf->getTable() .' as l ON ( a.station_id = l.id AND l.deleted = 0 )

							LEFT JOIN '. $uwf->getTable() .' as w ON w.id = (select w.id
																		from '. $uwf->getTable() .' as w
																		where w.user_id = c.user_id
																			and w.effective_date <= c.date_stamp
																			and w.deleted = 0
																			order by w.effective_date desc LiMiT 1)

							LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
							LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as r ON b.job_id = r.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as s ON b.job_item_id = s.id';
		}

		$query .= '	WHERE d.company_id = :company_id';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND d.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND d.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND d.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['branch_id']) AND isset($filter_data['branch_id'][0]) AND !in_array(-1, (array)$filter_data['branch_id']) ) {
			$query  .=	' AND b.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND b.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}


		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND b.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND b.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND r.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}


		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND c.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND c.date_stamp <= :end_date';
		}

		$query .= 	'
						AND (a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getLastPunchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array('b.branch_id', 'c.date_stamp', 'd.last_name', 'a.time_stamp', 'a.status_id', 'b.branch_id', 'b.department_id', 'e.type_id');
		if ( $order == NULL ) {
			$order = array( 'b.branch_id' => 'asc', 'd.last_name' => 'asc', 'a.time_stamp' => 'desc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset($filter_data['include_job_ids']) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset($filter_data['job_group_ids']) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset($filter_data['job_item_ids']) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$sf = new StationFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select
							a.id as punch_id,
							a.punch_control_id as punch_control_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.time_stamp as time_stamp,
							a.actual_time_stamp as actual_time_stamp,

							b.user_date_id as user_date_id,
							c.date_stamp as date_stamp,
							b.branch_id as branch_id,
							b.department_id as department_id,
							b.job_id as job_id,
							b.job_item_id as job_item_id,
							b.note as note,

							c.user_id as user_id,

							e.type_id as station_type_id,
							e.station_id as station_station_id,
							e.source as station_source,
							e.description as station_description

					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id
							LEFT JOIN '. $sf->getTable() .' as e ON a.station_id = e.id
							LEFT JOIN (
								select tmp2_d.id, max(tmp2_a.time_stamp) as max_punch_time_stamp
									from 	'. $this->getTable() .' as tmp2_a
									LEFT JOIN '. $pcf->getTable() .' as tmp2_b ON tmp2_a.punch_control_id = tmp2_b.id
									LEFT JOIN '. $udf->getTable() .' as tmp2_c ON tmp2_b.user_date_id = tmp2_c.id
									LEFT JOIN '. $uf->getTable() .' as tmp2_d ON tmp2_c.user_id = tmp2_d.id
									WHERE tmp2_d.company_id = :company_id';

									if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
										$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
										$query  .=	' AND tmp2_c.date_stamp >= :start_date';
									}
									if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
										$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
										$query  .=	' AND tmp2_c.date_stamp <= :end_date';
									}

									$query .= '
										AND tmp2_a.time_stamp is not null
										AND ( tmp2_a.deleted = 0 AND tmp2_b.deleted = 0 AND tmp2_c.deleted = 0 )
									group by tmp2_d.id
							) as tmp2 ON c.user_id = tmp2.id AND a.time_stamp = tmp2.max_punch_time_stamp

					';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON b.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON b.job_item_id = y.id';
		}

		$ph[':company_id'] = $company_id;
		$query .= '	WHERE tmp2.id IS NOT NULL AND d.company_id = :company_id';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND d.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND d.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND d.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query  .=	' AND b.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query  .=	' AND b.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}

		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND b.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND b.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND x.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			/*
			$ph[] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND c.date_stamp >= ?';
			*/
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateTimeString();
			$query  .=	' AND a.time_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateTimeString();
			$query  .=	' AND a.time_stamp <= :end_date';
		}

		//The Transfer where clause is an attempt to keep transferred punches from appearing twice.
		$query .= 	'
						AND ( a.transfer = 0 OR ( a.transfer = 1 AND a.status_id = 10) )
						AND ( a.deleted = 0 AND b.deleted =0 AND c.deleted = 0 AND d.deleted = 0 )
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
