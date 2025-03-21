<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class WageGroupList extends Controller
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
				OR !( $permission->Check('wage','view') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

        */
    }

    public function index() {
		$current_company = $this->currentCompany;
        $viewData['title'] = 'Wage Group List';

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

		$wglf = new WageGroupListFactory();
		$wglf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(),$page, NULL, $sort_array );

		$pager = new Pager($wglf);

		foreach ($wglf as $group_obj) {
			$wglf->data = (array)$group_obj;
			$group_obj = $wglf;

			$groups[] = array (
				'id' => $group_obj->getId(),
				'name' => $group_obj->getName(),
				'deleted' => $group_obj->getDeleted()
			);

		}
		
		$viewData['groups'] = $groups;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('accrual/ViewUserAccrualList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL(NULL, 'EditWageGroup.php') );
	}

	public function delete(){

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids'
			) 
		) );

		$current_company = $this->currentCompany;
		$delete = TRUE;

		$wglf = new WageGroupListFactory();
		foreach ($ids as $id) {
			$wglf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($wglf->rs as $wg_obj) {
				$wglf->data = (array)$wg_obj;
				$wg_obj = $wglf;

				$wg_obj->setDeleted($delete);
				if ( $wg_obj->isValid() == TRUE ) {
					$wg_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL(NULL, 'WageGroupList.php') );

	}
}

?>