<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Users\BankAccountListFactory;
use App\Models\Company\BranchListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Payperiod\PayPeriodListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\paystub\PayStubEntryAccountLinkListFactory;
use App\Models\paystub\PayStubFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use Illuminate\Support\Facades\View;

class PayStubSummary extends Controller
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
        $viewData['title'] = 'Pay Stub Summary Report';
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        if (!$permission->Check('report', 'enabled') || !$permission->Check('report', 'view_pay_stub_summary')) {
            $permission->Redirect(false);
        }

        extract(FormVariables::GetVariables([
            'action',
            'generic_data',
            'filter_data'
        ]));

        URLBuilder::setURL($_SERVER['SCRIPT_NAME'], [
            'filter_data' => $filter_data
        ]);

        $static_columns = [
            '-0800-epf_membership_no' => _('EPF #'),
            '-0801-full_name' => _('Full Name'),
            '-0802-basic_for_epf' => _('Basic for EPF'),
            '-0900-first_name' => _('First Name'),
            '-0900-name_initial' => _('Name with Initials'),
            '-0901-middle_name' => _('Middle Name'),
            '-0902-middle_initial' => _('Middle Initial'),
            '-0903-last_name' => _('Last Name'),
            '-1000-full_name' => _('Full Name'),
            '-1002-employee_number' => _('Employee #'),
            '-1010-title' => _('Title'),
            '-1020-province' => _('Province/State'),
            '-1030-country' => _('Country'),
            '-1039-group' => _('Group'),
            '-1040-default_branch' => _('Default Branch'),
            '-1050-default_department' => _('Default Department'),
            '-1060-sin' => _('SIN/SSN'),
            '-1065-birth_date' => _('Birth Date'),
            '-1070-hire_date' => _('Appointment Date'),
            '-1080-since_hire_date' => _('Since Hired'),
            '-1085-termination_date' => _('Termination Date'),
            '-1086-institution' => _('Bank Institution'),
            '-1087-transit' => _('Bank Transit/Routing'),
            '-1089-account' => _('Bank Account'),
            '-1090-pay_period' => _('Pay Period'),
            '-1100-pay_stub_start_date' => _('Start Date'),
            '-1110-pay_stub_end_date' => _('End Date'),
            '-1120-pay_stub_transaction_date' => _('Transaction Date'),
            '-1130-currency' => _('Currency'),
            '-1131-current_currency' => _('Current Currency'),
            '-1132-epf_20_persent' => _('E.P.F - 20%'),
        ];

        $psealf = new PayStubEntryAccountListFactory();
        $psen_columns = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray($current_company->getId(), 10, [10, 20, 30, 40, 50, 60, 65], false);
        $columns = Misc::prependArray($static_columns, $psen_columns);

        $default_transaction_start_date = TTDate::getBeginMonthEpoch(time());
        $default_transaction_end_date = TTDate::getEndMonthEpoch(time());

        $pplf = new PayPeriodListFactory();
        $pplf->getPayPeriodsWithPayStubsByCompanyId($current_company->getId());
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
                    $default_transaction_start_date = $pay_period_obj->getEndDate();
                    $default_transaction_end_date = $pay_period_obj->getTransactionDate() + 86400;
                }
                $pp++;
            }
            $pay_period_options = $pplf->getByIdListArray($pay_period_ids, null, ['start_date' => 'desc']);
       
		}

        if (isset($filter_data['transaction_start_date'])) {
            $filter_data['transaction_start_date'] = TTDate::getBeginDayEpoch(TTDate::parseDateTime($filter_data['transaction_start_date']));
        }

        if (isset($filter_data['transaction_end_date'])) {
            $filter_data['transaction_end_date'] = TTDate::getEndDayEpoch(TTDate::parseDateTime($filter_data['transaction_end_date']));
        }

        $filter_data = Misc::preSetArrayValues($filter_data, [
            'include_user_ids',
            'exclude_user_ids',
            'user_status_ids',
            'group_ids',
            'branch_ids',
            'department_ids',
            'user_title_ids',
            'currency_ids',
            'pay_period_ids',
            'column_ids'
        ], []);

        $permission_children_ids = [];
        if (!$permission->Check('pay_stub', 'view')) {
            $hlf = new HierarchyListFactory();
            $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId());
            Debug::Arr($permission_children_ids, 'Permission Children Ids:', __FILE__, __LINE__, __METHOD__, 10);

            if (!$permission->Check('pay_stub', 'view_child')) {
                $permission_children_ids = [];
            }
            if ($permission->Check('pay_stub', 'view_own')) {
                $permission_children_ids[] = $current_user->getId();
            }
            $filter_data['permission_children_ids'] = $permission_children_ids;
        }

        $ugdlf = new UserGenericDataListFactory();
        $ugdf = new UserGenericDataFactory();

        $action = $_POST['action'] ?? '';
        $action = !empty($action) ? str_replace(' ', '_', strtolower(trim($action))) : '';

        switch ($action) {
            case 'view_pay_stubs':
                Debug::Text('View Pay Stubs!', __FILE__, __LINE__, __METHOD__, 10);
                $pslf = new PayStubListFactory();
                $pslf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                if ($pslf->getRecordCount() > 0) {
                    $filter_data['hide_employer_rows'] = $filter_data['hide_employer_rows'] ?? false;
                    if ($filter_data['export_type'] == 'payslip3') {
                        $output = $pslf->getThreePaySlipPerPage($pslf, $filter_data['hide_employer_rows']);
                    } elseif ($filter_data['export_type'] == 'payslip4') {
                        $output = $pslf->getFourPaySlipPerPageLandscape($pslf, $filter_data['hide_employer_rows']);
                    } else {
                        $output = $pslf->getPayStub($pslf, $filter_data['hide_employer_rows']);
                    }
                    if (Debug::getVerbosity() < 11) {
                        return response()->streamDownload(function () use ($output) {
                            echo $output;
                        }, 'pay_stub.pdf', ['Content-Type' => 'application/pdf']);
                    }
                }
                break;

            case 'export':
                Debug::Text('Export!', __FILE__, __LINE__, __METHOD__, 10);
                if (in_array($filter_data['export_type'], ['csv', 'pdfp', 'pdfl', 'formc'])) {
                    $ulf = new UserListFactory();
                    $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                    if ($ulf->getRecordCount() > 0) {
                        if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
                            unset($filter_data['transaction_start_date'], $filter_data['transaction_end_date']);
                        } else {
                            unset($filter_data['pay_period_ids']);
                        }
                        foreach ($ulf as $u_obj) {
                            $filter_data['user_id'][] = $u_obj->getId();
                        }

                        $tmp_filter_pay_period_ids = $filter_data['pay_period_ids'] ?? [];
                        $filter_data['pay_period_ids'] = [];
                        foreach ($tmp_filter_pay_period_ids as $filter_pay_period_id) {
                            $filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
                        }

                        if ((isset($filter_data['transaction_start_date']) && isset($filter_data['transaction_end_date'])) || isset($filter_data['pay_period_ids'])) {
                            $psealf->getByCompanyId($current_company->getId());
                            $report_columns = [];
                            foreach ($psealf as $psea_obj) {
                                $report_columns[$psea_obj->getId()] = $psea_obj->getName();
                            }
                            $report_columns = Misc::prependArray($static_columns, $report_columns);

                            $pself = new PayStubEntryListFactory();
                            $pself->getReportByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                            $raw_rows = [];
                            if ($pself->getRecordCount() > 0) {
                                foreach ($pself as $pse_obj) {
                                    $user_id = $pse_obj->getColumn('user_id');
                                    $pay_stub_id = $pse_obj->getColumn('pay_stub_id');
                                    $currency_id = $pse_obj->getColumn('currency_id');
                                    $currency_rate = $pse_obj->getColumn('currency_rate');
                                    $pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

                                    if (!isset($raw_rows[$user_id][$pay_stub_id])) {
                                        $raw_rows[$user_id][$pay_stub_id] = [
                                            'pay_period_id' => $pse_obj->getColumn('pay_period_id'),
                                            'pay_stub_start_date' => TTDate::strtotime($pse_obj->getColumn('pay_stub_start_date')),
                                            'pay_stub_end_date' => TTDate::strtotime($pse_obj->getColumn('pay_stub_end_date')),
                                            'pay_stub_transaction_date' => TTDate::strtotime($pse_obj->getColumn('pay_stub_transaction_date')),
                                            'currency_id' => $currency_id,
                                            'currency_rate' => $currency_rate,
                                            'pay_stub_entry_name' => []
                                        ];
                                    }
                                    $raw_rows[$user_id][$pay_stub_id]['pay_stub_entry_name'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
                                }
                            }

                            if (!empty($raw_rows)) {
                                $ulf = new UserListFactory();
                                $utlf = new UserTitleListFactory();
                                $title_options = $utlf->getByCompanyIdArray($current_company->getId());
                                $uglf = new UserGroupListFactory();
                                $group_options = $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'no_tree_text', true));
                                $blf = new BranchListFactory();
                                $branch_options = $blf->getByCompanyIdArray($current_company->getId());
                                $dlf = new DepartmentListFactory();
                                $department_options = $dlf->getByCompanyIdArray($current_company->getId());
                                $crlf = new CurrencyListFactory();
                                $crlf->getByCompanyId($current_company->getId());
                                $currency_options = $crlf->getArrayByListFactory($crlf, false, true);
                                $crlf->getByCompanyIdAndBase($current_company->getId(), true);
                                $base_currency_obj = $crlf->getRecordCount() > 0 ? $crlf->getCurrent() : null;
                                $currency_convert_to_base = in_array('-1', $filter_data['currency_ids']) || count($filter_data['currency_ids']) > 1;

                                $balf = new BankAccountListFactory();
                                $tmp_rows = [];
                                $x = 0;

                                foreach ($raw_rows as $user_id => $data_b) {
                                    $user_obj = $ulf->getById($user_id)->getCurrent();
                                    $balf->getUserAccountByCompanyIdAndUserId($user_obj->getCompany(), $user_obj->getId());
                                    $ba_obj = $balf->getRecordCount() == 1 ? $balf->getCurrent() : null;

                                    foreach ($data_b as $pay_stub_id => $raw_row) {
                                        $tmp_rows[$x] = [
                                            'user_id' => $user_id,
                                            'first_name' => $user_obj->getFirstName(),
                                            'middle_name' => $user_obj->getMiddleName(),
                                            'middle_initial' => $user_obj->getMiddleInitial(),
                                            'last_name' => $user_obj->getLastName(),
                                            'full_name' => $user_obj->getFullName(true),
                                            'employee_number' => $user_obj->getEmployeeNumber(),
                                            'epf_membership_no' => $user_obj->getEpfMembershipNo(),
                                            'province' => $user_obj->getProvince(),
                                            'country' => $user_obj->getCountry(),
                                            'pay_period' => Option::getByKey($raw_row['pay_period_id'], $pay_period_options, null),
                                            'pay_period_order' => Option::getByKey($raw_row['pay_period_id'], $pay_period_end_dates, null),
                                            'pay_stub_start_date_order' => $raw_row['pay_stub_start_date'],
                                            'pay_stub_end_date_order' => $raw_row['pay_stub_end_date'],
                                            'pay_stub_transaction_order' => $raw_row['pay_stub_transaction_date'],
                                            'pay_stub_start_date' => TTDate::getDate('DATE', $raw_row['pay_stub_start_date']),
                                            'pay_stub_end_date' => TTDate::getDate('DATE', $raw_row['pay_stub_end_date']),
                                            'pay_stub_transaction_date' => TTDate::getDate('DATE', $raw_row['pay_stub_transaction_date']),
                                            'title' => Option::getByKey($user_obj->getTitle(), $title_options, null),
                                            'group' => Option::getByKey($user_obj->getGroup(), $group_options),
                                            'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $branch_options, null),
                                            'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $department_options, null),
                                            'sin' => $permission->Check('user', 'view_sin') ? $user_obj->getSIN() : $user_obj->getSecureSIN(),
                                            'birth_date_order' => $user_obj->getBirthDate(),
                                            'birth_date' => TTDate::getDate('DATE', $user_obj->getBirthDate()),
                                            'hire_date_order' => $user_obj->getHireDate(),
                                            'hire_date' => TTDate::getDate('DATE', $user_obj->getHireDate()),
                                            'since_hire_date' => TTDate::getHumanTimeSince($user_obj->getHireDate()),
                                            'termination_date_order' => $user_obj->getTerminationDate(),
                                            'termination_date' => TTDate::getDate('DATE', $user_obj->getTerminationDate()),
                                            'institution' => $ba_obj ? $ba_obj->getInstitution() : null,
                                            'transit' => $ba_obj ? $ba_obj->getTransit() : null,
                                            'account' => $ba_obj ? $ba_obj->getAccount() : null,
                                            'currency' => Option::getByKey($raw_row['currency_id'], $currency_options),
                                            'current_currency' => $currency_convert_to_base ? Option::getByKey($base_currency_obj->getId(), $currency_options) : Option::getByKey($raw_row['currency_id'], $currency_options),
                                            'name_initial' => $user_obj->getNameInitial()
                                        ];

                                        foreach ($raw_row['pay_stub_entry_name'] as $id => $amount) {
                                            $tmp_rows[$x][$id] = $base_currency_obj->getBaseCurrencyAmount($amount, $raw_row['currency_rate'], $currency_convert_to_base);
                                        }

                                        $tmp_rows[$x]['basic_for_epf'] = number_format(
                                            (float)($tmp_rows[$x][1] ?? 0) + (float)($tmp_rows[$x][49] ?? 0) + (float)($tmp_rows[$x][77] ?? 0) + (float)($tmp_rows[$x][7] ?? 0) - (float)($tmp_rows[$x][45] ?? 0),
                                            2,
                                            '.',
                                            ''
                                        );
                                        $tmp_rows[$x]['epf_20_persent'] = number_format(
                                            (float)($tmp_rows[$x][9] ?? 0) + (float)($tmp_rows[$x][10] ?? 0),
                                            2,
                                            '.',
                                            ''
                                        );

                                        $x++;
                                    }
                                }

                                if (!empty($tmp_rows) && !empty($filter_data['primary_group_by']) && $filter_data['primary_group_by'] != '0') {
                                    $ignore_elements = array_keys($static_columns);
                                    $filter_data['column_ids'] = array_diff($filter_data['column_ids'], $ignore_elements);
                                    if (!empty($filter_data['secondary_group_by']) && $filter_data['secondary_group_by'] != '0') {
                                        array_unshift($filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by']);
                                    } else {
                                        array_unshift($filter_data['column_ids'], $filter_data['primary_group_by']);
                                    }
                                    $tmp_rows = Misc::ArrayGroupBy(
                                        $tmp_rows,
                                        [Misc::trimSortPrefix($filter_data['primary_group_by']), Misc::trimSortPrefix($filter_data['secondary_group_by'])],
                                        Misc::trimSortPrefix($ignore_elements, true)
                                    );
                                }

                                $rows = [];
                                if (!empty($tmp_rows)) {
                                    foreach ($tmp_rows as $row) {
                                        $rows[] = $row;
                                    }

                                    $special_sort_columns = ['pay_period', 'pay_stub_start_date', 'pay_stub_end_date', 'pay_stub_transaction_date'];
                                    if (in_array(Misc::trimSortPrefix($filter_data['primary_sort']), $special_sort_columns)) {
                                        $filter_data['primary_sort'] = $filter_data['primary_sort'] . '_order';
                                    }
                                    if (in_array(Misc::trimSortPrefix($filter_data['secondary_sort']), $special_sort_columns)) {
                                        $filter_data['secondary_sort'] = $filter_data['secondary_sort'] . '_order';
                                    }

                                    $rows = Sort::Multisort(
                                        $rows,
                                        Misc::trimSortPrefix($filter_data['primary_sort']),
                                        Misc::trimSortPrefix($filter_data['secondary_sort']),
                                        $filter_data['primary_sort_dir'],
                                        $filter_data['secondary_sort_dir']
                                    );

                                    $total_row = Misc::ArrayAssocSum($rows, null, 2);
                                    $last_row = count($rows);
                                    $rows[$last_row] = $total_row;
                                    foreach ($static_columns as $static_column_key => $static_column_val) {
                                        $rows[$last_row][Misc::trimSortPrefix($static_column_key)] = null;
                                    }
                                    $rows[$last_row]['basic_for_epf'] = $total_row['basic_for_epf'];
                                    $rows[$last_row]['epf_20_persent'] = $total_row['epf_20_persent'];
                                }

                                $filter_columns = [];
                                foreach ($filter_data['column_ids'] as $column_key) {
                                    $filter_columns[Misc::trimSortPrefix($column_key)] = $report_columns[$column_key];
                                }

                                if ($filter_data['export_type'] == 'csv') {
                                    if (!empty($rows) && !empty($filter_columns)) {
                                        Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__, 10);
                                        $data = Misc::Array2CSV($rows, $filter_columns);
                                        return response()->streamDownload(function () use ($data) {
                                            echo $data;
                                        }, 'report.csv', ['Content-Type' => 'application/csv']);
                                    } else {
                                        return redirect()->back()->with('error', 'No Data To Export!');
                                    }
                                } elseif ($filter_data['export_type'] == 'pdfp') {
                                    $payperiod_string = '';
                                    if ($filter_data['date_type'] == 'pay_period_ids') {
                                        foreach ($filter_data['pay_period_ids'] as $id) {
                                            if ($id > 0) {
                                                $payperiod_string .= $pay_period_options[$id] . ', ';
                                            } else {
                                                $payperiod_string = 'ALL  ';
                                            }
                                        }
                                        $payperiod_string = rtrim($payperiod_string, ', ');
                                    }
                                    if (!empty($rows) && !empty($filter_columns)) {
                                        Debug::Text('Exporting as PDF (Portrait)', __FILE__, __LINE__, __METHOD__, 10);
                                        $pslf = new PayStubListFactory();
                                        $output = $pslf->Array2PDF($rows, $filter_columns, $current_user, $current_company, $filter_data['transaction_start_date'], $filter_data['transaction_end_date'], $payperiod_string);
                                        return response()->streamDownload(function () use ($output) {
                                            echo $output;
                                        }, 'pay_stub.pdf', ['Content-Type' => 'application/pdf']);
                                    } else {
                                        return redirect()->back()->with('error', 'No PDF Data To Export!');
                                    }
                                } elseif ($filter_data['export_type'] == 'pdfl') {
                                    $payperiod_string = '';
                                    if ($filter_data['date_type'] == 'pay_period_ids') {
                                        foreach ($filter_data['pay_period_ids'] as $id) {
                                            if ($id > 0) {
                                                $payperiod_string .= $pay_period_options[$id] . ', ';
                                            } else {
                                                $payperiod_string = 'ALL  ';
                                            }
                                        }
                                        $payperiod_string = rtrim($payperiod_string, ', ');
                                    }
                                    $dlf = new DepartmentListFactory();
                                    $dep_list = '';
                                    $di = 1;
                                    foreach ($filter_data['department_ids'] as $dep_ids) {
                                        $cma = ($di == count($filter_data['department_ids'])) ? '' : ',';
                                        $dep_list .= $dlf->getNameById($dep_ids) . $cma;
                                        $di++;
                                    }
                                    $uglf = new UserGroupListFactory();
                                    $grp_list = '';
                                    $gi = 1;
                                    foreach ($filter_data['group_ids'] as $grp_ids) {
                                        $cma = ($gi == count($filter_data['group_ids'])) ? '' : ',';
                                        $grp_list .= $uglf->getNameById($grp_ids) . $cma;
                                        $gi++;
                                    }
                                    $fliter_list = ['group' => $grp_list, 'dept' => $dep_list];

                                    if (!empty($rows) && !empty($filter_columns)) {
                                        Debug::Text('Exporting as PDF (Landscape)', __FILE__, __LINE__, __METHOD__, 10);
                                        $pslf = new PayStubListFactory();
                                        $output = $pslf->Array2PDFLandscape($rows, $filter_columns, $current_user, $current_company, $filter_data['transaction_start_date'], $filter_data['transaction_end_date'], $payperiod_string, $fliter_list);
                                        return response()->streamDownload(function () use ($output) {
                                            echo $output;
                                        }, 'pay_stub.pdf', ['Content-Type' => 'application/pdf']);
                                    } else {
                                        return redirect()->back()->with('error', 'No PDF Data To Export!');
                                    }
                                } elseif ($filter_data['export_type'] == 'formc') {
                                    $payperiod_string = '';
                                    if ($filter_data['date_type'] == 'pay_period_ids') {
                                        foreach ($filter_data['pay_period_ids'] as $id) {
                                            if ($id > 0) {
                                                $sub_string = substr($pay_period_options[$id], 14);
                                                $replace_string = str_replace('/', '-', $sub_string);
                                                $date = new \DateTime($replace_string);
                                                $payperiod_string .= $date->format('F Y') . ', ';
                                            } else {
                                                $payperiod_string = 'ALL  ';
                                            }
                                        }
                                        $payperiod_string = rtrim($payperiod_string, ', ');
                                    }
                                    if (!empty($rows) && !empty($filter_data['include_user_ids'])) {
                                        Debug::Text('Exporting as Form C PDF', __FILE__, __LINE__, __METHOD__, 10);
                                        $pslf = new PayStubListFactory();
                                        $output = $pslf->FormC($rows, $filter_data['include_user_ids'], $current_user, $current_company, $payperiod_string);
                                        return response()->streamDownload(function () use ($output) {
                                            echo $output;
                                        }, 'form_c.pdf', ['Content-Type' => 'application/pdf']);
                                    } else {
                                        return redirect()->back()->with('error', 'Please Select at Least One Employee or Pay Period!');
                                    }
                                } else {
                                    $pslf = new PayStubListFactory();
                                    $pslf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
                                    if ($pslf->getRecordCount() > 0 && strlen($filter_data['export_type']) >= 3) {
                                        $output = $pslf->exportPayStub($pslf, $filter_data['export_type']);
                                        $file_name = stristr($filter_data['export_type'], 'cheque') ? 'checks_' . str_replace(['/', ',', ' '], '_', now()->format('Y_m_d')) . '.pdf' : 'eft_' . ($setup_data['file_creation_number']++ ?? 0) . '_' . str_replace(['/', ',', ' '], '_', now()->format('Y_m_d')) . '.txt';
                                        return response()->streamDownload(function () use ($output) {
                                            echo $output;
                                        }, $file_name, ['Content-Type' => stristr($filter_data['export_type'], 'cheque') ? 'application/pdf' : 'application/text']);
                                    } else {
                                        return redirect()->back()->with('error', 'No Data To Export or Invalid Export Format!');
                                    }
                                }
                            }
                        }
                    }
                }
                break;

            case 'delete':
            case 'save':
                Debug::Text('Action: ' . $action, __FILE__, __LINE__, __METHOD__, 10);
                $generic_data['id'] = UserGenericDataFactory::reportFormDataHandler(
                    $action,
                    $filter_data,
                    $generic_data,
                    URLBuilder::getURL(null, $_SERVER['SCRIPT_NAME'])
                );
                unset($generic_data['name']);
                break;

            default:
                if ($action == 'load') {
                    Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__, 10);
                    extract(UserGenericDataFactory::getReportFormData($generic_data['id']));
                } elseif (empty($action)) {
                    $ugdlf->getByUserIdAndScriptAndDefault($current_user->getId(), $_SERVER['SCRIPT_NAME']);
                    if ($ugdlf->getRecordCount() > 0) {
                        $ugd_obj = $ugdlf->getCurrent();
                        $filter_data = $ugd_obj->getData();
                        $generic_data['id'] = $ugd_obj->getId();
                    } else {
                        $filter_data['user_status_ids'] = [-1];
                        $filter_data['branch_ids'] = [-1];
                        $filter_data['department_ids'] = [-1];
                        $filter_data['user_title_ids'] = [-1];
                        $filter_data['transaction_start_date'] = $default_transaction_start_date;
                        $filter_data['transaction_end_date'] = $default_transaction_end_date;
                        $filter_data['group_ids'] = [-1];
                        $filter_data['currency_ids'] = [-1];
                        $keys = array_keys($pay_period_options ?? []);
                        $first_key = !empty($keys) ? array_shift($keys) : null;
                        $filter_data['pay_period_ids'] = $first_key !== null ? ['-0000-' . $first_key] : [];

                        $default_columns = [
                            '-0800-epf_membership_no',
                            '-1000-full_name',
                            '49',
                            '45',
                            '-0802-basic_for_epf',
                            '9',
                            '20',
                            '8',
                            '29',
                            '12',
                            '13',
                            '10',
                            '-1132-epf_20_persent',
                            '18',
                            '75'
                        ];

                        $pseallf = new PayStubEntryAccountLinkListFactory();
                        $pseallf->getByCompanyId($current_company->getId());
                        $default_linked_columns = $pseallf->getRecordCount() > 0 ? [
                            $pseallf->getCurrent()->getTotalGross(),
                            $pseallf->getCurrent()->getTotalNetPay(),
                            $pseallf->getCurrent()->getTotalEmployeeDeduction(),
                            $pseallf->getCurrent()->getTotalEmployerDeduction()
                        ] : [];

                        $filter_data['column_ids'] = Misc::prependArray($default_columns, $default_linked_columns);
                        $filter_data['primary_sort'] = '-1000-full_name';
                        $filter_data['secondary_sort'] = '-1120-pay_stub_transaction_date';
                    }
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
                    'currency_ids',
                    'column_ids'
                ], null);

                $ulf = new UserListFactory();
                $all_array_option = ['-1' => _('-- All --')];

                $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), ['permission_children_ids' => $permission_children_ids]);
                $user_options = $ulf->getArrayByListFactory($ulf, false, true);
                $filter_data['src_include_user_options'] = Misc::arrayDiffByKey((array)$filter_data['include_user_ids'], $user_options);
                $filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['include_user_ids'], $user_options);

                $exclude_user_options = Misc::prependArray($all_array_option, $ulf->getArrayByListFactory($ulf, false, true));
                $filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey((array)$filter_data['exclude_user_ids'], $user_options);
                $filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey((array)$filter_data['exclude_user_ids'], $user_options);

                $user_status_options = Misc::prependArray($all_array_option, $ulf->getOptions('status'));
                $filter_data['src_user_status_options'] = Misc::arrayDiffByKey((array)$filter_data['user_status_ids'], $user_status_options);
                $filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_status_ids'], $user_status_options);

                $uglf = new UserGroupListFactory();
                $group_options = Misc::prependArray($all_array_option, $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', true)));
                $filter_data['src_group_options'] = Misc::arrayDiffByKey((array)$filter_data['group_ids'], $group_options);
                $filter_data['selected_group_options'] = Misc::arrayIntersectByKey((array)$filter_data['group_ids'], $group_options);

                $blf = new BranchListFactory();
                $blf->getByCompanyId($current_company->getId());
                $branch_options = Misc::prependArray($all_array_option, $blf->getArrayByListFactory($blf, false, true));
                $filter_data['src_branch_options'] = Misc::arrayDiffByKey((array)$filter_data['branch_ids'], $branch_options);
                $filter_data['selected_branch_options'] = Misc::arrayIntersectByKey((array)$filter_data['branch_ids'], $branch_options);

                $dlf = new DepartmentListFactory();
                $dlf->getByCompanyId($current_company->getId());
                $department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, false, true));
                $filter_data['src_department_options'] = Misc::arrayDiffByKey((array)$filter_data['department_ids'], $department_options);
                $filter_data['selected_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['department_ids'], $department_options);

                $utlf = new UserTitleListFactory();
                $utlf->getByCompanyId($current_company->getId());
                $user_title_options = Misc::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, false, true));
                $filter_data['src_user_title_options'] = Misc::arrayDiffByKey((array)$filter_data['user_title_ids'], $user_title_options);
                $filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey((array)$filter_data['user_title_ids'], $user_title_options);

                $pay_period_options = Misc::prependArray($all_array_option, $pplf->getArrayByListFactory($pplf, false, true));
                $filter_data['src_pay_period_options'] = Misc::arrayDiffByKey((array)$filter_data['pay_period_ids'], $pay_period_options);
                $filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey((array)$filter_data['pay_period_ids'], $pay_period_options);

                $crlf = new CurrencyListFactory();
                $crlf->getByCompanyId($current_company->getId());
                $currency_options = Misc::prependArray($all_array_option, $crlf->getArrayByListFactory($crlf, false, true));
                $filter_data['src_currency_options'] = Misc::arrayDiffByKey((array)$filter_data['currency_ids'], $currency_options);
                $filter_data['selected_currency_options'] = Misc::arrayIntersectByKey((array)$filter_data['currency_ids'], $currency_options);

                $filter_data['src_column_options'] = Misc::arrayDiffByKey((array)$filter_data['column_ids'], $columns);
                $filter_data['selected_column_options'] = Misc::arrayIntersectByKey((array)$filter_data['column_ids'], $columns);

                $filter_data['sort_options'] = $columns;
                $filter_data['sort_direction_options'] = Misc::getSortDirectionArray();
                $filter_data['group_by_options'] = Misc::prependArray(['0' => _('No Grouping')], $static_columns);
                $filter_data['export_type_options'] = Misc::prependArray([
                    'csv' => _('CSV (Excel)'),
                    'pdfp' => _('PDF (PORTRAIT)'),
                    'pdfl' => _('PDF (LANDSCAPE)'),
                    'formc' => _('Form C (PDF)'),
                    'payslip3' => _('3 Payslip/Page (PDF)'),
                    'payslip4' => _('4 Payslip/Page (Landscape PDF)')
                ], Misc::trimSortPrefix((new PayStubFactory())->getOptions('export_type')));

                $saved_report_options = $ugdlf->getByUserIdAndScriptArray($current_user->getId(), $_SERVER['SCRIPT_NAME']);
                $generic_data['saved_report_options'] = $saved_report_options;

                $viewData['generic_data'] = $generic_data;
                $viewData['filter_data'] = $filter_data;
                $viewData['ugdf'] = $ugdf;

                return view('report/PayStubSummary', $viewData);
        }

        $viewData['generated_time'] = TTDate::getTime();
        $viewData['pay_period_options'] = $pay_period_options ?? [];
        $viewData['filter_data'] = $filter_data;
        $viewData['columns'] = $filter_columns ?? [];
        $viewData['rows'] = $rows ?? [];
        return view('report/PayStubSummaryReport', $viewData);
    }
}