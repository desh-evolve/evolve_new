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
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Policy\ExceptionPolicyControlListFactory;
use App\Models\Policy\HolidayPolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PolicyGroupFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditPolicyGroup extends Controller
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
        if ( !$permission->Check('policy_group','enabled')
				OR !( $permission->Check('policy_group','edit') OR $permission->Check('policy_group','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

        $viewData['title'] = isset($id) ? 'Edit Policy Group' : 'Add Policy Group';
		$current_company = $this->currentCompany;
		
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'data'
			) 
		) );
		
		$pgf = new PolicyGroupFactory(); 

		if ( isset($id) ) {

			$pglf = new PolicyGroupListFactory();
			$pglf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($pglf->rs as $pg_obj) {
				$pglf->data = (array)$pg_obj;
				$pg_obj = $pglf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
					'id' => $pg_obj->getId(),
					'name' => $pg_obj->getName(),
					'meal_policy_ids' => $pg_obj->getMealPolicy(),
					'break_policy_ids' => $pg_obj->getBreakPolicy(),
					'holiday_policy_ids' => $pg_obj->getHolidayPolicy(),
					'exception_policy_control_id' => $pg_obj->getExceptionPolicyControlID(),
					'user_ids' => $pg_obj->getUser(),
					'over_time_policy_ids' => $pg_obj->getOverTimePolicy(),
					'premium_policy_ids' => $pg_obj->getPremiumPolicy(),
					'round_interval_policy_ids' => $pg_obj->getRoundIntervalPolicy(),
					'accrual_policy_ids' => $pg_obj->getAccrualPolicy(),
					'created_date' => $pg_obj->getCreatedDate(),
					'created_by' => $pg_obj->getCreatedBy(),
					'updated_date' => $pg_obj->getUpdatedDate(),
					'updated_by' => $pg_obj->getUpdatedBy(),
					'deleted_date' => $pg_obj->getDeletedDate(),
					'deleted_by' => $pg_obj->getDeletedBy()
				);
			}
		}

		$none_array_option = array('0' => _('-- None --') );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), FALSE, TRUE );

		$otplf = new OverTimePolicyListFactory();
		$over_time_policy_options = Misc::prependArray( $none_array_option, $otplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$pplf = new PremiumPolicyListFactory();
		$premium_policy_options = Misc::prependArray( $none_array_option, $pplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$riplf = new RoundIntervalPolicyListFactory();
		$round_interval_policy_options = Misc::prependArray( $none_array_option, $riplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$mplf = new MealPolicyListFactory();
		$meal_options = Misc::prependArray( $none_array_option, $mplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$bplf = new BreakPolicyListFactory();
		$break_options = Misc::prependArray( $none_array_option, $bplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$epclf = new ExceptionPolicyControlListFactory();
		$exception_options = Misc::prependArray( $none_array_option, $epclf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$hplf = new HolidayPolicyListFactory();
		$holiday_policy_options = Misc::prependArray( $none_array_option, $hplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyIdAndTypeID( $current_company->getId(), array(20, 30) ); //Calendar and Hour based.
		$accrual_options = Misc::prependArray( $none_array_option, $aplf->getArrayByListFactory( $aplf, FALSE ) );

		//Select box options;
		$data['user_options'] = $user_options;
		$data['over_time_policy_options'] = $over_time_policy_options;
		$data['premium_policy_options'] = $premium_policy_options;
		$data['round_interval_policy_options'] = $round_interval_policy_options;
		$data['accrual_policy_options'] = $accrual_options;
		$data['meal_options'] = $meal_options;
		$data['break_options'] = $break_options;
		$data['exception_options'] = $exception_options;
		$data['holiday_policy_options'] = $holiday_policy_options;

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}

		//$viewData['filter_user_options'] = $filter_user_options;
		$viewData['data'] = $data;
		$viewData['pgf'] = $pgf;

		//print_r($data['over_time_policy_options']);exit;

        return view('policy/EditPolicyGroup', $viewData);

    }

	public function submit(Request $request){
		$pgf = new PolicyGroupFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Debug::setVerbosity(11);

		$pgf->StartTransaction();

		$pgf->setId( $data['id'] );
		$pgf->setCompany( $current_company->getId() );
		$pgf->setName( $data['name'] );
		$pgf->setExceptionPolicyControlID( $data['exception_policy_control_id'] );

		if ( $pgf->isValid() ) {
			$pgf->Save(FALSE);

			if ( isset($data['user_ids'] ) ) {
				$pgf->setUser( $data['user_ids'] );
			} else {
				$pgf->setUser( array() );
			}

			if ( isset($data['over_time_policy_ids'] ) ) {
				$pgf->setOverTimePolicy( $data['over_time_policy_ids'] );
			} else {
				$pgf->setOverTimePolicy( array() );
			}

			if ( isset($data['premium_policy_ids'] ) ) {
				$pgf->setPremiumPolicy( $data['premium_policy_ids'] );
			} else {
				$pgf->setPremiumPolicy( array() );
			}

			if ( isset($data['round_interval_policy_ids']) ) {
				$pgf->setRoundIntervalPolicy( $data['round_interval_policy_ids'] );
			} else {
				$pgf->setRoundIntervalPolicy( array() );
			}

			if ( isset($data['accrual_policy_ids']) ) {
				$pgf->setAccrualPolicy( $data['accrual_policy_ids'] );
			} else {
				$pgf->setAccrualPolicy( array() );
			}

			if ( isset($data['meal_policy_ids']) ) {
				$pgf->setMealPolicy( $data['meal_policy_ids'] );
			} else {
				$pgf->setMealPolicy( array() );
			}

			if ( isset($data['break_policy_ids']) ) {
				$pgf->setBreakPolicy( $data['break_policy_ids'] );
			} else {
				$pgf->setBreakPolicy( array() );
			}

			if ( isset($data['holiday_policy_ids']) ) {
				$pgf->setHolidayPolicy( $data['holiday_policy_ids'] );
			} else {
				$pgf->setHolidayPolicy( array() );
			}

			if ( $pgf->isValid() ) {
				$pgf->Save();
				$pgf->CommitTransaction();

				Redirect::Page( URLBuilder::getURL( NULL, '/policy/policy_groups') );
			}


		}
		$pgf->FailTransaction();
	}
}

?>