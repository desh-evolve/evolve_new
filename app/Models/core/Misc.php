<?php

namespace App\Models\Core;

use App\Models\Accrual\AccrualListFactory;
use App\Models\Company\CompanyFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Report\TimesheetDetailReport;
use DateTime;

class Misc {
	/*
		this method assumes that the form has one or more
		submit buttons and that they are named according
		to this scheme:

		<input type="submit" name="submit:command" value="some value">

		This is useful for identifying which submit button actually
		submitted the form.
	*/
	static function findSubmitButton( $prefix = 'action' ) {
		// search post vars, then get vars.
		$queries = array($_POST, $_GET);
		foreach($queries as $query) {
			foreach($query as $key => $value) {
				//Debug::Text('Key: '. $key .' Value: '. $value, __FILE__, __LINE__, __METHOD__,10);
				$newvar = explode(':', $key, 2);
				//Debug::Text('Explode 0: '. $newvar[0] .' 1: '. $newvar[1], __FILE__, __LINE__, __METHOD__,10);
				 if ( isset($newvar[0]) AND isset($newvar[1]) AND $newvar[0] === $prefix ) {
					$val = $newvar[1];

					// input type=image stupidly appends _x and _y.
					if ( substr($val, strlen($val) - 2) === '_x' ) {
						$val = substr($val, 0, strlen($val) - 2);
					}

					//Debug::Text('Found Button: '. $val, __FILE__, __LINE__, __METHOD__,10);
					return strtolower($val);
				}
			}
		}

		return NULL;
	}

	static function getSortDirectionArray( $text_keys = FALSE ) {
		if ( $text_keys === TRUE ) {
			return array('asc' => 'ASC', 'desc' => 'DESC');
		} else {
			return array(1 => 'ASC', -1 => 'DESC');
		}
	}

	//This function totals arrays where the data wanting to be totaled is deep in a multi-dimentional array.
	//Usually a row array just before its passed to smarty.
	static function ArrayAssocSum($array, $element = NULL, $decimals = NULL) {

		$retarr = array();
		$totals = array();

		foreach($array as $key => $value) {
			if ( isset($element) AND isset($value[$element]) ) {
				foreach($value[$element] as $sum_key => $sum_value ) {
					if ( !isset($totals[$sum_key]) ) {
						$totals[$sum_key] = 0;
					}
					$totals[$sum_key] += $sum_value;
				}
			} else {
				//Debug::text(' Array Element not set: ', __FILE__, __LINE__, __METHOD__,10);
				foreach($value as $sum_key => $sum_value ) {
					if ( !isset($totals[$sum_key]) ) {
						$totals[$sum_key] = 0;
					}
					if ( !is_numeric( $sum_value )) {
						$sum_value = 0;
					}
					$totals[$sum_key] += $sum_value;
					//Debug::text(' Sum: '. $totals[$sum_key] .' Key: '. $sum_key .' This Value: '. $sum_value, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		//format totals
		if ( $decimals !== NULL ) {
			foreach($totals as $retarr_key => $retarr_value) {
				//echo "Key: $retarr_key Value: $retarr_value<br>\n";
				//Debug::text(' Number Formatting: '. $retarr_value , __FILE__, __LINE__, __METHOD__,10);
				$retarr[$retarr_key] = number_format($retarr_value, $decimals, '.','');
				//$retarr[$retarr_key] = round( $retarr_value, $decimals );
			}
		} else {
			return $totals;
		}
		unset($totals);

		return $retarr;
	}

	//This function is similar to a SQL group by clause, only its done on a AssocArray
	//Pass it a row array just before you send it to smarty.
	static function ArrayGroupBy($array, $group_by_elements, $ignore_elements = array() ) {

		if ( !is_array($group_by_elements) ) {
			$group_by_elements = array($group_by_elements);
		}

		if ( isset($ignore_elements) AND is_array($ignore_elements) ) {
			foreach($group_by_elements as $group_by_element) {
				//Remove the group by element from the ignore elements.
				unset($ignore_elements[$group_by_element]);
			}
		}

		$retarr = array();
		if ( is_array($array) ) {
			foreach( $array as $row) {
				$group_by_key_val = NULL;
				foreach($group_by_elements as $group_by_element) {
					if ( isset($row[$group_by_element]) ) {
						$group_by_key_val .= $row[$group_by_element];
					}
				}
				//Debug::Text('Group By Key Val: '. $group_by_key_val, __FILE__, __LINE__, __METHOD__,10);

				if ( !isset($retarr[$group_by_key_val]) ) {
					$retarr[$group_by_key_val] = array();
				}

				foreach( $row as $key => $val) {
					//Debug::text(' Key: '. $key .' Value: '. $val , __FILE__, __LINE__, __METHOD__,10);
					if ( in_array($key, $group_by_elements) ) {
						$retarr[$group_by_key_val][$key] = $val;
					} elseif( !in_array($key, $ignore_elements) ) {
						if ( isset($retarr[$group_by_key_val][$key]) ) {
							$retarr[$group_by_key_val][$key] = Misc::MoneyFormat( bcadd($retarr[$group_by_key_val][$key],$val), FALSE);
							//Debug::text(' Adding Value: '. $val .' For: '. $retarr[$group_by_key_val][$key], __FILE__, __LINE__, __METHOD__,10);
						} else {
							//Debug::text(' Setting Value: '. $val , __FILE__, __LINE__, __METHOD__,10);
							$retarr[$group_by_key_val][$key] = $val;
						}
					}
				}
			}
		}

		return $retarr;
	}

	static function ArrayAvg($arr) {

		if ((!is_array($arr)) OR (!count($arr) > 0)) {
			return FALSE;
		}

		return array_sum($arr) / count($arr);
	}

	
	static function prependArray($prepend_arr, $arr = null) {
		if ( !is_array($prepend_arr) AND is_array($arr) ) {
			return $arr;
		} elseif ( is_array($prepend_arr) AND !is_array($arr) ) {
			return $prepend_arr;
		} elseif ( !is_array($prepend_arr) AND !is_array($arr) ) {
			return FALSE;
		}

		$retarr = $prepend_arr;

		foreach($arr as $key => $value) {
			//Don't overwrite entries from the prepend array.
			if ( !isset($retarr[$key]) ) {
				$retarr[$key] = $value;
			}
		}

		return $retarr;
	}

	function flattenArray($array, $preserve = FALSE, $r = array() ) {
		foreach( $array as $key => $value ){
			if ( is_array($value) ) {
				foreach( $value as $k => $v ) {
					if ( is_array($v) ) {
						$tmp = $v;
						unset($value[$k]);
					}
				}

				if ($preserve) {
					$r[$key] = $value;
				} else {
					$r[] = $value;
				}
			}

			$r = isset($tmp) ? self::flattenArray($tmp, $preserve, $r) : $r;
		}

		return $r;
	}

	/*
		When passed an array of input_keys, and an array of output_key => output_values,
		this function will return all the output_key => output_value pairs where
		input_key == output_key
	*/
	static function arrayIntersectByKey( $keys, $options ) {
		if ( is_array($keys) and is_array($options) ) {
			foreach( $keys as $key ) {
				if ( isset($options[$key]) AND $key !== FALSE ) { //Ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return NULL;
	}

	/*
		When passed an associative array from a ListFactory, ie:
		array( 	0 => array( <...Data ..> ),
				1 => array( <...Data ..> ),
				2 => array( <...Data ..> ),
				... )
		this function will return an associative array of only the key=>value
		pairs that intersect across all rows.

	*/
	static function arrayIntersectByRow( $rows ) {
		if ( !is_array($rows) ) {
			return FALSE;
		}

		if ( count($rows) < 2 ) {
			return FALSE;
		}

		$retval = FALSE;
		if ( isset($rows[0]) ) {
			$retval = call_user_func_array( 'array_intersect_assoc', $rows );
			Debug::Arr($retval, 'Intersected/Common Data', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $retval;
	}
	/*
		Returns all the output_key => output_value pairs where
		the input_keys are not present in output array keys.

	*/
	static function arrayDiffByKey( $keys, $options ) {
		if ( is_array($keys) and is_array($options) ) {
			foreach( $options as $key => $value ) {
				if ( !in_array($key, $keys, TRUE) ) { //Use strict we ignore boolean FALSE, so the Root group isn't always selected.
					$retarr[$key] = $options[$key];
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		//Return NULL because if we return FALSE smarty will enter a
		//"blank" option into select boxes.
		return NULL;
	}

	function arrayMergeRecursiveDistinct( array $array1, array $array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) AND isset( $merged[$key] ) AND is_array( $merged[$key] ) ) {
				$merged[$key] = self::arrayMergeRecursiveDistinct( $merged[$key], $value );
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	static function arrayDiffAssocRecursive($array1, $array2) {
		if ( is_array($array1) ) {
			foreach($array1 as $key => $value) {
				if ( is_array($value) ) {
					  if ( !isset($array2[$key]) ) {
						  $difference[$key] = $value;
					  } elseif( !is_array($array2[$key]) ) {
						  $difference[$key] = $value;
					  } else {
						  $new_diff = self::arrayDiffAssocRecursive($value, $array2[$key]);
						  if ( $new_diff !== FALSE ) {
								$difference[$key] = $new_diff;
						  }
					  }
				  } elseif ( !isset($array2[$key]) OR $array2[$key] != $value ) {
					  $difference[$key] = $value;
				  }
			}
		}

		if ( !isset($difference) ) {
			return FALSE;
		}

		return $difference;
	}

	//Adds prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	static function addKeyPrefix( $prefix, $arr ) {
		foreach( $arr as $key => $value ) {
			$retarr[$prefix.$key] = $value;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}
	//Removes prefix to all array keys, mainly for reportings and joining array data together to avoid conflicting keys.
	static function removeKeyPrefix( $prefix, $arr ) {
		foreach( $arr as $key => $value ) {
			$retarr[self::strReplaceOnce($prefix, '', $key)] = $value;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	//Adds sort prefixes to an array maintaining the original order. Primarily used because Flex likes to reorded arrays with string keys.
	static function addSortPrefix( $arr, $begin_counter = 1 ) {
		$i=$begin_counter;
		foreach( $arr as $key => $value ) {
			$sort_prefix = NULL;
			if ( substr($key, 0, 1 ) != '-' ) {
				$sort_prefix = '-'.str_pad($i, 4, 0, STR_PAD_LEFT).'-';
			}
			$retarr[$sort_prefix.$key] = $value;
			$i++;
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	//Removes sort prefixes from an array.
	static function trimSortPrefix( $value, $trim_arr_value = FALSE ) {
		if ( is_array($value) AND count($value) > 0 ) {
			foreach( $value as $key => $val ) {
				if ( $trim_arr_value == TRUE ) {
					$retval[$key] = preg_replace('/^-[0-9]{3,4}-/i', '', $val);
				} else {
					$retval[preg_replace('/^-[0-9]{3,4}-/i', '', $key)] = $val;
				}
			}
		} else {
			$retval = preg_replace('/^-[0-9]{3,4}-/i', '', $value );
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return $value;
	}

	static function strReplaceOnce($str_pattern, $str_replacement, $string){
        if ( strpos($string, $str_pattern) !== FALSE ) {
            $occurrence = strpos($string, $str_pattern);
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
        }

        return $string;
    }

	static function FileDownloadHeader($file_name, $type, $size) {
		if ( $file_name == '' OR $size == '') {
			return FALSE;
		}

		$agent = trim($_SERVER['HTTP_USER_AGENT']);
		if ((preg_match('|MSIE ([0-9.]+)|', $agent, $version)) OR
			(preg_match('|Internet Explorer/([0-9.]+)|', $agent, $version))) {
			//header('Content-Type: application/x-msdownload');
			Header('Content-Type: '. $type);
			if ($version == '5.5') {
				header('Content-Disposition: filename="'.$file_name.'"');
			} else {
				header('Content-Disposition: attachment; filename="'.$file_name.'"');
			}
		} else {
			Header('Content-Type: '. $type);
			Header('Content-disposition: inline; filename='.$file_name);
		}

		Header('Content-Length: '. $size);

		return TRUE;
	}

	//This function helps sending binary data to the client for saving/viewing as a file.
	static function APIFileDownload($file_name, $type, $data) {
		if ( $file_name == '' OR $data == '' ) {
			return FALSE;
		}

		$size = strlen($data);

		self::FileDownloadHeader( $file_name, $type, $size );
		echo $data;
		return TRUE;
	}

	static function removeTrailingZeros( $value, $minimum_decimals = 2 ) {
		//Remove trailing zeros after the decimal, leave a minimum of X though.
		if ( strpos( $value, '.') !== FALSE ) {
			$trimmed_value = rtrim( $value, 0);

			$tmp_minimum_decimals = strlen( (int)strrev($trimmed_value) );
			if ( $tmp_minimum_decimals > $minimum_decimals ) {
				$minimum_decimals = $tmp_minimum_decimals;
			}
			return number_format( $value, $minimum_decimals, '.', '' );
		}

		return $value;
	}

	static function MoneyFormat($value, $pretty = TRUE) {

		if ( $pretty == TRUE ) {
			$thousand_sep = ',';
		} else {
			$thousand_sep = '';
		}

		return number_format( (float)$value, 2, '.', $thousand_sep);
	}

	//Removes vowels from the string always keeping the first letter.
	static function abbreviateString( $str ) {
		$vowels = array('a', 'e', 'i', 'o', 'u');

		$retarr = array();
		$words = explode( ' ', trim($str) );
		if ( is_array($words) ) {
			foreach( $words as $word ) {
				$first_letter_in_word = substr( $word, 0, 1);
				$word = str_ireplace( $vowels, '', trim($word) );
				if ( substr( $word, 0, 1) != $first_letter_in_word ) {
					$word = $first_letter_in_word.$word;
				}
				$retarr[] = $word;
			}

			return implode(' ', $retarr);
		}

		return FALSE;
	}

	static function TruncateString( $str, $length, $start = 0, $abbreviate = FALSE ) {
		if ( strlen( $str ) > $length ) {
			if ( $abbreviate == TRUE ) {
				//Try abbreviating it first.
				$retval = trim( substr( self::abbreviateString( $str ), $start, $length ) );
				if ( strlen( $retval ) > $length ) {
					$retval .= '...';
				}
			} else {
				$retval = trim( substr( trim($str), $start, $length ) ).'...';
			}
		} else {
			$retval = $str;
		}

		return $retval;
	}

	static function HumanBoolean($bool) {
		if ( $bool == TRUE ) {
			return 'Yes';
		} else {
			return 'No';
		}
	}

	static function getBeforeDecimal($float) {
		$float = Misc::MoneyFormat( $float, FALSE );

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[0]) ) {
			return $float_array[0];
		}

		return FALSE;
	}

	static function getAfterDecimal($float, $format_number = TRUE ) {
		if ( $format_number == TRUE ) {
			$float = Misc::MoneyFormat( $float, FALSE );
		}

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[1]) ) {
			return str_pad($float_array[1],2,'0');
		}

		return FALSE;
	}

	//Encode integer to a alphanumeric value that is reversible.
	static function encodeInteger( $int ) {
		return strtoupper( base_convert( strrev( str_pad( $int, 11, 0, STR_PAD_LEFT ) ), 10, 36) );
	}
	static function decodeInteger( $str ) {
		return (int)str_pad( strrev( base_convert( $str, 36, 10) ), 11, 0, STR_PAD_RIGHT );
	}

	static function calculatePercent( $current, $maximum, $precision = 0 ) {
		if ( $maximum == 0 ) {
			return 100;
		}

		$percent = round( ( ( $current / $maximum ) * 100 ), (int)$precision );

		if ( $precision == 0 ) {
			$percent = (int)$percent;
		}

		return $percent;
	}

	//Takes an array with columns, and a 2nd array with column names to sum.
	static function sumMultipleColumns($data, $sum_elements) {
		if (!is_array($data) ) {
			return FALSE;
		}

		if (!is_array($sum_elements) ) {
			return FALSE;
		}

		$retval = 0;

		foreach($sum_elements as $sum_element ) {
			if ( isset($data[$sum_element]) ) {
				$retval = bcadd( $retval, $data[$sum_element]);
				//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return $retval;
	}

	static function calculateMultipleColumns($data, $include_elements = array(), $exclude_elements = array() ) {
		if ( !is_array($data) ) {
			return FALSE;
		}

		$retval = 0;

		if ( is_array( $include_elements ) ) {
			foreach($include_elements as $include_element ) {
				if ( isset($data[$include_element]) ) {
					$retval = bcadd( $retval, $data[$include_element]);
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		if ( is_array( $exclude_elements ) ) {
			foreach($exclude_elements as $exclude_element ) {
				if ( isset($data[$exclude_element]) ) {
					$retval = bcsub( $retval, $data[$exclude_element]);
					//Debug::Text('Found Element in Source Data: '. $sum_element .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		return $retval;
	}

	static function getPointerFromArray( $array, $element, $start = 1 ) {
		//Debug::Arr($array, 'Source Array: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Text('Searching for Element: '. $element, __FILE__, __LINE__, __METHOD__,10);
		$keys = array_keys( $array );
		//Debug::Arr($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__,10);

		//Debug::Text($keys, 'Source Array Keys: ', __FILE__, __LINE__, __METHOD__,10);
		$key = array_search( $element, $keys );

		if ( $key !== FALSE ) {
			$key = $key + $start;
		}

		//Debug::Arr($key, 'Result: ', __FILE__, __LINE__, __METHOD__,10);
		return $key;
	}

	static function AdjustXY( $coord, $adjust_coord) {
		return $coord + $adjust_coord;
	}

	function writeBarCodeFile($file_name, $num, $print_text = TRUE, $height = 60 ) {
		if ( !class_exists('Image_Barcode') ) {
			require_once(Environment::getBasePath().'/classes/Image_Barcode/Barcode.php');
		}

		ob_start();
		Image_Barcode::draw($num, 'code128', 'png', FALSE, $print_text, $height);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		if ( file_put_contents($file_name, $ob_contents) > 0 ) {
			//echo "Writing file successfull<Br>\n";
			return TRUE;
		} else {
			//echo "Error writing file<Br>\n";
			return FALSE;
		}
	}

	static function hex2rgb($hex, $asString = true) {
		// strip off any leading #
		if (0 === strpos($hex, '#')) {
			$hex = substr($hex, 1);
		} else if (0 === strpos($hex, '&H')) {
			$hex = substr($hex, 2);
		}

		// break into hex 3-tuple
		$cutpoint = ceil(strlen($hex) / 2)-1;
		$rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

		// convert each tuple to decimal
		$rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
		$rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
		$rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);

		return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
	}

	static function Array2CSV( $data, $columns = NULL, $ignore_last_row = TRUE, $include_header = TRUE, $eol = "\n" ) {
		if ( is_array($data) AND count($data) > 0
				AND is_array($columns) AND count($columns) > 0 ) {

			if ( $ignore_last_row === TRUE ) {
				array_pop($data);
			}

			//Header
			if ( $include_header == TRUE ) {
				foreach( $columns as $column_name ) {
					$row_header[] = $column_name;
				}
				$out = '"'.implode('","', $row_header).'"'.$eol;
			} else {
				$out = NULL;
			}

			foreach( $data as $rows ) {
				foreach ($columns as $column_key => $column_name ) {
					if ( isset($rows[$column_key]) ) {
						$row_values[] = str_replace("\"", "\"\"", $rows[$column_key]);
					} else {
						//Make sure we insert blank columns to keep proper order of values.
						$row_values[] = NULL;
					}
				}

				$out .= '"'.implode('","', $row_values).'"'.$eol;
				unset($row_values);
			}

			return $out;
		}

		return FALSE;
	}

        static function Array2CSVReport( $data, $eol = "\n" ) {
            
            foreach ($data as $key => $row) {
                $employee_number[$key] = $row['employee_number'];
            }

            array_multisort($employee_number, SORT_ASC, $data);
		if ( is_array($data) AND count($data) > 0) {

			foreach( $data as $rows ) {
	//                                $out .=  'Pay Period'.','.$rows['pay_period'].$eol;
				$out .=  'EPF Number'.','.$rows['employee_number'].$eol;
                                $out .=  'Full Name'.','.$rows['first_name'].' '.$rows['last_name'].$eol;
                                $out .=  'Department'.','.$rows['default_department'].$eol.$eol;
                                
				$out .=  'Date,First In,Last Out,Worked Hrs,Status 1, Status 2,Late Arrival (Minute),Early Departure(Minute))'.$eol;
                                
                                $nof_ot_days = 0;
                                $rows['tot_data'] = $rows['data'][count($rows['data']) - 1];
                                array_pop($rows['data']);
                                
                                foreach($rows['data'] as $sub_data){
                                        
                                        if($sub_data['date_stamp'] != NULL ){
                                            $date = DateTime::createFromFormat('d/m/Y', $sub_data['date_stamp'])->format('d/m/Y D');
                                        
                                        if($sub_data['min_punch_time_stamp'] != NULL){
                                            $datetime1 = new DateTime();
                                            $datetime1->setTimestamp($sub_data['min_punch_time_stamp']);
                                            $min_punch = $datetime1->format("H:i");
                                        } else {
                                            $min_punch = '-';
                                        }

                                        if($sub_data['max_punch_time_stamp'] != NULL){
                                            $datetime2 = new DateTime();
                                            $datetime2->setTimestamp($sub_data['max_punch_time_stamp']);
                                            $max_punch = $datetime2->format("H:i");
                                        } else {
                                            $max_punch = '-';
                                        }
                                        
                                        if($sub_data['min_punch_time_stamp'] != NULL && $sub_data['max_punch_time_stamp'] != NULL){
                                            $interval = $datetime1->diff($datetime2);
                                            $total_work_hr = $total_work_hr + $interval;
                                            $date_int = $interval->format("%H:%I");
                                        } else {
                                            $date_int = '-';
                                        }
                                        
                                        $dateStamp = '';
                                        if ($sub_data['date_stamp'] != '') {
                                            $dateStamp = DateTime::createFromFormat('d/m/Y', $sub_data['date_stamp'])->format('Y-m-d');
                                        }

                                        if (isset($sub_data['over_time']) && $sub_data['over_time'] != '') {
                                             $nof_ot_days++;
                                        }
                                        
                                        $ttdr = new TimesheetDetailReport();
                                        $EmpDateStatus = $ttdr->getReportStatusByUserIdAndDate($rows['user_id'], $dateStamp);

                                        $status1 = $status2 = '';
                                        $earlySec = $lateSec = 0;
                                        if (($sub_data['min_punch_time_stamp'] != '' && $sub_data['min_punch_time_stamp'] != "") &&
                                                ($sub_data['shedule_start_time'] != "" && $sub_data['shedule_end_time'] != "")) {

                                            if($sub_data['min_punch_time_stamp'] !=''){
                                               $lateSec = strtotime($sub_data['shedule_start_time']) - $sub_data['min_punch_time_stamp'];
                                            }

                                            if($sub_data['max_punch_time_stamp'] !=''){
                                               $earlySec = strtotime($sub_data['shedule_end_time']) - $sub_data['max_punch_time_stamp'];
                                            }
                                            
                                            if ($earlySec > 0) {

                                                $alf = new AccrualListFactory();
                                                $day_check =$sub_data['date_stamp'];
                                                $ch_date = DateTime::createFromFormat('d/m/Y', $day_check);
                                                $ph_date = $ch_date->format('Y-m-d');
                                                
                                                $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],20,$ph_date);

                                                 if($alf->getRecordCount() > 0){
                                                     $a_obj =  $alf->getCurrent();
                                                     if($a_obj->getAccrualPolicyID()==8)
                                                     {
                                                         $nof_short_leave++;
                                                     }else{
                                                         $nof_leave++;
                                                     }
                                                 } else {
                                                     $totEarly = $totEarly + abs($earlySec);
                                                     $early = gmdate("H:i", abs($earlySec));
                                                     $nof_early++;
                                                 }
                                            }
                                            if ($lateSec < 0) {
                                                $totLate = $totLate + abs($lateSec);
                                                $late = gmdate("H:i", abs($lateSec));
                                                $nof_late++;
                                            }
                                            $status1 = 'P';
                                            $status2 = 'P';
                                        } else {
                                            $day = explode(' ', $date['date']);
                                            if ($day[1] == 'Sun') {
                                                if ($row_data_day_key[$date['day']]['worked_time'] != "") {
                                                    $status1 = 'POW';
                                                    $status2 = 'POW';
                                                    $nof_presence++;
                                                } else {
                                                    $status1 = 'WO';
                                                    $status2 = 'WO';
                                                }
                                            } else {
                                                $status1 = 'A';
                                                $status2 = 'A';
                                            }
                                        }
                                        if ($EmpDateStatus['status1'] == 'P') {
                                            $nof_presence++;
                                        }

                                        if ($sub_data['min_punch_time_stamp'] == '' || $sub_data['max_punch_time_stamp'] == '') {
                                            $date_int = '';
                                        }

                                        $day_2 = explode(' ', $date);

                                        if ($day_2[1] == 'Sun' || $day_2[1] == 'Sat') {
                                            $EmpDateStatus['status1'] = 'WO';
                                        }
                                        $date = DateTime::createFromFormat('d/m/Y', $sub_data['date_stamp'])->format('d/m/Y D');
                                        $date_actual = DateTime::createFromFormat('d/m/Y', $day_2[0])->format('Y-m-d'); 
                                        if($EmpDateStatus['status1'] == 'AB'){
                                            $alf = new AccrualListFactory();

                                            $alf->getByAccrualByUserIdAndTypeIdAndDate($rows['user_id'],20,$date_actual);

                                              if($alf->getRecordCount() > 0){

                                                  $EmpDateStatus['status2'] ='LV';
                                                  $nof_leave++;
                                              }
                                              else{

                                                  $nof_no_pay++;
                                              }
                                        }
                                        $out .= $date.','.$min_punch.','.$max_punch.','.$date_int.','.$EmpDateStatus['status1'].','.$EmpDateStatus['status2'].','.$late.','.$early.$eol;
                                        $early = '';
                                        $late = '';
                                }
                                
                        }
                                //total
                                $out .='Total,, '.$rows['tot_data']['worked_time'].',,,,'.gmdate("H:i", $totLate).','.gmdate("H:i", $totEarly).$eol;
                                $totLate = '';
                                $totEarly = '';
                                
                                $out .= $eol.$eol;
                                
			}
			return $out;
		}
		return FALSE;
	}
        
             
     static function getRemoteIPAddress() {
		global $config_vars;

		if ( isset($config_vars['other']['proxy_ip_address_header_name']) AND $config_vars['other']['proxy_ip_address_header_name'] != '' ) {
			$header_name = $config_vars['other']['proxy_ip_address_header_name'];
		}

		if ( isset($header_name) AND isset($_SERVER[$header_name]) AND $_SERVER[$header_name] != ''  ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'] .' Behind Proxy IP: '. $_SERVER[$header_name], __FILE__, __LINE__, __METHOD__, 10);
			return $_SERVER[$header_name];
		} elseif( isset($_SERVER['REMOTE_ADDR']) ) {
			//Debug::text('Remote IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
			return $_SERVER['REMOTE_ADDR'];
		}

		return FALSE;
	}

        
        
        
	static function inArrayByKeyAndValue( $arr, $search_key, $search_value ) {
		if ( !is_array($arr) AND $search_key != '' AND $search_value != '') {
			return FALSE;
		}

		//Debug::Text('Search Key: '. $search_key .' Search Value: '. $search_value, __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($arr, 'Hay Stack: ', __FILE__, __LINE__, __METHOD__,10);

		foreach( $arr as $arr_key => $arr_value ) {
			if ( isset($arr_value[$search_key]) ) {
				if ( $arr_value[$search_key] == $search_value ) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	//This function is used to quickly preset array key => value pairs so we don't
	//have to have so many isset() checks throughout the code.
	static function preSetArrayValues( $arr, $keys, $preset_value = NULL ) {
		foreach( $keys as $key ) {
			if ( !isset($arr[$key]) ) {
				$arr[$key] = $preset_value;
			}
		}

		return $arr;
	}

	function parseCSV($file, $head = FALSE, $first_column = FALSE, $delim="," , $len = 9216, $max_lines = NULL ) {
		if ( !file_exists($file) ) {
			Debug::text('Files does not exist: '. $file, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$return = false;
		$handle = fopen($file, "r");
		if ( $head !== FALSE ) {
			if ( $first_column !== FALSE ) {
			   while ( ($header = fgetcsv($handle, $len, $delim) ) !== FALSE) {
				   if ( $header[0] == $first_column ) {
					   //echo "FOUND HEADER!<br>\n";
					   $found_header = TRUE;
					   break;
				   }
			   }

			   if ( $found_header !== TRUE ) {
				   return FALSE;
			   }
			} else {
			   $header = fgetcsv($handle, $len, $delim);
			}
		}

		$i=1;
		while ( ($data = fgetcsv($handle, $len, $delim) ) !== FALSE) {
			if ( $head AND isset($header) ) {
				foreach ($header as $key => $heading) {
					$row[trim($heading)] = ( isset($data[$key]) ) ? $data[$key] : '';
				}
				$return[] = $row;
			} else {
				$return[] = $data;
			}

			if ( $max_lines !== NULL AND $max_lines != '' AND $i == $max_lines ) {
				break;
			}

			$i++;
		}

		fclose($handle);

		return $return;
	}

	function importApplyColumnMap( $column_map, $csv_arr ) {
		if ( !is_array($column_map) ) {
			return FALSE;
		}

		if ( !is_array($csv_arr) ) {
			return FALSE;
		}

		foreach( $column_map as $map_arr ) {
			$timetrex_column = $map_arr['timetrex_column'];
			$csv_column = $map_arr['csv_column'];
			$default_value = $map_arr['default_value'];

			if ( isset($csv_arr[$csv_column]) AND $csv_arr[$csv_column] != '' ) {
				$retarr[$timetrex_column] = trim( $csv_arr[$csv_column] );
				//echo "NOT using default value: ". $default_value ."\n";
			} elseif ( $default_value != '' ) {
				//echo "using Default value! ". $default_value ."\n";
				$retarr[$timetrex_column] = trim( $default_value );
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function importCallInputParseFunction( $function_name, $input, $default_value = NULL, $parse_hint = NULL ) {
		$full_function_name = 'parse_'.$function_name;

		if ( function_exists( $full_function_name ) ) {
			//echo "      Calling Custom Parse Function for: $function_name\n";
			return call_user_func( $full_function_name, $input, $default_value, $parse_hint );
		}

		return $input;
	}

	static function encrypt( $str, $key = NULL ) {
		if ( $str == '' ) {
			return FALSE;
		}

		if ( $key == NULL OR $key == '' ) {
			global $config_vars;
			$key = $config_vars['other']['salt'];
		}

		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		$max_key_size = mcrypt_enc_get_key_size($td);
		mcrypt_generic_init($td, substr($key, 0, $max_key_size), $iv);

		$encrypted_data = base64_encode( mcrypt_generic($td, trim($str) ) );

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $encrypted_data;
	}

	static function decrypt( $str, $key = NULL ) {
		if (  $key == NULL OR $key == '' ) {
			global $config_vars;
			$key = $config_vars['other']['salt'];
		}

		if ( $str == '' ) {
			return FALSE;
		}

		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		$max_key_size = mcrypt_enc_get_key_size($td);
		mcrypt_generic_init($td, substr($key, 0, $max_key_size), $iv);

		$unencrypted_data = rtrim( mdecrypt_generic($td, base64_decode( $str ) ) );

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $unencrypted_data;
	}

	static function getJSArray( $values, $name = NULL, $assoc = FALSE) {
		if ( $name != '' AND (bool)$assoc == TRUE ) {
			$retval = 'new Array();';
			if ( is_array($values) AND count($values) > 0 ) {
				foreach( $values as $key => $value ) {
					$retval .= $name.'[\''. $key .'\']=\''. $value .'\';';
				}
			}
		} else {
			$retval = 'new Array("';
			if ( is_array($values) AND count($values) > 0 ) {
				$retval .= implode('","', $values);
			}
			$retval .= '");';
		}

		return $retval;
	}

	//Uses the internal array pointer to get array neighnors.
	static function getArrayNeighbors( $arr, $key, $neighbor = 'both' ) {
		$neighbor = strtolower($neighbor);
		//Neighor can be: Prev, Next, Both

		$retarr = array( 'prev' => FALSE, 'next' => FALSE );

		$keys = array_keys($arr);
		$key_indexes = array_flip($keys);

		if ( $neighbor == 'prev' OR $neighbor == 'both' ) {
			if ( isset($keys[$key_indexes[$key]-1]) ) {
				$retarr['prev'] = $keys[$key_indexes[$key]-1];
			}
		}

		if ( $neighbor == 'next' OR $neighbor == 'both' ) {
			if ( isset($keys[$key_indexes[$key]+1]) ) {
				$retarr['next'] = $keys[$key_indexes[$key]+1];
			}
		}
		//next($arr);

		return $retarr;
	}

	static function getHostName( $include_port = TRUE ) {
		global $config_vars;

		$server_port = NULL;
		if ( isset( $_SERVER['SERVER_PORT'] ) ) {
			$server_port = ':'.$_SERVER['SERVER_PORT'];
		}

		if ( defined('DEPLOYMENT_ON_DEMAND') AND DEPLOYMENT_ON_DEMAND == TRUE AND isset($config_vars['other']['hostname']) AND $config_vars['other']['hostname'] != '' ) {
			$server_domain = $config_vars['other']['hostname'];
		} else {
			//Try server hostname/servername first, than fallback on .ini hostname setting.
			//If the admin sets the hostname in the .ini file, always use that, as the servers hostname from the CLI could be incorrect.
			if ( isset($config_vars['other']['hostname']) AND $config_vars['other']['hostname'] != '' ) {
				$server_domain = $config_vars['other']['hostname'];
				if ( strpos( $server_domain, ':') === FALSE ) {
					//Add port if its not already specified.
					$server_domain .= $server_port;
				}
			} elseif ( isset( $_SERVER['HTTP_HOST'] ) ) { //Use HTTP_HOST instead of SERVER_NAME first so it includes any custom ports.
				$server_domain = $_SERVER['HTTP_HOST'];
			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$server_domain = $_SERVER['SERVER_NAME'].$server_port;
			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
				$server_domain = $_SERVER['HOSTNAME'].$server_port;
			} else {
				$server_domain = 'localhost'.$server_port;
			}
		}

		if ( $include_port == FALSE ) {
			//strip off port, important for sending emails.
			$server_domain = str_replace( $server_port, '', $server_domain );
		}

		return $server_domain;
	}

	static function isOpenPort( $address, $port = 80, $timeout = 3 ) {
		$checkport = @fsockopen($address, $port, $errnum, $errstr, $timeout); //The 2 is the time of ping in secs

		//Check if port is closed or open...
		if( $checkport == FALSE ) {
			return FALSE;
		}

		return TRUE;
	}

	static function array_isearch( $str, $array ) {
		foreach ( $array as $key => $value ) {
			if ( strtolower( $value ) == strtolower( $str ) ) {
				return $key;
			}
		}

		return FALSE;
	}

	//Accepts a search_str and key=>val array that it searches through, to return the array key of the closest fuzzy match.
	static function findClosestMatch( $search_str, $search_arr, $minimum_percent_match = 0, $return_all_matches = FALSE ) {
		if ( $search_str == '' ) {
			return FALSE;
		}

		if ( !is_array($search_arr) OR count($search_arr) == 0 ) {
			return FALSE;
		}

		foreach( $search_arr as $key => $search_val ) {
			similar_text( strtolower($search_str), strtolower($search_val), $percent);
			if ( $percent >= $minimum_percent_match ) {
				$matches[$key] = $percent;
			}
		}

		if ( isset($matches) AND count($matches) > 0 ) {
			arsort($matches);

			if ( $return_all_matches == TRUE ) {
				return $matches;
			}

			//Debug::Arr( $search_arr, 'Search Str: '. $search_str .' Search Array: ' , __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr( $matches, 'Matches: ' , __FILE__, __LINE__, __METHOD__, 10);

			reset($matches);
			return key($matches);
		}

		//Debug::Text('No match found for: '. $search_str, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//Converts a number between 0 and 25 to the corresponding letter.
	static function NumberToLetter( $number ) {
		if ( $number > 25 ) {
			return FALSE;
		}

		return chr($number+65);
	}

	static function issetOr( &$var, $default = NULL ) {
		if ( isset($var) ) {
			return $var;
		}

		return $default;
	}

	static function getFullName($first_name, $middle_name, $last_name, $reverse = FALSE, $include_middle = TRUE) {
		if ( $first_name != '' AND $last_name != '' ) {
			if ( $reverse === TRUE ) {
				$retval = $last_name .', '. $first_name;
				if ( $include_middle == TRUE AND $middle_name != '' ) {
					$retval .= ' '.$middle_name[0].'.'; //Use just the middle initial.
				}
			} else {
				$retval = $first_name .' '. $last_name;
			}

			return $retval;
		}

		return FALSE;
	}

	//Caller ID numbers can come in in all sorts of forms:
	// 2505551234
	// 12505551234
	// +12505551234
	// (250) 555-1234
	//Parse out just the digits, and use only the last 10 digits.
	//Currently this will not support international numbers
	static function parseCallerID( $number ) {
		$validator = new Validator();

		$retval = substr( $validator->stripNonNumeric( $number ), -10, 10 );

		return $retval;
	}

	static function generateCopyName( $name, $strict = FALSE ) {
		$name = str_replace( ('Copy of'), '', $name );

		if ( $strict === TRUE ) {
			return ('Copy of').' '. $name;
		} else {
			return ('Copy of').' '. $name .' ['. rand(1,99) .']';
		}
	}

	static function generateShareName( $from, $name, $strict = FALSE ) {
		if ( $strict === TRUE ) {
			return $name .' ('. ('Shared by').': '. $from .')';
		} else {
			return $name .' ('. ('Shared by').': '. $from .') ['. rand(1,99) .']';
		}
	}

	/** Delete all files in directory
	* @param $path directory to clean
	* @param $recursive delete files in subdirs
	* @param $delDirs delete subdirs
	* @param $delRoot delete root directory
	* @access public
	* @return success
	*/
	static function cleanDir( $path, $recursive = FALSE, $del_dirs = FALSE, $del_root = FALSE ) {
		$result = TRUE;

		if( !$dir = @dir($path) ) {
			return FALSE;
		}

		while( $file = $dir->read() ) {
			if( $file === '.' OR $file === '..' ) {
				continue;
			}

			$full = $dir->path.DIRECTORY_SEPARATOR.$file;
			if ( is_dir($full) AND $recursive == TRUE ) {
				$result = self::cleanDir( $full, $recursive, $del_dirs, $del_dirs );
			} elseif( is_file($full) ) {
				$result = unlink($full);
				//Debug::Text('Deleting: '. $full , __FILE__, __LINE__, __METHOD__, 10);
			}

		}
		$dir->close();

		if ( $del_root == TRUE ) {
			//Debug::Text('Deleting Dir: '. $dir->path , __FILE__, __LINE__, __METHOD__, 10);
			$result = rmdir($dir->path);
		}

		return $result;
	}

	static function getFileList( $start_dir, $regex_filter = NULL, $recurse = FALSE ) {
		$files = array();
		if ( is_dir($start_dir) AND is_readable( $start_dir ) ) {
			$fh = opendir($start_dir);
			while ( ($file = readdir($fh)) !== FALSE ) {
				# loop through the files, skipping . and .., and recursing if necessary
				if ( strcmp($file, '.') == 0 OR strcmp($file, '..' ) == 0 ) {
					continue;
				}

				$filepath = $start_dir . DIRECTORY_SEPARATOR . $file;
				if ( is_dir($filepath) AND $recurse == TRUE ) {
					Debug::Text(' Recursing into dir: '. $filepath , __FILE__, __LINE__, __METHOD__, 10);

					$tmp_files = self::getFileList($filepath, $regex_filter, TRUE );
					if ( $tmp_files != FALSE AND is_array($tmp_files) ) {
						$files = array_merge( $files, $tmp_files );
					}
					unset($tmp_files);
				} elseif ( !is_dir( $filepath ) ) {
					if ( $regex_filter == '*' OR preg_match( '/'.$regex_filter.'/i', $file) == 1 ) {
						//Debug::Text(' Match: Dir: '. $start_dir .' File: '. $filepath , __FILE__, __LINE__, __METHOD__, 10);
						if ( is_readable($filepath) ) {
							array_push($files, $filepath);
						} else {
							Debug::Text(' Matching file is not read/writable: '. $filepath , __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						//Debug::Text(' NO Match: Dir: '. $start_dir .' File: '. $filepath , __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
			closedir($fh);
			sort($files);
		} else {
			# false if the function was called with an invalid non-directory argument
			$files = FALSE;
		}

		//Debug::Arr( $files, 'Matching files: ', __FILE__, __LINE__, __METHOD__, 10);
		return $files;
	}

	static function convertObjectToArray( $obj ) {
		if ( is_object($obj) ) {
			$obj = get_object_vars($obj);
		}

		if ( is_array($obj) ) {
			return array_map( array( 'Misc', __FUNCTION__) , $obj );
		} else {
			return $obj;
		}
	}

	static function getSystemLoad() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$loadavg_file = '/proc/loadavg';
			if ( file_exists( $loadavg_file ) AND is_readable( $loadavg_file ) ) {
				$buffer = '0 0 0';
				$buffer = file_get_contents( $loadavg_file );
				$load = explode(' ',$buffer);

				$retval = max((float)$load[0], (float)$load[1], (float)$load[2]);
				Debug::text(' Load Average: '. $retval , __FILE__, __LINE__, __METHOD__,10);

				return $retval;
			}
		}

		return 0;
	}

	static function isSystemLoadValid() {
		global $config_vars;

		if ( !isset($config_vars['other']['max_cron_system_load']) ) {
			$config_vars['other']['max_cron_system_load'] = 9999;
		}

		$system_load = Misc::getSystemLoad();
		if ( isset($config_vars['other']['max_cron_system_load']) AND $system_load <= $config_vars['other']['max_cron_system_load'] ) {
			Debug::text(' Load average within valid limits: Current: '. $system_load .' Max: '. $config_vars['other']['max_cron_system_load'], __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}

		Debug::text(' Load average NOT within valid limits: Current: '. $system_load .' Max: '. $config_vars['other']['max_cron_system_load'], __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
	}

	static function sendSystemMail( $subject, $body ) {
		if ( $subject == '' OR $body == '' ) {
			return FALSE;
		}

		//Email notification.
		$cc = array('admin@'.Misc::getHostName(FALSE) );

		//Get admin contacts for primary company.
		$clf = new CompanyListFactory();
		$clf->getById( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() > 0 ) {
			$c_obj = $clf->getCurrent();
			$admin_user_obj = $c_obj->getUserObject( $c_obj->getAdminContact() );
			if ( is_object( $admin_user_obj ) ) {
				$cc[] = $admin_user_obj->getWorkEmail();
			}

			$support_user_obj = $c_obj->getUserObject( $c_obj->getSupportContact() );
			if ( is_object($support_user_obj) ) {
				$cc[] = $support_user_obj->getWorkEmail();
			}

			$cc = implode(',', array_unique($cc) );
		}
		Debug::Text('CC: '. $cc , __FILE__, __LINE__, __METHOD__, 10);

		unset($clf, $c_obj, $admin_user_obj, $support_user_obj );

		$to = 'root@'.Misc::getHostName(FALSE);
		$from = APPLICATION_NAME.'@'.Misc::getHostName( FALSE );

		$headers = array(
							'From'    => $from,
							'Subject' => $subject,
							'cc'	  => $cc,
							'Reply-To' => $to,
							'Return-Path' => $to,
							'Errors-To' => $to,
						 );

		$mail = new TTMail();
		$mail->setTo( $to );
		$mail->setHeaders( $headers );
		$mail->setBody( $body );
		$retval = $mail->Send();

		return $retval;
	}

        
                
        //Checks refer to help mitigate CSRF attacks.
	static function checkValidReferer( $referer = FALSE ) {
		global $config_vars;
		
		if ( PRODUCTION == TRUE AND isset($config_vars['other']['enable_csrf_validation']) AND $config_vars['other']['enable_csrf_validation'] == TRUE ) {
			if ( $referer == FALSE ) {
				if ( isset($_SERVER['HTTP_ORIGIN']) AND $_SERVER['HTTP_ORIGIN'] != '' ) {
					//IE9 doesn't send this, but if it exists use it instead as its likely more trustworthy.
					//Debug::Text( 'Using Referer from Origin header...', __FILE__, __LINE__, __METHOD__, 10);
					$referer = $_SERVER['HTTP_ORIGIN'];
					if ( $referer == 'file://' ) { //Mobile App and some browsers can send the origin as: file://
						return TRUE;
					}
				} elseif ( isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER'] != '' ) {
					$referer = $_SERVER['HTTP_REFERER'];
				} else {
					$referer = '';
				}
			}

			//Debug::Text( 'Raw Referer: '. $referer, __FILE__, __LINE__, __METHOD__, 10);
			$referer = parse_url( $referer, PHP_URL_HOST );

			//Use HTTP_HOST rather than getHostName() as the same site can be referenced with multiple different host names
			//Especially considering on-site installs that default to 'localhost'
			//If deployment ondemand is set, then we assume SERVER_NAME is correct and revert to using that instead of HTTP_HOST which has potential to be forged.
			//Apache's UseCanonicalName On configuration directive can help ensure the SERVER_NAME is always correct and not masked.
			if ( DEPLOYMENT_ON_DEMAND == FALSE AND isset( $_SERVER['HTTP_HOST'] ) ) {
				$host_name = $_SERVER['HTTP_HOST'];
			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
				$host_name = $_SERVER['SERVER_NAME'];
			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
				$host_name = $_SERVER['HOSTNAME'];
			} else {
				$host_name = '';
			}
			$host_name = ( $host_name != '' ) ? parse_url( 'http://'.$host_name, PHP_URL_HOST ) : ''; //Need to add 'http://' so parse_url() can strip it off again.
			//Debug::Text( 'Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);

			if ( $referer == $host_name OR $host_name == '' ) {
				return TRUE;
			}

			Debug::Text( 'CSRF check failed... Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		return TRUE;
	}
	

        
	static function disableCaching( $email_notification = TRUE ) {
		//In case the cache directory does not exist, disabling caching can prevent errors from occurring or punches to be missed.
		//So this should be enabled even for ON-DEMAND services just in case.
		if ( PRODUCTION == TRUE ) {
			//Disable caching to prevent stale cache data from being read, and further cache errors.
			$install_obj = new Install();
			$tmp_config_vars['cache']['enable'] = FALSE;
			$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
			unset($install_obj, $tmp_config_vars);

			if ( $email_notification == TRUE ) {
				if ( $write_config_result == TRUE ) {
					$subject = APPLICATION_NAME. ' - Error!';
					$body = 'ERROR writing cache file, likely due to incorrect operating system permissions, disabling caching to prevent data corruption. This may result in '. APPLICATION_NAME .' performing slowly.'."\n\n";
					$body .= Debug::getOutput();
				} else {
					$subject = APPLICATION_NAME. ' - Error!';
					$body = 'ERROR writing config file, likely due to incorrect operating system permissions conflicts. Please correction permissions so '. APPLICATION_NAME .' can operate correctly.'."\n\n";
					$body .= Debug::getOutput();
				}
				return self::sendSystemMail( $subject, $body );
			}

			return TRUE;
		}

		return FALSE;
	}

	static function getMapURL( $address1, $address2, $city, $province, $postal_code, $country, $service = 'google', $service_params = array() ) {
		if ( $address1 == '' AND $address2 == '' ) {
			return FALSE;
		}

		$url = NULL;

		//Expand the country code to the full country name?
		if ( strlen($country) == 2 ) {
			$cf = new CompanyFactory();

			$long_country = Option::getByKey($country, $cf->getOptions('country') );
			if ( $long_country != '' ) {
				$country = $long_country;
			}
		}

		if ( $service == 'google' ) {
			$base_url = 'maps.google.com/?z=16&q=';
			$url = $base_url. urlencode($address1.' '. $city .' '. $province .' '. $postal_code .' '. $country);
		}

		if ( $url != '' ) {
			return 'http://'.$url;
		}

		return FALSE;
	}

	static function isEmail( $email, $check_dns = TRUE, $error_level = TRUE ) {
		if ( !function_exists('is_email') ) {
			require_once(Environment::getBasePath().'/classes/misc/is_email.php');
		}

		$result = is_email( $email, $check_dns, $error_level );
		if ( $result === ISEMAIL_VALID ) {
			return TRUE;
		} else {
			Debug::Text('Result Code: '. $result, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	static function getPasswordStrength( $password ) {
		if ( strlen( $password ) == 0 ) {
			return 1;
		}

		$strength = 0;

		//get the length of the password
		$length = strlen($password);

		//check if password is not all lower case
		if ( strtolower($password) != $password ) {
			$strength += 1;
		}

		//check if password is not all upper case
		if ( strtoupper($password) == $password) {
			$strength += 1;
		}

		//check string length is 8-15 chars
		if ( $length >= 6 && $length <= 9 ) {
			$strength += 1;
		}

		//check if lenth is 16-35 chars
		if ( $length >= 10 && $length <= 15 ) {
			$strength += 2;
		}

		//check if length greater than 35 chars
		if ( $length > 15 ) {
			$strength += 3;
		}

		//get the numbers in the password
		preg_match_all('/[0-9]/', $password, $numbers);
		$strength += count($numbers[0]) * 2;

		//check for special chars
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
		$strength += sizeof($specialchars[0]) * 3;

		//get the number of unique chars
		$chars = str_split($password);
		$num_unique_chars = sizeof( array_unique($chars) );
		$strength += $num_unique_chars * 2;

		//strength is a number 1-10;
		$strength = $strength > 99 ? 99 : $strength;
		$strength = floor($strength / 10 + 1);

		return $strength;
	}

	static function isSSL($check = false) {
		if ($check === true) {
			// Check if HTTPS is on or if the request is forwarded as HTTPS (e.g., behind a proxy)
			if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
				return true;
			}
	
			// Check for forwarded protocol (common in proxy setups)
			if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
				return true;
			}
	
			// Check for forwarded SSL (less common)
			if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
				return true;
			}
	
			// Check for server port (if HTTPS is running on a non-standard port)
			if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
				return true;
			}
		}
	
		return false;
	}
}
?>