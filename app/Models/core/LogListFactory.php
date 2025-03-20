<?php

namespace App\Models\Core;

use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class LogListFactory extends LogFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
				';
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
					';
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

		$uf = new UserFactory();

		$ph = array(
					':id' => $id,
					':company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	a.id = :id
						AND b.company_id = :company_id
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getLastEntryByUserIdAndActionAndTable($user_id, $action, $table_name) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $action == '') {
			return FALSE;
		}

		if ( $table_name == '') {
			return FALSE;
		}

		$action_key = Option::getByValue($action, $this->getOptions('action') );
		if ($action_key !== FALSE) {
			$action = $action_key;
		}

		$ph = array(
					':user_id' => $user_id,
					':table_name' => $table_name,
					':action_id' => $action,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = :user_id
						AND table_name = :table_name
						AND action_id = :action_id
					ORDER BY date desc
					LIMIT 1
					';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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

		$additional_order_fields = array('b.last_name');
		if ( $order == NULL ) {
			$order = array( 'date' => 'desc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);


		if ( isset($filter_data['user_ids']) ) {
			$filter_data['user_id'] = $filter_data['user_ids'];
		}
		if ( isset($filter_data['log_action_ids']) ) {
			$filter_data['log_action_id'] = $filter_data['log_action_ids'];
		}
		if ( isset($filter_data['log_table_name_ids']) ) {
			$filter_data['log_table_name_id'] = $filter_data['log_table_name_ids'];
		}

		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	b.company_id = :company_id
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['log_action_id']) AND isset($filter_data['log_action_id'][0]) AND !in_array(-1, (array)$filter_data['log_action_id']) ) {
			$query  .=	' AND a.action_id in ('. $this->getListSQL($filter_data['log_action_id'], $ph) .') ';
		}
		if ( isset($filter_data['log_table_name_id']) AND isset($filter_data['log_table_name_id'][0]) AND !in_array(-1, (array)$filter_data['log_table_name_id']) ) {
			$query  .=	' AND a.table_name in ('. $this->getListSQL($filter_data['log_table_name_id'], $ph) .') ';
		}
		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[':start_date'] = $filter_data['start_date'];
			$query  .=	' AND a.date >= :start_date';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[':end_date'] = $filter_data['end_date'];
			$query  .=	' AND a.date <= :end_date';
		}

		$query .= 	'
						AND ( b.deleted = 0 )
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

	function getByPhonePunchDataByCompanyIdAndStartDateAndEndDate($company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$ph = array(
					//'company_id' => $company_id,
					':start_date' => $start_date,
					':end_date' => $end_date,
					);

		$query = 'select 	m.*,
							CASE WHEN m.calls > m.minutes THEN m.calls ELSE m.minutes END as billable_units
							from (
								select 	company_id,
										product,
										sum(seconds)/60 as minutes,
										count(*) as calls,
										count(distinct(user_id)) as unique_users
								from
										( 	select 	company_id,
													user_id,
													CASE WHEN seconds < 60 THEN 60 ELSE seconds END as seconds,
													product from
													( 	select 	a.id,
																b.company_id,
																a.user_id,
																a.description,
																array_to_string( regexp_matches(a.description, \'([0-9]{1,3})s$\', \'i\'), \'\')::int as seconds,
																CASE WHEN a.description ILIKE \'%Destination: unknown%\' THEN \'local\' ELSE \'tollfree\' END as product
														from system_log as a
															LEFT JOIN users as b ON a.user_id = b.id
														where a.table_name = \'punch\'
															AND ( a.description ILIKE \'Telephone Punch End%\' )
															AND (a.date >= :start_date AND a.date < :end_date ) ';

															if ( $company_id != '' AND ( isset($company_id[0]) AND !in_array(-1, (array)$company_id) ) ) {
																$query  .=	' AND company_id in ('. $this->getListSQL($company_id, $ph) .') ';
															}

		$query .= '									) as tmp
										) as tmp2
								group by company_id, product ) as m
							LEFT JOIN company as n ON m.company_id = n.id
							order by product, name;
					';

		//$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
		Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = DB::select($query, $ph);

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

		$additional_order_fields = array('action_id','object_id','last_name', 'first_name');

		$sort_column_aliases = array(
									 'action' => 'action_id',
									 'object' => 'table_name',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'date' => 'desc', 'table_name' => 'asc', 'object_id' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['date']) ) {
				$order['date'] = 'desc';
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
							uf.first_name as first_name,
							uf.middle_name as middle_name,
							uf.last_name as last_name

					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = :company_id
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
		if ( isset($filter_data['action_id']) AND isset($filter_data['action_id'][0]) AND !in_array(-1, (array)$filter_data['action_id']) ) {
			$query  .=	' AND a.action_id in ('. $this->getListSQL($filter_data['action_id'], $ph) .') ';
		}
		if ( isset($filter_data['table_name']) AND isset($filter_data['table_name'][0]) AND !in_array(-1, (array)$filter_data['table_name']) ) {
			$query  .=	' AND a.table_name in ('. $this->getListSQL($filter_data['table_name'], $ph) .') ';
		}
		if ( isset($filter_data['object_id']) AND isset($filter_data['object_id'][0]) AND !in_array(-1, (array)$filter_data['object_id']) ) {
			$query  .=	' AND a.object_id in ('. $this->getListSQL($filter_data['object_id'], $ph) .') ';
		}

		//Need to support table_name -> object_id pairs for including log entires from different tables/objects.
		if ( isset( $filter_data['table_name_object_id'] ) AND is_array($filter_data['table_name_object_id']) AND count($filter_data['table_name_object_id']) > 0 ) {
			foreach( $filter_data['table_name_object_id'] as $table_name => $object_id ) {
				$ph[] = strtolower(trim($table_name));
				//$ph[] = (int)$object_id;

				$sub_query[] =	'(a.table_name = ? AND a.object_id in ('. $this->getListSQL($object_id, $ph) .') )';
			}

			if ( isset($sub_query) ) {
				$query .= ' AND ( '. implode(' OR ', $sub_query ) .' ) ';
			}
			unset($table_name, $object_id, $sub_query);
		}

		if ( isset($filter_data['description']) AND trim($filter_data['description']) != '' ) {
			$ph[':description'] = '%' . strtolower(trim($filter_data['description'])) . '%';
			$query  .=	' AND lower(a.description) LIKE :description';
		}

		$query .= 	'';

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
