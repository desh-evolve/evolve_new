<?php

namespace App\Models\Cron;

use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\TTDate;
use App\Models\Core\TTi18n;

class Cron {

	static protected $limits = array(
							'minute' => array('min' => 0, 'max' => 59 ),
							'hour' => array('min' => 0, 'max' => 23 ),
							'day_of_month' => array('min' => 1, 'max' => 31 ),
							'month' => array('min' => 1, 'max' => 12 ),
							'day_of_week' => array('min' => 0, 'max' => 7 ),
					);

	static function getOptions( $name, $interval = 1 ) {
		$all_array_option = array( '*' => ('-- All --') );

		$retval = FALSE;
		switch ( $name ) {
			case 'minute':
				for( $i=0; $i <= 59; $i+=$interval ) {
					$retval[$i] = $i;
				}
				$retval = Misc::prependArray( $all_array_option, $retval );
				break;
			case 'hour':
				for( $i=0; $i <= 23; $i+=$interval ) {
					$retval[$i] = $i;
				}
				$retval = Misc::prependArray( $all_array_option, $retval );
				break;
			case 'day_of_month':
				$retval = Misc::prependArray( $all_array_option, TTDate::getDayOfMonthArray() );
				break;
			case 'month':
				$retval = Misc::prependArray( $all_array_option, TTDate::getMonthOfYearArray() );
				break;
			case 'day_of_week':
				$retval = Misc::prependArray( $all_array_option, TTDate::getDayOfWeekArray() );
				break;
		}

		return $retval;
	}

	static function isValidLimit( $value_arr, $type ) {
		if ( isset(self::$limits[$type]) ) {
			$limit_arr = self::$limits[$type];
		} else {
			Debug::text('Type is invalid: '. $type, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( is_array($value_arr) AND is_array($limit_arr) ) {
			//Debug::Arr($value_arr, 'Value Arr: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($limit_arr, 'Limit Arr: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach($value_arr as $value ) {
				if ( $value == '*' ) {
					$retval = TRUE;
				} else {
					if ( $value >= $limit_arr['min'] AND $value <= $limit_arr['max'] ) {
						$retval = TRUE;
					} else {
						return FALSE;
					}
				}
			}

			return $retval;
		}

		return FALSE;
	}

	static function arrayToScheduleString( $arr, $type ) {
		if ( !is_array($arr) ) {
			if ( $arr == '' ) {
				$arr = '*';
			}
			$arr = array($arr);
		}

		if ( is_array($arr) ) {
			sort($arr);
			$retval = implode( ',', array_unique($arr) );

			return $retval;
		}

		return FALSE;
	}

	//Parses any column into a complete list of entries.
	//ie: converts: 	0-59 to an array of: 0,1,2,3,4,5,6,...
	//					0-2,16,18 to array of 0,1,2,16,18
	//					*/2 to array of 0,2,4,6,8,...
	static function parseScheduleString( $str, $type ) {
		if ( $str == '' ) {
			$str = '*';
		}

		$split_str = explode(',', $str);

		if ( count($split_str) == 0 ) {
			//Debug::text('Schedule String DOES NOT have multiple commas: '. count($split_str), __FILE__, __LINE__, __METHOD__, 10);
			$split_str = array($split_str);
		} else {
			//Debug::text('Schedule String has multiple commas: '. count($split_str), __FILE__, __LINE__, __METHOD__, 10);
		}

		$retarr = array();
		$limit_options = self::$limits;
		foreach( $split_str as $str_atom ) {
			if ( strpos($str_atom, '-') !== FALSE ) {
				//Debug::text('Schedule atom has basic range: '. $str_atom, __FILE__, __LINE__, __METHOD__, 10);
				//Found basic range
				//get Min/Max of range
				$str_atom_range = explode('-', $str_atom);

				$retarr = array_merge( $retarr, range($str_atom_range[0], $str_atom_range[1]) );
				unset($str_atom_range);
			} elseif ( strpos($str_atom, '/') !== FALSE ) {
				//Debug::text('Schedule atom has advanced range: '. $str_atom, __FILE__, __LINE__, __METHOD__, 10);
				//Found basic range
				//get Min/Max of range
				$str_atom_range = explode('/', $str_atom);

				$retarr = array_merge( $retarr, range($limit_options[$type]['min'],$limit_options[$type]['max'], $str_atom_range[1]) );
				unset($str_atom_range);
			} else {
				//No Range found
				//Debug::text('Schedule atom does not have range: '. $str_atom, __FILE__, __LINE__, __METHOD__, 10);

				if ( trim($str_atom) == '*' ) {
					//Debug::text('Found Full Range!: '. $str_atom, __FILE__, __LINE__, __METHOD__, 10);
					$retarr = array_merge( $retarr, range($limit_options[$type]['min'],$limit_options[$type]['max']) );
				} else {
					//Debug::text('Singleton: '. $str_atom, __FILE__, __LINE__, __METHOD__, 10);
					$retarr[] = (int)$str_atom;
				}
			}
		}

		rsort($retarr);

		//Debug::Arr($retarr, 'Final Array: ', __FILE__, __LINE__, __METHOD__, 10);
		return array_unique($retarr);
	}

	static function getNextScheduleDate( $min_col, $hour_col, $dom_col, $month_col, $dow_col, $epoch = NULL ) {
		if ( $epoch == '' ) {
			$epoch = 0;
		}

		$month_arr = self::parseScheduleString( $month_col, 'month' );
		$day_of_month_arr = self::parseScheduleString( $dom_col, 'day_of_month' );
		$day_of_week_arr = self::parseScheduleString( $dow_col, 'day_of_week' );
		$hour_arr = self::parseScheduleString( $hour_col, 'hour' );
		$minute_arr = self::parseScheduleString( $min_col, 'minute' );

		$retval = $epoch;
		$i=0;
		while ( $i < 500 ) { //Prevent infinite loop
			$date_arr = getdate( $retval );
			$i++;

			//Order from minute to month, least granular to most granular.
			if ( !in_array( $date_arr['minutes'], $minute_arr ) ) {
				$retval = TTDate::incrementDate( $retval, 1, 'minute');
				//Debug::text(' Minute: Retval: '. TTDate::getDate('DATE+TIME', $retval) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);
				continue;
			}

			if ( !in_array( $date_arr['hours'], $hour_arr ) ) {
				$retval = TTDate::incrementDate( $retval, 1, 'hour');
				//Debug::text(' Hour: Retval: '. TTDate::getDate('DATE+TIME', $retval) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);
				continue;
			}

			if ( !in_array( $date_arr['mday'], $day_of_month_arr ) OR !in_array( $date_arr['wday'], $day_of_week_arr ) ) {
				$retval = TTDate::getBeginDayEpoch( TTDate::incrementDate( $retval, 1, 'day') );
				//Debug::text(' Day: Retval: '. TTDate::getDate('DATE+TIME', $retval) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);
				continue;
			}

			if ( !in_array( $date_arr['mon'], $month_arr ) ) {
				$retval = TTDate::getBeginDayEpoch( TTDate::incrementDate( $retval, 1, 'month') );
				//Debug::text(' Month: Retval: '. TTDate::getDate('DATE+TIME', $retval) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);
				continue;
			}
			//Debug::text(' None: Retval: '. TTDate::getDate('DATE+TIME', $retval) .' Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);

			//Halt the loop...
			break;
		}

		Debug::text(' Next Scheduled Date: '. TTDate::getDate('DATE+TIME', $retval) .' Based on Current Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);

		//Debug::text('  JOB is NOT SCHEDULED TO RUN YET!', __FILE__, __LINE__, __METHOD__,10);
		return $retval;

	}


	static function isScheduledToRun( $min_col, $hour_col, $dom_col, $month_col, $dow_col, $epoch = NULL, $last_run_date = NULL ) {
		//Debug::text('Checking if Cron Job is scheduled to run: '. self::getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $epoch == '' ) {
			//$epoch = time();
			$epoch = 0;
		}

		//Debug::text('Checking if Cron Job is scheduled to run: '. self::getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $last_run_date == '' ) {
			$last_run_date = 0;
		}

		$next_schedule_epoch = self::getNextScheduleDate( $min_col, $hour_col, $dom_col, $month_col, $dow_col, $last_run_date );
		if ( $next_schedule_epoch < $epoch ) {
			Debug::text('  JOB is SCHEDULED TO RUN NOW!', __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}

		Debug::text('  JOB is NOT scheduled to run right now...' , __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}
}
?>
