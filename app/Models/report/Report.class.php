<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: Sort.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Core
 */
class Report {

	public $title = NULL;
	public $file_name = 'report';
	public $file_mime_type = 'application/pdf';

	protected $config = array(
								'other' => array(
												'report_name' => '', //Name to be displayed on the report.
												'disable_grand_total' => FALSE,
												//'output_format' => 'pdf', //PDF, PDF_PRINT, PDF_FORM(Tax Form), PDF_FORM_PRINT(Print Tax Form), HTML, EMAIL
												'page_orientation' => 'P', //Portrait
												'page_format' => 'Letter', //Letter/Legal
												'default_font' => 'freeserif',
												'font_size' => 0, //+5, +4, .., +1, 0, -1, ..., -4, -5 (adjusts relative font size)
												'table_header_font_size' => 10,
												'table_row_font_size' => 8,
												'top_margin' => 5, //Allow the user to adjust the left/top margins for different printers.
												'left_margin' => 5,
												'right_margin' => 5,
												'adjust_horizontal_position' => 0, //We may need these for government forms/check printing, for on-page adjustments.
												'adjust_vertical_position' => 0,
												'show_blank_values' => TRUE, //Uses "- -" in place of a blank value.
												'blank_value_placeholder' => '-', //Used to replace blank values with. Was '- -'
												'show_duplicate_values' => FALSE, //Hides duplicate values in the same columns.
												'duplicate_value_placeholder' => ' ', //Used to replace duplicate values with. Can't be '' as that represents a blank value.
												),
								'chart' => array(
												'enable' => FALSE,
												'type' => 'horizontal_bar', // horizontal_bar, vertical_bar, pie
												//'type' => 'vertical_bar', // horizontal_bar, vertical_bar, pie
												'display_mode' => 10, //Displays chart above/below table data
												'point_labels' => TRUE, //Show bar/point labels.
												'include_sub_total' => FALSE, //Include sub_totals in chart.
												'axis_scale_min' => FALSE, //Set y_axis_minimum value, to rebase the axis scale on.
												'axis_scale_static' => FALSE, //Keeps the same axis scale for all graphs in a group.
												'combine_columns' => TRUE, //Combine all columns into a single chart.
												)
							);

	protected $tmp_data = NULL;
	public $data = NULL;
	protected $total_row = NULL;
	protected $data_column_widths = NULL;
	public $pdf = NULL;

	protected $chart_images = array();

	protected $form_obj = NULL; //Government forms
	protected $form_data = NULL; //Government forms

	protected $profiler = NULL;

	public $user_obj = NULL;
	public $permission_obj = NULL;

	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;

	protected function __getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'page_orientation':
				$retval = array(
								'P' => TTi18n::getText('Portrait'),
								'L' => TTi18n::getText('Landscape'),
							   );
				break;
			case 'font_size':
				$retval = array(
								0 => '-'.TTi18n::getText('Default').'-',
								25 => ' 25%',
								50 => ' 50%',
								75 => ' 75%',
								100 => '100%',
								125 => '125%',
								150 => '150%',
								175 => '175%',
								200 => '200%',
								225 => '225%',
								250 => '250%',
								275 => '275%',
								300 => '300%',
							   );
				break;
			case 'chart_type':
				$retval = array(
								10 => TTi18n::getText('Bar - Horizontal'), //'horizontal_bar'
								15 => TTi18n::getText('Bar - Vertical'), //'vertical_bar'
								//20 => TTi18n::getText('Line'), //'line'
								//30 => TTi18n::getText('Pie'), //'pie'
							   );
				break;
			case 'chart_display_mode':
				$retval = array(
								10 => TTi18n::getText('Below Table'), //'below_table'
								20 => TTi18n::getText('Above Table'), //'above_table'
								30 => TTi18n::getText('Chart Only'), //'chart_only'
							   );
				break;

			//
			//Metadata options...
			//
			case 'metadata_columns':
				$options = array(
								'columns' => array( 'format' => TTi18n::getText('Format'), 'format2' => TTi18n::getText('SubFormat') ),
								'group_by' => array( 'aggregate' => TTi18n::getText('Aggregate') ),
								'sub_total_by' => array( 'aggregate' => TTi18n::getText('Aggregate') ),
								'sort_order' =>	array( 'sort_order' => TTi18n::getText('Sort') )
							);
				if ( isset($params) AND $params != '' AND isset($options[$params]) ) {
					$retval = $options[$params];
				} else {
					$retval = $options;
				}

				break;
			case 'metadata_column_options':
				$options = array(
								'text' => array(),
								//'time_stamp' => array( 'HH:mm' => 'Hours:Minutes', 'HH' => 'Hours', 'HH:mm:ss' => 'Hours:Min:Sec' )
								//'date_stamp' => array( 'DD-MM-YY' => 'Day-Month-Year', 'MM-YY' => 'Month-Year' )
								//'currency' => array( 1 => 'Include dollar sign' 0 => 'Exclude Dollar Sign' )
								//'precision' => array( 1 => '1 Decimal Place', 1 => '2 Decimal Places',  )
								//'numeric' => array( 0 => 'w/Seperator', 1 => 'w/o Seperator' )
								//'full_name' = array( 0 => 'First Name', 1 => 'Last Name', 2 => 'First & Last Name', 3 => 'Last & First Name' ),
								'aggregate' => array( FALSE => TTi18n::getText('Group By'), 'min' => TTi18n::getText('Min'), 'avg' => TTi18n::getText('Avg'), 'max' => TTi18n::getText('Max'), 'sum' => TTi18n::getText('Sum'), 'count' => TTi18n::getText('Count') ),
								'sort' => array( 'ASC' => TTi18n::getText('ASC'), 'DESC' => TTi18n::getText('DESC') )
							);
				if ( isset($params) AND $params != '' AND isset($options[$params]) ) {
					$retval = $options[$params];
				} else {
					$retval = $options;
				}
				break;

		}

		return $retval;
	}

	function __construct() {
		global $profiler;

		$this->profiler = $profiler;

		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		$this->setExecutionTimeLimit();
		$this->setExecutionMemoryLimit();

		return TRUE;
	}

	//Defines the max execution timelimit for PHP
	function setExecutionTimeLimit( $int = 1800) {
		ini_set( 'max_execution_time', $int );
		return TRUE;
	}

	//Defines the max execution memory limit for PHP
	function setExecutionMemoryLimit( $str = '1024M' ) {
		ini_set('memory_limit', $str);
		return TRUE;
	}

	//Object of the user generating the report, we use this to base permission checks on, etc...
	function setUserObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->user_obj = $obj;
			return TRUE;
		}

		return FALSE;
	}
	function getUserObject() {
		return $this->user_obj;
	}

	function setPermissionObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->permission_obj = $obj;
			return TRUE;
		}

		return FALSE;
	}
	function getPermissionObject() {
		return $this->permission_obj;
	}

	//Used for TTLog::addEntry.
	function getTable() {
		return 'report';
	}

	function getProgressBarObject() {
		if  ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}
	//Returns the AMF messageID for each individual call.
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}
	function setAMFMessageID( $id ) {
		Debug::Text('AMF Message ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		if ( $id != '' ) {
			$this->AMF_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	//Set all options at once.
	function setConfig( $data ) {
		if ( is_array($data) ) {
			$data = Misc::trimSortPrefix( $data );
			//Handle merging in each set*Config() function instead.
			//$data = array_merge( $this->config, Misc::trimSortPrefix( $data ) );
			//$this->config = $data;
			if ( isset($data['columns']) ) {
				$this->setColumnConfig( (array)$data['columns'] );
			}
			if ( isset($data['group']) ) {
				$this->setGroupConfig( (array)$data['group'] );
			}
			if ( isset($data['sub_total']) ) {
				$this->setSubTotalConfig( (array)$data['sub_total'] );
			}
			//This must come after sub_total, as sort needs to adjust itself automatically based on sub_total.
			if ( isset($data['sort']) ) {
				$this->setSortConfig( (array)$data['sort'] );
			}
			if ( isset($data['chart']) ) {
				$this->setChartConfig( (array)$data['chart'] );
			}
			if ( isset($data['form']) ) {
				$this->setFormConfig( (array)$data['form'] );
			}
			if ( isset($data['other']) ) {
				$this->setOtherConfig( (array)$data['other'] );
			}

			//Remove special data, then the remaining is all filter data.
			unset($data['columns'],$data['group'],$data['sub_total'],$data['sort'], $data['other'], $data['chart'], $data['form']);
			if ( isset($data['filter']) ) {
				$data = array_merge( $data, (array)$data['filter'] );
				unset($data['filter']);
			}
			$this->setFilterConfig( $data );

			return TRUE;
		}

		return FALSE;
	}

	//Get all options
	function getConfig() {
		return $this->config;
	}

	function getTemplate( $name ) {
		$config = $this->getOptions('template_config', array('template' => $name) );
		if ( is_array($config) ) {
			return $config;
		}

		return FALSE;
	}

	//Loads a template config.
	function loadTemplate( $name ) {
		$config = $this->getTemplate( $name );
		if ( is_array($config) ) {
			//Merge template with existing config data, so we can keep any default settings.
			$this->setConfig( Misc::trimSortPrefix( array_merge($this->config, $config) ) );
			//Debug::Arr($this->config, '  bConfig:', __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}

		return FALSE;
	}

	//Store column options - This must be in the format of 'column' => TRUE, ie: 'regular_time => TRUE
	function setColumnConfig( $data ) {
		if ( isset( $data[0] ) ) {
			//array of format: array('col1','col2','col3') was passed, flip it first before saving it. so Flex can use the array key to maintain order
			$data = array_unique($data);
			foreach( $data as $key => $col ) {
				$formatted_data[$col] = TRUE;
			}
		} else {
			$formatted_data = $data;
		}
		$this->config['columns'] = Misc::trimSortPrefix($formatted_data);
		return TRUE;
	}
	function getColumnConfig() {
		if ( isset($this->config['columns']) ) {
			return $this->config['columns'];
		}

		return FALSE;
	}

	//Store filter options
	function setFilterConfig( $data ) {
		$data = Misc::trimSortPrefix( $data );
		if ( isset($data['time_period']) AND is_array($data['time_period']) ) {
			Debug::Text('Found TimePeriod...', __FILE__, __LINE__, __METHOD__,10);

			//Convert time_period into start/end date, with pay_period_schedule_ids if necessary.
			if ( isset($data['time_period']) AND isset($data['time_period']['time_period'])
					AND ( $data['time_period']['time_period'] == 'custom_date' OR $data['time_period']['time_period'] == 'custom_time' ) ) {
				Debug::Text('Found Custom dates...', __FILE__, __LINE__, __METHOD__,10);
				if ( isset($data['time_period']['start_date']) ) {
					$data['start_date'] = TTDate::parseDateTime( $data['time_period']['start_date'] );
				}
				if ( isset($data['time_period']['end_date']) ) {
					$data['end_date'] = TTDate::parseDateTime( $data['time_period']['end_date'] );
				}
			} elseif ( isset($data['time_period']) )  {
				$params = array();
				if ( isset($data['time_period']['pay_period_schedule_id']) ) {
					$params = array('pay_period_schedule_id' => $data['time_period']['pay_period_schedule_id'] );
				} elseif ( isset($data['time_period']['pay_period_id']) ) {
					$params = array('pay_period_id' => $data['time_period']['pay_period_id'] );
				}

				if ( !isset($data['time_period']['time_period']) ) {
					Debug::Text('ERROR: Time Period idenfier not specified!', __FILE__, __LINE__, __METHOD__,10);
					$data['time_period']['time_period'] = NULL;
				}

				$time_period_dates = TTDate::getTimePeriodDates($data['time_period']['time_period'], NULL, $this->getUserObject(), $params );
				if ( $time_period_dates != FALSE ) {
					if ( isset($time_period_dates['start_date']) ) {
						$data['start_date'] = $time_period_dates['start_date'];
					}
					if ( isset($time_period_dates['end_date']) ) {
						$data['end_date'] = $time_period_dates['end_date'];
					}
					if ( isset($time_period_dates['pay_period_id']) ) {
						$data['pay_period_id'] = $time_period_dates['pay_period_id'];
					}
				}
			} else {
				Debug::Text('Invalid TimePeriod filter...', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		//Debug::Arr($data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);

 		$this->config['filter'] = $data;
		return TRUE;
	}
	function getFilterConfig() {
		if ( isset($this->config['filter']) ) {
			return $this->config['filter'];
		}

		return FALSE;
	}

	//Used for converting $test[] = blah, or $test[] = array( 'col' => blah ) to $test['col'] => $blah.
	//Mainly for multi-dimension awesomebox group by, sub_total by, sorting...
	function convertArrayNumericKeysToString( $arr ) {
		foreach( $arr as $key => $value ) {
			if ( is_array($value) ) {
				foreach( $value as $key2 => $value2 ) {
					$retarr[$key2] = $value2;
				}
			} else {
				$retarr[$key] = $value;
			}
		}

		if (isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}
	function formatGroupConfig() {
		$group_config = $this->getGroupConfig();

		if ( is_array( $group_config ) ) {
			$metadata = $this->getOptions('group_by_metadata');
			if ( is_array($metadata) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = $this->getOptions('aggregates');
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($group_config[0]) AND is_array( $group_config[0] ) ) {
				$group_data = array_merge( $aggregates, $this->convertArrayNumericKeysToString( $group_config ) );
			} elseif ( isset($group_config[0]) AND $group_config[0] !== FALSE )  {
				//Merge passed group array with default aggregates from sub-class
				$group_data = array_merge( array_flip( $group_config ), $aggregates );
				//Debug::Arr($group_data, 'Final Group Data: ', __FILE__, __LINE__, __METHOD__,10);
			} else {
				Debug::Text('ERROR: Group data cannot be determined!', __FILE__, __LINE__, __METHOD__,10);
				$group_data = FALSE;
			}

			return $group_data;
		}

		return FALSE;
	}

	//Grouping options - Use a single re-orderable dropdown for grouping options?
	//Add function like: getGroupOptions( $columns ), that only shows the possible group_by columns based on the displayed columns?
	function setGroupConfig( $data ) {
		if ( !is_array($data) OR ( is_array($data) AND count($data) == 0 ) ) {
			return FALSE;
		}

		//$data should be a basic array of: 0 => 'first_name', 1 => 'last_name', etc... Convert to this to a
		$this->config['group'] = $data;

		return TRUE;
	}
	function getGroupConfig() {
		if ( isset($this->config['group']) ) {
			return $this->config['group'];
		}

		return FALSE;
	}

	//When using grouping, we have to be able to get a list of just the columns that will be displayed for reporting purposes.
	function getReportColumns() {
		$columns = $this->getColumnConfig();

		if ( is_array( $this->formatGroupConfig() ) ) {
			$group_data = array_keys( $this->formatGroupConfig() );
			//Debug::Arr($group_data, 'testGroup Data: ', __FILE__, __LINE__, __METHOD__,10);

			$static_columns = array_keys( Misc::trimSortPrefix( $this->getOptions('static_columns') ) );

			$invalid_columns = array_diff( $static_columns, $group_data );
			//Debug::Arr($invalid_columns, 'Invalid Columns due to grouping... Removing from column list: ', __FILE__, __LINE__, __METHOD__,10);
			if ( is_array($invalid_columns) ) {
				foreach( $invalid_columns as $invalid_column ) {
					unset($columns[$invalid_column]);
				}
			}
			//Debug::Arr($columns, 'Remaining Column Config: ', __FILE__, __LINE__, __METHOD__,10);
		}

		return $columns;
	}

	// When multiple columns are selected for sub-totaling, we need to multiply the sub-total passes,
	// ie: pay_period,branch,department would need to sub-total on pay_period.branch.department, pay_period.branch, pay_period
	function formatSubTotalConfig() {
		$sub_total_config = $this->getSubTotalConfig();

		if ( is_array( $sub_total_config ) AND count($sub_total_config) > 0 ) {
			$metadata = $this->getOptions('sub_total_by_metadata');
			if ( is_array($metadata) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = $this->getOptions('aggregates');
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($sub_total_config[0]) ) {
				$sub_total_config = $this->convertArrayNumericKeysToString( $sub_total_config );
			}

			//Multiple sub-total config into each iteration. Order of the columns matters.
			//Reverse the array then
			for( $i=0; $i < count($sub_total_config); $i++ ) {
				$n=count($sub_total_config)-1;
				foreach( $sub_total_config as $column ) {
					if ( $n >= $i ) {
						$sub_total_data[$i][] = $column;
					}
					$n--;
				}
				$sub_total_data[$i] = array_merge( array_flip( $sub_total_data[$i] ), $aggregates );
			}
			//Debug::Arr($sub_total_data, 'Final SubTotal Data: ', __FILE__, __LINE__, __METHOD__,10);

			return $sub_total_data;
		}

		return FALSE;
	}

	//Sub-Total options - If grouping is being used, we can only sub-total based on grouped columns.
	// In any case we can't sub-total by the last column, as that wouldn't make any sense anyways.
	function setSubTotalConfig( $data ) {
		if ( !is_array($data) OR ( is_array($data) AND count($data) == 0 ) ) {
			return FALSE;
		}
		//$data should be a basic array of: 0 => 'first_name', 1 => 'last_name', etc... It will be converted later.

		//Make sure sub_total doesn't contain the last (or only) group by column, as it will sub-total every row.
		if ( is_array($this->getGroupConfig() ) AND count($this->getGroupConfig() ) > 0 ) {
			$group_config = array_reverse( $this->getGroupConfig() );
			$bad_key = array_search( $group_config[0], $data );
			if ( $bad_key !== FALSE ) {
				Debug::Text('Removing bad sub-total column: '. $data[$bad_key], __FILE__, __LINE__, __METHOD__,10);
				unset($data[$bad_key]);
			}
		}

		$this->config['sub_total'] = $data;

		return TRUE;
	}
	function getSubTotalConfig() {
		if ( isset($this->config['sub_total']) ) {
			return $this->config['sub_total'];
		}

		return FALSE;
	}

	//Sorting options
	//When sub-totaling, we must sort by the sub-total columns *first*, otherwise the sub-totals won't be in the right place.
	function setSortConfig( $data ) {
		$formatted_data = array();

		//Get any sub_total columns, and use them to sort by first.
		$sub_total_config = $this->getSubTotalConfig();
		if ( is_array($sub_total_config) ) {
			foreach($sub_total_config as $sub_total_col ) {
				$formatted_data[$sub_total_col] = 'asc';
			}
		}

		if ( isset( $data[0] ) ) {
			//Allow alternative format of: array( 0 => array('col1' => 'asc'), 1 => array('col2' => 'desc') ) so Flex can use the array key to maintain order
			foreach( $data as $key => $sort_arr ) {
				if ( is_array($sort_arr) ) {
					foreach( $sort_arr as $sort_col => $sort_dir ) {
						$formatted_data[$sort_col] = $sort_dir;
					}
				}
			}
		} else {
			$formatted_data = $data;
		}


		$this->config['sort'] = $formatted_data;
		return TRUE;
	}
	function getSortConfig() {
		if ( isset($this->config['sort']) ) {
			return $this->config['sort'];
		}

		return FALSE;
	}

	//Uses UserReportData class to save the form config for the entire company.
	function setCompanyFormConfig( $data = NULL ) {
		if ( $this->checkPermissions() == FALSE ) {
			Debug::Text('Invalid permissions!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $data == '' OR !is_array($data ) ) {
			$data = $this->getFormConfig();
		}

		if ( is_object( $this->getUserObject() ) ) {
			$urdf = TTnew( 'UserReportDataFactory' );

			$urdlf = TTnew( 'UserReportDataListFactory' );
			$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), get_class($this) );
			if ( $urdlf->getRecordCount() > 0 ) {
				$urdf->setID( $urdlf->getCurrent()->getID() );
			}
			$urdf->setCompany( $this->getUserObject()->getCompany() );
			$urdf->setScript( get_class($this) );
			$urdf->setName( $this->title );
			$urdf->setData( $data );
			$urdf->setDefault( TRUE );
			if ( $urdf->isValid() ) {
				$urdf->Save();
			} else {

			}

			return TRUE;
		}

		return FALSE;
	}
	function getCompanyFormConfig() {
		if ( $this->checkPermissions() == FALSE ) {
			Debug::Text('Invalid permissions!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$urdlf = TTnew( 'UserReportDataListFactory' );
		$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), get_class($this) );
		if ( $urdlf->getRecordCount() > 0 ) {
			Debug::Text('Found Company Report Setup!', __FILE__, __LINE__, __METHOD__,10);
			$urd_obj = $urdlf->getCurrent();
			$data = $urd_obj->getData();

			return $data;
		}
		unset($urd_obj);

		return FALSE;
	}

	//Used for government form config.
	function setFormConfig( $data ) {
		//Check to see if existing data for the form has already been saved.
		if ( $this->getCompanyFormConfig() === FALSE ) {
			$this->setCompanyFormConfig( $data ); //If no form config has been saved yet, do so on the first report generation only.
		}

		$this->config['form'] = $data;
		return TRUE;
	}
	function getFormConfig() {
		if ( isset($this->config['form']) ) {
			return $this->config['form'];
		}

		return FALSE;
	}

	//Misc. options
	//  Possible global options:
	function setOtherConfig( $data ) {
		if ( is_array($data) ) {
			$this->config['other'] = array_merge( $this->config['other'], $data ); //Merge data as to keep default settings whenever possible.
			return TRUE;
		}

		return FALSE;
	}
	function getOtherConfig() {
		if ( isset($this->config['other']) ) {
			return $this->config['other'];
		}

		return FALSE;
	}

	function isEnabledChart() {
		$config = $this->getChartConfig();
		if ( isset($config['enable']) AND $config['enable'] == TRUE ) {
			return TRUE;
		}

		return FALSE;
	}
	function setChartConfig( $data ) {
		$this->config['chart'] = $data;
		return TRUE;
	}
	function getChartConfig() {
		if ( isset($this->config['chart']) ) {
			return $this->config['chart'];
		}

		return FALSE;
	}

	//Returns the default file name if none is specified.
	function getFileName() {
		return $this->file_name.'_'.date('Y_m_d').'.pdf';
	}
	//Returns the default file mime type if none is specified.
	function getFileMimeType() {
		return $this->file_mime_type;
	}

	//Return options from sub-class for things like columns, sorting columns, grouping columns, sub-total columns, etc...
	function getOptions($name, $params = NULL) {
		if ( $params == NULL OR $params == '') {
			return $this->_getOptions( $name );
		} else {
			return $this->_getOptions( $name, $params );
		}

		return FALSE;
	}
	protected function _getOptions( $name, $params = NULL ) {
		return FALSE;
	}

	//Get raw data for report
	function getData() {
		Debug::Arr( $this->config ,'Final Report Config: ', __FILE__, __LINE__, __METHOD__,10);

		$this->profiler->startTimer( 'getData' );
		$this->_getData();
		$this->profiler->stopTimer( 'getData' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//PreProcess data such as calculating additional columns (Day of Week, Year Quarter, combined multiple columns together, etc...) from raw data etc...
	function preProcess( $format = NULL ) {

		$this->profiler->startTimer( 'preProcess' );
		$this->_preProcess( $format );
		$this->profiler->stopTimer( 'preProcess' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//Group Data - Automatically include all static columns that are also selected to be viewed, so the user doesn't have to re-select all columns twice.
	//				Its actually the opposite, select on NON-static columns, and ignore all static columns except the grouped columns.
	function group() {
		$this->profiler->startTimer( 'group' );

		if ( is_array( $this->formatGroupConfig() ) AND count( $this->formatGroupConfig() ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->data), NULL, TTi18n::getText('Grouping Data...') );

			$this->data = Group::GroupBy( $this->data, $this->formatGroupConfig() );

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), count($this->data) );
			//Debug::Arr($this->formatGroupConfig(), 'Group Config: ', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($this->data, 'Group Data: ', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->profiler->stopTimer( 'group' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//Sort data
	function sort() {
		$this->profiler->startTimer( 'sort' );
		if ( is_array( $this->getSortConfig() ) AND count( $this->getSortConfig() ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->data), NULL, TTi18n::getText('Sorting Data...') );

			Debug::Arr($this->getSortConfig(), 'Sort Config: ', __FILE__, __LINE__, __METHOD__,10);
			$this->data = Sort::arrayMultiSort( $this->data, $this->getSortConfig() );

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), count($this->data) );
		}
		$this->profiler->stopTimer( 'sort' );

		//Debug::Arr($this->data, 'Sort Data: ', __FILE__, __LINE__, __METHOD__,10);
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);
		return TRUE;
	}

	//Calculate overall total in memory before we do any sub-totaling, then append *after* subtotaling is complete.
	function Total() {
		$this->profiler->startTimer( 'Total' );

		$other_config = $this->getOtherConfig();
		if ( !isset($other_config['disable_grand_total']) OR $other_config['disable_grand_total'] == FALSE ) {

			$metadata = $this->getOptions('grand_total_metadata');
			if ( is_array($metadata) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = $this->getOptions('aggregates');
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__,10);

			//Use Group By , so we utilize the proper aggregates when totalling the entire report.
			//Add '_total' = TRUE as metadata.
			$total = Group::GroupBy( $this->data, $aggregates, 2 ); //2 = Total
			$total[0]['_total'] = TRUE;
			$this->total_row = $total[0];
			//Debug::Arr($this->total_row, ' Total Row: ', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->profiler->stopTimer( 'Total' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//Calculate subtotals - This must be done *after* sorting, as the data may need to be re-sorted to properly merge sub-totals back into main array.
	function subTotal() {
		$this->profiler->startTimer( 'subTotal' );
		if ( is_array( $this->getSubTotalConfig() ) AND count($this->getSubTotalConfig()) > 0 ) {

			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->formatSubTotalConfig()), NULL, TTi18n::getText('Totaling Data...') );

			$sub_total_data = array();
			$i=0;
			foreach( $this->formatSubTotalConfig() as $k => $iteration_config ) {
				Debug::Text(' SubTotal iteration: '. $i, __FILE__, __LINE__, __METHOD__,10);

				$tmp_sub_total_data = Group::GroupBy( $this->data, $iteration_config, 1 );
				if ( $i == 0 ) {
					$sub_total_data = $tmp_sub_total_data;
				} else {
					//Merge sub_total data arrays, if two keys match, increment by one, so the consecutive sub-totals rows come after one another.
					foreach( $tmp_sub_total_data as $key => $data ) {
						if ( isset($sub_total_data[$key]) ) {
							//Find non-conflicting key that preserves ordering.
							$new_key = $key;
							for($i=0; $i <= count($sub_total_data); $i++ ) {
								$new_key .= '_';
								//Stop the loop if the new key isn't also a duplicate.
								if ( !isset($sub_total_data[$new_key]) ) {
									break;
								}
							}
							//Debug::Text(' Conflicting key found: '. $key .', finding next available one: '. $new_key, __FILE__, __LINE__, __METHOD__,10);

							$sub_total_data[$new_key] = $data;
						} else {
							$sub_total_data[$key] = $data;
						}
					}
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $k );

				$i++;
			}

			$this->data = array_merge($this->data, $sub_total_data );
			unset($sub_total_data, $k, $key, $data, $tmp_sub_total_data);

			uksort($this->data, 'strnatcasecmp');
			//$this->data = array_values($this->data); //Reindex all rows after sorting.
			//Debug::Arr($this->data, ' SubTotal Data: ', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->profiler->stopTimer( 'subTotal' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function chart() {
		$this->profiler->startTimer( 'chart' );

		if ( $this->isEnabledChart() == TRUE ) {
			$rc = new ReportChart( $this );
			$this->chart_images = $rc->Output();
		} else {
			Debug::Text(' Charting not enabled...', __FILE__, __LINE__, __METHOD__,10);
		}

		$this->profiler->stopTimer( 'chart' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//Last operation before displaying data to the user, format data within locale, add dollar signs, thousand separators etc...
	//This increases performance when heavy grouping is used, as there is less data to process.
	//As well it reduces memory usage as we can overwrite columns with nicely displaying columns instead.
	//Unfortunatley the above performance optimizations dont' work, so we need to postProcess immediately after preProcess as
	//sometimes there are columns postProcess needs that grouping will drop. For example when two or three columns are required to postProcess into a single column.
	//If group_by on that single column happens before postProcess, all the necessary data will be lost.
	//This will have to be one of the restrictions, that postProcess can only use a *SINGLE* column at a time, as its not guaranteed to have more than that due to grouping.
	function postProcess( $format = NULL ) {
		//Append Total record to the end.
		if ( isset($this->total_row) AND is_array($this->total_row) ) {
			$this->data[] = $this->total_row;
		}

		$this->profiler->startTimer( 'postProcess' );
		$this->_postProcess( $format );
		$this->profiler->stopTimer( 'postProcess' );
		Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function columnFormatter( $type, $column, $value, $format = NULL ) {
		if ( is_array($value) AND isset($value['display']) ) { //Found sorting array, use display column.
			return $value['display'];
		} else {
			$retval = $value;

			if ( $format == 'csv' ) { //Force specific field formats for exporting to CSV format.
				switch ( $type ) {
					case 'report_date':
						$retval = TTDate::getReportDates( $column, $value, TRUE, $this->getUserObject() );
						break;
					case 'currency':
					case 'percent':
					case 'numeric':
						//Don't format above types.
						break;
					case 'time_unit':
						$retval = TTDate::getHours( $value ); //Force to hours always.
						break;
					case 'date_stamp':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'time_stamp':
						$retval = TTDate::getDate('DATE+TIME', $value );
						break;
					case 'boolean':
						if ( $value == TRUE ) {
							$retval = TTi18n::getText('Yes');
						} else {
							$retval = TTi18n::getText('No');
						}
					default:
						break;
				}
			} else {
				switch ( $type ) {
					case 'report_date':
						$retval = TTDate::getReportDates( $column, $value, TRUE, $this->getUserObject() );
						break;
					case 'currency':
						$retval = TTi18n::formatCurrency( $value );
						break;
					case 'percent':
						$retval = TTi18n::formatNumber( $value, TRUE ).'%';
						break;
					case 'numeric':
						$retval = TTi18n::formatNumber( $value, TRUE );
						break;
					case 'time_unit':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'date_stamp':
						$retval = TTDate::getDate('DATE', $value );
						break;
					case 'time_stamp':
						$retval = TTDate::getDate('DATE+TIME', $value );
						break;
					case 'boolean':
						if ( $value == TRUE ) {
							$retval = TTi18n::getText('Yes');
						} else {
							$retval = TTi18n::getText('No');
						}
						break;
					default:
						break;
				}
			}

			//Debug::Text('Column: '. $column .' Value: '. $value .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__,10);

			return $retval;
		}
	}

	function getTimePeriodFormatOptions( $format_options = array() ) {
		$report_date_columns = Misc::trimSortPrefix( $this->getOptions('date_columns') );
		if ( is_array($report_date_columns) ) {
			foreach( $report_date_columns as $column => $name ) {
				$format_options[$column] = 'report_date';
			}
		} else {
			Debug::Text('No Report Date columns...', __FILE__, __LINE__, __METHOD__,10);
		}

		return $format_options;
	}

	function getColumnFormatConfig() {
		return $this->getTimePeriodFormatOptions( Misc::trimSortPrefix( $this->getOptions('column_format') ) );
	}

	function _postProcess( $format = NULL ) {
		if ( is_array($this->data) AND count($this->data) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->data), NULL, TTi18n::getText('Post-Processing Data...') );

			//Get column formatting data.
			$column_format_config = $this->getColumnFormatConfig();
			//Debug::Arr($column_format_config, 'Column Format Config: ', __FILE__, __LINE__, __METHOD__,10);

			foreach( $this->data as $key => $row ) {
				if ( is_array($row) ) {
					foreach( $row as $column => $value ) {
						//Debug::Arr($row, ' Row: ', __FILE__, __LINE__, __METHOD__,10);
						if ( isset($column_format_config[$column]) ) {
							$this->data[$key][$column] = $this->columnFormatter( $column_format_config[$column], $column, $value, $format );
						} else {
							if ( is_array($row[$column]) AND isset($row[$column]['display']) ) { //Found sorting array, use display column.
								$this->data[$key][$column] = $row[$column]['display'];
							} else {
								//Don't modify any data.
							}
						}

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					}
				}
			}
		} else {
			Debug::Text('No data to postProcess...', __FILE__, __LINE__, __METHOD__,10);
		}

		//Debug::Arr($this->data, 'postProcess Data: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	//Returns the full description block of text.
	function getDescriptionBlock( $html = FALSE, $relative_time_period = FALSE ) {
		//Don't include the report name.
		//$body = TTI18n::getText('Report').': '. $this->title."\n\n";
		$body = '';

		//Report Name
		$report_name = $this->getDescription('report_name');
		if ( $report_name != '' ) {
			$body .= TTi18n::getText('Name').': '. $report_name."\n";
		}

		//Time Period: start/end date, or pay period.
		$description = $this->getDescription('time_period', array( 'relative_time_period' => $relative_time_period ) );
		if ( $description != '' ) {
			$body .= TTi18n::getText('Time Period').': '. $description."\n";
		}

		//Filter:
		$description = $this->getDescription('filter');
		if ( $description != '' ) {
			$body .= TTi18n::getText('Filter').': '. $description."\n";
		}

		//Group:
		$description = $this->getDescription('group');
		if ( $description != '' ) {
			$body .= TTi18n::getText('Group'). ': '. $description."\n";
		}

		//SubTotal:
		$description = $this->getDescription('sub_total');
		if ( $description != '' ) {
			$body .= TTi18n::getText('SubTotal').': '. $description."\n";
		}

		//Sort:
		$description = $this->getDescription('sort');
		if ( $description != '' ) {
			$body .= TTi18n::getText('Sort').': ' . $description."\n";
		}

		if ( $html == TRUE ){
			$body = nl2br( $body );
		}

		return $body;
	}

	function getDescription( $label, $params = NULL ) {
		$retval = FALSE;

		$label = strtolower( trim( $label ) );
		switch ( $label ) {
			case 'time_period':
				//Debug::Text('Valid Label: '. $label, __FILE__, __LINE__, __METHOD__,10);

				$config = $this->getFilterConfig();
				if ( isset($config['pay_period_id']) AND is_array($config['pay_period_id']) ) {
					//Pay Period based
					$pplf = TTnew( 'PayPeriodListFactory' );
					$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
					$pay_period_options = Misc::trimSortPrefix( $pplf->getArrayByListFactory( $pplf, FALSE, TRUE ) );

					foreach( $config['pay_period_id'] as $pay_period_id ) {
						$pay_period_names[] = Option::getByKey( $pay_period_id, $pay_period_options );
					}

					if ( isset($pay_period_names) ) {
						$retval = TTi18n::getText('Pay Periods').': '. implode(', ', $pay_period_names );
					} else {
						$retval = TTi18n::getText('Pay Periods').': '. TTi18n::getText('N/A');
					}
					unset($pplf, $pay_period_options, $pay_period_id, $pay_period_names);
				} elseif ( isset($config['time_period']) ) {
					if ( isset($params['relative_time_period']) AND $params['relative_time_period'] == TRUE
							AND isset($config['time_period']) AND isset($config['time_period']['time_period']) ) {
						//Show just the relative time period for displaying in a Saved Report datagrid, where exact dates may not be necessary.
						$retval = Option::getByKey( $config['time_period']['time_period'], Misc::trimSortPrefix( $this->getOptions( 'time_period' ) ) );
					} else {
						if ( isset($config['time_period']['time_period']) ) {
							$retval = Option::getByKey( $config['time_period']['time_period'], Misc::trimSortPrefix( $this->getOptions( 'time_period' ) ) ) .' [ ';
						}

						//Date based
						if ( isset($config['start_date']) AND $config['start_date'] != '' ) {
							$retval .= TTDate::getDate('DATE', $config['start_date']);
						} else {
							$retval .= TTi18n::getText('N/A');
						}

						$retval .= ' '. TTi18n::getText('to') .' ';

						if ( isset($config['end_date']) AND $config['end_date'] != '' ) {
							$retval .= TTDate::getDate('DATE', $config['end_date']);
						} else {
							$retval .= TTi18n::getText('N/A');
						}

						$retval .= ' ]';
					}
				} else {

				}
				break;
			case 'report_name':
				$config = $this->getOtherConfig();
				if ( isset($config['report_name']) AND $config['report_name'] != '' ) {
					$retval = $config['report_name'];
				}
				break;
			case 'filter':
			case 'group':
			case 'sub_total':
			case 'sort':
				switch ( $label ) {
					case 'filter':
						$config = (array)$this->getFilterConfig();
						unset($config['template']); //Ignore template when displaying this
						$filter_columns = array_keys($config);
						$columns = Misc::trimSortPrefix( $this->getOptions('setup_fields') );
						break;
					case 'group':
						$config = (array)$this->formatGroupConfig();
						$filter_columns = array();
						foreach( $config as $key => $val ){
							if ( $val == '' OR is_int($val) ) {
								$filter_columns[] = $key;
							}
						}
						unset($key, $val);
						$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
						break;
					case 'sub_total':
						$config = (array)$this->formatSubTotalConfig();
						$filter_columns = array();

						if ( is_array($config) AND isset($config[0]) AND is_array($config[0]) ) {
							$config = $config[0];
							foreach( $config as $key => $val ){
								if ( $val == '' OR is_int($val) ) {
									$filter_columns[] = $key;
								}
							}
						}
						unset($key, $val);
						$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
						break;
					case 'sort':
						$config = (array)$this->getSortConfig();
						$filter_columns = array_keys($config);
						$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
						break;
				}
				//Debug::Arr($config, ' Config: ', __FILE__, __LINE__, __METHOD__,10);

				if ( is_array( $filter_columns ) AND count($filter_columns) > 0 ) {
					foreach( $filter_columns as $column ) {
						//Debug::Text(' Column: '. $column, __FILE__, __LINE__, __METHOD__,10);
						if ( isset($columns[$column]) ) {
							$retval[] = trim( Option::getByKey( $column, $columns ) );
						}
					}

					if ( is_array($retval) ) {
						$retval = implode(', ', $retval );
					}
				}

				break;
			default:
				Debug::Text('Invalid label!', __FILE__, __LINE__, __METHOD__,10);
				break;
		}

		//Debug::Text('Getting description for label: '. $label .' Description: '. $retval, __FILE__, __LINE__, __METHOD__,10);

		return $retval;
	}

	function checkPermissions() {
		if ( is_object( $this->getPermissionObject() ) == TRUE ) {

			$retval = $this->_checkPermissions( $this->getUserObject()->getId(), $this->getUserObject()->getCompany() );
			Debug::Text('Permission Check Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__,10);
			return $retval;
		}

		Debug::Text('Permission Object not set!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getOutput( $format = NULL ) {
		//Get format from getMiscOptions().
		//Formats: RAW (PHP ARRAY), CSV, HTML, PDF

		if ( $this->checkPermissions() == FALSE ) {
			Debug::Text('Invalid permissions!', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		$this->start_time = microtime(TRUE);

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), 2, NULL, TTi18n::getText('Querying Database...') ); //Iterations need to be 2, otherwise progress bar is not created.
		$this->getProgressBarObject()->set( $this->getAMFMessageID(), 2 );

		$this->_preOutput( $format );

		$this->getData( $format );
		$this->preProcess( $format );
		$this->group();
		$this->sort(); //Sort needs to come before subTotal, as subTotal will need to re-sort the data in order to fit the sub-totals in.
		if ( $format != 'csv' ) { //Exclude total/sub-totals for CSV format
			$this->Total();
			$this->subTotal();
		}

		//Rekey data array starting at 0 sequentially.
		//This prevents the progress bar from jumping all over or moving in reverse.
		$this->data = array_values((array)$this->data);

		//We need to generate the charts before postProcess runs.
		$this->_pdf_Initialize();
		if ( $this->isEnabledChart() == TRUE  ) {
			$this->chart();
		}

		$this->postProcess( $format );

		$retval = $this->_output( $format );

		$this->_postOutput( $format );

		$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

		Debug::Text(' Format: '. $format .' Total Time: '. (microtime(TRUE)-$this->start_time) .' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage() , __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr( Debug::profileTimers( $this->profiler ), ' Profile Timers: ', __FILE__, __LINE__, __METHOD__,10);

		if ( $format != 'raw' AND ( !is_array($retval) OR !isset($retval['file_name']) OR !isset($retval['mime_type']) ) ) {
			return array(
						 'data' => $retval,
						 'file_name' => $this->getFileName(),
						 'mime_type' => $this->getFileMimeType(),
						 );
		} else {
			return $retval; //Array with file_name and mime_types
		}
	}

	function _preOutput( $format = NULL ) {
		return TRUE;
	}

	function _output( $format = NULL ) {
		Debug::Text('Format: '. $format, __FILE__, __LINE__, __METHOD__,10);
		if ( $format == 'raw' ) {
			//Should we rekey this array so the order can be presevered, which is critical to outputting it properly?
			return $this->data;
		} elseif ( $format == 'csv' ) {
			//Make sure we use the full readable column name when exporting to CSV.
			$column_options = Misc::trimSortPrefix( $this->getOptions('columns') );
			$column_config = $this->getReportColumns();
			foreach( $column_config as $column => $tmp ) {
				if ( isset($column_options[$column]) ) {
					$columns[$column] = $column_options[$column];
				}
			}
			//Debug::Arr($columns, 'Columns:  '. $format, __FILE__, __LINE__, __METHOD__,10);
			return array(
						 'data' => Misc::Array2CSV( $this->data, $columns, FALSE, TRUE ),
						 'file_name' => $this->file_name.'_'.date('Y_m_d').'.csv',
						 'mime_type' => 'text/csv',
						 );
		} else {
			Debug::Text('Exporting PDF format: '. $format, __FILE__, __LINE__, __METHOD__,10);
			return $this->_pdf();
		}

		return FALSE;
	}

	function _postOutput( $format = NULL ) {
		return TRUE;
	}

	function email( $output, $report_schedule_obj = NULL ) {
		Debug::Text('Emailing report...', __FILE__, __LINE__, __METHOD__,10);

		if ( is_array($output) AND isset($output['data']) AND $output['data'] != ''
				AND is_object( $this->getUserObject() )
				AND ( $this->getUserObject()->getHomeEmail() != FALSE OR $this->getUserObject()->getWorkEmail() != FALSE ) ) {

			if ( $this->getUserObject()->getWorkEmail() != FALSE ) {
				$primary_email = $this->getUserObject()->getWorkEmail();

				$secondary_email = NULL;
				if ( is_object( $report_schedule_obj ) AND $report_schedule_obj->getEnableHomeEmail() == TRUE AND $this->getUserObject()->getHomeEmail() != FALSE ) {
					$secondary_email .= $this->getUserObject()->getHomeEmail();
				}

				if ( is_object( $report_schedule_obj ) AND $report_schedule_obj->getOtherEmail() != '' ) {
					$secondary_email .= ' '. $report_schedule_obj->getOtherEmail();
				}
			} else {
				$primary_email = $this->getUserObject()->getHomeEmail();
				$secondary_email = NULL;
			}

			Debug::Text('Emailing report to: '. $primary_email .' CC: '. $secondary_email, __FILE__, __LINE__, __METHOD__,10);

			$subject = APPLICATION_NAME .' ';
			$other_config = $this->getOtherConfig();
			if ( isset($other_config['report_name']) AND $other_config['report_name'] != '' ) {
				$subject .= $other_config['report_name'] .' (';
			}
			$subject .= $this->title;
			if ( isset($other_config['report_name']) AND $other_config['report_name'] != '' ) {
				$subject .= ')';
			}

			$body = '<html><body>';
			$body .= TTI18n::getText('Report').': '. $this->title.'<br><br>';
			$body .= $this->getDescriptionBlock( TRUE );
			$body .= '</body></html>';
			//Debug::Text('Email Subject: '. $subject, __FILE__, __LINE__, __METHOD__,10);
			//Debug::Text('Email Body: '. $body, __FILE__, __LINE__, __METHOD__,10);

			TTLog::addEntry( 0, 500, TTi18n::getText('Emailing Report').': '. $this->title .' '. TTi18n::getText('To') .': '. $primary_email, NULL, $this->getTable() );

			$headers = array(
								'From'    => '"'. APPLICATION_NAME .' - '. TTi18n::gettext('Reports') .'"<DoNotReply@'. Misc::getHostName( FALSE ) .'>',
								'Subject' => $subject,
								'Cc'	  => $secondary_email,
							 );

			$mail = new TTMail();
			$mail->setTo( $primary_email );
			$mail->setHeaders( $headers );

			@$mail->getMIMEObject()->setHTMLBody($body);
			//$mail->getMIMEObject()->addAttachment($output, 'application/pdf', $this->file_name.'.pdf', FALSE, 'base64');
			$mail->getMIMEObject()->addAttachment($output['data'], $output['mime_type'], $output['file_name'], FALSE, 'base64');

			$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
			return $mail->Send();
		}

		Debug::Text('No report data to email!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function _pdf_detectPageSize( $column_options, $columns ) {
		$min_dimensions = array(216, 279); //Letter size, in mm

		//Compare size of table header with larger bold font compared to table data with smaller font.
		$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
		$table_column_name_widths = $this->_pdf_getTableColumnWidths( array_intersect_key($column_options, (array)$columns), $this->config['other']['layout']['header'] ); //Table header column names

		$this->pdf->SetFont($this->config['other']['default_font'],'', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
		$table_data_column_widths = $this->_pdf_getTableColumnWidths( $this->getLargestColumnData( array_intersect_key($column_options, (array)$columns) ), $this->config['other']['layout']['header'] ); //Table largest column data

		$width = 0;
		foreach( $table_column_name_widths as $column => $column_width ) {
			if (  $column_width > $table_data_column_widths[$column] ) {
				$tmp_width = $column_width;
			} else {
				$tmp_width = $table_data_column_widths[$column];
			}

			$this->data_column_widths[$column] = $tmp_width;
			$width += $tmp_width;
		}

		$margins = $this->pdf->getMargins();
		$width += $margins['left']+$margins['right'];

		if ( $width < $min_dimensions[0] ) {
			$width = $min_dimensions[0];
		}

		Debug::Text(' Detected Page Width including Margins: '. $width, __FILE__, __LINE__, __METHOD__,10);
		return $this->_pdf_getPageSizeDimensionsFromWidth( $width );
	}


	//Return the string from each column that is the largest, so we can base the column widths on these.
	function getLargestColumnData( $columns ) {
		//Cache the widths so all data doesn't need to be searched each time.
		$this->profiler->startTimer( 'getLargestColumnData' );

		$retarr = array();
		$widths = array();
		foreach( $columns as $key => $text ) {
			$retarr[$key] = $text;
			$widths[$key] = strlen( $text );
			//Make sure we include the length of the column header in this as well.
			if ( is_array($this->data) ) {
				foreach( $this->data as $row => $data_arr ) {
					if ( isset($data_arr[$key]) ) {
						if ( is_array($data_arr[$key]) AND isset($data_arr[$key]['display']) ) {
							$tmp_len = strlen($data_arr[$key]['display']);
							$data_arr[$key] = $data_arr[$key]['display'];
						} else {
							$tmp_len = strlen($data_arr[$key]);
						}
						if ( $tmp_len > $widths[$key] ) {
							$retarr[$key] = $data_arr[$key];
							$widths[$key] = $tmp_len;
						}
					}
				}
			}
		}

		$this->profiler->stopTimer( 'getLargestColumnData' );

		return $retarr;
	}

	function _pdf_getColumnWidth( $text, $layout ) {
		$max_width = 0;
		$cell_padding = 0;
		if ( isset($layout['max_width']) ) {
			$max_width = $layout['max_width'];
		}
		if ( isset($layout['cell_padding']) ) {
			$cell_padding = $layout['cell_padding'];
		}

		$string_width = ceil( $this->pdf->getStringWidth($text) + $cell_padding );

		if ( $max_width > 0 AND $string_width > $max_width ) {
			$string_width = $max_width;
		}
		//Debug::Text(' Sizing Text: '. $text .' Width: '. $string_width .' Max: '. $max_width .' Padding: '. $cell_padding, __FILE__, __LINE__, __METHOD__,10);

		return $string_width;
	}

	function _pdf_getTableColumnWidths( $columns, $layout, $fill_page = TRUE ) {

		if ( !is_array($columns) ) {
			return FALSE;
		}

		$widths = array();
		foreach( $columns as $key => $text ) {
			$widths[$key] = $this->_pdf_getColumnWidth( $text, $layout );
		}
		//Debug::Arr($widths, ' aColumn widths: ', __FILE__, __LINE__, __METHOD__,10);

		if ( $fill_page == TRUE AND count($widths) > 0 ) {
			$margins = $this->pdf->getMargins();
			$page_width = ($this->pdf->getPageWidth()-$margins['left']-$margins['right']);

			$total_width = array_sum($widths);
			if ( $total_width < $page_width ) {
				$empty_space = $page_width - $total_width;
				$empty_space_per_column = $empty_space / count($widths);

				//Try to make all column widths even numbers, than take any fractions and add them to the first column.
				$remainder = ( $empty_space_per_column-floor($empty_space_per_column) ) * count($widths);
				//Debug::Text(' Column widths are smaller than page size, resizing each column by: '. $empty_space_per_column .' Total Width: '. $total_width .' Page Width: '. $page_width .' Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__,10);

				$i=0;
				foreach( $widths as $key => $width ) {
					if ( $i == 0 ) {
						$widths[$key] += $remainder;
					}
					$widths[$key] += floor($empty_space_per_column);
					$i++;
				}
			}
		}
		//Debug::Arr($widths, ' Total Width: '. array_sum($widths) .' bColumn widths: ', __FILE__, __LINE__, __METHOD__,10);

		return $widths;
	}

	function _pdf_fontSize( $size ) {
		//The config font_size variable should be a scale, not a direct font size.
		$multiplier = $this->config['other']['font_size'];
		if ( $multiplier <= 0 ) {
			$multiplier = 100;
		}
		$retval = ceil( $size * ( $multiplier / 100 ) );
		//Debug::Text(' Requested Font Size: '. $size .' Relative Size: '. $retval, __FILE__, __LINE__, __METHOD__,10);
		return $retval;
	}

	function _pdf_getPageSizeDimensionsFromWidth( $min_width ) {
		//Handle portrait/landscape modes properly
		if ( $this->config['other']['page_orientation'] == 'L' ) {
			//Landscape
			$width = $min_width;
			$height = $min_width*0.784946236559;
		} else {
			//Portrait
			$width = $min_width;
			$height = $min_width*1.2739726027397260274;
		}
		//Debug::Text(' Orientation: '. $this->config['other']['page_orientation'] .' Width: '. $width .' Height: '. $height, __FILE__, __LINE__, __METHOD__,10);
		//return array( $width, $width*1.2739726027397260274 );
		return array( $width, $height );
	}

	function _pdf_drawLine( $width = 3 ) {
		$margins = $this->pdf->getMargins();

		$prev_width = $this->pdf->getLineWidth();
		$this->pdf->setLineWidth( $width );
		$this->pdf->setDrawColor(0); //Black
		$this->pdf->setFillColor(0); //Black

		$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), $this->pdf->getPageWidth()-$margins['right'], $this->pdf->getY() );

		$this->pdf->setLineWidth( $prev_width );

		$this->pdf->Ln( 0.75 );

		return TRUE;
	}

	function _pdf_checkPageBreak( $height, $add_page = TRUE ) {
		$margins = $this->pdf->getMargins();

		if ( ($this->pdf->getY()+$height) > ($this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10) ) {
			//Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__,10);
			$this->_pdf_AddPage();

			return TRUE;
		}
		return FALSE;
	}

	function _pdf_AddPage() {
		$this->_pdf_Footer();
		$this->pdf->AddPage();
		$this->_pdf_Header();
		return TRUE;
	}

	function _pdf_TopSummary() {
		$margins = $this->pdf->getMargins();

		//Draw report information
		if ( $this->pdf->getPage() == 1 ) {
			//Report Title top left.
			$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize(18) );
			$this->pdf->Cell(100, $this->_pdf_fontSize(10), $this->title, 0, 0, 'L', 0, '', 1);
			$this->pdf->Ln();

			//Logo - top right
			$image_width = 50;
			$image_height = 12;
			$this->pdf->Image( $this->getUserObject()->getCompanyObject()->getLogoFileName(), ($this->pdf->getPageWidth()-$margins['right']-50), $margins['top'], $image_width, $image_height, '', '', '', FALSE, 300, '', FALSE, FALSE, 0, TRUE);
			$this->pdf->Ln(1);
			$logo_image_y = $margins['top']+$image_height;
			//$this->pdf->setY( $this->pdf->getY()+5 ); //Place Abscissa below image.

			//Set font to small for report filter description
			$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );

			//Report Name
			$report_name = $this->getDescription('report_name');
			if ( $report_name != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('Name').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $report_name, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//Time Period: start/end date, or pay period.
			$description = $this->getDescription('time_period');
			if ( $description != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('Time Period').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $description, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//Filter:
			$description = $this->getDescription('filter');
			if ( $description != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('Filter').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $description, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//Group:
			$description = $this->getDescription('group');
			if ( $description != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('Group').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $description, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//SubTotal:
			$description = $this->getDescription('sub_total');
			if ( $description != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('SubTotal').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $description, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//Sort:
			$description = $this->getDescription('sort');
			if ( $description != '' ) {
				$this->pdf->Cell(15, $this->_pdf_fontSize(3), TTi18n::getText('Sort').':' , 0, 0, 'L', 0, '', 1);
				$this->pdf->Cell(100, $this->_pdf_fontSize(3), $description, 0, 0, 'L', 0, '', 1);
				$this->pdf->Ln();
			}

			//Generated Date/User top right.
			$this->pdf->setY( ( ($this->pdf->getY()-6) < $logo_image_y ) ? $logo_image_y : $this->pdf->getY()-6 );
			$this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
			$this->pdf->Cell(50, $this->_pdf_fontSize(3), TTi18n::getText('Generated').': '. TTDate::getDate('DATE+TIME', time() ), 0, 0, 'R', 0, '', 1);
			$this->pdf->Ln();
			$this->pdf->setX( $this->pdf->getPageWidth()-$margins['right']-50 );
			$this->pdf->Cell(50, $this->_pdf_fontSize(3), TTi18n::getText('Generated For').': '. $this->getUserObject()->getFullName(), 0, 0, 'R', 0, '', 1);
			$this->pdf->Ln( $this->_pdf_fontSize( 4 ) );

			$this->_pdf_drawLine(1);

			return TRUE;
		}

		return FALSE;
	}

	function _pdf_getMaximumNumLinesFromArray( $columns, $column_options, $column_widths ) {
		$max_num_lines = 1;
		foreach( $columns as $column => $tmp ) {
			if ( isset($column_options[$column]) AND isset($column_widths[$column]) ) {
				$num_lines = $this->pdf->getNumLines($column_options[$column], $column_widths[$column] );
				if ( $num_lines > $max_num_lines ) {
					$max_num_lines = $num_lines;
				}
			}
		}

		if ( $max_num_lines > 1 ) {
			return ($max_num_lines/1.5); //For some reason getNumLines returns 2 when 1.5 will do, so adjust for that here.
		}
		return $max_num_lines;
	}

	function _pdf_Header() {
		$column_options = Misc::trimSortPrefix( $this->getOptions('columns') );
		$columns = $this->getReportColumns();
		$header_layout = $this->config['other']['layout']['header'];

		$margins = $this->pdf->getMargins();

		//Draw report information
		if ( $this->pdf->getPage() > 1 ) {
			$this->_pdf_drawLine(0.75); //Slightly smaller than first/last lines.
		}

		if ( is_array($columns) AND count($columns) > 0 ) {
			$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
			$this->pdf->setTextColor(0);
			$this->pdf->setDrawColor(0);
			$this->pdf->setFillColor(240); //Grayscale only.

			$column_widths = $this->data_column_widths;
			$cell_height = $this->_pdf_getMaximumNumLinesFromArray( $columns, $column_options, $column_widths ) * $this->_pdf_fontSize( $header_layout['height'] );
			foreach( $columns as $column => $tmp ) {
				if ( isset($column_options[$column]) AND isset($column_widths[$column]) ) {
					$cell_width = $column_widths[$column];
					if ( ($this->pdf->getX()+$cell_width) > $this->pdf->getPageWidth() ) {
						Debug::Text(' Page not wide enough, it should be at least: '. ($this->pdf->getX()+$cell_width) .' Page Width: '. $this->pdf->getPageWidth(), __FILE__, __LINE__, __METHOD__,10);
						$this->pdf->Ln();
					}
					$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $header_layout['height'] ), $column_options[$column], $header_layout['border'], 0, $header_layout['align'], $header_layout['fill'], '', $header_layout['stretch'] );
					//Wrapping shouldn't be needed as the cell widths should expand to at least fit the header. Wrapping may be needed on regular rows though.
					//$this->pdf->MultiCell( $cell_width, $cell_height, $column_options[$column], 0, $header_layout['align'], $header_layout['fill'], 0 );
				} else {
					Debug::Text(' Invalid Column: '. $column, __FILE__, __LINE__, __METHOD__,10);
				}
			}
			$this->pdf->Ln();
			//$this->pdf->Ln( $cell_height ); //Used for multi-cell wrapping

			$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
		}

		return TRUE;
	}

	function _pdf_Footer() {
		$margins = $this->pdf->getMargins();

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(8) );
		$this->pdf->setTextColor(0);
		$this->pdf->setDrawColor(0);

		//Save x,y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		$this->pdf->setY( $this->pdf->getPageHeight()-$margins['bottom']-$margins['top']-10 );

		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::getText('Page').' '. $this->pdf->PageNo() .' of '. $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln();

		$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize(6) );
		$this->pdf->Cell( ($this->pdf->getPageWidth()-$margins['right']), $this->_pdf_fontSize(5), TTi18n::gettext('Report Generated By').' '. APPLICATION_NAME .' v'. APPLICATION_VERSION, 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );

		return TRUE;
	}

	function _pdf_Chart() {
		if ( $this->isEnabledChart() == TRUE  ) {
			$chart_config = $this->getChartConfig();

			Debug::Text(' Adding charts to PDF...', __FILE__, __LINE__, __METHOD__,10);

			$total_images = count($this->chart_images);
			if ( is_array($this->chart_images) AND $total_images > 0 ) {
				$margins = $this->pdf->getMargins();

				$x=1;
				foreach ( $this->chart_images as $chart_image ) {
					if ( $x == 1 AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 10 ) {
						//In case the table is displayed above the chart, insert a small space.
						$this->pdf->setY( $this->pdf->getY()+5 );
					}

					if ( isset($chart_image['file']) AND file_exists( $chart_image['file'] ) ) {
						$remaining_page_height = $this->pdf->getPageHeight() - $this->pdf->getY();

						Debug::Text(' Adding chart: '. $chart_image['file'] .' Page: '. $this->pdf->getPage() .' Width: '. $chart_image['width']  .' Height: '. $chart_image['height']  .' Page Width: '. $this->pdf->getPageWidth() .' Page Height: '. $this->pdf->getPageHeight(), __FILE__, __LINE__, __METHOD__,10);

						if ( $x == 1 AND $this->pdf->getPage() == 1 AND isset($chart_config['display_mode']) AND ( $chart_config['display_mode'] == 20 OR $chart_config['display_mode'] == 30 ) ) {
							//Resize the first chart to fit on the page with the report summary.
							$this->pdf->Image( $chart_image['file'], '', '', '', ($this->pdf->getPageHeight()-($this->pdf->getY()+$margins['bottom']+20)), '', '', '', FALSE, 300, 'C', FALSE, FALSE, 0, TRUE, FALSE, FALSE );
						} else {
							if ( $remaining_page_height < $chart_image['height'] ) {
								$this->_pdf_Footer();
								$this->pdf->AddPage();
								$this->pdf->setY( $this->pdf->getY()+$margins['top'] );
							}

							$this->pdf->Image( $chart_image['file'], '', '', '', '', '', '', '', FALSE, 300, 'C', FALSE, FALSE, 0, FALSE, FALSE, FALSE );
						}

						$this->pdf->setY(  $this->pdf->getY()+$chart_image['height'] );
						$this->_pdf_Footer();
					}

					if ( $x == $total_images AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 20 ) {
						//In case the table is displayed below the chart, insert a small space.
						$this->pdf->setY( $this->pdf->getY()+5 );
					}

					@unlink( $chart_image['file'] );

					$x++;
				}
			}
		}

		return TRUE;
	}

	function _pdf_Initialize() {
		$this->profiler->startTimer( 'PDF' );

		//Page width: 205mm
		$this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'] );

		$this->pdf->SetCreator( APPLICATION_NAME );
		$this->pdf->SetAuthor( APPLICATION_NAME );
		$this->pdf->SetTitle( $this->title );
		$this->pdf->SetSubject( APPLICATION_NAME .' '. TTi18n::getText('Report') );

		$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] );
		$this->pdf->SetAutoPageBreak(FALSE);

		$border = 0;

		$adjust_x = 10;
		$adjust_y = 10;

		$column_options = (array)Misc::trimSortPrefix( $this->getOptions('columns') );
		$static_column_options = (array)Misc::trimSortPrefix( $this->getOptions('static_columns') );
		$columns = $this->getReportColumns();
		$sub_total_columns = (array)$this->getSubTotalConfig();

		$sub_total_columns_count = ( count( array_intersect( array_keys( $static_column_options ), array_keys((array)$columns) ) ) > 1 ) ? count($sub_total_columns) : 0; //If there is only one static column, we can't indent the "Grand Total" label.
		//Debug::Arr($columns, ' Report Columns: ', __FILE__, __LINE__, __METHOD__,10);

		//
		//Table Header - Start
		//
		$this->config['other']['layout']['header'] = array(
																	'max_width' => 500,
																	'cell_padding' => 2,
																	'height' => 8,
																	'align' => 'R',
																	'border' => 0,
																	'fill' => 1,
																	'stretch' => 1 );

		//Determine how large the page needs to be, and change its format as necessary.
		$page_size = $this->_pdf_detectPageSize( $column_options, $columns );
		$this->pdf->AddPage( $this->config['other']['page_orientation'], $page_size );

		$this->profiler->stopTimer( 'PDF' );

		return TRUE;
	}

	function _pdf() {
		$chart_config = $this->getChartConfig();

		//$this->_pdf_Initialize(); //This is called in Output() function, as it needs to happen before the charts are generated.
		$this->_pdf_TopSummary();

		if ( $this->isEnabledChart() == TRUE AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 30 ) {
			$this->_pdf_Chart();
		} else {
			if ( $this->isEnabledChart() == TRUE AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 20 ) {
				$this->_pdf_Chart();
			}

			$this->_pdf_Header();
			$this->_pdf_Table();

			if ( $this->isEnabledChart() == TRUE AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 10 ) {
				$this->_pdf_Chart();
			}
		}

		$this->_pdf_Footer();
		$output = $this->pdf->Output('','S');

		if ( $output !== FALSE ) {
			return $output;
		}

		return FALSE;
	}

	//Generate PDF.
	function _pdf_Table() {
		$this->profiler->startTimer( 'PDF Table' );

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->data), NULL, TTi18n::getText('Generating PDF...') );

		$border = 0;

		$column_options = (array)Misc::trimSortPrefix( $this->getOptions('columns') );
		$static_column_options = (array)Misc::trimSortPrefix( $this->getOptions('static_columns') );
		$columns = $this->getReportColumns();
		$sub_total_columns = (array)$this->getSubTotalConfig();

		$sub_total_columns_count = ( count( array_intersect( array_keys( $static_column_options ), array_keys((array)$columns) ) ) > 1 ) ? count($sub_total_columns) : 0; //If there is only one static column, we can't indent the "Grand Total" label.
		$sub_total_rows = array(); //Count all rows included in sub_total
		for( $n=0; $n <= $sub_total_columns_count; $n++) {
			$sub_total_rows[$n]=0;
		}
		//Debug::Arr($columns, ' Report Columns: ', __FILE__, __LINE__, __METHOD__,10);

		$row_layout = array(
								'max_width' => 30,
								'cell_padding' => 2,
								'height' => 5,
								'align' => 'R',
								'border' => 0,
								'fill' => 1,
								'stretch' => 1 );

		$column_widths = $this->data_column_widths;

		$prev_row = array();
		$r=0;
		$total_rows = 0; //Count all rows included in grand total
		if ( is_array($columns) AND count($columns) > 0 AND is_array($this->data) AND count($this->data) > 0 ) {
			foreach( $this->data as $key => $row ) {
				//If the next row is a subtotal or total row, do a page break early, so we don't show just a subtotal/total by itself.
				if ( isset($this->data[$key+1])
						AND ( ( isset($this->data[$key+1]['_subtotal']) AND $this->data[$key+1]['_subtotal'] == TRUE )
								OR ( isset($this->data[$key+1]['_total']) AND $this->data[$key+1]['_total'] == TRUE ) ) ) {
					$page_break_row_height = $row_layout['height']*2.5;
				} else {
					$page_break_row_height = $row_layout['height'];
				}

				$new_page = $this->_pdf_checkPageBreak( $page_break_row_height,  TRUE );

				//Reset all styles/fills after page break.
				$this->pdf->SetFont($this->config['other']['default_font'],'', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
				$this->pdf->SetTextColor(0);
				$this->pdf->SetDrawColor(0);
				if ( $r % 2 == 0 ) {
					$this->pdf->setFillColor(255);
				} else {
					$this->pdf->setFillColor(250);
				}

				//Add a little extra space before the Grand Total line, so we can insert a double line.
				if ( isset($row['_total']) AND $row['_total'] == TRUE ) {
					$this->pdf->Ln(1);
				}

				$c=0;
				$total_row_sub_total_columns = 0;
				$blank_row = TRUE;
				if ( $r == 0 AND ( isset($row['_total']) AND $row['_total'] == TRUE ) ) {
					Debug::Text('Last row is grand total, no actual data to display...', __FILE__, __LINE__, __METHOD__,10);
					$error_msg = TTi18n::getText('NO DATA MATCHES CRITERIA');
					$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize(16) );
					$this->pdf->Cell( $this->pdf->getPageWidth(), 20, '['. $error_msg .']', 0, 0, 'C', 0, '', 0 );
				} else {
					if ( ( isset($row['_subtotal']) AND $row['_subtotal'] == TRUE ) ) {
						//Figure out how many subtotal columns are set, so we can merge the cells
						foreach( $sub_total_columns as $k => $sub_total_column ) {
							if ( isset($row[$sub_total_column]) ) {
								$total_row_sub_total_columns++;
							}
						}
					}

					foreach( $columns as $column => $tmp ) {
						if ( isset($row[$column]) ) {
							$value = $row[$column];
						} else {
							$value = ''; //This needs to be a space, otherwise cells won't be drawn and background colors won't be shown either.
						}

						//Debug::Text(' Row: '. $key .' Column: '. $column .'('.$c.') Value: '. $value .' Count Cols: '. count($row) .' Sub Total Columns: '. $total_row_sub_total_columns, __FILE__, __LINE__, __METHOD__,10);
						//Debug::Text(' Row: '. $key .' Column: '. $column .'('.$c.') Value: '. $value .' Count Cols: '. count($row), __FILE__, __LINE__, __METHOD__,10);
						$cell_width = ( isset($column_widths[$column]) ) ? $column_widths[$column] : 30;

						//Bold total and sub-total rows, add lines above each cell.
						if ( ( isset($row['_subtotal']) AND $row['_subtotal'] == TRUE ) OR ( isset($row['_total']) AND $row['_total'] == TRUE ) ) {
							$this->pdf->SetFont($this->config['other']['default_font'], 'B', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );

							if ( ( isset($row['_subtotal']) AND $row['_subtotal'] == TRUE ) ) {
								//Debug::Text(' SubTotal Row... SI: '. $sub_total_columns_count .' C: '. $c .' Row SI: '. $total_row_sub_total_columns, __FILE__, __LINE__, __METHOD__,10);
								if ( $c == $sub_total_columns_count ) {
									$value = TTi18n::getText('SubTotal').'['. $sub_total_rows[$total_row_sub_total_columns] .']:';
								} elseif ( $c < ($total_row_sub_total_columns-1) ) {
									$value = '';
								}
							} else {
								//Debug::Text(' C: '. $c .' Row SI: '. $sub_total_columns_count, __FILE__, __LINE__, __METHOD__,10);
								if ( $c == $sub_total_columns_count ) {
									$value = TTi18n::getText('Grand Total').'['. $total_rows .']:';
								}
							}

							//Put a line above the sub-total cell, and a double line above grand total cell
							if ( isset($row['_total']) AND $row['_total'] == TRUE ) {
								$this->pdf->setFillColor( 245 );
								$this->pdf->setLineWidth( 0.25 );
								$this->pdf->Line( $this->pdf->getX()+1, $this->pdf->getY()-1, $this->pdf->getX()+($cell_width-1), $this->pdf->getY()-1 );
								$this->pdf->Line( $this->pdf->getX()+1, $this->pdf->getY()-0.5, $this->pdf->getX()+($cell_width-1), $this->pdf->getY()-0.5 );
							} elseif ( $c >= ($total_row_sub_total_columns-1) ) {
								$this->pdf->setLineWidth( 0.5 );
								$this->pdf->Line( $this->pdf->getX()+1, $this->pdf->getY(), $this->pdf->getX()+($cell_width-1), $this->pdf->getY() );
							}
						} else {
							//Don't show duplicate data in cells that are next to one another. But always show data after a sub-total.
							//Only do this for static columns as well.
							//Make sure we don't remove duplicate values in pay stub reports, so if the value is a FLOAT then never replace it.
							//Make sure we don't replace duplicate values if the duplicates are blank value placeholders.
							if ( $this->config['other']['show_duplicate_values'] == FALSE AND $new_page == FALSE AND !isset($prev_row['_subtotal'])
									AND isset($prev_row[$column]) AND isset($row[$column]) AND !is_float($row[$column]) AND $prev_row[$column] === $row[$column]
									AND isset($static_column_options[$column])
									AND $prev_row[$column] !== $this->config['other']['blank_value_placeholder'] ) {
								//This needs to be a space otherwise cell background colors won't be shown.
								$value = ( $this->config['other']['duplicate_value_placeholder'] != '' ) ? $this->config['other']['duplicate_value_placeholder'] : ' ';
							}
						}

						if ( $this->config['other']['show_blank_values'] == TRUE AND $value == '' ) {
							//Update $row[$column] so the blank value gets put into the prev_row variable so we can check for it in the next loop.
							$value = $row[$column] = $this->config['other']['blank_value_placeholder'];
						}

						if ( !isset($row['_total']) AND $blank_row == TRUE AND $value == '' ) {
							$this->pdf->setX( $this->pdf->getX()+$cell_width );
						} else {
							$blank_row = FALSE;

							//Row formatting...
							if ( isset($row['_fontcolor']) AND is_array($row['_fontcolor']) ) {
								$this->pdf->setTextColor( $row['_fontcolor'][0], $row['_fontcolor'][1], $row['_fontcolor'][2] );
							} else {
								$this->pdf->setTextColor(0);
							}
							if ( isset($row['_drawcolor']) AND is_array($row['_drawcolor']) ) {
								$this->pdf->setDrawColor( $row['_drawcolor'][0], $row['_drawcolor'][1], $row['_drawcolor'][2] );
							} else {
								$this->pdf->setDrawColor(0);
							}
							if ( isset($row['_bgcolor']) AND is_array($row['_bgcolor']) ) {
								$this->pdf->setFillColor( $row['_bgcolor'][0], $row['_bgcolor'][1], $row['_bgcolor'][2] );
							}
							if ( isset($row['_border']) ) {
								$border = $row['_border'];
							} else {
								$border = $row_layout['border'];
							}

							$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $row_layout['height'] ), $value, $border, 0, $row_layout['align'], $row_layout['fill'], '', $row_layout['stretch'] );
						}

						$c++;
					}
				}

				//UnBold after sub-total rows, but NOT grand total row.
				if ( ( isset($row['_subtotal']) AND $row['_subtotal'] == TRUE ) ) {
					$this->pdf->SetFont($this->config['other']['default_font'], '', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
					$this->pdf->Ln(10);
				}

				if ( $blank_row == TRUE ) {
					$this->pdf->Ln(0);
				} else {
					$this->pdf->Ln();
					$r++;
				}

				if ( !isset($row['_total']) AND !isset($row['_subtotal']) ) {
					$total_rows++;
					//Increment all sub_total rows for each group_by column.
					for( $n=0; $n <= $sub_total_columns_count; $n++) {
						$sub_total_rows[$n]++;
					}
				} elseif ( isset($row['_subtotal']) ) {
					//Clear only the sub_total row counter that we are displaying currently.
					$sub_total_rows[$total_row_sub_total_columns]=0;
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );

				$prev_row = $row;
			}

			$this->_pdf_drawLine(1);
		} else {
			Debug::Text('No data or columns to display...', __FILE__, __LINE__, __METHOD__,10);
			if ( !is_array($columns) OR count($columns) == 0 ) {
				$error_msg = TTi18n::getText('NO DISPLAY COLUMNS SELECTED');
			} elseif ( !is_array($this->data) OR count($this->data) == 0 ) {
				$error_msg = TTi18n::getText('NO DATA MATCHES CRITERIA');
			} else {
				$error_msg = TTi18n::getText('UNABLE TO DISPLAY REPORT');
			}

			$this->pdf->SetFont($this->config['other']['default_font'],'B', $this->_pdf_fontSize(16) );
			$this->pdf->Cell( $this->pdf->getPageWidth(), 20, '['. $error_msg .']', 0, 0, 'C', 0, '', 0 );
			unset($error_msg);
		}

		$this->profiler->stopTimer( 'PDF Table' );

		return TRUE;
	}

	function downloadOutput() {
		/*
		Misc::FileDownloadHeader('report.pdf', 'application/pdf', strlen($output));
		echo $output;
		Debug::writeToLog();
		exit;
		*/
		return TRUE;
	}

	function emailOutput() {
		return TRUE;
	}
}

class ReportPDF extends Report {

	function header() {
		return TRUE;
	}

	function footer() {
		return TRUE;
	}

}

//For advanced reports that require cell background colors, borders, formatting etc...
//Use objects for the cell data, which can then be checked and handled separately.
//Make them all static objects for faster access?
//Need to be able to overload specific formatting classes that will only ever be used by one report.
/*
ReportFormatter
	ReportTable (ReportObjectTableFormatter)
		ReportTableHeader
		ReportTableFooter
	ReportColumn (ReportObjectColumnFormatter)
	ReportRow (ReportObjectRowFormatter)
	ReportCell (ReportObjectCellFormatter)
		ReportCell<Type> (ie: ReportCellCurrency, ReportCellPercent, ReportCellNumeric, ReportCellMyCustomType)


//I think all the objects need to be seperate from one another, so we can pass objects around efficiently, then the main processor can handle inheritance that way.
//This might still be too heavy weight for what we need.
$report = new ReportFormatter();
$table = $report->addTable();
	$table->addColumn();
	$table->addColumn();
$row = $table->addRow();
	$row->addCell()
	$row->addCell()
	$row->addCell()

*/
?>
