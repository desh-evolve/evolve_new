<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Payroll Services Copyright (C) 2003 - 2012 TimeTrex Payroll Services.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserWage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */


/*******************************************************************************
 * 
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 * I COPIED THIS CODE FROM PATH:- evolvepayroll\interface\users\EditUserWage.php
 * THIS CODE ADDED BY ME
 * CREATE USERES JOB HISTORY
 * 
 *******************************************************************************/



require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );


if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','personal_date_update_form')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit User Date Update Form')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'saved_search_id',
												'user_date_update_data'
												) ) );

        

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

$ujf = TTnew( 'UserDateUpdateFormFactory' );

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
                            
                                $ujf->setUser($user_obj->getID());
                                $ujf->setYearDate($user_date_update_data['year_date']);
                                $ujf->setEpfNo($user_date_update_data['epf_no']);
                                $ujf->setId($user_date_update_data['id']);
                                $ujf->setTitle($user_date_update_data['title_id']);
                                $ujf->setFullName($user_date_update_data['full_name']);
                                $ujf->setNic($user_date_update_data['nic']);
                                $ujf->setContactMobile($user_date_update_data['contact_mobile']);
                                $ujf->setContactHome($user_date_update_data['contact_home']);
                                $ujf->setPassportNo($user_date_update_data['passport_no']);
                                $ujf->setDrivingLicenceNo($user_date_update_data['driving_licence_no']);
                                $ujf->setPermenentAddress($user_date_update_data['permenent_address']);
                                $ujf->setPresentAddress($user_date_update_data['present_address']);
                                $ujf->setContactPerson($user_date_update_data['contact_person']);
                                $ujf->setAddressContactPerson($user_date_update_data['address_contact_person']);
                                $ujf->setTelContactPerson($user_date_update_data['tel_contact_person']);
                                $ujf->setSpouseName($user_date_update_data['spouse_name']);
                                $ujf->setMaritialStatus($user_date_update_data['maritial_status']);
                                $ujf->setContactSpouse($user_date_update_data['contact_spouse']);
                             
                                //Children three fields concatenate by __ Double Underscore 
                                //child1
                                $child1_str = $user_date_update_data['child1']['name'].'__'.$user_date_update_data['child1']['gender'].'__'.$user_date_update_data['child1']['dob'];
                                $ujf->setChild($child1_str,'1');
                             
                                //child2
                                $child1_str = $user_date_update_data['child2']['name'].'__'.$user_date_update_data['child2']['gender'].'__'.$user_date_update_data['child2']['dob'];
                                $ujf->setChild($child1_str,'2');
                             
                                //child3
                                $child1_str = $user_date_update_data['child3']['name'].'__'.$user_date_update_data['child3']['gender'].'__'.$user_date_update_data['child3']['dob'];
                                $ujf->setChild($child1_str,'3');
                             
                                //child4
                                $child1_str = $user_date_update_data['child4']['name'].'__'.$user_date_update_data['child4']['gender'].'__'.$user_date_update_data['child4']['dob'];
                                $ujf->setChild($child1_str,'4');
                                
//                                    echo '<pre>';  print_r($ujf); echo '<pre>'; die;
				if ( $ujf->isValid() ) {                               

					$ujf->Save();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id), 'UserDateUpdateForm.php') );

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
                        
                        $uwlf = TTnew( 'UserDateUpdateFormListFactory' );
                        
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($uwlf as $wage) {
//                            echo '<pre>'; print_r($wage); echo '<pre>'; die;
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
                                                
                                                //child1 Data
                                                 $child1_arr = explode('__',$wage->getChild('1'));
                                                 $child2_arr = explode('__',$wage->getChild('2'));
                                                 $child3_arr = explode('__',$wage->getChild('3'));
                                                 $child4_arr = explode('__',$wage->getChild('4'));
//                                                                             echo '<pre>'; print_r($child1_arr); echo '<pre>'; die;

						//Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$user_date_update_data = array(       
                                                    
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),                                                                                         
											'year_date' => $wage->getYearDate(),
											'epf_no' => $wage->getEpfNo(),
											'full_name' => $wage->getFullName(),
                                                                                        'title_id' => $wage->getTitle(),                                                                                               
                                                                                        'nic' => $wage->getNic(),                                                                                               
                                                                                        'contact_home' => $wage->getContactHome(),                                                                                               
											'contact_mobile' => $wage->getContactMobile(),
											'passport_no' => $wage->getPassportNo(),
											'driving_licence_no' => $wage->getDrivingLicenseNo(),
											'permenent_address' => $wage->getPermenentAddress(),
											'present_address' => $wage->getPresentAddress(),
											'contact_person' => $wage->getContactPerson(),
											'address_contact_person' => $wage->getAddressContactPerson(),
											'tel_contact_person' => $wage->getTelContactPerson(),
											'maritial_status' => $wage->getMaritialStatus(),
											'spouse_name' => $wage->getSpouseName(),
											'contact_spouse' => $wage->getContactSpouse(),
											'child1' => array(name=>$child1_arr[0],'gender'=>$child1_arr[1],'dob'=>$child1_arr[2]),
											'child2' => array(name=>$child2_arr[0],'gender'=>$child2_arr[1],'dob'=>$child2_arr[2]),
											'child3' => array(name=>$child3_arr[0],'gender'=>$child3_arr[1],'dob'=>$child3_arr[2]),
											'child4' => array(name=>$child4_arr[0],'gender'=>$child4_arr[1],'dob'=>$child4_arr[2]),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);
//                                                                                echo '<pre>'; print_r($user_date_update_data[child1][name]); echo '<pre>'; die;

                                                //print_r($wage_data);
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
                            
                            
                            //ARSP NOTE --> I MODIFIED THISC CODE
                            $user_date_update_data = array( 'first_worked_date' => TTDate::getTime(), 'default_branch_id' => $temp_default_branch_id, 'default_department_id' => $temp_default_department_id, 'title_id' => $temp_title_id );
			}
		}
                
//                var_dump($user_date_update_data); die;
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
		//Select box options;
		$blf = TTnew( 'BranchListFactory' );
		$user_date_update_data['branch_options'] = $blf->getByCompanyIdArray( $current_company->getId() );
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$dlf = TTnew( 'DepartmentListFactory' );
		$user_date_update_data['department_options'] = $dlf->getByCompanyIdArray( $current_company->getId() );  
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON 
                //Select box options;
		$utlf = TTnew( 'UserTitleListFactory' );
		$user_date_update_data['title_options'] = $utlf->getByCompanyIdArray( $current_company->getId() );

                
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();

                
		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = TTi18n::gettext('(Appointment Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => TTi18n::gettext('(Choose Date)')), $pay_period_boundary_dates);

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('user_date_update_data', $user_date_update_data);

		$smarty->assign_by_ref('tmp_effective_date', $tmp_effective_date);
		$smarty->assign_by_ref('pay_period_boundary_date_options', $pay_period_boundary_dates);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);


		break;
}

$smarty->assign_by_ref('ujf', $ujf);

$smarty->display('users/EditUserDateUpdateForm.tpl');
?>