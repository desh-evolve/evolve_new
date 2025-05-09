<?php

namespace App\Models\Policy;
use App\Models\Core\Factory;

use App\Models\Core\Debug;
use App\Models\Core\ExceptionListFactory;
use App\Models\Core\Option;
use App\Models\Core\TTLog;
use App\Models\Core\TTDate;
use App\Models\Core\UserDateListFactory;
use App\Models\Core\ExceptionFactory;
use App\Models\Core\UserDateTotalListFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyListFactory;
use App\Models\Punch\PunchListFactory;
use App\Models\Schedule\ScheduleListFactory;

class ExceptionPolicyFactory extends Factory {
	protected $table = 'exception_policy';
	protected $pk_sequence_name = 'exception_policy_id_seq'; //PK Sequence name

	protected $enable_grace = array('S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9', 'L1', 'L2', 'B1','B2', 'V1' );
	protected $enable_watch_window = array('S3', 'S4', 'S5', 'S6', 'O1','O2');
	protected static $premature_exceptions = array('M1', 'M2', 'M3', 'M4', 'L3', 'B4', 'B5', 'S8');

	protected static $premature_delay = 57600;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										//Schedule Exceptions
										'S1' /* A */ => ('Unscheduled Absence'),
										'S2' /* B */ => ('Not Scheduled'),
										'S3' /* C */ => ('In Early'),
										'S4' /* D */ => ('In Late'),
										'S5' /* E */ => ('Out Early'),
										'S6' /* F */ => ('Out Late'),

										'S7' /* G */ => ('Over Daily Scheduled Time'),
										'S8' /* H */ => ('Under Daily Scheduled Time'),
										'S9' => ('Over Weekly Scheduled Time'),
										//'S10' => ('Under Weekly Scheduled Time'), //Is this needed?

										//Add setting to set some sort of "Grace" period, or early warning system? Approaching overtime?
										//Have exception where they can set the cutoff in hours, and it triggers once the employee has exceeded the weekly hours.
										'O1' => ('Over Daily Time'),
										'O2' => ('Over Weekly Time'),

										//Punch Exceptions
										'M1' /* K */ => ('Missing In Punch'),
										'M2' /* L */ => ('Missing Out Punch'),
										'M3' /* P */  => ('Missing Lunch In/Out Punch'),
										'M4' => ('Missing Break In/Out Punch'),

										'L1' /* M */ => ('Long Lunch'),
										'L2' /* N */ => ('Short Lunch'),
										'L3' /* O */ => ('No Lunch'),

										'B1' => ('Long Break'),
										'B2' => ('Short Break'),
										'B3' => ('Too Many Breaks'),
										'B4' => ('Too Few Breaks'),
										'B5' => ('No Break'),
										//Worked too long without break/lunch, allow to set the time frame.
										//Make grace period the amount of time a break has to exceed, and watch window the longest they can work without a break?
										//No Break exception essentially handles this.
										//'B6' => ('Worked Too Long without Break')

										'V1' => ('TimeSheet Not Verified'),

										//Job Exceptions
										'J1' /* T J1 */  => ('Not Allowed On Job'),
										'J2' /* U J2 */  => ('Not Allowed On Task'),
										'J3' /* V J3 */  => ('Job Completed'),
										'J4' /* W J4 */  => ('No Job or Task'),
										//'J5' => ('No Task'), //Make J4 No Job only?
										//Add location based exceptions, ie: Restricted Location.
									);
				break;
			case 'severity':
				$retval = array(
											10 => ('Low'), //Black
											20 => ('Medium'), //Blue
											25 => ('High'), //Orange
											30 => ('Critical') //Rename to Critical: Red, was "High"
								);
				break;
			case 'email_notification':
				$retval = array(
											//Flex returns an empty object if 0 => None, so we make it a string and add a space infront ' 0' => None as a work around.
											' 0' => ('None'),
											10 => ('Employee'),
											20 => ('Supervisor'),
											//20 => ('Immediate Supervisor'),
											//20 => ('All Supervisors'),
											100 => ('Both')
								);
				break;
			case 'columns':
				$retval = array(
										'-1010-active' => ('Active'),
										'-1010-severity' => ('Severity'),
										'-1010-grace' => ('Grace'),
										'-1010-watch_window' => ('Watch Window'),
										'-1010-email_notification' => ('Email Notification'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
							);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'exception_policy_control_id' => 'ExceptionPolicyControl',
										'name' => 'Name',
										'type_id' => 'Type',
										'severity_id' => 'Severity',
										'is_enabled_watch_window' => 'isEnabledWatchWindow',
										'watch_window' => 'WatchWindow',
										'is_enabled_grace' => 'isEnabledGrace',
										'grace' => 'Grace',
										//'demerit' => 'Demerit',
										'email_notification_id' => 'EmailNotification',
										'active' => 'Active',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getExceptionPolicyControl() {
		if ( isset($this->data['exception_policy_control_id']) ) {
			return $this->data['exception_policy_control_id'];
		}

		return FALSE;
	}
	function setExceptionPolicyControl($id) {
		$id = trim($id);

		$epclf = new ExceptionPolicyControlListFactory();

		if ( $this->Validator->isResultSetWithRows(	'exception_policy_control',
													$epclf->getByID($id),
													('Exception Policy Control is invalid')
													) ) {

			$this->data['exception_policy_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getExceptionTypeDefaultValues( $exclude_exceptions, $product_edition = 10 ) {
		if ( !is_array($exclude_exceptions) ) {
			$exclude_exceptions = array();
		}
		$type_options = $this->getTypeOptions( $product_edition );

		$retarr = array();

		foreach ( $type_options as $type_id => $exception_name ) {
			//Skip excluded exceptions
			if ( in_array( $type_id, $exclude_exceptions ) ) {
				continue;
			}

			switch ( $type_id ) {
				case 'S1': //UnSchedule Absence
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 10,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S3': //In Early
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 10,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S4': //In Late
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 25,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S5': //Out Early
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S6': //Out Late
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 10,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 7200,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S7': //Over scheduled time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S8': //Under scheduled time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'S9': //Over Weekly Scheduled Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'O1': //Over Daily Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => (3600*8),
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'O2': //Over Weekly Time
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => (3600*40),
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M1': //Missing In Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M2': //Missing Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M3': //Missing Lunch In/Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'M4': //Missing Break In/Out Punch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 30,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L1': //Long Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L2': //Short Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 900,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'L3': //No Lunch
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B1': //Long Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 300,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B2': //Short Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 300,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B3': //Too Many Breaks
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B4': //Too Few Breaks
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'B5': //No Break
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 20,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'V1': //TimeSheet Not Verified
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 25,
												'email_notification_id' => 100,
												'demerit' => 0,
												'grace' => (48*3600), //48hrs grace period
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J1': //Not allowed on job
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J2': //Not allowed on task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J3': //Job completed
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => TRUE,
												'severity_id' => 20,
												'email_notification_id' => 20,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				case 'J4': //No Job Or Task
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
				default:
					$retarr[$type_id] = array(
												'id' => -1,
												'type_id' => $type_id,
												'name' => $type_options[$type_id],
												'active' => FALSE,
												'severity_id' => 10,
												'email_notification_id' => 0,
												'demerit' => 0,
												'grace' => 0,
												'is_enabled_grace' => $this->isEnabledGrace( $type_id ),
												'watch_window' => 0,
												'is_enabled_watch_window' => $this->isEnabledWatchWindow( $type_id )
												);
					break;
			}
		}

		return $retarr;
	}

	function getName() {
		return Option::getByKey( $this->getType(), $this->getTypeOptions( getTTProductEdition() ) );
	}

	function getTypeOptions( $product_edition = 10 ) {
		$options = $this->getOptions('type');

		if ( getTTProductEdition() != TT_PRODUCT_PROFESSIONAL OR $product_edition != 20 ) {
			$professional_exceptions = array('J1','J2','J3','J4');
			foreach( $professional_exceptions as $professional_exception ) {
				unset($options[$professional_exception]);
			}
		}

		return $options;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getSeverity() {
		if ( isset($this->data['severity_id']) ) {
			return $this->data['severity_id'];
		}

		return FALSE;
	}
	function setSeverity($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('severity') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'severity',
											$value,
											('Incorrect Severity'),
											$this->getOptions('severity')) ) {

			$this->data['severity_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWatchWindow() {
		if ( isset($this->data['watch_window']) ) {
			return $this->data['watch_window'];
		}

		return FALSE;
	}
	function setWatchWindow($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'watch_window',
													$value,
													('Incorrect Watch Window')) ) {

			$this->data['watch_window'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getGrace() {
		if ( isset($this->data['grace']) ) {
			return $this->data['grace'];
		}

		return FALSE;
	}
	function setGrace($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'grace',
													$value,
													('Incorrect grace value')) ) {

			$this->data['grace'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDemerit() {
		if ( isset($this->data['demerit']) ) {
			return $this->data['demerit'];
		}

		return FALSE;
	}
	function setDemerit($value) {
		$value = trim($value);

		if 	(	$value == 0
				OR $this->Validator->isNumeric(		'demerit',
													$value,
													('Incorrect demerit value')) ) {

			$this->data['demerit'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEmailNotification() {
		if ( isset($this->data['email_notification_id']) ) {
			return $this->data['email_notification_id'];
		}

		return FALSE;
	}
	function setEmailNotification($value) {
		$value = (int)trim($value);

		if ( $this->Validator->inArrayKey(	'email_notification',
											$value,
											('Incorrect Email Notification'),
											$this->getOptions('email_notification')) ) {

			$this->data['email_notification_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getActive() {
		return $this->fromBool( $this->data['active'] );
	}
	function setActive($bool) {
		$this->data['active'] = $this->toBool($bool);

		return TRUE;
	}

	function isEnabledGrace( $code = NULL ) {
		if ( $code == NULL ) {
			$code = $this->getType();
		}

		if ( in_array( $code, $this->enable_grace ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isEnabledWatchWindow( $code = NULL ) {
		if ( $code == NULL ) {
			$code = $this->getType();
		}

		if ( in_array( $code, $this->enable_watch_window ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function isPreMature( $code ) {
		if ( in_array( $code, self::$premature_exceptions ) ) {
			return TRUE;
		}

		return FALSE;
	}

	function calcExceptions( $user_date_id, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE ) {
		global $profiler;

		$profiler->startTimer( "ExceptionPolicy::calcExceptions()");

		if ( $user_date_id == '' ) {
			return FALSE;
		}
		Debug::text(' User Date ID: '. $user_date_id .' PreMature: '. (int)$enable_premature_exceptions , __FILE__, __LINE__, __METHOD__,10);

		//Get user date info
		$udlf = new UserDateListFactory(); 
		$udlf->getById( $user_date_id );
		if ( $udlf->getRecordCount() > 0 ) {
			$user_date_obj = $udlf->getCurrent();

			if ( $enable_future_exceptions == FALSE
					AND $user_date_obj->getDateStamp() > TTDate::getEndDayEpoch() ) {
				return FALSE;
			}
		} else {
			return FALSE;
		}

		//16hrs... If punches are older then this time, its no longer premature.
		//This should actually be the PayPeriod Schedule maximum shift time.
		if ( is_object($user_date_obj->getPayPeriodObject())
				AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
			self::$premature_delay = $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime();
			Debug::text(' Setting preMature Exception delay to maximum shift time: '. self::$premature_delay , __FILE__, __LINE__, __METHOD__,10);
		} else {
			self::$premature_delay = 57600;
		}

		//Get list of existing exceptions, so we can determine if we need to delete any. We can't delete them all blindly and re-create them
		//as this will send duplicate email notifications for every single punch.
		$existing_exceptions = array();
		$elf = new ExceptionListFactory();
		$elf->getByUserDateID( $user_date_id );
		if ( $elf->getRecordCount() > 0 ) {
			foreach( $elf->rs as $e_obj ) {
				$elf->data = (array)$e_obj;
				$e_obj = $elf;
				$existing_exceptions[] = array(
												'id' => $e_obj->getId(),
												'user_date_id' => $e_obj->getUserDateID(),
												'exception_policy_id' => $e_obj->getExceptionPolicyID(),
												'type_id' => $e_obj->getType(),
												'punch_id' => $e_obj->getPunchID(),
												'punch_control_id' => $e_obj->getPunchControlID(),
											);
			}
		}
		unset($elf, $e_obj);

		//Get all Punches on this date for this user.
		$plf = new PunchListFactory();
		$plf->getByUserDateId( $user_date_id );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::text(' Found Punches: '.  $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		}

		$slf = new ScheduleListFactory();
		$slf->getByUserDateIdAndStatusId( $user_date_id, 10 );
		if ( $slf->getRecordCount() > 0 ) {
			Debug::text(' Found Schedule: '.  $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		}

		$schedule_id_cache = NULL; //Cache schedule IDs so we don't need to do a lookup for every exception.

		$current_exceptions = array(); //Array holding current exception data.

		//Get all active exceptions.
		$eplf = new ExceptionPolicyListFactory();
		$eplf->getByPolicyGroupUserIdAndActive( $user_date_obj->getUser(), TRUE );
		if ( $eplf->getRecordCount() > 0 ) {
			Debug::text(' Found Active Exceptions: '.  $eplf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			foreach ( $eplf->rs as $ep_obj )  {
				$eplf->data = (array)$ep_obj;
				$ep_obj = $eplf;
				//Debug::text(' Found Exception Type: '. $ep_obj->getType() .' ID: '. $ep_obj->getID() .' Control ID: '. $ep_obj->getExceptionPolicyControl(), __FILE__, __LINE__, __METHOD__,10);

				if ( $enable_premature_exceptions == TRUE AND self::isPreMature( $ep_obj->getType() ) == TRUE ) {
					//Debug::text(' Premature Exception: '. $ep_obj->getType() , __FILE__, __LINE__, __METHOD__,10);
					$type_id = 5; //Pre-Mature
				} else {
					//Debug::text(' NOT Premature Exception: '. $ep_obj->getType() , __FILE__, __LINE__, __METHOD__,10);
					$type_id = 50; //Active
				}

				switch ( strtolower( $ep_obj->getType() ) ) {
					case 's1': 	//Unscheduled Absence... Anytime they are scheduled and have not punched in.
								//Ignore these exceptions if the schedule is after today (not including today),
								//so if a supervisors schedules an employee two days in advance they don't get a unscheduled
								//absence appearing right away.
								//Since we now trigger In Late/Out Late exceptions immediately after schedule time, only trigger this exception after
								//the schedule end time has passed.
						if ( $plf->getRecordCount() == 0 ) {
							if ( $slf->getRecordCount() > 0 ) {
								foreach( $slf->rs as $s_obj ) {
									$slf->data = (array)$s_obj;
									$s_obj = $slf;
									if ( $s_obj->getStatus() == 10
											//AND ( TTDate::getBeginDayEpoch( $s_obj->getStartTime() ) - TTDate::getBeginDayEpoch( TTDate::getTime() ) ) <= 0
											AND ( TTDate::getTime() >= $s_obj->getEndTime() )
											) {
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									}
								}
							} else {
								Debug::text(' NOT Scheduled', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 's2': //Not Scheduled
						$schedule_total_time = 0;

						if ( $slf->getRecordCount() == 0 ) {
							//FIXME: Assign this exception to the first punch of the day, so it can be related back to a punch branch/department?
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text(' Worked when wasnt scheduled', __FILE__, __LINE__, __METHOD__,10);
								$current_exceptions[] = array(
																'user_date_id' => $user_date_id,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							}
						} else {
							Debug::text(' IS Scheduled', __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 's3': //In Early
						if ( $plf->getRecordCount() > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								//Ignore punches that have the exact same timestamp, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																			);
											}
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						break;
					case 's4': //In Late
						if ( $plf->getRecordCount() > 0 ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;In Late. Punch: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__,10);
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getStartTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif (  TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getStartTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																			);
											}
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}
						}
						unset($scheduled_id_cache);

						//Late Starting their shift, with no punch yet, trigger exception if:
						//  - Schedule is found
						//	- Current time is after schedule start time and before schedule end time.
						// 	- Current time is after exception grace time
						//Make sure we take into account split shifts.
						Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Checking Late Starting Shift exception... Current time: '. TTDate::getDate('DATE+TIME', TTDate::getTime() ), __FILE__, __LINE__, __METHOD__,10);
						if ( $slf->getRecordCount() > 0 ) {
							foreach ( $slf->rs as $s_obj ) {
								$slf->data = (array)$s_obj;
								$s_obj = $slf;
								Debug::text(' Schedule Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__,10);
								if ( TTDate::getTime() >= $s_obj->getStartTime() AND TTDate::getTime() <= $s_obj->getEndTime() ) {
									if ( TTDate::inWindow( TTDate::getTime(), $s_obj->getStartTime(), $ep_obj->getGrace() ) == TRUE ) {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
									} else {
										if ( $plf->getRecordCount() > 0 ) {
											//See if we can find a punch within the schedule time, if so assume we already created the exception above.
											//Make sure we take into account the schedule policy start/stop window.
											//However in the case where a single schedule shift and just one punch exists, if an employee comes in really
											//early (1AM) before the schedule start/stop window it will trigger an In Late exception.
											//This could still be correct though if they only come in for an hour, then come in late for their shift later.
											//Schedule start/stop time needs to be correct.
											foreach ( $plf->rs as $p_obj ) {
												$plf->data = (array)$p_obj;
												$p_obj = $plf;
												if ( is_object($s_obj->getSchedulePolicyObject()) == FALSE OR $s_obj->inSchedule( $p_obj->getTimeStamp() ) == TRUE ) {
													Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Found punch for this schedule, skipping schedule...', __FILE__, __LINE__, __METHOD__,10);
													continue 2; //Skip to next schedule without creating exception.
												}
											}
										}

										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									}
								}
							}
						} else {
							Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 's5': //Out Early
						if ( $plf->getRecordCount() > 0 ) {
							//Loop through each punch, find out if they are scheduled, and if they are in early
							$prev_punch_time_stamp = FALSE;
							$total_punches = $plf->getRecordCount();
							$x=1;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								//Ignore punches that have the exact same timestamp and/or punches with the transfer flag, as they are likely transfer punches.
								//For Out Early, we have to wait until we are at the last punch, or there is a subsequent punch
								// to see if it matches the exact same time (transfer)
								//Therefore we need a two step confirmation before this exception can be triggered. Current punch, then next punch if it exists.
								if ( $p_obj->getTransfer() == FALSE AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() < $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);

												$tmp_exception = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																			);

												if ( $x	== $total_punches ) { //Trigger exception if we're the last punch.
													$current_exceptions[] = $tmp_exception;
												} else {
													//Save exception to be triggered if the next punch doesn't match the same time.
												}
											}
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								} elseif ( $p_obj->getType() == 10 AND $p_obj->getStatus() == 10 ) { //Normal In
									//This comes after an OUT punch, so we need to check if there are two punches
									//in a row with the same timestamp, if so ignore the exception.
									if ( isset($tmp_exception ) AND $p_obj->getTimeStamp() == $prev_punch_time_stamp ) {
										unset($tmp_exception);
									} elseif ( isset($tmp_exception) ) {
										$current_exceptions[] = $tmp_exception; //Set exception.
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();

								$x++;
							}
						}
						unset($tmp_exception, $x, $prev_punch_time_stamp);
						break;
					case 's6': //Out Late
						if ( $plf->getRecordCount() > 0  ) {
							$prev_punch_time_stamp = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );

								if ( $prev_punch_time_stamp != $p_obj->getTimeStamp() AND $p_obj->getType() == 10 AND $p_obj->getStatus() == 20 ) { //Normal Out
									if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
										$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
									}
									if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE ) {
										if ( $p_obj->getTimeStamp() > $p_obj->getScheduleObject()->getEndTime() ) {
											if ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
											} elseif ( TTDate::inWindow( $p_obj->getTimeStamp(), $p_obj->getScheduleObject()->getEndTime(), $ep_obj->getWatchWindow() ) == TRUE ) {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $p_obj->getID(),
																				'punch_control_id' => FALSE,
																			);
											}
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NO Schedule Found', __FILE__, __LINE__, __METHOD__,10);
									}
								}
								$prev_punch_time_stamp = $p_obj->getTimeStamp();
							}

							//Trigger exception if no out punch and we have passed schedule out time.
							//  - Schedule is found
							//	- Make sure the user is missing an OUT punch.
							//	- Current time is after schedule end time
							// 	- Current time is after exception grace time
							//  - Current time is before schedule end time + maximum shift time.
							if ( isset($punch_pairs) AND $slf->getRecordCount() > 0 ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('aFound Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('bFound Missing Out Punch: ', __FILE__, __LINE__, __METHOD__,10);

											foreach ( $slf->rs as $s_obj ) {
												$slf->data = (array)$s_obj;
												$s_obj = $slf;
												Debug::text('Punch: '. TTDate::getDate('DATE+TIME', $punch_pair[0]['time_stamp'] ) .' Schedule Start Time: '. TTDate::getDate('DATE+TIME', $s_obj->getStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__,10);
												//Because this is just an IN punch, make sure the IN punch is before the schedule end time
												//So we can eliminate split shift schedules.
												if ( $punch_pair[0]['time_stamp'] <= $s_obj->getEndTime()
														AND TTDate::getTime() >= $s_obj->getEndTime() AND TTDate::getTime() <= ($s_obj->getEndTime()+self::$premature_delay) ) {
													if ( TTDate::inWindow( TTDate::getTime(), $s_obj->getEndTime(), $ep_obj->getGrace() ) == TRUE ) {
														Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Within Grace time, IGNORE EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
													} else {
														Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;NOT Within Grace time, SET EXCEPTION: ', __FILE__, __LINE__, __METHOD__,10);
														$current_exceptions[] = array(
																						'user_date_id' => $user_date_id,
																						'exception_policy_id' => $ep_obj->getId(),
																						'type_id' => $type_id,
																						'punch_id' => FALSE,
																						'punch_control_id' => $punch_pair[0]['punch_control_id'],
																					);
													}
												}
											}
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm1': //Missing In Punch
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								//Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);

								if ( $type_id == 5 AND $p_obj->getTimeStamp() < (time()-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'punch_id' => $p_obj->getId() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									//Debug::Arr($punch_pair, 'Punch Pair for Control ID:'. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);

									if ( count($punch_pair) != 2 ) {
										Debug::text('aFound Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 20 ) { //Missing In Punch
											Debug::text('bFound Missing In Punch: ', __FILE__, __LINE__, __METHOD__,10);
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}
						break;
					case 'm2': //Missing Out Punch
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' Punch ID: '. $p_obj->getId() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);

								//This causes the exception to trigger if the first punch pair is more than the Maximum Shift time away from the current punch,
								//ie: In: 1:00AM, Out: 2:00AM, In 3:00PM (Maximum Shift Time less than 12hrs). The missing punch exception will be triggered immediately upon the 3:00PM punch.
								//if ( $type_id == 5 AND $p_obj->getTimeStamp() < (time()-self::$premature_delay) ) {
								//	$type_id = 50;
								//}

								$punch_pairs[$p_obj->getPunchControlID()][] = array( 'status_id' => $p_obj->getStatus(), 'punch_control_id' => $p_obj->getPunchControlID(), 'time_stamp' => $p_obj->getTimeStamp() );
							}

							if ( isset($punch_pairs) ) {
								foreach($punch_pairs as $punch_control_id => $punch_pair) {
									if ( count($punch_pair) != 2 ) {
										Debug::text('aFound Missing Punch: ', __FILE__, __LINE__, __METHOD__,10);

										if ( $punch_pair[0]['status_id'] == 10 ) { //Missing Out Punch
											Debug::text('bFound Missing Out Punch: ', __FILE__, __LINE__, __METHOD__,10);

											//Make sure we are at least MaximumShift Time from the matching In punch before trigging this exception.
											if ( $type_id == 5 AND $punch_pair[0]['time_stamp'] < (time()-self::$premature_delay) ) {
												$type_id = 50;
											}

											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => $punch_pair[0]['punch_control_id'],
																		);
										}
									} else {
										Debug::text('No Missing Punches...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($punch_pairs, $punch_pair);
						}

						break;
					case 'm3': //Missing Lunch In/Out punch
						if ( $plf->getRecordCount() > 0 ) {
							//We need to account for cases where they may punch IN from lunch first, then Out.
							//As well as just a Lunch In punch and nothing else.
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < (time()-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 20 ) { //Lunch
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Lunch/Out
											if ( !isset($punches[$key-1])
													OR ( isset($punches[$key-1]) AND is_object($punches[$key-1])
															AND ( $punches[$key-1]->getType() != 20
																OR $punches[$key-1]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Lunch/In
											if ( !isset($punches[$key+1]) OR ( isset($punches[$key+1]) AND is_object($punches[$key+1]) AND ( $punches[$key+1]->getType() != 20 OR $punches[$key+1]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Lunch In/Out Punch: ', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Lunch Punches match up.', __FILE__, __LINE__, __METHOD__,10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 'm4': //Missing Break In/Out punch
						if ( $plf->getRecordCount() > 0 ) {
							//We need to account for cases where they may punch IN from break first, then Out.
							//As well as just a break In punch and nothing else.
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $type_id == 5 AND $p_obj->getTimeStamp() < (time()-self::$premature_delay) ) {
									$type_id = 50;
								}

								$punches[] = $p_obj;
							}

							if ( isset($punches) AND is_array($punches) ) {
								foreach( $punches as $key => $p_obj ) {
									if ( $p_obj->getType() == 30 ) { //Break
										Debug::text(' Punch: Status: '. $p_obj->getStatus() .' Type: '. $p_obj->getType() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										if ( $p_obj->getStatus() == 10 ) {
											//Make sure previous punch is Break/Out
											if ( !isset($punches[$key-1])
													OR ( isset($punches[$key-1]) AND is_object($punches[$key-1])
															AND ( $punches[$key-1]->getType() != 30
																OR $punches[$key-1]->getStatus() != 20 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										} else {
											//Make sure next punch is Break/In
											if ( !isset($punches[$key+1]) OR ( isset($punches[$key+1]) AND is_object($punches[$key+1]) AND ( $punches[$key+1]->getType() != 30 OR $punches[$key+1]->getStatus() != 10 ) ) ) {
												//Invalid punch
												$invalid_punches[] = array('punch_id' => $p_obj->getId() );
											}
										}
									}
								}
								unset($punches, $key, $p_obj);

								if ( isset($invalid_punches) AND count($invalid_punches) > 0 ) {
									foreach( $invalid_punches as $invalid_punch_arr ) {
										Debug::text('Found Missing Break In/Out Punch: ', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $invalid_punch_arr['punch_id'],
																		'punch_control_id' => FALSE,
																	);
									}
									unset($invalid_punch_arr);
								} else {
									Debug::text('Lunch Punches match up.', __FILE__, __LINE__, __METHOD__,10);
								}
								unset($invalid_punches);
							}
						}
						break;
					case 's7': //Over Scheduled Hours
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$schedule_total_time = 0;

							if ( $slf->getRecordCount() > 0 ) {
								//Check for schedule policy
								foreach ( $slf->rs as $s_obj ) {
									$slf->data = (array)$s_obj;
									$s_obj = $slf;
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									$udtlf = new UserDateTotalListFactory();

									//Take into account auto-deduct/add meal policies
									//$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
									$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
									if ( $udtlf->getRecordCount() > 0 ) {
										foreach( $udtlf->rs as $udt_obj ) {
											$udtlf->data = (array)$udt_obj;
											$udt_obj = $udtlf;
											$daily_total_time += $udt_obj->getTotalTime();
										}
									}

									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__,10);

									if ( $daily_total_time > 0 AND $daily_total_time > ( $schedule_total_time + $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);

										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 's8': //Under Scheduled Hours
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$schedule_total_time = 0;

							if ( $slf->getRecordCount() > 0 ) {
								//Check for schedule policy
								foreach ( $slf->rs as $s_obj ) {
									$slf->data = (array)$s_obj;
									$s_obj = $slf;
									Debug::text(' Schedule Total Time: '. $s_obj->getTotalTime(), __FILE__, __LINE__, __METHOD__,10);

									$schedule_total_time += $s_obj->getTotalTime();
								}

								$daily_total_time = 0;
								if ( $schedule_total_time > 0 ) {
									//Get daily total time.
									$udtlf = new UserDateTotalListFactory();

									//Take into account auto-deduct/add meal policies
									//$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
									$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
									if ( $udtlf->getRecordCount() > 0 ) {
										foreach( $udtlf->rs as $udt_obj ) {
											$udtlf->data = (array)$udt_obj;
											$udt_obj = $udtlf;
											$daily_total_time += $udt_obj->getTotalTime();
										}
									}

									Debug::text(' Daily Total Time: '. $daily_total_time .' Schedule Total Time: '. $schedule_total_time, __FILE__, __LINE__, __METHOD__,10);

									if ( $daily_total_time < ( $schedule_total_time - $ep_obj->getGrace() ) ) {
										Debug::text(' Worked Under Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);

										if ( $type_id == 5 AND $user_date_obj->getDateStamp() < TTDate::getBeginDayEpoch( (time()-self::$premature_delay) ) ) {
											$type_id = 50;
										}

										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => FALSE,
																	);
									} else {
										Debug::text(' DID NOT Work Under Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text(' Not Scheduled', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 'o1': //Over Daily Time.
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$daily_total_time = 0;

							//Get daily total time.
							$udtlf = new UserDateTotalListFactory();

							//Take into account auto-deduct/add meal policies
							$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf->rs as $udt_obj ) {
									$udtlf->data = (array)$udt_obj;
									$udt_obj = $udtlf;
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}

							Debug::text(' Daily Total Time: '. $daily_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

							if ( $daily_total_time > 0 AND $daily_total_time > $ep_obj->getWatchWindow() ) {
								Debug::text(' Worked Over Daily Hours', __FILE__, __LINE__, __METHOD__,10);

								$current_exceptions[] = array(
																'user_date_id' => $user_date_id,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
							}
						}
						break;
					case 'o2': //Over Weekly Time.
					case 's9': //Over Weekly Scheduled Time.
						if ( $plf->getRecordCount() > 0 ) {
							//FIXME: Assign this exception to the last punch of the day, so it can be related back to a punch branch/department?
							//Get Pay Period Schedule info
							if ( is_object($user_date_obj->getPayPeriodObject())
									AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()) ) {
								$start_week_day_id = $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getStartWeekDay();
							} else {
								$start_week_day_id = 0;
							}
							Debug::text('Start Week Day ID: '. $start_week_day_id, __FILE__, __LINE__, __METHOD__, 10);

							$weekly_scheduled_total_time = 0;

							//Currently we only consider committed scheduled shifts. We may need to change this to take into account
							//recurring scheduled shifts that haven't been committed yet as well.
							//In either case though we should take into account the entires week worth of scheduled time even if we are only partially through
							//the week, that way we won't be triggering s9 exceptions on a Wed and a Fri or something, it will only occur on the last days of the week.
							if ( strtolower( $ep_obj->getType() ) == 's9' ) {
								$tmp_slf = new ScheduleListFactory();
								$tmp_slf->getByUserIdAndStartDateAndEndDate( $user_date_obj->getUser(), TTDate::getBeginWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id), TTDate::getEndWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id) );
								if ( $tmp_slf->getRecordCount() > 0 ) {
									foreach( $tmp_slf->rs as $s_obj ) {
										$tmp_slf->data = (array)$s_obj;
										$s_obj = $tmp_slf;
										$weekly_scheduled_total_time += $s_obj->getTotalTime();
									}
								}
								unset($tmp_slf, $s_obj);
							}

							//This ONLY takes in to account WORKED hours, not paid absence hours.
							$weekly_total_time = 0;

							//Get daily total time.
							$udtlf = new UserDateTotalListFactory();
							$weekly_total_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_date_obj->getUser(), TTDate::getBeginWeekEpoch($user_date_obj->getDateStamp(), $start_week_day_id), $user_date_obj->getDateStamp() );

							Debug::text(' Weekly Total Time: '. $weekly_total_time .' Weekly Scheduled Total Time: '. $weekly_scheduled_total_time .' Watch Window: '. $ep_obj->getWatchWindow() .' Grace: '. $ep_obj->getGrace() .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);
							//Don't trigger either of these exceptions unless both the worked and scheduled time is greater than 0. If they aren't scheduled at all
							//it should trigger a Unscheduled Absence exception instead of a over weekly scheduled time exception.
							if ( ( strtolower( $ep_obj->getType() ) == 'o2' AND $weekly_total_time > 0 AND $weekly_total_time > $ep_obj->getWatchWindow() )
									OR ( strtolower( $ep_obj->getType() ) == 's9' AND $weekly_scheduled_total_time > 0 AND $weekly_total_time > 0 AND $weekly_total_time > ( $weekly_scheduled_total_time + $ep_obj->getGrace() ) ) ) {
								Debug::text(' Worked Over Weekly Hours', __FILE__, __LINE__, __METHOD__,10);
								$current_exceptions[] = array(
																'user_date_id' => $user_date_id,
																'exception_policy_id' => $ep_obj->getId(),
																'type_id' => $type_id,
																'punch_id' => FALSE,
																'punch_control_id' => FALSE,
															);
							} else {
								Debug::text(' DID NOT Work Over Scheduled Hours', __FILE__, __LINE__, __METHOD__,10);
							}
						}

						break;
					case 'l1': //Long Lunch
					case 'l2': //Short Lunch
						if ( $plf->getRecordCount() > 0 ) {
							//Get all lunch punches.
							$pair = 0;
							$x = 0;
							$out_for_lunch = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 20 ) {
									$lunch_out_timestamp = $p_obj->getTimeStamp();
									$lunch_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_lunch = TRUE;
								} elseif ( $out_for_lunch == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 20) {
									$lunch_punch_arr[$pair][20] = $lunch_out_timestamp;
									$lunch_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_lunch = FALSE;
									$pair++;
									unset($lunch_out_timestamp);
								} else {
									$out_for_lunch = FALSE;
								}
							}

							if ( isset($lunch_punch_arr) ) {
								//Debug::Arr($lunch_punch_arr, 'Lunch Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $lunch_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$lunch_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Lunch Total Time: '. $lunch_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$mp_obj = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getMealPolicyObject();
										} else {
											$mplf = new MealPolicyListFactory();
											$mplf->getByPolicyGroupUserId( $user_date_obj->getUserObject()->getId() );
											if ( $mplf->getRecordCount() > 0 ) {
												Debug::text('Found Meal Policy to apply.', __FILE__, __LINE__, __METHOD__, 10);
												$mp_obj = $mplf->getCurrent();
											}
										}

										if ( isset($mp_obj) AND is_object($mp_obj) ) {
											$meal_policy_lunch_time = $mp_obj->getAmount();
											Debug::text('Meal Policy Time: '. $meal_policy_lunch_time, __FILE__, __LINE__, __METHOD__, 10);

											$add_exception = FALSE;
											if ( strtolower( $ep_obj->getType() ) == 'l1'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time > ($meal_policy_lunch_time + $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											} elseif ( strtolower( $ep_obj->getType() ) == 'l2'
													AND $meal_policy_lunch_time > 0
													AND $lunch_total_time > 0
													AND $lunch_total_time < ( $meal_policy_lunch_time - $ep_obj->getGrace() ) ) {
												$add_exception = TRUE;
											}

											if ( $add_exception == TRUE ) {
												Debug::text('Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);

												if ( isset($time_stamp_arr['punch_id']) ) {
													$punch_id = $time_stamp_arr['punch_id'];
												} else {
													$punch_id = FALSE;
												}

												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => $punch_id,
																				'punch_control_id' => FALSE,
																			);
												unset($punch_id);
											} else {
												Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
											}
										}

									} else {
										Debug::text(' Lunch Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Lunch Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'l3': //No Lunch
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a meal policy and base our
							//decision off that. We don't want a No Lunch exception on a 3hr short shift though.
							//Also ignore this exception if the lunch is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							//Find meal policy
							//Use scheduled meal policy first.
							$meal_policy_obj = NULL;
							if ( $slf->getRecordCount() > 0 ) {
								Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
								foreach ( $slf->rs as $s_obj ) {
									$slf->data = (array)$s_obj;
									$s_obj = $slf;
									if ( $s_obj->getSchedulePolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getType() != 10 ) {
										Debug::text('Found Schedule Meal Policy... Trigger Time: '. $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
										$meal_policy_obj = $s_obj->getSchedulePolicyObject()->getMealPolicyObject();
									} else {
										Debug::text('Schedule Meal Policy does not exist, or is auto-deduct?', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

								//Check if they have a meal policy, with no schedule.
								$mplf = new MealPolicyListFactory();
								$mplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
								if ( $mplf->getRecordCount() > 0 ) {
									foreach( $mplf->rs as $mp_obj ) {
										$mplf->data = (array)$mp_obj;
										$mp_obj = $mplf;
										if ( $mp_obj->getType() != 10 ) {
											Debug::text('Found UnScheduled meal Policy... Trigger Time: '. $mp_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
											$meal_policy_obj = $mp_obj;
										}
									}
									unset($mplf, $mp_obj);
								} else {
									//There is no  meal policy or schedule policy with a meal policy assigned to it
									//With out this we could still apply No meal exceptions, but they will happen even on
									//a 2minute shift.
									Debug::text('No Lunch policy, applying No meal exception.', __FILE__, __LINE__, __METHOD__,10);
									$meal_policy_obj = TRUE;
								}
							}

							if ( is_object($meal_policy_obj) OR $meal_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								$daily_total_time = 0;
								$udtlf = new UserDateTotalListFactory();
								$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
								if ( $udtlf->getRecordCount() > 0 ) {
									foreach( $udtlf->rs as $udt_obj ) {
										$udtlf->data = (array)$udt_obj;
										$udt_obj = $udtlf;
										$daily_total_time += $udt_obj->getTotalTime();
										$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
									}
								}
								Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);
								//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__,10);

								if ( $daily_total_time > 0 AND ( $meal_policy_obj === TRUE OR $daily_total_time > $meal_policy_obj->getTriggerTime() ) ) {
									//Check for meal punch.
									$meal_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf->rs as $p_obj ) {
										$plf->data = (array)$p_obj;
										$p_obj = $plf;
										if ( $p_obj->getType() == 20 ) { //20 = Lunch
											Debug::text('Found meal Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
											$meal_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $meal_policy_obj === TRUE OR $tmp_punch_total_time > $meal_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID() .' Total Time: '. $tmp_punch_total_time, __FILE__, __LINE__, __METHOD__,10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't meal the loop here, as we have to continue on and check for other meals.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $meal_punch == FALSE ) {
										Debug::text('Triggering No Lunch exception!', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
						}

/*
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a meal policy and base our
							//decision off that. We don't want a No Lunch exception on a 3hr shift though.
							//Also ignore this exception if the lunch is auto-deduct.
							$daily_total_time = 0;

							$udtlf = new UserDateTotalListFactory();
							$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}
							Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);

							if ( $daily_total_time > 0 ) {
								//Check for lunch punch.
								$lunch_punch = FALSE;
								foreach ( $plf as $p_obj ) {
									if ( $p_obj->getType() == 20 ) {
										Debug::text('Found Lunch Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										$lunch_punch = TRUE;
										break;
									}
								}

								if ( $lunch_punch == FALSE ) {
									Debug::text('DID NOT Find Lunch Punch... Checking meal policies. ', __FILE__, __LINE__, __METHOD__,10);

									//Use scheduled meal policy first.
									if ( $slf->getRecordCount() > 0 ) {
										Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
										foreach ( $slf as $s_obj ) {
											if ( $s_obj->getSchedulePolicyObject() !== FALSE
													AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject() !== FALSE
													AND $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getType() != 10 ) {
												Debug::text('Found Schedule Meal Policy... Trigger Time: '. $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
												if ( $daily_total_time > $s_obj->getSchedulePolicyObject()->getMealPolicyObject()->getTriggerTime() ) {
													Debug::text('Daily Total Time is After Schedule Meal Policy Trigger Time: ', __FILE__, __LINE__, __METHOD__,10);
													$current_exceptions[] = array(
																					'user_date_id' => $user_date_id,
																					'exception_policy_id' => $ep_obj->getId(),
																					'type_id' => $type_id,
																					'punch_id' => FALSE,
																					'punch_control_id' => FALSE,
																				);
												}
											} else {
												Debug::text('Schedule Meal Policy does not exist, or is auto-deduct?', __FILE__, __LINE__, __METHOD__,10);
											}
										}
									} else {
										Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

										//Check if they have a meal policy, with no schedule.
										$mplf = new MealPolicyListFactory();
										$mplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
										if ( $mplf->getRecordCount() > 0 ) {
											Debug::text('Found UnScheduled Meal Policy...', __FILE__, __LINE__, __METHOD__,10);

											$m_obj = $mplf->getCurrent();
											if ( $daily_total_time > $m_obj->getTriggerTime()
													AND $m_obj->getType() == 20 ) {
												Debug::text('Daily Total Time is After Schedule Meal Policy Trigger Time: '. $m_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => FALSE,
																			);
											} else {
												Debug::text('Auto-deduct meal policy, ignorning this exception.', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											//There is no  meal policy or schedule policy with a meal policy assigned to it
											//With out this we could still apply No Lunch exceptions, but they will happen even on
											//a 2minute shift.
											Debug::text('No meal policy, applying No Lunch exception.', __FILE__, __LINE__, __METHOD__,10);
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																		);
										}
									}

								} else {
									Debug::text('Found Lunch Punch... Ignoring this exception. ', __FILE__, __LINE__, __METHOD__,10);
								}
							}
						}
*/
						break;
					case 'b1': //Long Break
					case 'b2': //Short Break
						if ( $plf->getRecordCount() > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_break = TRUE;
								} elseif ( $out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
									$break_punch_arr[$pair][20] = $break_out_timestamp;
									$break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_break = FALSE;
									$pair++;
									unset($break_out_timestamp);
								} else {
									$out_for_break = FALSE;
								}
							}
							unset($pair);

							if ( isset($break_punch_arr) ) {
								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										$bplf = new BreakPolicyListFactory();
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$break_policy_ids = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
											$bplf->getByIdAndCompanyId( $break_policy_ids, $user_date_obj->getUserObject()->getCompany() );
										} else {
											$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
										}
										unset($break_policy_ids);

										if ( $bplf->getRecordCount() > 0 ) {
											Debug::text('Found Break Policy(ies) to apply: '. $bplf->getRecordCount() .' Pair: '. $pair, __FILE__, __LINE__, __METHOD__, 10);

											foreach( $bplf->rs as $bp_obj ) {
												$bplf->data = (array)$bp_obj;
												$bp_obj = $bplf;
												$bp_objs[] = $bp_obj;
											}
											unset($bplf, $bp_obj);

											if ( isset($bp_objs[$pair]) AND is_object($bp_objs[$pair]) ) {
												$bp_obj = $bp_objs[$pair];

												$break_policy_break_time = $bp_obj->getAmount();
												Debug::text('Break Policy Time: '. $break_policy_break_time .' ID: '. $bp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

												$add_exception = FALSE;
												if ( strtolower( $ep_obj->getType() ) == 'b1'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time > ($break_policy_break_time + $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												} elseif ( strtolower( $ep_obj->getType() ) == 'b2'
														AND $break_policy_break_time > 0
														AND $break_total_time > 0
														AND $break_total_time < ( $break_policy_break_time - $ep_obj->getGrace() ) ) {
													$add_exception = TRUE;
												}

												if ( $add_exception == TRUE ) {
													Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

													if ( isset($time_stamp_arr['punch_id']) ) {
														$punch_id = $time_stamp_arr['punch_id'];
													} else {
														$punch_id = FALSE;
													}

													$current_exceptions[] = array(
																					'user_date_id' => $user_date_id,
																					'exception_policy_id' => $ep_obj->getId(),
																					'type_id' => $type_id,
																					'punch_id' => $punch_id,
																					'punch_control_id' => FALSE,
																				);
													unset($punch_id);
												} else {
													Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
												}

												unset($bp_obj);
											}
											unset( $bp_objs );
										}
									} else {
										Debug::text(' Break Punches not paired... Skipping!', __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							} else {
								Debug::text(' No Break Punches found, or none are paired.', __FILE__, __LINE__, __METHOD__, 10);
							}
						}
						break;
					case 'b3': //Too Many Breaks
					case 'b4': //Too Few Breaks
						if ( $plf->getRecordCount() > 0 ) {
							//Get all break punches.
							$pair = 0;
							$x = 0;
							$out_for_break = FALSE;
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 20 AND $p_obj->getType() == 30 ) {
									$break_out_timestamp = $p_obj->getTimeStamp();
									$break_punch_arr[$pair]['punch_id'] = $p_obj->getId();
									$out_for_break = TRUE;
								} elseif ( $out_for_break == TRUE AND $p_obj->getStatus() == 10 AND $p_obj->getType() == 30) {
									$break_punch_arr[$pair][20] = $break_out_timestamp;
									$break_punch_arr[$pair][10] = $p_obj->getTimeStamp();
									$out_for_break = FALSE;
									$pair++;
									unset($break_out_timestamp);
								} else {
									$out_for_break = FALSE;
								}
							}
							unset($pair);

							//Get daily total time.
							$daily_total_time = 0;
							$udtlf = new UserDateTotalListFactory();
							$udtlf->getByUserDateIdAndStatusAndType( $user_date_id, 10, 10 );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf->rs as $udt_obj ) {
									$udtlf->data = (array)$udt_obj;
									$udt_obj = $udtlf;
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}

							Debug::text(' Daily Total Time: '. $daily_total_time .' User Date ID: '. $user_date_id, __FILE__, __LINE__, __METHOD__,10);

							//Make sure we take into account how long they have currently worked, so we don't
							//say too few breaks for 3hr shift that they employee took one break on.
							//Trigger this exception if the employee doesn't take a break at all?
							if ( isset($break_punch_arr) ) {
								$total_breaks = count($break_punch_arr);

								//Debug::Arr($break_punch_arr, 'Break Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

								foreach( $break_punch_arr as $pair => $time_stamp_arr ) {
									if ( isset($time_stamp_arr[10]) AND isset($time_stamp_arr[20]) ) {
										$break_total_time = bcsub($time_stamp_arr[10], $time_stamp_arr[20] );
										Debug::text(' Break Total Time: '. $break_total_time, __FILE__, __LINE__, __METHOD__, 10);

										if ( !isset($scheduled_id_cache[$p_obj->getID()]) ) {
											$scheduled_id_cache[$p_obj->getID()] = $p_obj->findScheduleID( NULL, $user_date_obj->getUser() );
										}

										//Check to see if they have a schedule policy
										$bplf = new BreakPolicyListFactory();
										if ( $p_obj->setScheduleID( $scheduled_id_cache[$p_obj->getID()] ) == TRUE
												AND is_object( $p_obj->getScheduleObject() ) == TRUE
												AND is_object( $p_obj->getScheduleObject()->getSchedulePolicyObject() ) == TRUE ) {
											$break_policy_ids = $p_obj->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
											$bplf->getByIdAndCompanyId( $break_policy_ids, $user_date_obj->getUserObject()->getCompany() );
										} else {
											//$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
											$bplf->getByPolicyGroupUserIdAndDayTotalTime( $user_date_obj->getUser(), $daily_total_time );
										}
										unset($break_policy_ids);

										$allowed_breaks = $bplf->getRecordCount();

										$add_exception = FALSE;
										if ( strtolower( $ep_obj->getType() ) == 'b3' AND $total_breaks > $allowed_breaks ) {
											Debug::text(' Too many breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} elseif ( strtolower( $ep_obj->getType() ) == 'b4' AND $total_breaks < $allowed_breaks )  {
											Debug::text(' Too few breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
											$add_exception = TRUE;
										} else {
											Debug::text(' Proper number of breaks taken...', __FILE__, __LINE__, __METHOD__, 10);
										}

										if ( $add_exception == TRUE
												AND ( strtolower( $ep_obj->getType() ) == 'b4'
													 OR ( strtolower( $ep_obj->getType() ) == 'b3' AND $pair > ($allowed_breaks-1) )  ) ) {
											Debug::text('Adding Exception! '. $ep_obj->getType(), __FILE__, __LINE__, __METHOD__, 10);

											if ( isset($time_stamp_arr['punch_id']) AND strtolower( $ep_obj->getType() ) == 'b3' ) {
												$punch_id = $time_stamp_arr['punch_id'];
											} else {
												$punch_id = FALSE;
											}

											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => $punch_id,
																			'punch_control_id' => FALSE,
																		);
											unset($punch_id);
										} else {
											Debug::text('Not Adding Exception!', __FILE__, __LINE__, __METHOD__, 10);
										}

									}
								}
							}
						}
						break;
					case 'b5': //No Break
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a break policy and base our
							//decision off that. We don't want a No Break exception on a 3hr short shift though.
							//Also ignore this exception if the break is auto-deduct.
							//**Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.

							//Find break policy
							//Use scheduled break policy first.
							$break_policy_obj = NULL;
							if ( $slf->getRecordCount() > 0 ) {
								Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
								foreach ( $slf->rs as $s_obj ) {
									$slf->data = (array)$s_obj;
									$s_obj = $slf;
									if ( $s_obj->getSchedulePolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getBreakPolicyObject() !== FALSE
											AND $s_obj->getSchedulePolicyObject()->getBreakPolicyObject()->getType() != 10 ) {
										Debug::text('Found Schedule Break Policy... Trigger Time: '. $s_obj->getSchedulePolicyObject()->getBreakPolicyObject()->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
										$break_policy_obj = $s_obj->getSchedulePolicyObject()->getBreakPolicyObject();
									} else {
										Debug::text('Schedule Break Policy does not exist, or is auto-deduct?', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							} else {
								Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

								//Check if they have a break policy, with no schedule.
								$bplf = new BreakPolicyListFactory();
								$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
								if ( $bplf->getRecordCount() > 0 ) {
									Debug::text('Found UnScheduled Break Policy...', __FILE__, __LINE__, __METHOD__,10);
									foreach( $bplf->rs as $bp_obj ) {
										$bplf->data = (array)$bp_obj;
										$bp_obj = $bplf;
										if ( $bp_obj->getType() != 10 ) {
											$break_policy_obj = $bp_obj;
										}
									}
									unset($bplf, $bp_obj);
								} else {
									//There is no  break policy or schedule policy with a break policy assigned to it
									//With out this we could still apply No Break exceptions, but they will happen even on
									//a 2minute shift.
									Debug::text('No break policy, applying No break exception.', __FILE__, __LINE__, __METHOD__,10);
									$break_policy_obj = TRUE;
								}
							}

							if ( is_object($break_policy_obj) OR $break_policy_obj === TRUE ) {
								$punch_control_id = FALSE;

								$daily_total_time = 0;
								$udtlf = new UserDateTotalListFactory();
								$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
								if ( $udtlf->getRecordCount() > 0 ) {
									foreach( $udtlf->rs as $udt_obj ) {
										$udtlf->data = (array)$udt_obj;
										$udt_obj = $udtlf;
										$daily_total_time += $udt_obj->getTotalTime();
										$punch_control_total_time[$udt_obj->getPunchControlID()] = $udt_obj->getTotalTime();
									}
								}
								Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);
								//Debug::Arr($punch_control_total_time, 'Punch Control Total Time: ', __FILE__, __LINE__, __METHOD__,10);

								if ( $daily_total_time > 0 AND ( $break_policy_obj === TRUE OR $daily_total_time > $break_policy_obj->getTriggerTime() ) ) {
									//Check for break punch.
									$break_punch = FALSE;
									$tmp_punch_total_time = 0;
									$tmp_punch_control_ids = array();
									foreach ( $plf->rs as $p_obj ) {
										$plf->data = (array)$p_obj;
										$p_obj = $plf;
										if ( $p_obj->getType() == 30 ) { //30 = Break
											Debug::text('Found break Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
											$break_punch = TRUE;
											break;
										}

										if ( isset($punch_control_total_time[$p_obj->getPunchControlID()]) AND !isset($tmp_punch_control_ids[$p_obj->getPunchControlID()]) ) {
											$tmp_punch_total_time += $punch_control_total_time[$p_obj->getPunchControlID()];
											if ( $punch_control_id === FALSE AND ( $break_policy_obj === TRUE OR $tmp_punch_total_time > $break_policy_obj->getTriggerTime() ) ) {
												Debug::text('Found punch control for exception: '. $p_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__,10);
												$punch_control_id = $p_obj->getPunchControlID();
												//Don't break the loop here, as we have to continue on and check for other breaks.
											}
										}
										$tmp_punch_control_ids[$p_obj->getPunchControlID()] = TRUE;
									}
									unset($tmp_punch_total_time, $tmp_punch_control_ids);

									if ( $break_punch == FALSE ) {
										Debug::text('Triggering No Break exception!', __FILE__, __LINE__, __METHOD__,10);
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => FALSE,
																		'punch_control_id' => $punch_control_id,
																	);
									}
								}
							}
						}
/*
						if ( $plf->getRecordCount() > 0 ) {
							//If they are scheduled or not, we can check for a break policy and base our
							//decision off that. We don't want a No Lunch exception on a 3hr shift though.
							//Also ignore this exception if the lunch is auto-deduct.
							//Try to assign this exception to a specific punch control id, so we can do searches based on punch branch.
							$daily_total_time = 0;

							$udtlf = new UserDateTotalListFactory();
							$udtlf->getByUserDateIdAndStatus( $user_date_id, 20 );
							if ( $udtlf->getRecordCount() > 0 ) {
								foreach( $udtlf as $udt_obj ) {
									$daily_total_time += $udt_obj->getTotalTime();
								}
							}
							Debug::text('Day Total Time: '. $daily_total_time, __FILE__, __LINE__, __METHOD__,10);

							if ( $daily_total_time > 0 ) {
								//Check for break punch.
								$break_punch = FALSE;
								foreach ( $plf as $p_obj ) {
									if ( $p_obj->getType() == 30 ) { //30 = Break
										Debug::text('Found break Punch: '. $p_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__,10);
										$break_punch = TRUE;
										break;
									}
								}

								if ( $break_punch == FALSE ) {
									Debug::text('DID NOT Find break Punch... Checking break policies. ', __FILE__, __LINE__, __METHOD__,10);

									//Use scheduled break policy first.
									if ( $slf->getRecordCount() > 0 ) {
										Debug::text('Schedule Found...', __FILE__, __LINE__, __METHOD__,10);
										foreach ( $slf as $s_obj ) {
											if ( $s_obj->getSchedulePolicyObject() !== FALSE
													AND $s_obj->getSchedulePolicyObject()->getBreakPolicyObject() !== FALSE
													AND $s_obj->getSchedulePolicyObject()->getBreakPolicyObject()->getType() != 10 ) {
												Debug::text('Found Schedule Break Policy... Trigger Time: '. $s_obj->getSchedulePolicyObject()->getBreakPolicyObject()->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
												if ( $daily_total_time > $s_obj->getSchedulePolicyObject()->getBreakPolicyObject()->getTriggerTime() ) {
													Debug::text('Daily Total Time is After Schedule Break Policy Trigger Time: ', __FILE__, __LINE__, __METHOD__,10);
													$current_exceptions[] = array(
																					'user_date_id' => $user_date_id,
																					'exception_policy_id' => $ep_obj->getId(),
																					'type_id' => $type_id,
																					'punch_id' => FALSE,
																					'punch_control_id' => FALSE,
																				);
												}
											} else {
												Debug::text('Schedule Break Policy does not exist, or is auto-deduct?', __FILE__, __LINE__, __METHOD__,10);
											}
										}
									} else {
										Debug::text('No Schedule Found...', __FILE__, __LINE__, __METHOD__,10);

										//Check if they have a break policy, with no schedule.
										$bplf = new BreakPolicyListFactory();
										$bplf->getByPolicyGroupUserId( $user_date_obj->getUser() );
										if ( $bplf->getRecordCount() > 0 ) {
											Debug::text('Found UnScheduled Break Policy...', __FILE__, __LINE__, __METHOD__,10);

											$b_obj = $bplf->getCurrent();
											//Make sure we include Normal and Auto-Add break policies, as they likely need to punch out for those.
											if ( $daily_total_time > $b_obj->getTriggerTime()
													AND $b_obj->getType() != 10 ) {
												Debug::text('Daily Total Time is After Schedule Break Policy Trigger Time: '. $b_obj->getTriggerTime(), __FILE__, __LINE__, __METHOD__,10);
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => FALSE,
																			);
											} else {
												Debug::text('Auto-deduct break policy, ignorning this exception.', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											//There is no  break policy or schedule policy with a break policy assigned to it
											//With out this we could still apply No Break exceptions, but they will happen even on
											//a 2minute shift.
											Debug::text('No break policy, applying No break exception.', __FILE__, __LINE__, __METHOD__,10);
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																		);
										}
									}

								} else {
									Debug::text('Found break Punch... Ignoring this exception. ', __FILE__, __LINE__, __METHOD__,10);
								}
							}
						}
*/
						break;
					case 'v1': //TimeSheet Not Verified
						//Get pay period schedule data, determine if timesheet verification is even enabled.
						if ( is_object($user_date_obj->getPayPeriodObject())
								AND is_object($user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject())
								AND $user_date_obj->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() > 10 ) {
							Debug::text('Verification enabled... Window Start: '. TTDate::getDate('DATE+TIME', $user_date_obj->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate() ) .' Grace Time: '. $ep_obj->getGrace() , __FILE__, __LINE__, __METHOD__,10);

							//*Only* trigger this exception on the last day of the pay period, because when the pay period is verified it has to force the last day to be recalculated.
							//Ignore timesheets without any time, (worked and absence). Or we could use the Watch Window to specify the minimum time required on
							//a timesheet to trigger this instead?
							//Make sure we are after the timesheet window start date + the grace period.
							if (	$user_date_obj->getPayPeriodObject()->getStatus() != 50
									AND TTDate::getTime() >= ($user_date_obj->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()+$ep_obj->getGrace())
									AND TTDate::getBeginDayEpoch( $user_date_obj->getDateStamp() ) == TTDate::getBeginDayEpoch( $user_date_obj->getPayPeriodObject()->getEndDate() )
									) {

									//Get pay period total time, include worked and paid absence time.
									$udtlf = new UserDateTotalListFactory();
									$total_time = $udtlf->getTimeSumByUserIDAndPayPeriodId( $user_date_obj->getUser(), $user_date_obj->getPayPeriodObject()->getID() );
									if ( $total_time > 0 ) {
										//Check to see if pay period has been verified or not yet.
										$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();
										$pptsvlf->getByPayPeriodIdAndUserId( $user_date_obj->getPayPeriodObject()->getId(), $user_date_obj->getUser() );

										$pay_period_verified = FALSE;
										if ( $pptsvlf->getRecordCount() > 0 ) {
											$pay_period_verified = $pptsvlf->getCurrent()->getAuthorized();
										}

										if ( $pay_period_verified == FALSE ) {
											//Always allow for emailing this exception because it can be triggered after a punch is modified and
											//any supervisor would need to be notified to verify the timesheet again.
											$current_exceptions[] = array(
																			'user_date_id' => $user_date_id,
																			'exception_policy_id' => $ep_obj->getId(),
																			'type_id' => $type_id,
																			'punch_id' => FALSE,
																			'punch_control_id' => FALSE,
																			'enable_email_notification' => TRUE,
																		);
										} else {
											Debug::text('TimeSheet has already been authorized!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('Timesheet does not have any worked or paid absence time...', __FILE__, __LINE__, __METHOD__,10);
									}
									unset($udtlf, $total_time);
							} else {
								Debug::text('Not within timesheet verification window, or not after grace time.', __FILE__, __LINE__, __METHOD__,10);
							}
						} else {
							Debug::text('No Pay Period Schedule or TimeSheet Verificiation disabled...', __FILE__, __LINE__, __METHOD__,10);
						}
						break;
					case 'j1': //Not Allowed on Job
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.
										$jlf = new JobListFactory();
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedUser( $user_date_obj->getUser() ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;User allowed on Job!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($j_obj);
						}
						break;
					case 'j2': //Not Allowed on Task
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 AND $p_obj->getPunchControlObject()->getJobItem() > 0 ) {
										//Found job punch, check job settings.
										$jlf = new JobListFactory();
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											if ( $j_obj->isAllowedItem( $p_obj->getPunchControlObject()->getJobItem() ) == FALSE ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Job item allowed on job!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}

							unset($j_obj);
						}
						break;
					case 'j3': //Job already completed
						if ( $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								if ( $p_obj->getStatus() == 10 ) { //In punches
									if ( is_object( $p_obj->getPunchControlObject() ) AND $p_obj->getPunchControlObject()->getJob() > 0 ) {
										//Found job punch, check job settings.
										$jlf = new JobListFactory();
										$jlf->getById( $p_obj->getPunchControlObject()->getJob() );
										if ( $jlf->getRecordCount() > 0 ) {
											$j_obj = $jlf->getCurrent();

											//Status is completed and the User Date Stamp is greater then the job end date.
											//If no end date is set, ignore this.
											if ( $j_obj->getStatus() == 30 AND $j_obj->getEndDate() != FALSE AND $user_date_obj->getDateStamp() > $j_obj->getEndDate() ) {
												$current_exceptions[] = array(
																				'user_date_id' => $user_date_id,
																				'exception_policy_id' => $ep_obj->getId(),
																				'type_id' => $type_id,
																				'punch_id' => FALSE,
																				'punch_control_id' => $p_obj->getPunchControlId(),
																			);
											} else {
												Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Job Not Completed!', __FILE__, __LINE__, __METHOD__,10);
											}
										} else {
											Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Job not found!', __FILE__, __LINE__, __METHOD__,10);
										}
									} else {
										Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;Not a Job Punch...', __FILE__, __LINE__, __METHOD__,10);
									}
								}
							}
							unset($j_obj);
						}
						break;
					case 'j4': //No Job or Task
						$add_exception = FALSE;
						if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL AND $plf->getRecordCount() > 0 ) {
							foreach ( $plf->rs as $p_obj ) {
								$plf->data = (array)$p_obj;
								$p_obj = $plf;
								//In punches only
								if ( $p_obj->getStatus() == 10 AND is_object( $p_obj->getPunchControlObject() ) ) {
									//If no Tasks are setup, ignore checking them.
									if ( $p_obj->getPunchControlObject()->getJob() == ''
											OR $p_obj->getPunchControlObject()->getJob() == 0
											OR $p_obj->getPunchControlObject()->getJob() == FALSE  ) {
										$add_exception = TRUE;
									}

									if ( $p_obj->getPunchControlObject()->getJobItem() == ''
											OR $p_obj->getPunchControlObject()->getJobItem() == 0
											OR $p_obj->getPunchControlObject()->getJobItem() == FALSE ) {

										//Make sure at least one task exists before triggering exception.
										$jilf = new JobItemListFactory();
										$jilf->getByCompanyID( $user_date_obj->getUserObject()->getCompany(), 1 ); //Limit to just 1 record.
										if ( $jilf->getRecordCount() > 0 ) {
											$add_exception = TRUE;
										}
									}

									if ( $add_exception === TRUE ) {
										$current_exceptions[] = array(
																		'user_date_id' => $user_date_id,
																		'exception_policy_id' => $ep_obj->getId(),
																		'type_id' => $type_id,
																		'punch_id' => $p_obj->getId(),
																		'punch_control_id' => $p_obj->getPunchControlId(),
																	);
									}
								}
							}
						}
						break;
					default:
						Debug::text('BAD, should never get here: ', __FILE__, __LINE__, __METHOD__,10);
						break;
				}
			}
		}
		unset($ep_obj);

		$exceptions = self::diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions );
		if ( is_array($exceptions) ) {
			if ( isset($exceptions['create_exceptions']) AND is_array($exceptions['create_exceptions']) AND count($exceptions['create_exceptions']) > 0 ) {
				Debug::text('Creating new exceptions... Total: '. count($exceptions['create_exceptions']), __FILE__, __LINE__, __METHOD__,10);
				foreach( $exceptions['create_exceptions'] as $tmp_exception ) {
					$ef = new ExceptionFactory();
					$ef->setUserDateID( $tmp_exception['user_date_id'] );
					$ef->setExceptionPolicyID( $tmp_exception['exception_policy_id'] );
					$ef->setType( $tmp_exception['type_id'] );
					if ( isset($tmp_exception['punch_control_id']) AND $tmp_exception['punch_control_id'] != '' ) {
						$ef->setPunchControlId( $tmp_exception['punch_control_id'] );
					}
					if ( isset($tmp_exception['punch_id']) AND $tmp_exception['punch_id'] != '' ) {
						$ef->setPunchId( $tmp_exception['punch_id'] );
					}
					$ef->setEnableDemerits( TRUE );
					if ( $ef->isValid() ) {
						if ( $enable_premature_exceptions == TRUE OR ( isset($tmp_exception['enable_email_notification']) AND $tmp_exception['enable_email_notification'] == TRUE ) ) {
							$eplf = new ExceptionPolicyListFactory();
							$eplf->getById( $tmp_exception['exception_policy_id'] );
							if ( $eplf->getRecordCount() == 1 ) {
								$ep_obj = $eplf->getCurrent();
								$ef->emailException( $user_date_obj->getUserObject(), $user_date_obj, $ep_obj );
							}
						}
						$ef->Save();
					}
				}
			}

			if ( isset($exceptions['delete_exceptions']) AND is_array($exceptions['delete_exceptions']) AND count($exceptions['delete_exceptions']) > 0 ) {
				Debug::Text('Deleting no longer valid exceptions... Total: '. count($exceptions['delete_exceptions']), __FILE__, __LINE__, __METHOD__,10);
				$ef = new ExceptionFactory();
				$ef->bulkDelete( $exceptions['delete_exceptions'] );
			}
		}
		$profiler->stopTimer( "ExceptionPolicy::calcExceptions()");

		return TRUE;
	}

	//This function needs to determine which new exceptions to create, and which old exceptions are no longer valid and to delete.
	function diffExistingAndCurrentExceptions( $existing_exceptions, $current_exceptions ) {
		//Debug::Arr($existing_exceptions, 'Existing Exceptions: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($current_exceptions, 'Current Exceptions: ', __FILE__, __LINE__, __METHOD__,10);

		if ( is_array($existing_exceptions) AND count($existing_exceptions) == 0 ) {
			//No existing exceptions, nothing to delete or compare, just create new exceptions.
			return array( 'create_exceptions' => $current_exceptions, 'delete_exceptions' => array() );
		}

		if ( is_array($current_exceptions) AND count($current_exceptions) == 0 ) {
			//No current exceptions, delete all existing exceptions.
			foreach( $existing_exceptions as $existing_exception ) {
				$delete_exceptions[] = $existing_exception['id'];
			}
			return array( 'create_exceptions' => array(), 'delete_exceptions' => $delete_exceptions );
		}

		$new_exceptions = $current_exceptions; //Copy array so we can work from the copy.

		//Remove any current exceptions that already exist as existing exceptions.
		foreach( $current_exceptions as $current_key => $current_exception ) {
			foreach( $existing_exceptions as $existing_key => $existing_exception ) {
				//Need to match all elements except 'id'.
				if ( 	$current_exception['exception_policy_id'] == $existing_exception['exception_policy_id']
						AND
						$current_exception['type_id'] == $existing_exception['type_id']
						AND
						$current_exception['user_date_id'] == $existing_exception['user_date_id']
						AND
						$current_exception['punch_control_id'] == $existing_exception['punch_control_id']
						AND
						$current_exception['punch_id'] == $existing_exception['punch_id']
					) {
					//Debug::text('Removing current exception that matches existing exception: '. $current_key, __FILE__, __LINE__, __METHOD__,10);
					unset($new_exceptions[$current_key]);
				} else {
					//Debug::text('NOT Removing current exception that matches existing exception: Current: '. $current_key .' Existing: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		//Mark any existing exceptions that are not in the current exception list for deletion.
		$delete_exceptions = array();

		$delete_exception = FALSE;
		$total_current_exceptions = count($current_exceptions);
		foreach( $existing_exceptions as $existing_key => $existing_exception ) {
			$match_count = $total_current_exceptions;

			foreach( $current_exceptions as $current_key => $current_exception ) {
				if ( !( $current_exception['exception_policy_id'] == $existing_exception['exception_policy_id'] AND $current_exception['type_id'] == $existing_exception['type_id'] AND $current_exception['user_date_id'] == $existing_exception['user_date_id'] AND $current_exception['punch_control_id'] == $existing_exception['punch_control_id'] AND $current_exception['punch_id'] == $existing_exception['punch_id'] ) ) {
					$match_count--;
				}
				//Debug::text('aDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
			}

			if ( $match_count == 0 ) {
				//Debug::text('bDetermining if we should delete this exception... Match Count: '. $match_count .' Total: '. $total_current_exceptions .' Existing Key: '. $existing_key, __FILE__, __LINE__, __METHOD__,10);
				$delete_exceptions[] = $existing_exception['id'];
			}
		}

		$retarr = array( 'create_exceptions' => $new_exceptions, 'delete_exceptions' => $delete_exceptions );
		//Debug::Arr($retarr, 'RetArr Exceptions: ', __FILE__, __LINE__, __METHOD__,10);
		return $retarr;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

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

			$this->setCreatedAndUpdatedColumns( $data );

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
						case 'is_enabled_watch_window':
						case 'is_enabled_grace':
							$function = str_replace('_', '', $variable);
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	//This is called for every record everytime, and doesn't help much because of that.
	//This has to be enabled to properly log modifications though.
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getExceptionPolicyControl(), $log_action, ('Exception Policy') .' - '. ('Type') .': '. Option::getByKey( $this->getType(), $this->getOptions('type') ), NULL, $this->getTable(), $this );
	}
}
?>
