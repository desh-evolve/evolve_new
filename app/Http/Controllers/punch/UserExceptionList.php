<?php

namespace App\Http\Controllers\punch;
use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

use App\Models\Core\Environment;
use App\Models\Core\CurrencyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\PayStubAmendment\PayStubAmendmentListFactory;
use App\Models\Policy\ExceptionPolicyFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class UserExceptionList extends Controller
{
	protected $permission;
    protected $current_user;
    protected $current_company;
    protected $current_user_prefs;
    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');
    
        $this->permission = View::shared('permission');
        $this->current_user = View::shared('current_user');
        $this->current_company = View::shared('current_company');
        $this->current_user_prefs = View::shared('current_user_prefs');

    }

	public function index(){
		$permission = $this->permission;
        $current_user = $this->current_user;
        $current_company = $this->current_company;
        $current_user_prefs = $this->current_user_prefs;

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child')) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Exception List';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'form',
														'filter_data',
														'page',
														'sort_column',
														'sort_order',
														'saved_search_id',
														'ids',
														) ) );

		$columns = array(
													'-1010-first_name' => _('First Name'),
													'-1020-middle_name' => _('Middle Name'),
													'-1030-last_name' => _('Last Name'),
													'-1040-date_stamp' => _('Date'),
													'-1050-severity' => _('Severity'),
													'-1060-exception_policy_type' => _('Exception'),
													'-1070-exception_policy_type_id' => _('Code'),
													);

		if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
			//Default columns.
			if ( $permission->Check('punch','view') == TRUE OR $permission->Check('punch','view_child')) {
				$filter_data['columns'] = array(
											'-1010-first_name',
											'-1030-last_name',
											'-1040-date_stamp',
											'-1050-severity',
											'-1060-exception_policy_type',
											'-1070-exception_policy_type_id',
											);
			} else {
				$filter_data['columns'] = array(
											'-1040-date_stamp',
											'-1050-severity',
											'-1060-exception_policy_type',
											'-1070-exception_policy_type_id',
											);
			}
			if ( $sort_column == '' ) {
				$sort_column = $filter_data['sort_column'] = 'severity';
				$sort_order = $filter_data['sort_order'] = 'desc';
			}
		}

		$ugdlf = new UserGenericDataListFactory(); 
		$ugdf = new UserGenericDataFactory();

		Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
		//Handle different actions for different forms.

		$action = Misc::findSubmitButton();
		if ( isset($form) AND $form != '' ) {
			$action = strtolower($form.'_'.$action);
		} else {
			$action = strtolower($action);
		}
		switch ($action) {
			case 'search_form_delete':
			case 'search_form_update':
			case 'search_form_save':
			case 'search_form_clear':
			case 'search_form_search':
				Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

				
				$saved_search_id = $ugdf->searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'UserExceptionList.php') );
			default:
				$new_filter_data = $ugdf->getSearchFormData( $saved_search_id, $sort_column ) ;
				$filter_data = array_merge($filter_data, $new_filter_data);
				
				Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

				$sort_array = NULL;
				if ( $sort_column != '' ) {
					$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
				}

				URLBuilder::setURL($_SERVER['SCRIPT_NAME'],	array(
																	'sort_column' => Misc::trimSortPrefix($sort_column),
																	'sort_order' => $sort_order,
																	'saved_search_id' => $saved_search_id,
																	'page' => $page
																) );

				$ulf = new UserListFactory(); 
				$elf = new ExceptionListFactory();

				$hlf = new HierarchyListFactory();
				$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
				Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
				$filter_data['permission_children_ids'] = array();
				if ( $permission->Check('punch','view') == FALSE ) {
					if ( $permission->Check('punch','view_child') ) {
						$filter_data['permission_children_ids'] = $permission_children_ids;
					}
					if ( $permission->Check('punch','view_own') ) {
						$filter_data['permission_children_ids'][] = $current_user->getId();
					}
				}

				$pplf = new PayPeriodListFactory();
				$pplf->getByCompanyId( $current_company->getId() );
				$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, FALSE );
				$pay_period_ids = array_keys((array)$pay_period_options);

				if ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_id']) OR $filter_data['pay_period_id'] == '' ) ) {
					$filter_data['pay_period_id'] = '-1';
				}

				$filter_data['pay_period_status_id'] = array(10);

				$filter_data['type_id'] = array(30,40,50,55,60,70);
				if (  isset($filter_data['pre_mature']) ) {
					$filter_data['type_id'][] = 5;
				}

				$elf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

				$pager = new Pager($elf);

				$epf = new ExceptionPolicyFactory(); 
				$exception_policy_type_options = $epf->getOptions('type');
				$exception_policy_severity_options = $epf->getOptions('severity');

				$utlf = new UserTitleListFactory();
				$utlf->getByCompanyId( $current_company->getId() );
				$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

				$blf = new BranchListFactory();
				$blf->getByCompanyId( $current_company->getId() );
				$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

				$dlf = new DepartmentListFactory();
				$dlf->getByCompanyId( $current_company->getId() );
				$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

				$uglf = new UserGroupListFactory();
				$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

				$ulf = new UserListFactory();
				$user_options = $ulf->getByCompanyIdArray( $current_company->getID(), FALSE );

				$rows = [];
				foreach ($elf->rs as $e_obj) {
					$elf->data = (array)$e_obj;
					$e_obj = $elf; 
					//Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
					$user_obj = $ulf->getById( $e_obj->getColumn('user_id') )->getCurrent();

					$rows[] = array(
										'id' => $e_obj->getId(),
										'user_date_id' => $e_obj->getUserDateID(),
										'user_id' => $e_obj->getColumn('user_id'),
										'first_name' => $user_obj->getFirstName(),
										'middle_name' => $user_obj->getMiddleName(),
										'last_name' => $user_obj->getLastName(),
										'user_full_name' => Option::getByKey($e_obj->getColumn('user_id'), $user_options),
										'date_stamp' => TTDate::getDate('DATE', TTDate::strtotime($e_obj->getColumn('user_date_stamp')) ),
										'date_stamp_epoch' => TTDate::strtotime($e_obj->getColumn('user_date_stamp')),
										'type_id' => $e_obj->getType(),
										'severity_id' => $e_obj->getColumn('severity_id'),
										'severity' => Option::getByKey($e_obj->getColumn('severity_id'), $exception_policy_severity_options),
										'exception_color' => $e_obj->getColor(),
										'exception_background_color' => $e_obj->getBackgroundColor(),
										'exception_policy_type_id' => $e_obj->getColumn('exception_policy_type_id'),
										'exception_policy_type' => Option::getByKey($e_obj->getColumn('exception_policy_type_id'), $exception_policy_type_options ),
										'created_date' => $e_obj->getCreatedDate(),
										'deleted' => $e_obj->getDeleted()
									);

				}

				$viewData['rows'] = $rows;

				$all_array_option = array('-1' => _('-- Any --'));

				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array( 'permission_children_ids' => $filter_data['permission_children_ids'] ) );
				$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

				//Select box options;
				$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
				$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
				$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
				$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
				$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
				$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
				$filter_data['severity_options'] = Misc::prependArray( $all_array_option, $exception_policy_severity_options );
				$filter_data['type_options'] = Misc::prependArray( $all_array_option, $exception_policy_type_options );

				$filter_data['saved_search_options'] = $ugdlf->getArrayByListFactory( $ugdlf->getByUserIdAndScript( $current_user->getId(), $_SERVER['SCRIPT_NAME']), FALSE );

				//Get column list
				$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['columns'], $columns );
				$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['columns'], $columns );

				$filter_data['sort_options'] = Misc::trimSortPrefix($columns);
				$filter_data['sort_direction_options'] = Misc::getSortDirectionArray(TRUE);

				foreach( $filter_data['columns'] as $column_key ) {
					$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
				}
				unset($column_key);

				$viewData['sort_column'] = $sort_column;
				$viewData['sort_order'] = $sort_order;
				$viewData['filter_data'] = $filter_data;
				$viewData['columns'] = $filter_columns;
				$viewData['total_columns'] = count($filter_columns)+3;
				$viewData['paging_data'] = $pager->getPageVariables();

				break;
		}

		return view('punch/UserExceptionList', $viewData);

	}
}


?>