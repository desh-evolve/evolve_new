<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditOverTimePolicy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('over_time_policy','enabled')
		OR !( $permission->Check('over_time_policy','edit') OR $permission->Check('over_time_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Overtime Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data['trigger_time'] ) ) {
	$data['trigger_time'] = TTDate::parseTimeUnit($data['trigger_time']);
}

if ( isset($data['max_time'] ) ) {
	$data['max_time'] = TTDate::parseTimeUnit($data['max_time']);
}

$otpf = new OverTimePolicyFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$otpf->setId( $data['id'] );
		$otpf->setCompany( $current_company->getId() );
		$otpf->setName( $data['name'] );
		$otpf->setType( $data['type_id'] );
		//$otpf->setLevel( $data['level'] );
		$otpf->setTriggerTime( $data['trigger_time'] );
                $otpf->setMaxTime( $data['max_time'] );
		$otpf->setRate( $data['rate'] );
		$otpf->setWageGroup( $data['wage_group_id'] );
		$otpf->setAccrualPolicyId( $data['accrual_policy_id'] );
		$otpf->setAccrualRate( $data['accrual_rate'] );
		$otpf->setPayStubEntryAccountId( $data['pay_stub_entry_account_id'] );

		if ( $otpf->isValid() ) {
			$otpf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'OverTimePolicyList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$otplf = new OverTimePolicyListFactory();
			$otplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($otplf as $otp_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $otp_obj->getId(),
									'name' => $otp_obj->getName(),
									'type_id' => $otp_obj->getType(),
									//'level' => $otp_obj->getLevel(),
									'trigger_time' => $otp_obj->getTriggerTime(),
                                                                        'max_time' => $otp_obj->getMaxTime(),
									'rate' => Misc::removeTrailingZeros( $otp_obj->getRate() ),
									'wage_group_id' => $otp_obj->getWageGroup(),
									'accrual_rate' => Misc::removeTrailingZeros( $otp_obj->getAccrualRate() ),
									'accrual_policy_id' => $otp_obj->getAccrualPolicyID(),
									'pay_stub_entry_account_id' => $otp_obj->getPayStubEntryAccountId(),
									'created_date' => $otp_obj->getCreatedDate(),
									'created_by' => $otp_obj->getCreatedBy(),
									'updated_date' => $otp_obj->getUpdatedDate(),
									'updated_by' => $otp_obj->getUpdatedBy(),
									'deleted_date' => $otp_obj->getDeletedDate(),
									'deleted_by' => $otp_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit') {
			$data = array( 'trigger_time' => 0,'max_time' => 0, 'rate' => '1.00', 'accrual_rate' => '1.00' );
		}

		$aplf = new AccrualPolicyListFactory();
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		$wglf = new WageGroupListFactory();
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		//Select box options;
		$data['type_options'] = $otpf->getOptions('type');
		$data['accrual_options'] = $accrual_options;
		$data['pay_stub_entry_options'] = $pay_stub_entry_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('otpf', $otpf);

$smarty->display('policy/EditOverTimePolicy.tpl');
?>