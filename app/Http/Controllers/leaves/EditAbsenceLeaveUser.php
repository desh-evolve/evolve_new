<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5459 $
 * $Id: EditCompanyDeduction.php 5459 2011-11-04 21:40:55Z ipso $
 * $Date: 2011-11-04 14:40:55 -0700 (Fri, 04 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('leaves','enabled')
		OR !( $permission->Check('leaves','edit') OR $permission->Check('leaves','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Leaves')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );


if ( isset($data)) {
	if ( $data['start_date'] != '' ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( $data['end_date'] != '' ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	}
}

$aluf = new AbsenceLeaveUserFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);
        
                $leaveTypeId = '1'; //Hard Coded to absence type id to 1 [absence_leave Table]
                    
                    $allf = new AbsenceLeaveListFactory();
                    $leaveType = $allf->getById($leaveTypeId)->getCurrent();
                    $leaveAmount = $data['amount']*$leaveType->getTimeSec();
                    
                    $aluf = new AbsenceLeaveUserFactory();
                    $aluf->setId($data['id']);
    //                            $aluf->setUserId($user );
//                    $aluf->setCompany( $current_company->getId() );
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
//                                        echo '<pre>';   print_r($aluf->data);  echo '<pre>';   die;
                        $aluf->Save(FALSE);  
                        $aluf->CommitTransaction(); 
                        
                        $aluelf = new AbsenceLeaveUserEntryListFactory();
                        $aluelf->getByAbsenceUserId($aluf->getId());
                        
                        //update to  deleted all users
//                        count($aluelf); die;
                        if($aluelf->rs->_numOfRows > 0){
                            
                            foreach ($aluelf as $aluef_obj){
//                                                        echo '<pre>'; print_r($aluef_obj); echo '<pre>'; die;
                                
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
    //                        echo '<pre>';   print_r($user_obj);  echo '<pre>';   die;
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
                                
//                                echo $aUser;die;
                                $aluef->setId( $aUser[$user]['id'] );

                                $aluef->setUserId( $user );
                                $aluef->setAbsenceLeaveUserId( $aluf->getId() );
                                
//                                echo '<pre>'; print_r( $aluef); echo '<pre>'; die;

                                    if ( $aluef->isValid() ) { 
//                                        echo $aluef->getId().' <bre>';
                                        $aluef->StartTransaction();   
                                        $aluef->Save(FALSE);
                                        $aluef->CommitTransaction(); 
//                                        echo 'aaaaokokoko';die;


                                    }else{
    //                                    echo 'nonon';
                                    }
                            }else{

                            }

    //                        echo '<pre>';                        print_r($aluef);echo '<pre>';
    //                        echo '<br>Basis  - '.$user_obj->getBasisOfEmployment().' = '.$data['basis_employment'];
    //                        echo '<br>worked  - '.$u_worked;
    //                        echo '<br>hiredate  - '.date('Y-m-d',$user_obj->getHireDate());
    //                        echo '<br> Min '.$data['minimum_length_of_service'].' - '.$minLength;
    //                        echo '<br>';
    //                        var_dump($minLengthStatus);
    //                        echo '<br>';
    //                        var_dump($maxLengthStatus);
    //                        echo '<br>';
    //                        echo '<br> Max '.$data['maximum_length_of_service'].' - '.$maxLength;
    //                        die;


                        }                     
                    }else{
//                                            echo '<pre>';   print_r($aluf);  echo '<pre>';   die;
                                        }
                    Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList.php') );

                }
                      break;
                
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$cdlf = new AbsenceLeaveUserListFactory();
			$cdlf->getById( $id );
                         $leaveTypeId = '1'; //hardcoded
			foreach ($cdlf as $cd_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
                            
                            $allf = new AbsenceLeaveListFactory();
                            $leaveType = $allf->getById($leaveTypeId)->getCurrent();
                            $leaveAmount = $cd_obj->getAmount()/$leaveType->getTimeSec();
                            
                            $user_list = array();
                            foreach ($cd_obj->getUser() as $arr){
                                $user_list[] = $arr['user_id'];
                            }
//                            echo '<pre>';   print_r($user_list);  echo '<pre>';   die;

				$data = array(
									'id' => $cd_obj->getId(),
//									'company_id' => $cd_obj->getCompany(),
									'status_id' => $cd_obj->getStatus(), 
									'name' => $cd_obj->getName(),
									'absence_policy_id' => $cd_obj->getAbsencePolicyId(),
									'amount' => $leaveAmount,
									'leave_date_year' => $cd_obj->getLeaveDateYear(),
									'basis_employment' => $cd_obj->getBasisEmployment(),
									'leave_applicable' => $cd_obj->getLeaveApplicable(),

//									'start_date' => $cd_obj->getStartDate(),
//									'end_date' => $cd_obj->getEndDate(),

									'minimum_length_of_service' => $cd_obj->getMinimumLengthOfService(),
									'minimum_length_of_service_unit_id' => $cd_obj->getMinimumLengthOfServiceUnit(),
									'maximum_length_of_service' => $cd_obj->getMaximumLengthOfService(),
									'maximum_length_of_service_unit_id' => $cd_obj->getMaximumLengthOfServiceUnit(),
//									'minimum_user_age' => $cd_obj->getMinimumUserAge(),
//									'maximum_user_age' => $cd_obj->getMaximumUserAge(),

									'user_ids' => $user_list,

									'created_date' => $cd_obj->getCreatedDate(),
									'created_by' => $cd_obj->getCreatedBy(),
									'updated_date' => $cd_obj->getUpdatedDate(),
									'updated_by' => $cd_obj->getUpdatedBy(),
									'deleted_date' => $cd_obj->getDeletedDate(),
									'deleted_by' => $cd_obj->getDeletedBy()
								);
//                                                                echo '<pre>';print_r($data); echo '<pre>';die;
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
             
                
		
		break;
}
//Select box options;
                $aplf = new AbsencePolicyListFactory();
		$absence_policy_options = Misc::prependArray( array( 0 => TTi18n::gettext('-- Please Choose --') ), $aplf->getByCompanyIdArray( $current_company->getId() ) );
        
		$data['status_options'] = $aluf->getOptions('status');
		$data['type_options'] = $aluf->getOptions('type');
		$data['length_of_service_unit_options'] = $aluf->getOptions('length_of_service_unit');
		$data['account_amount_type_options'] = $aluf->getOptions('account_amount_type');
		$data['absence_policy_options'] = $absence_policy_options;
		$data['basis_employment_options'] = $aluf->getOptions('basis_employment');
		$data['leave_applicable_options'] = $aluf->getOptions('leave_applicable');

        
		//var_dump($data);

		//Employee Selection Options
		$data['user_options'] = UserListFactory::getByCompanyIdArrayWithEPFNo( $current_company->getId(), FALSE, TRUE );
		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = UserListFactory::getByCompanyIdArrayWithEPFNo( $current_company->getId(), FALSE, TRUE );
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id, $tmp_user_options);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

$smarty->assign_by_ref('cdf', $aluf);

$smarty->display('leaves/EditAbsenceLeaveUser.tpl');
?>