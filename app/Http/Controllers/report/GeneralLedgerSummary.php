<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Payperiod\PayPeriodListFactory;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Core\Debug;
use App\Models\Core\FastTree;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\CurrencyListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Core\GeneralLedgerExport;
use App\Models\Core\GeneralLedgerExport_JournalEntry;
use App\Models\Core\GeneralLedgerExport_Record;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class GeneralLedgerSummary extends Controller
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

    protected function replaceGLAccountVariables($subject, $replace_arr = null)
    {
        $search_arr = [
            '#default_branch#',
            '#default_department#',
            '#employee_number#'
        ];

        if ($subject != '' && is_array($replace_arr)) {
            $subject = str_replace($search_arr, $replace_arr, $subject);
        }

        return $subject;
    }

    public function index()
    {
        $viewData['title'] = 'General Ledger Summary Report';
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        if (!$permission->Check('report', 'enabled') || !$permission->Check('report', 'view_general_ledger_summary')) {
            $permission->Redirect(FALSE);
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
            '-1000-full_name' => _('Full Name'),
            '-1010-title' => _('Title'),
            '-1020-province' => _('Province'),
            '-1030-country' => _('Country'),
            '-1040-default_branch' => _('Default Branch'),
            '-1050-default_department' => _('Default Department'),
        ];

        $columns = $static_columns;

        // Get all pay periods
        $pplf = new PayPeriodListFactory();
        $pplf->getByCompanyId($current_company->getId());
        $pay_period_ids = [];
        if ($pplf->getRecordCount() > 0) {
            foreach ($pplf->rs as $pay_period_obj) {
                $pplf->data = (array)$pay_period_obj;
                $pay_period_obj = $pplf;
                $pay_period_ids[] = $pay_period_obj->getId();
            }
            $pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, ['start_date' => 'desc']);
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
        ], []);

        $ugdlf = new UserGenericDataListFactory();
        $ugdf = new UserGenericDataFactory();

        $action = $_POST['action'] ?? '';
        $action = !empty($action) ? str_replace(' ', '_', strtolower(trim($action))) : '';

        switch ($action) {
            case 'export':
            case 'display_report':
                Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

                // Trim sort prefix from selected pay periods
                $tmp_filter_pay_period_ids = $filter_data['pay_period_ids'] ?? [];
                $filter_data['pay_period_ids'] = [];
                foreach ($tmp_filter_pay_period_ids as $filter_pay_period_id) {
                    $filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
                }

                if (!empty($filter_data['pay_period_ids'])) {
                    $psealf = new PayStubEntryAccountListFactory();
                    $psealf->getByCompanyId($current_company->getId());
                    $report_columns = [];
                    foreach ($psealf as $psea_obj) {
                        $report_columns[$psea_obj->getId()] = $psea_obj->getName();
                    }
                    $report_columns = Misc::prependArray($static_columns, $report_columns);

                    $psea_arr = [];
                    foreach ($psealf->rs as $psea_obj) {
                        $psealf->data = (array)$psea_obj;
                        $psea_obj = $psealf;
                        $psea_arr[$psea_obj->getId()] = [
                            'name' => $psea_obj->getName(),
                            'debit_account' => $psea_obj->getDebitAccount(),
                            'credit_account' => $psea_obj->getCreditAccount(),
                        ];
                    }
                    $pslf = new PayStubListFactory();
                    $pslf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);

                    if ($pslf->getRecordCount() > 0) {
                        $ulf = new UserListFactory();
                        $blf = new BranchListFactory();
                        $branch_options = $blf->getByCompanyIdArray($current_company->getId());

                        $branch_code_map = [0 => 0];
                        $blf->getByCompanyId($current_company->getId());
                        if ($blf->getRecordCount() > 0) {
                            foreach ($blf as $b_obj) {
                                $branch_code_map[$b_obj->getId()] = $b_obj->getManualID();
                            }
                        }

                        $dlf = new DepartmentListFactory();
                        $department_options = $dlf->getByCompanyIdArray($current_company->getId());

                        $department_code_map = [0 => 0];
                        $dlf->getByCompanyId($current_company->getId());
                        if ($dlf->getRecordCount() > 0) {
                            foreach ($dlf as $d_obj) {
                                $department_code_map[$d_obj->getId()] = $d_obj->getManualID();
                            }
                        }

                        $utlf = new UserTitleListFactory();
                        $title_options = $utlf->getByCompanyIdArray($current_company->getId());

                        $crlf = new CurrencyListFactory();
                        $crlf->getByCompanyId($current_company->getId());
                        $currency_options = $crlf->getArrayByListFactory($crlf, FALSE, TRUE);

                        $crlf->getByCompanyIdAndBase($current_company->getId(), TRUE);
                        $base_currency_obj = $crlf->getRecordCount() > 0 ? $crlf->getCurrent() : null;

                        $currency_convert_to_base = in_array('-1', $filter_data['currency_ids']) || count($filter_data['currency_ids']) > 1;

                        $tmp_rows = [];
                        foreach ($pslf->rs as $ps_obj) {
                            $pslf->data = (array)$ps_obj;
                            $ps_obj = $pslf;
                            $user_obj = $ulf->getById($ps_obj->getUser())->getCurrent();
                            // dd($user_obj);
                            $replace_arr = [
                                $branch_code_map[(int)$user_obj->getDefaultBranch()] ?? 'UNKNOWN_BR',
                                $department_code_map[(int)$user_obj->getDefaultDepartment()] ?? 'NO_DEPT',
                                $user_obj->getEmployeeNumber()
                            ];
                            $pself = new PayStubEntryListFactory();
                            $pself->getByPayStubIdAndYTDAdjustment($ps_obj->getId(), FALSE);
                            if ($pself->getRecordCount() > 0) {
                                $raw_je_records = [];
                                foreach ($pself->rs as $pse_obj) {
                                    $pself->data = (array)$pse_obj;
                                    $pse_obj = $pself;
                                    if (isset($psea_arr[$pse_obj->getPayStubEntryNameId()])) {
                                        if (!empty($psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'])) {
                                            $debit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account']);
                                            foreach ($debit_accounts as $debit_account) {
                                                $debit_account = $this->replaceGLAccountVariables($debit_account, $replace_arr);
                                                if ($pse_obj->getAmount() != 0) {
                                                    $raw_je_records[] = [
                                                        'type' => 'debit',
                                                        'account' => $debit_account,
                                                        'amount' => Misc::MoneyFormat($base_currency_obj->getBaseCurrencyAmount(
                                                            $pse_obj->getAmount(),
                                                            $ps_obj->getCurrencyRate(),
                                                            $currency_convert_to_base
                                                        ), FALSE),
                                                    ];
                                                }
                                            }
                                        }

                                        if (!empty($psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'])) {
                                            $credit_accounts = explode(',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account']);
                                            foreach ($credit_accounts as $credit_account) {
                                                $credit_account = $this->replaceGLAccountVariables($credit_account, $replace_arr);
                                                if ($pse_obj->getAmount() != 0) {
                                                    $raw_je_records[] = [
                                                        'type' => 'credit',
                                                        'account' => $credit_account,
                                                        'amount' => Misc::MoneyFormat($base_currency_obj->getBaseCurrencyAmount(
                                                            $pse_obj->getAmount(),
                                                            $ps_obj->getCurrencyRate(),
                                                            $currency_convert_to_base
                                                        ), FALSE),
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                                if (!empty($raw_je_records)) {
                                    $grouped_je_records = Misc::ArrayGroupBy($raw_je_records, ['type', 'account'], []);
                                    $total_je_records = ['type' => 'total', 'account' => null, 'amount' => null];
                                    foreach ($grouped_je_records as $grouped_je_record) {
                                        if (isset($total_je_records[$grouped_je_record['type']])) {
                                            $total_je_records[$grouped_je_record['type']] = bcadd(
                                                $total_je_records[$grouped_je_record['type']],
                                                $grouped_je_record['amount']
                                            );
                                        } else {
                                            $total_je_records[$grouped_je_record['type']] = $grouped_je_record['amount'];
                                        }

                                        if (isset($total_je_records['debit']) && isset($total_je_records['credit'])) {
                                            $total_je_records['diff'] = bcsub($total_je_records['debit'], $total_je_records['credit']);
                                        }
                                    }

                                    $grouped_je_records['total'] = $total_je_records;

                                    foreach ($grouped_je_records as $je_record) {
                                        $tmp_arr = [
                                            'user_id' => $user_obj->getId(),
                                            'full_name' => $user_obj->getFullName(TRUE),
                                            'transaction_date' => $ps_obj->getTransactionDate(),
                                            'pay_stub_id' => $ps_obj->getId(),
                                            'pay_period_id' => $ps_obj->getPayPeriod(),
                                            'province' => $user_obj->getProvince(),
                                            'country' => $user_obj->getCountry(),
                                            'title' => $title_options[$user_obj->getTitle()],
                                            'default_branch_id' => $user_obj->getDefaultBranch(),
                                            'default_branch' => $branch_options[$user_obj->getDefaultBranch()],
                                            'default_department_id' => $user_obj->getDefaultDepartment(),
                                            'default_department' => $department_options[$user_obj->getDefaultDepartment()],
                                            'type' => $je_record['type'],
                                            'account' => $je_record['account'],
                                            'amount' => $je_record['amount'],
                                        ];

                                        if ($je_record['type'] == 'total') {
                                            $tmp_arr['total_debits'] = Misc::MoneyFormat($je_record['debit'], FALSE);
                                            $tmp_arr['total_credits'] = Misc::MoneyFormat($je_record['credit'], FALSE);
                                            $tmp_arr['total_diff'] = Misc::MoneyFormat($je_record['diff'], FALSE);
                                        }

                                        $tmp_rows[] = $tmp_arr;
                                    }
                                }
                            }
                        }
                        if (!empty($tmp_rows) && !empty($filter_data['primary_group_by']) && $filter_data['primary_group_by'] != '0') {
                            $ignore_elements = array_keys($static_columns);
                            $tmp_rows = Misc::ArrayGroupBy($tmp_rows, [
                                'transaction_date',
                                'type',
                                'account',
                                Misc::trimSortPrefix($filter_data['primary_group_by'])
                            ], $ignore_elements);
                            $final_group_key = Misc::trimSortPrefix($filter_data['primary_group_by']);
                        } else {
                            $final_group_key = 'user_id';
                        }

                        $rows = [];

                        if (!empty($tmp_rows)) {
                            foreach ($tmp_rows as $row) {
                                $rows[] = $row;
                            }

                            $rows = Sort::Multisort(
                                $rows,
                                Misc::trimSortPrefix($filter_data['primary_sort']),
                                Misc::trimSortPrefix($filter_data['secondary_sort']),
                                $filter_data['primary_sort_dir'],
                                $filter_data['secondary_sort_dir']
                            );

                            $final_rows = [];
                            foreach ($rows as $row) {
                                if (!isset($final_rows[$row['transaction_date']][$row[$final_group_key]])) {
                                    $source = $final_group_key == 'user_id' ? $row['pay_stub_id'] : ($row[$final_group_key] == '--' ? 'TimeTrex' : $row[$final_group_key]);
                                    $comment = $final_group_key == 'user_id' ? $row['full_name'] : ($row[$final_group_key] == '--' ? 'Payroll' : $row[$final_group_key]);

                                    if ($currency_convert_to_base) {
                                        $comment .= ' [' . $base_currency_obj->getISOCode() . ']';
                                    }

                                    $final_rows[$row['transaction_date']][$row[$final_group_key]] = [
                                        'source' => $source,
                                        'comment' => $comment,
                                        'transaction_date' => $row['transaction_date'],
                                    ];
                                }

                                if ($row['type'] == 'total') {
                                    $final_rows[$row['transaction_date']][$row[$final_group_key]]['records'][$row['type']][] = [
                                        'time' => $row['type'],
                                        'account' => $row['account'],
                                        'amount' => $row['amount'],
                                        'total_debits' => $row['total_debits'],
                                        'total_credits' => $row['total_credits'],
                                        'total_diff' => $row['total_diff']
                                    ];
                                } else {
                                    $final_rows[$row['transaction_date']][$row[$final_group_key]]['records'][$row['type']][] = [
                                        'type' => $row['type'],
                                        'account' => $row['account'],
                                        'amount' => $row['amount'],
                                    ];
                                }
                            }

                            if ($action == 'export') {
                                $gle = new GeneralLedgerExport();
                                $gle->setFileFormat($filter_data['export_type']);
                            }

                            $rows = [];
                            foreach ($final_rows as $final_row_a) {
                                foreach ($final_row_a as $final_row_b) {
                                    if ($action == 'export') {
                                        $je = new GeneralLedgerExport_JournalEntry();
                                        $je->setDate($final_row_b['transaction_date']);
                                        $je->setSource($final_row_b['source'] == '--' ? 'TimeTrex' : $final_row_b['source']);
                                        $je->setComment($final_row_b['comment'] == '--' ? 'Payroll' : $final_row_b['comment']);

                                        if (!empty($final_row_b['records'])) {
                                            foreach ($final_row_b['records'] as $type => $je_records) {
                                                foreach ($je_records as $je_record) {
                                                    $record = new GeneralLedgerExport_Record();
                                                    $record->setAccount($je_record['account']);
                                                    $record->setType($type);
                                                    $record->setAmount($je_record['amount']);
                                                    $je->setRecord($record);
                                                }
                                            }
                                        }
                                        $gle->setJournalEntry($je);
                                    }
                                    $rows[] = $final_row_b;
                                }
                            }
                        }
                    }

              if ($action == 'export') {
                        if (!empty($rows) && !empty($gle) && !empty($filter_data['export_type'])) {
                            if ($gle->compile() == true) {
                                $data = $gle->getCompiledData();
                                $file_name = 'general_ledger_' . str_replace(['/', ',', ' '], '_', now()->format('Y_m_d')) .
                                    ($filter_data['export_type'] == 'simply' ? '.txt' : '.csv');

                                $headers = [
                                    'Content-Type' => 'text/csv; charset=utf-8',
                                    'Content-Disposition' => 'attachment; filename="' . $file_name . '"',
                                    'Content-Type' => 'application/octet-stream',
                                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                                    'Pragma' => 'no-cache',
                                    'Expires' => '0',
                                ];

                                $rows = array_map('str_getcsv', explode("\n", trim($data)));
                                $header = array_shift($rows);

                                $callback = function () use ($header, $rows) {
                                    $file = fopen('php://output', 'w');
                                    fwrite($file, "\xEF\xBB\xBF");
                                    fputcsv($file, $header);
                                    foreach ($rows as $row) {
                                        $row = array_pad($row, count($header), '');
                                        fputcsv($file, $row);
                                    }
                                    fclose($file);
                                };

                                Misc::FileDownloadHeader($file_name, 'application/text', null);
                                return response()->stream($callback, 200, $headers);
                            } else {
                                return redirect()->back()->with('error', 'One or more journal entries did not balance!');
                            }
                        } else {
                            return redirect()->back()->with('error', 'No Data To Export!');
                        }
                    } else {
                        $viewData['generated_time'] = TTDate::getTime();
                        $viewData['pay_period_options'] = $pay_period_options ?? [];
                        $viewData['filter_data'] = $filter_data;
                        $viewData['columns'] = $report_columns ?? [];
                        $viewData['rows'] = $rows ?? [];
                        return view('report/GeneralLedgerSummaryReport', $viewData);
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
                        $keys = array_keys($pay_period_options ?? []);
                        $first_key = !empty($keys) ? array_shift($keys) : null;
                        $filter_data['pay_period_ids'] = $first_key !== null ? ['-0000-' . $first_key] : [];
                        $filter_data['group_ids'] = [-1];
                        $filter_data['currency_ids'] = [-1];

                        $default_columns = ['-1000-full_name'];

                        $pseallf = new PayStubEntryAccountLinkListFactory();
                        $pseallf->getByCompanyId($current_company->getId());
                        if ($pseallf->getRecordCount() > 0) {
                            $pseal_obj = $pseallf->getCurrent();
                            $default_linked_columns = [
                                $pseal_obj->getTotalGross(),
                                $pseal_obj->getTotalNetPay(),
                                $pseal_obj->getTotalEmployeeDeduction(),
                                $pseal_obj->getTotalEmployerDeduction()
                            ];
                            $filter_data['secondary_sort'] = $pseal_obj->getTotalGross();
                        } else {
                            $default_linked_columns = [];
                        }

                        $filter_data['column_ids'] = Misc::prependArray($default_columns, $default_linked_columns);
                        $filter_data['primary_sort'] = '-1000-full_name';
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

                $ulf->getByCompanyId($current_company->getId());
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

                $dlf = new DepartmentListFactory();
                $dlf->getByCompanyId($current_company->getId());
                $department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, FALSE, TRUE));
                $filter_data['src_department_options'] = Misc::arrayDiffByKey((array)$filter_data['department_ids'], $department_options);
                $filter_data['selected_department_options'] = Misc::arrayIntersectByKey((array)$filter_data['department_ids'], $department_options);

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

                $crlf = new CurrencyListFactory();
                $crlf->getByCompanyId($current_company->getId());
                $currency_options = Misc::prependArray($all_array_option, $crlf->getArrayByListFactory($crlf, FALSE, TRUE));
                $filter_data['src_currency_options'] = Misc::arrayDiffByKey((array)$filter_data['currency_ids'], $currency_options);
                $filter_data['selected_currency_options'] = Misc::arrayIntersectByKey((array)$filter_data['currency_ids'], $currency_options);

                $filter_data['src_column_options'] = Misc::arrayDiffByKey((array)$filter_data['column_ids'], $columns);
                $filter_data['selected_column_options'] = Misc::arrayIntersectByKey((array)$filter_data['column_ids'], $columns);

                $filter_data['sort_options'] = $columns;
                $filter_data['sort_direction_options'] = Misc::getSortDirectionArray();
                $filter_data['group_by_options'] = Misc::prependArray(['0' => _('No Grouping')], $static_columns);
                $filter_data['export_type_options'] = [
                    'csv' => _('CSV (Excel)'),
                    // 'simply' => _('Simply Accounting GL'),
                ];

                $saved_report_options = $ugdlf->getByUserIdAndScriptArray($current_user->getId(), $_SERVER['SCRIPT_NAME']);
                $generic_data['saved_report_options'] = $saved_report_options;

                $viewData['generic_data'] = $generic_data;
                $viewData['filter_data'] = $filter_data;
                $viewData['ugdf'] = $ugdf;

                return view('report/GeneralLedgerSummary', $viewData);
        }
    }

}
