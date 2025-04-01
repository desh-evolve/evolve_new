<?php

namespace App\Http\Controllers\payperiod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\PayPeriod\PayPeriodScheduleFactory;
use App\Models\PayPeriod\PayPeriodScheduleListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditPayPeriodSchedule extends Controller
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
        if ( !$permission->Check('pay_period_schedule','enabled')
				OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = 'Edit Pay Period Schedule';
		$current_user_prefs = $this->userPrefs;
		$current_company = $this->currentCompany;

		if ( isset($pay_period_schedule_data) ) {
			if ( isset($pay_period_schedule_data['anchor_date']) ) {
				$pay_period_schedule_data['anchor_date'] = TTDate::parseDateTime( $pay_period_schedule_data['anchor_date'] );
			}
			if ( isset($pay_period_schedule_data['day_start_time'] ) ) {
				$pay_period_schedule_data['day_start_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['day_start_time'] );
			}
			if ( isset($pay_period_schedule_data['new_day_trigger_time']) ) {
				$pay_period_schedule_data['new_day_trigger_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['new_day_trigger_time'] );
			}
			if ( isset($pay_period_schedule_data['maximum_shift_time']) ) {
				$pay_period_schedule_data['maximum_shift_time'] = TTDate::parseTimeUnit( $pay_period_schedule_data['maximum_shift_time'] );
			}
		}

		//var_dump($pay_period_schedule_data);
		$ppsf = new PayPeriodScheduleFactory(); 

		if ( isset($id) ) {

			$ppslf = new PayPeriodScheduleListFactory();
			$ppslf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($ppslf->rs as $pay_period_schedule) {
				$ppslf->data = (array)$pay_period_schedule;
				$pay_period_schedule = $ppslf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$pay_period_schedule_data = array(
					'id' => $pay_period_schedule->getId(),
					'company_id' => $pay_period_schedule->getCompany(),
					'name' => $pay_period_schedule->getName(),
					'description' => $pay_period_schedule->getDescription(),
					'type' => $pay_period_schedule->getType(),
					'start_week_day_id' => $pay_period_schedule->getStartWeekDay(),
					'start_day_of_week' => $pay_period_schedule->getStartDayOfWeek(),
					'transaction_date' => $pay_period_schedule->getTransactionDate(),

					'primary_day_of_month' => $pay_period_schedule->getPrimaryDayOfMonth(),
					'secondary_day_of_month' => $pay_period_schedule->getSecondaryDayOfMonth(),

					'primary_transaction_day_of_month' => $pay_period_schedule->getPrimaryTransactionDayOfMonth(),
					'secondary_transaction_day_of_month' => $pay_period_schedule->getSecondaryTransactionDayOfMonth(),

					'transaction_date_bd' => $pay_period_schedule->getTransactionDateBusinessDay(),

					'anchor_date' => $pay_period_schedule->getAnchorDate(),

					'annual_pay_periods' => $pay_period_schedule->getAnnualPayPeriods(),

					'day_start_time' => Factory::convertToHoursAndMinutes($pay_period_schedule->getDayStartTime()),
					'time_zone' => $pay_period_schedule->getTimeZone(),
					'new_day_trigger_time' => Factory::convertToHoursAndMinutes($pay_period_schedule->getNewDayTriggerTime()),
					'maximum_shift_time' => Factory::convertToHoursAndMinutes($pay_period_schedule->getMaximumShiftTime()),
					'shift_assigned_day_id' => $pay_period_schedule->getShiftAssignedDay(),

					'timesheet_verify_type_id' => $pay_period_schedule->getTimeSheetVerifyType(),
					'timesheet_verify_before_end_date' => $pay_period_schedule->getTimeSheetVerifyBeforeEndDate(),
					'timesheet_verify_before_transaction_date' => $pay_period_schedule->getTimeSheetVerifyBeforeTransactionDate(),
					'timesheet_verify_notice_before_transaction_date' => $pay_period_schedule->getTimeSheetVerifyNoticeBeforeTransactionDate(),
					'timesheet_verify_notice_email' => $pay_period_schedule->getTimeSheetVerifyNoticeEmail(),

					'user_ids' => $pay_period_schedule->getUser(),

					'deleted' => $pay_period_schedule->getDeleted(),
					'created_date' => $pay_period_schedule->getCreatedDate(),
					'created_by' => $pay_period_schedule->getCreatedBy(),
					'updated_date' => $pay_period_schedule->getUpdatedDate(),
					'updated_by' => $pay_period_schedule->getUpdatedBy(),
					'deleted_date' => $pay_period_schedule->getDeletedDate(),
					'deleted_by' => $pay_period_schedule->getDeletedBy()
				);
			}
		} else {

			$pay_period_schedule_data = array(
				'anchor_date' => TTDate::getBeginMonthEpoch( time() ),
				'day_start_time' => Factory::convertToHoursAndMinutes(0),
				'new_day_trigger_time' => Factory::convertToHoursAndMinutes((3600*4)),
				'maximum_shift_time' => Factory::convertToHoursAndMinutes((3600*16)),
				'time_zone' => $current_user_prefs->getTimeZone(),
				'type' => 20,
				'timesheet_verify_type_id' => 10, //Disabled
				'timesheet_verify_before_end_date' => 0,
				'timesheet_verify_before_transaction_date' => 0,
				'annual_pay_periods' => 0
			);
		}
		//Select box options;
		$pay_period_schedule_data['type_options'] = $ppsf->getOptions('type');
		$pay_period_schedule_data['start_week_day_options'] = $ppsf->getOptions('start_week_day');
		$pay_period_schedule_data['shift_assigned_day_options'] = $ppsf->getOptions('shift_assigned_day');
		$pay_period_schedule_data['timesheet_verify_type_options'] = $ppsf->getOptions('timesheet_verify_type');
		$pay_period_schedule_data['time_zone_options'] = $ppsf->getTimeZoneOptions();
		$pay_period_schedule_data['transaction_date_bd_options'] = $ppsf->getOptions('transaction_date_business_day');
		$pay_period_schedule_data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$pay_period_schedule_data['transaction_date_options'] = Misc::prependArray( array( 0 => '0' ), TTDate::getDayOfMonthArray() );
		$pay_period_schedule_data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$pay_period_schedule_data['day_of_month_options'][-1] = _('- Last Day Of Month -');

		$pay_period_schedule_data['user_options'] = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);

		$viewData['pay_period_schedule_data'] = $pay_period_schedule_data;
		$viewData['ppsf'] = $ppsf;

        return view('payperiod/EditPayPeriodSchedule', $viewData);

    }

	public function submit(Request $request){
		$current_company = $this->currentCompany;
		
		$ppsf = new PayPeriodScheduleFactory();
		$pay_period_schedule_data = $request->pay_period_schedule_data;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ppsf->StartTransaction();

		$ppsf->setId($pay_period_schedule_data['id']);
		$ppsf->setCompany( $current_company->getId() );
		$ppsf->setName($pay_period_schedule_data['name']);
		$ppsf->setDescription($pay_period_schedule_data['description']);
		$ppsf->setType($pay_period_schedule_data['type']);
		$ppsf->setStartWeekDay($pay_period_schedule_data['start_week_day_id']);

		if ( $pay_period_schedule_data['type'] == 5 ) {
			$ppsf->setAnnualPayPeriods($pay_period_schedule_data['annual_pay_periods']);
		}

		if ( $pay_period_schedule_data['type'] == 10 OR $pay_period_schedule_data['type'] == 20 ) {
			$ppsf->setStartDayOfWeek($pay_period_schedule_data['start_day_of_week']);
			$ppsf->setTransactionDate($pay_period_schedule_data['transaction_date']);
		} elseif (  $pay_period_schedule_data['type'] == 30 ) {
			$ppsf->setPrimaryDayOfMonth($pay_period_schedule_data['primary_day_of_month']);
			$ppsf->setSecondaryDayOfMonth($pay_period_schedule_data['secondary_day_of_month']);
			$ppsf->setPrimaryTransactionDayOfMonth($pay_period_schedule_data['primary_transaction_day_of_month']);
			$ppsf->setSecondaryTransactionDayOfMonth($pay_period_schedule_data['secondary_transaction_day_of_month']);
		} elseif ( $pay_period_schedule_data['type'] == 50 ) {
			$ppsf->setPrimaryDayOfMonth($pay_period_schedule_data['primary_day_of_month']);
			$ppsf->setPrimaryTransactionDayOfMonth($pay_period_schedule_data['primary_transaction_day_of_month']);
		}

		if ( isset($pay_period_schedule_data['anchor_date']) ) {
			$ppsf->setAnchorDate( $pay_period_schedule_data['anchor_date'] );
		}

		$ppsf->setTransactionDateBusinessDay( $pay_period_schedule_data['transaction_date_bd'] );

		if ( isset($pay_period_schedule_data['day_start_time']) ) {
			$ppsf->setDayStartTime( Factory::convertToSeconds($pay_period_schedule_data['day_start_time']) );
		} else {
			$ppsf->setDayStartTime(	0 );
		}

		$ppsf->setTimeZone( $pay_period_schedule_data['time_zone'] );
		$ppsf->setNewDayTriggerTime( Factory::convertToSeconds($pay_period_schedule_data['new_day_trigger_time']) );
		$ppsf->setMaximumShiftTime( Factory::convertToSeconds($pay_period_schedule_data['maximum_shift_time']) );
		$ppsf->setShiftAssignedDay( $pay_period_schedule_data['shift_assigned_day_id'] );

		$ppsf->setTimeSheetVerifyType( $pay_period_schedule_data['timesheet_verify_type_id'] );
		$ppsf->setTimeSheetVerifyBeforeEndDate( $pay_period_schedule_data['timesheet_verify_before_end_date'] );
		$ppsf->setTimeSheetVerifyBeforeTransactionDate( $pay_period_schedule_data['timesheet_verify_before_transaction_date'] );

		if ( isset($pay_period_schedule_data['user_ids']) ){
			$ppsf->setUser( $pay_period_schedule_data['user_ids'] );
		}

		if ( $ppsf->isValid() ) {
			//Pay Period schedule has to be saved before users can be assigned to it, so do it this way.
			$ppsf->Save(FALSE);
			$ppsf->setEnableInitialPayPeriods(FALSE);

			if ( isset($pay_period_schedule_data['user_ids']) ){
				$ppsf->setUser( $pay_period_schedule_data['user_ids'] );
			} else {
				$ppsf->setUser( array() );
			}

			if ( $ppsf->isValid() ) {
				$ppsf->Save(TRUE);
				$ppsf->CommitTransaction();
				return redirect(URLBuilder::getURL( NULL, '/payroll/pay_period_schedules'));
			}
		}

		$ppsf->FailTransaction();
	}
}

?>