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
use phpDocumentor\Reflection\Types\Null_;

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

	public function index($id = null)
	{

		// If ID is not provided, get the current company's ID
		if (empty($id)) {
			$current_company = $this->currentCompany;
			$id = $current_company->getId();
		}

		$action = [];
		$current_company = $this->currentCompany;
		$permission = $this->permission;
		$viewData['title'] = 'Edit Company';

		$cf = new CompanyFactory();

		// Initialize the array keys to prevent undefined key errors
		$company_data = [
			'ldap_authentication_type_options' => [],
			'status_options' => [],
			'country_options' => [],
			'industry_options' => [],
			'province_options' => [],
			'product_edition_options' => [],
			'company_list_options' => [],
			'user_list_options' => [], // Ensure it's always set
		];


		if (isset($id)) {
			$clf = new CompanyListFactory();

			// if ($permission->Check('company', 'edit')) {
			//     $clf->GetByID($id);
			// } else {
			//     $id = $current_company->getId();
			//     $clf->GetByID($id);
			// }

			if ($permission->Check('company', 'edit')) {
				// Force a refresh to get the latest data
				$clf->GetByID($id, NULL, NULL, true);
			} else {
				$id = $current_company->getId();
				$clf->GetByID($id, NULL, NULL, true);  // Force refresh here as well
			}



			foreach ($clf->rs as $company) {
				$clf->data = (array) $company;
				$company = $clf;

				$company_data = [
					'id' => $company->getId(),
					'parent' => $company->getParent(),
					'status' => $company->getStatus(),
					'product_edition_id' => $company->getProductEdition(),
					'name' => $company->getName(),
					'short_name' => $company->getShortName(),
					'industry_id' => $company->getIndustry(),
					'business_number' => $company->getBusinessNumber(),
					'originator_id' => $company->getOriginatorID(),
					'data_center_id' => $company->getDataCenterID(),
					'address1' => $company->getAddress1(),
					'address2' => $company->getAddress2(),
					'city' => $company->getCity(),
					'country' => $company->getCountry(),
					'province' => $company->getProvince(),
					'postal_code' => $company->getPostalCode(),
					'work_phone' => $company->getWorkPhone(),
					'fax_phone' => $company->getFaxPhone(),
					'epf_number' => $company->getEpfNo(),
					'admin_contact' => $company->getAdminContact(),
					'billing_contact' => $company->getBillingContact(),
					'support_contact' => $company->getSupportContact(),
					'logo_file_name' => $company->getLogoFileName(null, false),
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
				];
			}

			// Ensure ldap_authentication_type_options is always set
			$company_data['ldap_authentication_type_options'] = $cf->getOptions('ldap_authentication_type') ?? [];
			$company_data['status_options'] = $cf->getOptions('status') ?? [];
			$company_data['country_options'] = $cf->getOptions('country') ?? [];
			$company_data['industry_options'] = $cf->getOptions('industry') ?? [];
			$company_data['province_options'] = $cf->getOptions('province') ?? [];
			$company_data['company_list_options'] = CompanyListFactory::getAllArray() ?? [];
			$company_data['product_edition_options'] = $cf->getOptions('product_edition') ?? [];
			$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id) ?? [];
		} elseif ($action != 'submit') {
			$company_data = [
				'parent' => $current_company->getId(),
			];

			// Select box options
			$company_data['status_options'] = $cf->getOptions('status') ?? [];
			$company_data['country_options'] = $cf->getOptions('country') ?? [];
			$company_data['industry_options'] = $cf->getOptions('industry') ?? [];
			$company_data['province_options'] = $cf->getOptions('province') ?? [];
			$company_data['company_list_options'] = CompanyListFactory::getAllArray() ?? [];
			$company_data['product_edition_options'] = $cf->getOptions('product_edition') ?? [];

			// Get other field names
			$oflf = new OtherFieldListFactory();
			$company_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray($current_company->getID(), 2);

			// Ensure ldap_authentication_type_options is set
			$company_data['ldap_authentication_type_options'] = $cf->getOptions('ldap_authentication_type') ?? [];

			if (!isset($id) && isset($company_data['id'])) {
				$id = $company_data['id'];
			}
			$company_data['user_list_options'] = UserListFactory::getByCompanyIdArray($id) ?? [];
		}

		// Assign company data to view
		$viewData['company_data'] = $company_data;
		$viewData['cf'] = $cf;
		// dd($company_data);

		return view('company/EditCompany', $viewData);
	}
	public function getLogo($company_id)
	{
		$cf = new CompanyFactory();
		$logo_path = $cf->getLogoFileName($company_id, false);
		
		if (file_exists($logo_path)) {
			return response()->file($logo_path);
		}
		
		// Return default logo if no company logo exists
		return response()->file(public_path('images/default-logo.png'));
	}

	public function save(Request $request)
	{
		$cf = new CompanyFactory();
		$company_data = $request->all();
		$current_company = $this->currentCompany;
		$permission = $this->permission;

		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);
		$cf->StartTransaction();


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

		if ($permission->Check('company', 'edit')) {
			$cf->setId($company_data['id']);
			$cf->setParent($company_data['parent']);
			$cf->setStatus($company_data['status']);
		} else {
			$cf->setId($current_company->getId());
		}

		if (isset($company_data['product_edition_id'])) {
			$cf->setProductEdition($company_data['product_edition_id']);
		}
		if (isset($company_data['name'])) {
			$cf->setName($company_data['name']);
		}
		if (isset($company_data['short_name'])) {
			$cf->setShortName($company_data['short_name']);
		}
		if (isset($company_data['industry_id'])) {
			$cf->setIndustry($company_data['industry_id']);
		}
		if (isset($company_data['business_number'])) {
			$cf->setBusinessNumber($company_data['business_number']);
		}
		if (isset($company_data['originator_id'])) {
			$cf->setOriginatorID($company_data['originator_id']);
		}
		if (isset($company_data['data_center_id'])) {
			$cf->setDataCenterID($company_data['data_center_id']);
		}
		if (isset($company_data['address1'])) {
			$cf->setAddress1($company_data['address1'] ?? '');
		}
		if (isset($company_data['address2'])) {
			$cf->setAddress2($company_data['address2'] ?? '');
		}
		if (isset($company_data['city'])) {
			$cf->setCity($company_data['city']);
		}
		if (isset($company_data['country'])) {
			$cf->setCountry($company_data['country']);
		}
		if (isset($company_data['province'])) {
			$cf->setProvince($company_data['province']);
		}
		if (isset($company_data['postal_code'])) {
			$cf->setPostalCode($company_data['postal_code']);
		}
		if (isset($company_data['work_phone'])) {
			$cf->setWorkPhone($company_data['work_phone']);
		}
		if (isset($company_data['fax_phone'])) {
			$cf->setFaxPhone($company_data['fax_phone']);
		}
		if (isset($company_data['epf_number'])) {
			$cf->setEpfNo($company_data['epf_number']); // FL Added for EPF E FORM 20160122
		}
		$cf->setAdminContact($company_data['admin_contact'] ?? '');
		$cf->setBillingContact($company_data['billing_contact'] ?? '');
		$cf->setSupportContact($company_data['support_contact'] ?? '');

		if (isset($company_data['enable_second_last_name']) and $company_data['enable_second_last_name'] == 1) {
			$cf->setEnableSecondLastName(TRUE);
		} else {
			$cf->setEnableSecondLastName(FALSE);
		}

		if (isset($company_data['other_id1'])) {
			$cf->setOtherID1($company_data['other_id1']);
		}
		if (isset($company_data['other_id2'])) {
			$cf->setOtherID2($company_data['other_id2']);
		}
		if (isset($company_data['other_id3'])) {
			$cf->setOtherID3($company_data['other_id3']);
		}
		if (isset($company_data['other_id4'])) {
			$cf->setOtherID4($company_data['other_id4']);
		}
		if (isset($company_data['other_id5'])) {
			$cf->setOtherID5($company_data['other_id5']);
		}

		if (isset($company_data['ldap_authentication_type_id'])) {
			$cf->setLDAPAuthenticationType($company_data['ldap_authentication_type_id']);
		}
		$cf->setLDAPHost($company_data['ldap_host'] ?? '');
		if (isset($company_data['ldap_port'])) {
			$cf->setLDAPPort($company_data['ldap_port']);
		}
		$cf->setLDAPBindUserName($company_data['ldap_bind_user_name'] ?? '');
		$cf->setLDAPBindPassword($company_data['ldap_bind_password'] ?? '');
		$cf->setLDAPBaseDN($company_data['ldap_base_dn'] ?? '');
		$cf->setLDAPBindAttribute($company_data['ldap_bind_attribute'] ?? '');
		$cf->setLDAPUserFilter($company_data['ldap_user_filter'] ?? '');
		$cf->setLDAPLoginAttribute($company_data['ldap_login_attribute'] ?? '');

		if ($cf->isNew() == TRUE) {
			$cf->setEnableAddCurrency(TRUE);
			$cf->setEnableAddPermissionGroupPreset(TRUE);
			$cf->setEnableAddStation(TRUE);
			$cf->setEnableAddPayStubEntryAccountPreset(TRUE);
			$cf->setEnableAddRecurringHolidayPreset(TRUE);
		}


		if ($cf->isValid()) {
			$cf->Save();
			$cf->CommitTransaction();

			// Fetch the updated company data again
			
			return redirect()->to(URLBuilder::getURL(null, '/company/company_information'))->with('success', 'Company saved successfully.');
		}

		$cf->FailTransaction();
		// If validation fails, return back with errors
		return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
	}
}
