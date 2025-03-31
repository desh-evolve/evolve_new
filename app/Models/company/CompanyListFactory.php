<?php
namespace App\Models\Company;

use IteratorAggregate;
use ArrayIterator;
use App\Models\Company;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Users\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyListFactory extends CompanyFactory implements IteratorAggregate
{

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from '. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit === null) {
			// Run query without limit
			$this->rs = DB::select($query);
		} else {
			// Run query with pagination
			$this->rs = DB::select(DB::raw($query . ' LIMIT :limit OFFSET :offset'), [
				'limit' => $limit,
				'offset' => ($page - 1) * $limit,
			]);
		}

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}
		$this->rs = $this->getCache($id);

		if (empty($this->rs) || $this->rs === FALSE) {
			$query = '
				SELECT *
				FROM ' . $this->getTable() . '
				WHERE id = :id
				AND deleted = 0';

			// Add any additional where conditions
			$query .= $this->getWhereSQL($where);

			// Add sorting
			$query .= $this->getSortSQL($order);

			// Prepare parameters
			$params = [
				':id' => $id
			];

			// Execute query with parameterized values
			$this->rs = DB::select($query, $params);

			// Save to cache if query was successful
			if ($this->rs !== FALSE) {
				$this->saveCache($this->rs, $id);
			}
		}

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
					':id' => $id,
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
						AND company_id = :company_id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		return $this;
	}

	function getByUserName($user_name, $where = NULL, $order = NULL) {
		if ( $user_name == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					':user_name' => strtolower( $user_name ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a, '. $uf->getTable() .' as b
					where	a.id = b.company_id
						AND b.status_id = 10
						AND b.user_name = :user_name
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);
		return $this;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			if ( $lf->getStatus() != 10 ) {
				$status = '('.Option::getByKey($lf->getStatus(), $lf->getOptions('status') ).') ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $lf->getStatus() == 10 ) ) {
				$list[$lf->getID()] = $status.$lf->getName();
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}


	static function getAllArray() {
		$clf = new CompanyListFactory();
		$clf->getAll();

		$company_list[0] = '--';

		foreach ($clf->rs as $company) {
			$clf->data = (array)$company;
			$company_list[$clf->getID()] = $clf->getName();
		}

		return $company_list;
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
									 'product_edition' => 'product_edition_id',
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
					//':company_id' => $company_id,
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
					where	1=1
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['short_name']) ) ? $this->getWhereClauseSQL( 'a.short_name', $filter_data['short_name'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['product_edition_id']) ) ? $this->getWhereClauseSQL( 'a.product_edition_id', $filter_data['product_edition_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['fax_phone']) ) ? $this->getWhereClauseSQL( 'a.fax_phone', $filter_data['fax_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['business_number']) ) ? $this->getWhereClauseSQL( 'a.business_number', $filter_data['businessnumber'], 'text', $ph ) : NULL;

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
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}
}
?>
