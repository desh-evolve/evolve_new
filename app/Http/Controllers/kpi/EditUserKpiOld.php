<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserWage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */


/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * I COPIED SOME THIS CODE FROM PATH:- evolvepayroll\interface\users\EditUserWage.php
 * THIS CODE ADDED BY ME
 * CREATE USERES KPI
 * 
 *******************************************************************************/



require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Key Performance Indicator')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'saved_search_id',
												'kpi_data'
												) ) );

//ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON KPI
if ( isset($kpi_data) ) {
	if ( $kpi_data['review_date'] != '' ) {
		$kpi_data['review_date'] = TTDate::parseDateTime($kpi_data['review_date']);
	}
}


//ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON KPI
if ( isset($kpi_data) ) {
	if ( $kpi_data['start_date'] != '' ) {
		$kpi_data['start_date'] = TTDate::parseDateTime($kpi_data['start_date']);
	}
}


//ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON KPI
if ( isset($kpi_data) ) {
	if ( $kpi_data['end_date'] != '' ) {
		$kpi_data['end_date'] = TTDate::parseDateTime($kpi_data['end_date']);
	}
}


//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

// ARSP HIDE THIS CODE FOR TESTING PURPOSE
//$uwf = TTnew( 'UserWageFactory' );
//$ujf = TTnew( 'UserJobFactory' );
$ujf = TTnew( 'UserKpiFactory' );

$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ulf->getByIdAndCompanyId($user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();

			$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
			$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
			if ( $permission->Check('wage','edit')
					OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
					OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {
                            
                            
				$ujf->setId($kpi_data['id']);
				$ujf->setUser($user_id);
                                
                                
                                $ujf->setDefaultBranch($kpi_data['default_branch_id']);
                                $ujf->setDefaultDepartment($kpi_data['default_department_id']);
                                $ujf->setTitle($kpi_data['title_id']);
                                
                                
                                if ( isset($kpi_data['start_date']) ) {                                    
                                    $ujf->setStartDate($kpi_data['start_date']);
                                }   
                                
                                if ( isset($kpi_data['end_date']) ) {                                    
                                    $ujf->setEndDate($kpi_data['end_date']);
                                }
                                
                                if ( isset($kpi_data['review_date']) ) {                                    
                                    $ujf->setReviewDate($kpi_data['review_date']);
                                }   
                                
                                //Score A 1-12
                                if ( isset($kpi_data['scorea1']) ) {                                    
                                    $ujf->setScoreA1($kpi_data['scorea1']);
                                }
                                
                                if ( isset($kpi_data['scorea2']) ) {                                    
                                    $ujf->setScoreA2($kpi_data['scorea2']);
                                } 
                                
                                if ( isset($kpi_data['scorea3']) ) {                                    
                                    $ujf->setScoreA3($kpi_data['scorea3']);
                                } 
                                
                                if ( isset($kpi_data['scorea4']) ) {                                    
                                    $ujf->setScoreA4($kpi_data['scorea4']);
                                } 
                                
                                if ( isset($kpi_data['scorea5']) ) {                                    
                                    $ujf->setScoreA5($kpi_data['scorea5']);
                                } 
                                
                                if ( isset($kpi_data['scorea6']) ) {                                    
                                    $ujf->setScoreA6($kpi_data['scorea6']);
                                } 
                                
                                if ( isset($kpi_data['scorea7']) ) {                                    
                                    $ujf->setScoreA7($kpi_data['scorea7']);
                                }                                 
                                
                                if ( isset($kpi_data['scorea8']) ) {                                    
                                    $ujf->setScoreA8($kpi_data['scorea8']);
                                }     
                                
                                if ( isset($kpi_data['scorea9']) ) {                                    
                                    $ujf->setScoreA9($kpi_data['scorea9']);
                                }     
                                
                                if ( isset($kpi_data['scorea10']) ) {                                    
                                    $ujf->setScoreA10($kpi_data['scorea10']);
                                }     
                               
                                                 
                               if ( isset($kpi_data['avg_key_peformance']) ) {                                    
                                    $ujf->setAvarageKeyPerfomance($kpi_data['avg_key_peformance']);
                                } 
                                
                                
                               if ( isset($kpi_data['total_score_genaral']) ) {                                    
                                    $ujf->setTotalScoreGenaral($kpi_data['total_score_genaral']);
                                } 
                               
                                if ( isset($kpi_data['total_score']) ) {        
//                                    echo "This is ARSP test";
//                                    echo $kpi_data['total_score'];
//                                    exit();
                                    $ujf->setTotalScore($kpi_data['total_score']);
                                }                    
                                
                                //----------------Remarks-----------------------
                                
                                //Remark A 1-12
                                if ( isset($kpi_data['remarka1']) ) {                                    
                                    $ujf->setRemarkA1($kpi_data['remarka1']);
                                }     
                                
                                if ( isset($kpi_data['remarka2']) ) {                                    
                                    $ujf->setRemarkA2($kpi_data['remarka2']);
                                }   
                                
                                if ( isset($kpi_data['remarka3']) ) {                                    
                                    $ujf->setRemarkA3($kpi_data['remarka3']);
                                }   
                                
                                if ( isset($kpi_data['remarka4']) ) {                                    
                                    $ujf->setRemarkA4($kpi_data['remarka4']);
                                }                                   
                                
                                if ( isset($kpi_data['remarka5']) ) {                                    
                                    $ujf->setRemarkA5($kpi_data['remarka5']);
                                }     
                                
                                if ( isset($kpi_data['remarka6']) ) {                                    
                                    $ujf->setRemarkA6($kpi_data['remarka6']);
                                }   
                                
                                if ( isset($kpi_data['remarka7']) ) {                                    
                                    $ujf->setRemarkA7($kpi_data['remarka7']);
                                }   
                                
                                if ( isset($kpi_data['remarka8']) ) {                                    
                                    $ujf->setRemarkA8($kpi_data['remarka8']);
                                }    
                                
                                if ( isset($kpi_data['remarka9']) ) {                                    
                                    $ujf->setRemarkA9($kpi_data['remarka9']);
                                }     
                                
                                if ( isset($kpi_data['remarka10']) ) {                                    
                                    $ujf->setRemarkA10($kpi_data['remarka10']);
                                }   
                                
                                                                 
                                
//                                print_r($wage_data);
//                                exit();
				if ( $ujf->isValid() ) {
					$ujf->Save();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id), '../kpi/KpiUserList.php') );

					break;
				}
			} else {
				$permission->Redirect( FALSE ); //Redirect
				exit;
			}
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

                        //ARSP NOTE --> I HIDE THIS CODE FOR TESTING			
                        //$uwlf = TTnew( 'UserJobListFactory' );
                        $uwlf = TTnew( 'UserKpiListFactory' );
                        
                        
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($uwlf as $wage) {
                            //echo $wage->getUser();
                            //echo $wage;
				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();
                                //print_r($user_obj);
				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
                                        //echo $is_owner;
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );
                                        //echo $is_owner;

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						//Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$kpi_data = array(       
                                                    
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_branch_id' => $wage->getDefaultBranch(),     
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'default_department_id' => $wage->getDefaultDepartment(), 
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'title_id' => $wage->getTitle(),                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'start_date' => $wage->getStartDate(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'end_date' => $wage->getEndDate(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'review_date' => $wage->getReviewDate(),
                                                                                        
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea1' => $wage->getScoreA1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea2' => $wage->getScoreA2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea3' => $wage->getScoreA3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea4' => $wage->getScoreA4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea5' => $wage->getScoreA5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea6' => $wage->getScoreA6(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea7' => $wage->getScoreA7(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea8' => $wage->getScoreA8(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea9' => $wage->getScoreA9(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea10' => $wage->getScoreA10(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea11' => $wage->getScoreA11(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorea12' => $wage->getScoreA12(),
                                  
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb1' => $wage->getScoreB1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb2' => $wage->getScoreB2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb3' => $wage->getScoreB3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb4' => $wage->getScoreB4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb5' => $wage->getScoreB5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scoreb6' => $wage->getScoreB6(),
                                                    
                                                                                                
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec1' => $wage->getScoreC1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec2' => $wage->getScoreC2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec3' => $wage->getScoreC3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec4' => $wage->getScoreC4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec5' => $wage->getScoreC5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scorec6' => $wage->getScoreC6(),
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored1' => $wage->getScoreD1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored2' => $wage->getScoreD2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored3' => $wage->getScoreD3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored4' => $wage->getScoreD4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored5' => $wage->getScoreD5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'scored6' => $wage->getScoreD6(),
                                                    
                                                    
                                                                                        //Remarks
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka1' => $wage->getRemarkA1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka2' => $wage->getRemarkA2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka3' => $wage->getRemarkA3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka4' => $wage->getRemarkA4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka5' => $wage->getRemarkA5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka6' => $wage->getRemarkA6(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka7' => $wage->getRemarkA7(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka8' => $wage->getRemarkA8(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka9' => $wage->getRemarkA9(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka10' => $wage->getRemarkA10(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka11' => $wage->getRemarkA11(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarka12' => $wage->getRemarkA12(),
                                  
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb1' => $wage->getRemarkB1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb2' => $wage->getRemarkB2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb3' => $wage->getRemarkB3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb4' => $wage->getRemarkB4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb5' => $wage->getRemarkB5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkb6' => $wage->getRemarkB6(),
                                                    
                                                                                                
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc1' => $wage->getRemarkC1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc2' => $wage->getRemarkC2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc3' => $wage->getRemarkC3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc4' => $wage->getRemarkC4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc5' => $wage->getRemarkC5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkc6' => $wage->getRemarkC6(),
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd1' => $wage->getRemarkD1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd2' => $wage->getRemarkD2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd3' => $wage->getRemarkD3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd4' => $wage->getRemarkD4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd5' => $wage->getRemarkD5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'remarkd6' => $wage->getRemarkD6(),
                                                    
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback1' => $wage->getFeedback1(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback2' => $wage->getFeedback2(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback3' => $wage->getFeedback3(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback4' => $wage->getFeedback4(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback5' => $wage->getFeedback5(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback6' => $wage->getFeedback6(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback7' => $wage->getFeedback7(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'feedback8' => $wage->getFeedback8(),
                                                    
                                                                                        'total_score_genaral' => $wage->getTotalScoreGenaral(),
                                                                                        'avg_key_peformance' => $wage->getAvarageKeyPerfomance(),
                               /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */ 'total_score' => $wage->getTotalScore(),
                                                    
											//'note' => $wage->getNote(), setAvarageKeyPerfomance
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);
                                                //print_r($wage_data);
                                                    
                                                //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON 
						//$tmp_effective_date = TTDate::getDate('DATE', $wage->getEffectiveDate() );
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {
			if ( $action != 'submit' ) {                        
                            
                            $ulf = TTnew( 'UserListFactory' );
                            $temp_default_branch_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultBranch();                            
                            $temp_default_department_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getDefaultDepartment();
                            $temp_title_id  = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent()->getTitle();
                            
                            
                            //ARSP NOTE --> I MODIFIED THIS CODE
                            //$kpi_data = array( 'first_worked_date' => TTDate::getTime(), 'default_branch_id' => $temp_default_branch_id, 'default_department_id' => $temp_default_department_id, 'title_id' => $temp_title_id );
                            $kpi_data = array( 'review_date' => TTDate::getTime(), 'default_branch_id' => $temp_default_branch_id, 'default_department_id' => $temp_default_department_id, 'title_id' => $temp_title_id );
			}
		}
                
                //ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON 
		//Select box options;
		//$wage_data['type_options'] = $uwf->getOptions('type'); 
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
		//Select box options;
		$blf = TTnew( 'BranchListFactory' );
		$kpi_data['branch_options'] = $blf->getByCompanyIdArray( $current_company->getId() );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$dlf = TTnew( 'DepartmentListFactory' );
		$kpi_data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );  
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$utlf = TTnew( 'UserTitleListFactory' );
		$kpi_data['title_options'] = $utlf->getByCompanyIdArray( $current_company->getId() );
		//$wage_data['title_options'] = $user_titles;                

                
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();

                
		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		//ARSP NOTE --> I HIDE FOR KPI-- $pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = TTi18n::gettext('(Hire Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		//ARSP NOTE --> I HIDE FOR KPI-- $pay_period_boundary_dates = Misc::prependArray( array(-1 => TTi18n::gettext('(Choose Date)')), $pay_period_boundary_dates);

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('kpi_data', $kpi_data);

		//$smarty->assign_by_ref('tmp_effective_date', $tmp_effective_date);
		//$smarty->assign_by_ref('pay_period_boundary_date_options', $pay_period_boundary_dates);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);


		break;
}

$smarty->assign_by_ref('ujf', $ujf);

$smarty->display('kpi/EditUserKpiOld.tpl');
?>