<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Option;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;


class UserDateTotalList extends Controller
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
				OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Hour List';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				//'user_date_id',
				'filter_user_id',
				'filter_date',
				'filter_system_time',
				'prev_day',
				'next_day',
				'prev_week',
				'next_week',
				'ids',
			) 
		) );
		
		if ( $filter_user_id != '' ) {
			$user_id = $filter_user_id;
		} else {
			$user_id = $current_user->getId();
		}
		
		if ( $filter_date != '' ) {
			$filter_date = TTDate::getBeginDayEpoch( TTDate::parseDateTime( $filter_date ) );
		}
		
		if ( isset($prev_day) ) {
			$filter_date = TTDate::getBeginDayEpoch( $filter_date-(86400) );
		} elseif ( isset($next_day) ) {
			$filter_date = TTDate::getBeginDayEpoch( $filter_date+(86400) );
		}
		
		if ( isset($prev_week) ) {
			$filter_date = TTDate::getBeginDayEpoch( $filter_date-(86400*7) );
		} elseif ( isset($next_week) ) {
			$filter_date = TTDate::getBeginDayEpoch( $filter_date+(86400*7) );
		}
		
		//This must be below any filter_date modifications
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array(
				//'user_date_id' => $user_date_id,
				'filter_date' => $filter_date,
				'filter_user_id' => $filter_user_id,
				'filter_system_time' => $filter_system_time,
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}
		
		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);
		
		switch ($action) {
			case 'add':
		
				//Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id), 'EditUserWage.php', FALSE) );
		
				break;
			case 'delete' OR 'undelete':
				if ( strtolower($action) == 'delete' ) {
					$delete = TRUE;
				} else {
					$delete = FALSE;
				}
		
				$udtlf = new UserDateTotalListFactory();
				if ( is_array($ids) ) {
					$id_count = count($ids)-1;
		
					$i=0;
					foreach ($ids as $tmp_id) {
						$udtlf->getById($tmp_id);
						foreach ($udtlf as $udt_obj) {
							$udt_obj->setDeleted($delete);
		
							if ( $id_count == $i ) {
								$udt_obj->setEnableCalcSystemTotalTime(TRUE);
							}
		
							$udt_obj->Save();
						}
						$i++;
					}
				}
		
				Redirect::Page( URLBuilder::getURL(array('user_id' => $user_id, 'filter_date' => $filter_date), 'UserDateTotalList.php') );
		
				break;
		
			default:
				if ( ( !isset($user_date_id) OR (isset($user_date_id) AND $user_date_id == '') ) AND $user_id != '' AND $filter_date != '' ) {
					Debug::Text('User Date ID not passed, inserting one.', __FILE__, __LINE__, __METHOD__,10);
					$user_date_id = UserDateFactory::findOrInsertUserDate($user_id, $filter_date); 
				}

				if ( !empty($user_date_id) ) {
					$udtlf = new UserDateTotalListFactory();
					$udtlf->getByUserDateIDAndStatusAndType( $user_date_id, array(10,20,30), array(10,20,30,40,100), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array);
					$pager = new Pager($udtlf); 
		
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
		
					/*
					$job_options = array();
					$job_item_options = array();
					if ( $current_company->getProductEdition() == 20 ) {
						$jlf = new JobListFactory();
						$job_options = $jlf->getByCompanyIdArray( $current_company->getId(), FALSE );
		
						$jilf = new JobItemListFactory();
						$job_item_options = $jilf->getByCompanyIdArray( $current_company->getId(), TRUE );
					}
					*/
		
					$day_total_time = 	array (
											'total_time' => 0,
											'worked_time' => 0,
											'difference' => 0
										);

					foreach ($udtlf->rs as $udt_obj) {
						$udtlf->data = (array)$udt_obj;
						$udt_obj = $udtlf;

						if ( $udt_obj->getStatus() == 20 ) {
							$day_total_time['worked_time'] += $udt_obj->getTotalTime();
						} elseif ( $udt_obj->getStatus() == 10 AND  $udt_obj->getType() == 10) {
							$day_total_time['total_time'] += $udt_obj->getTotalTime();
						}
		
						if ( $filter_system_time != 1 AND $udt_obj->getStatus() == 10 ) {
							continue;
						}
		
						if ( $udt_obj->getJob() != FALSE ) {
							$job = $job_options[$udt_obj->getJob()];
						} else {
							$job = 'No Job';
						}
		
						if ( $udt_obj->getJobItem() != FALSE ) {
							$job_item = $job_item_options[$udt_obj->getJobItem()];
						} else {
							$job_item = _('No Task');
						}
		
						$rows[] = array(
											'id' => $udt_obj->getId(),
											'status_id' => $udt_obj->getStatus(),
											'status' => Option::getByKey($udt_obj->getStatus(), $udt_obj->getOptions('status') ),
											'type_id' => $udt_obj->getType(),
											'type' => Option::getByKey($udt_obj->getType(), $udt_obj->getOptions('type') ),
											'branch_id' => $udt_obj->getBranch(),
											'branch' => $branch_options[$udt_obj->getBranch()],
											'department_id' => $udt_obj->getDepartment(),
											'department' => $department_options[$udt_obj->getDepartment()],
		
											'job_id' => $udt_obj->getJob(),
											'job' => $job,
											'job_item_id' => $udt_obj->getJobItem(),
											'job_item' => $job_item,
											'quantity' => (int)$udt_obj->getQuantity(),
											'bad_quantity' => (int)$udt_obj->getBadQuantity(),
		
											'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
											'absence_policy' => $absence_policy_options[$udt_obj->getAbsencePolicyID()],
											'over_time_policy_id' => $udt_obj->getOverTimePolicyID(),
											'over_time_policy' => $over_time_policy_options[$udt_obj->getOverTimePolicyID()],
											'premium_policy_id' => $udt_obj->getPremiumPolicyID(),
											'premium_policy' => $premium_policy_options[$udt_obj->getPremiumPolicyID()],
											'total_time' => $udt_obj->getTotalTime(),
											'override' => $udt_obj->getOverride(),
											'deleted' => $udt_obj->getDeleted()
										);
					}
					$day_total_time['difference'] = $day_total_time['worked_time'] - $day_total_time['total_time'];
		
					//var_dump($day_total_time);
		
					$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
					$viewData['user_options'] = $user_options;
					
					$viewData['rows'] = $rows ?? [];
					$viewData['day_total_time'] = $day_total_time;
					$viewData['user_date_id'] = $user_date_id ;
					$viewData['filter_user_id'] = $user_id ;
					$viewData['filter_date'] = $filter_date ;
					$viewData['filter_system_time'] = $filter_system_time ;
		
					$viewData['sort_column'] = $sort_column ;
					$viewData['sort_order'] = $sort_order ;
		
					$viewData['paging_data'] = $pager->getPageVariables() ;
		
				}
		
				break;
		}
		
		return view('punch/UserDateTotalList', $viewData);

	}

}

?>