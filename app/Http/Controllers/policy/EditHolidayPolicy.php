<?php

namespace App\Http\Controllers\policy;

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
use App\Models\Holiday\RecurringHolidayListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\HolidayPolicyFactory;
use App\Models\Policy\HolidayPolicyListFactory;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditHolidayPolicy extends Controller
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
        if ( !$permission->Check('holiday_policy','enabled')
				OR !( $permission->Check('holiday_policy','edit') OR $permission->Check('holiday_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Holiday Policy' : 'Add Holiday Policy';

		$current_company = $this->currentCompany;

		if ( isset($data['minimum_time'] ) ) {
			$data['minimum_time'] = TTDate::parseTimeUnit($data['minimum_time']);
		}
		if ( isset($data['maximum_time'] ) ) {
			$data['maximum_time'] = TTDate::parseTimeUnit($data['maximum_time']);
		}
		
		
		$hpf = new HolidayPolicyFactory();	

		if ( isset($id) ) {

			$hplf = new HolidayPolicyListFactory();
			$hplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($hplf->rs as $hp_obj) {
				$hplf->data = (array)$hp_obj;
				$hp_obj = $hplf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $hp_obj->getId(),
					'name' => $hp_obj->getName(),
					'type_id' => $hp_obj->getType(),
					'default_schedule_status_id' => $hp_obj->getDefaultScheduleStatus(),
					'minimum_employed_days' => $hp_obj->getMinimumEmployedDays(),

					'minimum_worked_period_days' => $hp_obj->getMinimumWorkedPeriodDays(),
					'minimum_worked_days' => $hp_obj->getMinimumWorkedDays(),
					'worked_scheduled_days' => $hp_obj->getWorkedScheduledDays(),

					'minimum_worked_after_period_days' => $hp_obj->getMinimumWorkedAfterPeriodDays(),
					'minimum_worked_after_days' => $hp_obj->getMinimumWorkedAfterDays(),
					'worked_after_scheduled_days' => $hp_obj->getWorkedAfterScheduledDays(),

					'average_time_days' => $hp_obj->getAverageTimeDays(),
					'average_days' => $hp_obj->getAverageDays(),
					'average_time_worked_days' => $hp_obj->getAverageTimeWorkedDays(),
					'force_over_time_policy' => $hp_obj->getForceOverTimePolicy(),
					'include_over_time' => $hp_obj->getIncludeOverTime(),
					'include_paid_absence_time' => $hp_obj->getIncludePaidAbsenceTime(),
					'minimum_time' => Factory::convertToHoursAndMinutes($hp_obj->getMinimumTime()),
					'maximum_time' => Factory::convertToHoursAndMinutes($hp_obj->getMaximumTime()),
					//'time' => $hp_obj->getTime(),

					'round_interval_policy_id' => $hp_obj->getRoundIntervalPolicyID(),
					'absence_policy_id' => $hp_obj->getAbsencePolicyID(),

					'recurring_holiday_ids' => $hp_obj->getRecurringHoliday(),

					'created_date' => $hp_obj->getCreatedDate(),
					'created_by' => $hp_obj->getCreatedBy(),
					'updated_date' => $hp_obj->getUpdatedDate(),
					'updated_by' => $hp_obj->getUpdatedBy(),
					'deleted_date' => $hp_obj->getDeletedDate(),
					'deleted_by' => $hp_obj->getDeletedBy()
				);
			}
		} else {
			//Defaults
			$data = array(
				'type_id' => 10,
				'default_schedule_status_id' => 20,
				'minimum_employed_days' => 30,
				'minimum_worked_period_days' => 30,
				'minimum_worked_days' => 15,
				'minimum_worked_after_period_days' => 0,
				'minimum_worked_after_days' => 0,
				'average_time_days' => 30,
				'average_days' => 30,
				'force_over_time_policy' => FALSE,
				'include_over_time' => FALSE,
				'include_paid_absence_time' => TRUE,
				'minimum_time' => Factory::convertToHoursAndMinutes(0),
				'maximum_time' => Factory::convertToHoursAndMinutes(0)
			);
		}

		$aplf = new AbsencePolicyListFactory(); 
		$absence_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$riplf = new RoundIntervalPolicyListFactory();
		$round_interval_options = $riplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$rhlf = new RecurringHolidayListFactory();
		$recurring_holiday_options = $rhlf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$sf = new ScheduleFactory();

		//Select box options;
		$data['type_options'] = $hpf->getOptions('type');
		$data['schedule_status_options'] = $sf->getOptions('status');
		$data['scheduled_day_options'] = $hpf->getOptions('scheduled_day');
		$data['absence_options'] = $absence_options;
		$data['round_interval_options'] = $round_interval_options;
		$data['recurring_holiday_options'] = $recurring_holiday_options;
		
		$viewData['data'] = $data;
		$viewData['hpf'] = $hpf;

        return view('policy/EditHolidayPolicy', $viewData);

    }

	public function submit(Request $request){
		$hpf = new HolidayPolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;
		
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$hpf->StartTransaction();

		$hpf->setId( $data['id'] );
		$hpf->setCompany( $current_company->getId() );
		$hpf->setName( $data['name'] );
		$hpf->setType( $data['type_id'] );

		$hpf->setDefaultScheduleStatus( $data['default_schedule_status_id'] );
		$hpf->setMinimumEmployedDays( $data['minimum_employed_days'] );

		$hpf->setMinimumWorkedPeriodDays( $data['minimum_worked_period_days'] );
		$hpf->setMinimumWorkedDays( $data['minimum_worked_days'] );
		$hpf->setWorkedScheduledDays( $data['worked_scheduled_days'] );

		$hpf->setMinimumWorkedAfterPeriodDays( $data['minimum_worked_after_period_days'] );
		$hpf->setMinimumWorkedAfterDays( $data['minimum_worked_after_days'] );
		$hpf->setWorkedAfterScheduledDays( $data['worked_after_scheduled_days'] );

		$hpf->setAverageTimeDays( $data['average_time_days'] );

		if ( isset($data['average_days']) ) {
			$hpf->setAverageDays( $data['average_days'] );
		} else {
			$hpf->setAverageDays( 0 );
		}

		if ( isset($data['average_time_worked_days']) ) {
			$hpf->setAverageTimeWorkedDays( TRUE );
		} else {
			$hpf->setAverageTimeWorkedDays( FALSE );
		}
		if ( isset($data['include_over_time']) ) {
			$hpf->setIncludeOverTime( TRUE );
		} else {
			$hpf->setIncludeOverTime( FALSE );
		}
		if ( isset($data['include_paid_absence_time']) ) {
			$hpf->setIncludePaidAbsenceTime( TRUE );
		} else {
			$hpf->setIncludePaidAbsenceTime( FALSE );
		}
		if ( isset($data['force_over_time_policy']) ) {
			$hpf->setForceOverTimePolicy( TRUE );
		} else {
			$hpf->setForceOverTimePolicy( FALSE );
		}

		$hpf->setMinimumTime( Factory::convertToSeconds($data['minimum_time']) );
		$hpf->setMaximumTime( Factory::convertToSeconds($data['maximum_time']) );
		$hpf->setAbsencePolicyID( $data['absence_policy_id'] );
		$hpf->setRoundIntervalPolicyID( $data['round_interval_policy_id'] );

		if ( $hpf->isValid() ) {
			$hpf->Save(FALSE);

			$hpf->setRecurringHoliday( $data['recurring_holiday_ids'] );

			if ( $hpf->isValid() ) {
				$hpf->Save();
				$hpf->CommitTransaction();
				return redirect(URLBuilder::getURL( NULL, '/policy/holiday_policies'));
			}
		}

		$hpf->FailTransaction();
	}
}


?>