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
        $permission = $this->permission;
        $current_user = $this->current_user;
        $current_company = $this->current_company;
        $current_user_prefs = $this->current_user_prefs;

        if ( !$permission->Check('user','enabled')
                OR !( $permission->Check('user','view') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }

        $viewData['title'] = 'Employee Group List';

        /*
        * Get FORM variables
        */
        extract	(FormVariables::GetVariables(
                                                array	(
                                                        'action',
                                                        'page',
                                                        'sort_column',
                                                        'sort_order',
                                                        'ids'
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

                Redirect::Page( URLBuilder::getURL( NULL, '/user_group/add', FALSE) );

                break;
            case 'delete' OR 'undelete':
                if ( strtolower($action) == 'delete' ) {
                    $delete = TRUE;
                } else {
                    $delete = FALSE;
                }

                $uglf = new UserGroupListFactory();

                foreach ($ids as $id) {
                    $uglf->getById($id );
                    foreach ($uglf->rs as $obj) {
                        $uglf->data = (array)$obj;
                        $obj = $uglf;

                        $obj->setDeleted($delete);
                        $obj->Save();
                    }
                }

                return redirect(URLBuilder::getURL(null, '/user_group', false));

                break;

            default:
                $uglf = new UserGroupListFactory();

                $nodes = FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'HTML' );

                //For some reason smarty prints out a blank row if nodes is false.
                if ( $nodes !== FALSE ) {
                    $viewData['rows'] = $nodes;
                }

                break;
        }

        return view('users/UserGroupList', $viewData);
    }
}