<?php

namespace App\Models\Report;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Hierarchy\HierarchyListFactory;
use App\Models\PayStub\PayStubEntryAccountLinkListFactory;
use App\Models\PayStub\PayStubEntryAccountListFactory;
use App\Models\PayStub\PayStubEntryListFactory;
use App\Models\PayStub\PayStubFactory;
use App\Models\PayStub\PayStubListFactory;
use App\Models\Users\UserGenericDataListFactory;
use App\Models\Users\UserListFactory;

class PayStubSummaryReport extends Report {

	function __construct() {
		$this->title = ('Pay Stub Summary Report');
		$this->file_name = 'paystub_summary_report';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_pay_stub_summary', $user_id, $company_id ) ) {
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
										'-1000-template' => ('Template'),
										'-1010-time_period' => ('Time Period'),

										'-2010-user_status_id' => ('Employee Status'),
										'-2020-user_group_id' => ('Employee Group'),
										'-2030-user_title_id' => ('Employee Title'),
										'-2040-include_user_id' => ('Employee Include'),
										'-2050-exclude_user_id' => ('Employee Exclude'),
										'-2060-default_branch_id' => ('Default Branch'),
										'-2070-default_department_id' => ('Default Department'),

										'-5000-columns' => ('Display Columns'),
										'-5010-group' => ('Group By'),
										'-5020-sub_total' => ('SubTotal By'),
										'-5030-sort' => ('Sort By'),
							   );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( 'transaction', ('Transaction Date'), 13, TRUE );
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => ('First Name'),
										'-1001-middle_name' => ('Middle Name'),
										'-1002-last_name' => ('Last Name'),
										'-1005-full_name' => ('Full Name'),
										'-1030-employee_number' => ('Employee #'),
										'-1040-status' => ('Status'),
										'-1050-title' => ('Title'),
										'-1060-province' => ('Province/State'),
										'-1070-country' => ('Country'),
										'-1080-user_group' => ('Group'),
										'-1090-default_branch' => ('Default Branch'),
										'-1100-default_department' => ('Default Department'),
										'-1110-currency' => ('Currency'),
										'-1200-permission_control' => ('Permission Group'),
										'-1210-pay_period_schedule' => ('Pay Period Schedule'),
										'-1220-policy_group' => ('Policy Group'),
										//Handled in date_columns above.
										//'-1250-pay_period' => ('Pay Period'),
							   );

				$retval = array_merge( $retval, $this->getOptions('date_columns') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										'-2010-hourly_rate' => ('Hourly Rate'),

							);

				$retval = array_merge( $retval, $this->getOptions('pay_stub_account_amount_columns') );
				ksort($retval);

				break;
			case 'pay_stub_account_amount_columns':
				//Get all pay stub accounts
				$retval = array();

				$psealf = new PayStubEntryAccountListFactory();
				$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(10,20,30,40,50,60,65) );
				if ( $psealf->getRecordCount() > 0 ) {
					$type_options  = $psealf->getOptions('type');
					foreach( $type_options as $key => $val ) {
						$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction', 'Total'), array('EE', 'ER', 'Ded', ''), $val);
					}
					$i=0;
					foreach( $psealf->rs as $psea_obj ) {
						$psealf->data = (array)$psea_obj;
						$psea_obj = $psealf;
						//Need to make the PSEA_ID a string so we can array_merge it properly later.
						if ( $psea_obj->getType() == 40 ) { //Total accounts.
							$prefix = NULL;
						} else {
							$prefix = $type_options[$psea_obj->getType()] .' - ';
						}

						$retval['-3'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-P'.$psea_obj->getID()] = $prefix.$psea_obj->getName();
						$i++;
					}
				}
				break;
			case 'pay_stub_account_unit_columns':
				//Units are only good for earnings?
				break;
			case 'pay_stub_account_ytd_columns':
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = $this->getOptions('dynamic_columns');
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE OR strpos('P', $column ) == 0 ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = array(

										'-1010-by_employee+totals' => ('Totals by Employee'),
										'-1020-by_employee+earnings' => ('Earnings by Employee'),
										'-1030-by_employee+employee_deductions' => ('Deductions by Employee'),
										'-1040-by_employee+employer_deductions' => ('Employer Contributions by Employee'),
										'-1050-by_employee+accruals' => ('Accruals by Employee'),
										'-1060-by_employee+totals+earnings+employee_deductions+employer_deductions+accruals' => ('All Accounts by Employee'),

										'-1110-by_title+totals' => ('Totals by Title'),
										'-1120-by_group+totals' => ('Totals by Group'),
										'-1130-by_branch+totals' => ('Totals by Branch'),
										'-1140-by_department+totals' => ('Totals by Department'),
										'-1150-by_branch_by_department+totals' => ('Totals by Branch/Department'),
										'-1160-by_pay_period+totals' => ('Totals by Pay Period'),

										'-1210-by_pay_period_by_employee+totals' => ('Totals by Pay Period/Employee'),
										'-1220-by_employee_by_pay_period+totals' => ('Totals by Employee/Pay Period'),
										'-1230-by_branch_by_pay_period+totals' => ('Totals by Branch/Pay Period'),
										'-1240-by_department_by_pay_period+totals' => ('Totals by Department/Pay Period'),
										'-1250-by_branch_by_department_by_pay_period+totals' => ('Totals by Branch/Department/Pay Period'),
							   );

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					$pseallf = new PayStubEntryAccountLinkListFactory(); 
					$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
					if ( $pseallf->getRecordCount() > 0 ) {
						$pseal_obj = $pseallf->getCurrent();

						$default_linked_columns = array(
													$pseal_obj->getTotalGross(),
													$pseal_obj->getTotalNetPay(),
													$pseal_obj->getTotalEmployeeDeduction(),
													$pseal_obj->getTotalEmployerDeduction() );
					} else {
						$default_linked_columns = array();
					}
					unset($pseallf, $pseal_obj);

					switch( $template ) {
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__,10);

									switch( $template_keyword ) {
										//Columns
										case 'earnings':
											$retval['columns'][] = 'P'.$default_linked_columns[0]; //Total Gross
											$retval['columns'][] = 'P'.$default_linked_columns[1]; //Net Pay

											$psealf = new PayStubEntryAccountListFactory();
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(10) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf->rs as $psea_obj ) {
													$psealf->data = (array)$psea_obj;
													$psea_obj = $psealf;
													$retval['columns'][] = 'P'.$psea_obj->getID();
												}
											}
											break;
										case 'employee_deductions':
											$retval['columns'][] = 'P'.$default_linked_columns[2]; //Employee Deductions

											$psealf = new PayStubEntryAccountListFactory();
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(20) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf->rs as $psea_obj ) {
													$psealf->data = (array)$psea_obj;
													$psea_obj = $psealf;
													$retval['columns'][] = 'P'.$psea_obj->getID();
												}
											}
											break;
										case 'employer_deductions':
											$retval['columns'][] = 'P'.$default_linked_columns[3]; //Employor Deductions

											$psealf = new PayStubEntryAccountListFactory();
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(30) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf->rs as $psea_obj ) {
													$psealf->data = (array)$psea_obj;
													$psea_obj = $psealf;
													$retval['columns'][] = 'P'.$psea_obj->getID();
												}
											}
											break;
										case 'totals':
											$psealf = new PayStubEntryAccountListFactory();
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(40) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf->rs as $psea_obj ) {
													$psealf->data = (array)$psea_obj;
													$psea_obj = $psealf;
													$retval['columns'][] = 'P'.$psea_obj->getID();
												}
											}
											break;
										case 'accruals':
											$psealf = new PayStubEntryAccountListFactory();
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(50) );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach( $psealf->rs as $psea_obj ) {
													$psealf->data = (array)$psea_obj;
													$psea_obj = $psealf;
													$retval['columns'][] = 'P'.$psea_obj->getID();
												}
											}
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_title':
											$retval['columns'][] = 'title';

											$retval['group'][] = 'title';

											$retval['sort'][] = array('title' => 'asc');
											break;
										case 'by_group':
											$retval['columns'][] = 'user_group';

											$retval['group'][] = 'user_group';

											$retval['sort'][] = array('user_group' => 'asc');
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
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_pay_period_by_branch':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('pay_period' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_employee_by_pay_period':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = array('full_name' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_pay_period':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'default_department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = array('default_department' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'default_branch';
											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											$retval['sort'][] = array('pay_period' => 'asc');
											break;
									}
								}
							}
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

	//Get raw data for report
	function _getData( $format = NULL ) {
		$this->tmp_data = array('pay_stub_entry' => array(), 'user' => array() );

		//Don't need to process data unless we're preparing the report.
		$psf = new PayStubFactory(); 
		$export_type_options = Misc::trimSortPrefix( $psf->getOptions('export_type') );
		Debug::Arr($export_type_options, 'zFormat: '. $format, __FILE__, __LINE__, __METHOD__,10);
		if ( isset($export_type_options[$format]) ) {
			Debug::Text('Skipping data retrieval for format: '. $format, __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('pay_stub','view') == FALSE OR $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			$hlf = new HierarchyListFactory(); 
			$permission_children_ids = $wage_permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
			$wage_permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('pay_stub','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('pay_stub','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('pay_stub','view_own') ) {
				$permission_children_ids[] = $this->getUserObject()->getID();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $this->getPermissionObject()->Check('wage','view') == TRUE ) {
			$wage_permission_children_ids = TRUE;
		} elseif ( $this->getPermissionObject()->Check('wage','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('wage','view_child') == FALSE ) {
				$wage_permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('wage','view_own') ) {
				$wage_permission_children_ids[] = $this->getUserObject()->getID();
			}
		}
		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

		$pself = new PayStubEntryListFactory(); 
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, ('Retrieving Data...') );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself->rs as $key => $pse_obj ) {
				$pself->data = (array)$pse_obj;
				$pse_obj = $pself;
				$hourly_rate = 0;
				if ( $wage_permission_children_ids === TRUE OR in_array( $user_id, $wage_permission_children_ids) ) {
					$hourly_rate = $pse_obj->getColumn( 'hourly_rate' );
				}

				$user_id = $pse_obj->getColumn('user_id');
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_period_transaction_date') );
				$branch = $pse_obj->getColumn('default_branch');
				$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_period_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_period_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_period_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_period_transaction_date') ),

																'pay_stub_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_stub_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_stub_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}


				if ( isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['P'.$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['P'.$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		//Get user data for joining.
		$ulf = new UserListFactory(); 
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, ('Retrieving Data...') );
		foreach ( $ulf->rs as $key => $u_obj ) {
			$ulf->data = (array)$u_obj;
			$u_obj = $ulf;
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, ('Pre-Processing Data...') );

		//Merge time data with user data
		$key=0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach( $level_1 as $date_stamp => $row ) {
						$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data  = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
												);

						//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function _outputPDFPayStub( $format ) {
		Debug::Text(' Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$filter_data = $this->getFilterConfig();

		if ( !$this->getPermissionObject()->Check('pay_stub','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub','view') OR $this->getPermissionObject()->Check('pay_stub','view_own')  OR $this->getPermissionObject()->Check('pay_stub','view_child')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$pslf = new PayStubListFactory(); 
		$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text('Record Count: '. $pslf->getRecordCount() .' Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
		if ( $pslf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
			$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

			$filter_data['hide_employer_rows'] = TRUE;
			if ( $format == 'pdf_employer_pay_stub' OR $format == 'pdf_employer_pay_stub_print' ) {
				//Must be false, because if it isn't checked it won't be set.
				$filter_data['hide_employer_rows'] = FALSE;
			}

			$output = $pslf->getPayStub( $pslf, (bool)$filter_data['hide_employer_rows'] );

			return $output;
		}

		Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function _outputExportPayStub( $format ) {
		Debug::Text(' Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		$filter_data = $this->getFilterConfig();

		if ( !$this->getPermissionObject()->Check('pay_stub','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub','view') OR $this->getPermissionObject()->Check('pay_stub','view_own')  OR $this->getPermissionObject()->Check('pay_stub','view_child')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$pslf = new PayStubListFactory();
		$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text('Record Count: '. $pslf->getRecordCount() .' Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
		if ( $pslf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
			$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

			$output = $pslf->exportPayStub( $pslf, $format );

			if ( stristr( $format, 'cheque') ) {
				$file_name = 'checks_'.date('Y_m_d').'.pdf';
				$mime_type = 'application/pdf';
			} else {
				//Include file creation number in the exported file name, so the user knows what it is without opening the file,
				//and can generate multiple files if they need to match a specific number.
				$ugdlf = new UserGenericDataListFactory(); 
				$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), 'PayStubFactory', TRUE );
				if ( $ugdlf->getRecordCount() > 0 ) {
					$ugd_obj = $ugdlf->getCurrent();
					$setup_data = $ugd_obj->getData();
				}

				if ( isset($setup_data) ) {
					$file_creation_number = $setup_data['file_creation_number']++;
				} else {
					$file_creation_number = 0;
				}

				$file_name = 'eft_'.$file_creation_number.'_'.date('Y_m_d').'.txt';
				$mime_type = 'application/text';
			}

			return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output );
		}

		Debug::Text('No data to return...', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function _output( $format = NULL ) {
		$psf = new PayStubFactory();
		$export_type_options = Misc::trimSortPrefix( $psf->getOptions('export_type') );
		Debug::Arr($export_type_options, 'Format: '. $format, __FILE__, __LINE__, __METHOD__,10);
		if ( $format == 'pdf_employee_pay_stub' OR $format == 'pdf_employee_pay_stub_print'
				OR $format == 'pdf_employer_pay_stub' OR $format == 'pdf_employer_pay_stub_print' ) {
			return $this->_outputPDFPayStub( $format );
		} elseif ( strlen( $format ) >= 4 AND isset( $export_type_options[$format] ) ) {
			return $this->_outputExportPayStub( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
