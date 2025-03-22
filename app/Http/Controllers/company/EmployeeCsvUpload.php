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
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EmployeeCsvUpload extends Controller
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
 		/*
        if ( !$permission->Check('user','employee_excel_upload') ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$cf = new CompanyFactory();

        $viewData['title'] = 'Employee CSV Upload';

		extract	(FormVariables::GetVariables(
			array(
				'action',
				'id',
				'company_data'
			) 
		) );

			if ( isset($id) ) {
	
				$clf = new CompanyListFactory();
	
				if ( $permission->Check('company','edit') ) {
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
	
			$smarty->assign_by_ref('company_data', $company_data);
			$smarty->assign_by_ref('cf', $cf);

        return view('company/EmployeeCsvUpload', $viewData);

    }

	public function submit(Request $request){

		extract	(FormVariables::GetVariables(
			array(
				'action',
				'id',
				'company_data'
			) 
		) );

		$permission = $this->permission;
		$current_company = $this->currentCompany;
		$user_data = $request->data;

		$ulf = new UserListFactory();
		$uf = new UserFactory();
		  
           //Debug::setVerbosity( 11 );
		   Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		   unset($id); //Do this so it doesn't reload the data from the DB.
   
		   //Additional permission checks.
		   if ( $permission->Check('company','view') ) {
			   $ulf->getById( $user_data['id'] );
		   } else {
			   $ulf->getByIdAndCompanyId( $user_data['id'], $current_company->getId() );
		   }
		   
		   if ( $ulf->getRecordCount() > 0 ) {
			   $user = $ulf->getCurrent();
   
			   $is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getID() );
			   $is_child = $permission->isChild( $user->getId(), $permission_children_ids );
			   if ( $permission->Check('user','edit')
					   OR ( $permission->Check('user','edit_child') AND $is_child === TRUE )
					   OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE ) ) {
					   // Security measure.
					   if ( !empty($user_data['id']) ) {
						   if ( $permission->Check('company','view') ) {
							   $uf = $ulf->getById( $user_data['id'] )->getCurrent();
						   } else {
							   $uf = $ulf->getByIdAndCompanyId($user_data['id'], $current_company->getId() )->getCurrent();
						   }
					   }
			   } else {
				   $permission->Redirect( FALSE ); //Redirect
				   exit;
			   }
			   unset($user);
		   }
   
		   if ( isset( $user_data['company_id'] ) ) {
			   if ( $permission->Check('company','view') ) {
				   $uf->setCompany( $user_data['company_id'] );
			   } else {
				   $uf->setCompany( $current_company->getId() );
			   }
		   } else {
			   $uf->setCompany( $current_company->getId() );
		   }
   
		   //Get New Hire Defaults.
		   $udlf = new UserDefaultListFactory();
		   $udlf->getByCompanyId( $uf->getCompany() );
		   if ( $udlf->getRecordCount() > 0 ) {
			   Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
			   $udf_obj = $udlf->getCurrent();
		   }
   
		   if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			   if ( isset( $user_data['status'] ) ) {
				   $uf->setStatus( $user_data['status'] );
			   }
   
			   if ( isset( $user_data['user_name'] ) ) {
				   $uf->setUserName( $user_data['user_name'] );
			   }
   
			   //Phone ID is optional now.
			   if ( isset( $user_data['phone_id'] ) ) {
				   $uf->setPhoneId( $user_data['phone_id'] );
			   }
		   }
   
		   if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			   if ( !empty($user_data['password']) OR !empty($user_data['password2']) ) {
				   if ( $user_data['password'] == $user_data['password2'] ) {
					   $uf->setPassword($user_data['password']);
				   } else {
					   $uf->Validator->isTrue(	'password',
											   FALSE,
											   __('Passwords don\'t match') );
				   }
			   }
   
			   if ( isset( $user_data['phone_password'] ) ) {
				   $uf->setPhonePassword($user_data['phone_password']);
			   }
		   }
   
				   
				   
		   if ( $user_data['id'] != $current_user->getID()
				   AND $permission->Check('user','edit_advanced') ) {
			   //Don't force them to update all fields.
			   //Unless they are editing their OWN user.
								   $uf->setFirstName($user_data['first_name']);
   
			   if ( isset($user_data['middle_name']) ) {
				   $uf->setMiddleName($user_data['middle_name']);
			   }
   
								   $uf->setLastName($user_data['last_name']);
   
			   if ( isset($user_data['second_last_name']) ){
				   $uf->setSecondLastName($user_data['second_last_name']);
			   }
   
			   if ( !empty($user_data['sex']) ) {
				   $uf->setSex($user_data['sex']);
			   }
   
			   if ( isset($user_data['address1']) ) {
				   $uf->setAddress1($user_data['address1']);
			   }
   
			   if ( isset($user_data['address2']) ) {
				   $uf->setAddress2($user_data['address2']);
			   }
						   //ARSP EDIT CODE-----> ADD NEW CODE FOR N.I.C
						   if ( isset($user_data['nic']) ) {
											   $uf->setNic($user_data['nic']);
									   }
									   //ARSP EDIT CODE-----> ADD NEW CODE FOR probation
						   if ( isset($user_data['probation']) ) {
											   $uf->setProbation($user_data['probation']);
									   }
									   
						   /**
							* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
							*/                        
						   if ( isset($user_data['basis_of_employment']) ) {
				   $uf->setBasisOfEmployment($user_data['basis_of_employment']);
			   }
						   
						   /**
							* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
							*/                         
						   if ( isset($user_data['month']) ){
				   $uf->setMonth($user_data['month']);
			   }                                      
   
										//ARSP EDIT CODE-----> ADD NEW CODE FOR EPF registration no
						   if ( isset($user_data['epf_registration_no']) ) {
											   $uf->setEpfRegistrationNo($user_data['epf_registration_no']);
									   }      
   
							//ARSP EDIT CODE-----> ADD NEW CODE FOR EPF membership no
							if ( isset($user_data['epf_membership_no']) ) {
											   $uf->setEpfMembershipNo($user_data['epf_membership_no']);
									   } 	
			   
   
			   if ( isset($user_data['city']) ) {
				   $uf->setCity($user_data['city']);
			   }
   
			   if ( isset($user_data['country']) ) {
				   $uf->setCountry($user_data['country']);
			   }
   
			   if ( isset($user_data['province']) ) {
				   $uf->setProvince($user_data['province']);
			   }
   
			   if ( isset($user_data['postal_code']) ) {
				   $uf->setPostalCode($user_data['postal_code']);
			   }
   
			   if ( isset($user_data['work_phone']) ) {
				   $uf->setWorkPhone($user_data['work_phone']);
			   }
   
			   if ( isset($user_data['work_phone_ext']) ) {
				   $uf->setWorkPhoneExt($user_data['work_phone_ext']);
			   }
   
			   if ( isset($user_data['home_phone']) ) {
				   $uf->setHomePhone($user_data['home_phone']);
			   }
   
			   if ( isset($user_data['mobile_phone']) ) {
				   $uf->setMobilePhone($user_data['mobile_phone']);
			   }
   
			   if ( isset($user_data['fax_phone']) ) {
				   $uf->setFaxPhone($user_data['fax_phone']);
			   }
   
			   if ( isset($user_data['home_email']) ) {
				   $uf->setHomeEmail($user_data['home_email']);
			   }
   
			   if ( isset($user_data['work_email']) ) {
				   $uf->setWorkEmail($user_data['work_email']);
			   }
   
			   if ( isset($user_data['sin']) ) {
				   $uf->setSIN($user_data['sin']);
			   }
						   
   
			   $uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
		   } else {
			   //Force them to update all fields.
   
			   $uf->setFirstName($user_data['first_name']);
			   $uf->setMiddleName($user_data['middle_name']);
			   $uf->setLastName($user_data['last_name']);
			   if ( isset($user_data['second_last_name']) ) {
				   $uf->setSecondLastName($user_data['second_last_name']);
			   }
			   $uf->setSex($user_data['sex']);
			   $uf->setAddress1($user_data['address1']);
			   $uf->setAddress2($user_data['address2']);
						   $uf->setAddress2($user_data['address2']);
   
						   //ARSP EDIT CODE--->
						   $uf->setNic($user_data['nic']);
   
									   //ARSP EDIT CODE---> ADD NEW CODE FOR PROBATION PERIOD
						   $uf->setProbation($user_data['probation']);
   
						  //ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
						   $uf->setEpfRegistrationNo($user_data['epf_registration_no']);
   
						   //ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
						   $uf->setEpfMembershipNo($user_data['epf_membership_no']);				
   
						   $uf->setCity($user_data['city']);
   
			   if ( isset($user_data['country']) ) {
				   $uf->setCountry($user_data['country']);
			   }
   
			   if ( isset($user_data['province']) ) {
				   $uf->setProvince($user_data['province']);
			   }
   
			   $uf->setPostalCode($user_data['postal_code']);
			   $uf->setWorkPhone($user_data['work_phone']);
			   $uf->setWorkPhoneExt($user_data['work_phone_ext']);
			   $uf->setHomePhone($user_data['home_phone']);
			   $uf->setMobilePhone($user_data['mobile_phone']);
			   $uf->setFaxPhone($user_data['fax_phone']);
			   $uf->setHomeEmail($user_data['home_email']);
			   $uf->setWorkEmail($user_data['work_email']);
			   if ( isset($user_data['sin']) ) {
				   $uf->setSIN($user_data['sin']);
			   }
   
			   $uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
		   }
				   
		   if ( DEMO_MODE == FALSE
			   AND isset($user_data['permission_control_id'])
			   AND $uf->getPermissionLevel() <= $permission->getLevel()
			   AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') ) ) {
			   $uf->setPermissionControl( $user_data['permission_control_id'] );
		   } elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
			   $uf->setPermissionControl( $udf_obj->getPermissionControl() );
		   }
   
		   if ( isset($user_data['pay_period_schedule_id']) AND ( $permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule') ) ) {
			   $uf->setPayPeriodSchedule( $user_data['pay_period_schedule_id'] );
		   } elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
						   $uf->setPayPeriodSchedule( $udf_obj->getPayPeriodSchedule() );
		   }
   
		   if ( isset($user_data['policy_group_id']) AND ( $permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group') ) ) {
			   $uf->setPolicyGroup( $user_data['policy_group_id'] );
		   } elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE) {
						   $uf->setPolicyGroup( $udf_obj->getPolicyGroup() );
		   }
   
		   if ( isset($user_data['hierarchy_control']) AND ( $permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy') ) ) {
			   $uf->setHierarchyControl( $user_data['hierarchy_control'] );
		   }
   
		   if ( isset($user_data['currency_id']) ) {
			   $uf->setCurrency( $user_data['currency_id'] );
		   } elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
						   $uf->setCurrency( $udf_obj->getCurrency() );
		   }
   
		   if ( isset($user_data['hire_date']) ) {
			   $uf->setHireDate( $user_data['hire_date'] );
		   }
		   if ( isset($user_data['termination_date']) ) {
			   $uf->setTerminationDate( $user_data['termination_date'] );
		   }
		   
		   /**
			* ARSP NOTE -->
			* I HIDE THIS ORIGINAL CODE FOR THUNDER & NEON AND ADDED NEW CODE
			*/  		 
		   //if ( isset($user_data['employee_number']) ) {
		   //	$uf->setEmployeeNumber( $user_data['employee_number'] );
		   //}
		   
		   /**
			* ARSP NOTE -->
			* I MODIFIED ABOVE ORIGINAL CODE THUNDER & NEON
			*/                
		   if ( isset($user_data['employee_number_only']) ) {                    
			   $uf->setEmployeeNumber( $user_data['branch_short_id'].$user_data['employee_number_only'] );
		   }
				   
				   /**
					* ARSP NOTE -->
					* I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
					*/                
		   if ( isset($user_data['employee_number_only']) ) {                        
			   $uf->setEmployeeNumberOnly($user_data['employee_number_only'], $user_data['default_branch_id']);//ARSP NOTE --> I ADDED EXTRA PARAMETER FOR THUNDER & NEON
		   }                 
		   
		   /**
			* ARSP NOTE -->
			* I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
			*/  		
		   if ( isset($user_data['punch_machine_user_id']) ) {
			   $uf->setPunchMachineUserID( $user_data['punch_machine_user_id'] );
		   }
		   
		   if ( isset($user_data['default_branch_id']) ) {
			   $uf->setDefaultBranch( $user_data['default_branch_id'] );
		   }
		   if ( isset($user_data['default_department_id']) ) {
			   $uf->setDefaultDepartment( $user_data['default_department_id'] );
		   }
		   if ( isset($user_data['group_id']) ) {
			   $uf->setGroup( $user_data['group_id'] );
		   }
		   if ( isset($user_data['title_id']) ) {
			   $uf->setTitle($user_data['title_id']);
		   }
		   
		   /**
			* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
			* EMPLOYEE JOB SKILLS
			*/                
		   if ( isset($user_data['job_skills']) ) {                        
			   $uf->setJobSkills($user_data['job_skills'],  $user_data['job_skills']);
		   }  		
		   
		   if ( isset($user_data['ibutton_id']) ) {
			   $uf->setIButtonId($user_data['ibutton_id']);
		   }
		   if ( isset($user_data['other_id1']) ) {
			   $uf->setOtherID1( $user_data['other_id1'] );
		   }
		   if ( isset($user_data['other_id2']) ) {
			   $uf->setOtherID2( $user_data['other_id2'] );
		   }
		   if ( isset($user_data['other_id3']) ) {
			   $uf->setOtherID3( $user_data['other_id3'] );
		   }
		   if ( isset($user_data['other_id4']) ) {
			   $uf->setOtherID4( $user_data['other_id4'] );
		   }
		   if ( isset($user_data['other_id5']) ) {
			   $uf->setOtherID5( $user_data['other_id5'] );
		   }
   
		   if ( isset($user_data['note']) ) {
			   $uf->setNote( $user_data['note'] );
		   }
		   
		   //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		   if ( isset($user_data['hire_note']) ) {
			   $uf->setHireNote( $user_data['hire_note'] );
		   }
				   
		   //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		   if ( isset($user_data['termination_note']) ) {
			   $uf->setTerminationNote( $user_data['termination_note'] );
		   }
				   
				   //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		   if ( isset($user_data['immediate_contact_person']) ) {
			   $uf->setImmediateContactPerson($user_data['immediate_contact_person'] );
		   }
				   
				   
				   //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		   if ( isset($user_data['immediate_contact_no']) ) {
			   $uf->setImmediateContactNo( $user_data['immediate_contact_no'] );
		   }
				   
				   //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		   if ( isset($user_data['bond_period']) ) {
			   $uf->setBondPeriod($user_data['bond_period'] );
		   }                   
				   
				   /**
					* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
					*/
		   if ( isset($user_data['resign_date']) ) {
			   $uf->setResignDate( $user_data['resign_date'] );
		   }                  
   
		   if ( $uf->isValid() ) {
			   $uf->Save(FALSE);
			   $user_data['id'] = $uf->getId();
			   Debug::Text('Inserted ID: '. $user_data['id'], __FILE__, __LINE__, __METHOD__,10);
   
			   Redirect::Page( URLBuilder::getURL( array('id' => $user_data['id'], 'saved_search_id' => $saved_search_id, 'company_id' => $company_id, 'data_saved' => TRUE), 'EditUser') );
   
		   }
	}
}

?>