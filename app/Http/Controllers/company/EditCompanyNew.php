<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Core\Debug;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Users\UserListFactory;

class EditCompanyNew extends Controller
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


    // public function index($id = null)
    // {
    //     // if ( !$this->permission->Check('company','enabled')
    //     //         OR !( $this->permission->Check('company','edit') OR $this->permission->Check('company','edit_own') ) ) {

    //     //     $this->permission->Redirect( FALSE ); //Redirect
    //     // }

    //     $viewData['title'] = 'Edit Company';

    //     extract(FormVariables::GetVariables(
    //         array(
    //             'action',
	// 			'id',
	// 			'company_data'
    //         )
    //     ) );

    //     return view('company_new.EditCompany');
    // }


    public function index($id = null)
    {
        // if (!$this->permission->Check('company', 'enabled') ||
        //     !($this->permission->Check('company', 'edit') || $this->permission->Check('company', 'edit_own'))) {
        //     return $this->permission->Redirect(false);
        // }


        // $company_data = [
        //     'id' => null,
        //     'parent' => null,
        //     'status' => null,
        //     'product_edition' => null,
        //     'name' => null,
        //     'short_name' => null,
        //     'industry_id' => null,
        //     'business_number' => null,
        //     'originator_id' => null,
        //     'data_center_id' => null,
        //     'address1' => null,
        //     'address2' => null,
        //     'city' => null,
        //     'province' => null,
        //     'country' => null,
        //     'postal_code' => null,
        //     'work_phone' => null,
        //     'fax_phone' => null,
        //     'admin_contact' => null,
        //     'billing_contact' => null,
        //     'support_contact' => null,
        //     'enable_second_last_name' => null,
        //     'other_id1' => null,
        //     'other_id2' => null,
        //     'other_id3' => null,
        //     'other_id4' => null,
        //     'other_id5' => null,
        //     'ldap_authentication_type_id' => null,
        //     'ldap_host' => null,
        //     'ldap_port' => null,
        //     'ldap_bind_user_name' => null,
        //     'ldap_bind_password' => null,
        //     'ldap_base_dn' => null,
        //     'ldap_bind_attribute' => null,
        //     'ldap_user_filter' => null,
        //     'ldap_login_attribute' => null,
        // ];

        $company_data = [];

        extract(FormVariables::GetVariables([
            'action',
            'id',
            'company_data'
        ]));


        if (isset($id)) {


            $clf = new CompanyListFactory();
            $clf->GetByID($id);

            foreach ($clf->rs as $company_obj) {
                $clf->data = (array)$company_obj;
                $company_obj = $clf;

                $company_data = array(
                                    'id' => $company_obj->getId(),
                                    'parent' => $company_obj->getParent(),
                                    'status' => $company_obj->getStatus(),
                                    'product_edition' => $company_obj->getProductEdition(),
                                    'name' => $company_obj->getName(),
                                    'short_name' => $company_obj->getShortName(),
                                    'industry_id' => $company_obj->getIndustry(),
                                    'business_number' => $company_obj->getBusinessNumber(),
                                    'originator_id' => $company_obj->getOriginatorID(),
                                    'data_center_id' => $company_obj->getDataCenterID(),
                                    'address1' => $company_obj->getAddress1(),
                                    'address2' => $company_obj->getAddress2(),
                                    'city' => $company_obj->getCity(),
                                    'province' => $company_obj->getProvince(),
                                    'country' => $company_obj->getCountry(),
                                    'postal_code' => $company_obj->getPostalCode(),
                                    'work_phone' => $company_obj->getWorkPhone(),
                                    'fax_phone' => $company_obj->getFaxPhone(),
                                    'admin_contact' => $company_obj->getAdminContact(),
                                    'billing_contact' => $company_obj->getBillingContact(),
                                    'support_contact' => $company_obj->getSupportContact(),
                                    'enable_second_last_name' => $company_obj->getEnableSecondLastName(),
                                    'other_id1' => $company_obj->getOtherID1(),
                                    'other_id2' => $company_obj->getOtherID2(),
                                    'other_id3' => $company_obj->getOtherID3(),
                                    'other_id4' => $company_obj->getOtherID4(),
                                    'other_id5' => $company_obj->getOtherID5(),
                                    'ldap_authentication_type_id' => $company_obj->getLDAPAuthenticationType(),
                                    'ldap_host' => $company_obj->getLDAPHost(),
                                    'ldap_port' => $company_obj->getLDAPPort(),
                                    'ldap_bind_user_name' => $company_obj->getLDAPBindUserName(),
                                    'ldap_bind_password' => $company_obj->getLDAPBindPassword(),
                                    'ldap_base_dn' => $company_obj->getLDAPBaseDN(),
                                    'ldap_bind_attribute' => $company_obj->getLDAPBindAttribute(),
                                    'ldap_user_filter' => $company_obj->getLDAPUserFilter(),
                                    'ldap_login_attribute' => $company_obj->getLDAPLoginAttribute(),

                                    'created_date' => $company_obj->getCreatedDate(),
                                    'created_by' => $company_obj->getCreatedBy(),
                                    'updated_date' => $company_obj->getUpdatedDate(),
                                    'updated_by' => $company_obj->getUpdatedBy(),
                                    'deleted_date' => $company_obj->getDeletedDate(),
                                    'deleted_by' => $company_obj->getDeletedBy(),
                                );
            }

            //Select box options;
            $company_data['status_options'] = $cf->getOptions('status');
            $company_data['country_options'] = $cf->getOptions('country');
            $company_data['industry_options'] = $cf->getOptions('industry');

            //Company list.
            $company_data['company_list_options'] = CompanyListFactory::getAllArray();
            $company_data['product_edition_options'] = $cf->getOptions('product_edition');



        } else {
            // Load default dropdown options even if no company exists
            $clf = new CompanyListFactory();
            $company_data['status_options'] = $clf->getOptions('status');
            $company_data['country_options'] = $clf->getOptions('country');
            $company_data['industry_options'] = $clf->getOptions('industry');
            $company_data['company_list_options'] = CompanyListFactory::getAllArray();
            $company_data['product_edition_options'] = $clf->getOptions('product_edition');
           
        }

        $viewData = [
            'title' => $id ? 'Edit Company' : 'Add Company',
            'company_data' => $company_data,
        ];

        return view('company_new.EditCompany', $viewData);
    }


    public function save(Request $request)
    {
        // if (!$this->permission->Check('company', 'edit')) {
        //     return $this->permission->Redirect(false);
        // }

        $current_company = $this->currentCompany;
        $company_data = $request->input('data');

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

        $cf = new CompanyFactory();

        $cf->StartTransaction();

        if ( $this->permission->Check('company','edit') ) {
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
		$cf->setAddress2($company_data['address2']);
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

        if ($cf->isValid()) {
            $cf->Save(TRUE);

            $cf->CommitTransaction();
            return redirect()->to(URLBuilder::getURL(null, '/company'))->with('success', 'Company Information Saved Successfully!');
        }

        $cf->FailTransaction();
        return redirect()->back()->withErrors(['error' => 'Company data is invalid.'])->withInput();

    }

}


?>
