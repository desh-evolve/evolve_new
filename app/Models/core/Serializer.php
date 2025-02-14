<?php

//Use this class to serializer arrays in PHP, XML, and JSON formats.
class Serializer {
	protected $available_formats = array('PHP','XML','JSON');
	protected $format = NULL;

	protected $simple_xml_obj = NULL;

	function __construct( $format = 'XML' ) {
		$format = strtoupper($format);

		if ( in_array($format, $this->available_formats) == TRUE ) {
			$this->format = $format;
		}

		return TRUE;
	}

	function PHPSerialize( $data ) {
		return serialize( $data );
	}
	function PHPDeSerialize( $data ) {
		return deserialize( $data );
	}

	function JSONSerialize( $data ) {
		return json_encode( $data );
	}
	function JSONDeSerialize( $data ) {
		return json_decode( $data );
	}

	function extractXML($xml) {
		if (! ( $xml->children() ) ) {
			return (string)$xml;
		}

		foreach ( $xml->children() as $child ) {
			$name = $child->getName();
			if ( count($xml->$name) == 1 ) {
				$element[$name] = $this->extractXML($child);
			} else {
				$element[$name][] = $this->extractXML($child);
			}
		}

		return $element;
	}
	function XMLArrayWalkCallBack( &$value, $key, $tmp_xml ) {
		$tmp_xml->addChild( $key, $value );
	}
	function XMLSerialize( $data ) {
		if ( is_array( $data ) ) {

			//The first level should be the class name as a key.
			/*
			  //Example array:
			array
			  'UserFactory' =>
				array
				  0 =>
					array
					  'id' => string '6217' (length=4)
					  'company_id' => string '1064' (length=4)
			*/
			foreach( $data as $class => $objects ) {
				$this->simple_xml_obj = new SimpleXMLElement('<timetrex></timetrex>');

				foreach( $objects as $key => $value ) {
					$tmp_xml = $this->simple_xml_obj->addChild( $class, '' );

					array_walk_recursive( $value, array( $this, 'XMLArrayWalkCallBack' ), $tmp_xml );
				}
			}
		}

		$retval = $this->simple_xml_obj->asXML();
		unset($this->simple_xml_obj);

		return $retval;
	}

	function XMLDeSerialize( $data ) {
		$xml = simplexml_load_string( $data );
		if ( $xml ) {
			return $this->extractXML( $xml );
		}
	}

	function serialize( $data ) {
		$function = $this->format.'Serialize';

		return $this->$function($data);
	}

	function deserialize( $data ) {
		$function = $this->format.'DeSerialize';

		return $this->$function($data);
	}
}
?>
