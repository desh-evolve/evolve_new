<?php

namespace App\Http\Controllers\bank_account;

use App\Http\Controllers\Controller;
use App\Models\Accrual\AccrualBalanceFactory;
use App\Models\Accrual\AccrualBalanceListFactory;
use App\Models\Accrual\AccrualFactory;
use App\Models\Accrual\AccrualListFactory;
use App\Models\Company\CompanyListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Users\BankAccountFactory;
use App\Models\Users\BankAccountListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class EditBankAccount extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;
    protected $currentUser;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->currentUser = View::shared('current_user');
        $this->permission = View::shared('permission');

    }

	public function index($user_id = null, $company_id =null)
    {
		/*

			if ( isset($company_id) AND $company_id != '' ) {
				if ( !$permission->Check('company','enabled')
						OR !( $permission->Check('company','edit_own_bank') ) ) {
					$permission->Redirect( FALSE ); //Redirect
				}
			} else {
				if ( !$permission->Check('user','enabled')
						OR !( $permission->Check('user','edit_bank') OR $permission->Check('user','edit_own_bank') ) ) {
					$permission->Redirect( FALSE ); //Redirect
				}
			}

        */

        $current_company = $this->company;
        $current_user = $this->currentUser;
        $permission = $this->permission;

		$viewData['title'] = 'Bank Account';
		$baf = new BankAccountFactory();
		$balf = new BankAccountListFactory();
		$ulf = new UserListFactory();
        $clf = new CompanyListFactory();

		$country = NULL;
        $view_type = '';
        $bank_data = [];

		if ( ( $user_id == '' OR $user_id == $current_user->getId() ) AND $company_id == '' /* AND $permission->Check('user','edit_own_bank') */ ) {
			//Fetch Current user Bank Details
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			$user_id = $current_user->getId();

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
            $full_name = $ulf->getById( $user_id )->getCurrent()->getFullName();
            $view_type = 'user';

		} elseif ( $user_id != '' /* AND $permission->Check('user','edit_bank') */ ) {
			//Specified User
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
            $full_name = $ulf->getById( $user_id )->getCurrent()->getFullName();
            $view_type = 'user';

		} elseif ( $company_id != '' /*  AND $permission->Check('company','edit_own_bank') */ ) {
			//Fetch Current company Bank Details
			$balf->getCompanyAccountByCompanyId( $current_company->getId() );
			$country = $current_company->getCountry();
            $full_name = $clf->getById($company_id)->getCurrent()->getName();
            $view_type = 'company';

		} else {
			$permission->Redirect( FALSE );
		}


		foreach ($balf->rs as $bank_account) {
			$balf->data = (array)$bank_account;
			$bank_account = $balf;
			//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

			$bank_data = array(
				'id' => $bank_account->getId(),
				'country' => strtolower($country),
				'institution' => $bank_account->getInstitution(),
				'transit' => $bank_account->getTransit(), //'account' => $bank_account->getSecureAccount(),//ARSP EDIT --> I Hide THIS CODE REASON- WE CANT SEE ORIGINAL ACCOUNT NUMBER
				'account' => $bank_account->getAccount(),//ARSP EDIT --> I ADD NEW CODE SHOW ONLY ORIGINAL ACCOUNT NUMBER
				'bank_name' => $bank_account->getBankName(),//ARSP EDIT --> I ADD NEW CODE FOR BANK NAME
				'bank_branch' => $bank_account->getBankBranch(),//ARSP EDIT --> I ADD NEW CODE FOR BANK BRANCH NAME
				'created_date' => $bank_account->getCreatedDate(),
				'created_by' => $bank_account->getCreatedBy(),
				'updated_date' => $bank_account->getUpdatedDate(),
				'updated_by' => $bank_account->getUpdatedBy(),
				'deleted_date' => $bank_account->getDeletedDate(),
				'deleted_by' => $bank_account->getDeletedBy()
			);
		}

		// if ( isset($user_id) AND $company_id == '' ) {
		// 	//$user_id = $current_user->getId();
		// 	$ulf = new UserListFactory();
		// 	$full_name = $ulf->getById( $user_id )->getCurrent()->getFullName();
		// } elseif ( $company_id != '' ) {
		// 	$clf = new CompanyListFactory();
		// 	$full_name = $clf->getById( $company_id )->getCurrent()->getName();
		// }

        // Pass data to Blade
		$bank_data['full_name'] = $full_name;
		$bank_data['country'] = strtolower($country);
		$bank_data['user_id'] = $user_id;
		$bank_data['company_id'] = $company_id;
		// $viewData['data_saved'] = $data_saved;
        $bank_data['view_type'] = $view_type;
        $viewData['bank_data'] = $bank_data;
        $viewData['baf'] = $baf;
        $viewData['company'] = $current_company;
        $viewData['current_user'] = $current_user;

        // dd($viewData);

        return view('bank_account.EditBankAccount', $viewData);

	}


    public function userIndex($user_id)
    {
        return $this->index($user_id, null);
    }


    public function companyIndex($company_id)
    {
        return $this->index(null, $company_id);
    }



	// public function delete()
    // {
    //     $current_company = $this->company;
    //     $current_user = $this->currentUser;
    //     $permission = $this->permission;

	// 	Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);
	// 	Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);

	// 	$balf = new BankAccountListFactory();

	// 	if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_own_bank') ) {
	// 		Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
	// 		$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
	// 		$redirect_arr = array('data_saved' => TRUE );
	// 	} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_bank') ) {
	// 		Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
	// 		$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );
	// 		$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
	// 	} elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' AND $permission->Check('company','edit_own_bank') ) {
	// 		Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
	// 		//Company bank.
	// 		$balf->GetCompanyAccountByCompanyId( $current_company->getId() );
	// 		$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
	// 	} else {
	// 		$permission->Redirect( FALSE );
	// 	}

	// 	Debug::Text('Found Records: '. $balf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
	// 	if ( $balf->getRecordCount() > 0 ) {
	// 		$b_obj = $balf->getCurrent();
	// 		$b_obj->setDeleted(TRUE);
	// 		$b_obj->Save();

	// 		Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/bank_account/EditBankAccount.php') );
	// 	}
	// }

    public function delete(Request $request)
    {
        $current_company = $this->company;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        // Get data from request
        $bank_data = $request->all();
        $user_id = $bank_data['user_id'] ?? null;
        $company_id = $bank_data['company_id'] ?? null;

        Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__, 10);
        Debug::Text('User ID: ' . ($user_id ?? 'N/A') . ' Company ID: ' . ($company_id ?? 'N/A'), __FILE__, __LINE__, __METHOD__, 10);

        $balf = new BankAccountListFactory();

        if (!empty($user_id) && $user_id == $current_user->getId() && empty($company_id) /* && $permission->Check('user', 'edit_own_bank') */ ) {
            Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__, 10);
            $balf->GetUserAccountByCompanyIdAndUserId($current_company->getId(), $current_user->getId());
            $redirect_url = route('bank_account.user', ['user_id' => $current_user->getId()]);

        } elseif (!empty($user_id) && empty($company_id) /* && $permission->Check('user', 'edit_bank') */ ) {
            Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__, 10);
            $balf->GetUserAccountByCompanyIdAndUserId($current_company->getId(), $user_id);
            $redirect_url = route('bank_account.user', ['user_id' => $user_id]);

        } elseif (!empty($company_id) && empty($user_id) /* && $permission->Check('company', 'edit_own_bank') */) {
            Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__, 10);
            $balf->GetCompanyAccountByCompanyId($company_id);
            $redirect_url = route('bank_account.company', ['company_id' => $company_id]);

        } else {
            return redirect()->back()->withErrors(['error' => 'Unauthorized action.']);
        }

        Debug::Text('Found Records: ' . $balf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

        if ($balf->getRecordCount() > 0) {
            $b_obj = $balf->getCurrent();
            $b_obj->setDeleted(true);
            $b_obj->Save();

            return redirect()->to($redirect_url)->with('success', 'Bank information deleted successfully.');
        }

        return redirect()->back()->withErrors(['error' => 'No bank records found to delete.']);
    }


	public function save(Request $request)
    {
        $baf = new BankAccountFactory();

        $bank_data = $request->all();
        $current_company = $this->company;
        $current_user = $this->currentUser;
        $permission = $this->permission;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($bank_data, 'Bank Data: ', __FILE__, __LINE__, __METHOD__,10);

		if ( !empty($bank_data['id']) ) {
			$baf->setId( $bank_data['id'] );
		}

        $user_id = $bank_data['user_id'] ?? null;
        $company_id = $bank_data['company_id'] ?? null;

		if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' /* AND $permission->Check('user','edit_own_bank') */ ) {
			Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
			//Current user
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $current_user->getId() );
			$redirect_arr = array('user_id' => $user_id, 'data_saved' => TRUE );
            $redirect_url = route('bank_account.user', $redirect_arr);

		} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' /* AND $permission->Check('user','edit_bank') */ ) {

            Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
			//Specified User
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $bank_data['user_id'] );
			$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
            $redirect_url = route('bank_account.user', $redirect_arr);

        } elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' /* AND $permission->Check('company','edit_own_bank') */ ) {

            Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
			//Company bank.
			$baf->setCompany( $bank_data['company_id'] );
			$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
            $redirect_url = route('bank_account.company', $redirect_arr);

        }

		if ( isset($bank_data['institution']) ) {
			$baf->setInstitution( $bank_data['institution'] );
		}else {
            $baf->setInstitution('000'); // Set default valid value if required
        }

		$baf->setTransit( $bank_data['transit'] ?? [] );
		$baf->setAccount( $bank_data['account'] ?? [] );
                $baf->setBankName( $bank_data['bank_name'] ?? [] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
		$baf->setBankBranch( $bank_data['bank_branch'] ?? [] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME


		if ( $baf->isValid() ) {
			$baf->Save();
			// Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'EditBankAccount.blade.php') );
            return redirect()->to($redirect_url)->with('success', 'Bank Information saved successfully.');
		}
        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
	}

}

?>
