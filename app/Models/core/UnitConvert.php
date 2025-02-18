<?php

namespace App\Models\Core;
use Illuminate\Support\Facades\Log;

class UnitConvert {
	/*
		This class is used to convert units, ie:
		pounds (lbs) to grams (g)
		inches (in) to meters (m)
		miles (mi) to kiliometers (km)
	*/

	//Convert weight units to grams, first.
	//Convert dimension units to mm, first.
	//Handle square and cubic (exponent) calculations as well.
	static $units = array(
						// 1 Unit = X G
						'oz' => 28.349523125,
						'lb' => 453.59237,
						'lbs' => 453.59237,
						'g'  => 1,
						'kg' => 1000,

						//1 Unit = X MM
						'mm' => 1,
						'in' => 25.4,
						'cm' => 10,
						'ft' => 304.8,
						'm' => 1000,
					);

	//Only units in the same array can be converted to one another.
	static $valid_unit_groups = array(
									'g' => array('g','oz','lb','lbs','kg'),
									'mm' => array('mm','in','cm','ft','m')
									);

	static function convert( $src_unit, $dst_unit, $measurement, $exponent = 1 ) {
		$src_unit = strtolower($src_unit);
		$dst_unit = strtolower($dst_unit);

		if ( !isset(self::$units[$src_unit]) ) {
			return FALSE;
		}
		if ( !isset(self::$units[$dst_unit]) ) {
			return FALSE;
		}

		if (  $src_unit == $dst_unit ) {
			return $measurement;
		}

		//Make sure we can convert from one unit to another.
		$valid_conversion = FALSE;
		foreach( self::$valid_unit_groups as $base_unit => $valid_units ) {
			if ( in_array($src_unit, $valid_units) AND in_array($dst_unit, $valid_units) ) {
				//Valid conversion
				$valid_conversion = TRUE;
			}
		}

		if ( $valid_conversion == FALSE ) {
			return FALSE;
		}

		$base_measurement = pow( self::$units[$src_unit], $exponent) * $measurement;
		Debug::Text(' Base Measurement: '. $base_measurement, __FILE__, __LINE__, __METHOD__,10);
		if ( $base_measurement != 0 ) {
			$retval = (1 / pow(self::$units[$dst_unit], $exponent) ) * $base_measurement;

			return $retval;
		}

		return FALSE;
	}
}
?>