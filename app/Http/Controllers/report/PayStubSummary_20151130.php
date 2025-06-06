<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4600 $
 * $Id: PayStubSummary.php 4600 2011-04-28 21:35:12Z ipso $
 * $Date: 2011-04-28 14:35:12 -0700 (Thu, 28 Apr 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_pay_stub_summary') ) {
	$permission->Redirect( FALSE ); //Redirect
}
$smarty->assign('title', __($title = 'Pay Stub Summary Report'));  // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'
												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$static_columns = array(
							'-0800-epf_membership_no' => _('EPF  #'),
							'-0801-full_name' => _('Full Name'),
							'-0802-basic_for_epf' => _('Basic for EPF'),
							'-0900-first_name' => _('First Name'),
							'-0901-middle_name' => _('Middle Name'),
							'-0902-middle_initial' => _('Middle Initial'),
							'-0903-last_name' => _('Last Name'),
							'-1000-full_name' => _('Full Name'),
							'-1002-employee_number' => _('Employee #'),
							'-1010-title' => _('Title'),
							'-1020-province' => _('Province/State'),
							'-1030-country' => _('Country'),
							'-1039-group' => _('Group'),
							'-1040-default_branch' => _('Default Branch'),
							'-1050-default_department' => _('Default Department'),
							'-1060-sin' => _('SIN/SSN'),
							'-1065-birth_date' => _('Birth Date'),
							'-1070-hire_date' => _('Hire Date'),
							'-1080-since_hire_date' => _('Since Hired'),
							'-1085-termination_date' => _('Termination Date'),
							'-1086-institution' => _('Bank Institution'),
							'-1087-transit' => _('Bank Transit/Routing'),
							'-1089-account' => _('Bank Account'),
							'-1090-pay_period' => _('Pay Period'),
							'-1100-pay_stub_start_date' => _('Start Date'),
							'-1110-pay_stub_end_date' => _('End Date'),
							'-1120-pay_stub_transaction_date' => _('Transaction Date'),
							'-1130-currency' => _('Currency'),
							'-1131-current_currency' => _('Current Currency'),
							'-1132-epf_20_persent' => _('E.P.F - 20%'),
							);

$psealf = new PayStubEntryAccountListFactory();
$psen_columns = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,40,50,60,65), FALSE );

$columns = Misc::prependArray( $static_columns, $psen_columns);

$default_transaction_start_date = TTDate::getBeginMonthEpoch( time() );
$default_transaction_end_date = TTDate::getEndMonthEpoch( time() );

//Get all pay periods
$pplf = new PayPeriodListFactory();
$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
$pay_period_options = array();
if ( $pplf->getRecordCount() > 0 ) {
	$pp=0;
	foreach ($pplf as $pay_period_obj) {
		$pay_period_ids[] = $pay_period_obj->getId();
		$pay_period_end_dates[$pay_period_obj->getId()] = $pay_period_obj->getEndDate();

		if ( $pp == 0 ) {
			$default_transaction_start_date = $pay_period_obj->getEndDate();
			$default_transaction_end_date = $pay_period_obj->getTransactionDate()+86400;
		}
		$pp++;
	}
	$pplf = new PayPeriodListFactory();
	$pay_period_options = $pplf->getByIdListArray($pay_period_ids, NULL, array('start_date' => 'desc'));
}

if ( isset($filter_data['transaction_start_date']) ) {
	$filter_data['transaction_start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime($filter_data['transaction_start_date']) );
}

if ( isset($filter_data['transaction_end_date']) ) {
	$filter_data['transaction_end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime($filter_data['transaction_end_date']) );
}

$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'currency_ids', 'pay_period_ids', 'column_ids' ), array() );

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('pay_stub','view') == FALSE ) {
	$hlf = new HierarchyListFactory();
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('pay_stub','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('pay_stub','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = new UserGenericDataListFactory();
$ugdf = new UserGenericDataFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'view_pay_stubs':
	case 'export':
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit! Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data, 'aFilter Data', __FILE__, __LINE__, __METHOD__,10);

		$ulf = new UserListFactory();
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset($filter_data['date_type']) AND $filter_data['date_type'] == 'pay_period_ids' ) {
				unset($filter_data['transaction_start_date']);
				unset($filter_data['transaction_end_date']);
			} else {
				unset($filter_data['pay_period_ids']);
			}

			foreach( $ulf as $u_obj ) {
				$filter_data['user_id'][] = $u_obj->getId();
			}

			//Trim sort prefix from selected pay periods.
			if ( isset($filter_data['pay_period_ids']) ) {
				$tmp_filter_pay_period_ids = $filter_data['pay_period_ids'];
				$filter_data['pay_period_ids'] = array();
				foreach( $tmp_filter_pay_period_ids as $key => $filter_pay_period_id) {
					$filter_data['pay_period_ids'][] = Misc::trimSortPrefix($filter_pay_period_id);
				}
				unset($key, $tmp_filter_pay_period_ids, $filter_pay_period_id);
			}

			if ( ( ( isset($filter_data['transaction_start_date']) AND isset($filter_data['transaction_end_date']) ) OR isset($filter_data['pay_period_ids']) )
					AND isset($filter_data['user_id']) ) {
					
					
					
					
					
                                //ARSP EDIT --> ADD NEW CODE FOR DISPLAY 3 PAY SLIP PER PAGE 
				if ( $action == 'view_pay_stubs' &&  $filter_data['export_type'] == 'payslip3') {
                                    
					Debug::Text('View Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

					$pslf = new PayStubListFactory();
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 ) {
						if ( !isset($filter_data['hide_employer_rows']) ) {
							//Must be false, because if it isn't checked it won't be set.
							$filter_data['hide_employer_rows'] = FALSE;
						}
//                                                print_r($pslf);
//                                                exit(0);

						$output = $pslf->getThreePaySlipPerPage( $pslf, (bool)$filter_data['hide_employer_rows'] );
                                                
						if ( Debug::getVerbosity() < 11 ) {
							Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
							echo $output;
							exit;
						}
					}
                                //ARSP EDIT --> view_pay_stubs code End.
				}       					
					
					
                                //
                                //ARSP NOTE --> FOUR PAY SLIP PER PAGE LANDSACPE
                                //
					
                                //ARSP NOTE --> I ADDED THIS CODE FOR THUNDER & NEON
                                //ARSP EDIT --> I ADD NEW CODE FOR DISPLAY 4 PAY SLIP PER PAGE LANDSCAPE 
				if ( $action == 'view_pay_stubs' &&  $filter_data['export_type'] == 'payslip4') {
                                    
					Debug::Text('View Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

					$pslf = new PayStubListFactory();
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 ) {
						if ( !isset($filter_data['hide_employer_rows']) ) {
							//Must be false, because if it isn't checked it won't be set.
							$filter_data['hide_employer_rows'] = FALSE;
						}
//                                                print_r($pslf);
//                                                exit(0);

						$output = $pslf->getFourPaySlipPerPageLandscape( $pslf, (bool)$filter_data['hide_employer_rows'] );
                                                
						if ( Debug::getVerbosity() < 11 ) {
							Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
							echo $output;
							exit;
						}
					}
                                //ARSP EDIT --> view_pay_stubs code End.
				}                 
					
					
					
					
					
					
				if ( $action == 'view_pay_stubs' ) {
					Debug::Text('View Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

					$pslf = new PayStubListFactory();
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 ) {
						if ( !isset($filter_data['hide_employer_rows']) ) {
							//Must be false, because if it isn't checked it won't be set.
							$filter_data['hide_employer_rows'] = FALSE;
						}

						$output = $pslf->getPayStub( $pslf, (bool)$filter_data['hide_employer_rows'] );

						if ( Debug::getVerbosity() < 11 ) {
							Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
							echo $output;
							exit;
						}
					}
				}
				//ARSP EDIT-->Add New code for $filter_data['export_type'] != 'pdf'
				 elseif ( $action == 'export' AND $filter_data['export_type'] != 'csv' AND $filter_data['export_type'] != 'pdfp' AND $filter_data['export_type'] != 'pdfl' AND $filter_data['export_type'] != 'formc')//ARSP EDIT --> ADD NEW CODE for $filter_data['export_type'] != 'pdfp' AND $filter_data['export_type'] != 'pdfl'
				  {
					Debug::Text('Export NON-CSV', __FILE__, __LINE__, __METHOD__,10);

					$pslf = new PayStubListFactory();
					//$pslf->getByUserIdAndCompanyIdAndPayPeriodId( $filter_data['user_ids'], $current_company->getId(), $filter_data['pay_period_ids']);
					$pslf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pslf->getRecordCount() > 0 AND strlen($filter_data['export_type']) >= 3) {
						$output = $pslf->exportPayStub( $pslf, $filter_data['export_type'] );

						if ( Debug::getVerbosity() < 11 ) {
							if ( stristr( $filter_data['export_type'], 'cheque') ) {
								Misc::FileDownloadHeader('checks_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.pdf', 'application/pdf', strlen($output));
							} else {

								//Include file creation number in the exported file name, so the user knows what it is without opening the file,
								//and can generate multiple files if they need to match a specific number.
								$ugdlf = new UserGenericDataListFactory();
								$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), 'PayStubFactory', TRUE );
								if ( $ugdlf->getRecordCount() > 0 ) {
									$ugd_obj = $ugdlf->getCurrent();
									$setup_data = $ugd_obj->getData();
								}

								if ( isset($setup_data) ) {
									$file_creation_number = $setup_data['file_creation_number']++;
								} else {
									$file_creation_number = 0;
								}
								Misc::FileDownloadHeader('eft_'. $file_creation_number .'_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.txt', 'application/text', strlen($output));
							}

							if ( $output != FALSE ) {
								echo $output;
							} else {
								echo _('No data to export.') ."<br>\n";
							}
							exit;
						}
					} else {
						echo _('No data to export or export format is invalid.') ."<br>\n";
						exit;
					}
				} else {
					//Get column headers
					$report_columns = array();

					//Strip off Employee Deduction, Earnings, etc from names so they don't clutter reports.
					$psealf->getByCompanyId( $current_company->getId() );
					foreach($psealf as $psea_obj) {
						//$report_columns[$psen_obj->getId()] = $psen_obj->getDescription();
						$report_columns[$psea_obj->getId()] = $psea_obj->getName();
					}
					//var_dump($report_columns);

					$report_columns = Misc::prependArray( $static_columns, $report_columns);

					$pself = new PayStubEntryListFactory();
					$pself->getReportByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
					if ( $pself->getRecordCount() > 0 ) {
						//Prepare data for regular report.
						foreach( $pself as $pse_obj ) {
							$user_id = $pse_obj->getColumn('user_id');
							$pay_stub_id = $pse_obj->getColumn('pay_stub_id');
							$currency_id = $pse_obj->getColumn('currency_id');
							$currency_rate = $pse_obj->getColumn('currency_rate');
							//$pay_period_id = $pse_obj->getColumn('pay_period_id');
							//$pay_stub_transaction_date = $pse_obj->getColumn('pay_stub_transaction_date');
							$pay_stub_entry_name_id = $pse_obj->getColumn('pay_stub_entry_name_id');

							//$raw_rows[$user_id][$pay_p][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');

							if ( !isset($raw_rows[$user_id][$pay_stub_id]) ) {
								$raw_rows[$user_id][$pay_stub_id]['pay_period_id'] = $pse_obj->getColumn('pay_period_id');
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_start_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_start_date') );
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_end_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_end_date') );
								$raw_rows[$user_id][$pay_stub_id]['pay_stub_transaction_date'] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
								$raw_rows[$user_id][$pay_stub_id]['currency_id'] = $pse_obj->getColumn('currency_id');
								$raw_rows[$user_id][$pay_stub_id]['currency_rate'] = $pse_obj->getColumn('currency_rate');
							}
							$raw_rows[$user_id][$pay_stub_id]['pay_stub_entry_name'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
						}
						unset($user_id, $pay_stub_id, $currency_id, $currency_rate, $pay_stub_entry_name_id);
					}
					//var_dump($raw_rows);

					if ( isset($raw_rows) ) {
						$ulf = new UserListFactory();

						$utlf = new UserTitleListFactory();
						$title_options = $utlf->getByCompanyIdArray( $current_company->getId() );

						$uglf = new UserGroupListFactory();
						$group_options = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'no_tree_text', TRUE) );

						$blf = new BranchListFactory();
						$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

						$dlf = new DepartmentListFactory();
						$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

						$crlf = new CurrencyListFactory();
						$crlf->getByCompanyId( $current_company->getId() );
						$currency_options = $crlf->getArrayByListFactory( $crlf, FALSE, TRUE );

						//Get Base Currency
						$crlf->getByCompanyIdAndBase( $current_company->getId(), TRUE );
						if ( $crlf->getRecordCount() > 0 ) {
							$base_currency_obj = $crlf->getCurrent();
						}

						$currency_convert_to_base = FALSE;
						if ( in_array( '-1', $filter_data['currency_ids']) OR count($filter_data['currency_ids']) > 1 ) {
							Debug::Text('More then one currency selected, converting to base!', __FILE__, __LINE__, __METHOD__,10);
							$currency_convert_to_base = TRUE;
						}

						$balf = new BankAccountListFactory();

						$x=0;
						foreach($raw_rows as $user_id => $data_b) {
							$user_obj = $ulf->getById( $user_id )->getCurrent();
							$balf->getUserAccountByCompanyIdAndUserId( $user_obj->getCompany(), $user_obj->getID() );
							if ( $balf->getRecordCount() == 1 ) {
								$ba_obj = $balf->getCurrent();
							}

							foreach($data_b as $pay_stub_id => $raw_row) {
								$tmp_rows[$x]['user_id'] = $user_id;
								$tmp_rows[$x]['first_name'] = $user_obj->getFirstName();
								$tmp_rows[$x]['middle_name'] = $user_obj->getMiddleName();
								$tmp_rows[$x]['middle_initial'] = $user_obj->getMiddleInitial();
								$tmp_rows[$x]['last_name'] = $user_obj->getLastName();
								$tmp_rows[$x]['full_name'] = $user_obj->getFullName(TRUE);
								$tmp_rows[$x]['employee_number'] = $user_obj->getEmployeeNumber();
								$tmp_rows[$x]['epf_membership_no'] = $user_obj->getEpfMembershipNo();
								//$tmp_rows[$x]['province'] = Option::getByKey($user_obj->getProvince(), $user_obj->getCompanyObject()->getOptions('province', $user_obj->getCountry() ) );
								//$tmp_rows[$x]['country'] = Option::getByKey($user_obj->getCountry(), $user_obj->getCompanyObject()->getOptions('country') );
								$tmp_rows[$x]['province'] = $user_obj->getProvince();
								$tmp_rows[$x]['country'] = $user_obj->getCountry();

								$tmp_rows[$x]['pay_period'] = Option::getByKey($raw_row['pay_period_id'], $pay_period_options, NULL );
								$tmp_rows[$x]['pay_period_order'] = Option::getByKey($raw_row['pay_period_id'], $pay_period_end_dates, NULL );

								$tmp_rows[$x]['pay_stub_start_date_order'] = $raw_row['pay_stub_start_date'];
								$tmp_rows[$x]['pay_stub_end_date_order'] = $raw_row['pay_stub_end_date'];
								$tmp_rows[$x]['pay_stub_transaction_order'] = $raw_row['pay_stub_transaction_date'];

								$tmp_rows[$x]['pay_stub_start_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_start_date'] );
								$tmp_rows[$x]['pay_stub_end_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_end_date'] );
								$tmp_rows[$x]['pay_stub_transaction_date'] = TTDate::getDate('DATE', $raw_row['pay_stub_transaction_date'] );

								$tmp_rows[$x]['title'] = Option::getByKey($user_obj->getTitle(), $title_options, NULL );
								$tmp_rows[$x]['group'] = Option::getByKey($user_obj->getGroup(), $group_options );
								$tmp_rows[$x]['default_branch'] =  Option::getByKey($user_obj->getDefaultBranch(), $branch_options, NULL );
								$tmp_rows[$x]['default_department'] = Option::getByKey($user_obj->getDefaultDepartment(), $department_options, NULL );
								
								$sin_number = NULL;
								if ( $permission->Check('user','view_sin') == TRUE ) {
									$sin_number = $user_obj->getSIN();
								} else {
									$sin_number = $user_obj->getSecureSIN();
								}

								$tmp_rows[$x]['sin'] = $sin_number;
								$tmp_rows[$x]['birth_date_order'] = $user_obj->getBirthDate();
								$tmp_rows[$x]['birth_date'] = TTDate::getDate('DATE', $user_obj->getBirthDate() );

								$tmp_rows[$x]['hire_date_order'] = $user_obj->getHireDate();
								$tmp_rows[$x]['hire_date'] = TTDate::getDate('DATE', $user_obj->getHireDate() );
								$tmp_rows[$x]['since_hire_date'] = TTDate::getHumanTimeSince( $user_obj->getHireDate() );

								$tmp_rows[$x]['termination_date_order'] = $user_obj->getTerminationDate();
								$tmp_rows[$x]['termination_date'] = TTDate::getDate('DATE', $user_obj->getTerminationDate() );

								if ( isset($ba_obj ) ) {
									$tmp_rows[$x]['institution'] = $ba_obj->getInstitution();
									$tmp_rows[$x]['transit'] = $ba_obj->getTransit();
									$tmp_rows[$x]['account'] = $ba_obj->getAccount();
								} else {
									$tmp_rows[$x]['institution'] = NULL;
									$tmp_rows[$x]['transit'] = NULL;
									$tmp_rows[$x]['account'] = NULL;
								}

								$tmp_rows[$x]['currency'] = $tmp_rows[$x]['current_currency'] = Option::getByKey( $raw_row['currency_id'], $currency_options );
								if ( $currency_convert_to_base == TRUE ) {
									$tmp_rows[$x]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
								}

								foreach($raw_row['pay_stub_entry_name'] as $id => $amount ) {
									//$tmp_rows[$x][$id] = $amount;
									$tmp_rows[$x][$id] = $base_currency_obj->getBaseCurrencyAmount( $amount, $raw_row['currency_rate'], $currency_convert_to_base );
								}
                                                                $tmp_rows[$x]['basic_for_epf'] = number_format(doubleval($tmp_rows[$x][49]) - doubleval($tmp_rows[$x][45]),2,'.','');
                                                                $tmp_rows[$x]['epf_20_persent'] = number_format(doubleval($tmp_rows[$x][9]) + doubleval($tmp_rows[$x][10]),2,'.','');
//                                                                var_dump(); die;

								unset($id, $amount);

								$x++;
							}
							unset($ba_obj);
						}
					}
					//var_dump($rows);

					if ( isset($tmp_rows) AND isset($filter_data['primary_group_by']) AND $filter_data['primary_group_by'] != '0' ) {
						Debug::Text('Primary Grouping Data By: '. $filter_data['primary_group_by'], __FILE__, __LINE__, __METHOD__,10);

						$ignore_elements = array_keys($static_columns);

						$filter_data['column_ids'] = array_diff( $filter_data['column_ids'], $ignore_elements );

						//Add the group by element back in
						if ( isset($filter_data['secondary_group_by']) AND $filter_data['secondary_group_by'] != 0 ) {
							array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'], $filter_data['secondary_group_by'] );
						} else {
							array_unshift( $filter_data['column_ids'], $filter_data['primary_group_by'] );
						}

						$tmp_rows = Misc::ArrayGroupBy( $tmp_rows, array(Misc::trimSortPrefix($filter_data['primary_group_by']),Misc::trimSortPrefix($filter_data['secondary_group_by'])), Misc::trimSortPrefix($ignore_elements, TRUE) );
					}

					if ( isset($tmp_rows) ) {
						foreach($tmp_rows as $row) {
							$rows[] = $row;
						}

						$special_sort_columns = array('pay_period', 'pay_stub_start_date', 'pay_stub_end_date', 'pay_stub_transaction_date');
						if ( in_array( Misc::trimSortPrefix($filter_data['primary_sort']), $special_sort_columns ) ) {
								$filter_data['primary_sort'] = $filter_data['primary_sort'].'_order';
						}
						if ( in_array( Misc::trimSortPrefix($filter_data['secondary_sort']), $special_sort_columns ) ) {
								$filter_data['secondary_sort'] = $filter_data['secondary_sort'].'_order';
						}

						$rows = Sort::Multisort($rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

						$total_row = Misc::ArrayAssocSum($rows, NULL, 2);

						$last_row = count($rows);
						$rows[$last_row] = $total_row;
						foreach ($static_columns as $static_column_key => $static_column_val) {
							$rows[$last_row][Misc::trimSortPrefix($static_column_key)] = NULL;
						}
						unset($static_column_key, $static_column_val);
					}

					foreach( $filter_data['column_ids'] as $column_key ) {
						$filter_columns[Misc::trimSortPrefix($column_key)] = $report_columns[$column_key];
					}
				}
			}
		}

		if ( $action == 'export' AND $filter_data['export_type'] == 'csv' ) {
			if ( isset($rows) AND isset($filter_columns) ) {
				Debug::Text('Exporting as CSV', __FILE__, __LINE__, __METHOD__,10);
				$data = Misc::Array2CSV( $rows, $filter_columns );

				Misc::FileDownloadHeader('report.csv', 'application/csv', strlen($data) );
				echo $data;
			} else {
				echo _('No Data To Export!') ."<br>\n";
			}
		}
		
		
                
                /*
                 * ARSP ADDED NEW CODE HERE...
                 * ARSP EDIT --> ADD NEW CODE FOR Export PDF REPORT
                 * PAGE ORIENTATION IS PORTRAIT 
                 */
				 //PAGE ORIENTATION IS PORTRAIT                       
                else if ( $action == 'export' AND $filter_data['export_type'] == 'pdfp' ) {
                    
                    
                    $payperiod_string= "";
                    if($filter_data['date_type'] == 'pay_period_ids')//ARSP -->IF YOU SELECT PAYPERIOD OPTION
                    {                        
                        foreach($filter_data['pay_period_ids'] AS $id)
                        {                            
                            if($id > 0)
                            {
                                $payperiod_string.= $pay_period_options[$id].', ';
                            }
                            else
                            {
                                $payperiod_string = "ALL  ";//if selected pay period is "All"  then only print "ALL" do not need to print all the pay period values. put 2 space after the "ALL"
                            }							
                        }
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the last space
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the comma(',')							
                    }
                                  
			if ( isset($rows) AND isset($filter_columns) ) {

				Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $pslf = new PayStubListFactory();//new code                                
                                $output = $pslf->Array2PDF($rows, $filter_columns, $current_user, $current_company, 	                                          $filter_data['transaction_start_date'], $filter_data['transaction_end_date'],                                          $payperiod_string);//new code                               
                                                                                               
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }                        
                        }else {
                                echo _('No PDF Data To Export!') ."<br>\n";                                    
                                }
                }		
				
				
				
                /*
                 * ARSP ADDED NEW CODE HERE...
                 * ARSP EDIT --> ADD NEW CODE FOR Export PDF REPORT
                 * PAGE ORIENTATION IS LANDSCAPE 
                 */        
				 //PAGE ORIENTATION IS LANDSCAPE               
                else if ( $action == 'export' AND $filter_data['export_type'] == 'pdfl' ) {
                    
                    
                    $payperiod_string= "";
                    if($filter_data['date_type'] == 'pay_period_ids')//ARSP -->IF YOU SELECT PAYPERIOD OPTION
                    {                        
                        foreach($filter_data['pay_period_ids'] AS $id)
                        {                            
                            if($id > 0)
                            {
                                $payperiod_string.= $pay_period_options[$id].', ';
                            }
                            else
                            {
                                $payperiod_string = "ALL  ";//if selected pay period is "All"  then only print "ALL" do not need to print all the pay period values, put 2 space after the "ALL"
                            }							
                        }
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the last space
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the comma(',')							
                    }
                                  
			if ( isset($rows) AND isset($filter_columns) ) {

				Debug::Text('Exporting as PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $pslf = new PayStubListFactory();//new code                                
                                $output = $pslf->Array2PDFLandscape($rows, $filter_columns, $current_user, $current_company, 	                                          $filter_data['transaction_start_date'], $filter_data['transaction_end_date'],                                          $payperiod_string);//new code                               
                                                                                               
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('pay_stub.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }                        
                        }else {
                                echo _('No PDF Data To Export!') ."<br>\n";                                    
                                }
                }
				
				
				

                /*
                 * ARSP ADD NEW CODE HERE...
                 * ARSP EDIT --> ADD NEW CODE CREATE FORM C
                 * PAGE ORIENTATION IS PORTRAIT
                 */      
                //PAGE ORIENTATION IS PORTRAIT 
                else if ( $action == 'export' AND $filter_data['export_type'] == 'formc' ) {
                    
                    $payperiod_string= "";
                    if($filter_data['date_type'] == 'pay_period_ids')
                    {
                        
                        foreach($filter_data['pay_period_ids'] AS $id)
                        {                            
                            if($id > 0)
                            {
                                $sub_string = substr($pay_period_options[$id],14); // this is the pay period format string(24) "26/03/2013 -> 25/04/2013"
                                $replace_string = str_replace("/", "-", $sub_string);// replace string '26/03/2013' to '26-03-2013'
                                
                                $date = new DateTime($replace_string);                                
                                $payperiod_string.= $date->format('F Y').', ';//only get Month Year Format eg:- April 2013
                            }
                            else
                            {
                                $payperiod_string = "ALL  ";//if selected pay period is "All"  then only print "ALL" do not need to print all the pay period values. put 2 space after the "ALL"
                            }

                            
                        }
//                                Print $payperiod_string;
//                                exit();
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the last space
                        $payperiod_string = substr_replace($payperiod_string ,"",-1);//remove the comma(',')							
                    

                                  
			if ( isset($rows) && $filter_data['include_user_ids']) {    

				Debug::Text('Exporting as Form C PDF', __FILE__, __LINE__, __METHOD__,10);
                                
                                $pslf = new PayStubListFactory();//new code                                
                                $output = $pslf->FormC($rows, $filter_data['include_user_ids'], $current_user, $current_company, $payperiod_string);//new code    
                                
                                   
                               
                                                                
                                if ( Debug::getVerbosity() < 11 ) {                                    
                                    Misc::FileDownloadHeader('form_c.pdf', 'application/pdf', strlen($output));
                                    echo $output;
                                    exit;                           
                                }                        
                        }else {
                                echo _('Please Select at Least One Employee.') ."<br>\n";                                    
                                }
                    }else{
                                echo _('Pleasae Select at Least One Pay Period.') ."<br>\n";                                    
                                }
                }  				
									
				
		
		
		
		
		 else {
			$smarty->assign_by_ref('generated_time', TTDate::getTime() );
			$smarty->assign_by_ref('pay_period_options', $pay_period_options );
			$smarty->assign_by_ref('filter_data', $filter_data );
			$smarty->assign_by_ref('columns', $filter_columns );
			$smarty->assign_by_ref('rows', $rows);

			$smarty->display('report/PayStubSummaryReport.tpl');
		}

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);
	default:
		BreadCrumb::setCrumb($title);

		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );
		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				//$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys((array)$pay_period_options)) );
				$filter_data['transaction_start_date'] = $default_transaction_start_date;
				$filter_data['transaction_end_date'] = $default_transaction_end_date;
				$filter_data['group_ids'] = array( -1 );
				$filter_data['currency_ids'] = array( -1 );
				$filter_data['pay_period_ids'] = array( '-0000-'.array_shift(array_keys($pay_period_options)) );
				//$filter_data['primary_group_by'] = '-1000-full_name';

				$default_columns = array( 0 => '-0800-epf_membership_no' ,
                                                          1 => '-1000-full_name', 
                                                          2 => '49', 
                                                          3 => '45', 
                                                          4 => '-0802-basic_for_epf', 
                                                          5 => '9', 
                                                          6 => '20', 
                                                          7 => '8', 
                                                          8 => '29', 
                                                          9 => '12', 
                                                          10 => '13', 
                                                          11 => '10', 
                                                          12 => '-1132-epf_20_persent', 
                                                          13 => '18', 
                                                          14 => '75' );

				$pseallf = new PayStubEntryAccountLinkListFactory();
				$pseallf->getByCompanyId( $current_company->getId() );
				if ( $pseallf->getRecordCount() > 0 ) {
					$pseal_obj = $pseallf->getCurrent();

//					$default_linked_columns = array(
//												$pseal_obj->getTotalGross(),
//												$pseal_obj->getTotalNetPay(),
//												$pseal_obj->getTotalEmployeeDeduction(),
//												$pseal_obj->getTotalEmployerDeduction() );
				} else {
					$default_linked_columns = array();
				}

				$filter_data['column_ids'] = Misc::prependArray( $default_columns, $default_linked_columns );

				$filter_data['primary_sort'] = '-1000-full_name';
				$filter_data['secondary_sort'] = '-1120-pay_stub_transaction_date';
			}
		}
		$filter_data = Misc::preSetArrayValues( $filter_data, array('include_user_ids', 'exclude_user_ids', 'user_status_ids', 'group_ids', 'branch_ids', 'department_ids', 'user_title_ids', 'pay_period_ids', 'currency_ids', 'column_ids' ), NULL );

		$ulf = new UserListFactory();
		$all_array_option = array('-1' => _('-- All --'));

		//Get include employee list.
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );
		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );

		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		$uglf = new UserGroupListFactory();
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		$blf = new BranchListFactory();
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		$dlf = new DepartmentListFactory();
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get pay periods
		//$pplf = new PayPeriodListFactory();
		//$pplf->getPayPeriodsWithPayStubsByCompanyId( $current_company->getId() );
		$pay_period_options = Misc::prependArray( $all_array_option, $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );
		$filter_data['src_pay_period_options'] = Misc::arrayDiffByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );
		$filter_data['selected_pay_period_options'] = Misc::arrayIntersectByKey( (array)$filter_data['pay_period_ids'], $pay_period_options );

		//Get currencies
		$crlf = new CurrencyListFactory();
		$crlf->getByCompanyId( $current_company->getId() );
		$currency_options = Misc::prependArray( $all_array_option, $crlf->getArrayByListFactory( $crlf, FALSE, TRUE ) );
		$filter_data['src_currency_options'] = Misc::arrayDiffByKey( (array)$filter_data['currency_ids'], $currency_options );
		$filter_data['selected_currency_options'] = Misc::arrayIntersectByKey( (array)$filter_data['currency_ids'], $currency_options );

		//Get column list
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );


		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$filter_data['group_by_options'] = Misc::prependArray( array('0' => _('No Grouping')), $static_columns );

		$psf = new PayStubFactory();
		//ARSP EDIT --> ADD Some New code('pdf' => _('PDF (PDF)')) ) for new 'pdf' dropdown list from export type  
		$filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => _('CSV (Excel)'), 'pdfp' => _('PDF (PORTRAIT)'), 'pdfl' => _('PDF (LANDSCAPE)'), 'formc' => _('Form C (PDF)'), 'payslip3' => _('3 Payslip/Page (PDF)'), 'payslip4' => _('4 Payslip/Page (Landscape PDF)')), Misc::trimSortPrefix( $psf->getOptions('export_type') ) );
		//$filter_data['export_type_options'] = Misc::prependArray( array( 'csv' => _('CSV (Excel)') ), Misc::trimSortPrefix( $psf->getOptions('export_type') ) );

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/PayStubSummary.tpl');

		break;
}
?>