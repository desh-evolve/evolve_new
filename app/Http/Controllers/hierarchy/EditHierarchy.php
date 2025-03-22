<?php

namespace App\Http\Controllers\hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditHierarchy extends Controller
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
				OR !( $permission->Check('hierarchy','edit') OR $permission->Check('hierarchy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'Edit Hierarchy';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'id',
				'old_id',
				'user_data'
			) 
		) );

		$ft = new FastTree($fast_tree_options);
		$ft->setTree( $hierarchy_id );

		$hf = new HierarchyFactory();

		$redirect=0;

		//BreadCrumb::setCrumb($title);
		if ( isset($id) AND !isset($user_data['user_id']) ) {
			$user_data['user_id'] = $id;
		}

		$hlf = new HierarchyListFactory();

		//$nodes = $hlf->FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'TEXT', TRUE);
		//$nodes = FastTree::FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'TEXT', TRUE);
		$nodes = FastTree::FormatArray( $hlf->getByCompanyIdAndHierarchyControlId( $current_company->getId(), $hierarchy_id ), 'TEXT', TRUE);

		foreach($nodes as $node) {
			$parent_list_options[$node['id']] = $node['text'];
		}

		//Get include employee list.
		$ulf = new UserListFactory();
		$ulf->getByCompanyId( $current_company->getId() );
		$raw_user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		//$raw_user_list_options = UserListFactory::getByCompanyIdArray( $current_company->getId() );

		//Only allow them to select employees not already in the tree.
		unset($parent_list_options[$id]); //If we're editing a single entry, include that user in the list.
		$parent_list_keys = array_keys($parent_list_options);
		$user_options = Misc::arrayDiffByKey( (array)$parent_list_keys, $raw_user_options );

		$src_user_options = Misc::arrayDiffByKey( (array)$user_data['user_id'], $user_options );
		$selected_user_options = Misc::arrayIntersectByKey( (array)$user_data['user_id'], $user_options );

		//$viewData['user_list_options'] = $user_list_options;
		$viewData['src_user_options'] = $src_user_options;
		$viewData['selected_user_options'] = $selected_user_options;
		$viewData['parent_list_options'] = $parent_list_options;

		if ( isset($id) AND $id != '' AND $redirect == 0) {
			Debug::Text(' ID: '. $id , __FILE__, __LINE__, __METHOD__,10);
			$node = $hlf->getByHierarchyControlIdAndUserId( $hierarchy_id, $id);

			$viewData['selected_node'] = $node;
		} else {
			$id = $user_data['user_id'][0];
		}

		$viewData['hierarchy_id'] = $hierarchy_id;
		$viewData['id'] = $id;
		$viewData['old_id'] = $id;
		$viewData['hf'] = $hf;

        return view('hierarchy/EditHierarchy', $viewData);

    }

	public function submit(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'id',
				'old_id',
				'user_data'
			) 
		) );

		$ft = new FastTree($fast_tree_options);
		$ft->setTree( $hierarchy_id );

		$hf = new HierarchyFactory();

		$redirect=0;


		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($user_data['user_id']) ) {
			foreach( $user_data['user_id'] as $user_id ) {
				if ( isset($id) AND $id != '') {
					$hf->setId( $id );
				}

				$hf->setHierarchyControl( $hierarchy_id );
				$hf->setPreviousUser( $old_id );
				//$hf->setUser( $user_data['user_id'] );
				$hf->setUser( $user_id );
				$hf->setParent( $user_data['parent_id'] );

				if ( isset($user_data['share']) ) {
					Debug::Text(' Setting share!: ', __FILE__, __LINE__, __METHOD__,10);
					$hf->setShared( TRUE );
				} else {
					$hf->setShared( FALSE );
				}

				if ( $hf->isValid() ) {
					Debug::Text(' Valid!: ', __FILE__, __LINE__, __METHOD__,10);

					if ( $hf->Save() === FALSE ) {
						$redirect++;
					}
				} else {
					$redirect++;
				}
			}
		}

		if ( $redirect == 0 ) {
			Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'HierarchyList') );
		}
	}
}


?>