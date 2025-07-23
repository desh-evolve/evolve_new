<?php

namespace App\Models\Users;

use DateTime;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class BonusDecemberListFactory  extends  BonusDecemberFactory implements IteratorAggregate {
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
		$this->data = $this->rs;
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
		$this->data = $this->rs;
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
					':company_id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}
		$this->data = $this->rs;
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
		$this->data = $this->rs;
		return $this;
	}




        function getByCompanyIdArray($company_id) {

		$bdlf = new BonusDecemberListFactory();
		$bdlf->getByCompanyId($company_id);

		$bonus_list[0] = '--';

                $start_date = new DateTime();
                $end_date = new DateTime();

		foreach ($bdlf->rs as $bonus_december_obj) {
			$bdlf->data = (array)$bonus_december_obj;
			$bonus_december_obj = $bdlf;

            $start_date->setTimestamp($bonus_december_obj->getStartDate());
            $end_date->setTimestamp($bonus_december_obj->getEndDate());

			$bonus_list[$bonus_december_obj->getID()] = $start_date->format('Y-m-d').' - '.$end_date->format('Y-m-d');
		}

		return $bonus_list;
	}


}
