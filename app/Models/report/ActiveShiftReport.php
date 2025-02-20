<?php

namespace App\Models\Report;

class ActiveShiftReport extends Report {

	function __construct() {
		$this->title = TTi18n::getText('Whos In Summary');
		$this->file_name = 'whos_in_summary';

		parent::__construct();

		return TRUE;
	}

	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report','enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report','view_active_shift', $user_id, $company_id ) ) {
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
										//'time_period',
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
				/*
				$retval = array_merge(
									TTDate::getReportDateOptions( 'time_stamp', TTi18n::getText('Punch Time'), 19, FALSE ),
									array()
								);
				*/
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),

										'-1010-user_name' => TTi18n::gettext('User Name'),
										'-1020-phone_id' => TTi18n::gettext('PIN/Phone ID'),

										'-1030-employee_number' => TTi18n::gettext('Employee #'),

										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-user_group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Branch'), //abbreviate for space
										'-1100-default_department' => TTi18n::gettext('Department'), //abbreviate for space
										'-1110-currency' => TTi18n::gettext('Currency'),

										'-1200-permission_control' => TTi18n::gettext('Permission Group'),
										'-1210-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1220-policy_group' => TTi18n::gettext('Policy Group'),

										'-1310-sex' => TTi18n::gettext('Sex'),
										'-1320-address1' => TTi18n::gettext('Address 1'),
										'-1330-address2' => TTi18n::gettext('Address 2'),

										'-1340-city' => TTi18n::gettext('City'),
										'-1350-province' => TTi18n::gettext('Province/State'),
										'-1360-country' => TTi18n::gettext('Country'),
										'-1370-postal_code' => TTi18n::gettext('Postal Code'),
										'-1380-work_phone' => TTi18n::gettext('Work Phone'),
										'-1391-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1400-home_phone' => TTi18n::gettext('Home Phone'),
										'-1410-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1420-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1430-home_email' => TTi18n::gettext('Home Email'),
										'-1440-work_email' => TTi18n::gettext('Work Email'),
										'-1740-time_zone_display' => TTi18n::gettext('Time Zone'),

										'-1801-type' => TTi18n::gettext('Type'),
										'-1802-status' => TTi18n::gettext('Status'),
										'-1810-branch' => TTi18n::gettext('Branch'),
										'-1820-department' => TTi18n::gettext('Department'),
										'-1830-station_type' => TTi18n::gettext('Station Type'),
										'-1840-station_station_id' => TTi18n::gettext('Station ID'),
										'-1850-station_source' => TTi18n::gettext('Station Source'),
										'-1860-station_description' => TTi18n::gettext('Station Description'),

										'-1900-time_stamp' => TTi18n::gettext('Punch Time'),
										'-1910-actual_time_stamp' => TTi18n::gettext('Actual Punch Time'),
										'-2010-note' => TTi18n::gettext('Note'),

							   );

				//$retval = array_merge( $retval, $this->getOptions('date_columns') );
				//ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										'-2000-total_user' => TTi18n::gettext('Total Employees'), //Group counter...
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
						if ( strpos($column, 'wage') !== FALSE OR strpos($column, 'hourly_rate') !== FALSE ) {
							$retval[$column] = 'currency';
						}
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
								if ( strpos($column, 'hourly_rate') !== FALSE OR strpos($column, 'wage') !== FALSE ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = array(
										'-1010-by_status_by_employee' => TTi18n::gettext('Punches By Status'),
										'-1020-by_type_by_employee' => TTi18n::gettext('Punches By Type'),
										'-1030-by_status_by_type_by_employee' => TTi18n::gettext('Punches By Status/Type'),
										'-1040-by_type_by_status_by_employee' => TTi18n::gettext('Punches By Type/Status'),

										'-1050-by_employee' => TTi18n::gettext('Punches By Employee'),

										'-1060-by_default_branch_by_employee' => TTi18n::gettext('Punches By Default Branch'),
										'-1070-by_default_department_by_employee' => TTi18n::gettext('Punches By Default Department'),
										'-1080-by_default_branch_by_default_department_by_employee' => TTi18n::gettext('Punches By Default Branch/Department'),

										'-1090-by_branch_by_employee' => TTi18n::gettext('Punches By Branch'),
										'-1100-by_department_by_employee' => TTi18n::gettext('Punches By Department'),
										'-1110-by_branch_by_department_by_employee' => TTi18n::gettext('Punches By Branch/Department'),

										'-1120-by_station_by_employee' => TTi18n::gettext('Punches By Station'),
										'-1130-by_station_type_by_employee' => TTi18n::gettext('Punches By Station Type'),

										//'-1230-by_branch+total_user' => TTi18n::gettext('Total Employees By Branch'),
							   );

				break;
			case 'template_config':
				$retval['-1010-time_period']['time_period'] = 'last_7_days'; //Default to just the last 7 days to speed up the query.

				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'by_employee':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						case 'by_default_branch_by_employee':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_default_department_by_employee':
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_default_branch_by_default_department_by_employee':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('default_branch' => 'asc');
							$retval['sort'][] = array('default_department' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;


						case 'by_branch_by_employee':
							$retval['columns'][] = 'branch';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('branch' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_department_by_employee':
							$retval['columns'][] = 'department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('department' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_branch_by_department_by_employee':
							$retval['columns'][] = 'branch';
							$retval['columns'][] = 'department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('branch' => 'asc');
							$retval['sort'][] = array('department' => 'asc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_status_by_employee':
							$retval['columns'][] = 'status';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_type_by_employee':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_status_by_type_by_employee':
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('type' => 'desc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_type_by_status_by_employee':
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('type' => 'desc');
							$retval['sort'][] = array('status' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;

						case 'by_station_by_employee':
							$retval['columns'][] = 'station_description';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('station_description' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						case 'by_station_type_by_employee':
							$retval['columns'][] = 'station_type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = array('station_type' => 'asc');
							$retval['sort'][] = array('last_name' => 'asc');
							$retval['sort'][] = array('first_name' => 'asc');
							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__,10);
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
		$this->tmp_data = array('user' => array(), 'user_preference' => array(), 'punch' => array(), 'total_user' => array() );

		$columns = $this->getColumnConfig();
		$filter_data = $this->getFilterConfig();

		if ( $this->getPermissionObject()->Check('user','view') == FALSE ) {
			$hlf = TTnew( 'HierarchyListFactory' );
			$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getUserObject()->getCompany(), $this->getUserObject()->getID() );
			Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = array();
		}
		if ( $this->getPermissionObject()->Check('user','view') == FALSE ) {
			if ( $this->getPermissionObject()->Check('user','view_child') == FALSE ) {
				$permission_children_ids = array();
			}
			if ( $this->getPermissionObject()->Check('user','view_own') ) {
				$permission_children_ids[] = $this->getUserObject()->getID();
			}

			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__,10);

		//
		//FIXME: Figure out way to only show users with punches if they specify that. Perhaps some sort of array intersect?
		//

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			//$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnConfig() );
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->data;
			//$this->tmp_data['user'][$u_obj->getId()]['status'] = Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions( 'status' ) );

			$this->tmp_data['user_preference'][$u_obj->getId()] = array();

			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}


		//Get user preference data for joining.
		$uplf = TTnew( 'UserPreferenceListFactory' );
		$uplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Preference Rows: '. $uplf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $uplf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $uplf as $key => $up_obj ) {
			$this->tmp_data['user_preference'][$up_obj->getUser()] = (array)$up_obj->getObjectAsArray( $this->getColumnConfig() );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user_preference'], 'TMP Data: ', __FILE__, __LINE__, __METHOD__,10);

		//Get last punch (active shift) data for joining with users. That way we can full data from both tables.
		$plf = TTnew( 'PunchListFactory' );
		$plf->getAPIActiveShiftReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' Active Shift Rows: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $plf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $plf as $key => $p_obj ) {
			$this->tmp_data['punch'][$p_obj->getColumn('user_id')] = (array)$p_obj->getObjectAsArray( $this->getColumnConfig() );
			if ( $p_obj->getStatus() == 10 ) {
				$this->tmp_data['punch'][$p_obj->getColumn('user_id')]['_bgcolor'] = array(225,255,225);
				//$this->tmp_data['punch'][$p_obj->getColumn('user_id')]['_fontcolor'] = array(25,225,25); //Green
			} else {
				$this->tmp_data['punch'][$p_obj->getColumn('user_id')]['_bgcolor'] = array(255,225,225);
				//$this->tmp_data['punch'][$p_obj->getColumn('user_id')]['_fontcolor'] = array(225,25,25); //Red
			}
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['punch'], 'TMP Data (punch): ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['punch']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Use the punch data is the primary dataset and merge user/user preference data to it. This will make it
		//so the report only shows employees with punches within the time period specified.
		//If the user wants to see more employees they can increase the time period to "All".
		$key=0;
		if ( isset($this->tmp_data['punch']) ) {
			foreach( $this->tmp_data['punch'] as $user_id => $row ) {
				$processed_data = array();
				if ( isset($this->tmp_data['user_preference'][$user_id]) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_preference'][$user_id] );
				}
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user'][$user_id] );
				}

				$this->data[] = array_merge( $row, $processed_data );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				$key++;
			}
			unset($this->tmp_data, $row, $date_columns, $user_id, $processed_data );
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}
}
?>
