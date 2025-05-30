<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ActiveShiftList.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_active_shift') ) {
	$permission->Redirect( FALSE ); //Redirect
}

//Debug::setVerbosity(11);


$smarty->assign('title', __($title = 'Whos In Summary')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
												) );

$static_columns = array(			'-1000-first_name' => _('First Name'),
									'-1001-last_name' => _('Last Name'),
									'-1002-employee_number' => _('Employee #'),
									'-1010-title' => _('Title'),
									'-1020-province' => _('Province/State'),
									'-1030-country' => _('Country'),
									'-1039-group' => _('Group'),
									'-1040-default_branch' => _('Default Branch'),
									'-1050-default_department' => _('Default Department'),
									'-1100-time_stamp' => _('Punch Time'),
									'-1110-actual_time_stamp' => _('Punch Actual Time'),
									'-1120-type' => _('Type'),
									'-1130-status' => _('Status'),
									'-1160-branch' => _('Branch'),
									'-1170-department' => _('Department'),
									'-1171-station_type' => _('Station Type'),
									'-1172-station_station_id' => _('Station ID'),
									'-1173-station_source' => _('Station Source'),
									'-1174-station_description' => _('Station Description'),
									'-1220-note' => _('Note'),
									'-2000-function' => _('Functions'),
									);

$professional_edition_static_columns = array(
									'-1180-job' => _('Job'),
									'-1190-job_item' => _('Task'),
									);

if ( $current_company->getProductEdition() == 20 ) {
	$static_columns = Misc::prependArray( $static_columns, $professional_edition_static_columns);
	ksort($static_columns);
}

$columns = $static_columns;

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('punch','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('punch','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('punch','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'Filter Data', __FILE__, __LINE__, __METHOD__,10);

		$filter_data['job_group_ids'] = Misc::trimSortPrefix( $filter_data['job_group_ids'], TRUE );

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			$plf = new PunchListFactory();
			if ( $current_company->getProductEdition() == 20 ) {
				if ( !isset($filter_data['job_item_ids']) ) {
					$filter_data['job_item_ids'] = array();
				}

				$jlf = new JobListFactory();
				$jlf->getSearchByCompanyIdAndStatusIdAndBranchIdAndDepartmentIdAndGroupIdAndClientIdAndIncludeIdAndExcludeId(
					$current_company->getId(),
					NULL,
					NULL,
					NULL,
					Misc::trimSortPrefix( $filter_data['job_group_ids'], TRUE ),
					NULL,
					$filter_data['include_job_ids'],
					$filter_data['exclude_job_ids'] );

				$filter_data['job_ids'] = array();
				if ( $jlf->getRecordCount() > 0 ) {
					foreach( $jlf as $j_obj ) {
						$filter_data['job_ids'][] = $j_obj->getId();
					}
				}
			} else {
				$filter_data['job_ids'] = array( -1 );
				$filter_data['job_item_ids'] = array( -1 );
			}

			$epoch = TTDate::getTime();
			$filter_data['start_date'] = ($epoch-86400);
			$filter_data['end_date'] = ($epoch+86400);
			$filter_data['status_id'] = 10;

			$ulf = new UserListFactory();

			$utlf = new UserTitleListFactory();
			$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

			$uglf = new UserGroupListFactory();
			$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

			$blf = new BranchListFactory();
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = new DepartmentListFactory();
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			$slf = new StationListFactory();
			$station_type_options = $slf->getOptions('type');

			if ( $current_company->getProductEdition() == 20 ) {
				$jlf = new JobListFactory();
				$job_options = $jlf->getByCompanyIdArray( $current_company->getId() );

				$jilf = new JobItemListFactory();
				$job_item_options = $jilf->getByCompanyIdArray( $current_company->getId() );
			} else {
				$job_options = array();
				$job_item_options = array();
			}

			$punch_type_options = $plf->getOptions('type');

			$plf->getLastPunchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
			if ( $plf->getRecordCount() > 0 ) {
				foreach( $plf as $p_obj ) {
					//Get user info
					$ulf->getById( $p_obj->getColumn('user_id') );

					if ( $ulf->getRecordCount() > 0 ) {
						$user_obj = $ulf->getCurrent();
/*
						                                                       {if $permission->Check('punch','view') OR ( $permission->Check('punch','view_child') AND $user.is_child === TRUE ) OR ( $permission->Check('punch','view_own') AND $user.is_owner === TRUE ) }
                                                                {assign var="user_id" value=$user.id}
                                                                [ <a href="{urlbuilder script="../timesheet/ViewUserTimeSheet.php" values="filter_data[user_id]=$user_id" merge="FALSE"}">{t}View{/t}</a> ]
                                                        {/if}

*/
						$view_link =

						$rows[] = array(
											'id' => $user_obj->GetId(),
											'first_name' => $user_obj->getFirstName(),
											'last_name' => $user_obj->getLastName(),
											'employee_number' => $user_obj->getEmployeeNumber(),
											'title' => Option::getByKey( $user_obj->getTitle(), $title_options ),
											'province' => $user_obj->getProvince(),
											'country' => $user_obj->getCountry(),
											'group' => Option::getByKey( $user_obj->getGroup(), $group_options ),
											'default_branch' => Option::getByKey( $user_obj->getDefaultBranch(), $branch_options ),
											'default_department' => Option::getByKey( $user_obj->getDefaultDepartment(), $department_options ),

											'is_owner' => $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() ),
											'is_child' => $permission->isChild( $user_obj->getId(), $permission_children_ids ),

											'punch_id' => $p_obj->getId(),
											'time_stamp' => $p_obj->getTimeStamp(),
											'actual_time_stamp' => $p_obj->getActualTimeStamp(),
											'status_id' => $p_obj->getStatus(),
											'status' => Option::getByKey( $p_obj->getStatus(), $p_obj->getOptions('status') ),
											'type_id' => $p_obj->getType(),
											'type' => Option::getByKey( $p_obj->getType(), $p_obj->getOptions('type') ),
											'branch_id' => $p_obj->getColumn('branch_id'),
											'branch' => Option::getByKey( $p_obj->getColumn('branch_id'), $branch_options ),
											'department_id' => $p_obj->getColumn('department_id'),
											'department' => Option::getByKey( $p_obj->getColumn('department_id'), $department_options ),
											'job_id' => $p_obj->getColumn('job_id'),
											'job' => Option::getByKey( $p_obj->getColumn('job_id'), $job_options ),
											'job_item_id' => $p_obj->getColumn('job_id'),
											'job_item' => Option::getByKey( $p_obj->getColumn('job_item_id'), $job_item_options ),
											'note' => $p_obj->getColumn('note'),
											'station_type' => Option::getByKey( $p_obj->getColumn('station_type_id'), $station_type_options ),
											'station_station_id' => $p_obj->getColumn('station_station_id'),
											'station_source' => $p_obj->getColumn('station_source'),
											'station_description' => Misc::TruncateString( $p_obj->getColumn('station_description'), 30 ),
										);
					}
				}
			}
			//print_r($rows);

			if ( isset($rows) ) {
				foreach($rows as $row) {
					$tmp_rows[] = $row;
				}
				//var_dump($tmp_rows);

				$rows = Sort::Multisort($tmp_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

				//Convert units
				$tmp_rows = $rows;
				unset($rows);

				$trimmed_static_columns = array_keys( Misc::trimSortPrefix($static_columns) );
				foreach($tmp_rows as $row ) {
					foreach($row as $column => $column_data) {
						//if ( $column != 'full_name' AND $column_data != '' ) {
						if ( $column == 'time_stamp'
								OR $column == 'actual_time_stamp' ) {
							$column_data = TTDate::getDate( 'DATE+TIME', $column_data );
						}

						if ( $column_data == '' ) {
							$column_data = NULL;
						}

						$row_columns[$column] = $column_data;
						unset($column, $column_data);
					}

					$rows[] = $row_columns;
					unset($row_columns);
				}
			}
		}
		//var_dump($rows);

		foreach( $filter_data['column_ids'] as $column_key ) {
			$filter_columns[Misc::trimSortPrefix($column_key)] = $columns[$column_key];
		}



		if ( $action == 'export' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns, FALSE );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo _('No Data To Export!') ."<br>\n";
			}
		} else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/ActiveShiftListReport.tpl');
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);
		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);

				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['punch_branch_ids'] = array( -1 );
				$filter_data['punch_department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['job_group_ids'] = array( -1 );
				$filter_data['include_job_ids'] = array();
				$filter_data['exclude_job_ids'] = array();
				$filter_data['job_item_ids'] = array( -1 );

				$filter_data['group_ids'] = array( -1 );

				//$filter_data['user_ids'] = array_keys( UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, FALSE ) );
				if ( !isset($filter_data['column_ids']) ) {
					$filter_data['column_ids']	= array();
				}

				$filter_data['column_ids'] = array_merge( $filter_data['column_ids'],
										array(
											'-1000-first_name',
											'-1001-last_name',
											'-1160-branch',
											'-1170-department',
											'-1120-type',
											'-1130-status',
											'-1100-time_stamp',
											'-1174-station_description',
												) );

				$filter_data['primary_sort'] = '-1001-last_name';
				$filter_data['secondary_sort'] = '-1100-time_stamp';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'punch_branch_ids', 'punch_department_ids', 'user_title_ids', 'pay_period_ids', 'include_job_ids', 'exclude_job_ids', 'job_branch_ids', 'job_department_ids', 'job_group_ids', 'client_ids', 'job_item_ids', 'job_item_group_ids', 'column_ids' ), NULL );

		$ulf = new UserListFactory();

		$all_array_option = array('-1' => _('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		$filter_data['src_punch_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_branch_ids'], $branch_options );
		$filter_data['selected_punch_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_branch_ids'], $branch_options );

		$filter_data['src_punch_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['punch_department_ids'], $department_options );
		$filter_data['selected_punch_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['punch_department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();

			//Get include job list.
			$jlf->getByCompanyId( $current_company->getId() );
			$job_options = Misc::prependArray( array('0' => _('- No Job -')), $jlf->getArrayByListFactory( $jlf, FALSE, TRUE ) );
			$filter_data['job_manual_id_options'] = $jlf->getManualIDArrayByListFactory($jlf, TRUE);

			$filter_data['src_include_job_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_job_ids'], $job_options );
			$filter_data['selected_include_job_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_job_ids'], $job_options );

			//Get exclude job list
			$exclude_job_options = Misc::prependArray( $all_array_option, $jlf->getArrayByListFactory( $jlf, FALSE, TRUE ) );
			$filter_data['src_exclude_job_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_job_ids'], $job_options );
			$filter_data['selected_exclude_job_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_job_ids'], $job_options );

			//Get Job Groups
			$jglf = new JobGroupListFactory();
			$nodes = FastTree::FormatArray( $jglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_group_options = Misc::prependArray( $all_array_option, $jglf->getArrayByNodes( $nodes, FALSE, TRUE ) );
			$filter_data['src_job_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['job_group_ids'], $job_group_options );
			$filter_data['selected_job_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['job_group_ids'], $job_group_options );

			//Get Job Items
			$jilf = new JobItemListFactory();
			$jilf->getByCompanyId( $current_company->getId() );
			$job_item_options = Misc::prependArray( array('-1' => _('-- All --'), '0' => _('- No Task -') ), $jilf->getArrayByListFactory( $jilf, FALSE, TRUE ) );
			$filter_data['src_job_item_options'] = Misc::arrayDiffByKey( (array)$filter_data['job_item_ids'], $job_item_options );
			$filter_data['selected_job_item_options'] = Misc::arrayIntersectByKey( (array)$filter_data['job_item_ids'], $job_item_options );
		}

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

		//$filter_data['refresh_options'] = array( 0 => _('- Disabled -'), 60 => _('1 minute'), 300 => _('5 minutes'), 600 => _('10 minutes'), 1800 => _('30 minutes'), 3600 => _('60 minutes') );

		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = 'Wage Effective Date';
		unset($filter_data['sort_options']['effective_date']);
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/ActiveShiftList.tpl');

		break;
}
?>
