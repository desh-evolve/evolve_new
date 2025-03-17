<?php

namespace App\Models\Report;

class PayrollExportReport extends TimesheetSummaryReport {

	function __construct() {
		$this->title = ('Payroll Export Report');
		$this->file_name = 'payroll_export';

		//Don't call TimesheetSummaryReport __construct(), skip one level lower to the Report class instead.
		Report::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_payroll_export', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'export_type':
				$retval = array(
								0 => ('-- Please Choose --'),
								'adp' 				=> ('ADP'),
								'paychex_preview' 	=> ('Paychex Preview'),
								'paychex_online' 	=> ('Paychex Online Payroll'),
								'ceridian_insync' 	=> ('Ceridian Insync'),
								'millenium' 		=> ('Millenium'),
								'quickbooks' 		=> ('QuickBooks Pro'),
								'surepayroll' 		=> ('SurePayroll'),
								'chris21' 			=> ('Chris21'),
								'csv' 				=> ('Generic Excel/CSV'),
								//'other' 			=> ('-- Other --'),
								);
				break;
			case 'export_policy':
				$static_columns = array();

				$columns = array(					'-0010-regular_time' => ('Regular Time'),
													);

				$columns = Misc::prependArray( $static_columns, $columns);

				//Get all Overtime policies.
				$otplf = new OverTimePolicyListFactory();
				$otplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $otplf->getRecordCount() > 0 ) {
					foreach ($otplf as $otp_obj ) {
						$otp_columns['-0020-over_time_policy-'.$otp_obj->getId()] = ('Overtime').': '.$otp_obj->getName();
					}

					$columns = array_merge( $columns, $otp_columns);
				}

				//Get all Premium policies.
				$pplf = new PremiumPolicyListFactory();
				$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach ($pplf as $pp_obj ) {
						$pp_columns['-0030-premium_policy-'.$pp_obj->getId()] = ('Premium').': '.$pp_obj->getName();
					}

					$columns = array_merge( $columns, $pp_columns);
				}


				//Get all Absence Policies.
				$aplf = new AbsencePolicyListFactory();
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach ($aplf as $ap_obj ) {
						$ap_columns['-0040-absence_policy-'.$ap_obj->getId()] = ('Absence').': '.$ap_obj->getName();
					}

					$columns = array_merge( $columns, $ap_columns);
				}

				$retval = $columns;
				break;
			case 'default_hour_codes':
				$export_type = $this->getOptions('export_type');
				$export_policy = Misc::trimSortPrefix( $this->getOptions('export_policy') );

				foreach( $export_type as $type => $name ) {
					switch( strtolower($type) ) {
						case 'paychex_online':
							foreach( $export_policy as $id => $name ) {
								if ( strpos( $id, 'regular') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Regular';
								} elseif ( strpos( $id, 'over_time') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Overtime';
								} elseif ( strpos( $id, 'absence') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'Absence';
								}
							}

							break;
						default:
							if ( $type === 0 ) {
								continue;
							}

							foreach( $export_policy as $id => $name ) {
								if ( strpos( $id, 'regular') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'REG';
								} elseif ( strpos( $id, 'over_time') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'OT';
								} elseif ( strpos( $id, 'absence') !== FALSE ) {
									$retval[$type]['columns'][$id]['hour_code'] = 'ABS';
								}
							}
						break;
					}
				}
				break;
			case 'hour_column_name':
				$hour_column_name_map = array(
								'adp' 				=> ('ADP Hours Code'),
								'paychex_preview' 	=> ('Paychex Hours Code'),
								'paychex_online' 	=> ('Paychex Hours Code'),
								'ceridian_insync' 	=> ('Ceridian Hours Code'),
								'millenium' 		=> ('Millenium Hours Code'),
								'quickbooks' 		=> ('Quickbooks Payroll Item Name'),
								'surepayroll' 		=> ('Payroll Code'),
								'csv' 				=> ('Hours Code'),
								);

				if (  isset($params['export_type']) AND isset($hour_column_name_map[$params['export_type']]) ) {
					$retval = $hour_column_name_map[$params['export_type']];
				} else {
					$retval = $hour_column_name_map['csv'];
				}
				break;
			case 'adp_hour_column_options':
				$retval['adp_hour_column_options'][0] = ('-- DO NOT EXPORT --');
				$retval['adp_hour_column_options']['-0010-regular_time'] = ('Regular Time');
				$retval['adp_hour_column_options']['-0020-overtime'] = ('Overtime');
				for ( $i=3; $i <= 4; $i++ ) {
					$retval['adp_hour_column_options']['-003'.$i.'-'.$i] = ('Hours') .' '. $i;
				}
				break;
			case 'adp_company_code_options':
			case 'adp_batch_options':
			case 'adp_temp_dept_options':
				$retval = array(
								0 => ('-- Custom --'),
								'-0010-default_branch_manual_id' => ('Default Branch: Code'),
								'-0020-default_department_manual_id' => ('Default Department: Code'),
								'-0030-branch_manual_id' => ('Branch: Code'),
								'-0040-department_manual_id' => ('Department: Code'),
								);

				$oflf = new OtherFieldListFactory();

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', ('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', ('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', ('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', ('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, $branch_options, $department_options );
				break;
			case 'quickbooks_proj_options':
				$retval = array(
								0 => ('-- NONE --'),
								'default_branch' => ('Default Branch'),
								'default_department' => ('Default Department'),
								'group' => ('Group'),
								'title' => ('Title'),
								);
				break;
			default:
				return parent::_getOptions( $name, $params );
				break;
		}

		return $retval;
	}

	function getExportTypeTemplate( $config, $format ) {

		if ( $format == 'payroll_export' ) {
			unset($config['columns'],$config['group'],$config['sort'],$config['sub_total']);
			$config['other']['disable_grand_total'] = TRUE; //Disable grand totals.

			if ( isset($config['form']['export_type']) ) {
				$export_type = $config['form']['export_type'];

				switch( strtolower($export_type) ) {
					case 'adp':
						//$setup_data = $this->getFormConfig(); //get setup data to determine PROJ field.
						$setup_data = $config['form']; //get setup data to determine custom formats...

						$config['columns'][] = 'default_branch_id';
						$config['columns'][] = 'default_department_id';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'default_branch_id';
						$config['group'][] = 'default_department_id';
						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['group'][] = 'employee_number';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['company_code'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['batch_id'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] ) => 'asc' );
						}
						$config['sort'][] = array('employee_number' => 'asc' );
						break;
					case 'paychex_preview':
					case 'paychex_online':
					case 'millenium':
					case 'ceridian_insync':
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'employee_number';

						$config['sort'][] = array('employee_number' => 'asc');
						break;
					case 'quickbooks':
						$setup_data = $this->getFormConfig(); //get setup data to determine PROJ field.

						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) ) {
							$config['columns'][] = $setup_data['quickbooks']['proj'];
						}

						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';
						$config['group'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) ) {
							$config['group'][] = $setup_data['quickbooks']['proj'];
						}

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
						break;
					case 'surepayroll':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
						break;
					case 'chris21':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc');
						break;
					case 'csv':
						//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
						$config['columns'][] = 'full_name';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'default_branch';
						$config['columns'][] = 'default_department';
						$config['columns'][] = 'pay_period';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'full_name';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'default_branch';
						$config['group'][] = 'default_department';
						$config['group'][] = 'pay_period';

						$config['sort'][] = array('full_name' => 'asc', 'employee_number' => 'asc', 'default_branch' => 'asc', 'default_department' => 'asc', 'pay_period' => 'asc');
						break;
				}
				Debug::Arr($config, 'Export Type Template: '. $export_type, __FILE__, __LINE__, __METHOD__,10);
			} else {
				Debug::Text('No Export Type defined, not modifying config...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $config;
	}

	//Short circuit this function, as no postprocessing is required for exporting the data.
	function _postProcess( $format = NULL ) {
		if ( $format == 'payroll_export' ) {
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	function _outputPayrollExport( $format = NULL ) {
		$setup_data = $this->getFormConfig();

		Debug::Text('Generating Payroll Export... Format: '. $format, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($setup_data['export_type']) ) {
			Debug::Text('Export Type: '. $setup_data['export_type'], __FILE__, __LINE__, __METHOD__,10);
		} else {
			Debug::Text('No Export Type defined!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
		Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__,10);
		$rows = $this->data;
		Debug::Arr($rows, 'PreData: ', __FILE__, __LINE__, __METHOD__,10);

		$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.txt';
		$mime_type = 'application/text';
		$data = NULL;

		switch( strtolower(trim($setup_data['export_type'])) ) {
			case 'adp': //ADP export format.
				//File format supports multiple rows per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everything on row.
				$export_column_map = array(
										'company_code' => 'Co Code',
										'batch_id' => 'Batch ID',
										'temp_dept' => 'Temp Dept',
										'employee_number' =>  'File #',
										'regular_time' => 'Reg Hours',
										'overtime' => 'O/T Hours',
										'3_code' => 'Hours 3 Code',
										'3_amount' => 'Hours 3 Amount',
										'4_code' => 'Hours 4 Code',
										'4_amount' => 'Hours 4 Amount',
										);

				ksort($setup_data['adp']['columns']);
				$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

				foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
					$column_name = NULL;
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$export_data_map[$column_id] = 'regular_time';
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$export_data_map[$column_id] = 'overtime';
					} elseif ( $column_data['hour_column'] >= 3 ) {
						$export_data_map[$column_id] = $column_data;
					}
				}

				if ( !isset($setup_data['adp']['company_code_value']) ) {
					$setup_data['adp']['company_code_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['batch_id_value']) ) {
					$setup_data['adp']['batch_id_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['temp_dept_value']) ) {
					$setup_data['adp']['temp_dept_value'] = NULL;
				}

				$company_code_column = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
				$batch_id_column = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
				$temp_dept_column = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
				foreach($rows as $row) {
					$static_columns = array(
										'company_code' => ( isset($row[$company_code_column]) ) ? $row[$company_code_column] : $setup_data['adp']['company_code_value'],
										'batch_id' => ( isset($row[$batch_id_column]) ) ? $row[$batch_id_column] : $setup_data['adp']['batch_id_value'],
										'temp_dept' => ( isset($row[$temp_dept_column]) ) ? $row[$temp_dept_column] : $setup_data['adp']['temp_dept_value'],
										'employee_number' => str_pad( $row['employee_number'], 6, 0, STR_PAD_LEFT), //ADP employee numbers should always be 6 digits.
										);

					foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
						$column_data = Misc::trimSortPrefix( $column_data, TRUE );
						Debug::Text('ADP Column ID: '. $column_id .' Hour Column: '. $column_data['hour_column'] .' Code: '. $column_data['hour_code'], __FILE__, __LINE__, __METHOD__,10);
						if ( isset( $row[$column_id] ) AND $column_data['hour_column'] != '0' ) {
							foreach( $export_column_map as $export_column_id => $export_column_name ) {
								Debug::Arr($row, 'Row: Column ID: '. $column_id .' Export Column ID: '. $export_column_id .' Name: '. $export_column_name, __FILE__, __LINE__, __METHOD__,10);

								if ( ( $column_data['hour_column'] == $export_column_id OR $column_data['hour_column'].'_code' == $export_column_id )
										AND !in_array( $export_column_id, array('company_code','batch_id','temp_dept', 'employee_number')) ) {
									if ( (int)substr( $export_column_id, 0, 1 ) > 0 ) {
										$tmp_row[$column_data['hour_column'].'_code'] = $column_data['hour_code'];
										$tmp_row[$column_data['hour_column'].'_amount'] = TTDate::getTimeUnit( $row[$column_id], 20 );
									} else {
										$tmp_row[$export_column_id] = TTDate::getTimeUnit( $row[$column_id], 20 );
									}

									//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
									$tmp_rows[] = array_merge( $static_columns, $tmp_row );
									unset($tmp_row);
								}
							}
						}
					}
				}

				$file_name = 'EPI000000.csv';
				if ( isset( $tmp_rows) ) {
					//File format supports multiple entries per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everyone one row.
					$file_name = 'EPI'. $tmp_rows[0]['company_code'] . $tmp_rows[0]['batch_id'] .'.csv';

					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'adp_old': //ADP export format.
				$file_name = 'EPI'. $setup_data['adp']['company_code'] . $setup_data['adp']['batch_id'] .'.csv';

				$export_column_map = array();
				$static_export_column_map = array(
										'company_code' => 'Co Code',
										'batch_id' => 'Batch ID',
										'employee_number' =>  'File #',
										);

				$static_export_data_map = array(
								'company_code' => $setup_data['adp']['company_code'],
								'batch_id' => $setup_data['adp']['batch_id'],
								);

				//
				//Format allows for multiple duplicate columns.
				//ie: Hours 3 Code, Hours 3 Amount, Hours 3 Code, Hours 3 Amount, ...
				//However, we can only have a SINGLE O/T Hours column.
				//We also need to combine hours with the same code together.
				//
				ksort($setup_data['adp']['columns']);
				$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

				foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
					$column_name = NULL;
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$column_name = 'Reg Hours';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$column_name = 'O/T Hours';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					} elseif ( $column_data['hour_column'] >= 3 ) {
						$column_name = 'Hours '. $column_data['hour_column'] .' Amount';
						$export_column_map[$setup_data['adp']['columns'][$column_id]['hour_code'].'_code'] = 'Hours '. $column_data['hour_column'] .' Code';
						$export_data_map[$column_id] = trim($setup_data['adp']['columns'][$column_id]['hour_code']);
					}

					if ( $column_name != '' ) {
						$export_column_map[trim($setup_data['adp']['columns'][$column_id]['hour_code'])] = $column_name;
					}
				}
				$export_column_map = Misc::prependArray( $static_export_column_map, $export_column_map);

				//
				//Combine time from all columns with the same hours code.
				//
				$i=0;
				foreach($rows as $row) {
					foreach ( $static_export_column_map as $column_id => $column_name ) {
						if ( isset($static_export_data_map[$column_id]) ) {
							//Copy over static config values like company code/batch_id.
							$tmp_rows[$i][$column_id] = $static_export_data_map[$column_id];
						} elseif( isset($row[$column_id]) ) {
							if ( isset($static_export_column_map[$column_id]) ) {
								//Copy over employee_number. (File #)
								$tmp_rows[$i][$column_id] = $row[$column_id];
							}
						}
					}

					foreach ( $export_data_map as $column_id => $column_name ) {
						if ( !isset($tmp_rows[$i][$column_name]) ) {
							$tmp_rows[$i][$column_name] = 0;
						}

						if ( isset($row[$column_id]) ) {
							$tmp_rows[$i][$column_name] += $row[$column_id];
						}
						$tmp_rows[$i][$column_name.'_code']  = $column_name;
					}

					$i++;
				}

				//Convert time from seconds to hours.
				$convert_unit_columns = array_keys($static_export_column_map);

				foreach( $tmp_rows as $row => $data ) {
					foreach( $data as $column_id => $column_data ) {
						//var_dump($column_id,$column_data);
						if ( is_int($column_data) AND !in_array( $column_id, $convert_unit_columns ) ) {
							$tmp_rows[$row][$column_id] = TTDate::getTimeUnit( $column_data, 20 );
						}
					}
				}
				unset($row, $data, $column_id, $column_data);

				$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );

				break;
			case 'paychex_preview': //Paychex Preview export format.
				//Add an advanced PayChex Previous format that supports rates perhaps?
				if ( !isset($setup_data['paychex_preview']['client_number']) ) {
					$setup_data['paychex_preview']['client_number'] = '0000';
				}

				$file_name = $setup_data['paychex_preview']['client_number'] .'_TA.txt';

				ksort($setup_data['paychex_preview']['columns']);
				$setup_data['paychex_preview']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview']['columns'] );

				$data = NULL;
				foreach($rows as $row) {
					foreach( $setup_data['paychex_preview']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
							$data .= str_pad('E'. str_pad( $column_data['hour_code'], 2, 0, STR_PAD_LEFT) , 47, ' ', STR_PAD_LEFT);
							$data .= str_pad( str_pad( TTDate::getTimeUnit( $row[$column_id], 20 ), 8, 0, STR_PAD_LEFT) , 17, ' ', STR_PAD_LEFT)."\n";
						}
					}
				}
				break;
			case 'paychex_online': //Paychex Online Payroll CSV
				ksort($setup_data['paychex_online']['columns']);
				$setup_data['paychex_online']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_online']['columns'] );

				$earnings = array();
				//Find all the hours codes
				foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
					$hour_code = $column_data['hour_code'];
					$earnings[] = $hour_code;
				}

				$export_column_map['employee_number'] = '';
				foreach($earnings as $key => $value) {
					$export_column_map[$value] = '';
				}

				$i=0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_row['employee_number'] = 'Employee Number';
						foreach($earnings as $key => $value) {
							$tmp_row[$value] = $value . ' Hours';
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_row);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						$tmp_row['employee_number'] = $row['employee_number'];
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_row[$hour_code] = TTDate::getTimeUnit($hours, 20);
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_hour_codes, $hour_code, $hours, $tmp_row);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'millenium': //Millenium export format. Also used by Qqest.
				ksort($setup_data['millenium']['columns']);
				$setup_data['millenium']['columns'] = Misc::trimSortPrefix( $setup_data['millenium']['columns'] );

				$export_column_map = array('employee_number' => '', 'transaction_code' => '', 'hour_code' => '', 'hours' => '');
				foreach($rows as $row) {
					foreach( $setup_data['millenium']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employee_number' => $row['employee_number'],
												'transaction_code' => 'E',
												'hour_code' => trim($column_data['hour_code']),
												'hours' => TTDate::getTimeUnit( $row[$column_id], 20 )
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;

			case 'ceridian_insync': //Ceridian InSync export format. Needs to be .IMP to import? DOS line endings?
				if ( !isset($setup_data['ceridian_insync']['employer_number']) OR $setup_data['ceridian_insync']['employer_number'] == '' ) {
					$setup_data['ceridian_insync']['employer_number'] = '0001';
				}

				$file_name = strtolower(trim($setup_data['export_type'])).'_'. $setup_data['ceridian_insync']['employer_number'] .'_'. date('Y_m_d').'.imp';

				ksort($setup_data['ceridian_insync']['columns']);
				$setup_data['ceridian_insync']['columns'] = Misc::trimSortPrefix( $setup_data['ceridian_insync']['columns'] );

				$export_column_map = array(	'employer_number' => '', 'import_type_id' => '', 'employee_number' => '', 'check_type' => '',
											'hour_code' => '', 'value' => '', 'distribution' => '', 'rate' => '', 'premium' => '', 'day' => '', 'pay_period' => '');
				foreach($rows as $row) {
					foreach( $setup_data['ceridian_insync']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employer_number' => $setup_data['ceridian_insync']['employer_number'], //Employer No./Payroll Number
												'import_type_id' => 'COSTING', //This can change, must be configurable.
												'employee_number' => str_pad( $row['employee_number'], 9, '0', STR_PAD_LEFT),
												'check_type' => 'REG',
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
												'distribution' => NULL,
												'rate' => NULL, //This overrides whats in ceridian and seems to cause problems.
												//'rate' => ( isset($row[$column_id.'_hourly_rate']) ) ? $row[$column_id.'_hourly_rate'] : NULL,
												'premium' => NULL,
												'day' => NULL,
												'pay_period' => NULL,
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE, "\r\n" ); //Use DOS line endings only.
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

				break;
			case 'quickbooks': //Quickbooks Pro export format.
				$file_name = 'payroll_export.iif';

				ksort($setup_data['quickbooks']['columns']);
				$setup_data['quickbooks']['columns'] = Misc::trimSortPrefix( $setup_data['quickbooks']['columns'] );

				//
				// Quickbooks header
				//
				/*
					Company Create Time can be found by first running an Timer Activity export in QuickBooks and viewing the output.

					PITEM field needs to be populated, as that is the PAYROLL ITEM in quickbooks. It can be the same as the ITEM field.
					PROJ could be mapped to the default department/branch?
				*/
				$data =  "!TIMERHDR\tVER\tREL\tCOMPANYNAME\tIMPORTEDBEFORE\tFROMTIMER\tCOMPANYCREATETIME\n";
				$data .= "TIMERHDR\t8\t0\t". trim($setup_data['quickbooks']['company_name']) ."\tN\tY\t". trim($setup_data['quickbooks']['company_created_date']) ."\n";
				$data .= "!TIMEACT\tDATE\tJOB\tEMP\tITEM\tPITEM\tDURATION\tPROJ\tNOTE\tXFERTOPAYROLL\tBILLINGSTATUS\n";

				foreach($rows as $row) {
					foreach( $setup_data['quickbooks']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Make sure employee name is in format: LastName, FirstName MiddleInitial
							$tmp_employee_name = $row['last_name'].', '. $row['first_name'];
							if ( isset($row['middle_name']) AND strlen($row['middle_name']) > 0 ) {
								$tmp_employee_name .= ' '.substr(trim($row['middle_name']),0,1);
							}

							$proj = NULL;
							if ( isset($row[$setup_data['quickbooks']['proj']]) ) {
								$proj = $row[$setup_data['quickbooks']['proj']];
							}

							$data .= "TIMEACT\t". date('n/j/y', $row['pay_period_end_date'])."\t\t". $tmp_employee_name ."\t". trim($column_data['hour_code']) ."\t". trim($column_data['hour_code']) ."\t".  TTDate::getTimeUnit( $row[$column_id], 10 ) ."\t". $proj ."\t\tY\t0\n";
							unset($tmp_employee_name);
						}
					}
				}

				break;
			case 'surepayroll': //SurePayroll Export format.
				ksort($setup_data['surepayroll']['columns']);
				$setup_data['surepayroll']['columns'] = Misc::trimSortPrefix( $setup_data['surepayroll']['columns'] );

				//
				//header
				//
				$data = 'TC'."\n";
				$data .= '00001'."\n";

				$export_column_map = array(	'pay_period_end_date' => 'Entry Date',
											'employee_number' => 'Employee Number',
											'last_name' => 'Last Name',
											'first_name' => 'First Name',
											'hour_code' => 'Payroll Code',
											'value' => 'Hours' );

				foreach($rows as $row) {
					foreach( $setup_data['surepayroll']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data,'Output2', __FILE__, __LINE__, __METHOD__,10);
							$tmp_rows[] = array(
												'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
												'employee_number' => $row['employee_number'],
												'last_name' => $row['last_name'],
												'first_name' => $row['first_name'],
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id], 20 ),
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data .= Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
					$data = str_replace('"','', $data);
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;

			case 'chris21': //Chris21 Export format.
				//Columns required: Employee_number (2), Date (10), ADJUSTMENT_CODE (12), HOURS (13), SIGNED_HOURS(15)[?]
				//Use SIGNED_HOURS only, as it provides more space?
				ksort($setup_data['chris21']['columns']);
				$setup_data['chris21']['columns'] = Misc::trimSortPrefix( $setup_data['chris21']['columns'] );

				$data = '';
				foreach($rows as $row) {
					foreach( $setup_data['chris21']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data,'Output2', __FILE__, __LINE__, __METHOD__,10);
							$data .= str_repeat(' ', 8); 															//8 digits Blank
							$data .= str_pad( substr( $row['employee_number'], 0, 7), 7, ' ', STR_PAD_RIGHT); 		//7 digits
							$data .= str_repeat(' ', 11); 															//14 digits Blank
							$data .= date('dmy', $row['pay_period_end_date']);										//4 digits Date
							$data .= str_repeat(' ', 4); 															//4 digits Blank
							$data .= str_pad( substr( trim($column_data['hour_code']), 0, 4), 4, ' ', STR_PAD_RIGHT);//4 digits
							$data .= '0000'; 																		//4 digits HOURS field, always be 0, use SIGNED_HOURS instead.
							$data .= str_repeat(' ', 4); 															//4 digits Blank
							$data .= str_pad( str_replace('.','', TTDate::getTimeUnit( $row[$column_id], 20 ) ), 6, 0, STR_PAD_LEFT); //Hours without decimal padded to 6 digits.
							$data .= '+000000000'; 																	//Filler
							$data .= "\n";
						}
					}
				}
				unset($tmp_rows, $column_id, $column_data, $rows, $row);
				break;

			case 'csv': //Generic CSV.
				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				ksort($setup_data['csv']['columns']);
				$setup_data['csv']['columns'] = Misc::trimSortPrefix( $setup_data['csv']['columns'] );

				$export_column_map = array('employee' => '', 'employee_number' => '', 'default_branch' => '', 'default_department' => '', 'pay_period' => '', 'hour_code' => '', 'hours' => '');

				$i=0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_rows[] = array(
											'employee' => 'Employee',
											'employee_number' => 'Employee Number',
											'default_branch' => 'Default Branch',
											'default_department' => 'Default Department',
											'pay_period' => 'Pay Period',
											'hour_code' => 'Hours Code',
											'hours' => 'Hours',
											);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['csv']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_rows[] = array(
												'employee' => $row['full_name'],
												'employee_number' => $row['employee_number'],
												'default_branch' => $row['default_branch'],
												'default_department' => $row['default_department'],
												'pay_period' => $row['pay_period']['display'],
												'hour_code' => $hour_code,
												'hours' => TTDate::getTimeUnit($hours, 20 ),
												);
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
		}

		//Debug::Arr($data, 'Export Data: ', __FILE__, __LINE__, __METHOD__,10);
		return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $data );
	}

	function _output( $format = NULL ) {
		//Get Form Config data, which can use for the export config.
		if ( $format == 'payroll_export' ) {
			return $this->_outputPayrollExport( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
