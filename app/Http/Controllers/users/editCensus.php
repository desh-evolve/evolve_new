<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserCensusInformationFactory;
use App\Models\Users\UserCensusInformationListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditCensus extends Controller
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

        // if ( !$permission->Check('user','enabled')
        //         OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

    }


    public function index($id = null)
    {
        $current_company = $this->currentCompany;
        $ucif = new UserCensusInformationFactory();

        $viewData['title'] = 'Employee Census';
        $data = [];

        if ( isset($id) ) {

            $ucilf = new UserCensusInformationListFactory();
            $ucilf->getById($id);

            foreach ($ucilf->rs as $ucif_obj) {
                $ucilf->data = (array)$ucif_obj;
				$ucif_obj = $ucilf;

                $data = array(
                    'id' => $ucif_obj->getId(),
                    'user_id' => $ucif_obj->getUser(),
                    'dependant' => $ucif_obj->getDependant(),
                    'name' => $ucif_obj->getName(),
                    'relationship' => $ucif_obj->getRelationship(),
                    'dob' => $ucif_obj->getBirthDate(),
                    'nic' => $ucif_obj->getNic(),
                    'gender' => $ucif_obj->getGender(),
                );

            }
        }

        // else{
        //     $data['user_id']= $filter_user_id;
        // }

        $ulf = new UserListFactory();
        $user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

        $data['user_options'] = $user_options;
        $data['gender_options'] = $ucif->getOptions('gender');

        $viewData['data'] = $data;
		$viewData['ucif'] = $ucif;
        // dd($viewData);

        return view('users/EditCensus', $viewData);

    }


    public function save(Request $request)
    {
        $ucif = new UserCensusInformationFactory();
        $data = $request->all();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

        // print_r($data);
        //  exit();

        if ( isset($data) ) {
            if ( isset($data['dob']) AND $data['dob'] != '') {
                $data['dob'] = TTDate::parseDateTime($data['dob']);
            }
        }

		$ucif->setId( $data['id'] );
		$ucif->setUser( $data['user_id'] );
		$ucif->setDependant( $data['dependant'] );
		$ucif->setName( $data['name'] );
		$ucif->setRelationship( $data['relationship']);
        $ucif->setNic( $data['nic'] );
		$ucif->setBirthDate( $data['dob'] );
		$ucif->setGender( $data['gender'] );

		if ( $ucif->isValid() ) {
			$ucif->Save();

            return redirect()->to(URLBuilder::getURL(array('filter_user_id' => $data['user_id']) , '/user/census'))->with('success', 'Employee Census saved successfully.');
		}

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();

    }

}
