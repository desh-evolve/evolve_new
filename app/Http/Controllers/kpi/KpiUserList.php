<?php

namespace App\Http\Controllers\kpi;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserKpiListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Support\Facades\View;

class KpiUserList extends Controller
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

        /*
        if ( !$permission->Check('wage','enabled')
				OR !( $permission->Check('wage','view') OR $permission->Check('wage','view_child') OR $permission->Check('wage','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
    }

    public function index() {

        $viewData['title'] = 'Employee KPI List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'saved_search_id',
				'ids',
				'user_id'
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
		array (
			'user_id' => $user_id,
			'sort_column' => $sort_column,
			'sort_order' => $sort_order,
			'page' => $page
			) 
		);
		
		$ulf = new UserListFactory();
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$user_has_default_wage = FALSE;

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

		//$uwlf = new UserWageListFactory();
                //$ujlf = new UserJobListFactory();
                $uklf = new UserKpiListFactory();
		$uklf->GetByUserIdAndCompanyId($user_id, $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($uklf);

		//$wglf = new WageGroupListFactory();
		//$wage_group_options = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
		//Select box options;
		$blf = new BranchListFactory(); 
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );  
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$utlf = new UserTitleListFactory();
		$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );
                

		$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
		if ( is_object($user_obj) ) {
			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );


			if ( $permission->Check('wage','view')
					OR ( $permission->Check('wage','view_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','view_child') AND $is_child === TRUE ) ) {

				foreach ($uklf->rs as $kpi) {
					$uklf->data = (array)$kpi;
					$kpi = $uklf;

					$kpi_history[] = array(
										'id' => $kpi->getId(),
										'user_id' => $kpi->getUser(),
										//'wage_group_id' => $wage->getWageGroup(),
										//'wage_group' => Option::getByKey($wage->getWageGroup(), $wage_group_options ),
										//'type' => Option::getByKey($wage->getType(), $wage->getOptions('type') ),
										//'wage' => Misc::MoneyFormat( Misc::removeTrailingZeros($wage->getWage()), TRUE ),
										//'currency_symbol' => $currency_symbol,
										//'effective_date' => TTDate::getDate( 'DATE', $wage->getEffectiveDate() ),
                                            
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => Option::getByKey($job->getDefaultBranch(), $branch_options ), $job->getDefaultBranch(),     
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => Option::getByKey($job->getDefaultDepartment(), $department_options ),$job->getDefaultDepartment(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => Option::getByKey($kpi->getTitle(), $title_options ), $kpi->getTitle(), 
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'start_date' => TTDate::getDate( 'DATE', $kpi->getStartDate() ),
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'end_date' => TTDate::getDate( 'DATE', $kpi->getEndDate() ),  
                       /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'review_date' => TTDate::getDate( 'DATE', $kpi->getReviewDate() ),                                                                                                 
                                            
                       ///* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'note' => $job->getNote(),                                              
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'deleted' => $kpi->getDeleted()
									);
                                         

					//if ( $wage->getWageGroup() == 0 ) {
					//	$user_has_default_wage = TRUE;
					//}
				}                    
         
			}
		}

		$ulf = new UserListFactory();

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );

		if ( $permission->Check('wage','view') == FALSE ) {
			if ( $permission->Check('wage','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('wage','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );

		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$viewData['user_options'] = $user_options;
		$viewData['kpi_history'] = $kpi_history;
		$viewData['user_id'] = $user_id;
		//$viewData['user_has_default_wage'] = $user_has_default_wage;
		$viewData['saved_search_id'] = $saved_search_id;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('kpi/KpiUserList', $viewData);

    }

	public function add(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'saved_search_id',
				'ids',
				'user_id'
			) 
		) );

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id, 'saved_search_id' => $saved_search_id ), 'EditUserKpiOld', FALSE) );
	}

	public function delete(){
		$current_company = $this->currentCompany;
		
		$delete = TRUE;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'saved_search_id',
				'ids',
				'user_id'
			) 
		) );

		$uklf = new UserKpiListFactory();

		if ( $ids != '' ) {
			foreach ($ids as $id) {
				$uklf->getByIdAndCompanyId($id, $current_company->getId() );
				foreach ($uklf->rs as $kpi) {
					$uklf->data = (array)$kpi;
					$kpi = $uklf;

					$kpi->setDeleted($delete);
					$kpi->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id), 'KpiUserList') );
	}
}




?>