<?php

namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\StationBranchFactory;
use App\Models\Core\StationFactory;
use App\Models\Core\TTi18n;
use App\Models\Core\UserDateTotalFactory;
use App\Models\Punch\PunchControlFactory;
use App\Models\Schedule\RecurringScheduleTemplateFactory;
use App\Models\Schedule\ScheduleFactory;
use App\Models\Users\UserDefaultFactory;
use App\Models\Users\UserFactory;
use App\Models\Core\TTLog;
use Illuminate\Support\Facades\DB;

class BranchFactory extends Factory
{
	protected $table = 'branch';
	protected $pk_sequence_name = 'branch_id_seq'; //PK Sequence name

	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-\.\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

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

					'-1140-address1' => ('Address 1'),
					'-1150-address2' => ('Address 2'),
					'-1160-city' => ('City'),
					'-1170-province' => ('Province/State'),
					'-1180-country' => ('Country'),
					'-1190-postal_code' => ('Postal Code'),
					'-1200-work_phone' => ('Work Phone'),
					'-1210-fax_phone' => ('Fax Phone'),

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
					'city',
					'province',
				);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
					'name',
					'manual_id'
				);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
					'country',
					'province',
					'postal_code'
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
			'status_id' => 'Status',
			'status' => FALSE,
			'manual_id' => 'ManualID',
			'name' => 'Name',
			'address1' => 'Address1',
			'address2' => 'Address2',
			'city' => 'City',
			'country' => 'Country',
			'province' => 'Province',
			'postal_code' => 'PostalCode',
			'work_phone' => 'WorkPhone',
			'fax_phone' => 'FaxPhone',
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
		if (isset($this->data['company_id'])) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
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
		if (isset($this->data['status_id'])) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
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
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}


		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if ($id === FALSE) {
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
		/*
		//old code
		if ( $company_id == '' ANd is_object($current_company) ) {
			$company_id = $current_company->getId();
		} elseif ( $company_id == '' AND isset($this) AND is_object($this) ) {
			$company_id = $this->getCompany();
		}*/

		if ($company_id == '' && is_object($current_company)) {
			$company_id = $current_company->getId();
		} elseif ($company_id == '' && method_exists(static::class, 'getCompany')) {
			$company_id = static::getCompany();
		}


		$blf = new BranchListFactory();
		$blf->getHighestManualIDByCompanyId($company_id);
		if ($blf->getRecordCount() > 0) {
			$next_available_manual_id = $blf->getCurrent()->getManualId() + 1;
		} else {
			$next_available_manual_id = 1;
		}

		return $next_available_manual_id;
	}

	function getManualID()
	{
		if (isset($this->data['manual_id'])) {
			return (int)$this->data['manual_id'];
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


	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE SHORT NAME OF THE BRANCH NAME
         */
	function getBranchShortID()
	{
		if (isset($this->data['branch_short_id'])) {
			return $this->data['branch_short_id'];
		}

		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE SHORT NAME OF THE BRANCH NAME
         */
	function setBranchShortID($value)
	{
		$value = trim($value);

		if (
			$value != NULL
			and
			$this->Validator->isLength('branch_short_id', $value, ('Branch Short ID is too short or too long'), 1, 100)
			and
			$this->Validator->isTrue('branch_short_id', $this->isUniqueBranchShortID($value), ('Branch Short ID is already in use, please enter a different one'))
		) {

			$this->data['branch_short_id'] = $value;

			return TRUE;
		}
		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE SHORT NAME OF THE BRANCH NAME
         */
	function isUniqueBranchShortID($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':branch_short_id' => $id,
			':company_id' => $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where branch_short_id = :branch_short_id AND company_id = :company_id AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}

		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if ($id === FALSE) {
			return TRUE;
		} else {
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}


	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE EPF NUMBER
         */
	function getEpfNo()
	{
		if (isset($this->data['epf_no'])) {
			return $this->data['epf_no'];
		}

		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE EPF NUMBER
         */
	// function setEpfNo($value)
	// {
	// 	$value = trim($value);
	// 	print_r($value);
	// 	exit;

	// 	if (
	// 		$value != NULL
	// 		and
	// 		$this->Validator->isLength('epf_no', $value, ('EPF No is too short or too long'), 1, 100)
	// 		and
	// 		$this->Validator->isTrue('epf_no', $this->isUniqueEpfNo($value), ('EPF No is already in use, please enter a different one'))
	// 	) {

	// 		$this->data['epf_no'] = $value;

	// 		return TRUE;
	// 	}
	// 	return FALSE;
	// }

	function setEpfNo($value)
	{
		$value = trim($value);
		// print_r($value); // This will print the value you are checking.
		// exit; // Exit to check the value.

		// First check: Is $value NULL?
		if ($value == NULL) {
			echo "Validation failed: EPF No is NULL.\n";
			return FALSE;
		}

		// Second check: Validate length (if length is too short or too long)
		if (!$this->Validator->isLength('epf_no', $value, 'EPF No is too short or too long', 1, 100)) {
			echo "Validation failed: EPF No length is invalid (too short or too long).\n";
			print_r("Validation failed: EPF No length is invalid (too short or too long).\n"); // This will print the value you are checking.
			exit;
			return FALSE;
		}

		// Third check: Check if EPF No is unique
		if (!$this->Validator->isTrue('epf_no', $this->isUniqueEpfNo($value), 'EPF No is already in use, please enter a different one')) {
			echo "Validation failed: EPF No is already in use.\n";
			print_r("Validation failed: EPF No is already in use.\n"); // This will print the value you are checking.
			exit;
			return FALSE;
		}

		// If all validations pass
		$this->data['epf_no'] = $value;
		return TRUE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE EPF NUMBER
         */
	function isUniqueEpfNo($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':epf_no' => $id,
			':company_id' => $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where epf_no = :epf_no AND company_id = :company_id AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if ($id === FALSE) {
			$id = 0;
		} else {
			// Ensure $id is an array before accessing index 0
			if (is_array($id) && isset($id[0])) {
				$id = current(get_object_vars($id[0]));
			} else {
				$id = 0; // Handle the case where $id is not an array or the index 0 doesn't exist
			}
		}
		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);
		
		// if ($id === FALSE) {
		// 	var_dump($id,'333');
		// 	return TRUE;
		// } else {var_dump($id,'00');
		// 	if ($id == $this->getId()) {
		// 		return TRUE;
		// 	}
		// }

		if (empty($id)) { // Check if $id is NULL, FALSE, 0, or an empty value
			var_dump($id, 'ID is not available');
			return TRUE;
		} else {
			var_dump($id, 'ID is available');
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}






	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE ETF NUMBER
         */
	function getEtfNo()
	{
		if (isset($this->data['etf_no'])) {
			return $this->data['etf_no'];
		}

		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE ETF NUMBER
         */
	function setEtfNo($value)
	{
		$value = trim($value);

		if (
			$value != NULL
			and
			$this->Validator->isLength('etf_no', $value, ('ETF No is too short or too long'), 1, 100)
			and
			$this->Validator->isTrue('etf_no', $this->isUniqueEtfNo($value), ('ETF No is already in use, please enter a different one'))
		) {

			$this->data['etf_no'] = $value;

			return TRUE;
		}
		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE ETF NUMBER
         */
	function isUniqueEtfNo($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':etf_no' => $id,
			':company_id' => $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where etf_no = :etf_no AND company_id = :company_id AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}

		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if ($id === FALSE) {
			return TRUE;
		} else {
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}





	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE TIN NUMBER
         */
	function getTinNo()
	{
		if (isset($this->data['tin_no'])) {
			return $this->data['tin_no'];
		}

		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE TIN NUMBER
         */
	function setTinNo($value)
	{
		$value = trim($value);

		if (
			$value != NULL
			and
			$this->Validator->isLength('tin_no', $value, ('TIN No is too short or too long'), 1, 100)
			and
			$this->Validator->isTrue('tin_no', $this->isUniqueTinNo($value), ('TIN No is already in use, please enter a different one'))
		) {

			$this->data['tin_no'] = $value;

			return TRUE;
		}
		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE TIN NUMBER
         */
	function isUniqueTinNo($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':tin_no' => $id,
			':company_id' => $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where tin_no = :tin_no AND company_id = :company_id AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}

		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if ($id === FALSE) {
			return TRUE;
		} else {
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}





	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE Business Registration NUMBER
         */
	function getBusinessRegNo()
	{
		if (isset($this->data['business_reg_no'])) {
			return $this->data['business_reg_no'];
		}

		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE Business Registration NUMBER
         */
	function setBusinessRegNo($value)
	{
		$value = trim($value);

		if (
			$value != NULL
			and
			$this->Validator->isLength('business_reg_no', $value, ('Business Registration No is too short or too long'), 1, 100)
			and
			$this->Validator->isTrue('business_reg_no', $this->isUniqueBusinessRegNo($value), ('Business Registration No is already in use, please enter a different one'))
		) {

			$this->data['business_reg_no'] = $value;

			return TRUE;
		}
		return FALSE;
	}

	/*
         * ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
         * THIS ID IS UNIQUE Business Registration NUMBER
         */
	function isUniqueBusinessRegNo($id)
	{
		if ($this->getCompany() == FALSE) {
			return FALSE;
		}

		$ph = array(
			':business_reg_no' => $id,
			':company_id' => $this->getCompany(),
		);

		$query = 'select id from ' . $this->getTable() . ' where business_reg_no = :business_reg_no AND company_id = :company_id AND deleted=0';
		// $id = $this->db->GetOne($query, $ph);
		$id = DB::select($query, $ph);

		if (empty($id)) {
			$id = 0;
		} else {
			$id = current(get_object_vars($id[0]));
		}

		Debug::Arr($id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10);

		if ($id === FALSE) {
			return TRUE;
		} else {
			if ($id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}







	function isUniqueName($name)
	{
		Debug::Arr($this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10);
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

		$query = 'select id from ' . $this->getTable() . '
					where company_id = :company_id
						AND name = :name
						AND deleted = 0';
		// $name_id = $this->db->GetOne($query, $ph);
		$name_id = DB::select($query, $ph);

		if (empty($name_id)) {
			$name_id = 0;
		} else {
			$name_id = current(get_object_vars($name_id[0]));
		}

		Debug::Arr($name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10);

		if ($name_id === FALSE) {
			return TRUE;
		} else {
			if ($name_id == $this->getId()) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName()
	{
		if (isset($this->data['name'])) {
			return $this->data['name'];
		}

		return FALSE;
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
				('Branch name already exists')
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

	function getAddress1()
	{
		if (isset($this->data['address1'])) {
			return $this->data['address1'];
		}

		return FALSE;
	}
	function setAddress1($address1)
	{
		$address1 = trim($address1);

		if (
			$address1 != NULL
			and
			($this->Validator->isRegEx(
				'address1',
				$address1,
				('Address1 contains invalid characters'),
				$this->address_validator_regex
			)
				and
				$this->Validator->isLength(
					'address1',
					$address1,
					('Address1 is too short or too long'),
					2,
					250
				))
		) {

			$this->data['address1'] = $address1;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress2()
	{
		if (isset($this->data['address2'])) {
			return $this->data['address2'];
		}

		return FALSE;
	}
	function setAddress2($address2)
	{
		$address2 = trim($address2);

		if (
			$address2 != NULL
			and (
				$this->Validator->isRegEx(
					'address2',
					$address2,
					('Address2 contains invalid characters'),
					$this->address_validator_regex
				)
				and
				$this->Validator->isLength(
					'address2',
					$address2,
					('Address2 is too short or too long'),
					2,
					250
				))
		) {

			$this->data['address2'] = $address2;

			return TRUE;
		}

		return FALSE;
	}

	function getCity()
	{
		if (isset($this->data['city'])) {
			return $this->data['city'];
		}

		return FALSE;
	}
	function setCity($city)
	{
		$city = trim($city);

		if (
			$this->Validator->isRegEx(
				'city',
				$city,
				('City contains invalid characters'),
				$this->city_validator_regex
			)
			and
			$this->Validator->isLength(
				'city',
				$city,
				('City name is too short or too long'),
				2,
				250
			)
		) {

			$this->data['city'] = $city;

			return TRUE;
		}

		return FALSE;
	}

	function getProvince()
	{
		if (isset($this->data['province'])) {
			return $this->data['province'];
		}

		return FALSE;
	}
	function setProvince($province)
	{
		$province = trim($province);

		Debug::Text('Country: ' . $this->getCountry() . ' Province: ' . $province, __FILE__, __LINE__, __METHOD__, 10);

		$cf = new CompanyFactory();

		$options_arr = $cf->getOptions('province');
		if (isset($options_arr[$this->getCountry()])) {
			$options = $options_arr[$this->getCountry()];
		} else {
			$options = array();
		}

		//If country isn't set yet, accept the value and re-validate on save.
		if (
			$this->getCountry() == FALSE
			or
			$this->Validator->inArrayKey(
				'province',
				$province,
				('Invalid Province'),
				$options
			)
		) {

			$this->data['province'] = $province;

			return TRUE;
		}

		return FALSE;
	}

	function getCountry()
	{
		if (isset($this->data['country'])) {
			return $this->data['country'];
		}

		return FALSE;
	}
	function setCountry($country)
	{
		$country = trim($country);

		$cf = new CompanyFactory();

		if ($this->Validator->inArrayKey(
			'country',
			$country,
			('Invalid Country'),
			$cf->getOptions('country')
		)) {

			$this->data['country'] = $country;

			return TRUE;
		}

		return FALSE;
	}

	function getPostalCode()
	{
		if (isset($this->data['postal_code'])) {
			return $this->data['postal_code'];
		}

		return FALSE;
	}
	function setPostalCode($postal_code)
	{
		$postal_code = strtoupper($this->Validator->stripSpaces($postal_code));

		if (
			$postal_code == ''
			or
			(
				$this->Validator->isPostalCode(
					'postal_code',
					$postal_code,
					('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
					$this->getCountry(),
					$this->getProvince()
				)
				and
				$this->Validator->isLength(
					'postal_code',
					$postal_code,
					('Postal/ZIP Code is too short or too long'),
					1,
					10
				)
			)
		) {

			$this->data['postal_code'] = $postal_code;

			return TRUE;
		}

		return FALSE;
	}

	function getLongitude()
	{
		if (isset($this->data['longitude'])) {
			return (float)$this->data['longitude'];
		}

		return FALSE;
	}
	function setLongitude($value)
	{
		$value = trim((float)$value);

		if (
			$value == 0
			or
			$this->Validator->isFloat(
				'longitude',
				$value,
				('Longitude is invalid')
			)
		) {
			$this->data['longitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLatitude()
	{
		if (isset($this->data['latitude'])) {
			return (float)$this->data['latitude'];
		}

		return FALSE;
	}
	function setLatitude($value)
	{
		$value = trim((float)$value);

		if (
			$value == 0
			or
			$this->Validator->isFloat(
				'latitude',
				$value,
				('Latitude is invalid')
			)
		) {
			$this->data['latitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhone()
	{
		if (isset($this->data['work_phone'])) {
			return $this->data['work_phone'];
		}

		return FALSE;
	}
	function setWorkPhone($work_phone)
	{
		$work_phone = trim($work_phone);

		if (
			$work_phone != NULL
			and $this->Validator->isPhoneNumber(
				'work_phone',
				$work_phone,
				('Work phone number is invalid')
			)
		) {

			$this->data['work_phone'] = $work_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getFaxPhone()
	{
		if (isset($this->data['fax_phone'])) {
			return $this->data['fax_phone'];
		}

		return FALSE;
	}
	function setFaxPhone($fax_phone)
	{
		$fax_phone = trim($fax_phone);

		if (
			$fax_phone != NULL
			and $this->Validator->isPhoneNumber(
				'fax_phone',
				$fax_phone,
				('Fax phone number is invalid')
			)
		) {

			$this->data['fax_phone'] = $fax_phone;

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
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID($this->getCompany(), 110, $this->getID());
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

	function Validate()
	{
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return TRUE;
	}

	function preSave()
	{
		if ($this->getStatus() == FALSE) {
			$this->setStatus(10);
		}

		if ($this->getManualID() == FALSE) {
			$this->setManualID(BranchListFactory::getNextAvailableManualId($this->getCompany()));
		}

		return TRUE;
	}
	function postSave()
	{
		$this->removeCache($this->getId());

		if ($this->getDeleted() == FALSE) {
			CompanyGenericTagMapFactory::setTags($this->getCompany(), 110, $this->getID(), $this->getTag());
		}

		if ($this->getDeleted() == TRUE) {
			Debug::Text('UnAssign Hours from Branch: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this branch.
			$pcf = new PunchControlFactory();
			$udtf = new UserDateTotalFactory();
			$uf = new UserFactory();
			$sf = new StationFactory();
			$sbf = new StationBranchFactory();
			$sf_b = new ScheduleFactory();
			$udf = new UserDefaultFactory();
			$rstf = new RecurringScheduleTemplateFactory();

			$query = 'update ' . $pcf->getTable() . ' set branch_id = 0 where branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $udtf->getTable() . ' set branch_id = 0 where branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $sf_b->getTable() . ' set branch_id = 0 where branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $uf->getTable() . ' set default_branch_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND default_branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $udf->getTable() . ' set default_branch_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND default_branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $sf->getTable() . ' set branch_id = 0 where company_id = ' . (int)$this->getCompany() . ' AND branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'delete from ' . $sbf->getTable() . ' where branch_id = ' . (int)$this->getId();
			DB::select($query);

			$query = 'update ' . $rstf->getTable() . ' set branch_id = 0 where branch_id = ' . (int)$this->getId();
			DB::select($query);

			//Job employee criteria
			$cgmlf = new CompanyGenericMapListFactory();
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID($this->getCompany(), 1010, $this->getID());
			if ($cgmlf->getRecordCount() > 0) {
				foreach ($cgmlf as $cgm_obj) {
					Debug::text('Deleting from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$cgm_obj->Delete();
				}
			}
		}

		return TRUE;
	}

	function getMapURL()
	{
		return Misc::getMapURL($this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry());
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
		return TTLog::addEntry($this->getId(), $log_action, ('Branch') . ': ' . $this->getName(), NULL, $this->getTable(), $this);
	}
}
