<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4993 $
 * $Id: PayStubListFactory.class.php 4993 2011-07-15 23:32:11Z ipso $
 * $Date: 2011-07-15 16:32:11 -0700 (Fri, 15 Jul 2011) $
 */

/**
 * @package Module_Pay_Stub
 */
class PayStubListFactory extends PayStubFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
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

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						);

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

	function getByIdAndCompanyIdAndIgnoreDeleted($id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id
					);

		//Include deleted pay stubs, for re-calculating YTD amounts?
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b
					where	a.user_id = b.id
						AND a.id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByIdAndUserId($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => (int)$id,
					'user_id' => (int)$user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND user_id = ?
						AND deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByUserIdAndCompanyIdAndPayPeriodId($user_id, $company_id, $pay_period_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $pay_period_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'a.user_id' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						';

		if ( $pay_period_id != '' AND isset($pay_period_id[0]) AND !in_array(-1, (array)$pay_period_id) ) {
			$query .= ' AND a.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .') ';
		}

		$query .= '
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getLastPayStubByUserIdAndStartDate($user_id, $start_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.start_date' => 'desc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND a.start_date < ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND ( a.deleted = 0 AND c.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc', 'a.advance' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND ( a.deleted = 0 AND c.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByCompanyIdAndId($company_id, $id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND a.deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByUserIdAndId($user_id, $id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'user_id' => $user_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.id = ?
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByPayPeriodId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCurrencyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	currency_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, FALSE );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndPayPeriodId($company_id, $pay_period_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $pay_period_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL OR !is_array($order) ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();
		$pplf = new PayPeriodListFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b,
							'. $pplf->getTable() .' as c
					where	a.user_id = b.id
						AND a.pay_period_id = c.id
						AND b.company_id = ?
						AND a.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .')
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		//$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
/*
	function getByCompanyIdAndTransactionStartDateAndTransactionEndDate($company_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL OR !is_array($order) ) {
			$order = array( 'a.transaction_date' => 'desc', 'a.advance' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = '. $company_id .'
						AND a.transaction_date >= '. $this->db->BindTimeStamp( TTDate::getBeginDayEpoch( $start_date ) ) .'
						AND a.transaction_date <= '. $this->db->BindTimeStamp( TTDate::getBeginDayEpoch( $end_date ) ) .'
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->rs = $this->db->Execute($query);

		return $this;
	}
*/
	function getByUserIdAndPayPeriodId($user_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pay_period_id == '') {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

        
             /*FL ADDED FOR MEMBER COUNT FOR PARTICULAR PAYPERIOD */
        
        function getMembersForPayPeriod($pay_period_id, $where = NULL, $order = NULL) {
		 
		if ( $pay_period_id == '') {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => $pay_period_id, 
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND deleted = 0
                                                GROUP BY user_id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        /*END FL ADDED FOR MEMBER COUNT FOR PARTICULAR PAYPERIOD */
        
        
        
	function getByUserIdAndPayPeriodIdAndAdvance($user_id, $pay_period_id, $advance, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pay_period_id == '') {
			return FALSE;
		}

		/*
		//Advance is boolean, don't need this check.
		if ( $advance == '') {
			return FALSE;
		}
		*/

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'user_id' => $user_id,
					'advance' => $this->toBool( $advance ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND user_id = ?
						AND advance = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

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
		Debug::Arr($order,'aOrder Data:', __FILE__, __LINE__, __METHOD__,10);

		$additional_order_fields = array('b.last_name', 'b.first_name');
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'b.last_name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always try to order by status first so UNPAID employees go to the bottom.
			if ( isset($order['last_name']) ) {
				$order['b.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
			if ( isset($order['first_name']) ) {
				$order['b.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['status']) ) {
				$order['status_id'] = $order['status'];
				unset($order['status']);
			}

			if ( isset($order['transaction_date']) ) {
				$order['last_name'] = 'asc';
			} else {
				$order['transaction_date'] = 'desc';
			}

			$strict = TRUE;
		}

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['user_id'] = $filter_data['include_user_ids'];
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
		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}
		if ( isset($filter_data['currency_ids']) ) {
			$filter_data['currency_id'] = $filter_data['currency_ids'];
		}

		//Debug::Arr($order,'bOrder Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	b.company_id = ?
					';

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND b.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND b.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND b.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND b.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
			$query  .=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query  .=	' AND a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_stub_status_id']) AND isset($filter_data['pay_stub_status_id'][0]) AND !in_array(-1, (array)$filter_data['pay_stub_status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['pay_stub_status_id'], $ph) .') ';
		}

		if ( isset($filter_data['transaction_start_date']) AND trim($filter_data['transaction_start_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_start_date'])) );
			$query  .=	' AND a.transaction_date >= ?';
		}
		if ( isset($filter_data['transaction_end_date']) AND trim($filter_data['transaction_end_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_end_date'])) );
			$query  .=	' AND a.transaction_date <= ?';
		}
		if ( isset($filter_data['transaction_date']) AND trim($filter_data['transaction_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_date'])) );
			$query  .=	' AND a.transaction_date = ?';
		}

		$query .= 	'
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
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

		$additional_order_fields = array('user_status_id','last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title', 'country', 'province', 'currency');

		$sort_column_aliases = array(
									'user_status' => 'user_status_id',
									);
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc', 'b.last_name' => 'asc' );
			$strict = FALSE;
		} else {
			if ( isset($order['transaction_date']) ) {
				$order['last_name'] = 'asc';
			} else {
				$order['transaction_date'] = 'desc';
			}

			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.status_id as user_status_id,
							b.city as city,
							b.province as province,
							b.country as country,

							b.default_branch_id as default_branch_id,
							bf.name as default_branch,
							b.default_department_id as default_department_id,
							df.name as default_department,
							b.group_id as group_id,
							ugf.name as "group",
							b.title_id as title_id,
							utf.name as title,

							cf.name as currency,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $bf->getTable() .' as bf ON ( b.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as df ON ( b.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as ugf ON ( b.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as utf ON ( b.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as cf ON ( a.currency_id = cf.id AND cf.deleted = 0 )


						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND b.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND b.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query  .=	' AND a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}

		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp($filter_data['start_date']);
			$query  .=	' AND a.transaction_date >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( $filter_data['end_date'] );
			$query  .=	' AND a.transaction_date <= ?';
		}
		/*
		if ( isset($filter_data['transaction_date']) AND trim($filter_data['transaction_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['transaction_date']));
			$query  .=	' AND a.end_date <= ?';
		}
		*/

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

		Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}
        
        function getByPayperiodsIdAndUserId($pay_periods_id,$user_id,$limit = NULL, $page = NULL)
        {
            
            if ( $pay_periods_id == '') {
			return FALSE;
            }
                
            if ( $user_id == '') {
			return FALSE;
            }
                
            
            
            $uf = new UserFactory();
            
            
            $ph = array(
					'pay_period_id' => $pay_periods_id,
                                        'user_id' => $user_id,
					);
            
            
            $query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.pay_period_id = ? AND a.user_id= ? 
                                         AND ( a.deleted = 0 AND b.deleted = 0 )
					';
            
            
             if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

            return $this;
                
                
        }

        
        
       function getByPayperiodsIdAndUserIdForPayroll($pay_periods_id,$user_id,$limit = NULL, $page = NULL)
        {
            
            if ( $pay_periods_id == '') {
			return FALSE;
            }
                
            if ( $user_id == '') {
			return FALSE;
            }
                
            
            
            $uf = new UserFactory();
            
            
            $ph = array(
					'pay_period_id' => $pay_periods_id,
                                        'user_id' => $user_id,
					);
            
            
            $query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.pay_period_id = ? AND a.user_id= ? 
                                         AND (  b.deleted = 0 )
					';
            
            
             if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

            return $this;
                
                
        }
}
?>
