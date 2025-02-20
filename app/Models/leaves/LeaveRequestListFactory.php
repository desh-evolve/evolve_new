<?php

namespace App\Models\Leaves;
use IteratorAggregate;

class LeaveRequestListFactory extends LeaveRequestFactory  implements IteratorAggregate {
    //put your code here
    
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
        
        
        
    function getByIdAndCompanyId($id, $company_id=1, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
                                        'company_id'=>$company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ? 
                                         company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        
    function getByUserIdAndCompanyId($user_id, $company_id=1, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
                                        'company_id'=>$company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?  
                                         AND company_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        
        
    function getByCoveredEmployeeId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		
		
			$ph = array(
						'covered_by' => $id,
						);

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where covered_by = ? 
                                                AND is_covered_approved = 1 
                                                AND status = 10 
						AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			$this->saveCache($this->rs,$id);
		

		return $this;
	}
        
        
        
        
    function getBySupervisorEmployeeId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		
		
			$ph = array(
						'supervisor_id' => $id,
						);

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where supervisor_id = ? 
                                                AND is_covered_approved = 1 
                                                AND is_supervisor_approved = 0 
                                                AND status = 10 
						AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			$this->saveCache($this->rs,$id);
		

		return $this;
	}
        
        
        
        
        
        function getByHrEmployeeId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		
		/*
			$ph = array(
						'supervisor_id' => $id,
						);
*/
			$query = '
						select 	*
						from	'. $this->getTable() .'
						where  is_covered_approved = 1 
                                                AND is_supervisor_approved = 1 
                                                AND is_hr_approved = 0 
                                                AND status = 10 
						AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			$this->saveCache($this->rs,$id);
		

		return $this;
	}
        
        
        function checkUserHasLeaveForDay($user_id,$leave_date, $where = NULL, $order = NULL)
        {
                if ( $leave_date == '') {
			return FALSE;
		}
                
                if ( $user_id == '') {
			return FALSE;
		}
                
                
                   $ph = array(               
						'user_id' => $user_id,
						);

			$query = "select * from ". $this->getTable() ." as a "
                                . " where '".$leave_date."' between a.leave_from and a.leave_to "
                                . " and a.user_id = ? "
                                . " and a.is_covered_approved = 1 "
                                . " and a.is_supervisor_approved = 1 "
                                . " and a.is_hr_approved = 1 "
                                . " and a.deleted = 0";
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			//$this->saveCache($this->rs,$id);
		

		return $this;
        }
        
        
        
                
        function checkUserHasLeaveTypeForDay($user_id,$leave_date,$leave_policy, $where = NULL, $order = NULL)
        {
                if ( $leave_date == '') {
			return FALSE;
		}
                
                if ( $user_id == '') {
			return FALSE;
		}
                
                
                   $ph = array(               
						'user_id' => $user_id,
                                                'accurals_policy_id'=> $leave_policy,
						);

			$query = "select * from ". $this->getTable() ." as a "
                                . " where '".$leave_date."' between a.leave_from and a.leave_to "
                                . " and a.user_id = ? "
                                . " and a.is_covered_approved = 1 "
                                . " and a.is_supervisor_approved = 0 "
                                . " and a.is_hr_approved = 0 "
                                . " and a.accurals_policy_id = ?"
                                . " and a.status = 10"
                                . " and a.deleted = 0";
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, $ph);

			//$this->saveCache($this->rs,$id);
		

		return $this;
        }
        
        
   
        
          function getAllConfirmedLeave($id, $data,$where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		
		/*
			$ph = array(
						'supervisor_id' => $id,
						);
*/
			$query = '
						select 	*
						from	'. $this->getTable() .'
						where  is_covered_approved = 1 
                                                AND is_supervisor_approved = 1 
                                                ';
                        
                        if(isset($data['user_id'])){
                               $query .= '  AND user_id = '.$data['user_id'];
                        }
                        
                        
                        if(isset($data['start_date']) && !empty($data['start_date'])){
                               $query .= "  AND leave_from > '".$data['start_date']."'";
                        }
                        
                        
                         if(isset($data['end_date']) && !empty($data['end_date'])){
                               $query .= "  AND leave_from < '".$data['end_date']."'";
                        }
                        
                        
                         $query .= '             AND status = 10 
						AND deleted = 0';
                        
                        
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->db->Execute($query, isset($ph) ? $ph : []);

			$this->saveCache($this->rs,$id);
		

		return $this;
	}
        
        
                        
        function getPayperiodsShortLeaveCount($user_id,$leave_policy, $pp_start_date,$pp_end_date,$where = NULL, $order = NULL)
        {
                if ( $pp_end_date == '') {
			return FALSE;
		}
                
                if ( $pp_end_date == '') {
			return FALSE;
		}
                
                if ( $user_id == '') {
			return FALSE;
		}
                
                
                   $ph = array(               
						'user_id' => $user_id,
                                                'accurals_policy_id'=> $leave_policy,
						);

			$query = "select count(*) as count from ". $this->getTable() ." as a "
                                . " WHERE  a.leave_from between '".$pp_start_date."' and '".$pp_end_date."'"
                                . " and a.user_id = ? "
                                . " and a.accurals_policy_id = ?"
                                . " and a.status = 10"
                                . " and a.deleted = 0";
                     
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$row = $this->db->GetRow($query, $ph);

                            if ( $row['count'] === NULL ) {
                                    $row['count'] = 0;
                            }

		

			//$this->saveCache($this->rs,$id);
		

		return $row;
        }
        
        
        
}
