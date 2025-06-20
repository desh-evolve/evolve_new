<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Schedule\RecurringScheduleControlListFactory;
use App\Models\Schedule\RecurringScheduleTemplateControlListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class RecurringScheduleControlList extends Controller
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
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;

		if ( !$permission->Check('recurring_schedule','enabled')
				OR !( $permission->Check('recurring_schedule','view') OR $permission->Check('recurring_schedule','view_own') OR $permission->Check('recurring_schedule','view_child') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		//Debug::setVerbosity(11);

		$viewData['title'] = 'Recurring Schedule List';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'form',
														'page',
														'sort_column',
														'sort_order',
														'filter_data',
														'saved_search_id',
														'filter_template_id',
														'ids',
														) ) );

		$columns = array(
													'-1010-first_name' => _('First Name'),
													'-1020-middle_name' => _('Middle Name'),
													'-1030-last_name' => _('Last Name'),
													'-1040-name' => _('Name'),
													'-1050-description' => _('Description'),
													'-1070-start_date' => _('Start Date'),
													'-1080-end_date' => _('End Date'),
													);

		if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
			//Default columns.
			$filter_data['columns'] = array(
										'-1010-first_name',
										'-1030-last_name',
										'-1040-name',
										'-1050-description',
										'-1070-start_date',
										'-1080-end_date',
										);

			if ( $sort_column == '' ) {
				$sort_column = $filter_data['sort_column'] = 'last_name';
				$sort_order = $filter_data['sort_order'] = 'asc';
			}
		}

		$ugdlf = new UserGenericDataListFactory(); 
		$ugdf = new UserGenericDataFactory(); 

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$hlf = new HierarchyListFactory(); 
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

		//===================================================================================
		$action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
		//===================================================================================

		if ( isset($form) AND $form != '' ) {
			$action = strtolower($form.'_'.$action);
		} else {
			$action = strtolower($action);
		}


		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);
		switch ($action) {
			case 'add':

				Redirect::Page( URLBuilder::getURL( NULL, '/schedule/edit_recurring_schedule', FALSE) );

				break;
			case 'delete':
			case 'undelete':
				if ( strtolower($action) == 'delete' ) {
					$delete = TRUE;
				} else {
					$delete = FALSE;
				}

				$rsclf = new RecurringScheduleControlListFactory();

				foreach ($ids as $id => $user_ids) {
					$rsclf->getByIdAndCompanyId($id, $current_company->getId() );
					foreach ($rsclf->rs as $rsc_obj) {
						$rsclf->data = (array)$rsc_obj;
						$rsc_obj = $rsclf;
						//Get all users for this schedule.
						$current_users = $rsc_obj->getUser();

						$user_diff_arr = array_diff( (array)$current_users, (array)$user_ids );
						//Debug::Arr($user_diff_arr,'User Diff:', __FILE__, __LINE__, __METHOD__,10);

						if ( is_array($user_diff_arr) AND count($user_diff_arr) == 0 ) {
							Debug::Text('No more users assigned to schedule, deleting...', __FILE__, __LINE__, __METHOD__,10);

							//No more users assigned to this schedule, delete the whole thing.
							$rsc_obj->setDeleted($delete);
						} elseif ( is_array($user_diff_arr) AND count($user_diff_arr) > 0 ) {
							Debug::Text('Still more users assigned to schedule, removing users only...', __FILE__, __LINE__, __METHOD__,10);
							//Still users assigned to this schedule, remove users from it.
							$rsc_obj->setUser( $user_diff_arr );
						}

						if ( $rsc_obj->isValid() ) {
							$rsc_obj->Save();
						}
					}
				}

				Redirect::Page( URLBuilder::getURL( NULL, '/schedule/recurring_schedule_control_list') );

				break;
			case 'search_form_delete':
			case 'search_form_update':
			case 'search_form_save':
			case 'search_form_clear':
			case 'search_form_search':
				Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

				$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'RecurringScheduleControlList.php') );
			default:

				//extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) ); 
				Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
				Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

				if ( isset($filter_template_id) AND $filter_template_id != '' ) {
					$filter_data['template_id'] = array($filter_template_id);
				}

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

				$rsclf = new RecurringScheduleControlListFactory(); 
				$ulf = new UserListFactory();

				if ( $permission->Check('recurring_schedule','view') == FALSE ) {
					if ( $permission->Check('recurring_schedule','view_child') ) {
						$filter_data['permission_children_ids'] = $permission_children_ids;
					}
					if ( $permission->Check('recurring_schedule','view_own') ) {
						$filter_data['permission_children_ids'][] = $current_user->getId();
					}
				}

				$rsclf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

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

				$rstclf = new RecurringScheduleTemplateControlListFactory();
				$template_options = $rstclf->getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );

				$rows = [];

				foreach ($rsclf->rs as $rsc_obj) {
					$rsclf->data = (array)$rsc_obj;
					$rsc_obj = $rsclf;
					$user_id = $rsc_obj->getColumn('user_id');

					$ulf = new UserListFactory();
					$ulf->getByID( $user_id );
					if ( $ulf->getRecordCount() == 1 ) {
						$u_obj = $ulf->getCurrent();
					} else {
						//Skip this row.
						Debug::Text('Skipping Row: User ID: '. $user_id , __FILE__, __LINE__, __METHOD__,10);
						continue;
					}

					$rows[] = array(
										'id' => $rsc_obj->getId(),
										'user_id' => $user_id,
										'name' => $rsc_obj->getColumn('name'),
										'description' => $rsc_obj->getColumn('description'),
										'start_week' => $rsc_obj->getStartWeek(),
										'start_date' => $rsc_obj->getStartDate(),
										'end_date' => $rsc_obj->getEndDate(),
										'first_name' => $u_obj->getFirstName(),
										'middle_name' => $u_obj->getMiddleName(),
										'last_name' => $u_obj->getLastName(),
										'user_full_name' => $u_obj->getFullName(TRUE),

										'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
										'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),

										'deleted' => $rsc_obj->getDeleted()
									);

				}

				$all_array_option = array('-1' => _('-- Any --'));

				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

				//Select box options;
				$filter_data['template_options'] = Misc::prependArray( $all_array_option, $template_options );
				$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
				$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
				$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
				$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
				$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );

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

				$viewData['rows'] = $rows;
				$viewData['filter_data'] = $filter_data;
				$viewData['columns'] = $filter_columns ;
				$viewData['total_columns'] = count($filter_columns)+3 ;
				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;
				$viewData['saved_search_id'] = $saved_search_id ;

				break;
		}
		return view('schedule/RecurringScheduleControlList', $viewData);
	}
}

?>