<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\OtherFieldFactory;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditOtherField extends Controller
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

    public function index($id = null) {
		/*
        if ( !$permission->Check('other_field','enabled')
				OR !( $permission->Check('other_field','edit') OR $permission->Check('other_field','edit_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $off = new OtherFieldFactory();
        $viewData['title'] = 'Edit Other Field';

		if ( isset($id) ) {

			$oflf = new OtherFieldListFactory();

			//$uwlf->GetByUserIdAndCompanyId($current_user->getId(), $current_company->getId() );
			$oflf->getById($id, NULL, NULL, true);

			foreach ($oflf->rs as $obj) {
				$oflf->data = (array)$obj;
				$obj = $oflf;

				$data = array(
					'id' => $obj->getId(),
					'company_id' => $obj->getCompany(),
					'type_id' => $obj->getType(),
					'other_id1' => $obj->getOtherID1(),
					'other_id2' => $obj->getOtherID2(),
					'other_id3' => $obj->getOtherID3(),
					'other_id4' => $obj->getOtherID4(),
					'other_id5' => $obj->getOtherID5(),
					'created_date' => $obj->getCreatedDate(),
					'created_by' => $obj->getCreatedBy(),
					'updated_date' => $obj->getUpdatedDate(),
					'updated_by' => $obj->getUpdatedBy(),
					'deleted_date' => $obj->getDeletedDate(),
					'deleted_by' => $obj->getDeletedBy()
				);
			}
		}
        
		//Select box options;
		//$jif = new JobItemFactory();

		$data['type_options'] = $off->getOptions('type');

		$viewData['data'] = $data;
		$viewData['off'] = $off;

        return view('company/EditOtherField', $viewData);

    }

	public function save(Request $request)
    {
		$current_company = $this->currentCompany;

        $data = $request->all();
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

        $off = new OtherFieldFactory();

		$off->setId( $data['id'] );
		$off->setCompany( $current_company->getId() );
		$off->setType( $data['type_id'] );
		$off->setOtherID1( $data['other_id1'] );
		$off->setOtherID2( $data['other_id2'] );
		$off->setOtherID3( $data['other_id3'] );
		$off->setOtherID4( $data['other_id4'] );
		$off->setOtherID5( $data['other_id5'] );

		// if ( $off->isValid() ) {
		// 	$off->Save();
		// 	Redirect::Page( URLBuilder::getURL( array('type_id' => $data['type_id']), 'OtherFieldList.php') );
		// }

        if ($off->isValid()) {
            $off->Save();
            return redirect()->to(URLBuilder::getURL(array('type_id' => $data['type_id']), '/company/other_field'))->with('success', 'Other Field saved successfully.');
        }

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
	}
}

?>
