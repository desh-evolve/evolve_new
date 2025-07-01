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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class EmployeeNopayCountReport extends Controller
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

        $viewData['title'] = 'Employee Nopay Count Report';
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;
        $current_user_prefs = $this->userPrefs;

        /*
         * Get FORM variables
         */
        extract(FormVariables::GetVariables(
            [
                'action',
                'generic_data',
                'filter_data'
            ]
        ));
        // if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
        //     if (!Gate::allows('punch-enabled') ||
        //         !(Gate::allows('punch-view') || Gate::allows('punch-view-own') || Gate::allows('punch-view-child'))) {
        //         return redirect()->route('home')->with('error', 'Unauthorized');
        //     }
        // } else {
        //     if (!Gate::allows('report-enabled') || !Gate::allows('report-view-timesheet-summary')) {
        //         return redirect()->route('home')->with('error', 'Unauthorized');
        //     }
        // }

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], ['filter_data' => $filter_data]);
        $static_columns = [
            '-1000-date_stamp' => _('Date'),
            '-1050-min_punch_time_stamp' => 'First In Punch',
            '-1060-max_punch_time_stamp' => 'Last Out Punch',
        ];

        $columns = [
            '-1070-schedule_working' => _('Scheduled Time'),
            '-1080-schedule_absence' => _('Scheduled Absence'),
            '-1090-worked_time' => _('Worked Time'),
            '-1100-actual_time' => _('Actual Time'),
            '-1110-actual_time_diff' => _('Actual Time Difference'),
            '-1120-actual_time_diff_wage' => _('Actual Time Difference Wage'),
            '-1130-paid_time' => _('Paid Time'),
            '-1140-regular_time' => _('Regular Time'),
            '-1150-over_time' => _('Total Over Time'),
            '-1160-absence_time' => _('Total Absence Time'),
        ];

        $columns = Misc::prependArray($static_columns, $columns);

        // Get Overtime policies
        $otplf = new OverTimePolicyListFactory();
        $otplf->getByCompanyId($current_company->getId());


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
            foreach ($pplf->rs as $pp_obj) {
                $pplf->data = (array)$pp_obj;
                $pp_obj = $pplf;
                $pp_columns['premium_policy-' . $pp_obj->getId()] = $pp_obj->getName();
            }
            $columns = array_merge($columns, $pp_columns);
        }

        // Get Absence policies
        $aplf = new AbsencePolicyListFactory();
        $aplf->getByCompanyId($current_company->getId());
        if ($aplf->getRecordCount() > 0) {
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
            $pplf = new PayPeriodListFactory();
            $pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, ['start_date' => 'desc'], FALSE);
        }

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

        // Get Permission Hierarchy Children
        $permission_children_ids = [];
        $wage_permission_children_ids = [];
        if (!Gate::allows('punch-view')) {
            $hlf = new HierarchyListFactory();
            $permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId());
            Debug::Arr($permission_children_ids, 'Permission Children Ids:', __FILE__, __LINE__, __METHOD__, 10);

            if (!Gate::allows('punch-view-child')) {
                $permission_children_ids = [];
            }
            if (Gate::allows('punch-view-own')) {
                $permission_children_ids[] = $current_user->getId();
            }
            $filter_data['permission_children_ids'] = $permission_children_ids;
        }

        // Get Wage Permission Hierarchy Children
        if (!Gate::allows('wage-view')) {
            if (!Gate::allows('wage-view-child')) {
                $wage_permission_children_ids = [];
            }
            if (Gate::allows('wage-view-own')) {
                $wage_permission_children_ids[] = $current_user->getId();
            }
            $wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
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
                $filter_columns = [];
                $rows = [];
                if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
                    if (!isset($filter_data['user_id']) || !(Gate::allows('punch-view') || Gate::allows('punch-view-child'))) {
                        $filter_data['user_id'] = $current_user->getId();
                    }
                    $action = 'display_timesheet';
                    if ($filter_data['print_timesheet'] == 2) {
                        $action = 'display_detailed_timesheet';
                    }

                    $filter_data = [
                        'permission_children_ids' => [(int)$filter_data['user_id']],
                        'pay_period_ids' => [(int)$filter_data['pay_period_ids']],
                        'date_type' => 'pay_period_ids',
                        'primary_sort' => '-1000-date_stamp',
                        'secondary_sort' => NULL,
                        'primary_sort_dir' => 1,
                        'secondary_sort_dir' => NULL,
                        'column_ids' => array_keys($static_columns)
                    ];
                }
                $ulf = new UserListFactory();
                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                if ($ulf->getRecordCount() > 0) {
                    if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                        unset($filter_data['start_date'], $filter_data['end_date']);
                    } else {
                        unset($filter_data['pay_period_ids']);
                    }

                    // foreach ($ulf->rs as $u_obj) {
                    //      dd($u_obj);
                    //     $ulf->data = (array)$u_obj;
                    //     $u_obj = $ulf;
                    //     dd($u_obj->getId());
                    //     $filter_data['user_id'][] = $u_obj->getId();
                    // }
                    foreach ($ulf->rs as $u_obj) {
                        $filter_data['user_id'][] = $u_obj->id;
                    }

                    if (isset($filter_data['pay_period_ids'])) {
                        $tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
                        $filter_data['pay_period_ids'] = [];
                        foreach ($tmp_filter_pay_period_ids as $filter_pay_period_id) {
                            $filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
                        }
                    }

                    $end_date = (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) ?
                        (in_array('-1', $filter_data['pay_period_ids']) ? time() : max(array_map(function ($id) use ($pay_period_end_dates) {
                            return $pay_period_end_dates[Misc::trimSortPrefix($id)] ?? time();
                        }, $filter_data['pay_period_ids']))) : ($filter_data['end_date'] ?? time());

                    if ($permission->Check('wage', 'view') == TRUE) {
                        $wage_filter_data['permission_children_ids'] = $filter_data['user_id'];
                    }
                    $uwlf = new UserWageListFactory();
                    $uwlf->getLastWageByUserIdAndDate($wage_filter_data['permission_children_ids'], $end_date);

                    if ($uwlf->getRecordCount() > 0) {
                        foreach ($uwlf->rs as $uw_obj) {
                            $uwlf->data = (array)$uw_obj;
                            $uw_obj = $uwlf;
                            $user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate($uw_obj->getHourlyRate());
                        }
                    }
                    $udtlf = new UserDateTotalListFactory();
                    if (isset($filter_data['user_id'])) {
                        $udtlf->getDayReportByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                    }

                    $slf = new ScheduleListFactory();

                    if (isset($filter_data['user_id'])) {

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
                            $column = NULL;
                        }

                        if ($column == 'worked_time') {
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] ?? 0) + (int)$udt_obj->getColumn('total_time');
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] ?? 0) + $udt_obj->getColumn('actual_total_time');

                            $actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time'));
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] ?? 0) + $actual_time_diff;

                            $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = isset($user_wage[$user_id]) ?
                                Misc::MoneyFormat(bcmul(TTDate::getHours($actual_time_diff), $user_wage[$user_id]), FALSE) :
                                Misc::MoneyFormat(0, FALSE);
                        } elseif ($column != NULL && $udt_obj->getColumn('total_time') > 0) {
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

                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'] ?? NULL;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'] ?? NULL;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] ?? NULL;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] ?? NULL;
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('min_punch_time_stamp'));
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('max_punch_time_stamp'));
                    }
                    if ($action == 'display_detailed_timesheet') {
                        $plf = new PunchListFactory();
                        $plf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                        if ($plf->getRecordCount() > 0) {
                            foreach ($plf->rs as $p_obj) {
                                $plf->data = (array)$p_obj;
                                $p_obj = $plf;
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
                    $group_options = $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'no_tree_text', TRUE));

                    $verified_time_sheets = NULL;
                    if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
                        $pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
                        $pptsvlf->getByPayPeriodIdAndCompanyId($filter_data['pay_period_ids'][0], $current_company->getId());
                        if ($pptsvlf->getRecordCount() > 0) {
                            foreach ($pptsvlf->rs as $pptsv_obj) {
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
                                $rows[$i]['full_name'] = $user_obj->getFullName(TRUE);
                                $rows[$i]['employee_number'] = $user_obj->getEmployeeNumber();
                                $rows[$i]['province'] = $user_obj->getProvince();
                                $rows[$i]['country'] = $user_obj->getCountry();
                                $rows[$i]['group'] = Option::getByKey($user_obj->getGroup(), $group_options, NULL);
                                $rows[$i]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL);
                                $rows[$i]['default_branch'] = Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL);
                                $rows[$i]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL);

                                $rows[$i]['verified_time_sheet_date'] = FALSE;
                                if ($verified_time_sheets !== NULL && isset($verified_time_sheets[$user_id][$pay_period_id])) {
                                    if ($verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 50) {
                                        $rows[$i]['verified_time_sheet'] = _('Yes');
                                        $rows[$i]['verified_time_sheet_date'] = $verified_time_sheets[$user_id][$pay_period_id]['created_date'];
                                    } elseif (in_array($verified_time_sheets[$user_id][$pay_period_id]['status_id'], [30, 45])) {
                                        $rows[$i]['verified_time_sheet'] = _('Pending');
                                    } else {
                                        $rows[$i]['verified_time_sheet'] = _('Declined');
                                    }
                                } else {
                                    $rows[$i]['verified_time_sheet'] = _('No');
                                }

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
                                        $total_sub_row = Misc::ArrayAssocSum($tmp_sub_rows, NULL, 2);
                                        $tmp_sub_rows[] = array_merge($total_sub_row, array_fill_keys(array_keys(Misc::trimSortPrefix($static_columns)), NULL));
                                    }

                                    $trimmed_static_columns = array_keys(Misc::trimSortPrefix($static_columns));

                                    foreach ($tmp_sub_rows as $sub_row) {
                                        // foreach ($sub_row as $column => $column_data) {
                                        //     if (!in_array($action, ['display_timesheet', 'display_detailed_timesheet'])) {
                                        //         if ($column == 'date_stamp') {
                                        //             $column_data = TTDate::getDate('DATE', $column_data);
                                        //         } elseif (!strstr($column, 'wage') && !in_array($column, $trimmed_static_columns)) {
                                        //             // dd($column_data);
                                        //             $column_data = TTDate::getTimeUnit($column_data);
                                        //         }
                                        //     }
                                        //     $sub_row_columns[$column] = $column_data;
                                        // }
                                        foreach ($sub_row as $column => $column_data) {
                                            if (!in_array($action, ['display_timesheet', 'display_detailed_timesheet'])) {
                                                if ($column == 'date_stamp') {
                                                    $column_data = TTDate::getDate('DATE', $column_data);
                                                } elseif (
                                                    !strstr($column, 'wage') &&
                                                    !in_array($column, $trimmed_static_columns) &&
                                                    in_array($column, ['absence_time', 'regular_time', 'paid_time', 'worked_time', 'actual_time', 'absence_policy-4'])
                                                ) {
                                                    if (is_numeric($column_data) && !is_null($column_data)) {
                                                        $column_data = TTDate::getTimeUnit($column_data);
                                                    }
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
                                unset($sub_rows, $tmp_sub_rows);
                                $i++;
                            }
                        }
                    }
                    if ($action == 'export') {
                        if (isset($rows) && isset($filter_columns)) {
                            if ($filter_data['export_type'] == 'csv') {
                                $export_filter_columns = [
                                    'first_name' => _('First Name'),
                                    'last_name' => _('Last Name'),
                                    'full_name' => _('Full Name'),
                                    'employee_number' => _('Employee #'),
                                    'province' => _('Province/State'),
                                    'country' => _('Country'),
                                    'group' => _('Group'),
                                    'title' => _('Title'),
                                    'default_branch' => _('Default Branch'),
                                    'default_department' => _('Default Department'),
                                    'pay_period' => _('Pay Period'),
                                ];

                                $filter_columns = Misc::prependArray($export_filter_columns, $filter_columns);

                                foreach ($rows as $row) {
                                    if (is_array($row['data'])) {
                                        foreach ($row['data'] as $sub_row) {
                                            unset($row['data']);
                                            $tmp_rows[] = array_merge($row, $sub_row);
                                        }
                                    }
                                }

                                Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__, 10);
                                $data = Misc::Array2CSV($tmp_rows, $filter_columns);
                                return response($data, 200, [
                                    'Content-Type' => 'application/csv',
                                    'Content-Disposition' => 'attachment; filename="report.csv"',
                                    'Content-Length' => strlen($data)
                                ]);
                            }

                            if ($filter_data['export_type'] == 'pdfOTDetails') {
                                Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__, 10);
                                $tssr = new TimesheetDetailReport();
                                $output = $tssr->OTDetailReport($rows, $filter_columns, $filter_data, $current_user, $current_company);
                                return response()->streamDownload(function () use ($output) {
                                    echo $output;
                                }, 'OverTimeReport.pdf', ['Content-Type' => 'application/pdf']);
                            }

                            if ($filter_data['export_type'] == 'mothlyTimesheet') {
                                Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__, 10);
                                $tssr = new TimesheetDetailReport();
                                $output = $tssr->EmployeeTimeSheet($rows, $filter_columns, $filter_data, $current_user, $current_company);
                                return response()->streamDownload(function () use ($output) {
                                    echo $output;
                                }, 'EmployeeTimeSheetReport.pdf', ['Content-Type' => 'application/pdf']);
                            }

                            if ($filter_data['export_type'] == 'csv_format') {
                                Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__, 10);
                                $data = Misc::Array2CSVReport($rows);
                                return response($data, 200, [
                                    'Content-Type' => 'application/csv',
                                    'Content-Disposition' => 'attachment; filename="report.csv"',
                                    'Content-Length' => strlen($data)
                                ]);
                            }

                            if ($filter_data['export_type'] == 'EmployeeNapayCount') {
                                Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__, 10);
                                $tsdr = new TimesheetDetailReport();
                                $output = $tsdr->MonthlyAttendanceDetailed($rows, $filter_columns, $filter_data, $current_user, $current_company);
                                return response()->streamDownload(function () use ($output) {
                                    echo $output;
                                }, 'EmployeeNopayCountFilter.pdf', ['Content-Type' => 'application/pdf']);
                            }
                        } else {
                            echo __("No Data To Export!") . "<br>\n";
                        }
                    } else {
                        $viewData['generated_time'] = TTDate::getTime();
                        $viewData['pay_period_options'] = $pay_period_options;
                        $viewData['filter_data'] = $filter_data;
                        $viewData['columns'] = $filter_columns;
                        $viewData['rows'] = $rows;
                        $viewData['hidden_elements'] = Misc::prependArray([
                            'displayReport' => 'hidden',
                            'displayTimeSheet' => 'hidden',
                            'displayDetailedTimeSheet' => 'hidden',
                            'export' => ''
                        ]);
                        return view('report/EmployeeNopayReport', $viewData);
                    }
                }

                break;

            case 'delete':
            case 'save':
                Debug::Text('Action: ' . $action, __FILE__, __LINE__, __METHOD__, 10);
                $generic_data['id'] = UserGenericDataFactory::reportFormDataHandler($action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']));
                unset($generic_data['name']);
                // Fall through to default case

            default:
                if ($action == 'load') {
                    Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__, 10);
                    extract(UserGenericDataFactory::getReportFormData($generic_data['id']));
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
                ], NULL);

                $ulf = new UserListFactory();
                $all_array_option = ['-1' => _('-- All --')];

                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), ['permission_children_ids' => $permission_children_ids]);
                $user_options = $ulf->getArrayByListFactory($ulf, FALSE, TRUE);
                $filter_data['src_include_user_options'] = Misc::arrayDiffByKey((array)$filter_data['include_user_ids'], $user_options);
                $filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['include_user_ids'], $user_options);

                $exclude_user_options = Misc::prependArray($all_array_option, $ulf->getArrayByListFactory($ulf, FALSE, TRUE));
                $filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey((array)$filter_data['exclude_user_ids'], $user_options);
                $filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['exclude_user_ids'], $user_options);

                $user_status_options = Misc::prependArray($all_array_option, $ulf->getOptions('status'));
                $filter_data['src_user_status_options'] = Misc::arrayDiffByKey((array)$filter_data['user_status_ids'], $user_status_options);
                $filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_status_ids'], $user_status_options);

                $uglf = new UserGroupListFactory();
                $group_options = Misc::prependArray($all_array_option, $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', TRUE)));
                $filter_data['src_group_options'] = Misc::arrayDiffByKey((array)$filter_data['group_ids'], $group_options);
                $filter_data['selected_group_options'] = Misc::arrayIntersectByKey((array)$filter_data['group_ids'], $group_options);

                $blf = new BranchListFactory();
                $blf->getByCompanyId($current_company->getId());
                $branch_options = Misc::prependArray($all_array_option, $blf->getArrayByListFactory($blf, FALSE, TRUE));
                $filter_data['src_branch_options'] = Misc::arrayDiffByKey((array)$filter_data['branch_ids'], $branch_options);
                $filter_data['selected_branch_options'] = Misc::arrayIntersectByKey((array)$filter_data['branch_ids'], $branch_options);

                $filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey((array)$filter_data['punch_branch_ids'], $branch_options);
                $filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey((array)$filter_data['punch_branch_ids'], $branch_options);

                $dlf = new DepartmentListFactory();
                $dlf->getByCompanyId($current_company->getId());
                $department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, FALSE, TRUE));
                $filter_data['src_department_options'] = Misc::arrayDiffByKey((array)$filter_data['department_ids'], $department_options);
                $filter_data['selected_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['department_ids'], $department_options);

                $filter_data['src_punch_department_options'] = Misc::arrayDiffByKey((array)$filter_data['punch_department_ids'], $department_options);
                $filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['punch_department_ids'], $department_options);

                $utlf = new UserTitleListFactory();
                $utlf->getByCompanyId($current_company->getId());
                $user_title_options = Misc::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, FALSE, TRUE));
                $filter_data['src_user_title_options'] = Misc::arrayDiffByKey((array)$filter_data['user_title_ids'], $user_title_options);
                $filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_title_ids'], $user_title_options);

                $pplf = new PayPeriodListFactory();
                $pplf->getByCompanyId($current_company->getId());
                $pay_period_options = Misc::prependArray($all_array_option, $pplf->getArrayByListFactory($pplf, FALSE, TRUE));
                $filter_data['src_pay_period_options'] = Misc::arrayDiffByKey((array)$filter_data['pay_period_ids'], $pay_period_options);
                $filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey((array)$filter_data['pay_period_ids'], $pay_period_options);

                $filter_data['src_column_options'] = Misc::arrayDiffByKey((array)$filter_data['column_ids'], $columns);
                $filter_data['selected_column_options'] = Misc::arrayIntersectByKey((array)$filter_data['column_ids'], $columns);

                $filter_data['sort_options'] = $columns;
                $filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
                unset($filter_data['sort_options']['effective_date']);
                $filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

                $filter_data['export_type_options'] = Misc::prependArray([
                    'EmployeeNapayCount' => _('Employee Nopay Count (PDF)'),
                    'csv_format' => _('CSV (Excel)')
                ]);

                $hidden_elements = Misc::prependArray([
                    'displayReport' => 'hidden',
                    'displayTimeSheet' => 'hidden',
                    'displayDetailedTimeSheet' => 'hidden',
                    'export' => ''
                ]);

                // $saved_report_options = $ugdlf->getByUserIdAndScriptArray($current_user->getId(), $_SERVER['SCRIPT_NAME']);

                $viewData['generic_data'] = $generic_data ?? ['saved_report_options' => $ugdlf->getByUserIdAndScriptArray($current_user->getId(), $_SERVER['SCRIPT_NAME'])];
                $viewData['filter_data'] = $filter_data;
                $viewData['ugdf'] = $ugdf;
                $viewData['current_user_prefs'] = $current_user_prefs;
                $viewData['hidden_elements'] = $hidden_elements;
                // dd($viewData);
                return view('report/EmployeeNopayCountFilter', $viewData);
                break;
        }
    }
}
