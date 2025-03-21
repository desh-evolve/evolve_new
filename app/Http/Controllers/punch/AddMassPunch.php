<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: AddMassPunch.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');

//Debug::setVerbosity(11);

$skip_message_check = TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('punch','enabled')
		OR !( $permission->Check('punch','edit')
				OR $permission->Check('punch','edit_own')
				OR $permission->Check('punch','edit_child')
				 ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'Mass Punch')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'pc_data',
												'filter_user_id'												
												) ) );

$punch_full_time_stamp = NULL;
if ( isset($pc_data) ) {
	if ( $pc_data['start_date_stamp'] != ''
			AND !is_numeric($pc_data['start_date_stamp'])
			AND $pc_data['end_date_stamp'] != ''
			AND !is_numeric($pc_data['end_date_stamp'])
			AND $pc_data['time_stamp'] != ''
			AND !is_numeric($pc_data['time_stamp'])
			) {
		$pc_data['start_punch_full_time_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp'].' '.$pc_data['time_stamp']);
		$pc_data['end_punch_full_time_stamp'] = TTDate::parseDateTime($pc_data['end_date_stamp'].' '.$pc_data['time_stamp']);
		$pc_data['time_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp'].' '.$pc_data['time_stamp']);
	} else {
		$pc_data['start_punch_full_time_stamp'] = NULL;
		$pc_data['end_punch_full_time_stamp'] = NULL;
	}

	if ( $pc_data['start_date_stamp'] != '') {
		$pc_data['start_date_stamp'] = TTDate::parseDateTime($pc_data['start_date_stamp']);
	}
	if ( $pc_data['end_date_stamp'] != '') {
		$pc_data['end_date_stamp'] = TTDate::parseDateTime($pc_data['end_date_stamp']);
	}
	
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$hlf = new HierarchyListFactory();
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
$filter_data = array();
//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
if ( $permission->Check('punch','edit') == FALSE ) {
	if ( $permission->Check('punch','edit_child') ) {
		$filter_data['permission_children_ids'] = $permission_children_ids;
	}
	if ( $permission->Check('punch','edit_own') ) {
		$filter_data['permission_children_ids'][] = $current_user->getId();
	}
}

$pcf = new PunchControlFactory();
$pf = new PunchFactory();
$ulf = new UserListFactory();

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$fail_transaction = FALSE;

		if ( TTDate::getDayDifference( $pc_data['start_date_stamp'], $pc_data['end_date_stamp']) > 31 ) {
			Debug::Text('Date Range Exceeds 31 days, truncating', __FILE__, __LINE__, __METHOD__,10);
			$pc_data['end_date_stamp'] = $pc_data['start_date_stamp'] + (86400*31);
		}

		if ( isset($filter_user_id) AND is_array($filter_user_id) AND count($filter_user_id) > 0 ) {
			Redirect::Page( URLBuilder::getURL( array('action' => 'add_mass_punch', 'filter_user_id' => $filter_user_id, 'data' => $pc_data ), '../progress_bar/ProgressBarControl.php') );
		} else {
			$pcf->Validator->isTrue('user_id',FALSE, 'Please select at least one employee');
		}
	default:
		if ( $action != 'submit' AND !is_array($pc_data) ) {
			Debug::Text(' ID was NOT passed: '. $id, __FILE__, __LINE__, __METHOD__,10);
			$time_stamp = $date_stamp = TTDate::getBeginDayEpoch( TTDate::getTime() ) + (3600*12); //Noon
			
			$pc_data = array(
							//'user_id' => $user_obj->getId(),
							//'user_full_name' => $user_obj->getFullName(),
							'start_date_stamp' => $date_stamp,
							'end_date_stamp' => $date_stamp,
							'time_stamp' => $time_stamp,
							'status_id' => 10,
							//'branch_id' => $user_obj->getDefaultBranch(),
							//'department_id' => $user_obj->getDefaultDepartment(),
							'quantity' => 0,
							'bad_quantity' => 0,
							'dow' => array(1 => TRUE, 2 => TRUE, 3 => TRUE, 4 => TRUE, 5 => TRUE)
							);


			unset($time_stamp, $date_stamp);
		}
		//var_dump($pc_data);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		$src_user_options = UserListFactory::getArrayByListFactory( $ulf, FALSE, FALSE );

		$user_options = Misc::arrayDiffByKey( (array)$filter_user_id, $src_user_options );
		$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $src_user_options );
		
		$prepend_array_option = array( 0 => '--', -1 => _('-- Default --') );

		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $prepend_array_option,  $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );

		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $prepend_array_option,  $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$jlf->getByStatusIdAndCompanyId( array(10,20,30,40), $current_company->getId() );
			//$jlf->getByCompanyIdAndUserIdAndStatus( $current_company->getId(),  $pc_data['user_id'], array(10,20,30,40) );
			$pc_data['job_options'] = $jlf->getArrayByListFactory( $jlf, TRUE, TRUE );
			$pc_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$pc_data['job_item_options'] = $jilf->getArrayByListFactory( $jilf, TRUE );
			$pc_data['job_item_manual_id_options'] = $jilf->getManualIdArrayByListFactory( $jilf, TRUE );
		}

		//Select box options;
		$smarty->assign_by_ref('user_options', $user_options);		
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);
		
		$pc_data['status_options'] = $pf->getOptions('status');
		$pc_data['type_options'] = $pf->getOptions('type');
		$pc_data['branch_options'] = $branch_options;
		$pc_data['department_options'] = $department_options;

		//Get other field names
		$oflf = new OtherFieldListFactory();
		$pc_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getId(), 15 );

		//var_dump($pc_data);
		$smarty->assign_by_ref('pc_data', $pc_data);

		break;
}

$smarty->assign_by_ref('pcf', $pcf);
$smarty->assign_by_ref('pf', $pf);

$smarty->display('punch/AddMassPunch.tpl');
?>