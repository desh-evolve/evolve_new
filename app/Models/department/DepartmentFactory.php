<?php

namespace App\Models\Department;

use App\Models\Company\CompanyGenericMapListFactory;
use App\Models\Company\CompanyGenericTagMapFactory;
use App\Models\Company\CompanyGenericTagMapListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Company\CompanyListFactory;
use Illuminate\Support\Facades\DB;

class DepartmentFactory extends Factory
{
	protected $table = 'department';
	protected $pk_sequence_name = 'department_id_seq'; //PK Sequence name


	function _getFactoryOptions($name)
	{

		$retval = NULL;
		switch ($name) {
			case 'status':
				$retval = array(
					10 => ('ENABLED'),
					20 => ('DISABLED')
				);
				break;
			case 'columns':
				$retval = array(
					'-1010-status' => ('Status'),
					'-1020-manual_id' => ('Code'),
					'-1030-name' => ('Name'),

					'-1300-tag' => ('Tags'),

					'-2000-created_by' => ('Created By'),
					'-2010-created_date' => ('Created Date'),
					'-2020-updated_by' => ('Updated By'),
					'-2030-updated_date' => ('Updated Date'),
				);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey($this->getOptions('default_display_columns'), Misc::trimSortPrefix($this->getOptions('columns')));
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
					'manual_id',
					'name',
				);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
					'name',
					'manual_id'
				);
		}

		return $retval;
	}

	function _getVariableToFunctionMap($data)
	{
		$variable_function_map = array(
			'id' => 'ID',
			'company_id' => 'Company',
			'status_id' => 'Status',
			'status' => FALSE,
			'manual_id' => 'ManualID',
			'name' => 'Name',
			'other_id1' => 'OtherID1',
			'other_id2' => 'OtherID2',
			'other_id3' => 'OtherID3',
			'other_id4' => 'OtherID4',
			'other_id5' => 'OtherID5',
			'tag' => 'Tag',
			'deleted' => 'Deleted',
		);
		return $variable_function_map;
	}

	function getCompany()
	{
		return $this->data['company_id'];
	}
	function setCompany($id)
	{
		$id = trim($id);

		$clf = new CompanyListFactory();

		if (
			$id == 0
			or $this->Validator->isResultSetWithRows(
				'company',
				$clf->getByID($id),
				('Company is invalid')
			)
		) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus()
	{
		//Have to return the KEY because it should always be a drop down box.
		//return Option::getByKey($this->data['status_id'], $this->getOptions('status') );
		return $this->data['status_id'];
	}
	function setStatus($status)
	{
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status'));
		if ($key !== FALSE) {
			$status = $key;
		}

		if ($this->Validator->inArrayKey(
			'status',
			$status,
			('Incorrect Status'),
			$this->getOptions('status')
		)) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueManualID($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':manual_id' => $id,
			':company_id' =>  $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where manual_id = :manual_id AND company_id = :company_id AND deleted=0';
		$id = DB::select($query, $ph);

		// if ($id === FALSE ) {
		//     $id = 0;
		// }else{
		//     $id = current(get_object_vars($id[0]));
		// }

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}

		Debug::Arr($id, 'Unique Department: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if (empty($id) || $id === FALSE) {
			return TRUE;
		} else {
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}
	static function getNextAvailableManualId($company_id = NULL)
	{
		global $current_company;

		if ($company_id == '' and is_object($current_company)) {
			$company_id = $current_company->getId();
		} elseif ($company_id == '' and isset($this) and is_object($this)) {
			$company_id = $this->getCompany();
		}

		$dlf = new DepartmentListFactory();
		$dlf->getHighestManualIDByCompanyId($company_id);
		if ($dlf->getRecordCount() > 0) {
			$next_available_manual_id = $dlf->getCurrent()->getManualId() + 1;
		} else {
			$next_available_manual_id = 1;
		}

		return $next_available_manual_id;
	}

	function getManualID()
	{
		if (isset($this->data['manual_id'])) {
			return $this->data['manual_id'];
		}

		return FALSE;
	}
	function setManualID($value)
	{
		$value = trim($value);

		if (
			$this->Validator->isNumeric(
				'manual_id',
				$value,
				('Code is invalid')
			)
			and
			$this->Validator->isLength(
				'manual_id',
				$value,
				('Code has too many digits'),
				0,
				10
			)
			and
			$this->Validator->isTrue(
				'manual_id',
				$this->isUniqueManualID($value),
				('Code is already in use, please enter a different one')
			)
		) {

			$this->data['manual_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueName($name)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$name = trim($name);
		if ($name == '') {
			return FALSE;
		}

		$ph = array(
			':company_id' => $this->getCompany(),
			':name' => $name,
		);

		$query = 'select id from ' . $this->table . '
					where company_id = :company_id
						AND name = :name
						AND deleted = 0';

		// dd($query);
		$name_id = DB::select($query, $ph);

		if (empty($name_id)) {
			$name_id = 0;
		} else {
			$name_id = $name_id[0]->id ?? 0;

			if ($name_id == $this->getId()) {
				return TRUE;
			}
		}

		return ($name_id == 0) ? TRUE : FALSE;
		if (empty($name_id)) {
			$name_id = 0;
		} else {
			$name_id = $name_id[0]->id ?? 0;

			if ($name_id == $this->getId()) {
				return TRUE;
			}
		}

		return ($name_id == 0) ? TRUE : FALSE;
	}

	function getName()
	{
		return $this->data['name'];
	}
	function setName($name)
	{
		$name = trim($name);

		if (
			$this->Validator->isLength(
				'name',
				$name,
				('Department name is too short or too long'),
				2,
				100
			)
			and
			$this->Validator->isTrue(
				'name',
				$this->isUniqueName($name),
				('Department already exists')
			)

		) {

			$this->data['name'] = $name;
			$this->setNameMetaphone($name);

			return TRUE;
		}

		return FALSE;
	}
	function getNameMetaphone()
	{
		if (isset($this->data['name_metaphone'])) {
			return $this->data['name_metaphone'];
		}

		return FALSE;
	}
	function setNameMetaphone($value)
	{
		$value = metaphone(trim($value));

		if ($value != '') {
			$this->data['name_metaphone'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch()
	{
		$dblf = new DepartmentBranchListFactory();
		$dblf->getByDepartmentId($this->getId());
		foreach ($dblf->rs as $department_branch) {
			$dblf->data = (array) $department_branch;
			$department_branch = $dblf;
			$branch_list[] = $department_branch->getBranch();
		}

		if (isset($branch_list)) {
			return $branch_list;
		}

		return FALSE;
	}
	function setBranch($ids)
	{
		if (is_array($ids) and count($ids) > 0) {
			//If needed, delete mappings first.
			$dblf = new DepartmentBranchListFactory();
			$dblf->getByDepartmentId($this->getId());

			$branch_ids = array();
			foreach ($dblf->rs as $department_branch) {
				$dblf->data = (array) $department_branch;
				$department_branch = $dblf;
				$branch_id = $department_branch->getBranch();
				Debug::text('Department ID: ' . $department_branch->getDepartment() . ' Branch: ' . $branch_id, __FILE__, __LINE__, __METHOD__, 10);

				//Delete branches that are not selected.
				if (!in_array($branch_id, $ids)) {
					Debug::text('Deleting DepartmentBranch: ' . $branch_id, __FILE__, __LINE__, __METHOD__, 10);
					$department_branch->Delete();
				} else {
					//Save branch ID's that need to be updated.
					Debug::text('NOT Deleting DepartmentBranch: ' . $branch_id, __FILE__, __LINE__, __METHOD__, 10);
					$branch_ids[] = $branch_id;
				}
			}

			//Insert new mappings.
			$dbf = new DepartmentBranchFactory();
			foreach ($ids as $id) {
				if (!in_array($id, $branch_ids)) {
					$dbf->setDepartment($this->getId());
					$dbf->setBranch($id);

					if ($this->Validator->isTrue(
						'branch',
						$dbf->Validator->isValid(),
						('Branch selection is invalid')
					)) {
						$dbf->save();
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1()
	{
		if (isset($this->data['other_id1'])) {
			return $this->data['other_id1'];
		}

		return FALSE;
	}
	function setOtherID1($value)
	{
		$value = trim($value);

		if (
			$value == ''
			or
			$this->Validator->isLength(
				'other_id1',
				$value,
				('Other ID 1 is invalid'),
				1,
				255
			)
		) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2()
	{
		if (isset($this->data['other_id2'])) {
			return $this->data['other_id2'];
		}

		return FALSE;
	}
	function setOtherID2($value)
	{
		$value = trim($value);

		if (
			$value == ''
			or
			$this->Validator->isLength(
				'other_id2',
				$value,
				('Other ID 2 is invalid'),
				1,
				255
			)
		) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3()
	{
		if (isset($this->data['other_id3'])) {
			return $this->data['other_id3'];
		}

		return FALSE;
	}
	function setOtherID3($value)
	{
		$value = trim($value);

		if (
			$value == ''
			or
			$this->Validator->isLength(
				'other_id3',
				$value,
				('Other ID 3 is invalid'),
				1,
				255
			)
		) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4()
	{
		if (isset($this->data['other_id4'])) {
			return $this->data['other_id4'];
		}

		return FALSE;
	}
	function setOtherID4($value)
	{
		$value = trim($value);

		if (
			$value == ''
			or
			$this->Validator->isLength(
				'other_id4',
				$value,
				('Other ID 4 is invalid'),
				1,
				255
			)
		) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5()
	{
		if (isset($this->data['other_id5'])) {
			return $this->data['other_id5'];
		}

		return FALSE;
	}
	function setOtherID5($value)
	{
		$value = trim($value);

		if (
			$value == ''
			or
			$this->Validator->isLength(
				'other_id5',
				$value,
				('Other ID 5 is invalid'),
				1,
				255
			)
		) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getTag()
	{
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if (isset($this->tmp_data['tags'])) {
			return $this->tmp_data['tags'];
		} elseif ($this->getCompany() > 0 and $this->getID() > 0) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID($this->getCompany(), 120, $this->getID());
		}

		return FALSE;
	}
	function setTag($tags)
	{
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}

	function preSave()
	{
		if ($this->getStatus() == FALSE) {
			$this->setStatus(10);
		}

		if ($this->getManualID() == FALSE) {
			$this->setManualID(DepartmentListFactory::getNextAvailableManualId($this->getCompany()));
		}

		return TRUE;
	}

	function postSave()
	{
		$this->removeCache($this->getId());

		if ($this->getDeleted() == FALSE) {
			CompanyGenericTagMapFactory::setTags($this->getCompany(), 120, $this->getID(), $this->getTag());
		}

		if ($this->getDeleted() == TRUE) {
			Debug::Text('UnAssign Hours from Department: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this department.
			$pcf = new PunchControlFactory();
			$udtf = new UserDateTotalFactory();
			$uf = new UserFactory();
			$sf = new StationFactory();
			$sdf = new StationDepartmentFactory();
			$sf_b = new ScheduleFactory();
			$udf = new UserDefaultFactory();
			$rstf = new RecurringScheduleTemplateFactory();

			$query = 'update ' . $pcf->getTable() . ' set department_id = 0 where department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $udtf->getTable() . ' set department_id = 0 where department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $sf_b->getTable() . ' set department_id = 0 where department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $uf->getTable() . ' set default_department_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND default_department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $udf->getTable() . ' set default_department_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND default_department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $sf->getTable() . ' set department_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'delete from ' . $sdf->getTable() . ' where department_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $rstf->getTable() . ' set department_id = 0 where department_id = ' . (int)$this->getId();
			DB::select($query);

			//Job employee criteria
			$cgmlf = new CompanyGenericMapListFactory();
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID($this->getCompany(), 1020, $this->getID());
			if ($cgmlf->getRecordCount() > 0) {
				foreach ($cgmlf->rs as $cgm_obj) {
					$cgmlf->data = (array) $cgm_obj;
					$cgm_obj = $cgmlf;
					Debug::text('Deleteing from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$cgm_obj->Delete();
				}
			}
		}

		return TRUE;
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray($data)
	{
		if (is_array($data)) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ($variable_function_map as $key => $function) {
				if (isset($data[$key])) {

					$function = 'set' . $function;
					switch ($key) {
						default:
							if (method_exists($this, $function)) {
								$this->$function($data[$key]);
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns($data);

			return TRUE;
		}

		return FALSE;
	}


	function getObjectAsArray($include_columns = NULL)
	{
		$variable_function_map = $this->getVariableToFunctionMap();
		if (is_array($variable_function_map)) {
			foreach ($variable_function_map as $variable => $function_stub) {
				if ($include_columns == NULL or (isset($include_columns[$variable]) and $include_columns[$variable] == TRUE)) {

					$function = 'get' . $function_stub;
					switch ($variable) {
						case 'status':
							$function = 'get' . $variable;
							if (method_exists($this, $function)) {
								$data[$variable] = Option::getByKey($this->$function(), $this->getOptions($variable));
							}
							break;
						default:
							if (method_exists($this, $function)) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns($data, $include_columns);
		}

		return $data;
	}

	function addLog($log_action)
	{
		return TTLog::addEntry($this->getId(), $log_action, ('Department') . ': ' . $this->getName(), NULL, $this->getTable(), $this);
	}
}
