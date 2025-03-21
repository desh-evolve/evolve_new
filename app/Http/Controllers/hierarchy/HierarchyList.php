<?php

namespace App\Http\Controllers\hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FastTree;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use Illuminate\Support\Facades\View;

class HierarchyList extends Controller
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
        if ( !$permission->Check('hierarchy','enabled')
				OR !( $permission->Check('hierarchy','view') OR $permission->Check('hierarchy','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
    }

    public function index() {

        $viewData['title'] = 'Hierarchy Tree';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'ids'
			) 
		) );


		$hlf = new HierarchyListFactory();
		//$nodes = $hlf->FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'HTML' );
		//$nodes = FastTree::FormatArray( $hlf->getByHierarchyControlId( $hierarchy_id ), 'HTML' );
		$nodes = FastTree::FormatArray( $hlf->getByCompanyIdAndHierarchyControlId( $current_company->getId(), $hierarchy_id ), 'HTML' );


		//For some reason smarty prints out a blank row if nodes is false.
		if ( $nodes !== FALSE ) {
			$viewData['users'] = $nodes;
		}

		$viewData['hierarchy_id'] = $hierarchy_id;

        return view('hierarchy/HierarchyList', $viewData);

    }

	public function add(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'ids'
			) 
		) );

		Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'EditHierarchy') );
	}

	public function delete(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'hierarchy_id',
				'ids'
			) 
		) );

		foreach ($ids as $id) {
			Debug::Text(' Deleting ID: '. $id , __FILE__, __LINE__, __METHOD__,10);

	        $hf = new HierarchyListFactory();
			$hf->setUser( $id );
			$hf->setHierarchyControl( $hierarchy_id );
			$hf->Delete();
		}

		//FIXME: Get parent ID of each node we're deleting and clear the cache based on the hierarchy_id and it instead
		if ( isset($hf) AND is_object($hf) ) {
			$hf->removeCache( NULL, $hf->getTable(TRUE) ); //On delete we have to delete the entire group.
		}
		unset($hf);

		Redirect::Page( URLBuilder::getURL( array('hierarchy_id' => $hierarchy_id) , 'HierarchyList') );

	}
}

?>