<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\UserTitleList;
use App\Models\Company\BranchListFactory;
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
use App\Models\Punch\PunchListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class PunchList extends Controller
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

    public function index() {

        $permission = $this->permission;
        $current_user = $this->current_user;
        $current_company = $this->current_company;
        $current_user_prefs = $this->current_user_prefs;
		
        if ( !$permission->Check('punch','enabled')
                OR !( $permission->Check('punch','edit') OR $permission->Check('punch','edit_child')) ) {
            $permission->Redirect( FALSE ); //Redirect
        }

        $viewData['title'] = 'Punch List';

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
                                            '-1000-first_name' => 'First Name',
                                            '-1002-last_name' => 'Last Name',
                                            '-1010-title' => 'Title',
                                            '-1039-group' => 'Group',
                                            '-1040-default_branch' => 'Default Branch',
                                            '-1050-default_department' => 'Default Department',
                                            '-1160-branch' => 'Branch',
                                            '-1170-department' => 'Department',
                                            '-1200-type_id' => 'Type',
                                            '-1202-status_id' => 'Status',
                                            '-1210-date_stamp' => 'Date',
                                            '-1220-time_stamp' => 'Time',
                                            );

        $professional_edition_columns = array(
        /*
                                            '-1180-job' => 'Job',
                                            '-1182-job_status' => 'Job Status',
                                            '-1183-job_branch' => 'Job Branch',
                                            '-1184-job_department' => 'Job Department',
                                            '-1185-job_group' => 'Job Group',
                                            '-1190-job_item' => 'Task',
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
                                        '-1200-type_id',
                                        '-1202-status_id',
                                        '-1220-time_stamp',
                                        );

            if ( $sort_column == '' ) {
                $sort_column = $filter_data['sort_column'] = 'time_stamp';
                $sort_order = $filter_data['sort_order'] = 'desc';
            }
        }

        $ugdlf = new UserGenericDataListFactory();
        $ugdf = new UserGenericDataFactory();

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
            case 'delete':
            case 'undelete':
                //Debug::setVerbosity( 11 );
                if ( strtolower($action) == 'delete' ) {
                    $delete = TRUE;
                } else {
                    $delete = FALSE;
                }

                if ( DEMO_MODE == FALSE
                    AND ( $permission->Check('punch','delete') OR $permission->Check('punch','delete_own') OR $permission->Check('punch','delete_child')  ) ) {
                    $plf = new PunchListFactory();
                    $plf->StartTransaction();

                    $plf->getByCompanyIdAndId($current_company->getID(), $ids );
                    if ( $plf->getRecordCount() > 0 ) {
                        foreach($plf->rs as $p_obj) {
                            $plf->data = (array)$p_obj;
                            $p_obj = $plf;

                            $p_obj->setDeleted(TRUE);
                            $p_obj->setEnableCalcTotalTime( TRUE );
                            $p_obj->setEnableCalcSystemTotalTime( TRUE );
                            $p_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
                            $p_obj->setEnableCalcUserDateTotal( TRUE );
                            $p_obj->setEnableCalcException( TRUE );
                            if (  $p_obj->isValid() ) {
                                $p_obj->Save();
                            }
                        }
                    }
                    //$plf->FailTransaction();
                    $plf->CommitTransaction();
                }

                Redirect::Page( URLBuilder::getURL( array('saved_search_id' => $saved_search_id, 'sort_column' => $sort_column, 'sort_order' => $sort_order, 'page' => $page ), '/attendance/punchlist') );

                break;
            case 'search_form_delete':
            case 'search_form_update':
            case 'search_form_save':
            case 'search_form_clear':
            case 'search_form_search':

                $saved_search_id = $ugdf->searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, '/attendance/punchlist') );
            default:
                $fd = $filter_data;
                extract( $ugdf->getSearchFormData( $saved_search_id, $sort_column ) );
                $filter_data = [...$filter_data, ...$fd];

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
                $plf = new PunchListFactory();

                $hlf = new HierarchyListFactory();
                $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
                //Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
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

                //If they aren't searching, limit to the last pay period by default for performance optimization when there are hundreds of thousands of punches.
                if ( $action == '' AND isset($pay_period_ids[0]) AND isset($pay_period_ids[1]) AND !isset($filter_data['pay_period_ids']) ) {
                    $filter_data['pay_period_ids'] = array($pay_period_ids[0],$pay_period_ids[1]);
                }

                //Order In punches before Out punches.
                $sort_array = Misc::prependArray( $sort_array, array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'desc' ) );

                $plf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );


                $punch_status_options = $plf->getOptions('status');
                $punch_type_options = $plf->getOptions('type');

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

                foreach ($plf->rs as $p_obj) {
                    $plf->data = (array)$p_obj;
                    $p_obj = $plf;
                    //Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
                    $user_obj = $ulf->getById( $p_obj->getColumn('user_id') )->getCurrent();

                    $rows[] = array(
                                'id' => $p_obj->getColumn('punch_id'),
                                'punch_control_id' => $p_obj->getPunchControlId(),

                                'user_id' => $p_obj->getColumn('user_id'),
                                'first_name' => $user_obj->getFirstName(),
                                'last_name' => $user_obj->getLastName(),
                                'title' => Option::getByKey($user_obj->getTitle(), $title_options ),
                                'group' => Option::getByKey($user_obj->getGroup(), $group_options ),
                                'default_branch' => Option::getByKey($user_obj->getDefaultBranch(), $branch_options ),
                                'default_department' => Option::getByKey($user_obj->getDefaultDepartment(), $department_options ),

                                'branch_id' => $p_obj->getColumn('branch_id'),
                                'branch' => Option::getByKey( $p_obj->getColumn('branch_id'), $branch_options ),
                                'department_id' => $p_obj->getColumn('department_id'),
                                'department' => Option::getByKey( $p_obj->getColumn('department_id'), $department_options ),
                                //'status_id' => $p_obj->getStatus(),
                                'status_id' => Option::getByKey($p_obj->getStatus(), $punch_status_options),
                                //'type_id' => $p_obj->getType(),
                                'type_id' => Option::getByKey($p_obj->getType(), $punch_type_options),
                                'date_stamp' => TTDate::getDate('DATE', TTDate::strtotime($p_obj->getColumn('date_stamp')) ),

                                'job_id' => $p_obj->getColumn('job_id'),
                                'job_name' => $p_obj->getColumn('job_name'),
                                'job_group_id' => $p_obj->getColumn('job_group_id'),
                                'job_item_id' => $p_obj->getColumn('job_item_id'),

                                'time_stamp' => TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ),

                                'is_owner' => $permission->isOwner( $p_obj->getCreatedBy(), $current_user->getId() ),
                                'is_child' => $permission->isChild( $p_obj->getColumn('user_id'), $permission_children_ids ),
                            );

                }
                $viewData['rows'] = $rows;

                $all_array_option = array('-1' => '-- Any --');

                $ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
                $filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

                //Select box options;
                $filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
                $filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
                $filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
                $filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
                $filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
                $filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
                $filter_data['punch_status_options'] = Misc::prependArray( $all_array_option, $punch_status_options );
                $filter_data['punch_type_options'] = Misc::prependArray( $all_array_option, $punch_type_options );

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

                break;
        }

        return view('punch/PunchList', $viewData);
        
	}
}


?>
