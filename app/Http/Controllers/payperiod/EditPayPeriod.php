<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditPayPeriod.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('pay_period_schedule','enabled')
		OR !( $permission->Check('pay_period_schedule','edit') OR $permission->Check('pay_period_schedule','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Pay Period')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'pay_period_schedule_id',
												'data'
												) ) );

if ( isset($data) ) {
	if ( isset($data['start_date']) ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( isset($data['end_date']) ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	}
	if ( isset($data['transaction_date']) ) {
		$data['transaction_date'] = TTDate::parseDateTime( $data['transaction_date'] );
	}
	if ( isset($data['advance_end_date']) ) {
		$data['advance_end_date'] = TTDate::parseDateTime( $data['advance_end_date'] );
	}
	if ( isset($data['advance_transaction_date']) ) {
		$data['advance_transaction_date'] = TTDate::parseDateTime( $data['advance_transaction_date'] );
	}
}

$ppf = new PayPeriodFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$ppf->StartTransaction();

		if ( $data['id'] == '' ) {
			$ppf->setCompany( $current_company->getId() );
			$ppf->setStatus(10); //Open
		} else {
			$ppf->setId($data['id']);
		}

		$ppf->setPayPeriodSchedule($data['pay_period_schedule_id']);
		if ( is_object( $ppf->getPayPeriodScheduleObject() ) ) {
			$ppf->getPayPeriodScheduleObject()->setPayPeriodTimeZone();
		}
		$ppf->setStartDate($data['start_date']);
		$ppf->setEndDate($data['end_date']+59);
		$ppf->setTransactionDate($data['transaction_date']+59);

		if ( isset($data['advance_end_date']) ) {
			$ppf->setAdvanceEndDate($data['advance_end_date']);
		}
		if ( isset($data['advance_transaction_date']) ) {
			$ppf->setAdvanceTransactionDate($data['advance_transaction_date']);
		}

		$ppf->setEnableImportData( TRUE ); //Import punches when creating new pay periods.

		if ( $ppf->isValid() ) {
			$ppf->Save();

			$ppf->CommitTransaction();
			Redirect::Page( URLBuilder::getURL( array('id' => $data['pay_period_schedule_id'] ), 'PayPeriodList.php') );
			break;
		}

		$ppf->FailTransaction();

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$pplf = new PayPeriodListFactory();
			$pplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($pplf as $pp_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
													'id' => $pp_obj->getId(),
													'company_id' => $pp_obj->getCompany(),
													'pay_period_schedule_id' => $pp_obj->getPayPeriodSchedule(),
													'pay_period_schedule_type_id' => $pp_obj->getPayPeriodScheduleObject()->getType(),
													'start_date' => $pp_obj->getStartDate(),
													'end_date' => $pp_obj->getEndDate(),
													'transaction_date' => $pp_obj->getTransactionDate(),
													'advance_end_date' => $pp_obj->getAdvanceEndDate(),
													'advance_transaction_date' => $pp_obj->getAdvanceTransactionDate(),
													'deleted' => $pp_obj->getDeleted(),
													'created_date' => $pp_obj->getCreatedDate(),
													'created_by' => $pp_obj->getCreatedBy(),
													'updated_date' => $pp_obj->getUpdatedDate(),
													'updated_by' => $pp_obj->getUpdatedBy(),
													'deleted_date' => $pp_obj->getDeletedDate(),
													'deleted_by' => $pp_obj->getDeletedBy()
												);
			}
		} else {
			if ( isset($pay_period_schedule_id) AND $pay_period_schedule_id != '') {
				$ppslf = new PayPeriodScheduleListFactory();
				$ppslf->getByIdAndCompanyId( $pay_period_schedule_id, $current_company->getId() );
				if ( $ppslf->getRecordCount() > 0 ) {
					$data['pay_period_schedule_type_id'] = $ppslf->getCurrent()->getType();
				}

				$data['pay_period_schedule_id'] = $pay_period_schedule_id;

				//Get end date of previous pay period, and default the start date of the new pay period to that.
				$pplf = new PayPeriodListFactory();
				$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id, 1, NULL, NULL, array('start_date' => 'desc') );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf as $pp_obj) {
						$data['start_date'] = $pp_obj->getEndDate()+1;
						$data['end_date'] = $pp_obj->getEndDate()+86400;
					}
				}
			}
		}

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('ppf', $ppf);

$smarty->display('payperiod/EditPayPeriod.tpl');
?>