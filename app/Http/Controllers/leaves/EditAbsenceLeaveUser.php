<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\Debug;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Redirect;
use App\Models\Core\TTDate;
use App\Models\Core\URLBuilder;
use App\Models\Leaves\AbsenceLeaveListFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryFactory;
use App\Models\Leaves\AbsenceLeaveUserEntryListFactory;
use App\Models\Leaves\AbsenceLeaveUserFactory;
use App\Models\Leaves\AbsenceLeaveUserListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Users\UserListFactory;
use Illuminate\Support\Facades\View;

class EditAbsenceLeaveUser extends Controller
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

        /*
        if ( !$permission->Check('leaves','enabled')
                OR !( $permission->Check('leaves','edit') OR $permission->Check('leaves','edit_own') ) ) {
            $permission->Redirect( FALSE ); //Redirect
        }
        */

        $viewData['title'] = 'Edit Employee Leaves';

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
        }
        
        $aluf = new AbsenceLeaveUserFactory();

        if ( isset($id) ) {
			$cdlf = new AbsenceLeaveUserListFactory();
			$cdlf->getById( $id );
                         $leaveTypeId = '1'; //hardcoded
			foreach ($cdlf->rs as $cd_obj) {
                $cdlf->data = (array)$cd_obj;
                $cd_obj = $cdlf;
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
                            
                            $allf = new AbsenceLeaveListFactory();
                            $leaveType = $allf->getById($leaveTypeId)->getCurrent();
                            $leaveAmount = $cd_obj->getAmount()/$leaveType->getTimeSec();
                            
                            $user_list = array();
                            foreach ($cd_obj->getUser() as $arr){
                                $user_list[] = $arr['user_id'];
                            }

				$data = array(
									'id' => $cd_obj->getId(),
								    'status_id' => $cd_obj->getStatus(), 
									'name' => $cd_obj->getName(),
									'absence_policy_id' => $cd_obj->getAbsencePolicyId(),
									'amount' => $leaveAmount,
									'leave_date_year' => $cd_obj->getLeaveDateYear(),
									'basis_employment' => $cd_obj->getBasisEmployment(),
									'leave_applicable' => $cd_obj->getLeaveApplicable(),
									'minimum_length_of_service' => $cd_obj->getMinimumLengthOfService(),
									'minimum_length_of_service_unit_id' => $cd_obj->getMinimumLengthOfServiceUnit(),
									'maximum_length_of_service' => $cd_obj->getMaximumLengthOfService(),
									'maximum_length_of_service_unit_id' => $cd_obj->getMaximumLengthOfServiceUnit(),

									'user_ids' => $user_list,

									'created_date' => $cd_obj->getCreatedDate(),
									'created_by' => $cd_obj->getCreatedBy(),
									'updated_date' => $cd_obj->getUpdatedDate(),
									'updated_by' => $cd_obj->getUpdatedBy(),
									'deleted_date' => $cd_obj->getDeletedDate(),
									'deleted_by' => $cd_obj->getDeletedBy()
								);
			}                        

		} elseif ( $action != 'submit' ) {
			$data = array(
						'country' => 0,
						'province' => 0,
						'district' => 0,
						'user_value1' => 0,
						'user_value2' => 0,
						'user_value3' => 0,
						'user_value4' => 0,
						'user_value5' => 0,
						'user_value6' => 0,
						'user_value7' => 0,
						'user_value8' => 0,
						'user_value9' => 0,
						'user_value10' => 0,
						'minimum_length_of_service' => 0,
						'maximum_length_of_service' => 0,
						'minimum_user_age' => 0,
						'maximum_user_age' => 0,
						'calculation_order' => 100,
						);
		}
             
        
        $aplf = new AbsencePolicyListFactory();
		$absence_policy_options = Misc::prependArray( array( 0 => _('-- Please Choose --') ), $aplf->getByCompanyIdArray( $current_company->getId() ) );
        
		$data['status_options'] = $aluf->getOptions('status');
		$data['type_options'] = $aluf->getOptions('type');
		$data['length_of_service_unit_options'] = $aluf->getOptions('length_of_service_unit');
		$data['account_amount_type_options'] = $aluf->getOptions('account_amount_type');
		$data['absence_policy_options'] = $absence_policy_options;
		$data['basis_employment_options'] = $aluf->getOptions('basis_employment');
		$data['leave_applicable_options'] = $aluf->getOptions('leave_applicable');

        
		//var_dump($data);

		//Employee Selection Options
        $ulf = new UserListFactory();
		$data['user_options'] = $ulf->getByCompanyIdArrayWithEPFNo( $current_company->getId(), FALSE, TRUE );
		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $ulf->getByCompanyIdArrayWithEPFNo( $current_company->getId(), FALSE, TRUE );
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id, $tmp_user_options);
		}

        $viewData['filter_user_options'] = $filter_user_options;
        $viewData['data'] = $data;
        $viewData['cdf'] = $cdf;

        return view('leaves/EditAbsenceLeaveUser', $viewData);

    }

    public function submit(){
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
        }
        
        $aluf = new AbsenceLeaveUserFactory();

        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);
        
                $leaveTypeId = '1'; //Hard Coded to absence type id to 1 [absence_leave Table]
                    
                    $allf = new AbsenceLeaveListFactory();
                    $leaveType = $allf->getById($leaveTypeId)->getCurrent();
                    $leaveAmount = $data['amount']*$leaveType->getTimeSec();
                    
                    $aluf = new AbsenceLeaveUserFactory();
                    $aluf->setId($data['id']);
                    $aluf->setStatus( $data['status_id'] );
                    $aluf->setAbsencePolicyId( $data['absence_policy_id'] );
                    $aluf->setName( $data['name'] );
                    $aluf->setAmount( $leaveAmount );
                    $aluf->setLeaveDateYear( $data['leave_date_year'] );
                    $aluf->setBasisEmployment( $data['basis_employment'] );
                    $aluf->setLeaveApplicable( $data['leave_applicable'] );
                    $aluf->setAbsenceLeaveId($leaveTypeId); //1 for leave type in day [absence_leave table]

                    if ( isset($data['minimum_length_of_service']) ) {
                            $aluf->setMinimumLengthOfService( $data['minimum_length_of_service'] );
                            $aluf->setMinimumLengthOfServiceUnit( $data['minimum_length_of_service_unit_id'] );
                    }
                    if ( isset($data['maximum_length_of_service']) ) {
                            $aluf->setMaximumLengthOfService( $data['maximum_length_of_service'] );
                            $aluf->setMaximumLengthOfServiceUnit( $data['maximum_length_of_service_unit_id'] );
                    }
                    
                    $aluf->StartTransaction();
                    if ( $aluf->isValid() ) { 
                        $aluf->Save(FALSE);  
                        $aluf->CommitTransaction(); 
                        
                        $aluelf = new AbsenceLeaveUserEntryListFactory();
                        $aluelf->getByAbsenceUserId($aluf->getId());
                        
                        if($aluelf->rs->_numOfRows > 0){
                            
                            foreach ($aluelf->rs as $aluef_obj){
                                $aluelf->data = (array)$aluef_obj;
                                $aluef_obj = $aluelf;
                                
                                $aluef_obj->StartTransaction();
                                $aluef_obj->Delete();
        
                                $aluef_obj->CommitTransaction(); 

                            }
                        }
                        
                   if(count($data['user_ids']) > 0){
                     
                        foreach ($data['user_ids'] as $user){
                            $uf = new UserListFactory();
                            $uf->getById($user);
                            $user_obj = $uf->getCurrent();
                            
                            $u_worked = strtotime(date('Y-m-d')) - $user_obj->getHireDate(); 
                            $minLength = $aluf->getLengthServiceToSec($data['minimum_length_of_service'], $data['minimum_length_of_service_unit_id']);
                            $maxLength = $aluf->getLengthServiceToSec($data['maximum_length_of_service'], $data['maximum_length_of_service_unit_id']);

                            if($minLength==0){  
                                $minLengthStatus = TRUE;
                            }else{
                                $minLengthStatus = $u_worked > $minLength;
                            }

                            if($maxLength==0){
                                $maxLengthStatus = TRUE;
                            }else{
                                $maxLengthStatus = $u_worked < $maxLength;
                            }
                            if($user_obj->getBasisOfEmployment() == $data['basis_employment'] && $minLengthStatus && $maxLengthStatus){
                            //users
                                $aUser = $aluf->getUser();
                                $aluef = new AbsenceLeaveUserEntryFactory();
                                
                                $aluef->setId( $aUser[$user]['id'] );

                                $aluef->setUserId( $user );
                                $aluef->setAbsenceLeaveUserId( $aluf->getId() );
                                

                                    if ( $aluef->isValid() ) { 
                                        $aluef->StartTransaction();   
                                        $aluef->Save(FALSE);
                                        $aluef->CommitTransaction(); 
                                    }
                            }
                        }                     
                    }
                    Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList.php') );

                }
    }
}

?>