<?php

class Environment {

	static protected $template_dir = 'templates';
	static protected $template_compile_dir = 'templates_c';

	static function getBasePath() {
		//return dirname( __FILE__ ) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR;
		return str_replace('classes'. DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR .'core', '', dirname( __FILE__ ) );
	}

	static function getBaseURL() {
		global $config_vars;

		if ( isset($config_vars['path']['base_url']) ) {
			return $config_vars['path']['base_url']. '/'; //Don't use directory separator here
		}

		return '/';
	}

	//Returns the BASE_URL for the API functions.
	static function getAPIBaseURL( $api = NULL ) {
		global $config_vars;

		//If "interface" appears in the base URL, replace it with API directory
		$base_url = str_replace( array('/interface','/api'), '', $config_vars['path']['base_url']);

		if ( $api == '' ) {
			if ( defined('TIMETREX_AMF_API') AND TIMETREX_AMF_API == TRUE ) {
				$api = 'amf';
			} elseif ( defined('TIMETREX_SOAP_API') AND TIMETREX_SOAP_API == TRUE )  {
				$api = 'soap';
			} elseif ( defined('TIMETREX_JSON_API') AND TIMETREX_JSON_API == TRUE )  {
				$api = 'json';
			}
		}

		$base_url = $base_url.'/api/'.$api.'/';

		return $base_url;
	}

	static function getAPIURL( $api ) {
		return self::getAPIBaseURL( $api ).'api.php';
	}

	static function getTemplateDir() {
		return self::getBasePath() . self::$template_dir . DIRECTORY_SEPARATOR;
	}

	static function getTemplateCompileDir() {
		return self::getBasePath() . self::$template_compile_dir . DIRECTORY_SEPARATOR;
	}

	static function getImagesPath() {
		return self::getBasePath() . DIRECTORY_SEPARATOR .'interface'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR;
	}

	static function getImagesURL() {
		return self::getBaseURL() .'images/';
	}

	static function getStorageBasePath() {
		global $config_vars;

		return $config_vars['path']['storage'] . DIRECTORY_SEPARATOR;
	}
	
	
	
	 //ARSP ADD NEW CODE FOR GET USER FILE STORAGE PATH
    //ARSP ADD SOME CODE FOR timetrex.ini.php FILE ALSO
    static function getUserFileStorageBasePath() {
		global $config_vars;  
                
                //echo "test config 12345".$config_vars['path']['user_file_path'] . DIRECTORY_SEPARATOR;;

		return $config_vars['path']['user_file_path'] . '/';
	}

	static function getLogBasePath() {
		global $config_vars;

		return $config_vars['path']['log'] . DIRECTORY_SEPARATOR;
	}

}
?>
