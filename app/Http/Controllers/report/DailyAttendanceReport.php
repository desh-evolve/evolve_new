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
use App\Models\payperiod\PayPeriodListFactory;
use App\Models\policy\OverTimePolicyListFactory;
use App\Models\policy\PremiumPolicyListFactory;
use App\Models\policy\AbsencePolicyListFactory;
use App\Models\payperiod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\punch\PunchListFactory;
use App\Models\schedule\ScheduleListFactory;
use Illuminate\Support\Facades\View;
use App\Models\core\Option;
use App\Models\core\Sort;
use App\Models\core\FastTree;
use App\Models\core\TTi18n;
use App\Models\users\UserGenericDataListFactory;
use App\Models\users\UserGenericDataFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\users\UserWageListFactory;
use App\Models\core\UserDateTotalListFactory;
use App\Models\Core\TTPDF;
use DateTime;
use PDF;

class DailyAttendanceReport extends Controller
{
	protected $permission;
	protected $company;
	protected $userPrefs;

	public function __construct()
	{
		$basePath = Environment::getBasePath();
		require_once($basePath . '/app/Helpers/global.inc.php');
		require_once($basePath . '/app/Helpers/Interface.inc.php');

		$this->userPrefs = View::shared('current_user');
		$this->company = View::shared('current_company');
		$this->permission = View::shared('permission');
	}

	

	public function index(Request $request)
	{
		$filter_data = $request->input('filter_data', []);
		$generic_data = $request->input('generic_data', []);

		$columns = $this->getColumnDefinitions();
		$columns = $this->appendPolicyColumns($columns);
		$pay_period_options = $this->getPayPeriodOptions();
		$filter_data = $this->initializeFilterData($filter_data, $pay_period_options);
		$permission_children_ids = $this->getPermissionChildrenIds();
		$filter_data['permission_children_ids'] = $permission_children_ids;

		$data = $this->prepareFormData($filter_data, $columns, $generic_data, $pay_period_options);



		$viewData = [
			'title' => 'Daily Attendance Report',
			'data' => $data,
			'ugdf' => new UserGenericDataFactory()
		];
		// dd($viewData);
		return view('report.DailyAttendance', $viewData);
	}

	public function generate(Request $request)
	{
		// dd($request->all());
		$filter_data = $request->input('filter_data', []);
		$action = $request->input('action', 'display_report');
		$columns = $this->getColumnDefinitions();
		$columns = $this->appendPolicyColumns($columns);

		if (isset($filter_data['print_timesheet']) && $filter_data['print_timesheet'] >= 1) {
			if (
				!$this->permission->Check('punch', 'enabled') ||
				!($this->permission->Check('punch', 'view') ||
					$this->permission->Check('punch', 'view_own') ||
					$this->permission->Check('punch', 'view_child'))
			) {
				return Redirect::to('/')->with('error', 'Permission denied');
			}

			if (
				!isset($filter_data['include_user_ids']) ||
				!($this->permission->Check('punch', 'view') ||
					$this->permission->Check('punch', 'view_child'))
			) {
				$filter_data['include_user_ids'] = $this->userPrefs->getId();
			}

			$action = $filter_data['print_timesheet'] == 2 ? 'display_detailed_timesheet' : 'display_timesheet';
			$filter_data = [
				'permission_children_ids' => [(int)$filter_data['include_user_ids']],
				'pay_period_ids' => [(int)$filter_data['pay_period_ids']],
				'date_type' => 'pay_period_ids',
				'primary_sort' => '-1000-date_stamp',
				'secondary_sort' => null,
				'primary_sort_dir' => 'asc',
				'secondary_sort_dir' => null,
				'column_ids' => array_keys($this->getStaticColumns())
			];
		} else {
			if (
				!$this->permission->Check('report', 'enabled') ||
				!$this->permission->Check('report', 'view_timesheet_summary')
			) {
				return Redirect::to('/')->with('error', 'Permission denied');
			}
		}

		return $this->generateReport($filter_data, $columns, $action);
	}

	private function getStaticColumns()
	{
		return [
			'-1000-date_stamp' => 'Date',
			'-1050-min_punch_time_stamp' => 'First In Punch',
			'-1060-max_punch_time_stamp' => 'Last Out Punch',
		];
	}

	private function getColumnDefinitions()
	{
		$static_columns = $this->getStaticColumns();
		$dynamic_columns = [
			'-1070-schedule_working' => 'Scheduled Time',
			'-1080-schedule_absence' => 'Scheduled Absence',
			'-1090-worked_time' => 'Worked Time',
			'-1100-actual_time' => 'Actual Time',
			'-1110-actual_time_diff' => 'Actual Time Difference',
			'-1120-actual_time_diff_wage' => 'Actual Time Difference Wage',
			'-1130-paid_time' => 'Paid Time',
			'-1140-regular_time' => 'Regular Time',
			'-1150-over_time' => 'Total Over Time',
			'-1160-absence_time' => 'Total Absence Time',
		];

		return Misc::prependArray($static_columns, $dynamic_columns);
	}

	private function appendPolicyColumns($columns)
	{
		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId($this->company->getId());
		$otp_columns = [];
		foreach ($otplf as $otp_obj) {
			$otp_columns['over_time_policy-' . $otp_obj->getId()] = $otp_obj->getName();
		}
		$columns = array_merge($columns, $otp_columns);

		$pplf = new PremiumPolicyListFactory();
		$pplf->getByCompanyId($this->company->getId());
		$pp_columns = [];
		foreach ($pplf as $pp_obj) {
			$pp_columns['premium_policy-' . $pp_obj->getId()] = $pp_obj->getName();
		}
		$columns = array_merge($columns, $pp_columns);

		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId($this->company->getId());
		$ap_columns = [];
		foreach ($aplf as $ap_obj) {
			$ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
		}
		$columns = array_merge($columns, $ap_columns);

		return $columns;
	}

	private function getPayPeriodOptions()
	{
		$pplf = new PayPeriodListFactory();
		$pplf->getByCompanyId($this->company->getId());
		$pay_period_ids = [];
		$pay_period_end_dates = [];
		$default_start_date = null;
		$default_end_date = null;
		$pp = 0;
		// dd($pplf);
		foreach ($pplf->rs as $pay_period_obj) {
			$pay_period_ids[] = $pay_period_obj->id;
			$pay_period_end_dates[$pay_period_obj->id] = $pay_period_obj->end_date;
			if ($pp == 0) {
				$default_start_date = $pay_period_obj->start_date;
				$default_end_date = $pay_period_obj->end_date;
			}
			$pp++;
		}

		$pplf = new PayPeriodListFactory();
		$pay_period_options = $pplf->getByIdListArray($pay_period_ids, null, ['start_date' => 'desc'], false);

		return [
			'options' => $pay_period_options,
			'end_dates' => $pay_period_end_dates,
			'default_start_date' => $default_start_date,
			'default_end_date' => $default_end_date
		];
	}

	private function initializeFilterData($filter_data, $pay_period_options)
	{
		$default_data = [
			'include_user_ids' => [],
			'exclude_user_ids' => [],
			'user_status_ids' => [-1],
			'group_ids' => [-1],
			'branch_ids' => [-1],
			'department_ids' => [-1],
			'user_title_ids' => [-1],
			'pay_period_ids' => ['-0000-' . array_key_first((array)$pay_period_options['options'])],
			'start_date' => $pay_period_options['default_start_date'],
			'end_date' => $pay_period_options['default_end_date'],
			'column_ids' => [
				'-1000-date_stamp',
				'-1090-worked_time',
				'-1130-paid_time',
				'-1140-regular_time'
			],
			'primary_sort' => '-1000-date_stamp',
			'secondary_sort' => '-1090-worked_time',
			'date_type' => 'date', // Add default date_type
		];

		$filter_data = array_merge($default_data, $filter_data);

		if (isset($filter_data['start_date'])) {
			$filter_data['start_date'] = TTDate::parseDateTime($filter_data['start_date']);
		}
		if (isset($filter_data['end_date'])) {
			$filter_data['end_date'] = TTDate::parseDateTime($filter_data['end_date']);
		}

		return Misc::preSetArrayValues($filter_data, [
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
	}

	private function getPermissionChildrenIds()
	{
		$permission_children_ids = [];
		$wage_permission_children_ids = [];

		if (!$this->permission->Check('punch', 'view')) {
			$hlf = new HierarchyListFactory();
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID(
				$this->company->getId(),
				$this->userPrefs->getId()
			);

			if (!$this->permission->Check('punch', 'view_child')) {
				$permission_children_ids = [];
			}
			if ($this->permission->Check('punch', 'view_own')) {
				$permission_children_ids[] = $this->userPrefs->getId();
			}
		}

		if (!$this->permission->Check('wage', 'view')) {
			if (!$this->permission->Check('wage', 'view_child')) {
				$wage_permission_children_ids = [];
			}
			if ($this->permission->Check('wage', 'view_own')) {
				$wage_permission_children_ids[] = $this->userPrefs->getId();
			}
		}

		return $permission_children_ids;
	}

	private function generateReport($filter_data, $columns, $action)
	{
		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria($this->company->getId(), $filter_data);
		if ($ulf->getRecordCount() > 0) {
			if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
				unset($filter_data['start_date'], $filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			$filter_data['user_id'] = array_map(function ($u_obj) {
				return $u_obj->getId();
			}, iterator_to_array($ulf));

			if (isset($filter_data['pay_period_ids'])) {
				$filter_data['pay_period_ids'] = array_map([Misc::class, 'trimSortPrefix'], $filter_data['pay_period_ids']);
			}

			$report_data = $this->prepareReportData($filter_data, $columns);

			if ($action == 'display_timesheet' || $action == 'display_detailed_timesheet') {
				return $this->generateTimesheetPDF($report_data['rows'], $action, $filter_data);
			} elseif ($action == 'export') {
				return $this->exportReport($report_data, $filter_data, $columns);
			}

			$viewData = [
				'title' => 'Daily Attendance Report',
				'generated_time' => TTDate::getTime(),
				'pay_period_options' => $this->getPayPeriodOptions()['options'],
				'filter_data' => $filter_data,
				'columns' => $report_data['filter_columns'],
				'rows' => $report_data['rows']
			];
			// dd($viewData);
			return view('report.DailyAttendanceReport', $viewData);
		}

		return response('No Data To Export!', 200);
	}

	private function prepareReportData($filter_data, $columns)
	{
		$pay_period_options = $this->getPayPeriodOptions();
		$end_date = $this->calculateEndDate($filter_data, $pay_period_options['end_dates']);

		$wage_filter_data = $this->permission->Check('wage', 'view')
			? ['permission_children_ids' => $filter_data['include_user_ids']]
			: ['permission_children_ids' => $this->getPermissionChildrenIds()];

		$user_wage = $this->getUserWageData($wage_filter_data['permission_children_ids'], $end_date);
		$schedule_rows = $this->getScheduleData($filter_data);
		$udt_data = $this->getUserDateTotalData($filter_data);
		$punch_rows = $this->getPunchData($filter_data);
		$verified_time_sheets = $this->getVerifiedTimeSheets($filter_data);
		$options = $this->getReportOptions();
		$tmp_rows = $this->buildReportRows($udt_data, $schedule_rows, $user_wage, $filter_data);
		$rows = $this->processReportRows($tmp_rows, $filter_data, $options, $punch_rows, $verified_time_sheets);

		$filter_columns = array_intersect_key($columns, array_flip($filter_data['column_ids']));

		return [
			'rows' => $rows,
			'filter_columns' => $filter_columns
		];
	}

	private function calculateEndDate($filter_data, $pay_period_end_dates)
	{
		if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
			if (in_array('-1', $filter_data['pay_period_ids'])) {
				return time();
			}

			$end_date = null;
			foreach ($filter_data['pay_period_ids'] as $pay_period_id) {
				$pay_period_id = Misc::trimSortPrefix($pay_period_id);
				if (isset($pay_period_end_dates[$pay_period_id])) {
					$end_date = $end_date === null ? $pay_period_end_dates[$pay_period_id] : max($end_date, $pay_period_end_dates[$pay_period_id]);
				}
			}
			return $end_date ?: time();
		}

		return $filter_data['end_date'];
	}

	private function getUserWageData($user_ids, $end_date)
	{
		// dd($user_ids, $end_date);
		$uwlf = new UserWageListFactory();
		$uwlf->getLastWageByUserIdAndDate($user_ids, $end_date);
		$user_wage = [];

		foreach ($uwlf->rs as $uw_obj) {
			$uwlf->data = (array)$uw_obj;
			$uw_obj = $uwlf;
			$user_wage[$uw_obj->getUser()] = $uw_obj->getBaseCurrencyHourlyRate($uw_obj->getHourlyRate());
		}

		return $user_wage;
	}

	private function getScheduleData($filter_data)
	{

		$current_company = $this->company;
		$slf = new ScheduleListFactory();
		$schedule_rows = [];

		if (isset($filter_data['include_user_ids'])) {
			$slf->getDayReportByCompanyIdAndArrayCriteria($current_company->getId(), $filter_data);
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

		return $schedule_rows;
	}

	private function getUserDateTotalData($filter_data)
	{
		$udtlf = new UserDateTotalListFactory();
		$udt_data = [];

		if (isset($filter_data['include_user_ids'])) {
			$udtlf->getDayReportByCompanyIdAndArrayCriteria($this->company->getId(), $filter_data);
			// foreach ($udtlf as $udt_obj) {
			foreach ($udtlf->rs as $udt_obj) {
				$udtlf->data = (array)$udt_obj;
				$udt_obj = $udtlf;
				$udt_data[] = $udt_obj;
			}
		}

		return $udt_data;
	}

	private function getPunchData($filter_data)
	{
		$punch_rows = [];
		$plf = new PunchListFactory();
		$plf->getSearchByCompanyIdAndArrayCriteria($this->company->getId(), $filter_data);
		// foreach ($plf as $p_obj) {
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

		return $punch_rows;
	}

	private function getVerifiedTimeSheets($filter_data)
	{
		$verified_time_sheets = null;

		if (isset($filter_data['pay_period_ids']) && count($filter_data['pay_period_ids']) > 0) {
			$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
			$pptsvlf->getByPayPeriodIdAndCompanyId($filter_data['pay_period_ids'][0], $this->company->getId());
			// foreach ($pptsvlf as $pptsv_obj) {
			foreach ($pptsvlf->rs as $pptsv_obj) {
				$pptsvlf->data = (array)$pptsv_obj;
				$pptsv_obj = $pptsvlf;
				$verified_time_sheets[$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = [
					'status_id' => $pptsv_obj->getStatus(),
					'created_date' => $pptsv_obj->getCreatedDate()
				];
			}
		}

		return $verified_time_sheets;
	}

	private function getReportOptions()
	{
		$utlf = new UserTitleListFactory();
		$blf = new BranchListFactory();
		$dlf = new DepartmentListFactory();
		$uglf = new UserGroupListFactory();

		return [
			'titles' => $utlf->getByCompanyIdArray($this->company->getId()),
			'branches' => $blf->getByCompanyIdArray($this->company->getId()),
			'departments' => $dlf->getByCompanyIdArray($this->company->getId()),

			'groups' => $uglf->getArrayByNodes(FastTree::FormatArray(
				$uglf->getByCompanyIdArray($this->company->getId()),
				'no_tree_text',
				true
			))
		];
	}

	private function buildReportRows($udt_data, $schedule_rows, $user_wage, $filter_data)
	{
		$tmp_rows = [];
		// foreach ($udt_data as $udt_obj) {
		
			
		foreach ($udt_data as $udt_obj) {
			
			$user_id = $udt_obj->getColumn('id');
			$pay_period_id = $udt_obj->getColumn('pay_period_id');
			
			$date_stamp = TTDate::strtotime($udt_obj->getColumn('date_stamp'));
			$status_id = $udt_obj->getColumn('status_id');
			$type_id = $udt_obj->getColumn('type_id');

			$column = $this->determineColumn($status_id, $type_id, $udt_obj);
			$category = $policy_id = 0;

			if ($column == 'paid_time') {
				$category = $column;
			} elseif ($column == 'regular_time') {
				$category = $column;
			} elseif (str_starts_with($column, 'over_time_policy-')) {
				$category = 'over_time_policy';
				$policy_id = $udt_obj->getColumn('over_time_policy_id');
			} elseif (str_starts_with($column, 'premium_policy-')) {
				$category = 'premium_policy';
				$policy_id = $udt_obj->getColumn('premium_policy_id');
			} elseif (str_starts_with($column, 'absence_policy-')) {
				$category = 'absence_policy';
				$policy_id = $udt_obj->getColumn('absence_policy_id');
			} elseif ($column == 'worked_time') {
				$category = $column;
			}

			if ($column == 'worked_time') {
				$this->handleWorkedTime($tmp_rows, $pay_period_id, $user_id, $date_stamp, $udt_obj, $user_wage);
			} elseif ($column !== null && $udt_obj->getColumn('total_time') > 0) {
				$this->handleNonWorkedTime($tmp_rows, $pay_period_id, $user_id, $date_stamp, $udt_obj, $status_id, $type_id, $column, $category, $policy_id);
			}

			$this->addScheduleData($tmp_rows, $schedule_rows, $pay_period_id, $user_id, $date_stamp);
			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['min_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('min_punch_time_stamp'));
			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['max_punch_time_stamp'] = TTDate::strtotime($udt_obj->getColumn('max_punch_time_stamp'));
		}

		return $tmp_rows;
	}

	private function determineColumn($status_id, $type_id, $udt_obj)
	{
		if ($status_id == 10 && $type_id == 10) {
			return 'paid_time';
		} elseif ($status_id == 10 && $type_id == 20) {
			return 'regular_time';
		} elseif ($status_id == 10 && $type_id == 30) {
			return 'over_time_policy-' . $udt_obj->getColumn('over_time_policy_id');
		} elseif ($status_id == 10 && $type_id == 40) {
			return 'premium_policy-' . $udt_obj->getColumn('premium_policy_id');
		} elseif ($status_id == 30 && $type_id == 10) {
			return 'absence_policy-' . $udt_obj->getColumn('absence_policy_id');
		} elseif (($status_id == 20 && $type_id == 10) || ($status_id == 10 && $type_id == 100)) {
			return 'worked_time';
		}
		return null;
	}

	private function handleWorkedTime(&$tmp_rows, $pay_period_id, $user_id, $date_stamp, $udt_obj, $user_wage)
	{
		$total_time = (int)$udt_obj->getColumn('total_time');
		$actual_total_time = $udt_obj->getColumn('actual_total_time');
		$actual_time_diff = bcsub($actual_total_time, $total_time);

		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['worked_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['worked_time'] ?? 0) + $total_time;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time'] ?? 0) + $actual_total_time;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff'] ?? 0) + $actual_time_diff;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['actual_time_diff_wage'] = isset($user_wage[$user_id])
			? Misc::MoneyFormat(bcmul(TTDate::getHours($actual_time_diff), $user_wage[$user_id]), false)
			: Misc::MoneyFormat(0, false);
	}

	private function handleNonWorkedTime(&$tmp_rows, $pay_period_id, $user_id, $date_stamp, $udt_obj, $status_id, $type_id, $column, $category, $policy_id)
	{
		$total_time = $udt_obj->getColumn('total_time');

		if ($status_id == 30 && $type_id == 10) {
			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['absence_time'] ?? 0) + $total_time;
		}

		if ($status_id == 10 && $type_id == 30) {
			$tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['over_time'] ?? 0) + $total_time;
		}

		$tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp][$column] ?? 0) + $total_time;

		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] = ($tmp_rows[$pay_period_id][$user_id][$date_stamp]['categorized_time'][$category][$policy_id] ?? 0) + $total_time;
	}

	private function addScheduleData(&$tmp_rows, $schedule_rows, $pay_period_id, $user_id, $date_stamp)
	{
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_working'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['working'] ?? null;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_absence'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['absence'] ?? null;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_start_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['start_time'] ?? null;
		$tmp_rows[$pay_period_id][$user_id][$date_stamp]['schedule_end_time'] = $schedule_rows[$pay_period_id][$user_id][$date_stamp]['end_time'] ?? null;
	}

	private function processReportRows($tmp_rows, $filter_data, $options, $punch_rows, $verified_time_sheets)
	{
		// dd($tmp_rows, $filter_data, $options, $punch_rows, $verified_time_sheets);
		$rows = [];
		$ulf = new UserListFactory();
		$pay_period_options = $this->getPayPeriodOptions()['options'];

		foreach ($tmp_rows as $pay_period_id => $data_a) {
			foreach ($data_a as $user_id => $data_b) {
				$user_obj = $ulf->getById($user_id)->getCurrent();
				$row = [
					'pay_period' => $pay_period_options[$pay_period_id] ?? 'N/A',
					'pay_period_id' => $pay_period_id,
					'user_id' => $user_id,
					'first_name' => $user_obj->getFirstName(),
					'last_name' => $user_obj->getLastName(),
					'full_name' => $user_obj->getFullName(true),
					'employee_number' => $user_obj->getEmployeeNumber(),
					'province' => $user_obj->getProvince(),
					'country' => $user_obj->getCountry(),
					'group' => Option::getByKey($user_obj->getGroup(), $options['groups'], null),
					'title' => Option::getByKey($user_obj->getTitle(), $options['titles'], null),
					'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $options['branches'], null),
					'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $options['departments'], null),
					'verified_time_sheet' => $this->determineVerifiedTimeSheetStatus($verified_time_sheets, $user_id, $pay_period_id),
					'verified_time_sheet_date' => $verified_time_sheets[$user_id][$pay_period_id]['created_date'] ?? false
				];

				$sub_rows = $this->processSubRows($data_b, $filter_data, $punch_rows, $pay_period_id, $user_id);

				$row['data'] = $sub_rows;
				$rows[] = $row;
			}
		}
		return $rows;
	}

	private function determineVerifiedTimeSheetStatus($verified_time_sheets, $user_id, $pay_period_id)
	{
		if ($verified_time_sheets !== null && isset($verified_time_sheets[$user_id][$pay_period_id])) {
			if ($verified_time_sheets[$user_id][$pay_period_id]['status_id'] == 50) {
				return TTi18n::gettext('Yes');
			} elseif (in_array($verified_time_sheets[$user_id][$pay_period_id]['status_id'], [30, 45])) {
				return TTi18n::gettext('Pending');
			}
			return TTi18n::gettext('Declined');
		}
		return TTi18n::gettext('No');
	}

	private function processSubRows($data_b, $filter_data, $punch_rows, $pay_period_id, $user_id)
	{
		$sub_rows = [];
		$static_columns = $this->getStaticColumns();

		foreach ($data_b as $date_stamp => $data_c) {
			$sub_row = ['date_stamp' => $date_stamp];
			foreach ($data_c as $column => $total_time) {
				$sub_row[$column] = $total_time;
			}
			$sub_rows[] = $sub_row;
		}

		$sub_rows = Sort::Multisort(
			$sub_rows,
			Misc::trimSortPrefix($filter_data['primary_sort'] ?? '-1000-date_stamp'),
			Misc::trimSortPrefix($filter_data['secondary_sort'] ?? '-1090-worked_time'),
			$filter_data['primary_sort_dir'] ?? 'asc',
			$filter_data['secondary_sort_dir'] ?? 'asc'
		);

		$total_sub_row = Misc::ArrayAssocSum($sub_rows, null, 2);
		$sub_rows[] = array_merge($total_sub_row, array_fill_keys(array_keys(Misc::trimSortPrefix($static_columns)), null));

		$trimmed_static_columns = array_keys(Misc::trimSortPrefix($static_columns));
		$processed_sub_rows = [];
		foreach ($sub_rows as $sub_row) {
			$sub_row_columns = [];
			foreach ($sub_row as $column => $column_data) {
				if (in_array($column, ['schedule_start_time', 'schedule_end_time'])) {
					$column_data = substr($column_data, 11, 5);
				} elseif ($column == 'date_stamp') {
					$column_data = TTDate::getDate('DATE', $column_data);
				} elseif (in_array($column, ['min_punch_time_stamp', 'max_punch_time_stamp'])) {
					$column_data = TTDate::getDate('TIME', $column_data);
				} elseif (!str_contains($column, 'wage') && !in_array($column, $trimmed_static_columns)) {
					$column_data = TTDate::getTimeUnit($column_data);
				}
				$sub_row_columns[$column] = $column_data;
			}
			$processed_sub_rows[] = $sub_row_columns;
		}

		return $processed_sub_rows;
	}

	private function generateTimesheetPDF($rows, $action, $filter_data)
	{
		$pdf = new TTPDF('P', 'mm', 'Letter');
		$pdf->setMargins(10, 5);
		$pdf->SetAutoPageBreak($action == 'display_detailed_timesheet');
		$pdf->SetFont('freeserif', '', 10);
		$border = 0;
		$pdf_created_date = time();

		foreach ($rows as $user_data) {
			$pdf->AddPage();
			$adjust_x = 10;
			$adjust_y = 10;

			$pdf->SetFont('', 'B', $action == 'display_detailed_timesheet' ? 22 : 32);
			$pdf->Cell(200, $action == 'display_detailed_timesheet' ? 8 : 15, TTi18n::gettext($action == 'display_detailed_timesheet' ? 'Detailed Employee TimeSheet' : 'Employee TimeSheet'), $border, 0, 'C');
			$pdf->Ln();
			$pdf->SetFont('', 'B', 12);
			$pdf->Cell(200, 5, $this->company->getName(), $border, 0, 'C');
			$pdf->Ln($action == 'display_detailed_timesheet' ? 8 : 10);

			$pdf->Rect($pdf->getX(), $pdf->getY() - ($action == 'display_detailed_timesheet' ? 1 : 2), 200, $action == 'display_detailed_timesheet' ? 14 : 19);

			$pdf->SetFont('', '', $action == 'display_detailed_timesheet' ? 10 : 12);
			$pdf->Cell(30, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Employee:'), $border, 0, 'R');
			$pdf->SetFont('', 'B', $action == 'display_detailed_timesheet' ? 10 : 12);
			$pdf->Cell(70, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['first_name'] . ' ' . $user_data['last_name'] . ' (#' . $user_data['employee_number'] . ')', $border, 0, 'L');

			$pdf->SetFont('', '', $action == 'display_detailed_timesheet' ? 10 : 12);
			$pdf->Cell(40, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Pay Period:'), $border, 0, 'R');
			$pdf->SetFont('', 'B', $action == 'display_detailed_timesheet' ? 10 : 12);
			$pdf->Cell(60, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['pay_period'], $border, 0, 'L');
			$pdf->Ln();

			$pdf->SetFont('', '', $action == 'display_detailed_timesheet' ? 10 : 12);
			$pdf->Cell(30, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Title:'), $border, 0, 'R');
			$pdf->Cell(70, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['title'], $border, 0, 'L');
			$pdf->Cell(40, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Branch:'), $border, 0, 'R');
			$pdf->Cell(60, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['default_branch'], $border, 0, 'L');
			$pdf->Ln();

			$pdf->Cell(30, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Group:'), $border, 0, 'R');
			$pdf->Cell(70, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['group'], $border, 0, 'L');
			$pdf->Cell(40, $action == 'display_detailed_timesheet' ? 4 : 5, TTi18n::gettext('Department:'), $border, 0, 'R');
			$pdf->Cell(60, $action == 'display_detailed_timesheet' ? 4 : 5, $user_data['default_department'], $border, 0, 'L');
			$pdf->Ln($action == 'display_detailed_timesheet' ? 3 : 5);

			$column_widths = $action == 'display_detailed_timesheet'
				? [
					'line' => 5,
					'date_stamp' => 20,
					'dow' => 10,
					'in_punch_time_stamp' => 20,
					'out_punch_time_stamp' => 20,
					'worked_time' => 15,
					'paid_time' => 15,
					'regular_time' => 15,
					'over_time' => 37,
					'absence_time' => 43
				]
				: [
					'line' => 5,
					'date_stamp' => 20,
					'dow' => 10,
					'min_punch_time_stamp' => 25,
					'max_punch_time_stamp' => 25,
					'worked_time' => 25,
					'regular_time' => 25,
					'over_time' => 20,
					'paid_time' => 20,
					'absence_time' => 25
				];

			if (isset($user_data['data']) && is_array($user_data['data'])) {
				if (isset($filter_data['date_type']) && $filter_data['date_type'] == 'pay_period_ids') {
					$pplf = new PayPeriodListFactory();
					$pplf->getById($user_data['pay_period_id']);
					if ($pplf->getRecordCount() == 1) {
						$pp_obj = $pplf->getCurrent();
						for ($d = TTDate::getBeginDayEpoch($pp_obj->getStartDate()); $d <= $pp_obj->getEndDate(); $d += 86400) {
							if (!Misc::inArrayByKeyAndValue($user_data['data'], 'date_stamp', TTDate::getBeginDayEpoch($d))) {
								$user_data['data'][] = [
									'date_stamp' => TTDate::getBeginDayEpoch($d),
									'min_punch_time_stamp' => null,
									'max_punch_time_stamp' => null,
									'in_punch_time_stamp' => null,
									'out_punch_time_stamp' => null,
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

						$line_h = $action == 'display_detailed_timesheet' ? 5 : 6;
						$cell_h_min = $cell_h_max = $line_h * 2;

						$pdf->SetFont('', 'B', 10);
						$pdf->setFillColor(220, 220, 220);
						$pdf->MultiCell($column_widths['line'], $line_h, '#', 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['date_stamp'], $line_h, TTi18n::gettext('Date'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['dow'], $line_h, TTi18n::gettext('DoW'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths[$action == 'display_detailed_timesheet' ? 'in_punch_time_stamp' : 'min_punch_time_stamp'], $line_h, TTi18n::gettext($action == 'display_detailed_timesheet' ? 'In' : 'First In'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths[$action == 'display_detailed_timesheet' ? 'out_punch_time_stamp' : 'max_punch_time_stamp'], $line_h, TTi18n::gettext($action == 'display_detailed_timesheet' ? 'Out' : 'Last Out'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['worked_time'], $line_h, TTi18n::gettext('Worked Time'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['regular_time'], $line_h, TTi18n::gettext('Regular Time'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['over_time'], $line_h, TTi18n::gettext('Over Time'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['paid_time'], $line_h, TTi18n::gettext('Paid Time'), 1, 'C', 1, 0);
						$pdf->MultiCell($column_widths['absence_time'], $line_h, TTi18n::gettext('Absence Time'), 1, 'C', 1, 0);
						$pdf->Ln();
					}

					$data = Misc::preSetArrayValues($data, [
						'date_stamp',
						'min_punch_time_stamp',
						'max_punch_time_stamp',
						'in_punch_time_stamp',
						'out_punch_time_stamp',
						'worked_time',
						'paid_time',
						'absence_time',
						'regular_time',
						'over_time'
					], '--');

					if ($x % 2 == 0) {
						$pdf->setFillColor(220, 220, 220);
					} else {
						$pdf->setFillColor(255, 255, 255);
					}

					if ($data['date_stamp'] !== '') {
						if ($action == 'display_detailed_timesheet') {
							$this->renderDetailedTimesheetRow($pdf, $data, $user_data, $column_widths, $x, $filter_data);
						} else {
							$pdf->SetFont('', '', 10);
							$pdf->Cell($column_widths['line'], 6, $x, 1, 0, 'C', 1);
							$pdf->Cell($column_widths['date_stamp'], 6, TTDate::getDate('DATE', $data['date_stamp']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['dow'], 6, date('D', $data['date_stamp']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['min_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['min_punch_time_stamp']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['max_punch_time_stamp'], 6, TTDate::getDate('TIME', $data['max_punch_time_stamp']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($data['worked_time']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($data['regular_time']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($data['over_time']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($data['paid_time']), 1, 0, 'C', 1);
							$pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($data['absence_time']), 1, 0, 'C', 1);
							$pdf->Ln();
						}

						$totals['worked_time'] += $data['worked_time'];
						$totals['paid_time'] += $data['paid_time'];
						$totals['absence_time'] += $data['absence_time'];
						$totals['regular_time'] += $data['regular_time'];
						$totals['over_time'] += $data['over_time'];

						$week_totals['worked_time'] += $data['worked_time'];
						$week_totals['paid_time'] += $data['paid_time'];
						$week_totals['absence_time'] += $data['absence_time'];
						$week_totals['regular_time'] += $data['regular_time'];
						$week_totals['over_time'] += $data['over_time'];

						if ($x % 7 == 0 || $i == $max_i) {
							$total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths[$action == 'display_detailed_timesheet' ? 'in_punch_time_stamp' : 'min_punch_time_stamp'];
							$pdf->SetFont('', 'B', $action == 'display_detailed_timesheet' ? 9 : 10);
							$pdf->Cell($total_cell_width, 6, TTi18n::gettext('Week Total:') . ' ', 0, 0, 'R', 0);
							$pdf->Cell($column_widths['worked_time'], 6, TTDate::getTimeUnit($week_totals['worked_time']), 0, 0, 'C', 0);
							$pdf->Cell($column_widths['regular_time'], 6, TTDate::getTimeUnit($week_totals['regular_time']), 0, 0, 'C', 0);
							$pdf->Cell($column_widths['over_time'], 6, TTDate::getTimeUnit($week_totals['over_time']), 0, 0, 'C', 0);
							$pdf->Cell($column_widths['paid_time'], 6, TTDate::getTimeUnit($week_totals['paid_time']), 0, 0, 'C', 0);
							$pdf->Cell($column_widths['absence_time'], 6, TTDate::getTimeUnit($week_totals['absence_time']), 0, 0, 'C', 0);
							$pdf->Ln($action == 'display_detailed_timesheet' ? 1 : 2);

							$week_totals = Misc::preSetArrayValues(null, ['worked_time', 'paid_time', 'absence_time', 'regular_time', 'over_time'], 0);
							$x = 0;
							$y++;

							if ($y == 4 && $i !== $max_i) {
								$pdf->AddPage();
							}
						}
					}

					$i++;
					$x++;
				}

				if (isset($totals) && is_array($totals)) {
					$pdf->Ln($action == 'display_detailed_timesheet' ? 4 : 3);
					$total_cell_width = $column_widths['line'] + $column_widths['date_stamp'] + $column_widths['dow'] + $column_widths[$action == 'display_detailed_timesheet' ? 'in_punch_time_stamp' : 'min_punch_time_stamp'];
					$pdf->SetFont('', 'B', $action == 'display_detailed_timesheet' ? 9 : 10);
					$pdf->Cell($total_cell_width, 6, '', 0, 0, 'R', 0);
					$pdf->Cell($column_widths[$action == 'display_detailed_timesheet' ? 'out_punch_time_stamp' : 'max_punch_time_stamp'], 6, TTi18n::gettext('Overall Total:') . ' ', 'T', 0, 'R', 0);
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

				$pdf->MultiCell(200, 5, TTi18n::gettext('By signing this timesheet I hereby certify that the above time accurately and fully reflects the time that') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . TTi18n::gettext('worked during the designated period.'), $border, 'L');
				$pdf->Ln(5);

				$pdf->Cell(40, 5, TTi18n::gettext('Employee Signature:'), $border, 0, 'L');
				$pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
				$pdf->Cell(40, 5, TTi18n::gettext('Supervisor Signature:'), $border, 0, 'R');
				$pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
				$pdf->Ln();

				$pdf->Cell(40, 5, '', $border, 0, 'R');
				$pdf->Cell(60, 5, $user_data['first_name'] . ' ' . $user_data['last_name'], $border, 0, 'C');
				$pdf->Ln();

				$pdf->Cell(140, 5, '', $border, 0, 'R');
				$pdf->Cell(60, 5, '_____________________________', $border, 0, 'C');
				$pdf->Ln();

				$pdf->Cell(140, 5, '', $border, 0, 'R');
				$pdf->Cell(60, 5, TTi18n::gettext('(print name)'), $border, 0, 'C');

				if ($user_data['verified_time_sheet_date'] != false) {
					$pdf->Ln();
					$pdf->SetFont('', 'B', 10);
					$pdf->Cell(200, 5, TTi18n::gettext('TimeSheet electronically signed by') . ' ' . $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . TTi18n::gettext('on') . ' ' . TTDate::getDate('DATE+TIME', $user_data['verified_time_sheet_date']), $border, 0, 'C');
					$pdf->SetFont('', '', 10);
				}

				$pdf->SetFont('', 'I', 8);
				$pdf->setXY(Misc::AdjustXY(0, $adjust_x), Misc::AdjustXY(245, $adjust_y));
				$pdf->Cell(200, 5, TTi18n::gettext('Generated:') . ' ' . TTDate::getDate('DATE+TIME', $pdf_created_date), $border, 0, 'C');
			}
		}

		$output = $pdf->Output('', 'S');

		if ($output !== false && Debug::getVerbosity() < 11) {
			return response($output)
				->header('Content-Type', 'application/pdf')
				->header('Content-Disposition', 'attachment; filename="' . ($action == 'display_detailed_timesheet' ? 'detailed_timesheet.pdf' : 'timesheet.pdf') . '"')
				->header('Content-Length', strlen($output));
		}

		return response(TTi18n::gettext('ERROR: Employee TimeSheet(s) not available!'), 200);
	}

	private function renderDetailedTimesheetRow($pdf, $data, $user_data, $column_widths, $x, $filter_data)
	{
		$punch_rows = $this->getPunchData($filter_data);
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
				$punch_data[10]['time_stamp'] = $punch_data[10]['time_stamp'] ?? null;
				$punch_data[10]['type_code'] = $punch_data[10]['type_code'] ?? null;
				$punch_data[20]['time_stamp'] = $punch_data[20]['time_stamp'] ?? null;
				$punch_data[20]['type_code'] = $punch_data[20]['type_code'] ?? null;

				if ($n > 0) {
					$pdf->setXY($pre_punch_x, $pre_punch_y + $default_line_h);
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

		$pdf->Cell($column_widths['worked_time'], $line_h, TTDate::getTimeUnit($data['worked_time']), 1, 0, 'C', 1);
		$pdf->Cell($column_widths['paid_time'], $line_h, TTDate::getTimeUnit($data['paid_time']), 1, 0, 'C', 1);
		$pdf->Cell($column_widths['regular_time'], $line_h, TTDate::getTimeUnit($data['regular_time']), 1, 0, 'C', 1);

		if ($data['over_time'] > 0 && isset($data['categorized_time']['over_time_policy'])) {
			$pre_over_time_x = $pdf->getX();
			$pdf->SetFont('', '', 8);
			foreach ($data['categorized_time']['over_time_policy'] as $policy_id => $value) {
				$otp_columns = $this->getPolicyColumns()['otp_columns'];
				$pdf->Cell($column_widths['over_time'], $line_h / $total_over_time_rows, $otp_columns['over_time_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
				$pdf->setXY($pre_over_time_x, $pdf->getY() + ($line_h / $total_over_time_rows));
			}
			$pdf->setXY($pre_over_time_x + $column_widths['over_time'], $pre_punch_y);
			$pdf->SetFont('', '', 9);
		} else {
			$pdf->Cell($column_widths['over_time'], $line_h, TTDate::getTimeUnit($data['over_time']), 1, 0, 'C', 1);
		}

		if ($data['absence_time'] > 0 && isset($data['categorized_time']['absence_policy'])) {
			$pre_absence_time_x = $pdf->getX();
			$pdf->SetFont('', '', 8);
			foreach ($data['categorized_time']['absence_policy'] as $policy_id => $value) {
				$ap_columns = $this->getPolicyColumns()['ap_columns'];
				$pdf->Cell($column_widths['absence_time'], $line_h / $total_absence_rows, $ap_columns['absence_policy-' . $policy_id] . ': ' . TTDate::getTimeUnit($value), 1, 0, 'C', 1);
				$pdf->setXY($pre_absence_time_x, $pdf->getY() + ($line_h / $total_absence_rows));
			}
			$pdf->setY($pdf->getY() - ($line_h / $total_absence_rows));
			$pdf->SetFont('', '', 9);
		} else {
			$pdf->Cell($column_widths['absence_time'], $line_h, TTDate::getTimeUnit($data['absence_time']), 1, 0, 'C', 1);
		}

		$pdf->Ln();
	}

	private function getPolicyColumns()
	{
		$otp_columns = [];
		$otplf = new OverTimePolicyListFactory();
		$otplf->getByCompanyId($this->company->getId());
		foreach ($otplf as $otp_obj) {
			$otp_columns['over_time_policy-' . $otp_obj->getId()] = $otp_obj->getName();
		}

		$ap_columns = [];
		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId($this->company->getId());
		foreach ($aplf as $ap_obj) {
			$ap_columns['absence_policy-' . $ap_obj->getId()] = $ap_obj->getName();
		}

		return ['otp_columns' => $otp_columns, 'ap_columns' => $ap_columns];
	}

	private function exportReport($report_data, $filter_data, $columns)
	{
		$rows = $report_data['rows'];
		$filter_columns = $report_data['filter_columns'];

		if (!empty($rows) && !empty($filter_columns)) {
			$export_filter_columns = [
				'first_name' => TTi18n::gettext('First Name'),
				'last_name' => TTi18n::gettext('Last Name'),
				'full_name' => TTi18n::gettext('Full Name'),
				'employee_number' => TTi18n::gettext('Employee #'),
				'province' => TTi18n::gettext('Province/State'),
				'country' => TTi18n::gettext('Country'),
				'group' => TTi18n::gettext('Group'),
				'title' => TTi18n::gettext('Title'),
				'default_branch' => TTi18n::gettext('Default Branch'),
				'default_department' => TTi18n::gettext('Default Department'),
				'pay_period' => TTi18n::gettext('Pay Period'),
			];

			$filter_columns = Misc::prependArray($export_filter_columns, $filter_columns);

			$tmp_rows = [];
			foreach ($rows as $row) {
				if (is_array($row['data'])) {
					foreach ($row['data'] as $sub_row) {
						unset($row['data']);
						$tmp_rows[] = array_merge($row, $sub_row);
					}
				}
			}

			if ($filter_data['export_type'] == 'csv') {
				$data = Misc::Array2CSV($tmp_rows, $filter_columns);
				return response($data)
					->header('Content-Type', 'application/csv')
					->header('Content-Disposition', 'attachment; filename="report.csv"')
					->header('Content-Length', strlen($data));
			}

			return response('PDF export not implemented', 501);
		}

		return response(TTi18n::gettext('No Data To Export!'), 200);
	}

	private function prepareFormData($filter_data, $columns, $generic_data, $pay_period_options)
	{
		$all_array_option = ['-1' => TTi18n::gettext('-- All --')];

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria($this->company->getId(), [
			'permission_children_ids' => $filter_data['permission_children_ids']
		]);
		$user_options = $ulf->getArrayByListFactory($ulf, false, true);

		$data = [
			'src_include_user_options' => Misc::arrayDiffByKey($filter_data['include_user_ids'] ?? [], $user_options),
			'selected_include_user_options' => Misc::arrayIntersectByKey($filter_data['include_user_ids'] ?? [], $user_options),
			'src_exclude_user_options' => Misc::arrayDiffByKey($filter_data['exclude_user_ids'] ?? [], $user_options),
			'selected_exclude_user_options' => Misc::arrayIntersectByKey($filter_data['exclude_user_ids'] ?? [], $user_options),
			'src_user_status_options' => Misc::arrayDiffByKey($filter_data['user_status_ids'] ?? [], $ulf->getOptions('status')),
			'selected_user_status_options' => Misc::arrayIntersectByKey($filter_data['user_status_ids'] ?? [], $ulf->getOptions('status')),
			'pay_period_options' => Misc::prependArray($all_array_option, $pay_period_options['options']),
			'src_pay_period_options' => Misc::arrayDiffByKey($filter_data['pay_period_ids'] ?? [], $pay_period_options['options']),
			'selected_pay_period_options' => Misc::arrayIntersectByKey($filter_data['pay_period_ids'] ?? [], $pay_period_options['options'])
		];

		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray($all_array_option, $uglf->getArrayByNodes(
			FastTree::FormatArray($uglf->getByCompanyIdArray($this->company->getId()), 'TEXT', true)
		));
		$data['src_group_options'] = Misc::arrayDiffByKey($filter_data['group_ids'] ?? [], $group_options);
		$data['selected_group_options'] = Misc::arrayIntersectByKey($filter_data['group_ids'] ?? [], $group_options);

		$blf = new BranchListFactory();
		$blf->getByCompanyId($this->company->getId());
		$branch_options = Misc::prependArray($all_array_option, $blf->getArrayByListFactory($blf, false, true));
		$data['src_branch_options'] = Misc::arrayDiffByKey($filter_data['branch_ids'] ?? [], $branch_options);
		$data['selected_branch_options'] = Misc::arrayIntersectByKey($filter_data['branch_ids'] ?? [], $branch_options);
		$data['src_punch_branch_options'] = Misc::arrayDiffByKey($filter_data['punch_branch_ids'] ?? [], $branch_options);
		$data['selected_punch_branch_options'] = Misc::arrayIntersectByKey($filter_data['punch_branch_ids'] ?? [], $branch_options);

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId($this->company->getId());
		$department_options = Misc::prependArray($all_array_option, $dlf->getArrayByListFactory($dlf, false, true));
		$data['src_department_options'] = Misc::arrayDiffByKey($filter_data['department_ids'] ?? [], $department_options);
		$data['selected_department_options'] = Misc::arrayIntersectByKey($filter_data['department_ids'] ?? [], $department_options);
		$data['src_punch_department_options'] = Misc::arrayDiffByKey($filter_data['punch_department_ids'] ?? [], $department_options);
		$data['selected_punch_department_options'] = Misc::arrayIntersectByKey($filter_data['punch_department_ids'] ?? [], $department_options);

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId($this->company->getId());
		$user_title_options = Misc::prependArray($all_array_option, $utlf->getArrayByListFactory($utlf, false, true));
		$data['src_user_title_options'] = Misc::arrayDiffByKey($filter_data['user_title_ids'] ?? [], $user_title_options);
		$data['selected_user_title_options'] = Misc::arrayIntersectByKey($filter_data['user_title_ids'] ?? [], $user_title_options);

		$data['src_column_options'] = Misc::arrayDiffByKey($filter_data['column_ids'] ?? [], $columns);
		$data['selected_column_options'] = Misc::arrayIntersectByKey($filter_data['column_ids'] ?? [], $columns);

		$data['sort_options'] = array_merge($columns, ['effective_date_order' => 'Wage Effective Date']);
		unset($data['sort_options']['effective_date']);
		$data['sort_direction_options'] = Misc::getSortDirectionArray();

		$data['hidden_elements'] = [
			'displayReport' => 'hidden',
			'displayTimeSheet' => 'hidden',
			'displayDetailedTimeSheet' => 'hidden',
			'export' => ''
		];

		$data['export_type_options'] = [
			'pdfDailyDetailAttendance' => TTi18n::gettext('Daily Attendance Report')
		];

		$ugdlf = new UserGenericDataListFactory();
		$data['saved_report_options'] = $ugdlf->getByUserIdAndScriptArray(
			$this->userPrefs->getId(),
			request()->url()
		);
		$data['generic_data'] = $generic_data;
		$data['filter_data'] = $filter_data;

		return $data;
	}
}
