<?php

namespace App\Http\Controllers\department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use Illuminate\Support\Facades\View;

class DepartmentList extends Controller
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

        /*
        if ( !$permission->Check('department','enabled')
				OR !( $permission->Check('department','view') OR $permission->Check('department','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'Department List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids'
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$dlf = new DepartmentListFactory();
		$dlf->GetByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($dlf);

		$departments = array();
		if ( $dlf->getRecordCount() > 0 ) {
			foreach ($dlf->rs as $department) {
				$dlf->data = (array)$department;
				$department = $dlf;

				$departments[] = array(
					'id' => $department->GetId(),
					'status_id' => $department->getStatus(),
					'manual_id' => $department->getManualID(),
					'name' => $department->getName(),
					'deleted' => $department->getDeleted()
				);
			}
		}

		$smarty->assign_by_ref('departments', $departments);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

        return view('department/DepartmentList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL(NULL, 'EditDepartment.php') );
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
				'ids'
			) 
		) );

		$dlf = new DepartmentListFactory();

		foreach ($ids as $id) {
			$dlf->GetByIdAndCompanyId($id, $current_company->getId() );
			foreach ($dlf->rs as $department) {
				$dlf->data = (array)$department;
				$department = $dlf;

				$department->setDeleted($delete);
				$department->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'DepartmentList.php') );

	}
}

?>