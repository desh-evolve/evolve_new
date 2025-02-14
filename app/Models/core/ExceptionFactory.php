<?php

class ExceptionFactory extends Factory {
	protected $table = 'exception';
	protected $pk_sequence_name = 'exception_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $exception_policy_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				//Exception life-cycle
				//
				// - Exception occurs, such as missed out punch, in late.
				//   - If the exception is pre-mature, we wait 16-24hrs for it to become a full-blown exception
				// - If the exception requires authorization, it sits in a pending state waiting for supervsior intervention.
				// - Supervisor authorizes the exception, or makes a correction, leaves a note or something.
				//	 - Exception no longer appears on timesheet/exception list.
				$retval = array(
										5  => TTi18n::gettext('Pre-Mature'),
										30 => TTi18n::gettext('PENDING AUTHORIZATION'),
										40 => TTi18n::gettext('AUTHORIZATION OPEN'),
										50 => TTi18n::gettext('ACTIVE'),
										55 => TTi18n::gettext('AUTHORIZATION DECLINED'),
										60 => TTi18n::gettext('DISABLED'),
										70 => TTi18n::gettext('Corrected')
									);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										//'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1039-group' => TTi18n::gettext('Group'),
										'-1040-default_branch' => TTi18n::gettext('Default Branch'),
										'-1050-default_department' => TTi18n::gettext('Default Department'),
										'-1160-branch' => TTi18n::gettext('Branch'),
										'-1170-department' => TTi18n::gettext('Department'),

										'-1040-date_stamp' => TTi18n::gettext('Date'),
										'-1050-severity' => TTi18n::gettext('Severity'),
										'-1060-exception_policy_type' => TTi18n::gettext('Exception'),
										'-1070-exception_policy_type_id' => TTi18n::gettext('Code'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('date_stamp','severity', 'exception_policy_type', 'exception_policy_type_id'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'date_stamp',
								'severity',
								'exception_policy_type',
								'exception_policy_type_id',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);

		}

		return $retval;
	}

	function _getVariableToFunctionMap() {
			$variable_function_map = array(
											'id' => 'ID',
											'user_date_id' => 'UserDateID',
											'date_stamp' => FALSE,
											'exception_policy_id' => 'ExceptionPolicyID',
											'punch_control_id' => 'PunchControlID',
											'punch_id' => 'PunchID',
											'type_id' => 'Type',
											'type' => FALSE,
											'severity_id' => FALSE,
											'severity' => FALSE,
											'exception_color' => 'Color',
											'exception_background_color' => 'BackgroundColor',
											'exception_policy_type_id' => FALSE,
											'exception_policy_type' => FALSE,
											//'enable_demerit' => 'EnableDemerits',

											'user_id' => FALSE,
											'first_name' => FALSE,
											'last_name' => FALSE,
											'user_status_id' => FALSE,
											'user_status' => FALSE,
											'group_id' => FALSE,
											'group' => FALSE,
											'title_id' => FALSE,
											'title' => FALSE,
											'default_branch_id' => FALSE,
											'default_branch' => FALSE,
											'default_department_id' => FALSE,
											'default_department' => FALSE,

											'branch_id' => FALSE,
											'branch' => FALSE,
											'department_id' => FALSE,
											'department' => FALSE,

											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	function getUserDateObject() {
		if ( is_object($this->user_date_obj) ) {
			return $this->user_date_obj;
		} else {
			$udlf = TTnew( 'UserDateListFactory' );
			$this->user_date_obj = $udlf->getById( $this->getUserDateID() )->getCurrent();

			return $this->user_date_obj;
		}
	}

	function getExceptionPolicyObject() {
		if ( is_object($this->exception_policy_obj) ) {
			return $this->exception_policy_obj;
		} else {
			$eplf = TTnew( 'ExceptionPolicyListFactory' );
			$this->exception_policy_obj = $eplf->getById( $this->getExceptionPolicyID() )->getCurrent();

			return $this->exception_policy_obj;
		}
	}

	function getUserDateID() {
		if ( isset($this->data['user_date_id']) ) {
			return $this->data['user_date_id'];
		}

		return FALSE;
	}
	function setUserDateID($id = NULL) {
		$id = trim($id);

		$udlf = TTnew( 'UserDateListFactory' );

		if (  $this->Validator->isResultSetWithRows(	'user_date',
														$udlf->getByID($id),
														TTi18n::gettext('Invalid User Date ID')
														) ) {
			$this->data['user_date_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getExceptionPolicyID() {
		if ( isset($this->data['exception_policy_id']) ) {
			return $this->data['exception_policy_id'];
		}

		return FALSE;
	}
	function setExceptionPolicyID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$eplf = TTnew( 'ExceptionPolicyListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'exception_policy',
														$eplf->getByID($id),
														TTi18n::gettext('Invalid Exception Policy ID')
														) ) {
			$this->data['exception_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPunchControlID() {
		if ( isset($this->data['punch_control_id']) ) {
			return $this->data['punch_control_id'];
		}

		return FALSE;
	}
	function setPunchControlID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$pclf = TTnew( 'PunchControlListFactory' );

		if (
				$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'punch_control',
														$pclf->getByID($id),
														TTi18n::gettext('Invalid Punch Control ID')
														) ) {
			$this->data['punch_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPunchID() {
		if ( isset($this->data['punch_id']) ) {
			return $this->data['punch_id'];
		}

		return FALSE;
	}
	function setPunchID($id) {
		$id = trim($id);

		if ( $id == '' OR empty($id) ) {
			$id = NULL;
		}

		$plf = TTnew( 'PunchListFactory' );

		if (	$id == NULL
				OR
				$this->Validator->isResultSetWithRows(	'punch',
														$plf->getByID($id),
														TTi18n::gettext('Invalid Punch ID')
														) ) {
			$this->data['punch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getEnableDemerits() {
		if ( isset($this->data['enable_demerit']) ) {
			return $this->data['enable_demerit'];
		}

		return FALSE;
	}
	function setEnableDemerits($bool) {
		$this->data['enable_demerit'] = $bool;

		return TRUE;
	}

	function getBackgroundColor() {
		//Use HTML color codes so they work in Flex too.
		if (  $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn('severity_id') != '' ) {
				switch ( $this->getColumn('severity_id') ) {
					case 10:
						$retval = FALSE;
						break;
					case 20:
						$retval = '#FFFF00'; #'yellow';
						break;
					case 25:
						$retval = '#FF9900'; #'orange';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}

	function getColor() {
		$retval = FALSE;

		//Use HTML color codes so they work in Flex too.
		if (  $this->getType() == 5 ) {
			$retval = '#666666'; #'gray';
		} else {
			if ( $this->getColumn('severity_id') != '' ) {
				switch ( $this->getColumn('severity_id') ) {
					case 10:
						$retval = '#000000'; #'black';
						break;
					case 20:
						$retval = '#0000FF'; #'blue';
						break;
					case 25:
						$retval = '#FF9900'; #'blue';
						break;
					case 30:
						$retval = '#FF0000'; #'red';
						break;
				}
			}
		}

		return $retval;
	}

	function getEmailExceptionAddresses( $u_obj = NULL, $ep_obj = NULL ) {
		Debug::text(' Attempting to Email Notification...', __FILE__, __LINE__, __METHOD__,10);

		//Make sure type is not pre-mature.
		if ( $this->getType() > 5 ) {
			if ( !is_object($ep_obj) ) {
				$ep_obj = $this->getExceptionPolicyObject();
			}

			//Make sure exception policy email notifications are enabled.
			if ( $ep_obj->getEmailNotification() > 0 ) {
				if ( !is_object($u_obj) ) {
					$u_obj = $this->getUserDateObject()->getUserObject();
				}

				$up_obj = $this->getUserDateObject()->getUserObject()->getUserPreferenceObject();

				//Make sure user email notifications are enabled.
				if ( ( $ep_obj->getEmailNotification() == 10 OR $ep_obj->getEmailNotification() == 100 ) AND $up_obj->getEnableEmailNotificationException() == TRUE ) {
					Debug::Text(' Emailing exception to user!', __FILE__, __LINE__, __METHOD__,10);
					if ( $u_obj->getWorkEmail() != '' ) {
						$retarr[] = $u_obj->getWorkEmail();
					}
					if ( $up_obj->getEnableEmailNotificationHome() == TRUE AND $u_obj->getHomeEmail() != '' ) {
						$retarr[] = $u_obj->getHomeEmail();
					}
				} else {
					Debug::Text(' Skipping email to user.', __FILE__, __LINE__, __METHOD__,10);
				}

				//Make sure supervisor email notifcations are enabled
				if ( $ep_obj->getEmailNotification() == 20 OR $ep_obj->getEmailNotification() == 100 ) {
					//Find supervisor(s)
					$hlf = TTnew( 'HierarchyListFactory' );
					$parent_user_id = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $u_obj->getCompany(), $u_obj->getId(), 80 );
					if ( $parent_user_id != FALSE ) {
						//Parent could be multiple supervisors, make sure we email them all.
						$ulf = TTnew( 'UserListFactory' );
						$ulf->getByIdAndCompanyId( $parent_user_id, $u_obj->getCompany() );
						if ( $ulf->getRecordCount() > 0 ) {
							foreach( $ulf as $parent_user_obj ) {
								//$parent_user_obj = $ulf->getCurrent();

								if ( is_object( $parent_user_obj->getUserPreferenceObject() ) AND $parent_user_obj->getUserPreferenceObject()->getEnableEmailNotificationException() == TRUE ) {
									Debug::Text(' Emailing exception to supervisor!', __FILE__, __LINE__, __METHOD__,10);
									if ( $parent_user_obj->getWorkEmail() != '' ) {
										$retarr[] = $parent_user_obj->getWorkEmail();
									}

									if ( $up_obj->getEnableEmailNotificationHome() == TRUE AND $parent_user_obj->getHomeEmail() != '' ) {
										$retarr[] = $parent_user_obj->getHomeEmail();
									}
								} else {
									Debug::Text(' Skipping email to supervisor.', __FILE__, __LINE__, __METHOD__,10);
								}
							}
						}
					} else {
						Debug::Text(' No Hierarchy Parent Found, skipping email to supervisor.', __FILE__, __LINE__, __METHOD__,10);
					}
				}

				if ( isset($retarr) AND is_array($retarr) ) {
					return $retarr;
				} else {
					Debug::text(' No user objects to email too...', __FILE__, __LINE__, __METHOD__,10);
				}
			} else {
				Debug::text(' Exception Policy Email Exceptions are disabled, skipping email...', __FILE__, __LINE__, __METHOD__,10);
			}
		} else {
			Debug::text(' Pre-Mature exception, or not in production mode, skipping email...', __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
	}


	/*

		What do we pass the emailException function?
			To address, CC address (home email) and Bcc (supervisor) address?

	*/
	function emailException( $u_obj, $user_date_obj, $ep_obj = NULL ) {

		if ( !is_object( $u_obj ) ) {
			return FALSE;
		}

		if ( !is_object( $user_date_obj ) ) {
			return FALSE;
		}

		if ( !is_object($ep_obj) ) {
			$ep_obj = $this->getExceptionPolicyObject();
		}

		//Only email on active exceptions.
		if ( $this->getType() != 50 ) {
			return FALSE;
		}

		$email_to_arr = $this->getEmailExceptionAddresses( $u_obj, $ep_obj );
		if ( $email_to_arr == FALSE ) {
			return FALSE;
		}

		$from = 'DoNotReply@'.Misc::getHostName( FALSE );

		$to = array_shift( $email_to_arr );
		Debug::Text('To: '. $to, __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($email_to_arr) AND count($email_to_arr) > 0 ) {
			$bcc = implode(',', $email_to_arr);
		} else {
			$bcc = NULL;
		}

		$exception_email_subject = ' #exception_name# (#exception_code#) '. TTi18n::gettext('exception for') .' #employee_first_name# #employee_last_name# '. TTi18n::gettext('on') .' #date#';
		$exception_email_body  = TTi18n::gettext('Employee:').' #employee_first_name# #employee_last_name#'."\n";
		$exception_email_body .= TTi18n::gettext('Date:').' #date#'."\n";
		$exception_email_body .= TTi18n::gettext('Exception:').' #exception_name# (#exception_code#)'."\n";
		$exception_email_body .= TTi18n::gettext('Severity:').' #exception_severity#'."\n";
		$exception_email_body .= TTi18n::gettext('Link:').' <a href="http://'. Misc::getHostName().Environment::getBaseURL().'">'.APPLICATION_NAME.' '. TTi18n::gettext('Login') .'</a>';

		//Define subject/body variables here.
		$search_arr = array(
							'#employee_first_name#',
							'#employee_last_name#',
							'#exception_code#',
							'#exception_name#',
							'#exception_severity#',
							'#date#',
							'#link#',
							);

		$replace_arr = array(
							$u_obj->getFirstName(),
							$u_obj->getLastName(),
							$ep_obj->getType(),
							Option::getByKey( $ep_obj->getType(), $ep_obj->getOptions('type') ),
							Option::getByKey( $ep_obj->getSeverity(), $ep_obj->getOptions('severity') ),
							TTDate::getDate('DATE', $user_date_obj->getDateStamp() ),
							NULL,
							);

		$subject = str_replace( $search_arr, $replace_arr, $exception_email_subject );
		Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__,10);

		$headers = array(
							'From'    => $from,
							'Subject' => $subject,
							'Bcc'	  => $bcc,
							'Reply-To' => $to,
							'Return-Path' => $to,
							'Errors-To' => $to,
						 );

		$body = '<pre>'.str_replace( $search_arr, $replace_arr, $exception_email_body ).'</pre>';
		Debug::Text('Body: '. $body, __FILE__, __LINE__, __METHOD__,10);

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody($body);

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == TRUE ) {
			TTLog::addEntry( $this->getId(), 500,  TTi18n::getText('Email Exception to').': '. $to .' Bcc: '. $headers['Bcc'], NULL, $this->getTable() );
			return TRUE;
		}

		return TRUE;
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

		$epf = TTnew( 'ExceptionPolicyFactory' );
		$exception_policy_type_options = $epf->getOptions('type');
		$exception_policy_severity_options = $epf->getOptions('severity');

		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {
					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'branch_id':
						case 'branch':
						case 'department_id':
						case 'department':
						case 'severity_id':
						case 'exception_policy_type_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'severity':
							$data[$variable] = Option::getByKey( $this->getColumn( 'severity_id' ), $exception_policy_severity_options );
							break;
						case 'exception_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'exception_policy_type_id' ), $exception_policy_type_options );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'date_stamp' ) ) );
							break;
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

}
?>
