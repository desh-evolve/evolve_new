<?php

namespace App\Models\Core;

use App\Models\Company\BranchListFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Department\DepartmentListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//include_once('Net/IPv4.php');

class StationFactory extends Factory {
	protected $table = 'station';
	protected $pk_sequence_name = 'station_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
											10 	=> ('DISABLED'),
											20	=> ('ENABLED')
									);
				break;
			case 'type':
				$retval = array(
											10 	=> ('PC'),
									);

				if ( getTTProductEdition() >= 15 ) {
					$retval[20]	= ('PHONE');
					$retval[25]	= ('WirelessWeb');
					$retval[28]	= ('iPhone');
					$retval[30]	= ('iBUTTON');
					$retval[40]	= ('Barcode');
					$retval[50]	= ('FingerPrint');
					$retval[100] = ('TimeClock: TT-A8');
					//$retval[120] = ('TimeClock: TT-S300');
					$retval[150] = ('TimeClock: TT-US100');
					//$retval[200] = ('TimeClock: ACTAtek');
				}
				break;
			case 'station_reserved_word':
				$retval = array('any', '*');
				break;
			case 'source_reserved_word':
				$retval = array('any', '*');
				break;
			case 'branch_selection_type':
				$retval = array(
										10 => ('All Branches'),
										20 => ('Only Selected Branches'),
										30 => ('All Except Selected Branches'),
									);
				break;
			case 'department_selection_type':
				$retval = array(
										10 => ('All Departments'),
										20 => ('Only Selected Departments'),
										30 => ('All Except Selected Departments'),
									);
				break;
			case 'group_selection_type':
				$retval = array(
										10 => ('All Groups'),
										20 => ('Only Selected Groups'),
										30 => ('All Except Selected Groups'),
									);
				break;
			case 'poll_frequency':
				$retval = array(
										60 => ('1 Minute'),
										120 => ('2 Minutes'),
										300 => ('5 Minutes'),
										600 => ('10 Minutes'),
										900 => ('15 Minutes'),
										1800 => ('30 Minutes'),
										3600 => ('1 Hour'),
										7200 => ('2 Hours'),
										10800 => ('3 Hours'),
										21600 => ('6 Hours'),
										43200 => ('12 Hours'),
										86400 => ('24 Hours'),
										172800 => ('48 Hours'),
										259200 => ('72 Hours'),
										604800 => ('1 Week'),
									);
				break;
			case 'partial_push_frequency':
			case 'push_frequency':
				$retval = array(
										60 => ('1 Minute'),
										120 => ('2 Minutes'),
										300 => ('5 Minutes'),
										600 => ('10 Minutes'),
										900 => ('15 Minutes'),
										1800 => ('30 Minutes'),
										3600 => ('1 Hour'),
										7200 => ('2 Hours'),
										10800 => ('3 Hours'),
										21600 => ('6 Hours'),
										43200 => ('12 Hours'),
										86400 => ('24 Hours'),
										172800 => ('48 Hours'),
										259200 => ('72 Hours'),
										604800 => ('1 Week'),
									);
				break;
			case 'time_clock_command':
				$retval = array(
										'test_connection' => ('Test Connection'),
										'set_date' => ('Set Date'),
										'download' => ('Download Data'),
										'upload' => ('Upload Data'),
										'update_config' => ('Update Configuration'),
										'delete_data' => ('Delete all Data'),
										'reset_last_punch_time_stamp' => ('Reset Last Punch Time'),
										'clear_last_punch_time_stamp' => ('Clear Last Punch Time'),
										'restart' => ('Restart'),
										'firmware' => ('Update Firmware (CAUTION)'),
									);
				break;
			case 'mode_flag':
				$retval = array(
										1 		=> ('-- Default --'),
										2 		=> ('Must Select In/Out Status'),
										//4 	=> ('Enable Work Code (Mode 1)'),
										//8 	=> ('Enable Work Code (Mode 2)'),
										4 		=> ('Disable Out Status'),
										8 		=> ('Enable: Breaks'),
										16 		=> ('Enable: Lunches'),
										32  	=> ('Enable: Branch'),
										64  	=> ('Enable: Department'),

										32768  	=> ('Authentication: Fingerprint & Password'),
										65536  	=> ('Authentication: Fingerprint & Proximity Card'),
										131072 	=> ('Authentication: PIN & Fingerprint'),
										262144	=> ('Authentication: Proximity Card & Password'),

										1048576	=> ('Enable: External Proximity Card Reader'),
										2097152 => ('Enable: Pre-Punch Message'),
										4194304	=> ('Enable: Post-Punch Message'),

										1073741824 => ('Enable: Diagnostic Logs'),
									);
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$retval[128]  = ('Enable: Job');
					$retval[256]  = ('Enable: Task');
					$retval[512]  = ('Enable: Quantity');
					$retval[1024] = ('Enable: Bad Quantity');
				}

				ksort($retval);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => ('Status'),
										'-1020-type' => ('Type'),
										'-1030-source' => ('Source'),

										'-1140-station_id' => ('Station'),
										'-1150-description' => ('Description'),

										'-1160-time_zone' => ('Time Zone'),

										'-1160-branch_selection_type' => ('Branch Selection Type'),
										'-1160-department_selection_type' => ('Department Selection Type'),
										'-1160-group_selection_type' => ('Group Selection Type'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'type',
								'source',
								'station_id',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'station_id',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'station_id' => 'Station',
										'source' => 'Source',
										'description' => 'Description',
										'branch_id' => 'DefaultBranch',
										'department_id' => 'DefaultDepartment',
										'time_zone' => 'TimeZone',
										'user_group_selection_type_id' => 'GroupSelectionType',
										'group_selection_type' => FALSE,
										'group' => FALSE,
										'branch_selection_type_id' => 'BranchSelectionType',
										'branch_selection_type' => FALSE,
										'branch' => FALSE,
										'department_selection_type_id' => 'DepartmentSelectionType',
										'department_selection_type' => FALSE,
										'department' => FALSE,
										'include_user' => FALSE,
										'exclude_user' => FALSE,
										'port' => 'Port',
										'user_name' => 'UserName',
										'password' => 'Password',
										'poll_frequency' => 'PollFrequency',
										'push_frequency' => 'PushFrequency',
										'partial_push_frequency' => 'PartialPushFrequency',
										'enable_auto_punch_status' => 'EnableAutoPunchStatus',
										'mode_flag' => 'ModeFlag',
										'work_code_definition' => 'WorkCodeDefinition',
										'last_punch_time_stamp' => 'LastPunchTimeStamp',
										'last_poll_date' => 'LastPollDate',
										'last_poll_status_message' => 'LastPollStatusMessage',
										'last_push_date' => 'LastPushDate',
										'last_push_status_message' => 'LastPushStatusMessage',
										'last_partial_push_date' => 'LastPartialPushDate',
										'last_partial_push_status_message' => 'LastPartialPushStatusMessage',
										'user_value_1' => 'UserValue1',
										'user_value_2' => 'UserValue2',
										'user_value_3' => 'UserValue3',
										'user_value_4' => 'UserValue4',
										'user_value_5' => 'UserValue5',
										'allowed_date' => 'AllowedDate',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	 }

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = new CompanyListFactory();
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();
		$rs = $clf->getByID($id);

		if ( $this->Validator->isResultSetWithRows(	'company', $rs, ('Company is invalid') ) ) {
			$this->data['company_id'] = $id;
			return TRUE;
		}
		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueStation($station) {
		$ph = array(
					':company_id' => $this->getCompany(),
					':station' => $station,
					);

		$query = 'select id from '. $this->table .' where company_id = :company_id AND station_id = :station AND deleted=0';

		$id = DB::selectOne($query, $ph);

		Debug::Arr($id,'Unique Station: '. $station, __FILE__, __LINE__, __METHOD__,10);
					
		if ( empty($id) || $id === FALSE ) {
			return TRUE;
		} else {
			
			if ($id->id == $this->getId() ) {
				return TRUE;
			}
			
		}
		return FALSE;
	}

	function getStation() {
		if ( isset($this->data['station_id']) ) {
			return $this->data['station_id'];
		}

		return FALSE;
	}
	function setStation($station_id = NULL) {
		$station_id = trim($station_id);

		if ( empty($station_id) ) {
			$station_id = $this->genStationID();
		}

		if (	in_array(strtolower($station_id), $this->getOptions('station_reserved_word'))
				OR
				(
				$this->Validator->isLength(	'station_id',
											$station_id,
											('Incorrect Station ID length'),
											2, 250 )
				AND
				$this->Validator->isTrue(	'station_id',
											$this->isUniqueStation($station_id),
											('Station ID already exists'))
				)
			) {

			$this->data['station_id'] = $station_id;

			return TRUE;
		}

		return FALSE;
	}

	function getSource() {
		if ( isset($this->data['source']) ) {
			return $this->data['source'];
		}

		return FALSE;
	}
	function setSource($source) {
		$source = trim($source);

		if ( 	in_array(strtolower($source), $this->getOptions('source_reserved_word') )
				OR
				(
				$source != NULL
				AND
				$this->Validator->isLength(	'source',
											$source,
											('Incorrect Source ID length'),
											2, 250 )
				)
			) {

			$this->data['source'] = $source;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if ( $this->Validator->isLength(	'description',
											$description,
											('Incorrect Description length'),
											0, 255 ) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultBranch() {
		if ( isset($this->data['branch_id']) ) {
			return (int)$this->data['branch_id'];
		}

		return FALSE;
	}
	function setDefaultBranch($id) {
		$id = trim($id);

		$blf = new BranchListFactory();

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'branch_id',
														$blf->getByID($id),
														('Invalid Branch')
													) ) {

			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultDepartment() {
		if ( isset($this->data['department_id']) ) {
			return (int)$this->data['department_id'];
		}

		return FALSE;
	}
	function setDefaultDepartment($id) {
		$id = trim($id);

		$dlf = new DepartmentListFactory();

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'department_id',
														$dlf->getByID($id),
														('Invalid Department')
													) ) {

			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJob() {
		if ( isset($this->data['job_id']) ) {
			return (int)$this->data['job_id'];
		}

		return FALSE;
	}
	function setDefaultJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Job ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jlf = new JobListFactory();
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_id',
														$jlf->getByID($id),
														('Invalid Job')
													) ) {

			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return (int)$this->data['job_item_id'];
		}

		return FALSE;
	}
	function setDefaultJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Job Item ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$jilf = new JobItemListFactory();
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item_id',
														$jilf->getByID($id),
														('Invalid Task')
													) ) {

			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		$upf = new UserPreferenceFactory();

		if ( $time_zone == 0
				OR
				$this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											('Incorrect Time Zone'),
											Misc::trimSortPrefix( $upf->getOptions('time_zone') ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function getGroupSelectionType() {
		if ( isset($this->data['user_group_selection_type_id']) ) {
			return $this->data['user_group_selection_type_id'];
		}

		return FALSE;
	}
	function setGroupSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'user_group_selection_type',
											$value,
											('Incorrect Group Selection Type'),
											$this->getOptions('group_selection_type')) ) {

			$this->data['user_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getGroup() {
		$lf = new StationUserGroupListFactory();
		$lf->getByStationId( $this->getId() );
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getGroup();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setGroup($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new StationUserGroupListFactory();
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$id = $lf_a->getGroup();
					Debug::text('Group ID: '. $lf_a->getGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf_a->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $lf_a);
			}

			//Insert new mappings.
			$lf_b = new UserGroupListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new StationUserGroupFactory();
					$f->setStation( $this->getId() );
					$f->setGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'group',
														$f->Validator->isValid(),
														('Selected Group is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getBranchSelectionType() {
		if ( isset($this->data['branch_selection_type_id']) ) {
			return $this->data['branch_selection_type_id'];
		}

		return FALSE;
	}
	function setBranchSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'branch_selection_type',
											$value,
											('Incorrect Branch Selection Type'),
											$this->getOptions('branch_selection_type')) ) {

			$this->data['branch_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		$lf = new StationBranchListFactory();
		$lf->getByStationId( $this->getId() );
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getBranch();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setBranch($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}
		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new StationBranchListFactory();
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$id = $lf_a->getBranch();
					//Debug::text('Branch ID: '. $lf_a->getBranch() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf_a->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $lf_a);
			}

			//Insert new mappings.
			$lf_b = new BranchListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new StationBranchFactory();
					$f->setStation( $this->getId() );
					$f->setBranch( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'branch',
														$f->Validator->isValid(),
														('Selected Branch is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getDepartmentSelectionType() {
		if ( isset($this->data['department_selection_type_id']) ) {
			return $this->data['department_selection_type_id'];
		}

		return FALSE;
	}
	function setDepartmentSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'department_selection_type',
											$value,
											('Incorrect Department Selection Type'),
											$this->getOptions('department_selection_type')) ) {

			$this->data['department_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		$lf = new StationDepartmentListFactory();
		$lf->getByStationId( $this->getId() );
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getDepartment();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setDepartment($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new StationDepartmentListFactory();
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$id = $lf_a->getDepartment();
					//Debug::text('Department ID: '. $lf_a->getDepartment() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf_a->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $lf_a);
			}

			//Insert new mappings.
			$lf_b = new DepartmentListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new StationDepartmentFactory();
					$f->setStation( $this->getId() );
					$f->setDepartment( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'department',
														$f->Validator->isValid(),
														('Selected Department is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getIncludeUser() {
		$lf = new StationIncludeUserListFactory();
		$lf->getByStationId( $this->getId() );
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getIncludeUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setIncludeUser($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new StationIncludeUserListFactory();
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$id = $lf_a->getIncludeUser();
					Debug::text('IncludeUser ID: '. $lf_a->getIncludeUser() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf_a->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $lf_a);
			}

			//Insert new mappings.
			$lf_b = new UserListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new StationIncludeUserFactory();
					$f->setStation( $this->getId() );
					$f->setIncludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'include_user',
														$f->Validator->isValid(),
														('Selected Employee is invalid').' ('. $obj->getFullName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function getExcludeUser() {
		$lf = new StationExcludeUserListFactory();
		$lf->getByStationId( $this->getId() );
		foreach ($lf->rs as $obj) {
			$lf->data = (array)$obj;
			$list[] = $lf->getExcludeUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setExcludeUser($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = new StationExcludeUserListFactory();
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a->rs as $obj) {
					$lf_a->data = (array)$obj;
					$id = $lf_a->getExcludeUser();
					Debug::text('ExcludeUser ID: '. $lf_a->getExcludeUser() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf_a->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $lf_a);
			}

			//Insert new mappings.
			$lf_b = new UserListFactory();

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = new StationExcludeUserFactory();
					$f->setStation( $this->getId() );
					$f->setExcludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'exclude_user',
														$f->Validator->isValid(),
														('Selected Employee is invalid').' ('. $obj->getFullName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}



	/*

		TimeClock specific fields

	*/
	function getPort() {
		if ( isset($this->data['port']) ) {
		return $this->data['port'];
		}

		return FALSE;
	}
	function setPort($value) {
		$value = trim($value);

		if ( $value == ''
				OR
				$this->Validator->isNumeric(	'port',
											$value,
											('Incorrect port')
											) ) {

			$this->data['port'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserName() {
		if ( isset($this->data['user_name']) ) {
			return $this->data['user_name'];
		}

		return FALSE;
	}
	function setUserName($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'user_name',
											$value,
											('Incorrect User Name length'),
											0, 255 ) ) {

			$this->data['user_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPassword() {
		if ( isset($this->data['password']) ) {
			return $this->data['password'];
		}

		return FALSE;
	}
	function setPassword($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'password',
											$value,
											('Incorrect Password length'),
											0, 255 ) ) {

			$this->data['password'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPollFrequency() {
		if ( isset($this->data['poll_frequency']) ) {
			return $this->data['poll_frequency'];
		}

		return FALSE;
	}
	function setPollFrequency($value) {
		$value = trim($value);

		if (	$value == 0
				OR
				$this->Validator->inArrayKey(	'poll_frequency',
											$value,
											('Incorrect Download Frequency'),
											$this->getOptions('poll_frequency')) ) {

			$this->data['poll_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}


	function getPushFrequency() {
		if ( isset($this->data['push_frequency']) ) {
			return $this->data['push_frequency'];
		}

		return FALSE;
	}
	function setPushFrequency($value) {
		$value = trim($value);

		if ( 	$value == 0
				OR
				$this->Validator->inArrayKey(	'push_frequency',
												$value,
												('Incorrect Upload Frequency'),
												$this->getOptions('push_frequency')) ) {

			$this->data['push_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPartialPushFrequency() {
		if ( isset($this->data['partial_push_frequency']) ) {
			return $this->data['partial_push_frequency'];
		}

		return FALSE;
	}
	function setPartialPushFrequency($value) {
		$value = trim($value);

		if ( $value == 0
			OR
			$this->Validator->inArrayKey(	'partial_push_frequency',
											$value,
											('Incorrect Partial Upload Frequency'),
											$this->getOptions('push_frequency')) ) {

			$this->data['partial_push_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableAutoPunchStatus() {
		return $this->fromBool( $this->data['enable_auto_punch_status'] );
	}
	function setEnableAutoPunchStatus($bool) {
		$this->data['enable_auto_punch_status'] = $this->toBool($bool);

		return TRUE;
	}

	function getModeFlag() {
		if ( isset($this->data['mode_flag']) ) {
			return Option::getArrayByBitMask( $this->data['mode_flag'], $this->getOptions('mode_flag'));
		}

		return FALSE;
	}
	function setModeFlag($arr) {
		$bitmask = Option::getBitMaskByArray( $arr, $this->getOptions('mode_flag') );

		if ( $this->Validator->isNumeric(	'mode_flag',
											$bitmask,
											('Incorrect Mode') ) ) {

			$this->data['mode_flag'] = $bitmask;

			return TRUE;
		}

		return FALSE;
	}

	function parseWorkCode( $work_code ) {
		$definition = $this->getWorkCodeDefinition();

		$work_code = str_pad( $work_code, 9, 0, STR_PAD_LEFT);

		$retarr = array( 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 );

		$start_digit = 0;
		if ( isset($definition['branch']) AND $definition['branch'] > 0 ) {
			$retarr['branch_id'] = (int)substr( $work_code, $start_digit, $definition['branch']);
			$start_digit += $definition['branch'];
		}

		if ( isset($definition['department']) AND $definition['department'] > 0 ) {
			$retarr['department_id'] = (int)substr( $work_code, $start_digit, $definition['department']);
			$start_digit += $definition['department'];
		}

		if ( isset($definition['job']) AND $definition['job'] > 0 ) {
			$retarr['job_id'] = (int)substr( $work_code, $start_digit, $definition['job']);
			$start_digit += $definition['job'];
		}

		if ( isset($definition['job_item']) AND $definition['job_item'] > 0 ) {
			$retarr['job_item_id'] = (int)substr( $work_code, $start_digit, $definition['job_item']);
			$start_digit += $definition['job_item'];
		}

		Debug::Arr($retarr, 'Parsed Work Code: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}
/*
	function getWorkCodeDefinition() {
		if ( isset($this->data['work_code_definition']) ) {
			return unserialize( $this->data['work_code_definition'] );
		}

		return FALSE;
	}
	function setWorkCodeDefinition($arr) {
		if ( $arr == FALSE ) {
			return TRUE;
		}

		$arr = Misc::preSetArrayValues( $arr, array('branch', 'department', 'job', 'job_item'), 0);

		if ( is_array( $arr )
				AND ($arr['branch']+$arr['department']+$arr['job']+$arr['job_item']) == 9 ) {
			$this->data['work_code_definition'] = serialize( $arr );

			return TRUE;
		} else {
			$this->Validator->isTRUE(	'work_code_definition',
										FALSE,
										('Incorrect work code field lengths, they must all add up to 9') );
		}

		return FALSE;
	}
*/

	//Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPollDateAndLastPunchTimeStamp( $id, $last_poll_date = 0, $last_punch_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = new StationListFactory();
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'last_poll_date' => $last_poll_date,
						'last_punch_date' => Carbon::parse( $last_punch_date )->toDateTimeString(),
						'id' => $id,
						);
			$query = 'UPDATE '. $this->getTable() .' set last_poll_date = :last_poll_date ,last_punch_time_stamp = :last_punch_date where id = :id';
			DB::select($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	//Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPushDate( $id, $last_push_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = new StationListFactory();
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						':last_push_date' => $last_push_date,
						':id' => $id,
						);

			$query = 'UPDATE '. $this->getTable() .' set last_push_date = :last_push_date where id = :id';
			DB::select($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	//Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPartialPushDate( $id, $last_partial_push_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = new StationListFactory();
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						':last_partial_push_date' => $last_partial_push_date,
						':id' => $id,
						);

			$query = 'UPDATE '. $this->getTable() .' set last_partial_push_date = :last_partial_push_date where id = :id';
			DB::select($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	function getLastPunchTimeStamp( $raw = FALSE) {
		if ( isset($this->data['last_punch_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['last_punch_time_stamp'];
			} else {
				return TTDate::strtotime( $this->data['last_punch_time_stamp'] );
			}
		}

		return FALSE;
	}
	function setLastPunchTimeStamp($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'last_punch_time_stamp',
												$epoch,
												('Incorrect last punch date')) ) {

			$this->data['last_punch_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPollDate() {
		if ( isset($this->data['last_poll_date']) ) {
			return $this->data['last_poll_date'];
		}

		return FALSE;
	}
	function setLastPollDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'last_poll_date',
												$epoch,
												('Incorrect last poll date')) ) {

			$this->data['last_poll_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPollStatusMessage() {
		if ( isset($this->data['last_poll_status_message']) ) {
			return $this->data['last_poll_status_message'];
		}

		return FALSE;
	}
	function setLastPollStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_poll_status_message',
											$value,
											('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_poll_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLastPushDate() {
		if ( isset($this->data['last_push_date']) ) {
			return $this->data['last_push_date'];
		}

		return FALSE;
	}
	function setLastPushDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'last_push_date',
												$epoch,
												('Incorrect last push date')) ) {

			$this->data['last_push_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPushStatusMessage() {
		if ( isset($this->data['last_push_status_message']) ) {
			return $this->data['last_push_status_message'];
		}

		return FALSE;
	}
	function setLastPushStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_push_status_message',
											$value,
											('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_push_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLastPartialPushDate() {
		if ( isset($this->data['last_partial_push_date']) ) {
			return $this->data['last_partial_push_date'];
		}

		return FALSE;
	}
	function setLastPartialPushDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'last_partial_push_date',
												$epoch,
												('Incorrect last partial push date')) ) {

			$this->data['last_partial_push_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPartialPushStatusMessage() {
		if ( isset($this->data['last_partial_push_status_message']) ) {
			return $this->data['last_partial_push_status_message'];
		}

		return FALSE;
	}
	function setLastPartialPushStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_partial_push_status_message',
											$value,
											('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_partial_push_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1() {
		if ( isset($this->data['user_value_1']) ) {
			return $this->data['user_value_1'];
		}

		return FALSE;
	}
	function setUserValue1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_1',
											$value,
											('User Value 1 is invalid'),
											1,255) ) {

			$this->data['user_value_1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue2() {
		if ( isset($this->data['user_value_2']) ) {
			return $this->data['user_value_2'];
		}

		return FALSE;
	}
	function setUserValue2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_2',
											$value,
											('User Value 2 is invalid'),
											2,255) ) {

			$this->data['user_value_2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue3() {
		if ( isset($this->data['user_value_3']) ) {
			return $this->data['user_value_3'];
		}

		return FALSE;
	}
	function setUserValue3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_3',
											$value,
											('User Value 3 is invalid'),
											3,255) ) {

			$this->data['user_value_3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue4() {
		if ( isset($this->data['user_value_4']) ) {
			return $this->data['user_value_4'];
		}

		return FALSE;
	}
	function setUserValue4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_4',
											$value,
											('User Value 4 is invalid'),
											4,255) ) {

			$this->data['user_value_4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue5() {
		if ( isset($this->data['user_value_5']) ) {
			return $this->data['user_value_5'];
		}

		return FALSE;
	}
	function setUserValue5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_5',
											$value,
											('User Value 5 is invalid'),
											5,255) ) {

			$this->data['user_value_5'] = $value;

			return TRUE;
		}

		return FALSE;
	}



	private function genStationID() {
		return md5( uniqid( dechex( mt_srand() ) ) );
	}

	function setCookie() {
		if ( $this->getStation() ) {

			setcookie('StationID', $this->getStation(), time()+157680000, Environment::getBaseURL() );

			return TRUE;
		}

		return FALSE;
	}

	function destroyCookie() {
		setcookie('StationID', NULL, time()+9999999, Environment::getBaseURL() );

		return TRUE;
	}

	//Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateAllowedDate( $id, $user_id ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$slf = new StationListFactory();
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						':allowed_date' => TTDate::getTime(),
						':id' => $id,
						);
			$query = 'UPDATE '. $this->getTable() .' set allowed_date = :allowed_date where id = :id';
			DB::select($query, $ph);

			TTLog::addEntry( $id, 200,  ('Access from station Allowed'), $user_id, $this->getTable() ); //Allow

			return TRUE;
		}

		return FALSE;
	}

	function getAllowedDate() {
		if ( isset($this->data['allowed_date']) ) {
			return $this->data['allowed_date'];
		}

		return FALSE;
	}
	function setAllowedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'allowed_date',
												$epoch,
												('Incorrect allowed date')) ) {

			$this->data['allowed_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function checkSource( $source, $current_station_id ) {
		$source = trim($source);

		if ( isset($_SERVER['REMOTE_ADDR']) ) {
			$remote_addr = $_SERVER['REMOTE_ADDR'];
		} else {
			$remote_addr = NULL;
		}
		if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$x_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$x_forwarded_for = NULL;
		}

		//IGNORE x_forwarded_for for now, because anyone could spoof this.
		//Add a switch that will enable/disable this feature.

		//$remote_addr = '192.168.2.10';
		//$remote_addr = '192.168.1.10';
		//$remote_addr = '127.0.0.1';

		if ( in_array( $this->getType(), array(10,25) ) AND preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})*/', $source) ) {
			Debug::text('Source is an IP address!', __FILE__, __LINE__, __METHOD__, 10);
		} elseif ( in_array( $this->getType(), array(10,25) ) AND !in_array( strtolower( $this->getStation() ), $this->getOptions('station_reserved_word') ) )  {
			Debug::text('Source is NOT an IP address, do hostname lookup: '. $source , __FILE__, __LINE__, __METHOD__, 10);

			$hostname_lookup = $this->getCache( $remote_addr.$source );
			if ( $hostname_lookup === FALSE ) {
				$hostname_lookup = gethostbyname( $source );

				$this->saveCache($hostname_lookup, $remote_addr.$source );
			}

			if ($hostname_lookup == $source ) {
				Debug::text('Hostname lookup failed!', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::text('Hostname lookup succeeded: '. $hostname_lookup, __FILE__, __LINE__, __METHOD__, 10);
				$source = $hostname_lookup;
			}
			unset($hostname_lookup);
		} else {
			Debug::text('Source is not internet related', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('Source: '. $source .' Remote IP: '. $remote_addr .' Behind Proxy IP: '. $x_forwarded_for, __FILE__, __LINE__, __METHOD__, 10);
		if ( 	(
					$current_station_id == $this->getStation()
						OR in_array( strtolower( $this->getStation() ), $this->getOptions('station_reserved_word') )
				)
				AND
				(
					in_array( strtolower( $this->getSource() ), $this->getOptions('source_reserved_word') )
					OR
						( $source == $remote_addr
							/*OR $source == $x_forwarded_for*/ )
					OR
						( $current_station_id == $this->getSource() )
					OR
						( Net_IPv4::ipInNetwork( $remote_addr, $source) )
					OR
						in_array( $this->getType(), array(100,110,120,200) )
				)

			) {

			Debug::text('Returning TRUE', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('Returning FALSE', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isAllowed($user_id = NULL, $current_station_id = NULL, $id = NULL) {
		if ($user_id == NULL OR $user_id == '') {
			global $current_user;
			$user_id = $current_user->getId();
		}
		//Debug::text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($current_station_id == NULL OR $current_station_id == ''){
			global $current_station;
			$current_station_id = $current_station->getStation();
		}
		//Debug::text('Station ID: '. $current_station_id, __FILE__, __LINE__, __METHOD__, 10);

		//Debug::text('Status: '. $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStatus() != 20 ) { //Enabled
			return FALSE;
		}

		$retval = FALSE;

		Debug::text('User ID: '. $user_id .' Station ID: '. $current_station_id .' Status: '. $this->getStatus() .' Current Station: '. $this->getStation() , __FILE__, __LINE__, __METHOD__, 10);

		//Handle IP Addresses/Hostnames
		if ( $this->getType() == 10
				AND !in_array( strtolower( $this->getSource() ), $this->getOptions('source_reserved_word') ) ) {

			if ( strpos( $this->getSource(), ',') !== FALSE ) {
				//Found list
				$source = explode(',', $this->getSource() );
			} else {
				//Found single entry
				$source[] = $this->getSource();
			}

			if ( is_array($source) ) {
				foreach( $source as $tmp_source ) {
					if ( $this->checkSource( $tmp_source, $current_station_id ) == TRUE ) {
						$retval = TRUE;
						break;
					}
				}
				unset($tmp_source);
			}
		} else {
			$source = $this->getSource();

			$retval = $this->checkSource( $source, $current_station_id );
		}

		//Debug::text('Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Current Station ID: '. $current_station_id .' Station ID: '. $this->getStation(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $retval === TRUE ) {
			Debug::text('Station IS allowed! ', __FILE__, __LINE__, __METHOD__, 10);

			//Set last allowed date, so we can track active/inactive stations.
			if ( $id != NULL AND $id != '' ) {
				$this->updateAllowedDate( $id, $user_id );
			}

			return TRUE;
		}

		Debug::text('Station IS NOT allowed! ', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//A fast way to check many stations if the user is allowed.
	function checkAllowed($user_id = NULL, $station_id = NULL, $type = 'PC') {
		if ($user_id == NULL OR $user_id == '') {
			global $current_user;
			$user_id = $current_user->getId();
		}
		Debug::text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($station_id == NULL OR $station_id == ''){
			global $current_station;
			if ( is_object($current_station) ) {
				$station_id = $current_station->getStation();
			} elseif ( $this->getId() != '' ) {
				$station_id = $this->getId();
			} else {
				Debug::text('Unable to get Station Object! Station ID: '. $station_id, __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
		}

		$slf = new StationListFactory();
		$slf->getByUserIdAndStatusAndType($user_id, 'ENABLED', $type);
		Debug::text('Station ID: '. $station_id .' Type: '. $type .' Found Stations: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach($slf->rs as $station) {
			$slf->data = (array)$station;
			Debug::text('Checking Station ID: '. $slf->getId(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $slf->isAllowed( $user_id, $station_id, $slf->getId() ) === TRUE) {
				Debug::text('Station IS allowed! '. $station_id .' - ID: '. $slf->getId() , __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		}

		return FALSE;
	}

	static function getOrCreatePCStation( $station_id, $company_id ) {
		Debug::text('Checking for Station ID: '. $station_id .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10);
		$slf = new StationListFactory();
		$slf->getByStationIdandCompanyId( $station_id, $company_id );
		if ( $slf->getRecordCount() == 1 ) {
			$retval = $slf->getCurrent()->getStation();
		} else {
			Debug::text('Station ID: '. $station_id .' does not exist, creating new station', __FILE__, __LINE__, __METHOD__, 10);

			//Insert new station
			$sf = new StationFactory();
			$sf->setCompany( $company_id );
			$sf->setStatus( 'ENABLED' );
			$sf->setType( 'PC' );
			$sf->setSource( $_SERVER['REMOTE_ADDR'] );
			$sf->setStation();
			$sf->setDescription( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250) );
			if ( $sf->Save(FALSE) ) {
				$retval = $sf->getStation();
			}
		}

		Debug::text('Returning Station ID: '. $station_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function Validate() {
		if ( $this->getDescription() == '' ) {
			$this->Validator->isTrue(		'description',
											FALSE,
											('Description must be specified'));
		}

		return TRUE;
	}

	function preSave() {
		//New stations are deny all by default, so if they haven't
		//set the selection types, default them to only selected, so
		//everyone is denied, because none are selected.
		if ( $this->getGroupSelectionType() == FALSE ) {
			$this->setGroupSelectionType( 20 ); //Only selected.
		}
		if ( $this->getBranchSelectionType() == FALSE ) {
			$this->setBranchSelectionType( 20 ); //Only selected.
		}
		if ( $this->getDepartmentSelectionType() == FALSE ) {
			$this->setDepartmentSelectionType( 20 ); //Only selected.
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getStation() );
/*
		foreach ($this->getUser() as $user_id ) {
			$cache_id = 'station_checkAllowed_'.$this->getId().$user_id;
			$this->removeCache( $cache_id );
		}
*/
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'group':
							$this->setGroup( $data[$key] );
							break;
						case 'branch':
							$this->setBranch( $data[$key] );
							break;
						case 'department':
							$this->setDepartment( $data[$key] );
							break;
						case 'include_user':
							$this->setIncludeUser( $data[$key] );
							break;
						case 'exclude_user':
							$this->setExcludeUser( $data[$key] );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							break;
						case 'group':
							$data[$variable] = $this->getGroup();
							break;
						case 'branch':
							$data[$variable] = $this->getBranch();
							break;
						case 'department':
							$data[$variable] = $this->getDepartment();
							break;
						case 'include_user':
							$data[$variable] = $this->getIncludeUser();
							break;
						case 'exclude_user':
							$data[$variable] = $this->getExcludeUser();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( !( $log_action == 10 AND $this->getType() == 10 ) ) {
			return TTLog::addEntry( $this->getId(), $log_action,  ('Station'), NULL, $this->getTable(), $this );
		}
	}
}
?>
