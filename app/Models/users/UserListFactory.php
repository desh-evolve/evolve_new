<?php

namespace App\Models\Users;

use App\Models\Company\BranchFactory;
use App\Models\Company\CompanyFactory;
use App\Models\Core\CurrencyFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\PermissionControlFactory;
use App\Models\Core\PermissionUserFactory;
use App\Models\Core\StationBranchFactory;
use App\Models\Core\StationDepartmentFactory;
use App\Models\Core\StationExcludeUserFactory;
use App\Models\Core\StationFactory;
use App\Models\Core\StationIncludeUserFactory;
use App\Models\Core\StationUserGroupFactory;
use App\Models\Department\DepartmentFactory;
use App\Models\PayPeriod\PayPeriodScheduleFactory;
use App\Models\PayPeriod\PayPeriodScheduleUserFactory;
use App\Models\Policy\PolicyGroupFactory;
use App\Models\Policy\PolicyGroupUserFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class UserListFactory extends UserFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'company_id' => 'asc', 'status_id' => '= 10 desc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}

		return $this;
	}

	function getByStatus($status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$ph = array(
					':status_id' => $status,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						status_id = :status_id
						AND deleted = 0';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyIdAndStatus($company_id, $status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$ph = array(
					':company_id' => $company_id,
					':status_id' => $status,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						company_id = :company_id
						AND status_id = :status_id
						AND deleted = 0';

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	static function getFullNameById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$ulf = new UserListFactory();
		$ulf = $ulf->getById( $id );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent();
			return $u_obj->getFullName();
		}

		return FALSE;
	}

	function getById($id) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( empty($this->rs)) {
			$ph = array(
						':id' => $id,
						);

			$query = '
						select 	*
						from 	'. $this->getTable() .'
						where	id = :id
							AND deleted = 0';

			$this->rs = DB::select($query, $ph);
			$this->saveCache($this->rs, $id);
		}
		return $this;
	}


        /*
         * ARSP EDIT -->
         * THIS CODE ADDED BY ME
         * THIS CODE USE FOR IF IMPORT USER DEDUCTION /EARNINGS  CSV FORMAT THAT TIME CHECK THE EXISTING EMPLOYEE NUMBER
         */
	function getByEmployeeNumber($emp_no) {
		if ( $emp_no == '') {
			return FALSE;
		}

		$ph = array(
					':employee_number' => $emp_no,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	employee_number = :employee_number
						AND deleted = 0';

		$this->rs = DB::select($query, $ph);

		return $this;
	}


        /*
         * ARSP NOTE  --> I ADDED THIS CODE FOR THUNDER & NEON
         *
         */
	function getAllJobSkillsUniqueOptions() {

		//		$query = '
		//					select 	job_skills
		//					from	'. $this->getTable() .'
		//					where   job_skills != null
		//						AND deleted = 0';
		//
		//		$this->rs = DB::select($query);
		//                echo "ARSP --------------------------------------<br/>";
		//                print_r($this);
		//                echo "<pre>";
		//                print_r($this);
		//                echo "<pre>";

				$ulf = new UserListFactory();
				$ulf->getAll();

				foreach ($ulf->rs as $user) {
					$ulf->data = (array)$user;
					$user = $ulf;

					if($user->getJobSkills() != '' OR $user->getJobSkills() != NULL) {

						// split the phrase by any number of commas or space characters,
						// which include " ", \r, \t, \n and \f
						$keywords = preg_split("/[\s,]+/", $user->getJobSkills());

						$array[] = $keywords;
					}
				}

						//$final = array();
						foreach ($array as $value)
						{
							foreach ($value as $val )
							{
								$final[] = $val;
							}
						}
		//                print_r($final);
		//                exit();

						$result = array_unique($final);
		//                print_r($result);
		//                exit();




		//                print_r($result);
		//                exit();
				return $result;
			}


			function getByIdAndCompanyId($id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
				if ( $id == '') {
					return FALSE;
				}

				if ( $company_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id
								AND	id in ('. $this->getListSQL($id, $ph) .')
								AND deleted = 0';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order, $strict );

				if ($limit == NULL) {
					$this->rs = DB::select($query, $ph);
				} else {
					$this->rs = DB::select($query, $ph);
					//$this->rs = DB::select($query, $ph);
				}

				//$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByUserName($user_name, $where = NULL, $order = NULL) {
				if ( $user_name == '') {
					return FALSE;
				}

				$ph = array(
							':user_name' => $user_name,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where	user_name = :user_name
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}


			function getByHomeEmailOrWorkEmail( $email ) {
				$email = trim(strtolower($email));

				if ( $email == '') {
					return FALSE;
				}

				if ( $this->Validator->isEmail('email', $email ) == FALSE ) {
					return FALSE;
				}

				$ph = array(
							':home_email' => $email,
							':work_email' => $email,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where
								( lower(home_email) = :home_email
									OR lower(work_email) = :work_email )
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByPasswordResetKey( $key ) {
				$key = trim($key);

				if ( $this->Validator->isRegEx('email', $key, NULL, '/^[a-z0-9]{32}$/i' ) == FALSE ) {
					return FALSE;
				}

				$ph = array(
							':key' => $key,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where
								password_reset_key = :key
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByUserNameAndCompanyId($user_name, $company_id, $where = NULL, $order = NULL) {
				if ( $user_name == '') {
					return FALSE;
				}

				if ( $company_id == '') {
					return FALSE;
				}

				$ph = array(
							':user_name' => $user_name,
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	company_id = :user_name
								AND user_name = :company_id
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByUserNameAndStatus($user_name, $status, $where = NULL, $order = NULL) {
				if ( $user_name == '') {
					return FALSE;
				}

				$key = Option::getByValue($status, $this->getOptions('status') );

				if ($key !== FALSE) {
					$status = $key;
				}

				$ph = array(
					':user_name' => $user_name,
					':status' => $status,
				);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where	user_name = :user_name
								AND status_id = :status
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByPhoneIdAndStatus($phone_id, $status, $where = NULL, $order = NULL) {
				if ( $phone_id == '') {
					return FALSE;
				}

				$key = Option::getByValue($status, $this->getOptions('status') );
				if ($key !== FALSE) {
					$status = $key;
				}

				$ph = array(
							':phone_id' => $phone_id,
							':status' => $status,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where	phone_id = :phone_id
								AND status_id = :status
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

		/*
			function getByIButtonIdAndStatus($id, $status, $where = NULL, $order = NULL) {
				if ( $id == '') {
					return FALSE;
				}

				$key = Option::getByValue($status, $this->getOptions('status') );
				if ($key !== FALSE) {
					$status = $key;
				}

				$ph = array(
							'id' => $id,
							'status' => $status,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where	ibutton_id = ?
								AND status_id = ?
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}
		*/

			function getByCompanyIdandBirthday($company_id,$birth_date=Null) {
				if ( $company_id == '') {
					return FALSE;
				}

				$ph = array(
					':company_id' => $company_id,
					':birth_date'  => $birth_date,
				);

				$query = "
							select 	*
							from	". $this->getTable() ."
							where	company_id = :company_id
														AND DATE_FORMAT(FROM_UNIXTIME(birth_date),'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')
														OR (
					(
						DATE_FORMAT(NOW(),'%Y') % 4 <> 0
						OR (
								DATE_FORMAT(NOW(),'%Y') % 100 = 0
								AND DATE_FORMAT(NOW(),'%Y') % 400 <> 0
							)
					)
					AND DATE_FORMAT(NOW(),'%m-%d') = '03-01'
					AND DATE_FORMAT(FROM_UNIXTIME(birth_date),'%m-%d') = '02-29'
				)
								AND deleted = 0";

				$this->rs = DB::select($query, $ph);

				return $this;
			}




			function getByIdAndStatus($id, $status, $where = NULL, $order = NULL) {
				if ( $id == '') {
					return FALSE;
				}

				$key = Option::getByValue($status, $this->getOptions('status') );
				if ($key !== FALSE) {
					$status = $key;
				}

				$ph = array(
							':id' => $id,
							':status' => $status,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where	id = :id
								AND status_id = :status
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCurrencyID($id, $where = NULL, $order = NULL) {
				if ( $id == '') {
					return FALSE;
				}

				$ph = array(
							':id' => $id,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	currency_id = :id
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCompanyIDAndGroupID($company_id, $id, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $id == '') {
					return FALSE;
				}

				$ph = array(
							':company_id' => $company_id,
							':id' => $id,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	company_id = :company_id
								AND group_id = :id
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCompanyIDAndIButtonId($company_id, $id, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $id == '') {
					return FALSE;
				}

				$ph = array(
							':company_id' => $company_id,
							':id' => $id,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	company_id = :company_id
								AND ibutton_id = :id
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCompanyIDAndRFId($company_id, $id, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $id == '') {
					return FALSE;
				}

				$ph = array(
							':company_id' => $company_id,
							':id' => $id,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	company_id = :company_id
								AND rf_id = :id
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCompanyIDAndEmployeeNumber($company_id, $employee_number, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $employee_number == '') {
					return FALSE;
				}

				$ph = array(
							':company_id' => $company_id,
							':employee_number' => $employee_number,
							);

				$query = '
							select 	*
							from	'. $this->getTable() .'
							where 	company_id = :company_id
								AND employee_number = :employee_number
								AND deleted = 0';

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getByCompanyIDAndStationIDAndStatusAndDate($company_id, $station_id, $status_id, $date = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $station_id == '') {
					return FALSE;
				}

				if ( $status_id == '') {
					return FALSE;
				}

				if ( $date == '') {
					$date = 0;
				}

				if ( $order == NULL ) {
					$order = array( 'a.id' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$sf = new StationFactory();
				$sugf = new StationUserGroupFactory();
				$sbf = new StationBranchFactory();
				$sdf = new StationDepartmentFactory();
				$siuf = new StationIncludeUserFactory();
				$seuf = new StationExcludeUserFactory();

				$ph = array(
							':company_id' => $company_id,
							':station_id' => $station_id,
							':status_id' => $status_id,
							//'date' => $date,
							//'date2' => $date,
							);

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a,
									'. $sf->getTable() .' as z
							where	a.company_id = :company_id
								AND z.id = :station_id
								AND a.status_id = :status_id
								AND
									(
										(
											(
												z.user_group_selection_type_id = 10
													OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											)
											AND
											(
												z.branch_selection_type_id = 10
													OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											)
											AND
											(
												z.department_selection_type_id = 10
													OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											)
											AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
										)
										OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
									)';

				if ( isset($date) AND $date > 0 ) {
					//Append the same date twice for created and updated.
					$ph[':created_date'] = $date;
					$ph[':updated_date'] = $date;
					$query  .=	' AND ( a.created_date >= :created_date OR a.updated_date >= :updated_date )';
					unset($date_filter);
				}

				$query .= ' AND ( a.deleted = 0 AND z.deleted = 0 )';

				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order, $strict );

				if ($limit == NULL) {
					$this->rs = DB::select($query, $ph);
				} else {
					$this->rs = DB::select($query, $ph);
					//$this->rs = DB::select($query, $ph);
				}

				return $this;
			}


			function getByCompanyIDAndStationIDAndStatusAndDateAndValidUserIDs($company_id, $station_id, $status_id, $date = NULL, $valid_user_ids = array(), $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $station_id == '') {
					return FALSE;
				}

				if ( $status_id == '') {
					return FALSE;
				}

				if ( $date == '') {
					$date = 0;
				}

				if ( $order == NULL ) {
					$order = array( 'a.id' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$sf = new StationFactory();
				$sugf = new StationUserGroupFactory();
				$sbf = new StationBranchFactory();
				$sdf = new StationDepartmentFactory();
				$siuf = new StationIncludeUserFactory();
				$seuf = new StationExcludeUserFactory();
				$uif = new UserIdentificationFactory();

				$ph = array(
							':company_id' => $company_id,
							':station_id' => $station_id,
							':status_id' => $status_id,
							//'date' => $date,
							//'date2' => $date,
							);

				//Also include users with user_identifcation rows that have been *created* after the given date
				//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a

							LEFT JOIN '. $sf->getTable() .' as z ON (1=1)
							LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
							where	a.company_id = :company_id
								AND z.id = :station_id
								AND a.status_id = :status_id
								AND
									(
										(
											(
												(
													(
														z.user_group_selection_type_id = 10
															OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
															OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													)
													AND
													(
														z.branch_selection_type_id = 10
															OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
															OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													)
													AND
													(
														z.department_selection_type_id = 10
															OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
															OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													)
													AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
												)
												OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
											)

									';

				if ( isset($date) AND $date > 0 ) {
					//Append the same date twice for created and updated.
					$ph[':created_date'] = (int)$date;
					$ph[':updated_date'] = (int)$date;
					$ph[':created_date'] = (int)$date;
					$query  .=	' 		AND ( a.created_date >= :created_date OR a.updated_date >= :updated_date OR uif.created_date >= :created_date )
										)';
				} else {
						$query  .=	'   )';
				}

				if ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) {
					$query  .=	' OR a.id in ('. $this->getListSQL($valid_user_ids, $ph) .') ';
				}

				$query .= '			)
								AND ( a.deleted = 0 AND z.deleted = 0 )';

				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order, $strict );

				if ($limit == NULL) {
					$this->rs = DB::select($query, $ph);
				} else {
					$this->rs = DB::select($query, $ph);
					//$this->rs = DB::select($query, $ph);
				}

				return $this;
			}




			function getByMachineId($id) {
				if ( $id == '') {
					return FALSE;
				}



					$ph = array(
								':punch_machine_user_id' => $id,
								);

					$query = '
								select 	*
								from 	'. $this->getTable() .'
								where	punch_machine_user_id = :punch_machine_user_id
									AND deleted = 0';

					$this->rs = DB::select($query, $ph);




				return $this;
			}





			function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id
								AND deleted = 0';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order, $strict );

				if ($limit == NULL) {
					$this->rs = DB::select($query, $ph);
				} else {
					$this->rs = DB::select($query, $ph);
					//$this->rs = DB::select($query, $ph);
				}

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
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id ';

				//isset() returns false on NULL.
				$query .= $this->getWhereClauseSQL( 'longitude', $longitude, 'numeric', $ph );
				$query .= $this->getWhereClauseSQL( 'latitude', $latitude, 'numeric', $ph );
				$query .= '	AND deleted = 0';

				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order, $strict );

				if ($limit == NULL) {
					$this->rs = DB::select($query, $ph);
				} else {
					$this->rs = DB::select($query, $ph);
					//$this->rs = DB::select($query, $ph);
				}

				return $this;
			}

			static function getByCompanyIdArray($company_id, $include_blank = TRUE, $include_disabled = TRUE, $last_name_first = TRUE ) {

				$ulf = new UserListFactory();
				$ulf->getByCompanyId($company_id);
				if ( $include_blank == TRUE ) {
					$user_list[0] = '--';
				}

				foreach ($ulf->rs as $user) {
					$ulf->data = (array)$user;
					$user = $ulf;

					if ( $user->getStatus() > 10 ) { //INACTIVE
						$status = '('.Option::getByKey( $user->getStatus(), $user->getOptions('status') ).') ';
					} else {
						$status = NULL;
					}

					if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $user->getStatus() == 10 ) ) {
						$user_list[$user->getID()] = $status.$user->getEmployeeNumber().' '.$user->getFullName($last_name_first);
					}
				}

				if ( isset($user_list) ) {
					return $user_list;
				}

				return FALSE;
			}


			function getByCompanyIdArrayWithEPFNo($company_id, $include_blank = TRUE, $include_disabled = TRUE, $last_name_first = TRUE ) {

				$ulf = new UserListFactory();

				$order = array( 'id' => 'asc' );
				$ulf->getByCompanyId($company_id, NULL, NULL, NULL, $order);

				if ( $include_blank == TRUE ) {
					$user_list[0] = '--';
				}

				foreach ($ulf->rs as $user) {
					$ulf->data = (array)$user;
					$user = $ulf;
					if ( $user->getStatus() > 10 ) { //INACTIVE
						$status = '('.Option::getByKey( $user->getStatus(), $user->getOptions('status') ).') ';
					} else {
						$status = NULL;
					}

					if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $user->getStatus() == 10 ) ) {
						$user_list[$user->getID()] = $status.$user->getEpfMembershipNo().' - '.$user->getFullName($last_name_first);

						//$epfnos[] = $user->getEpfMembershipNo();

					}
				}
				//echo '<br><br><br><br><br><br><br><br><br><br><br><br><br>';

				//array_multisort($user_list_new);

				//echo "<pre>"; print_r($user_list);

				if ( isset($user_list) ) {
					return $user_list;
				}

				return FALSE;
			}



			static function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
				if ( !is_object($lf) ) {
					return FALSE;
				}

				if ( $include_blank == TRUE ) {
					$list[0] = '--';

				}
				foreach ($lf->rs as $obj) {
					$lf->data = (array)$obj;
					$obj = $lf;
					if ( !isset($status_options) ) {
						$status_options = $obj->getOptions('status');
					}

					if ( $obj->getStatus() > 10 ) { //INACTIVE
						$status = '('.Option::getByKey( $obj->getStatus(), $status_options ).') ';
						//$status = '(INACTIVE) ';
					} else {
						$status = NULL;
					}

					if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $obj->getStatus() == 10 ) ) {
						$list[$obj->getID()] = $status.$obj->getEpfMembershipNo().' - '.$obj->getFullName(TRUE);
					}
				}

				if ( isset($list) ) {
					return $list;
				}

				return FALSE;
			}

			function getDeletedByCompanyIdAndDate($company_id, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
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
							':deleted_date' => $date,
							);

				//INCLUDE Deleted rows in this query.
				$query = '
							select 	*
							from	'. $this->getTable() .'
							where
									company_id = :company_id
								AND
									( created_date >= :created_date OR updated_date >= :updated_date OR deleted_date >= :deleted_date )
								AND deleted = 1
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
							':uif_created_date' => $date,
							);

				$uif = new UserIdentificationFactory();

				//INCLUDE Deleted rows in this query.
				//Also include users with user_identifcation rows that have been *created* after the given date
				//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
				$query = '
							select 	a.*
							from	'. $this->getTable() .' as a
							LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
							where
									a.company_id = :company_id
								AND
									( a.created_date >= :created_date OR a.updated_date >= :updated_date OR uif.created_date >= :uif_created_date )
							';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order );

				$this->rs = $this->db->SelectLimit($query, 1, -1, $ph);
				if ( $this->getRecordCount() > 0 ) {
					Debug::text('User rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

					return TRUE;
				}

				Debug::text('User rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);

				return FALSE;
			}

			function getHighestEmployeeNumberByCompanyId($id, $where = NULL, $order = NULL) {
				if ( $id == '' ) {
					return FALSE;
				}

				$ph = array(
							':id' => $id,
							':id2' => $id,
							);

				//employee_number is a varchar field, so we can't reliably cast it to an integer
				//however if we left pad it, we can get a similar effect.
				//Eventually we can change it to an integer field.
				$query = '
							select 	*
							from	'. $this->getTable() .' as a
							where	company_id = :id
								AND id = ( select id
											from '. $this->getTable() .'
											where company_id = :id2
												AND employee_number != \'\'
												AND employee_number IS NOT NULL
												AND deleted = 0
											ORDER BY LPAD( employee_number, 10, \'0\' ) DESC
											LIMIT 1
											)
								AND deleted = 0
							LIMIT 1
								';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

				/**
				 * ARSP NOTE-->
				 * I ADDED THIS CODE FOR THUNDER AND NEON
				 */
				function getHighestEmployeeNumberOnlyByCompanyId($id, $where = NULL, $order = NULL) {
				if ( $id == '' ) {
					return FALSE;
				}

				$ph = array(
							':id' => $id,
							':id2' => $id,
							);

				//employee_number is a varchar field, so we can't reliably cast it to an integer
				//however if we left pad it, we can get a similar effect.
				//Eventually we can change it to an integer field.
				$query = '
							select 	*
							from	'. $this->getTable() .' as a
							where	company_id = :id
								AND id = ( select id
											from '. $this->getTable() .'
											where company_id = :id2
												AND employee_number_only != \'\'
												AND employee_number_only IS NOT NULL
												AND deleted = 0
											ORDER BY LPAD( employee_number_only, 10, \'0\' ) DESC
											LIMIT 1
											)
								AND deleted = 0
							LIMIT 1
								';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

				/**
				 * ARSP NOTE-->
				 * I ADDED THIS CODE FOR THUNDER AND NEON
				 */
				function getHighestEmployeeNumberOnlyByBranchId($id, $where = NULL, $order = NULL) {
				if ( $id == 0 ) {
					return FALSE;
				}

				$ph = array(
							':id' => $id,
							':id2' => $id,
							);

				//employee_number is a varchar field, so we can't reliably cast it to an integer
				//however if we left pad it, we can get a similar effect.
				//Eventually we can change it to an integer field.
				$query = '
							select 	*
							from	'. $this->getTable() .' as a
							where	default_branch_id = :id
								AND id = ( select id
											from '. $this->getTable() .'
											where default_branch_id = :id2
												AND employee_number_only != \'\'
												AND employee_number_only IS NOT NULL
												AND deleted = 0
											ORDER BY LPAD( employee_number_only, 10, \'0\' ) DESC
											LIMIT 1
											)
								AND deleted = 0
							LIMIT 1
								';
				$query .= $this->getWhereSQL( $where );
				$query .= $this->getSortSQL( $order );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

				function getSearchByArrayCriteria( $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
				if ( !is_array($order) ) {
					//Use Filter Data ordering if its set.
					if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
						$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
					}
				}

				$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
				if ( $order == NULL ) {
					$order = array( 'company_id' => 'asc', 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
					$strict = FALSE;
				} else {
					//Do order by column conversions, because if we include these columns in the SQL
					//query, they contaminate the data array.
					if ( isset($order['default_branch']) ) {
						$order['b.name'] = $order['default_branch'];
						unset($order['default_branch']);
					}
					if ( isset($order['default_department']) ) {
						$order['c.name'] = $order['default_department'];
						unset($order['default_department']);
					}
					if ( isset($order['user_group']) ) {
						$order['d.name'] = $order['user_group'];
						unset($order['user_group']);
					}
					if ( isset($order['title']) ) {
						$order['e.name'] = $order['title'];
						unset($order['title']);
					}

					//Always try to order by status first so INACTIVE employees go to the bottom.
					if ( !isset($order['status_id']) ) {
						$order = Misc::prependArray( array('status_id' => 'asc'), $order );
					}
					//Always sort by last name,first name after other columns
					if ( !isset($order['last_name']) ) {
						$order['last_name'] = 'asc';
					}
					if ( !isset($order['first_name']) ) {
						$order['first_name'] = 'asc';
					}
					$strict = TRUE;
				}
				//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
				//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

				if ( isset($filter_data['company_ids']) ) {
					$filter_data['company_id'] = $filter_data['company_ids'];
				}

				if ( isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
				}
				if ( isset($filter_data['include_user_ids']) ) {
					$filter_data['id'] = $filter_data['include_user_ids'];
				}
				if ( isset($filter_data['user_status_ids']) ) {
					$filter_data['status_id'] = $filter_data['user_status_ids'];
				}
				if ( isset($filter_data['user_title_ids']) ) {
					$filter_data['title_id'] = $filter_data['user_title_ids'];
				}
				if ( isset($filter_data['group_ids']) ) {
					$filter_data['group_id'] = $filter_data['group_ids'];
				}
				if ( isset($filter_data['branch_ids']) ) {
					$filter_data['default_branch_id'] = $filter_data['branch_ids'];
				}
				if ( isset($filter_data['department_ids']) ) {
					$filter_data['default_department_id'] = $filter_data['department_ids'];
				}
				if ( isset($filter_data['currency_ids']) ) {
					$filter_data['currency_id'] = $filter_data['currency_ids'];
				}

				$bf = new BranchFactory();
				$df = new DepartmentFactory();
				$ugf = new UserGroupFactory();
				$utf = new UserTitleFactory();

				$ph = array();

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a
								LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
								LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
								LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
								LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
							where	1=1
							';

				if ( isset($filter_data['company_id']) AND isset($filter_data['company_id'][0]) AND !in_array(-1, (array)$filter_data['company_id']) ) {
					$query  .=	' AND a.company_id in ('. $this->getListSQL($filter_data['company_id'], $ph) .') ';
				}
				if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
				}
				if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
				}
				if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
					$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
				}
				if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
					$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
				}
				if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
					if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
						$uglf = new UserGroupListFactory();
						$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $filter_data['company_id'], $filter_data['group_id'], TRUE);
					}
					$query  .=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
					$query  .=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
					$query  .=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
				}
				if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
					$query  .=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
				}
				if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
					$query  .=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
				}
				if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
					$query  .=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
				}
				if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
					$query  .=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
				}
				if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
					$query  .=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
				}
				if ( isset($filter_data['city']) AND trim($filter_data['city']) != '' ) {
					$ph[':city'] = strtolower(trim($filter_data['city']));
					$query  .=	' AND lower(a.city) LIKE :city';
				}
				if ( isset($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
					$ph[':first_name'] = strtolower(trim($filter_data['first_name']));
					$query  .=	' AND lower(a.first_name) LIKE :first_name';
				}
				if ( isset($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
					$ph[':last_name'] = strtolower(trim($filter_data['last_name']));
					$query  .=	' AND lower(a.last_name) LIKE :last_name';
				}
				if ( isset($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
					$ph[':home_phone'] = trim($filter_data['home_phone']);
					$query  .=	' AND a.home_phone LIKE :home_phone';
				}
				if ( isset($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
					$ph[':employee_number'] = trim($filter_data['employee_number']);
					$query  .=	' AND a.employee_number LIKE :employee_number';
				}
				if ( isset($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
					$ph[':user_name'] = strtolower(trim($filter_data['user_name']));
					$query  .=	' AND lower(a.user_name) LIKE :user_name';
				}
				if ( isset($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
					$ph[':sin'] = trim($filter_data['sin']);
					$query  .=	' AND a.sin LIKE :sin';
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

			function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {

				//  echo '<pre>'; print_r($filter_data); echo '<pre>';  die;
				if ( $company_id == '') {
					return FALSE;
				}

				if ( !is_array($order) ) {
					//Use Filter Data ordering if its set.
					if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
						$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
					}
				}

				$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
				if ( $order == NULL ) {
					$order = array( 'punch_machine_user_id'=>'asc', 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
					$strict = FALSE;
				} else {
					//Do order by column conversions, because if we include these columns in the SQL
					//query, they contaminate the data array.
					if ( isset($order['default_branch']) ) {
						$order['b.name'] = $order['default_branch'];
						unset($order['default_branch']);
					}
					if ( isset($order['default_department']) ) {
						$order['c.name'] = $order['default_department'];
						unset($order['default_department']);
					}
					if ( isset($order['user_group']) ) {
						$order['d.name'] = $order['user_group'];
						unset($order['user_group']);
					}
					if ( isset($order['title']) ) {
						$order['e.name'] = $order['title'];
						unset($order['title']);
					}

					if ( !isset($order['punch_machine_user_id']) ) {
						$order = Misc::prependArray( array('punch_machine_user_id' => 'asc'), $order );
					}
					//Always try to order by status first so INACTIVE employees go to the bottom.
					if ( isset($order['status_id']) ) {
						$order = Misc::prependArray( array('status_id' => 'asc'), $order );
					}
					//Always sort by last name,first name after other columns
					if ( isset($order['last_name']) ) {
						$order['last_name'] = 'asc';
					}
					if ( isset($order['first_name']) ) {
						$order['first_name'] = 'asc';
					}
					$strict = TRUE;
				}
				//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
				//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

				if ( isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
				}
				if ( isset($filter_data['include_user_ids']) ) {
					$filter_data['id'] = $filter_data['include_user_ids'];
				}
				if ( isset($filter_data['user_status_ids']) ) {
					$filter_data['status_id'] = $filter_data['user_status_ids'];
				}
				if ( isset($filter_data['user_title_ids']) ) {
					$filter_data['title_id'] = $filter_data['user_title_ids'];
				}
				if ( isset($filter_data['group_ids']) ) {
					$filter_data['group_id'] = $filter_data['group_ids'];
				}
				if ( isset($filter_data['branch_ids']) ) {
					$filter_data['default_branch_id'] = $filter_data['branch_ids'];
				}
				if ( isset($filter_data['department_ids']) ) {
					$filter_data['default_department_id'] = $filter_data['department_ids'];
				}
				if ( isset($filter_data['currency_ids']) ) {
					$filter_data['currency_id'] = $filter_data['currency_ids'];
				}

				$bf = new BranchFactory();
				$df = new DepartmentFactory();
				$ugf = new UserGroupFactory();
				$utf = new UserTitleFactory();

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a
								LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
								LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
								LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
								LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
							where	a.company_id = :company_id ';

				//     $query  .=	' AND a.basis_of_employment in ('. $this->getListSQL($filter_data['basis_of_employment'][0], $ph) .') ';
				if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
				}
				if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
				}
				if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
					$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
				}
				if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
					$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
				}
				if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
					if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
						$uglf = new UserGroupListFactory();
						$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
					}
					$query  .=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
					$query  .=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
					$query  .=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
				}
				if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
					$query  .=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
				}
				if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
					$query  .=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
				}
				if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
					$query  .=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
				}
				if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
					$query  .=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
				}
				if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
					$query  .=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
				}
				if ( isset($filter_data['city']) AND trim($filter_data['city']) != '' ) {
					$ph[':city'] = strtolower(trim($filter_data['city']));
					$query  .=	' AND lower(a.city) LIKE :city';
				}
				if ( isset($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
					$ph[':first_name'] = strtolower(trim($filter_data['first_name']));
					$query  .=	' AND lower(a.first_name) LIKE :first_name';
				}
				if ( isset($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
					$ph[':last_name'] = strtolower(trim($filter_data['last_name']));
					$query  .=	' AND lower(a.last_name) LIKE :last_name';
				}
				if ( isset($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
					$ph[':home_phone'] = trim($filter_data['home_phone']);
					$query  .=	' AND a.home_phone LIKE :home_phone';
				}
				if ( isset($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
					$ph[':employee_number'] = trim($filter_data['employee_number']);
					$query  .=	' AND a.employee_number LIKE :employee_number';
				}
						//////////////eranda
						if ( isset($filter_data['basis_of_employment']) ) {
					//$ph[] = trim($filter_data['basis_of_employment']);
					$query  .=	' AND a.basis_of_employment = '.$filter_data['basis_of_employment'].'';
				}
						//////////
				if ( isset($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
					$ph[':user_name'] = strtolower(trim($filter_data['user_name']));
					$query  .=	' AND lower(a.user_name) LIKE :user_name';
				}
				if ( isset($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
					$ph[':sin'] = trim($filter_data['sin']);
					$query  .=	' AND a.sin LIKE :sin';
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


				function getSearchByCompanyIdAndArrayCriteriaForOT( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( !is_array($order) ) {
					//Use Filter Data ordering if its set.
					if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
						$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
					}
				}

				$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
					$strict = FALSE;
				} else {
					//Do order by column conversions, because if we include these columns in the SQL
					//query, they contaminate the data array.
					if ( isset($order['default_branch']) ) {
						$order['b.name'] = $order['default_branch'];
						unset($order['default_branch']);
					}
					if ( isset($order['default_department']) ) {
						$order['c.name'] = $order['default_department'];
						unset($order['default_department']);
					}
					if ( isset($order['user_group']) ) {
						$order['d.name'] = $order['user_group'];
						unset($order['user_group']);
					}
					if ( isset($order['title']) ) {
						$order['e.name'] = $order['title'];
						unset($order['title']);
					}

					//Always try to order by status first so INACTIVE employees go to the bottom.
					if ( !isset($order['status_id']) ) {
						$order = Misc::prependArray( array('status_id' => 'asc'), $order );
					}
					//Always sort by last name,first name after other columns
					if ( !isset($order['last_name']) ) {
						$order['last_name'] = 'asc';
					}
					if ( !isset($order['first_name']) ) {
						$order['first_name'] = 'asc';
					}
					$strict = TRUE;
				}
				//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
				//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

				if ( isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
				}
				if ( isset($filter_data['include_user_ids']) ) {
					$filter_data['id'] = $filter_data['include_user_ids'];
				}
				if ( isset($filter_data['user_status_ids']) ) {
					$filter_data['status_id'] = $filter_data['user_status_ids'];
				}
				if ( isset($filter_data['user_title_ids']) ) {
					$filter_data['title_id'] = $filter_data['user_title_ids'];
				}
				if ( isset($filter_data['group_ids']) ) {
					$filter_data['group_id'] = $filter_data['group_ids'];
				}
				if ( isset($filter_data['branch_ids']) ) {
					$filter_data['default_branch_id'] = $filter_data['branch_ids'];
				}
				if ( isset($filter_data['department_ids']) ) {
					$filter_data['default_department_id'] = $filter_data['department_ids'];
				}
				if ( isset($filter_data['currency_ids']) ) {
					$filter_data['currency_id'] = $filter_data['currency_ids'];
				}

				$bf = new BranchFactory();
				$df = new DepartmentFactory();
				$ugf = new UserGroupFactory();
				$utf = new UserTitleFactory();

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a
								LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
								LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
								LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
								LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
							where	a.company_id = :company_id
							';

				if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
				}
				if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
				}
				if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
					$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
				}
				if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
					$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
				}
				if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
					if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
						$uglf = new UserGroupListFactory();
						$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
					}
					$query  .=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
					$query  .=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
					$query  .=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
				}
				if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
					$query  .=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
				}
				if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
					$query  .=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
				}
				if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
					$query  .=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
				}
				if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
					$query  .=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
				}
				if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
					$query  .=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
				}
				if ( isset($filter_data['city']) AND trim($filter_data['city']) != '' ) {
					$ph[':city'] = strtolower(trim($filter_data['city']));
					$query  .=	' AND lower(a.city) LIKE :city';
				}
				if ( isset($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
					$ph[':first_name'] = strtolower(trim($filter_data['first_name']));
					$query  .=	' AND lower(a.first_name) LIKE :first_name';
				}
				if ( isset($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
					$ph[':last_name'] = strtolower(trim($filter_data['last_name']));
					$query  .=	' AND lower(a.last_name) LIKE :last_name';
				}
				if ( isset($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
					$ph[':home_phone'] = trim($filter_data['home_phone']);
					$query  .=	' AND a.home_phone LIKE :home_phone';
				}
				if ( isset($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
					$ph[':employee_number'] = trim($filter_data['employee_number']);
					$query  .=	' AND a.employee_number LIKE :employee_number';
				}
				if ( isset($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
					$ph[':user_name'] = strtolower(trim($filter_data['user_name']));
					$query  .=	' AND lower(a.user_name) LIKE :user_name';
				}
				if ( isset($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
					$ph[':sin'] = trim($filter_data['sin']);
					$query  .=	' AND a.sin LIKE :sin';
				}

				$query .= 	'   AND a.group_id in (4,6)
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


				function getSearchByCompanyIdAndArrayCriteriaForOP( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( !is_array($order) ) {
					//Use Filter Data ordering if its set.
					if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
						$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
					}
				}

				$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
					$strict = FALSE;
				} else {
					//Do order by column conversions, because if we include these columns in the SQL
					//query, they contaminate the data array.
					if ( isset($order['default_branch']) ) {
						$order['b.name'] = $order['default_branch'];
						unset($order['default_branch']);
					}
					if ( isset($order['default_department']) ) {
						$order['c.name'] = $order['default_department'];
						unset($order['default_department']);
					}
					if ( isset($order['user_group']) ) {
						$order['d.name'] = $order['user_group'];
						unset($order['user_group']);
					}
					if ( isset($order['title']) ) {
						$order['e.name'] = $order['title'];
						unset($order['title']);
					}

					//Always try to order by status first so INACTIVE employees go to the bottom.
					if ( !isset($order['status_id']) ) {
						$order = Misc::prependArray( array('status_id' => 'asc'), $order );
					}
					//Always sort by last name,first name after other columns
					if ( !isset($order['last_name']) ) {
						$order['last_name'] = 'asc';
					}
					if ( !isset($order['first_name']) ) {
						$order['first_name'] = 'asc';
					}
					$strict = TRUE;
				}
				//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
				//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

				if ( isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
				}
				if ( isset($filter_data['include_user_ids']) ) {
					$filter_data['id'] = $filter_data['include_user_ids'];
				}
				if ( isset($filter_data['user_status_ids']) ) {
					$filter_data['status_id'] = $filter_data['user_status_ids'];
				}
				if ( isset($filter_data['user_title_ids']) ) {
					$filter_data['title_id'] = $filter_data['user_title_ids'];
				}
				if ( isset($filter_data['group_ids']) ) {
					$filter_data['group_id'] = $filter_data['group_ids'];
				}
				if ( isset($filter_data['branch_ids']) ) {
					$filter_data['default_branch_id'] = $filter_data['branch_ids'];
				}
				if ( isset($filter_data['department_ids']) ) {
					$filter_data['default_department_id'] = $filter_data['department_ids'];
				}
				if ( isset($filter_data['currency_ids']) ) {
					$filter_data['currency_id'] = $filter_data['currency_ids'];
				}

				$bf = new BranchFactory();
				$df = new DepartmentFactory();
				$ugf = new UserGroupFactory();
				$utf = new UserTitleFactory();

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a
								LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
								LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
								LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
								LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
							where	a.company_id = :company_id
							';

				if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
				}
				if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
					$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
				}
				if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
					$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
				}
				if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
					$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
				}
				if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
					if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
						$uglf = new UserGroupListFactory();
						$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
					}
					$query  .=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
					$query  .=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
				}
				if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
					$query  .=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
				}
				if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
					$query  .=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
				}
				if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
					$query  .=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
				}
				if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
					$query  .=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
				}
				if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
					$query  .=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
				}
				if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
					$query  .=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
				}
				if ( isset($filter_data['city']) AND trim($filter_data['city']) != '' ) {
					$ph[':city'] = strtolower(trim($filter_data['city']));
					$query  .=	' AND lower(a.city) LIKE :city';
				}
				if ( isset($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
					$ph[':first_name'] = strtolower(trim($filter_data['first_name']));
					$query  .=	' AND lower(a.first_name) LIKE :first_name';
				}
				if ( isset($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
					$ph[':last_name'] = strtolower(trim($filter_data['last_name']));
					$query  .=	' AND lower(a.last_name) LIKE :last_name';
				}
				if ( isset($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
					$ph[':home_phone'] = trim($filter_data['home_phone']);
					$query  .=	' AND a.home_phone LIKE :home_phone';
				}
				if ( isset($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
					$ph[':employee_number'] = trim($filter_data['employee_number']);
					$query  .=	' AND a.employee_number LIKE :employee_number';
				}
				if ( isset($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
					$ph[':user_name'] = strtolower(trim($filter_data['user_name']));
					$query  .=	' AND lower(a.user_name) LIKE :user_name';
				}
				if ( isset($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
					$ph[':sin'] = trim($filter_data['sin']);
					$query  .=	' AND a.sin LIKE :sin';
				}

				$query .= 	'  AND a.group_id = 3
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


			function getSearchByCompanyIdAndBranchIdAndDepartmentIdAndStatusId($company_id, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id
							';

				if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
					$query  .=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
				}
				if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
					$query  .=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
				}
				if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
					$query  .=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
				}

				$query .= 	'
								AND deleted = 0
							';
				$query .= $this->getSortSQL( $order, $strict );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getSearchByCompanyIdAndGroupIdAndSubGroupsAndBranchIdAndDepartmentIdAndStatusId($company_id, $group_id, $include_sub_groups, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				if ( $include_sub_groups == TRUE
					AND ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) ) {
					$uglf = new UserGroupListFactory();
					$group_id = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $group_id, TRUE);
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id
							';

				if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
					$query  .=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
				}
				if ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) {
					$query  .=	' AND group_id in ('. $this->getListSQL($group_id, $ph) .') ';
				}
				if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
					$query  .=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
				}
				if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
					$query  .=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
				}

				$query .= 	'
								AND deleted = 0
							';
				$query .= $this->getSortSQL( $order, $strict );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

				/**
				*ARSP NOTE --> THIS CODE ADDED BY ME FOR THUNDER & NEON
				*/
			function getSearchByJobSkills($job_skill) {

						$job_skill = '%'.$job_skill.'%';

				if ( $job_skill == '') {
					return FALSE;
				}

				/*
				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}
				*/

				$ph = array(
							':job_skills' => $job_skill,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	job_skills LIKE  :job_skills
							';

				$query .= 	'
								AND deleted = 0
							';
						//echo $query;
						$order = array('first_name' => 'asc');
				//$query .= $this->getSortSQL( $order, $strict );

				$this->rs = DB::select($query, $ph);
						//var_dump($this);

				return $this;
			}

			function getSearchByCompanyIdAndUserIDAndGroupIdAndSubGroupsAndBranchIdAndDepartmentIdAndStatusId($company_id, $user_id, $group_id, $include_sub_groups, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $user_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				if ( $include_sub_groups == TRUE
					AND ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) ) {
					$uglf = new UserGroupListFactory();
					$group_id = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $group_id, TRUE);
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	*
							from 	'. $this->getTable() .'
							where	company_id = :company_id
								AND	id in ('. $this->getListSQL($user_id, $ph) .')
							';

				if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
					$query  .=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
				}
				if ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) {
					$query  .=	' AND group_id in ('. $this->getListSQL($group_id, $ph) .') ';
				}
				if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
					$query  .=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
				}
				if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
					$query  .=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
				}

				$query .= 	'
								AND deleted = 0
							';
				$query .= $this->getSortSQL( $order, $strict );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getSearchByCompanyIdAndStatusIdAndBranchIdAndDepartmentIdAndUserTitleIdAndIncludeIdAndExcludeId($company_id, $status_id, $branch_id, $department_id, $user_title_id = NULL, $include_user_id = NULL, $exclude_user_id = NULL, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}

				$ph = array(
							':company_id' => $company_id,
							);

				$query = '
							select 	a.*
							from 	'. $this->getTable() .' as a
							where 	a.company_id = :company_id

							';

				$filter_query = NULL;
				if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, $status_id) ) {
					$filter_query  .=	' AND a.status_id in ('. $this->getListSQL($status_id, $ph) .') ';
				}
				if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, $branch_id) ) {
					$filter_query  .=	' AND a.default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
				}
				if ( $department_id != '' AND isset($department_id[0]) AND !in_array(-1, $department_id) ) {
					$filter_query  .=	' AND a.default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
				}
				if ( $user_title_id != '' AND isset($user_title_id[0]) AND !in_array(-1, $user_title_id) ) {
					$filter_query  .=	' AND a.title_id in ('. $this->getListSQL($user_title_id, $ph) .') ';
				}
				if ( $exclude_user_id != '' AND isset($exclude_user_id[0]) ) {
					$filter_query  .=	' AND a.id not in ('. $this->getListSQL($exclude_user_id, $ph) .') ';
				}

				//If Branch,Dept,Status,Exclude are set, we need to prepend
				//the company_id filter.
				if ( isset($filter_query) AND $filter_query != '' ) {
					$query .= $filter_query;
					$include_user_by_or = TRUE;
				} else {
					$include_user_by_or = FALSE;
				}

				if ( $include_user_id != '' AND isset($include_user_id[0]) ) {
					$ph[':company_id'] = $company_id;

					//If other criteria are set, we OR this filter.
					//otherwise we just leave it as is.
					if ( $include_user_by_or == TRUE ) {
						$query .= ' OR ';
					} else {
						$query .= ' AND ';
					}
					$query  .=	' ( a.company_id = :company_id AND a.id in ('. $this->getListSQL($include_user_id, $ph) .') ) ';
				}

				$query .= 	'
								AND a.deleted = 0
							';
				$query .= $this->getSortSQL( $order, $strict );

				$this->rs = DB::select($query, $ph);

				return $this;
			}

			function getReportByCompanyIdAndUserIDList($company_id, $user_ids, $order = NULL) {
				if ( $company_id == '') {
					return FALSE;
				}

				if ( $user_ids == '') {
					return FALSE;
				}
		/*
				if ( $order == NULL ) {
					$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
					$strict = FALSE;
				} else {
					$strict = TRUE;
				}
		*/

		//		$utf = new UserTaxFactory();
		//					LEFT JOIN '. $utf->getTable() .' as b ON a.id = b.user_id AND (b.deleted=0 OR b.deleted IS NULL)
		$baf = new BankAccountFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	c.*,a.*
					from 	'. $this->getTable() .' as a
					LEFT JOIN '. $baf->getTable() .' as c ON a.id = c.user_id AND (c.deleted=0 OR c.deleted IS NULL)
					where
						a.company_id = :company_id
						AND a.id in ('. $this->getListSQL($user_ids, $ph) .')
						AND ( a.deleted = 0 )
				';
		$query .= $this->getSortSQL( $order, FALSE );

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

		if ( isset($filter_data['user_status_id']) ) {
			$filter_data['status_id'] = $filter_data['user_status_id'];
		}

		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['exclude_user_id']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		if ( isset($filter_data['group_id']) ) {
			$filter_data['user_group_id'] = $filter_data['group_id'];
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array(	'default_branch',
											'default_department',
											'sex',
											'user_group',
											'title',
											'currency',
											'permission_control',
											'pay_period_schedule',
											'policy_group',
											);

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 'status' => 'status_id',
									 'sex' => 'sex_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'id' =>'asc', 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.

			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name,first name after other columns
			if ( !isset($order['last_name']) ) {
				$order['last_name'] = 'asc';
			}
			if ( !isset($order['first_name']) ) {
				$order['first_name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$compf = new CompanyFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();
		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							compf.name as company,
							b.name as default_branch,
							c.name as default_department,
							d.name as user_group,
							e.name as title,
							f.name as currency,
							g.id as permission_control_id,
							g.name as permission_control,
							h.id as pay_period_schedule_id,
							h.name as pay_period_schedule,
							i.id as policy_group_id,
							i.name as policy_group,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $compf->getTable() .' as compf ON ( a.company_id = compf.id AND compf.deleted = 0)
						LEFT JOIN '. $bf->getTable() .' as b ON ( a.default_branch_id = b.id AND b.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as c ON ( a.default_department_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as d ON ( a.group_id = d.id AND d.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as e ON ( a.title_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as f ON ( a.currency_id = f.id AND f.deleted = 0 )

						LEFT JOIN
						(
							SELECT g2.*,g1.user_id
							FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
							WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
						) as g ON ( a.id = g.user_id )
						LEFT JOIN
						(
							SELECT h2.*, h1.user_id
							FROM '. $ppsuf->getTable() .' as h1, '. $ppsf->getTable() .' as h2
							WHERE ( h1.pay_period_schedule_id = h2.id AND h2.deleted = 0)
						) as h ON ( a.id = h.user_id )
						LEFT JOIN
						(
							SELECT i2.*, i1.user_id
							FROM '. $pguf->getTable() .' as i1, '. $pgf->getTable() .' as i2
							WHERE ( i1.policy_group_id = i2.id AND i2.deleted = 0)
						) as i ON ( a.id = i.user_id ) ';

		$query .= '
						LEFT JOIN '. $this->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $this->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = :company_id
					';

		//if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
		//	$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		//}

		//if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
		//	$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		//}

		//if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
		//	$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		//}

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'a.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_group']) ) ? $this->getWhereClauseSQL( 'd.name', $filter_data['user_group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'a.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch']) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['default_branch'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'a.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department']) ) ? $this->getWhereClauseSQL( 'c.name', $filter_data['default_department'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'a.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title']) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['title'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['currency_id']) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['currency']) ) ? $this->getWhereClauseSQL( 'f.name', $filter_data['currency'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['permission_control_id']) ) ? $this->getWhereClauseSQL( 'g.permission_control_id', $filter_data['permission_control_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['permission_control']) ) ? $this->getWhereClauseSQL( 'g.name', $filter_data['permission_control'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_schedule_id']) ) ? $this->getWhereClauseSQL( 'i.pay_period_schedule_id', $filter_data['pay_period_schedule_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_schedule']) ) ? $this->getWhereClauseSQL( 'h.name', $filter_data['pay_period_schedule'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['policy_group_id']) ) ? $this->getWhereClauseSQL( 'k.policy_group_id', $filter_data['policy_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['policy_group']) ) ? $this->getWhereClauseSQL( 'i.name', $filter_data['policy_group'], 'text', $ph ) : NULL;

		if ( isset($filter_data['sex']) AND trim($filter_data['sex']) != '' AND !isset($filter_data['sex_id']) ) {
			$filter_data['sex_id'] = Option::getByFuzzyValue( $filter_data['sex'], $this->getOptions('sex') );
		}
		$query .= ( isset($filter_data['sex_id']) ) ?$this->getWhereClauseSQL( 'a.sex_id', $filter_data['sex_id'], 'text_list', $ph ) : NULL;

		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'a.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['home_phone']) ) ? $this->getWhereClauseSQL( 'a.home_phone', $filter_data['home_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['employee_number']) ) ? $this->getWhereClauseSQL( 'a.employee_number', $filter_data['employee_number'], 'numeric', $ph ) : NULL;
		$query .= ( isset($filter_data['user_name']) ) ? $this->getWhereClauseSQL( 'a.user_name', $filter_data['user_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['sin']) ) ? $this->getWhereClauseSQL( 'a.sin', $filter_data['sin'], 'numeric', $ph ) : NULL;

		$query .= ( isset($filter_data['work_email']) ) ? $this->getWhereClauseSQL( 'a.work_email', $filter_data['work_email'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['home_email']) ) ? $this->getWhereClauseSQL( 'a.home_email', $filter_data['home_email'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( '', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		//$query .= ( isset($filter_data['longitude']) ) ? $this->getWhereClauseSQL( 'a.longitude', $filter_data['longitude'], 'numeric', $ph ) : NULL;

		if ( isset($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query  .=	' AND '. $date_filter;
			}
			unset($date_filter);
		}
		if ( isset($filter_data['updated_date']) AND trim($filter_data['updated_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['updated_date'], 'a.updated_date' );
			if ( $date_filter != FALSE ) {
				$query  .=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		if ( isset($filter_data['created_by']) AND trim($filter_data['created_by']) != '' ) {
			$ph[':created_by'] = strtolower(trim($filter_data['created_by']));
			$query  .=	' AND (lower(y.first_name) LIKE :created_by OR lower(y.last_name) LIKE :created_by ) ';
		}
		if ( isset($filter_data['updated_by']) AND trim($filter_data['updated_by']) != '' ) {
			$ph[':updated_by'] = strtolower(trim($filter_data['updated_by']));
			$query  .=	' AND (lower(z.first_name) LIKE :updated_by OR lower(z.last_name) LIKE :updated_by ) ';
		}

		$query .= 	'
						AND ( a.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		if ($limit == NULL) {
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
			//$this->rs = DB::select($query, $ph);
		}

		return $this;
	}


        function getTerminationByPayperiod($payperiod_id,$order = NULL){

            if ( $payperiod_id == '') {
			return FALSE;
		}

                $ph = array(
					':pay_period_id' => $payperiod_id,
					);


                $query = 'select a.id,a.termination_date from users as a
                            inner join user_date as ud on ud.user_id = a.id
                            inner join pay_period as pp on pp.id = ud.pay_period_id
                            where  ud.pay_period_id = :pay_period_id
                            and a.termination_date is not null
                            and a.termination_date between unix_timestamp(pp.start_date) and unix_timestamp(pp.end_date)
                            and a.deleted = 0 and ud.deleted = 0 and pp.deleted=0
                            group by a.id,a.termination_date';


                $query .= $this->getSortSQL( $order, FALSE );

		$this->rs = DB::select($query, $ph);

		return $this;
        }
}
?>
