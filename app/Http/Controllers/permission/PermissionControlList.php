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
use App\Models\Core\PermissionControlFactory;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PermissionControlList extends Controller
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

	public function index()
	{

		$permission = $this->permission;
		$current_user = $this->current_user;
		$current_company = $this->current_company;
		$current_user_prefs = $this->current_user_prefs;

		if ( !$permission->Check('permission','enabled')
				OR !( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		//Debug::setVerbosity(11);

		$viewData['title'] = 'Permission Group List';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'page',
														'sort_column',
														'sort_order',
														'ids',
														) ) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
													array(
															'sort_column' => $sort_column,
															'sort_order' => $sort_order,
															'page' => $page
														) );

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

		//===================================================================================
        $action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
        //===================================================================================
		
		switch ($action) {
			case 'add':

				Redirect::Page( URLBuilder::getURL( NULL, '/permission_control/add', FALSE) );

				break;
			case 'copy':
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

				Redirect::Page( URLBuilder::getURL( NULL, '/permission_control') );

				break;
			case 'delete':
			case 'undelete':
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

				Redirect::Page( URLBuilder::getURL( NULL, '/permission_control') );

				break;

			default:
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
				$viewData['rows'] = $rows;

				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;

				$viewData['paging_data'] = $pager->getPageVariables() ;

				break;
		}

		return view('permission/PermissionControlList', $viewData);
	}
}
