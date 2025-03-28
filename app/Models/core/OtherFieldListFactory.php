<?php

namespace App\Models\Core;

use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class OtherFieldListFactory extends OtherFieldFactory implements IteratorAggregate {

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

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = :id
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	// function getByCompanyIdAndTypeID($id, $type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
	// 	if ( $id == '' ) {
	// 		return FALSE;
	// 	}

	// 	if ( $type_id == '' ) {
	// 		return FALSE;
	// 	}

	// 	$ph = array(
	// 				'id' => (int)$id,
	// 				//'type_id' => (int)$type_id,
	// 				);

	// 	$query = '
	// 				select 	*
	// 				from	'. $this->getTable() .' as a
	// 				where	company_id = ?
	// 					AND type_id in ('. $this->getListSQL($type_id, $ph) .')
	// 					AND deleted = 0';
	// 	$query .= $this->getWhereSQL( $where );
	// 	$query .= $this->getSortSQL( $order );

	// 	if ($limit == NULL) {
	// 		$this->rs = DB::select($query, $ph);
	// 	} else {
	// 		$this->rs = DB::select($query, $ph);
	// 		//$this->rs = DB::select($query, $ph);
	// 	}

	// 	return $this;
	// }

	function getByCompanyIdAndTypeID($id, $type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if (empty($id) || empty($type_id)) {
			return FALSE;
		}

		// Ensure $type_id is an array
		if (!is_array($type_id)) {
			$type_id = [$type_id];
		}

		$ph = array(
					':id' => (int)$id,
					//'type_id' => (int)$type_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = :id
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			// Handle pagination if needed
			$this->rs = DB::select($query, $ph);
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
					':company_id' => $company_id,
					':id' => $id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
					where
						a.company_id = :company_id
						AND	a.id = :id
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndTypeIDArray($id, $type_id, $key_prefix = NULL, $name_prefix = NULL ) {

		$oflf = new OtherFieldListFactory();
		$oflf->getByCompanyIdAndTypeID( $id, $type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL );

		if ( $oflf->getRecordCount() > 0 ) {
			foreach($oflf->rs as $obj) {
				$oflf->data = (array)$obj;
				if ( is_array($key_prefix) ) {
					if ( isset($key_prefix[$oflf->getType()]) ) {
						$prefix = $key_prefix[$oflf->getType()];
					} else {
						$prefix = NULL;
					}
				} else {
					$prefix = $key_prefix;
				}

				if ( is_array($name_prefix) ) {
					if ( isset($name_prefix[$oflf->getType()]) ) {
						$prefix2 = $name_prefix[$oflf->getType()];
					} else {
						$prefix2 = NULL;
					}
				} else {
					$prefix2 = $name_prefix;
				}

				if ( $oflf->getOtherID1() != '' ) {
					$retarr[$prefix.'other_id1'] = $prefix2.$oflf->getOtherID1();
				}
				if ( $oflf->getOtherID2() != '' ) {
					$retarr[$prefix.'other_id2'] = $prefix2.$oflf->getOtherID2();
				}
				if ( $oflf->getOtherID3() != '' ) {
					$retarr[$prefix.'other_id3'] = $prefix2.$oflf->getOtherID3();
				}
				if ( $oflf->getOtherID4() != '' ) {
					$retarr[$prefix.'other_id4'] = $prefix2.$oflf->getOtherID4();
				}
				if ( $oflf->getOtherID5() != '' ) {
					$retarr[$prefix.'other_id5'] = $prefix2.$oflf->getOtherID5();
				}
				if ( $oflf->getOtherID6() != '' ) {
					$retarr[$prefix.'other_id6'] = $prefix2.$oflf->getOtherID6();
				}
				if ( $oflf->getOtherID7() != '' ) {
					$retarr[$prefix.'other_id7'] = $prefix2.$oflf->getOtherID7();
				}
				if ( $oflf->getOtherID8() != '' ) {
					$retarr[$prefix.'other_id8'] = $prefix2.$oflf->getOtherID8();
				}
				if ( $oflf->getOtherID9() != '' ) {
					$retarr[$prefix.'other_id9'] = $prefix2.$oflf->getOtherID9();
				}
				if ( $oflf->getOtherID10() != '' ) {
					$retarr[$prefix.'other_id10'] = $prefix2.$oflf->getOtherID10();
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
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
					':company_id' => $company_id,
					':created_date' => $date,
					':updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
							company_id = :company_id
						AND
							( created_date >= :created_date OR updated_date >= :updated_date )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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

		$additional_order_fields = array('type_id');

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['type_id']) ) {
				$order = Misc::prependArray( array('type_id' => 'asc'), $order );
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id,
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
					where	a.company_id = :company_id';
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
