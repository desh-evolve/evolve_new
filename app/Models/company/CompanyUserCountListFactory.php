<?php

namespace App\Models\Company;

use App\Models\Users\UserFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class CompanyUserCountListFactory extends CompanyUserCountFactory implements IteratorAggregate {

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

		$this->rs = $this->getCache($id);
		if ( empty($this->rs) || $this->rs === FALSE ) {
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

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = :id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getActiveUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						status_id = 10
						AND deleted = 0
					GROUP BY company_id
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

	function getInActiveUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						status_id != 10
						AND deleted = 0
					GROUP BY company_id
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

	function getDeletedUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						deleted = 1
					GROUP BY company_id
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

	function getMinAvgMaxByCompanyIdAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $id,
                    ':start_date' => Carbon::createFromTimestamp($start_date)->toDateString(),
                    ':end_date' => Carbon::createFromTimestamp($end_date)->toDateString(),
					);

		$query = '
					select
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND date_stamp >= :start_date
						AND date_stamp <= :end_date
						';
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

	//This function returns data for multiple companies, used by the API.
	function getMinAvgMaxByCompanyIDsAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					//':company_id' => $id,
                    ':start_date' => Carbon::createFromTimestamp($start_date)->toDateString(),
                    ':end_date' => Carbon::createFromTimestamp($end_date)->toDateString(),
					);

		$query = '
					select
							company_id,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where
						date_stamp >= :start_date
						AND date_stamp <= :end_date ';

		if ( $id != '' AND ( isset($id[0]) AND !in_array(-1, (array)$id) ) ) {
			$query  .=	' AND company_id in ('. $this->getListSQL($id, $ph) .') ';
		}

		$query .= ' group by company_id';

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

	function getMonthlyMinAvgMaxByCompanyIdAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $id,
                    ':start_date' => Carbon::createFromTimestamp($start_date)->toDateString(),
                    ':end_date' => Carbon::createFromTimestamp($end_date)->toDateString(),
					);

		// if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
		// 	//$month_sql = '(month( date_stamp ))';
		// 	$month_sql = '( date_format( date_stamp, \'%Y-%m-01\') )';
		// } else {
		// 	//$month_sql = '( date_part(\'month\', date_stamp) )';
		// 	$month_sql = '( to_char(date_stamp, \'YYYY-MM\') || \'-01\' )'; //Concat -01 to end due to EnterpriseDB issue with to_char
		// }

        // Handle MySQL vs Postgres date formatting
        if (DB::connection()->getDriverName() === 'mysql') {
            $month_sql = "DATE_FORMAT(date_stamp, '%Y-%m-01')";
        } else {
            $month_sql = "(TO_CHAR(date_stamp, 'YYYY-MM') || '-01')";
        }


		$query = '
					select
							'. $month_sql .' as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where	company_id = :company_id
						AND date_stamp >= :start_date
						AND date_stamp <= :end_date
					GROUP BY '. $month_sql .'
						';
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

	function getMonthlyMinAvgMaxByStartDateAndEndDate($start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					// ':start_date' => Carbon::parse( $start_date )->toDateString(),
					// ':end_date' => Carbon::parse( $end_date )->toDateString(),
                    ':start_date' => Carbon::createFromTimestamp($start_date)->toDateString(),
                    ':end_date' => Carbon::createFromTimestamp($end_date)->toDateString(),
					);

		// if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
		// 	//$month_sql = '(month( date_stamp ))';
		// 	$month_sql = '( date_format( date_stamp, \'%Y-%m-01\') )';
		// } else {
		// 	//$month_sql = '( date_part(\'month\', date_stamp) )';
		// 	$month_sql = '( to_char(date_stamp, \'YYYY-MM-01\') )';
		// }

        // Handle MySQL vs Postgres date formatting
        if (DB::connection()->getDriverName() === 'mysql') {
            $month_sql = "DATE_FORMAT(date_stamp, '%Y-%m-01')";
        } else {
            $month_sql = "(TO_CHAR(date_stamp, 'YYYY-MM') || '-01')";
        }

		$query = '
					select
							company_id,
							'. $month_sql .' as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where
						date_stamp >= :start_date
						AND date_stamp <= :end_date
					GROUP BY company_id,'. $month_sql .'
					ORDER BY company_id,'. $month_sql .'
						';
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

	//This gets the totals across all companies.
	function getTotalMonthlyMinAvgMaxByCompanyStatusAndStartDateAndEndDate($status_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$cf = new CompanyFactory();

		$ph = array(
					':status_id' => $status_id,
					// ':start_date' => Carbon::parse( $start_date )->toDateString(),
					// ':end_date' => Carbon::parse( $end_date )->toDateString(),
                    ':start_date' => Carbon::createFromTimestamp($start_date)->toDateString(),
                    ':end_date' => Carbon::createFromTimestamp($end_date)->toDateString(),
					);

		// if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
		// 	//$month_sql = '(month( date_stamp ))';
		// 	$month_sql = '( date_format( a.date_stamp, \'%Y-%m-01\') )';
		// } else {
		// 	//$month_sql = '( date_part(\'month\', date_stamp) )';
		// 	$month_sql = '( to_char(a.date_stamp, \'YYYY-MM-01\') )';
		// }

        // Handle MySQL vs Postgres date formatting
        if (DB::connection()->getDriverName() === 'mysql') {
            $month_sql = "DATE_FORMAT(date_stamp, '%Y-%m-01')";
        } else {
            $month_sql = "(TO_CHAR(date_stamp, 'YYYY-MM') || '-01')";
        }

		$query = '
					select
							date_stamp,
							sum(min_active_users) as min_active_users,
							sum(avg_active_users) as avg_active_users,
							sum(max_active_users) as max_active_users,

							sum(min_inactive_users) as min_inactive_users,
							sum(avg_inactive_users) as avg_inactive_users,
							sum(max_inactive_users) as max_inactive_users,

							sum(min_deleted_users) as min_deleted_users,
							sum(avg_deleted_users) as avg_deleted_users,
							sum(max_deleted_users) as max_deleted_users
					FROM (
							select
									company_id,
									'. $month_sql .' as date_stamp,
									min(a.active_users) as min_active_users,
									ceil(avg(a.active_users)) as avg_active_users,
									max(a.active_users) as max_active_users,

									min(a.inactive_users) as min_inactive_users,
									ceil(avg(a.inactive_users)) as avg_inactive_users,
									max(a.inactive_users) as max_inactive_users,

									min(a.deleted_users) as min_deleted_users,
									ceil(avg(a.deleted_users)) as avg_deleted_users,
									max(a.deleted_users) as max_deleted_users

							from	'. $this->getTable() .' as a
								LEFT JOIN '. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
							where
								cf.status_id = :status_id
								AND a.date_stamp >= :start_date
								AND a.date_stamp <= :end_date
								AND ( cf.deleted = 0 )
							GROUP BY company_id,'. $month_sql .'
						) as tmp
					GROUP BY date_stamp
						';
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

	function getLastDateByCompanyId($company_id, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = :company_id
					ORDER BY date_stamp desc
					LIMIT 1
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

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
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = :company_id
						AND	id = :id
						';
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}


}
?>
