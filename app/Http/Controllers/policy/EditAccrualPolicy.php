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
use App\Models\Policy\AccrualPolicyFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\AccrualPolicyMilestoneFactory;
use App\Models\Policy\AccrualPolicyMilestoneListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditAccrualPolicy extends Controller
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
        if ( !$permission->Check('accrual_policy','enabled')
				OR !( $permission->Check('accrual_policy','edit') OR $permission->Check('accrual_policy','edit_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$viewData['title'] = isset($id) ? 'Edit Accrual Policy' : 'Add Accrual Policy';
		$current_company = $this->currentCompany;

		if ( isset($data['recalculate_start_date']) ) {
			$data['recalculate_start_date'] = TTDate::parseDateTime( $data['recalculate_start_date'] );
		}
		if ( isset($data['recalculate_end_date']) ) {
			$data['recalculate_end_date'] = TTDate::parseDateTime( $data['recalculate_end_date'] );
		}
		
		if ( isset($data['milestone_rows']) ) {
			foreach( $data['milestone_rows'] as $milestone_row_id => $milestone_row ) {
		
				if ( $data['type_id'] == 20 AND isset($milestone_row['accrual_rate']) AND $milestone_row['accrual_rate'] != '' ) {
					$data['milestone_rows'][$milestone_row_id]['accrual_rate'] = TTDate::parseTimeUnit($milestone_row['accrual_rate'] );
							//$data['milestone_rows'][$milestone_row_id]['accrual_rate'] = $milestone_row['accrual_rate'];
				}
				if ( isset($milestone_row['maximum_time']) AND $milestone_row['maximum_time'] != '' ) {
					$data['milestone_rows'][$milestone_row_id]['maximum_time'] = TTDate::parseTimeUnit($milestone_row['maximum_time'] );
							//$data['milestone_rows'][$milestone_row_id]['maximum_time'] = $milestone_row['maximum_time'] ;
				}
				/*
				if ( isset($milestone_row['minimum_time']) AND $milestone_row['minimum_time'] != '' ) {
					$data['milestone_rows'][$milestone_row_id]['minimum_time'] = TTDate::parseTimeUnit($milestone_row['minimum_time'] );
				}
				*/
				if ( isset($milestone_row['rollover_time']) AND $milestone_row['rollover_time'] != '' ) {
					$data['milestone_rows'][$milestone_row_id]['rollover_time'] = TTDate::parseTimeUnit($milestone_row['rollover_time'] );
							
						  
				}
		
			}
		}
		
		$apf = new AccrualPolicyFactory();
		$apmf = new AccrualPolicyMilestoneFactory();

		if ( isset($id) ) {

			$aplf = new AccrualPolicyListFactory();
			$apmlf = new AccrualPolicyMilestoneListFactory();

			$aplf->getByIdAndCompanyID( $id, $current_company->getID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$apmlf->getByAccrualPolicyId( $id );
				if ( $apmlf->getRecordCount() > 0 ) {
					foreach( $apmlf->rs as $apm_obj ) {
						$apmlf->data = (array)$apm_obj;
						$apm_obj = $apmlf;

						$milestone_rows[$apm_obj->getId()] = array(
																'id' => $apm_obj->getId(),
																'length_of_service' => $apm_obj->getLengthOfService(),
																'length_of_service_unit_id' => $apm_obj->getLengthOfServiceUnit(),
																'accrual_rate' => $apm_obj->getAccrualRate(),
																'maximum_time' => $apm_obj->getMaximumTime(),
																'rollover_time' => $apm_obj->getRolloverTime(),
																//'minimum_time' => $apm_obj->getMinimumTime(),
																);
					}
				} else {
					$milestone_rows[-1] = array(
						'id' => -1,
						'length_of_service' => 0,
						'accrual_rate' => 0,
						'minimum_time' => 0,
						'maximum_time' => 0,
						'rollover_time' => '', //NULL is not used.
						);

				}

				foreach ($aplf->rs as $ap_obj) {
					$aplf->data = (array)$ap_obj;
					$ap_obj = $aplf;

					//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
					$data = array(
										'id' => $ap_obj->getId(),
										'name' => $ap_obj->getName(),
										'type_id' => $ap_obj->getType(),
										'enable_pay_stub_balance_display' => $ap_obj->getEnablePayStubBalanceDisplay(),
										'apply_frequency_id' => $ap_obj->getApplyFrequency(),
										'apply_frequency_month' => $ap_obj->getApplyFrequencyMonth(),
										'apply_frequency_day_of_month' => $ap_obj->getApplyFrequencyDayOfMonth(),
										'apply_frequency_day_of_week' => $ap_obj->getApplyFrequencyDayOfWeek(),
										'apply_frequency_hire_date' => $ap_obj->getApplyFrequencyHireDate(),
										'milestone_rollover_hire_date' => $ap_obj->getMilestoneRolloverHireDate(),
										'milestone_rollover_month' => $ap_obj->getMilestoneRolloverMonth(),
										'milestone_rollover_day_of_month' => $ap_obj->getMilestoneRolloverDayOfMonth(),
										'minimum_employed_days' => $ap_obj->getMinimumEmployedDays(),

										'recalculate_start_date' => TTDate::getBeginMonthEpoch( time() ),
										'recalculate_end_date' => TTDate::getEndMonthEpoch( time() ),

										'milestone_rows' => $milestone_rows,

										'created_date' => $ap_obj->getCreatedDate(),
										'created_by' => $ap_obj->getCreatedBy(),
										'updated_date' => $ap_obj->getUpdatedDate(),
										'updated_by' => $ap_obj->getUpdatedBy(),
										'deleted_date' => $ap_obj->getDeletedDate(),
										'deleted_by' => $ap_obj->getDeletedBy()
									);
				}
			}
		} elseif ( $action == 'add_milestone' ) {
			Debug::Text('Adding Blank Week', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($data['milestone_rows']) ) {
				$data['milestone_rows'] = array();
			}

			$row_keys = array_keys($data['milestone_rows']);
			sort($row_keys);

			Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
			$lowest_id = $row_keys[0];
			if ( $lowest_id < 0 ) {
				$next_blank_id = $lowest_id-1;
			} else {
				$next_blank_id = -1;
			}

			Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

			$data['milestone_rows'][$next_blank_id] = array(
							'id' => $next_blank_id,
							'length_of_service' => 0,
							'accrual_rate' => 0,
							'minimum_time' => 0,
							'maximum_time' => 0,
							'rollover_time' => '',
							);
		} elseif ( $action != 'submit' AND $action != 'change_type' ) {
			$data = array(
						'type_id' => 10,
						'minimum_employed_days' => 0,
						'recalculate_start_date' => TTDate::getBeginMonthEpoch( time() ),
						'recalculate_end_date' => TTDate::getEndMonthEpoch( time() ),
						'apply_frequency_hire_date' => TRUE,
						'milestone_rows' => array( -1 => array(
													'id' => -1,
													'length_of_service' => 0,
													'accrual_rate' => '0.0000',
													'minimum_time' => 0,
													'maximum_time' => 0,
													'rollover_time' => '',
												) )
						);
		} else {
			if ( $data['type_id'] == 20 ) {
				$data['recalculate_start_date'] = TTDate::getBeginMonthEpoch( time() );
				$data['recalculate_end_date'] = TTDate::getEndMonthEpoch( time() );
			}
		}
		//print_r($data);

		//Select box options;
		$data['type_options'] = $apf->getOptions('type');
		$data['apply_frequency_options'] = $apf->getOptions('apply_frequency');
		$data['month_options'] = TTDate::getMonthOfYearArray();
		$data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$data['length_of_service_unit_options'] = $apmf->getOptions('length_of_service_unit');

		$viewData['data'] = $data;
		$viewData['apf'] = $apf;
		$viewData['apmf'] = $apmf;

        return view('policy/EditAccrualPolicy', $viewData);

    }

	public function delete(){
		//Debug::setVerbosity(11);
		if ( count($ids) > 0) {
			foreach ($ids as $apm_id) {
				if ($apm_id > 0) {
					Debug::Text('cDeleting Milestone Row ID: '. $apm_id, __FILE__, __LINE__, __METHOD__,10);

					$apmlf = new AccrualPolicyMilestoneListFactory();
					$apmlf->getById( $apm_id );
					if ( $apmlf->getRecordCount() == 1 ) {
						foreach($apmlf->rs as $apm_obj ) {
							$apmlf->data = (array)$apm_obj;
							$apm_obj = $apmlf;
							
							$apm_obj->setDeleted( TRUE );
							if ( $apm_obj->isValid() ) {
								$apm_obj->Save();
							}
						}
					}
				}
				unset($data['milestone_rows'][$apm_id]);

			}
			unset($apm_id);
		}

		Redirect::Page( URLBuilder::getURL( array('id' => $data['id']), 'EditAccrualPolicy') );

	}

	public function submit(Request $request){
		$apf = new AccrualPolicyFactory();  
		$apmf = new AccrualPolicyMilestoneFactory();

		$data = $request->data;
		$current_company = $this->currentCompany;

		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$redirect=0;

		$apf->StartTransaction();

		$apf->setId( $data['id'] );
		$apf->setCompany( $current_company->getId() );
		$apf->setName( $data['name'] );
		$apf->setType( $data['type_id'] );

		if ( isset($data['enable_pay_stub_balance_display']) ) {
			$apf->setEnablePayStubBalanceDisplay( TRUE );
		} else {
			$apf->setEnablePayStubBalanceDisplay( FALSE );
		}

		$apf->setApplyFrequency( $data['apply_frequency_id'] );
		$apf->setApplyFrequencyMonth( $data['apply_frequency_month'] );
		$apf->setApplyFrequencyDayOfMonth( $data['apply_frequency_day_of_month'] );
		$apf->setApplyFrequencyDayOfWeek( $data['apply_frequency_day_of_week'] );
		$apf->setApplyFrequencyHireDate( $data['apply_frequency_hire_date'] );

		if ( isset($data['milestone_rollover_hire_date']) ) {
			$apf->setMilestoneRolloverHireDate( TRUE );
		} else {
			$apf->setMilestoneRolloverHireDate( FALSE );
			$apf->setMilestoneRolloverMonth( $data['milestone_rollover_month'] );
			$apf->setMilestoneRolloverDayOfMonth( $data['milestone_rollover_day_of_month'] );
		}

		$apf->setMinimumEmployedDays( $data['minimum_employed_days'] );

		if ( $apf->isValid() ) {
			$ap_id = $apf->Save();

			if ( $ap_id === TRUE ) {
				$ap_id = $data['id'];
			}

			if ( ( $data['type_id'] == 20 OR $data['type_id'] == 30 ) AND isset($data['milestone_rows']) AND count($data['milestone_rows']) > 0 ) {
				foreach( $data['milestone_rows'] as $milestone_row_id => $milestone_row ) {
					Debug::Text('Row ID: '. $milestone_row_id, __FILE__, __LINE__, __METHOD__,10);
					if ( $milestone_row['accrual_rate'] > 0 ) {
						if ( $milestone_row_id > 0 ) {
							$apmf->setId( $milestone_row_id);
						}

						$apmf->setAccrualPolicy( $ap_id );
						$apmf->setLengthOfService( $milestone_row['length_of_service'] );
						$apmf->setLengthOfServiceUnit( $milestone_row['length_of_service_unit_id'] );
						$apmf->setAccrualRate( $milestone_row['accrual_rate'] );
						$apmf->setMaximumTime( $milestone_row['maximum_time'] );
						//$apmf->setMinimumTime( $milestone_row['minimum_time'] );
						$apmf->setRolloverTime( $milestone_row['rollover_time'] );

						if ( $apmf->isValid() ) {
							Debug::Text('Saving Milestone Row ID: '. $milestone_row_id, __FILE__, __LINE__, __METHOD__,10);
							$apmf->Save();
						} else {
							$redirect++;
						}
					}
				}
			}

			if ( $redirect == 0 ) {
				$apf->CommitTransaction();
				//$apf->FailTransaction();

				if ( isset($ap_id) AND isset($data['recalculate']) AND $data['recalculate'] == 1 ) {
					Debug::Text('Recalculating Accruals...', __FILE__, __LINE__, __METHOD__,10);

					if ( isset($data['recalculate_start_date']) AND isset($data['recalculate_end_date'])
							AND $data['recalculate_start_date'] < $data['recalculate_end_date']) {
						Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_accrual_policy', 'data' => array('accrual_policy_id' => $ap_id, 'start_date' => $data['recalculate_start_date'], 'end_date' => $data['recalculate_end_date']), 'next_page' => urlencode( URLBuilder::getURL( NULL, '../policy/AccrualPolicyList') ) ), '../progress_bar/ProgressBarControl'), FALSE );
					}
				}

				Redirect::Page( URLBuilder::getURL( NULL, 'AccrualPolicyList') );
			}

		}
		$apf->FailTransaction();
	}

}


?>