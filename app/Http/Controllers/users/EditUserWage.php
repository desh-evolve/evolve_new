<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Company\WageGroupListFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayPeriod\PayPeriodListFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserDeductionListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserWageFactory;
use App\Models\Users\UserWageListFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditUserWage extends Controller
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

        // if ( !$permission->Check('wage','enabled')
        //         OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
        //     $permission->Redirect( FALSE ); //Redirect
        // }


    }


    public function index($id = null)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;
        $viewData['title'] = 'Employee Wage';

        $uwf = new UserWageFactory();
        $ulf = new UserListFactory();
        $tmp_effective_date = null;
        $user_id = request()->get('user_id') ?? $wage_data['user_id'] ?? null;

        $hlf = new HierarchyListFactory();
        $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );


        if ( isset($id) ) {

			$uwlf = new UserWageListFactory();
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

            $wage_data = [];

			foreach ($uwlf->rs as $wage) {
                $uwlf->data = (array)$wage;
                $wage = $uwlf;

				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();

                $budgetary_allowance = 0;
                $udlf = new UserDeductionListFactory();
                $udlf->getByUserIdAndCompanyDeductionId($wage->getUser(), 3);

                if($udlf->getRecordCount()>0){
                    foreach ($udlf->rs as $udlf_obj){
                        $udlf->data = (array)$udlf_obj;
                        $udlf_obj = $udlf;

                        $budgetary_allowance = $udlf_obj->getUserValue1();
                    }
                }

				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$wage_data = array(
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
											'wage_group_id' => $wage->getWageGroup(),
											'type' => $wage->getType(),
											'wage' => Misc::removeTrailingZeros( $wage->getWage() ),
                                            'budgetary_allowance' => Misc::MoneyFormat( $budgetary_allowance , FALSE),
											'hourly_rate' => Misc::removeTrailingZeros( $wage->getHourlyRate() ),
											'weekly_time' => $wage->getWeeklyTime(),
											'effective_date' => $wage->getEffectiveDate(),
											'labor_burden_percent' => (float)$wage->getLaborBurdenPercent(),
											'note' => $wage->getNote(),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);

						$tmp_effective_date = TTDate::getDate('DATE', $wage->getEffectiveDate() );
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {

            $budgetary_allowance = 0;
            $udlf = new UserDeductionListFactory();
            $udlf->getByUserIdAndCompanyDeductionId($user_id, 3);

            if($udlf->getRecordCount()>0){
                foreach ($udlf->rs as $udlf_obj){
                    $udlf->data = (array)$udlf_obj;
                    $udlf_obj = $udlf;

                    $budgetary_allowance = $udlf_obj->getUserValue1();
                }
            }

            $wage_data = array( 'effective_date' => TTDate::getTime(), 'labor_burden_percent' => 0 ,'budgetary_allowance' => Misc::MoneyFormat( $budgetary_allowance , FALSE));

		}
		//Select box options;
		$wage_data['type_options'] = $uwf->getOptions('type');

		$wglf = new WageGroupListFactory();
		$wage_data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$crlf = new CurrencyListFactory();
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

        $ulf = new UserListFactory();
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();

		if ( is_object( $user_data->getCurrencyObject() ) ) {
			$wage_data['currency_symbol'] = $user_data->getCurrencyObject()->getSymbol();
			$wage_data['iso_code'] = $user_data->getCurrencyObject()->getISOCode();
		}

		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = _('(Appointment Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => _('(Choose Date)')), $pay_period_boundary_dates);

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getByUserId( $user_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pay_period_schedule_id = $ppslf->getCurrent()->getId();
			$pay_period_schedule_name = $ppslf->getCurrent()->getName();
			Debug::Text('Pay Period Schedule ID: '. $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = new PayPeriodListFactory();
			$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id, 10, NULL, NULL, array('transaction_date' => 'desc') );
			$pay_period_boundary_dates = NULL;
            $pay_period_dates = NULL;

            foreach($pplf->rs as $pay_period_obj) {
                $pplf->data = (array)$pay_period_obj;
                $pay_period_obj = $pplf;

				// $pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getEndDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getEndDate() );
				if ( !isset($pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )])) {
					$pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getStartDate() );
				}
			}
		} else {

            $viewData['pay_period_schedule'] = false;
			$uwf->Validator->isTrue(		'employee',
											FALSE,
											_('Employee is not currently assigned to a pay period schedule.').' <a href="'.URLBuilder::getURL( NULL, '../payperiod/PayPeriodScheduleList.php').'">'. _('Click here</a> to assign') );
		}


        $viewData['user_data'] = $user_data;
        $viewData['currency_options'] = $currency_options;
        $viewData['wage_data'] = $wage_data;
        $viewData['tmp_effective_date'] = $tmp_effective_date;
        $viewData['pay_period_boundary_date_options'] = $pay_period_boundary_dates;
        $viewData['uwf'] = $uwf;

        // dd($viewData);

        return view('users.editUserWage', $viewData);

    }


    public function save(Request $request)
    {
        $current_company = $this->currentCompany;
        $current_user = $this->currentUser;
        $permission = $this->permission;

        $user_id = $request->input('user_id');
        $ulf = new UserListFactory();
        $uwf = new UserWageFactory();
        $wage_data = $request->all();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ulf->getByIdAndCompanyId($user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

            $hlf = new HierarchyListFactory();
            $permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );


			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

            if ( isset($wage_data) ) {
                if ( $wage_data['effective_date'] != '' ) {
                    $wage_data['effective_date'] = TTDate::parseDateTime($wage_data['effective_date']);
                }
            }

            if ( $permission->Check('wage','edit')
                OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
                OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

                $uwf->setId($wage_data['id']);
                $uwf->setUser($user_id);
                $uwf->setWageGroup($wage_data['wage_group_id']);
                $uwf->setType($wage_data['type']);
                $uwf->setWage($wage_data['wage']);
                $uwf->setHourlyRate($wage_data['hourly_rate']);
                $uwf->setBudgetoryAllowance($wage_data['budgetary_allowance']);
                $uwf->setWeeklyTime( TTDate::parseTimeUnit( $wage_data['weekly_time'] ) );
                $uwf->setEffectiveDate( $wage_data['effective_date'] );
                $uwf->setLaborBurdenPercent( 0 );
                $uwf->setNote( $wage_data['note'] );

                if ( $uwf->isValid() ) {
                    $uwf->Save();

                    return redirect()->to(URLBuilder::getURL(array('user_id' => $user_id) , '/user/wage'))->with('success', 'Employee Wage saved successfully.');

                }
            }else {
                // If validation fails, return back with errors
                return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
            }
        }
    }


}


