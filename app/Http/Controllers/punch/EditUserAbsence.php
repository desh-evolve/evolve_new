<?php

namespace App\Http\Controllers\punch;

use App\Http\Controllers\Controller;
use App\Models\Company\BranchListFactory;

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Core\UserDateFactory;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Leaves\AbsenceLeaveListFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryRecordFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryRecordListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;


class EditUserAbsence extends Controller
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

		if ( !$permission->Check('absence','enabled')
				OR !( $permission->Check('absence','edit')
						OR $permission->Check('absence','edit_own')
						OR $permission->Check('absence','edit_child')
						) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		$viewData['title'] = 'Edit Absence';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'id',
				'user_id',
				'date_stamp',
				'udt_data'
			) 
		) );

		if ( isset($udt_data) ) {
			if ( $udt_data['total_time'] != '') {
				$udt_data['total_time'] = TTDate::parseTimeUnit( $udt_data['total_time'] ) ;
			}
		}

		$udtf = new UserDateTotalFactory();

		$action = Misc::findSubmitButton();
		$action = strtolower($action);
		switch ($action) {
			case 'delete':
				Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);
		
						$LeaveRecId = '';
						$aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
						$aluerlf->getByUserDateId($udt_data['user_date_id']);
						if($aluerlf->getCurrent()->getId()){
							$LeaveRecId = $aluerlf->getCurrent()->getId();
						}  

					
						$udtlf = new UserDateTotalListFactory();
						$aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
					
				$udtlf->getById( $udt_data['id'] );
				if ( $udtlf->getRecordCount() > 0 ) {
					foreach($udtlf->rs as $udt_obj) {
						$udtlf->data = (array)$udt_obj;
						$udt_obj = $udtlf;

						$aluerlf->getByUserDateId($udt_obj->getUserDateId()); 
						$leave_rec_obj = $aluerlf->getCurrent(); 

						if($leave_rec_obj->getId()){ 
							$leave_rec_obj->setDeleted(TRUE); 
							if($leave_rec_obj->isValid()){
								$leave_rec_obj->Save();
							}
						}   

						$udt_obj->setDeleted(TRUE);
						if ( $udt_obj->isValid() ) {
								$udt_obj->setEnableCalcSystemTotalTime( TRUE );
								$udt_obj->setEnableCalcWeeklySystemTotalTime( TRUE );
								$udt_obj->setEnableCalcException( TRUE );
								$udt_obj->Save();
						}
					}
				}

				Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );

				break;
			case 'submit':

				Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
				//Debug::setVerbosity(11);

				//-----start Leave Validation
				$leave_year = date('Y', $udt_data['date_stamp']);
				
				
				//Limit it to 31 days.
			if ( $udt_data['repeat'] > 31 ) {
					$udt_data['repeat'] = 31;
				}
				Debug::Text('Repeating Punch For: '. $udt_data['repeat'] .' Days', __FILE__, __LINE__, __METHOD__,10);

				$udtf->StartTransaction();

				$fail_transaction = FALSE;
				for($i=0; $i <= (int)$udt_data['repeat']; $i++ ) {
					Debug::Text('Absence Repeat: '. $i, __FILE__, __LINE__, __METHOD__,10);

					if ( $i == 0 ) {
						$date_stamp = $udt_data['date_stamp'];
					} else {
						$date_stamp = $udt_data['date_stamp'] + (86400 * $i);
					}
					Debug::Text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp), __FILE__, __LINE__, __METHOD__,10);

					if ( $i == 0 AND $udt_data['id'] != '' ) {
						//Because if a user modifies the type of absence, the accrual balances
						//may come out of sync. Instead of just editing the entry directly, lets
						//delete the old one, and insert it as new.
						if ( $udt_data['absence_policy_id'] == $udt_data['old_absence_policy_id'] ) {
							Debug::Text('Editing absence, absence policy DID NOT change', __FILE__, __LINE__, __METHOD__,10);
							$udtf->setId($udt_data['id']);
						} else {
							Debug::Text('Editing absence, absence policy changed, deleting old record ID: '. $udt_data['id'] , __FILE__, __LINE__, __METHOD__,10);
							$udtlf = new UserDateTotalListFactory();
							$udtlf->getById( $udt_data['id'] );
							if ( $udtlf->getRecordCount() == 1 ) {
								$udt_obj = $udtlf->getCurrent();
								$udt_obj->setDeleted(TRUE);
								if ( $udt_obj->isValid() ) {
									$udt_obj->Save();
								}
							}
							unset($udtlf, $udt_obj);
						}
					}

					$udtf->setUserDateId( UserDateFactory::findOrInsertUserDate($udt_data['user_id'], $date_stamp) );
					$udtf->setStatus( 30 ); //Absence
					$udtf->setType( 10 ); //Total
					$udtf->setAbsencePolicyID( $udt_data['absence_policy_id'] ); //Total
					if ( isset($udt_data['branch_id']) ) {
						$udtf->setBranch($udt_data['branch_id']);
					}
					if ( isset($udt_data['department_id']) ) {
						$udtf->setDepartment($udt_data['department_id']);
					}
					if ( isset($udt_data['job_id']) ) {
						$udtf->setJob($udt_data['job_id']);
					}
					if ( isset($udt_data['job_item_id']) ) {
						$udtf->setJobItem($udt_data['job_item_id']);
					}
					$udtf->setTotalTime($udt_data['total_time']);
					if ( isset($udt_data['override']) ) {
						$udtf->setOverride(TRUE);
					} else {
						$udtf->setOverride(FALSE);
					}

					//print_r($udt_data['override']);

					if ( $udtf->isValid() ) {
						//FIXME: In some cases TimeSheet Not Verified exceptions are enabled, and an employee has no time on their timesheet
						//and absences are entered, we need to recalculate exceptions on the last day of the pay period to trigger the V1 exception.
						$udtf->setEnableCalcSystemTotalTime(TRUE);
						$udtf->setEnableCalcWeeklySystemTotalTime( TRUE );
						$udtf->setEnableCalcException( TRUE );

						$aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
						$aluerlf->getByUserDateId($udt_data['user_date_id']);
						$LeaveRecId = '';
						if($aluerlf->getCurrent()->getId()){
							$LeaveRecId = $aluerlf->getCurrent()->getId();
						}

						$aluerf = new AbsenceLeaveUserEntryRecordFactory();

						$aluerf->setId($LeaveRecId);
						$aluerf->setUserId($udt_data['user_id']);
						$aluerf->setAbsencePolicyId($udt_data['absence_policy_id']);
						$aluerf->setAbsenceLeaveId($udt_data['absence_leave_id']);
						$aluerf->setUserDateId($udtf->getUserDateId()); 
						$aluerf->setTimeStamp($date_stamp);
						$aluerf->setAmount($udt_data['total_time']);

						$aluerf->Save(); 
						if ( $udtf->Save() != TRUE ) {
							$fail_transaction = TRUE;
							break;
						}
					} else {
						$fail_transaction = TRUE;
						break;
					}
				}

				if ( $fail_transaction == FALSE ) {
					//$udtf->FailTransaction();
					$udtf->CommitTransaction();

				//	print_r($udt_data);

					Redirect::Page( URLBuilder::getURL( array('refresh' => TRUE ), '../CloseWindow.php') );
					break;
				} else {
					$udtf->FailTransaction();
				}
			default:
				/*

				Don't allow editing System time. If they want to force a bank time
				they can just add that to the accrual, and either set a time pair to 0
				or enter a Absense Dock (only for salary) employees.

				However when you do a Absense dock, what hours is it docking from,
				total, regular,overtime?

				*/
				if ( $id != '' ) {
					Debug::Text(' ID was passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

					$udtlf = new UserDateTotalListFactory();
					$udtlf->getById( $id );

								
								$aluerlf = new AbsenceLeaveUserEntryRecordListFactory();
							
					foreach ($udtlf as $udt_obj) {
						$udtlf->data = (array)$udt_obj;
						$udt_obj = $udtlf;
						//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
									
						$user_id = $udt_obj->getUserDateObject()->getUser();
										
										
										$aluerlf->getByAbsencePolicyIdAndUserIdUserDateId($udt_obj->getAbsencePolicyID(),$user_id, $udt_obj->getUserDateId());
										$absence_leave_id = $aluerlf->getCurrent()->getAbsenceLeaveId(); 
										
						$udt_data = array(
										'id' => $udt_obj->getId(),
										'user_date_id' => $udt_obj->getUserDateId(),
										'date_stamp' => $udt_obj->getUserDateObject()->getDateStamp(),
										'user_id' => $udt_obj->getUserDateObject()->getUser(),
										'user_full_name' => $udt_obj->getUserDateObject()->getUserObject()->getFullName(),
										'status_id' => $udt_obj->getStatus(),
										'type_id' => $udt_obj->getType(),
										'total_time' => $udt_obj->getTotalTime(),
										'absence_policy_id' => $udt_obj->getAbsencePolicyID(),
										'absence_leave_id' => $absence_leave_id,
										'branch_id' => $udt_obj->getBranch(),
										'department_id' => $udt_obj->getDepartment(),
										'job_id' => $udt_obj->getJob(),
										'job_item_id' => $udt_obj->getJobItem(),
										'override' => $udt_obj->getOverride(),
										'created_date' => $udt_obj->getCreatedDate(),
										'created_by' => $udt_obj->getCreatedBy(),
										'updated_date' => $udt_obj->getUpdatedDate(),
										'updated_by' => $udt_obj->getUpdatedBy(),
										'deleted_date' => $udt_obj->getDeletedDate(),
										'deleted_by' => $udt_obj->getDeletedBy()
									);
					}
				} elseif ( $action != 'submit' ) {
					Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);

					//Get user full name
					$ulf = new UserListFactory(); 
					$user_obj = $ulf->getById( $user_id )->getCurrent();
					$user_date_id = UserDateFactory::getUserDateID($user_id, $date_stamp);
				
					$udt_data = array(
									'user_id' => $user_id,
									'date_stamp' => $date_stamp,
									'user_date_id' => $user_date_id,
									'user_full_name' => $user_obj->getFullName(),
									'branch_id' => $user_obj->getDefaultBranch(),
									'department_id' => $user_obj->getDefaultDepartment(),
									'total_time' => 28800,
									'override' => TRUE
								);
				}

				$aplf = new AbsencePolicyListFactory();
				$absence_policy_options = Misc::prependArray( array( 0 => _('--') ), $aplf->getByCompanyIdArray( $current_company->getId() ) );
				
				$blf = new BranchListFactory();
				$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

				$dlf = new DepartmentListFactory();
				$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

				/*
				if ( $current_company->getProductEdition() == 20 ) {
					$jlf = new JobListFactory();
					$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(), $user_id, array(10,20,30,40) );
					$udt_data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
					$udt_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

					$jilf = new JobItemListFactory();
					$jilf->getByCompanyId( $current_company->getId() );
					$udt_data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE, TRUE );
					$udt_data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
				}
				*/

				//Select box options;
				//$udt_data['status_options'] = $udtf->getOptions('status');
				//$udt_data['type_options'] = $udtf->getOptions('type');
				$udt_data['absence_policy_options'] = $absence_policy_options;
				$udt_data['branch_options'] = $branch_options;
				$udt_data['department_options'] = $department_options;
						
				$allf = new AbsenceLeaveListFactory(); 
				$udt_data['leave_day_options'] = $allf->getAllByIdArray(FALSE); //FL ADDED FOR LEAVE DAY OPTION

				$viewData['udt_data'] = $udt_data;

				//echo '<pre>'; print_r($udt_data); //die;

				break;
		}

		$viewData['udtf'] = $udtf;

		return view('punch/EditUserAbsence', $viewData);
	}
}

?>