<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class AbsencePolicyList extends Controller
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
        if ( !$permission->Check('absence_policy','enabled')
				OR !( $permission->Check('absence_policy','view') OR $permission->Check('absence_policy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Absence Policy List';

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

		$aplf = new AbsencePolicyListFactory();
		$aplf->getByCompanyId( $current_company->getId() );

		$pager = new Pager($aplf);

		$type_options = $aplf->getOptions('type');

		foreach ($aplf->rs as $ap_obj) {
			$aplf->data = (array)$ap_obj;
			$ap_obj = $aplf;

			$policies[] = array(
								'id' => $ap_obj->getId(),
								'name' => $ap_obj->getName(),
								'type_id' => $ap_obj->getType(),
								'type' => $type_options[$ap_obj->getType()],
								'deleted' => $ap_obj->getDeleted()
							);

		}

		$viewData['policies'] = $policies;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();
        return view('policy/AbsencePolicyList', $viewData);

    }

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditAbsencePolicy', FALSE) );
	}

	public function delete( $ids ){

		$current_company = $this->currentCompany;
		
		$aplf = new AbsencePolicyListFactory();
		$delete = TRUE;

		foreach ($ids as $id) {
			$aplf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($aplf->rs as $ap_obj) {
				$aplf->data = (array)$ap_obj;
				$ap_obj = $aplf;

				$ap_obj->setDeleted($delete);
				if ( $ap_obj->isValid() ) {
					$ap_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsencePolicyList') );

	}

}


?>