<?php

namespace App\Models\Core;
use Illuminate\Support\Facades\Log;

class TTMail {
	private $mime_obj = NULL;
	private $mail_obj = NULL;

	private $data = NULL;
	public $default_mime_config = array(
										'html_charset' => 'UTF-8',
										'text_charset' => 'UTF-8',
										'head_charset' => 'UTF-8',
										);

	function __construct() {
		//For some reason the EOL defaults to \r\n, which seems to screw with Amavis
		//This also prevents wordwrapping at 70 chars.
		if ( !defined('MAIL_MIMEPART_CRLF') ) {
			define('MAIL_MIMEPART_CRLF', "\n");
		}

		return TRUE;
	}

	function getMimeObject() {
		if ( $this->mime_obj == NULL ) {
			require_once('Mail/mime.php');
			$this->mime_obj = @new Mail_Mime();
		}

		return $this->mime_obj;
	}
	function getMailObject() {
		if ( $this->mail_obj == NULL ) {
			require_once('Mail.php');

			//Determine if use Mail/SMTP, or SOAP.
			$delivery_method = $this->getDeliveryMethod();

			if ( $delivery_method == 'mail' ) {
				$this->mail_obj = &Mail::factory('mail');

			} elseif ( $delivery_method == 'smtp' ) {
				$smtp_config = $this->getSMTPConfig();
				$this->mail_obj = &Mail::factory('smtp', array (
											'host' => $smtp_config['host'],
											'port' => $smtp_config['port'],
											'auth' => true,
											'username' => $smtp_config['username'],
											'password' => $smtp_config['password']
											)
										);

				Debug::Arr($smtp_config, 'SMTP Config: ', __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $this->mail_obj;
	}

	function getDeliveryMethod() {
		global $config_vars;

		$possible_values = array( 'mail','soap','smtp' );
		if ( isset( $config_vars['mail']['delivery_method'] ) AND in_array( strtolower( trim($config_vars['mail']['delivery_method']) ), $possible_values ) ) {
			return $config_vars['mail']['delivery_method'];
		}

		if ( DEPLOYMENT_ON_DEMAND == TRUE ) {
			return 'mail';
		}

		return 'soap'; //Default to SOAP as it has a better chance of working than mail/SMTP
	}

	function getSMTPConfig() {
		global $config_vars;

		$retarr = array(
						'host' => NULL,
						'post' => 25,
						'username' => NULL,
						'password' => NULL,
						);

		if ( isset( $config_vars['mail']['smtp_host'] ) ) {
			$retarr['host'] = $config_vars['mail']['smtp_host'];
		}

		if ( isset( $config_vars['mail']['smtp_port'] ) ) {
			$retarr['port'] = $config_vars['mail']['smtp_port'];
		}

		if ( isset( $config_vars['mail']['smtp_username'] ) ) {
			$retarr['username'] = $config_vars['mail']['smtp_username'];
		}
		if ( isset( $config_vars['mail']['smtp_password'] ) ) {
			$retarr['password'] = $config_vars['mail']['smtp_password'];
		}

		return $retarr;
	}

	function getMIMEHeaders() {
		$mime_headers = @$this->getMIMEObject()->headers( $this->getHeaders() );
		//Debug::Arr($this->data['headers'], 'MIME Headers: ', __FILE__, __LINE__, __METHOD__,10);
		return $mime_headers;
	}
	function getHeaders() {
		if ( isset( $this->data['headers'] ) ) {
			return $this->data['headers'];
		}

		return FALSE;
	}
	function setHeaders( $headers, $include_default = FALSE ) {
		$this->data['headers'] = $headers;

		if ( $include_default == TRUE ) {
			//May have to go to base64 encoding all data for proper UTF-8 support.
			$this->data['headers']['Content-type'] = 'text/html; charset="UTF-8"';
		}

		//Debug::Arr($this->data['headers'], 'Headers: ', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	function getTo() {
		if ( isset( $this->data['to'] ) ) {
			return $this->data['to'];
		}

		return FALSE;
	}
	function setTo( $email ) {
		$this->data['to'] = $email;

		return TRUE;
	}

	function getBody() {
		if ( isset( $this->data['body'] ) ) {
			return $this->data['body'];
		}

		return FALSE;
	}
	function setBody( $body ) {
		$this->data['body'] = $body;

		return TRUE;
	}

	function Send() {
		Debug::Text('Attempting to send email To: '. $this->getTo(), __FILE__, __LINE__, __METHOD__,10);

		if ( $this->getTo() == FALSE ) {
			Debug::Text('To Address invalid...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $this->getBody() == FALSE ) {
			Debug::Text('Body invalid...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		Debug::Text('Sending Email To: '. $this->getTo() .' Body Size: '. strlen( $this->getBody() ) .' Method: '. $this->getDeliveryMethod(), __FILE__, __LINE__, __METHOD__,10);

		if ( PRODUCTION == FALSE ) {
			Debug::Text('Not in production mode, not sending emails...', __FILE__, __LINE__, __METHOD__,10);
			//$to = 'root@localhost';
			return FALSE;
		}

		if ( DEMO_MODE == TRUE ) {
			Debug::Text('In DEMO mode, not sending emails...', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		switch ( $this->getDeliveryMethod() ) {
			case 'smtp':
			case 'mail':
				$send_retval = $this->getMailObject()->send( $this->getTo(), $this->getMIMEHeaders(), $this->getBody() );
				break;
			case 'soap':
				$ttsc = new TimeTrexSoapClient();
				$send_retval = $ttsc->sendEmail( $this->getTo(), $this->getMIMEHeaders(), $this->getBody() );
				break;
		}

		if ( $send_retval == TRUE ) {
			return TRUE;
		}

		Debug::Arr($send_retval, 'Send Email Failed!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}
}
?>
