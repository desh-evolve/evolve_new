<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditRecurringSchedule.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('recurring_schedule','enabled')
		OR !( $permission->Check('recurring_schedule','edit') OR $permission->Check('recurring_schedule','edit_own') OR $permission->Check('recurring_schedule','edit_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$title = 'Edit Recurring Schedule';
$smarty->assign('title', _('Edit Recurring Schedule'));

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data)) {
	if ( $data['start_date'] != '' ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( $data['end_date'] != '' ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	}
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$filter_data = NULL;
$permission_children_ids = array();
if ( $permission->Check('recurring_schedule','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('recurring_schedule','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('recurring_schedule','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$rscf = new RecurringScheduleControlFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rscf->StartTransaction();

		$fail_transaction = FALSE;
		if ( is_array($data['template_id']) ) {
			foreach( $data['template_id'] as $template_id ) {
				$rscf->setId( $data['id'] );
				$rscf->setCompany( $current_company->getId() );
				$rscf->setRecurringScheduleTemplateControl( $template_id );
				$rscf->setStartWeek( $data['start_week'] );
				$rscf->setStartDate( $data['start_date'] );
				$rscf->setEndDate( $data['end_date'] );
				if ( isset($data['auto_fill']) ) {
					$rscf->setAutoFill( TRUE );
				} else {
					$rscf->setAutoFill( FALSE );
				}

				if ( $rscf->isValid() ) {
					if ( $rscf->Save(FALSE) === FALSE ) {
						$fail_transaction = TRUE;
						break;
					}

					if ( isset($data['user_ids']) ) {
						$rscf->setUser( $data['user_ids'] );
					}

					if ( $rscf->isValid() ) {
						if ( $rscf->Save() === FALSE ) {
							$fail_transaction = TRUE;
							break;
						}
					} else {
						$fail_transaction = TRUE;
						break;
					}
				} else {
					$fail_transaction = TRUE;
					break;
				}
			}
		}

		if ( $fail_transaction == FALSE ) {
			//$rscf->FailTransaction();
			$rscf->CommitTransaction();

			Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleControlList.php') );
			break;
		} else {
			$rscf->FailTransaction();
		}

	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$rsclf = new RecurringScheduleControlListFactory();
			$rsclf->getByIdAndCompanyId( $id, $current_company->getID() );

			foreach ($rsclf as $rsc_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $rsc_obj->getId(),
									'template_id' => $rsc_obj->getRecurringScheduleTemplateControl(),
									'start_week' => $rsc_obj->getStartWeek(),
									'start_date' => $rsc_obj->getStartDate(),
									'end_date' => $rsc_obj->getEndDate(),
									'auto_fill' => $rsc_obj->getAutoFill(),
									'user_ids' => $rsc_obj->getUser(),
									'created_date' => $rsc_obj->getCreatedDate(),
									'created_by' => $rsc_obj->getCreatedBy(),
									'updated_date' => $rsc_obj->getUpdatedDate(),
									'updated_by' => $rsc_obj->getUpdatedBy(),
									'deleted_date' => $rsc_obj->getDeletedDate(),
									'deleted_by' => $rsc_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			Debug::Text('New Schedule', __FILE__, __LINE__, __METHOD__,10);
				$data = array(
									'start_week' => 1,
									'start_date' => TTDate::getBeginWeekEpoch( TTDate::getTime() ),
									'end_date' => NULL
								);

		}

		//Select box options;
		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );
		//$user_options = $ulf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$rstclf = new RecurringScheduleTemplateControlListFactory();
		$template_options = $rstclf->getByCompanyIdArray( $current_company->getId() );

		//Select box options;
		$data['template_options'] = $template_options;
		$data['user_options'] = $user_options;

		if ( isset($data['user_ids']) AND is_array($data['user_ids']) ) {
			$tmp_user_options = $user_options;
			foreach( $data['user_ids'] as $user_id ) {
				if( isset($tmp_user_options[$user_id]) ) {
					$filter_user_options[$user_id] = $tmp_user_options[$user_id];
				}
			}
			unset($user_id);
		}
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);

		//var_dump($week_rows);

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('rscf', $rscf);

$smarty->display('schedule/EditRecurringSchedule.tpl');
?>