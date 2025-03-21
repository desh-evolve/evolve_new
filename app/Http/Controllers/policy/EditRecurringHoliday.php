<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditRecurringHoliday.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('holiday_policy','enabled')
		OR !( $permission->Check('holiday_policy','edit') OR $permission->Check('holiday_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Recurring Holiday')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$rhf = new RecurringHolidayFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rhf->setId( $data['id'] );
		$rhf->setCompany( $current_company->getId() );
		$rhf->setName( $data['name'] );
		$rhf->setType( $data['type_id'] );
		/*
		if ( isset($data['easter']) ) {
			$rhf->setEaster( TRUE );
		} else {
			$rhf->setEaster( FALSE );
		}
		*/
		$rhf->setSpecialDay( $data['special_day_id'] );
		$rhf->setWeekInterval( $data['week_interval'] );
		$rhf->setPivotDayDirection( $data['pivot_day_direction_id'] );

		if ( $data['type_id'] == 20 ) {
			$rhf->setDayOfWeek( $data['day_of_week_20'] );
		} elseif ( $data['type_id'] == 30 ) {
			$rhf->setDayOfWeek( $data['day_of_week_30'] );
		}

		$rhf->setDayOfMonth( $data['day_of_month'] );
		$rhf->setMonth( $data['month'] );

		$rhf->setAlwaysOnWeekDay( $data['always_week_day_id'] );

		if ( $rhf->isValid() ) {
			$rhf->Save();

			Redirect::Page( URLBuilder::getURL( NULL, 'RecurringHolidayList.php') );

			break;
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$rhlf = new RecurringHolidayListFactory();
			$rhlf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($rhlf as $rh_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $rh_obj->getId(),
									'name' => $rh_obj->getName(),
									'type_id' => $rh_obj->getType(),
									'special_day_id' => $rh_obj->getSpecialDay(),
									'week_interval' => $rh_obj->getWeekInterval(),
									'pivot_day_direction_id' => $rh_obj->getPivotDayDirection(),
									'day_of_week' => $rh_obj->getDayOfWeek(),
									'day_of_month' => $rh_obj->getDayOfMonth(),
									'month' => $rh_obj->getMonth(),
									'always_week_day_id' => $rh_obj->getAlwaysOnWeekDay(),
									'created_date' => $rh_obj->getCreatedDate(),
									'created_by' => $rh_obj->getCreatedBy(),
									'updated_date' => $rh_obj->getUpdatedDate(),
									'updated_by' => $rh_obj->getUpdatedBy(),
									'deleted_date' => $rh_obj->getDeletedDate(),
									'deleted_by' => $rh_obj->getDeletedBy()
								);
			}
		}

		//Select box options;
		$data['special_day_options'] = $rhf->getOptions('special_day');
		$data['type_options'] = $rhf->getOptions('type');
		$data['week_interval_options'] = $rhf->getOptions('week_interval');
		$data['pivot_day_direction_options'] = $rhf->getOptions('pivot_day_direction');
		$data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$data['month_of_year_options'] = TTDate::getMonthOfYearArray();
		$data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$data['always_week_day_options'] = $rhf->getOptions('always_week_day');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rhf', $rhf);

$smarty->display('policy/EditRecurringHoliday.tpl');
?>