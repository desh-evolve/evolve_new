<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: TimesheetDetail.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2018-09-19 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */

use App\Models\User;
use App\Models\PayPeriod;
use App\Models\Schedule;
use App\Models\UserDateTotal;
use App\Models\UserWage;
use App\Models\OverTimePolicy;
use App\Models\PremiumPolicy;
use App\Models\AbsencePolicy;
use App\Models\Punch;
use App\Models\PayPeriodTimeSheetVerify;
use App\Models\Branch;
use App\Models\Department;
use App\Models\UserTitle;
use App\Models\UserGroup;
use App\Models\Hierarchy;
use App\Services\TimesheetDetailReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Laravel equivalents for global includes
require_once(base_path('app/Helpers/MiscHelper.php')); // Custom helper for Misc functions
require_once(base_path('app/Helpers/TTDateHelper.php')); // Custom helper for TTDate functions

$request = request(); // Laravel Request object
$current_user = Auth::user();
$current_company = $current_user->company; // Assuming User model has a company relationship

$smarty = new stdClass(); // Mock Smarty for assign_by_ref compatibility
$smarty->assign_by_ref = function($key, &$value) use ($smarty) {
    $smarty->$key = &$value;
};
$smarty->display = function($template) {
    return view(str_replace('.tpl', '', $template)); // Convert .tpl to Blade view
};

$smarty->assign('title', __($title = 'Nopay Count Report')); // See index.php

/*
 * Get FORM variables
 */
extract([
    'action' => $request->input('action'),
    'generic_data' => $request->input('generic_data', []),
    'filter_data' => $request->input('filter_data', [])
]);

if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
    if (!Gate::allows('punch-enabled') ||
        !(Gate::allows('punch-view') || Gate::allows('punch-view-own') || Gate::allows('punch-view-child'))) {
        return redirect()->route('home')->with('error', 'Unauthorized'); // Redirect
    }
} else {
    if (!Gate::allows('report-enabled') || !Gate::allows('report-view-timesheet-summary')) {
        return redirect()->route('home')->with('error', 'Unauthorized'); // Redirect
    }
}

// URLBuilder equivalent using Laravel's route helper
$filter_data_url = route('reports.employee-nopay-count', ['filter_data' => $filter_data]);

$static_columns = array(
    '-1000-date_stamp' => _('Date'),
    '-1050-min_punch_time_stamp' => 'First In Punch',
    '-1060-max_punch_time_stamp' => 'Last Out Punch',
);

$columns = array(
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
);

$columns = Misc::prependArray($static_columns, $columns);

// Get all Overtime policies.
$otplf = new OverTimePolicyListFactory();
$otplf->getByCompanyId($current_company->getId());
if ($otplf->getRecordCount() > 0) {
    foreach ($otplf as $otp_obj) {
        $otp_columns['over_time_policy-' . $otp_obj->getId()] = $otp_obj->getName();
    }
    $columns = array_merge($columns, $otp_columns);
}

// Get all Premium policies.
$pplf = new PremiumPolicyListFactory();
$pplf->getByCompanyId($current_company->getId());
if ($pplf->getRecordCount() > 0) {
    foreach ($pplf as $pp_obj) {
        $pp_columns['premium_policy-' . $pp_obj->getId()] = $pp_obj->getName();
    }
    $columns = array_merge($columns, $pp_columns);
}

// Get all Absence Policies.
$aplf = new AbsencePolicyListFactory();
$aplf->getByCompanyId($current_company->getId());
if ($aplf->getRecordCount() > 0) {
    foreach ($aplf as $ap_obj) {
        $ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
    }
    $columns = array_merge($columns, $ap_columns);
}

// Get all pay periods
$pplf = new PayPeriodListFactory();
$pplf->getByCompanyId($current_company->getId());
if ($pplf->getRecordCount() > 0) {
    $pp = 0;
    foreach ($pplf as $pay_period_obj) {
        $pay_period_ids[] = $pay_period_obj->getId();
        $pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();
        if ($pp == 0) {
            $default_start_date = $pay_period_obj->getStartDate();
            $default_end_date = $pay_period_obj->getEndDate();
        }
        $pp++;
    }
    $pplf = new PayPeriodListFactory();
    $pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'), FALSE);
}

if (isset($filter_data['start_date'])) {
    $filter_data['start_date'] = TTDateHelper::parseDateTime($filter_data['start_date']);
}

if (isset($filter_data['end_date'])) {
    $filter_data['end_date'] = TTDateHelper::parseDateTime($filter_data['end_date']);
}

$filter_data = MiscHelper::preSetArrayValues($filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids'), array());

// Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
$wage_permission_children_ids = array();
if (Gate::allows('punch-view') == FALSE) {
    $hlf = new HierarchyListFactory();
    $permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID($current_company->id, $current_user->id);
    Log::debug('Permission Children Ids: ' . json_encode($permission_children_ids));
    if (Gate::allows('punch-view-child') == FALSE) {
        $permission_children_ids = array();
    }
    if (Gate::allows('punch-view-own')) {
        $permission_children_ids[] = $current_user->id;
    }
    $filter_data['permission_children_ids'] = $permission_children_ids;
}

// Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
if (Gate::allows('wage-view') == FALSE) {
    if (Gate::allows('wage-view-child') == FALSE) {
        $wage_permission_children_ids = array();
    }
    if (Gate::allows('wage-view-own')) {
        $wage_permission_children_ids[] = $current_user->id;
    }
    $wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = MiscHelper::findSubmitButton();

switch ($action) {
    case 'export':
    case 'display_report':
    case 'display_timesheet':
    case 'display_detailed_timesheet':
        Log::debug('Submit!');
        // Determine if this is a regular employee trying to print their own timesheet.
        if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
            if (!isset($filter_data['user_id']) || !(Gate::allows('punch-view') || Gate::allows('punch-view-child'))) {
                $filter_data['user_id'] = $current_user->id;
            }
            $action = 'display_timesheet';
            if ($filter_data['print_timesheet'] == 2) {
                $action = 'display_detailed_timesheet';
            }
            $filter_data = array(
                'permission_children_ids' => [(int)$filter_data['user_id']],
                'pay_period_ids' => [(int)$filter_data['pay_period_ids']],
                'date_type' => 'pay_period_ids',
                'primary_sort' => '-1000-date_stamp',
                'secondary_sort' => NULL,
                'primary_sort_dir' => 1,
                'secondary_sort_dir' => NULL,
                'column_ids' => array_keys($static_columns)
            );
        }

        $ulf = new UserListFactory();
        $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->id, $filter_data);
        if ($ulf->getRecordCount() > 0) {
            if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                unset($filter_data['start_date']);
                unset($filter_data['end_date']);
            } else {
                unset($filter_data['pay_period_ids']);
            }

            foreach ($ulf as $u_obj) {
                $filter_data['user_id'][] = $u_obj->getId();
            }

            if (isset($filter_data['pay_period_ids'])) {
                $tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
                $filter_data['pay_period_ids'] = array();
                foreach ($tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
                    $filter_data['pay_period_ids'][] = MiscHelper::trimSortPrefix($filter_pay_period_id);
                }
                unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
            }

            // Get greatest end date of the selected ones.
            if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
                if (in_array('-1', $filter_data['pay_period_ids'])) {
                    $end_date = time();
                } else {
                    $i = 0;
                    foreach ($filter_data['pay_period_ids'] as $tmp_pay_period_id) {
                        $tmp_pay_period_id = MiscHelper::trimSortPrefix($tmp_pay_period_id);
                        if ($i == 0 && isset($pay_period_end_dates[$tmp_pay_period_id])) {
                            $end_date = $pay_period_end_dates[$tmp_pay_period_id];
                        } elseif (isset($pay_period_end_dates[$tmp_pay_period_id]) && $pay_period_end_dates[$tmp_pay_period_id] > $end_date) {
                            $end_date = $pay_period_end_dates[$tmp_pay_period_id];
                        } else {
                            $end_date = time();
                        }
                        $i++;
                    }
                    unset($tmp_pay_period_id, $i);
                }
            } else {
                $end_date = $filter_data['end_date'];
            }

            // Make sure we account for wage permissions.
            if (Gate::allows('wage-view') == TRUE) {
                $wage_filter_data['permission_children_ids'] = $filter_data['user_id'];
            }
            $uwlf = new UserWageListFactory();
            $uwlf->getLastWageByUserIdAndDate($wage_filter_data['permission_children_ids'], $end_date);
            if ($uwlf->getRecordCount() > 0) {
                foreach ($uwlf as $uw_obj) {
                    $user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate($uw_obj->getHourlyRate());
                }
            }
            unset($end_date);

            $udtlf = new UserDateTotalListFactory();
            if (isset($filter_data['user_id'])) {
                $udtlf->getDayReportByCompanyIdAndArrayCriteria($current_company->id, $filter_data);
            }

            $slf = new ScheduleListFactory();
            if (isset($filter_data['user_id'])) {
                $slf->getDayReportByCompanyIdAndArrayCriteria($current_company->id, $filter_data);
            }
            if ($slf->getRecordCount() > 0) {
                foreach ($slf as $s_obj) {
                    $user_id = $s_obj->getColumn('user_id');
                    $status_id = $s_obj->getColumn('status_id');
                    $status = strtolower(Option::getByKey($status_id, $s_obj->getOptions('status')));
                    $pay_period_id = $s_obj->getColumn('pay_period_id');
                    $date_stamp = TTDateHelper::strtotime($s_obj->getColumn('date_stamp'));

                    $schedule_rows[$pay_period_id][$user_id][$date_stamp][$status] = $s_obj->getColumn('total_time');
                    $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] = $s_obj->getColumn('start_time');
                    $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] = $s_obj->getColumn('end_time');
                    unset($user_id, $status_id, $status, $pay_period_id, $date_stamp);
                }
            }

            foreach ($udtlf as $udt_obj) {
                $user_id = $udt_obj->getColumn('id');
                $pay_period_id = $udt_obj->getColumn('pay_period_id');
                $date_stamp = TTDateHelper::strtotime($udt_obj->getColumn('date_stamp'));

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
                    if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column])) {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += (int)$udt_obj->getColumn('total_time');
                    } else {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = (int)$udt_obj->getColumn('total_time');
                    }
                    if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'])) {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] += $udt_obj->getColumn('actual_total_time');
                    } else {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = $udt_obj->getColumn('actual_total_time');
                    }

                    $actual_time_diff = bcsub($udt_obj->getColumn('actual_total_time'), $udt_obj->getColumn('total_time'));
                    if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'])) {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] += $actual_time_diff;
                    } else {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = $actual_time_diff;
                    }

                    if (isset($user_wage[$user_id])) {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = MiscHelper::MoneyFormat(bcmul(TTDateHelper::getHours($actual_time_diff), $user_wage[$user_id]), FALSE);
                    } else {
                        $tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = MiscHelper::MoneyFormat(0, FALSE);
                    }
                    unset($actual_time_diff);
                } elseif ($column != NULL) {
                    if ($udt_obj->getColumn('total_time') > 0) {
                        if ($status_id == 30 && $type_id == 10) {
                            if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'])) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] += $udt_obj->getColumn('total_time');
                            } else {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] = $udt_obj->getColumn('total_time');
                            }
                        }

                        if ($status_id == 10 && $type_id == 30) {
                            if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'])) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] += $udt_obj->getColumn('total_time');
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time_policy_id'] = $policy_id;
                            } else {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] = $udt_obj->getColumn('total_time');
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time_policy_id'] = $policy_id;
                            }
                        }

                        if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column])) {
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] += $udt_obj->getColumn('total_time');
                        } else {
                            $tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = $udt_obj->getColumn('total_time');
                        }

                        if ($action == 'display_timesheet' || $action == 'display_detailed_timesheet') {
                            if (isset($tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id])) {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] += $udt_obj->getColumn('total_time');
                            } else {
                                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] = $udt_obj->getColumn('total_time');
                            }
                        }
                    }
                }

                if (isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'])) {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'];
                } else {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = NULL;
                }

                if (isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'])) {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'];
                } else {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = NULL;
                }

                if (isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'])) {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'];
                } else {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_start_time'] = NULL;
                }

                if (isset($schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'])) {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'];
                } else {
                    $tmp_rows[$pay_period_id][$user_id][$date_stamp]['shedule_end_time'] = NULL;
                }

                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDateHelper::strtotime($udt_obj->getColumn('min_punch_time_stamp'));
                $tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDateHelper::strtotime($udt_obj->getColumn('max_punch_time_stamp'));
            }

            // Get all punches
            if ($action == 'display_detailed_timesheet') {
                $plf = new PunchListFactory();
                $plf->getSearchByCompanyIdAndArrayCriteria($current_company->id, $filter_data);
                if ($plf->getRecordCount() > 0) {
                    foreach ($plf as $p_obj) {
                        $punch_rows[$p_obj->getColumn('pay_period_id')][$p_obj->getColumn('user_id')][TTDateHelper::strtotime($p_obj->getColumn('date_stamp'))][$p_obj->getPunchControlID()][$p_obj->getStatus()] = array(
                            'status_id' => $p_obj->getStatus(),
                            'type_id' => $p_obj->getType(),
                            'type_code' => $p_obj->getTypeCode(),
                            'time_stamp' => $p_obj->getTimeStamp()
                        );
                    }
                }
                unset($plf, $p_obj);
            }

            $ulf = new UserListFactory();

            $utlf = new UserTitleListFactory();
            $title_options = $utlf->getByCompanyIdArray($current_company->id);

            $blf = new BranchListFactory();
            $branch_options = $blf->getByCompanyIdArray($current_company->id);

            $dlf = new DepartmentListFactory();
            $department_options = $dlf->getByCompanyIdArray($current_company->id);

            $uglf = new UserGroupListFactory();
            $group_options = $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->id), 'no_tree_text', TRUE));

            // Get verified timesheets
            $verified_time_sheets = NULL;
            if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
                $pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
                $pptsvlf->getByPayPeriodIdAndCompanyId($filter_data['pay_period_ids'][0], $current_company->id);
                if ($pptsvlf->getRecordCount() > 0) {
                    foreach ($pptsvlf as $pptsv_obj) {
                        $verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = array(
                            'status_id' => $pptsv_obj->getStatus(),
                            'created_date' => $pptsv_obj->getCreatedDate(),
                        );
                    }
                }
            }

            if (isset($tmp_rows)) {
                $i = 0;
                foreach ($tmp_rows as $pay_period_id => $data_a) {
                    foreach ($data_a as $user_id => $data_b) {
                        $user_obj = $ulf->getById($user_id)->getCurrent();

                        if (isset($pay_period_options[$pay_period_id])) {
                            $rows[$i]['pay_period'] = $pay_period_options[$pay_period_id];
                        } else {
                            $rows[$i]['pay_period'] = 'N/A';
                        }
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
                            } elseif ($verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 30 || $verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 45) {
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
                            foreach ($sub_rows as $sub_row) {
                                $tmp_sub_rows[] = $sub_row;
                            }

                            $sub_rows = Sort::Multisort($tmp_sub_rows, MiscHelper::trimSortPrefix($filter_data['primary_sort']), MiscHelper::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

                            if ($action != 'display_timesheet' && $action != 'display_detailed_timesheet') {
                                $total_sub_row = MiscHelper::ArrayAssocSum($sub_rows, NULL, 2);
                                $last_sub_row = count($sub_rows);
                                $sub_rows[$last_sub_row] = $total_sub_row;
                                foreach ($static_columns as $static_column_key => $static_column_val) {
                                    $sub_rows[$last_sub_row][MiscHelper::trimSortPrefix($static_column_key)] = NULL;
                                }
                                unset($static_column_key, $static_column_val);
                            }

                            $tmp_sub_rows = $sub_rows;
                            unset($sub_rows);

                            $trimmed_static_columns = array_keys(MiscHelper::trimSortPrefix($static_columns));
                            foreach ($tmp_sub_rows as $sub_row) {
                                foreach ($sub_row as $column => $column_data) {
                                    if ($action != 'display_timesheet' && $action != 'display_detailed_timesheet') {
                                        if ($column == 'shedule_start_time' || $column == 'shedule_end_time') {
                                            // $column_data = substr($column_data, 11, 5);
                                        } elseif ($column == 'date_stamp') {
                                            $column_data = TTDateHelper::getDate('DATE', $column_data);
                                        } elseif ($column == 'min_punch_time_stamp' || $column == 'max_punch_time_stamp') {
                                            // $column_data = TTDateHelper::getDate('TIME', $column_data);
                                        } elseif ($column == 'over_time_policy_id') {
                                            $column_data = $column_data;
                                        } elseif (!strstr($column, 'wage') && !in_array($column, $trimmed_static_columns)) {
                                            $column_data = TTDateHelper::getTimeUnit($column_data);
                                        }
                                    }
                                    $sub_row_columns[$column] = $column_data;
                                    unset($column, $column_data);
                                }
                                $sub_rows[] = $sub_row_columns;
                                unset($sub_row_columns);
                            }

                            foreach ($filter_data['column_ids'] as $column_key) {
                                if (isset($columns[$column_key])) {
                                    $filter_columns[MiscHelper::trimSortPrefix($column_key)] = $columns[$column_key];
                                }
                            }
                        }

                        $rows[$i]['data'] = $sub_rows;
                        unset($sub_rows, $tmp_sub_rows);
                        $i++;
                    }
                }
            }
            unset($tmp_rows);
        }

        if ($action == 'export') {
            if (isset($rows) && isset($filter_columns)) {
                if ($filter_data['export_type'] == 'csv') {
                    $export_filter_columns = array(
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
                    );

                    $filter_columns = MiscHelper::prependArray($export_filter_columns, $filter_columns);

                    foreach ($rows as $row) {
                        if (is_array($row['data'])) {
                            foreach ($row['data'] as $sub_row) {
                                unset($row['data']);
                                $tmp_rows[] = array_merge($row, $sub_row);
                            }
                        }
                    }
                    unset($rows);

                    Log::debug('Exporting as CSV');
                    $data = MiscHelper::Array2CSV($tmp_rows, $filter_columns);
                    return response($data, 200, [
                        'Content-Type' => 'application/csv',
                        'Content-Disposition' => 'attachment; filename="report.csv"',
                        'Content-Length' => strlen($data)
                    ]);
                }
                // FL ADDED FOR GET OT DETAIL REPORT 20160518
                if ($filter_data['export_type'] == 'pdfOTDetails') {
                    Log::debug('Exporting as PDF');
                    $tssr = new TimesheetDetailReport();
                    $output = $tssr->OTDetailReport($rows, $filter_columns, $filter_data, $current_user, $current_company);
                    return response()->streamDownload(function () use ($output) {
                        echo $output;
                    }, 'OverTimeReport.pdf', ['Content-Type' => 'application/pdf']);
                }
                // FL ADDED FOR GET TIME SHEET REPORT 20160518
                if ($filter_data['export_type'] == 'mothlyTimesheet') {
                    Log::debug('Exporting as PDF');
                    $tssr = new TimesheetDetailReport();
                    $output = $tssr->EmployeeTimeSheet($rows, $filter_columns, $filter_data, $current_user, $current_company);
                    return response()->streamDownload(function () use ($output) {
                        echo $output;
                    }, 'EmployeeTimeSheetReport.pdf', ['Content-Type' => 'application/pdf']);
                }
                if ($filter_data['export_type'] == 'csv_format') {
                    if (isset($rows) && isset($filter_columns)) {
                        Log::debug('Exporting as CSV');
                        $data = MiscHelper::Array2CSVReport($rows);
                        return response($data, 200, [
                            'Content-Type' => 'application/csv',
                            'Content-Disposition' => 'attachment; filename="report.csv"',
                            'Content-Length' => strlen($data)
                        ]);
                    } else {
                        echo _('No Data To Export!') . "<br>\n";
                    }
                }
                // FL ADDED FOR GET OT DETAIL MONTHLY ATTENDANCE REPORT 20160524
                if ($filter_data['export_type'] == 'EmployeeNapayCount') {
                    Log::debug('Exporting as PDF');
                    $tsdr = new TimesheetDetailReport();
                    $output = $tsdr->MonthlyAttendanceDetailed($rows, $filter_columns, $filter_data, $current_user, $current_company);
                    return response()->streamDownload(function () use ($output) {
                        echo $output;
                    }, 'EmployeeNopayCountReport.pdf', ['Content-Type' => 'application/pdf']);
                }
            } else {
                echo __("No Data To Export!") . "<br>\n";
            }
        } else {
            $smarty->assign_by_ref('generated_time', TTDateHelper::getTime());
            $smarty->assign_by_ref('pay_period_options', $pay_period_options);
            $smarty->assign_by_ref('filter_data', $filter_data);
            $smarty->assign_by_ref('columns', $filter_columns);
            $smarty->assign_by_ref('rows', $rows);
            $smarty->assign('hidden_elements', MiscHelper::prependArray(array(
                'displayReport' => 'hidden',
                'displayTimeSheet' => 'hidden',
                'displayDetailedTimeSheet' => 'hidden',
                'export' => ''
            )));

            return $smarty->display('report/EmployeeNopayCountReport.tpl');
        }
        break;
    case 'delete':
    case 'save':
        Log::debug('Action: ' . $action);
        $generic_data['id'] = UserGenericDataFactory::reportFormDataHandler($action, $filter_data, $generic_data, route('reports.employee-nopay-count'));
        unset($generic_data['name']);
    default:
        // BreadCrumb::setCrumb($title); // Not needed in Laravel
        if ($action == 'load') {
            Log::debug('Loading Report!');
            $report_data = UserGenericDataFactory::getReportFormData($generic_data['id']);
            extract($report_data);
        } elseif ($action == '') {
            $ugdlf->getByUserIdAndScriptAndDefault($current_user->id, request()->path());
            if ($ugdlf->getRecordCount() > 0) {
                Log::debug('Found Default Report!');
                $ugd_obj = $ugdlf->getCurrent();
                $filter_data = $ugd_obj->getData();
                $generic_data['id'] = $ugd_obj->getId();
            } else {
                Log::debug('Default Settings!');
                $filter_data['user_status_ids'] = array(-1);
                $filter_data['branch_ids'] = array(-1);
                $filter_data['department_ids'] = array(-1);
                $filter_data['user_title_ids'] = array(-1);
                $filter_data['pay_period_ids'] = array('-0000-' . @array_shift(array_keys((array)$pay_period_options)));
                $filter_data['start_date'] = $default_start_date;
                $filter_data['end_date'] = $default_end_date;
                $filter_data['group_ids'] = array(-1);

                if (!isset($filter_data['column_ids'])) {
                    $filter_data['column_ids'] = array();
                }

                $filter_data['column_ids'] = array_merge($filter_data['column_ids'], array(
                    '-1000-date_stamp',
                    '-1090-worked_time',
                    '-1130-paid_time',
                    '-1140-regular_time'
                ));

                $filter_data['primary_sort'] = '-1000-date_stamp';
                $filter_data['secondary_sort'] = '-1090-worked_time';
            }
        }
        $filter_data = MiscHelper::preSetArrayValues($filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'column_ids'), NULL);

        $ulf = new UserListFactory();
        $all_array_option = array('-1' => _('-- All --'));

        $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->id, array('permission_children_ids' => $permission_children_ids));
        $user_options = $ulf->getArrayByListFactory($ulf, FALSE, TRUE);
        $filter_data['src_include_user_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['include_user_ids'], $user_options);
        $filter_data['selected_include_user_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['include_user_ids'], $user_options);

        $exclude_user_options = MiscHelper::prependArray($all_array_option, $ulf->getArrayByListFactory($ulf, FALSE, TRUE));
        $filter_data['src_exclude_user_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['exclude_user_ids'], $user_options);
        $filter_data['selected_exclude_user_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['exclude_user_ids'], $user_options);

        $user_status_options = MiscHelper::prependArray($all_array_option, $ulf->getOptions('status'));
        $filter_data['src_user_status_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['user_status_ids'], $user_status_options);
        $filter_data['selected_user_status_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['user_status_ids'], $user_status_options);

        $uglf = new UserGroupListFactory();
        $group_options = MiscHelper::prependArray($all_array_option, $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->id), 'TEXT', TRUE)));
        $filter_data['src_group_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['group_ids'], $group_options);
        $filter_data['selected_group_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['group_ids'], $group_options);

        $blf = new BranchListFactory();
        $blf->getByCompanyId($current_company->id);
        $branch_options = MiscHelper::prependArray($all_array_option, $blf->getArrayByListFactory($blf, FALSE, TRUE));
        $filter_data['src_branch_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['branch_ids'], $branch_options);
        $filter_data['selected_branch_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['branch_ids'], $branch_options);

        $filter_data['src_punch_branch_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['punch_branch_ids'], $branch_options);
        $filter_data['selected_punch_branch_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['punch_branch_ids'], $branch_options);

        $dlf = new DepartmentListFactory();
        $dlf->getByCompanyId($current_company->id);
        $department_options = MiscHelper::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, FALSE, TRUE));
        $filter_data['src_department_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['department_ids'], $department_options);
        $filter_data['selected_department_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['department_ids'], $department_options);

        $filter_data['src_punch_department_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['punch_department_ids'], $department_options);
        $filter_data['selected_punch_department_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['punch_department_ids'], $department_options);

        $utlf = new UserTitleListFactory();
        $utlf->getByCompanyId($current_company->id);
        $user_title_options = MiscHelper::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, FALSE, TRUE));
        $filter_data['src_user_title_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['user_title_ids'], $user_title_options);
        $filter_data['selected_user_title_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['user_title_ids'], $user_title_options);

        $pplf = new PayPeriodListFactory();
        $pplf->getByCompanyId($current_company->id);
        $pay_period_options = MiscHelper::prependArray($all_array_option, $pplf->getArrayByListFactory($pplf, FALSE, TRUE));
        $filter_data['src_pay_period_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['pay_period_ids'], $pay_period_options);
        $filter_data['selected_pay_period_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['pay_period_ids'], $pay_period_options);

        $filter_data['src_column_options'] = MiscHelper::arrayDiffByKey((array)$filter_data['column_ids'], $columns);
        $filter_data['selected_column_options'] = MiscHelper::arrayIntersectByKey((array)$filter_data['column_ids'], $columns);

        $filter_data['sort_options'] = $columns;
        $filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
        unset($filter_data['sort_options']['effective_date']);
        $filter_data['sort_direction_options'] = MiscHelper::getSortDirectionArray();

        $filter_data['export_type_options'] = MiscHelper::prependArray(array(
            'EmployeeNapayCount' => _('Employee Nopay Count (PDF)'),
            'csv_format' => _('CSV (Excel)')
        ));

        $hidden_elements = MiscHelper::prependArray(array(
            'displayReport' => 'hidden',
            'displayTimeSheet' => 'hidden',
            'displayDetailedTimeSheet' => 'hidden',
            'export' => ''
        ));

        $smarty->assign('hidden_elements', $hidden_elements);

        $saved_report_options = $ugdlf->getByUserIdAndScriptArray($current_user->id, request()->path());
        $generic_data['saved_report_options'] = $saved_report_options;
        $smarty->assign_by_ref('generic_data', $generic_data);
        $smarty->assign_by_ref('filter_data', $filter_data);
        $smarty->assign_by_ref('ugdf', $ugdf);

        return $smarty->display('report/EmployeeNopayCountReport.tpl');
        break;
}
?>