<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserEducationFactory;
use App\Models\Users\UserEducationListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserEducation extends Controller
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
        $uef = new UserEducationFactory();

        $viewData['title'] = 'Employee Qualification';
        $data = [];

        if ( isset($id) ) {

            $uelf = new UserEducationListFactory();
            $uelf->getById($id);

            foreach ($uelf->rs as $uef_obj) {
                $uelf->data = (array)$uef_obj;
				$uef_obj = $uelf;

                $data = array(
                    'id' => $uef_obj->getId(),
                    'user_id' => $uef_obj->getUser(),
                    'qualification' => $uef_obj->getQualificationName(),
                    'institute' => $uef_obj->getInstitute(),
                    'year' => $uef_obj->getYear(),
                    'remaks' => $uef_obj->getRemarks(),

                );

            }


        }

        // else {
        //     $data['user_id']= $filter_user_id;
        // }

        $ulf = new UserListFactory();
        $user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

        $data['user_options'] = $user_options;

        $viewData['data'] = $data;
		$viewData['uef'] = $uef;
        // dd($viewData);

        return view('users/EditUserEducation', $viewData);

    }


    public function save(Request $request)
    {
        $uef = new UserEducationFactory();
        $data = $request->all();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$uef->setId( $data['id'] );
		$uef->setUser( $data['user_id'] );
		$uef->setQualificationName( $data['qualification'] );
		$uef->setInstitute( $data['institute'] );
		$uef->setYear( $data['year']);
        $uef->setRemarks( $data['remaks'] );


		if ( $uef->isValid() ) {
			$uef->Save();

            return redirect()->to(URLBuilder::getURL(array('filter_user_id' => $data['user_id']) , '/user/qualification'))->with('success', 'Employee Qualification saved successfully.');

		}
         // If validation fails, return back with errors
         return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
    }


}
