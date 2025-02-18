<?php

namespace App\Models\Core;

class LockFile {
	var $file_name = NULL;

	var $max_lock_file_age = 86400;

	function __construct( $file_name ) {

		$this->file_name = $file_name;

		return TRUE;
	}

	function getFileName( ) {
		return $this->file_name;
	}

	function setFileName($file_name) {
		if ( $file_name != '') {
			$this->file_name = $file_name;

			return TRUE;
		}

		return FALSE;
	}

	function create() {
		return touch( $this->getFileName() );
	}

	function delete() {
		if ( file_exists( $this->getFileName() ) ) {
			return unlink( $this->getFileName() );
		}

		Debug::text(' Failed deleting lock file: '. $this->file_name, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function exists() {
		//Ignore lock files older than max_lock_file_age, so if the server crashes or is rebooted during an operation, it will start again the next day.
		clearstatcache();
		if ( file_exists( $this->getFileName() ) AND filemtime( $this->getFileName() ) >= ( time()-$this->max_lock_file_age ) ) {
			return TRUE;
		}

		return FALSE;
	}
}
?>
