<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: PayStubAmendmentList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('pay_stub_amendment','enabled')
		OR !( $permission->Check('pay_stub_amendment','view') OR $permission->Check('pay_stub_amendment','view_child') OR $permission->Check('pay_stub_amendment','view_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Pay Stub Amendment List')); // See index.php

BreadCrumb::setCrumb($title);
/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'form',
												'page',
												'filter_data',
												'sort_column',
												'sort_order',
												'saved_search_id',
												'filter_user_id',
												'recurring_ps_amendment_id',
												'ids',
												) ) );

$columns = array(
											'-1010-first_name' => _('First Name'),
											'-1020-middle_name' => _('Middle Name'),
											'-1030-last_name' => _('Last Name'),
											'-1040-status' => _('Status'),
											'-1050-type' => _('Type'),
											'-1060-pay_stub_account_name' => _('Account'),
											'-1070-effective_date' => _('Effective Date'),
											'-1080-amount' => _('Amount'),
											'-1090-rate' => _('Rate'),
											'-1100-units' => _('Units'),
											'-1110-description' => _('Description'),
											'-1120-ytd_adjustment' => _('YTD'),
											);

if ( $saved_search_id == '' AND !isset($filter_data['columns']) ) {
	//Default columns.
	$filter_data['columns'] = array(
								'-1010-first_name',
								'-1030-last_name',
								'-1040-status',
								'-1060-pay_stub_account_name',
								'-1070-effective_date',
								'-1080-amount',
								'-1110-description',
								);
	if ( $sort_column == '' ) {
		$sort_column = $filter_data['sort_column'] = 'effective_date';
		$sort_order = $filter_data['sort_order'] = 'desc';
	}
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();
$pplf = new PayPeriodListFactory();

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

Debug::Text('Form: '. $form, __FILE__, __LINE__, __METHOD__,10);
//Handle different actions for different forms.
$action = Misc::findSubmitButton();
if ( isset($form) AND $form != '' ) {
	$action = strtolower($form.'_'.$action);
} else {
	$action = strtolower($action);
}
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

switch ($action) {
	case 'add':

		Redirect::Page( URLBuilder::getURL( array('user_id' => $filter_user_id), 'EditPayStubAmendment.php', FALSE) );

		break;
	case 'delete':
	case 'undelete':
		if ( strtolower($action) == 'delete' ) {
			$delete = TRUE;
		} else {
			$delete = FALSE;
		}

		$psalf = new PayStubAmendmentListFactory();

		foreach ($ids as $id) {
			$psalf->getById( $id );
			foreach ($psalf as $pay_stub_amendment) {
				//Only delete PS amendments NOT in the paid status.
				if ( $pay_stub_amendment->getStatus() != 55 ) {
					$pay_stub_amendment->setDeleted($delete);
					$pay_stub_amendment->Save();
				}
			}
		}
		unset($pay_stub_amendment);

		Redirect::Page( URLBuilder::getURL( NULL, 'PayStubAmendmentList.php', TRUE) );

		break;
	case 'search_form_delete':
	case 'search_form_update':
	case 'search_form_save':
	case 'search_form_clear':
	case 'search_form_search':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$saved_search_id = UserGenericDataFactory::searchFormDataHandler( $action, $filter_data, URLBuilder::getURL(NULL, 'PayStubAmendmentList.php') );
	default:
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, $sort_column ) );
		Debug::Text('Sort Column: '. $sort_column, __FILE__, __LINE__, __METHOD__,10);
		Debug::Text('Saved Search ID: '. $saved_search_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_user_id) AND $filter_user_id != '' ) {
			$filter_data['user_id'] = $filter_user_id;
		}

		if ( isset($recurring_ps_amendment_id) AND $recurring_ps_amendment_id != '' ) {
			$filter_data['recurring_ps_amendment_id'] = $recurring_ps_amendment_id;
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

		$ulf = new UserListFactory();
		$psalf = new PayStubAmendmentListFactory();

		if ( $permission->Check('pay_stub_amendment','view') == FALSE ) {
			if ( $permission->Check('pay_stub_amendment','view_child') ) {
				$filter_data['permission_children_ids'] = $permission_children_ids;
			}
			if ( $permission->Check('pay_stub_amendment','view_own') ) {
				$filter_data['permission_children_ids'][] = $current_user->getId();
			}
		}

		$filter_data['start_date'] = NULL;
		$filter_data['end_date'] = NULL;
		if ( isset($filter_data['pay_period_id']) AND $filter_data['pay_period_id'] != '-1' ) {
			//Get Pay Period Start/End dates
			$pplf->getByIdAndCompanyId( Misc::trimSortPrefix( $filter_data['pay_period_id'] ), $current_company->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				$pp_obj = $pplf->getCurrent();
				$filter_data['start_date'] = $pp_obj->getStartDate();
				$filter_data['end_date'] = $pp_obj->getEndDate();
			}
		}
		$psalf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data, $current_user_prefs->getItemsPerPage(), $page, NULL, $sort_array );

		$pager = new Pager($psalf);

		$psealf = new PayStubEntryAccountListFactory();
		$pay_stub_entry_name_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50,60,65) );

		//Get pay periods
		$pplf->getByCompanyId( $current_company->getId() );
		$pay_period_options = $pplf->getArrayByListFactory( $pplf, FALSE, TRUE );

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$title_options = $utlf->getArrayByListFactory( $utlf, FALSE, TRUE );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );

		$rpsalf = new RecurringPayStubAmendmentListFactory();
		$rpsalf->getByCompanyId( $current_company->getId() );
		$recurring_ps_amendment_options = $rpsalf->getArrayByListFactory( $rpsalf, FALSE, TRUE );

		$uglf = new UserGroupListFactory();
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );

		foreach ($psalf as $psa_obj) {
			$user_obj = $ulf->getById( $psa_obj->getUser() )->getCurrent();

			if ( $psa_obj->getType() == 10 ) {
				$amount = $psa_obj->getAmount();
			} else {
				$amount = $psa_obj->getPercentAmount().'%';
			}
			$pay_stub_amendments[] = array(
								'id' => $psa_obj->getId(),
								'user_id' => $psa_obj->getUser(),
								'first_name' => $user_obj->getFirstName(),
								'middle_name' => $user_obj->getMiddleName(),
								'last_name' => $user_obj->getLastName(),
								'status_id' =>$psa_obj->getStatus(),
								'status' => Option::getByKey($psa_obj->getStatus(), $psa_obj->getOptions('status') ),
								'type_id' => $psa_obj->getType(),
								'type' => Option::getByKey($psa_obj->getType(), $psa_obj->getOptions('type') ),
								'effective_date' => TTDate::getDate('DATE', $psa_obj->getEffectiveDate() ),
								'pay_stub_account_name' => Option::getByKey( $psa_obj->getPayStubEntryNameId(), $pay_stub_entry_name_options ),
								'amount' => $amount,
								//'amount' => $psa_obj->getAmount(),
								//'percent_amount' => $psa_obj->getPercentAmount(),
								'rate' => $psa_obj->getRate(),
								'units' => $psa_obj->getUnits(),
								'description' => $psa_obj->getDescription(),
								'authorized' => $psa_obj->getAuthorized(),
								'ytd_adjustment' => Misc::HumanBoolean($psa_obj->getYTDAdjustment()),
								'deleted' => $psa_obj->getDeleted()
							);

		}

		$all_array_option = array('-1' => _('-- Any --'));

		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$filter_data['user_options'] = Misc::prependArray( $all_array_option, UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE ) );

		//Select box options;
		$filter_data['branch_options'] = Misc::prependArray( $all_array_option, $branch_options );
		$filter_data['department_options'] = Misc::prependArray( $all_array_option, $department_options );
		$filter_data['title_options'] = Misc::prependArray( $all_array_option, $title_options );
		$filter_data['group_options'] = Misc::prependArray( $all_array_option, $group_options );
		$filter_data['status_options'] = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['pay_period_options'] = Misc::prependArray( $all_array_option, $pay_period_options );
		$filter_data['recurring_ps_amendment_options'] = Misc::prependArray( $all_array_option, $recurring_ps_amendment_options );
		$filter_data['pay_stub_entry_name_options'] = Misc::prependArray( $all_array_option, $pay_stub_entry_name_options );

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

		$smarty->assign_by_ref('pay_stub_amendments', $pay_stub_amendments);
		$smarty->assign_by_ref('filter_data', $filter_data);
		$smarty->assign_by_ref('columns', $filter_columns );
		$smarty->assign('total_columns', count($filter_columns)+3 );

		$smarty->assign_by_ref('sort_column', $sort_column );
		$smarty->assign_by_ref('sort_order', $sort_order );
		$smarty->assign_by_ref('saved_search_id', $saved_search_id );

		$smarty->assign_by_ref('paging_data', $pager->getPageVariables() );

		break;
}
$smarty->display('pay_stub_amendment/PayStubAmendmentList.tpl');
?>