<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditPolicyGroup.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('policy_group','enabled')
		OR !( $permission->Check('policy_group','edit') OR $permission->Check('policy_group','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Policy Group')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$pgf = new PolicyGroupFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Debug::setVerbosity(11);

		$pgf->StartTransaction();

		$pgf->setId( $data['id'] );
		$pgf->setCompany( $current_company->getId() );
		$pgf->setName( $data['name'] );
		$pgf->setExceptionPolicyControlID( $data['exception_policy_control_id'] );

		if ( $pgf->isValid() ) {
			$pgf->Save(FALSE);

			if ( isset($data['user_ids'] ) ) {
				$pgf->setUser( $data['user_ids'] );
			} else {
				$pgf->setUser( array() );
			}

			if ( isset($data['over_time_policy_ids'] ) ) {
				$pgf->setOverTimePolicy( $data['over_time_policy_ids'] );
			} else {
				$pgf->setOverTimePolicy( array() );
			}

			if ( isset($data['premium_policy_ids'] ) ) {
				$pgf->setPremiumPolicy( $data['premium_policy_ids'] );
			} else {
				$pgf->setPremiumPolicy( array() );
			}

			if ( isset($data['round_interval_policy_ids']) ) {
				$pgf->setRoundIntervalPolicy( $data['round_interval_policy_ids'] );
			} else {
				$pgf->setRoundIntervalPolicy( array() );
			}

			if ( isset($data['accrual_policy_ids']) ) {
				$pgf->setAccrualPolicy( $data['accrual_policy_ids'] );
			} else {
				$pgf->setAccrualPolicy( array() );
			}

			if ( isset($data['meal_policy_ids']) ) {
				$pgf->setMealPolicy( $data['meal_policy_ids'] );
			} else {
				$pgf->setMealPolicy( array() );
			}

			if ( isset($data['break_policy_ids']) ) {
				$pgf->setBreakPolicy( $data['break_policy_ids'] );
			} else {
				$pgf->setBreakPolicy( array() );
			}

			if ( isset($data['holiday_policy_ids']) ) {
				$pgf->setHolidayPolicy( $data['holiday_policy_ids'] );
			} else {
				$pgf->setHolidayPolicy( array() );
			}

			if ( $pgf->isValid() ) {
				$pgf->Save();
				$pgf->CommitTransaction();

				Redirect::Page( URLBuilder::getURL( NULL, 'PolicyGroupList.php') );

				break;
			}


		}
		$pgf->FailTransaction();

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$pglf = new PolicyGroupListFactory();
			$pglf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($pglf as $pg_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $pg_obj->getId(),
									'name' => $pg_obj->getName(),
									'meal_policy_ids' => $pg_obj->getMealPolicy(),
									'break_policy_ids' => $pg_obj->getBreakPolicy(),
									'holiday_policy_ids' => $pg_obj->getHolidayPolicy(),
									'exception_policy_control_id' => $pg_obj->getExceptionPolicyControlID(),
									'user_ids' => $pg_obj->getUser(),
									'over_time_policy_ids' => $pg_obj->getOverTimePolicy(),
									'premium_policy_ids' => $pg_obj->getPremiumPolicy(),
									'round_interval_policy_ids' => $pg_obj->getRoundIntervalPolicy(),
									'accrual_policy_ids' => $pg_obj->getAccrualPolicy(),
									'created_date' => $pg_obj->getCreatedDate(),
									'created_by' => $pg_obj->getCreatedBy(),
									'updated_date' => $pg_obj->getUpdatedDate(),
									'updated_by' => $pg_obj->getUpdatedBy(),
									'deleted_date' => $pg_obj->getDeletedDate(),
									'deleted_by' => $pg_obj->getDeletedBy()
								);
			}
		}

		$none_array_option = array('0' => _('-- None --') );

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), FALSE, TRUE );

		$otplf = new OverTimePolicyListFactory();
		$over_time_policy_options = Misc::prependArray( $none_array_option, $otplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$pplf = new PremiumPolicyListFactory();
		$premium_policy_options = Misc::prependArray( $none_array_option, $pplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$riplf = new RoundIntervalPolicyListFactory();
		$round_interval_policy_options = Misc::prependArray( $none_array_option, $riplf->getByCompanyIDArray( $current_company->getId(), FALSE ) );

		$mplf = new MealPolicyListFactory();
		$meal_options = Misc::prependArray( $none_array_option, $mplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$bplf = new BreakPolicyListFactory();
		$break_options = Misc::prependArray( $none_array_option, $bplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$epclf = new ExceptionPolicyControlListFactory();
		$exception_options = Misc::prependArray( $none_array_option, $epclf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$hplf = new HolidayPolicyListFactory();
		$holiday_policy_options = Misc::prependArray( $none_array_option, $hplf->getByCompanyIdArray( $current_company->getId(), FALSE ) );

		$aplf = new AccrualPolicyListFactory();
		$aplf->getByCompanyIdAndTypeID( $current_company->getId(), array(20, 30) ); //Calendar and Hour based.
		$accrual_options = Misc::prependArray( $none_array_option, $aplf->getArrayByListFactory( $aplf, FALSE ) );

		//Select box options;
		$data['user_options'] = $user_options;
		$data['over_time_policy_options'] = $over_time_policy_options;
		$data['premium_policy_options'] = $premium_policy_options;
		$data['round_interval_policy_options'] = $round_interval_policy_options;
		$data['accrual_policy_options'] = $accrual_options;
		$data['meal_options'] = $meal_options;
		$data['break_options'] = $break_options;
		$data['exception_options'] = $exception_options;
		$data['holiday_policy_options'] = $holiday_policy_options;

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $data['user_ids'] as $user_id ) {
				if ( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('pgf', $pgf);

$smarty->display('policy/EditPolicyGroup.tpl');
?>