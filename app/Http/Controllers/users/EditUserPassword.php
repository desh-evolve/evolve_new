<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5166 $
 * $Id: EditUserPassword.php 5166 2011-08-26 23:01:36Z ipso $
 * $Date: 2011-08-26 16:01:36 -0700 (Fri, 26 Aug 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own_password') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Change Web Password')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_data'
												) ) );

$ulf = new UserListFactory();
$uf = new UserFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//If this user only has edit_own permissions, force his own user_id.
		if ( $permission->Check('user','edit_own') AND !$permission->Check('user','edit')  ) {
			$user_data['id'] = $current_user->getId();
		}

		// Security measure.
		if ( !empty($user_data['id']) ) {
			$uf = $ulf->GetByIdAndCompanyId($user_data['id'], $current_company->getId() )->getCurrent();

			if ( !empty($user_data['current_password']) ) {
				if ( $uf->checkPassword($user_data['current_password']) !== TRUE ) {
					Debug::Text('Password check failed!', __FILE__, __LINE__, __METHOD__,10);
					$uf->Validator->isTrue(	'current_password',
											FALSE,
											_('Current password is incorrect') );
				}
			} else {
				Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__,10);
				$uf->Validator->isTrue(	'current_password',
										FALSE,
										_('Current password is incorrect') );

			}


			if ( !empty($user_data['id']) ) {
				if ( !empty($user_data['password']) OR !empty($user_data['password2']) ) {
					if ( $user_data['password'] == $user_data['password2'] ) {
						$uf->setPassword($user_data['password']);
					} else {
						$uf->Validator->isTrue(	'password',
												FALSE,
												__('Passwords don\'t match') );
					}
				} else {
					$uf->Validator->isTrue(	'password',
											FALSE,
											__('Passwords don\'t match') );

				}
			}

			if ( $uf->isValid() ) {
				if ( DEMO_MODE == FALSE ) {
					TTLog::addEntry( $uf->getId(), 20, _('Password - Web'), NULL, $uf->getTable() );
					$uf->Save();
				}

				if ( $user_data['id'] != $current_user->getId() ) {
					//Probably coming from the user list.
					Redirect::Page( URLBuilder::getURL(NULL, 'UserList.php') );
				} else {
					Redirect::Page( URLBuilder::getURL(NULL, '../index.php') );
				}

				break;
			}
		}
	default:
		if ( isset($id) ) {
			Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			//If this user only has edit_own permissions, force his own user_id.
			if ( $permission->Check('user','edit_own') AND !$permission->Check('user','edit')  ) {
				$id = $current_user->getId();
			}

			BreadCrumb::setCrumb($title);

			$ulf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($ulf as $user) {
				Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);

				$user_data = array(
									'id' => $user->getId(),
									'company' => $user->getCompany(),
									'status' => $user->getStatus(),
									'user_name' => $user->getUserName(),
									'password' => $user->getPassword(),
									'phone_id' => $user->getPhoneId(),
									'phone_password' => $user->getPhonePassword(),
									'first_name' => $user->getFirstName(),
									'middle_name' => $user->getMiddleName(),
									'last_name' => $user->getLastName(),
									'sex' => $user->getSex(),
									'address1' => $user->getAddress1(),
									'address2' => $user->getAddress2(),
									'city' => $user->getCity(),
									'province' => $user->getProvince(),
									'country' => $user->getCountry(),
									'postal_code' => $user->getPostalCode(),
									'work_phone' => $user->getWorkPhone(),
									'work_phone_ext' => $user->getWorkPhoneExt(),
									'home_phone' => $user->getHomePhone(),
									'mobile_phone' => $user->getMobilePhone(),
									'fax_phone' => $user->getFaxPhone(),
									'home_email' => $user->getHomeEmail(),
									'work_email' => $user->getWorkEmail(),
									'birth_date' => $user->getBirthDate(),
									'hire_date' => $user->getHireDate(),
									'sin' => $user->getSIN(),
									'created_date' => $user->getCreatedDate(),
									'created_by' => $user->getCreatedBy(),
									'updated_date' => $user->getUpdatedDate(),
									'updated_by' => $user->getUpdatedBy(),
									'deleted_date' => $user->getDeletedDate(),
									'deleted_by' => $user->getDeletedBy()
								);
			}
		} elseif ( $action == 'submit') {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset( $uf ) AND is_object($uf) AND !isset($user_data['user_name'] ) ) {
				//Do this so the user_name still displays on form error.
				$user_data['user_name'] = $uf->getUserName();
			}
		}

		$smarty->assign_by_ref('user_data', $user_data);

		break;
}

$smarty->assign_by_ref('uf', $uf);

$smarty->display('users/EditUserPassword.tpl');
?>