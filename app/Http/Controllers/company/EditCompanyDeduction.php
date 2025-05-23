<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDeductionFactory;
use App\Models\Company\CompanyDeductionListFactory;
use App\Models\Company\CompanyFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditCompanyDeduction extends Controller
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

    public function index() {

		/*
        if ( !$permission->Check('company_tax_deduction','enabled')
				OR !( $permission->Check('company_tax_deduction','edit') OR $permission->Check('company_tax_deduction','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Tax / Deduction';
		$current_company = $this->currentCompany;

		if ( isset($data)) {
			if ( $data['start_date'] != '' ) {
				$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
			}
			if ( $data['end_date'] != '' ) {
				$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
			}
		}
		

		if ( isset($id) ) {

			$cdlf = new CompanyDeductionListFactory();
			$cdlf->getByCompanyIdAndId( $current_company->getId(), $id );

			foreach ($cdlf->rs as $cd_obj) {
				$cdlf->data = (array)$cd_obj;
				$cd_obj = $cdlf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $cd_obj->getId(),
					'company_id' => $cd_obj->getCompany(),
					'status_id' => $cd_obj->getStatus(),
					'type_id' => $cd_obj->getType(),
					'name' => $cd_obj->getName(),

					'start_date' => $cd_obj->getStartDate(),
					'end_date' => $cd_obj->getEndDate(),

					'minimum_length_of_service' => $cd_obj->getMinimumLengthOfService(),
					'minimum_length_of_service_unit_id' => $cd_obj->getMinimumLengthOfServiceUnit(),
					'maximum_length_of_service' => $cd_obj->getMaximumLengthOfService(),
					'maximum_length_of_service_unit_id' => $cd_obj->getMaximumLengthOfServiceUnit(),
					'minimum_user_age' => $cd_obj->getMinimumUserAge(),
					'maximum_user_age' => $cd_obj->getMaximumUserAge(),

					'calculation_id' => $cd_obj->getCalculation(),
					'calculation_order' => $cd_obj->getCalculationOrder(),

					'country' => $cd_obj->getCountry(),
					'province' => $cd_obj->getProvince(),
					'district' => $cd_obj->getDistrict(),

					'company_value1' => $cd_obj->getCompanyValue1(),
					'company_value2' => $cd_obj->getCompanyValue2(),

					'user_value1' => $cd_obj->getUserValue1(),
					'user_value2' => $cd_obj->getUserValue2(),
					'user_value3' => $cd_obj->getUserValue3(),
					'user_value4' => $cd_obj->getUserValue4(),
					'user_value5' => $cd_obj->getUserValue5(),
					'user_value6' => $cd_obj->getUserValue6(),
					'user_value7' => $cd_obj->getUserValue7(),
					'user_value8' => $cd_obj->getUserValue8(),
					'user_value9' => $cd_obj->getUserValue9(),
					'user_value10' => $cd_obj->getUserValue10(),

					'lock_user_value1' => $cd_obj->getLockUserValue1(),
					'lock_user_value2' => $cd_obj->getLockUserValue2(),
					'lock_user_value3' => $cd_obj->getLockUserValue3(),
					'lock_user_value4' => $cd_obj->getLockUserValue4(),
					'lock_user_value5' => $cd_obj->getLockUserValue5(),
					'lock_user_value6' => $cd_obj->getLockUserValue6(),
					'lock_user_value7' => $cd_obj->getLockUserValue7(),
					'lock_user_value8' => $cd_obj->getLockUserValue8(),
					'lock_user_value9' => $cd_obj->getLockUserValue9(),
					'lock_user_value10' => $cd_obj->getLockUserValue10(),
					
					'basis_of_employment'=>$cd_obj->getBasisOfEmployment(),
					'pay_stub_entry_account_id' => $cd_obj->getPayStubEntryAccount(),
					'include_pay_stub_entry_account_ids' => $cd_obj->getIncludePayStubEntryAccount(),
					'exclude_pay_stub_entry_account_ids' => $cd_obj->getExcludePayStubEntryAccount(),
					'include_account_amount_type_id' => $cd_obj->getIncludeAccountAmountType(),
					'exclude_account_amount_type_id' => $cd_obj->getExcludeAccountAmountType(),
					'user_ids' => $cd_obj->getUser(),
					'created_date' => $cd_obj->getCreatedDate(),
					'created_by' => $cd_obj->getCreatedBy(),
					'updated_date' => $cd_obj->getUpdatedDate(),
					'updated_by' => $cd_obj->getUpdatedBy(),
					'deleted_date' => $cd_obj->getDeletedDate(),
					'deleted_by' => $cd_obj->getDeletedBy()
				);
			}
		} else {
			$data = array(
				'country' => 0,
				'province' => 0,
				'district' => 0,
				'user_value1' => 0,
				'user_value2' => 0,
				'user_value3' => 0,
				'user_value4' => 0,
				'user_value5' => 0,
				'user_value6' => 0,
				'user_value7' => 0,
				'user_value8' => 0,
				'user_value9' => 0,
				'user_value10' => 0,
				'minimum_length_of_service' => 0,
				'maximum_length_of_service' => 0,
				'minimum_user_age' => 0,
				'maximum_user_age' => 0,
				'basis_of_employment'=>1,
				'calculation_order' => 100,
			);
		}

		$cdf = new CompanyDeductionFactory();
		//Select box options;
		$data['status_options'] = $cdf->getOptions('status');
		$data['type_options'] = $cdf->getOptions('type');
		$data['length_of_service_unit_options'] = $cdf->getOptions('length_of_service_unit');
		$data['account_amount_type_options'] = $cdf->getOptions('account_amount_type');

		$cf = new CompanyFactory(); 
		$data['country_options'] = Misc::prependArray( array( 0 => '--' ), $cf->getOptions('country') );
		if ( isset($data['country']) ) {
			$data['province_options'] = $cf->getOptions('province', $data['country'] );
		}
		if ( isset($data['district']) ) {
			$district_options = $cf->getOptions('district', $data['country'] );
			if ( isset($district_options[$data['province']]) ) {
				$data['district_options'] = $district_options[$data['province']];
			}
		}

		$data['us_eic_filing_status_options'] = $cdf->getOptions('us_eic_filing_status');
		$data['federal_filing_status_options'] = $cdf->getOptions('federal_filing_status');
		$data['state_filing_status_options'] = $cdf->getOptions('state_filing_status');
		$data['state_ga_filing_status_options'] = $cdf->getOptions('state_ga_filing_status');
		$data['state_nj_filing_status_options'] = $cdf->getOptions('state_nj_filing_status');
		$data['state_nc_filing_status_options'] = $cdf->getOptions('state_nc_filing_status');
		$data['state_ma_filing_status_options'] = $cdf->getOptions('state_ma_filing_status');
		$data['state_al_filing_status_options'] = $cdf->getOptions('state_al_filing_status');
		$data['state_ct_filing_status_options'] = $cdf->getOptions('state_ct_filing_status');
		$data['state_wv_filing_status_options'] = $cdf->getOptions('state_wv_filing_status');
		$data['state_me_filing_status_options'] = $cdf->getOptions('state_me_filing_status');
		$data['state_de_filing_status_options'] = $cdf->getOptions('state_de_filing_status');
		$data['state_dc_filing_status_options'] = $cdf->getOptions('state_dc_filing_status');
		$data['state_la_filing_status_options'] = $cdf->getOptions('state_la_filing_status');

		$data['calculation_options'] = $cdf->getOptions('calculation');
		$data['js_arrays'] = $cdf->getJavaScriptArrays();

		$psealf = new PayStubEntryAccountListFactory();
		$data['pay_stub_entry_account_options'] = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50), FALSE );
		//$data['pay_stub_entry_account_options'] = PayStubEntryAccountListFactory::getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(20,30), FALSE );

		//Employee Selection Options
		$data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );
		
		$viewData['data'] = $data;

		//dd($viewData);
		$viewData['cdf'] = $cdf;

        return view('company/EditCompanyDeduction', $viewData);

    }

	public function submit(Request $request){
		$current_company = $this->currentCompany;
		$data = $request->data;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$cdf = new CompanyDeductionFactory();

		$cdf->StartTransaction();

		$cdf->setId( $data['id'] );
		$cdf->setCompany( $current_company->getId() );
		$cdf->setStatus( $data['status_id'] );
		$cdf->setType( $data['type_id'] );
		$cdf->setName( $data['name'] );
		$cdf->setCalculation( $data['calculation_id'] );
		$cdf->setCalculationOrder( $data['calculation_order'] );

		if ( isset($data['country']) ) {
			$cdf->setCountry($data['country']);
		}

		if ( isset($data['province']) ) {
			$cdf->setProvince($data['province']);
		} else {
			$cdf->setProvince(NULL);
		}

		if ( isset($data['district']) ) {
			$cdf->setDistrict($data['district']);
		} else {
			$cdf->setDistrict(NULL);
		}

		if ( isset($data['company_value1']) ) {
			$cdf->setCompanyValue1( $data['company_value1'] );
		}
		if ( isset($data['company_value2']) ) {
			$cdf->setCompanyValue2( $data['company_value2'] );
		}

		$cdf->setPayStubEntryAccount( $data['pay_stub_entry_account_id'] );
		if ( isset($data['user_value1']) ) {
			$cdf->setUserValue1( $data['user_value1'] );
		}
		if ( isset($data['user_value2']) ) {
			$cdf->setUserValue2( $data['user_value2'] );
		}
		if ( isset($data['user_value3']) ) {
			$cdf->setUserValue3( $data['user_value3'] );
		}
		if ( isset($data['user_value4']) ) {
			$cdf->setUserValue4( $data['user_value4'] );
		}
		if ( isset($data['user_value5']) ) {
			$cdf->setUserValue5( $data['user_value5'] );
		}
		if ( isset($data['user_value6']) ) {
			$cdf->setUserValue6( $data['user_value6'] );
		}
		if ( isset($data['user_value7']) ) {
			$cdf->setUserValue7( $data['user_value7'] );
		}
		if ( isset($data['user_value8']) ) {
			$cdf->setUserValue8( $data['user_value8'] );
		}
		if ( isset($data['user_value9']) ) {
			$cdf->setUserValue9( $data['user_value9'] );
		}
		if ( isset($data['user_value10']) ) {
			$cdf->setUserValue10( $data['user_value10'] );
		}


		if ( isset($data['start_date']) ) {
			$cdf->setStartDate( $data['start_date'] );
		}
		if ( isset($data['end_date']) ) {
			$cdf->setEndDate( $data['end_date'] );
		}

		if ( isset($data['minimum_length_of_service']) ) {
			$cdf->setMinimumLengthOfService( $data['minimum_length_of_service'] );
			$cdf->setMinimumLengthOfServiceUnit( $data['minimum_length_of_service_unit_id'] );
		}
		if ( isset($data['maximum_length_of_service']) ) {
			$cdf->setMaximumLengthOfService( $data['maximum_length_of_service'] );
			$cdf->setMaximumLengthOfServiceUnit( $data['maximum_length_of_service_unit_id'] );
		}

		if ( isset($data['minimum_user_age']) ) {
			$cdf->setMinimumUserAge( $data['minimum_user_age'] );
		}
		if ( isset($data['maximum_user_age']) ) {
			$cdf->setMaximumUserAge( $data['maximum_user_age'] );
		}
                
                if ( isset($data['basis_of_employment']) ) {
			$cdf->setBasisOfEmployment( $data['basis_of_employment'] );
		}

		if ( isset($data['include_account_amount_type_id']) ) {
			$cdf->setIncludeAccountAmountType( $data['include_account_amount_type_id'] );
		}
		if ( isset($data['exclude_account_amount_type_id']) ) {
			$cdf->setExcludeAccountAmountType( $data['exclude_account_amount_type_id'] );
		}

		if ( $cdf->isValid() ) {
			$cdf->Save(FALSE);

			if ( isset($data['include_pay_stub_entry_account_ids']) ){
				$cdf->setIncludePayStubEntryAccount( $data['include_pay_stub_entry_account_ids'] );
			} else {
				$cdf->setIncludePayStubEntryAccount( array() );
			}

			if ( isset($data['exclude_pay_stub_entry_account_ids']) ){
				$cdf->setExcludePayStubEntryAccount( $data['exclude_pay_stub_entry_account_ids'] );
			} else {
				$cdf->setExcludePayStubEntryAccount( array() );
			}

			if ( isset($data['user_ids']) ){
				$cdf->setUser( $data['user_ids'] );
			} else {
				$cdf->setUser( array() );
			}

			if ( $cdf->isValid() ) {
				$cdf->Save(TRUE);

				$cdf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList') );
			}
		}
	}
}

?>