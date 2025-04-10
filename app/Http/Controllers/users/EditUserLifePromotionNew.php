<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserLifePromotionFactory;
use App\Models\Users\UserLifePromotionListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserLifePromotionNew extends Controller
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


    public function index($id = null)
    {
        // if ( !$permission->Check('user','enabled')
        //         OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

        $current_company = $this->currentCompany;
        $ulpf = new UserLifePromotionFactory();

        $viewData['title'] = 'Employee Promotions';
        $data = [];

        if ( isset($id) ) {

            $ulplf = new UserLifePromotionListFactory();
            $ulplf->getById($id);

            foreach ($ulplf->rs as $ulpf_obj) {
                $ulplf->data = (array)$ulpf_obj;
				$ulpf_obj = $ulplf;

                $data = array(
                    'id' => $ulpf_obj->getId(),
                    'user_id' => $ulpf_obj->getUser(),
                    'current_designation' => $ulpf_obj->getCurrentDesignation(),
                    'new_designation' => $ulpf_obj->getNewDesignation(),
                    'current_salary' => $ulpf_obj->getCurrentSalary(),
                    'new_salary' => $ulpf_obj->getNewSalary(),
                    'effective_date' => $ulpf_obj->getEffectiveDate(),

                );

            }


         }

        $ulf = new UserListFactory();
        $user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );


        $data['user_options'] = $user_options;
        // $data['user_id'] = $filter_user_id;

        $viewData['data'] = $data;
		$viewData['ulpf'] = $ulpf;
        // dd($viewData);

        return view('users/EditUserLifePromotion', $viewData);

    }


    public function save(Request $request)
    {
        $ulpf = new UserLifePromotionFactory();
        $data = $request->all();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

        // print_r($data);
        //  exit();

        // Ensure 'user_id' is set before proceeding
        if (empty($data['user_id'])) {
            return redirect()->back()->withErrors(['user_id' => 'User ID is required.'])->withInput();
        }


		$ulpf->setId( $data['id'] );
		$ulpf->setUser( $data['user_id'] );
		$ulpf->setCurrentDesignation( $data['current_designation'] );
		$ulpf->setNewDesignation( $data['new_designation'] );
		$ulpf->setCurrentSalary( $data['current_salary']);
        $ulpf->setNewSalary( $data['new_salary'] );
        // $ulpf->setEffectiveDate( $data['effective_date'] );
        if ( isset($data) ) {

            if ( isset($data['effective_date']) AND $data['effective_date'] != '') {
                $data['effective_date'] = TTDate::parseDateTime($data['effective_date']);
            }


        }


		if ( $ulpf->isValid() ) {
			$ulpf->Save();

            return redirect()->to(URLBuilder::getURL(array('filter_user_id' => $data['user_id']) , '/user/promotion'))->with('success', 'Employee Promotion saved successfully.');

		}

         // If validation fails, return back with errors
         return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();

    }


}
