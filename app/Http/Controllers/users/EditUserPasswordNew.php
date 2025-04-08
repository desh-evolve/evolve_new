<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserPasswordNew extends Controller
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


    public function index()
    {
        return view('users.editUserPassword');
    }


    public function newindex()
    {
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $viewData['title'] = 'Change Web Password';

        if ( !$permission->Check('user','enabled')
                OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own_password') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }


                return view('users.editUserPassword');
    }

}
