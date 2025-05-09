<?php

namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Users\UserWageListFactory;
use Illuminate\Support\Facades\DB;

class WageGroupFactory extends Factory
{
	protected $table = 'wage_group';
	protected $pk_sequence_name = 'wage_group_id_seq'; //PK Sequence name

	function _getFactoryOptions($name)
	{

		$retval = NULL;
		switch ($name) {
			case 'columns':
				$retval = array(
					'-1030-name' => ('Name'),

					'-2000-created_by' => ('Created By'),
					'-2010-created_date' => ('Created Date'),
					'-2020-updated_by' => ('Updated By'),
					'-2030-updated_date' => ('Updated Date'),
				);
				break;
			case 'unique_columns': //Columns that are displayed by default.
				$retval = array(
					'name',
				);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey($this->getOptions('default_display_columns'), Misc::trimSortPrefix($this->getOptions('columns')));
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
					'name',
				);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap($data)
	{
		$variable_function_map = array(
			'id' => 'ID',
			'company_id' => 'Company',
			'name' => 'Name',
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

	function isUniqueName($name)
	{
		$ph = array(
			':company_id' => $this->getCompany(),
			':name' => $name,
		);

		$query = 'select id from ' . $this->table . '
					where company_id = :company_id
						AND name = :name
						AND deleted = 0';

		$result = DB::select($query, $ph);
		$name_id = !empty($result) ? $result[0]->id : null;
		Debug::Arr($name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10);

		// if (empty($name_id) || $name_id === FALSE) {
		// 	return TRUE;
		// } else {
		// 	if ($name_id == $this->getId()) {
		// 		return TRUE;
		// 	}
		// }
		if ($name_id === NULL || $name_id == $this->getId()) { 
			return TRUE; 
		}
	

		return FALSE;
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
				('Name is too short or too long'),
				2,
				100
			)
			and
			$this->Validator->isTrue(
				'name',
				$this->isUniqueName($name),
				('Group already exists')
			)

		) {
			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function Validate()
	{
		Debug::Text('Validate...', __FILE__, __LINE__, __METHOD__, 10);
		// dd($this->getDeleted());
		if ($this->getDeleted() == TRUE) {
			//Check to make sure there are no hours using this OT policy.
			$uwlf = new UserWageListFactory();
			$uwlf->getByWageGroupIDAndCompanyId($this->getID(), $this->getCompany());
			if ($uwlf->getRecordCount() > 0) {
				$this->Validator->isTRUE(
					'in_use',
					FALSE,
					('This wage group is in use')
				);
			}
		}

		return TRUE;
	}

	function postSave()
	{
		return TRUE;
	}

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
		return TTLog::addEntry($this->getId(), $log_action, ('Wage Group'), NULL, $this->getTable(), $this);
	}
}
