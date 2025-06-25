<?php

namespace App\Models\Core;

use TCPDF;

class TTPDF extends TCPDF {
	protected function _freadint($f) {
		//Read a 4-byte integer from file
		$a=unpack('Ni',fread($f,4));

		//Fixed bug in PHP v5.2.1 and less where it is returning a huge negative number.
		//See: http://ca3.php.net/manual/en/function.unpack.php
		//If you are trying to make unpack 'N' work with unsigned long on 64 bit machines, you should take a look to this bug:
		//http://bugs.php.net/bug.php?id=40543
		$b = sprintf("%b", $a['i']); // binary representation
		if(strlen($b) == 64){
			$new = substr($b, 33);
			$a['i'] = bindec($new);
		}
		return $a['i'];
	}

	function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache); //Make sure TCPDF constructor is called with all the arguments
		$this->setFontSubsetting(FALSE); //Makes PDFs larger, but severly slows down TCPDF if enabled. (+6 seconds per PDF)

		return TRUE;
	}

	//TCPDF oddly enough defines standard header/footers, instead of disabling them
	//in every script, just override them as blank here.
	function header() {
		return TRUE;
	}
	function footer() {
		return TRUE;
	}
}

?>
