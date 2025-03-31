<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Core\TTi18n;
use App\Models\Core\BreadCrumb;
use App\Models\Core\Debug;
use App\Models\Core\FastTree;
use App\Models\users\UserGroupListFactory;

class UserGroupList extends Controller
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

    public function index(Request $request)
    {
        // if (!$this->permission->Check('user', 'enabled') || 
        //     !$this->permission->Check('user', 'view')) {
        //     return $this->permission->Redirect(false);
        // }

        $current_company = $this->currentCompany;
        $current_user_prefs = $this->userPrefs;

        // Get FORM variables
        $ids = $request->input('ids', []);

        

        // Default case - show list
        $uglf = new UserGroupListFactory();
		$uglf->getByCompanyId($current_company->getId());
    
		// Prepare the data for the view
		$rows = [];
		foreach ($uglf->rs as $row) {
			$rows[] = [
				'id' => $row->id,
				'name' => $row->name,
				'deleted' => $row->deleted,
				// Add default spacing and level for flat display
				'spacing' => '',
				'level' => 0
			];
		}

        $viewData[ 'title' ] = !empty($id)?"Edit":"Add";
        
        
		$viewData = [
			'title' => 'User Groups',
			'rows' => $rows,
			'permission' => $this->permission
		];

        return view('users.UserGroupList', $viewData);
    }

    public function add()
    {
        return Redirect::Page(URLBuilder::getURL(null, 'EditUserGroup.php', false));
    }

    public function deleteOrUndelete($ids, $delete)
    {
        $current_company = $this->currentCompany;
        $uglf = new UserGroupListFactory();
        
        foreach ($ids as $id) {
            $uglf->getById($id);
            foreach ($uglf as $obj) {
                $obj->setDeleted($delete);
                $obj->Save();
            }
        }
        
        return Redirect::Page(URLBuilder::getURL(null, 'UserGroupList.php'));
    }
}