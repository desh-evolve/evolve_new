<?php

namespace App\Models\Report;

class Form941Report extends Report {

	protected $user_ids = array();

	function __construct() {
		$this->title = TTi18n::getText('Form 941 Report');
		$this->file_name = 'form_941';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_form941', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'default_setup_fields':
				$retval = array(
										'template',
										'time_period',
										'columns',
							   );

				break;
			case 'setup_fields':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-template' => TTi18n::gettext('Template'),
										'-1010-time_period' => TTi18n::gettext('Time Period'),

										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										//'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
										//'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
							   );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),
										'-1030-employee_number' => TTi18n::gettext('Employee #'),
										'-1035-sin' => TTi18n::gettext('SIN/SSN'),
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										//'-1111-current_currency' => TTi18n::gettext('Current Currency'),

										//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

										//Handled in date_columns above.
										//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

										'-1400-permission_control' => TTi18n::gettext('Permission Group'),
										'-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1420-policy_group' => TTi18n::gettext('Policy Group'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2010-wages' => TTi18n::gettext('Wages'), //Line 2
										'-2020-income_tax' => TTi18n::gettext('Income Tax'), //Line 3
										'-2030-social_security_wages' => TTi18n::gettext('Taxable Social Security Wages'), //Line 5a
										'-2040-social_security_tips' => TTi18n::gettext('Taxable Social Security Tips'), //Line 5b
										'-2050-medicare_wages' => TTi18n::gettext('Taxable Medicare Wages'), //Line 5c
										'-2060-sick_wages' => TTi18n::gettext('Sick Pay'), //Line 7b
										'-2070-eic' => TTi18n::gettext('Earned Income Credit (EIC)'), //Line 9
							);
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = $this->getOptions('dynamic_columns');
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						$retval[$column] = 'currency';
					}
				}
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}

				break;
			case 'schedule_deposit':
				$retval = array(
									10 => TTi18n::gettext('Monthly'),
									20 => TTi18n::gettext('Semi-Weekly')
								);
				break;
			case 'templates':
				$retval = array(
										'-1010-by_month' => TTi18n::gettext('by Month'),
										'-1020-by_employee' => TTi18n::gettext('by Employee'),
										'-1030-by_branch' => TTi18n::gettext('by Branch'),
										'-1040-by_department' => TTi18n::gettext('by Department'),
										'-1050-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'default':
							//Proper settings to generate the form.
							//$retval['-1010-time_period']['time_period'] = 'last_quarter';

							$retval['columns'] = $this->getOptions('columns');

							$retval['group'][] = 'date_quarter_month';

							$retval['sort'][] = array('date_quarter_month' => 'asc');

							$retval['other']['grand_total'] = TRUE;

							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_quarter';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

									switch( $template_keyword ) {
										//Columns

										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_month':
											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_department';

											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_employee':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_month_by_branch':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_month_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_branch_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;

									}
								}
							}

							$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) ) );

							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset($retval['filter']) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset($retval['filter']);
				}
				if ( isset($retval['columns']) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset($retval['columns']);
				}
				if ( isset($retval['group']) ) {
					$retval['-5020-group'] = $retval['group'];
					unset($retval['group']);
				}
				if ( isset($retval['sub_total']) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset($retval['sub_total']);
				}
				if ( isset($retval['sort']) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset($retval['sort']);
				}
				Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__,10);

				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	function getFormObject() {
		if ( !isset($this->form_obj['gf']) OR !is_object($this->form_obj['gf']) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() .'/classes/fpdi/fpdi.php');
			require_once( Environment::getBasePath() .'/classes/tcpdf/tcpdf.php');
			require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');

			$gf = new GovernmentForms();

			$this->form_obj['gf'] = $gf;
			return $this->form_obj['gf'];
		}

		return $this->form_obj['gf'];
	}

	function getF941Object() {
		if ( !isset($this->form_obj['f941']) OR !is_object($this->form_obj['f941']) ) {
			$this->form_obj['f941'] = $this->getFormObject()->getFormObject( '941', 'US' );
			return $this->form_obj['f941'];
		}

		return $this->form_obj['f941'];
	}

	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'wages' => $default_include_exclude_arr,
                'income_tax' => $default_include_exclude_arr,
                'social_security_wages' => $default_include_exclude_arr,
                'social_security_tips' => $default_include_exclude_arr,
                'medicare_wages' => $default_include_exclude_arr,
                'sick_wages' =>$default_include_exclude_arr,
                'eic' => $default_include_exclude_arr,
			);

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );
		return $retarr;
	}

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'pay_stub_entry' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$pself = TTnew( 'PayStubEntryListFactory' );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {

				$user_id = $this->user_ids[] = $pse_obj->getColumn('user_id');
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}


				if ( isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
			}

			if ( isset($this->tmp_data['pay_stub_entry']) AND is_array($this->tmp_data['pay_stub_entry']) ) {
				foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_a) {
					foreach($data_a as $date_stamp => $data_b) {
						$quarter_month = TTDate::getYearQuarterMonth( $date_stamp );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['wages'] 					= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['wages']['include_pay_stub_entry_account'], 					$form_data['wages']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['income_tax'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['income_tax']['include_pay_stub_entry_account'], 				$form_data['income_tax']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'] 	= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_wages']['include_pay_stub_entry_account'], 	$form_data['social_security_wages']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'] 	= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['social_security_tips']['include_pay_stub_entry_account'], 	$form_data['social_security_tips']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'] 			= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['medicare_wages']['include_pay_stub_entry_account'], 			$form_data['medicare_wages']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['sick_wages'] 				= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['sick_wages']['include_pay_stub_entry_account'], 				$form_data['sick_wages']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['eic'] 					= Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['eic']['include_pay_stub_entry_account'], 					$form_data['eic']['exclude_pay_stub_entry_account'] );

						//Separate data used for reporting, grouping, sorting, from data specific used for the Form.
						if ( !isset($this->form_data['pay_period'][$quarter_month][$date_stamp]) ) {
							$this->form_data['pay_period'][$quarter_month][$date_stamp] = Misc::preSetArrayValues( array(), array('l2','l3','l5a','l5b','l5c','l7b','l9','l5a2','l5b2','l5c2','l5d','l6','l7a','l7d','l8','l10' ), 0 );
						}
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l2'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l3'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['income_tax'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['social_security_tips'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['medicare_wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l7b'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['sick_wages'];
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l9'] += $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['eic'];

						//Calculated fields, make sure we don't use += on these.
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a'], $this->getF941Object()->social_security_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b'], $this->getF941Object()->social_security_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c2'] = bcmul( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c'], $this->getF941Object()->medicare_rate );
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d'] =  bcadd( bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a2'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b2']), $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c2']);
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l6'] = bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l3'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d']);

						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l7a'] = bcsub( Misc::MoneyFormat($this->form_data['pay_period'][$quarter_month][$date_stamp]['l5d'], FALSE) , bcadd( bcadd($this->form_data['pay_period'][$quarter_month][$date_stamp]['l5a2'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5b2']), $this->form_data['pay_period'][$quarter_month][$date_stamp]['l5c2'] ) );

						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l7d'] = bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l7a'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l7b']);
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l8'] = bcadd( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l6'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l7d']);
						$this->form_data['pay_period'][$quarter_month][$date_stamp]['l10'] = bcsub( $this->form_data['pay_period'][$quarter_month][$date_stamp]['l8'], $this->form_data['pay_period'][$quarter_month][$date_stamp]['l9']);
					}
				}

				//Total all pay periods by month_id
				if ( isset($this->form_data['pay_period']) ) {
					foreach( $this->form_data['pay_period'] as $month_id => $pp_data ) {
						$this->form_data['quarter'][$month_id] = Misc::ArrayAssocSum($pp_data, NULL, 8);
					}

					//Total all quarters.
					if ( isset($this->form_data['quarter']) ) {
						$this->form_data['total'] = Misc::ArrayAssocSum( $this->form_data['quarter'], NULL, 6);
					}
				}

			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				foreach( $level_1 as $date_stamp => $row ) {
					$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data  = array(
											//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
											);

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function _outputPDFForm( $format = NULL ) {
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' ) {
			$show_background = FALSE;
		}
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$f941 = $this->getF941Object();
		$f941->setDebug(FALSE);
		$f941->setShowBackground( $show_background );

		$f941->year = TTDate::getYear( $filter_data['end_date'] );

		//Add support for the user to manually set this data in the setup_data. That way they can use multiple tax IDs for different employees, all beit manually.
		$f941->ein = ( isset($setup_data['ein']) AND $setup_data['ein'] != '' ) ? $setup_data['ein'] : $current_company->getBusinessNumber();
		$f941->name = ( isset($setup_data['name']) AND $setup_data['name'] != '' ) ? $setup_data['name'] : $this->getUserObject()->getFullName();
		$f941->trade_name = ( isset($setup_data['company_name']) AND $setup_data['company_name'] != '' ) ? $setup_data['company_name'] : $current_company->getName();
		$f941->address = ( isset($setup_data['address1']) AND $setup_data['address1'] != '' ) ? $setup_data['address1'] : $current_company->getAddress1() .' '. $current_company->getAddress2();
		$f941->city = ( isset($setup_data['city']) AND $setup_data['city'] != '' ) ? $setup_data['city'] : $current_company->getCity();
		$f941->state = ( isset($setup_data['province']) AND ( $setup_data['province'] != '' AND $setup_data['province'] != 0 ) ) ? $setup_data['province'] : $current_company->getProvince();
		$f941->zip_code = ( isset($setup_data['postal_code']) AND $setup_data['postal_code'] != '' ) ? $setup_data['postal_code'] : $current_company->getPostalCode();

		$f941->quarter = TTDate::getYearQuarter( $filter_data['end_date'] );

		//Debug::Arr($this->form_data, 'Final Data for Form: ', __FILE__, __LINE__, __METHOD__,10);
		if ( isset($this->form_data) AND count($this->form_data) == 3 ) {

			$f941->l1 = count($this->user_ids);
			$f941->l2 = $this->form_data['total']['l2'];
			$f941->l3 = $this->form_data['total']['l3'];

			$f941->l5a = $this->form_data['total']['l5a'];
			$f941->l5b = $this->form_data['total']['l5b'];
			$f941->l5c = $this->form_data['total']['l5c'];

			$f941->l7a = $this->form_data['total']['l7a'];
			$f941->l7b = $this->form_data['total']['l7b'];
			//$f941->l7c = $lines_arr['total']['7c'];

			$f941->l9 = $this->form_data['total']['l9'];

			if ( isset($setup_data['quarter_deposit']) AND $setup_data['quarter_deposit'] != ''  ) {
				$f941->l11 = Misc::MoneyFormat($setup_data['quarter_deposit'], FALSE);
			} else {
				$f941->l11 = $setup_data['quarter_deposit'] = $this->form_data['total']['l10'];
			}

			$f941->l15b = TRUE;

			$f941->l16 = $current_company->getProvince();

			if ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 10 ) {
				if ( isset($this->form_data['quarter'][1]['l10']) ) {
					$f941->l17_month1 = $this->form_data['quarter'][1]['l10'];
				}
				if ( isset($this->form_data['quarter'][2]['l10']) ) {
					$f941->l17_month2 = $this->form_data['quarter'][2]['l10'];
				}
				if ( isset($this->form_data['quarter'][3]['l10']) ) {
					$f941->l17_month3 = $this->form_data['quarter'][3]['l10'];
				}
			} elseif ( isset($setup_data['deposit_schedule']) AND $setup_data['deposit_schedule'] == 20 ) {
				$f941sb = $this->getFormObject()->getFormObject( '941sb', 'US' );
				$f941sb->setShowBackground( $show_background );

				$f941sb->year = $f941->year;
				$f941sb->ein = $f941->ein;
				$f941sb->name = $f941->name;
				$f941sb->quarter = $f941->quarter;

				for( $i=1; $i <= 3; $i++ ) {
					for( $d=1; $d <= 31; $d++ ) {
						if ( isset($this->form_data['pay_period'][$i]) ) {
							foreach( $this->form_data['pay_period'][$i] as $pay_period_epoch => $data ) {
								$dom = TTDate::getDayOfMonth($pay_period_epoch);
								if ( $d == $dom ) {
									$f941sb_data[$i][$d] = Misc::MoneyFormat($data['l10'], FALSE);
								}
							}
						}
					}
				}

				if ( isset($f941sb_data[1]) ) {
					$f941sb->month1 = $f941sb_data[1];
				}
				if ( isset($f941sb_data[2]) ) {
					$f941sb->month2 = $f941sb_data[2];
				}
				if ( isset($f941sb_data[3]) ) {
					$f941sb->month3 = $f941sb_data[3];
				}
				unset($i, $d, $f941sb_data);
			}
		} else {
			Debug::Arr($this->data, 'Invalid Form Data: ', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->getFormObject()->addForm( $f941 );

		if ( isset($f941sb) AND is_object( $f941sb ) ) {
			$this->getFormObject()->addForm( $f941sb );
		}

		$output = $this->getFormObject()->output( 'PDF' );

		return $output;
	}

	function _output( $format = NULL ) {
		if ( $format == 'pdf_form' OR $format == 'pdf_form_print' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}

	}
}
?>
