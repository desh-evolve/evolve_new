<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\users\UserGroupListFactory;
use App\Models\users\UserGroupFactory;
use Illuminate\Support\Facades\View;

class EditUserGroup extends Controller
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
                OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }

        //Debug::setVerbosity(11);

        $viewData['title'] = 'Edit Employee Group';

        /*
        * Get FORM variables
        */
        extract	(FormVariables::GetVariables(
                                                array	(
                                                        'action',
                                                        'id',
                                                        'previous_parent_id',
                                                        'data'
                                                        ) ) );

        $ugf = new UserGroupFactory();

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
            case 'submit':
                Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

                $ugf->setId( $data['id'] );
                $ugf->setCompany( $current_company->getId() );
                $ugf->setPreviousParent( $previous_parent_id );
                $ugf->setParent( $data['parent_id'] );
                $ugf->setName( $data['name'] );
                
                if ( $ugf->isValid() ) {
                    $ugf->Save();

                    return redirect(URLBuilder::getURL(null, '/user_group', false));

                    break;
                }
            default:
                $uglf = new UserGroupListFactory();

                $nodes = FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);

                foreach($nodes as $node) {
                    $parent_list_options[$node['id']] = $node['text'];
                }

                $viewData['parent_list_options'] = $parent_list_options;

                if ( isset($id) ) {
                    
                    //Get parent data
                    $ft = new FastTree( ['table' => 'user_group_tree'] );
                    $ft->setTree( $current_company->getID() );

                    //$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
                    $uglf->getById( $id );
                    
                    foreach ($uglf->rs as $group_obj) {
                        $uglf->data = (array)$group_obj;
                        $group_obj = $uglf;

                        $parent_id = $ft->getParentID( $group_obj->getId() );
                        
                        $data = array(
                            'id' => $group_obj->getId(),
                            'previous_parent_id' => $parent_id,
                            'parent_id' => $parent_id,
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

                $viewData['data'] = $data;

                break;
        }

        $viewData['ugf'] = $ugf;
        
        return view('users/EditUserGroup', $viewData);
    }
}