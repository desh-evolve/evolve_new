<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\OtherFieldFactory;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class OtherFieldList extends Controller
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
        if ( !$permission->Check('other_field','enabled')
				OR !( $permission->Check('other_field','view') OR $permission->Check('other_field','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		
        $viewData['title'] = 'Other Field List';

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				'type_id' => $type_id,
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$oflf = new OtherFieldListFactory(); 

		$oflf->getByCompanyId($current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($oflf);

		//Get types
		$off = new OtherFieldFactory();
		$type_options = $off->getOptions('type');

		foreach ($oflf->rs as $obj) {
			$oflf->data = (array)$obj;
			$obj = $oflf;

			$rows[] = array(
								'id' => $obj->getId(),
								'type_id' => $obj->getType(),
								'type' => $type_options[$obj->getType()],
								'other_id1' => $obj->getOtherID1(),
								'other_id2' => $obj->getOtherID2(),
								'other_id3' => $obj->getOtherID3(),
								'other_id4' => $obj->getOtherID4(),
								'other_id5' => $obj->getOtherID5(),
								//'user_id' => $wage->getUser(),
								//'type' => Option::getByKey($wage->getType(), $wage->getOptions('type') ),
								'deleted' => $obj->getDeleted()
							);

		}

		$viewData['rows'] = $rows;
		$viewData['type_id'] = $type_id;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('company/OtherFieldList', $viewData);

    }

	public function add(){

		Redirect::Page( URLBuilder::getURL(array('type_id' => $type_id), 'EditOtherField', FALSE) );
	}

	public function delete(){

		$delete = TRUE;

		$oflf = new OtherFieldListFactory();

		foreach ($ids as $id) {
			$oflf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($oflf->rs as $of_obj) {
				$oflf->data = (array)$of_obj;
				$of_obj = $oflf;

				$of_obj->setDeleted($delete);
				$of_obj->Save();
			}
		}

		Redirect::Page( URLBuilder::getURL(array('type_id' => $type_id), 'OtherFieldList') );

	}
}

?>