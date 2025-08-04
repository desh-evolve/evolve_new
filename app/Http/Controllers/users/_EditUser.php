<?php

// namespace App\Http\Controllers\users;

// use App\Http\Controllers\Controller;
// use App\Models\Company\BranchListFactory;
// use App\Models\Company\CompanyListFactory;
// use App\Models\Core\CurrencyListFactory;
// use App\Models\Core\Debug;
// use Illuminate\Http\Request;

// use App\Models\Core\Environment;
// use App\Models\Core\FastTree;
// use App\Models\Core\Misc;
// use App\Models\Core\Option;
// use App\Models\Core\OtherFieldListFactory;
// use App\Models\Core\PermissionControlListFactory;
// use App\Models\Core\Redirect;
// use App\Models\Core\TTDate;
// use App\Models\Core\URLBuilder;
// use App\Models\Department\DepartmentListFactory;
// use App\Models\Hierarchy\HierarchyControlListFactory;
// use App\Models\Hierarchy\HierarchyListFactory;
// use App\Models\Hierarchy\HierarchyObjectTypeFactory;
// use App\Models\PayPeriod\PayPeriodScheduleListFactory;
// use App\Models\Policy\PolicyGroupListFactory;
// use App\Models\Users\UserDefaultListFactory;
// use App\Models\Users\UserFactory;
// use App\Models\Users\UserGenericDataFactory;
// use App\Models\Users\UserGroupListFactory;
// use App\Models\Users\UserListFactory;
// use App\Models\Users\UserTitleListFactory;
// use App\Models\Users\UserWageFactory;
// use App\Models\Users\UserWageListFactory;
// use Illuminate\Support\Facades\View;
// use DateTime;

// class EditUser extends Controller
// {
// 	protected $permission;
// 	protected $currentUser;
// 	protected $currentCompany;
// 	protected $userPrefs;

// 	public function __construct()
// 	{
// 		$basePath = Environment::getBasePath();
// 		require_once($basePath . '/app/Helpers/global.inc.php');
// 		require_once($basePath . '/app/Helpers/Interface.inc.php');

// 		$this->permission = View::shared('permission');
// 		$this->currentUser = View::shared('current_user');
// 		$this->currentCompany = View::shared('current_company');
// 		$this->userPrefs = View::shared('current_user_prefs');
// 	}

// 	public function index($id = null)
// 	{
// 		$id = $_GET['id'] ?? null;
// 		/*
// 			if ( !$this->permission->Check('user','enabled')
// 					OR !( $this->permission->Check('user','edit') OR $this->permission->Check('user','edit_own') OR $this->permission->Check('user','edit_child') OR $this->permission->Check('user','add')) ) {
// 				$this->permission->Redirect( FALSE ); //Redirect
// 			}
// 		*/

// 		$viewData['title'] = !empty($id) ? 'Edit Employee' : 'Add Employee';

// 		$current_company = $this->currentCompany;
// 		$current_user = $this->currentUser;
// 		$permission = $this->permission;

// 		$company_id = $current_company->getId();

// 		$ulf = new UserListFactory();
// 		$uf = new UserFactory();
// 		$hlf = new HierarchyListFactory();

// 		$this->permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId($current_company->getId(), $current_user->getId());
// 		//Include current user in list.
// 		if ($this->permission->Check('user', 'edit_own')) {
// 			$this->permission_children_ids[] = $current_user->getId();
// 		}

// 		$action = Misc::findSubmitButton();

// 		if ($this->permission->Check('company', 'view') == FALSE or $company_id == '' or $company_id == '-1') {
// 			$company_id = $current_company->getId();
// 		}

// 		if (isset($id)) {

// 			if ($this->permission->Check('company', 'view')) {
// 				$ulf->getById($id)->getCurrent();
// 			} else {
// 				$ulf->getByIdAndCompanyId($id, $company_id);
// 			}

// 			foreach ($ulf->rs as $user) {
// 				$ulf->data = (array)$user;
// 				$user = $ulf;

// 				$is_owner = $this->permission->isOwner($user->getCreatedBy(), $user->getId());
// 				$is_child = $this->permission->isChild($user->getId(), $this->permission_children_ids);
// 				if (
// 					$this->permission->Check('user', 'edit')
// 					or ($this->permission->Check('user', 'edit_own') and $is_owner === TRUE)
// 					or ($this->permission->Check('user', 'edit_child') and $is_child === TRUE)
// 				) {

// 					$user_title = NULL;
// 					if ($user->getTitle() != 0 and is_object($user->getTitleObject())) {
// 						$user_title = $user->getTitleObject()->getName();
// 					}
// 					Debug::Text('Title: ' . $user_title, __FILE__, __LINE__, __METHOD__, 10);

// 					if ($this->permission->Check('user', 'view_sin') == TRUE) {
// 						$sin_number = $user->getSIN();
// 					} else {
// 						$sin_number = $user->getSecureSIN();
// 					}

// 					$user_data = 	array(
// 						'id' => $user->getId(),
// 						'company_id' => $user->getCompany(),
// 						'status' => $user->getStatus(),
// 						'user_name' => $user->getUserName(),
// 						'title_id' => $user->getTitle(),
// 						'job_skills' => $user->getJobSkills(),
// 						'title' => $user_title,
// 						'phone_id' => $user->getPhoneId(),
// 						'phone_password' => $user->getPhonePassword(),
// 						'ibutton_id' => $user->getIbuttonId(),
// 						'employee_number_only' => $user->getEmployeeNumberOnly(),
// 						'punch_machine_user_id' => $user->getPunchMachineUserID(),

// 						'title_name' => $user->getNameTitle(),
// 						'first_name' => $user->getFirstName(),
// 						'middle_name' => $user->getMiddleName(),
// 						'full_name' => $user->getFullNameField(),
// 						'calling_name' => $user->getCallingName(),
// 						'name_with_initials' => $user->getNameWithInitials(),
// 						'last_name' => $user->getLastName(),
// 						'second_last_name' => $user->getSecondLastName(),
// 						'religion' => $user->getReligion(),
// 						'sex' => $user->getSex(),
// 						'marital' => $user->getMarital(),
// 						'address1' => $user->getAddress1(),
// 						'address2' => $user->getAddress2(),
// 						'address3' => $user->getAddress3(),
// 						'nic' => $user->getNic(),
// 						'city' => $user->getCity(),
// 						'province' => $user->getProvince(),
// 						'country' => $user->getCountry(),
// 						'postal_code' => $user->getPostalCode(),
// 						'work_phone' => $user->getWorkPhone(),
// 						'work_phone_ext' => $user->getWorkPhoneExt(),
// 						'home_phone' => $user->getHomePhone(),
// 						'mobile_phone' => $user->getMobilePhone(),
// 						'office_mobile' => $user->getOfficeMobile(),
// 						'fax_phone' => $user->getFaxPhone(),
// 						'home_email' => $user->getHomeEmail(),
// 						'work_email' => $user->getWorkEmail(),
// 						'personal_email' => $user->getPersonalEmail(),

// 						'epf_registration_no' => $current_company->getEpfNo(),
// 						'epf_membership_no' => $user->getEpfMembershipNo(),
// 						'birth_date' => $user->getBirthDate(),
// 						'retirement_date' => $user->getRetirementDate(),
// 						'hire_date' => $user->getHireDate(),
// 						'termination_date' => $user->getTerminationDate(),
// 						'resign_date' => $user->getResignDate(),
// 						'confirmed_date' => $user->getConfiremedDate(),
// 						'sin' => $sin_number,

// 						'other_id1' => $user->getOtherID1(),
// 						'other_id2' => $user->getOtherID2(),
// 						'other_id3' => $user->getOtherID3(),
// 						'other_id4' => $user->getOtherID4(),
// 						'other_id5' => $user->getOtherID5(),

// 						'note' => $user->getNote(),
// 						'hire_note' => $user->getHireNote(),
// 						'termination_note' => $user->getTerminationNote(),

// 						'immediate_contact_person' => $user->getImmediateContactPerson(),
// 						'immediate_contact_no' => $user->getImmediateContactNo(),

// 						'bond_period' => $user->getBondPeriod(),

// 						'default_branch_id' => $user->getDefaultBranch(),
// 						'default_department_id' => $user->getDefaultDepartment(),
// 						'group_id' => $user->getGroup(),
// 						'currency_id' => $user->getCurrency(),
// 						'permission_level' => $user->getPermissionLevel(),
// 						'is_owner' => $is_owner,
// 						'is_child' => $is_child,
// 						'created_date' => $user->getCreatedDate(),
// 						'created_by' => $user->getCreatedBy(),
// 						'updated_date' => $user->getUpdatedDate(),
// 						'updated_by' => $user->getUpdatedBy(),
// 						'deleted_date' => $user->getDeletedDate(),
// 						'deleted_by' => $user->getDeletedBy(),

// 						// 'user_file'=> $user->getUserFilesUrl(),
// 						// 'file_name'=>$user->getFileName(),
// 						'probation' => $user->getProbation(),

// 						// 'basis_of_employment'=>$user->getBasisOfEmployment(),
// 						'month' => $user->getMonth(),

// 						// 'user_template_url'=>$user->getUserTemplateUrl(),
// 						// 'user_template_name'=>$user->getTemplateName(),


// 						// 'user_id_copy_url'=>$user->getUserIdCopyUrl(),
// 						// 'user_id_copy_name'=>$user->getUserIdCopyFileName(),

// 						// 'user_birth_certificate_url'=>$user->getUserBirthCertificateUrl(),
// 						// 'user_birth_certificate_name'=>$user->getUserBirthCertificateFileName(),

// 						// 'user_gs_letter_url'=>$user->getUserGsLetterUrl(),
// 						// 'user_gs_letter_name'=>$user->getUserGsLetterFileName(),

// 						// 'user_police_report_url'=>$user->getUserPoliceReportUrl(),
// 						// 'user_police_report_name'=>$user->getUserPoliceReportFileName(),

// 						// 'user_nda_url'=>$user->getUserNdaUrl(),
// 						// 'user_nda_name'=>$user->getUserNdaFileName(),

// 						// 'bond_url'=>$user->getBondUrl(),
// 						// 'bond_name'=>$user->getBondFileName()

// 					);

// 					$pclfb = new PermissionControlListFactory();
// 					$pclfb->getByCompanyIdAndUserId($user->getCompany(), $id);
// 					if ($pclfb->getRecordCount() > 0) {
// 						$user_data['permission_control_id'] = $pclfb->getCurrent()->getId();
// 					}

// 					$ppslfb = new PayPeriodScheduleListFactory();
// 					$ppslfb->getByUserId($id);
// 					if ($ppslfb->getRecordCount() > 0) {
// 						$user_data['pay_period_schedule_id'] = $ppslfb->getCurrent()->getId();
// 					}

// 					$pglf = new PolicyGroupListFactory();
// 					$pglf->getByUserIds($id);
// 					if ($pglf->getRecordCount() > 0) {
// 						$user_data['policy_group_id'] = $pglf->getCurrent()->getId();
// 					}

// 					$hclf = new HierarchyControlListFactory();
// 					$hclf->getObjectTypeAppendedListByCompanyIDAndUserID($user->getCompany(), $user->getID());
// 					$user_data['hierarchy_control'] = $hclf->getArrayByListFactory($hclf, FALSE, TRUE, FALSE);
// 					unset($hclf);
// 				} else {
// 					$this->permission->Redirect(FALSE); //Redirect
// 				}
// 			}

// 			$uwlf = new UserWageListFactory();
// 			$uwlf->getByUserId($user_data['id']);

// 			foreach ($uwlf->rs as $wage) {
// 				$uwlf->data = (array)$wage;
// 				$wage = $uwlf;

// 				$wage_data = array(
// 					'id' => $wage->getId(),
// 					'user_id' => $wage->getUser(),
// 					'wage' => Misc::removeTrailingZeros($wage->getWage())
// 				);
// 			}
// 		} elseif ($action == 'submit') {
// 			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__, 10);

// 			if (isset($user_obj)) {
// 				Debug::Text('User Object set', __FILE__, __LINE__, __METHOD__, 10);

// 				$user_data['is_owner'] = $this->permission->isOwner($user_obj->getCreatedBy(), $user_obj->getId());
// 				$user_data['is_owner'] = $this->permission->isChild($user_obj->getId(), $this->permission_children_ids);

// 				//If user doesn't have permissions to edit these values, we have to pull them
// 				//out of the DB and update the array.
// 				if (!isset($user_data['company_id'])) {
// 					$user_data['company_id'] = $user_obj->getCompany();
// 				}

// 				if (!isset($user_data['status'])) {
// 					$user_data['status'] = $user_obj->getStatus();
// 				}

// 				if (!isset($user_data['user_name'])) {
// 					$user_data['user_name'] = $user_obj->getUserName();
// 				}

// 				if (!isset($user_data['phone_id'])) {
// 					$user_data['phone_id'] = $user_obj->getPhoneId();
// 				}

// 				if (!isset($user_data['hire_date'])) {
// 					$user_data['hire_date'] = $user_obj->getHireDate();
// 				}

// 				if (!isset($user_data['birth_date'])) {
// 					$user_data['birth_date'] = $user_obj->getBirthDate();
// 				}

// 				if (!isset($user_data['province'])) {
// 					$user_data['province'] = $user_obj->getProvince();
// 				}

// 				if (!isset($user_data['country'])) {
// 					$user_data['country'] = $user_obj->getCountry();
// 				}
// 			} else {
// 				Debug::Text('User Object NOT set', __FILE__, __LINE__, __METHOD__, 10);
// 				if (!isset($user_data['company_id'])) {
// 					$user_data['company_id'] = $company_id;
// 				}
// 			}
// 		} else {
// 			Debug::Text('Adding new User.', __FILE__, __LINE__, __METHOD__, 10);

// 			//Get New Hire Defaults.
// 			$udlf = new UserDefaultListFactory();
// 			$udlf->getByCompanyId($company_id);
// 			if ($udlf->getRecordCount() > 0) {
// 				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__, 10);
// 				$udf_obj = $udlf->getCurrent();

// 				$user_data = array(
// 					'company_id' => $company_id,
// 					'title_id' => $udf_obj->getTitle(),
// 					//'employee_number' => $udf_obj->getEmployeeNumber(),
// 					'city' => $udf_obj->getCity(),
// 					'province' => $udf_obj->getProvince(),
// 					'country' => $udf_obj->getCountry(),
// 					'work_phone' => $udf_obj->getWorkPhone(),
// 					'work_phone_ext' => $udf_obj->getWorkPhoneExt(),
// 					'work_email' => $udf_obj->getWorkEmail(),
// 					'hire_date' => $udf_obj->getHireDate(),
// 					'default_branch_id' => $udf_obj->getDefaultBranch(),
// 					'default_department_id' => $udf_obj->getDefaultDepartment(),
// 					'permission_control_id' => $udf_obj->getPermissionControl(),
// 					'pay_period_schedule_id' => $udf_obj->getPayPeriodSchedule(),
// 					'policy_group_id' => $udf_obj->getPolicyGroup(),
// 					'currency_id' => $udf_obj->getCurrency(),
// 				);
// 			}

// 			if (!isset($user_obj)) {
// 				$user_obj = $ulf->getByIdAndCompanyId($this->currentUser->getId(), $company_id)->getCurrent();
// 			}

// 			if (!isset($user_data['company_id'])) {
// 				$user_data['company_id'] = $company_id;
// 			}

// 			if (!isset($user_data['country'])) {
// 				$user_data['country'] = 'CA';
// 			}
// 			$ulf->getHighestEmployeeNumberOnlyByCompanyId($company_id);
// 			if ($ulf->getRecordCount() > 0) {
// 				Debug::Text('Highest Employee Number: ' . $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__, 10);
// 				if (is_numeric($ulf->getCurrent()->getEmployeeNumberOnly()) == TRUE) {
// 					$user_data['next_available_employee_number_only'] = $ulf->getCurrent()->getEmployeeNumberOnly() + 1;
// 				} else {
// 					Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__, 10);
// 					$user_data['next_available_employee_number_only'] = NULL;
// 				}
// 			} else {
// 				$user_data['next_available_employee_number_only'] = 1;
// 			}



// 			if (!isset($user_data['hire_date']) or $user_data['hire_date'] == '') {
// 				$user_data['hire_date'] = time();
// 			}
// 		}
// 		//var_dump($user_data);

// 		//Select box options;
// 		$blf = new BranchListFactory();
// 		$branch_options = $blf->getByCompanyIdArray($company_id);

// 		$dlf = new DepartmentListFactory();
// 		$department_options = $dlf->getByCompanyIdArray($company_id);

// 		$culf = new CurrencyListFactory();
// 		$culf->getByCompanyId($company_id);
// 		$currency_options = $culf->getArrayByListFactory($culf, FALSE, TRUE);

// 		$hotf = new HierarchyObjectTypeFactory();
// 		$hierarchy_object_type_options = $hotf->getOptions('object_type');

// 		$hclf = new HierarchyControlListFactory();
// 		$hclf->getObjectTypeAppendedListByCompanyID($company_id);
// 		$hierarchy_control_options = $hclf->getArrayByListFactory($hclf, TRUE, TRUE);


// 		$clf = new CompanyListFactory();
// 		$clf->getById($company_id);

// 		$user_data['epf_registration_no'] = $current_company->getEpfNo();

// 		//Select box options;
// 		$user_data['branch_options'] = $branch_options;
// 		$user_data['department_options'] = $department_options;
// 		$user_data['currency_options'] = $currency_options;

// 		$user_data['sex_options'] = $uf->getOptions('sex');

// 		$user_data['title_name_options'] = $uf->getOptions('title');
// 		$user_data['status_options'] = $uf->getOptions('status');
// 		$user_data['religion_options'] = $uf->getOptions('religion');

// 		$user_data['marital_options'] = $uf->getOptions('marital');




// 		$clf = new CompanyListFactory();
// 		$user_data['country_options'] = $clf->getOptions('country');
// 		$user_data['province_options'] = $clf->getOptions('province', $user_data['country']);

// 		$utlf = new UserTitleListFactory();
// 		$user_titles = $utlf->getByCompanyIdArray($company_id);
// 		$user_data['title_options'] = $user_titles;

// 		$user_data['month_options'] = $uf->getOptions('month');

// 		$user_data['bond_period_option'] = $uf->getOptions('bond_period');

// 		//Get Permission Groups
// 		$pclf = new PermissionControlListFactory();
// 		$pclf->getByCompanyIdAndLevel($company_id, $this->permission->getLevel());
// 		$user_data['permission_control_options'] = $pclf->getArrayByListFactory($pclf, FALSE);

// 		//Get pay period schedules
// 		$ppslf = new PayPeriodScheduleListFactory();
// 		$pay_period_schedules = $ppslf->getByCompanyIDArray($company_id);
// 		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

// 		$pglf = new PolicyGroupListFactory();
// 		$policy_groups = $pglf->getByCompanyIDArray($company_id);
// 		$user_data['policy_group_options'] = $policy_groups;

// 		$uglf = new UserGroupListFactory();
// 		$user_data['group_options'] = $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($company_id), 'TEXT', TRUE));

// 		//Get other field names
// 		$oflf = new OtherFieldListFactory();
// 		$user_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray($company_id, 10);

// 		$user_data['hierarchy_object_type_options'] = $hierarchy_object_type_options;
// 		$user_data['hierarchy_control_options'] = $hierarchy_control_options;

// 		//Company list.
// 		if ($this->permission->Check('company', 'view')) {
// 			$user_data['company_options'] = CompanyListFactory::getAllArray();
// 		} else {
// 			$user_data['company_options'] = array($company_id => $current_company->getName());
// 		}

// 		$user_data['user_options'] = UserListFactory::getArrayByListFactory($ulf, FALSE, TRUE);

// 		$viewData['user_data'] = $user_data;
// 		$viewData['uf'] = $uf;

// 		// dd($viewData);
// 		return view('users/EditUser', $viewData);
// 	}

// 	public function login()
// 	{
// 		if ($this->permission->Check('company', 'view') and $this->permission->Check('company', 'login_other_user')) {

// 			Debug::Text('Login as different user: ' . $id, __FILE__, __LINE__, __METHOD__, 10);
// 			//Get record for other user so we can check to make sure its not a primary company.
// 			$ulf = new UserListFactory();
// 			$ulf->getById($id);
// 			if ($ulf->getRecordCount() > 0) {
// 				if (isset($config_vars['other']['primary_company_id']) and $config_vars['other']['primary_company_id'] != $ulf->getCurrent()->getCompany()) {
// 					$authentication->changeObject($id);

// 					TTLog::addEntry($this->currentUser->getID(), 'Login',  _('Switch User') . ': ' . _('SourceIP') . ': ' . $authentication->getIPAddress() . ' ' . _('SessionID') . ': ' . $authentication->getSessionID() . ' ' .  _('UserID') . ': ' . $id, $this->currentUser->getId(), 'authentication');

// 					Redirect::Page(URLBuilder::getURL(NULL, '../index.php'));
// 				} else {
// 					$this->permission->Redirect(FALSE); //Redirect
// 				}
// 			}
// 		} else {
// 			$this->permission->Redirect(FALSE); //Redirect
// 		}
// 	}

// 	public function submit(Request $request, $id = null)
// 	{
// 		//Debug::setVerbosity( 11 );
// 		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);
// 		unset($id); //Do this so it doesn't reload the data from the DB.
// 		// //Additional permission checks.
// 		// if ( $this->permission->Check('company','view') ) {
// 		// 	$ulf->getById( $user_data['id'] );
// 		// } else {
// 		// 	$ulf->getByIdAndCompanyId( $user_data['id'], $current_company->getId() );
// 		// }

// 		$user_data = $request->input('user_data');
// 		// dd($user_data);
// 		$ulf = new UserListFactory();
// 		$uf = new UserFactory();

// 		$hlf = new HierarchyListFactory;

// 		$ulf->getById($user_data['id']);
// 		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId($this->currentCompany->getId(),  $this->currentUser->getId());

// 		// dd($ulf);
// 		if ($ulf->getRecordCount() > 0) {
// 			$user = $ulf->getCurrent();

// 			$is_owner = $this->permission->isOwner($user->getCreatedBy(), $user->getID());
// 			$is_child = $this->permission->isChild($user->getId(), $permission_children_ids);
// 			if (
// 				$this->permission->Check('user', 'edit')
// 				or ($this->permission->Check('user', 'edit_child') and $is_child === TRUE)
// 				or ($this->permission->Check('user', 'edit_own') and $is_owner === TRUE)
// 			) {
// 				// Security measure.
// 				if (!empty($user_data['id'])) {
// 					if ($this->permission->Check('company', 'view')) {
// 						$uf = $ulf->getById($user_data['id'])->getCurrent();
// 					} else {
// 						$uf = $ulf->getByIdAndCompanyId($user_data['id'], $this->currentCompany->getId())->getCurrent();
// 					}
// 				}
// 			} else {
// 				$this->permission->Redirect(FALSE); //Redirect
// 				exit;
// 			}
// 			unset($user);
// 		}

// 		if (isset($user_data['company_id'])) {
// 			if ($this->permission->Check('company', 'view')) {
// 				$uf->setCompany($user_data['company_id']);
// 			} else {
// 				$uf->setCompany($this->currentCompany->getId());
// 			}
// 		} else {
// 			$uf->setCompany($this->currentCompany->getId());
// 		}

// 		//Get New Hire Defaults.
// 		$udlf = new UserDefaultListFactory();
// 		$udlf->getByCompanyId($uf->getCompany());
// 		if ($udlf->getRecordCount() > 0) {
// 			Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__, 10);
// 			$udf_obj = $udlf->getCurrent();
// 		}

// 		if (DEMO_MODE == FALSE or $uf->isNew() == TRUE) {
// 			if (isset($user_data['status'])) {
// 				$uf->setStatus($user_data['status']);
// 			}

// 			if (isset($user_data['user_name'])) {
// 				$uf->setUserName($user_data['user_name']);
// 			}

// 			//Phone ID is optional now.
// 			if (isset($user_data['phone_id'])) {
// 				$uf->setPhoneId($user_data['phone_id']);
// 			}
// 		}

// 		if (DEMO_MODE == FALSE or $uf->isNew() == TRUE) {
// 			if (!empty($user_data['password']) or !empty($user_data['password2'])) {
// 				if ($user_data['password'] == $user_data['password2']) {
// 					$uf->setPassword($user_data['password']);
// 				} else {
// 					$uf->Validator->isTrue(
// 						'password',
// 						FALSE,
// 						__('Passwords don\'t match')
// 					);
// 				}
// 			}

// 			if (isset($user_data['phone_password'])) {
// 				$uf->setPhonePassword($user_data['phone_password']);
// 			}
// 		}

// 		if (
// 			isset($user_data['id']) &&
// 			$user_data['id'] != $this->currentUser->getID() &&
// 			$this->permission->Check('user', 'edit_advanced')
// 		) {
// 			//Don't force them to update all fields.
// 			//Unless they are editing their OWN user.
// 			$uf->setFirstName($user_data['first_name']);

// 			if (isset($user_data['middle_name'])) {
// 				$uf->setMiddleName($user_data['middle_name']);
// 			}

// 			if (isset($user_data['full_name'])) {
// 				$uf->setFullNameField($user_data['full_name']);
// 			}

// 			if (isset($user_data['calling_name'])) {
// 				$uf->setCallingName($user_data['calling_name']);
// 			}


// 			if (isset($user_data['name_with_initials'])) {
// 				$uf->setNameWithInitials($user_data['name_with_initials']);
// 			}



// 			$uf->setLastName($user_data['last_name']);

// 			if (isset($user_data['second_last_name'])) {
// 				$uf->setSecondLastName($user_data['second_last_name']);
// 			}

// 			if (!empty($user_data['title_name'])) {
// 				$uf->setNameTitle($user_data['title_name']);
// 			}


// 			if (!empty($user_data['sex'])) {
// 				$uf->setSex($user_data['sex']);
// 			}




// 			if (!empty($user_data['religion'])) {
// 				$uf->setReligion($user_data['religion']);
// 			}

// 			if (!empty($user_data['marital'])) {
// 				$uf->setMarital($user_data['marital']);
// 			}

// 			if (isset($user_data['address1'])) {
// 				$uf->setAddress1($user_data['address1']);
// 			}

// 			if (isset($user_data['address2'])) {
// 				$uf->setAddress2($user_data['address2']);
// 			}

// 			if (isset($user_data['address3'])) {
// 				$uf->setAddress3($user_data['address3']);
// 			}
// 			//ARSP EDIT CODE-----> ADD NEW CODE FOR N.I.C
// 			if (isset($user_data['nic'])) {
// 				$uf->setNic($user_data['nic']);
// 			}
// 			//ARSP EDIT CODE-----> ADD NEW CODE FOR probation
// 			if (isset($user_data['probation'])) {
// 				$uf->setProbation($user_data['probation']);
// 			}

// 			/**
// 			 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 			 */
// 			if (isset($user_data['basis_of_employment'])) {
// 				$uf->setBasisOfEmployment($user_data['basis_of_employment']);
// 			}

// 			/**
// 			 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 			 */
// 			if (isset($user_data['month'])) {
// 				$uf->setMonth($user_data['month']);
// 			}

// 			//ARSP EDIT CODE-----> ADD NEW CODE FOR EPF registration no
// 			if (isset($user_data['epf_registration_no'])) {
// 				$uf->setEpfRegistrationNo($user_data['epf_registration_no']);
// 			}

// 			//ARSP EDIT CODE-----> ADD NEW CODE FOR EPF membership no
// 			if (isset($user_data['epf_membership_no'])) {
// 				$uf->setEpfMembershipNo($user_data['epf_membership_no']);
// 			}


// 			/**
// 			 * ARSP NOTE -->
// 			 * I ADDED THIS CODE FOR THUNDER & NEON
// 			 */
// 			//                        if ( isset($user_data['employee_number_only']) ) {
// 			//				$uf->setEmployeeNumberOnly($user_data['employee_number_only']);
// 			//			}

// 			if (isset($user_data['city'])) {
// 				$uf->setCity($user_data['city']);
// 			}

// 			if (isset($user_data['country'])) {
// 				$uf->setCountry($user_data['country']);
// 			}

// 			if (isset($user_data['province'])) {
// 				$uf->setProvince($user_data['province']);
// 			}

// 			if (isset($user_data['postal_code'])) {
// 				$uf->setPostalCode($user_data['postal_code']);
// 			}

// 			if (isset($user_data['work_phone'])) {
// 				$uf->setWorkPhone($user_data['work_phone']);
// 			}

// 			if (isset($user_data['work_phone_ext'])) {
// 				$uf->setWorkPhoneExt($user_data['work_phone_ext']);
// 			}

// 			if (isset($user_data['home_phone'])) {
// 				$uf->setHomePhone($user_data['home_phone']);
// 			}

// 			if (isset($user_data['mobile_phone'])) {
// 				$uf->setMobilePhone($user_data['mobile_phone']);
// 			}

// 			if (isset($user_data['fax_phone'])) {
// 				$uf->setFaxPhone($user_data['fax_phone']);
// 			}

// 			if (isset($user_data['home_email'])) {
// 				$uf->setHomeEmail($user_data['home_email']);
// 			}

// 			if (isset($user_data['work_email'])) {
// 				$uf->setWorkEmail($user_data['work_email']);
// 			}

// 			if (isset($user_data['office_mobile'])) {
// 				$uf->setOfficeMobile($user_data['office_mobile']);
// 			}


// 			if (isset($user_data['personal_email'])) {
// 				$uf->setPersonalEmail($user_data['personal_email']);
// 			}




// 			if (isset($user_data['sin'])) {
// 				$uf->setSIN($user_data['sin']);
// 			}


// 			$uf->setBirthDate(TTDate::getTimeStampFromSmarty('birth_', $user_data));

// 			$date = new DateTime();
// 			$date->setTimestamp($uf->getBirthDate());
// 			$date->modify('+60 years');

// 			$uf->setRetirementDate($date->getTimestamp());
// 			$uf->setRetirementDate($user_data['retirement_date']);
// 		} else {
// 			//Force them to update all fields.

// 			$uf->setFirstName($user_data['first_name']);
// 			$uf->setMiddleName($user_data['middle_name'] ?? '');
// 			$uf->setFullNameField($user_data['full_name']);
// 			$uf->setCallingName($user_data['calling_name'] ?? '');
// 			$uf->setNameWithInitials($user_data['name_with_initials']);

// 			$uf->setLastName($user_data['last_name']);
// 			if (isset($user_data['second_last_name'])) {
// 				$uf->setSecondLastName($user_data['second_last_name']);
// 			}
// 			$uf->setSex($user_data['sex']);
// 			$uf->setMarital($user_data['marital']);
// 			$uf->setReligion($user_data['religion']);
// 			$uf->setAddress1($user_data['address1']);
// 			$uf->setAddress2($user_data['address2']);
// 			$uf->setAddress3($user_data['address3']);
// 			$uf->setNameTitle($user_data['title_name']);


// 			//ARSP EDIT CODE--->
// 			$uf->setNic($user_data['nic']);

// 			//ARSP EDIT CODE---> ADD NEW CODE FOR PROBATION PERIOD
// 			$uf->setProbation($user_data['probation'] ?? '');

// 			//ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
// 			$uf->setEpfRegistrationNo($user_data['epf_registration_no']);

// 			//ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
// 			$uf->setEpfMembershipNo($user_data['epf_membership_no']);

// 			$uf->setCity($user_data['city']);

// 			if (isset($user_data['country'])) {
// 				$uf->setCountry($user_data['country']);
// 			}

// 			if (isset($user_data['province'])) {
// 				$uf->setProvince($user_data['province']);
// 			}

// 			$uf->setPostalCode($user_data['postal_code']);
// 			$uf->setWorkPhone($user_data['work_phone']);
// 			$uf->setWorkPhoneExt($user_data['work_phone_ext']);
// 			$uf->setHomePhone($user_data['home_phone']);
// 			$uf->setMobilePhone($user_data['mobile_phone']);
// 			$uf->setFaxPhone($user_data['fax_phone']);
// 			$uf->setHomeEmail($user_data['home_email'] ?? '');
// 			$uf->setWorkEmail($user_data['work_email']);
// 			$uf->setOfficeMobile($user_data['office_mobile']);
// 			$uf->setPersonalEmail($user_data['personal_email']);
// 			$uf->setConfiremedDate($user_data['confirmed_date']);
// 			$uf->setResignDate($user_data['resign_date']);

// 			if (isset($user_data['sin'])) {
// 				$uf->setSIN($user_data['sin']);
// 			}
// 			$uf->setBirthDate(TTDate::getTimeStampFromSmarty('birth_', $user_data));

// 			$uf->setRetirementDate($user_data['retirement_date']);
// 		}

// 		if (
// 			DEMO_MODE == FALSE
// 			and isset($user_data['permission_control_id'])
// 			and $uf->getPermissionLevel() <= $this->permission->getLevel()
// 			and ($this->permission->Check('permission', 'edit') or $this->permission->Check('permission', 'edit_own') or $this->permission->Check('user', 'edit_permission_group'))
// 		) {
// 			$uf->setPermissionControl($user_data['permission_control_id']);
// 		} elseif (isset($udf_obj) and is_object($udf_obj) and $uf->isNew() == TRUE) {
// 			$uf->setPermissionControl($udf_obj->getPermissionControl());
// 		}

// 		if (isset($user_data['pay_period_schedule_id']) and ($this->permission->Check('pay_period_schedule', 'edit') or $this->permission->Check('user', 'edit_pay_period_schedule'))) {
// 			$uf->setPayPeriodSchedule($user_data['pay_period_schedule_id']);
// 		} elseif (isset($udf_obj) and is_object($udf_obj) and $uf->isNew() == TRUE) {
// 			$uf->setPayPeriodSchedule($udf_obj->getPayPeriodSchedule());
// 		}

// 		if (isset($user_data['policy_group_id']) and ($this->permission->Check('policy_group', 'edit') or $this->permission->Check('user', 'edit_policy_group'))) {
// 			$uf->setPolicyGroup($user_data['policy_group_id']);
// 		} elseif (isset($udf_obj) and is_object($udf_obj) and $uf->isNew() == TRUE) {
// 			$uf->setPolicyGroup($udf_obj->getPolicyGroup());
// 		}

// 		if (isset($user_data['hierarchy_control']) and ($this->permission->Check('hierarchy', 'edit') or $this->permission->Check('user', 'edit_hierarchy'))) {
// 			$uf->setHierarchyControl($user_data['hierarchy_control']);
// 		}

// 		if (isset($user_data['currency_id'])) {
// 			$uf->setCurrency($user_data['currency_id']);
// 		} elseif (isset($udf_obj) and is_object($udf_obj) and $uf->isNew() == TRUE) {
// 			$uf->setCurrency($udf_obj->getCurrency());
// 		}

// 		if (isset($user_data['hire_date'])) {
// 			$uf->setHireDate($user_data['hire_date']);
// 		}
// 		if (isset($user_data['termination_date'])) {
// 			$uf->setTerminationDate($user_data['termination_date']);
// 		}

// 		/**
// 		 * ARSP NOTE -->
// 		 * I HIDE THIS ORIGINAL CODE FOR THUNDER & NEON AND ADDED NEW CODE
// 		 */
// 		//if ( isset($user_data['employee_number']) ) {
// 		//	$uf->setEmployeeNumber( $user_data['employee_number'] );
// 		//}

// 		/**
// 		 * ARSP NOTE -->
// 		 * I MODIFIED ABOVE ORIGINAL CODE THUNDER & NEON
// 		 */
// 		if (isset($user_data['employee_number_only'])) {
// 			$uf->setEmployeeNumber($user_data['branch_short_id'] . $user_data['employee_number_only']);
// 		}

// 		/**
// 		 * ARSP NOTE -->
// 		 * I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
// 		 */
// 		if (isset($user_data['employee_number_only'])) {
// 			$uf->setEmployeeNumberOnly($user_data['employee_number_only'], $user_data['default_branch_id']); //ARSP NOTE --> I ADDED EXTRA PARAMETER FOR THUNDER & NEON
// 		}

// 		/**
// 		 * ARSP NOTE -->
// 		 * I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
// 		 */
// 		if (isset($user_data['punch_machine_user_id'])) {
// 			$uf->setPunchMachineUserID($user_data['punch_machine_user_id']);
// 		}

// 		if (isset($user_data['default_branch_id'])) {
// 			$uf->setDefaultBranch($user_data['default_branch_id']);
// 		}
// 		if (isset($user_data['default_department_id'])) {
// 			$uf->setDefaultDepartment($user_data['default_department_id']);
// 		}
// 		if (isset($user_data['group_id'])) {
// 			$uf->setGroup($user_data['group_id']);
// 		}
// 		if (isset($user_data['title_id'])) {
// 			$uf->setTitle($user_data['title_id']);
// 		}

// 		/**
// 		 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		 * EMPLOYEE JOB SKILLS
// 		 */
// 		if (isset($user_data['job_skills'])) {
// 			$uf->setJobSkills($user_data['job_skills'],  $user_data['job_skills']);
// 		}

// 		if (isset($user_data['ibutton_id'])) {
// 			$uf->setIButtonId($user_data['ibutton_id']);
// 		}
// 		if (isset($user_data['other_id1'])) {
// 			$uf->setOtherID1($user_data['other_id1']);
// 		}
// 		if (isset($user_data['other_id2'])) {
// 			$uf->setOtherID2($user_data['other_id2']);
// 		}
// 		if (isset($user_data['other_id3'])) {
// 			$uf->setOtherID3($user_data['other_id3']);
// 		}
// 		if (isset($user_data['other_id4'])) {
// 			$uf->setOtherID4($user_data['other_id4']);
// 		}
// 		if (isset($user_data['other_id5'])) {
// 			$uf->setOtherID5($user_data['other_id5']);
// 		}

// 		if (isset($user_data['note'])) {
// 			$uf->setNote($user_data['note']);
// 		}

// 		//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		if (isset($user_data['hire_note'])) {
// 			$uf->setHireNote($user_data['hire_note']);
// 		}

// 		//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		if (isset($user_data['termination_note'])) {
// 			$uf->setTerminationNote($user_data['termination_note']);
// 		}

// 		//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		if (isset($user_data['immediate_contact_person'])) {
// 			$uf->setImmediateContactPerson($user_data['immediate_contact_person']);
// 		}


// 		//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		if (isset($user_data['immediate_contact_no'])) {
// 			$uf->setImmediateContactNo($user_data['immediate_contact_no']);
// 		}

// 		//ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		if (isset($user_data['bond_period'])) {
// 			$uf->setBondPeriod($user_data['bond_period']);
// 		}

// 		/**
// 		 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
// 		 */
// 		if (isset($user_data['resign_date'])) {
// 			$uf->setResignDate($user_data['resign_date']);
// 		}


// 		if (isset($user_data['confirmed_date'])) {
// 			$uf->setConfiremedDate($user_data['confirmed_date']);
// 		}
// 		//                var_dump($uf->isValid()); die;
// 		//                echo '<pre>';                print_r($uf->getCurrent()); echo '<pre>'; die;

// 		if ($uf->isValid()) {
// 			$uf->Save(FALSE);


// 			$user_data['id'] = $uf->getId();
// 			Debug::Text('Inserted ID: ' . $user_data['id'], __FILE__, __LINE__, __METHOD__, 10);

// 			return redirect()->to(URLBuilder::getURL(null, '/admin/userlist'))->with('success', 'USer saved successfully.');

// 			// Redirect::Page(URLBuilder::getURL(array('id' => $user_data['id'], 'saved_search_id' => $saved_search_id, 'company_id' => $company_id, 'data_saved' => TRUE), 'EditUser.php'));
// 		}
// 	}
// }

// <?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FastTree;
use App\Models\Core\Misc;
use App\Models\Core\OtherFieldListFactory;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\TTDate;
use App\Models\Department\DepartmentListFactory;
use App\Models\Hierarchy\HierarchyControlListFactory;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\Hierarchy\HierarchyObjectTypeFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\Users\UserWageListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use DateTime;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;

class _EditUser extends Controller
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

		// Initialize shared data using middleware or service provider
		$this->permission = View::shared('permission');
		$this->currentUser = View::shared('current_user');
		$this->currentCompany = View::shared('current_company');
		$this->userPrefs = View::shared('current_user_prefs');
	}

	public function index(Request $request)
	{
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;

		$id = $request->input('id');
		// dd($this->currentCompany->getId());
		$saved_search_id = $request->input('saved_search_id');
		$incomplete = $request->input('incomplete');
		$data_saved = $request->input('data_saved');
		$company_id = $current_company->getId();

		// Permission check
		if (!$this->hasUserEditPermission()) {
			return redirect()->route('home')->with('error', 'Unauthorized access');
		}

		$viewData['title'] = $id ? 'Edit Employee' : 'Add Employee';
		$ulf = new UserListFactory();
		$uf = new UserFactory();
		$hlf = new HierarchyListFactory();

		// Get permission children IDs
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId(
			$this->currentCompany->getId(),
			$this->currentUser->getId()
		);
		if ($this->permission->Check('user', 'edit_own')) {
			$permission_children_ids[] = $this->currentUser->getId();
		}

		// Initialize user data
		$user_data = [];
		if ($id) {
			$user_data = $this->fetchUserData($id, $company_id, $permission_children_ids);
			if (!$user_data) {
				return redirect()->route('home')->with('error', 'User not found or unauthorized');
			}
		} else {
			$user_data = $this->initializeNewUserData($company_id, $ulf);
		}

		// Fetch form options
		$user_data = array_merge($user_data, $this->fetchFormOptions($company_id, $uf));

		// Calculate warning messages
		$user_data = array_merge($user_data, $this->calculateWarnings($user_data, $uf));

		// Handle file data
		$user_data = array_merge($user_data, $this->fetchFileData($user_data));

		$viewData['user_data'] = $user_data;
		$viewData['uf'] = $uf;
		$viewData['saved_search_id'] = $saved_search_id;
		$viewData['incomplete'] = $incomplete;
		$viewData['data_saved'] = $data_saved;

		// return view('users.edit-user', $viewData);
		return view('users/_EditUser', $viewData);
	}

	public function login(Request $request)
	{
		$id = $request->input('id');
		if (!$this->permission->Check('company', 'view') || !$this->permission->Check('company', 'login_other_user')) {
			return redirect()->route('home')->with('error', 'Unauthorized access');
		}

		$ulf = new UserListFactory();
		$ulf->getById($id);
		if ($ulf->getRecordCount() > 0) {
			$user = $ulf->getCurrent();
			if (config('app.primary_company_id') !== $user->getCompany()) {
				$authentication = app('Authentication'); // Assume a service for authentication
				$authentication->changeObject($id);

				\App\Models\Core\TTLog::addEntry(
					$this->currentUser->getID(),
					'Login',
					__('Switch User') . ': SourceIP: ' . $authentication->getIPAddress() .
						' SessionID: ' . $authentication->getSessionID() . ' UserID: ' . $id,
					$this->currentUser->getId(),
					'authentication'
				);

				return redirect()->route('home');
			}
		}

		return redirect()->route('home')->with('error', 'Invalid user or company');
	}

	public function submit(Request $request)
	{
		$user_data = $request->input('user_data', []);
		$delete_file_name = $request->input('delete_file_name');
		$delete_user_id = $request->input('delete_user_id');
		$delete_file_type = $request->input('delete_file_type');

		// Validate input
		$this->validateUserData($request);

		// Permission check
		if (!$this->hasUserEditPermission()) {
			return redirect()->route('home')->with('error', 'Unauthorized access');
		}

		$ulf = new UserListFactory();
		$uf = new UserFactory();
		$hlf = new HierarchyListFactory();

		$company_id = $this->currentCompany->getId();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId(
			$company_id,
			$this->currentUser->getId()
		);

		// Handle existing user
		if (!empty($user_data['id'])) {
			$ulf->getById($user_data['id']);
			if ($ulf->getRecordCount() > 0) {
				$user = $ulf->getCurrent();
				$is_owner = $this->permission->isOwner($user->getCreatedBy(), $user->getId());
				$is_child = $this->permission->isChild($user->getId(), $permission_children_ids);

				if (
					!$this->permission->Check('user', 'edit') &&
					!($this->permission->Check('user', 'edit_child') && $is_child) &&
					!($this->permission->Check('user', 'edit_own') && $is_owner)
				) {
					return redirect()->route('home')->with('error', 'Unauthorized access');
				}

				$uf = $this->permission->Check('company', 'view')
					? $ulf->getById($user_data['id'])->getCurrent()
					: $ulf->getByIdAndCompanyId($user_data['id'], $company_id)->getCurrent();
			}
		}

		// Set company
		$uf->setCompany($user_data['company_id'] ?? $company_id);

		// Apply new hire defaults for new users
		$udlf = new UserDefaultListFactory();
		$udlf->getByCompanyId($uf->getCompany());
		$udf_obj = $udlf->getRecordCount() > 0 ? $udlf->getCurrent() : null;

		// Set user attributes
		$this->setUserAttributes($uf, $user_data, $udf_obj);

		// Handle file deletion
		if ($delete_file_name && $delete_user_id && $delete_file_type) {
			$this->deleteUserFile($delete_file_name, $delete_user_id, $delete_file_type);
		}

		// Validate and save
		if ($uf->isValid()) {
			$uf->Save(false);
			$user_id = $uf->getId();

			// Generate appointment letter
			// $this->generateAppointmentLetter($user_data, $user_id);
			// return redirect()->to(URLBuilder::getURL(null, '/department'))->with('success', 'Department saved successfully.');

			return redirect()->route('admin.userlist')->with('success', 'User saved successfully.');
		}

		return back()->withErrors($uf->Validator->getErrors())->withInput();
	}

	protected function hasUserEditPermission()
	{
		return $this->permission->Check('user', 'enabled') &&
			($this->permission->Check('user', 'edit') ||
				$this->permission->Check('user', 'edit_own') ||
				$this->permission->Check('user', 'edit_child') ||
				$this->permission->Check('user', 'add'));
	}

	protected function fetchUserData($id, $company_id, $permission_children_ids)
	{

		$ulf = new UserListFactory();
		$user_data = [];

		$ulf->getByIdAndCompanyId($id, $company_id);
		foreach ($ulf->rs as $user) {
			$ulf->data = (array)$user;
			$user = $ulf;

			$is_owner = $this->permission->isOwner($user->getCreatedBy(), $user->getId());
			$is_child = $this->permission->isChild($user->getId(), $permission_children_ids);

			if (
				$this->permission->Check('user', 'edit') ||
				($this->permission->Check('user', 'edit_own') && $is_owner) ||
				($this->permission->Check('user', 'edit_child') && $is_child)
			) {
				$user_title = $user->getTitle() && is_object($user->getTitleObject())
					? $user->getTitleObject()->getName()
					: null;
				$sin_number = $this->permission->Check('user', 'view_sin')
					? $user->getSIN()
					: $user->getSecureSIN();

				$user_data = [
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
					'nic' => $user->getNic(),
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
					'epf_registration_no' => $this->currentCompany->getEpfNo(),
					'epf_membership_no' => $user->getEpfMembershipNo(),
					'birth_date' => $user->getBirthDate(),
					'retirement_date' => $user->getRetirementDate(),
					'hire_date' => $user->getHireDate(),
					'termination_date' => $user->getTerminationDate(),
					'resign_date' => $user->getResignDate(),
					'confirmed_date' => $user->getConfiremedDate(),
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
					'probation' => $user->getProbation(),
					'basis_of_employment' => $user->getBasisOfEmployment(),
					'month' => $user->getMonth(),
				];

				// Fetch additional data
				$pclfb = new PermissionControlListFactory();
				$pclfb->getByCompanyIdAndUserId($user->getCompany(), $id);
				if ($pclfb->getRecordCount() > 0) {
					$user_data['permission_control_id'] = $pclfb->getCurrent()->getId();
				}

				$ppslfb = new PayPeriodScheduleListFactory();
				$ppslfb->getByUserId($id);
				if ($ppslfb->getRecordCount() > 0) {
					$user_data['pay_period_schedule_id'] = $ppslfb->getCurrent()->getId();
				}

				$pglf = new PolicyGroupListFactory();
				$pglf->getByUserIds($id);
				if ($pglf->getRecordCount() > 0) {
					$user_data['policy_group_id'] = $pglf->getCurrent()->getId();
				}

				$hclf = new HierarchyControlListFactory();
				$hclf->getObjectTypeAppendedListByCompanyIDAndUserID($user->getCompany(), $user->getId());
				$user_data['hierarchy_control'] = $hclf->getArrayByListFactory($hclf, false, true, false);

				// Fetch wage data
				$uwlf = new UserWageListFactory();
				$uwlf->getByUserId($id);
				foreach ($uwlf as $wage) {
					$user_data['wage'] = Misc::removeTrailingZeros($wage->getWage());
				}
			}
		}

		return $user_data;
	}

	protected function initializeNewUserData($company_id, UserListFactory $ulf)
	{
		$udlf = new UserDefaultListFactory();
		$udlf->getByCompanyId($company_id);
		$user_data = [];

		if ($udlf->getRecordCount() > 0) {
			$udf_obj = $udlf->getCurrent();
			$user_data = [
				'company_id' => $company_id,
				'title_id' => $udf_obj->getTitle(),
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
			];
		}

		$user_data['company_id'] = $user_data['company_id'] ?? $company_id;
		$user_data['country'] = $user_data['country'] ?? 'CA';
		$user_data['hire_date'] = $user_data['hire_date'] ?? time();

		$ulf->getHighestEmployeeNumberOnlyByCompanyId($company_id);
		if ($ulf->getRecordCount() > 0 && is_numeric($ulf->getCurrent()->getEmployeeNumberOnly())) {
			$user_data['next_available_employee_number_only'] = $ulf->getCurrent()->getEmployeeNumberOnly() + 1;
		} else {
			$user_data['next_available_employee_number_only'] = 1;
		}

		return $user_data;
	}

	protected function fetchFormOptions($company_id, UserFactory $uf)
	{
		$blf = new BranchListFactory();
		$dlf = new DepartmentListFactory();
		$culf = new CurrencyListFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hclf = new HierarchyControlListFactory();
		$clf = new CompanyListFactory();
		$utlf = new UserTitleListFactory();
		$pclf = new PermissionControlListFactory();
		$ppslf = new PayPeriodScheduleListFactory();
		$pglf = new PolicyGroupListFactory();
		$uglf = new UserGroupListFactory();
		$oflf = new OtherFieldListFactory();

		return [
			'branch_options' => $blf->getByCompanyIdArray($company_id),
			'department_options' => $dlf->getByCompanyIdArray($company_id),
			'currency_options' => $culf->getByCompanyId($company_id)->getArrayByListFactory($culf, false, true),
			'hierarchy_object_type_options' => $hotf->getOptions('object_type'),
			'hierarchy_control_options' => $hclf->getObjectTypeAppendedListByCompanyID($company_id)->getArrayByListFactory($hclf, true, true),
			'epf_registration_no' => $this->currentCompany->getEpfNo(),
			'sex_options' => $uf->getOptions('sex'),
			'title_name_options' => $uf->getOptions('title'),
			'status_options' => $uf->getOptions('status'),
			'religion_options' => $uf->getOptions('religion'),
			'marital_options' => $uf->getOptions('marital'),
			'country_options' => $clf->getOptions('country'),
			'province_options' => $clf->getOptions('province', $user_data['country'] ?? 'CA'),
			'title_options' => $utlf->getByCompanyIdArray($company_id),
			'month_options' => $uf->getOptions('month'),
			'bond_period_option' => $uf->getOptions('bond_period'),
			'permission_control_options' => $pclf->getByCompanyIdAndLevel($company_id, $this->permission->getLevel())->getArrayByListFactory($pclf, false),
			'pay_period_schedule_options' => $ppslf->getByCompanyIDArray($company_id),
			'policy_group_options' => $pglf->getByCompanyIDArray($company_id),
			'group_options' => $uglf->getArrayByNodes(FastTree::FormatArray($uglf->getByCompanyIdArray($company_id), 'TEXT', true)),
			'other_field_names' => $oflf->getByCompanyIdAndTypeIdArray($company_id, 10),
			'company_options' => $this->permission->Check('company', 'view')
				? CompanyListFactory::getAllArray()
				: [$company_id => $this->currentCompany->getName()],
		];
	}

	/*
	protected function fetchFileData($user_data)
	{
		$file_types = [
			'user_file' => 'user_files',
			'user_template' => 'user_templates',
			'user_id_copy' => 'user_id_copies',
			'user_birth_certificate' => 'user_birth_certificates',
			'user_gs_letter' => 'user_gs_letters',
			'user_police_report' => 'user_police_reports',
			'user_nda' => 'user_ndas',
			'bond' => 'bonds',
		];

		$file_data = [];
		foreach ($file_types as $type => $disk) {
			$path = "user_{$user_data['id']}/{$type}";
			$files = Storage::disk($disk)->files($path);
			$urls = array_map(fn($file) => Storage::disk($disk)->url($file), $files);
			$names = array_map('basename', $files);

			$file_data["{$type}_url"] = $urls;
			$file_data["{$type}_name"] = $names;
			$file_data["{$type}_array_size"] = count($names);
			$file_data["var_" . substr($type, 0, 3)] = 1; // For iteration index
		}

		return $file_data;
	}
	*/
	protected function fetchFileData($user_data)
    {
        $file_types = [
            'user_file' => 'user_files',
            'user_template' => 'user_templates',
            'user_id_copy' => 'user_id_copies',
            'user_birth_certificate' => 'user_birth_certificates',
            'user_gs_letter' => 'user_gs_letters',
            'user_police_report' => 'user_police_reports',
            'user_nda' => 'user_ndas',
            'bond' => 'bonds',
        ];

        $file_data = [];
        foreach ($file_types as $type => $disk) {
            try {
                // Check if disk exists
                if (!config("filesystems.disks.$disk")) {
                    Log::warning("Disk [$disk] is not configured in filesystems.php");
                    continue;
                }

                // Check if user ID is set (skip for new users)
                if (empty($user_data['id'])) {
                    $file_data["{$type}_url"] = [];
                    $file_data["{$type}_name"] = [];
                    $file_data["{$type}_array_size"] = 0;
                    $file_data["var_" . substr($type, 0, 3)] = 1;
                    continue;
                }

                $path = "user_{$user_data['id']}/{$type}";
                $files = Storage::disk($disk)->files($path);
                $urls = array_map(fn($file) => Storage::disk($disk)->url($file), $files);
                $names = array_map('basename', $files);

                $file_data["{$type}_url"] = $urls;
                $file_data["{$type}_name"] = $names;
                $file_data["{$type}_array_size"] = count($names);
                $file_data["var_" . substr($type, 0, 3)] = 1; // For iteration index
            } catch (\Exception $e) {
                Log::error("Error fetching files for disk [$disk]", [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
                $file_data["{$type}_url"] = [];
                $file_data["{$type}_name"] = [];
                $file_data["{$type}_array_size"] = 0;
                $file_data["var_" . substr($type, 0, 3)] = 1;
            }
        }

        return $file_data;
    }

	protected function calculateWarnings($user_data, UserFactory $uf)
	{
		$warnings = [];

		// Probation warning
		if (!empty($user_data['id']) && !empty($user_data['hire_date']) && !empty($user_data['probation']) && $user_data['probation'] > 0) {
			$warnings['probation_warning'] = $uf->getWarning($user_data['hire_date'], $user_data['probation']);
		}

		// Basis of employment warning
		if (
			!empty($user_data['id']) && !empty($user_data['hire_date']) && !empty($user_data['month']) && $user_data['month'] > 0 &&
			!empty($user_data['basis_of_employment']) && !in_array($user_data['basis_of_employment'], [4, 6])
		) {
			if ($user_data['basis_of_employment'] != 5) {
				$warnings['basis_of_employment_warning'] = $uf->getWarning1(
					$user_data['hire_date'],
					$user_data['month'],
					$user_data['basis_of_employment']
				);
			} elseif ($user_data['basis_of_employment'] == 5 && !empty($user_data['resign_date'])) {
				$warnings['basis_of_employment_warning'] = $uf->getWarning1(
					$user_data['resign_date'],
					3,
					$user_data['basis_of_employment']
				);
			}
		}

		// Bond warning
		if (!empty($user_data['id']) && !empty($user_data['hire_date']) && !empty($user_data['bond_period']) && $user_data['bond_period'] > 0) {
			$warnings['bond_warning'] = $uf->getWarning2($user_data['hire_date'], $user_data['bond_period']);
		}

		return $warnings;
	}

	protected function validateUserData(Request $request)
	{
		$rules = [
			'user_data.first_name' => 'required|string|max:255',
			'user_data.last_name' => 'required|string|max:255',
			'user_data.user_name' => 'nullable|string|max:255|unique:users,user_name,' . ($request->input('user_data.id') ?? 'NULL'),
			'user_data.password' => 'nullable|string|min:8|confirmed',
			'user_data.email' => 'nullable|email|max:255',
			'user_data.hire_date' => 'nullable|date',
			'user_data.termination_date' => 'nullable|date',
			'user_data.resign_date' => 'nullable|date',
			'user_data.confirmed_date' => 'nullable|date',
			'user_data.birth_date' => 'nullable|date',
		];

		$request->validate($rules);
	}

	protected function setUserAttributes(UserFactory $uf, $user_data, $udf_obj = null)
	{
		if (config('app.demo_mode', false) === false || $uf->isNew()) {
			if (isset($user_data['status'])) {
				$uf->setStatus($user_data['status']);
			}
			if (isset($user_data['user_name'])) {
				$uf->setUserName($user_data['user_name']);
			}
			if (isset($user_data['phone_id'])) {
				$uf->setPhoneId($user_data['phone_id']);
			}
			if (!empty($user_data['password']) && $user_data['password'] === $user_data['password2']) {
				$uf->setPassword($user_data['password']);
			} elseif (!empty($user_data['password'])) {
				$uf->Validator->isTrue('password', false, __('Passwords don\'t match'));
			}
			if (isset($user_data['phone_password'])) {
				$uf->setPhonePassword($user_data['phone_password']);
			}
		}

		if ($user_data['id'] != $this->currentUser->getId() && $this->permission->Check('user', 'edit_advanced')) {
			$this->setOptionalAttributes($uf, $user_data);
		} else {
			$this->setRequiredAttributes($uf, $user_data);
		}

		// Set additional attributes
		if (
			config('app.demo_mode', false) === false &&
			isset($user_data['permission_control_id']) &&
			$uf->getPermissionLevel() <= $this->permission->getLevel() &&
			($this->permission->Check('permission', 'edit') ||
				$this->permission->Check('permission', 'edit_own') ||
				$this->permission->Check('user', 'edit_permission_group'))
		) {
			$uf->setPermissionControl($user_data['permission_control_id']);
		} elseif ($udf_obj && $uf->isNew()) {
			$uf->setPermissionControl($udf_obj->getPermissionControl());
		}

		if (
			isset($user_data['pay_period_schedule_id']) &&
			($this->permission->Check('pay_period_schedule', 'edit') ||
				$this->permission->Check('user', 'edit_pay_period_schedule'))
		) {
			$uf->setPayPeriodSchedule($user_data['pay_period_schedule_id']);
		} elseif ($udf_obj && $uf->isNew()) {
			$uf->setPayPeriodSchedule($udf_obj->getPayPeriodSchedule());
		}

		if (
			isset($user_data['policy_group_id']) &&
			($this->permission->Check('policy_group', 'edit') ||
				$this->permission->Check('user', 'edit_policy_group'))
		) {
			$uf->setPolicyGroup($user_data['policy_group_id']);
		} elseif ($udf_obj && $uf->isNew()) {
			$uf->setPolicyGroup($udf_obj->getPolicyGroup());
		}

		if (
			isset($user_data['hierarchy_control']) &&
			($this->permission->Check('hierarchy', 'edit') ||
				$this->permission->Check('user', 'edit_hierarchy'))
		) {
			$uf->setHierarchyControl($user_data['hierarchy_control']);
		}

		if (isset($user_data['currency_id'])) {
			$uf->setCurrency($user_data['currency_id']);
		} elseif ($udf_obj && $uf->isNew()) {
			$uf->setCurrency($udf_obj->getCurrency());
		}

		if (isset($user_data['hire_date'])) {
			$uf->setHireDate(TTDate::parseDateTime($user_data['hire_date']));
		}
		if (isset($user_data['termination_date'])) {
			$uf->setTerminationDate(TTDate::parseDateTime($user_data['termination_date']));
		}
		if (isset($user_data['resign_date'])) {
			$uf->setResignDate(TTDate::parseDateTime($user_data['resign_date']));
		}
		if (isset($user_data['confirmed_date'])) {
			$uf->setConfiremedDate(TTDate::parseDateTime($user_data['confirmed_date']));
		}
		if (isset($user_data['employee_number_only'])) {
			$uf->setEmployeeNumber($user_data['branch_short_id'] . $user_data['employee_number_only']);
			$uf->setEmployeeNumberOnly($user_data['employee_number_only'], $user_data['default_branch_id']);
		}
		if (isset($user_data['punch_machine_user_id'])) {
			$uf->setPunchMachineUserID($user_data['punch_machine_user_id']);
		}
		if (isset($user_data['default_branch_id'])) {
			$uf->setDefaultBranch($user_data['default_branch_id']);
		}
		if (isset($user_data['default_department_id'])) {
			$uf->setDefaultDepartment($user_data['default_department_id']);
		}
		if (isset($user_data['group_id'])) {
			$uf->setGroup($user_data['group_id']);
		}
		if (isset($user_data['title_id'])) {
			$uf->setTitle($user_data['title_id']);
		}
		if (isset($user_data['job_skills'])) {
			$uf->setJobSkills($user_data['job_skills'], $user_data['job_skills']);
		}
		if (isset($user_data['ibutton_id'])) {
			$uf->setIButtonId($user_data['ibutton_id']);
		}
		if (isset($user_data['other_id1'])) {
			$uf->setOtherID1($user_data['other_id1']);
		}
		if (isset($user_data['other_id2'])) {
			$uf->setOtherID2($user_data['other_id2']);
		}
		if (isset($user_data['other_id3'])) {
			$uf->setOtherID3($user_data['other_id3']);
		}
		if (isset($user_data['other_id4'])) {
			$uf->setOtherID4($user_data['other_id4']);
		}
		if (isset($user_data['other_id5'])) {
			$uf->setOtherID5($user_data['other_id5']);
		}
		if (isset($user_data['note'])) {
			$uf->setNote($user_data['note']);
		}
		if (isset($user_data['hire_note'])) {
			$uf->setHireNote($user_data['hire_note']);
		}
		if (isset($user_data['termination_note'])) {
			$uf->setTerminationNote($user_data['termination_note']);
		}
		if (isset($user_data['immediate_contact_person'])) {
			$uf->setImmediateContactPerson($user_data['immediate_contact_person']);
		}
		if (isset($user_data['immediate_contact_no'])) {
			$uf->setImmediateContactNo($user_data['immediate_contact_no']);
		}
		if (isset($user_data['bond_period'])) {
			$uf->setBondPeriod($user_data['bond_period']);
		}
	}

	protected function setOptionalAttributes(UserFactory $uf, $user_data)
	{
		$uf->setFirstName($user_data['first_name']);
		if (isset($user_data['middle_name'])) {
			$uf->setMiddleName($user_data['middle_name']);
		}
		if (isset($user_data['full_name'])) {
			$uf->setFullNameField($user_data['full_name']);
		}
		if (isset($user_data['calling_name'])) {
			$uf->setCallingName($user_data['calling_name']);
		}
		if (isset($user_data['name_with_initials'])) {
			$uf->setNameWithInitials($user_data['name_with_initials']);
		}
		$uf->setLastName($user_data['last_name']);
		if (isset($user_data['second_last_name'])) {
			$uf->setSecondLastName($user_data['second_last_name']);
		}
		if (!empty($user_data['title_name'])) {
			$uf->setNameTitle($user_data['title_name']);
		}
		if (!empty($user_data['sex'])) {
			$uf->setSex($user_data['sex']);
		}
		if (!empty($user_data['religion'])) {
			$uf->setReligion($user_data['religion']);
		}
		if (!empty($user_data['marital'])) {
			$uf->setMarital($user_data['marital']);
		}
		if (isset($user_data['address1'])) {
			$uf->setAddress1($user_data['address1']);
		}
		if (isset($user_data['address2'])) {
			$uf->setAddress2($user_data['address2']);
		}
		if (isset($user_data['address3'])) {
			$uf->setAddress3($user_data['address3']);
		}
		if (isset($user_data['nic'])) {
			$uf->setNic($user_data['nic']);
		}
		if (isset($user_data['probation'])) {
			$uf->setProbation($user_data['probation']);
		}
		if (isset($user_data['basis_of_employment'])) {
			$uf->setBasisOfEmployment($user_data['basis_of_employment']);
		}
		if (isset($user_data['month'])) {
			$uf->setMonth($user_data['month']);
		}
		if (isset($user_data['epf_registration_no'])) {
			$uf->setEpfRegistrationNo($user_data['epf_registration_no']);
		}
		if (isset($user_data['epf_membership_no'])) {
			$uf->setEpfMembershipNo($user_data['epf_membership_no']);
		}
		if (isset($user_data['city'])) {
			$uf->setCity($user_data['city']);
		}
		if (isset($user_data['country'])) {
			$uf->setCountry($user_data['country']);
		}
		if (isset($user_data['province'])) {
			$uf->setProvince($user_data['province']);
		}
		if (isset($user_data['postal_code'])) {
			$uf->setPostalCode($user_data['postal_code']);
		}
		if (isset($user_data['work_phone'])) {
			$uf->setWorkPhone($user_data['work_phone']);
		}
		if (isset($user_data['work_phone_ext'])) {
			$uf->setWorkPhoneExt($user_data['work_phone_ext']);
		}
		if (isset($user_data['home_phone'])) {
			$uf->setHomePhone($user_data['home_phone']);
		}
		if (isset($user_data['mobile_phone'])) {
			$uf->setMobilePhone($user_data['mobile_phone']);
		}
		if (isset($user_data['fax_phone'])) {
			$uf->setFaxPhone($user_data['fax_phone']);
		}
		if (isset($user_data['home_email'])) {
			$uf->setHomeEmail($user_data['home_email']);
		}
		if (isset($user_data['work_email'])) {
			$uf->setWorkEmail($user_data['work_email']);
		}
		if (isset($user_data['office_mobile'])) {
			$uf->setOfficeMobile($user_data['office_mobile']);
		}
		if (isset($user_data['personal_email'])) {
			$uf->setPersonalEmail($user_data['personal_email']);
		}
		if (isset($user_data['sin'])) {
			$uf->setSIN($user_data['sin']);
		}
		$uf->setBirthDate(TTDate::getTimeStampFromSmarty('birth_', $user_data));
		$date = new DateTime();
		$date->setTimestamp($uf->getBirthDate());
		$date->modify('+60 years');
		$uf->setRetirementDate($date->getTimestamp());
		if (isset($user_data['retirement_date'])) {
			$uf->setRetirementDate($user_data['retirement_date']);
		}
	}

	protected function setRequiredAttributes(UserFactory $uf, $user_data)
	{
		$uf->setFirstName($user_data['first_name']);
		$uf->setMiddleName($user_data['middle_name'] ?? '');
		$uf->setFullNameField($user_data['full_name']);
		$uf->setCallingName($user_data['calling_name'] ?? '');
		$uf->setNameWithInitials($user_data['name_with_initials']);
		$uf->setLastName($user_data['last_name']);
		if (isset($user_data['second_last_name'])) {
			$uf->setSecondLastName($user_data['second_last_name']);
		}
		$uf->setSex($user_data['sex']);
		$uf->setMarital($user_data['marital']);
		$uf->setReligion($user_data['religion']);
		$uf->setAddress1($user_data['address1']);
		$uf->setAddress2($user_data['address2']);
		$uf->setAddress3($user_data['address3']);
		$uf->setNameTitle($user_data['title_name']);
		$uf->setNic($user_data['nic']);
		$uf->setProbation($user_data['probation'] ?? '');
		$uf->setEpfRegistrationNo($user_data['epf_registration_no']);
		$uf->setEpfMembershipNo($user_data['epf_membership_no']);
		$uf->setCity($user_data['city']);
		if (isset($user_data['country'])) {
			$uf->setCountry($user_data['country']);
		}
		if (isset($user_data['province'])) {
			$uf->setProvince($user_data['province']);
		}
		$uf->setPostalCode($user_data['postal_code']);
		$uf->setWorkPhone($user_data['work_phone']);
		$uf->setWorkPhoneExt($user_data['work_phone_ext']);
		$uf->setHomePhone($user_data['home_phone']);
		$uf->setMobilePhone($user_data['mobile_phone']);
		$uf->setFaxPhone($user_data['fax_phone']);
		$uf->setHomeEmail($user_data['home_email'] ?? '');
		$uf->setWorkEmail($user_data['work_email']);
		$uf->setOfficeMobile($user_data['office_mobile']);
		$uf->setPersonalEmail($user_data['personal_email']);
		$uf->setConfiremedDate($user_data['confirmed_date']);
		$uf->setResignDate($user_data['resign_date']);
		if (isset($user_data['sin'])) {
			$uf->setSIN($user_data['sin']);
		}
		$uf->setBirthDate(TTDate::getTimeStampFromSmarty('birth_', $user_data));
		$uf->setRetirementDate($user_data['retirement_date']);
	}

	public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'file_type' => 'required|string|in:user_image,user_file,user_id_copy,user_birth_certificate,user_gs_letter,user_police_report,user_nda,bond',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if (!$this->permission->Check('user', 'edit_advanced')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $file = $request->file('file');
        $fileType = $request->input('file_type');
        $userId = $request->input('user_id');

        $diskMap = [
            'user_image' => 'user_images',
            'user_file' => 'user_files',
            'user_id_copy' => 'user_id_copies',
            'user_birth_certificate' => 'user_birth_certificates',
            'user_gs_letter' => 'user_gs_letters',
            'user_police_report' => 'user_police_reports',
            'user_nda' => 'user_ndas',
            'bond' => 'bonds',
        ];

        $disk = $diskMap[$fileType] ?? 'user_files';
        $path = "user_{$userId}/{$fileType}/" . $file->getClientOriginalName();

        try {
            Storage::disk($disk)->put($path, file_get_contents($file));
            Log::info("File uploaded", [
                'disk' => $disk,
                'path' => $path,
                'user_id' => $this->currentUser->getId(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error uploading file", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to upload file'], 500);
        }
    }

    public function serveFile(Request $request, $disk, $path)
    {
        $validDisks = ['user_images', 'user_files', 'user_id_copies', 'user_birth_certificates', 'user_gs_letters', 'user_police_reports', 'user_ndas', 'bonds', 'templates', 'user_appointment_letters'];
        if (!in_array($disk, $validDisks) || !config("filesystems.disks.$disk")) {
            abort(404, 'File not found');
        }

        if (!$this->permission->Check('user', 'view') && !$this->permission->Check('user', 'edit_advanced')) {
            abort(403, 'Unauthorized');
        }

        $path = str_replace(['../', '..\\'], '', $path);
        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'File not found');
        }

        try {
            $file = Storage::disk($disk)->get($path);
            $mimeType = Storage::disk($disk)->mimeType($path);
            $fileName = basename($path);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            abort(500, 'Error serving file');
        }
    }

	protected function deleteUserFile($file_name, $user_id, $file_type)
	{
		$file_types = [
			'user_file' => 'user_files',
			'user_template' => 'user_templates',
			'user_id_copy' => 'user_id_copies',
			'user_birth_certificate' => 'user_birth_certificates',
			'user_gs_letter' => 'user_gs_letters',
			'user_police_report' => 'user_police_reports',
			'user_nda' => 'user_ndas',
			'bond' => 'bonds',
		];

		if (isset($file_types[$file_type])) {
			$path = "user_{$user_id}/{$file_type}/{$file_name}";
			Storage::disk($file_types[$file_type])->delete($path);
		}
	}

	/*
	protected function generateAppointmentLetter($user_data, $user_id)
	{
		$phpWord = new PhpWord();
		$templatePath = storage_path('app/templates/appointment_letter_template.docx');
		$outputPath = storage_path("app/user_appointment_letters/{$user_id}/outputfile.docx");

		if (!file_exists($templatePath)) {
			\Log::error("Appointment letter template not found at: {$templatePath}");
			return;
		}

		$document = $phpWord->loadTemplate($templatePath);
		$document->setValue('Value0', $user_data['first_name'] ?? '');
		$document->setValue('Value1', $user_data['last_name'] ?? '');
		$document->setValue('Value2', $user_data['address1'] ?? '');
		$document->setValue('Value3', $user_data['address2'] ?? '');
		$document->setValue('Value4', $user_data['hire_date'] ? gmdate('M d Y', $user_data['hire_date']) : '');
		$document->setValue('Value5', $user_data['title'] ?? '');
		$document->setValue('Value6', $user_data['wage'] ?? '');
		$document->setValue('Value7', $this->currentCompany->getName() ?? '');
		$document->setValue('Value8', $user_data['probation'] ?? '');

		// Ensure directory exists
		$outputDir = dirname($outputPath);
		if (!file_exists($outputDir)) {
			mkdir($outputDir, 0755, true);
		}

		$document->save($outputPath);
	}
	*/
}
