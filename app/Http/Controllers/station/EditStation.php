<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/

use App\Models\Core\StationFactory;

/*
 * $Revision: 4104 $
 * $Id: EditStation.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('station','enabled')
		OR !( $permission->Check('station','edit') OR $permission->Check('station','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', __($title = 'Edit Station')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

$sf = new StationFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$sf->StartTransaction();

		$sf->setId($data['id']);
		$sf->setCompany( $current_company->getId() );
		$sf->setStatus($data['status']);
		$sf->setType($data['type']);
		$sf->setSource($data['source']);
		$sf->setStation($data['station']);
		$sf->setDescription($data['description']);

		if ( isset($data['port']) ) {
			$sf->setPort($data['port']);
		}
		if ( isset($data['user_name']) ) {
			$sf->setUserName($data['user_name']);
		}
		if ( isset($data['password']) ) {
			$sf->setPassword($data['password']);
		}

		if ( $data['type'] >= 100 ) {
			if ( isset($data['poll_frequency']) ) {
				$sf->setPollFrequency($data['poll_frequency']);
			}
			if ( isset($data['push_frequency']) ) {
				$sf->setPushFrequency($data['push_frequency']);
			}
			if ( isset($data['partial_push_frequency']) ) {
				$sf->setPartialPushFrequency($data['partial_push_frequency']);
			}
			if ( isset($data['enable_auto_punch_status']) ) {
				$sf->setEnableAutoPunchStatus(TRUE);
			} else {
				$sf->setEnableAutoPunchStatus(FALSE);
			}
			if ( isset($data['mode_flag']) ) {
				$sf->setModeFlag($data['mode_flag']);
			}
		}

		if ( isset($data['branch_id']) ) {
			$sf->setDefaultBranch($data['branch_id']);
		}
		if ( isset($data['department_id']) ) {
			$sf->setDefaultDepartment($data['department_id']);
		}
		if ( isset($data['job_id']) ) {
			$sf->setDefaultJob($data['job_id']);
		}
		if ( isset($data['job_item_id']) ) {
			$sf->setDefaultJobItem($data['job_item_id']);
		}

		if ( isset($data['time_zone_id']) ) {
			$sf->setTimeZone($data['time_zone_id']);
		}

		$sf->setGroupSelectionType( $data['group_selection_type_id'] );
		$sf->setBranchSelectionType( $data['branch_selection_type_id'] );
		$sf->setDepartmentSelectionType( $data['department_selection_type_id'] );

		if ( $sf->isValid() ) {
			$sf->Save(FALSE);

			if ( isset($data['group_ids']) ){
				$sf->setGroup( $data['group_ids'] );
			} else {
				$sf->setGroup( array() );
			}

			if ( isset($data['branch_ids']) ){
				$sf->setBranch( $data['branch_ids'] );
			} else {
				$sf->setBranch( array() );
			}

			if ( isset($data['department_ids']) ){
				$sf->setDepartment( $data['department_ids'] );
			} else {
				$sf->setDepartment( array() );
			}

			if ( isset($data['include_user_ids']) ){
				$sf->setIncludeUser( $data['include_user_ids'] );
			} else {
				$sf->setIncludeUser( array() );
			}

			if ( isset($data['exclude_user_ids']) ){
				$sf->setExcludeUser( $data['exclude_user_ids'] );
			} else {
				$sf->setExcludeUser( array() );
			}

			if ( $sf->isValid() ) {
				$sf->Save(TRUE);

				//$sf->FailTransaction();
				$sf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL(NULL, 'StationList.php') );

				break;
			}
		}
		$sf->FailTransaction();
	case 'time_clock_command':
		if ( getTTProductEdition() >= 15 AND $action != 'submit' ) {
			//Debug::setVerbosity(11);
			Debug::Text('Time Clock Command: '. $data['time_clock_command'], __FILE__, __LINE__, __METHOD__,10);

			try {
				$tc = new TimeClock( $data['type'] );
				$tc->setIPAddress( $data['source'] );
				$tc->setPort( $data['port'] );
				//$tc->setUsername( $data['user_name'] );
				$tc->setPassword( $data['password'] );

				$slf = new StationListFactory();
				$slf->getByIdAndCompanyId( $data['id'], $current_company->getId() );
				if ( $slf->getRecordCount() == 1 ) {
					$s_obj = $slf->getCurrent();
				}

				$s_obj->setLastPunchTimeStamp( $s_obj->getLastPunchTimeStamp() );

				if ( $s_obj->getTimeZone() != '' AND !is_numeric( $s_obj->getTimeZone() ) ) {
					Debug::text('Setting Station TimeZone To: '. $s_obj->getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);
					TTDate::setTimeZone( $s_obj->getTimeZone() );
				}

				$result_str = NULL;
				switch ( $data['time_clock_command'] ) {
					case 'test_connection':
						if ( $tc->testConnection() == TRUE ) {
							$result_str = _('Connection Succeeded!');
						} else {
							$result_str = _('Connection Failed!');
						}
						break;
					case 'set_date':
						TTDate::setTimeZone( $data['time_zone_id'], $s_obj->getTimeZone() );

						if ( $tc->setDate( time() ) == TRUE ) {
							$result_str = _('Date Successfully Set To: '). TTDate::getDate('DATE+TIME', time() );
						} else {
							$result_str = _('Setting Date Failed!');
						}
						break;
					case 'download':
						if ( isset($s_obj) AND $tc->Poll( $current_company, $s_obj) == TRUE ) {
							$result_str = _('Download Data Succeeded!');
							if ( $s_obj->isValid() ) {
								$s_obj->Save(FALSE);
							}
						} else {
							$result_str = _('Download Data Failed!');
						}
						break;
					case 'upload':
						if ( isset($s_obj) AND $tc->Push( $current_company, $s_obj) == TRUE ) {
							$result_str = _('Upload Data Succeeded!');
							if ( $s_obj->isValid() ) {
								$s_obj->Save(FALSE);
							}
						} else {
							$result_str = _('Upload Data Failed!');
						}
						break;
					case 'update_config':
						if ( isset($s_obj) AND $tc->setModeFlag( $s_obj->getModeFlag() ) == TRUE ) {
							$result_str = _('Update Configuration Succeeded');
						} else {
							$result_str = _('Update Configuration Failed');
						}
						break;
					case 'delete_data':
						if ( isset($s_obj) AND $tc->DeleteAllData( $s_obj ) == TRUE ) {
							$result_str = _('Delete Data Succeeded!');
							if ( $s_obj->isValid() ) {
								$s_obj->Save(FALSE);
							}
						} else {
							$result_str = _('Delete Data Failed!');
						}
						break;
					case 'reset_last_punch_time_stamp':
						$s_obj->setLastPunchTimeStamp( time() );
						if ( $s_obj->isValid() ) {
							$s_obj->Save(FALSE);
						}
						break;
					case 'clear_last_punch_time_stamp':
						$s_obj->setLastPunchTimeStamp( 1 );
						if ( $s_obj->isValid() ) {
							$s_obj->Save(FALSE);
						}
						break;
					case 'restart':
						$tc->restart();
						$result_str = _('Restart Succeeded!');
						break;
					case 'firmware':
						if ( $tc->setFirmware() == TRUE ) {
							$result_str = _('Firmware Update Succeeded!');
						} else {
							$result_str = _('Firmware Update Failed!');
						}
						break;
				}

				TTLog::addEntry( $s_obj->getId(), 500,  _('TimeClock Manual Command').': '. ucwords( str_replace('_', ' ', $data['time_clock_command'] ) ) .' '._('Result').': '. $result_str, NULL, $s_obj->getTable() );

				if ( isset($s_obj) ) {
					$data['last_poll_date'] = $s_obj->getLastPollDate();
					$data['last_push_date'] = $s_obj->getLastPushDate();
				}
				unset($s_obj, $slf);
			} catch ( Exception $e ) {
				$result_str = _('Connection Failed!');
			}

			$smarty->assign_by_ref('time_clock_command_result', $result_str);
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$slf = new StationListFactory();

			$slf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($slf as $s_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$data = array(
									'id' => $s_obj->getId(),
									'status' => $s_obj->getStatus(),
									'type' => $s_obj->getType(),
									'station' => $s_obj->getStation(),
									'source' => $s_obj->getSource(),
									'description' => $s_obj->getDescription(),

									'port' => $s_obj->getPort(),
									'user_name' => $s_obj->getUserName(),
									'password' => $s_obj->getPassword(),

									'poll_frequency' => $s_obj->getPollFrequency(),
									'push_frequency' => $s_obj->getPushFrequency(),
									'partial_push_frequency' => $s_obj->getPartialPushFrequency(),

									'enable_auto_punch_status' => $s_obj->getEnableAutoPunchStatus(),
									'mode_flag' => $s_obj->getModeFlag(),

									'last_punch_time_stamp' => $s_obj->getLastPunchTimeStamp(),
									'last_poll_date' => $s_obj->getLastPollDate(),
									'last_push_date' => $s_obj->getLastPushDate(),
									'last_partial_push_date' => $s_obj->getLastPartialPushDate(),

									'branch_id' => $s_obj->getDefaultBranch(),
									'department_id' => $s_obj->getDefaultDepartment(),
									'job_id' => $s_obj->getDefaultJob(),
									'job_item_id' => $s_obj->getDefaultJobItem(),
									'time_zone_id' => $s_obj->getTimeZone(),

									'group_selection_type_id' => $s_obj->getGroupSelectionType(),
									'group_ids' => $s_obj->getGroup(),

									'branch_selection_type_id' => $s_obj->getBranchSelectionType(),
									'branch_ids' => $s_obj->getBranch(),

									'department_selection_type_id' => $s_obj->getDepartmentSelectionType(),
									'department_ids' => $s_obj->getDepartment(),

									'include_user_ids' => $s_obj->getIncludeUser(),
									'exclude_user_ids' => $s_obj->getExcludeUser(),

									'created_date' => $s_obj->getCreatedDate(),
									'created_by' => $s_obj->getCreatedBy(),
									'updated_date' => $s_obj->getUpdatedDate(),
									'updated_by' => $s_obj->getUpdatedBy(),
									'deleted_date' => $s_obj->getDeletedDate(),
									'deleted_by' => $s_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' AND $action != 'time_clock_command' ) {

			$data = array(	'status' => 20,
							'port' => 80,
							'password' => 0,
							'poll_frequency' => 600,
							'push_frequency' => 86400,
							'partial_push_frequency' => 3600 );
		}

		$data = Misc::preSetArrayValues( $data, array('branch_ids', 'department_ids', 'group_ids', 'include_user_ids', 'exclude_user_ids'), NULL);

		//Select box options;
		$data['status_options'] = $sf->getOptions('status');
		$data['type_options'] = $sf->getOptions('type');
		$data['poll_frequency_options'] = $sf->getOptions('poll_frequency');
		$data['push_frequency_options'] = $sf->getOptions('push_frequency');
		$data['time_clock_command_options'] = $sf->getOptions('time_clock_command');
		$data['mode_flag_options'] = $sf->getOptions('mode_flag');

		if ( $current_company->getProductEdition() == 20 ) {
			$jlf = new JobListFactory();
			$jlf->getByCompanyId( $current_company->getId() );
			$data['job_options'] = Misc::prependArray( array(0 => '-- None --'), $jlf->getArrayByListFactory( $jlf, FALSE, TRUE ) );

			$jilf = new JobItemListFactory();
			$jilf->getByCompanyIdAndStatus( $current_company->getId(), 10 );
			$data['job_item_options'] = Misc::prependArray( array(0 => '-- None --'), $jilf->getArrayByListFactory( $jilf, TRUE, FALSE ) );
		}

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );
		$data['src_branch_options'] = Misc::arrayDiffByKey( (array)$data['branch_ids'], $branch_options );
		$data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );
		$data['src_department_options'] = Misc::arrayDiffByKey( (array)$data['department_ids'], $department_options );
		$data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$data['department_ids'], $department_options );

		$uglf = new UserGroupListFactory();
		$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) );
		$data['src_group_options'] = Misc::arrayDiffByKey( (array)$data['group_ids'], $group_options );
		$data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$data['group_ids'], $group_options );

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), NULL );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

		$data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$data['include_user_ids'], $user_options );
		$data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$data['include_user_ids'], $user_options );

		$data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$data['exclude_user_ids'], $user_options );
		$data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$data['exclude_user_ids'], $user_options );

		$data['group_selection_type_options'] = $sf->getOptions('group_selection_type');
		$data['branch_selection_type_options'] = $sf->getOptions('branch_selection_type');
		$data['department_selection_type_options'] = $sf->getOptions('department_selection_type');

		$data['branch_options'] = Misc::prependArray( array(0 => '-- None --'), $branch_options );
		$data['department_options'] = Misc::prependArray( array(0 => '-- None --'), $department_options );

		$upf = new UserPreferenceFactory();
		$timezone_options = Misc::prependArray( array(0 => '-- None --'), $upf->getOptions('time_zone') );
		$data['time_zone_options'] = $timezone_options;

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('station/EditStation.tpl');
?>