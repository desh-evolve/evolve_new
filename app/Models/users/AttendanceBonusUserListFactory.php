<?php

namespace App\Models\Users; 
use IteratorAggregate;

class AttendanceBonusUserListFactory extends AttendanceBonusUserFactory implements IteratorAggregate {
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

		return $this;
	}
        
    
   function getByUserIdAndAttendanceBonusId($user_id,$bonus_attendance_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}
                
                
                if ( $bonus_attendance_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
                                        'bonus_attendance_id' =>$bonus_attendance_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ? 
                                                AND bonus_attendance_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

  
    function getByBonusAttendanceId($bonus_attendance_id, $where = NULL, $order = NULL) {
		
                
                if ( $bonus_attendance_id == '') {
			return FALSE;
		}

		$ph = array(
					
                                        'bonus_attendance_id' =>$bonus_attendance_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	bonus_attendance_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

        
}
