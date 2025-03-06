<?php

namespace App\Models\Core;

class LogDetailFactory extends Factory {
	protected $table = 'system_log_detail';
	protected $pk_sequence_name = 'system_log_detail_id_seq'; //PK Sequence name

	function getSystemLog() {
		return $this->data['system_log_id'];
	}
	function setSystemLog($id) {
		$id = trim($id);

		//Allow NULL ids.
		if ( $id == '' OR $id == NULL ) {
			$id = 0;
		}

		$llf = new LogListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$llf->getByID($id),
															('System log is invalid')
															) ) {
			$this->data['system_log_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getField() {
		if ( isset($this->data['field']) ) {
			return $this->data['field'];
		}

		return FALSE;
	}
	function setField($value) {
		$value = trim($value);

		if (	$this->Validator->isString(		'field',
												$value,
												('Field is invalid'))
			) {
			$this->data['field'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getOldValue() {
		if ( isset($this->data['old_value']) ) {
			return $this->data['old_value'];
		}

		return FALSE;
	}
	function setOldValue($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'old_value',
												$text,
												('Old value is invalid'),
												0,
												1024)

			) {
			$this->data['old_value'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getNewValue() {
		if ( isset($this->data['new_value']) ) {
			return $this->data['new_value'];
		}

		return FALSE;
	}
	function setNewValue($text) {
		$text = trim($text);

		if (
				$this->Validator->isLength(		'new_value',
												$text,
												('New value is invalid'),
												0,
												1024)

			) {
			$this->data['new_value'] = $text;

			return TRUE;
		}

		return FALSE;
	}

/*
	function normalizeField( $obj, $field ) {
		$retval = str_replace('_id', '', $field);

		return $retval;
	}

	function getDisplayField( $obj ) {
		$class = get_class($obj);
		$field = $this->getField();

		if ( is_object( $obj ) ) {
			//Handle global fields
			$columns = array();
			$global_columns = array(
							'user_id' => ('Employee'),
							'currency_id' => ('Currency'),
							'branch_id' => ('Branch'),
							'department_id' => ('Department'),
							'group_id' => ('Group'),
							'job_id' => ('Job'),
							'job_item_id' => ('Task'),
							'exception_policy_control_id' => ('Exception Policy'),
							'accrual_policy_id' => ('Accrual Policy'),
							'absence_policy_id' => ('Absence Policy'),
							'round_interval_policy_id' => ('Rounding Policy'),
							'pay_stub_entry_account_id' => ('Pay Stub Account'),
							'wage_group_id' => ('Wage Group'),
							'company_deduction_id' => ('Tax / Deduction'),

							'client_id' => ('Client'),
							'product_id' => ('Product'),
							'invoice_district_id' => ('District'),
							'total_time' => ('Total Time'),

							'other_id1' => ('Other ID1'), //Need to find the proper names for these eventually.
							'other_id2' => ('Other ID2'),
							'other_id3' => ('Other ID3'),
							'other_id4' => ('Other ID4'),
							'other_id5' => ('Other ID5'),
							);

			//Handle class specific fields.
			switch ( $class ) {
				case 'StationFactory':
				case 'StationListFactory':
					$columns = array(
									'partial_push_frequency' => ('Partial Push Frequency'),
									'push_frequency' => ('Push Frequency'),
									'poll_frequency' => ('Poll Frequency'),
									'enable_auto_punch_status' => ('Enable Auto Punch Status'),
									);
					break;
				case 'MealPolicyFactory':
				case 'MealPolicyListFactory':
				case 'BreakPolicyFactory':
				case 'BreakPolicyListFactory':
					$columns = array(
									'start_window' => ('Start Window'),
									'window_length' => ('Window Length'),
									'minimum_punch_time' => ('Minimum Punch Time'),
									'maximum_punch_time' => ('Maximum Punch Time'),
									);
					break;
				case 'AccrualPolicyFactory':
				case 'AccrualPolicyListFactory':
					$columns = array(
									'enable_pay_stub_balance_display' => ('Display Balance on Pay Stub'),
									'minimum_time' => ('Minimum Time'),
									'maximum_time' => ('Maximum Time'),
									'apply_frequency_id' => ('Frequency'),
									'apply_frequency_month' => ('Frequency Month'),
									'apply_frequency_day_of_month' => ('Frequency Day of Month'),
									'apply_frequency_day_of_week' => ('Frequency Day of Week'),
									'apply_frequency_hire_date' => ('Frequency Hire Date'),
									'milestone_rollover_month' => ('Rollover Month'),
									'milestone_rollover_day_of_month' => ('Rollover Day of Month'),
									'milestone_rollover_hire_date' => ('Rollover Hire Date'),
									'minimum_employed_days' => ('Minimum Employed Days'),
									);
					break;
				case 'AccrualPolicyMilestoneFactory':
				case 'AccrualPolicyMilestoneListFactory':
					$columns = array(
									'length_of_service_days' => ('Length Of Service in Days'),
									);
					break;
				case 'PremiumPolicyFactory':
				case 'PremiumPolicyListFactory':
					$columns = array(
									'minimum_time' => ('Minimum Time'),
									'maximum_time' => ('Maximum Time'),
									'include_break_policy' => ('Include Break Policy in Calculation'),
									'include_meal_policy' => ('Include Meal Policy in Calculation'),
									'include_partial_punch' => ('Include Partial Punches'),
									'daily_trigger_time' => ('Active After Daily (Regular) Hours'),
									'weekly_trigger_time' => ('Active After Weekly (Regular) Hours'),
									'start_time' => ('Start Time'),
									'end_time' => ('End Time'),
									'start_date' => ('Start Date'),
									'end_date' => ('End Date'),
									'sun' => ('Effective Day: Sun'),
									'mon' => ('Effective Day: Mon'),
									'tue' => ('Effective Day: Tue'),
									'wed' => ('Effective Day: Wed'),
									'thu' => ('Effective Day: Thu'),
									'fri' => ('Effective Day: Fri'),
									'sat' => ('Effective Day: Sat'),
									'exclude_default_branch' => ('Exclude Default Branch'),
									'exclude_default_department' => ('Exclude Default Department'),
									'branch_selection_type_id' => ('Branch Selection Type'),
									'department_selection_type_id' => ('Department Selection Type'),
									'minimum_break_time' => ('Minimum Time Recognized As Break'),
									'maximum_no_break_time' => ('Maximum Time Without A Break'),
									);
					break;
				case 'HolidayPolicyFactory':
				case 'HolidayPolicyListFactory':
					$columns = array(
									'time' => ('Holiday Time'),
									'worked_scheduled_days' => ('Worked Before Type'),
									'minimum_worked_days' => ('Work Before Days'),
									'minimum_worked_period_days' => ('Work Before Limit Days'),

									'worked_after_scheduled_days' => ('Worked After Type'),
									'minimum_worked_after_days' => ('Work After Days'),
									'minimum_worked_after_period_days' => ('Work After Limit Days'),

									'minimum_time' => ('Minimum Time'),
									'maximum_time' => ('Maximum Time'),
									'average_time_days' => ('Days To Average Time Over'),
									'average_time_worked_days' => ('Worked Days Only'),

									'force_over_time_policy' => ('Always Apply Over Time Policy'),
									'include_over_time' => ('Include Over Time in Average'),
									'include_paid_absence_time' => ('Include Paid Absence Time in Average'),

									);
					break;
				case 'RecurringHolidayFactory':
				case 'RecurringHolidayListFactory':
					$columns = array(
									'month_int' => ('Month'),
									'day_of_month' => ('Day Of The Month'),
									'day_of_week' => ('Day Of Week'),
									'week_interval' => ('Week Interval'),
									'pivot_day_direction_id' => ('Pivot Day Direction'),
									'special_day' => ('Special Day'),
									'always_week_day_id' => ('Always On Week Day'),
									);
					break;
				case 'PayPeriodScheduleFactory':
				case 'PayPeriodScheduleListFactory':
					$columns = array(
									'annual_pay_periods' => ('Annual Pay Periods'),
									'start_day_of_week' => ('Pay Period Starts On'),
									'start_week_day_id' => ('Overtime Week'),
									'shift_assigned_day_id' => ('Assign Shifts To'),
									'maximum_shift_time' => ('Maximum Shift Time'),
									'new_day_trigger_time' => ('Minimum Time-Off Between Shifts'),
									'timesheet_verify_type_id' => ('TimeSheet Verification'),
									'timesheet_verify_before_end_date' => ('Verification Window Starts'),
									'timesheet_verify_before_transaction_date' => ('Verification Window Ends'),
									'anchor_date' => ('Initial Date'),
									'transaction_date' => ('Transaction Date'),
									'transaction_date_bd' => ('Transaction Always On Business Day'),
									'primary_day_of_month' => ('Primary Start Day'),
									'primary_transaction_day_of_month' => ('Primary Transaction Day'),
									'secondary_day_of_month' => ('Secondary Start Day'),
									'secondary_transaction_day_of_month' => ('Secondary Transaction Day'),
									);
					break;
				case 'RequestFactory':
				case 'RequestListFactory':
					$columns = array(
									'authorization_level' => ('Authorization Level'),
									);
					break;
				case 'PayStubAmendmentFactory':
				case 'PayStubAmendmentListFactory':
				case 'RecurringPayStubAmendmentFactory':
				case 'RecurringPayStubAmendmentListFactory':
					$columns = array(
									'percent_amount' => ('Percent'),
									'percent_amount_entry_name_id' => ('Percent Of'),
									);
					break;
				case 'RecurringScheduleControlFactory':
				case 'RecurringScheduleControlListFactory':
					$columns = array(
									'start_week' => ('Start Week'),
									);
					break;
				case 'RecurringScheduleTemplateControlFactory':
				case 'RecurringScheduleTemplateControlListFactory':
				case 'RecurringScheduleTemplateFactory':
				case 'RecurringScheduleTemplateListFactory':
					$columns = array(
									'recurring_schedule_template_control_id' => ('Recurring Schedule Template'),
									'mon' => ('Monday'),
									'tue' => ('Tuesday'),
									'wed' => ('Wednesday'),
									'thu' => ('Thursday'),
									'fri' => ('Friday'),
									'sat' => ('Saturday'),
									'sun' => ('Sunday'),
									);
					break;
				case 'UserFactory':
				case 'UserListFactory':
					$columns = array(
									'sex' => ('Sex'),
									'second_last_name' => ('Second Surname'),
									'password_updated_date' => ('Password Updated'),
									);
					break;
				case 'UserPreferenceFactory':
				case 'UserPreferenceListFactory':
					$columns = array(
									'language' => ('Language'),
									'date_format' => ('Date Format'),
									'time_format' => ('Time Format'),
									'time_zone' => ('TimeZone'),
									'time_unit_format' => ('Time Unit Format'),
									'start_week_day' => ('Start Weekday'),
									'enable_email_notification_exception' => ('Email Notification Exception'),
									'enable_email_notification_message' => ('Email Notification Message'),
									'enable_email_notification_home' => ('Email Notification Home'),
									);
					break;
				case 'CompanyDeductionFactory':
				case 'CompanyDeductionListFactory':
				case 'UserDeductionFactory':
				case 'UserDeductionListFactory':
					$columns = array(
									'minimum_length_of_service_unit_id' => ('Minimum Length Of Service Units'),
									'minimum_length_of_service_days' => ('Minimum Length Of Service Days'),
									'minimum_length_of_service' => ('Minimum Length Of Service'),
									'maximum_length_of_service_unit_id' => ('Maximum Length Of Service Units'),
									'maximum_length_of_service_days' => ('Maximum Length Of Service Days'),
									'maximum_length_of_service' => ('Maximum Length Of Service'),
									'calculation_order' => ('Calculation Order'),
									'include_account_amount_type_id' => ('Include PS Account Value'),
									'exclude_account_amount_type_id' => ('Exclude PS Account Value'),
									'user_value1' => ('Value 1'),
									'user_value2' => ('Value 2'),
									'user_value3' => ('Value 3'),
									'user_value4' => ('Value 4'),
									'user_value5' => ('Value 5'),
									'user_value5' => ('Value 6'),
									'user_value5' => ('Value 7'),
									'user_value5' => ('Value 8'),
									'user_value5' => ('Value 9'),
									'user_value5' => ('Value 10'),
									);
					break;
				case 'JobFactory':
				case 'JobListFactory':
					$columns = array(
									'supervisor_user_id' => ('Supervisor'),
									'default_item_id' => ('Default Task'),
									'user_branch_selection_type_id' => ('Branch Selection Type'),
									'user_department_selection_type_id' => ('Department Selection Type'),
									'user_group_selection_type_id' => ('Group Selection Type'),
									'job_item_group_selection_type_id' => ('Task Group Selection Type'),
									);
					break;
				case 'DocumentFactory':
				case 'DocumentListFactory':
				case 'DocumentRevisionFactory':
				case 'DocumentRevisionListFactory':
					$columns = array(
									'document_id' => ('Document'),
									'mime_type' => ('MIME Type'),
									'local_file_name' => ('Local File Name'),
									);
					break;
				case 'ClientPaymentFactory':
				case 'ClientPaymentListFactory':
					$columns = array(
									'cc_bank_phone' => ('Issuing Bank Phone Number'),
									'cc_expire' => ('Expiry Date'),
									'cc_name' => ('Card Holder Name'),
									'cc_number' => ('Credit Card Number'),
									'cc_check' => ('Security Code'),
									'bank_account' => ('Bank Account'),
									'bank_transit' => ('Bank Routing/Transit'),
									'bank_institution' => ('Bank Institution'),
									);
					break;
				case 'ProductFactory':
				case 'ProductListFactory':
					$columns = array(
									'group_id' => ('Group'),
									'minimum_purchase_quantity' => ('Minimum Purchase Quantity'),
									'maximum_purchase_quantity' => ('Maximum Purchase Quantity'),
									'price_locked' => ('Lock Price'),
									'description_locked' => ('Lock Description '),
									'dimension_unit_id' => ('Dimension Unit'),
									'weight_unit_id' => ('Weight Unit'),
									'origin_country' => ('Origin Country'),
									'tariff_code' => ('Tariff Code'),
									'customs_unit_value' => ('Customs Unit Value'),
									'unit_cost' => ('Unit Cost'),
									'unit_price_type_id' => ('Price Type'),
									'upc' => ('UPC'),
									);
				case 'InvoiceFactory':
				case 'InvoiceListFactory':
					$columns = array(
									'billing_contact_id' => ('Billing Contact'),
									'shipping_contact_id' => ('Shipping Contact'),
									'other_contact_id' => ('Other Contact'),
									'shipping_policy_id' => ('Shipping Policy'),
									);
					break;

			}

			$display_field = Option::getByKey($field, array_merge($global_columns, $columns) );

			//Try getting the column name from the class 'column' array.
			if ( $display_field == '' ) {
				$columns = Misc::trimSortPrefix( $obj->getOptions('columns') );
				$display_field = Option::getByKey($field, $columns );

				if ( $display_field == '' AND strpos( $field, '_id') ) {
					$tmp_field = $this->normalizeField( $obj, $field );
					$display_field = Option::getByKey($tmp_field, $columns );
				}
			}
			Debug::text('Converting: '. $field .' to Display for Class: '. $class .' Field: '. $field .' Retval: '. $display_field, __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $display_field == '' ) {
			$display_field = ucfirst($field);
		}

		return $display_field;
	}

	//Converts raw data from the database into something useful to the user.
	//ie: branch_id = 3 to "Westbank"
	function getDisplayValue( $obj, $value ) {
		$retval = $value;

		$class = get_class($obj);
		$field = $this->getField();

		switch ( $field ) {
			//Global variables, like branches,departments,titles,jobs,tasks,status_id,type_id etc...
			case 'branch_id':
			case 'default_branch_id':
				$lf = new BranchListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'department_id':
			case 'default_department_id':
				$lf = new DepartmentListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'title_id':
				$lf = new UserTitleListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'currency_id':
				$lf = new CurrencyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'group_id':
				$lf = new UserGroupListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'supervisor_user_id':
			case 'support_contact':
			case 'sales_contact':
			case 'sales_contact_id':
			case 'user_id':
				$lf = new UserListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getFullName();
				} else {
					$retval = ('--');
				}
				break;
			case 'accrual_policy_id':
				$lf = new AccrualPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'schedule_policy_id':
				$lf = new SchedulePolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'exception_policy_control_id':
				$lf = new ExceptionPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'meal_policy_id':
				$lf = new MealPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'break_policy_id':
				$lf = new BreakPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'absence_policy_id':
				$lf = new AbsencePolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'over_time_policy_id':
				$lf = new OverTimePolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'holiday_policy_id':
				$lf = new HolidayPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'round_interval_policy_id':
				$lf = new RoundIntervalPolicyListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'wage_group_id':
				$lf = new WageGroupListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'company_deduction_id':
				$lf = new CompanyDeductionListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'pay_stub_entry_account_id':
			case 'pay_stub_entry_name_id':
			case 'accrual_pay_stub_entry_account_id':
			case 'percent_amount_entry_name_id':
				$lf = new PayStubEntryAccountListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$type_options  = $lf->getOptions('type');
					$retval = $type_options[$lf->getCurrent()->getType()] .' - '. $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;
			case 'recurring_schedule_template_control_id':
				$lf = new RecurringScheduleTemplateControlListFactory();
				$lf->getById( $value );
				if ( $lf->getRecordCount() > 0 ) {
					$retval = $lf->getCurrent()->getName();
				} else {
					$retval = ('--');
				}
				break;

			//
			//Start Professional Edition tables
			//
			case 'job_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new JobListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'default_item_id':
			case 'job_item_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new JobItemListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'client_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new ClientListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getCompanyName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'billing_contact_id':
			case 'shipping_contact_id':
			case 'other_contact_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new ClientContactListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getFullName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'shipping_policy_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new ShippingPolicyListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'product_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new ProductListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'document_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new DocumentListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'invoice_district_id':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$lf = new InvoiceDistrictListFactory();
					$lf->getById( $value );
					if ( $lf->getRecordCount() > 0 ) {
						$retval = $lf->getCurrent()->getName();
					} else {
						$retval = ('--');
					}
				}
				break;
			case 'invoice_date':
			case 'order_date':
			case 'shipped_date':
			case 'required_date':
				if ( $value != '' ) {
					$retval = TTDate::getDate('DATE', $value );
				} else {
					$retval = NULL;
				}
				break;
			//
			//End Professional Edition tables
			//

			case 'status_id':
			case 'type_id':
				$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
				$retval = Option::getByKey($value, $options );
				break;
			case 'origin_country':
			case 'country':
				$cf = new CompanyFactory();
				$retval = Option::getByKey($value, $cf->getOptions( 'country') );
				break;
			case 'province': //Cant do this one, as it requires the country value too.
				break;
			case 'time_zone':
				$upf = new UserPreferenceFactory();
				$retval = Option::getByKey($value, Misc::TrimSortPrefix( $upf->getOptions( $field ) ) );
				break;
			case 'time_stamp':
			case 'start_time':
			case 'end_time':
				$retval = TTDate::getDate('DATE+TIME', $value );
				break;
			case 'date_stamp':
			case 'start_date':
			case 'end_date':
			case 'transaction_date':
				if ( $value != '' ) {
					$retval = TTDate::getDate('DATE', $value );
				} else {
					$retval = NULL;
				}
				break;
			case 'total_time':
				$retval = TTDate::getTimeUnit( $value );
				break;
			case 'authorized':
				$retval = Misc::HumanBoolean( $value );
				break;

		}

		//Handle class specific fields.
		switch ( $class ) {
			case 'UserFactory':
			case 'UserListFactory':
				switch ( $field ) {
					case 'sin':
						$retval = $obj->getSecureSIN( $value );
						break;
					case 'hire_date':
					case 'birth_date':
					case 'termination_date':
					case 'password_updated_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'sex_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
				}
				break;
			case 'CurrencyFactory':
			case 'CurrencyListFactory':
				switch ( $field ) {
					case 'actual_rate_updated_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'is_default':
					case 'is_base':
					case 'auto_update':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'AccrualFactory':
			case 'AccrualListFactory':
				switch ( $field ) {
					case 'amount':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'StationFactory':
			case 'StationListFactory':
				switch ( $field ) {
					case 'branch_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('branch_selection_type') );
						break;
					case 'department_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('department_selection_type') );
						break;
					case 'group_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('group_selection_type') );
						break;
					case 'partial_push_frequency':
						$retval = Option::getByKey($value, $obj->getOptions('partial_push_frequency') );
						break;
					case 'push_frequency':
						$retval = Option::getByKey($value, $obj->getOptions('push_frequency') );
						break;
					case 'poll_frequency':
						$retval = Option::getByKey($value, $obj->getOptions('poll_frequency') );
						break;
					case 'enable_auto_punch_status':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'SchedulePolicyFactory':
			case 'SchedulePolicyListFactory':
				switch ( $field ) {
					case 'start_stop_window':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'RoundIntervalPolicyFactory':
			case 'RoundIntervalPolicyListFactory':
				switch ( $field ) {
					case 'punch_type_id':
					case 'round_type_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'grace':
					case 'round_interval':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'strict':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'MealPolicyFactory':
			case 'MealPolicyListFactory':
			case 'BreakPolicyFactory':
			case 'BreakPolicyListFactory':
				switch ( $field ) {
					case 'auto_detect_type_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'start_window':
					case 'window_length':
					case 'minimum_punch_time':
					case 'maximum_punch_time':
					case 'trigger_time':
					case 'amount':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'include_lunch_punch_time':
					case 'include_break_punch_time':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'AccrualPolicyFactory':
			case 'AccrualPolicyListFactory':
				switch ( $field ) {
					case 'apply_frequency_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'apply_frequency_month':
					case 'milestone_rollover_month':
						$options = TTDate::getMonthOfYearArray();
						$retval = Option::getByKey($value, $options );
						break;
					case 'apply_frequency_day_of_week':
						$options = TTDate::getDayOfWeekArray();
						$retval = Option::getByKey($value, $options );
						break;
					case 'enable_pay_stub_balance_display':
					case 'milestone_rollover_hire_date':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'AccrualPolicyMilestoneFactory':
			case 'AccrualPolicyMilestoneListFactory':
				switch ( $field ) {
					case 'length_of_service_unit_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'maximum_time':
					case 'accrual_rate':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'OverTimePolicyFactory':
			case 'OverTimePolicyListFactory':
				switch ( $field ) {
					case 'trigger_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'PremiumPolicyFactory':
			case 'PremiumPolicyListFactory':
				switch ( $field ) {
					case 'pay_type_id':
					case 'branch_selection_type_id':
					case 'department_selection_type_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'daily_trigger_time':
					case 'weekly_trigger_time':
					case 'minimum_time':
					case 'maximum_time':
					case 'minimum_break_time':
					case 'maximum_no_break_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'start_time':
					case 'end_time':
						$retval = TTDate::getDate('TIME', $value );
						break;
					case 'sun':
					case 'mon':
					case 'tue':
					case 'wed':
					case 'thu':
					case 'fri':
					case 'sat':
					case 'include_break_policy':
					case 'include_meal_policy':
					case 'include_partial_punch':
					case 'exclude_default_branch':
					case 'exclude_default_department':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'HolidayPolicyFactory':
			case 'HolidayPolicyListFactory':
				switch ( $field ) {
					case 'worked_scheduled_days':
					case 'worked_after_scheduled_days':
						$options = $obj->getOptions( 'scheduled_day' );
						$retval = Option::getByKey($value, $options );
						break;
					case 'default_schedule_status_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'time':
					case 'minimum_time':
					case 'maximum_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'include_paid_absence_time':
					case 'include_over_time':
					case 'force_over_time_policy':
					case 'average_time_worked_days':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'RecurringHolidayFactory':
			case 'RecurringHolidayListFactory':
				switch ( $field ) {
					case 'special_day':
					case 'week_interval':
					case 'always_week_day_id':
					case 'pivot_day_direction_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'day_of_week':
						$retval = Option::getByKey($value, TTDate::getDayOfWeekArray() );
						break;
					case 'month_int':
						$retval = Option::getByKey($value, TTDate::getMonthOfYearArray() );
						break;
				}
				break;

			case 'ExceptionPolicyFactory':
			case 'ExceptionPolicyListFactory':
				switch ( $field ) {
					case 'email_notification_id':
					case 'severity_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'grace':
					case 'watch_window':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'active':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'PayPeriodScheduleFactory':
			case 'PayPeriodScheduleListFactory':
				switch ( $field ) {
					case 'shift_assigned_day_id':
					case 'start_week_day_id':
					case 'timesheet_verify_type_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'transaction_date_bd':
						$options = $obj->getOptions( 'transaction_date_business_day' );
						$retval = Option::getByKey($value, $options );
						break;
					case 'start_day_of_week':
						$retval = Option::getByKey($value, TTDate::getDayOfWeekArray()  );
						break;
					case 'timesheet_verify_before_end_date':
					case 'timesheet_verify_before_transaction_date':
						$retval = Misc::removeTrailingZeros( TTDate::getDays( $value ), 0 );
						break;
					case 'maximum_shift_time':
					case 'new_day_trigger_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'anchor_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'active':
						$retval = Misc::HumanBoolean( $value );
						break;
					case 'transaction_date': //Not an actual date, but how long after PP ends.
						$retval = $value;
						break;
				}
				break;
			case 'PayStubAmendmentFactory':
			case 'PayStubAmendmentListFactory':
			case 'RecurringPayStubAmendmentFactory':
			case 'RecurringPayStubAmendmentListFactory':
				switch ( $field ) {
					case 'effective_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'ytd_adjustment':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'RecurringPayStubAmendmentFactory':
			case 'RecurringPayStubAmendmentListFactory':
				switch ( $field ) {
					case 'frequency_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;
				}
				break;
			case 'RecurringScheduleControlFactory':
			case 'RecurringScheduleControlListFactory':
				switch ( $field ) {
					case 'auto_fill':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'RecurringScheduleTemplateControlFactory':
			case 'RecurringScheduleTemplateControlListFactory':
			case 'RecurringScheduleTemplateFactory':
			case 'RecurringScheduleTemplateListFactory':
				switch ( $field ) {
					case 'sun':
					case 'mon':
					case 'tue':
					case 'wed':
					case 'thu':
					case 'fri':
					case 'sat':
					case 'include_break_policy':
					case 'include_meal_policy':
					case 'include_partial_punch':
					case 'exclude_default_branch':
					case 'exclude_default_department':
						$retval = Misc::HumanBoolean( $value );
						break;
					case 'start_time':
					case 'end_time':
						$retval = TTDate::getDate('TIME', $value );
						break;
				}
				break;
			case 'UserPreferenceFactory':
			case 'UserPreferenceListFactory':
				switch ( $field ) {
					case 'start_week_day':
					case 'time_unit_format':
					case 'time_format':
					case 'date_format':
						$options = Misc::trimSortPrefix( $obj->getOptions( $this->normalizeField( $obj, $field) ) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'enable_email_notification_home':
					case 'enable_email_notification_message':
					case 'enable_email_notification_exception':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'UserWageFactory':
			case 'UserWageListFactory':
				switch ( $field ) {
					case 'effective_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'weekly_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'CompanyDeductionFactory':
			case 'CompanyDeductionListFactory':
				switch ( $field ) {
					case 'calculation_id':
						$options = Misc::trimSortPrefix( $obj->getOptions( $this->normalizeField( $obj, $field) ) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'include_account_amount_type_id':
					case 'exclude_account_amount_type_id':
						$options = Misc::trimSortPrefix( $obj->getOptions( 'account_amount_type' ) );
						$retval = Option::getByKey($value, $options );
						break;
					case 'minimum_length_of_service_unit_id':
					case 'maximum_length_of_service_unit_id':
						$options = Misc::trimSortPrefix( $obj->getOptions( 'length_of_service_unit' ) );
						$retval = Option::getByKey($value, $options );
						break;
				}
				break;
			case 'JobFactory':
			case 'JobListFactory':
				switch ( $field ) {
					case 'group_id':
						$lf = new JobGroupListFactory();
						$lf->getById( $value );
						if ( $lf->getRecordCount() > 0 ) {
							$retval = $lf->getCurrent()->getName();
						} else {
							$retval = ('--');
						}
						break;
					case 'user_branch_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('user_branch_selection_type') );
						break;
					case 'user_department_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('user_department_selection_type') );
						break;
					case 'user_group_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('user_group_selection_type') );
						break;
					case 'job_item_group_selection_type_id':
						$retval = Option::getByKey($value, $obj->getOptions('job_item_group_selection_type') );
						break;
					case 'estimate_time':
					case 'minimum_time':
						$retval = TTDate::getTimeUnit( $value );
						break;

				}
				break;
			case 'JobItemFactory':
			case 'JobItemListFactory':
				switch ( $field ) {
					case 'group_id':
						$lf = new JobItemGroupListFactory();
						$lf->getById( $value );
						if ( $lf->getRecordCount() > 0 ) {
							$retval = $lf->getCurrent()->getName();
						} else {
							$retval = ('--');
						}
						break;
					case 'estimate_time':
					case 'minimum_time':
						$retval = TTDate::getTimeUnit( $value );
						break;
				}
				break;
			case 'DocumentFactory':
			case 'DocumentListFactory':
				switch ( $field ) {
					case 'group_id':
						$lf = new DocumentGroupListFactory();
						$lf->getById( $value );
						if ( $lf->getRecordCount() > 0 ) {
							$retval = $lf->getCurrent()->getName();
						} else {
							$retval = ('--');
						}
						break;
					case 'template':
					case 'private':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'ClientFactory':
			case 'ClientListFactory':
				switch ( $field ) {
					case 'group_id':
						$lf = new ClientGroupListFactory();
						$lf->getById( $value );
						if ( $lf->getRecordCount() > 0 ) {
							$retval = $lf->getCurrent()->getName();
						} else {
							$retval = ('--');
						}
						break;
				}
				break;
			case 'ClientContactFactory':
			case 'ClientContactListFactory':
				switch ( $field ) {
					case 'is_default':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'ProductFactory':
			case 'ProductListFactory':
				switch ( $field ) {
					case 'group_id':
						$lf = new ProductGroupListFactory();
						$lf->getById( $value );
						if ( $lf->getRecordCount() > 0 ) {
							$retval = $lf->getCurrent()->getName();
						} else {
							$retval = ('--');
						}
						break;

					case 'unit_price_type_id':
					case 'weight_unit_id':
					case 'dimension_unit_id':
						$options = $obj->getOptions( $this->normalizeField( $obj, $field) );
						$retval = Option::getByKey($value, $options );
						break;

					case 'price_locked':
					case 'description_locked':
						$retval = Misc::HumanBoolean( $value );
						break;
				}
				break;
			case 'TransactionFactory':
			case 'TransactionListFactory':
				switch ( $field ) {
					case 'effective_date':
						$retval = TTDate::getDate('DATE', $value );
						break;
				}
				break;
		}

		Debug::text('  Converting: '. $value .' to Display Value for Class: '. $class .' Field: '. $field .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getDisplayOldValue( $obj ) {
		return $this->getDisplayValue( $obj, $this->getOldValue() );
	}
	function getDisplayNewValue( $obj ) {
		return $this->getDisplayValue( $obj, $this->getNewValue() );
	}
*/
	//When comparing the two arrays, if there are sub-arrays, we need to *always* include those, as we can't actually
	//diff the two, because they are already saved by the time we get to this function, so there will never be any changes to them.
	//We don't want to include sub-arrays, as the sub-classes should handle the logging themselves.
	function diffData( $arr1, $arr2 ) {
		if ( !is_array($arr1) OR !is_array($arr2) ) {
			return FALSE;
		}

		$retarr = FALSE;
		foreach( $arr1 as $key => $val ) {
			if ( !isset($arr2[$key]) OR is_array($val) OR is_array($arr2[$key]) OR ( $arr2[$key] != $val ) ) {
				$retarr[$key] = $val;
			}
		}

		return $retarr;
	}

	function addLogDetail( $action_id, $system_log_id, $object ) {
		$start_time = microtime(TRUE);

		//Only log detail records on add,edit,delete,undelete
		//Logging data on Add/Delete/UnDelete, or anything but Edit will greatly bloat the database, on the order of tens of thousands of entries
		//per day. The issue though is its nice to know exactly what data was originally added, then what was edited, and what was finally deleted.
		//We may need to remove logging for added data, but leave it for edit/delete, so we know exactly what data was deleted.
		if ( !in_array($action_id, array(10,20,30,31,40) ) ) {
			Debug::text('Invalid Action ID: '. $action_id, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $system_log_id > 0 AND is_object($object) ) {
			$class = get_class( $object );
			Debug::text('System Log ID: '. $system_log_id .' Class: '. $class, __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($object->data, 'Object Data: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($object->old_data, 'Object Old Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//Only store raw data changes, don't convert *_ID fields to full text names, it bloats the storage and slows down the logging process too much.
			//We can do the conversion when someone actually looks at the audit logs, which will obviously be quite rare in comparison. Even though this will
			//require quite a bit more code to handle.
			//There are also translation issues if we convert IDs to text at this point. However there could be continuity problems if ID values change in the future.
			$new_data = $object->data;
			//Debug::Arr($new_data, 'New Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( $action_id == 20 ) { //Edit
				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					$tmp_class = new $class;
					$tmp_class->setObjectFromArray( $object->old_data );
					$old_data = $tmp_class->data;
					unset($tmp_class);
				} else {
					$old_data = $object->old_data;
				}

				//We don't want to include any sub-arrays, as those classes should take care of their own logging, even though it may be slower in some cases.
				$diff_arr = array_diff_assoc( (array)$new_data, (array)$old_data );
			} elseif ( $action_id == 30 ) { //Delete
				$old_data = array();
				if ( method_exists( $object, 'setObjectFromArray' ) ) {
					//Run the old data back through the objects own setObjectFromArray(), so any necessary values can be parsed.
					$tmp_class = new $class;
					$tmp_class->setObjectFromArray( $object->data );
					$diff_arr = $tmp_class->data;
					unset($tmp_class);
				} else {
					$diff_arr = $object->data;
				}
			} else { //Add
				//Debug::text('Not editing, skipping the diff process...', __FILE__, __LINE__, __METHOD__, 10);
				//No need to store data that is added, as its already in the database, and if it gets changed or deleted we store it then.
				$old_data = array();
				$diff_arr = $object->data;
			}
			//Debug::Arr($old_data, 'Old Data Arr: ', __FILE__, __LINE__, __METHOD__, 10);

			//Handle class specific fields.
			switch ( $class ) {
				case 'UserFactory':
				case 'UserListFactory':
					unset(
							$diff_arr['labor_standard_industry'],
							$diff_arr['password'],
							$diff_arr['phone_password'],
							$diff_arr['password_reset_key'],
							$diff_arr['password_updated_date'],
							$diff_arr['full_name'],
							$diff_arr['first_name_metaphone'],
							$diff_arr['last_name_metaphone'],
							$diff_arr['finger_print_1'],
							$diff_arr['finger_print_2'],
							$diff_arr['finger_print_3'],
							$diff_arr['finger_print_4'],
							$diff_arr['finger_print_1_updated_date'],
							$diff_arr['finger_print_2_updated_date'],
							$diff_arr['finger_print_3_updated_date'],
							$diff_arr['finger_print_4_updated_date']
							);
					break;
				case 'PayPeriodScheduleFactory':
				case 'PayPeriodScheduleListFactory':
					unset(
							$diff_arr['primary_date_ldom'],
							$diff_arr['primary_transaction_date_ldom'],
							$diff_arr['primary_transaction_date_bd'],
							$diff_arr['secondary_date_ldom'],
							$diff_arr['secondary_transaction_date_ldom'],
							$diff_arr['secondary_transaction_date_bd']
							);
					break;
				case 'StationFactory':
				case 'StationListFactory':
					unset(
							$diff_arr['last_poll_date'],
							$diff_arr['last_push_date'],
							$diff_arr['last_punch_time_stamp'],
							$diff_arr['last_partial_push_date'],
							$diff_arr['mode_flag'], //This is changed often for some reason, would be nice to audit it though.
							$diff_arr['work_code_definition'],
							$diff_arr['allowed_date']
						);
					break;
				case 'PunchFactory':
				case 'PunchListFactory':
					unset(
							$diff_arr['actual_time_stamp'],
							$diff_arr['original_time_stamp'],
							$diff_arr['punch_control_id'],
							$diff_arr['station_id'],
							$diff_arr['latitude'],
							$diff_arr['longitude']
							);
					break;
				case 'PunchControlFactory':
				case 'PunchControlListFactory':
					unset(
							//$diff_arr['user_date_id'],
							$diff_arr['actual_total_time']
							);
					break;
				case 'UserDateTotalFactory':
				case 'UserDateTotalListFactory':
					break;
				case 'AccrualFactory':
				case 'AccrualListFactory':
					unset(
							$diff_arr['user_date_total_id']
							);
					break;
				case 'ClientContactFactory':
				case 'ClientContactListFactory':
					unset(
							$diff_arr['password']
							);
					break;
				case 'ClientPaymentFactory':
				case 'ClientPaymentListFactory':
					if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
						//Only log secure values.
						if ( isset($diff_arr['cc_number']) ) {
							$old_data['cc_number'] = ( isset($old_data['cc_number']) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $old_data['cc_number'] ) ) : '';
							$new_data['cc_number'] = ( isset($new_data['cc_number']) ) ? $object->getSecureCreditCardNumber( Misc::decrypt( $new_data['cc_number'] ) ) : '';
						}

						if ( isset($diff_arr['bank_account']) ) {
							$old_data['bank_account'] = ( isset($old_data['bank_account']) ) ? $object->getSecureAccount( $old_data['bank_account'] ) : '';
							$new_data['bank_account'] = ( isset($old_data['bank_account']) ) ? $object->getSecureAccount( $new_data['bank_account'] ) : '';
						}

						if ( isset($diff_arr['cc_check']) ) {
							$old_data['cc_check'] = ( isset($old_data['cc_check']) ) ? $object->getSecureCreditCardCheck( $old_data['cc_check'] ) : '';
							$new_data['cc_check'] = ( isset($old_data['cc_check']) ) ? $object->getSecureCreditCardCheck( $new_data['cc_check'] ) : '';
						}
					}
					break;
			}

			//Ignore specific columns here, like updated_date, updated_by, etc...
			unset(
					//These fields should never change, and therefore don't need to be recorded.
					$diff_arr['id'],
					$diff_arr['company_id'],

					$diff_arr['user_date_id'], //UserDateTotal, Schedule, PunchControl, etc...

					//General fields to skip
					$diff_arr['created_date'],
					$diff_arr['created_by'],
					$diff_arr['created_by_id'],
					$diff_arr['updated_date'],
					$diff_arr['updated_by'],
					$diff_arr['updated_by_id'],
					$diff_arr['deleted_date'],
					$diff_arr['deleted_by'],
					$diff_arr['deleted_by_id'],
					$diff_arr['deleted']
					);
			//Debug::Arr($diff_arr, 'Array Diff: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array($diff_arr) AND count($diff_arr) > 0 ) {
				foreach( $diff_arr as $field => $value ) {

					$old_value = NULL;
					if ( isset($old_data[$field]) ) {
						$old_value = $old_data[$field];
						if ( is_bool($old_value) AND $old_value === FALSE ) {
							$old_value = NULL;
						} elseif ( is_array($old_value) ) {
							//$old_value = serialize($old_value);
							//If the old value is an array, replace it with NULL because it will always match the NEW value too.
							$old_value = NULL;
						}
					}

					$new_value = $new_data[$field];
					if ( is_bool($new_value) AND $new_value === FALSE ) {
						$new_value = NULL;
					} elseif ( is_array($new_value) ) {
						$new_value = serialize($new_value);
					}

					//Debug::Text('Old Value: '. $old_value .' New Value: '. $new_value, __FILE__, __LINE__, __METHOD__, 10);
					if ( !($old_value == '' AND $new_value == '') ) {
						$ph[] = (int)$system_log_id;
						$ph[] = $field;
						$ph[] = $new_value;
						$ph[] = $old_value;
						$data[] = '(?,?,?,?)';
					}
				}

				if ( isset($data) ) {
					//Save data in a single SQL query.
					$query = 'INSERT INTO '. $this->getTable() .'(SYSTEM_LOG_ID,FIELD,NEW_VALUE,OLD_VALUE) VALUES'. implode(',', $data );
					//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
					$this->db->Execute($query, $ph);

					Debug::Text('Logged detail records in: '. (microtime(TRUE)-$start_time), __FILE__, __LINE__, __METHOD__, 10);

					return TRUE;
				}
			}
		}

		Debug::Text('Not logging detail records, likely no data changed in: '. (microtime(TRUE)-$start_time) .'s', __FILE__, __LINE__, __METHOD__, 10);
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

	function preSave() {
		if ($this->getDate() === FALSE ) {
			$this->setDate();
		}

		return TRUE;
	}
}
?>
