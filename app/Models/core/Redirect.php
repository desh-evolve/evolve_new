<?php

namespace App\Models\Core;

class Redirect {
	static function page($url = NULL) {
		if ( empty($url) AND !empty($_SERVER['HTTP_REFERER']) ) {
			$url = $_SERVER['HTTP_REFERER'];
		}

		Debug::Text('Redirect URL: '. $url, __FILE__, __LINE__, __METHOD__,11);

		if ( Debug::getVerbosity() != 11 ) {
			header("Location: $url\n\n");

			//Prevent the rest of the script from running after redirect?
			Debug::writeToLog();

			ob_clean();
			exit;
		}

		return TRUE;
	}
}
?>
