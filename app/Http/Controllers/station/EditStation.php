<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Core\StationFactory;
use App\Models\Core\StationListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use App\Models\Core\TimeClock;
use App\Models\Core\TTLog;
use App\Models\Core\TTDate;
use App\Models\Core\FastTree;
use App\Models\users\UserGroupListFactory;
use App\Models\users\UserListFactory;
use App\Models\company\BranchListFactory;
use App\Models\department\DepartmentListFactory;
use App\Models\Core\JobListFactory;
use App\Models\Core\JobItemListFactory;
use App\Models\Core\UserPreferenceFactory;
use Illuminate\Support\Facades\View;

class EditStation extends Controller
{
	protected $permission;
	protected $company;
	protected $userPrefs;
	protected $stationFactory;
	protected $stationListFactory;

	public function __construct()
	{
		$basePath = Environment::getBasePath();
		require_once($basePath . '/app/Helpers/global.inc.php');
		require_once($basePath . '/app/Helpers/Interface.inc.php');

		$this->userPrefs = View::shared('current_user_prefs');
		$this->company = View::shared('current_company');
		$this->permission = View::shared('permission');
	}

	public function index($id = null)
	{
		$current_company = $this->company;
		$sf = new StationFactory();

		if ($id) {
			$slf = new StationListFactory();
			$slf->getByIdAndCompanyId($id, $current_company->getId());
			foreach ($slf->rs as $s_obj) {
				$slf->data = (array)$s_obj;
				$s_obj = $slf;
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
		} else {
			$data = [
				'status' => 20,
				'port' => 80,
				'password' => 0,
				'poll_frequency' => 600,
				'push_frequency' => 86400,
				'partial_push_frequency' => 3600
			];
		}
		// Select box options
		$data['status_options'] = $sf->getOptions('status');
		$data['type_options'] = $sf->getOptions('type');
		$data['poll_frequency_options'] = $sf->getOptions('poll_frequency');
		$data['push_frequency_options'] = $sf->getOptions('push_frequency');
		$data['time_clock_command_options'] = $sf->getOptions('time_clock_command');
		$data['mode_flag_options'] = $sf->getOptions('mode_flag');

		// if ($current_company->getProductEdition() == 20) {
		//     $jlf = new JobListFactory();
		//     $jlf->getByCompanyId($current_company->getId());
		//     $data['job_options'] = Misc::prependArray([0 => '-- None --'], $jlf->getArrayByListFactory($jlf, false, true));

		//     $jilf = new JobItemListFactory();
		//     $jilf->getByCompanyIdAndStatus($current_company->getId(), 10);
		//     $data['job_item_options'] = Misc::prependArray([0 => '-- None --'], $jilf->getArrayByListFactory($jilf, true, false));
		// }
		// Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId($current_company->getId());
		$branch_options = $blf->getArrayByListFactory($blf, false, true);
		$data['src_branch_options'] = Misc::arrayDiffByKey((array)($data['branch_ids'] ?? []), $branch_options);
		$data['selected_branch_options'] = Misc::arrayIntersectByKey((array)($data['branch_ids'] ?? []), $branch_options);
		// $data['selected_branch_options'] = Misc::arrayIntersectByKey((array)$data['branch_ids'], $branch_options);

		// dd($data);
		// Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId($current_company->getId());
		$department_options = $dlf->getArrayByListFactory($dlf, false, true);
		$data['src_department_options'] = Misc::arrayDiffByKey((array)($data['department_ids'] ?? []), $department_options);
		$data['selected_department_options'] = Misc::arrayIntersectByKey((array)($data['department_ids'] ?? []), $department_options);

		$uglf = new UserGroupListFactory();
		$uglf->getByCompanyId($current_company->getId());
		$group_options = $uglf->getArrayByListFactory($uglf, false, true);
		$data['src_group_options'] = Misc::arrayDiffByKey((array)($data['group_ids'] ?? []), $group_options);
		$data['selected_group_options'] = Misc::arrayIntersectByKey((array)($data['group_ids'] ?? []), $group_options);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria($current_company->getId(), null);
		$user_options = $ulf->getArrayByListFactory($ulf, false, true);

		$data['src_include_user_options'] = Misc::arrayDiffByKey((array)($data['include_user_ids'] ?? []), $user_options);
		$data['selected_include_user_options'] = Misc::arrayIntersectByKey((array)($data['include_user_ids'] ?? []), $user_options);

		$data['src_exclude_user_options'] = Misc::arrayDiffByKey((array)($data['exclude_user_ids'] ?? []), $user_options);
		$data['selected_exclude_user_options'] = Misc::arrayIntersectByKey((array)($data['exclude_user_ids'] ?? []), $user_options);

		$data['group_selection_type_options'] = $sf->getOptions('group_selection_type');
		$data['branch_selection_type_options'] = $sf->getOptions('branch_selection_type');
		$data['department_selection_type_options'] = $sf->getOptions('department_selection_type');

		$data['branch_options'] = Misc::prependArray([0 => '-- None --'], $branch_options);
		$data['department_options'] = Misc::prependArray([0 => '-- None --'], $department_options);

		// $upf = new UserPreferenceFactory();
		$timezone_options = Misc::prependArray([0 => '-- None --'], $this->userPrefs->getOptions('time_zone'));
		$data['time_zone_options'] = $timezone_options;

		$viewData = [
			'title' => $id ? 'Edit Station' : 'Add Station',
			'data' => $data,
			'sf' => $sf
		];
		// dd($viewData);
		return view('station.EditStation', $viewData);
	}

	public function submit(Request $request, $id = null)
	{

		$current_company = $this->company;
		$data = $request->input('data', []); 
		$action = $request->input('action');

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

		if ($action === 'time_clock_command') {
			return $this->handleTimeClockCommand($data, $id);
		}

		$sf = new StationFactory();
		$sf->StartTransaction();

		$sf->setId($id ?? null);
		$sf->setCompany($current_company->getId());
		$sf->setStatus($data['status'] ?? '');
		$sf->setType($data['type'] ?? '');
		$sf->setSource($data['source'] ?? '');
		$sf->setStation($data['station'] ?? '');
		$sf->setDescription($data['description'] ?? '');

		if (isset($data['port'])) {
			$sf->setPort($data['port']);
		}
		if (isset($data['user_name'])) {
			$sf->setUserName($data['user_name']);
		}
		if (isset($data['password'])) {
			$sf->setPassword($data['password']);
		}

		if (($data['type'] ?? 0)>= 100) {
			if (isset($data['poll_frequency'])) {
				$sf->setPollFrequency($data['poll_frequency']);
			}
			if (isset($data['push_frequency'])) {
				$sf->setPushFrequency($data['push_frequency']);
			}
			if (isset($data['partial_push_frequency'])) {
				$sf->setPartialPushFrequency($data['partial_push_frequency']);
			}
			$sf->setEnableAutoPunchStatus(isset($data['enable_auto_punch_status']));
			if (isset($data['mode_flag'])) {
				$sf->setModeFlag($data['mode_flag']);
			}
		}

		if (isset($data['branch_id'])) {
			$sf->setDefaultBranch($data['branch_id']);
		}
		if (isset($data['department_id'])) {
			$sf->setDefaultDepartment($data['department_id']);
		}
		if (isset($data['job_id'])) {
			$sf->setDefaultJob($data['job_id']);
		}
		if (isset($data['job_item_id'])) {
			$sf->setDefaultJobItem($data['job_item_id']);
		}

		if (isset($data['time_zone_id'])) {
			$sf->setTimeZone($data['time_zone_id']);
		}

		// dd($data);
		$sf->setGroupSelectionType($data['group_selection_type_id'] ?? '');
		$sf->setBranchSelectionType($data['branch_selection_type_id'] ?? '');
		$sf->setDepartmentSelectionType($data['department_selection_type_id'] ?? '');
		if ($sf->isValid()) {
			$sf->Save(false);

			$sf->setGroup($data['group_ids'] ?? []);
			$sf->setBranch($data['branch_ids'] ?? []);
			$sf->setDepartment($data['department_ids'] ?? []);
			$sf->setIncludeUser($data['include_user_ids'] ?? []);
			$sf->setExcludeUser($data['exclude_user_ids'] ?? []);

			if ($sf->isValid()) {
				$sf->Save(true);
				$sf->CommitTransaction();
				return redirect()->to(URLBuilder::getURL(null, '/station'))->with('success', 'Station saved successfully.');
			}
		}

		$sf->FailTransaction();
		return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
	}

	protected function handleTimeClockCommand($data, $id)
	{
		$current_company = $this->company;

		try {
			$tc = new TimeClock($data['type']);
			$tc->setIPAddress($data['source']);
			$tc->setPort($data['port']);
			$tc->setPassword($data['password']);

			$slf = new StationListFactory();
			$slf->getByIdAndCompanyId($id, $current_company->getId());
			if ($slf->getRecordCount() == 1) {
				$s_obj = $slf->getCurrent();
			}

			$s_obj->setLastPunchTimeStamp($s_obj->getLastPunchTimeStamp());

			if ($s_obj->getTimeZone() != '' && !is_numeric($s_obj->getTimeZone())) {
				Debug::text('Setting Station TimeZone To: ' . $s_obj->getTimeZone(), __FILE__, __LINE__, __METHOD__, 10);
				TTDate::setTimeZone($s_obj->getTimeZone());
			}

			$result_str = null;
			switch ($data['time_clock_command']) {
				case 'test_connection':
					$result_str = $tc->testConnection() ? _('Connection Succeeded!') : _('Connection Failed!');
					break;
				case 'set_date':
					TTDate::setTimeZone($data['time_zone_id'], $s_obj->getTimeZone());
					$result_str = $tc->setDate(time()) ?
						_('Date Successfully Set To: ') . TTDate::getDate('DATE+TIME', time()) :
						_('Setting Date Failed!');
					break;
				case 'download':
					if (isset($s_obj) && $tc->Poll($current_company, $s_obj)) {
						$result_str = _('Download Data Succeeded!');
						if ($s_obj->isValid()) {
							$s_obj->Save(false);
						}
					} else {
						$result_str = _('Download Data Failed!');
					}
					break;
				case 'upload':
					if (isset($s_obj) && $tc->Push($current_company, $s_obj)) {
						$result_str = _('Upload Data Succeeded!');
						if ($s_obj->isValid()) {
							$s_obj->Save(false);
						}
					} else {
						$result_str = _('Upload Data Failed!');
					}
					break;
				case 'update_config':
					$result_str = isset($s_obj) && $tc->setModeFlag($s_obj->getModeFlag()) ?
						_('Update Configuration Succeeded') :
						_('Update Configuration Failed');
					break;
				case 'delete_data':
					if (isset($s_obj) && $tc->DeleteAllData($s_obj)) {
						$result_str = _('Delete Data Succeeded!');
						if ($s_obj->isValid()) {
							$s_obj->Save(false);
						}
					} else {
						$result_str = _('Delete Data Failed!');
					}
					break;
				case 'reset_last_punch_time_stamp':
					$s_obj->setLastPunchTimeStamp(time());
					if ($s_obj->isValid()) {
						$s_obj->Save(false);
					}
					break;
				case 'clear_last_punch_time_stamp':
					$s_obj->setLastPunchTimeStamp(1);
					if ($s_obj->isValid()) {
						$s_obj->Save(false);
					}
					break;
				case 'restart':
					$tc->restart();
					$result_str = _('Restart Succeeded!');
					break;
				case 'firmware':
					$result_str = $tc->setFirmware() ?
						_('Firmware Update Succeeded!') :
						_('Firmware Update Failed!');
					break;
			}

			TTLog::addEntry(
				$s_obj->getId(),
				500,
				_('TimeClock Manual Command') . ': ' . ucwords(str_replace('_', ' ', $data['time_clock_command'])) . ' ' . _('Result') . ': ' . $result_str,
				null,
				$s_obj->getTable()
			);

			if (isset($s_obj)) {
				$data['last_poll_date'] = $s_obj->getLastPollDate();
				$data['last_push_date'] = $s_obj->getLastPushDate();
			}

			return redirect()->back()->with([
				'time_clock_command_result' => $result_str,
				'data' => $data
			]);
		} catch (Exception $e) {
			return redirect()->back()->with([
				'time_clock_command_result' => _('Connection Failed!'),
				'data' => $data
			]);
		}
	}
}
