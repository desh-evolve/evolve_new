<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Holiday\RecurringHolidayFactory;
use App\Models\Holiday\RecurringHolidayListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditRecurringHoliday extends Controller
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

		$viewData['title'] = isset($id) ? 'Edit Recurring Holiday' : 'Add Recurring Holiday';
		$current_company = $this->currentCompany;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'data'
			) 
		) );
		
		$rhf = new RecurringHolidayFactory();

		if ( isset($id) ) {
			$rhlf = new RecurringHolidayListFactory(); 
			$rhlf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($rhlf->rs as $rh_obj) {
				$rhlf->data = (array)$rh_obj;
				$rh_obj = $rhlf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $rh_obj->getId(),
					'name' => $rh_obj->getName(),
					'type_id' => $rh_obj->getType(),
					'special_day_id' => $rh_obj->getSpecialDay(),
					'week_interval' => $rh_obj->getWeekInterval(),
					'pivot_day_direction_id' => $rh_obj->getPivotDayDirection(),
					'day_of_week' => $rh_obj->getDayOfWeek(),
					'day_of_month' => $rh_obj->getDayOfMonth(),
					'month' => $rh_obj->getMonth(),
					'always_week_day_id' => $rh_obj->getAlwaysOnWeekDay(),
					'created_date' => $rh_obj->getCreatedDate(),
					'created_by' => $rh_obj->getCreatedBy(),
					'updated_date' => $rh_obj->getUpdatedDate(),
					'updated_by' => $rh_obj->getUpdatedBy(),
					'deleted_date' => $rh_obj->getDeletedDate(),
					'deleted_by' => $rh_obj->getDeletedBy()
				);
			}
		}

		//Select box options;
		$data['special_day_options'] = $rhf->getOptions('special_day');
		$data['type_options'] = $rhf->getOptions('type');
		$data['week_interval_options'] = $rhf->getOptions('week_interval');
		$data['pivot_day_direction_options'] = $rhf->getOptions('pivot_day_direction');
		$data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$data['month_of_year_options'] = TTDate::getMonthOfYearArray();
		$data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$data['always_week_day_options'] = $rhf->getOptions('always_week_day');

		$viewData['data'] = $data;
		$viewData['rhf'] = $rhf;

        return view('policy/EditRecurringHoliday', $viewData);

    }

	public function submit(Request $request){
		$rhf = new RecurringHolidayFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rhf->setId( $data['id'] );
		$rhf->setCompany( $current_company->getId() );
		$rhf->setName( $data['name'] );
		$rhf->setType( $data['type_id'] );
		/*
		if ( isset($data['easter']) ) {
			$rhf->setEaster( TRUE );
		} else {
			$rhf->setEaster( FALSE );
		}
		*/
		$rhf->setSpecialDay( $data['special_day_id'] );
		$rhf->setWeekInterval( $data['week_interval'] );
		$rhf->setPivotDayDirection( $data['pivot_day_direction_id'] );

		if ( $data['type_id'] == 20 ) {
			$rhf->setDayOfWeek( $data['day_of_week_20'] );
		} elseif ( $data['type_id'] == 30 ) {
			$rhf->setDayOfWeek( $data['day_of_week_30'] );
		}

		$rhf->setDayOfMonth( $data['day_of_month'] );
		$rhf->setMonth( $data['month'] );

		$rhf->setAlwaysOnWeekDay( $data['always_week_day_id'] );

		if ( $rhf->isValid() ) {
			$rhf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList') );

		}
	}
}

?>