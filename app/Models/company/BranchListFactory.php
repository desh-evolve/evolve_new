<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
namespace App\Models\Company;
use Illuminate\Support\Facades\Log;

/*
 * $Revision: 5369 $
 * $Id: BranchListFactory.class.php 5369 2011-10-21 19:37:24Z ipso $
 * $Date: 2011-10-21 12:37:24 -0700 (Fri, 21 Oct 2011) $
 */

/**
 * @package Module_Company
 */
class BranchListFactory extends BranchFactory implements IteratorAggregate {

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

	static function getNameById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$lf = new BranchListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();
			return $obj->getName();
			//return $obj->getcity();
		}

		return FALSE;
	}
	static function getAddress1ById( $id ) { // eranda add code address 1
		if ( $id == '') {
			return FALSE;
		}

		$lf = new BranchListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();

			return $obj->getAddress1();
			//return $obj->getcity();
		}

		return FALSE;
	}
	static function getAddress2ById( $id ) { // eranda add code address 2
		if ( $id == '') {
			return FALSE;
		}

		$lf = new BranchListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();

			return $obj->getAddress2();
			//return $obj->getcity();
		}

		return FALSE;
	}

	static function getCityById( $id ) { // eranda add code City
		if ( $id == '') {
			return FALSE;
		}

		$lf = new BranchListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();

			return $obj->getCity();
			//return $obj->getcity();
		}

		return FALSE;
	}
	static function getPostCodeById( $id ) { // eranda add code City
		if ( $id == '') {
			return FALSE;
		}

		$lf = new BranchListFactory();
		$lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();

			return $obj->getPostalCode();
			//return $obj->getcity();
		}

		return FALSE;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
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
	
	/**
	 * ARSP NOTE--> I ADDED THIS CODE FOR THUNDER & NEON
	 * getByBranchShortId()
	 */
	function getBranchShortIdById($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == 0) {
			return NULL;
		}
                
                $lf = new BranchListFactory();
                $lf = $lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$obj = $lf->getCurrent();
                        $branch_short_id = $obj->getBranchShortID();
                        
                        if($branch_short_id == FALSE)
                        {
                            return NULL;
                        }
                        else
                        {
                            return $branch_short_id;
                        }
		}
	} 	

	function getByCompanyIdAndStatus($company_id, $status_id, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}
		if ( $status_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'status_id' => $status_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	status_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdAndLongitudeAndLatitude($company_id, $longitude, $latitude, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'longitude' => 'asc', 'latitude' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ? ';

		//isset() returns false on NULL.
		$query .= $this->getWhereClauseSQL( 'longitude', $longitude, 'numeric', $ph );
		$query .= $this->getWhereClauseSQL( 'latitude', $latitude, 'numeric', $ph );
		$query .= '	AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			$this->rs = $this->db->Execute($query, $ph);
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByManualIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	manual_id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getHighestManualIDByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'id2' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND id = ( select id
									from '. $this->getTable() .'
									where company_id = ?
										AND manual_id IS NOT NULL
										AND deleted = 0
									ORDER BY manual_id DESC
									LIMIT 1
									)
						AND deleted = 0
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);

		return $this;
	}

	function getByCompanyIdArray($company_id, $include_blank = TRUE, $include_disabled = TRUE ) {

		$blf = new BranchListFactory();
		$blf->getByCompanyId($company_id);

		return $blf->getArrayByListFactory( $blf, $include_blank, $include_disabled );
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			if ( $obj->getStatus() == 20 ) {
				$status = '(DISABLED) ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $obj->getStatus() == 10 ) ) {
				$list[$obj->getID()] = $status.$obj->getName();
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
							company_id = ?
						AND
							( created_date >=  ? OR updated_date >= ? )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->Execute($query, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('Rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}
		Debug::text('Rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
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

		$additional_order_fields = array('status_id');

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
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

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text_metaphone', $ph ) : NULL;

		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['manual_id']) ) ? $this->getWhereClauseSQL( 'a.manual_id', $filter_data['manual_id'], 'numeric', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['fax_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['fax_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( '', array( 'company_id' => $company_id, 'object_type_id' => 110, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

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
