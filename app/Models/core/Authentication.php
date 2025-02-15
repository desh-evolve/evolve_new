<?php

use Illuminate\Support\Facades\Log;

class Authentication {
	protected $name = 'SessionID';
	protected $idle = 14400; //Max IDLE time
	protected $session_id = NULL;
	protected $ip_address = NULL;
	protected $created_date = NULL;
	protected $updated_date = NULL;

	protected $obj = NULL;

	function __construct() {
		global $db;

		$this->db = $db;

		$this->rl = new RateLimit();
		$this->rl->setID( 'authentication_'.$_SERVER['REMOTE_ADDR'] );
		$this->rl->setAllowedCalls( 20 );
		$this->rl->setTimeFrame( 900 ); //15 minutes

		return TRUE;
	}

	function getName() {
		return $this->name;
	}
	function setName($name) {
		if ( !empty($name) ) {
			$this->name = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getIPAddress() {
		return $this->ip_address;
	}
	function setIPAddress($ip_address = NULL) {
		if (empty( $ip_address ) ) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}

		if ( !empty($ip_address) ) {
			$this->ip_address = $ip_address;

			return TRUE;
		}

		return FALSE;
	}

	function getIdle() {
		//Debug::text('Idle Seconds Allowed: '. $this->idle, __FILE__, __LINE__, __METHOD__, 10);
		return $this->idle;
	}
	function setIdle($secs) {
		if ( is_int($secs) ) {
			$this->idle = $secs;

			return TRUE;
		}

		return FALSE;
	}

	function getCreatedDate() {
		return $this->created_date;
	}
	function setCreatedDate($epoch = NULL) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric($epoch) ) {
			$this->created_date = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getUpdatedDate() {
		return $this->updated_date;
	}
	function setUpdatedDate($epoch = NULL) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric($epoch) ) {
			$this->updated_date = $epoch;

			return TRUE;
		}

		return FALSE;
	}


	function changeObject($user_id) {
		$this->setObject( $user_id );

		$ph = array(
					'user_id' => $user_id,
					'session_id' => $this->getSessionID(),
					);

		$query = 'update authentication set user_id = ?
					where session_id = ?
					';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}
	function getObject() {
		if ( is_object($this->obj) ) {
			return $this->obj;
		}

		return FALSE;
	}
	function setObject($user_id) {
		if ( !empty($user_id) ) {

			$ulf = new UserListFactory();

			$ulf->getByID($user_id);

			foreach ($ulf as $user) {
				$this->obj = $user;

				return TRUE;
			}
		}

		return FALSE;
	}

	function getSessionID() {
		return $this->session_id;
	}
	function setSessionID($session_id) {
		$validator = new Validator;
		$session_id = $validator->stripNonAlphaNumeric( $session_id );

		if (!empty( $session_id ) ) {
			$this->session_id = $session_id;

			return TRUE;
		}

		return FALSE;
	}

	private function genSessionID() {
		return md5( uniqid( dechex( mt_srand() ) ) );
	}

	function checkCompanyStatus( $user_name ) {
		$ulf = new UserListFactory();
		$ulf->getByUserName( strtolower($user_name) );

		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( is_object($u_obj) ) {
				$clf = new CompanyListFactory();
				$clf->getById( $u_obj->getCompany() );
				if ( $clf->getRecordCount() == 1 ) {
					if ( $clf->getCurrent()->getStatus() == 10 ) {
						return TRUE;
					}
				}

			}
		}

		return FALSE;
	}

	function checkPassword($user_name, $password) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByUserNameAndStatus(strtolower(trim($user_name)), 10 ); //Active

		foreach ($ulf as $user) {
			if ( $user->checkPassword($password) ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	function checkPhonePassword($phone_id, $password) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByPhoneIdAndStatus($phone_id, 10 );

		foreach ($ulf as $user) {
			if ( $user->checkPhonePassword($password) ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	function checkIButton($id) {
		$uilf = new UserIdentificationListFactory();
		$uilf->getByTypeIdAndValue(10, $id);
		if ( $uilf->getRecordCount() > 0 ) {
			foreach( $uilf as $ui_obj ) {
				if ( is_object( $ui_obj->getUserObject() ) AND $ui_obj->getUserObject()->getStatus() == 10 ) {
					$this->setObject( $ui_obj->getUser() );
					return TRUE;
				}
			}
		}
/*
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByIButtonIdAndStatus($id, 10 );

		foreach ($ulf as $user) {
			if ( $user->checkIButton($id) ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}
*/
		return FALSE;
	}

	function checkBarcode($user_id, $employee_number) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByIdAndStatus($user_id, 10 );

		foreach ($ulf as $user) {
			if ( $user->checkEmployeeNumber($employee_number) ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	function checkFingerPrint($id) {
		$ulf = new UserListFactory();

		$ulf->getByIdAndStatus($id, 10 );

		foreach ($ulf as $user) {
			//if ( $user->checkEmployeeNumber($id) ) {
			if ( $user->getId() == $id ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	function checkClientPC($user_name) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByUserNameAndStatus(strtolower($user_name), 10 );

		foreach ($ulf as $user) {
			if ( $user->getUserName() == $user_name ) {
				$this->setObject( $user->getID() );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	private function isSSL() {
		if ( isset($_SERVER['HTTPS']) AND ( $_SERVER['HTTPS'] == 'on' OR $_SERVER['HTTPS'] == 1 ) ) {
			return TRUE;
		}

		return FALSE;
	}

	private function setCookie() {
		if ( $this->getSessionID() ) {
			setcookie($this->getName(), $this->getSessionID(), time()+9999999, Environment::getBaseURL(), NULL, $this->isSSL() );

			return TRUE;
		}

		return FALSE;
	}

	private function destroyCookie() {
		setcookie($this->getName(), NULL, time()+9999999, Environment::getBaseURL(), NULL, $this->isSSL() );

		return TRUE;
	}

	private function UpdateLastLoginDate() {
		$ph = array(
					'last_login_date' => TTDate::getTime(),
					'user_id' => (int)$this->getObject()->getID(),
					);

		$query = 'update users set last_login_date = ?
					where id = ?';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Update() {
		$ph = array(
					'updated_date' => TTDate::getTime(),
					'session_id' => $this->getSessionID(),
					);

		$query = 'update authentication set updated_date = ?
					where session_id = ?
					';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Delete() {
		$ph = array(
					'session_id' => $this->getSessionID(),
					);

		//Can't use IdleTime here, as some users have different idle times.
		//Assume none are longer then one day though.
		$query = 'delete from authentication
						where session_id = ?
							OR (updated_date - created_date) > '. (86400*2) .'
							OR ('. TTDate::getTime() .' - updated_date) > 86400';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Write() {
		$ph = array(
					'session_id' => $this->getSessionID(),
					'user_id' => $this->getObject()->getID(),
					'ip_address' => $this->getIPAddress(),
					'created_date' => $this->getCreatedDate(),
					'updated_date' => $this->getUpdatedDate()
					);

		$query = 'insert into authentication (session_id,user_id,ip_address,created_date,updated_date)
						VALUES(
								?,
								?,
								?,
								?,
								?
							)';
		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Read() {
		$ph = array(
					'session_id' => $this->getSessionID(),
					'ip_address' => $this->getIPAddress(),
					'updated_date' => ( TTDate::getTime() - $this->getIdle() ),
					);


		$query = 'select session_id,user_id,ip_address,created_date,updated_date from authentication
					WHERE session_id = ?
						AND ip_address = ?
						AND updated_date >= ?
						';

		//Debug::text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$result = $this->db->GetRow($query, $ph);

		if ( count($result) > 0) {
			$this->setSessionID($result['session_id']);
			$this->setIPAddress($result['ip_address']);
			$this->setCreatedDate($result['created_date']);
			$this->setUpdatedDate($result['updated_date']);

			if ( $this->setObject($result['user_id']) ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function Login($user_name, $password, $type = 'USER_NAME') {
		//DO NOT lowercase username, because iButton values are case sensitive.
		$user_name = html_entity_decode( trim($user_name) );
		$password = html_entity_decode( $password );

		//Checks user_name/password
        if ( $user_name == '' OR $password == '' ) {
			return FALSE;
		}

		Debug::text('Login Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		//Prevent brute force attacks by IP address.

		//Allowed up to 20 attempts in a 30 min period.
		if ( $this->rl->check() == FALSE ) {
			Debug::Text('Excessive failed password attempts... Preventing login from: '. $_SERVER['REMOTE_ADDR'] .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__,10);
			sleep(5); //Excessive password attempts, sleep longer.
			return FALSE;
		}

		if ( strtolower($type) == 'user_name' ) {
			if ( $this->checkCompanyStatus( $user_name ) == TRUE ) {
				//Lowercase regular user_names here only.
				$password_result = $this->checkPassword( strtolower($user_name), $password);
			} else {
				$password_result = FALSE; //No company by that user name.
			}
		} elseif (strtolower($type) == 'phone_id') {
			$password_result = $this->checkPhonePassword($user_name, $password);
		} elseif (strtolower($type) == 'ibutton') {
			$password_result = $this->checkIButton($user_name);
		} elseif (strtolower($type) == 'barcode') {
			$password_result = $this->checkBarcode($user_name, $password);
		} elseif (strtolower($type) == 'finger_print') {
			$password_result = $this->checkFingerPrint( $user_name );
		} elseif (strtolower($type) == 'client_pc') {
			//This is for client application persistent connections, use:
			//Login Type: client_pc
			//Station Type: PC

			//$password_result = $this->checkClientPC( $user_name );
			$password_result = $this->checkBarcode($user_name, $password);
		} else {
			return FALSE;
		}

		if ( $password_result === TRUE ) {
			Debug::text('Login Succesful!', __FILE__, __LINE__, __METHOD__, 10);

			$this->setSessionID( $this->genSessionID() );
			$this->setIPAddress();
			$this->setCreatedDate();
			$this->setUpdatedDate();

			//Sets session cookie.
			$this->setCookie();

			//Write data to db.
			$this->Write();

			//Only update last_login_date when using user_name to login to the web interface.
			if ( strtolower($type) == 'user_name' ) {
				$this->UpdateLastLoginDate();
			}

			TTDebug::addEntry( $this->getObject()->getID(), 100,  TTi18n::getText('SourceIP').': '. $this->getIPAddress() .' '. TTi18n::getText('Type').': '. $type .' '.  TTi18n::getText('SessionID') .': '.$this->getSessionID() .' '.  TTi18n::getText('UserID').': '. $this->getObject()->getId(), $this->getObject()->getID() , 'authentication'); //Login

			$this->rl->delete(); //Clear failed password rate limit upon successful login.

			return TRUE;
		}

		Debug::text('Login Failed! Attempt: '. $this->rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);

		sleep( ($this->rl->getAttempts()*0.5) ); //If password is incorrect, sleep for some time to slow down brute force attacks.

		return FALSE;
	}

	function Logout( $session_id = NULL ) {
		$this->destroyCookie();
		$this->Delete();

		TTDebug::addEntry( $this->getObject()->getID(), 110,  TTi18n::getText('SourceIP').': '. $this->getIPAddress() .' '.  TTi18n::getText('SessionID').': '.$this->getSessionID() .' '.  TTi18n::getText('UserID').': '. $this->getObject()->getId(), $this->getObject()->getID() , 'authentication');

		BreadCrumb::Delete();

		return TRUE;
	}

	function Check($session_id = NULL) {
		global $profiler;
		$profiler->startTimer( "Authentication::Check()");

		//Debug::text('Session Name: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);

		//Support session_ids passed by cookie, post, and get.
		if ( $session_id == '' ) {
			if ( isset($_COOKIE[$this->getName()]) AND $_COOKIE[$this->getName()] != '' ) {
				$session_id = $_COOKIE[$this->getName()];
			} elseif ( isset($_POST[$this->getName()]) AND $_POST[$this->getName()] != '' ) {
				$session_id = $_POST[$this->getName()];
			} elseif ( isset($_GET[$this->getName()]) AND $_GET[$this->getName()] != '' ) {
				$session_id = $_GET[$this->getName()];
			} else {
				$session_id = FALSE;
			}
		}

		/*
		if ( $session_id == '' AND isset($_COOKIE[$this->getName()]) ) {
			$session_id = $_COOKIE[$this->getName()];
		}
		*/

		Debug::text('Session ID: '. $session_id .' URL: '. $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10);
		//Checks session cookie, returns user_id;
		if ( isset( $session_id ) ) {

			/*
				Bind session ID to IP address to aid in preventing session ID theft,
				if this starts to cause problems
				for users behind load balancing proxies, allow them to choose to
				bind session IDs to just the first 1-3 quads of their IP address
				as well as the MD5 of their user-agent string.
				Could also use "behind proxy IP address" if one is supplied.
			*/
			$this->setSessionID( $session_id );
			$this->setIPAddress();

			if ( $this->Read() == TRUE ) {

				//touch UpdatedDate
				$this->Update();

				$profiler->stopTimer( "Authentication::Check()");
				return TRUE;
			}
		}

		$profiler->stopTimer( "Authentication::Check()");

		return FALSE;
	}
}
?>
