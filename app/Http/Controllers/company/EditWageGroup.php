<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupFactory;
use App\Models\Company\WageGroupListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditWageGroup extends Controller
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
				OR !( $permission->Check('wage','edit') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
    }

    public function index($id = null) {

        $viewData['title'] = 'Edit Wage Group';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'group_data'
			) 
		) );

		if ( isset($id) ) {

			$wglf = new WageGroupListFactory();

			$wglf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($wglf->rs as $group_obj) {
				$wglf->data = (array)$group_obj;
				$group_obj = $wglf;
				//Debug::Arr($title_obj,'Title Object', __FILE__, __LINE__, __METHOD__,10);

				$group_data = array(
									'id' => $group_obj->getId(),
									'name' => $group_obj->getName(),
									'created_date' => $group_obj->getCreatedDate(),
									'created_by' => $group_obj->getCreatedBy(),
									'updated_date' => $group_obj->getUpdatedDate(),
									'updated_by' => $group_obj->getUpdatedBy(),
									'deleted_date' => $group_obj->getDeletedDate(),
									'deleted_by' => $group_obj->getDeletedBy()
								);
			}
		}

		$smarty->assign_by_ref('group_data', $group_data);
		$smarty->assign_by_ref('wgf', $wgf);

        return view('company/EditWageGroup', $viewData);

    }

	public function submit(Request $request){
		$current_company = $this->currentCompany;
		$group_data = $request->data;

		$wgf = new WageGroupFactory();

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$wgf->setId($group_data['id']);
		$wgf->setCompany( $current_company->getId() );
		$wgf->setName($group_data['name']);

		if ( $wgf->isValid() ) {
			$wgf->Save();
			Redirect::Page( URLBuilder::getURL(NULL, 'WageGroupList') );
		}
	}
}

?>