<?php

namespace App\Http\Controllers\hierarchy;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyControlListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeFactory;
use Illuminate\Support\Facades\View;

class HierarchyControlList extends Controller
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

		if ( !$permission->Check('hierarchy','enabled')
				OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Hierarchy List';

		// Get FORM variables
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'page',
														'sort_column',
														'sort_order',
														'ids',
														'id'
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


		//$ppslf = new PayPeriodScheduleFactory();

		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

		$action = !empty($_POST['action']) ? strtolower(str_replace(' ', '_', trim($_POST['action']))) : '';

		switch ($action) {
			case 'add':

				Redirect::Page( URLBuilder::getURL(NULL, '/company/hierarchy/add', FALSE) );

				break;
			case 'delete' OR 'undelete':
				if ( strtolower($action) == 'delete' ) {
					$delete = TRUE;
				} else {
					$delete = FALSE;
				}

				$hclf = new HierarchyControlListFactory();

				foreach ($ids as $id) {
					//$dsclf->GetByIdAndUserId($id, $current_user->getId() );
					$hclf->GetById($id);
					foreach ($hclf->rs as $hierarchy_control) {
						$hclf->data = (array)$hierarchy_control;
						$hierarchy_control = $hclf;

						$hierarchy_control->setDeleted($delete);
						$hierarchy_control->Save();
					}
				}

				Redirect::Page( URLBuilder::getURL(NULL, '/company/hierarchy/list') );

				break;

			default:
				$hclf = new HierarchyControlListFactory();
				$hclf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

				$pager = new Pager($hclf);

				$hotf = new HierarchyObjectTypeFactory();
				$object_type_options = $hotf->getOptions('object_type');

				foreach ($hclf->rs as $hierarchy_control) {
					$hclf->data = (array)$hierarchy_control;
					$hierarchy_control = $hclf;

					$object_type_ids = $hierarchy_control->getObjectType();

					$object_types = array();
					foreach($object_type_ids as $object_type_id) {
						if ( isset($object_type_options[$object_type_id]) ) {
							$object_types[] = $object_type_options[$object_type_id];
						}
					}

					$hierarchy_controls[] = array(
						'id' => $hierarchy_control->getId(),
						'name' => $hierarchy_control->getName(),
						'description' => $hierarchy_control->getDescription(),
						'object_types' => $object_types,
						'deleted' => $hierarchy_control->getDeleted()
						);

					unset($object_types);
				}

				$viewData['hierarchy_controls'] = $hierarchy_controls;

				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;

				$viewData['paging_data'] = $pager->getPageVariables() ;

				break;
		}
		
		return view('hierarchy/HierarchyControlList', $viewData);
	}
}


?>