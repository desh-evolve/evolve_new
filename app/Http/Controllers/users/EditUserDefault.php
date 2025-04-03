<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyDeductionListFactory;
use App\Models\Company\CompanyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\PermissionControlListFactory;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;
use App\Models\Core\URLBuilder;
use App\Models\Department\DepartmentListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Users\UserDefaultFactory;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserFactory;
use App\Models\Users\UserPreferenceFactory;
use App\Models\Users\UserTitleListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserDefault extends Controller
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
        //         OR !( $permission->Check('user','edit') ) ) {

        //     $permission->Redirect( FALSE ); //Redirect

        // }


        $current_company = $this->currentCompany;
        $viewData['title'] = 'Add New Hire Defaults';

        $uf = new UserFactory();
        $udlf = new UserDefaultListFactory();
        $udf = new UserDefaultFactory();
        $user_data = [];

        if ( isset($id) ) {
            $udlf->getByCompanyId($current_company->getId() );

			foreach ($udlf->rs as $user) {
				$udlf->data = (array)$user;
				$user = $udlf;

                $user_title = NULL;
				if ( is_object( $user->getTitleObject() )  ) {
					$user_title = $user->getTitleObject()->getName();
				}
				Debug::Text('Title: '. $user_title , __FILE__, __LINE__, __METHOD__,10);

				$user_data = array(
                                        'id' => $user->getId(),
                                        'company' => $user->getCompany(),
                                        'title_id' => $user->getTitle(),
                                        'title' => $user_title,
                                        'employee_number' => $user->getEmployeeNumber(),
                                        'city' => $user->getCity(),
                                        'province' => $user->getProvince(),
                                        'country' => $user->getCountry(),
                                        'work_phone' => $user->getWorkPhone(),
                                        'work_phone_ext' => $user->getWorkPhoneExt(),
                                        'work_email' => $user->getWorkEmail(),
                                        'hire_date' => $user->getHireDate(),
                                        'default_branch_id' => $user->getDefaultBranch(),
                                        'default_department_id' => $user->getDefaultDepartment(),
                                        'currency_id' => $user->getCurrency(),
                                        'permission_control_id' => $user->getPermissionControl(),
                                        'pay_period_schedule_id' => $user->getPayPeriodSchedule(),
                                        'policy_group_id' => $user->getPolicyGroup(),

                                        'company_deduction_ids' => $user->getCompanyDeduction(),

                                        'language' => $user->getLanguage(),
                                        'date_format' => $user->getDateFormat(),
                                        'other_date_format' => $user->getDateFormat(),
                                        'time_format' => $user->getTimeFormat(),
                                        'time_zone' => $user->getTimeZone(),
                                        'time_unit_format' => $user->getTimeUnitFormat(),
                                        'items_per_page' => $user->getItemsPerPage(),
                                        'start_week_day' => $user->getStartWeekDay(),
                                        'enable_email_notification_exception' => $user->getEnableEmailNotificationException(),
                                        'enable_email_notification_message' => $user->getEnableEmailNotificationMessage(),
                                        'enable_email_notification_home' => $user->getEnableEmailNotificationHome(),


                                        'created_date' => $user->getCreatedDate(),
                                        'created_by' => $user->getCreatedBy(),
                                        'updated_date' => $user->getUpdatedDate(),
                                        'updated_by' => $user->getUpdatedBy(),
                                        'deleted_date' => $user->getDeletedDate(),
                                        'deleted_by' => $user->getDeletedBy()
                                );
			}

		}

        if (empty($user_data)) {
            $user_data = [
                'items_per_page' => 10,
                'time_zone' => 'GMT',
                'country' => 'CA',
                'language' => 'en'
            ];
        }

        $upf = new UserPreferenceFactory();

        //Select box options;
		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		$culf = new CurrencyListFactory();
        $culf->getByCompanyId( $current_company->getId() );
		$currency_options = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		//Select box options;
		$user_data['branch_options'] = $branch_options;
		$user_data['department_options'] = $department_options;
      	$user_data['currency_options'] = $currency_options;

		$cf = new CompanyFactory();
		$user_data['country_options'] = $cf->getOptions('country');
		$user_data['province_options'] = $cf->getOptions('province');

		$utlf = new UserTitleListFactory();
		$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );
		$user_data['title_options'] = $user_titles;

		//Get Permission Groups
		$pclf = new PermissionControlListFactory();
		$pclf->getByCompanyId( $current_company->getId() );
		$user_data['permission_control_options'] = $pclf->getArrayByListFactory( $pclf, FALSE );

		//Get pay period schedules
		$ppslf = new PayPeriodScheduleListFactory();
		$pay_period_schedules = $ppslf->getByCompanyIDArray( $current_company->getId() );
		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

		$pglf = new PolicyGroupListFactory();
		$policy_groups = $pglf->getByCompanyIDArray( $current_company->getId() );
		$user_data['policy_group_options'] = $policy_groups;

		$user_data['company'] = $current_company->getName();

		// $user_data['language_options'] = TTi18n::getLanguageArray();
        $user_data['language_options'] = [ 'en' => 'English' ]; // hardcode the language options you can edit
		$user_data['date_format_options'] = $upf->getOptions('date_format');
		$user_data['other_date_format_options'] = $upf->getOptions('other_date_format');
		$user_data['time_format_options'] = $upf->getOptions('time_format');
		$user_data['time_unit_format_options'] = $upf->getOptions('time_unit_format');
		$user_data['timesheet_view_options'] = $upf->getOptions('timesheet_view');
		$user_data['start_week_day_options'] = $upf->getOptions('start_week_day');

		$timezone_options = Misc::prependArray( array(-1 => '---'), $upf->getOptions('time_zone') );
		$user_data['time_zone_options'] = $timezone_options;

        //Get all Company Deductions for drop down box.
        $cdlf = new CompanyDeductionListFactory();
        $user_data['company_deduction_options'] = $cdlf->getByCompanyIdAndStatusIdArray( $current_company->getId(), 10, FALSE);

        $viewData['user_data'] = $user_data;
		$viewData['udf'] = $udf;

        return view('users/EditUserDefault', $viewData);
    }


    public function save(Request $request)
    {
       $udf = new UserDefaultFactory();
       $user_data = $request->all();
    //    dd($request->all());

       $current_company = $this->currentCompany;
       $permission = $this->permission;

       Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

       if ( isset($user_data['id']) AND $user_data['id'] != '' ) {
            $udf->setId( $user_data['id'] );
        }
        $udf->setCompany( $current_company->getId() );
        $udf->setTitle($user_data['title_id']);
        $udf->setCity($user_data['city']);
        $udf->setCountry($user_data['country']);
        $udf->setProvince($user_data['province']);
        $udf->setWorkPhone($user_data['work_phone']);
        $udf->setWorkPhoneExt($user_data['work_phone_ext']);
        $udf->setWorkEmail($user_data['work_email']);
        $udf->setPayPeriodSchedule( $user_data['pay_period_schedule_id'] );
        $udf->setPolicyGroup( $user_data['policy_group_id'] );
        $udf->setCurrency( $user_data['currency_id'] );

        // if ( $permission->Check('permission','edit') AND isset($user_data['permission_control_id']) ) {
        //     $udf->setPermissionControl( $user_data['permission_control_id'] );
        // }
        // dd($permission->Check('permission', 'edit'), $user_data['permission_control_id']);

        if (isset($user_data['permission_control_id'])) {
            $udf->setPermissionControl($user_data['permission_control_id']);
        }

        // Convert hire_date using TTDate::parseDateTime()
        if (isset($user_data['hire_date']) && $user_data['hire_date'] != '') {
            $user_data['hire_date'] = TTDate::parseDateTime($user_data['hire_date']);
        }
        $udf->setHireDate( $user_data['hire_date'] );

        $udf->setEmployeeNumber( $user_data['employee_number'] );
        $udf->setDefaultBranch( $user_data['default_branch_id'] );
        $udf->setDefaultDepartment( $user_data['default_department_id'] );
        $udf->setLanguage( $user_data['language'] );
        if ($user_data['language']=== 'en'){
        $udf->setDateFormat( $user_data['date_format'] );
        }else{
            $udf->setDateFormat( $user_data['other_date_format'] );
        }
        $udf->setTimeFormat( $user_data['time_format']);
        $udf->setTimeUnitFormat( $user_data['time_unit_format'] );
        $udf->setTimeZone( $user_data['time_zone'] );
        $udf->setItemsPerPage( $user_data['items_per_page'] );
        $udf->setStartWeekDay( $user_data['start_week_day'] );

        if ( isset($user_data['enable_email_notification_exception']) ) {
            $udf->setEnableEmailNotificationException( TRUE );
        } else {
            $udf->setEnableEmailNotificationException( FALSE );
        }

        if ( isset($user_data['enable_email_notification_message']) ) {
            $udf->setEnableEmailNotificationMessage( TRUE );
        } else {
            $udf->setEnableEmailNotificationMessage( FALSE );
        }

        if ( isset($user_data['enable_email_notification_home']) ) {
            $udf->setEnableEmailNotificationHome( TRUE );
        } else {
            $udf->setEnableEmailNotificationHome( FALSE );
        }

        if ( $udf->isValid() ) {
			$udf->Save(FALSE);

			if ( isset($user_data['company_deduction_ids'] ) ) {
					$udf->setCompanyDeduction( $user_data['company_deduction_ids'] );
			} else {
					$udf->setCompanyDeduction( array() );
			}

			if ( $udf->isValid() ) {
				$udf->Save(FALSE);

                return redirect()->to(URLBuilder::getURL(array('id' => $user_data['id'], 'data_saved' => TRUE), '/new_hire_defaults/add'))->with('success', 'Form Data saved successfully.');
			}
		}

        // If validation fails, return back with errors
        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
    }

}
