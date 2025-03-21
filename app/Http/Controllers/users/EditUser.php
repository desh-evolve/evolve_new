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
 * $Revision: 5453 $
 * $Id: EditUser.php 5453 2011-11-03 20:30:28Z ipso $
 * $Date: 2011-11-03 13:30:28 -0700 (Thu, 03 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//ARSP ADD CODE --> 
require_once('../../word_doc/PHPWord.php');

//ARSP ADD CODE --> NEW CODE FOR DELETE UPLOADED USER FILES
require_once(Environment::getBasePath() .'classes/upload/fileupload.class.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Employee')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_data',
												'saved_search_id',
												'company_id',
												'incomplete',
												'data_saved',
/* ARSP EDIT --> add new code for get DELETE FILE NAME */                   'delete_file_name',
/* ARSP EDIT --> add new code for get DELETE USER ID */                    'delete_user_id',
/* ARSP EDIT --> add new code for get DELETE FILE TYPE */                  'delete_file_type',
/* ARSP EDIT --> add new code for get pROBATION wARNING MESSAGE */         'probation_warning',
/* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON */                   'basis_of_employment_warning'
                                                                                    
												) ) );

/*
Can't switch to free form dates for selecting Birth dates because they come before 1970!! :(
Strtotime sucks too much.
*/



                        

if ( isset($user_data) ) {
    if ( isset($user_data['hire_date']) AND $user_data['hire_date'] != '') {
        $user_data['hire_date'] = TTDate::parseDateTime($user_data['hire_date']);
    }
    if ( isset($user_data['termination_date']) AND $user_data['termination_date'] != '') {
        Debug::Text('Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
        $user_data['termination_date'] = TTDate::parseDateTime($user_data['termination_date']);
    }
    /**
     * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
     */
    if ( isset($user_data['resign_date']) AND $user_data['resign_date'] != '') {
        $user_data['resign_date'] = TTDate::parseDateTime($user_data['resign_date']);
    }  
    
    if ( isset($user_data['confirmed_date']) AND $user_data['confirmed_date'] != '') {
        $user_data['confirmed_date'] = TTDate::parseDateTime($user_data['confirmed_date']);
    }
    
    else {
        Debug::Text('NOT Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
    }

	$user_data['birth_date'] = TTDate::getTimeStampFromSmarty('birth_', $user_data);
}

$ulf = new UserListFactory();
$uf = new UserFactory();
//ARSP  EDIT --> ADDD NEW CODE FOR SALARY (WAGE)
$uwf = new UserWageFactory();

$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId( $current_company->getId(), $current_user->getId() );
//Include current user in list.
if ( $permission->Check('user','edit_own') ) {
	$permission_children_ids[] = $current_user->getId();

        
}
//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

//Debug::Text('aCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();

switch ($action) {
	case 'login':
		if ( $permission->Check('company','view') AND $permission->Check('company','login_other_user') ) {

			Debug::Text('Login as different user: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//Get record for other user so we can check to make sure its not a primary company.
			$ulf = new UserListFactory();
			$ulf->getById( $id );
			if ( $ulf->getRecordCount() > 0 ) {
				if ( isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] != $ulf->getCurrent()->getCompany() ) {
					$authentication->changeObject( $id );

					TTLog::addEntry( $current_user->getID(), 'Login',  _('Switch User').': '. _('SourceIP').': '. $authentication->getIPAddress() .' '. _('SessionID') .': '.$authentication->getSessionID() .' '.  _('UserID').': '. $id, $current_user->getId(), 'authentication');

					Redirect::Page( URLBuilder::getURL( NULL, '../index.php') );
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}
			}
		} else {
			$permission->Redirect( FALSE ); //Redirect
		}
		break;
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		unset($id); //Do this so it doesn't reload the data from the DB.

		//Additional permission checks.
		if ( $permission->Check('company','view') ) {
			$ulf->getById( $user_data['id'] );
		} else {
			$ulf->getByIdAndCompanyId( $user_data['id'], $current_company->getId() );
		}

		if ( $ulf->getRecordCount() > 0 ) {
			$user = $ulf->getCurrent();

			$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getID() );
			$is_child = $permission->isChild( $user->getId(), $permission_children_ids );
			if ( $permission->Check('user','edit')
					OR ( $permission->Check('user','edit_child') AND $is_child === TRUE )
					OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE ) ) {
					// Security measure.
					if ( !empty($user_data['id']) ) {
						if ( $permission->Check('company','view') ) {
                            $uf = $ulf->getById( $user_data['id'] )->getCurrent();
						} else {
                            $uf = $ulf->getByIdAndCompanyId($user_data['id'], $current_company->getId() )->getCurrent();
						}
					}
			} else {
				$permission->Redirect( FALSE ); //Redirect
				exit;
			}
			unset($user);
		}

		if ( isset( $user_data['company_id'] ) ) {
			if ( $permission->Check('company','view') ) {
				$uf->setCompany( $user_data['company_id'] );
			} else {
				$uf->setCompany( $current_company->getId() );
			}
		} else {
			$uf->setCompany( $current_company->getId() );
		}

        //Get New Hire Defaults.
        $udlf = new UserDefaultListFactory();
        $udlf->getByCompanyId( $uf->getCompany() );
        if ( $udlf->getRecordCount() > 0 ) {
            Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
            $udf_obj = $udlf->getCurrent();
        }

		if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			if ( isset( $user_data['status'] ) ) {
				$uf->setStatus( $user_data['status'] );
			}

			if ( isset( $user_data['user_name'] ) ) {
				$uf->setUserName( $user_data['user_name'] );
			}

			//Phone ID is optional now.
			if ( isset( $user_data['phone_id'] ) ) {
				$uf->setPhoneId( $user_data['phone_id'] );
			}
		}

		if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			if ( !empty($user_data['password']) OR !empty($user_data['password2']) ) {
				if ( $user_data['password'] == $user_data['password2'] ) {
					$uf->setPassword($user_data['password']);
				} else {
					$uf->Validator->isTrue(	'password',
											FALSE,
											__('Passwords don\'t match') );
				}
			}

			if ( isset( $user_data['phone_password'] ) ) {
				$uf->setPhonePassword($user_data['phone_password']);
			}
		}

                
                
                
                
                
                
                
                
                
                
                
                
                
		if ( $user_data['id'] != $current_user->getID()
				AND $permission->Check('user','edit_advanced') ) {
			//Don't force them to update all fields.
			//Unless they are editing their OWN user.
                                $uf->setFirstName($user_data['first_name']);

			if ( isset($user_data['middle_name']) ) {
				$uf->setMiddleName($user_data['middle_name']);
			}
                        
                        if ( isset($user_data['full_name']) ) {
				$uf->setFullNameField($user_data['full_name']);
			}
                        
                        if ( isset($user_data['calling_name']) ) {
				 $uf->setCallingName($user_data['calling_name']);
			}
                        
                        
                        if ( isset($user_data['name_with_initials']) ) {
				 $uf->setNameWithInitials($user_data['name_with_initials']);
			}
                        
                       

                         $uf->setLastName($user_data['last_name']);

			if ( isset($user_data['second_last_name']) ){
				$uf->setSecondLastName($user_data['second_last_name']);
			}

			if ( !empty($user_data['title_name']) ) {
				$uf->setNameTitle($user_data['title_name']);
			}
                        
                        
                        if ( !empty($user_data['sex']) ) {
				$uf->setSex($user_data['sex']);
			}
                        
                        
                        
                        
                        if ( !empty($user_data['religion']) ) {
				$uf->setReligion($user_data['religion']);
			}
                        
                        if ( !empty($user_data['marital']) ) {
                            $uf->setMarital($user_data['marital']);
                        }

			if ( isset($user_data['address1']) ) {
				$uf->setAddress1($user_data['address1']);
			}

			if ( isset($user_data['address2']) ) {
				$uf->setAddress2($user_data['address2']);
			}
                        
                        if ( isset($user_data['address3']) ) {
				$uf->setAddress3($user_data['address3']);
			}
                        //ARSP EDIT CODE-----> ADD NEW CODE FOR N.I.C
                        if ( isset($user_data['nic']) ) {
                                            $uf->setNic($user_data['nic']);
                                    }
                                    //ARSP EDIT CODE-----> ADD NEW CODE FOR probation
                        if ( isset($user_data['probation']) ) {
                                            $uf->setProbation($user_data['probation']);
                                    }
                                    
                        /**
                         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                         */                        
                        if ( isset($user_data['basis_of_employment']) ) {
				$uf->setBasisOfEmployment($user_data['basis_of_employment']);
			}
                        
                        /**
                         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                         */                         
                        if ( isset($user_data['month']) ){
				$uf->setMonth($user_data['month']);
			}                                      

                                     //ARSP EDIT CODE-----> ADD NEW CODE FOR EPF registration no
                        if ( isset($user_data['epf_registration_no']) ) {
                                            $uf->setEpfRegistrationNo($user_data['epf_registration_no']);
                                    }      

                         //ARSP EDIT CODE-----> ADD NEW CODE FOR EPF membership no
                         if ( isset($user_data['epf_membership_no']) ) {
                                            $uf->setEpfMembershipNo($user_data['epf_membership_no']);
                                    } 	
			
			
			/**
			 * ARSP NOTE -->
			 * I ADDED THIS CODE FOR THUNDER & NEON
			 */
//                        if ( isset($user_data['employee_number_only']) ) {
//				$uf->setEmployeeNumberOnly($user_data['employee_number_only']);
//			}			

			if ( isset($user_data['city']) ) {
				$uf->setCity($user_data['city']);
			}

			if ( isset($user_data['country']) ) {
				$uf->setCountry($user_data['country']);
			}

			if ( isset($user_data['province']) ) {
				$uf->setProvince($user_data['province']);
			}

			if ( isset($user_data['postal_code']) ) {
				$uf->setPostalCode($user_data['postal_code']);
			}

			if ( isset($user_data['work_phone']) ) {
				$uf->setWorkPhone($user_data['work_phone']);
			}

			if ( isset($user_data['work_phone_ext']) ) {
				$uf->setWorkPhoneExt($user_data['work_phone_ext']);
			}

			if ( isset($user_data['home_phone']) ) {
				$uf->setHomePhone($user_data['home_phone']);
			}

			if ( isset($user_data['mobile_phone']) ) {
				$uf->setMobilePhone($user_data['mobile_phone']);
			}

			if ( isset($user_data['fax_phone']) ) {
				$uf->setFaxPhone($user_data['fax_phone']);
			}

			if ( isset($user_data['home_email']) ) {
				$uf->setHomeEmail($user_data['home_email']);
			}

			if ( isset($user_data['work_email']) ) {
				$uf->setWorkEmail($user_data['work_email']);
			}
                        
                        if ( isset($user_data['office_mobile']) ) {
				$uf->setOfficeMobile($user_data['office_mobile']);
			}
                        
                        
                        if ( isset($user_data['personal_email']) ) {
				$uf->setPersonalEmail($user_data['personal_email']);
			}
                        
                        
                       

			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}
                        

                                $uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
                                
                                $date = new DateTime();
                                $date->setTimestamp($uf->getBirthDate());
                                $date->modify('+60 years');
                        
                                $uf->setRetirementDate(  $date->getTimestamp()  );
                                 $uf->setRetirementDate( $user_data['retirement_date']  );
		} else {
			//Force them to update all fields.

			$uf->setFirstName($user_data['first_name']);
			$uf->setMiddleName($user_data['middle_name']);
                        $uf->setFullNameField($user_data['full_name']);
                        $uf->setCallingName($user_data['calling_name']);
                        $uf->setNameWithInitials($user_data['name_with_initials']);
                       
			$uf->setLastName($user_data['last_name']);
			if ( isset($user_data['second_last_name']) ) {
				$uf->setSecondLastName($user_data['second_last_name']);
			}
			$uf->setSex($user_data['sex']);
                        $uf->setMarital($user_data['marital']);
                        $uf->setReligion($user_data['religion']);
			$uf->setAddress1($user_data['address1']);
			$uf->setAddress2($user_data['address2']);
                        $uf->setAddress3($user_data['address3']);
                        $uf->setNameTitle($user_data['title_name']);
                        

                        //ARSP EDIT CODE--->
                        $uf->setNic($user_data['nic']);

                                    //ARSP EDIT CODE---> ADD NEW CODE FOR PROBATION PERIOD
                        $uf->setProbation($user_data['probation']);

                       //ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
                        $uf->setEpfRegistrationNo($user_data['epf_registration_no']);

                        //ARSP EDIT CODE---> ADD NEW CODE FOR Epf registration no
                        $uf->setEpfMembershipNo($user_data['epf_membership_no']);				

                        $uf->setCity($user_data['city']);

			if ( isset($user_data['country']) ) {
				$uf->setCountry($user_data['country']);
			}

			if ( isset($user_data['province']) ) {
				$uf->setProvince($user_data['province']);
			}

			$uf->setPostalCode($user_data['postal_code']);
			$uf->setWorkPhone($user_data['work_phone']);
			$uf->setWorkPhoneExt($user_data['work_phone_ext']);
			$uf->setHomePhone($user_data['home_phone']);
			$uf->setMobilePhone($user_data['mobile_phone']);
			$uf->setFaxPhone($user_data['fax_phone']);
			$uf->setHomeEmail($user_data['home_email']);
			$uf->setWorkEmail($user_data['work_email']);
                        $uf->setOfficeMobile($user_data['office_mobile']);
                        $uf->setPersonalEmail($user_data['personal_email']);
                        $uf->setConfiremedDate( $user_data['confirmed_date'] );
                        $uf->setResignDate( $user_data['resign_date'] );
                        
			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}

			$uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
                        
                        $uf->setRetirementDate( $user_data['retirement_date']  );
		}

                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
		if ( DEMO_MODE == FALSE
			AND isset($user_data['permission_control_id'])
			AND $uf->getPermissionLevel() <= $permission->getLevel()
			AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') ) ) {
			$uf->setPermissionControl( $user_data['permission_control_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
			$uf->setPermissionControl( $udf_obj->getPermissionControl() );
		}

		if ( isset($user_data['pay_period_schedule_id']) AND ( $permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule') ) ) {
			$uf->setPayPeriodSchedule( $user_data['pay_period_schedule_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
                        $uf->setPayPeriodSchedule( $udf_obj->getPayPeriodSchedule() );
        }

		if ( isset($user_data['policy_group_id']) AND ( $permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group') ) ) {
			$uf->setPolicyGroup( $user_data['policy_group_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE) {
                        $uf->setPolicyGroup( $udf_obj->getPolicyGroup() );
        }

		if ( isset($user_data['hierarchy_control']) AND ( $permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy') ) ) {
			$uf->setHierarchyControl( $user_data['hierarchy_control'] );
		}

		if ( isset($user_data['currency_id']) ) {
			$uf->setCurrency( $user_data['currency_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
                        $uf->setCurrency( $udf_obj->getCurrency() );
        }

		if ( isset($user_data['hire_date']) ) {
			$uf->setHireDate( $user_data['hire_date'] );
		}
		if ( isset($user_data['termination_date']) ) {
			$uf->setTerminationDate( $user_data['termination_date'] );
		}
		
		/**
		 * ARSP NOTE -->
		 * I HIDE THIS ORIGINAL CODE FOR THUNDER & NEON AND ADDED NEW CODE
		 */  		 
		//if ( isset($user_data['employee_number']) ) {
		//	$uf->setEmployeeNumber( $user_data['employee_number'] );
		//}
		
		/**
		 * ARSP NOTE -->
		 * I MODIFIED ABOVE ORIGINAL CODE THUNDER & NEON
		 */                
		if ( isset($user_data['employee_number_only']) ) {                    
			$uf->setEmployeeNumber( $user_data['branch_short_id'].$user_data['employee_number_only'] );
		}
                
                /**
                 * ARSP NOTE -->
                 * I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
                 */                
		if ( isset($user_data['employee_number_only']) ) {                        
			$uf->setEmployeeNumberOnly($user_data['employee_number_only'], $user_data['default_branch_id']);//ARSP NOTE --> I ADDED EXTRA PARAMETER FOR THUNDER & NEON
		}                 
		
		/**
		 * ARSP NOTE -->
		 * I ADDED THIS ORIGINAL CODE AND ADDED NEW CODE
		 */  		
		if ( isset($user_data['punch_machine_user_id']) ) {
			$uf->setPunchMachineUserID( $user_data['punch_machine_user_id'] );
		}
		
		if ( isset($user_data['default_branch_id']) ) {
			$uf->setDefaultBranch( $user_data['default_branch_id'] );
		}
		if ( isset($user_data['default_department_id']) ) {
			$uf->setDefaultDepartment( $user_data['default_department_id'] );
		}
		if ( isset($user_data['group_id']) ) {
			$uf->setGroup( $user_data['group_id'] );
		}
		if ( isset($user_data['title_id']) ) {
			$uf->setTitle($user_data['title_id']);
		}
		
		/**
		 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		 * EMPLOYEE JOB SKILLS
		 */                
		if ( isset($user_data['job_skills']) ) {                        
			$uf->setJobSkills($user_data['job_skills'],  $user_data['job_skills']);
		}  		
		
		if ( isset($user_data['ibutton_id']) ) {
			$uf->setIButtonId($user_data['ibutton_id']);
		}
		if ( isset($user_data['other_id1']) ) {
			$uf->setOtherID1( $user_data['other_id1'] );
		}
		if ( isset($user_data['other_id2']) ) {
			$uf->setOtherID2( $user_data['other_id2'] );
		}
		if ( isset($user_data['other_id3']) ) {
			$uf->setOtherID3( $user_data['other_id3'] );
		}
		if ( isset($user_data['other_id4']) ) {
			$uf->setOtherID4( $user_data['other_id4'] );
		}
		if ( isset($user_data['other_id5']) ) {
			$uf->setOtherID5( $user_data['other_id5'] );
		}

		if ( isset($user_data['note']) ) {
			$uf->setNote( $user_data['note'] );
		}
		
        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( isset($user_data['hire_note']) ) {
			$uf->setHireNote( $user_data['hire_note'] );
		}
                
        //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( isset($user_data['termination_note']) ) {
			$uf->setTerminationNote( $user_data['termination_note'] );
		}
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( isset($user_data['immediate_contact_person']) ) {
			$uf->setImmediateContactPerson($user_data['immediate_contact_person'] );
		}
                
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( isset($user_data['immediate_contact_no']) ) {
			$uf->setImmediateContactNo( $user_data['immediate_contact_no'] );
		}
                
                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
		if ( isset($user_data['bond_period']) ) {
			$uf->setBondPeriod($user_data['bond_period'] );
		}                   
                
                /**
                 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                 */
		if ( isset($user_data['resign_date']) ) {
			$uf->setResignDate( $user_data['resign_date'] );
		}    
                
                
                if ( isset($user_data['confirmed_date']) ) {
			$uf->setConfiremedDate( $user_data['confirmed_date'] );
		}    
//                var_dump($uf->isValid()); die;
//                echo '<pre>';                print_r($uf->getCurrent()); echo '<pre>'; die;

		if ( $uf->isValid() ) {
			$uf->Save(FALSE);
                        
                        
			$user_data['id'] = $uf->getId();
			Debug::Text('Inserted ID: '. $user_data['id'], __FILE__, __LINE__, __METHOD__,10);

			Redirect::Page( URLBuilder::getURL( array('id' => $user_data['id'], 'saved_search_id' => $saved_search_id, 'company_id' => $company_id, 'data_saved' => TRUE), 'EditUser.php') );

			break;
		}
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
	default:
		//Debug::Text('bCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $permission->Check('company','view') == FALSE OR $company_id == '' OR $company_id == '-1' ) {
			$company_id = $current_company->getId();
		}
		//Debug::Text('cCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($id) AND $action !== 'submit' ) {
			//Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			BreadCrumb::setCrumb($title);

			if ( $permission->Check('company','view') ) {
				$ulf->getById( $id )->getCurrent();
			} else {
				//$ulf->GetByIdAndCompanyId( $id, $company_id )->getCurrent();
				$ulf->getByIdAndCompanyId($id, $company_id );
			}

			foreach ($ulf as $user) {
				//Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);
				$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getId() );
				$is_child = $permission->isChild( $user->getId(), $permission_children_ids );
				if ( $permission->Check('user','edit')
						OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user','edit_child') AND $is_child === TRUE ) ) {

                    $user_title = NULL;
					if ( $user->getTitle() != 0 AND is_object( $user->getTitleObject() ) ) {
						$user_title = $user->getTitleObject()->getName();
					}
					Debug::Text('Title: '. $user_title , __FILE__, __LINE__, __METHOD__,10);

					if ( $permission->Check('user','view_sin') == TRUE ) {
						$sin_number = $user->getSIN();
					} else {
						$sin_number = $user->getSecureSIN();
					}

					$user_data = array(
										'id' => $user->getId(),
										'company_id' => $user->getCompany(),
										'status' => $user->getStatus(),
										'user_name' => $user->getUserName(),
										'title_id' => $user->getTitle(),
        /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/                  'job_skills' => $user->getJobSkills(),
										'title' => $user_title,
	//									'password' => $user->getPassword(),
										'phone_id' => $user->getPhoneId(),
										'phone_password' => $user->getPhonePassword(),
										'ibutton_id' => $user->getIbuttonId(),
            /*ARSP NOTE --> I HIDE THIS CODE FOR THUNDER & NEON*/	//	'employee_number' => $user->getEmployeeNumber(),
            /*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/              'employee_number_only' => $user->getEmployeeNumberOnly(),
			/*ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON*/  'punch_machine_user_id' => $user->getPunchMachineUserID(),			
			
			
                                                                                'title_name' => $user->getNameTitle(),
										'first_name' => $user->getFirstName(),
										'middle_name' => $user->getMiddleName(),
										'full_name' => $user->getFullNameField(),
                                                                                'calling_name' => $user->getCallingName(),
                                                                                'name_with_initials' => $user->getNameWithInitials(),
                                                                                'last_name' => $user->getLastName(),
										'second_last_name' => $user->getSecondLastName(),
                                                                                'religion' => $user->getReligion(),
										'sex' => $user->getSex(),
                                                                                'marital' => $user->getMarital(),
										'address1' => $user->getAddress1(),
										'address2' => $user->getAddress2(),
                                                                                'address3' => $user->getAddress3(),
                                        /*ARSP EDIT--> ADD CODE FOR NIC  */     'nic' =>$user->getNic(),
										'city' => $user->getCity(),
										'province' => $user->getProvince(),
										'country' => $user->getCountry(),
										'postal_code' => $user->getPostalCode(),
										'work_phone' => $user->getWorkPhone(),
										'work_phone_ext' => $user->getWorkPhoneExt(),
										'home_phone' => $user->getHomePhone(),
										'mobile_phone' => $user->getMobilePhone(),
                                                                                'office_mobile' => $user->getOfficeMobile(),
										'fax_phone' => $user->getFaxPhone(),
										'home_email' => $user->getHomeEmail(),
										'work_email' => $user->getWorkEmail(),
                                                                                'personal_email' => $user->getPersonalEmail(),
                           /* ARSP EDIT-> ADD CODE GET epf_registration_no  */ // 'epf_registration_no'=> $user->getEpfRegistrationNo(),
                                                                                'epf_registration_no'=>$current_company->getEpfNo(),
                           /* ARSP EDIT-> ADD CODE GET epf_membership_no  */    'epf_membership_no'=> $user->getEpfMembershipNo(),		
										'birth_date' => $user->getBirthDate(),
                                                                                'retirement_date' => $user->getRetirementDate(),
										'hire_date' => $user->getHireDate(),
										'termination_date' => $user->getTerminationDate(),
                      /* ARSP EDIT --> I ADDED THIS CPDE FOR THUNDER & NEON  */ 'resign_date' => $user->getResignDate(), 
                                                                                'confirmed_date'=> $user->getConfiremedDate(), 
										'sin' => $sin_number,

										'other_id1' => $user->getOtherID1(),
										'other_id2' => $user->getOtherID2(),
										'other_id3' => $user->getOtherID3(),
										'other_id4' => $user->getOtherID4(),
										'other_id5' => $user->getOtherID5(),

										'note' => $user->getNote(),
                      /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */ 'hire_note' => $user->getHireNote(),
                      /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */ 'termination_note' => $user->getTerminationNote(),                       
										
                      /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */ 'immediate_contact_person' => $user->getImmediateContactPerson(),
                      /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */ 'immediate_contact_no' => $user->getImmediateContactNo(),                       
                                            
                      /* ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON  */ 'bond_period' => $user->getBondPeriod(),

										'default_branch_id' => $user->getDefaultBranch(),
										'default_department_id' => $user->getDefaultDepartment(),
										'group_id' => $user->getGroup(),
                                                                                'currency_id' => $user->getCurrency(),
										'permission_level' => $user->getPermissionLevel(),
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'created_date' => $user->getCreatedDate(),
										'created_by' => $user->getCreatedBy(),
										'updated_date' => $user->getUpdatedDate(),
										'updated_by' => $user->getUpdatedBy(),
										'deleted_date' => $user->getDeletedDate(),
										'deleted_by' => $user->getDeletedBy(),
                            //   /* ARSP EDIT-> Add CODE GET USER IMAGE  */     'logo_file_name' => $user->getUserImageFileName(NULL, FALSE),
                               /* ARSP EDIT-> ADD CODE GET USER FILES URL  */   'user_file'=> $user->getUserFilesUrl(),
                               /* ARSP EDIT-> ADD CODE GET USER FILES NAME  */  'file_name'=>$user->getFileName(),
			      /* ARSP EDIT-> ADD CODE GET PROBATION PERIOD  */  'probation'=>$user->getProbation(),
                                            
                         /* ARSP NOTE->I ADDED THIS CODE FOR THUNDER & NEON  */ 'basis_of_employment'=>$user->getBasisOfEmployment(),    
                         /* ARSP EDIT->I ADDED THIS CODE FOR THUNDER & NEON  */ 'month'=>$user->getMonth(),    
                                            
                               /* ARSP EDIT-> ADD CODE GET TEMPLATE FILES URL*/ 'user_template_url'=>$user->getUserTemplateUrl(),
                               /* ARSP EDIT-> ADD CODE GET TEMPLATE FILES URL*/ 'user_template_name'=>$user->getTemplateName(),							   
                                                                                                        
                                                      
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_id_copy_url'=>$user->getUserIdCopyUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_id_copy_name'=>$user->getUserIdCopyFileName(),
                                            
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_birth_certificate_url'=>$user->getUserBirthCertificateUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_birth_certificate_name'=>$user->getUserBirthCertificateFileName(),
                                                                                
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_gs_letter_url'=>$user->getUserGsLetterUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_gs_letter_name'=>$user->getUserGsLetterFileName(),
                                                                                    
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_police_report_url'=>$user->getUserPoliceReportUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_police_report_name'=>$user->getUserPoliceReportFileName(),

                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_nda_url'=>$user->getUserNdaUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'user_nda_name'=>$user->getUserNdaFileName(),
                                            
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'bond_url'=>$user->getBondUrl(),
                          /* ARSP EDIT-> I ADDED THIS CODE FOR THUNDER & NEON*/ 'bond_name'=>$user->getBondFileName()
                                                                                                        
                                                                                
									);
                                        //print_r($user_data);
                                        
                                        
                                       
                                        
                                        
                                        

					$pclfb = new PermissionControlListFactory();
					$pclfb->getByCompanyIdAndUserId( $user->getCompany(), $id );
					if ( $pclfb->getRecordCount() > 0 ) {
						$user_data['permission_control_id'] = $pclfb->getCurrent()->getId();
					}

					$ppslfb = new PayPeriodScheduleListFactory();
					$ppslfb->getByUserId( $id );
					if ( $ppslfb->getRecordCount() > 0 ) {
						$user_data['pay_period_schedule_id'] = $ppslfb->getCurrent()->getId();
					}

					$pglf = new PolicyGroupListFactory();
					$pglf->getByUserIds( $id );
					if ( $pglf->getRecordCount() > 0 ) {
						$user_data['policy_group_id'] = $pglf->getCurrent()->getId();
					}

					$hclf = new HierarchyControlListFactory();
					$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $user->getCompany(), $user->getID() );
					$user_data['hierarchy_control'] = $hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE );
					unset($hclf);
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}

			}
			
			
			
//START-------------------------------------------//ARSP ADD NEW CODE FOR SALARY(WAGE)----------------------------------------- 
                        $uwlf = new UserWageListFactory();
						$uwlf->getByUserId($user_data['id']);                       
                        
                                foreach ($uwlf as $wage) {        
                                    
                                    
                                    
                                        $wage_data = array(
										'id' => $wage->getId(),
										'user_id' => $wage->getUser(),										
                                        'wage' => Misc::removeTrailingZeros( $wage->getWage() )									
										);

                        }

                        //print_r($wage_data);
//END-------------------------------------------//ARSP ADD NEW CODE FOR SALARY(WAGE)-----------------------------------------  
			
			
			
			
			
			
			
		} elseif ( $action == 'submit') {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($user_obj ) ) {
				Debug::Text('User Object set', __FILE__, __LINE__, __METHOD__,10);

				$user_data['is_owner'] = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() );
				$user_data['is_owner'] = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				//If user doesn't have permissions to edit these values, we have to pull them
				//out of the DB and update the array.
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $user_obj->getCompany();
				}

				if ( !isset( $user_data['status'] ) ) {
					$user_data['status'] = $user_obj->getStatus();
				}

				if ( !isset( $user_data['user_name'] ) ) {
					$user_data['user_name'] = $user_obj->getUserName();
				}

				if ( !isset( $user_data['phone_id'] ) ) {
					$user_data['phone_id'] = $user_obj->getPhoneId();
				}

				if ( !isset( $user_data['hire_date'] ) ) {
					$user_data['hire_date'] = $user_obj->getHireDate();
				}

				if ( !isset( $user_data['birth_date'] ) ) {
					$user_data['birth_date'] = $user_obj->getBirthDate();
				}

				if ( !isset( $user_data['province'] ) ) {
					$user_data['province'] = $user_obj->getProvince();
				}

				if ( !isset( $user_data['country'] ) ) {
					$user_data['country'] = $user_obj->getCountry();
				}

			} else {
				Debug::Text('User Object NOT set', __FILE__, __LINE__, __METHOD__,10);
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $company_id;
				}

			}


		} else {
			Debug::Text('Adding new User.', __FILE__, __LINE__, __METHOD__,10);

			//Get New Hire Defaults.
			$udlf = new UserDefaultListFactory();
			$udlf->getByCompanyId( $company_id );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
				$udf_obj = $udlf->getCurrent();

				$user_data = array(
								'company_id' => $company_id,
								'title_id' => $udf_obj->getTitle(),
								//'employee_number' => $udf_obj->getEmployeeNumber(),
								'city' => $udf_obj->getCity(),
								'province' => $udf_obj->getProvince(),
								'country' => $udf_obj->getCountry(),
								'work_phone' => $udf_obj->getWorkPhone(),
								'work_phone_ext' => $udf_obj->getWorkPhoneExt(),
								'work_email' => $udf_obj->getWorkEmail(),
								'hire_date' => $udf_obj->getHireDate(),
								'default_branch_id' => $udf_obj->getDefaultBranch(),
								'default_department_id' => $udf_obj->getDefaultDepartment(),
								'permission_control_id' => $udf_obj->getPermissionControl(),
								'pay_period_schedule_id' => $udf_obj->getPayPeriodSchedule(),
								'policy_group_id' => $udf_obj->getPolicyGroup(),
                                                                'currency_id' => $udf_obj->getCurrency(),
							);
			}

			if ( !isset($user_obj ) ) {
				$user_obj = $ulf->getByIdAndCompanyId($current_user->getId(), $company_id )->getCurrent();
			}

			if ( !isset( $user_data['company_id'] ) ) {
				$user_data['company_id'] = $company_id;
			}

			if ( !isset( $user_data['country'] ) ) {
				$user_data['country'] = 'CA';
			}

			/**
			 * ARSP NOTE-->
			 * I HIDE THIS CODE FOR THUNDER AND NEON
			 */   
/*			$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
			if ( $ulf->getRecordCount() > 0 ) {
				Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,10);
				if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == TRUE ) {
					$user_data['next_available_employee_number'] = $ulf->getCurrent()->getEmployeeNumber()+1;
				} else {
					Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__,10);
					$user_data['next_available_employee_number'] = NULL;
				}
			} else {
				$user_data['next_available_employee_number'] = 1;
			}*/
			
			//$ulf->getHighestEmployeeNumberByCompanyId( $company_id ); ARSP NOTE -->  I HIDE THIS CODE FOR THUNDER AND NEON
                        						
			/**
			 * ARSP NOTE-->
			 * I HIDE ABOVE ORIGINAL CODE FOR THUNDER AND NEON I ADDED MODIFIED
			 */                        
                        $ulf->getHighestEmployeeNumberOnlyByCompanyId( $company_id );                       
			if ( $ulf->getRecordCount() > 0 ) {
				Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,10);
				if ( is_numeric( $ulf->getCurrent()->getEmployeeNumberOnly() ) == TRUE ) {//ARSP NOTE --> I MODIFIED THIS CODE FOR THUNDER & NEON                                  
                                        //ARSP NOTE--> I HIDE THIS CODE FOR THUNDER AND NEON
					//$user_data['next_available_employee_number'] = $ulf->getCurrent()->getEmployeeNumber()+1;
                                    
                                        /**
                                         * ARSP NOTE-->
                                         * I ADDED THIS CODE FOR THUNDER AND NEON
                                         */                                      
                                        $user_data['next_available_employee_number_only'] = $ulf->getCurrent()->getEmployeeNumberOnly()+1;
				} else {
					Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__,10);
                                        //$user_data['next_available_employee_number'] = NULL;
                                        
                                        /**
                                         * ARSP NOTE-->
                                         * I ADDED THIS CODE FOR THUNDER AND NEON
                                         */                                           
					$user_data['next_available_employee_number_only'] = NULL;
				}
			} else {
				//$user_data['next_available_employee_number'] = 1;
                                /**
                                 * ARSP NOTE-->
                                 * I ADDED THIS CODE FOR THUNDER AND NEON
                                 */                                           
                                $user_data['next_available_employee_number_only'] = 1;                            
			}			
			
			

			if ( !isset($user_data['hire_date']) OR $user_data['hire_date'] == '' ) {
				$user_data['hire_date'] = time();
			}
		}
		//var_dump($user_data);              

		//Select box options;
		$blf = new BranchListFactory();
		$branch_options = $blf->getByCompanyIdArray( $company_id );

		$dlf = new DepartmentListFactory();
		$department_options = $dlf->getByCompanyIdArray( $company_id );

		$culf = new CurrencyListFactory();
                $culf->getByCompanyId( $company_id );
		$currency_options = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		$hotf = new HierarchyObjectTypeFactory();
		$hierarchy_object_type_options = $hotf->getOptions('object_type');

		$hclf = new HierarchyControlListFactory();
		$hclf->getObjectTypeAppendedListByCompanyID( $company_id );
		$hierarchy_control_options = $hclf->getArrayByListFactory( $hclf, TRUE, TRUE );
                
                
                $clf = new CompanyListFactory();
                $clf->getById($company_id);
                
                $user_data['epf_registration_no'] =$current_company->getEpfNo();

		//Select box options;
		$user_data['branch_options'] = $branch_options;
		$user_data['department_options'] = $department_options;
                $user_data['currency_options'] = $currency_options;

		$user_data['sex_options'] = $uf->getOptions('sex');
                
                $user_data['title_name_options'] = $uf->getOptions('title');
		$user_data['status_options'] = $uf->getOptions('status');
                $user_data['religion_options'] = $uf->getOptions('religion');
                
                $user_data['marital_options'] = $uf->getOptions('marital');
		
                
                

		$clf = new CompanyListFactory();
		$user_data['country_options'] = $clf->getOptions('country');
		$user_data['province_options'] = $clf->getOptions('province', $user_data['country'] );

		$utlf = new UserTitleListFactory();
		$user_titles = $utlf->getByCompanyIdArray( $company_id );
		$user_data['title_options'] = $user_titles;

                /**
                 * ARSP NOTE -->
                 * I ADDED THIS CODE FOR THUNDER & NEON
                 */
                $user_data['month_options'] = $uf->getOptions('month');
 
                /**
                 * ARSP NOTE -->
                 * I ADDED THIS CODE FOR THUNDER & NEON
                 */
                $user_data['bond_period_option'] = $uf->getOptions('bond_period'); 
                
		//Get Permission Groups
		$pclf = new PermissionControlListFactory();
		$pclf->getByCompanyIdAndLevel( $company_id, $permission->getLevel() );
		$user_data['permission_control_options'] = $pclf->getArrayByListFactory( $pclf, FALSE );

		//Get pay period schedules
		$ppslf = new PayPeriodScheduleListFactory();
		$pay_period_schedules = $ppslf->getByCompanyIDArray( $company_id );
		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

		$pglf = new PolicyGroupListFactory();
		$policy_groups = $pglf->getByCompanyIDArray( $company_id );
		$user_data['policy_group_options'] = $policy_groups;

		$uglf = new UserGroupListFactory();
		$user_data['group_options'] = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $company_id ), 'TEXT', TRUE) );

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$user_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $company_id, 10 );

		$user_data['hierarchy_object_type_options'] = $hierarchy_object_type_options;
		$user_data['hierarchy_control_options'] = $hierarchy_control_options;

		//Company list.
		if ( $permission->Check('company','view') ) {
			$user_data['company_options'] = CompanyListFactory::getAllArray();
		} else {
			$user_data['company_options'] = array( $company_id => $current_company->getName() );
		}

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );
		if ( $permission->Check('user','edit') == FALSE ) {
			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data );
		$user_data['user_options'] = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$smarty->assign_by_ref('user_data', $user_data);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);
		$smarty->assign_by_ref('incomplete', $incomplete);
		$smarty->assign_by_ref('data_saved', $data_saved);

		Debug::Text('Current User Permission Level: '. $permission->getLevel() .' Level for user we are currently editing: '. $permission->getLevel( $uf->getID(), $uf->getCompany() ) .' User ID: '. $uf->getID(), __FILE__, __LINE__, __METHOD__,10);

		break;
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
}




 //echo $user_data.company_options[1];

// ARSP EDIT --> Appointment Letter (Word Doc) Generate

$PHPWord = new PHPWord();

$document = $PHPWord->loadTemplate('../../storage/appointment_letter_template/Template.docx');

$document->setValue('Value0', $user_data['first_name']);
$document->setValue('Value1', $user_data['last_name']);
$document->setValue('Value2', $user_data['address1']);
$document->setValue('Value3', $user_data['address2']);


/*
 * Example of $epoch 
$epoch = 1340000000;
echo date('r', $epoch); // output as RFC 2822 date - returns local time
echo gmdate('r', $epoch); // returns GMT/UTC time
 * */
 
$hire_date = gmdate('M d Y', $user_data['hire_date']);

$document->setValue('Value4', $hire_date);
$document->setValue('Value5', $user_data['title']);
$document->setValue('Value6', $wage_data['wage']);
$document->setValue('Value7', $current_company->getName());
$document->setValue('Value8', $user_data['probation']);

$letter_path = $user_data['id'];
//echo $path = "../../storage/user_appointment_letter/".$letter_path."/outputfile.docx";
$structure ="../../storage/user_appointment_letter/".$letter_path;
rmdir($structure);
mkdir($structure, 0755, true);

//$document->save('../../storage/appointment_letter_template/1/outputfile.docx');
//echo $p = Environment::getUserAppointmentLetterBasePath().'user_appointment_letter'.'/'.$letter_path.'/'.'outputfile.docx';
$document->save($structure."/outputfile.docx");

//END --> Appointment Letter Word Doc Generate







////ARSP EDIT --> ADD NEW CODE FOR DELETE USER UPLOADED FILES 
//if(isset($delete_file_name) && isset($delete_user_id ))
//{
//$deleteFile = new fileupload();
//$path ="../../storage/user_file/".$delete_user_id."/".$delete_file_name;
//$deleteFile->deleteFiles($path,$delete_user_id );
//}



//ARSP EDIT --> ADD NEW CODE FOR DELETE USER UPLOADED FILES 
if(isset($delete_file_name) && isset($delete_user_id ) && isset($delete_file_type ))
{


    if($delete_file_type == 'user_file' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_file/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }
    
    if($delete_file_type == 'user_template' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_template_file/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }
    
    if($delete_file_type == 'user_id_copy' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_id_copy/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }    

    if($delete_file_type == 'user_birth_certificate' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_birth_certificate/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }  
    
    if($delete_file_type == 'user_gs_letter' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_gs_letter/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }    

    if($delete_file_type == 'user_police_report' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_police_report/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    } 
    
    if($delete_file_type == 'user_nda' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_nda/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }   
    
    if($delete_file_type == 'bond' )
    {
        $deleteFile = new fileupload();
        $path ="../../storage/user_bond/".$delete_user_id."/".$delete_file_name;
        $deleteFile->deleteFiles($path,$delete_user_id );
    }     
}


//ARSP EDIT --> ADD NEW CODE WARNING MESSAGES IF EXCEED THE PROBATION PERIOD 
if( isset($user_data['id']) && isset($user_data['hire_date']) && isset($user_data['probation']) && ($user_data['probation'] > 0) ) 
{
    $probation_warning = $uf->getWarning($user_data['hire_date'], $user_data['probation']);    
    //$warning_message;
    if($probation_warning != "")
    {
        //echo $warning_message;
        $smarty->assign_by_ref('probation_warning',$probation_warning);//All files url
    }
    
}


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
//ARSP EDIT --> ADD NEW CODE WARNING MESSAGES IF EXCEED THE PROBATION PERIOD 
if( isset($user_data['id']) && isset($user_data['hire_date']) && isset($user_data['month']) && $user_data['month'] > 0 && ($user_data['basis_of_employment'] >0) && ($user_data['basis_of_employment'] != 4) && ($user_data['basis_of_employment'] != 6))  
{
    //$warning_message;
    if($user_data['basis_of_employment'] != 5)
    {        
        $basis_of_employment_warning = $uf->getWarning1($user_data['hire_date'], $user_data['month'],$user_data['basis_of_employment']);    

        if($basis_of_employment_warning != "")
        {
            //echo $warning_message;
            $smarty->assign_by_ref('basis_of_employment_warning',$basis_of_employment_warning);//All files url
        }        
    }
    
    if($user_data['basis_of_employment'] == 5 && $user_data['resign_date'] != '')
    {
        //echo "ARSP RESIGN DATE = ".$user_data['resign_date'];
        //var_dump($user_data['resign_date']);
        //echo "<p/>";
        $basis_of_employment_warning = $uf->getWarning1($user_data['resign_date'], 3, $user_data['basis_of_employment']);    

        if($basis_of_employment_warning != "")
        {
            //echo $warning_message;
            $smarty->assign_by_ref('basis_of_employment_warning',$basis_of_employment_warning);//All files url
        } 
    }
    
}



/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
//ARSP EDIT --> I ADDED THIS CODE FOR BOND WARNING 
if( isset($user_data['id']) && isset($user_data['hire_date']) && isset($user_data['bond_period']) && $user_data['bond_period'] > 0) 
{
    //$warning_message;   
    if($user_data['bond_period'] != 0 && $user_data['hire_date'] != '')
    {
        //echo "ARSP Hire DATE = ".$user_data['hire_date'];
        //var_dump($user_data['hire_date']);
        //echo "<p/>";
        $bond_warning = $uf->getWarning2($user_data['hire_date'], $user_data['bond_period']);    

        if($bond_warning != "")
        {
            echo $warning_message;
            $smarty->assign_by_ref('bond_warning',$bond_warning);
        } 
    }
    
}



// ARSP ADD NEW SMARTY CODE TO SEND USER TEMPLATE FILE ARRAY       
$smarty->assign_by_ref('user_template_url',$user_data['user_template_url']);//All template files url
$smarty->assign_by_ref('user_template_name',$user_data['user_template_name']);// All template files name
$count = is_array($user_data['user_template_name']) ? count($user_data['user_template_name']) : 0 ;
$smarty->assign_by_ref('user_template_array_size', $count);
$var1 = 1;
$smarty->assign_by_ref('var1',$var1);



// ARSP ADD NEW SMARTY CODE TO SEND USER FILE ARRAY
//print_r($user_data['user_file']);
$user_file_url = $user_data['user_file'];
//echo '<pre>';
//print_r($test_user_file);
//echo '</pre>';

//print_r($file_name);
$smarty->assign_by_ref('user_file_url',$user_file_url);//All files url
$smarty->assign_by_ref('file_name',$user_data['file_name']);// All files name
$count = is_array(count($user_data['file_name'])) ? count($user_data['file_name']):0;
$smarty->assign_by_ref('array_size', $count);
$var = 1;//use to print the index
$smarty->assign_by_ref('var',$var);






/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('user_id_copy_url',$user_data['user_id_copy_url']);//All template files url
$smarty->assign_by_ref('user_id_copy_name',$user_data['user_id_copy_name']);// All template files name
$count = is_array(count($user_data['user_id_copy_name'])) ? count($user_data['user_id_copy_name']) : 0;
$smarty->assign_by_ref('user_id_copy_array_size', $count);
$var2 = 1;
$smarty->assign_by_ref('var2',$var2);


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('user_birth_certificate_url',$user_data['user_birth_certificate_url']);//All template files url
$smarty->assign_by_ref('user_birth_certificate_name',$user_data['user_birth_certificate_name']);// All template files name
$count = is_array(count($user_data['user_birth_certificate_name'])) ? count($user_data['user_birth_certificate_name']) : 0;
$smarty->assign_by_ref('user_birth_certificate_array_size', $count);
$var3 = 1;
$smarty->assign_by_ref('var3',$var3);


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('user_gs_letter_url',$user_data['user_gs_letter_url']);//All template files url
$smarty->assign_by_ref('user_gs_letter_name',$user_data['user_gs_letter_name']);// All template files name
$count = is_array(count($user_data['user_gs_letter_name'])) ? count($user_data['user_gs_letter_name']) : 0;
$smarty->assign_by_ref('user_gs_letter_array_size', $count);
$var4 = 1;
$smarty->assign_by_ref('var4',$var4);


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('user_police_report_url',$user_data['user_police_report_url']);//All template files url
$smarty->assign_by_ref('user_police_report_name',$user_data['user_police_report_name']);// All template files name
$count = is_array(count($user_data['user_police_report_name'])) ? count($user_data['user_police_report_name']) : 0;
$smarty->assign_by_ref('user_police_report_array_size', $count);
$var5 = 1;
$smarty->assign_by_ref('var5',$var5);


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('user_nda_url',$user_data['user_nda_url']);//All template files url
$smarty->assign_by_ref('user_nda_name',$user_data['user_nda_name']);// All template files name
$count = is_array(count($user_data['user_nda_name'])) ? count($user_data['user_nda_name']) : 0;
$smarty->assign_by_ref('user_nda_array_size', $count);
$var6 = 1;
$smarty->assign_by_ref('var6',$var6);


/**
 * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
 */
$smarty->assign_by_ref('bond_url',$user_data['bond_url']);//All template files url
$smarty->assign_by_ref('bond_name',$user_data['bond_name']);// All template files name
$count = is_array(count($user_data['bond_name'])) ? count($user_data['bond_name']) : 0;
$smarty->assign_by_ref('bond_array_size', $count);
$var7 = 1;
$smarty->assign_by_ref('var7',$var7);





//echo "Test ARsp code -->".count($test_user_file);
//END ARSP EDIT
$smarty->assign_by_ref('uf', $uf);

$smarty->display('users/EditUser.tpl');
?>