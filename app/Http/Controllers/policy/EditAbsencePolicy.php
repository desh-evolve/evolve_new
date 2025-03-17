<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4953 $
 * $Id: EditAbsencePolicy.php 4953 2011-07-08 17:57:55Z ipso $
 * $Date: 2011-07-08 10:57:55 -0700 (Fri, 08 Jul 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('absence_policy','enabled')
		OR !( $permission->Check('absence_policy','edit') OR $permission->Check('absence_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Absence Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$apf = TTnew( 'AbsencePolicyFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$apf->setId( $data['id'] );
		$apf->setCompany( $current_company->getId() );
		$apf->setName( $data['name'] );
		$apf->setType( $data['type_id'] );
		$apf->setRate( $data['rate'] );
		$apf->setWageGroup( $data['wage_group_id'] );
		$apf->setAccrualRate( $data['accrual_rate'] );
		$apf->setAccrualPolicyID( $data['accrual_policy_id'] );
		$apf->setPayStubEntryAccountID( $data['pay_stub_entry_account_id'] );

		if ( $apf->isValid() ) {
			$apf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'AbsencePolicyList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$aplf->getByIdAndCompanyID( $id, $current_company->getId() );

			foreach ($aplf as $ap_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $ap_obj->getId(),
									'name' => $ap_obj->getName(),
									'type_id' => $ap_obj->getType(),
									'rate' => Misc::removeTrailingZeros( $ap_obj->getRate() ),
									'wage_group_id' => $ap_obj->getWageGroup(),
									'accrual_rate' => Misc::removeTrailingZeros( $ap_obj->getAccrualRate() ),
									'pay_stub_entry_account_id' => $ap_obj->getPayStubEntryAccountID(),
									'accrual_policy_id' => $ap_obj->getAccrualPolicyID(),
									'created_date' => $ap_obj->getCreatedDate(),
									'created_by' => $ap_obj->getCreatedBy(),
									'updated_date' => $ap_obj->getUpdatedDate(),
									'updated_by' => $ap_obj->getUpdatedBy(),
									'deleted_date' => $ap_obj->getDeletedDate(),
									'deleted_by' => $ap_obj->getDeletedBy()
								);
			}
		} else {
			$data = array(
						  'rate' => '1.00',
						  'accrual_rate' => '1.00',
						  );

		}

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		$wglf = TTnew( 'WageGroupListFactory' );
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		//Select box options;
		$data['type_options'] = $apf->getOptions('type');
		$data['accrual_options'] = $accrual_options;
		$data['pay_stub_entry_options'] = $pay_stub_entry_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('apf', $apf);

$smarty->display('policy/EditAbsencePolicy.tpl');
?>