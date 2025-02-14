<?php

require_once( Environment::getBasePath() .'/classes/pear/System/SharedMemory.php');
class SharedMemory {
	protected $obj = NULL;

	function __construct() {
		global $config_vars;
		if ( OPERATING_SYSTEM == 'WIN' ) {
			$this->obj = &System_SharedMemory::Factory( 'File', array('tmp' => $config_vars['cache']['dir'] ) );
		} else {
			$this->obj = &System_SharedMemory::Factory( 'File', array('tmp' => $config_vars['cache']['dir'] ) );
			//$this->obj = &System_SharedMemory::Factory( 'Systemv', array( 'size' => $size ) ); //Run into size issues all the time.
		}

		return TRUE;
	}

	function set( $key, $value ) {
		if ( is_string( $key ) ) {
			return $this->obj->set( $key, $value );
		}
		return FALSE;
	}

	function get( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->get( $key );
		}
		return FALSE;
	}

	function delete( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->rm( $key );
		}
		return FALSE;
	}
}
?>
