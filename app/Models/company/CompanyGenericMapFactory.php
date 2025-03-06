<?php

namespace App\Models\Company;

use App\Models\Core\Debug;
use App\Models\Core\Factory;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Department\DepartmentListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use App\Models\Policy\AccrualPolicyListFactory;
use App\Models\Policy\BreakPolicyListFactory;
use App\Models\Policy\ExceptionPolicyListFactory;
use App\Models\Policy\HolidayPolicyListFactory;
use App\Models\Policy\MealPolicyListFactory;
use App\Models\Policy\OverTimePolicyFactory;
use App\Models\Policy\OverTimePolicyListFactory;
use App\Models\Policy\PolicyGroupListFactory;
use App\Models\Policy\PremiumPolicyListFactory;
use App\Models\Policy\RoundIntervalPolicyListFactory;
use App\Models\Users\UserGroupListFactory;
use App\Models\Users\UserListFactory;

class CompanyGenericMapFactory extends Factory {
	protected $table = 'company_generic_map';
	protected $pk_sequence_name = 'company_generic_map_id_seq'; //PK Sequence name

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
					//Policy Group mapping
					110 => 'policy_group_over_time_policy',
					120 => 'policy_group_premium_policy',
					130 => 'policy_group_round_interval_policy',
					140 => 'policy_group_accrual_policy',
					150 => 'policy_group_meal_policy',
					155 => 'schedule_policy_meal_policy', //Mapping meal policies to schedule policies.
					160 => 'policy_group_break_policy',
					165 => 'schedule_policy_break_policy', //Mapping break policies to schedule policies.
					170 => 'policy_group_absence_policy',
					180 => 'policy_group_holiday_policy',
					190 => 'policy_group_exception_policy',

/*
					//Station user mapping
					310 => 'station_branch',
					320 => 'station_department',
					330 => 'station_user_group',
					340 => 'station_include_user',
					350 => 'station_exclude_user',

					//Premium Policy mapping
					510 => 'premium_policy_branch',
					520 => 'premium_policy_department',
					530 => 'premium_policy_job',
					540 => 'premium_policy_job_group',
					550 => 'premium_policy_job_item',
					560 => 'premium_policy_job_item_group',
*/
					//Job user mapping
					1010 => 'job_user_branch',
					1020 => 'job_user_department',
					1030 => 'job_user_group',
					1040 => 'job_include_user',
					1050 => 'job_exclude_user',

					//Job task mapping
					1060 => 'job_job_item_group',
					1070 => 'job_include_job_item',
					1080 => 'job_exclude_job_item',

					//Invoice Payment Gateway mapping
					3010 => 'payment_gateway_credit_card_type',
					3020 => 'payment_gateway_bank_account_type',
				);
				break;
		}

		return $retval;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return $this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getObjectID() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObjectID($id) {
		$id = trim($id);

		$pglf = new PolicyGroupListFactory();

		if ( $this->Validator->isNumeric(	'object_id',
										$id,
										('Object ID is invalid')
										) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMapID() {
		if ( isset($this->data['map_id']) ) {
			return $this->data['map_id'];
		}

		return FALSE;
	}
	function setMapID($id) {
		$id = trim($id);

		$pglf = new PolicyGroupListFactory();

		if ( $this->Validator->isNumeric(	'map_id',
										$id,
										('Map ID is invalid')
										) ) {
			$this->data['map_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	static function setMapIDs( $company_id, $object_type_id, $object_id, $ids, $is_new = FALSE ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $object_type_id == '') {
			return FALSE;
		}

		if ( $object_id == '') {
			return FALSE;
		}

		//If IDs is defined as a blank value, and not an array, assume its a blank array and remove all mapped IDs.
		if ( $ids == '' ) {
			$ids = array();
			//return FALSE;
		}

		if ( !is_array($ids) AND is_numeric( $ids ) ) {
			$ids = array($ids);
		}

		Debug::Arr($ids, 'Object Type ID: '. $object_type_id .' Object ID: '. $object_id .' IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($ids) ) {
			if ( $is_new == FALSE ) {
				//If needed, delete mappings first.
				$cgmlf = new CompanyGenericMapListFactory();
				$cgmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );

				$tmp_ids = array();
				foreach ($cgmlf as $obj) {
					$id = $obj->getMapID();
					Debug::text('Object Type ID: '. $object_type_id .' Object ID: '. $obj->getObjectID() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete objects that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$cgmf = new CompanyGenericMapFactory();
					$cgmf->setCompany( $company_id );
					$cgmf->setObjectType( $object_type_id );
					$cgmf->setObjectID( $object_id );
					$cgmf->setMapId( $id );
					$cgmf->Save();
				}
			}

			return TRUE;
		}

		Debug::text('No objects to map.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
		return FALSE;
	}

	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;
	}

	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {
		return FALSE;
	}

	function addLog( $log_action ) {
		$retval = FALSE;
		if ( $this->getObjectType() > 0 ) {
			$description = ('Generic Object Mapping');
			switch( $this->getObjectType() ) {
				case 110:
				case 120:
				case 130:
				case 140:
				case 150:
				case 160:
				case 170:
				case 180:
				case 190:
					switch( $this->getObjectType() )  {
						case 110:
							$lf = new OverTimePolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Overtime Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 120:
							$lf = new PremiumPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Premium Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 130:
							$lf = new RoundIntervalPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Rounding Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 140:
							$lf = new AccrualPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Accrual Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 150:
							$lf = new MealPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Meal Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 160:
							$lf = new BreakPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Break Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 170:
							$lf = new AbsencePolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Absence Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 180:
							$lf = new HolidayPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Holiday Policy').': '. $lf->getCurrent()->getName();
							}
							break;
						case 190: //Not handled with generic mapping currently.
							$lf = new ExceptionPolicyListFactory();
							$lf->getById( $this->getMapId() );
							if ( $lf->getRecordCount() > 0 ) {
								$description = ('Exception Policy').': '. $lf->getCurrent()->getName();
							}
							break;
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'policy_group' );
					break;
				case 165:
					$lf = new BreakPolicyListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ('Break Policy').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'schedule_policy' );
					break;
				//Job user mapping
				case 1010: //'job_user_branch',
					$lf = new BranchListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ('Branch').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_branch' );
					break;
				case 1020: // => 'job_user_department',
					$lf = new DepartmentListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ('Department').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_department' );
					break;
				case 1030: // => 'job_user_group',
					$lf = new UserGroupListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ('Employee Group').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_user_group' );
					break;
				case 1040: // => 'job_include_user',
				case 1050: // => 'job_exclude_user',
					switch( $this->getObjectType() ) {
						case 1040:
							$table_name = 'job_include_user';
							$type = ('Include');
							break;
						case 1050:
							$table_name = 'job_exclude_user';
							$type = ('Exclude');
							break;
					}

					$lf = new UserListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = $type.' '. ('Employee').': '. $lf->getCurrent()->getFullName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );
					break;
				//Job task mapping
				case 1060: // => 'job_job_item_group',
					$lf = new JobItemGroupListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ('Task Group').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'job_job_item_group' );
					break;
				case 1070: // => 'job_include_job_item',
				case 1080: // => 'job_exclude_job_item',
					switch( $this->getObjectType() ) {
						case 1070:
							$table_name = 'job_include_job_item';
							$type = ('Include');
							break;
						case 1080:
							$table_name = 'job_exclude_job_item';
							$type = ('Exclude');
							break;
					}

					$lf = new JobItemListFactory();
					$lf->getById( $this->getMapId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = $type.' '. ('Task').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description .' Record Count: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );
					break;
				case 3010: // => 'payment_gateway_credit_card_type',
					$table_name = 'payment_gateway_credit_card_type';

					$cpf = new ClientPaymentFactory();
					$description = ('Credit Card Type').': '. Option::getByKey( $this->getMapId(), $cpf->getOptions('credit_card_type') );

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );

					break;
				case 3020: // => 'payment_gateway_bank_account_type',
					$table_name = 'payment_gateway_bank_account_type';

					$cpf = new ClientPaymentFactory();
					$description = ('Bank Account Type').': '. Option::getByKey( $this->getMapId(), $cpf->getOptions('bank_account_type') );

					Debug::text('Action: '. $log_action .' MapID: '. $this->getMapID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, $table_name );

					break;
					break;
			}
		}

		return $retval;
	}

}
?>
