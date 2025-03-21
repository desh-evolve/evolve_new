<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 5135 $
 * $Id: UserExceptionList.php 5135 2011-08-16 22:54:35Z ipso $
 * $Date: 2011-08-16 15:54:35 -0700 (Tue, 16 Aug 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','view') OR $permission->Check('punch','view_own') OR $permission->Check('punch','view_child')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity( 11 );

$smarty->assign('title', __($title = 'Exception List')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'filter_data',
												'page',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => _('First Name'),
											'-1020-middle_name' => _('Middle Name'),
											'-1030-last_name' => _('Last Name'),
											'-1040-date_stamp' => _('Date'),
											'-1050-severity' => _('Severity'),
											'-1060-exception_policy_type' => _('Exception'),
											'-1070-exception_policy_type_id' => _('Code'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	if ( $permission->Check('punch','view') == TRUE OR $permission->Check('punch','view_child')) {
		$filter_data['columns'] = array(
									'-1010-first_name',
									'-1030-last_name',
									'-1040-date_stamp',
									'-1050-severity',
									'-1060-exception_policy_type',
									'-1070-exception_policy_type_id',
									);
	} else {
		$filter_data['columns'] = array(
									'-1040-date_stamp',
									'-1050-severity',
									'-1060-exception_policy_type',
									'-1070-exception_policy_type_id',
									);
	}
	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'severity';
		$sort_order = $filter_data['sort_order'] = 'desc';
	}
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
//Handle different actions for different forms.

$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
switch ($action) {
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'UserExceptionList.php') );
	default:
		BreadCrumb::setCrumb($title);

		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

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

		$ulf = new UserListFactory();
		$elf = new ExceptionListFactory();

		$hlf = new HierarchyListFactory();
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
		Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		$filter_data['permission_children_ids'] = array();
		if ( $permission->Check('punch','view') == FALSE ) {
			if ( $permission->Check('punch','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('punch','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$pplf = new PayPeriodListFactory();
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, FALSE );
		$pay_period_ids = array_keys((array)$pay_period_options);

		if ( isset($pay_period_ids[0]) AND ( !isset($filter_data['pay_period_id']) OR $filter_data['pay_period_id'] == '' ) ) {
			$filter_data['pay_period_id'] = '-1';
		}

		$filter_data['pay_period_status_id'] = array(10);

		$filter_data['type_id'] = array(30,40,50,55,60,70);
		if (  isset($filter_data['pre_mature']) ) {
			$filter_data['type_id'][] = 5;
		}

		$elf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($elf);

		$epf = new ExceptionPolicyFactory();
		$exception_policy_type_options = $epf->getOptions('type');
		$exception_policy_severity_options = $epf->getOptions('severity');

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

		$ulf = new UserListFactory();
		$user_options = $ulf->getByCompanyIdArray( $current_company->getID(), FALSE );

		foreach ($elf as $e_obj) {
			//Debug::Text('Status ID: '. $r_obj->getStatus() .' Status: '. $status_options[$r_obj->getStatus()], __FILE__, __LINE__, __METHOD__,10);
			$user_obj = $ulf->getById( $e_obj->getColumn('user_id') )->getCurrent();

			$rows[] = array(
								'id' => $e_obj->getId(),
								'user_date_id' => $e_obj->getUserDateID(),
								'user_id' => $e_obj->getColumn('user_id'),
								'first_name' => $user_obj->getFirstName(),
								'middle_name' => $user_obj->getMiddleName(),
								'last_name' => $user_obj->getLastName(),
								'user_full_name' => Option::getByKey($e_obj->getColumn('user_id'), $user_options),
								'date_stamp' => TTDate::getDate('DATE', TTDate::strtotime($e_obj->getColumn('user_date_stamp')) ),
								'date_stamp_epoch' => TTDate::strtotime($e_obj->getColumn('user_date_stamp')),
								'type_id' => $e_obj->getType(),
								'severity_id' => $e_obj->getColumn('severity_id'),
								'severity' => Option::getByKey($e_obj->getColumn('severity_id'), $exception_policy_severity_options),
								'exception_color' => $e_obj->getColor(),
								'exception_background_color' => $e_obj->getBackgroundColor(),
								'exception_policy_type_id' => $e_obj->getColumn('exception_policy_type_id'),
								'exception_policy_type' => Option::getByKey($e_obj->getColumn('exception_policy_type_id'), $exception_policy_type_options ),
								'created_date' => $e_obj->getCreatedDate(),
								'deleted' => $e_obj->getDeleted()
							);

		}

		
		$smarty->assign_by_ref('rows', $rows);

		$all_array_option = array('-1' => _('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array( 'permission_children_ids' => $filter_data['permission_children_ids'] ) );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
		$filter_data['severity_options'] = Misc::prependArray( $all_array_option, $exception_policy_severity_options );
		$filter_data['type_options'] = Misc::prependArray( $all_array_option, $exception_policy_type_options );

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

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('punch/UserExceptionList.tpl');
?>