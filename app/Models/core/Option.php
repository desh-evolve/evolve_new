<?php

namespace App\Models\Core;

class Option {
	static function getByKey($key, $options, $false = FALSE ) {
		if ( isset($options[$key]) ){
			//Debug::text('Returning Value: '. $options[$key] , __FILE__, __LINE__, __METHOD__, 9);

			return $options[$key];
		}

		return $false;
		//return FALSE;
	}

	static function getByValue($value, $options, $value_is_translated = TRUE ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//       BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == TRUE ) {
			$value = ( $value );
		}
		if ( is_array( $value ) ) {
			return FALSE;
		}

		if ( !is_array( $options ) ) {
			return FALSE;
		}

		$flipped_options = array_flip($options);

		if ( isset($flipped_options[$value]) ){
			//Debug::text('Returning Key: '. $flipped_options[$value] , __FILE__, __LINE__, __METHOD__, 9);

			return $flipped_options[$value];
		}

		return FALSE;
	}

	static function getByFuzzyValue($value, $options, $value_is_translated = TRUE ) {
		// I18n: Calling gettext on the value here enables a match with the translated value in the relevant factory.
		//       BUT... such string comparisons are messy and we really should be using getByKey for most everything.
		//		 Exceptions can be made by passing false for $value_is_translated.
		if ( $value_is_translated == TRUE ) {
			$value = ( $value );
		}
		if ( is_array( $value ) ) {
			return FALSE;
		}

		if ( !is_array( $options ) ) {
			return FALSE;
		}

		$retarr = Misc::findClosestMatch( $value, $options, 10, FALSE );
		Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__,10);

		/*
		//Convert SQL search value ie: 'test%test%' to a regular expression.
		$value = str_replace('%', '.*', $value);

		foreach( $options as $key => $option_value ) {
			if ( preg_match('/^'.$value.'$/i', $option_value) ) {
				$retarr[] = $key;
			}
		}
		*/

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	//Takes $needles as an array, loops through them returning matching
	//keys => value pairs from haystack
	//Useful for filtering results to a select box, like status.
	static function getByArray($needles, $haystack) {

		if (!is_array($needles) ) {
			$needles = array($needles);
		}

		$needles = array_unique($needles);

		foreach($needles as $needle) {
			if ( isset($haystack[$needle]) ) {
				$retval[$needle] = $haystack[$needle];
			}
		}

		if ( isset($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	static function getArrayByBitMask( $bitmask, $options ) {
		$bitmask = (int)$bitmask;

		if ( is_numeric($bitmask) AND is_array($options) ) {
			foreach( $options as $key => $value ) {
				//Debug::Text('Checking Bitmask: '. $bitmask .' mod '. $key .' != 0', __FILE__, __LINE__, __METHOD__,10);
				if ( ($bitmask & (int)$key) !== 0 ) {
					//Debug::Text('Found Bit: '. $key, __FILE__, __LINE__, __METHOD__,10);
					$retarr[] = $key;
				}
			}
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	static function getBitMaskByArray( $keys, $options ) {
		$retval = 0;
		if ( is_array($keys) AND is_array($options) ) {
			foreach( $keys as $key ) {
				if ( isset($options[$key]) ) {
					$retval |= $key;
				} else {
					Debug::Text('Key is not a valid bitmask int: '. $key, __FILE__, __LINE__, __METHOD__,10);
				}
			}
		}

		return $retval;
	}
}
?>
