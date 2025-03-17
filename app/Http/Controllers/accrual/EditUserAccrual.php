<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserAccrual.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual','enabled')
		OR !( $permission->Check('accrual','edit') OR $permission->Check('accrual','edit_own') OR $permission->Check('accrual','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'Edit Accrual')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'filter_user_id',
												'accrual_policy_id',
												'data'
												) ) );

if ( isset($data) ) {
	$data['time_stamp'] = TTDate::parseDateTime($data['time_stamp']);
	$data['amount'] = TTDate::parseTimeUnit( $data['amount'] );
}

$af = new AccrualFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
            
           // echo $data['amount'];
            //exit();

		$af->setId( $data['id'] );
		$af->setUser( $data['user_id'] );
		$af->setType( $data['type_id'] );
		$af->setAccrualPolicyID( $data['accrual_policy_id'] );
		$af->setAmount( $data['amount'] *8);
		$af->setTimeStamp( $data['time_stamp'] );
		$af->setEnableCalcBalance( TRUE );

		if ( $af->isValid() ) {
			$af->Save();

			Redirect::Page( URLBuilder::getURL( array('filter_user_id' => $data['user_id']) , 'UserAccrualBalanceList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$alf = new AccrualListFactory();
			$alf->getById($id);

			foreach ($alf as $a_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $a_obj->getId(),
									'user_id' => $a_obj->getUser(),
									'accrual_policy_id' => $a_obj->getAccrualPolicyID(),
									'type_id' => $a_obj->getType(),
									'amount' => ($a_obj->getAmount()/8),
									'time_stamp' => $a_obj->getTimeStamp(),
									'user_date_total_id' => $a_obj->getUserDateTotalID(),
									'created_date' => $a_obj->getCreatedDate(),
									'created_by' => $a_obj->getCreatedBy(),
									'updated_date' => $a_obj->getUpdatedDate(),
									'updated_by' => $a_obj->getUpdatedBy(),
									'deleted_date' => $a_obj->getDeletedDate(),
									'deleted_by' => $a_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			if ( $user_id == '' ) {
				$user_id = $filter_user_id;
			}
			$data = array(
						'user_id' => $user_id,
						'accrual_policy_id' => $accrual_policy_id,
						'amount' => 0,
						'time_stamp' => TTDate::getTime()
						);
		}

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

		//Select box options;
		$data['type_options'] = $af->getOptions('user_type');
		$data['user_options'] = $user_options;
		$data['accrual_policy_options'] = $accrual_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('af', $af);

$smarty->display('accrual/EditUserAccrual.tpl');
?>