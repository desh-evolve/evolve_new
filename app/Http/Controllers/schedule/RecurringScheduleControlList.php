<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: RecurringScheduleControlList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('recurring_schedule','enabled')
		OR !( $permission->Check('recurring_schedule','view') OR $permission->Check('recurring_schedule','view_own') OR $permission->Check('recurring_schedule','view_child') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);

$smarty->assign('title', __($title = 'Recurring Schedule List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'page',
												'sort_column',
												'sort_order',
												'filter_data',
												'saved_search_id',
												'filter_template_id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => _('First Name'),
											'-1020-middle_name' => _('Middle Name'),
											'-1030-last_name' => _('Last Name'),
											'-1040-name' => _('Name'),
											'-1050-description' => _('Description'),
											'-1070-start_date' => _('Start Date'),
											'-1080-end_date' => _('End Date'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	$filter_data['columns'] = array(
								'-1010-first_name',
								'-1030-last_name',
								'-1040-name',
								'-1050-description',
								'-1070-start_date',
								'-1080-end_date',
								);

	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'last_name';
		$sort_order = $filter_data['sort_order'] = 'asc';
	}
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( NULL, 'EditRecurringSchedule.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$rsclf = new RecurringScheduleControlListFactory();

		foreach ($ids as $id => $user_ids) {
			$rsclf->getByIdAndCompanyId($id, $current_company->getId() );
			foreach ($rsclf as $rsc_obj) {
				//Get all users for this schedule.
				$current_users = $rsc_obj->getUser();

				$user_diff_arr = array_diff( (array)$current_users, (array)$user_ids );
				//Debug::Arr($user_diff_arr,'User Diff:', __FILE__, __LINE__, __METHOD__,10);

				if ( is_array($user_diff_arr) AND count($user_diff_arr) == 0 ) {
					Debug::Text('No more users assigned to schedule, deleting...', __FILE__, __LINE__, __METHOD__,10);

					//No more users assigned to this schedule, delete the whole thing.
					$rsc_obj->setDeleted($delete);
				} elseif ( is_array($user_diff_arr) AND count($user_diff_arr) > 0 ) {
					Debug::Text('Still more users assigned to schedule, removing users only...', __FILE__, __LINE__, __METHOD__,10);
					//Still users assigned to this schedule, remove users from it.
					$rsc_obj->setUser( $user_diff_arr );
				}

				if ( $rsc_obj->isValid() ) {
					$rsc_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'RecurringScheduleControlList.php') );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'RecurringScheduleControlList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_template_id) AND $filter_template_id != '' ) {
			$filter_data['template_id'] = array($filter_template_id);
		}

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],	array(
															'sort_column' => Misc::trimSortPrefix($sort_column),
															'sort_order' => $sort_order,
															'saved_search_id' => $saved_search_id,
															'page' => $page
														) );

		$rsclf = new RecurringScheduleControlListFactory();
		$ulf = new UserListFactory();

		if ( $permission->Check('recurring_schedule','view') == FALSE ) {
			if ( $permission->Check('recurring_schedule','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('recurring_schedule','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$rsclf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($rsclf);

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

		$uglf = new UserGroupListFactory();
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

		$rstclf = new RecurringScheduleTemplateControlListFactory();
		$template_options = $rstclf->getByCompanyIdArray( $current_company->getId(), FALSE, TRUE );

		foreach ($rsclf as $rsc_obj) {
			$user_id = $rsc_obj->getColumn('user_id');

			$ulf = new UserListFactory();
			$ulf->getByID( $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				$u_obj = $ulf->getCurrent();
			} else {
				//Skip this row.
				Debug::Text('Skipping Row: User ID: '. $user_id , __FILE__, __LINE__, __METHOD__,10);
				continue;
			}

			$rows[] = array(
								'id' => $rsc_obj->getId(),
								'user_id' => $user_id,
								'name' => $rsc_obj->getColumn('name'),
								'description' => $rsc_obj->getColumn('description'),
								'start_week' => $rsc_obj->getStartWeek(),
								'start_date' => $rsc_obj->getStartDate(),
								'end_date' => $rsc_obj->getEndDate(),
								'first_name' => $u_obj->getFirstName(),
								'middle_name' => $u_obj->getMiddleName(),
								'last_name' => $u_obj->getLastName(),
								'user_full_name' => $u_obj->getFullName(TRUE),

								'is_owner' => $permission->isOwner( $u_obj->getCreatedBy(), $u_obj->getId() ),
								'is_child' => $permission->isChild( $u_obj->getId(), $permission_children_ids ),

								'deleted' => $rsc_obj->getDeleted()
							);

		}

		$all_array_option = array('-1' => _('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['template_options'] = Misc::prependArray( $all_array_option, $template_options );
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );

		$filter_data['saved_search_options'] = $ugdlf->getArrayByListFactory( $ugdlf->getByUserIdAndScript( $current_user->getId(), $_SERVER['SCRIPT_NAME']), FALSE );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['columns'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['columns'], $columns );

		$filter_data['sort_options'] = Misc::trimSortPrefix($columns);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray(TRUE);

		foreach( $filter_data['columns'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}
		unset($column_key);

		$smarty->assign_by_ref('rows', $rows);

		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('schedule/RecurringScheduleControlList.tpl');
?>