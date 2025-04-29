<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyFactory;
use App\Models\Company\CompanyListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditCompany extends Controller
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

		$current_company = $this->currentCompany;
        $current_user_prefs = $this->userPrefs;

		/*
        if ( !$permission->Check('company','enabled')
				OR !( $permission->Check('company','edit') OR $permission->Check('company','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */
		$id = $this->currentCompany->data['id'] ?? null;

        $viewData['title'] = 'Edit Company';

		$cf = new CompanyFactory();

		if ( isset($id) ) {

			$clf = new CompanyListFactory();

			if ( $this->permission->Check('company','edit') ) {
				$clf->GetByID($id);
			} else {
				$id = $current_company->getId();
				$clf->GetByID( $id );
			}

			foreach ($clf->rs as $company) {
				$clf->data = (array)$company;
				$company = $clf;
				//Debug::Arr($company,'Company', __FILE__, __LINE__, __METHOD__,10);

				$company_data = array(
									'id' => $company->getId(),
									'parent' => $company->getParent(),
									'status' => $company->getStatus(),
									'product_edition' => $company->getProductEdition(),
									'name' => $company->getName(),
									'short_name' => $company->getShortName(),
									'industry_id' => $company->getIndustry(),
									'business_number' => $company->getBusinessNumber(),
									'originator_id' => $company->getOriginatorID(),
									'data_center_id' => $company->getDataCenterID(),
									'address1' => $company->getAddress1(),
									'address2' => $company->getAddress2(),
									'city' => $company->getCity(),
									'province' => $company->getProvince(),
									'country' => $company->getCountry(),
									'postal_code' => $company->getPostalCode(),
									'work_phone' => $company->getWorkPhone(),
									'fax_phone' => $company->getFaxPhone(),
									'epf_number' => $company->getEpfNo(),//FL ADDED 20160122 for EPF e Return
									'admin_contact' => $company->getAdminContact(),
									'billing_contact' => $company->getBillingContact(),
									'support_contact' => $company->getSupportContact(),
									'logo_file_name' => $company->getLogoFileName( NULL, FALSE ),
									'enable_second_last_name' => $company->getEnableSecondLastName(),
									'other_id1' => $company->getOtherID1(),
									'other_id2' => $company->getOtherID2(),
									'other_id3' => $company->getOtherID3(),
									'other_id4' => $company->getOtherID4(),
									'other_id5' => $company->getOtherID5(),
									'ldap_authentication_type_id' => $company->getLDAPAuthenticationType(),
									'ldap_host' => $company->getLDAPHost(),
									'ldap_port' => $company->getLDAPPort(),
									'ldap_bind_user_name' => $company->getLDAPBindUserName(),
									'ldap_bind_password' => $company->getLDAPBindPassword(),
									'ldap_base_dn' => $company->getLDAPBaseDN(),
									'ldap_bind_attribute' => $company->getLDAPBindAttribute(),
									'ldap_user_filter' => $company->getLDAPUserFilter(),
									'ldap_login_attribute' => $company->getLDAPLoginAttribute(),

									'created_date' => $company->getCreatedDate(),
									'created_by' => $company->getCreatedBy(),
									'updated_date' => $company->getUpdatedDate(),
									'updated_by' => $company->getUpdatedBy(),
									'deleted_date' => $company->getDeletedDate(),
									'deleted_by' => $company->getDeletedBy(),
								);
			}
		} elseif ( $action != 'submit' ) {
			$company_data = array(
								  'parent' => $current_company->getId(),
								  );
		}

		//Select box options;
		$company_data['status_options'] = $cf->getOptions('status');
		$company_data['country_options'] = $cf->getOptions('country');
		$company_data['industry_options'] = $cf->getOptions('industry');
		
        $company_data['province_options'] = $cf->getOptions('province', $company_data['country'] ?? null);

		//Company list.
		$company_data['company_list_options'] = CompanyListFactory::getAllArray();
		$company_data['product_edition_options'] = $cf->getOptions('product_edition');

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$company_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), 2 );

		$company_data['ldap_authentication_type_options'] = $cf->getOptions('ldap_authentication_type');

		if (!isset($id) AND isset($company_data['id']) ) {
			$id = $company_data['id'];
		}
		$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id);

		  // Add logo file name to company data
		  if (isset($company_data['id'])) {
			$company_data['company_logo'] = $cf->getLogoFileName($company_data['id']);
		}

		$viewData['company_data'] = $company_data;
		$viewData['cf'] = $cf;

		// dd($viewData);
		return view('company/EditCompany', $viewData);
	}

	public function submit(Request $request){
		// dd($request->input('data'));
		$cf = new CompanyFactory();
		$company_data = $request->input('data');
		$current_company = $this->currentCompany;
		$permission = $this->permission;

		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$cf->StartTransaction();

		if ( $permission->Check('company','edit') ) {
			$cf->setId( $company_data['id'] );
			$cf->setParent( $company_data['parent'] );
			$cf->setStatus( $company_data['status'] );
		} else {
			$cf->setId( $current_company->getId() );
		}

		$cf->setProductEdition($company_data['product_edition']);
		if ( isset($company_data['name']) ) {
			$cf->setName($company_data['name']);
		}
		$cf->setShortName($company_data['short_name']);
		$cf->setIndustry($company_data['industry_id']);
		$cf->setBusinessNumber($company_data['business_number']);
		$cf->setOriginatorID($company_data['originator_id']);
		$cf->setDataCenterID($company_data['data_center_id']);
		$cf->setAddress1($company_data['address1']);
		$cf->setAddress2($company_data['address_2']);
		$cf->setCity($company_data['city']);
		$cf->setCountry($company_data['country']);
		if ( isset($company_data['province']) ) {
			$cf->setProvince($company_data['province']);
		}
		$cf->setPostalCode($company_data['postal_code']);
		$cf->setWorkPhone($company_data['work_phone']);
		$cf->setFaxPhone($company_data['fax_phone']);
		$cf->setEpfNo($company_data['epf_number']);// FL Added for EPF E FORM 20160122
		$cf->setAdminContact($company_data['admin_contact']);
		$cf->setBillingContact($company_data['billing_contact']);
		$cf->setSupportContact($company_data['support_contact']);

		if ( isset($company_data['enable_second_last_name']) AND $company_data['enable_second_last_name'] == 1 ) {
			$cf->setEnableSecondLastName( TRUE );
		} else {
			$cf->setEnableSecondLastName( FALSE );
		}

		if ( isset($company_data['other_id1']) ) {
			$cf->setOtherID1( $company_data['other_id1'] );
		}
		if ( isset($company_data['other_id2']) ) {
			$cf->setOtherID2( $company_data['other_id2'] );
		}
		if ( isset($company_data['other_id3']) ) {
			$cf->setOtherID3( $company_data['other_id3'] );
		}
		if ( isset($company_data['other_id4']) ) {
			$cf->setOtherID4( $company_data['other_id4'] );
		}
		if ( isset($company_data['other_id5']) ) {
			$cf->setOtherID5( $company_data['other_id5'] );
		}

		$cf->setLDAPAuthenticationType($company_data['ldap_authentication_type_id']);
		$cf->setLDAPHost($company_data['ldap_host']);
		$cf->setLDAPPort($company_data['ldap_port']);
		$cf->setLDAPBindUserName($company_data['ldap_bind_user_name']);
		$cf->setLDAPBindPassword($company_data['ldap_bind_password']);
		$cf->setLDAPBaseDN($company_data['ldap_base_dn']);
		$cf->setLDAPBindAttribute($company_data['ldap_bind_attribute']);
		$cf->setLDAPUserFilter($company_data['ldap_user_filter']);
		$cf->setLDAPLoginAttribute($company_data['ldap_login_attribute']);

		if ( $cf->isNew() == TRUE ) {
			$cf->setEnableAddCurrency( TRUE );
			$cf->setEnableAddPermissionGroupPreset( TRUE );
			$cf->setEnableAddStation( TRUE );
			$cf->setEnableAddPayStubEntryAccountPreset( TRUE );
			$cf->setEnableAddRecurringHolidayPreset( TRUE );
		}

		// Handle logo upload
		if ($request->hasFile('company_logo')) {
			$file = $request->file('company_logo');
			$company_id = $company_data['id'] ?? $current_company->getId();
			
			// Validate the file
			$validated = $request->validate([
				'company_logo' => 'image|mimes:jpeg,png|max:2048', // 2MB max
			]);
			
			// Get storage path
			$storage_path = $cf->getStoragePath($company_id);
			if (!file_exists($storage_path)) {
				mkdir($storage_path, 0755, true);
			}
			
			// Clean old logo files
			$cf->cleanStoragePath($company_id);
			
			// Save new logo
			$extension = $file->getClientOriginalExtension();
			$file_name = 'logo.' . $extension;
			$file->move($storage_path, $file_name);
		}

		if ( $cf->isValid() ) {
			$cf->Save();

			$cf->CommitTransaction();

			// if ( $permission->Check('company','edit') ) {
				return redirect()->to(URLBuilder::getURL(null, '/company/company_information'))->with('success', 'Company saved successfully.');
			// } else {
			// 	return redirect()->to(URLBuilder::getURL(null, '/login'))->with('success', 'Company saved successfully.');
			// }
		}

		$cf->FailTransaction();
	}

}

?>
