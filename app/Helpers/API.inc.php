<?php
/*********************************************************************************

 ********************************************************************************/

define('TIMETREX_API', TRUE );
forceNoCacheHeaders(); //Send headers to disable caching.

//Returns valid classes when unauthenticated.
function getUnauthenticatedAPIClasses() {
	return array('APIAuthentication','APIClientStationUnAuthenticated', 'APIAuthenticationPlugin', 'APIClientStationUnAuthenticatedPlugin', 'APIProgressBar', 'APIInstall');
}

//Returns session ID from _COOKIE, _POST, then _GET.
function getSessionID() {
	if ( isset($_COOKIE['SessionID']) AND $_COOKIE['SessionID'] != '' ) {
		$session_id = $_COOKIE['SessionID'];
	} elseif ( isset($_POST['SessionID']) AND $_POST['SessionID'] != '' ) {
		$session_id = $_POST['SessionID'];
	} elseif ( isset($_GET['SessionID']) AND $_GET['SessionID'] != '' ) {
		$session_id = $_GET['SessionID'];
	} else {
		$session_id = FALSE;
	}

	return $session_id;
}

//Returns Station ID from _COOKIE, _POST, then _GET.
function getStationID() {
	if ( isset($_COOKIE['StationID']) AND $_COOKIE['StationID'] != '' ) {
		$station_id = $_COOKIE['StationID'];
	} elseif ( isset($_POST['StationID']) AND $_POST['StationID'] != '' ) {
		$station_id = $_POST['StationID'];
	} elseif ( isset($_GET['StationID']) AND $_GET['StationID'] != '' ) {
		$station_id = $_GET['StationID'];
	} else {
		$station_id = FALSE;
	}

	return $station_id;
}

//Make sure cron job information is always logged.
//Don't do this until log rotation is implemented.
/*
Debug::setEnable( TRUE );
Debug::setBufferOutput( TRUE );
Debug::setEnableLog( TRUE );
if ( Debug::getVerbosity() <= 1 ) {
	Debug::setVerbosity( 1 );
}
*/
?>