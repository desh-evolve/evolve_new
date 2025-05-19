<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\TTLog;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserPhonePasswordNew extends Controller
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
        //         OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own_phone_password') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }

        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $ulf = new UserListFactory();
        $uf = new UserFactory();

        $viewData['title'] = 'Change Quick Punch Password';

        $id = $current_user->getId();

		$ulf->GetByIdAndCompanyId($id, $current_company->getId() );
        $user_data = [];

        if ( isset($id) ) {
			Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			//If this user only has edit_own permissions, force his own user_id.
			// if ( $permission->Check('user','edit_own') AND !$permission->Check('user','edit')  ) {
			// 	$id = $current_user->getId();
			// }

			foreach ($ulf->rs as $user) {
                $ulf->data = (array)$user;
                $user = $ulf;

				// Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);

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
		} else {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset( $uf ) AND is_object($uf) ) {
				//Do this so the user_name still displays on form error.
				if ( !isset($user_data['user_name']) ) {
					$user_data['user_name'] = $uf->getUserName();
				}
				if ( !isset($user_data['phone_id']) ) {
					$user_data['phone_id'] = $uf->getPhoneId();
				}
				if ( !isset( $user_data['phone_password'] ) ) {
					$user_data['phone_password'] = $uf->getPhonePassword();
				}
			}
		}


        $viewData['user_data'] = $user_data;
        $viewData['uf'] = $uf;
        // dd($viewData);

        return view('users.EditUserPhonePassword', $viewData);

    }


    public function save(Request $request)
    {
        $current_user = $this->currentUser;
        $current_company = $this->currentCompany;
        $permission = $this->permission;
        
        $user_data = $request->all();
        // dd($request->all());

        $ulf = new UserListFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//If this user only has edit_own permissions, force his own user_id.
		if ( $permission->Check('user','edit_own') AND !$permission->Check('user','edit')  ) {
			$user_data['id'] = $current_user->getId();
		}

		// Security measure.
		if ( !empty($user_data['id']) ) {
			$uf = $ulf->GetByIdAndCompanyId($user_data['id'], $current_company->getId() )->getCurrent();

			if ( !empty($user_data['current_password']) ) {
				if ( $uf->checkPhonePassword($user_data['current_password']) !== TRUE ) {
					Debug::Text('Password check failed!', __FILE__, __LINE__, __METHOD__,10);
					$uf->Validator->isTrue(	'current_password',
											FALSE,
											_('Current password is incorrect') );
				}
			} else {
				Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__,10);
				$uf->Validator->isTrue(	'current_password',
										FALSE,
										_('Current password is incorrect') );

			}


			if ( !empty($user_data['id']) ) {
				if ( !empty($user_data['password']) OR !empty($user_data['password2']) ) {
					if ( $user_data['password'] == $user_data['password2'] ) {
						$uf->setPhonePassword($user_data['password']);
					} else {
						$uf->Validator->isTrue(	'password',
												FALSE,
												__('Passwords don\'t match') );
					}
				} else {
					$uf->Validator->isTrue(	'password',
											FALSE,
											__('Passwords don\'t match') );

				}
			}

			if ( $uf->isValid() ) {
				if ( DEMO_MODE == FALSE ) {
					TTLog::addEntry( $uf->getId(), 20, _('Password - Phone'), NULL, $uf->getTable() );
					$uf->Save();
				}

				if ( $user_data['id'] != $current_user->getId() ) {
					//Probably coming from the user list.
                    return redirect()->to(URLBuilder::getURL(null, '/user/quick_punch_password'))->with('success', 'Quick Punch Password saved successfully.');
				} else {
                    return redirect()->back()->withErrors(['error' => 'Invalid data.'])->withInput();
				}

			}
        }
    }


}
