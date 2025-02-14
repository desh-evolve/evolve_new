<?php

class Debug {
	static protected $enable = FALSE; 			//Enable/Disable debug printing.
	static protected $verbosity = 5; 			//Display debug info with a verbosity level equal or lesser then this.
	static protected $buffer_output = TRUE; 	//Enable/Disable output buffering.
	static protected $debug_buffer = NULL; 		//Output buffer.
	static protected $enable_tidy = FALSE; 		//Enable/Disable tidying of output
	static protected $enable_display = FALSE;	//Enable/Disable displaying of debug output
	static protected $enable_log = FALSE; 		//Enable/Disable logging of debug output
	static protected $max_buffer_size = 5000;	//Max buffer size in lines.

	static protected $buffer_size = 0; 			//Current buffer size in lines.

	static $tidy_obj = NULL;

	static function setEnable($bool) {
		self::$enable = $bool;
	}

	static function getEnable() {
		return self::$enable;
	}

	static function setBufferOutput($bool) {
		self::$buffer_output = $bool;
	}

	static function setVerbosity($level) {
		global $db;

		self::$verbosity = $level;

		if (is_object($db) AND $level == 11) {
			$db->debug=TRUE;
		}
	}
	static function getVerbosity() {
		return self::$verbosity;
	}

	static function setEnableTidy($bool) {
		self::$enable_tidy = $bool;
	}

	static function getEnableTidy() {
		return self::$enable_tidy;
	}

	static function setEnableDisplay($bool) {
		self::$enable_display = $bool;
	}

	static function getEnableDisplay() {
		return self::$enable_display;
	}

	static function setEnableLog($bool) {
		self::$enable_log = $bool;
	}

	static function getEnableLog() {
		return self::$enable_log;
	}

	static function Text($text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = "[Function]";
		}

		$text = 'DEBUG ['. $line .']:'. "\t" .'<b>'. $method .'()</b>: '. $text ."<br>\n";

		if ( self::$buffer_output == TRUE ) {
			self::$debug_buffer[] = array($verbosity, $text);
			self::$buffer_size++;
		} else {
			if ( self::$enable_display == TRUE ) {
				echo $text;
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				syslog(LOG_WARNING, $text );
			}
		}

		return true;
	}

	static function profileTimers( $profile_obj ) {
		if ( !is_object($profile_obj) ) {
			return FALSE;
		}
		
		ob_start();
		$profile_obj->printTimers();
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	static function backTrace() {
		ob_start();
		debug_print_backtrace();
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	static function varDump( $array ) {
		ob_start();
		var_dump($array); //Xdebug may interfere with this and cause it to not display all the data...
		//print_r($array);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	static function Arr($array, $text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = "[Function]";
		}

		$output = 'DEBUG ['. $line .'] Array: <b>'. $method .'()</b>: '. $text ."\n";
		$output .= "<pre>\n". self::varDump($array) ."</pre><br>\n";

		if (self::$buffer_output == TRUE) {
			self::$debug_buffer[] = array($verbosity, $output);
			self::$buffer_size = self::$buffer_size + count(array($array));
		} else {
			if ( self::$enable_display == TRUE ) {
				echo $output;
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				syslog(LOG_WARNING, $text );
			}
		}

		return TRUE;
	}

	static function getOutput() {
		$output = NULL;
		if ( count(self::$debug_buffer) > 0 ) {
			foreach (self::$debug_buffer as $arr) {
				$verbosity = $arr[0];
				$text = $arr[1];

				if ($verbosity <= self::getVerbosity() ) {
					$output .= $text;
				}
			}

			return $output;
		}

		return FALSE;
	}

	static function emailLog() {
		if ( PRODUCTION === TRUE ) {
			$output = self::getOutput();

			if (strlen($output) > 0) {
				$server_domain = Misc::getHostName();

				//mail('root@'.$server_domain,, $output, "From: ". APPLICATION_NAME ."@".$server_domain."\n");
				Misc::sendSystemMail( APPLICATION_NAME. ' - Error!', $output );
			}
		}
		return TRUE;
	}

	static function writeToLog() {
		if (self::$enable_log == TRUE AND self::$buffer_output == TRUE) {
			global $config_vars;

			$date_format = 'D M j G:i:s T Y';
			$file_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR .'timetrex.log';

			$eol = "\n";
			if ( is_writable( $config_vars['path']['log'] ) ) {
				$output = '---------------[ '. Date('r') .' (PID: '.getmypid().') ]---------------'.$eol;
				if ( is_array(self::$debug_buffer) ) {
					foreach (self::$debug_buffer as $arr) {

						$verbosity = $arr[0];
						$text = $arr[1];

						if ($verbosity <= self::getVerbosity() ) {
							$output .= $text;
						}
					}
				}
				$output .= '---------------[ '. Date('r') .' (PID: '.getmypid().') ]---------------'.$eol;

				$fp = @fopen( $file_name,'a' );
				@fwrite($fp, strip_tags( $output ) );
				@fclose($fp);
				unset($output);
			}
		}

		return FALSE;
	}

	static function Display() {
		//if (self::$enable == TRUE AND self::$buffer_output == TRUE) {
		if (self::$enable_display == TRUE AND self::$buffer_output == TRUE) {

			$output = self::getOutput();

			if ( function_exists('memory_get_usage') ) {
				$memory_usage = memory_get_usage();
			} else {
				$memory_usage = "N/A";
			}

			if (strlen($output) > 0) {
				echo "<br>\n<b>Debug Buffer</b><br>\n";
				echo "============================================================================<br>\n";
				echo "Memory Usage: ". $memory_usage ." Buffer Size: ". self::$buffer_size."<br>\n";
				echo "----------------------------------------------------------------------------<br>\n";
				echo $output;
				echo "============================================================================<br>\n";
			}

		}
	}

	static function Tidy() {
		if (self::$enable_tidy == TRUE ) {

			$tidy_config = Environment::getBasePath() .'/includes/tidy.conf';

			self::$tidy_obj = tidy_parse_string( ob_get_contents(), $tidy_config );

			//erase the output buffer
			ob_clean();

			//tidy_clean_repair();
			self::$tidy_obj->cleanRepair();

			echo self::$tidy_obj;

		}
		return TRUE;
    }

	static function DisplayTidyErrors() {
		if ( self::$enable_tidy == TRUE
				AND ( tidy_error_count(self::$tidy_obj) > 0 OR tidy_warning_count(self::$tidy_obj) > 0 ) ) {
			echo "<br>\n<b>Tidy Output</b><br><pre>\n";
			echo "============================================================================<br>\n";
			echo htmlentities( self::$tidy_obj->errorBuffer );
			echo "============================================================================<br></pre>\n";
		}
	}

	static function clearBuffer() {
		self::$debug_buffer = NULL;
		return TRUE;
	}
}
?>