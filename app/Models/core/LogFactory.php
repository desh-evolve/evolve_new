<?php

namespace App\Models\Core;

use App\Models\Users\UserListFactory;

class LogFactory extends Factory {
	protected $table = 'system_log';
	protected $pk_sequence_name = 'system_log_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'action':
				$retval = array(
											10 => ('Add'),
											20 => ('Edit'),
											30 => ('Delete'),
											31 => ('Delete (F)'), //Full Delete
											40 => ('UnDelete'),
											100 => ('Login'),
											110 => ('Logout'),
											200 => ('Allow'),
											210 => ('Deny'),
											500 => ('Notice'),
											510 => ('Warning'),
											900 => ('Other')
									);
				break;
			case 'table_name':
				$retval = array(
											'authentication'					=> ('Authentication'),
											'company'							=> ('Company'),
											'branch'							=> ('Branch'),
											'department'						=> ('Department'),
											'currency'							=> ('Currency'),
											'accrual'							=> ('Accrual'),
											'authorizations'					=> ('Authorizations'),
											'request'							=> ('Request'),
											'holidays'							=> ('Holidays'),
											'bank_account'						=> ('Bank Account'),
											'roe'								=> ('Record of Employment'),
											'station'							=> ('Station'),
											'station_user_group'				=> ('Station Employee Group'),
											'station_branch'					=> ('Station Branch'),
											'station_department'				=> ('Station Department'),
											'station_include_user'				=> ('Station Include Employee'),
											'station_exclude_user'				=> ('Station Exclude Employee'),
											'station'							=> ('Station'),
											'punch'								=> ('Punch'),
											'punch_control'						=> ('Punch Control'),
											'schedule'							=> ('Schedule'),
											'other_field'						=> ('Other Field'),
											'system_setting'					=> ('System Setting'),
											'cron'								=> ('Maintenance Jobs'),
											'permission_control'				=> ('Permission Groups'),
											'permission_user'					=> ('Permission Employees'),
											'permission'						=> ('Permissions'),

											'policy_group'						=> ('Policy Group'),
											'policy_group_user'					=> ('Policy Group Employees'),
											'schedule_policy'					=> ('Schedule Policy'),
											'round_interval_policy'				=> ('Rounding Policy'),
											'meal_policy'						=> ('Meal Policy'),
											'break_policy'						=> ('Break Policy'),
											'accrual_policy'					=> ('Accrual Policy'),
											'accrual_policy_milestone'			=> ('Accrual Policy Milestone'),
											'over_time_policy'					=> ('Overtime Policy'),
											'premium_policy'					=> ('Premium Policy'),
											'premium_policy_branch'				=> ('Premium Policy Branch'),
											'premium_policy_department'			=> ('Premium Policy Department'),
											'premium_policy_job_group'			=> ('Premium Policy Job Group'),
											'premium_policy_job'				=> ('Premium Policy Job'),
											'premium_policy_job_item_group'		=> ('Premium Policy Task Group'),
											'premium_policy_job_item'			=> ('Premium Policy Task'),
											'absence_policy'					=> ('Absense Policy'),
											'exception_policy_control'			=> ('Exception Policy'),
											'exception_policy'					=> ('Exception Policy'),
											'holiday_policy'					=> ('Holiday Policy'),
											'holiday_policy_recurring_holiday'	=> ('Holiday Policy'),

											'pay_period'						=> ('Pay Period'),
											'pay_period_schedule'				=> ('Pay Period Schedule'),
											'pay_period_schedule_user'			=> ('Pay Period Schedule Employees'),
											'pay_period_time_sheet_verify'		=> ('TimeSheet Verify'),

											'pay_stub'							=> ('Pay Stub'),
											'pay_stub_amendment'				=> ('Pay Stub Amendment'),
											'pay_stub_entry_account'			=> ('Pay Stub Account'),
											'pay_stub_entry_account_link'		=> ('Pay Stub Account Linking'),

											'recurring_holiday'					=> ('Recurring Holiday'),
											'recurring_ps_amendment'			=> ('Recurring PS Amendment'),
											'recurring_schedule_control'		=> ('Recurring Schedule'),
											'recurring_schedule_user'			=> ('Recurring Schedule Employees'),
											'recurring_schedule_template_control' => ('Recurring Schedule Template'),
											'recurring_schedule_template'		=> ('Recurring Schedule Week'),

											'user_date_total'					=> ('Employee Hours'),
											'user_default'						=> ('New Hire Defaults'),
											'user_generic_data'					=> ('Employee Generic Data'),
											'user_preference'					=> ('Employee Preference'),
											'users'								=> ('Employee'),
											'company_deduction'					=> ('Tax / Deduction'),
											'company_deduction_pay_stub_entry_account' => ('Tax / Deduction PS Accounts'),
											'user_deduction'					=> ('Employee Deduction'),
											'user_title'						=> ('Employee Title'),
											'user_wage'							=> ('Employee Wage'),

											'hierarchy_control'					=> ('Hierarchy'),
											'hierarchy_object_type'				=> ('Hierarchy Object Type'),
											'hierarchy_user'					=> ('Hierarchy Subordinate'),
											'hierarchy_level'					=> ('Hierarchy Superior'),
											'hierarchy'							=> ('Hierarchy Tree'),

											'job'								=> ('Job'),
											'job_user_branch'					=> ('Job Branch'),
											'job_user_department'				=> ('Job Department'),
											'job_user_group'					=> ('Job Group'),
											'job_include_user'					=> ('Job Include Employee'),
											'job_exclude_user'					=> ('Job Exclude Employee'),
											'job_job_item_group'				=> ('Job Task Group'),
											'job_include_job_item'				=> ('Job Include Task'),
											'job_exclude_job_item'				=> ('Job Exclude Task'),
											'job_item'							=> ('Job Task'),
											'job_item_amendment'				=> ('Job Task Amendment'),
											'document'							=> ('Document'),
											'document_revision'					=> ('Document Revision'),
											'client'							=> ('Client'),
											'client_contact'					=> ('Client Contact'),
											'client_payment'					=> ('Client Payment'),
											'invoice'							=> ('Invoice'),
											'invoice_config'					=> ('Invoice Settings'),
											'invoice_transaction'				=> ('Invoice Transaction'),
											'product'							=> ('Product'),
											'product_price'						=> ('Product Price Bracket'),
											'product_tax_policy'				=> ('Product Tax Policy'),
											'tax_area_policy'					=> ('Invoice Tax Area Policy'),
											'tax_policy'						=> ('Invoice Tax Policy'),
											'transaction'						=> ('Invoice Transaction'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-first_name' => ('First Name'),
										'-1020-last_name' => ('Last Name'),
										'-1100-date' => ('Date'),
										'-1110-object' => ('Object'),
										'-1120-action' => ('Action'),
										'-1130-description' => ('Description'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'date',
								'object',
								'action',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
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
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'object_id' => 'Object',
										'table_name' => 'TableName',
										'object' => FALSE, //Actually the display table name.

										'action_id' => 'Action',
										'action' => FALSE,
										'description' => 'Description',
										'date' => 'Date',

										'details' => 'Details',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = new UserListFactory(); 
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getLink() {

		$link = FALSE;

		//Only show links on add/edit/allow actions.
		if ( !in_array( $this->getAction(), array(10,20,200) ) ) {
			return $link;
		}

		switch ( $this->getTableName() ) {
			case 'authentication':
				break;
			case 'company':
				$link = 'company/EditCompany.php?id='. $this->getObject();
				break;
			case 'branch':
				$link = 'branch/EditBranch.php?id='. $this->getObject();
				break;
			case 'department':
				$link = 'department/EditDepartment.php?id='. $this->getObject();
				break;
			case 'currency':
				$link = 'currency/EditCurrency.php?id='. $this->getObject();
				break;
			case 'accrual':
				//$link = 'currency/EditCurrency.php?id='. $this->getObject();
				break;
			case 'authorizations':
				break;
			case 'request':
				$link = 'request/ViewRequest.php?id='. $this->getObject();
				break;
			case 'permission_control':
				$link = 'permission/EditPermissionControl.php?id='. $this->getObject();
				break;
			case 'holidays':
				break;
			case 'bank_account':
				break;
			case 'roe':
				break;
			case 'station':
				$link = 'station/EditStation.php?id='. $this->getObject();
				break;
			case 'punch':
				break;
			case 'other_field':
				break;
			case 'system_setting':
				break;
			case 'cron':
				break;
			case 'policy_group':
				$link = 'policy/EditPolicyGroup.php?id='. $this->getObject();
				break;
			case 'schedule_policy':
				$link = 'policy/EditSchedulePolicy.php?id='. $this->getObject();
				break;
			case 'round_interval_policy':
				$link = 'policy/EditRoundIntervalPolicy.php?id='. $this->getObject();
				break;
			case 'meal_policy':
				$link = 'policy/EditMealPolicy.php?id='. $this->getObject();
				break;
			case 'accrual_policy':
				$link = 'policy/EditAccrualPolicy.php?id='. $this->getObject();
				break;
			case 'over_time_policy':
				$link = 'policy/EditOverTimePolicy.php?id='. $this->getObject();
				break;
			case 'premium_policy':
				$link = 'policy/EditPremiumTimePolicy.php?id='. $this->getObject();
				break;
			case 'absence_policy':
				$link = 'policy/EditAbsencePolicy.php?id='. $this->getObject();
				break;
			case 'exception_policy_control':
				$link = 'policy/EditExceptionControlPolicy.php?id='. $this->getObject();
				break;
			case 'holiday_policy':
				$link = 'policy/EditHolidayPolicy.php?id='. $this->getObject();
				break;
			case 'pay_period':
				$link = 'payperiod/ViewPayPeriod.php?pay_period_id='. $this->getObject();
				break;
			case 'pay_period_schedule':
				$link = 'payperiod/EditPayPeriodSchedule.php?id='. $this->getObject();
				break;
			case 'pay_period_time_sheet_verify':
				break;
			case 'pay_stub':
				break;
			case 'pay_stub_amendment':
				$link = 'pay_stub_amendment/EditPayStubAmendment.php?id='. $this->getObject();
				break;
			case 'pay_stub_entry_account':
				$link = 'pay_stub/EditPayStubEntryAccount.php?id='. $this->getObject();
				break;
			case 'pay_stub_entry_account_link':
				break;
			case 'recurring_holiday':
				$link = 'policy/EditRecurringHoliday.php?id='. $this->getObject();
				break;
			case 'recurring_ps_amendment':
				$link = 'pay_stub_amendment/EditRecurringPayStubAmendment.php?id='. $this->getObject();
				break;
			case 'recurring_schedule_control':
				$link = 'schedule/EditRecurringSchedule.php?id='. $this->getObject();
				break;
			case 'recurring_schedule_template_control':
				$link = 'schedule/EditRecurringScheduleTemplate.php?id='. $this->getObject();
				break;
			case 'user_date_total':
				break;
			case 'user_default':
				$link = 'users/EditUserDefault.php?id='. $this->getObject();
				break;
			case 'user_generic_data':
				break;
			case 'user_preference':
				$link = 'users/EditUserPreference.php?user_id='. $this->getObject();
				break;
			case 'users':
				$link = 'users/EditUser.php?id='. $this->getObject();
				break;
			case 'company_deduction':
				$link = 'company/EditCompanyDeduction.php?id='. $this->getObject();
				break;
			case 'user_deduction':
				$link = 'users/EditUserDeduction.php?id='. $this->getObject();
				break;
			case 'user_title':
				$link = 'users/EditUserTitle.php?id='. $this->getObject();
				break;
			case 'user_wage':
				$link = 'users/EditUserWage.php?id='. $this->getObject();
				break;
			case 'job':
				$link = 'job/EditJob.php?id='. $this->getObject();
				break;
			case 'job_item':
				$link = 'job_item/EditJobItem.php?id='. $this->getObject();
				break;
			case 'job_item_amendment':
				$link = 'job_item/EditJobItemAmendment.php?id='. $this->getObject();
				break;
			case 'document':
				$link = 'document/EditDocument.php?document_id='. $this->getObject();
				break;
			case 'document_revision':
				break;
			case 'client':
				$link = 'client/EditClient.php?client_id='. $this->getObject();
				break;
			case 'client_contact':
				$link = 'client/EditClientContact.php?id='. $this->getObject();
				break;
			case 'client_payment':
				$link = 'client/EditClientPayment.php?id='. $this->getObject();
				break;
			case 'invoice':
				$link = 'invoice/EditInvoice.php?id='. $this->getObject();
				break;
			case 'invoice_config':
				$link = 'invoice/EditInvoiceConfig.php';
				break;
			case 'invoice_transaction':
				$link = 'invoice/EditTransaction.php?id='. $this->getObject();
				break;
			case 'product':
				$link = 'product/EditProduct.php?id='. $this->getObject();
				break;
			case 'tax_area_policy':
				$link = 'invoice_policy/EditTaxAreaPolicy.php?id='. $this->getObject();
				break;
			case 'tax_policy':
				$link = 'invoice_policy/EditTaxPolicy.php?id='. $this->getObject();
				break;
		}

		if ( $link !== FALSE ) {
			$link = Environment::getBaseURL().$link;
		}

		return $link;
	}

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		//Allow NULL ids.
		if ( $id == '' OR $id == NULL ) {
			$id = 0;
		}

		$ulf = new UserListFactory();
		
		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															('User is invalid')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObject() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObject($id) {
		$id = trim($id);

		if (	$this->Validator->isNumeric(	'object',
												$id,
												('Object is invalid'))
			) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTableName() {
		if ( isset($this->data['table_name']) ) {
			return $this->data['table_name'];
		}

		return FALSE;
	}
	function setTableName($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'table',
												$text,
												('Table is invalid'),
												2,
												250)

			) {
			$this->data['table_name'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getAction() {
		return $this->data['action_id'];
	}
	function setAction($action) {
		$action = trim($action);

		//Use integer ID values instead.
		$key = Option::getByValue($action, $this->getOptions('action') );
		if ($key !== FALSE) {
			Debug::text('Text Action: '. $action .' Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
			$action = $key;
		}
		if ( $this->Validator->inArrayKey(	'action',
											$action,
											('Incorrect Action'),
											$this->getOptions('action')) ) {

			$this->data['action_id'] = $action;

			return FALSE;
		}

		return FALSE;
	}

	function getDescription() {
		return $this->data['description'];
	}
	function setDescription($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'description',
												$text,
												('Description is invalid'),
												2,
												2000)

			) {
			$this->data['description'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getDate() {
		if ( isset($this->data['date']) ) {
			return $this->data['date'];
		}

		return FALSE;
	}

	function setDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == '') {
			$epoch = TTDate::getTime();
		}

		if 	(	$this->Validator->isDate(		'date',
												$epoch,
												('Date is invalid')) ) {

			$this->data['date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function addEntry( $object_id, $action_id, $description = NULL, $user_id = NULL, $table = NULL) {
		if ($object_id == '' ) {
			return FALSE;
		}

		if ($action_id == '') {
			return FALSE;
		}

		if ( $user_id == '' ) {
			global $current_user;
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				$user_id = 0;
			}
		}

		if ( $table == '' ) {
			$table = $this->getTable();
		}

		$this->setObject( $object_id );
		$this->setAction( $action_id );
		$this->setTable( $table );
		$this->setUser( (int)$user_id );
		$this->setDescription( $description );

		if ( $this->isValid() === TRUE ) {
			$this->Save();

			return TRUE;
		}

		return FALSE;
	}

	function getDetails() {
		if ( getTTProductEdition() > 10 AND $this->isNew() == FALSE AND is_object( $this->getUserObject() ) ) {
			//Get class for this table
			Debug::Text( 'Table: '. $this->getTableName(), __FILE__, __LINE__, __METHOD__,10);
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'includes'. DIRECTORY_SEPARATOR .'TableMap.inc.php');
			if ( isset($global_table_map[$this->getTableName()]) ) {
				$table_class = $global_table_map[$this->getTableName()];
				$class = new $table_class;
				Debug::Text( 'Table Class: '. $table_class, __FILE__, __LINE__, __METHOD__,10);

				$ldlf = new LogDetailListFactory();
				$ldlf->getBySystemLogIdAndCompanyId( $this->getID(), $this->getUserObject()->getCompany() );
				if ( $ldlf->getRecordCount() > 0 ) {
					foreach( $ldlf as $ld_obj ) {
						$detail_row[] = array(
											'field' => $ld_obj->getField(),
											'display_field' => LogDetailDisplay::getDisplayField( $class, $ld_obj->getField() ),
											'old_value' => LogDetailDisplay::getDisplayOldValue( $class, $ld_obj->getField(), $ld_obj->getOldValue() ),
											'new_value' => LogDetailDisplay::getDisplayNewValue( $class, $ld_obj->getField(), $ld_obj->getNewValue() ),
											);
					}

					$detail_row = Sort::multiSort( $detail_row, 'display_field' ) ;
					//Debug::Arr( $detail_row, 'Detail Row: ', __FILE__, __LINE__, __METHOD__,10);

					return $detail_row;
				}
			}
		}

		Debug::Text('No Log Details... ID: '. $this->getID(), __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	//Don't allow remote API calls to set audit trail records.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			//$this->setCreatedAndUpdatedColumns( $data );

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
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'date':
							$data[$variable] = TTDate::getAPIDate('DATE+TIME', $this->getDate() );
							break;
						case 'object':
							$data[$variable] = Option::getByKey( $this->getTableName(), $this->getOptions( 'table_name' ) );
							break;
						case 'action':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'details':
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->getDetails();
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
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

	function preSave() {
		if ($this->getDate() === FALSE ) {
			$this->setDate();
		}

		return TRUE;
	}
}
?>
