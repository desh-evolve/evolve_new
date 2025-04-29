<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use Illuminate\Support\Facades\View;


class EditUserDateTotal extends Controller
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

    public function index() {
		$current_company = $this->currentCompany;
		$current_user = $this->currentUser;
		$permission = $this->permission;
		$current_user_prefs = $this->userPrefs;

		if ( !$permission->Check('punch','enabled')
				OR !( $permission->Check('punch','edit')
						OR $permission->Check('punch','edit_own')
						) ) {

			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Edit Hour';

		extract	(FormVariables::GetVariables(
			array(
				'action',
				'id',
				'user_date_id',
				'user_id',
				'date',
				'udt_data'
			) 
		) );
		
		
		if ( isset($udt_data) ) {
			if ( $udt_data['total_time'] != '') {
				$udt_data['total_time'] = TTDate::parseTimeUnit( $udt_data['total_time'] ) ;
			}
		}
		
		$udtf = new UserDateTotalFactory();
		
		$action = strtolower($action);
		switch ($action) {
			case 'submit':
				Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
				//Debug::setVerbosity(11);
		
				$udtf->setId($udt_data['id']);
				$udtf->setUserDateId($udt_data['user_date_id']);
				$udtf->setStatus($udt_data['status_id']);
				$udtf->setType( $udt_data['type_id'] );
				$udtf->setBranch($udt_data['branch_id']);
				$udtf->setDepartment($udt_data['department_id']);
		
				if ( isset($udt_data['job_id']) ) {
					$udtf->setJob($udt_data['job_id']);
				}
				if ( isset($udt_data['job_item_id']) ) {
					$udtf->setJobItem($udt_data['job_item_id']);
				}
				if ( isset($udt_data['quantity']) ) {
					$udtf->setQuantity($udt_data['quantity']);
				}
				if ( isset($udt_data['bad_quantity']) ) {
					$udtf->setBadQuantity($udt_data['bad_quantity']);
				}
		
				$udtf->setOverTimePolicyID($udt_data['over_time_policy_id']);
				$udtf->setPremiumPolicyID($udt_data['premium_policy_id']);
				$udtf->setAbsencePolicyID($udt_data['absence_policy_id']);
				$udtf->setMealPolicyID($udt_data['meal_policy_id']);
		
				$udtf->setTotalTime($udt_data['total_time']);
				$udtf->setPunchControlID( (int)$udt_data['punch_control_id']);
				if ( isset($udt_data['override']) AND $udt_data['override'] == 1 ) {
					Debug::Text('Setting override to TRUE!', __FILE__, __LINE__, __METHOD__,10);
					$udtf->setOverride(TRUE);
				} else {
					$udtf->setOverride(FALSE);
				}
		
				if ( $udtf->isValid() ) {
					$udtf->setEnableCalcSystemTotalTime( TRUE );
					$udtf->setEnableCalcWeeklySystemTotalTime( TRUE );
					$udtf->setEnableCalcException( TRUE );
		
					$udtf->Save();
		
					Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
					break;
				}
			default:
				if ( !empty($id) ) {
					Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
		
					$udtlf = new UserDateTotalListFactory(); 
					$udtlf->getById( $id );
					
					foreach ($udtlf->rs as $udt_obj) {
						$udtlf->data = (array)$udt_obj;
						$udt_obj = $udtlf;
						//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
		
						$udt_data = array(
											'id' => $udt_obj->getId(),
											'user_date_id' => $udt_obj->getUserDateId(),
											'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
											'user_id' => $udt_obj->getUserDateObject()->getUser(),
											'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
											'status_id' => $udt_obj->getStatus(),
											'type_id' => $udt_obj->getType(),
											'total_time' => $udt_obj->getTotalTime(),
											'branch_id' => $udt_obj->getBranch(),
											'department_id' => $udt_obj->getDepartment(),
											'job_id' => $udt_obj->getJob(),
											'job_item_id' => $udt_obj->getJobItem(),
											'quantity' => $udt_obj->getQuantity(),
											'bad_quantity' => $udt_obj->getBadQuantity(),
											'punch_control_id' => $udt_obj->getPunchControlID(),
											'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
											'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
											'premium_policy_id' => $udt_obj->getPremiumPolicyID(),
											'meal_policy_id' => $udt_obj->getMealPolicyID(),
											'override' => $udt_obj->getOverride(),
											'created_date' => $udt_obj->getCreatedDate(),
											'created_by' => $udt_obj->getCreatedBy(),
											'updated_date' => $udt_obj->getUpdatedDate(),
											'updated_by' => $udt_obj->getUpdatedBy(),
											'deleted_date' => $udt_obj->getDeletedDate(),
											'deleted_by' => $udt_obj->getDeletedBy(),
											'override' => $udt_obj->getOverride(),
										);
					}
				} elseif ( $action != 'submit' ) {
					Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
					//UserID has to be set at minimum
					if ( !empty($user_date_id) ) {
						$udlf = new UserDateListFactory();  
						$udlf->getById( $user_date_id );
						if ( $udlf->getRecordCount() > 0 ) {
							$udt_obj = $udlf->getCurrent();
		
							$udt_data = array(
								'user_date_id' => $user_date_id,
								'date_stamp' => $udt_obj->getDateStamp(),
								'user_id' => $udt_obj->getUser(),
								'user_full_name' => $udt_obj->getUserObject()->getFullName(),
								'branch_id' => $udt_obj->getUserObject()->getDefaultBranch(),
								'department_id' => $udt_obj->getUserObject()->getDefaultDepartment(),
								'total_time' => 0,
								'status_id' => 20,
								'quantity' => 0,
								'bad_quantity' => 0,
								'punch_control_id' => 0,
								'override' => FALSE
							);
						}
					}
				}
		
				$blf = new BranchListFactory();
				$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );
		
				$dlf = new DepartmentListFactory();
				$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );
		
				//Absence policies
				$otplf = new AbsencePolicyListFactory();
				$absence_policy_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		
				//Overtime policies
				$otplf = new OverTimePolicyListFactory();
				$over_time_policy_options = $otplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		
				//Premium policies
				$pplf = new PremiumPolicyListFactory();
				$premium_policy_options = $pplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		
				//Meal policies
				$mplf = new MealPolicyListFactory();
				$meal_policy_options = $mplf->getByCompanyIDArray( $current_company->getId(), TRUE );
		
				/*
				if ( $current_company->getProductEdition() == 20 ) {
					$jlf = new JobListFactory();
					$udt_data['job_options'] = $jlf->getByCompanyIdAndUserIdAndStatusArray( $current_company->getId(),  $udt_data['user_id'], array(10,20,30,40), TRUE );
		
					$jilf = new JobItemListFactory();
					$udt_data['job_item_options'] = $jilf->getByCompanyIdArray( $current_company->getId(), TRUE );
				}
				*/
		
				//Select box options;
				$udt_data['status_options'] = $udtf->getOptions('status');
				$udt_data['type_options'] = $udtf->getOptions('type');
				$udt_data['branch_options'] = $branch_options;
				$udt_data['department_options'] = $department_options;
				$udt_data['absence_policy_options'] = $absence_policy_options;
				$udt_data['over_time_policy_options'] = $over_time_policy_options;
				$udt_data['premium_policy_options'] = $premium_policy_options;
				$udt_data['meal_policy_options'] = $meal_policy_options;
		
				//var_dump($pc_data);
		
				$viewData['udt_data'] = $udt_data;
				$viewData['user_date_id'] = $user_date_id;
				$viewData['user_id'] = $user_id;
				$viewData['current_user_prefs'] = $current_user_prefs;
		
				break;
		}
		//dd($viewData);
		$viewData['udtf'] = $udtf;
		
		return view('punch/EditUserDateTotal', $viewData);
	}

}

?>