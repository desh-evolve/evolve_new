<?php

namespace App\Models\Leaves;

use App\Models\Core\Misc;
use App\Models\Policy\AccrualPolicyFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class AbsenceLeaveUserEntryRecordListFactory extends AbsenceLeaveUserEntryRecordFactory implements IteratorAggregate {

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

			$this->rs = DB::select($query, $ph);

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByUserDateId($id, $where = NULL, $order = NULL) { 
		if ( $id == '') {
			return FALSE;
		}
 
		$ph = array(
                                'user_date_id' => $id
                                );

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order ); 
		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getAbsencePolicyByUserDateId($id, $where = NULL, $order = NULL) { 
		if ( $id == '') {
			return FALSE;
		}
 
		$ph = array(
                'user_date_id' => $id
                );

        $apf = TTnew('AbsencePolicyFactory');
		$query = ' select 	a.*, b.name absence_name
					from	'. $this->getTable() .' a
                    LEFT JOIN '.$apf->getTable().' b 
                    ON b.id = a.absence_policy_id
					where	a.user_date_id = ? 
					AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order ); 
		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByAbsenceUserId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'absence_leave_user_id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.absence_leave_user_id = ?
							AND deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}

	function getByAbsenceUserIdAndUserId($id,$user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'absence_leave_user_id' => $id,
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.absence_leave_user_id = ? ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph);
	}
	function getByAbsencePolicyIdAndUserId($id,$user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'absence_policy_id' => $id,
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.absence_policy_id = ? 
                                         AND a.user_id = ?';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph); 

	}
        
    function getByAbsencePolicyIdAndUserIdUserDateId($absence_p_id, $user_id, $user_date_id, $where = NULL, $order = NULL) {
		if ( $absence_p_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'alur.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
                'absence_policy_id' => $absence_p_id,
                'user_id' => $user_id,
                'user_date_id' => $user_date_id,
                );

                $udf = TTnew('UserDateFactory');
                $udtf = TTnew('UserDateTotalFactory');
                
		$query = 'SELECT alur . * 
                            FROM  '. $udtf->getTable() .' udt
                            RIGHT JOIN '.$udf->getTable().' ud ON ud.id = udt.user_date_id
                            RIGHT JOIN '.$this->getTable().' alur ON alur.user_date_id = ud.id
                            WHERE udt.absence_policy_id = ?
                            AND ud.user_id = ?
                            AND udt.deleted =  0
                            AND alur.user_date_id =  ?';
                
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph); 

	}


	function getgetAbsenceLeaveIdByAbsencePolicyIdAndUserIdUserDateId($absence_p_id, $user_id, $user_date_id, $where = NULL, $order = NULL) {
		if ( $absence_p_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'alur.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
                'absence_policy_id' => $absence_p_id,
                'user_id' => $user_id,
                'user_date_id' => $user_date_id,
                );

                
		$query = 'SELECT alur . * 
                            FROM  '.$this->getTable().' alur 
                            WHERE alur.absence_policy_id = ?
                            AND alur.user_id = ?
                            AND alur.deleted =  0
                            AND alur.user_date_id =  ?';
                
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph); 
                
           

	}


        function getByAbsencePolicyIdAndUserId2($id,$user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'alur.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
                            'absence_policy_id' => $id,
                            'user_id' => $user_id
                            );

                $udf = TTnew('UserDateFactory');
                $udtf = TTnew('UserDateTotalFactory');
                
		$query = 'SELECT alur . * 
                            FROM  '. $udtf->getTable() .' udt
                            RIGHT JOIN '. $udf->getTable() .' ud ON ud.id = udt.user_date_id
                            RIGHT JOIN '. $this->getTable() .' alur ON alur.user_date_id = ud.id
                            WHERE udt.absence_policy_id = ?
                            AND ud.user_id = ?
                            AND alur.deleted = 0
                            AND udt.deleted = 0 ';  
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = DB::select($query, $ph); 

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

		$additional_order_fields = array('type_id');

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['type_id']) ) {
				$order = Misc::prependArray( array('type_id' => 'asc'), $order );
			}
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$apf = new AccrualPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							apf.name as accrual_policy,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $apf->getTable() .' as apf ON ( a.accrual_policy_id = apf.id AND apf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
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
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_stub_entry_account_id']) AND isset($filter_data['pay_stub_entry_account_id'][0]) AND !in_array(-1, (array)$filter_data['pay_stub_entry_account_id']) ) {
			$query  .=	' AND a.pay_stub_entry_account_id in ('. $this->getListSQL($filter_data['pay_stub_entry_account_id'], $ph) .') ';
		}
		if ( isset($filter_data['accrual_policy_id']) AND isset($filter_data['accrual_policy_id'][0]) AND !in_array(-1, (array)$filter_data['accrual_policy_id']) ) {
			$query  .=	' AND a.accrual_policy_id in ('. $this->getListSQL($filter_data['accrual_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE ?';
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

        
        //result for dropdown FL ADDED 20160718
	function getAllByIdArray( $include_blank = TRUE) {

		$aplf = new AbsenceLeaveListFactory();
		$aplf->getAll();

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($aplf as $ap_obj) {
			$list[$ap_obj->getId()] = $ap_obj->getName();
//			$list[$ap_obj->getTimeSec()] = $ap_obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
        
       

}
?>
