<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5454 $
 * $Id: UserDeductionListFactory.class.php 5454 2011-11-04 02:19:05Z ipso $
 * $Date: 2011-11-03 19:19:05 -0700 (Thu, 03 Nov 2011) $
 */

/**
 * @package Module_Users
 */
class UserDeductionListFactory extends UserDeductionFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0
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

		if ( is_array($id) ) {
			$this->rs = FALSE;
		} else {
			$this->rs = $this->getCache($id);
		}

		if ( $this->rs === FALSE ) {
			$ph = array();

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where	id in ('. $this->getListSQL($id, $ph) .')
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			if ( !is_array($id) ) {
				$this->saveCache($this->rs,$id);
			}
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
		return $this->getByCompanyIdAndId( $company_id, $id, $order );
	}
	function getByCompanyIdAndId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.id = ?
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b,
							'. $cdf->getTable() .' as c
					where
						a.user_id = b.id
						AND a.company_deduction_id = c.id
						AND b.company_id = ?
						AND a.deleted = 0
					ORDER BY c.calculation_order
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndCompanyDeductionId($company_id, $deduction_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $deduction_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'deduction_id' => $deduction_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.company_deduction_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndCompanyDeductionId($user_id, $deduction_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $deduction_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'deduction_id' => $deduction_id,
					);
                
                if ( $order == NULL ) {
			$order = array( 'b.id' => 'asc');
			
		}

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND a.company_deduction_id = ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND (a.deleted = 0 AND b.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndCountryID($user_id, $country_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $country_id == '') {
			return FALSE;
		}

		//$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = array(
					'user_id' => $user_id,
					'country_id' => $country_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $cdf->getTable() .' as b
					where
						a.company_deduction_id = b.id
						AND a.user_id = ?
						AND b.country = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByUserIdAndPayStubEntryAccountID($user_id, $pse_account_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pse_account_id == '') {
			return FALSE;
		}

		//$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $cdf->getTable() .' as b
					where
						a.company_deduction_id = b.id
						AND a.user_id = ?
						AND b.pay_stub_entry_account_id in ('. $this->getListSQL($pse_account_id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndUserId($company_id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'c.status_id' => 'asc', 'c.calculation_order' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b,
							'. $cdf->getTable() .' as c
					where
						a.user_id = b.id
						AND a.company_deduction_id = c.id
						AND b.company_id = ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND (a.deleted = 0 AND c.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        
     function getByCompanyIdAndUserIdForMid($company_id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'c.status_id' => 'asc', 'c.calculation_order' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$cdf = new CompanyDeductionFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b,
							'. $cdf->getTable() .' as c
					where
						a.user_id = b.id
						AND a.company_deduction_id = c.id
						AND b.company_id = ?
						AND a.user_id in ('. $this->getListSQL($user_id, $ph) .')
                                                AND a.company_deduction_id not in (2,97,24,61,66,67,72)
						AND (a.deleted = 0 AND c.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        

	function getByCompanyIdAndUserIdAndId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.user_id = ?
						AND a.id = ?
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

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

		$additional_order_fields = array();
		if ( $order == NULL ) {
			$order = array( 'uf.last_name' => 'asc', 'uf.first_name' => 'asc');
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['uf.last_name']) ) {
				$order['uf.last_name'] = 'asc';
			}
			if ( !isset($order['uf.first_name']) ) {
				$order['uf.first_name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							uf.first_name as first_name,
							uf.last_name as last_name,
							uf.country as country,
							uf.province as province,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	uf.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
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
		if ( isset($filter_data['company_deduction_id']) AND isset($filter_data['company_deduction_id'][0]) AND !in_array(-1, (array)$filter_data['company_deduction_id']) ) {
			$query  .=	' AND a.company_deduction_id in ('. $this->getListSQL($filter_data['company_deduction_id'], $ph) .') ';
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
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

}
?>
