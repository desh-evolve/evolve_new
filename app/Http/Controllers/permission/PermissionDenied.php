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

    public function index() {
        
        $viewData['title'] = 'Permission Denied';

        /*
         * Get FORM variables
         */
        extract	(FormVariables::GetVariables(
                                                array	(
                                                        'action',
                                                        'id',
                                                        'data'
                                                        ) ) );
        
        return view('permission/PermissionDenied');
    }
}



?>