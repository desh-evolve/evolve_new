<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWorkExperionceFactory;
use App\Models\Users\UserWorkExperionceListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserWorkExperionce extends Controller
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
        $uwef = new UserWorkExperionceFactory();

        $viewData['title'] = 'Employee Work Experience';
        $data = [];

        if ( isset($id) ) {

            $uwelf = new UserWorkExperionceListFactory();
            $uwelf->getById($id);

            foreach ($uwelf->rs as $uwef_obj) {
                $uwelf->data = (array)$uwef_obj;
				$uwef_obj = $uwelf;

                $data = array(
                    'id' => $uwef_obj->getId(),
                    'user_id' => $uwef_obj->getUser(),
                    'company_name' => $uwef_obj->getCompanyName(),
                    'from_date' => $uwef_obj->getFromDate(),
                    'to_date' => $uwef_obj->getToDate(),
                    'department' => $uwef_obj->getDepartment(),
                    'designation' => $uwef_obj->getDesignation(),
                    'remaks' => $uwef_obj->getRemarks(),

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
		$viewData['uwef'] = $uwef;
        // dd($viewData);

        return view('users/EditUserWorkExperionce', $viewData);
    }

    public function save(Request $request)
    {
        $uwef = new UserWorkExperionceFactory();
        $data = $request->all();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

        // print_r($data);
        //  exit();

        if ( isset($data) ) {
            if ( isset($data['from_date']) AND $data['from_date'] != '') {
                $data['from_date'] = TTDate::parseDateTime($data['from_date']);
            }

            if ( isset($data['to_date']) AND $data['to_date'] != '') {
                $data['to_date'] = TTDate::parseDateTime($data['to_date']);
            }
        }

        $uwef->setId( $data['id'] );
        $uwef->setUser( $data['user_id'] );
        $uwef->setCompanyName( $data['company_name'] );
        $uwef->setFromDate( $data['from_date'] );
        $uwef->setToDate( $data['to_date']);
                $uwef->setDepartment( $data['department'] );
        $uwef->setDesignation( $data['designation'] );
                $uwef->setRemarks( $data['remaks'] );

        if ( $uwef->isValid() ) {
            $uwef->Save();

            return redirect()->to(URLBuilder::getURL(array('filter_user_id' => $data['user_id']) , '/user/work_experionce'))->with('success', 'Employee Work Experience saved successfully.');
        }
        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();

    }

}
