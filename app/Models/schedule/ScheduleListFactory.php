<?php

namespace App\Models\Schedule;

use App\Models\Company\BranchFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateListFactory;
use App\Models\Department\DepartmentFactory;
use App\Models\PayPeriod\PayPeriodFactory;
use App\Models\Policy\AbsencePolicyFactory;
use App\Models\Policy\OverTimePolicyFactory;
use App\Models\Policy\SchedulePolicyFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserGroupFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserTitleFactory;
use App\Models\Users\UserWageFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;
use Carbon\Carbon;

class ScheduleListFactory extends ScheduleFactory implements IteratorAggregate {

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
		$udf = new UserDateFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = :company_id
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					ORDER BY a.start_time asc, a.status_id desc
					';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserDateId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':user_date_id' => $id,
					);
                
                //FL CHANGED THE QUERY FOR NPVC 20160812
                $spf = new SchedulePolicyFactory();
                  
		$query = '
					select 	a.*, b.name as shedule_policy_name
					from	'. $this->getTable() .' a
                                        LEFT JOIN '.$spf->getTable().' b ON b.id = a.schedule_policy_id
					where	a.user_date_id = :user_date_id
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
                
             
		return $this;
	}

	function getByUserDateIdAndStatusID($id, $status_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					':id' => $id,
					':status_id' => $status_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = :id
						AND status_id = :status_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'b.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					':start_date' => Carbon::parse( $start_date )->toDateString(),
					':end_date' => Carbon::parse( $end_date )->toDateString(),
					);

		//ORDER BY a.branch_id, a.department_id
		//						AND b.user_id = '. $user_id .'
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.date_stamp >= :start_date
						AND b.date_stamp <= :end_date
						AND b.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch( $user_id, $epoch, $week_start_epoch ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( $week_start_epoch == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					':user_id' => $user_id,
					':week_start_epoch' => Carbon::parse( $week_start_epoch )->toDateString(),
					':epoch' =>  Carbon::parse( $epoch )->toDateString(),
					);

		//DO NOT Include paid absences. Only count regular time towards weekly overtime.
		//And other weekly overtime polices!
		$query = '
					select 	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					where
						b.user_id = :user_id
						AND b.date_stamp >= :week_start_epoch
						AND b.date_stamp < :epoch
						AND a.status_id = 10
						AND a.deleted = 0
				';
		$total = DB::select($query, $ph);

		if ($total === FALSE ) {
            $total = 0;
        }else{
            $total = current(get_object_vars($total[0]));
        }

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByUserIdAndTypeAndDirectionFromDate($user_id, $type_id, $direction, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $direction == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		if ( strtolower($direction) == 'before' ) {
			$direction = '<';
		} elseif ( strtolower($direction) == 'after' ) {
			$direction = '>';
		} else {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'b.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					':date' => Carbon::parse( $date )->toDateString(),
					':type_id' => $type_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.date_stamp '. $direction .' :date
						AND a.status_id = :type_id
						AND b.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByStartDateAndEndDate($start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					':start_date' => Carbon::parse( $start_date )->toDateString(),
					':end_date' => Carbon::parse( $end_date )->toDateString(),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.date_stamp >= :start_date
						AND b.date_stamp <= :end_date
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getConflictingByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		Debug::Text('User ID: '. $user_id .' Start Date: '. $start_date .' End Date: '. $end_date, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		//MySQL is picky when it comes to timestamp filters on datestamp columns.
		$start_datestamp = Carbon::parse( $start_date )->toDateString();
		$end_datestamp = Carbon::parse( $end_date )->toDateString();

		$start_timestamp = Carbon::parse( $start_date )->toDateTimeString();
		$end_timestamp = Carbon::parse( $end_date )->toDateTimeString();

		$udf = new UserDateFactory();
/*
	This doesn't allow matching start/end times. SO it doesn't work with
	scheduling job transfers.
						(
							(start_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time >= '. $start_date .' AND start_time <= '. $end_date .')
							OR
							(end_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time <= '. $start_date .' AND end_time >= '. $end_date .')
						)


*/

		$ph = array(
					':user_id' => $user_id,
					':start_date_a' => $start_datestamp,
					':end_date_b' => $end_datestamp,
					':start_date1' => $start_timestamp,
					':end_date1' => $end_timestamp,
					':start_date2' => $start_timestamp,
					':end_date2' => $end_timestamp,
					':start_date3' => $start_timestamp,
					':end_date3' => $end_timestamp,
					':start_date4' => $start_timestamp,
					':end_date4' => $end_timestamp,
					':start_date5' => $start_timestamp,
					':end_date5' => $end_timestamp,
					);

		//Add filter on date_stamp for optimization
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where a.user_date_id = b.id
						AND b.user_id = :user_id
						AND b.date_stamp >= :start_date_a
						AND b.date_stamp <= :end_date_b
						AND
						(
							(start_time >= :start_date1 AND end_time <= :end_date1 )
							OR
							(start_time >= :start_date2 AND start_time < :end_date2 )
							OR
							(end_time > :start_date3 AND end_time <= :end_date3 )
							OR
							(start_time <= :start_date4 AND end_time >= :end_date4 )
							OR
							(start_time = :start_date5 AND end_time = :end_date5 )
						)
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY start_time';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getConflictingByUserDateIdAndStartDateAndEndDate($user_date_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		Debug::Text('User Date ID: '. $user_date_id .' Start Date: '. $start_date .' End Date: '. $end_date, __FILE__, __LINE__, __METHOD__,10);

		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		//MySQL is picky when it comes to timestamp filters on datestamp columns.
		$start_datestamp = Carbon::parse( $start_date )->toDateString();
		$end_datestamp = Carbon::parse( $end_date )->toDateString();

		$start_timestamp = Carbon::parse( $start_date )->toDateTimeString();
		$end_timestamp = Carbon::parse( $end_date )->toDateTimeString();

		$udf = new UserDateFactory();
/*
	This doesn't allow matching start/end times. SO it doesn't work with
	scheduling job transfers.
						(
							(start_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time >= '. $start_date .' AND start_time <= '. $end_date .')
							OR
							(end_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time <= '. $start_date .' AND end_time >= '. $end_date .')
						)


*/

		$ph = array(
					':user_date_id' => $user_date_id,
					':start_date_a' => $start_datestamp,
					':end_date_b' => $end_datestamp,
					':start_date1' => $start_timestamp,
					':end_date1' => $end_timestamp,
					':start_date2' => $start_timestamp,
					':end_date2' => $end_timestamp,
					':start_date3' => $start_timestamp,
					':end_date3' => $end_timestamp,
					':start_date4' => $start_timestamp,
					':end_date4' => $end_timestamp,
					':start_date5' => $start_timestamp,
					':end_date5' => $end_timestamp,
					);

		//Add filter on date_stamp for optimization
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where a.user_date_id = b.id
						AND a.user_date_id = :user_date_id
						AND b.date_stamp >= :start_date_a
						AND b.date_stamp <= :end_date_b
						AND
						(
							(start_time > :start_date1 AND end_time < :end_date1 )
							OR
							(start_time > :start_date2 AND start_time < :end_date2 )
							OR
							(end_time > :start_date3 AND end_time < :end_date3 )
							OR
							(start_time < :start_date4 AND end_time > :end_date4 )
							OR
							(start_time = :start_date5 AND end_time = :end_date5 )
						)
						AND ( a.deleted = 0 AND b.deleted=0 )
					ORDER BY start_time';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getScheduleObjectByUserIdAndEpoch( $user_id, $epoch ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		$udlf = new UserDateListFactory();
		$udlf->getByUserIdAndDate( $user_id, $epoch );
		if ( $udlf->getRecordCount() > 0 ) {
			Debug::Text(' Found User Date ID! ', __FILE__, __LINE__, __METHOD__,10);

			$slf = new ScheduleListFactory();
			$slf->getByUserDateId( $udlf->getCurrent()->getId() );
			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text(' Found Schedule!: ', __FILE__, __LINE__, __METHOD__,10);
				foreach($slf->rs as $s_obj ) {
					$slf->data = (array)$s_obj;
					$s_obj = $slf;
					if ( $s_obj->inSchedule( $epoch ) ) {
						Debug::Text(' in Found Schedule! Branch: '. $s_obj->getBranch(), __FILE__, __LINE__, __METHOD__,10);
						return $s_obj;
					} else {
						Debug::Text(' NOT in Found Schedule!: ', __FILE__, __LINE__, __METHOD__,10);
					}
				}

			}

		}

		return FALSE;
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

		$additional_order_fields = array('pay_period_id', 'user_id', 'last_name');

		$sort_column_aliases = array(
									 'pay_period' => 'udf.pay_period',
									 'user_id' => 'udf.user_id',
									 'status_id' => 'a.status_id',
									 'last_name' => 'uf.last_name',
									 'first_name' => 'uf.first_name',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			//$order = array( 'udf.pay_period_id' => 'asc','udf.user_id' => 'asc', 'a.start_time' => 'asc' );
			$order = array( 'uf.last_name' => 'asc', 'a.start_time' => 'asc' );
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
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['status_ids']) ) {
			$filter_data['status_id'] = $filter_data['status_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['schedule_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['schedule_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['schedule_branch_ids']) ) {
			$filter_data['schedule_branch_id'] = $filter_data['schedule_branch_ids'];
		}
		if ( isset($filter_data['schedule_department_ids']) ) {
			$filter_data['schedule_department_id'] = $filter_data['schedule_department_ids'];
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
		$uwf = new UserWageFactory();
		$apf = new AbsencePolicyFactory();
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
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.user_date_id as user_date_id,
							a.branch_id as branch_id,
							bfb.name as branch,
							a.department_id as department_id,
							dfb.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							apf.type_id as absence_policy_type_id,

							bf.name as default_branch,
							df.name as default_department,
							ugf.name as "group",
							utf.name as title,

							udf.user_id as user_id,
							udf.date_stamp as date_stamp,
							udf.pay_period_id as pay_period_id,

							uf.first_name as first_name,
							uf.last_name as last_name,
							uf.default_branch_id as default_branch_id,
							uf.default_department_id as default_department_id,
							uf.title_id as title_id,
							uf.group_id as group_id,
							uf.created_by as user_created_by,

							uwf.id as user_wage_id,
							uwf.hourly_rate as user_wage_hourly_rate,
							uwf.effective_date as user_wage_effective_date ';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						x.name as job,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id,

						y.name as job_item,
						y.manual_id as job_item_manual_id,
						y.group_id as job_item_group_id';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $udf->getTable() .' as udf ON a.user_date_id = udf.id
							LEFT JOIN '. $uf->getTable() .' as uf ON udf.user_id = uf.id
							LEFT JOIN '. $bf->getTable() .' as bf ON ( uf.default_branch_id = bf.id AND bf.deleted = 0)
							LEFT JOIN '. $bf->getTable() .' as bfb ON ( a.branch_id = bfb.id AND bfb.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as df ON ( uf.default_department_id = df.id AND df.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as dfb ON ( a.department_id = dfb.id AND dfb.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as ugf ON ( uf.group_id = ugf.id AND ugf.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as utf ON ( uf.title_id = utf.id AND utf.deleted = 0 )
							LEFT JOIN '. $apf->getTable() .' as apf ON a.absence_policy_id = apf.id
							LEFT JOIN '. $uwf->getTable() .' as uwf ON uwf.id = (select z.id
								from '. $uwf->getTable() .' as z
								where z.user_id = udf.user_id
									and z.effective_date <= udf.date_stamp
									and z.deleted = 0
									order by z.effective_date desc limit 1)
					';
					//check here
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON a.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON a.job_item_id = y.id';
		}

		$query .= '	WHERE uf.company_id = :company_id';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND uf.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND uf.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND uf.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND uf.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND uf.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND uf.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND uf.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND uf.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['schedule_branch_id']) AND isset($filter_data['schedule_branch_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_branch_id']) ) {
			$query  .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['schedule_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['schedule_department_id']) AND isset($filter_data['schedule_department_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_department_id']) ) {
			$query  .=	' AND a.department_id in ('. $this->getListSQL($filter_data['schedule_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['schedule_policy_id']) AND isset($filter_data['schedule_policy_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_policy_id']) ) {
			$query  .=	' AND a.schedule_policy_id in ('. $this->getListSQL($filter_data['schedule_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND udf.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}

		//Use the job_id in the schedule table so we can filter by '0' or No Job
		if ( isset($filter_data['job_id']) AND isset($filter_data['job_id'][0]) AND !in_array(-1, (array)$filter_data['job_id']) ) {
			$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND x.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND a.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}

		//====================
		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateTimeString();
			$query  .=	' AND a.start_time >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateTimeString();
			$query  .=	' AND a.start_time <= :end_date';
		}

		$query .= 	'
						AND (a.deleted = 0 AND udf.deleted = 0 AND uf.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		Debug::Arr($ph,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getReportByPayPeriodIdAndUserId($pay_period_id, $user_id, $where = NULL, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array();

		$query = '
					select 	b.user_id as user_id,
							b.pay_period_id as pay_period_id,
							a.status_id as status_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND b.pay_period_id in ('. $this->getListSQL( $pay_period_id, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					GROUP BY b.user_id,b.pay_period_id,a.status_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array( ':company_id' => $company_id );

		$query = '
					select 	b.user_id as user_id,
							b.pay_period_id as pay_period_id,
							a.status_id as status_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = :company_id ';
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

 		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}

		//Schedule/Punch branches/departments
 		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query .= 	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
 		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query .= 	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateString();
			$query  .=	' AND b.date_stamp >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateString();
			$query  .=	' AND b.date_stamp <= :end_date';
		}

		$query .= ' 	AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0)
					GROUP BY b.user_id,b.pay_period_id,a.status_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getDayReportByPayPeriodIdAndUserId($pay_period_id, $user_id, $where = NULL, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array();

		$query = '
					select 	b.user_id as user_id,
							b.pay_period_id as pay_period_id,
							b.date_stamp as date_stamp,
							a.status_id as status_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
					';

		if ( $pay_period_id != '' AND isset($pay_period_id[0]) AND !in_array(-1, (array)$pay_period_id) ) {
			$query .= ' AND b.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .') ';
		}

		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					GROUP BY b.user_id,b.pay_period_id,b.date_stamp,a.status_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getDayReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array( ':company_id' => $company_id );

		$query = '
					select 	b.user_id as user_id,
							b.pay_period_id as pay_period_id,
							b.date_stamp as date_stamp,
							a.status_id as status_id,
							a.schedule_policy_id as sch_policy_id,
                                                        start_time,end_time,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = :company_id
					';

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

 		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}

		//Schedule/Punch branches/departments
 		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query .= 	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
 		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query .= 	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
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
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0)
					GROUP BY b.user_id,b.pay_period_id,b.date_stamp,a.status_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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

		$additional_order_fields = array('schedule_policy_id', 'schedule_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', );

		$sort_column_aliases = array(
									 'updated_date' => 'a.updated_date',
									 'created_date' => 'a.created_date',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
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
		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['start_time']) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset($filter_data['end_time']) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		//"group" is a reserved word in MySQL.
		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.user_date_id as user_date_id,
							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,

							i.name as schedule_policy,

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

							m.id as user_wage_id,
							m.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						w.name as job,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id,

						x.name as job_item,
						x.manual_id as job_item_manual_id,
						x.group_id as job_item_group_id
						';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $spf->getTable() .' as i ON a.schedule_policy_id = i.id
							LEFT JOIN '. $udf->getTable() .' as c ON a.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $uwf->getTable() .' as m ON m.id = (select m.id
																		from '. $uwf->getTable() .' as m
																		where m.user_id = c.user_id
																			and m.effective_date <= c.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE d.company_id = :company_id';

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
			$query  .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND a.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['schedule_policy_id']) AND isset($filter_data['schedule_policy_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_policy_id']) ) {
			$query  .=	' AND a.schedule_policy_id in ('. $this->getListSQL($filter_data['schedule_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}


		//Use the job_id in the schedule table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND a.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND w.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}


		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateTimeString();
			$query  .=	' AND a.start_time >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateTimeString();
			$query  .=	' AND a.start_time <= :end_date';
		}

		if ( isset($filter_data['created_by']) AND isset($filter_data['created_by'][0]) AND !in_array(-1, (array)$filter_data['created_by']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['created_by'], $ph) .') ';
		}
		if ( isset($filter_data['updated_by']) AND isset($filter_data['updated_by'][0]) AND !in_array(-1, (array)$filter_data['updated_by']) ) {
			$query  .=	' AND a.updated_by in ('. $this->getListSQL($filter_data['updated_by'], $ph) .') ';
		}

		$query .= 	'
						AND (a.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
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

	function getScheduleSummaryReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('schedule_policy_id', 'schedule_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', );

		$sort_column_aliases = array(
									 'updated_date' => 'a.updated_date',
									 'created_date' => 'a.created_date',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
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
		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['start_time']) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset($filter_data['end_time']) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$udf = new UserDateFactory();
		$ppf = new PayPeriodFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					':company_id' => $company_id,
					);

		//"group" is a reserved word in MySQL.
		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.user_date_id as user_date_id,
							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							i.name as schedule_policy,

							a.absence_policy_id as absence_policy_id,
							apf.name as absence_policy,
							apf.type_id as absence_policy_type_id,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

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

							m.id as user_wage_id,
							m.hourly_rate as user_wage_hourly_rate,
							m.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= ',
						w.name as job_name,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $spf->getTable() .' as i ON ( a.schedule_policy_id = i.id AND i.deleted = 0)
							LEFT JOIN '. $apf->getTable() .' as apf ON ( a.absence_policy_id = apf.id AND apf.deleted = 0)
							LEFT JOIN '. $udf->getTable() .' as c ON a.user_date_id = c.id
							LEFT JOIN '. $ppf->getTable() .' as ppf ON c.pay_period_id = ppf.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $uwf->getTable() .' as m ON m.id = (select m.id
																		from '. $uwf->getTable() .' as m
																		where m.user_id = c.user_id
																			and m.effective_date <= c.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE d.company_id = :company_id';

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
			$query  .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND a.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['schedule_policy_id']) AND isset($filter_data['schedule_policy_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_policy_id']) ) {
			$query  .=	' AND a.schedule_policy_id in ('. $this->getListSQL($filter_data['schedule_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}


		//Use the job_id in the schedule table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND a.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND w.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}


		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = Carbon::parse($filter_data['start_date'])->toDateTimeString();
			$query  .=	' AND a.start_time >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = Carbon::parse($filter_data['end_date'])->toDateTimeString();
			$query  .=	' AND a.start_time <= :end_date';
		}

		if ( isset($filter_data['created_by']) AND isset($filter_data['created_by'][0]) AND !in_array(-1, (array)$filter_data['created_by']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['created_by'], $ph) .') ';
		}
		if ( isset($filter_data['updated_by']) AND isset($filter_data['updated_by'][0]) AND !in_array(-1, (array)$filter_data['updated_by']) ) {
			$query  .=	' AND a.updated_by in ('. $this->getListSQL($filter_data['updated_by'], $ph) .') ';
		}

		$query .= 	'
						AND (a.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		Debug::Arr($ph,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}
}
?>
