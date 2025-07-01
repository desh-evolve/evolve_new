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
use App\Models\Core\Option;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\Policy\SchedulePolicyListFactory;
use App\Models\Schedule\ScheduleListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class ScheduleList extends Controller
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


		if ( !$permission->Check('schedule','enabled')
				OR !( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_child')) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		//Debug::setVerbosity( 11 );

		$viewData['title'] = 'Scheduled Shifts List';


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
											'-1000-first_name' => _('First Name'),
											'-1002-last_name' => _('Last Name'),
											'-1010-title' => _('Title'),
											'-1039-group' => _('Group'),
											'-1040-default_branch' => _('Default Branch'),
											'-1050-default_department' => _('Default Department'),
											'-1160-branch_id' => _('Branch'),
											'-1170-department_id' => _('Department'),
											'-1202-status_id' => _('Status'),
											'-1210-start_time' => _('Start Time'),
											'-1220-end_time' => _('End Time'),
											'-1230-total_time' => _('Total Time'),
											);

		$professional_edition_columns = array(
		/*
											'-1180-job' => _('Job'),
											'-1182-job_status' => _('Job Status'),
											'-1183-job_branch' => _('Job Branch'),
											'-1184-job_department' => _('Job Department'),
											'-1185-job_group' => _('Job Group'),
											'-1190-job_item' => _('Task'),
		*/
											);

		if ( $current_company->getProductEdition() == 20 ) {
			$columns = Misc::prependArray( $columns, $professional_edition_columns);
			ksort($columns);
		}

		if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
			//Default columns.
			$filter_data['columns'] = array(
										'-1000-first_name',
										'-1002-last_name',
										'-1202-status_id',
										'-1210-start_time',
										'-1220-end_time',
										'-1230-total_time',
										);

			if ( $sort_column == '' ) {
				$sort_column = $filter_data['sort_column'] = 'start_time';
				$sort_order = $filter_data['sort_order'] = 'desc';
			}
		}

		$ugdlf = new UserGenericDataListFactory();
		$ugdf = new UserGenericDataFactory();

		Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
		//Handle different actions for different forms.

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
		switch ($action) {
			case 'search_form_delete':
			case 'search_form_update':
			case 'search_form_save':
			case 'search_form_clear':
			case 'search_form_search':
				Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

				//$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'ScheduleList.php') );
			default:

				//extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
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
				$slf = new ScheduleListFactory();

				$hlf = new HierarchyListFactory();
				$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
				Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
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

				if ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_ids']) OR $filter_data['pay_period_ids'] == '' ) ) {
					$filter_data['pay_period_ids'] = '-1';
				}

				//If they aren't searching, limit to the last pay period by default for performance optimization when there are hundreds of thousands of schedules.
				if ( $action == '' AND isset($pay_period_ids[0]) AND isset($pay_period_ids[1]) AND !isset($filter_data['pay_period_ids']) ) {
					$filter_data['pay_period_ids'] = array($pay_period_ids[0],$pay_period_ids[1]);
				}

				//Order In punches before Out punches.
				$sort_array = Misc::prependArray( $sort_array, array( 'udf.pay_period_id' => 'asc','uf.last_name' => 'asc', 'a.start_time' => 'asc', 'a.status_id' => 'asc' ) );
				$slf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );


				$schedule_status_options = $slf->getOptions('status');

				$splf = new SchedulePolicyListFactory();
				$schedule_policy_options = $splf->getByCompanyIdArray( $current_company->getId(), FALSE );

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
				foreach ($slf->rs as $s_obj) {
					$slf->data = (array)$s_obj;
					$s_obj = $slf;

					//Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
					$user_obj = $ulf->getById( $s_obj->getColumn('user_id') )->getCurrent();

					$rows[] = array(
								'id' => $s_obj->getColumn('schedule_id'),

								'user_id' => $s_obj->getColumn('user_id'),
								'first_name' => $user_obj->getFirstName(),
								'last_name' => $user_obj->getLastName(),
								'title' => Option::getByKey($user_obj->getTitle(), $title_options ),
								'group' => Option::getByKey($user_obj->getGroup(), $group_options ),
								'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $branch_options ),
								'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $department_options ),

								//'branch_id' => $s_obj->getColumn('branch_id'),
								'branch_id' => Option::getByKey( $s_obj->getBranch(), $branch_options ),
								//'department_id' => $s_obj->getColumn('department_id'),
								'department_id' => Option::getByKey( $s_obj->getDepartment(), $department_options ),
								//'status_id' => $s_obj->getStatus(),
								'status_id' => Option::getByKey($s_obj->getStatus(), $schedule_status_options),
								'start_time' => TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ),
								'end_time' => TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ),

								'total_time' => TTDate::getTimeUnit( $s_obj->getTotalTime() ),

								//'job_id' => $s_obj->getColumn('job_id'),
								//'job_name' => $s_obj->getColumn('job_name'),

								'is_owner' => $permission->isOwner( $s_obj->getCreatedBy(), $current_user->getId() ),
								'is_child' => $permission->isChild( $s_obj->getColumn('user_id'), $permission_children_ids ),
							);

				}

				$viewData['rows'] = $rows;

				$all_array_option = array('-1' => _('-- Any --'));

				$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
				$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

				//Select box options;
				$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
				$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
				$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
				$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
				$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
				$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
				$filter_data['schedule_status_options'] = Misc::prependArray( $all_array_option, $schedule_status_options );
				$filter_data['schedule_policy_options'] = Misc::prependArray( $all_array_option, $schedule_policy_options );

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

				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;
				$viewData['filter_data'] = $filter_data;
				$viewData['columns'] = $filter_columns ;
				$viewData['total_columns'] = count($filter_columns)+3 ;
                // dd($viewData);

				break;
		}

		return view('schedule/ScheduleList', $viewData);

	}


    public function delete($id)
    {
        $current_company = $this->currentCompany;

        if (empty($id)) {
			return response()->json(['error' => 'No schedule List selected.'], 400);
		}

        $delete = TRUE;
		$slf = new ScheduleListFactory();
        // $slf->StartTransaction();

        $slf->getByCompanyIdAndId($current_company->getID(), $id );

        if ( $slf->getRecordCount() > 0 ) {

            foreach($slf->rs as $s_obj) {
                $slf->data = (array)$s_obj;
                $s_obj = $slf;

                $s_obj->setDeleted($delete);
                if ( $s_obj->isValid() ) {
                    // $s_obj->setEnableReCalculateDay(TRUE); //Need to remove absence time when deleting a schedule.
                    $res = $s_obj->Save();

                    if($res){
                        return response()->json(['success' => 'Schedule Deleted Successfully.']);
                    }else{
                        return response()->json(['error' => 'Schedule Deleted Failed.']);
                    }
                }
            }
        }
        //$plf->FailTransaction();
        // $slf->CommitTransaction();

		return response()->json(['success' => 'Operation completed successfully.']);

    }

}

?>
