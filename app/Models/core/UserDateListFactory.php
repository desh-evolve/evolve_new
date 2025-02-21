<?php

namespace App\Models\Core;

class UserDateListFactory extends UserDateFactory implements IteratorAggregate {

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
					'id' => $id,
					);

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {

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

	function getByIds($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndStartDateAndEndDateAndPayPeriodStatus($company_id, $start_date, $end_date, $status, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$ppf = new PayPeriodFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
						LEFT JOIN '. $ppf->getTable() .' as c ON a.pay_period_id = c.id
					where	b.company_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND c.status_id in ('. $this->getListSQL($status, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserId($user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByPayPeriodId($pay_period_id, $order = NULL) {
		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByDate($date) {
		if ( $date == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where
						a.date_stamp = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndDate($user_id, $date) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}	

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = 0
					ORDER BY id ASC
					';

		$this->rs = $this->db->Execute($query, $ph);

                
               
		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndStartDateAndEndDateAndEmptyPayPeriod($user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND ( pay_period_id = 0 OR pay_period_id IS NULL )
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndPayPeriodID($user_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .')
						AND deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndPayPeriodID($company_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					//'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	/*

		Report functions

	*/

	function getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate($time_period, $user_ids, $company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $time_period == '' ) {
			return FALSE;
		}

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		/*
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select 	a.user_id,
								(EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) ) as date,
								count(*) as total
						from	'. $this->getTable() .' as a,
								'. $uf->getTable() .' as b
						where 	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND exists(
										select id
										from '. $pcf->getTable() .' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id,(EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) )
						) tmp
					GROUP BY user_id
					';

/*
		$query = '
					select 	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select 	a.user_id,
								(date_part(\''.$time_period.'\', a.date_stamp) || \'-\' || date_part(\'year\', a.date_stamp) ) as date,
								count(*) as total
						from	'. $this->getTable() .' as a,
								'. $uf->getTable() .' as b
						where 	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND exists(
										select id
										from '. $pcf->getTable() .' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id,(date_part(\''. $time_period.'\', a.date_stamp) || \'-\' ||  date_part(\'year\', a.date_stamp) )
						) tmp
					GROUP BY user_id
					';
*/
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function deleteByUserIdAndDateAndDeleted( $user_id, $date, $deleted ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}

		if ( $deleted == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'date' => $this->db->BindDate( $date ),
					'deleted' => (int)$deleted
					);

		$query = '
					delete
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = ?
					';

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getArrayByListFactory($lf) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$list[] = $obj->getID();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
        
        
        function getTotalNopayTime($user_id,$absence_policy_id,$nopay_start_date,$nopay_end_date){
            
              if ( $user_id == '' ) {
			return FALSE;
		}
                
                
              if ( $absence_policy_id == '' ) {
			return FALSE;
		}
                
              if ( $nopay_start_date == '' ) {
			return FALSE;
		}
                
                
                $ph = array(
					'user_id' => $user_id,
                                        'absence_policy_id'=>$absence_policy_id,
					
					);
                
                
                $udtf= new UserDateTotalFactory();
                
                $query = "
					SELECT sum(udt.total_time ) as total_nopay_time
FROM  ". $this->getTable() ." as a
inner join ". $udtf->getTable() ." as udt on a.id = udt.user_date_id
where a.user_id = ? 
and udt.status_id = 30
and udt.type_id = 10
and udt.absence_policy_id = ? 
and a.date_stamp between '".$nopay_start_date."' AND '".$nopay_end_date."'
and a.deleted = 0 
and udt.deleted = 0";

		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
        }

        
        
        
        
         
function getTotalNopayTimeByPayperiods($user_id,$absence_policy_id,$pay_periods_id){
            
              if ( $user_id == '' ) {
			return FALSE;
		}
                
                
              if ( $absence_policy_id == '' ) {
			return FALSE;
		}
                
              if ( $pay_periods_id == '' ) {
			return FALSE;
		}
                
                
                $ph = array(
					'user_id' => $user_id,
                                        'absence_policy_id'=>$absence_policy_id,
                                        'pay_period_id'=>$pay_periods_id,
					
					);
                
                
                $udtf= new UserDateTotalFactory();
                
                $query = "
					SELECT sum(udt.total_time ) as total_nopay_time
FROM  ". $this->getTable() ." as a
inner join ". $udtf->getTable() ." as udt on a.id = udt.user_date_id
where a.user_id = ? 
and udt.status_id = 30
and udt.type_id = 10
and udt.absence_policy_id = ? 
and a.pay_period_id = ?  
and a.deleted = 0 
and udt.deleted = 0";

		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
        }

}
?>
