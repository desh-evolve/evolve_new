<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RecurringScheduleTemplateControlList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('recurring_schedule_template','enabled')
		OR !( $permission->Check('recurring_schedule_template','view') OR $permission->Check('recurring_schedule_template','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Recurring Schedule Template List')); // See index.php
BreadCrumb::setCrumb($title);
/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'ids',
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'sort_column' => $sort_column,
													'sort_order' => $sort_order,
													'page' => $page
												) );

$sort_array = NULL;
if ( $sort_column != '' ) {
	$sort_array = array($sort_column => $sort_order);
}

Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringScheduleTemplate.php', FALSE) );

		break;
	case 'copy':
		$rstf = new RecurringScheduleTemplateFactory();
		$rstlf = new RecurringScheduleTemplateListFactory();
		$rstclf = new RecurringScheduleTemplateControlListFactory();

		foreach ($ids as $id) {
			$rstclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rstclf as $rstc_obj) {
				$rstc_obj->StartTransaction();

				//Get week data
				$rstlf->getByRecurringScheduleTemplateControlId( $rstc_obj->getId() );
				if ( $rstlf->getRecordCount() > 0 ) {
					foreach( $rstlf as $rst_obj) {
						$week_rows[$rst_obj->getId()] = array(
											'id' => $rst_obj->getId(),
											'week' => $rst_obj->getWeek(),
											'sun' => $rst_obj->getSun(),
											'mon' => $rst_obj->getMon(),
											'tue' => $rst_obj->getTue(),
											'wed' => $rst_obj->getWed(),
											'thu' => $rst_obj->getThu(),
											'fri' => $rst_obj->getFri(),
											'sat' => $rst_obj->getSat(),
											'start_time' => $rst_obj->getStartTime(),
											'end_time' => $rst_obj->getEndTime(),
											'total_time' => $rst_obj->getTotalTime(),
											'schedule_policy_id' => $rst_obj->getSchedulePolicyID(),
											'branch_id' => $rst_obj->getBranch(),
											'department_id' => $rst_obj->getDepartment(),
											'job_id' => $rst_obj->getJob(),
											'job_item_id' => $rst_obj->getJobItem()
											);
					}
				}

				$rstc_obj->setId(FALSE);
				$rstc_obj->setName( Misc::generateCopyName( $rstc_obj->getName() ) );
				$rstc_obj->setCreatedBy( $current_user->getId() ); //Make sure the created by are changed to the current user.
				$rstc_obj->setCreatedDate( time() );
				if ( $rstc_obj->isValid() ) {
					$rstc_id = $rstc_obj->Save();

					if ( count($week_rows) > 0 ) {
						foreach( $week_rows as $week_row_id => $week_row ) {
							Debug::Text('Row ID: '. $week_row_id .' Week: '. $week_row['week'] , __FILE__, __LINE__, __METHOD__,10);

							if ( $week_row['week'] != '' AND $week_row['week'] > 0 ) {
								$rstf->setRecurringScheduleTemplateControl( $rstc_id );
								$rstf->setWeek( $week_row['week'] );

								$rstf->setSun( $week_row['sun'] );
								$rstf->setMon( $week_row['mon'] );
								$rstf->setTue( $week_row['tue'] );
								$rstf->setWed( $week_row['wed'] );
								$rstf->setThu( $week_row['thu'] );
								$rstf->setFri( $week_row['fri'] );
								$rstf->setSat( $week_row['sat'] );

								$rstf->setStartTime( $week_row['start_time'] );
								$rstf->setEndTime( $week_row['end_time'] );

								$rstf->setSchedulePolicyID( $week_row['schedule_policy_id'] );
								$rstf->setBranch( $week_row['branch_id'] );
								$rstf->setDepartment( $week_row['department_id'] );

								if ( isset($week_row['job_id']) ) {
									$rstf->setJob( $week_row['job_id'] );
								}

								if ( isset($week_row['job_item_id']) ) {
									$rstf->setJobItem( $week_row['job_item_id'] );
								}

								if ( $rstf->isValid() ) {
									Debug::Text('Saving Week Row ID: '. $week_row_id, __FILE__, __LINE__, __METHOD__,10);
									$rstf->Save();
								}
							}
						}
					}

					$rstc_obj->CommitTransaction();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleTemplateControlList.php') );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rstclf = new RecurringScheduleTemplateControlListFactory();

		foreach ($ids as $id) {
			$rstclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rstclf as $rstc_obj) {
				$rstc_obj->setDeleted($delete);
				if ( $rstc_obj->isValid() ) {
					$rstc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleTemplateControlList.php') );

		break;

	default:
		$rstclf = new RecurringScheduleTemplateControlListFactory();

		$filter_data = NULL;
		if ( $permission->Check('recurring_schedule_template','view') == FALSE ) {
			$filter_data['created_by'] = array( $current_user->getId() );
		}
		$rstclf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		//$rstclf->getByCompanyId( $current_company->getId(), $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($rstclf);

		foreach ($rstclf as $rstc_obj) {

			$rows[] = array(
								'id' => $rstc_obj->getId(),
								'name' => $rstc_obj->getName(),
								'description' => $rstc_obj->getDescription(),

								'is_owner' => $permission->isOwner( $rstc_obj->getCreatedBy(), NULL ),

								'deleted' => $rstc_obj->getDeleted()
							);

		}
		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('schedule/RecurringScheduleTemplateControlList.tpl');
?>