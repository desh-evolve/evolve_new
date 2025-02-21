<?php

namespace App\Models\Users; 
use IteratorAggregate;

class BonusDecemberUserListFactory extends BonusDecemberUserFactory implements IteratorAggregate {
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

	function getByUserIdAndBonusDecemberId($user_id,$bonus_december_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}
                
                
                if ( $bonus_december_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
                                        'bonus_december_id' =>$bonus_december_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ? 
                                                AND bonus_december_id = ? 
						AND deleted = 0';
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
                                        'bonus_december_id' => $bonus_december_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ? 
                                                AND bonus_december_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}
        
        
        
      function getByBonusDecemberId($bonus_december_id, $where = NULL, $order = NULL) {
		
                
                if ( $bonus_december_id == '') {
			return FALSE;
		}

		$ph = array(
					
                                        'bonus_december_id' =>$bonus_december_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	bonus_december_id = ? 
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

    
}
