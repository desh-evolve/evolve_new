<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use App\Models\Core\TTDate;
use App\Models\users\UserListFactory;
use App\Models\users\UserGroupListFactory;
use App\Models\company\BranchListFactory;
use App\Models\department\DepartmentListFactory;
use App\Models\users\UserTitleListFactory;
use App\Models\users\UserWageListFactory;
use App\Models\users\UserDeductionListFactory;
use App\Models\users\UserPreferenceFactory;
use App\Models\core\PermissionControlListFactory;
use App\Models\payperiod\PayPeriodScheduleListFactory;
use App\Models\policy\PolicyGroupListFactory;
use App\Models\core\CurrencyListFactory;
use App\Models\users\BankAccountFactory;
use App\Models\core\OtherFieldListFactory;
use App\Models\company\CompanyDeductionListFactory;
use App\Models\users\UserGenericDataListFactory;
use App\Models\users\UserGenericDataFactory;
use Illuminate\Support\Facades\View;
use App\Models\hierarchy\HierarchyListFactory;
use App\Models\core\Option;
use App\Models\core\Sort;
use App\Models\core\FastTree;
use App\Models\core\TTi18n;

class UserInformation extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
    }

    //     public function index(Request $request)
    //     {
    //         if (!$this->permission->Check('report', 'enabled') || 
    //             !$this->permission->Check('report', 'view_user_information')) {
    //             return Redirect::to('/')->with('error', 'Permission denied');
    //         }

    //         $current_company = $this->company;
    //         $filter_data = $request->input('filter_data', []);
    //         $generic_data = $request->input('generic_data', []);
    //         $action = $request->input('action', '');

    //         // Define columns
    //         $columns = $this->getColumnDefinitions();

    //         // Get custom user fields
    //         $oflf = new OtherFieldListFactory();
    //         $other_field_names = $oflf->getByCompanyIdAndTypeIdArray($current_company->getId(), 10);
    //         if (is_array($other_field_names)) {
    //             $columns = Misc::prependArray($columns, $other_field_names);
    //         }

    //         // Company Deductions
    //         $cdlf = new CompanyDeductionListFactory();
    //         $deduction_columns = $cdlf->getByCompanyIdAndStatusIdArray($current_company->getId(), 10, false);
    //         $columns = Misc::prependArray($columns, $deduction_columns);

    //         // Initialize filter data if not set
    //         $filter_data = $this->initializeFilterData($filter_data);

    //         // Handle permission hierarchy
    //         $permission_children_ids = $this->getPermissionChildrenIds();
    //         $filter_data['permission_children_ids'] = $permission_children_ids;

    //         $wage_permission_children_ids = $this->getWagePermissionChildrenIds();
    //         $wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;

    //         // Process form submission
    //         if ($action === 'export' || $action === 'display_report') {
    //             return $this->generateReport($filter_data, $columns, $action);
    //         }

    //         // Default view logic
    //         $data = $this->prepareFormData($filter_data, $columns, $generic_data);

    //         $viewData = [
    //             'title' => 'Employee Detail Report',
    //             'data' => $data,
    //             'ugdf' => new UserGenericDataFactory()
    //         ];
    // // dd($viewData);
    //         return view('report.UserInformation', $viewData);
    //     }

    public function index(Request $request)
    {
        // if (!$this->permission->Check('report', 'enabled') || 
        //     !$this->permission->Check('report', 'view_user_information')) {
        //     return Redirect::to('/')->with('error', 'Permission denied');
        // }

        $current_company = $this->company;
        $filter_data = $request->input('filter_data', []);
        $generic_data = $request->input('generic_data', []);

        // Define columns
        $columns = $this->getColumnDefinitions();

        // Get custom user fields
        $oflf = new OtherFieldListFactory();
        $other_field_names = $oflf->getByCompanyIdAndTypeIdArray($current_company->getId(), 10);
        if (is_array($other_field_names)) {
            $columns = Misc::prependArray($columns, $other_field_names);
        }

        // Company Deductions
        $cdlf = new CompanyDeductionListFactory();
        $deduction_columns = $cdlf->getByCompanyIdAndStatusIdArray($current_company->getId(), 10, false);
        $columns = Misc::prependArray($columns, $deduction_columns);

        // Initialize filter data if not set
        $filter_data = $this->initializeFilterData($filter_data);

        // Handle permission hierarchy
        $permission_children_ids = $this->getPermissionChildrenIds();
        $filter_data['permission_children_ids'] = $permission_children_ids;

        $wage_permission_children_ids = $this->getWagePermissionChildrenIds();
        $wage_filter_data['permission_children_ids'] = $wage_permission_children_ids;

        $data = $this->prepareFormData($filter_data, $columns, $generic_data);

        $viewData = [
            'title' => 'Employee Detail Report',
            'data' => $data,
            'ugdf' => new UserGenericDataFactory()
        ];

        return view('report.UserInformation', $viewData);
    }
    public function generate(Request $request)
    {
        // dd($request->all());
        // if (!$this->permission->Check('report', 'enabled') || 
        //     !$this->permission->Check('report', 'view_user_information')) {
        //     return Redirect::to('/')->with('error', 'Permission denied');
        // }

        $filter_data = $request->input('filter_data', []);
        $columns = $this->getColumnDefinitions();

        // Get custom user fields and deductions
        $oflf = new OtherFieldListFactory();
        $other_field_names = $oflf->getByCompanyIdAndTypeIdArray($this->company->getId(), 10);
        if (is_array($other_field_names)) {
            $columns = Misc::prependArray($columns, $other_field_names);
        }

        $cdlf = new CompanyDeductionListFactory();
        $deduction_columns = $cdlf->getByCompanyIdAndStatusIdArray($this->company->getId(), 10, false);
        $columns = Misc::prependArray($columns, $deduction_columns);

        $action = $request->input('action', 'display_report'); // Default to display if no action specified
        return $this->generateReport($filter_data, $columns, $action);
    }
    private function getColumnDefinitions()
    {
        return [
            '-1010-employee_number' => 'Employee #',
            '-1020-status' => 'Status',
            '-1030-user_name' => 'User Name',
            '-1040-phone_id' => 'Phone ID',
            '-1050-ibutton_id' => 'iButton',
            '-1060-first_name' => 'First Name',
            '-1070-middle_name' => 'Middle Name',
            '-1080-last_name' => 'Last Name',
            '-1085-full_name' => 'Full Name',
            '-1087-calling_name' => 'Calling Name',
            '-1090-title' => 'Title',
            '-1099-group' => 'Group',
            '-1100-default_branch' => 'Branch',
            '-1110-default_department' => 'Department',
            '-1112-permission_control' => 'Permission Group',
            '-1115-policy_group' => 'Policy Group',
            '-1118-pay_period_schedule' => 'Pay Period Schedule',
            '-1120-sex' => 'Sex',
            '-1130-address1' => 'Address 1',
            '-1140-address2' => 'Address 2',
            '-1150-city' => 'City',
            '-1160-province' => 'Province/State',
            '-1170-country' => 'Country',
            '-1180-postal_code' => 'Postal Code',
            '-1190-work_phone' => 'Work Phone',
            '-1200-home_phone' => 'Home Phone',
            '-1210-mobile_phone' => 'Mobile Phone',
            '-1220-fax_phone' => 'Fax Phone',
            '-1230-home_email' => 'Home Email',
            '-1240-work_email' => 'Work Email',
            '-1250-birth_date' => 'Birth Date',
            '-1260-hire_date' => 'Appointment Date',
            '-1270-termination_date' => 'Termination Date',
            '-1280-sin' => 'SIN/SSN',
            '-1289-note' => 'Note',
            '-1290-institution' => 'Bank Institution',
            '-1300-transit' => 'Bank Transit/Routing',
            '-1310-account' => 'Bank Account',
            '-1319-currency' => 'Currency',
            '-1320-wage_type' => 'Wage Type',
            '-1330-wage' => 'Wage',
            '-1340-effective_date' => 'Wage Effective Date',
            '-1500-language' => 'Language',
            '-1510-date_format' => 'Date Format',
            '-1520-time_format' => 'Time Format',
            '-1530-time_unit' => 'Time Units',
            '-1540-time_zone' => 'Time Zone',
            '-1550-items_per_page' => 'Rows Per page',
        ];
    }

    private function initializeFilterData($filter_data)
    {
        return array_merge([
            'include_user_ids' => [],
            'exclude_user_ids' => [],
            'user_status_ids' => [],
            'group_ids' => [],
            'branch_ids' => [],
            'department_ids' => [],
            'user_title_ids' => [],
            'column_ids' => []
        ], $filter_data);
    }

    private function getPermissionChildrenIds()
    {
        if (!$this->permission->Check('user', 'view')) {
            $hlf = new HierarchyListFactory();
            $children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID(
                $this->company->getId(),
                View::shared('current_user')->getId()
            );

            if (!$this->permission->Check('user', 'view_child')) {
                $children_ids = [];
            }
            if ($this->permission->Check('user', 'view_own')) {
                $children_ids[] = View::shared('current_user')->getId();
            }
            return $children_ids;
        }
        return [];
    }

    private function getWagePermissionChildrenIds()
    {
        if (!$this->permission->Check('wage', 'view')) {
            $children_ids = [];
            if ($this->permission->Check('wage', 'view_own')) {
                $children_ids[] = View::shared('current_user')->getId();
            }
            return $children_ids;
        }
        return [];
    }

    private function generateReport($filter_data, $columns)
    {
        $current_company = $this->company;
        $ulf = new UserListFactory();
        $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);

        if ($ulf->getRecordCount() > 0) {
            $filter_data['user_ids'] = array_map(function ($u_obj) {
                return $u_obj->id;
            }, iterator_to_array($ulf));

            $report_data = $this->prepareReportData($filter_data);

            // if ($action === 'export') {
            //     return $this->exportToCSV($report_data['rows'], $report_data['filter_columns']);
            // }

            $viewData = [
                'title' => 'Employee Detail Report',
                'generated_time' => TTDate::getTime(),
                'columns' => $report_data['filter_columns'],
                'rows' => $report_data['rows'],
                'company_name' => $current_company->getName()
            ];
            return view('report.UserInformationReport', $viewData);
        }

        return response('No Data To Export!', 200);
    }

    // private function exportCSV($filter_data, $columns)
    // {
    //     $current_company = $this->company;
    //     $ulf = new UserListFactory();
    //     $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);

    //     if ($ulf->getRecordCount() > 0) {
    //         $filter_data['user_ids'] = array_map(function ($u_obj) {
    //             return $u_obj->id;
    //         }, iterator_to_array($ulf));

    //         $report_data = $this->prepareReportData($filter_data);

    //         return $this->exportToCSV($report_data['rows'], $report_data['filter_columns']);
    //     }

    //     return response('No Data To Export!', 200);
    // }

    private function prepareReportData($filter_data)
    {
        $current_company = $this->company;
        $ulf = new UserListFactory();
        $ulf->getReportByCompanyIdAndUserIDList($current_company->getId(), $filter_data['include_user_ids']);

        $options = $this->getReportOptions();
        $wage_data = $this->getWageData($filter_data['include_user_ids']);
        $deduction_data = $this->getDeductionData($filter_data['include_user_ids']);
        $tmp_rows = [];
        foreach ($ulf->rs as $u_obj) {

            $row = $this->buildReportRow($u_obj, $options, $wage_data, $deduction_data);
            if (isset($deduction_data[$u_obj->id])) {
                $row = Misc::prependArray($row, $deduction_data[$u_obj->id]);
            }
            $tmp_rows[] = $row;
        }

        $rows = $this->processRows($tmp_rows, $filter_data);
        $filter_columns = array_intersect_key($this->getColumnDefinitions(), array_flip($filter_data['column_ids']));

        return [
            'rows' => $rows,
            'filter_columns' => $filter_columns
        ];
    }

    private function getReportOptions()
    {
        $current_company = $this->company;
        return [
            'titles' => (new UserTitleListFactory())->getByCompanyIdArray($current_company->getId()),
            'groups' => (new UserGroupListFactory())->getArrayByNodes(FastTree::FormatArray(
                (new UserGroupListFactory())->getByCompanyIdArray($current_company->getId()),
                'no_tree_text',
                true
            )),
            'branches' => (new BranchListFactory())->getByCompanyIdArray($current_company->getId()),
            'departments' => (new DepartmentListFactory())->getByCompanyIdArray($current_company->getId()),
            'permissions' => (new PermissionControlListFactory())->getArrayByListFactory(
                (new PermissionControlListFactory())->getByCompanyId($current_company->getId()),
                true
            ),
            'pay_periods' => (new PayPeriodScheduleListFactory())->getByCompanyIDArray($current_company->getId()),
            'policies' => (new PolicyGroupListFactory())->getByCompanyIDArray($current_company->getId()),
            'currencies' => (new CurrencyListFactory())->getArrayByListFactory(
                (new CurrencyListFactory())->getByCompanyId($current_company->getId()),
                false,
                true
            ),
            'preferences' => $this->getPreferenceOptions()
        ];
    }

    private function getPreferenceOptions()
    {
        $upf = new UserPreferenceFactory();
        return [
            // 'language' => TTi18n::getLanguageArray(),
            'date_format' => $upf->getOptions('date_format'),
            'time_format' => $upf->getOptions('time_format'),
            'time_unit' => $upf->getOptions('time_unit_format'),
            'time_zone' => $upf->getOptions('time_zone')
        ];
    }

    // private function getWageData($user_ids)
    // {
    //     $uwlf = new UserWageListFactory();
    //     $uwlf->getLastWageByUserIdAndDate($user_ids, TTDate::getTime());
    //     $wage_data = [];

    //     foreach ($uwlf->rs as $uw_obj) {

    //     // dd($uw_obj);
    //         $wage_data[$uw_obj->getUser()] = [
    //             'type' => Option::getByKey($uw_obj->getType(), $uw_obj->getOptions('type')),
    //             'wage' => $uw_obj->getWage(),
    //             'effective_date' => $uw_obj->getEffectiveDate()
    //         ];
    //     }

    //     return $wage_data;
    // }

    private function getWageData($user_ids)
    {
        $uwlf = new UserWageListFactory();
        $uwlf->getLastWageByUserIdAndDate($user_ids, TTDate::getTime());
        $wage_data = [];

        foreach ($uwlf->rs as $uw_obj) {
            $wage_data[$uw_obj->user_id] = [
                'type' => Option::getByKey($uw_obj->type_id, $uwlf->getOptions('type')),
                'wage' => $uw_obj->wage,
                'effective_date' => $uw_obj->effective_date
            ];
        }
        return $wage_data;
    }

    private function getDeductionData($user_ids)
    {
        $udlf = new UserDeductionListFactory();
        $udlf->getByCompanyIdAndUserId($this->company->getId(), $user_ids);
        $deduction_data = [];
        foreach ($udlf as $ud_obj) {
            $user_values = $this->getUserValues($ud_obj);
            $deduction_data[$ud_obj->getUser()][$ud_obj->getCompanyDeduction()] =
                implode(' / ', $user_values) ?: 'N/A';
        }
        return $deduction_data;
    }

    private function getUserValues($ud_obj)
    {
        $user_value_1_options = $ud_obj->getCompanyDeductionObject()->getUserValue1Options();
        $user_values = [];

        $tmp_user_value = $ud_obj->getUserValue1() !== false
            ? $ud_obj->getUserValue1()
            : ($ud_obj->getCompanyDeductionObject()->getUserValue1() ?: null);

        $user_values[] = is_array($user_value_1_options)
            ? Option::getByKey($tmp_user_value, $user_value_1_options)
            : $tmp_user_value;

        if ($ud_obj->getUserValue2() !== false) {
            $user_values[] = $ud_obj->getUserValue2();
        } elseif ($ud_obj->getCompanyDeductionObject()->getUserValue2()) {
            $user_values[] = $ud_obj->getCompanyDeductionObject()->getUserValue2();
        }

        if ($ud_obj->getUserValue3() !== false) {
            $user_values[] = $ud_obj->getUserValue3();
        } elseif ($ud_obj->getCompanyDeductionObject()->getUserValue3()) {
            $user_values[] = $ud_obj->getCompanyDeductionObject()->getUserValue3();
        }

        return $user_values;
    }

    // private function buildReportRow($u_obj, $options, $wage_data, $deduction_data)
    // {
    //     $bf = new BankAccountFactory();
    //     $user_wage_data = $wage_data[$u_obj->id] ?? [
    //         'type' => null,
    //         'wage' => null,
    //         'effective_date' => null
    //     ];

    //     $permission_id = $options['permissions'][$u_obj->id] ?? 0;
    //     $policy_id = $options['policies'][$u_obj->id] ?? 0;
    //     $pay_period_id = $options['pay_periods'][$u_obj->id] ?? 0;
    //     $sin = $this->permission->Check('user', 'view_sin')
    //         ? $u_obj->getSIN()
    //         : $u_obj->getSecureSIN();

    //     return [
    //         'employee_number' => $u_obj->getEmployeeNumber(),
    //         'status' => Option::getByKey($u_obj->getStatus(), $u_obj->getOptions('status')),
    //         'user_name' => $u_obj->getUserName(),
    //         'phone_id' => $u_obj->getPhoneID(),
    //         'ibutton_id' => $u_obj->getIButtonID(),
    //         'first_name' => $u_obj->getFirstName(),
    //         'middle_name' => $u_obj->getMiddleName(),
    //         'last_name' => $u_obj->getLastName(),
    //         'calling_name' => $u_obj->getCallingName(),
    //         'full_name' => $u_obj->getFullNameField(),
    //         'title' => Option::getByKey($u_obj->getTitle(), $options['titles']),
    //         'group' => Option::getByKey($u_obj->getGroup(), $options['groups']),
    //         'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $options['branches']),
    //         'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $options['departments']),
    //         'permission_control' => Option::getByKey($permission_id, $options['permissions']),
    //         'policy_group' => Option::getByKey($policy_id, $options['policies']),
    //         'pay_period_schedule' => Option::getByKey($pay_period_id, $options['pay_periods']),
    //         'sex' => Option::getByKey($u_obj->getSex(), $u_obj->getOptions('sex')),
    //         'address1' => $u_obj->getAddress1(),
    //         'address2' => $u_obj->getAddress2(),
    //         'city' => $u_obj->getCity(),
    //         'province' => $u_obj->getProvince(),
    //         'country' => $u_obj->getCountry(),
    //         'postal_code' => $u_obj->getPostalCode(),
    //         'work_phone' => $u_obj->getWorkPhone(),
    //         'home_phone' => $u_obj->getHomePhone(),
    //         'mobile_phone' => $u_obj->getMobilePhone(),
    //         'fax_phone' => $u_obj->getFaxPhone(),
    //         'home_email' => $u_obj->getHomeEmail(),
    //         'work_email' => $u_obj->getWorkEmail(),
    //         'birth_date' => $u_obj->getBirthDate(),
    //         'sin' => $sin,
    //         'hire_date' => $u_obj->getHireDate(),
    //         'termination_date' => $u_obj->getTerminationDate(),
    //         'note' => $u_obj->getNote(),
    //         'institution' => $u_obj->getColumn('institution'),
    //         'transit' => $u_obj->getColumn('transit'),
    //         'account' => $bf->getSecureAccount($u_obj->getColumn('account')),
    //         'currency' => Option::getByKey($u_obj->getCurrency(), $options['currencies']),
    //         'wage_type' => $user_wage_data['type'],
    //         'wage' => $user_wage_data['wage'],
    //         'effective_date' => $user_wage_data['effective_date']
    //     ];
    // }

    private function buildReportRow($u_obj, $options, $wage_data, $deduction_data)
    {
        $bf = new BankAccountFactory();

        $user_wage_data = $wage_data[$u_obj->id] ?? [
            'type' => null,
            'wage' => null,
            'effective_date' => null
        ];

        $permission_id = $options['permissions'][$u_obj->id] ?? 0;
        $policy_id = $options['policies'][$u_obj->id] ?? 0;
        $pay_period_id = $options['pay_periods'][$u_obj->id] ?? 0;

        // SIN: use direct access since getSIN() / getSecureSIN() don't exist
        $sin = $this->permission->Check('user', 'view_sin')
            ? $u_obj->sin
            : '***'; // or mask it however you prefer

        return [
            'employee_number' => $u_obj->employee_number,
            'status' => Option::getByKey($u_obj->status_id, $u_obj->status ?? []),
            'user_name' => $u_obj->user_name,
            'phone_id' => $u_obj->phone_id,
            'ibutton_id' => $u_obj->ibutton_id,
            'first_name' => $u_obj->first_name,
            'middle_name' => $u_obj->middle_name,
            'last_name' => $u_obj->last_name,
            'calling_name' => $u_obj->calling_name,
            'full_name' => $u_obj->full_name,
            'title' => Option::getByKey($u_obj->title_id, $options['titles']),
            'group' => Option::getByKey($u_obj->group_id, $options['groups']),
            'default_branch' => Option::getByKey($u_obj->default_branch_id, $options['branches']),
            'default_department' => Option::getByKey($u_obj->default_department_id, $options['departments']),
            'permission_control' => Option::getByKey($permission_id, $options['permissions']),
            'policy_group' => Option::getByKey($policy_id, $options['policies']),
            'pay_period_schedule' => Option::getByKey($pay_period_id, $options['pay_periods']),
            'sex' => Option::getByKey($u_obj->sex_id, $u_obj->sex ?? []),
            'address1' => $u_obj->address1,
            'address2' => $u_obj->address2,
            'city' => $u_obj->city,
            'province' => $u_obj->province,
            'country' => $u_obj->country,
            'postal_code' => $u_obj->postal_code,
            'work_phone' => $u_obj->work_phone,
            'home_phone' => $u_obj->home_phone,
            'mobile_phone' => $u_obj->mobile_phone,
            'fax_phone' => $u_obj->fax_phone,
            'home_email' => $u_obj->home_email,
            'work_email' => $u_obj->work_email,
            'birth_date' => $u_obj->birth_date,
            'sin' => $sin,
            'hire_date' => $u_obj->hire_date,
            'termination_date' => $u_obj->termination_date,
            'note' => $u_obj->note,
            'institution' => $u_obj->institution,
            'transit' => $u_obj->transit,
            'account' => $bf->getSecureAccount($u_obj->account),
            'currency' => Option::getByKey($u_obj->currency_id, $options['currencies']),
            'wage_type' => $user_wage_data['type'],
            'wage' => $user_wage_data['wage'],
            'effective_date' => $user_wage_data['effective_date']
        ];
    }


    // private function processRows($tmp_rows, $filter_data)
    // {
    //     $rows = [];
    //     $sorted_rows = Sort::Multisort(
    //         // $tmp_rows,
    //         Misc::trimSortPrefix($filter_data['primary_sort'] ?? '-1080-last_name'),
    //         Misc::trimSortPrefix($filter_data['secondary_sort'] ?? '-1160-province'),
    //         $filter_data['primary_sort_dir'] ?? 'asc',
    //         $filter_data['secondary_sort_dir'] ?? 'asc'
    //     );

    //     foreach ($sorted_rows as $tmp_row) {
    //         $row_columns = [];
    //         foreach ($tmp_row as $column => $value) {
    //             if ($value !== '' && strpos($column, '_date') !== false) {
    //                 $value = TTDate::getDate('DATE', $value);
    //             }
    //             $row_columns[$column] = $value;
    //         }
    //         $rows[] = $row_columns;
    //     }
    //     return $rows;
    // }

    private function processRows($tmp_rows, $filter_data)
    {
        $rows = [];

        // Debug input
        Debug::Arr($tmp_rows, 'Input Rows', __FILE__, __LINE__, __METHOD__, 10);
        if (!empty($tmp_rows)) {
            Debug::Arr(array_keys($tmp_rows[0] ?? []), 'Row Keys', __FILE__, __LINE__, __METHOD__, 10);
        }

        $sorted_rows = Sort::Multisort(
            $tmp_rows,
            Misc::trimSortPrefix($filter_data['primary_sort'] ?? '-1080-last_name'),
            Misc::trimSortPrefix($filter_data['secondary_sort'] ?? '-1160-province'),
            $filter_data['primary_sort_dir'] ?? 'asc',
            $filter_data['secondary_sort_dir'] ?? 'asc'
        );

        // Debug output
        Debug::Arr($sorted_rows, 'Sorted Rows', __FILE__, __LINE__, __METHOD__, 10);

        foreach ($sorted_rows as $tmp_row) {
            $row_columns = [];
            foreach ($tmp_row as $column => $value) {
                if ($value !== '' && strpos($column, '_date') !== false) {
                    $value = TTDate::getDate('DATE', $value);
                }
                $row_columns[$column] = $value;
            }
            $rows[] = $row_columns;
        }

        return $rows;
    }

    private function exportToCSV($rows, $filter_columns)
    {
        $csv = Misc::Array2CSV($rows, $filter_columns, false);
        return response($csv)
            ->header('Content-Type', 'application/csv')
            ->header('Content-Disposition', 'attachment; filename="report.csv"')
            ->header('Content-Length', strlen($csv));
    }

    private function prepareFormData($filter_data, $columns, $generic_data)
    {
        $current_company = $this->company;
        $all_array_option = ['-1' => '-- All --'];

        // User options
        $ulf = new UserListFactory();
        $ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), [
            'permission_children_ids' => $filter_data['permission_children_ids']
        ]);
        $user_options = $ulf->getArrayByListFactory($ulf, false, true);

        $data = [
            'include_user_options' => Misc::arrayDiffByKey($filter_data['include_user_ids'] ?? [], $user_options),
            'selected_include_user_options' => Misc::arrayIntersectByKey($filter_data['include_user_ids'] ?? [], $user_options),
            'exclude_user_options' => Misc::arrayDiffByKey($filter_data['exclude_user_ids'] ?? [], $user_options),
            'selected_exclude_user_options' => Misc::arrayIntersectByKey($filter_data['exclude_user_ids'] ?? [], $user_options),
            'user_status_options' => Misc::prependArray($all_array_option, $ulf->getOptions('status')),
            'selected_user_status_options' => Misc::arrayIntersectByKey($filter_data['user_status_ids'] ?? [], $ulf->getOptions('status'))
        ];

        // Group options
        $uglf = new UserGroupListFactory();
        $group_options = Misc::prependArray($all_array_option, $uglf->getArrayByNodes(
            FastTree::FormatArray($uglf->getByCompanyIdArray($current_company->getId()), 'TEXT', true)
        ));
        $data['group_options'] = Misc::arrayDiffByKey($filter_data['group_ids'] ?? [], $group_options);
        $data['selected_group_options'] = Misc::arrayIntersectByKey($filter_data['group_ids'] ?? [], $group_options);

        // Branch options
        $blf = new BranchListFactory();
        $blf->getByCompanyId($current_company->getId());
        $branch_options = Misc::prependArray($all_array_option, $blf->getArrayByListFactory($blf, false, true));
        $data['branch_options'] = Misc::arrayDiffByKey($filter_data['branch_ids'] ?? [], $branch_options);
        $data['selected_branch_options'] = Misc::arrayIntersectByKey($filter_data['branch_ids'] ?? [], $branch_options);

        // Department options
        $dlf = new DepartmentListFactory();
        $dlf->getByCompanyId($current_company->getId());
        $department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, false, true));
        $data['department_options'] = Misc::arrayDiffByKey($filter_data['department_ids'] ?? [], $department_options);
        $data['selected_department_options'] = Misc::arrayIntersectByKey($filter_data['department_ids'] ?? [], $department_options);

        // Title options
        $utlf = new UserTitleListFactory();
        $utlf->getByCompanyId($current_company->getId());
        $title_options = Misc::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, false, true));
        $data['title_options'] = Misc::arrayDiffByKey($filter_data['user_title_ids'] ?? [], $title_options);
        $data['selected_title_options'] = Misc::arrayIntersectByKey($filter_data['user_title_ids'] ?? [], $title_options);

        // Column options
        $data['column_options'] = Misc::arrayDiffByKey($filter_data['column_ids'] ?? [], $columns);
        $data['selected_column_options'] = Misc::arrayIntersectByKey($filter_data['column_ids'] ?? [], $columns);

        // Sort options
        $data['sort_options'] = array_merge($columns, ['effective_date_order' => 'Wage Effective Date']);
        unset($data['sort_options']['effective_date']);
        $data['sort_direction_options'] = Misc::getSortDirectionArray();

        // Saved reports
        $ugdlf = new UserGenericDataListFactory();
        $data['saved_report_options'] = $ugdlf->getByUserIdAndScriptArray(
            View::shared('current_user')->getId(),
            request()->url()
        );
        $data['generic_data'] = $generic_data;
        $data['filter_data'] = $filter_data;

        return $data;
    }
}
