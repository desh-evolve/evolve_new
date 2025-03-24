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
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class PermissionDenied extends Controller
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
        if ( $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')) {
            $user_id = $user_id;
        } else {
            $user_id = $current_user->getId();
        }
        */

        $viewData['title'] = 'Permission Denied';


		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'data'
			) 
		) );

        return view('permission/PermissionDenied', $viewData);

    }
}



?>