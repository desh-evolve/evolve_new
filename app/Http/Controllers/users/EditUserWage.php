<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditUserWage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('wage','enabled')
		OR !( $permission->Check('wage','edit') OR $permission->Check('wage','edit_child') OR $permission->Check('wage','edit_own') OR $permission->Check('wage','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Edit Employee Wage')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_id',
												'saved_search_id',
												'wage_data'
												) ) );

if ( isset($wage_data) ) {
	if ( $wage_data['effective_date'] != '' ) {
		$wage_data['effective_date'] = TTDate::parseDateTime($wage_data['effective_date']);
	}
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

$uwf = new UserWageFactory();

$ulf = new UserListFactory();

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
				$uwf->setId($wage_data['id']);
				$uwf->setUser($user_id);
				$uwf->setWageGroup($wage_data['wage_group_id']);
				$uwf->setType($wage_data['type']);
				$uwf->setWage($wage_data['wage']);
				$uwf->setHourlyRate($wage_data['hourly_rate']);
                                $uwf->setBudgetoryAllowance($wage_data['budgetary_allowance']);
				$uwf->setWeeklyTime( TTDate::parseTimeUnit( $wage_data['weekly_time'] ) );
				$uwf->setEffectiveDate( $wage_data['effective_date'] );
				$uwf->setLaborBurdenPercent( 0 );
				$uwf->setNote( $wage_data['note'] );

				if ( $uwf->isValid() ) {
					$uwf->Save();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $user_id, 'saved_search_id' => $saved_search_id), 'UserWageList.php') );

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

			$uwlf = new UserWageListFactory();
			$uwlf->getByIdAndCompanyId($id, $current_company->getId() );
                        
                        

			foreach ($uwlf as $wage) {
				$user_obj = $ulf->getByIdAndCompanyId( $wage->getUser(), $current_company->getId() )->getCurrent();
                                
                                $budgetary_allowance = 0;
                                $udlf = new UserDeductionListFactory();
                                $udlf->getByUserIdAndCompanyDeductionId($wage->getUser(), 3);
                                if($udlf->getRecordCount()>0){
                                    foreach ($udlf as $udlf_obj){
                                        $budgetary_allowance = $udlf_obj->getUserValue1();
                                    }
                                }
                                
				if ( is_object($user_obj) ) {
					$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
					$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

					if ( $permission->Check('wage','edit')
							OR ( $permission->Check('wage','edit_own') AND $is_owner === TRUE )
							OR ( $permission->Check('wage','edit_child') AND $is_child === TRUE ) ) {

						$user_id = $wage->getUser();

						Debug::Text('Labor Burden Hourly Rate: '. $wage->getLaborBurdenHourlyRate( $wage->getHourlyRate() ), __FILE__, __LINE__, __METHOD__,10);
						$wage_data = array(
											'id' => $wage->getId(),
											'user_id' => $wage->getUser(),
											'wage_group_id' => $wage->getWageGroup(),
											'type' => $wage->getType(),
											'wage' => Misc::removeTrailingZeros( $wage->getWage() ),
                                                                                        'budgetary_allowance' => Misc::MoneyFormat( $budgetary_allowance , FALSE),
											'hourly_rate' => Misc::removeTrailingZeros( $wage->getHourlyRate() ),
											'weekly_time' => $wage->getWeeklyTime(),
											'effective_date' => $wage->getEffectiveDate(),
											'labor_burden_percent' => (float)$wage->getLaborBurdenPercent(),
											'note' => $wage->getNote(),
											'created_date' => $wage->getCreatedDate(),
											'created_by' => $wage->getCreatedBy(),
											'updated_date' => $wage->getUpdatedDate(),
											'updated_by' => $wage->getUpdatedBy(),
											'deleted_date' => $wage->getDeletedDate(),
											'deleted_by' => $wage->getDeletedBy()
										);

						$tmp_effective_date = TTDate::getDate('DATE', $wage->getEffectiveDate() );
					} else {
						$permission->Redirect( FALSE ); //Redirect
						exit;
					}
				}
			}
		} else {
			if ( $action != 'submit' ) {
                            
                                $budgetary_allowance = 0;
                                $udlf = new UserDeductionListFactory();
                                $udlf->getByUserIdAndCompanyDeductionId($user_id, 3);
                                if($udlf->getRecordCount()>0){
                                    foreach ($udlf as $udlf_obj){
                                        $budgetary_allowance = $udlf_obj->getUserValue1();
                                    }
                                }
                                
				$wage_data = array( 'effective_date' => TTDate::getTime(), 'labor_burden_percent' => 0 ,'budgetary_allowance' => Misc::MoneyFormat( $budgetary_allowance , FALSE));
			}
		}
		//Select box options;
		$wage_data['type_options'] = $uwf->getOptions('type');

		$wglf = new WageGroupListFactory();
		$wage_data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$crlf = new CurrencyListFactory();
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

		$ulf = new UserListFactory();
		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		$user_data = $ulf->getCurrent();
		if ( is_object( $user_data->getCurrencyObject() ) ) {
			$wage_data['currency_symbol'] = $user_data->getCurrencyObject()->getSymbol();
			$wage_data['iso_code'] = $user_data->getCurrencyObject()->getISOCode();
		}

		//Get pay period boundary dates for this user.
		//Include user hire date in the list.
		$pay_period_boundary_dates[TTDate::getDate('DATE', $user_data->getHireDate() )] = _('(Appointment Date)').' '. TTDate::getDate('DATE', $user_data->getHireDate() );
		$pay_period_boundary_dates = Misc::prependArray( array(-1 => _('(Choose Date)')), $pay_period_boundary_dates);

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getByUserId( $user_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pay_period_schedule_id = $ppslf->getCurrent()->getId();
			$pay_period_schedule_name = $ppslf->getCurrent()->getName();
			Debug::Text('Pay Period Schedule ID: '. $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = new PayPeriodListFactory();
			$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id, 10, NULL, NULL, array('transaction_date' => 'desc') );
			$pay_period_dates = NULL;
			foreach($pplf as $pay_period_obj) {
				//$pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getEndDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getEndDate() );
				if ( !isset($pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )])) {
					$pay_period_boundary_dates[TTDate::getDate('DATE', $pay_period_obj->getStartDate() )] = '('. $pay_period_schedule_name .') '.TTDate::getDate('DATE', $pay_period_obj->getStartDate() );
				}
			}
		} else {
			$smarty->assign('pay_period_schedule', FALSE);

			$uwf->Validator->isTrue(		'employee',
											FALSE,
											_('Employee is not currently assigned to a pay period schedule.').' <a href="'.URLBuilder::getURL( NULL, '../payperiod/PayPeriodScheduleList.php').'">'. _('Click here</a> to assign') );
		}

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('wage_data', $wage_data);

		$smarty->assign_by_ref('tmp_effective_date', $tmp_effective_date);
		$smarty->assign_by_ref('pay_period_boundary_date_options', $pay_period_boundary_dates);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);


		break;
}

$smarty->assign_by_ref('uwf', $uwf);

$smarty->display('users/EditUserWage.tpl');
?>