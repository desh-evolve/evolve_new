<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserPasswordNew extends Controller
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


    public function index()
    {
        return view('users.editUserPassword');
    }


    public function newindex()
    {
        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $permission = $this->permission;

        $ulf = new UserListFactory();
        $uf = new UserFactory();

        $viewData['title'] = 'Change Web Password';

        if ( !$permission->Check('user','enabled')
                OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own_password') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }


        if ( isset($id) ) {
			Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			//If this user only has edit_own permissions, force his own user_id.
			if ( $permission->Check('user','edit_own') AND !$permission->Check('user','edit')  ) {
				$id = $current_user->getId();
			}

			$ulf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($ulf as $user) {
				Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);

				$user_data = array(
									'id' => $user->getId(),
									'company' => $user->getCompany(),
									'status' => $user->getStatus(),
									'user_name' => $user->getUserName(),
									'password' => $user->getPassword(),
									'phone_id' => $user->getPhoneId(),
									'phone_password' => $user->getPhonePassword(),
									'first_name' => $user->getFirstName(),
									'middle_name' => $user->getMiddleName(),
									'last_name' => $user->getLastName(),
									'sex' => $user->getSex(),
									'address1' => $user->getAddress1(),
									'address2' => $user->getAddress2(),
									'city' => $user->getCity(),
									'province' => $user->getProvince(),
									'country' => $user->getCountry(),
									'postal_code' => $user->getPostalCode(),
									'work_phone' => $user->getWorkPhone(),
									'work_phone_ext' => $user->getWorkPhoneExt(),
									'home_phone' => $user->getHomePhone(),
									'mobile_phone' => $user->getMobilePhone(),
									'fax_phone' => $user->getFaxPhone(),
									'home_email' => $user->getHomeEmail(),
									'work_email' => $user->getWorkEmail(),
									'birth_date' => $user->getBirthDate(),
									'hire_date' => $user->getHireDate(),
									'sin' => $user->getSIN(),
									'created_date' => $user->getCreatedDate(),
									'created_by' => $user->getCreatedBy(),
									'updated_date' => $user->getUpdatedDate(),
									'updated_by' => $user->getUpdatedBy(),
									'deleted_date' => $user->getDeletedDate(),
									'deleted_by' => $user->getDeletedBy()
								);
			}
		} elseif ( $action == 'submit') {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset( $uf ) AND is_object($uf) AND !isset($user_data['user_name'] ) ) {
				//Do this so the user_name still displays on form error.
				$user_data['user_name'] = $uf->getUserName();
			}
		}


        return view('users.editUserPassword');
    }

}
