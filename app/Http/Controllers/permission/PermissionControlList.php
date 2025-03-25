<?php

namespace App\Http\Controllers\permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PermissionControlList extends Controller
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
        if ( !$permission->Check('permission','enabled')
				OR !( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Permission Group List';

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$pclf = new PermissionControlListFactory();
		$pclf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($pclf);

		foreach ($pclf->rs as $pc_obj) {
			$pclf->data = (array)$pc_obj;
			$pc_obj = $pclf;

			$rows[] = array(
								'id' => $pc_obj->getId(),
								'name' => $pc_obj->getColumn('name'),
								'description' => $pc_obj->getColumn('description'),
								'level' => $pc_obj->getLevel(),

								'deleted' => $pc_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );


		$smarty->display('permission/PermissionControlList.tpl');
        return view('accrual/ViewUserAccrualList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditPermissionControl.php', FALSE) );
	}

	public function copy(){
		$pclf = new PermissionControlListFactory();

		$pclf->StartTransaction();

		foreach ($ids as $id) {
			$pclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pclf->rs as $pc_obj) {
				$pclf->data = (array)$pc_obj;
				$pc_obj = $pclf;

				$permission_arr = $pc_obj->getPermission();

				$pc_obj->setId(FALSE);
				$pc_obj->setName( Misc::generateCopyName( $pc_obj->getName() ) );
				if ( $pc_obj->isValid() ) {
					$pc_obj->Save(FALSE);
					$pc_obj->setPermission( $permission_arr );
				}
				unset($pc_obj, $permission_arr);

			}
		}

		$pclf->CommitTransaction();

		Redirect::Page( URLBuilder::getURL( NULL, 'PermissionControlList.php') );

	}

	public function delete(){
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$pclf = new PermissionControlListFactory();

		foreach ($ids as $id) {
			$pclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($pclf->rs as $pc_obj) {
				$pclf->data = (array)$pc_obj;
				$pc_obj = $pclf;

				$pc_obj->setDeleted($delete);
				if ( $pc_obj->isValid() ) {
					$pc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'PermissionControlList.php') );

	}

}


?>