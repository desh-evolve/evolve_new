<?php

namespace App\Http\Controllers\policy;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;
use App\Models\Company\WageGroupListFactory;
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
use App\Models\Department\DepartmentListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\PremiumPolicyFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditPremiumPolicy extends Controller
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
        if ( !$permission->Check('premium_policy','enabled')
				OR !( $permission->Check('premium_policy','edit') OR $permission->Check('premium_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Premium Policy' : 'Add Premium Policy';
		$current_company = $this->currentCompany;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'data'
			) 
		) );
		
		if ( isset($data)) {
			if ( $data['start_date'] != '' ) {
				$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
			}
			if ( $data['end_date'] != '' ) {
				$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
			}
			if ( $data['start_time'] != '' ) {
				$data['start_time'] = TTDate::parseDateTime( $data['start_time'] );
			}
			if ( $data['end_time'] != '' ) {
				$data['end_time'] = TTDate::parseDateTime( $data['end_time'] );
			}
		
			if ( isset($data['maximum_no_break_time'] ) ) {
				$data['maximum_no_break_time'] = TTDate::parseTimeUnit($data['maximum_no_break_time']);
			}
			if ( isset($data['minimum_break_time'] ) ) {
				$data['minimum_break_time'] = TTDate::parseTimeUnit($data['minimum_break_time']);
			}
		
			if ( isset($data['minimum_time_between_shift'] ) ) {
				$data['minimum_time_between_shift'] = TTDate::parseTimeUnit($data['minimum_time_between_shift']);
			}
			if ( isset($data['minimum_first_shift_time'] ) ) {
				$data['minimum_first_shift_time'] = TTDate::parseTimeUnit($data['minimum_first_shift_time']);
			}
		
			if ( isset($data['minimum_shift_time'] ) ) {
				$data['minimum_shift_time'] = TTDate::parseTimeUnit($data['minimum_shift_time']);
			}
		
			if ( isset($data['minimum_time'] ) ) {
				$data['minimum_time'] = TTDate::parseTimeUnit($data['minimum_time']);
			}
			if ( isset($data['maximum_time'] ) ) {
				$data['maximum_time'] = TTDate::parseTimeUnit($data['maximum_time']);
			}
		
			if ( $data['type_id'] == 30 ) {
				if ( isset($data['daily_trigger_time2'] ) ) {
					$data['daily_trigger_time'] = TTDate::parseTimeUnit($data['daily_trigger_time2']);
				}
			} else {
				if ( isset($data['daily_trigger_time'] ) ) {
					$data['daily_trigger_time'] = TTDate::parseTimeUnit($data['daily_trigger_time']);
				}
			}
		
			if ( isset($data['weekly_trigger_time'] ) ) {
				$data['weekly_trigger_time'] = TTDate::parseTimeUnit($data['weekly_trigger_time']);
			}
		}
		
		$ppf = new PremiumPolicyFactory();

		if ( isset($id) ) {

			$pplf = new PremiumPolicyListFactory();
			$pplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($pplf->rs as $pp_obj) {
				$pplf->data = (array)$pp_obj;
				$pp_obj = $pplf;

				$data = array(
					'id' => $pp_obj->getId(),
					'name' => $pp_obj->getName(),
					'type_id' => $pp_obj->getType(),
					'pay_type_id' => $pp_obj->getPayType(),
					//'level' => $pp_obj->getLevel(),

					'start_date' => $pp_obj->getStartDate(),
					'end_date' => $pp_obj->getEndDate(),

					'start_time' => $pp_obj->getStartTime(),
					'end_time' => $pp_obj->getEndTime(),

					'daily_trigger_time' => $pp_obj->getDailyTriggerTime(),
					'weekly_trigger_time' => $pp_obj->getWeeklyTriggerTime(),

					'sun' => $pp_obj->getSun(),
					'mon' => $pp_obj->getMon(),
					'tue' => $pp_obj->getTue(),
					'wed' => $pp_obj->getWed(),
					'thu' => $pp_obj->getThu(),
					'fri' => $pp_obj->getFri(),
					'sat' => $pp_obj->getSat(),

					'include_partial_punch' => $pp_obj->getIncludePartialPunch(),

					'maximum_no_break_time' => $pp_obj->getMaximumNoBreakTime(),
					'minimum_break_time' => $pp_obj->getMinimumBreakTime(),

					'minimum_time_between_shift' => $pp_obj->getMinimumTimeBetweenShift(),
					'minimum_first_shift_time' => $pp_obj->getMinimumFirstShiftTime(),

					'minimum_shift_time' => $pp_obj->getMinimumShiftTime(),

					'minimum_time' => $pp_obj->getMinimumTime(),
					'maximum_time' => $pp_obj->getMaximumTime(),

					'include_meal_policy' => $pp_obj->getIncludeMealPolicy(),
					'include_break_policy' => $pp_obj->getIncludeBreakPolicy(),

					'wage_group_id' => $pp_obj->getWageGroup(),
					'rate' => Misc::removeTrailingZeros( $pp_obj->getRate() ),

					'accrual_rate' => Misc::removeTrailingZeros( $pp_obj->getAccrualRate() ),
					'accrual_policy_id' => $pp_obj->getAccrualPolicyID(),
					'pay_stub_entry_account_id' => $pp_obj->getPayStubEntryAccountId(),

					'branch_selection_type_id' => $pp_obj->getBranchSelectionType(),
					'exclude_default_branch' => $pp_obj->getExcludeDefaultBranch(),
					'branch_ids' => $pp_obj->getBranch(),

					'department_selection_type_id' => $pp_obj->getDepartmentSelectionType(),
					'exclude_default_department' => $pp_obj->getExcludeDefaultDepartment(),
					'department_ids' => $pp_obj->getDepartment(),

					'job_group_selection_type_id' => $pp_obj->getJobGroupSelectionType(),
					'job_group_ids' => $pp_obj->getJobGroup(),
					'job_selection_type_id' => $pp_obj->getJobSelectionType(),
					'job_ids' => $pp_obj->getJob(),

					'job_item_group_selection_type_id' => $pp_obj->getJobItemGroupSelectionType(),
					'job_item_group_ids' => $pp_obj->getJobItemGroup(),
					'job_item_selection_type_id' => $pp_obj->getJobItemSelectionType(),
					'job_item_ids' => $pp_obj->getJobItem(),

					'created_date' => $pp_obj->getCreatedDate(),
					'created_by' => $pp_obj->getCreatedBy(),
					'updated_date' => $pp_obj->getUpdatedDate(),
					'updated_by' => $pp_obj->getUpdatedBy(),
					'deleted_date' => $pp_obj->getDeletedDate(),
					'deleted_by' => $pp_obj->getDeletedBy()
				);
			}
		} elseif ( $action != 'submit') {
			$data = array(
				'start_time' => NULL,
				'end_time' => NULL,
				'sun' => TRUE,
				'mon' => TRUE,
				'tue' => TRUE,
				'wed' => TRUE,
				'thu' => TRUE,
				'fri' => TRUE,
				'sat' => TRUE,
				'wage_group_id' => 0,
				'rate' => '1.00',
				'accrual_rate' => '1.00',
				'daily_trigger_time' => 0,
				'weekly_trigger_time' => 0,
				'maximum_no_break_time' => 0,
				'minimum_break_time' => 0,
				'minimum_time_between_shift' => 0,
				'minimum_first_shift_time' => 0,
				'minimum_shift_time' => 0,
				'minimum_time' => 0,
				'maximum_time' => 0,
				'include_meal_policy' => TRUE,
				'include_break_policy' => TRUE,
			);
		}

		$data = Misc::preSetArrayValues( $data, array('branch_ids', 'department_ids', 'job_group_ids', 'job_ids', 'job_item_group_ids', 'job_item_ids'), NULL);

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );
		$data['src_branch_options'] = Misc::arrayDiffByKey( (array)$data['branch_ids'], $branch_options );
		$data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );
		$data['src_department_options'] = Misc::arrayDiffByKey( (array)$data['department_ids'], $department_options );
		$data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$data['department_ids'], $department_options );

		if ( $current_company->getProductEdition() == 20 ) {
			//Get Job Groups
			$jglf = new JobGroupListFactory();
			$nodes = FastTree::FormatArray( $jglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_group_options = $jglf->getArrayByNodes( $nodes, FALSE, FALSE );
			$data['src_job_group_options'] = Misc::arrayDiffByKey( (array)$data['job_group_ids'], $job_group_options );
			$data['selected_job_group_options'] = Misc::arrayIntersectByKey( (array)$data['job_group_ids'], $job_group_options );

			//Get Jobs
			$jlf = new JobListFactory();
			$jlf->getByCompanyId( $current_company->getId() );
			$job_options = $jlf->getArrayByListFactory( $jlf, FALSE, TRUE );
			$data['src_job_options'] = Misc::arrayDiffByKey( (array)$data['job_ids'], $job_options );
			$data['selected_job_options'] = Misc::arrayIntersectByKey( (array)$data['job_ids'], $job_options );

			//Get Job Item Groups
			$jiglf = new JobItemGroupListFactory();
			$nodes = FastTree::FormatArray( $jiglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_item_group_options = $jiglf->getArrayByNodes( $nodes, FALSE, FALSE );
			$data['src_job_item_group_options'] = Misc::arrayDiffByKey( (array)$data['job_item_group_ids'], $job_item_group_options );
			$data['selected_job_item_group_options'] = Misc::arrayIntersectByKey( (array)$data['job_item_group_ids'], $job_item_group_options );

			//Get Job Items
			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$job_item_options = $jilf->getArrayByListFactory( $jilf, FALSE, TRUE );
			$data['src_job_item_options'] = Misc::arrayDiffByKey( (array)$data['job_item_ids'], $job_item_options );
			$data['selected_job_item_options'] = Misc::arrayIntersectByKey( (array)$data['job_item_ids'], $job_item_options );
		}

		//Select box options;
		$wglf = new WageGroupListFactory();
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$data['type_options'] = $ppf->getOptions('type');
		$data['pay_type_options'] = $ppf->getOptions('pay_type');

		$data['branch_selection_type_options'] = $ppf->getOptions('branch_selection_type');
		$data['department_selection_type_options'] = $ppf->getOptions('department_selection_type');
		$data['job_group_selection_type_options'] = $ppf->getOptions('job_group_selection_type');
		$data['job_selection_type_options'] = $ppf->getOptions('job_selection_type');
		$data['job_item_group_selection_type_options'] = $ppf->getOptions('job_item_group_selection_type');
		$data['job_item_selection_type_options'] = $ppf->getOptions('job_item_selection_type');

		$data['pay_stub_entry_options'] = $pay_stub_entry_options;
		$data['accrual_options'] = $accrual_options;

		$viewData['data'] = $data;
		$viewData['ppf'] = $ppf;

        return view('policy/EditPremiumPolicy', $viewData);

    }

	public function submit(Request $request){
		$ppf = new PremiumPolicyFactory();
		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$ppf->StartTransaction();

		$ppf->setId( $data['id'] );
		$ppf->setCompany( $current_company->getId() );
		$ppf->setName( $data['name'] );
		$ppf->setType( $data['type_id'] );
		$ppf->setPayType( $data['pay_type_id'] );

		if ( $data['type_id'] == 10 OR $data['type_id'] == 100 ) {
			$ppf->setStartDate( $data['start_date'] );
			$ppf->setEndDate( $data['end_date'] );

			$ppf->setStartTime( $data['start_time'] );
			$ppf->setEndTime( $data['end_time'] );

			$ppf->setDailyTriggerTime( $data['daily_trigger_time'] );
			$ppf->setWeeklyTriggerTime( $data['weekly_trigger_time'] );

			if ( isset($data['mon']) ) {
				$ppf->setMon( TRUE );
			} else {
				$ppf->setMon( FALSE );
			}

			if ( isset($data['tue']) ) {
				$ppf->setTue( TRUE );
			} else {
				$ppf->setTue( FALSE );
			}

			if ( isset($data['wed']) ) {
				$ppf->setWed( TRUE );
			} else {
				$ppf->setWed( FALSE );
			}

			if ( isset($data['thu']) ) {
				$ppf->setThu( TRUE );
			} else {
				$ppf->setThu( FALSE );
			}

			if ( isset($data['fri']) ) {
				$ppf->setFri( TRUE );
			} else {
				$ppf->setFri( FALSE );
			}

			if ( isset($data['sat']) ) {
				$ppf->setSat( TRUE );
			} else {
				$ppf->setSat( FALSE );
			}

			if ( isset($data['sun']) ) {
				$ppf->setSun( TRUE );
			} else {
				$ppf->setSun( FALSE );
			}

			if ( isset($data['include_partial_punch']) ) {
				$ppf->setIncludePartialPunch( TRUE );
			} else {
				$ppf->setIncludePartialPunch( FALSE );
			}
		} elseif ( $data['type_id'] == 30 ) {
			$ppf->setDailyTriggerTime( $data['daily_trigger_time'] );
		}

		if ( isset($data['maximum_no_break_time']) ) {
			$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
		}
		if ( isset($data['minimum_break_time']) ) {
			$ppf->setMinimumBreakTime( $data['minimum_break_time'] );
		}

		if ( isset($data['minimum_time_between_shift']) ) {
			$ppf->setMinimumTimeBetweenShift( $data['minimum_time_between_shift'] );
		}
		if ( isset($data['minimum_first_shift_time']) ) {
			$ppf->setMinimumFirstShiftTime( $data['minimum_first_shift_time'] );
		}

		if ( isset($data['minimum_shift_time']) ) {
			$ppf->setMinimumShiftTime( $data['minimum_shift_time'] );
		}

		$ppf->setMinimumTime( $data['minimum_time'] );
		$ppf->setMaximumTime( $data['maximum_time'] );
		if ( isset($data['include_meal_policy']) ) {
			$ppf->setIncludeMealPolicy( TRUE );
		} else {
			$ppf->setIncludeMealPolicy( FALSE );
		}
		if ( isset($data['include_break_policy']) ) {
			$ppf->setIncludeBreakPolicy( TRUE );
		} else {
			$ppf->setIncludeBreakPolicy( FALSE );
		}

		$ppf->setWageGroup( $data['wage_group_id'] );
		$ppf->setRate( $data['rate'] );
		$ppf->setPayStubEntryAccountId( $data['pay_stub_entry_account_id'] );
		$ppf->setAccrualPolicyId( $data['accrual_policy_id'] );
		$ppf->setAccrualRate( $data['accrual_rate'] );

		$ppf->setBranchSelectionType( $data['branch_selection_type_id'] );
		if ( isset($data['exclude_default_branch']) ) {
			$ppf->setExcludeDefaultBranch( TRUE );
		} else {
			$ppf->setExcludeDefaultBranch( FALSE );
		}

		$ppf->setDepartmentSelectionType( $data['department_selection_type_id'] );
		if ( isset($data['exclude_default_department']) ) {
			$ppf->setExcludeDefaultDepartment( TRUE );
		} else {
			$ppf->setExcludeDefaultDepartment( FALSE );
		}

		if ( $current_company->getProductEdition() == 20 ) {
			$ppf->setJobGroupSelectionType( $data['job_group_selection_type_id'] );
			$ppf->setJobSelectionType( $data['job_selection_type_id'] );

			$ppf->setJobItemGroupSelectionType( $data['job_item_group_selection_type_id'] );
			$ppf->setJobItemSelectionType( $data['job_item_selection_type_id'] );
		} else {
			//Set selection types to "All" so speed up checks in calcPremiumPolicy
			$ppf->setJobGroupSelectionType( 10 );
			$ppf->setJobSelectionType( 10 );

			$ppf->setJobItemGroupSelectionType( 10 );
			$ppf->setJobItemSelectionType( 10 );
		}

		if ( $ppf->isValid() ) {
			$ppf->Save(FALSE);

			if ( isset($data['branch_ids']) ){
				$ppf->setBranch( $data['branch_ids'] );
			} else {
				$ppf->setBranch( array() );
			}

			if ( isset($data['department_ids']) ){
				$ppf->setDepartment( $data['department_ids'] );
			} else {
				$ppf->setDepartment( array() );
			}

			if ( $current_company->getProductEdition() == 20 ) {
				if ( isset($data['job_group_ids']) ){
					$ppf->setJobGroup( $data['job_group_ids'] );
				} else {
					$ppf->setJobGroup( array() );
				}
				if ( isset($data['job_ids']) ){
					$ppf->setJob( $data['job_ids'] );
				} else {
					$ppf->setJob( array() );
				}

				if ( isset($data['job_item_group_ids']) ){
					$ppf->setJobItemGroup( $data['job_item_group_ids'] );
				} else {
					$ppf->setJobItemGroup( array() );
				}
				if ( isset($data['job_item_ids']) ){
					$ppf->setJobItem( $data['job_item_ids'] );
				} else {
					$ppf->setJobItem( array() );
				}
			}

			if ( $ppf->isValid() ) {
				$ppf->Save(TRUE);

				//$ppf->FailTransaction();
				$ppf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'PremiumPolicyList') );
			}
		}

		$ppf->FailTransaction();
	}
}

?>