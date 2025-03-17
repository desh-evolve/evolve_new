<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5391 $
 * $Id: EditBankAccount.php 5391 2011-10-25 19:53:15Z ipso $
 * $Date: 2011-10-25 12:53:15 -0700 (Tue, 25 Oct 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

$smarty->assign('title', TTi18n::gettext($title = 'Bank Account')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'user_id',
												'company_id',
												'bank_data',
												'data_saved',
												) ) );

if ( isset($company_id) AND $company_id != '' ) {
	if ( !$permission->Check('company','enabled')
			OR !( $permission->Check('company','edit_own_bank') ) ) {
		$permission->Redirect( FALSE ); //Redirect
	}
} else {
	if ( !$permission->Check('user','enabled')
			OR !( $permission->Check('user','edit_bank') OR $permission->Check('user','edit_own_bank') ) ) {
		$permission->Redirect( FALSE ); //Redirect
	}
}

$baf = new BankAccountFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'delete':
		Debug::Text('Delete!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);

		$balf = new BankAccountListFactory();

		if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_own_bank') ) {
			Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
			$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			$redirect_arr = array('data_saved' => TRUE );
		} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_bank') ) {
			Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
			$balf->GetUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );
			$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
		} elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' AND $permission->Check('company','edit_own_bank') ) {
			Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
			//Company bank.
			$balf->GetCompanyAccountByCompanyId( $current_company->getId() );
			$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
		} else {
			$permission->Redirect( FALSE );
		}

		Debug::Text('Found Records: '. $balf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $balf->getRecordCount() > 0 ) {
			$b_obj = $balf->getCurrent();
			$b_obj->setDeleted(TRUE);
			$b_obj->Save();

			Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/bank_account/EditBankAccount.php') );
		}

		break;
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('User ID: '. $bank_data['user_id'] .' Company ID: '. $bank_data['company_id'], __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($bank_data, 'Bank Data: ', __FILE__, __LINE__, __METHOD__,10);

		if ( !empty($bank_data['id']) ) {
			$baf->setId( $bank_data['id'] );
		}

		if ( ( $bank_data['user_id'] != '' AND $bank_data['user_id'] == $current_user->getId() ) AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_own_bank') ) {
			Debug::Text('Current User/Company', __FILE__, __LINE__, __METHOD__,10);
			//Current user
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $current_user->getId() );
			$redirect_arr = array('data_saved' => TRUE );
		} elseif ( $bank_data['user_id'] != '' AND $bank_data['company_id'] == '' AND $permission->Check('user','edit_bank') ) {
			Debug::Text('Specified User', __FILE__, __LINE__, __METHOD__,10);
			//Specified User
			$baf->setCompany( $current_company->getId() );
			$baf->setUser( $bank_data['user_id'] );
			$redirect_arr = array('user_id' => $user_id, 'company_id' => $company_id, 'data_saved' => TRUE );
		} elseif ( $bank_data['company_id'] != '' AND $bank_data['user_id'] == '' AND $permission->Check('company','edit_own_bank') ) {
			Debug::Text('Specified Company', __FILE__, __LINE__, __METHOD__,10);
			//Company bank.
			$baf->setCompany( $bank_data['company_id'] );
			$redirect_arr = array('company_id' => $company_id, 'data_saved' => TRUE );
		} else {
			$permission->Redirect( FALSE );
		}

		if ( isset($bank_data['institution']) ) {
			$baf->setInstitution( $bank_data['institution'] );
		}
		$baf->setTransit( $bank_data['transit'] );
		$baf->setAccount( $bank_data['account'] );
                $baf->setBankName( $bank_data['bank_name'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
		$baf->setBankBranch( $bank_data['bank_branch'] );//ARSP EDIT --> I ADD NEW CODE FOR SET BANK NAME
                
                

		if ( $baf->isValid() ) {
			$baf->Save();

			Redirect::Page( URLBuilder::getURL( $redirect_arr, Environment::getBaseURL().'/bank_account/EditBankAccount.php') );

			break;
		} else {
			Debug::Text('Invalid bank data...', __FILE__, __LINE__, __METHOD__,10);
		}
	default:
		$balf = new BankAccountListFactory();
		$ulf = new UserListFactory();

		$country = NULL;
		if ( ( $user_id == '' OR $user_id == $current_user->getId() ) AND $company_id == '' AND $permission->Check('user','edit_own_bank') ) {
			//Current user
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $current_user->getId() );
			$user_id = $current_user->getId();

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
		} elseif ( $user_id != '' AND $permission->Check('user','edit_bank') ) {
			//Specified User
			$balf->getUserAccountByCompanyIdAndUserId( $current_company->getId(), $user_id );

			$user_obj = $ulf->getByIdAndCompanyId( $user_id, $current_company->getId() )->getCurrent();
			$country = $user_obj->getCountry();
		} elseif ( $company_id != '' AND $permission->Check('company','edit_own_bank') ) {
			//Company bank.
			$balf->getCompanyAccountByCompanyId( $current_company->getId() );

			$country = $current_company->getCountry();
		} else {
			$permission->Redirect( FALSE );
		}

		if ( !isset($action) ) {
			BreadCrumb::setCrumb($title);

			foreach ($balf as $bank_account) {
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$bank_data = array(
									'id' => $bank_account->getId(),
									'country' => strtolower($country),
									'institution' => $bank_account->getInstitution(),
									'transit' => $bank_account->getTransit(),
                                                                        //'account' => $bank_account->getSecureAccount(),//ARSP EDIT --> I Hide THIS CODE REASON- WE CANT SEE ORIGINAL ACCOUNT NUMBER
									'account' => $bank_account->getAccount(),//ARSP EDIT --> I ADD NEW CODE SHOW ONLY ORIGINAL ACCOUNT NUMBER
                                                                        'bank_name' => $bank_account->getBankName(),//ARSP EDIT --> I ADD NEW CODE FOR BANK NAME
                                                                        'bank_branch' => $bank_account->getBankBranch(),//ARSP EDIT --> I ADD NEW CODE FOR BANK BRANCH NAME
									'created_date' => $bank_account->getCreatedDate(),
									'created_by' => $bank_account->getCreatedBy(),
									'updated_date' => $bank_account->getUpdatedDate(),
									'updated_by' => $bank_account->getUpdatedBy(),
									'deleted_date' => $bank_account->getDeletedDate(),
									'deleted_by' => $bank_account->getDeletedBy()
								);
			}
		}

		if ( isset($user_id) AND $company_id == '' ) {
			//$user_id = $current_user->getId();
			$ulf = new UserListFactory();
			$full_name = $ulf->getById( $user_id )->getCurrent()->getFullName();
		} elseif ( $company_id != '' ) {
			$clf = new CompanyListFactory();
			$full_name = $clf->getById( $company_id )->getCurrent()->getName();
		}

		$bank_data['full_name'] = $full_name;
		$bank_data['country'] = strtolower($country);

		$bank_data['user_id'] = $user_id;
		$bank_data['company_id'] = $company_id;

		//var_dump($bank_data);
		$smarty->assign_by_ref('bank_data', $bank_data);
		$smarty->assign_by_ref('data_saved', $data_saved);

		break;
}

$smarty->assign_by_ref('baf', $baf);
//$smarty->assign_by_ref('current_time', TTDate::getDate('TIME') );

$smarty->display('bank_account/EditBankAccount.tpl');
?>
