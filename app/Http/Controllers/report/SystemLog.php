<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Sort;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\LogListFactory;
use App\Models\Core\LogFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;
use App\Models\Core\BreadCrumb;

class SystemLog extends Controller
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


		$viewData['title'] = 'Audit Trail Report';
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;
        $current_user_prefs = $this->userPrefs;

		extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'

												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$columns = $static_columns =  array(
							'-1000-full_name' => _('Full Name'),
							'-1010-date' => _('Date'),
							'-1020-table_name' => _('Object'),
							'-1030-action' => _('Action'),
							'-1040-description' => _('Description'),
							'-1050-function' => _('Functions'),
						);

if ( isset($filter_data['start_date']) ) {
	$filter_data['start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['start_date']) );
}

if ( isset($filter_data['end_date']) ) {
	$filter_data['end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime($filter_data['end_date']) );
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'log_action_ids', 'log_table_name_ids', 'column_ids' ), array());

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('user','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('user','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('user','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = $_POST['action'] ?? '';
$action = !empty($action) ? str_replace(' ', '_', strtolower(trim($action))) : '';
switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!: '. $action, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['start_date']);
				unset($filter_data['end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach ($ulf->rs as $u_obj) {
                    $ulf->data = (array)$u_obj;
                    $u_obj = $ulf;
				$filter_data['user_id'][] = $u_obj->getId();
			}

			if ( isset($filter_data['pay_period_ids']) ) {
				//Trim sort prefix from selected pay periods.
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			$ulf = new UserListFactory();
			$llf = new LogListFactory();

			$log_action_options = $llf->getOptions('action');
			$log_table_name_options = $llf->getOptions('table_name');

			//5000 row limit.
			$llf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, 5000 );
			if ( $llf->getRecordCount() > 0 ) {
				$x=0;

				foreach ($llf->rs as $l_obj) {
                        $llf->data = (array)$l_obj;
                        $l_obj = $llf;

					$user_obj = $ulf->getById( $l_obj->getUser() )->getCurrent();

					$tmp_action = Option::getByKey($l_obj->getAction(), $log_action_options );
					$table_name = Option::getByKey($l_obj->getTableName(), $log_table_name_options );

					if ( $tmp_action != '' AND $table_name != '' ) {
						$link = $l_obj->getLink();
						if ( $link !== FALSE ) {
							$function = '<a href="'.$link.'" target="_blank">View</a>';
						} else {
							$function = NULL;
						}

						$rows[$x] = array(
										'user_id' => $l_obj->getUser(),
										'full_name' => $user_obj->getFullName(),
										'action_id' => $l_obj->getAction(),
										'action' => $tmp_action,
										'table_name_id' => $l_obj->getTableName(),
										'table_name' => $table_name,
										'date' => TTDate::getDate('DATE+TIME', $l_obj->getDate() ),
										'description' => $l_obj->getDescription(),
										'function' => $function,
										);
					} else {
						Debug::Text('Skipping Action: '. $l_obj->getAction() .' or Table Name: '. $l_obj->getTableName(), __FILE__, __LINE__, __METHOD__,10);
					}
					unset($tmp_action);

					$x++;
				}
			}

			if ( isset($rows) ) {
				foreach($rows as $row) {
					$tmp_rows[] = $row;
				}
				//var_dump($tmp_rows);

				$special_sort_columns = array('pay_period');
				if ( in_array( Misc::trimSortPrefix($filter_data['primary_sort']), $special_sort_columns ) ) {
						$filter_data['primary_sort'] = $filter_data['primary_sort'].'_order';
				}
				if ( in_array( Misc::trimSortPrefix($filter_data['secondary_sort']), $special_sort_columns ) ) {
						$filter_data['secondary_sort'] = $filter_data['secondary_sort'].'_order';
				}

				$rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);
			}
		}

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}

		// dd($rows);
		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
			
            $data = Misc::Array2CSV($rows, $filter_columns);

            // Set headers for CSV download
            header('Content-Type: application/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="report.csv"');
            header('Content-Length: ' . strlen($data));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Output the CSV data
            echo $data;
			} else {
				echo __("No Data To Export!") ."<br>\n";
			}
		} else {

					$viewData['generated_time'] = TTDate::getTime();
					// $viewData['pay_period_options'] = $pay_period_options;
					$viewData['filter_data'] = $filter_data;
					$viewData['columns'] = $filter_columns;
					$viewData['rows'] = $rows;
					return view('report/SystemLogReport', $viewData);
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
	BreadCrumb::setCrumb($viewData['title']);

		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
				$ugdf = new UserGenericDataFactory();
				$ugdf->getReportFormData($generic_data['id']);
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				//$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				$filter_data['start_date'] = TTDate::getBeginMonthEpoch( time() );
				$filter_data['end_date'] = TTDate::getEndMonthEpoch( time() );
				$filter_data['group_ids'] = array( -1 );
				$filter_data['log_action_ids'] = array( -1 );
				$filter_data['log_table_name_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-full_name',
											'-1010-date',
											'-1020-table_name',
											'-1030-action',
											'-1040-description',
											'-1050-function',
												) );

				$filter_data['primary_sort'] = '-1010-date';
				$filter_data['primary_sort_dir'] = '-1';
				$filter_data['secondary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort_dir'] = '-1';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'log_action_ids', 'log_table_name_ids', 'column_ids' ), NULL);

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => _('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get Log Actions
		$lf = new LogFactory();
		$log_action_options = Misc::prependArray( $all_array_option, $lf->getOptions('action') );
		$filter_data['src_log_action_options'] = Misc::arrayDiffByKey( (array)$filter_data['log_action_ids'], $log_action_options );
		$filter_data['selected_log_action_options'] = Misc::arrayIntersectByKey( (array)$filter_data['log_action_ids'], $log_action_options );

		//Get table names
		$log_table_name_options = Misc::prependArray( $all_array_option, $lf->getOptions('table_name') );
		$filter_data['src_log_table_name_options'] = Misc::arrayDiffByKey( (array)$filter_data['log_table_name_ids'], $log_table_name_options );
		$filter_data['selected_log_table_name_options'] = Misc::arrayIntersectByKey( (array)$filter_data['log_table_name_ids'], $log_table_name_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		//$filter_data['group_by_options'] = Misc::prependArray( array('0' => _('No Grouping')), $static_columns );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;


		$viewData['generic_data'] = $generic_data;

		$viewData['filter_data'] = $filter_data;

		$viewData['ugdf'] = $ugdf;

		return view('report/SystemLog', $viewData);

		break;

		}
	}
}