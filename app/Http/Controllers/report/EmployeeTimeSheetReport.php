<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\Users\UserWageListFactory;
use App\Models\Report\TimesheetDetailReport;
use Illuminate\Support\Facades\View;
use TCPDF;

class EmployeeTimeSheetReport extends Controller
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

    public function index()
    {
        $viewData['title'] = __('Attendance Report');
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        /*
         * Get FORM variables
         */
        extract(FormVariables::GetVariables([
            'action',
            'generic_data',
            'filter_data'
        ]));
		      // Permission checks
        if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
            if (!$permission->Check('punch', 'enabled') ||
                !($permission->Check('punch', 'view') || $permission->Check('punch', 'view_own') || $permission->Check('punch', 'view_child'))) {
                $permission->Redirect(false);
            }
        } else {
            if (!$permission->Check('report', 'enabled') || !$permission->Check('report', 'view_timesheet_summary')) {
                $permission->Redirect(false);
            }
        }

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], ['filter_data' => $filter_data]);

        // Define static and dynamic columns
        $static_columns = [
            '-1000-date_stamp' => __('Date'),
            '-1050-min_punch_time_stamp' => 'First In Punch',
            '-1060-max_punch_time_stamp' => 'Last Out Punch',
        ];

        $columns = [
            '-1070-schedule_working' => __('Scheduled Time'),
            '-1080-schedule_absence' => __('Scheduled Absence'),
            '-1090-worked_time' => __('Worked Time'),
            '-1100-actual_time' => __('Actual Time'),
            '-1110-actual_time_diff' => __('Actual Time Difference'),
            '-1120-actual_time_diff_wage' => __('Actual Time Difference Wage'),
            '-1130-paid_time' => __('Paid Time'),
            '-1140-regular_time' => __('Regular Time'),
            '-1150-over_time' => __('Total Over Time'),
            '-1160-absence_time' => __('Total Absence Time'),
        ];

        $columns = Misc::prependArray($static_columns, $columns);
		

        // Get Overtime policies
        $otplf = new OverTimePolicyListFactory();
        $otplf->getByCompanyId($current_company->getId());
        if ($otplf->getRecordCount() > 0) {
            $otp_columns = [];
                 foreach ($otplf->rs as $otp_obj) { {
                    $otplf->data = (array)$otp_obj;
                    $otp_obj = $otplf;
                $otp_columns['over_time_policy-' . $otp_obj->getId()] = $otp_obj->getName();
            }
            $columns = array_merge($columns, $otp_columns);
        }

        // Get Premium policies
        $pplf = new PremiumPolicyListFactory();
        $pplf->getByCompanyId($current_company->getId());
        if ($pplf->getRecordCount() > 0) {
            $pp_columns = [];
            foreach ($pplf as $pp_obj) {
                $pp_columns['premium_policy-' . $pp_obj->getId()] = $pp_obj->getName();
            }
            $columns = array_merge($columns, $pp_columns);
        }

        // Get Absence policies
        $aplf = new AbsencePolicyListFactory();
        $aplf->getByCompanyId($current_company->getId());
        if ($aplf->getRecordCount() > 0) {
            $ap_columns = [];
            foreach ($aplf->rs as $ap_obj) {
				$aplf->data = (array)$ap_obj;
                $ap_obj = $aplf;
                $ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
            }
            $columns = array_merge($columns, $ap_columns);
        }

        // Get pay periods
        $pplf = new PayPeriodListFactory();
        $pplf->getByCompanyId($current_company->getId());
        $pay_period_ids = [];
        $pay_period_end_dates = [];
        $pay_period_options = [];
        if ($pplf->getRecordCount() > 0) {
            $pp = 0;
			
            foreach ($pplf->rs as $pay_period_obj) {
				  $pplf->data = (array)$pay_period_obj;
                $pay_period_obj = $pplf;
                $pay_period_ids[] = $pay_period_obj->getId();
                $pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();
                if ($pp == 0) {
                    $default_start_date = $pay_period_obj->getStartDate();
                    $default_end_date = $pay_period_obj->getEndDate();
                }
                $pp++;
            }
			
            $pay_period_options = $pplf->getByIdListArray($pay_period_ids, null, ['start_date' => 'desc'], false);
        }

        // Parse dates
        if (isset($filter_data['start_date'])) {
            $filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
        }
        if (isset($filter_data['end_date'])) {
            $filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
        }

        $filter_data = Misc::preSetArrayValues($filter_data, [
            'include_user_ids',
            'exclude_user_ids',
            'user_status_ids',
            'group_ids',
            'branch_ids',
            'department_ids',
            'user_title_ids',
            'pay_period_ids',
            'column_ids'
        ], []);

        // Permission Hierarchy Children
        $permission_children_ids = [];
        $wage_permission_children_ids = [];
        if (!$permission->Check('punch', 'view')) {
            $hlf = new HierarchyListFactory();
            $permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId());
            Debug::Arr($permission_children_ids, 'Permission Children Ids:', __FILE__, __LINE__, __METHOD__, 10);

            if (!$permission->Check('punch', 'view_child')) {
                $permission_children_ids = [];
            }
            if ($permission->Check('punch', 'view_own')) {
                $permission_children_ids[] = $current_user->getId();
            }
            $filter_data['permission_children_ids'] = $permission_children_ids ?? [];
        }

        // Wage Permission Hierarchy Children
        if (!$permission->Check('wage', 'view')) {
            if (!$permission->Check('wage', 'view_child')) {
                $wage_permission_children_ids = [];
            }
            if ($permission->Check('wage', 'view_own')) {
                $wage_permission_children_ids[] = $current_user->getId();
            }
            $wage_filter_data['permission_children_ids'] = $wage_permission_children_ids ?? [];
        }

        $ugdlf = new UserGenericDataListFactory();
        $ugdf = new UserGenericDataFactory();
     	$action = $_POST['action'] ?? '';
        $action = !empty($action) ? str_replace(' ', '_', strtolower(trim($action))) : '';


        switch ($action) {
            case 'export':
            case 'display_report':
            case 'display_timesheet':
            case 'display_detailed_timesheet':
                Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

                // Handle print timesheet logic
                if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
                    if (!isset($filter_data['user_id']) || !($permission->Check('punch', 'view') || $permission->Check('punch', 'view_child'))) {
                        $filter_data['user_id'] = $current_user->getId();
                    }
                    $action = $filter_data['print_timesheet'] == 2 ? 'display_detailed_timesheet' : 'display_timesheet';
                    $filter_data = [
                        'permission_children_ids' => [(int)$filter_data['user_id']],
                        'pay_period_ids' => [(int)$filter_data['pay_period_ids']],
                        'date_type' => 'pay_period_ids',
                        'primary_sort' => '-1000-date_stamp',
                        'secondary_sort' => null,
                        'primary_sort_dir' => 1,
                        'secondary_sort_dir' => null,
                        'column_ids' => array_keys($static_columns)
                    ];
                } 

                $ulf = new UserListFactory();
                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                $rows = [];
                $filter_columns = [];
                if ($ulf->getRecordCount() > 0) {
                    if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                        unset($filter_data['start_date'], $filter_data['end_date']);
                    } else {
                        unset($filter_data['pay_period_ids']);
                    }

                    foreach ($ulf->rs as $u_obj) {
						$ulf->data = (array)$u_obj;
                        $u_obj = $ulf;
                        $filter_data['user_id'][] = $u_obj->getId();
                    }
                    if (isset($filter_data['pay_period_ids'])) {
                        $tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
                        $filter_data['pay_period_ids'] = [];
                        foreach ($tmp_filter_pay_period_ids as $filter_pay_period_id) {
                            $filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
                        }
                    }

                    $end_date = (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0)
                        ? (in_array('-1', $filter_data['pay_period_ids']) ? time() : max(array_map(function ($id) use ($pay_period_end_dates) {
                            return $pay_period_end_dates[Misc::trimSortPrefix($id)] ?? time();
                        }, $filter_data['pay_period_ids'])))
                        : ($filter_data['end_date'] ?? time());

                    if ($permission->Check('wage', 'view')) {
                        $wage_filter_data['permission_children_ids'] = $filter_data['user_id'];
                    }

                    $uwlf = new UserWageListFactory();
                    $uwlf->getLastWageByUserIdAndDate($wage_filter_data['permission_children_ids'], $end_date);
                    $user_wage = [];
                    if ($uwlf->getRecordCount() > 0) {
                        foreach ($uwlf as $uw_obj) {
                            $user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate($uw_obj->getHourlyRate());
                        }
                    }

                    $udtlf = new UserDateTotalListFactory();
                    $slf = new ScheduleListFactory();
                    $schedule_rows = [];
                    $tmp_rows = [];
                    $punch_rows = [];

                    if (isset($filter_data['user_id'])) {
                        $udtlf->getDayReportByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                        $slf->getDayReportByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                    
					}

                    if ($slf->getRecordCount() > 0) {
                        foreach ($slf->rs as $s_obj) {
							$slf->data = (array)$s_obj;
                            $s_obj = $slf;
                            $user_id = $s_obj->getColumn('user_id');
                            $status_id = $s_obj->getColumn('status_id');
                            $status = strtolower(Option::getByKey($status_id, $s_obj->getOptions('status')));
                            $pay_period_id = $s_obj->getColumn('pay_period_id');
                            $date_stamp = TTDate::strtotime($s_obj->getColumn('date_stamp'));

                            $schedule_rows[$pay_period_id][$user_id][$date_stamp][$status] = $s_obj->getColumn('total_time');
                            $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] = $s_obj->getColumn('start_time');
                            $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] = $s_obj->getColumn('end_time');
                        }
                    }

                    foreach ($udtlf->rs as $udt_obj) {
						$udtlf->data = (array)$udt_obj;
                        $udt_obj = $udtlf;
                        $user_id = $udt_obj->getColumn('id');
                        $pay_period_id = $udt_obj->getColumn('pay_period_id');
                        $date_stamp = TTDate::strtotime($udt_obj->getColumn('date_stamp'));
                        $status_id = $udt_obj->getColumn('status_id');
                        $type_id = $udt_obj->getColumn('type_id');
                        $category = 0;
                        $policy_id = 0;

                        if ($status_id == 10 && $type_id == 10) {
                            $column = 'paid_time';
                            $category = $column;
                        } elseif ($status_id == 10 && $type_id == 20) {
                            $column = 'regular_time';
                            $category = $column;
                        } elseif ($status_id == 10 && $type_id == 30) {
                            $column = 'over_time_policy-' . $udt_obj->getColumn('over_time_policy_id');
                            $category = 'over_time_policy';
                            $policy_id = $udt_obj->getColumn('over_time_policy_id');
                        } elseif ($status_id == 10 && $type_id == 40) {
                            $column = 'premium_policy-' . $udt_obj->getColumn('premium_policy_id');
                            $category = 'premium_policy';
                            $policy_id = $udt_obj->getColumn('premium_policy_id');
                        } elseif ($status_id == 30 && $type_id == 10) {
                            $column = 'absence_policy-' . $udt_obj->getColumn('absence_policy_id');
                            $category = 'absence_policy';
                            $policy_id = $udt_obj->getColumn('absence_policy_id');
                        } elseif (($status_id == 20 && $type_id == 10) || ($status_id == 10 && $type_id == 100)) {
                            $column = 'worked_time';
                            $category = $column;
                        } else {
                            $column = null;
                        }

                        if ($column == 'worked_time') {
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] ?? 0) + (int)$udt_obj->getColumn('total_time');
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] ?? 0) + $udt_obj->getColumn('actual_total_time');

                            $actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time'));
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] ?? 0) + $actual_time_diff;

                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = isset($user_wage[$user_id])
                                ? Misc::MoneyFormat(bcmul(TTDate::getHours($actual_time_diff), $user_wage[$user_id]), false)
                                : Misc::MoneyFormat(0, false);
                        } elseif ($column != null && $udt_obj->getColumn('total_time') > 0) {
                            if ($status_id == 30 && $type_id == 10) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] ?? 0) + $udt_obj->getColumn('total_time');
                            }
                            if ($status_id == 10 && $type_id == 30) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] ?? 0) + $udt_obj->getColumn('total_time');
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time_policy_id'] = $policy_id;
                            }
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] ?? 0) + $udt_obj->getColumn('total_time');

                            if (in_array($action, ['display_timesheet', 'display_detailed_timesheet'])) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] =
                                    ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] ?? 0) +
                                    $udt_obj->getColumn('total_time');
                            }
                        }

                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'] ?? null;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'] ?? null;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] ?? null;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] ?? null;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('min_punch_time_stamp'));
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('max_punch_time_stamp'));
                    }

                    if ($action == 'display_detailed_timesheet') {
                        $plf = new PunchListFactory();
                        $plf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                        if ($plf->getRecordCount() > 0) {
                            foreach ($plf as $p_obj) {
                                $punch_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][TTDate::strtotime($p_obj->getColumn('date_stamp'))][$p_obj->getPunchControlID()][$p_obj->getStatus()] = [
                                    'status_id' => $p_obj->getStatus(),
                                    'type_id' => $p_obj->getType(),
                                    'type_code' => $p_obj->getTypeCode(),
                                    'time_stamp' => $p_obj->getTimeStamp()
                                ];
                            }
                        }
                    }

                    $ulf = new UserListFactory();
                    $utlf = new UserTitleListFactory();
                    $title_options = $utlf->getByCompanyIdArray($current_company->getId());
                    $blf = new BranchListFactory();
                    $branch_options = $blf->getByCompanyIdArray($current_company->getId());
                    $dlf = new DepartmentListFactory();
                    $department_options = $dlf->getByCompanyIdArray($current_company->getId());
                    $uglf = new UserGroupListFactory();
                    $group_options = $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'no_tree_text', true));

                    $verified_time_sheets = null;
                    if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
                        $pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
                        $pptsvlf->getByPayPeriodIdAndCompanyId($filter_data['pay_period_ids'][0], $current_company->getId());
                        if ($pptsvlf->getRecordCount() > 0) {
                            foreach ($pptsvlf as $pptsv_obj) {
                                $verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = [
                                    'status_id' => $pptsv_obj->getStatus(),
                                    'created_date' => $pptsv_obj->getCreatedDate(),
                                ];
                            }
                        }
                    }

                    if (isset($tmp_rows)) {
                        $i = 0;
                        foreach ($tmp_rows as $pay_period_id => $data_a) {
                            foreach ($data_a as $user_id => $data_b) {
                                $user_obj = $ulf->getById($user_id)->getCurrent();
                                $rows[$i]['pay_period'] = $pay_period_options[$pay_period_id] ?? 'N/A';
                                $rows[$i]['pay_period_id'] = $pay_period_id;
                                $rows[$i]['user_id'] = $user_id;
                                $rows[$i]['first_name'] = $user_obj->getFirstName();
                                $rows[$i]['last_name'] = $user_obj->getLastName();
                                $rows[$i]['full_name'] = $user_obj->getFullName(true);
                                $rows[$i]['employee_number'] = $user_obj->getEmployeeNumber();
                                $rows[$i]['province'] = $user_obj->getProvince();
                                $rows[$i]['country'] = $user_obj->getCountry();
                                $rows[$i]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, null);
                                $rows[$i]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, null);
                                $rows[$i]['default_branch'] = Option::getByKey($user_obj->getDefaultBranch(), $branch_options, null);
                                $rows[$i]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, null);

                                $rows[$i]['verified_time_sheet_date'] = false;
                                if ($verified_time_sheets !== null && isset($verified_time_sheets[$user_id][$pay_period_id])) {
                                    if ($verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 50) {
                                        $rows[$i]['verified_time_sheet'] = __('Yes');
                                        $rows[$i]['verified_time_sheet_date'] = $verified_time_sheets[$user_id][$pay_period_id]['created_date'];
                                    } elseif (in_array($verified_time_sheets[$user_id][$pay_period_id]['status_id'], [30, 45])) {
                                        $rows[$i]['verified_time_sheet'] = __('Pending');
                                    } else {
                                        $rows[$i]['verified_time_sheet'] = __('Declined');
                                    }
                                } else {
                                    $rows[$i]['verified_time_sheet'] = __('No');
                                }

                                $sub_rows = [];
                                $x = 0;
                                foreach ($data_b as $date_stamp => $data_c) {
                                    $sub_rows[$x]['date_stamp'] = $date_stamp;
                                    foreach ($data_c as $column => $total_time) {
                                        $sub_rows[$x][$column] = $total_time;
                                    }
                                    $x++;
                                }

                                if (isset($sub_rows)) {
                                    $tmp_sub_rows = Sort::Multisort(
                                        $sub_rows,
                                        Misc::trimSortPrefix($filter_data['primary_sort']),
                                        Misc::trimSortPrefix($filter_data['secondary_sort']),
                                        $filter_data['primary_sort_dir'],
                                        $filter_data['secondary_sort_dir']
                                    );

                                    if (!in_array($action, ['display_timesheet', 'display_detailed_timesheet'])) {
                                        $total_sub_row = Misc::ArrayAssocSum($tmp_sub_rows, null, 2);
                                        $tmp_sub_rows[] = array_merge($total_sub_row, array_fill_keys(array_keys(Misc::trimSortPrefix($static_columns)), null));
                                    }

                                    $trimmed_static_columns = array_keys(Misc::trimSortPrefix($static_columns));
                                    $sub_rows = [];
									
                                    foreach ($tmp_sub_rows as $sub_row) {
										$sub_row_columns = [];
										foreach ($sub_row as $column => $column_data) {
											if (!in_array($action, ['display_timesheet', 'display_detailed_timesheet'])) {
												if ($column == 'date_stamp') {
													$column_data = TTDate::getDate('DATE', $column_data);
												} elseif (!strstr($column, 'wage') && !in_array($column, $trimmed_static_columns)) {
													// Cast $column_data to integer or float to ensure numeric type
													$column_data = is_numeric($column_data) ? (int)$column_data : 0;
													$column_data = TTDate::getTimeUnit($column_data);
												}
											}
											$sub_row_columns[$column] = $column_data;
										}
										$sub_rows[] = $sub_row_columns;
									}

                                    foreach ($filter_data['column_ids'] as $column_key) {
                                        if (isset($columns[$column_key])) {
                                            $filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
                                        }
                                    }
                                }

                                $rows[$i]['data'] = $sub_rows;
                                $i++;
                            }
                        }
                    }

                    if ($action == 'display_timesheet') {
                        if (isset($rows)) {
                            $pdf_created_date = time();
                            $pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
                            $pdf->setMargins(5, 5);
                            $pdf->SetAutoPageBreak(false);
                            $pdf->SetFont('freeserif', '', 10);

                            $border = 0;
                            foreach ($rows as $user_data) {
                                $pdf->AddPage();
                                $adjust_x = 10;
                                $adjust_y = 10;

                                $pdf->SetFont('', 'B', 32);
                                $pdf->Cell(200, 15, __('Employee TimeSheet'), $border, 0, 'C');
                                $pdf->Ln();
                                $pdf->SetFont('', 'B', 12);
                                $pdf->Cell(200, 5, $current_company->getName(), $border, 0, 'C');
                                $pdf->Ln(10);

                                $pdf->Rect($pdf->getX(), $pdf->getY() - 2, 200, 19);

                                $pdf->SetFont('', '', 12);
                                $pdf->Cell(30, 5, __('Employee:'), $border, 0, 'R');
                                $pdf->SetFont('', 'B', 12);
                                $pdf->Cell(70, 5, $user_data['first_name'] . ' ' . $user_data['last_name'] . ' (#' . $user_data['employee_number'] . ')', $border, 0, 'L');

                                $pdf->SetFont('', '', 12);
                                $pdf->Cell(40, 5, __('Pay Period:'), $border, 0, 'R');
                                $pdf->SetFont('', 'B', 12);
                                $pdf->Cell(60, 5, $user_data['pay_period'], $border, 0, 'L');
                                $pdf->Ln();

                                $pdf->SetFont('', '', 12);
                                $pdf->Cell(30, 5, __('Title:'), $border, 0, 'R');
                                $pdf->Cell(70, 5, $user_data['title'], $border, 0, 'L');
                                $pdf->Cell(40, 5, __('Branch:'), $border, 0, 'R');
                                $pdf->Cell(60, 5, $user_data['default_branch'], $border, 0, 'L');
                                $pdf->Ln();

                                $pdf->Cell(30, 5, __('Group:'), $border, 0, 'R');
                                $pdf->Cell(70, 5, $user_data['group'], $border, 0, 'L');
                                $pdf->Cell(40, 5, __('Department:'), $border, 0, 'R');
                                $pdf->Cell(60, 5, $user_data['default_department'], $border, 0, 'L');
                                $pdf->Ln(5);

                                $pdf->SetFont('', '', 10);
                                $column_widths = [
                                    'line' => 5,
                                    'date_stamp' => 20,
                                    'dow' => 10,
                                    'min_punch_time_stamp' => 25,
                                    'max_punch_time_stamp' => 25,
                                    'worked_time' => 25,
                                    'regular_time' => 25,
                                    'over_time' => 20,
                                    'paid_time' => 20,
                                    'absence_time' => 25,
                                ];

                                if (isset($user_data['data']) && is_array($user_data['data'])) {
                                    if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                                        $pplf = new PayPeriodListFactory();
                                        $pplf->getById($user_data['pay_period_id']);
                                        if ($pplf->getRecordCount() == 1) {
                                            $pp_obj = $pplf->getCurrent();
                                            for ($d = TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d += 86400) {
                                                if (Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d)) == false) {
                                                    $user_data['data'][] = [
                                                        'date_stamp' => TTDate::getBeginDayEpoch($d),
                                                        'min_punch_time' => null,
                                                        'max_punch_time' => null,
                                                        'worked_time' => null,
                                                        'regular_time' => null,
                                                        'over_time' => null,
                                                        'paid_time' => null,
                                                        'absence_time' => null
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    $user_data['data'] = Sort::Multisort($user_data['data'], 'date_stamp', null, 'ASC');
                                    $week_totals = Misc::preSetArrayValues(null, ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);
                                    $totals = Misc::preSetArrayValues([], ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);

                                    $i = 1;
                                    $x = 1;
                                    $y = 1;
                                    $max_i = count($user_data['data']);
                                    foreach ($user_data['data'] as $data) {
                                        if ($i == 1 || $x == 1) {
                                            if ($x == 1) {
                                                $pdf->Ln();
                                            }
                                            $line_h = 6;
                                            $pdf->SetFont('', 'B', 10);
                                            $pdf->setFillColor(220, 220, 220);
                                            $pdf->MultiCell($column_widths['line'], $line_h, '#', 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['date_stamp'], $line_h, __('Date'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['dow'], $line_h, __('DoW'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['min_punch_time_stamp'], $line_h, __('First In'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['max_punch_time_stamp'], $line_h, __('Last Out'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['worked_time'], $line_h, __('Worked Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['regular_time'], $line_h, __('Regular Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['over_time'], $line_h, __('Over Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['paid_time'], $line_h, __('Paid Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['absence_time'], $line_h, __('Absence Time'), 1, 'C', 1, 0);
                                            $pdf->Ln();
                                        }

                                        $data = Misc::preSetArrayValues($data, ['date_stamp', 'min_punch_time_stamp', 'max_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], '--');

                                        if ($x % 2 == 0) {
                                            $pdf->setFillColor(220, 220, 220);
                                        } else {
                                            $pdf->setFillColor(255, 255, 255);
                                        }

                                        if ($data['date_stamp'] !== '') {

											 		$worked_time = is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                                    $regular_time = is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                                    $over_time = is_numeric($data['over_time']) ? $data['over_time'] : 0;
                                                    $paid_time = is_numeric($data['paid_time']) ? $data['paid_time'] : 0;
                                                    $absence_time = is_numeric($data['absence_time']) ? $data['absence_time'] : 0;


													$pdf->SetFont('', '', 10);
													$pdf->Cell($column_widths['line'], 6, $x, 1, 0, 'C', 1);
													$pdf->Cell($column_widths['date_stamp'], 6, TTDate::getDate('DATE', $data['date_stamp']), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['dow'], 6, date('D', $data['date_stamp']), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['min_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['min_punch_time_stamp']), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['max_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['max_punch_time_stamp']), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($worked_time), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($regular_time), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($over_time), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($paid_time), 1, 0, 'C', 1);
													$pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($absence_time), 1, 0, 'C', 1);
													$pdf->Ln();
                                        }

										$totals['worked_time'] += is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                        $totals['paid_time'] += is_numeric($data['paid_time']) ? $data['paid_time'] : 0;
                                        $totals['absence_time'] += is_numeric($data['absence_time']) ? $data['absence_time'] : 0;
                                        $totals['regular_time'] += is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                        $totals['over_time'] += is_numeric($data['over_time']) ? $data['over_time'] : 0;

                                    	$week_totals['worked_time'] += is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                        $week_totals['paid_time'] += is_numeric($data['paid_time']) ? $data['paid_time'] : 0;
                                        $week_totals['absence_time'] += is_numeric($data['absence_time']) ? $data['absence_time'] : 0;
                                        $week_totals['regular_time'] += is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                        $week_totals['over_time'] += is_numeric($data['over_time']) ? $data['over_time'] : 0;

                                        if ($x % 7 == 0 || $i == $max_i) {
                                            $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['min_punch_time_stamp'] + $column_widths['max_punch_time_stamp'];
                                            $pdf->SetFont('', 'B', 10);
                                            $pdf->Cell($total_cell_width, 6, __('Week Total:') . ' ', 0, 0, 'R', 0);
                                            $pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($week_totals['worked_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($week_totals['regular_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($week_totals['over_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($week_totals['paid_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($week_totals['absence_time']), 0, 0, 'C', 0);
                                            $pdf->Ln(2);

                                            $week_totals = Misc::preSetArrayValues(null, ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);
                                            $x = 0;
                                            $y++;

                                            if ($y == 4 && $i !== $max_i) {
                                                $pdf->AddPage();
                                            }
                                        }

                                        $i++;
                                        $x++;
                                    }

                                    if (isset($totals) && is_array($totals)) {
                                        $pdf->Ln(3);
                                        $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['min_punch_time_stamp'];
                                        $pdf->SetFont('', 'B', 10);
                                        $pdf->Cell($total_cell_width, 6, '', 0, 0, 'R', 0);
                                        $pdf->Cell($column_widths['max_punch_time_stamp'], 6, __('Overall Total:') . ' ', 'T', 0, 'R', 0);
                                        $pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($totals['worked_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($totals['regular_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($totals['over_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($totals['paid_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($totals['absence_time']), 'T', 0, 'C', 0);
                                        $pdf->Ln();
                                    }

                                    $pdf->SetFont('', '', 10);
                                    $pdf->setFillColor(255, 255, 255);
                                    $pdf->Ln();

                                    $pdf->MultiCell(200, 5, __('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . __('worked during the designated period.'), $border, 'L');
                                    $pdf->Ln(5);

                                    $pdf->Cell(40, 5, __('Employee Signature:'), $border, 0, 'L');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Cell(40, 5, __('Supervisor Signature:'), $border, 0, 'R');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(40, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, $user_data['first_name'] . ' ' . $user_data['last_name'], $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(140, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(140, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, __('(print name)'), $border, 0, 'C');

                                    if ($user_data['verified_time_sheet_date'] != false) {
                                        $pdf->Ln();
                                        $pdf->SetFont('', 'B', 10);
                                        $pdf->Cell(200, 5, __('TimeSheet electronically signed by') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . __('on') . ' ' . TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date']), $border, 0, 'C');
                                        $pdf->SetFont('', '', 10);
                                    }

                                    $pdf->SetFont('', 'I', 8);
                                    $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y));
                                    $pdf->Cell(200, 5, __('Generated:') . ' ' . TTDate::getDate('DATE+TIME', $pdf_created_date), $border, 0, 'C');
                                }
                            }

                            $output = $pdf->Output('', 'S');
                            if ($output !== false && Debug::getVerbosity() < 11) {
                                return response()->streamDownload(function () use ($output) {
                                    echo $output;
                                }, 'timesheet.pdf', ['Content-Type' => 'application/pdf']);
                            } else {
                                echo __('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
                                exit;
                            }
                        }
                    } elseif ($action == 'display_detailed_timesheet') {
                        if (isset($rows)) {
                            $pdf_created_date = time();
                            $pdf = new TCPDF('P', 'mm', 'Letter', true, 'UTF-8', false);
                            $pdf->setMargins(5, 5);
                            $pdf->SetAutoPageBreak(true, 10);
                            $pdf->SetFont('freeserif', '', 10);

                            $border = 0;
                            foreach ($rows as $user_data) {
                                $pdf->AddPage();
                                $adjust_x = 10;
                                $adjust_y = 10;

                                $pdf->SetFont('', 'B', 22);
                                $pdf->Cell(200, 8, __('Detailed Employee TimeSheet'), $border, 0, 'C');
                                $pdf->Ln();
                                $pdf->SetFont('', 'B', 12);
                                $pdf->Cell(200, 5, $current_company->getName(), $border, 0, 'C');
                                $pdf->Ln(8);

                                $pdf->Rect($pdf->getX(), $pdf->getY() - 1, 200, 14);

                                $pdf->SetFont('', '', 10);
                                $pdf->Cell(30, 4, __('Employee:'), $border, 0, 'R');
                                $pdf->SetFont('', 'B', 10);
                                $pdf->Cell(70, 4, $user_data['first_name'] . ' ' . $user_data['last_name'] . ' (#' . $user_data['employee_number'] . ')', $border, 0, 'L');

                                $pdf->SetFont('', '', 10);
                                $pdf->Cell(40, 4, __('Pay Period:'), $border, 0, 'R');
                                $pdf->SetFont('', 'B', 10);
                                $pdf->Cell(60, 4, $user_data['pay_period'], $border, 0, 'L');
                                $pdf->Ln();

                                $pdf->SetFont('', '', 10);
                                $pdf->Cell(30, 4, __('Title:'), $border, 0, 'R');
                                $pdf->Cell(70, 4, $user_data['title'], $border, 0, 'L');
                                $pdf->Cell(40, 4, __('Branch:'), $border, 0, 'R');
                                $pdf->Cell(60, 4, $user_data['default_branch'], $border, 0, 'L');
                                $pdf->Ln();

                                $pdf->Cell(30, 4, __('Group:'), $border, 0, 'R');
                                $pdf->Cell(70, 4, $user_data['group'], $border, 0, 'L');
                                $pdf->Cell(40, 4, __('Department:'), $border, 0, 'R');
                                $pdf->Cell(60, 4, $user_data['default_department'], $border, 0, 'L');
                                $pdf->Ln(3);

                                $pdf->SetFont('', '', 10);
                                $column_widths = [
                                    'line' => 5,
                                    'date_stamp' => 20,
                                    'dow' => 10,
                                    'in_punch_time_stamp' => 20,
                                    'out_punch_time_stamp' => 20,
                                    'worked_time' => 15,
                                    'paid_time' => 15,
                                    'regular_time' => 15,
                                    'over_time' => 37,
                                    'absence_time' => 43,
                                ];

                                if (isset($user_data['data']) && is_array($user_data['data'])) {
                                    if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                                        $pplf = new PayPeriodListFactory();
                                        $pplf->getById($user_data['pay_period_id']);
                                        if ($pplf->getRecordCount() == 1) {
                                            $pp_obj = $pplf->getCurrent();
                                            for ($d = TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d += 86400) {
                                                if (Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d)) == false) {
                                                    $user_data['data'][] = [
                                                        'date_stamp' => TTDate::getBeginDayEpoch($d),
                                                        'in_punch_time' => null,
                                                        'out_punch_time' => null,
                                                        'worked_time' => null,
                                                        'regular_time' => null,
                                                        'over_time' => null,
                                                        'paid_time' => null,
                                                        'absence_time' => null
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    $user_data['data'] = Sort::Multisort($user_data['data'], 'date_stamp', null, 'ASC');
                                    $week_totals = Misc::preSetArrayValues(null, ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);
                                    $totals = Misc::preSetArrayValues([], ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);

                                    $i = 1;
                                    $x = 1;
                                    $y = 1;
                                    $max_i = count($user_data['data']);
                                    foreach ($user_data['data'] as $data) {
                                        if ($i == 1 || $x == 1) {
                                            if ($x == 1) {
                                                $pdf->Ln();
                                            }
                                            $line_h = 5;
                                            $pdf->SetFont('', 'B', 10);
                                            $pdf->setFillColor(220, 220, 220);
                                            $pdf->MultiCell($column_widths['line'], $line_h, '#', 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['date_stamp'], $line_h, __('Date'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['dow'], $line_h, __('DoW'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['in_punch_time_stamp'], $line_h, __('In'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['out_punch_time_stamp'], $line_h, __('Out'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['worked_time'], $line_h, __('Worked Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['paid_time'], $line_h, __('Paid Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['regular_time'], $line_h, __('Regular Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['over_time'], $line_h, __('Over Time'), 1, 'C', 1, 0);
                                            $pdf->MultiCell($column_widths['absence_time'], $line_h, __('Absence Time'), 1, 'C', 1, 0);
                                            $pdf->Ln();
                                        }

                                        $data = Misc::preSetArrayValues($data, ['date_stamp', 'in_punch_time_stamp', 'out_punch_time_stamp', 'worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], '--');

                                        if ($x % 2 == 0) {
                                            $pdf->setFillColor(220, 220, 220);
                                        } else {
                                            $pdf->setFillColor(255, 255, 255);
                                        }

                                        if ($data['date_stamp'] !== '') {
                                            $default_line_h = 4;
                                            $line_h = $default_line_h;
                                            $total_rows_arr = [];

                                            $total_punch_rows = 1;
                                            if (isset($punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']])) {
                                                $day_punch_data = $punch_rows[$user_data['pay_period_id']][$user_data['user_id']][$data['date_stamp']];
                                                $total_punch_rows = count($day_punch_data);
                                            }
                                            $total_rows_arr[] = $total_punch_rows;

                                            $total_over_time_rows = 1;
                                            if ($data['over_time'] > 0 && isset($data['categorized_time']['over_time_policy'])) {
                                                $total_over_time_rows = count($data['categorized_time']['over_time_policy']);
                                            }
                                            $total_rows_arr[] = $total_over_time_rows;

                                            $total_absence_rows = 1;
                                            if ($data['absence_time'] > 0 && isset($data['categorized_time']['absence_policy'])) {
                                                $total_absence_rows = count($data['categorized_time']['absence_policy']);
                                            }
                                            $total_rows_arr[] = $total_absence_rows;

                                            rsort($total_rows_arr);
                                            $max_rows = $total_rows_arr[0];
                                            $line_h = $default_line_h * $max_rows;

                                            $pdf->SetFont('', '', 9);
                                            $pdf->Cell($column_widths['line'], $line_h, $x, 1, 0, 'C', 1);
                                            $pdf->Cell($column_widths['date_stamp'], $line_h, TTDate::getDate('DATE', $data['date_stamp']), 1, 0, 'C', 1);
                                            $pdf->Cell($column_widths['dow'], $line_h, date('D', $data['date_stamp']), 1, 0, 'C', 1);

                                            $pre_punch_x = $pdf->getX();
                                            $pre_punch_y = $pdf->getY();

                                            if (isset($day_punch_data)) {
                                                $pdf->SetFont('', '', 8);
                                                $n = 0;
                                                foreach ($day_punch_data as $punch_control_id => $punch_data) {
                                                    if (!isset($punch_data[10]['time_stamp'])) {
                                                        $punch_data[10]['time_stamp'] = null;
                                                        $punch_data[10]['type_code'] = null;
                                                    }
                                                    if (!isset($punch_data[20]['time_stamp'])) {
                                                        $punch_data[20]['time_stamp'] = null;
                                                        $punch_data[20]['type_code'] = null;
                                                    }

                                                    if ($n > 0) {
                                                        $pdf->setXY($pre_punch_x, $punch_y + $default_line_h);
                                                    }

                                                    $pdf->Cell($column_widths['in_punch_time_stamp'], $line_h / $total_punch_rows, TTDate::getDate('TIME', $punch_data[10]['time_stamp']) . ' ' . $punch_data[10]['type_code'], 1, 0, 'C', 1);
                                                    $pdf->Cell($column_widths['out_punch_time_stamp'], $line_h / $total_punch_rows, TTDate::getDate('TIME', $punch_data[20]['time_stamp']) . ' ' . $punch_data[20]['type_code'], 1, 0, 'C', 1);

                                                    $punch_x = $pdf->getX();
                                                    $punch_y = $pdf->getY();
                                                    $n++;
                                                }
                                                $pdf->setXY($punch_x, $pre_punch_y);
                                                $pdf->SetFont('', '', 9);
                                            } else {
                                                $pdf->Cell($column_widths['in_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
                                                $pdf->Cell($column_widths['out_punch_time_stamp'], $line_h, '', 1, 0, 'C', 1);
                                            }
											$worked_time = is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                            $regular_time = is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                            $paid_time = is_numeric($data['paid_time']) ? $data['paid_time'] : 0;

                                            $pdf->Cell($column_widths['worked_time'], $line_h, TTDate::getTimeUnit($worked_time), 1, 0, 'C', 1);
                                            $pdf->Cell($column_widths['paid_time'], $line_h, TTDate::getTimeUnit($paid_time), 1, 0, 'C', 1);
                                            $pdf->Cell($column_widths['regular_time'], $line_h, TTDate::getTimeUnit($regular_time), 1, 0, 'C', 1);

                                            if ($data['over_time'] > 0 && isset($data['categorized_time']['over_time_policy'])) {
                                                $pre_over_time_x = $pdf->getX();
                                                $pdf->SetFont('', '', 8);
                                                  $over_time_policy_total_rows = count($data['categorized_time']['over_time_policy']);
                                                        foreach ($data['categorized_time']['over_time_policy'] as $policy_id => $value) {
                                                            $pdf->Cell($column_widths['over_time'], $line_h / $total_over_time_rows, $otp_columns['over_time_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
                                                            $pdf->setXY($pre_over_time_x, $pdf->getY() + ($line_h / $total_over_time_rows));
                                                    $over_time_x = $pdf->getX();
                                                }
                                                $pdf->setXY($over_time_x + $column_widths['over_time'], $pre_punch_y);
                                                $pdf->SetFont('', '', 9);
                                            } else {
												
											$over_time = is_numeric($data['over_time']) ? $data['over_time'] : 0;
                                                $pdf->Cell($column_widths['over_time'], $line_h, TTDate::getTimeUnit($over_time), 1, 0, 'C', 1);
                                            }

						
                                            if ($data['absence_time'] > 0 && isset($data['categorized_time']['absence_policy'])) {
												
                                                $pre_absence_time_x = $pdf->getX();
                                                $pdf->SetFont('', '', 8);
                                            $absence_policy_total_rows = count($data['categorized_time']['absence_policy']);
                                                        foreach ($data['categorized_time']['absence_policy'] as $policy_id => $value) {
                                                    $pdf->Cell($column_widths['absence_time'], $line_h / $total_absence_rows, $ap_columns['absence_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
                                                    $pdf->setXY($pre_absence_time_x, $pdf->getY() + ($line_h / $total_absence_rows));
                                                }
                                                $pdf->setY($pdf->getY() - ($line_h / $total_absence_rows));
                                                $pdf->SetFont('', '', 9);
                                            } else {
												
											$absence_time = is_numeric($data['absence_time']) ? $data['absence_time'] : 0;
                                                $pdf->Cell($column_widths['absence_time'], $line_h, TTDate::getTimeUnit($absence_time), 1, 0, 'C', 1);
                                            }

                                            $pdf->Ln();
                                            unset($day_punch_data);
                                        }

                                     			$totals['worked_time'] += is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                                $totals['paid_time'] += is_numeric($data['paid_time']) ? $data['paid_time'] : 0;
                                                $totals['absence_time'] += is_numeric($data['absence_time']) ? $data['absence_time'] : 0;
                                                $totals['regular_time'] += is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                                $totals['over_time'] += is_numeric($data['over_time']) ? $data['over_time'] : 0;

                                                $week_totals['worked_time'] += is_numeric($data['worked_time']) ? $data['worked_time'] : 0;
                                                $week_totals['paid_time'] += is_numeric($data['paid_time']) ? $data['paid_time'] : 0;
                                                $week_totals['absence_time'] += is_numeric($data['absence_time']) ? $data['absence_time'] : 0;
                                                $week_totals['regular_time'] += is_numeric($data['regular_time']) ? $data['regular_time'] : 0;
                                                $week_totals['over_time'] += is_numeric($data['over_time']) ? $data['over_time'] : 0;
                                        
												if ($x % 7 == 0 || $i == $max_i) {
                                            $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['in_punch_time_stamp'] + $column_widths['out_punch_time_stamp'];
                                            $pdf->SetFont('', 'B', 9);
                                            $pdf->Cell($total_cell_width, 6, __('Week Total:') . ' ', 0, 0, 'R', 0);
                                            $pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($week_totals['worked_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($week_totals['paid_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($week_totals['regular_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($week_totals['over_time']), 0, 0, 'C', 0);
                                            $pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($week_totals['absence_time']), 0, 0, 'C', 0);
                                            $pdf->Ln(1);

                                            $week_totals = Misc::preSetArrayValues(null, ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);
                                            $x = 0;
                                            $y++;

                                            if ($y == 4 && $i !== $max_i) {
                                                $pdf->AddPage();
                                            }
                                        }

                                        $i++;
                                        $x++;
                                    }

                                    if (isset($totals) && is_array($totals)) {
                                        $pdf->Ln(4);
                                        $total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths['in_punch_time_stamp'];
                                        $pdf->SetFont('', 'B', 9);
                                        $pdf->Cell($total_cell_width, 6, '', 0, 0, 'R', 0);
                                        $pdf->Cell($column_widths['out_punch_time_stamp'], 6, __('Overall Total:') . ' ', 'T', 0, 'R', 0);
                                        $pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($totals['worked_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($totals['paid_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($totals['regular_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($totals['over_time']), 'T', 0, 'C', 0);
                                        $pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($totals['absence_time']), 'T', 0, 'C', 0);
                                        $pdf->Ln();
                                    }

                                    $pdf->SetFont('', '', 10);
                                    $pdf->setFillColor(255, 255, 255);
                                    $pdf->Ln();

                                    $pdf->MultiCell(200, 5, __('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . __('worked during the designated period.'), $border, 'L');
                                    $pdf->Ln(5);

                                    $pdf->Cell(40, 5, __('Employee Signature:'), $border, 0, 'L');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Cell(40, 5, __('Supervisor Signature:'), $border, 0, 'R');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(40, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, $user_data['first_name'] . ' ' . $user_data['last_name'], $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(140, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
                                    $pdf->Ln();
                                    $pdf->Cell(140, 5, '', $border, 0, 'R');
                                    $pdf->Cell(60, 5, __('(print name)'), $border, 0, 'C');

                                    if ($user_data['verified_time_sheet_date'] != false) {
                                        $pdf->Ln();
                                        $pdf->SetFont('', 'B', 10);
                                        $pdf->Cell(200, 5, __('TimeSheet electronically signed by') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . __('on') . ' ' . TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date']), $border, 0, 'C');
                                        $pdf->SetFont('', '', 10);
                                    }

                                    $pdf->SetFont('', 'I', 8);
                                    $pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y));
                                    $pdf->Cell(200, 5, __('Generated:') . ' ' . TTDate::getDate('DATE+TIME', $pdf_created_date), $border, 0, 'C');
                                }
                            }

                            $output = $pdf->Output('', 'S');
                            if ($output !== false && Debug::getVerbosity() < 11) {
                                return response()->streamDownload(function () use ($output) {
                                    echo $output;
                                }, 'detailed_timesheet.pdf', ['Content-Type' => 'application/pdf']);
                            } else {
                                echo __('ERROR: Employee TimeSheet(s) not available!') . "<br>\n";
                                exit;
                            }
                        }
                    }else {
					$viewData['generated_time']= TTDate::getTime() ;
					$viewData['pay_period_options']= $pay_period_options ;
					$viewData['filter_data']= $filter_data ;
					$viewData['columns']=  $filter_columns ;
					$viewData['rows']=  $rows;


				return view('report/TimesheetDetailReport', $viewData);

					} 
				}
				 break;

            default:
                if ($action == 'load') {
                    Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__, 10);
					$ugdf = new UserGenericDataFactory();
                    $ugdf->getReportFormData($generic_data['id']);
                } elseif ($action == '') {
                    $ugdlf->getByUserIdAndScriptAndDefault($current_user->getId(), $_SERVER['SCRIPT_NAME']);
                    if ($ugdlf->getRecordCount() > 0) {
                        Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__, 10);
                        $ugd_obj = $ugdlf->getCurrent();
                        $filter_data = $ugd_obj->getData();
                        $generic_data['id'] = $ugd_obj->getId();
                    } else {
                        Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__, 10);
                        $filter_data['user_status_ids'] = [-1];
						$filter_data['user_id'] = [-1];
                        $filter_data['branch_ids'] = [-1];
                        $filter_data['department_ids'] = [-1];
                        $filter_data['user_title_ids'] = [-1];
                        $filter_data['pay_period_ids'] = ['-0000-' . @array_shift(array_keys((array)$pay_period_options))];
                        $filter_data['start_date'] = $default_start_date;
                        $filter_data['end_date'] = $default_end_date;
                        $filter_data['group_ids'] = [-1];
                        $filter_data['column_ids'] = [
                            '-1000-date_stamp',
                            '-1090-worked_time',
                            '-1130-paid_time',
                            '-1140-regular_time'
                        ];
                        $filter_data['primary_sort'] = '-1000-date_stamp';
                        $filter_data['secondary_sort'] = '-1090-worked_time';
                    }
                }

                $filter_data = Misc::preSetArrayValues($filter_data, [
                    'include_user_ids',
                    'exclude_user_ids',
                    'user_status_ids',
                    'group_ids',
                    'branch_ids',
                    'department_ids',
                    'punch_branch_ids',
                    'punch_department_ids',
                    'user_title_ids',
                    'pay_period_ids',
                    'column_ids'
                ], null);

                $ulf = new UserListFactory();
                $all_array_option = ['-1' => __('-- All --')];

				// Get include employee list
				$ulf = new UserListFactory();
				$ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), ['permission_children_ids' => $permission_children_ids]);
				$user_options = $ulf->getArrayByListFactory($ulf, false, true);
				$filter_data['src_include_user_options'] = Misc::arrayDiffByKey((array)$filter_data['include_user_ids'], $user_options);
				$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['include_user_ids'], $user_options);

				// Get exclude employee list
				$exclude_user_options = Misc::prependArray($all_array_option, $ulf->getArrayByListFactory($ulf, false, true));
				$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey((array)$filter_data['exclude_user_ids'], $user_options);
				$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['exclude_user_ids'], $user_options);

				// Get employee status list
				$user_status_options = Misc::prependArray($all_array_option, $ulf->getOptions('status'));
				$filter_data['src_user_status_options'] = Misc::arrayDiffByKey((array)$filter_data['user_status_ids'], $user_status_options);
				$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_status_ids'], $user_status_options);

				// Get employee groups
				$uglf = new UserGroupListFactory();
				$group_options = Misc::prependArray($all_array_option, $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', true)));
				$filter_data['src_group_options'] = Misc::arrayDiffByKey((array)$filter_data['group_ids'], $group_options);
				$filter_data['selected_group_options'] = Misc::arrayIntersectByKey((array)$filter_data['group_ids'], $group_options);

				// Get branches
				$blf = new BranchListFactory();
				$blf->getByCompanyId($current_company->getId());
				$branch_options = Misc::prependArray($all_array_option, $blf->getArrayByListFactory($blf, false, true));
				$filter_data['src_branch_options'] = Misc::arrayDiffByKey((array)$filter_data['branch_ids'], $branch_options);
				$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey((array)$filter_data['branch_ids'], $branch_options);
				$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey((array)$filter_data['punch_branch_ids'], $branch_options);
				$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey((array)$filter_data['punch_branch_ids'], $branch_options);

				// Get departments
				$dlf = new DepartmentListFactory();
				$dlf->getByCompanyId($current_company->getId());
				$department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, false, true));
				$filter_data['src_department_options'] = Misc::arrayDiffByKey((array)$filter_data['department_ids'], $department_options);
				$filter_data['selected_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['department_ids'], $department_options);
				$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey((array)$filter_data['punch_department_ids'], $department_options);
				$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['punch_department_ids'], $department_options);

				// Get employee titles
				$utlf = new UserTitleListFactory();
				$utlf->getByCompanyId($current_company->getId());
				$user_title_options = Misc::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, false, true));
				$filter_data['src_user_title_options'] = Misc::arrayDiffByKey((array)$filter_data['user_title_ids'], $user_title_options);
				$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_title_ids'], $user_title_options);

				// Get pay periods
				$pplf = new PayPeriodListFactory();
				$pplf->getByCompanyId($current_company->getId());
				$pay_period_options = Misc::prependArray($all_array_option, $pplf->getArrayByListFactory($pplf, false, true));
				$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey((array)$filter_data['pay_period_ids'], $pay_period_options);
				$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey((array)$filter_data['pay_period_ids'], $pay_period_options);

				// Get column list
				$filter_data['src_column_options'] = Misc::arrayDiffByKey((array)$filter_data['column_ids'], $columns);
				$filter_data['selected_column_options'] = Misc::arrayIntersectByKey((array)$filter_data['column_ids'], $columns);

				// Get primary/secondary order list
				$filter_data['sort_options'] = $columns;
				$filter_data['sort_options']['effective_date_order'] = __('Wage Effective Date');
				unset($filter_data['sort_options']['effective_date']);
				$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

				// Export type options
				$filter_data['export_type_options'] = Misc::prependArray([
					'mothlyTimesheet' => __('Employee Time Sheet (PDF)'),
					'csv_format' => __('CSV (Excel)')
				]);

				// Hidden elements for UI
				$hidden_elements = Misc::prependArray([
					'displayReport' => 'hidden',
					'displayTimeSheet' => 'hidden',
					'displayDetailedTimeSheet' => 'hidden',
					'export' => ''
				]);
				// Get saved reports
				$ugdlf = new UserGenericDataListFactory();
				$saved_report_options = $ugdlf->getByUserIdAndScriptArray($current_user->getId(), request()->getPathInfo());
				$generic_data['saved_report_options'] = $saved_report_options;

				// Assign data to view
				$viewData['generic_data'] = $generic_data;
				$viewData['filter_data'] = $filter_data;
				$viewData['ugdf'] = new UserGenericDataFactory();
				$viewData['hidden_elements'] = $hidden_elements;

				return view('report/TimesheetDetail', $viewData);

				break;
		}
    }
}
}
