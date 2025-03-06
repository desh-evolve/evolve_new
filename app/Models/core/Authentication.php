<?php

namespace App\Models\Core;

use App\Models\Company\CompanyListFactory;
use App\Models\Users\UserIdentificationListFactory;
use App\Models\Users\UserListFactory;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

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

		$query = 'update authentication set user_id = :user_id
					where session_id = :session_id
					';

		try {
			DB::update($query, $ph);
		} catch (Throwable $e) {
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

			
			$ulf->data = (array)$ulf->rs[0];
			
			foreach ($ulf->data as $user) {
				$this->obj = $ulf;

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
		return $_COOKIE['SessionID'];
		//return md5(uniqid(dechex(mt_rand()), true));
		//return bin2hex(random_bytes(16));
	}	

	public function checkCompanyStatus( $user_name ) {
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

	/*
	function checkPassword($user_name, $password) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$ulf->getByUserNameAndStatus(strtolower(trim($user_name)), 10 ); //Active
		
		foreach ($ulf as $user) {
			echo '<br>hi<br>';
			if ( $user->checkPassword($password) ) {
				$this->setObject( $user->getID() );
				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}
	*/

	function checkPassword($user_name, $password) {
		//Use UserFactory to set name.
		$ulf = new UserListFactory();

		$users = $ulf->getByUserNameAndStatus(strtolower(trim($user_name)), 10 ); //Active
		
		if($users->rs){
			if ( $ulf->checkPassword($password) ) {
				$this->setObject( $ulf->getID() );
				return TRUE;
			} else {
				return FALSE;
			}
		}else{
			return FALSE;
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

	private function setCookie()
	{
		if ($this->getSessionID()) {
			Cookie::queue($this->getName(), $this->getSessionID(), 9999999, '/', null, $this->isSSL(), false);
			return true;
		}

		return false;
	}

	private function destroyCookie()
	{
		Cookie::queue(Cookie::forget($this->getName()));
		return true;
	}

	private function UpdateLastLoginDate() {
		$query = 'UPDATE users SET last_login_date = ? WHERE id = ?';
		$ph = [
			TTDate::getTime(),
			(int) $this->getObject()->getID(),
		];
		
		try {
			DB::update($query, $ph);
		} catch (Throwable $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Update() {
		$ph = array(
					'updated_date' => TTDate::getTime(),
					'session_id' => $this->getSessionID(),
					);

		$query = 'update authentication set updated_date = :updated_date
					where session_id = :session_id';

		try {
			DB::update($query, $ph);
		} catch (Throwable $e) {
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
						where session_id = :session_id
							OR (updated_date - created_date) > '. (86400*2) .'
							OR ('. TTDate::getTime() .' - updated_date) > 86400';

		try {
			DB::delete($query, $ph);
		} catch (Throwable $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Write() {
		
		$ph = array(
			':session_id' => $this->getSessionID(),
			':user_id' => $this->getObject()->getID(),
			':ip_address' => $this->getIPAddress(),
			':created_date' => $this->getCreatedDate(),
			':updated_date' => $this->getUpdatedDate()
		);

		$query = 'insert into authentication (session_id,user_id,ip_address,created_date,updated_date)
					VALUES(
							:session_id,
							:user_id,
							:ip_address,
							:created_date,
							:updated_date
						)';
		try {
			DB::insert($query, $ph);
		} catch (Throwable $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	private function Read() {
		
		$ph = array(
			':session_id' => $this->getSessionID(),
			':ip_address' => $this->getIPAddress(),
			':updated_date' => ( TTDate::getTime() - $this->getIdle() ),
		);

		$query = 'select session_id,user_id,ip_address,created_date,updated_date from authentication
			WHERE session_id = :session_id
			AND ip_address = :ip_address
			AND updated_date >= :updated_date
		';
		
		//Debug::text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		
		//$result = $this->db->GetRow($query, $ph);
		$result = DB::select($query, $ph);
		//print_r($ph);exit;
		// Get the first result
		$result = !empty($result) ? $result : [];
		if ( count($result) > 0) {
			$result = (array)$result[0];
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

	public function login($user_name, $password, $type = 'USER_NAME')
	{
		$user_name = trim(html_entity_decode($user_name));
		$password = html_entity_decode($password);

		if (empty($user_name) || empty($password)) {
			return false;
		}

		Log::info('Login Type: ' . $type);

		$ipAddress = request()->ip();
		$key = "login_attempts_{$ipAddress}";
		
		/*
		// check here => remove this after developing login
		// Prevent brute force attacks
		if (RateLimiter::tooManyAttempts($key, 20)) {
			Log::warning("Excessive failed login attempts from $ipAddress. Locking for 15 minutes.");
			sleep(5);
			return false;
		}
		*/
		$password_result = false;

		switch (strtolower($type)) {
			case 'user_name':
				if ($this->checkCompanyStatus($user_name)) {
					$password_result = $this->checkPassword(strtolower($user_name), $password);
				}
				break;
			case 'phone_id':
				$password_result = $this->checkPhonePassword($user_name, $password);
				break;
			case 'ibutton':
				$password_result = $this->checkIButton($user_name);
				break;
			case 'barcode':
				$password_result = $this->checkBarcode($user_name, $password);
				break;
			case 'finger_print':
				$password_result = $this->checkFingerPrint($user_name);
				break;
			case 'client_pc':
				//This is for client application persistent connections, use:
				//Login Type: client_pc
				//Station Type: PC

				//$password_result = $this->checkClientPC( $user_name );
				$password_result = $this->checkBarcode($user_name, $password);
				break;
			default:
				return false;
		}

		if ($password_result === true) {
			Log::info('Login Successful!');

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

			TTLog::addEntry( $this->getObject()->getID(), 100,  ('SourceIP').': '. $this->getIPAddress() .' '. ('Type').': '. $type .' '.  ('SessionID') .': '.$this->getSessionID() .' '.  ('UserID').': '. $this->getObject()->getId(), $this->getObject()->getID() , 'authentication'); //Login

			Log::info("User {$user_name} logged in successfully with IP: {$ipAddress}");

			// Clear rate limit on successful login
			RateLimiter::clear($key);

			return true;
		}

		Log::warning("Login Failed! Attempts: " . RateLimiter::attempts($key));

		RateLimiter::hit($key, 1800); // 30-minute limit
		sleep(RateLimiter::attempts($key) * 0.5); //If password is incorrect, sleep for some time to slow down brute force attacks.

		return false;
	}

	function Logout( $session_id = NULL ) {
		$this->destroyCookie();
		$this->Delete();

		TTLog::addEntry( $this->getObject()->getID(), 110,  ('SourceIP').': '. $this->getIPAddress() .' '.  ('SessionID').': '.$this->getSessionID() .' '.  ('UserID').': '. $this->getObject()->getId(), $this->getObject()->getID() , 'authentication');

		BreadCrumb::Delete();

		return TRUE;
	}

	function Check($session_id = NULL) {
		global $profiler;

		$profiler = new Profiler();
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
			//echo 'read:';
			//print_r($this->Read());exit;
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
