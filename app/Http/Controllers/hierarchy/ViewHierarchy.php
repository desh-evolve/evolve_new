<?php

namespace App\Http\Controllers\hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class ViewHierarchy extends Controller
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
        if ( !$permission->Check('hierarchy','enabled')
				OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'View Hierarchy';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'id'
			) 
		) );

		if ( isset($id) ) {

			$hlf = new HierarchyListFactory();

			$tmp_id = $id;
			$i=0;
			do {
				Debug::Text(' Iteration...', __FILE__, __LINE__, __METHOD__,10);
				$parents = $hlf->getParentLevelIdArrayByHierarchyControlIdAndUserId( $hierarchy_id, $tmp_id);

				$level = $hlf->getFastTreeObject()->getLevel( $tmp_id )-1;

				if ( is_array($parents) AND count($parents) > 0 ) {
					$parent_users = array();
					foreach($parents as $user_id) {
						//Get user information
						$ulf = new UserListFactory();
						$ulf->getById( $user_id );
						$user = $ulf->getCurrent();
						unset($ulf);

						$parent_users[] = array( 'name' => $user->getFullName() );
						unset($user);
					}

					$parent_groups[] = array( 'users' => $parent_users, 'level' => $level );
					unset($parent_users);
				}

				if ( isset($parents[0]) ) {
					$tmp_id = $parents[0];
				}
				
				$i++;
			} while ( is_array($parents) AND count($parents) > 0 AND $i < 100 );
		}
		
		$viewData['parent_groups'] = $parent_groups;

        return view('hierarchy/ViewHierarchy', $viewData);

    }
}


?>