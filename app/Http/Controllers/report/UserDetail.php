<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\ExceptionPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\Users\UserWageListFactory;
use Illuminate\Support\Facades\View;

class UserDetail extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;
    protected $userPrefs;

    public function __construct()
    {

        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        $this->userPrefs = View::shared('current_user_prefs');

    }

    public function index() {
		/*
		if ( !$permission->Check('report','enabled')
				OR !$permission->Check('report','view_user_detail') ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/

		$viewData['title'] = 'Employee Detail Report';
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;
		$current_user_prefs = $this->userPrefs;
		
		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'generic_data',
				'filter_data'
			) 
		) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],

		array(
			'filter_data' => $filter_data
			//'sort_column' => $sort_column,
			//'sort_order' => $sort_order,
		) );


		$columns = array(
			'employee' => _('Employee Information'),
			'wage' => _('Wage History'),
			//'schedule' => 'Schedule History',
			'attendance' => _('Attendance History'),
			'exception' => _('Exception History'),
			//'accrual' => 'Accrual Balances',
		);

		$static_columns = array(
			'-1000-full_name' => _('Full Name'),
			'-1010-title' => _('Title'),
			'-1020-province' => _('Province/State'),
			'-1030-country' => _('Country'),
			'-1040-default_branch' => _('Default Branch'),
			'-1050-default_department' => _('Default Department'),
			//'-1060-verified_time_sheet' => _('Verified TimeSheet'),
		);

		//$columns = Misc::prependArray( $columns, $deduction_columns);

		if ( isset($filter_data['start_date']) ) {
			$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
		}

		if ( isset($filter_data['end_date']) ) {
			$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
		}

		if ( !isset($filter_data['include_user_ids']) ) {
			$filter_data['include_user_ids'] = array();
		}
		if ( !isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_user_ids'] = array();
		}
		if ( !isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_ids'] = array();
		}
		if ( !isset($filter_data['group_ids']) ) {
			$filter_data['group_ids'] = array();
		}
		if ( !isset($filter_data['branch_ids']) ) {
			$filter_data['branch_ids'] = array();
		}
		if ( !isset($filter_data['department_ids']) ) {
			$filter_data['department_ids'] = array();
		}
		if ( !isset($filter_data['user_title_ids']) ) {
			$filter_data['user_title_ids'] = array();
		}
		if ( !isset($filter_data['column_ids']) ) {
			$filter_data['column_ids'] = array();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = array();
		$wage_permission_children_ids = array();
		if ( $permission->Check('user','view') == FALSE ) {
			$hlf = new HierarchyListFactory(); 
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

			if ( $permission->Check('user','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $permission->Check('user','view_own') ) {
				$permission_children_ids[] = $current_user->getId();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}

		//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') == FALSE ) {
				$wage_permission_children_ids = array();
			}
			if ( $permission->Check('wage','view_own') ) {
				$wage_permission_children_ids[] = $current_user->getId();
			}

			$wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
		}

		$ugdlf = new UserGenericDataListFactory();
		$ugdf = new UserGenericDataFactory();

		//$action = Misc::findSubmitButton();

		$action = $_POST['action'] ?? '';
		$action = !empty($action) ? str_replace(' ', '_', strtolower(trim($action))) : '';

		// if(!empty($action)){
		// 	dd($filter_data);
		// }

		switch ($action) {
			case 'export':
			case 'display_report':
				Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
				//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);
				$filter_columns = [];
				$rows = [];

				$ulf = new UserListFactory();
				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				
				if ( $ulf->getRecordCount() > 0 ) {
					foreach( $ulf->rs as $u_obj ) {
						$ulf->data = (array)$u_obj;
						$u_obj = $ulf;
						$filter_data['user_ids'][] = $u_obj->getId();
					}

					//Get title list,
					$utlf = new UserTitleListFactory(); 
					$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );

					//Get default branch list
					$blf = new BranchListFactory();
					$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

					$dlf = new DepartmentListFactory();
					$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

					/* Get Wage History */
					if ( isset($columns['wage']) ) {
						if ( $permission->Check('wage','view') == TRUE ) {
							$wage_filter_data['permission_children_ids'] = $filter_data['user_ids'];
						}

						$uwlf = new UserWageListFactory(); 
						$uwlf->getByUserIdAndCompanyIdAndStartDateAndEndDate( $wage_filter_data['permission_children_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $uwlf->getRecordCount() > 0 ) {
							foreach( $uwlf->rs as $uw_obj ) {
								$uwlf->data = (array)$uw_obj;
								$uw_obj = $uwlf;

								$user_wage_rows[$uw_obj->getUser()][] = array(
									'type_id' => $uw_obj->getType(),
									'type' => Option::getByKey($uw_obj->getType(), $uw_obj->getOptions('type') ),
									'wage' => $uw_obj->getWage(),
									'currency_symbol' => $uw_obj->getUserObject()->getCurrencyObject()->getSymbol(),
									'effective_date' => $uw_obj->getEffectiveDate(),
									'effective_date_since' => TTDate::getHumanTimeSince( $uw_obj->getEffectiveDate() )
								);
							}
						}
					}

					/* Get Attendance History */
					if ( isset($columns['attendance']) ) {
						//Get policy names.
						$oplf = new OverTimePolicyListFactory(); 
						$over_time_policy_arr = $oplf->getByCompanyIdArray($current_company->getId(), FALSE);

						$aplf = new AbsencePolicyListFactory();
						$absence_policy_arr = $aplf->getByCompanyIdArray($current_company->getId(), FALSE);

						$pplf = new PremiumPolicyListFactory();
						$premium_policy_arr = $pplf->getByCompanyIdArray($current_company->getId(), FALSE);

						//Get stats on number of days worked per month/week
						$udlf = new UserDateListFactory(); 
						$udlf->getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $udlf->getRecordCount() > 0 ) {
							foreach( $udlf->rs as $ud_obj ) {
								$udlf->data = (array)$ud_obj;
								$ud_obj = $udlf;

								//$user_days_worked[$ud_obj->getUser()]['month']
								$user_attendance_rows[$ud_obj->getUser()]['days_worked']['month'] = array(
									'avg' => round( $ud_obj->getColumn('avg'),2),
									'min' => $ud_obj->getColumn('min'),
									'max' => $ud_obj->getColumn('max'),
								);
							}
						}

						$udlf->getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $udlf->getRecordCount() > 0 ) {
							foreach( $udlf->rs as $ud_obj ) {
								$udlf->data = (array)$ud_obj;
								$ud_obj = $udlf;

								$user_attendance_rows[$ud_obj->getUser()]['days_worked']['week'] = array(
									'avg' => round( $ud_obj->getColumn('avg'),2),
									'min' => $ud_obj->getColumn('min'),
									'max' => $ud_obj->getColumn('max'),
								);
							}
						}
						//var_dump($user_days_worked);

						$udtlf = new UserDateTotalListFactory();
						$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'day', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );

						if ( $udtlf->getRecordCount() > 0 ) {
							foreach( $udtlf->rs as $udt_obj ) {
								$udtlf->data = (array)$udt_obj;
								$udt_obj = $udtlf;

								if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
									$type = 'regular';
									$policy_id = 0;
									$policy_name = 'regular';
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
									AND $udt_obj->getOverTimePolicyId() != 0 ) {
									$type = 'over_time';
									$policy_id = $udt_obj->getOverTimePolicyId();
									$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
									AND $udt_obj->getPremiumPolicyId() != 0) {
									$type = 'premium';
									$policy_id = $udt_obj->getPremiumPolicyId();
									$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
								} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
									AND $udt_obj->getAbsencePolicyId() != 0 ) {
									$type = 'absence';
									$policy_id = $udt_obj->getAbsencePolicyId();
									$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
								} else {
									$type = NULL;
									$policy_id = NULL;
								}

								if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
								$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id] = array(
										'name' => $policy_name,
										'day' => array(
											'avg' => round( $udt_obj->getColumn('avg'),1),
											'min' => $udt_obj->getColumn('min'),
											'max' => $udt_obj->getColumn('max'),
											'date_units' => $udt_obj->getColumn('date_units'),
										),
										'week' => array(),
										'month' => array(),
									);
								}
								unset($type, $policy_id, $policy_name);
							}
						}

						$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $udtlf->getRecordCount() > 0 ) {
							foreach( $udtlf->rs as $udt_obj ) {
								$udtlf->data = (array)$udt_obj;
								$udt_obj = $udtlf;

								if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
									$type = 'regular';
									$policy_id = 0;
									$policy_name = 'regular';
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
									AND $udt_obj->getOverTimePolicyId() != 0 ) {
									$type = 'over_time';
									$policy_id = $udt_obj->getOverTimePolicyId();
									$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
									AND $udt_obj->getPremiumPolicyId() != 0) {
									$type = 'premium';
									$policy_id = $udt_obj->getPremiumPolicyId();
									$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
								} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
									AND $udt_obj->getAbsencePolicyId() != 0 ) {
									$type = 'absence';
									$policy_id = $udt_obj->getAbsencePolicyId();
									$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
								} else {
									$type = NULL;
									$policy_id = NULL;
								}

								if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
									$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id]['week'] = array(
										'avg' => round( $udt_obj->getColumn('avg'),1),
										'min' => $udt_obj->getColumn('min'),
										'max' => $udt_obj->getColumn('max'),
										'date_units' => $udt_obj->getColumn('date_units'),
									);
								}
								unset($type, $policy_id, $policy_name);
							}
						}

						$udtlf->getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $udtlf->getRecordCount() > 0 ) {
							foreach( $udtlf->rs as $udt_obj ) {
								$udtlf->data = (array)$udt_obj;
								$udt_obj = $udtlf;

								if ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 20 ) {
									$type = 'regular';
									$policy_id = 0;
									$policy_name = 'regular';
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 30
									AND $udt_obj->getOverTimePolicyId() != 0 ) {
									$type = 'over_time';
									$policy_id = $udt_obj->getOverTimePolicyId();
									$policy_name = $over_time_policy_arr[$udt_obj->getOverTimePolicyId()];
								} elseif ( $udt_obj->getStatus() == 10 AND $udt_obj->getType() == 40
									AND $udt_obj->getPremiumPolicyId() != 0) {
									$type = 'premium';
									$policy_id = $udt_obj->getPremiumPolicyId();
									$policy_name = $premium_policy_arr[$udt_obj->getPremiumPolicyId()];
								} elseif ( $udt_obj->getStatus() == 30 AND $udt_obj->getType() == 10
									AND $udt_obj->getAbsencePolicyId() != 0 ) {
									$type = 'absence';
									$policy_id = $udt_obj->getAbsencePolicyId();
									$policy_name = $absence_policy_arr[$udt_obj->getAbsencePolicyId()];
								} else {
									$type = NULL;
									$policy_id = NULL;
								}

								if ( $type !== NULL AND $policy_id !== NULL AND $policy_name !== NULL ) {
									$user_attendance_rows[$udt_obj->getColumn('user_id')]['hours_worked'][$type][$policy_id]['month'] = array(
										'avg' => round( $udt_obj->getColumn('avg'),1),
										'min' => $udt_obj->getColumn('min'),
										'max' => $udt_obj->getColumn('max'),
										'date_units' => $udt_obj->getColumn('date_units'),
									);
								}
								unset($type, $policy_id, $policy_name);
							}
						}


						//var_dump($user_attendance_rows);
						//Repeat broken out by branch/department as well

					}

					/* Exception History */
					if ( isset($columns['exception']) ) {

						//Get exception types.
						$eplf = new ExceptionPolicyListFactory();
						$eplf->getByCompanyId( $current_company->getId() );
						if ( $eplf->getRecordCount() > 0 ) {
							foreach( $eplf->rs as $ep_obj) {
								$eplf->data = (array)$ep_obj;
								$ep_obj = $eplf;

								$exception_policy_arr[$ep_obj->getId()] = array(
									'type_id' => $ep_obj->getType(),
									'name' => Option::getByKey($ep_obj->getType(), $ep_obj->getOptions('type') ),
									'severity_id' => $ep_obj->getSeverity(),
								);
							}
						}
						//var_dump($exception_policy_arr);

						$elf = new ExceptionListFactory();
						$elf->getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'week', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $elf->getRecordCount() > 0 ) {
							foreach( $elf->rs as $e_obj ) {
								$elf->data = (array)$e_obj;
								$e_obj = $elf;

								$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['week'] = array(
									'exception_policy_id' => $e_obj->getColumn('exception_policy_id'),
									'name' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['name'],
									'code' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['type_id'],
									'avg' => round( $e_obj->getColumn('avg'),2),
									'min' => $e_obj->getColumn('min'),
									'max' => $e_obj->getColumn('max'),
									'total' => $e_obj->getColumn('total'),
								);
							}
						}

						$elf->getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( 'month', $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $elf->getRecordCount() > 0 ) {
							foreach( $elf->rs as $e_obj ) {
								$elf->data = (array)$e_obj;
								$e_obj = $elf;

								$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['month'] = array(
									'exception_policy_id' => $e_obj->getColumn('exception_policy_id'),
									'name' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['name'],
									'code' => $exception_policy_arr[$e_obj->getColumn('exception_policy_id')]['type_id'],
									'avg' => round( $e_obj->getColumn('avg'),2),
									'min' => $e_obj->getColumn('min'),
									'max' => $e_obj->getColumn('max'),
									'total' => $e_obj->getColumn('total'),
								);
							}
						}

						$elf->getDOWReportByUserIdAndCompanyIdAndStartDateAndEndDate( $filter_data['user_ids'], $current_company->getId(), $filter_data['start_date'], $filter_data['end_date'] );
						if ( $elf->getRecordCount() > 0 ) {
							foreach( $elf->rs as $e_obj ) {
								$elf->data = (array)$e_obj;
								$e_obj = $elf;

								$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow'][$e_obj->getColumn('dow')] = $e_obj->getColumn('total');

								if ( isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') > $user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max']['total'] ) {
									$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => 	$e_obj->getColumn('total'), 'dow' => $e_obj->getColumn('dow') );
								} elseif ( isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') == $user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max']['total'] ) {
									$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => 	$e_obj->getColumn('total'), 'dow' => 99 );
								} elseif ( !isset($user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'])
								AND $e_obj->getColumn('total') > 0 ) {
									$user_exception_rows[$e_obj->getColumn('user_id')][$e_obj->getColumn('exception_policy_id')]['dow']['max'] = array( 'total' => 	$e_obj->getColumn('total'), 'dow' => $e_obj->getColumn('dow') );
								}

							}
						}

					}
					//var_dump($user_exception_rows);

					/* Get Employee contact information. */
					$ulf = new UserListFactory();
					$ulf->getReportByCompanyIdAndUserIDList( $current_company->getId(), $filter_data['user_ids'] );

					foreach ($ulf->rs as $u_obj ) {
						$ulf->data = (array)$u_obj;
						$u_obj = $ulf;

						if ( isset($user_wage_rows[$u_obj->getID()]) ) {
							$tmp_user_wage_rows = $user_wage_rows[$u_obj->getID()];
						} else {
							$tmp_user_wage_rows = NULL;
						}

						if ( isset($user_attendance_rows[$u_obj->getID()]) ) {
							$tmp_user_attendance_rows = $user_attendance_rows[$u_obj->getID()];
						} else {
							$tmp_user_attendance_rows = NULL;
						}

						if ( isset($user_exception_rows[$u_obj->getID()]) ) {
							$tmp_user_exception_rows = $user_exception_rows[$u_obj->getID()];
						} else {
							$tmp_user_exception_rows = NULL;
						}

						$row_arr = array(
							'id' => $u_obj->getId(),
							'employee_number' => $u_obj->getEmployeeNumber(),
							'user_name' => $u_obj->getUserName(),
							'phone_id' => $u_obj->getPhoneID(),
							'ibutton_id' => $u_obj->getIButtonID(),

							'full_name' => $u_obj->getFullName(TRUE),
							'first_name' => $u_obj->getFirstName(),
							'middle_name' => $u_obj->getMiddleName(),
							'last_name' => $u_obj->getLastName(),

							'title' => Option::getByKey($u_obj->getTitle(), $user_titles ),

							'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
							'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),

							'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex') ),

							'address1' => $u_obj->getAddress1(),
							'address2' => $u_obj->getAddress2(),
							'city' => $u_obj->getCity(),
							'province' => $u_obj->getProvince(),
							'country' => $u_obj->getCountry(),
							'postal_code' => $u_obj->getPostalCode(),
							'work_phone' => $u_obj->getWorkPhone(),
							'home_phone' => $u_obj->getHomePhone(),
							'mobile_phone' => $u_obj->getMobilePhone(),
							'fax_phone' => $u_obj->getFaxPhone(),
							'home_email' => $u_obj->getHomeEmail(),
							'work_email' => $u_obj->getWorkEmail(),
							'birth_date' => $u_obj->getBirthDate(),
							'birth_date_since' => $u_obj->getAge(),
							'sin' => $u_obj->getSIN(),
							'hire_date' => $u_obj->getHireDate(),
							'hire_date_since' => TTDate::getHumanTimeSince( $u_obj->getHireDate() ),
							'termination_date' => $u_obj->getTerminationDate(),

							'user_wage_rows' => $tmp_user_wage_rows,
							'user_attendance_rows' => $tmp_user_attendance_rows,
							'user_exception_rows' => $tmp_user_exception_rows,
						);

						$rows[] = $row_arr;

						unset($tmp_user_wage_rows);
					}

					$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
				}

				foreach( $filter_data['column_ids'] as $column_key ) {
					$filter_columns[$column_key] = $columns[$column_key];
				}

				if ( $action == 'export' ) {
					if ( isset($rows) AND isset($filter_columns) ) {
						Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
						$data = Misc::Array2CSV( $rows, $filter_columns );

						Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
						echo $data;
					} else {
						echo __("No Data To Export!") ."<br>\n";
					}
				} else {

					$viewData['generated_time'] = TTDate::getTime();
					$viewData['columns'] = $filter_columns;
					$viewData['rows'] = $rows;

					return view('report/UserDetailReport', $viewData);
				}

				break;
			case 'delete':
			case 'save':
				Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

				$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
				unset($generic_data['name']);

			default:

				if ( $action == 'load' ) {
					Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
					extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );

				} elseif ( $action == '' ) {
					//Check for default saved report first.
					$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );

					if ( $ugdlf->getRecordCount() > 0 ) {
						Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);
						
						$ugd_obj = $ugdlf->getCurrent();
						$filter_data = $ugd_obj->getData();
						$generic_data['id'] = $ugd_obj->getId();
						
					} else {
						//Default selections
						$filter_data['user_status_ids'] = array( -1 );
						$filter_data['branch_ids'] = array( -1 );
						$filter_data['department_ids'] = array( -1 );
						$filter_data['user_title_ids'] = array( -1 );
						$filter_data['group_ids'] = array( -1 );

						$filter_data['column_ids'] = array_keys($columns);

						$filter_data['start_date'] = TTDate::getBeginMonthEpoch();
						$filter_data['end_date'] = TTDate::getEndMonthEpoch();

						$filter_data['primary_sort'] = '-1000-full_name';
						$filter_data['secondary_sort'] = '-1000-full_name';
					}
				}

				$ulf = new UserListFactory();
				$all_array_option = array('-1' => _('-- All --'));

				//Get include employee list.
				if ( !isset($filter_data['include_user_ids']) ) {
					$filter_data['include_user_ids'] = NULL;
				}
				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );

				$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
				$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
				$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

				//Get exclude employee list
				if ( !isset($filter_data['exclude_user_ids']) ) {
					$filter_data['exclude_user_ids'] = NULL;
				}
				$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
				$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
				$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

				//Get employee status list.
				if ( !isset($filter_data['user_status_ids']) ) {
					$filter_data['user_status_ids'] = NULL;
				}
				$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
				$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
				$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

				//Get Employee Groups
				if ( !isset($filter_data['group_ids']) ) {
					$filter_data['group_ids'] = NULL;
				}
				$uglf = new UserGroupListFactory();
				$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
				$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
				$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

				//Get branches
				if ( !isset($filter_data['branch_ids']) ) {
					$filter_data['branch_ids'] = NULL;
				}
				$blf = new BranchListFactory();
				$blf->getByCompanyId( $current_company->getId() );
				$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
				$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
				$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

				//Get departments
				if ( !isset($filter_data['department_ids']) ) {
					$filter_data['department_ids'] = NULL;
				}
				$dlf = new DepartmentListFactory();
				$dlf->getByCompanyId( $current_company->getId() );
				$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
				$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
				$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

				//Get employee titles
				if ( !isset($filter_data['user_title_ids']) ) {
					$filter_data['user_title_ids'] = NULL;
				}
				$utlf = new UserTitleListFactory();
				$utlf->getByCompanyId( $current_company->getId() );
				$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
				$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
				$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

				//Get column list
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids'] = NULL;
				}
				$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
				$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

				//Get primary/secondary order list
				$filter_data['sort_options'] = $static_columns;
				$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

				$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
				$generic_data['saved_report_options'] = $saved_report_options;

				$viewData['generic_data'] = $generic_data;
				$viewData['filter_data'] = $filter_data;
				$viewData['ugdf'] = $ugdf;
				$viewData['current_user_prefs'] = $current_user_prefs;

				return view('report/UserDetail', $viewData);

				break;
		}

	}
}


?>
