<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/

use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Misc;
use App\Models\Core\Profiler;
use App\Models\Core\Redirect;

/*
 * $Revision: 5613 $
 * $Id: global.inc.php 5613 2011-11-24 19:49:41Z ipso $
 * $Date: 2011-11-24 11:49:41 -0800 (Thu, 24 Nov 2011) $
 */

$global_script_start_time = microtime(true);

//BUG in PHP 5.2.2 that causes $HTTP_RAW_POST_DATA not to be set. Work around it.
if (strpos(phpversion(), '5.2.2') !== FALSE) {
    $HTTP_RAW_POST_DATA = file_get_contents("php://input");
}

if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

ob_start(); //Take care of GZIP in Apache

ini_set( 'max_execution_time', 1800 );
//Disable magic quotes at runtime. Require magic_quotes_gpc to be disabled during install.
//Check: http://ca3.php.net/manual/en/security.magicquotes.php#61188 for disabling magic_quotes_gpc
ini_set( 'magic_quotes_runtime', 0 );

define('APPLICATION_VERSION', '3.7.0' );

/*
	Config file inside webroot.
*/
$configFilePath = config('evolve.paths.base_url');  // Get base URL or config file path from config file

if ($configFilePath) {
    // Now, you can use config values rather than the raw ini file
    // Use values from config file, you no longer need to parse INI manually
    $config_vars = config('evolve');  // Load config directly from Laravel config
} else {
    echo "Config file does not exist!\n";
    exit;
}

/*
	Config file outside webroot.
*/
//define('CONFIG_FILE', '/etc/timetrex.ini.php');

if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
	define('OPERATING_SYSTEM', 'WIN');
} else {
	define('OPERATING_SYSTEM', 'LINUX');
}

if ( isset($config_vars['debug']['production']) AND $config_vars['debug']['production'] == 1 ) {
	define('PRODUCTION', TRUE);
} else {
	define('PRODUCTION', FALSE);
}

// **REMOVING OR CHANGING THIS APPLICATION NAME AND ORGANIZATION URL IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT**
// Define APPLICATION_NAME
if (isset($config_vars['branding']['application_name']) && $config_vars['branding']['application_name'] != '') {
    define('APPLICATION_NAME', $config_vars['branding']['application_name']);
} else {
    define('APPLICATION_NAME', (PRODUCTION == FALSE) ? 'evolve-Debug' : 'Evolve');
}

// Define ORGANIZATION_NAME
if (isset($config_vars['branding']['organization_name']) && $config_vars['branding']['organization_name'] != '') {
    define('ORGANIZATION_NAME', $config_vars['branding']['organization_name']);
} else {
    define('ORGANIZATION_NAME', 'Evolve');
}

// Define ORGANIZATION_URL
if (isset($config_vars['branding']['organization_url']) && $config_vars['branding']['organization_url'] != '') {
    define('ORGANIZATION_URL', $config_vars['branding']['organization_url']);
} else {
    define('ORGANIZATION_URL', 'www.evolbve-sl.com');
}

if ( isset($config_vars['other']['demo_mode']) AND $config_vars['other']['demo_mode'] == 1 ) {
	define('DEMO_MODE', TRUE);
} else {
	define('DEMO_MODE', FALSE);
}

if ( isset($config_vars['other']['deployment_on_demand']) AND $config_vars['other']['deployment_on_demand'] == 1 ) {
	define('DEPLOYMENT_ON_DEMAND', TRUE);
} else {
	define('DEPLOYMENT_ON_DEMAND', FALSE);
}

if ( isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] > 0 ) {
	define('PRIMARY_COMPANY_ID', (int)$config_vars['other']['primary_company_id']);
} else {
	define('PRIMARY_COMPANY_ID', FALSE);
}



//Try to dynamically load required PHP extensions if they aren't already.
//This saves people from having to modify php.ini if possible.
//v5.3 of PHP deprecates DL().
if ( version_compare(PHP_VERSION, '5.3.0', '<') AND function_exists('dl') == TRUE AND (bool)ini_get( 'enable_dl' ) == TRUE AND (bool)ini_get( 'safe_mode' ) == FALSE ) {
	$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';

	//This quite possibly breaks PEAR's Cache_Lite <= v1.7.2 because
	//it uses strlen() on binary data to write the cache file?
	//http://pear.php.net/bugs/bug.php?id=8361
	/*
	if ( extension_loaded('mbstring') == FALSE ) {
		@dl($prefix . 'mbstring.' . PHP_SHLIB_SUFFIX);
		ini_set('mbstring.func_overload', 7); //Overload all string functions.
	}
	*/

	if ( extension_loaded('gettext') == FALSE ) {
		@dl($prefix . 'gettext.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('bcmath') == FALSE ) {
		@dl($prefix . 'bcmath.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('soap') == FALSE ) {
		@dl($prefix . 'soap.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('mcrypt') == FALSE ) {
		@dl($prefix . 'mcrypt.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('calendar') == FALSE ) {
		@dl($prefix . 'calendar.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('gd') == FALSE ) {
		@dl($prefix . 'gd.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('gd') == FALSE AND extension_loaded('gd2') == FALSE ) {
		@dl($prefix . 'gd2.' . PHP_SHLIB_SUFFIX);
	}
	if ( extension_loaded('ldap') == FALSE ) {
		@dl($prefix . 'ldap.' . PHP_SHLIB_SUFFIX);
	}

	//Load database extension based on config file.
	if ( isset($config_vars['database']['type']) ) {
		if ( stristr($config_vars['database']['type'], 'postgres') AND extension_loaded('pgsql') == FALSE ) {
			@dl($prefix . 'pgsql.' . PHP_SHLIB_SUFFIX);
		} elseif ( stristr($config_vars['database']['type'], 'mysqlt') AND extension_loaded('mysql') == FALSE ) {
			@dl($prefix . 'mysql.' . PHP_SHLIB_SUFFIX);
		} elseif ( stristr($config_vars['database']['type'], 'mysqli') AND extension_loaded('mysqli') == FALSE ) {
			@dl($prefix . 'mysqli.' . PHP_SHLIB_SUFFIX);
		}
	}
}

//Windows doesn't define LC_MESSAGES, so lets do it manually here.
if ( defined('LC_MESSAGES') == FALSE) {
	define('LC_MESSAGES', 6);
}

//IIS 5 doesn't seem to set REQUEST_URI, so attempt to build one on our own
//This also appears to fix CGI mode.
//Inspired by: http://neosmart.net/blog/2006/100-apache-compliant-request_uri-for-iis-and-windows/
if ( !isset($_SERVER['REQUEST_URI']) ) {
	if ( isset($_SERVER['SCRIPT_NAME']) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	} elseif ( isset( $_SERVER['PHP_SELF']) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
	}

	if ( isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] != '') {
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
	}
}

//HTTP Basic authentication doesn't work properly with CGI/FCGI unless we decode it this way.
if ( ( PHP_SAPI == 'cgi' OR PHP_SAPI == 'cgi-fcgi' ) AND isset($_SERVER['HTTP_AUTHORIZATION']) ) {
	//<IfModule mod_rewrite.c>
	//RewriteEngine on
	//RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
	//</IfModule>
	list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode( substr( $_SERVER['HTTP_AUTHORIZATION'], 6) ) );
}


require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR .'ClassMap.inc.php');

function custom_autoload( $name ) {
	global $global_class_map, $profiler;

	if ( isset($profiler) ) {
		$profiler->startTimer( 'custom_autoload' );
	}

	if ( isset($global_class_map[$name]) ) {
		$file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR . $global_class_map[$name];
	} else {
		//If the class name contains "plugin", try to load classes directly from the plugins directory.
		if ( strpos( $name, 'Plugin') === FALSE ) {
			$file_name = $name .'.class.php';
		} else {
			$file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR . 'plugins'  . DIRECTORY_SEPARATOR . str_replace('Plugin', '', $name ) .'.plugin.php';
		}
	}

	//Use include_once() instead of require_once so the installer
	//doesn't Fatal Error without displaying anything.
	//include_once() is redundant in custom_autoload.
	//Debug::Text('Autoloading Class: '. $name .' File: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
	//Debug::Arr(Debug::BackTrace(), 'Backtrace: ', __FILE__, __LINE__, __METHOD__,10);
	@include( $file_name );

	if ( isset($profiler) ) {
		$profiler->stopTimer( 'custom_autoload' );
	}

	return TRUE;
}
spl_autoload_register('custom_autoload'); //Registers the autoloader mainly for use with PHPUnit

//The basis for the plugin system, instantiate all classes through this, allowing the class to be overloaded on the fly by a class in the plugin directory.
//ie: $uf = TTNew( 'UserFactory' ); OR $uf = TTNew( 'UserFactory', $arg1, $arg2, $arg3 );
function TTnew( $class_name ) { //Unlimited arguments are supported.
    global $config_vars;

    //Check if the plugin system is enabled in the config.
    if ( isset($config_vars['other']['enable_plugins']) AND $config_vars['other']['enable_plugins'] == 1 ) {
        $plugin_class_name = $class_name.'Plugin';

		//This improves performance greatly for classes with no plugins.
		//But it may cause problems if the original class was somehow loaded before the plugin.
		//However the plugin wouldn't apply to it anyways in that case.
		//
		//Due to a bug that would cause the plugin to not be properly loaded if both the Factory and ListFactory were loaded in the same script
		//we need to always reload the plugin class if the current class relates to it.
		$is_class_exists = class_exists( $class_name, FALSE );
		if ( $is_class_exists == FALSE OR ( $is_class_exists == TRUE AND stripos( $plugin_class_name, $class_name ) !== FALSE ) ) {
			if ( class_exists( $plugin_class_name, FALSE ) == FALSE ) {
				//Class file needs to be loaded.
				$plugin_directory = Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'plugins';
				$plugin_class_file_name = $plugin_directory . DIRECTORY_SEPARATOR . $class_name .'.plugin.php';
				//Debug::Text('Plugin System enabled! Looking for class: '. $class_name .' in file: '. $plugin_class_file_name, __FILE__, __LINE__, __METHOD__,10);
				if ( file_exists( $plugin_class_file_name ) ) {
					@include_once( $plugin_class_file_name );
					$class_name = $plugin_class_name;
					Debug::Text('Found Plugin: '. $plugin_class_file_name .' Class: '. $class_name, __FILE__, __LINE__, __METHOD__,10);
				}
			} else {
				//Class file is already loaded.
				$class_name = $plugin_class_name;
			}
		}
    }

    if ( func_num_args() > 1 ) {
        $params = func_get_args();
        array_shift( $params ); //Eliminate the class name argument.

		$reflection_class = new ReflectionClass($class_name);
		return $reflection_class->newInstanceArgs($params);
    } else {
		return new $class_name();
    }
}



function TTgetPluginClassName( $class_name ) {
	global $config_vars;

	//Check if the plugin system is enabled in the config.
	if ( isset($config_vars['other']['enable_plugins']) AND $config_vars['other']['enable_plugins'] == 1 ) {
		$plugin_class_name = $class_name.'Plugin';

		//This improves performance greatly for classes with no plugins.
		//But it may cause problems if the original class was somehow loaded before the plugin.
		//However the plugin wouldn't apply to it anyways in that case.
		//
		//Due to a bug that would cause the plugin to not be properly loaded if both the Factory and ListFactory were loaded in the same script
		//we need to always reload the plugin class if the current class relates to it.
		$is_class_exists = class_exists( $class_name, FALSE );
		if ( $is_class_exists == FALSE OR ( $is_class_exists == TRUE AND stripos( $plugin_class_name, $class_name ) !== FALSE ) ) {
			if ( class_exists( $plugin_class_name, FALSE ) == FALSE ) {
				//Class file needs to be loaded.
				$plugin_directory = Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'plugins';
				$plugin_class_file_name = $plugin_directory . DIRECTORY_SEPARATOR . $class_name .'.plugin.php';
				//Debug::Text('Plugin System enabled! Looking for class: '. $class_name .' in file: '. $plugin_class_file_name, __FILE__, __LINE__, __METHOD__,10);
				if ( file_exists( $plugin_class_file_name ) ) {
					@include_once( $plugin_class_file_name );
					$class_name = $plugin_class_name;
					Debug::Text('Found Plugin: '. $plugin_class_file_name .' Class: '. $class_name, __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				//Class file is already loaded.
				$class_name = $plugin_class_name;
			}
		}
		//else {
			//Debug::Text('Plugin not found...', __FILE__, __LINE__, __METHOD__, 10);
		//}
	}
	//else {
		//Debug::Text('Plugins disabled...', __FILE__, __LINE__, __METHOD__, 10);
	//}

	return $class_name;
}



//Function to force browsers to cache certain files.
function forceCacheHeaders( $file_name = NULL, $mtime = NULL, $etag = NULL ) {
	if ( $file_name == '' ) {
		$file_name = $_SERVER['SCRIPT_FILENAME'];
	}

	if ( $mtime == '' ) {
		$file_modified_time = filemtime($file_name);
	} else {
		$file_modified_time = $mtime;
	}

	if ( $etag != '' ) {
		$etag = trim($etag);
	}

	//For some reason even with must-revalidate the browsers won't check ETag every page load.
	//So some pages may get cached for an hour or two regardless of ETag changes.
	Header('Cache-Control: must-revalidate, max-age=0');
	Header('Cache-Control: private', FALSE);
	Header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	//Check eTag first, then last modified time.
	if ( ( isset($_SERVER['HTTP_IF_NONE_MATCH']) AND trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag )
			OR ( !isset($_SERVER['HTTP_IF_NONE_MATCH'])
					AND isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
					AND strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $file_modified_time ) ) {
		//Cached page, send 304 code and exit.
		Header('HTTP/1.1 304 Not Modified');
		Header('Connection: close');
		ob_clean();
		exit; //File is cached, don't continue.
	} else {
		//Not cached page, add headers to assist caching.
		if ( $etag != '' ) {
			Header('ETag: '. $etag);
		}
		Header('Last-Modified: '.gmdate('D, d M Y H:i:s', $file_modified_time).' GMT');
	}

	return TRUE;
}



//Force no caching of file.
function forceNoCacheHeaders() {

	//CSP headers break many things at this stage, unless "unsafe" is used for almost everything.
	//Header('Content-Security-Policy: default-src *; script-src \'self\' *.google-analytics.com *.google.com');
	Header('Content-Security-Policy: default-src * \'unsafe-inline\'; script-src \'unsafe-eval\' \'unsafe-inline\' \'self\' *.google-analytics.com *.google.com');

	//Help prevent XSS or frame clickjacking.
	Header('X-XSS-Protection: 1; mode=block');
	Header('X-Frame-Options: SAMEORIGIN');

	//Reduce MIME-TYPE security risks.
	Header('X-Content-Type-Options: nosniff');
	
	//Turn caching off.
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
	//Can Break IE with downloading PDFs over SSL.
	// IE gets: "file could not be written to cache"
	// It works on some IE installs though.
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
	if ( isset($_SERVER['HTTP_USER_AGENT']) AND stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE ) {
		header('Pragma: token'); //If set to no-cache it breaks IE downloading reports, with error that the site is not available.
		if ( preg_match('/(?i)MSIE [5-9]/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
			header('Connection: close'); //ie6-9 may send empty POST requests causing API errors due to poor keepalive handling, so force all connections to close instead.
		}
	} else {
		header('Pragma: no-cache');
	}

	//Only when force_ssl is enabled and the user is using SSL, include the STS header.
	global $config_vars;

	// Check if SSL is forced and the current request is using HTTPS
	if (isset($config_vars['other']['force_ssl']) && $config_vars['other']['force_ssl'] === true && Misc::isSSL(true)) {
		header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
	}
}



define('TT_PRODUCT_PROFESSIONAL', 20 );
define('TT_PRODUCT_BUSINESS', 15 );
define('TT_PRODUCT_STANDARD', 10 );
function getTTProductEdition() {
	if ( file_exists( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'job'. DIRECTORY_SEPARATOR .'JobFactory.class.php') ) {
		return TT_PRODUCT_PROFESSIONAL;
	} elseif ( file_exists( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'time_clock'. DIRECTORY_SEPARATOR .'TimeClock.class.php') ) {
		return TT_PRODUCT_BUSINESS;
	}

	return TT_PRODUCT_STANDARD;
}
function getTTProductEditionName( ) {
	switch( getTTProductEdition() ) {
		case 15:
			$retval = 'Business';
			break;
		case 20:
			$retval = 'Professional';
			break;
		default:
			$retval = 'Standard';
			break;
	}

	return $retval;
}

//This has to be first, always.
require_once app_path('Models/Core/Environment.php');
require_once app_path('Models/Core/Profiler.php');

$profiler = new Profiler( true );

set_include_path(
					'.' . PATH_SEPARATOR .
					Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . 'core' .
					PATH_SEPARATOR . Environment::getBasePath() .'classes' .
					PATH_SEPARATOR . Environment::getBasePath() .'classes' . DIRECTORY_SEPARATOR .'plugins' .
					//PATH_SEPARATOR . get_include_path() . //Don't include system include path, as it can cause conflicts with other packages bundled with TimeTrex. However the bundled PEAR.php must check for class_exists('PEAR') to prevent conflicts with PHPUnit.
					PATH_SEPARATOR . Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR . 'pear' ); //Put PEAR path at the end so system installed PEAR is used first, this prevents require_once() from including PEAR from two directories, which causes a fatal error.

require_once app_path('Models/Core/Exception.php');
require_once app_path('Models/Core/Debug.php');

if ( isset($_SERVER['REQUEST_URI']) ) {
	Debug::Text('URI: '. $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10);
}
Debug::Text('Version: '. APPLICATION_VERSION .' Edition: '. getTTProductEdition() .' Production: '. (int)PRODUCTION .' Demo Mode: '. (int)DEMO_MODE, __FILE__, __LINE__, __METHOD__, 10);

if ( function_exists('bcscale') ) {
	bcscale(10);
}

Debug::setEnable( (bool)$config_vars['debug']['enable'] );
Debug::setEnableTidy( FALSE );
Debug::setEnableDisplay( (bool)$config_vars['debug']['enable_display'] );
Debug::setBufferOutput( (bool)$config_vars['debug']['buffer_output'] );
Debug::setEnableLog( (bool)$config_vars['debug']['enable_log'] );
Debug::setVerbosity( (int)$config_vars['debug']['verbosity'] );

if ( Debug::getEnable() == TRUE AND Debug::getEnableDisplay() == TRUE ) {
	ini_set( 'display_errors', 1 );
} else {
	ini_set( 'display_errors', 0 );
}

//Make sure we are using SSL if required.
if ( $config_vars['other']['force_ssl'] == 1 AND !isset( $_SERVER['HTTPS'] ) AND isset( $_SERVER['HTTP_HOST'] ) AND isset( $_SERVER['REQUEST_URI'] ) AND !isset( $disable_https ) AND !isset( $enable_wap ) AND php_sapi_name() != 'cli' ) {
	Redirect::Page( 'https://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
	exit;
}

if ( isset($enable_wap) AND $enable_wap == TRUE ) {
	header( 'Content-type: text/vnd.wap.wml', TRUE );
}

require_once('Cache.inc.php');
require_once('Database.inc.php');
?>
