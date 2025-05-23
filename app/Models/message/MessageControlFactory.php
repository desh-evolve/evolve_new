<?php

namespace App\Models\Message;

use App\Models\Core\AuthorizationListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\TTi18n;
use App\Models\Core\TTLog;
use App\Models\Core\TTMail;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyFactory;
use App\Models\Request\RequestListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceListFactory;

class MessageControlFactory extends Factory {
	protected $table = 'message_control';
	protected $pk_sequence_name = 'message_control_id_seq'; //PK Sequence name

	protected $obj_handler = NULL;
	protected $tmp_data = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => ('UNREAD'),
										20 => ('READ')
									);
				break;
			case 'type':
				$retval = array(
										5 => 'email',
										//10 => 'default_schedule',
										//20 => 'schedule_amendment',
										//30 => 'shift_amendment',
										40 => 'authorization',
										50 => 'request',
										60 => 'job',
										70 => 'job_item',
										80 => 'client',
										90 => 'timesheet',
										100 => 'user' //For notes assigned to users?
									);
				break;
			case 'object_type':
			case 'object_name':
				$retval = array(
										5 => ('Email'), //Email from user to another
										10 => ('Recurring Schedule'),
										20 => ('Schedule Amendment'),
										30 => ('Shift Amendment'),
										40 => ('Authorization'),
										50 => ('Request'),
										60 => ('Job'),
										70 => ('Task'),
										80 => ('Client'),
										90 => ('TimeSheet'),
										100 => ('Employee') //For notes assigned to users?
									);
				break;
			case 'folder':
				$retval = array(
										10 => ('Inbox'),
										20 => ('Sent')
									);
				break;
			case 'priority':
				$retval = array(
										10 => ('LOW'),
										50 => ('NORMAL'),
										100 => ('HIGH'),
										110 => ('URGENT')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-from_first_name' => ('From: First Name'),
										'-1020-from_middle_name' => ('From: Middle Name'),
										'-1030-from_last_name' => ('From: Last Name'),

										'-1110-to_first_name' => ('To: First Name'),
										'-1120-to_middle_name' => ('To: Middle Name'),
										'-1130-to_last_name' => ('To: Last Name'),

										'-1200-subject' => ('Subject'),
										'-1210-object_type' => ('Type'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										//'-2020-updated_by' => ('Updated By'),
										//'-2030-updated_date' => ('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'from_first_name',
								'from_last_name',
								'to_first_name',
								'to_last_name',
								'subject',
								'object_type',
								'created_date',
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

										'from_user_id' => 'FromUserID',
										'from_first_name' => FALSE,
										'from_middle_name' => FALSE,
										'from_last_name' => FALSE,

										'to_user_id' => 'ToUserID',
										'to_first_name' => FALSE,
										'to_middle_name' => FALSE,
										'to_last_name' => FALSE,

										'status_id' => FALSE,
										'object_type_id' => 'ObjectType',
										'object_type' => FALSE,
										'object_id' => 'Object',
										'parent_id' => 'Parent',
										'priority_id' => 'Priority',
										'subject' => 'Subject',
										'body' => 'Body',
										'require_ack' => 'RequireAck',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}


	function getFromUserId() {
		if ( isset($this->tmp_data['from_user_id']) ) {
			return $this->tmp_data['from_user_id'];
		}

		return FALSE;
	}
	function setFromUserId( $id ) {
		if ( $id != '' ) {
			$this->tmp_data['from_user_id'] = $id;
			return TRUE;
		}
		return FALSE;
	}

	function getToUserId() {
		if ( isset($this->tmp_data['to_user_id']) ) {
			return $this->tmp_data['to_user_id'];
		}

		return FALSE;
	}
	function setToUserId( $ids ) {
		if ( !is_array($ids) ) {
			$ids = array($ids);
		}

		$ids = array_unique($ids);
		if ( count($ids) > 0 ) {
			foreach($ids as $id ) {
				if ( $id > 0 ) {
					$this->tmp_data['to_user_id'][] = $id;
				}
			}

			return TRUE;
		}
		return FALSE;
	}

	//Expose message_sender_id for migration purposes.
	function getMessageSenderId() {
		if ( isset($this->tmp_data['message_sender_id']) ) {
			return $this->tmp_data['message_sender_id'];
		}

		return FALSE;
	}
	function setMessageSenderId( $id ) {
		if ( $id != '' ) {
			$this->tmp_data['message_sender_id'] = $id;
			return TRUE;
		}
		return FALSE;

	}

	function isAck() {
		if ( $this->getRequireAck() == TRUE AND $this->getColumn('ack_date') == '' ) {
			return FALSE;
		}

		return TRUE;
	}

	//Parent ID is the parent message_sender_id.
	function getParent() {
		if ( isset($this->tmp_data['parent_id']) ) {
			return $this->tmp_data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {
		$id = trim($id);

		if ( empty($id) ) {
			$id = 0;
		}

		if ( $id == 0
				OR $this->Validator->isNumeric(				'parent',
															$id,
															('Parent is invalid')
															) ) {
			$this->tmp_data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//These functions are out of the ordinary, as the getStatus gets the status of a message based on a SQL join to the recipient table.
	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}

	function getObjectHandler() {
		if ( is_object($this->obj_handler) ) {
			return $this->obj_handler;
		} else {
			switch ( $this->getObjectType() ) {
				case 5:
				case 100:
					$this->obj_handler = new UserListFactory();
					break;
				case 40:
					$this->obj_handler = new AuthorizationListFactory();
					break;
				case 50:
					$this->obj_handler = new RequestListFactory();
					break;
				case 90:
					$this->obj_handler = new PayPeriodTimeSheetVerifyFactory();
					break;
			}

			return $this->obj_handler;
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return $this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											('Object Type is invalid'),
											$this->getOptions('type')) ) {

			$this->data['object_type_id'] = $type;

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

		if ( $this->Validator->isResultSetWithRows(	'object',
													$this->getObjectHandler()->getByID($id),
													('Object ID is invalid')
													) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPriority() {
		if ( isset($this->data['priority_id']) ) {
			return $this->data['priority_id'];
		}

		return FALSE;
	}
	function setPriority($priority = NULL) {
		$priority = trim($priority);

		if ( empty($priority) ) {
			$priority = 50;
		}

		$key = Option::getByValue($priority, $this->getOptions('priority') );
		if ($key !== FALSE) {
			$priority = $key;
		}

		if ( $this->Validator->inArrayKey(	'priority',
											$priority,
											('Invalid Priority'),
											$this->getOptions('priority')) ) {

			$this->data['priority_id'] = $priority;

			return FALSE;
		}

		return FALSE;
	}

	function getSubject() {
		if ( isset($this->data['subject']) ) {
			return $this->data['subject'];
		}

		return FALSE;
	}
	function setSubject($text) {
		$text = trim($text);

		if 	(	strlen($text) == 0
				OR
				$this->Validator->isLength(		'subject',
												$text,
												('Invalid Subject length'),
												2,
												100) ) {

			$this->data['subject'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getBody() {
		if ( isset($this->data['body']) ) {
			return $this->data['body'];
		}

		return FALSE;
	}

	function setBody($text) {
		$text = trim($text);

		if 	(	$this->Validator->isLength(		'body',
												$text,
												('Invalid Body length'),
												5,
												1024) ) {

			$this->data['body'] = $text;

			return TRUE;
		}

		return FALSE;
	}

	function getRequireAck() {
		return $this->fromBool( $this->data['require_ack'] );
	}
	function setRequireAck($bool) {
		$this->data['require_ack'] = $this->toBool($bool);

		return true;
	}

	function getEnableEmailMessage() {
		if ( isset($this->email_message) ) {
			return $this->email_message;
		}

		return TRUE;
	}
	function setEnableEmailMessage($bool) {
		$this->email_message = $bool;

		return TRUE;
	}

	function Validate() {
		//Only validate from/to user if there is a subject and body set, otherwise validation will fail on a new object with no data all the time.
		if ( $this->getSubject() != '' AND $this->getBody() != '' ) {
			if ( $this->Validator->hasError( 'from' ) == FALSE AND $this->getFromUserId() == '' ) {
				$this->Validator->isTrue(	'from',
											FALSE,
											('Message sender is invalid') );

			}

			//Messages attached to objects do not require a recipient.
			if ( $this->Validator->hasError( 'to' ) == FALSE AND $this->getToUserId() == FALSE AND $this->getObjectType() == 5 ) {
				$this->Validator->isTrue(	'to',
											FALSE,
											('Message recipient is invalid') );
			}
		}

		/* This causes issues with the HTML interface.
		if ( $this->getObjectType() == '' ) {
				$this->Validator->isTrue(	'object_type_id',
											FALSE,
											('Object type is invalid') );
		}
		*/

		//If deleted is TRUE, we need to make sure all sender/recipient records are also deleted.
		return TRUE;
	}

	static function markRecipientMessageAsRead( $company_id, $user_id, $ids ) {
		if ( $company_id == '' OR $user_id == '' OR $ids == '' OR count($ids) == 0 ) {
			return FALSE;
		}

		Debug::Arr($ids, 'Message Recipeint Ids: ', __FILE__, __LINE__, __METHOD__,10);

		$mrlf = new MessageRecipientListFactory();
		$mrlf->getByCompanyIdAndUserIdAndMessageSenderIdAndStatus( $company_id, $user_id, $ids, 10 );

		if ( $mrlf->getRecordCount() > 0 ) {
			foreach( $mrlf->rs as $mr_obj ) {
				$mrlf->data = (array)$mr_obj;
				$mr_obj = $mrlf;
				$mr_obj->setStatus( 20 ); //Read
				$mr_obj->Save();
			}
		}

		return TRUE;
	}


	function getEmailMessageAddresses() {
		$user_ids = $this->getToUserId();
		if ( isset($user_ids) AND is_array($user_ids) ) {
			//Get user preferences and determine if they accept email notifications.
			Debug::Arr($user_ids, 'Recipient User Ids: ', __FILE__, __LINE__, __METHOD__,10);

			$uplf = new UserPreferenceListFactory();
			$uplf->getByUserId( $user_ids );
			if ( $uplf->getRecordCount() > 0 ) {
				foreach( $uplf->rs as $up_obj ) {
					$uplf->data = (array)$up_obj;
					$up_obj = $uplf;
					if ( $up_obj->getEnableEmailNotificationMessage() == TRUE AND $up_obj->getUserObject()->getStatus() == 10 ) {
						if ( $up_obj->getUserObject()->getWorkEmail() != '' ) {
							$retarr[] = $up_obj->getUserObject()->getWorkEmail();
						}

						if ( $up_obj->getEnableEmailNotificationHome() AND $up_obj->getUserObject()->getHomeEmail() != '' ) {
							$retarr[] = $up_obj->getUserObject()->getHomeEmail();
						}
					}
				}

				if ( isset($retarr) ) {
					Debug::Arr($retarr, 'Recipient Email Addresses: ', __FILE__, __LINE__, __METHOD__,10);
					return array_unique($retarr);

				}
			}
		}

		return FALSE;
	}

	function emailMessage() {
		Debug::Text('emailMessage: ', __FILE__, __LINE__, __METHOD__,10);

		$email_to_arr = $this->getEmailMessageAddresses();
		if ( $email_to_arr == FALSE ) {
			return FALSE;
		}

		$from = $reply_to = 'DoNotReply@'. Misc::getHostName( FALSE );

		global $current_user, $config_vars;
		if ( is_object($current_user) AND $current_user->getWorkEmail() != '' ) {
			$reply_to = $current_user->getWorkEmail();
		}
		Debug::Text('From: '. $from .' Reply-To: '. $reply_to, __FILE__, __LINE__, __METHOD__,10);

		$to = array_shift( $email_to_arr );
		Debug::Text('To: '. $to, __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($email_to_arr) AND count($email_to_arr) > 0 ) {
			$bcc = implode(',', $email_to_arr);
		} else {
			$bcc = NULL;
		}
		Debug::Text('Bcc: '. $bcc, __FILE__, __LINE__, __METHOD__,10);

		$email_subject = ('New message waiting in').' '. APPLICATION_NAME;
		$email_body  = ('*DO NOT REPLY TO THIS EMAIL - PLEASE USE THE LINK BELOW INSTEAD*')."\n\n";
		$email_body  .= ('You have a new message waiting for you in').' '. APPLICATION_NAME."\n";
		if ( $this->getSubject() != '' ) {
			$email_body .= ('Subject:').' '. $this->getSubject()."\n";
		}

		$protocol = 'http';
		if ( isset($config_vars['other']['force_ssl']) AND $config_vars['other']['force_ssl'] == 1 ) {
			$protocol .= 's';
		}

		$email_body .= ('Link').': <a href="'. $protocol .'://'. Misc::getHostName().Environment::getBaseURL().'">'. APPLICATION_NAME .' '. ('Login') .'</a>';

		//Define subject/body variables here.
		$search_arr = array(
							'#employee_first_name#',
							'#employee_last_name#',
							);

		$replace_arr = array(
							NULL,
							NULL,
							);

		$subject = str_replace( $search_arr, $replace_arr, $email_subject );
		Debug::Text('Subject: '. $subject, __FILE__, __LINE__, __METHOD__,10);

		$headers = array(
							'From'    => $from,
							'Subject' => $subject,
							'Bcc'	  => $bcc,
							'Reply-To' => $reply_to,
							'Return-Path' => $reply_to,
							'Errors-To' => $reply_to,
						 );

		$body = '<pre>'.str_replace( $search_arr, $replace_arr, $email_body ).'</pre>';
		Debug::Text('Body: '. $body, __FILE__, __LINE__, __METHOD__,10);

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody($body);

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == TRUE ) {
			TTLog::addEntry( $this->getId(), 500,  ('Email Message to').': '. $to .' Bcc: '. $headers['Bcc'], NULL, $this->getTable() );
			return TRUE;
		}

		return TRUE; //Always return true
	}

	function preSave() {
		//Check to make sure the 'From' user_id doesn't appear in the 'To' user list as well.
		$from_user_id_key = array_search( $this->getFromUserId(), (array)$this->getToUserId() );
		if ( $from_user_id_key !== FALSE ) {
			$to_user_ids = $this->getToUserId();
			unset($to_user_ids[$from_user_id_key]);
			$this->setToUserId( $to_user_ids );

			Debug::text('From user is assigned as a To user as well, removing...'. (int)$from_user_id_key, __FILE__, __LINE__, __METHOD__,9);
		}

		Debug::Arr($this->getFromUserId(), 'From: ', __FILE__, __LINE__, __METHOD__,9);
		Debug::Arr($this->getToUserId(), 'Sending To: ', __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}

	function postSave() {
		//Save Sender/Recipient records for this message.
		if ( $this->getDeleted() == FALSE ) {
			$to_user_ids = $this->getToUserId();
			if ( $to_user_ids != FALSE ) {
				foreach( $to_user_ids as $to_user_id ) {
					//We need one message_sender record for every recipient record, otherwise when a message is sent to
					//multiple recipients, and one of them replies, the parent_id will point to original sender record which
					//then maps to every single recipient, making it hard to show messages just between the specific users.
					//
					//On the other hand, having multiple sender records, one for each recipient makes it hard to show
					//just the necessary messages on the embedded message list, as it wants to show duplicates messages for
					//each recipient.
					$msf = new MessageSenderFactory();
					$msf->setUser( $this->getFromUserId() );
					Debug::Text('Parent ID: '. $this->getParent(), __FILE__, __LINE__, __METHOD__,10);

					$msf->setParent( $this->getParent() );
					$msf->setMessageControl( $this->getId() );
					$msf->setCreatedBy( $this->getCreatedBy() );
					$msf->setCreatedDate( $this->getCreatedDate() );
					$msf->setUpdatedBy( $this->getUpdatedBy() );
					$msf->setUpdatedDate( $this->getUpdatedDate() );
					if ( $msf->isValid() ) {
						$message_sender_id = $msf->Save();
						$this->setMessageSenderId( $message_sender_id ); //Used mainly for migration purposes, so we can obtain this from outside the class.
						Debug::Text('Message Sender ID: '. $message_sender_id, __FILE__, __LINE__, __METHOD__,10);

						if ( $message_sender_id != FALSE ) {
							$mrf = new MessageRecipientFactory();
							$mrf->setUser( $to_user_id );
							$mrf->setMessageSender( $message_sender_id );
							if ( isset($this->migration_status) ) {
								$mrf->setStatus( $this->migration_status );
							}
							$mrf->setCreatedBy( $this->getCreatedBy() );
							$mrf->setCreatedDate( $this->getCreatedDate() );
							$mrf->setUpdatedBy( $this->getUpdatedBy() );
							$mrf->setUpdatedDate( $this->getUpdatedDate() );
							if ( $mrf->isValid() ) {
								$mrf->Save();
							}
						}
					}
				}

				//Send email to all recipients.
				if ( $this->getEnableEmailMessage() == TRUE ) {
					$this->emailMessage();
				}
			} else {
				//If no recipients are specified (user replying to their own request before a superior does, or a user sending a request without a hierarchy)
				//Make sure we have at least one sender record.
				//Either that or make sure we always reply to ALL senders and recipients in the thread.
			}
		}

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
						case 'to_user_id':
						case 'to_first_name':
						case 'to_middle_name':
						case 'to_last_name':
						case 'from_user_id':
						case 'from_first_name':
						case 'from_middle_name':
						case 'from_last_name':
						case 'status_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'object_type':
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
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
