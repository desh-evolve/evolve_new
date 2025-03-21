<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditROE.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('roe','enabled')
		OR !( $permission->Check('roe','edit') OR $permission->Check('roe','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Record Of Employment')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'roe_data',
												'setup_data',
												'user_id'
												) ) );

$roef = new ROEFactory();

if ( isset($roe_data) ) {
	if ( $roe_data['first_date'] != '' ) {
		$roe_data['first_date'] = TTDate::parseDateTime($roe_data['first_date']);
	}
	if ( $roe_data['last_date'] != '' ) {
		$roe_data['last_date'] = TTDate::parseDateTime($roe_data['last_date']);
	}
	if ( $roe_data['pay_period_end_date'] != '' ) {
		$roe_data['pay_period_end_date'] = TTDate::parseDateTime($roe_data['pay_period_end_date']);
	}
	if ( $roe_data['recall_date'] != '' ) {
		$roe_data['recall_date'] = TTDate::parseDateTime($roe_data['recall_date']);
	}
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Save report setup data
		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $roef->getTable() );
		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugdf->setID( $ugdlf->getCurrent()->getID() );
		}
		$ugdf->setCompany( $current_company->getId() );
		$ugdf->setScript( $roef->getTable() );
		$ugdf->setName( $title );
		$ugdf->setData( $setup_data );
		$ugdf->setDefault( TRUE );
		if ( $ugdf->isValid() ) {
			$ugdf->Save();
		}

		if ( !empty($roe_data['id']) ) {
			$roef->setId( $roe_data['id'] );
		}

		$roef->setUser( $roe_data['user_id'] );
		$roef->setPayPeriodType( $roe_data['pay_period_type_id'] );
		$roef->setCode( $roe_data['code_id'] );

		if ( $roe_data['first_date'] != '' ) {
			$roef->setFirstDate( $roe_data['first_date'] );
		}
		if ( $roe_data['last_date'] != '' ) {
			$roef->setLastDate( $roe_data['last_date']);
		}
		if ( $roe_data['pay_period_end_date'] != '' ) {
			$roef->setPayPeriodEndDate( $roe_data['pay_period_end_date'] );
		}
		if ( $roe_data['recall_date'] != '' ) {
			$roef->setRecallDate( $roe_data['recall_date'] );
		}

		$roef->setSerial( $roe_data['serial'] );
		$roef->setComments( $roe_data['comments'] );

		if ( $roef->isValid() ) {
			$roef->setEnableReCalculate( TRUE );
			if ( isset($roe_data['generate_pay_stub']) AND $roe_data['generate_pay_stub'] == 1 ) {
				$roef->setEnableGeneratePayStub(TRUE );
			} else {
				$roef->setEnableGeneratePayStub( FALSE );
			}
			if ( isset($roe_data['release_accruals']) AND $roe_data['release_accruals'] == 1 ) {
				$roef->setEnableReleaseAccruals(TRUE );
			} else {
				$roef->setEnableReleaseAccruals( FALSE );
			}

			$roef->Save();

			$ugsf = new UserGenericStatusFactory();
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$next_page = URLBuilder::getURL( array('user_id' => $roe_data['user_id'] ), '../roe/ROEList.php');

			Redirect::Page( URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Record of Employement', 'batch_next_page' => $next_page), '../users/UserGenericStatusList.php') );

			unset($ugsf);

			//Redirect::Page( URLBuilder::getURL( array('user_id' => $roe_data['user_id'] ), 'ROEList.php') );

			break;
		}

	default:
		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $roef->getTable() );
		if ( $ugdlf->getRecordCount() > 0 ) {
			Debug::Text('Found Company Report Setup!', __FILE__, __LINE__, __METHOD__,10);
			$ugd_obj = $ugdlf->getCurrent();
			$setup_data = $ugd_obj->getData();
		}
		unset($ugd_obj);

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$roelf = new ROEListFactory();

			$roelf->getById( $id );

			foreach ($roelf as $roe) {
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$roe_data = array(
									'id' => $roe->getId(),
									'user_id' => $roe->getUser(),
									'pay_period_type_id' => $roe->getPayPeriodType(),
									'code_id' => $roe->getCode(),
									'first_date' => $roe->getFirstDate(),
									'last_date' => $roe->getLastDate(),
									'pay_period_end_date' => $roe->getPayPeriodEndDate(),
									'recall_date' => $roe->getRecallDate(),
									'insurable_hours' => $roe->getInsurableHours(),
									'insurable_earnings' => $roe->getInsurableEarnings(),
									'vacation_pay' => $roe->getVacationPay(),
									'serial' => $roe->getSerial(),
									'comments' => $roe->getComments(),
									'created_date' => $roe->getCreatedDate(),
									'created_by' => $roe->getCreatedBy(),
									'updated_date' => $roe->getUpdatedDate(),
									'updated_by' => $roe->getUpdatedBy(),
									'deleted_date' => $roe->getDeletedDate(),
									'deleted_by' => $roe->getDeletedBy()
								);
			}
		} elseif ( !isset($action))  {
			//Get all the data we should need for this ROE in regards to pay period and such
			//Guess for end dates...

			//get User data for hire date
			$ulf = new UserListFactory();
			$user_obj = $ulf->getById($user_id)->getCurrent();

			$plf = new PunchListFactory();

			//Is there a previous ROE? If so, find first shift back since ROE was issued.
			$rlf = new ROEListFactory();
			$rlf->getLastROEByUserId( $user_id );
			if ( $rlf->getRecordCount() > 0 ) {
				$roe_obj = $rlf->getCurrent();

				Debug::Text('Previous ROE Last Date: '. TTDate::getDate('DATE+TIME', $roe_obj->getLastDate() ) , __FILE__, __LINE__, __METHOD__,10);
				//$plf->getFirstPunchByUserIDAndEpoch( $user_id, $roe_obj->getLastDate() );
				$plf->getNextPunchByUserIdAndEpoch( $user_id, $roe_obj->getLastDate() );
				if ( $plf->getRecordCount() > 0 ) {
					$first_date = $plf->getCurrent()->getTimeStamp();
				}
			}

			if ( !isset($first_date) OR $first_date == '' ) {
				$first_date = $user_obj->getHireDate();
			}
			Debug::Text('First Date: '. TTDate::getDate('DATE+TIME', $first_date) , __FILE__, __LINE__, __METHOD__,10);

			//Get last shift worked (not scheduled)
			$plf->getLastPunchByUserId( $user_id );
			if ( $plf->getRecordCount() > 0 ) {
				$punch_obj = $plf->getCurrent();
				$last_date = $punch_obj->getPunchControlObject()->getUserDateObject()->getDateStamp();
			} else {
				$last_date = TTDate::getTime();
			}

			Debug::Text('Last Punch Date: '. TTDate::getDate('DATE+TIME', $last_date) , __FILE__, __LINE__, __METHOD__,10);

			//Get pay period of last shift workd
			$plf = new PayPeriodListFactory();
			$pay_period_obj = $plf->getByUserIdAndEndDate( $user_id, $last_date )->getCurrent();

			$pay_period_type_id = FALSE;
			if ( is_object( $pay_period_obj->getPayPeriodScheduleObject() ) ) {
				$pay_period_type_id = $pay_period_obj->getPayPeriodScheduleObject()->getType();
			}
			$roe_data = array(
								'user_id' => $user_id,
								'pay_period_type_id' => $pay_period_type_id,
								'first_date' => $first_date,
								'last_date' => $last_date,
								'pay_period_end_date' => $pay_period_obj->getEndDate()
								);
		}

		//Select box options;
		$roe_data['code_options'] = $roef->getOptions('code');

		$ppsf = new PayPeriodScheduleFactory();
		$roe_data['pay_period_type_options'] = $ppsf->getOptions('type');
		unset($roe_data['pay_period_type_options'][5]);

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$smarty->assign_by_ref('user_options', $user_options);

		//PSEA accounts
		$psealf = new PayStubEntryAccountListFactory();
		$earning_pay_stub_entry_account_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,30,40), TRUE );
		$smarty->assign_by_ref('earning_pay_stub_entry_account_options', $earning_pay_stub_entry_account_options);

		$smarty->assign_by_ref('roe_data', $roe_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		break;
}

$smarty->assign_by_ref('roef', $roef);

$smarty->display('roe/EditROE.tpl');
?>