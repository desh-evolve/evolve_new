<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyControlListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserGenericDataFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\Users\UserWageFactory;
use App\Models\Users\UserWageListFactory;
use Illuminate\Support\Facades\View;

class EditUser extends Controller
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

    public function index($user_id = null) {
		/*
			if ( !$permission->Check('user','enabled')
					OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
				$permission->Redirect( FALSE ); //Redirect
			}
		*/

		$viewData['title'] = !empty($user_id) ? 'Edit Employee' : 'Add Employee';

		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;

		$company_id = $current_company->getId();

		$ulf = new UserListFactory();
		$uf = new UserFactory(); 

		if ( isset($user_id) ) {
			$ulf->getById($user_id);
			$user_data = $ulf->getCurrent()->data;

			if ( isset($user_data['hire_date']) AND $user_data['hire_date'] != '') {
				$user_data['hire_date'] = TTDate::parseDateTime($user_data['hire_date']);
			}

			if ( isset($user_data['termination_date']) AND $user_data['termination_date'] != '') {
				Debug::Text('Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
				$user_data['termination_date'] = TTDate::parseDateTime($user_data['termination_date']);
			}
			
			if ( isset($user_data['resign_date']) AND $user_data['resign_date'] != '') {
				$user_data['resign_date'] = TTDate::parseDateTime($user_data['resign_date']);
			}  
			
			if ( isset($user_data['confirmed_date']) AND $user_data['confirmed_date'] != '') {
				$user_data['confirmed_date'] = TTDate::parseDateTime($user_data['confirmed_date']);
			} else {
				Debug::Text('NOT Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
			}
		}
		
		
		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId( $current_company->getId(), $current_user->getId() );
		//Include current user in list.
		if ( $permission->Check('user','edit_own') ) {
			$permission_children_ids[] = $current_user->getId();
		}
		
		$action = Misc::findSubmitButton();

		if ( $permission->Check('company','view') == FALSE OR $company_id == '' OR $company_id == '-1' ) {
			$company_id = $current_company->getId();
		}

		if ( isset($id) AND $action !== 'submit' ) {
			
			if ( $permission->Check('company','view') ) {
				$ulf->getById( $id )->getCurrent();
			} else {
				$ulf->getByIdAndCompanyId($id, $company_id );
			}

			foreach ($ulf->rs as $user) {
				$ulf->data = (array)$user;
				$user = $ulf;
				
				$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getId() );
				$is_child = $permission->isChild( $user->getId(), $permission_children_ids );
				if ( $permission->Check('user','edit')
						OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user','edit_child') AND $is_child === TRUE ) ) {

                    $user_title = NULL;
					if ( $user->getTitle() != 0 AND is_object( $user->getTitleObject() ) ) {
						$user_title = $user->getTitleObject()->getName();
					}
					Debug::Text('Title: '. $user_title , __FILE__, __LINE__, __METHOD__,10);

					if ( $permission->Check('user','view_sin') == TRUE ) {
						$sin_number = $user->getSIN();
					} else {
						$sin_number = $user->getSecureSIN();
					}

					$user_data = 	array (
						'id' => $user->getId(),
						'company_id' => $user->getCompany(),
						'status' => $user->getStatus(),
						'user_name' => $user->getUserName(),
						'title_id' => $user->getTitle(),
						'job_skills' => $user->getJobSkills(),
						'title' => $user_title,
						'phone_id' => $user->getPhoneId(),
						'phone_password' => $user->getPhonePassword(),
						'ibutton_id' => $user->getIbuttonId(),
						'employee_number_only' => $user->getEmployeeNumberOnly(),
						'punch_machine_user_id' => $user->getPunchMachineUserID(),			

						'title_name' => $user->getNameTitle(),
						'first_name' => $user->getFirstName(),
						'middle_name' => $user->getMiddleName(),
						'full_name' => $user->getFullNameField(),
						'calling_name' => $user->getCallingName(),
						'name_with_initials' => $user->getNameWithInitials(),
						'last_name' => $user->getLastName(),
						'second_last_name' => $user->getSecondLastName(),
						'religion' => $user->getReligion(),
						'sex' => $user->getSex(),
						'marital' => $user->getMarital(),
						'address1' => $user->getAddress1(),
						'address2' => $user->getAddress2(),
						'address3' => $user->getAddress3(),
						'nic' =>$user->getNic(),
						'city' => $user->getCity(),
						'province' => $user->getProvince(),
						'country' => $user->getCountry(),
						'postal_code' => $user->getPostalCode(),
						'work_phone' => $user->getWorkPhone(),
						'work_phone_ext' => $user->getWorkPhoneExt(),
						'home_phone' => $user->getHomePhone(),
						'mobile_phone' => $user->getMobilePhone(),
						'office_mobile' => $user->getOfficeMobile(),
						'fax_phone' => $user->getFaxPhone(),
						'home_email' => $user->getHomeEmail(),
						'work_email' => $user->getWorkEmail(),
						'personal_email' => $user->getPersonalEmail(),
			
						'epf_registration_no'=>$current_company->getEpfNo(),
						'epf_membership_no'=> $user->getEpfMembershipNo(),		
						'birth_date' => $user->getBirthDate(),
						'retirement_date' => $user->getRetirementDate(),
						'hire_date' => $user->getHireDate(),
						'termination_date' => $user->getTerminationDate(),
						'resign_date' => $user->getResignDate(), 
						'confirmed_date'=> $user->getConfiremedDate(), 
						'sin' => $sin_number,

						'other_id1' => $user->getOtherID1(),
						'other_id2' => $user->getOtherID2(),
						'other_id3' => $user->getOtherID3(),
						'other_id4' => $user->getOtherID4(),
						'other_id5' => $user->getOtherID5(),

						'note' => $user->getNote(),
						'hire_note' => $user->getHireNote(),
						'termination_note' => $user->getTerminationNote(),                       

						'immediate_contact_person' => $user->getImmediateContactPerson(),
						'immediate_contact_no' => $user->getImmediateContactNo(),                       

						'bond_period' => $user->getBondPeriod(),

						'default_branch_id' => $user->getDefaultBranch(),
						'default_department_id' => $user->getDefaultDepartment(),
						'group_id' => $user->getGroup(),
						'currency_id' => $user->getCurrency(),
						'permission_level' => $user->getPermissionLevel(),
						'is_owner' => $is_owner,
						'is_child' => $is_child,
						'created_date' => $user->getCreatedDate(),
						'created_by' => $user->getCreatedBy(),
						'updated_date' => $user->getUpdatedDate(),
						'updated_by' => $user->getUpdatedBy(),
						'deleted_date' => $user->getDeletedDate(),
						'deleted_by' => $user->getDeletedBy(),
						
						'user_file'=> $user->getUserFilesUrl(),
						'file_name'=>$user->getFileName(),
						'probation'=>$user->getProbation(),
							
						'basis_of_employment'=>$user->getBasisOfEmployment(),    
						'month'=>$user->getMonth(),    

						'user_template_url'=>$user->getUserTemplateUrl(),
						'user_template_name'=>$user->getTemplateName(),							   


						'user_id_copy_url'=>$user->getUserIdCopyUrl(),
						'user_id_copy_name'=>$user->getUserIdCopyFileName(),

						'user_birth_certificate_url'=>$user->getUserBirthCertificateUrl(),
						'user_birth_certificate_name'=>$user->getUserBirthCertificateFileName(),

						'user_gs_letter_url'=>$user->getUserGsLetterUrl(),
						'user_gs_letter_name'=>$user->getUserGsLetterFileName(),

						'user_police_report_url'=>$user->getUserPoliceReportUrl(),
						'user_police_report_name'=>$user->getUserPoliceReportFileName(),

						'user_nda_url'=>$user->getUserNdaUrl(),
						'user_nda_name'=>$user->getUserNdaFileName(),

						'bond_url'=>$user->getBondUrl(),
						'bond_name'=>$user->getBondFileName()
																															
					);

					$pclfb = new PermissionControlListFactory();
					$pclfb->getByCompanyIdAndUserId( $user->getCompany(), $id );
					if ( $pclfb->getRecordCount() > 0 ) {
						$user_data['permission_control_id'] = $pclfb->getCurrent()->getId();
					}

					$ppslfb = new PayPeriodScheduleListFactory();
					$ppslfb->getByUserId( $id );
					if ( $ppslfb->getRecordCount() > 0 ) {
						$user_data['pay_period_schedule_id'] = $ppslfb->getCurrent()->getId();
					}

					$pglf = new PolicyGroupListFactory();
					$pglf->getByUserIds( $id );
					if ( $pglf->getRecordCount() > 0 ) {
						$user_data['policy_group_id'] = $pglf->getCurrent()->getId();
					}

					$hclf = new HierarchyControlListFactory();
					$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $user->getCompany(), $user->getID() );
					$user_data['hierarchy_control'] = $hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE );
					unset($hclf);
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}

			}
			
			$uwlf = new UserWageListFactory();
			$uwlf->getByUserId($user_data['id']);                       
			
			foreach ($uwlf->rs as $wage) {        
				$uwlf->data = (array)$wage;
				$wage = $uwlf;
				
				$wage_data = array(
					'id' => $wage->getId(),
					'user_id' => $wage->getUser(),										
					'wage' => Misc::removeTrailingZeros( $wage->getWage() )									
				);
			}

		} elseif ( $action == 'submit') {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($user_obj ) ) {
				Debug::Text('User Object set', __FILE__, __LINE__, __METHOD__,10);

				$user_data['is_owner'] = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() );
				$user_data['is_owner'] = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				//If user doesn't have permissions to edit these values, we have to pull them
				//out of the DB and update the array.
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $user_obj->getCompany();
				}

				if ( !isset( $user_data['status'] ) ) {
					$user_data['status'] = $user_obj->getStatus();
				}

				if ( !isset( $user_data['user_name'] ) ) {
					$user_data['user_name'] = $user_obj->getUserName();
				}

				if ( !isset( $user_data['phone_id'] ) ) {
					$user_data['phone_id'] = $user_obj->getPhoneId();
				}

				if ( !isset( $user_data['hire_date'] ) ) {
					$user_data['hire_date'] = $user_obj->getHireDate();
				}

				if ( !isset( $user_data['birth_date'] ) ) {
					$user_data['birth_date'] = $user_obj->getBirthDate();
				}

				if ( !isset( $user_data['province'] ) ) {
					$user_data['province'] = $user_obj->getProvince();
				}

				if ( !isset( $user_data['country'] ) ) {
					$user_data['country'] = $user_obj->getCountry();
				}

			} else {
				Debug::Text('User Object NOT set', __FILE__, __LINE__, __METHOD__,10);
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $company_id;
				}

			}


		} else {
			Debug::Text('Adding new User.', __FILE__, __LINE__, __METHOD__,10);

			//Get New Hire Defaults.
			$udlf = new UserDefaultListFactory(); 
			$udlf->getByCompanyId( $company_id );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
				$udf_obj = $udlf->getCurrent();

				$user_data = array(
					'company_id' => $company_id,
					'title_id' => $udf_obj->getTitle(),
					//'employee_number' => $udf_obj->getEmployeeNumber(),
					'city' => $udf_obj->getCity(),
					'province' => $udf_obj->getProvince(),
					'country' => $udf_obj->getCountry(),
					'work_phone' => $udf_obj->getWorkPhone(),
					'work_phone_ext' => $udf_obj->getWorkPhoneExt(),
					'work_email' => $udf_obj->getWorkEmail(),
					'hire_date' => $udf_obj->getHireDate(),
					'default_branch_id' => $udf_obj->getDefaultBranch(),
					'default_department_id' => $udf_obj->getDefaultDepartment(),
					'permission_control_id' => $udf_obj->getPermissionControl(),
					'pay_period_schedule_id' => $udf_obj->getPayPeriodSchedule(),
					'policy_group_id' => $udf_obj->getPolicyGroup(),
													'currency_id' => $udf_obj->getCurrency(),
				);
			}

			if ( !isset($user_obj ) ) {
				$user_obj = $ulf->getByIdAndCompanyId($current_user->getId(), $company_id )->getCurrent();
			}

			if ( !isset( $user_data['company_id'] ) ) {
				$user_data['company_id'] = $company_id;
			}

			if ( !isset( $user_data['country'] ) ) {
				$user_data['country'] = 'CA';
			}                    
            $ulf->getHighestEmployeeNumberOnlyByCompanyId( $company_id );                       
			if ( $ulf->getRecordCount() > 0 ) {
				Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,10);
				if ( is_numeric( $ulf->getCurrent()->getEmployeeNumberOnly() ) == TRUE ) {                              
                    $user_data['next_available_employee_number_only'] = $ulf->getCurrent()->getEmployeeNumberOnly()+1;
				} else {
					Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__,10);                                       
					$user_data['next_available_employee_number_only'] = NULL;
				}
			} else {                                         
                $user_data['next_available_employee_number_only'] = 1;                            
			}			
			
			

			if ( !isset($user_data['hire_date']) OR $user_data['hire_date'] == '' ) {
				$user_data['hire_date'] = time();
			}
		}
		//var_dump($user_data);              

		//Select box options;
		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $company_id );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $company_id );

		$culf = new CurrencyListFactory();
        $culf->getByCompanyId( $company_id );
		$currency_options = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		$hotf = new HierarchyObjectTypeFactory();
		$hierarchy_object_type_options = $hotf->getOptions('object_type');

		$hclf = new HierarchyControlListFactory();
		$hclf->getObjectTypeAppendedListByCompanyID( $company_id );
		$hierarchy_control_options = $hclf->getArrayByListFactory( $hclf, TRUE, TRUE );
                
                
        $clf = new CompanyListFactory();
        $clf->getById($company_id);
                
        $user_data['epf_registration_no'] =$current_company->getEpfNo();

		//Select box options;
		$user_data['branch_options'] = $branch_options;
		$user_data['department_options'] = $department_options;
        $user_data['currency_options'] = $currency_options;

		$user_data['sex_options'] = $uf->getOptions('sex');
                
        $user_data['title_name_options'] = $uf->getOptions('title');
		$user_data['status_options'] = $uf->getOptions('status');
        $user_data['religion_options'] = $uf->getOptions('religion');
        
        $user_data['marital_options'] = $uf->getOptions('marital');
		
                
                

		$clf = new CompanyListFactory();
		$user_data['country_options'] = $clf->getOptions('country');
		$user_data['province_options'] = $clf->getOptions('province', $user_data['country'] );

		$utlf = new UserTitleListFactory();
		$user_titles = $utlf->getByCompanyIdArray( $company_id );
		$user_data['title_options'] = $user_titles;

        $user_data['month_options'] = $uf->getOptions('month');
 
        $user_data['bond_period_option'] = $uf->getOptions('bond_period'); 
                
		//Get Permission Groups
		$pclf = new PermissionControlListFactory();
		$pclf->getByCompanyIdAndLevel( $company_id, $permission->getLevel() );
		$user_data['permission_control_options'] = $pclf->getArrayByListFactory( $pclf, FALSE );

		//Get pay period schedules
		$ppslf = new PayPeriodScheduleListFactory();
		$pay_period_schedules = $ppslf->getByCompanyIDArray( $company_id );
		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

		$pglf = new PolicyGroupListFactory();
		$policy_groups = $pglf->getByCompanyIDArray( $company_id );
		$user_data['policy_group_options'] = $policy_groups;

		$uglf = new UserGroupListFactory();
		$user_data['group_options'] = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $company_id ), 'TEXT', TRUE) );

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$user_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $company_id, 10 );

		$user_data['hierarchy_object_type_options'] = $hierarchy_object_type_options;
		$user_data['hierarchy_control_options'] = $hierarchy_control_options;

		//Company list.
		if ( $permission->Check('company','view') ) {
			$user_data['company_options'] = CompanyListFactory::getAllArray();
		} else {
			$user_data['company_options'] = array( $company_id => $current_company->getName() );
		}

		$user_data['user_options'] = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$viewData['user_data'] = $user_data;
		$viewData['uf'] = $uf;

		return view('users/EditUser', $viewData);
	}

	public function login(){
		if ( $permission->Check('company','view') AND $permission->Check('company','login_other_user') ) {

			Debug::Text('Login as different user: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//Get record for other user so we can check to make sure its not a primary company.
			$ulf = new UserListFactory();
			$ulf->getById( $id );
			if ( $ulf->getRecordCount() > 0 ) {
				if ( isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] != $ulf->getCurrent()->getCompany() ) {
					$authentication->changeObject( $id );

					TTLog::addEntry( $current_user->getID(), 'Login',  _('Switch User').': '. _('SourceIP').': '. $authentication->getIPAddress() .' '. _('SessionID') .': '.$authentication->getSessionID() .' '.  _('UserID').': '. $id, $current_user->getId(), 'authentication');

					Redirect::Page( URLBuilder::getURL( NULL, '../index.php') );
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}
			}
		} else {
			$permission->Redirect( FALSE ); //Redirect
		}
	}

	public function submit(){
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
						
						if ( isset($user_data['full_name']) ) {
				$uf->setFullNameField($user_data['full_name']);
			}
						
						if ( isset($user_data['calling_name']) ) {
				$uf->setCallingName($user_data['calling_name']);
			}
						
						
						if ( isset($user_data['name_with_initials']) ) {
				$uf->setNameWithInitials($user_data['name_with_initials']);
			}
						
					

			$uf->setLastName($user_data['last_name']);

			if ( isset($user_data['second_last_name']) ){
				$uf->setSecondLastName($user_data['second_last_name']);
			}

			if ( !empty($user_data['title_name']) ) {
				$uf->setNameTitle($user_data['title_name']);
			}
						
						
						if ( !empty($user_data['sex']) ) {
				$uf->setSex($user_data['sex']);
			}
						
						
						
						
						if ( !empty($user_data['religion']) ) {
				$uf->setReligion($user_data['religion']);
			}
						
						if ( !empty($user_data['marital']) ) {
							$uf->setMarital($user_data['marital']);
						}

			if ( isset($user_data['address1']) ) {
				$uf->setAddress1($user_data['address1']);
			}

			if ( isset($user_data['address2']) ) {
				$uf->setAddress2($user_data['address2']);
			}
						
						if ( isset($user_data['address3']) ) {
				$uf->setAddress3($user_data['address3']);
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
			
			
			/**
			 * ARSP NOTE -->
			 * I ADDED THIS CODE FOR THUNDER & NEON
			 */
		//                        if ( isset($user_data['employee_number_only']) ) {
		//				$uf->setEmployeeNumberOnly($user_data['employee_number_only']);
		//			}			

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
						
						if ( isset($user_data['office_mobile']) ) {
				$uf->setOfficeMobile($user_data['office_mobile']);
			}
						
						
						if ( isset($user_data['personal_email']) ) {
				$uf->setPersonalEmail($user_data['personal_email']);
			}
						
						
					

			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}
						

								$uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
								
								$date = new DateTime();
								$date->setTimestamp($uf->getBirthDate());
								$date->modify('+60 years');
						
								$uf->setRetirementDate(  $date->getTimestamp()  );
								$uf->setRetirementDate( $user_data['retirement_date']  );
		} else {
			//Force them to update all fields.

			$uf->setFirstName($user_data['first_name']);
			$uf->setMiddleName($user_data['middle_name']);
						$uf->setFullNameField($user_data['full_name']);
						$uf->setCallingName($user_data['calling_name']);
						$uf->setNameWithInitials($user_data['name_with_initials']);
					
			$uf->setLastName($user_data['last_name']);
			if ( isset($user_data['second_last_name']) ) {
				$uf->setSecondLastName($user_data['second_last_name']);
			}
			$uf->setSex($user_data['sex']);
						$uf->setMarital($user_data['marital']);
						$uf->setReligion($user_data['religion']);
			$uf->setAddress1($user_data['address1']);
			$uf->setAddress2($user_data['address2']);
						$uf->setAddress3($user_data['address3']);
						$uf->setNameTitle($user_data['title_name']);
						

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
						$uf->setOfficeMobile($user_data['office_mobile']);
						$uf->setPersonalEmail($user_data['personal_email']);
						$uf->setConfiremedDate( $user_data['confirmed_date'] );
						$uf->setResignDate( $user_data['resign_date'] );
						
			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}

			$uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
						
						$uf->setRetirementDate( $user_data['retirement_date']  );
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
				
				
				if ( isset($user_data['confirmed_date']) ) {
			$uf->setConfiremedDate( $user_data['confirmed_date'] );
		}    
		//                var_dump($uf->isValid()); die;
		//                echo '<pre>';                print_r($uf->getCurrent()); echo '<pre>'; die;

		if ( $uf->isValid() ) {
			$uf->Save(FALSE);
						
						
			$user_data['id'] = $uf->getId();
			Debug::Text('Inserted ID: '. $user_data['id'], __FILE__, __LINE__, __METHOD__,10);

			Redirect::Page( URLBuilder::getURL( array('id' => $user_data['id'], 'saved_search_id' => $saved_search_id, 'company_id' => $company_id, 'data_saved' => TRUE), 'EditUser.php') );

		}
		
	
	}
}


?>