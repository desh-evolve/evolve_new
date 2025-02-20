<?php
namespace App\Models\Company;
use App\Models\Core\Factory;

class CompanyFactory extends Factory {
	protected $table = 'company';
	protected $pk_sequence_name = 'company_id_seq'; //PK Sequence name

	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-\.\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	var $user_default_obj = NULL;
	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('ACTIVE'),
										20 => TTi18n::gettext('HOLD'),
										30 => TTi18n::gettext('CANCELLED')
									);
				break;
			case 'product_edition':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$retval[10] = TTi18n::gettext('Standard');
					$retval[15] = TTi18n::gettext('Business');
					$retval[20] = TTi18n::gettext('Professional');
				} elseif ( getTTProductEdition() == TT_PRODUCT_BUSINESS ) {
					$retval[10] = TTi18n::gettext('Standard');
					$retval[15] = TTi18n::gettext('Business');
				} else {
					$retval[10] = TTi18n::gettext('Standard');
				}

				break;
			case 'country':
				$retval = array(
										'CA' => TTi18n::gettext('Canada'),
										'US' => TTi18n::gettext('United States'),
										'AF' => TTi18n::gettext('Afghanistan'),
										'AL' => TTi18n::gettext('Albania'),
										'DZ' => TTi18n::gettext('Algeria'),
										'AS' => TTi18n::gettext('American Samoa'),
										'AD' => TTi18n::gettext('Andorra'),
										'AO' => TTi18n::gettext('Angola'),
										'AI' => TTi18n::gettext('Anguilla'),
										'AQ' => TTi18n::gettext('Antarctica'),
										'AG' => TTi18n::gettext('Antigua and Barbuda'),
										'AR' => TTi18n::gettext('Argentina'),
										'AM' => TTi18n::gettext('Armenia'),
										'AW' => TTi18n::gettext('Aruba'),
										'AU' => TTi18n::gettext('Australia'),
										'AT' => TTi18n::gettext('Austria'),
										'AZ' => TTi18n::gettext('Azerbaijan'),
										'BS' => TTi18n::gettext('Bahamas'),
										'BH' => TTi18n::gettext('Bahrain'),
										'BD' => TTi18n::gettext('Bangladesh'),
										'BB' => TTi18n::gettext('Barbados'),
										'BY' => TTi18n::gettext('Belarus'),
										'BE' => TTi18n::gettext('Belgium'),
										'BZ' => TTi18n::gettext('Belize'),
										'BJ' => TTi18n::gettext('Benin'),
										'BM' => TTi18n::gettext('Bermuda'),
										'BT' => TTi18n::gettext('Bhutan'),
										'BO' => TTi18n::gettext('Bolivia'),
										'BA' => TTi18n::gettext('Bosnia and Herzegovina'),
										'BW' => TTi18n::gettext('Botswana'),
										'BV' => TTi18n::gettext('Bouvet Island'),
										'BR' => TTi18n::gettext('Brazil'),
										'IO' => TTi18n::gettext('British Indian Ocean Territory'),
										'BN' => TTi18n::gettext('Brunei Darussalam'),
										'BG' => TTi18n::gettext('Bulgaria'),
										'BF' => TTi18n::gettext('Burkina Faso'),
										'BI' => TTi18n::gettext('Burundi'),
										'KH' => TTi18n::gettext('Cambodia'),
										'CM' => TTi18n::gettext('Cameroon'),
										'CV' => TTi18n::gettext('Cape Verde'),
										'KY' => TTi18n::gettext('Cayman Islands'),
										'CF' => TTi18n::gettext('Central African Republic'),
										'TD' => TTi18n::gettext('Chad'),
										'CL' => TTi18n::gettext('Chile'),
										'CN' => TTi18n::gettext('China'),
										'CX' => TTi18n::gettext('Christmas Island'),
										'CC' => TTi18n::gettext('Cocos (Keeling) Islands'),
										'CO' => TTi18n::gettext('Colombia'),
										'KM' => TTi18n::gettext('Comoros'),
										'CG' => TTi18n::gettext('Congo'),
										'CD' => TTi18n::gettext('Congo, the Democratic Republic of'),
										'CK' => TTi18n::gettext('Cook Islands'),
										'CR' => TTi18n::gettext('Costa Rica'),
										'CI' => TTi18n::gettext('Cote D\'Ivoire'),
										'HR' => TTi18n::gettext('Croatia'),
										'CU' => TTi18n::gettext('Cuba'),
										'CY' => TTi18n::gettext('Cyprus'),
										'CZ' => TTi18n::gettext('Czech Republic'),
										'DK' => TTi18n::gettext('Denmark'),
										'DJ' => TTi18n::gettext('Djibouti'),
										'DM' => TTi18n::gettext('Dominica'),
										'DO' => TTi18n::gettext('Dominican Republic'),
										'EC' => TTi18n::gettext('Ecuador'),
										'EG' => TTi18n::gettext('Egypt'),
										'SV' => TTi18n::gettext('El Salvador'),
										'GQ' => TTi18n::gettext('Equatorial Guinea'),
										'ER' => TTi18n::gettext('Eritrea'),
										'EE' => TTi18n::gettext('Estonia'),
										'ET' => TTi18n::gettext('Ethiopia'),
										'FK' => TTi18n::gettext('Falkland Islands (Malvinas)'),
										'FO' => TTi18n::gettext('Faroe Islands'),
										'FJ' => TTi18n::gettext('Fiji'),
										'FI' => TTi18n::gettext('Finland'),
										'FR' => TTi18n::gettext('France'),
										'GF' => TTi18n::gettext('French Guiana'),
										'PF' => TTi18n::gettext('French Polynesia'),
										'TF' => TTi18n::gettext('French Southern Territories'),
										'GA' => TTi18n::gettext('Gabon'),
										'GM' => TTi18n::gettext('Gambia'),
										'GE' => TTi18n::gettext('Georgia'),
										'DE' => TTi18n::gettext('Germany'),
										'GH' => TTi18n::gettext('Ghana'),
										'GI' => TTi18n::gettext('Gibraltar'),
										'GR' => TTi18n::gettext('Greece'),
										'GL' => TTi18n::gettext('Greenland'),
										'GD' => TTi18n::gettext('Grenada'),
										'GP' => TTi18n::gettext('Guadeloupe'),
										'GU' => TTi18n::gettext('Guam'),
										'GT' => TTi18n::gettext('Guatemala'),
										'GN' => TTi18n::gettext('Guinea'),
										'GW' => TTi18n::gettext('Guinea-Bissau'),
										'GY' => TTi18n::gettext('Guyana'),
										'HT' => TTi18n::gettext('Haiti'),
										'HM' => TTi18n::gettext('Heard Island and Mcdonald Islands'),
										'VA' => TTi18n::gettext('Holy See (Vatican City State)'),
										'HN' => TTi18n::gettext('Honduras'),
										'HK' => TTi18n::gettext('Hong Kong'),
										'HU' => TTi18n::gettext('Hungary'),
										'IS' => TTi18n::gettext('Iceland'),
										'IN' => TTi18n::gettext('India'),
										'ID' => TTi18n::gettext('Indonesia'),
										'IR' => TTi18n::gettext('Iran, Islamic Republic of'),
										'IQ' => TTi18n::gettext('Iraq'),
										'IE' => TTi18n::gettext('Ireland'),
										'IL' => TTi18n::gettext('Israel'),
										'IT' => TTi18n::gettext('Italy'),
										'JM' => TTi18n::gettext('Jamaica'),
										'JP' => TTi18n::gettext('Japan'),
										'JO' => TTi18n::gettext('Jordan'),
										'KZ' => TTi18n::gettext('Kazakhstan'),
										'KE' => TTi18n::gettext('Kenya'),
										'KI' => TTi18n::gettext('Kiribati'),
										'KP' => TTi18n::gettext('Korea, Democratic People\'s Republic of'),
										'KR' => TTi18n::gettext('Korea, Republic of'),
										'KW' => TTi18n::gettext('Kuwait'),
										'KG' => TTi18n::gettext('Kyrgyzstan'),
										'LA' => TTi18n::gettext('Lao People\'s Democratic Republic'),
										'LV' => TTi18n::gettext('Latvia'),
										'LB' => TTi18n::gettext('Lebanon'),
										'LS' => TTi18n::gettext('Lesotho'),
										'LR' => TTi18n::gettext('Liberia'),
										'LY' => TTi18n::gettext('Libyan Arab Jamahiriya'),
										'LI' => TTi18n::gettext('Liechtenstein'),
										'LT' => TTi18n::gettext('Lithuania'),
										'LU' => TTi18n::gettext('Luxembourg'),
										'MO' => TTi18n::gettext('Macao'),
										'MK' => TTi18n::gettext('Macedonia, Former Yugoslav Republic of'),
										'MG' => TTi18n::gettext('Madagascar'),
										'MW' => TTi18n::gettext('Malawi'),
										'MY' => TTi18n::gettext('Malaysia'),
										'MV' => TTi18n::gettext('Maldives'),
										'ML' => TTi18n::gettext('Mali'),
										'MT' => TTi18n::gettext('Malta'),
										'MH' => TTi18n::gettext('Marshall Islands'),
										'MQ' => TTi18n::gettext('Martinique'),
										'MR' => TTi18n::gettext('Mauritania'),
										'MU' => TTi18n::gettext('Mauritius'),
										'YT' => TTi18n::gettext('Mayotte'),
										'MX' => TTi18n::gettext('Mexico'),
										'FM' => TTi18n::gettext('Micronesia, Federated States of'),
										'MD' => TTi18n::gettext('Moldova, Republic of'),
										'MC' => TTi18n::gettext('Monaco'),
										'MN' => TTi18n::gettext('Mongolia'),
										'MS' => TTi18n::gettext('Montserrat'),
										'MA' => TTi18n::gettext('Morocco'),
										'MZ' => TTi18n::gettext('Mozambique'),
										'MM' => TTi18n::gettext('Myanmar'),
										'NA' => TTi18n::gettext('Namibia'),
										'NR' => TTi18n::gettext('Nauru'),
										'NP' => TTi18n::gettext('Nepal'),
										'NL' => TTi18n::gettext('Netherlands'),
										'AN' => TTi18n::gettext('Netherlands Antilles'),
										'NC' => TTi18n::gettext('New Caledonia'),
										'NZ' => TTi18n::gettext('New Zealand'),
										'NI' => TTi18n::gettext('Nicaragua'),
										'NE' => TTi18n::gettext('Niger'),
										'NG' => TTi18n::gettext('Nigeria'),
										'NU' => TTi18n::gettext('Niue'),
										'NF' => TTi18n::gettext('Norfolk Island'),
										'MP' => TTi18n::gettext('Northern Mariana Islands'),
										'NO' => TTi18n::gettext('Norway'),
										'OM' => TTi18n::gettext('Oman'),
										'PK' => TTi18n::gettext('Pakistan'),
										'PW' => TTi18n::gettext('Palau'),
										'PS' => TTi18n::gettext('Palestinian Territory, Occupied'),
										'PA' => TTi18n::gettext('Panama'),
										'PG' => TTi18n::gettext('Papua New Guinea'),
										'PY' => TTi18n::gettext('Paraguay'),
										'PE' => TTi18n::gettext('Peru'),
										'PH' => TTi18n::gettext('Philippines'),
										'PN' => TTi18n::gettext('Pitcairn'),
										'PL' => TTi18n::gettext('Poland'),
										'PT' => TTi18n::gettext('Portugal'),
										'PR' => TTi18n::gettext('Puerto Rico'),
										'QA' => TTi18n::gettext('Qatar'),
										'RE' => TTi18n::gettext('Reunion'),
										'RO' => TTi18n::gettext('Romania'),
										'RU' => TTi18n::gettext('Russian Federation'),
										'RW' => TTi18n::gettext('Rwanda'),
										'SH' => TTi18n::gettext('Saint Helena'),
										'KN' => TTi18n::gettext('Saint Kitts and Nevis'),
										'LC' => TTi18n::gettext('Saint Lucia'),
										'PM' => TTi18n::gettext('Saint Pierre and Miquelon'),
										'VC' => TTi18n::gettext('Saint Vincent, Grenadines'),
										'WS' => TTi18n::gettext('Samoa'),
										'SM' => TTi18n::gettext('San Marino'),
										'ST' => TTi18n::gettext('Sao Tome and Principe'),
										'SA' => TTi18n::gettext('Saudi Arabia'),
										'SN' => TTi18n::gettext('Senegal'),
										'CS' => TTi18n::gettext('Serbia and Montenegro'),
										'SC' => TTi18n::gettext('Seychelles'),
										'SL' => TTi18n::gettext('Sierra Leone'),
										'SG' => TTi18n::gettext('Singapore'),
										'SK' => TTi18n::gettext('Slovakia'),
										'SI' => TTi18n::gettext('Slovenia'),
										'SB' => TTi18n::gettext('Solomon Islands'),
										'SO' => TTi18n::gettext('Somalia'),
										'ZA' => TTi18n::gettext('South Africa'),
										'GS' => TTi18n::gettext('South Georgia, South Sandwich Islands'),
										'ES' => TTi18n::gettext('Spain'),
										'LK' => TTi18n::gettext('Sri Lanka'),
										'SD' => TTi18n::gettext('Sudan'),
										'SR' => TTi18n::gettext('Suriname'),
										'SJ' => TTi18n::gettext('Svalbard and Jan Mayen'),
										'SZ' => TTi18n::gettext('Swaziland'),
										'SE' => TTi18n::gettext('Sweden'),
										'CH' => TTi18n::gettext('Switzerland'),
										'SY' => TTi18n::gettext('Syrian Arab Republic'),
										'TW' => TTi18n::gettext('Taiwan'),
										'TJ' => TTi18n::gettext('Tajikistan'),
										'TZ' => TTi18n::gettext('Tanzania, United Republic of'),
										'TH' => TTi18n::gettext('Thailand'),
										'TL' => TTi18n::gettext('Timor-Leste'),
										'TG' => TTi18n::gettext('Togo'),
										'TK' => TTi18n::gettext('Tokelau'),
										'TO' => TTi18n::gettext('Tonga'),
										'TT' => TTi18n::gettext('Trinidad and Tobago'),
										'TN' => TTi18n::gettext('Tunisia'),
										'TR' => TTi18n::gettext('Turkey'),
										'TM' => TTi18n::gettext('Turkmenistan'),
										'TC' => TTi18n::gettext('Turks and Caicos Islands'),
										'TV' => TTi18n::gettext('Tuvalu'),
										'UG' => TTi18n::gettext('Uganda'),
										'UA' => TTi18n::gettext('Ukraine'),
										'AE' => TTi18n::gettext('United Arab Emirates'),
										'GB' => TTi18n::gettext('United Kingdom'),
										'UM' => TTi18n::gettext('United States Minor Outlying Islands'),
										'UY' => TTi18n::gettext('Uruguay'),
										'UZ' => TTi18n::gettext('Uzbekistan'),
										'VU' => TTi18n::gettext('Vanuatu'),
										'VE' => TTi18n::gettext('Venezuela'),
										'VN' => TTi18n::gettext('Viet Nam'),
										'VG' => TTi18n::gettext('Virgin Islands, British'),
										'VI' => TTi18n::gettext('Virgin Islands, U.s.'),
										'WF' => TTi18n::gettext('Wallis and Futuna'),
										'EH' => TTi18n::gettext('Western Sahara'),
										'YE' => TTi18n::gettext('Yemen'),
										'ZM' => TTi18n::gettext('Zambia'),
										'ZW' => TTi18n::gettext('Zimbabwe'),
									);
				break;
			case 'province':
				$retval = array(
										'CA' => array(
														'AB' => TTi18n::gettext('Alberta'),
														'BC' => TTi18n::gettext('British Columbia'),
														'SK' => TTi18n::gettext('Saskatchewan'),
														'MB' => TTi18n::gettext('Manitoba'),
														'QC' => TTi18n::gettext('Quebec'),
														'ON' => TTi18n::gettext('Ontario'),
														'NL' => TTi18n::gettext('NewFoundLand'),
														'NB' => TTi18n::gettext('New Brunswick'),
														'NS' => TTi18n::gettext('Nova Scotia'),
														'PE' => TTi18n::gettext('Prince Edward Island'),
														'NT' => TTi18n::gettext('Northwest Territories'),
														'YT' => TTi18n::gettext('Yukon'),
														'NU' => TTi18n::gettext('Nunavut')
														),
										'US' => array(
														'AL' => TTi18n::gettext('Alabama'),
														'AK' => TTi18n::gettext('Alaska'),
														'AZ' => TTi18n::gettext('Arizona'),
														'AR' => TTi18n::gettext('Arkansas'),
														'CA' => TTi18n::gettext('California'),
														'CO' => TTi18n::gettext('Colorado'),
														'CT' => TTi18n::gettext('Connecticut'),
														'DE' => TTi18n::gettext('Delaware'),
														'DC' => TTi18n::gettext('D.C.'),
														'FL' => TTi18n::gettext('Florida'),
														'GA' => TTi18n::gettext('Georgia'),
														'HI' => TTi18n::gettext('Hawaii'),
														'ID' => TTi18n::gettext('Idaho'),
														'IL' => TTi18n::gettext('Illinois'),
														'IN' => TTi18n::gettext('Indiana'),
														'IA' => TTi18n::gettext('Iowa'),
														'KS' => TTi18n::gettext('Kansas'),
														'KY' => TTi18n::gettext('Kentucky'),
														'LA' => TTi18n::gettext('Louisiana'),
														'ME' => TTi18n::gettext('Maine'),
														'MD' => TTi18n::gettext('Maryland'),
														'MA' => TTi18n::gettext('Massachusetts'),
														'MI' => TTi18n::gettext('Michigan'),
														'MN' => TTi18n::gettext('Minnesota'),
														'MS' => TTi18n::gettext('Mississippi'),
														'MO' => TTi18n::gettext('Missouri'),
														'MT' => TTi18n::gettext('Montana'),
														'NE' => TTi18n::gettext('Nebraska'),
														'NV' => TTi18n::gettext('Nevada'),
														'NH' => TTi18n::gettext('New Hampshire'),
														'NM' => TTi18n::gettext('New Mexico'),
														'NJ' => TTi18n::gettext('New Jersey'),
														'NY' => TTi18n::gettext('New York'),
														'NC' => TTi18n::gettext('North Carolina'),
														'ND' => TTi18n::gettext('North Dakota'),
														'OH' => TTi18n::gettext('Ohio'),
														'OK' => TTi18n::gettext('Oklahoma'),
														'OR' => TTi18n::gettext('Oregon'),
														'PA' => TTi18n::gettext('Pennsylvania'),
														'RI' => TTi18n::gettext('Rhode Island'),
														'SC' => TTi18n::gettext('South Carolina'),
														'SD' => TTi18n::gettext('South Dakota'),
														'TN' => TTi18n::gettext('Tennessee'),
														'TX' => TTi18n::gettext('Texas'),
														'UT' => TTi18n::gettext('Utah'),
														'VT' => TTi18n::gettext('Vermont'),
														'VA' => TTi18n::gettext('Virginia'),
														'WA' => TTi18n::gettext('Washington'),
														'WV' => TTi18n::gettext('West Virginia'),
														'WI' => TTi18n::gettext('Wisconsin'),
														'WY' => TTi18n::gettext('Wyoming')
														),
										//Use '00' for 0, as I think there is a bug in the
										//AJAX library that appends function text if its just
										//a integer.
										'AF' => array( '00' => '--'),
										'AL' => array( '00' => '--'),
										'DZ' => array( '00' => '--'),
										'AS' => array( '00' => '--'),
										'AD' => array( '00' => '--'),
										'AO' => array( '00' => '--'),
										'AI' => array( '00' => '--'),
										'AQ' => array( '00' => '--'),
										'AG' => array( '00' => '--'),
										'AR' => array( '00' => '--'),
										'AM' => array( '00' => '--'),
										'AW' => array( '00' => '--'),
										'AU' => array(
														'00' => '--',
														'ACT'	=> TTi18n::gettext('Australian Capital Territory'),
														'NSW'	=> TTi18n::gettext('New South Wales'),
														'NT'	=> TTi18n::gettext('Northern Territory'),
														'QLD'	=> TTi18n::gettext('Queensland'),
														'SA'	=> TTi18n::gettext('South Australia'),
														'TAS'	=> TTi18n::gettext('Tasmania'),
														'VIC'	=> TTi18n::gettext('Victoria'),
														'WA'	=> TTi18n::gettext('Western Australia'),
													),
										'AT' => array( '00' => '--'),
										'AZ' => array( '00' => '--'),
										'BS' => array( '00' => '--'),
										'BH' => array( '00' => '--'),
										'BD' => array( '00' => '--'),
										'BB' => array(
														'00' => '--',
														'M' => TTi18n::gettext('St. Michael'),
														'X' => TTi18n::gettext('Christ Church'),
														'G' => TTi18n::gettext('St. George'),
														'J' => TTi18n::gettext('St. John'),
														'P' => TTi18n::gettext('St. Philip'),
														'O' => TTi18n::gettext('St. Joseph'),
														'L' => TTi18n::gettext('St. Lucy'),
														'S' => TTi18n::gettext('St. James'),
														'T' => TTi18n::gettext('St. Thomas'),
														'A' => TTi18n::gettext('St. Andrew'),
														'E' => TTi18n::gettext('St. Peter')
													),
										'BY' => array( '00' => '--'),
										'BE' => array( '00' => '--'),
										'BZ' => array( '00' => '--'),
										'BJ' => array( '00' => '--'),
										'BM' => array( '00' => '--'),
										'BT' => array( '00' => '--'),
										'BO' => array( '00' => '--'),
										'BA' => array( '00' => '--'),
										'BW' => array( '00' => '--'),
										'BV' => array( '00' => '--'),
										'BR' => array( '00' => '--'),
										'IO' => array( '00' => '--'),
										'BN' => array( '00' => '--'),
										'BG' => array( '00' => '--'),
										'BF' => array( '00' => '--'),
										'BI' => array( '00' => '--'),
										'KH' => array( '00' => '--'),
										'CM' => array( '00' => '--'),
										'CV' => array( '00' => '--'),
										'KY' => array( '00' => '--'),
										'CF' => array( '00' => '--'),
										'TD' => array( '00' => '--'),
										'CL' => array( '00' => '--'),
										'CN' => array( '00' => '--'),
										'CX' => array( '00' => '--'),
										'CC' => array( '00' => '--'),
										'CO' => array(
														'00' => '--',
														'AM' => TTi18n::gettext('Amazonas'),
														'AN' => TTi18n::gettext('Antioquia'),
														'AR' => TTi18n::gettext('Arauca'),
														'AT' => TTi18n::gettext('Atlantico'),
														'BL' => TTi18n::gettext('Bolivar'),
														'BY' => TTi18n::gettext('Boyaca'),
														'CL' => TTi18n::gettext('Caldas'),
														'CQ' => TTi18n::gettext('Caqueta'),
														'CS' => TTi18n::gettext('Casanare'),
														'CA' => TTi18n::gettext('Cauca'),
														'CE' => TTi18n::gettext('Cesar'),
														'CH' => TTi18n::gettext('Choco'),
														'CO' => TTi18n::gettext('Cordoba'),
														'CU' => TTi18n::gettext('Cundinamarca'),
														'DC' => TTi18n::gettext('Distrito Capital'),
														'GN' => TTi18n::gettext('Guainia'),
														'GV' => TTi18n::gettext('Guaviare'),
														'HU' => TTi18n::gettext('Huila'),
														'LG' => TTi18n::gettext('La Guajira'),
														'MA' => TTi18n::gettext('Magdalena'),
														'ME' => TTi18n::gettext('Meta'),
														'NA' => TTi18n::gettext('Narino'),
														'NS' => TTi18n::gettext('Norte de Santander'),
														'PU' => TTi18n::gettext('Putumayo'),
														'QD' => TTi18n::gettext('Quindio'),
														'RI' => TTi18n::gettext('Risaralda'),
														'SA' => TTi18n::gettext('San Andres y Providencia'),
														'ST' => TTi18n::gettext('Santander'),
														'SU' => TTi18n::gettext('Sucre'),
														'TO' => TTi18n::gettext('Tolima'),
														'VC' => TTi18n::gettext('Valle del Cauca'),
														'VP' => TTi18n::gettext('Vaupes'),
														'VD' => TTi18n::gettext('Vichada'),
														),
										'KM' => array( '00' => '--'),
										'CG' => array( '00' => '--'),
										'CD' => array( '00' => '--'),
										'CK' => array( '00' => '--'),
										'CR' => array(
														'00' => '--',
														'AL' => TTi18n::gettext('Alajuela'),
														'CA' => TTi18n::gettext('Cartago'),
														'GU' => TTi18n::gettext('Guanacaste'),
														'HE' => TTi18n::gettext('Heredia'),
														'LI' => TTi18n::gettext('Limon'),
														'PU' => TTi18n::gettext('Puntarenas'),
														'SJ' => TTi18n::gettext('San Jose'),
														),
										'CI' => array( '00' => '--'),
										'HR' => array( '00' => '--'),
										'CU' => array( '00' => '--'),
										'CY' => array( '00' => '--'),
										'CZ' => array( '00' => '--'),
										'DK' => array( '00' => '--'),
										'DJ' => array( '00' => '--'),
										'DM' => array( '00' => '--'),
										'DO' => array( '00' => '--'),
										'EC' => array( '00' => '--'),
										'EG' => array( '00' => '--'),
										'SV' => array(
														'00' => '--',
														'AH' => TTi18n::gettext('Ahuachapan'),
														'CA' => TTi18n::gettext('Cabanas'),
														'CH' => TTi18n::gettext('Chalatenango'),
														'CU' => TTi18n::gettext('Cuscatlan'),
														'LI' => TTi18n::gettext('La Libertad'),
														'PA' => TTi18n::gettext('La Paz'),
														'UN' => TTi18n::gettext('La Union'),
														'MO' => TTi18n::gettext('Morazan'),
														'SM' => TTi18n::gettext('San Miguel'),
														'SS' => TTi18n::gettext('San Salvador'),
														'SA' => TTi18n::gettext('Santa Ana'),
														'SV' => TTi18n::gettext('San Vicente'),
														'SO' => TTi18n::gettext('Sonsonate'),
														'US' => TTi18n::gettext('Usulatan')
														),
										'GQ' => array( '00' => '--'),
										'ER' => array( '00' => '--'),
										'EE' => array( '00' => '--'),
										'ET' => array( '00' => '--'),
										'FK' => array( '00' => '--'),
										'FO' => array( '00' => '--'),
										'FJ' => array( '00' => '--'),
										'FI' => array( '00' => '--'),
										'FR' => array( '00' => '--'),
										'GF' => array( '00' => '--'),
										'PF' => array( '00' => '--'),
										'TF' => array( '00' => '--'),
										'GA' => array( '00' => '--'),
										'GM' => array( '00' => '--'),
										'GE' => array( '00' => '--'),
										'DE' => array( '00' => '--'),
										'GH' => array( '00' => '--'),
										'GI' => array( '00' => '--'),
										'GR' => array( '00' => '--'),
										'GL' => array( '00' => '--'),
										'GD' => array( '00' => '--'),
										'GP' => array( '00' => '--'),
										'GU' => array( '00' => '--'),
										'GT' => array(
														'00' => '--',
														'AV' => TTi18n::gettext('Alta Verapaz'),
														'BV' => TTi18n::gettext('Baja Verapaz'),
														'GT' => TTi18n::gettext('Chimaltenango'),
														'CQ' => TTi18n::gettext('Chiquimula'),
														'PR' => TTi18n::gettext('El Progreso'),
														'ES' => TTi18n::gettext('Escuintla'),
														'GU' => TTi18n::gettext('Guatemala'),
														'HU' => TTi18n::gettext('Huehuetenango'),
														'IZ' => TTi18n::gettext('Izaqbal'),
														'JA' => TTi18n::gettext('Jalapa'),
														'JU' => TTi18n::gettext('Jutiapa'),
														'PE' => TTi18n::gettext('Peten'),
														'QZ' => TTi18n::gettext('Quetzaltenango'),
														'QC' => TTi18n::gettext('Quiche'),
														'RE' => TTi18n::gettext('Retalhuleu'),
														'SA' => TTi18n::gettext('Sacatepequez'),
														'SM' => TTi18n::gettext('San Marcos'),
														'SR' => TTi18n::gettext('Santa Rosa'),
														'SO' => TTi18n::gettext('Solola'),
														'SU' => TTi18n::gettext('Suchitepequez'),
														'TO' => TTi18n::gettext('Totonicapan'),
														'ZA' => TTi18n::gettext('Zacapa')
														),
										'GN' => array( '00' => '--'),
										'GW' => array( '00' => '--'),
										'GY' => array( '00' => '--'),
										'HT' => array( '00' => '--'),
										'HM' => array( '00' => '--'),
										'VA' => array( '00' => '--'),
										'HN' => array(
														'00' => '--',
														'AT' => TTi18n::gettext('Atlantida'),
														'CH' => TTi18n::gettext('Choluteca'),
														'CL' => TTi18n::gettext('Colon'),
														'CM' => TTi18n::gettext('Comayagua'),
														'CP' => TTi18n::gettext('Copan'),
														'CR' => TTi18n::gettext('Cortes'),
														'EP' => TTi18n::gettext('El Paraiso'),
														'FM' => TTi18n::gettext('Francisco Morazan'),
														'GD' => TTi18n::gettext('Gracias a Dios'),
														'IN' => TTi18n::gettext('Intibuca'),
														'IB' => TTi18n::gettext('Islas de la Bahia'),
														'LP' => TTi18n::gettext('La Paz'),
														'LE' => TTi18n::gettext('Lempira'),
														'OC' => TTi18n::gettext('Ocotepeque'),
														'OL' => TTi18n::gettext('Olancho'),
														'SB' => TTi18n::gettext('Santa Barbara'),
														'VA' => TTi18n::gettext('Valle'),
														'YO' => TTi18n::gettext('Yoro'),
														),
										'HK' => array( '00' => '--'),
										'HU' => array( '00' => '--'),
										'IS' => array( '00' => '--'),
										'IN' => array(
														'00' => '--',
														'AN' => TTi18n::gettext('Andaman and Nicobar Islands'),
														'AP' => TTi18n::gettext('Andhra Pradesh'),
														'AR' => TTi18n::gettext('Arunachal Pradesh'),
														'AS' => TTi18n::gettext('Assam'),
														'BR' => TTi18n::gettext('Bihar'),
														'CH' => TTi18n::gettext('Chandigarh'),
														'CT' => TTi18n::gettext('Chhattisgarh'),
														'DN' => TTi18n::gettext('Dadra and Nagar Haveli'),
														'DD' => TTi18n::gettext('Daman and Diu'),
														'DL' => TTi18n::gettext('Delhi'),
														'GA' => TTi18n::gettext('Goa'),
														'GJ' => TTi18n::gettext('Gujarat'),
														'HR' => TTi18n::gettext('Haryana'),
														'HP' => TTi18n::gettext('Himachal Pradesh'),
														'JK' => TTi18n::gettext('Jammu and Kashmir'),
														'JH' => TTi18n::gettext('Jharkhand'),
														'KA' => TTi18n::gettext('Karnataka'),
														'KL' => TTi18n::gettext('Kerala'),
														'LD' => TTi18n::gettext('Lakshadweep'),
														'MP' => TTi18n::gettext('Madhya Pradesh'),
														'MH' => TTi18n::gettext('Maharashtra'),
														'MN' => TTi18n::gettext('Manipur'),
														'ML' => TTi18n::gettext('Meghalaya'),
														'MZ' => TTi18n::gettext('Mizoram'),
														'NL' => TTi18n::gettext('Nagaland'),
														'OR' => TTi18n::gettext('Orissa'),
														'PY' => TTi18n::gettext('Pondicherry'),
														'PB' => TTi18n::gettext('Punjab'),
														'RJ' => TTi18n::gettext('Rajasthan'),
														'SK' => TTi18n::gettext('Sikkim'),
														'TN' => TTi18n::gettext('Tamil Nadu'),
														'TR' => TTi18n::gettext('Tripura'),
														'UP' => TTi18n::gettext('Uttar Pradesh'),
														'UL' => TTi18n::gettext('Uttarakhand'),
														'WB' => TTi18n::gettext('West Bengal'),
														),
										'ID' => array(
														'00' => '--',
														'BA' => TTi18n::gettext('Bali'),
														'BB' => TTi18n::gettext('Bangka-Belitung'),
														'BT' => TTi18n::gettext('Banten'),
														'BE' => TTi18n::gettext('Bengkulu'),
														'JT' => TTi18n::gettext('Central Java'),
														'KT' => TTi18n::gettext('Central Kalimantan'),
														'ST' => TTi18n::gettext('Central Sulawesi'),
														'JI' => TTi18n::gettext('East Java'),
														'KI' => TTi18n::gettext('East Kalimantan'),
														'NT' => TTi18n::gettext('East Nusa Tenggara'),
														'GO' => TTi18n::gettext('Gorontalo'),
														'JA' => TTi18n::gettext('Jambi'),
														'JK' => TTi18n::gettext('Jakarta'),
														'LA' => TTi18n::gettext('Lampung'),
														'MA' => TTi18n::gettext('Maluku'),
														'MU' => TTi18n::gettext('North Maluku'),
														'SA' => TTi18n::gettext('North Sulawesi'),
														'SU' => TTi18n::gettext('North Sumatra'),
														'RI' => TTi18n::gettext('Riau'),
														'KR' => TTi18n::gettext('Riau Islands'),
														'SS' => TTi18n::gettext('South Sumatra'),
														'SN' => TTi18n::gettext('South Sulawesi'),
														'KS' => TTi18n::gettext('South Kalimantan'),
														'SG' => TTi18n::gettext('Southeast Sulawesi'),
														'JB' => TTi18n::gettext('West Java'),
														'KB' => TTi18n::gettext('West Kalimantan'),
														'NB' => TTi18n::gettext('West Nusa Tenggara'),
														'SR' => TTi18n::gettext('West Sulawesi'),
														'SB' => TTi18n::gettext('West Sumatra'),
														'YO' => TTi18n::gettext('Yogyakarta'),
														),
										'IR' => array( '00' => '--'),
										'IQ' => array( '00' => '--'),
										'IE' => array( '00' => '--'),
										'IL' => array( '00' => '--'),
										'IT' => array( '00' => '--'),
										'JM' => array( '00' => '--'),
										'JP' => array( '00' => '--'),
										'JO' => array( '00' => '--'),
										'KZ' => array( '00' => '--'),
										'KE' => array( '00' => '--'),
										'KI' => array( '00' => '--'),
										'KP' => array( '00' => '--'),
										'KR' => array( '00' => '--'),
										'KW' => array( '00' => '--'),
										'KG' => array( '00' => '--'),
										'LA' => array( '00' => '--'),
										'LV' => array( '00' => '--'),
										'LB' => array( '00' => '--'),
										'LS' => array( '00' => '--'),
										'LR' => array( '00' => '--'),
										'LY' => array( '00' => '--'),
										'LI' => array( '00' => '--'),
										'LT' => array( '00' => '--'),
										'LU' => array( '00' => '--'),
										'MO' => array( '00' => '--'),
										'MK' => array( '00' => '--'),
										'MG' => array( '00' => '--'),
										'MW' => array( '00' => '--'),
										'MY' => array( '00' => '--'),
										'MV' => array( '00' => '--'),
										'ML' => array( '00' => '--'),
										'MT' => array( '00' => '--'),
										'MH' => array( '00' => '--'),
										'MQ' => array( '00' => '--'),
										'MR' => array( '00' => '--'),
										'MU' => array( '00' => '--'),
										'YT' => array( '00' => '--'),
										'MX' => array(
														'00' => '--',
														'AG' => TTi18n::gettext('Aguascalientes'),
														'BN' => TTi18n::gettext('Baja California'),
														'BS' => TTi18n::gettext('Baja California Sur'),
														'CM' => TTi18n::gettext('Campeche'),
														'CP' => TTi18n::gettext('Chiapas'),
														'CP' => TTi18n::gettext('Chihuahua'),
														'CA' => TTi18n::gettext('Coahuila'),
														'CL' => TTi18n::gettext('Colima'),
														'DF' => TTi18n::gettext('Distrito Federal'),
														'DU' => TTi18n::gettext('Durango'),
														'GJ' => TTi18n::gettext('Guanajuato'),
														'GR' => TTi18n::gettext('Guerrero'),
														'HI' => TTi18n::gettext('Hidalgo'),
														'JA' => TTi18n::gettext('Jalisco'),
														'MX' => TTi18n::gettext('Mexico'),
														'MC' => TTi18n::gettext('Michoacan'),
														'MR' => TTi18n::gettext('Morelos'),
														'NA' => TTi18n::gettext('Niyarit'),
														'NL' => TTi18n::gettext('Nuevo Leon'),
														'OA' => TTi18n::gettext('Oaxaca'),
														'PU' => TTi18n::gettext('Puebla'),
														'QE' => TTi18n::gettext('Queretaro'),
														'QR' => TTi18n::gettext('Quintana Roo'),
														'SL' => TTi18n::gettext('San Luis Potosi'),
														'SI' => TTi18n::gettext('Sinaloa'),
														'SO' => TTi18n::gettext('Sonora'),
														'TB' => TTi18n::gettext('Tabasco'),
														'TM' => TTi18n::gettext('Tamaulipas'),
														'TL' => TTi18n::gettext('Tlaxcala'),
														'VE' => TTi18n::gettext('Veracruz-Llave'),
														'YU' => TTi18n::gettext('Yucatan'),
														'ZA' => TTi18n::gettext('Zacatecas'),
														),
										'FM' => array( '00' => '--'),
										'MD' => array( '00' => '--'),
										'MC' => array( '00' => '--'),
										'MN' => array( '00' => '--'),
										'MS' => array( '00' => '--'),
										'MA' => array( '00' => '--'),
										'MZ' => array( '00' => '--'),
										'MM' => array( '00' => '--'),
										'NA' => array( '00' => '--'),
										'NR' => array( '00' => '--'),
										'NP' => array( '00' => '--'),
										'NL' => array( '00' => '--'),
										'AN' => array( '00' => '--'),
										'NC' => array( '00' => '--'),
										'NZ' => array( '00' => '--'),
										'NI' => array(
														'00' => '--',
														'BO' => TTi18n::gettext('Boaco'),
														'CA' => TTi18n::gettext('Carazo'),
														'CI' => TTi18n::gettext('Chinandega'),
														'CO' => TTi18n::gettext('Chontales'),
														'ES' => TTi18n::gettext('Esteli'),
														'GR' => TTi18n::gettext('Granada'),
														'JI' => TTi18n::gettext('Jinotega'),
														'LE' => TTi18n::gettext('Leon'),
														'MD' => TTi18n::gettext('Madriz'),
														'MN' => TTi18n::gettext('Managua'),
														'MS' => TTi18n::gettext('Masaya'),
														'MT' => TTi18n::gettext('Matagalpa'),
														'NS' => TTi18n::gettext('Nueva Segovia'),
														'SJ' => TTi18n::gettext('Rio San Juan'),
														'RI' => TTi18n::gettext('Rivas'),
														'AN' => TTi18n::gettext('Region Autonoma Atlantico Norte'),
														'AS' => TTi18n::gettext('Region Autonoma Atlantico Sur'),
														),
										'NE' => array( '00' => '--'),
										'NG' => array( '00' => '--'),
										'NU' => array( '00' => '--'),
										'NF' => array( '00' => '--'),
										'MP' => array( '00' => '--'),
										'NO' => array( '00' => '--'),
										'OM' => array( '00' => '--'),
										'PK' => array( '00' => '--'),
										'PW' => array( '00' => '--'),
										'PS' => array( '00' => '--'),
										'PA' => array(
														'00' => '--',
														'BC' => TTi18n::gettext('Bocas del Toro'),
														'CH' => TTi18n::gettext('Chiriqui'),
														'CC' => TTi18n::gettext('Cocle'),
														'CL' => TTi18n::gettext('Colon'),
														'DR' => TTi18n::gettext('Darien'),
														'HE' => TTi18n::gettext('Herrera'),
														'LS' => TTi18n::gettext('Los Santos'),
														'PN' => TTi18n::gettext('Panama'),
														'SB' => TTi18n::gettext('San Blas'),
														'VR' => TTi18n::gettext('Veraguas'),
														),
										'PG' => array( '00' => '--'),
										'PY' => array( '00' => '--'),
										'PE' => array( '00' => '--'),
										'PH' => array(
														'00' => '--',
														'AB' => TTi18n::gettext('Abra'),
														'AN' => TTi18n::gettext('Agusan del Norte'),
														'AS' => TTi18n::gettext('Agusan del Sur'),
														'AK' => TTi18n::gettext('Aklan'),
														'AL' => TTi18n::gettext('Albay'),
														'AQ' => TTi18n::gettext('Antique'),
														'AP' => TTi18n::gettext('Apayao'),
														'AU' => TTi18n::gettext('Aurora'),
														'BS' => TTi18n::gettext('Basilan'),
														'BA' => TTi18n::gettext('Bataan'),
														'BN' => TTi18n::gettext('Batanes'),
														'BT' => TTi18n::gettext('Batangas'),
														'BG' => TTi18n::gettext('Benguet'),
														'BI' => TTi18n::gettext('Biliran'),
														'BO' => TTi18n::gettext('Bohol'),
														'BK' => TTi18n::gettext('Bukidnon'),
														'BU' => TTi18n::gettext('Bulacan'),
														'CG' => TTi18n::gettext('Cagayan'),
														'CN' => TTi18n::gettext('Camarines Norte'),
														'CS' => TTi18n::gettext('Camarines Sur'),
														'CM' => TTi18n::gettext('Camiguin'),
														'CP' => TTi18n::gettext('Capiz'),
														'CT' => TTi18n::gettext('Catanduanes'),
														'CV' => TTi18n::gettext('Cavite'),
														'CB' => TTi18n::gettext('Cebu'),
														'CL' => TTi18n::gettext('Compostela Valley'),
														'NC' => TTi18n::gettext('Cotabato'),
														'DV' => TTi18n::gettext('Davao del Norte'),
														'DS' => TTi18n::gettext('Davao del Sur'),
														'DO' => TTi18n::gettext('Davao Oriental'),
														'DI' => TTi18n::gettext('Dinagat Islands'),
														'ES' => TTi18n::gettext('Eastern Samar'),
														'GU' => TTi18n::gettext('Guimaras'),
														'IF' => TTi18n::gettext('Ifugao'),
														'IN' => TTi18n::gettext('Ilocos Norte'),
														'IS' => TTi18n::gettext('Ilocos Sur'),
														'II' => TTi18n::gettext('Iloilo'),
														'IB' => TTi18n::gettext('Isabela'),
														'KA' => TTi18n::gettext('Kalinga'),
														'LG' => TTi18n::gettext('Laguna'),
														'LN' => TTi18n::gettext('Lanao del Norte'),
														'LS' => TTi18n::gettext('Lanao del Sur'),
														'LU' => TTi18n::gettext('La Union'),
														'LE' => TTi18n::gettext('Leyte'),
														'MG' => TTi18n::gettext('Maguindanao'),
														'MQ' => TTi18n::gettext('Marinduque'),
														'MB' => TTi18n::gettext('Masbate'),
														'MM' => TTi18n::gettext('Metropolitan Manila'),
														'MD' => TTi18n::gettext('Misamis Occidental'),
														'MN' => TTi18n::gettext('Misamis Oriental'),
														'MT' => TTi18n::gettext('Mountain'),
														'ND' => TTi18n::gettext('Negros Occidental'),
														'NR' => TTi18n::gettext('Negros Oriental'),
														'NS' => TTi18n::gettext('Northern Samar'),
														'NE' => TTi18n::gettext('Nueva Ecija'),
														'NV' => TTi18n::gettext('Nueva Vizcaya'),
														'MC' => TTi18n::gettext('Occidental Mindoro'),
														'MR' => TTi18n::gettext('Oriental Mindoro'),
														'PL' => TTi18n::gettext('Palawan'),
														'PM' => TTi18n::gettext('Pampanga'),
														'PN' => TTi18n::gettext('Pangasinan'),
														'QZ' => TTi18n::gettext('Quezon'),
														'QR' => TTi18n::gettext('Quirino'),
														'RI' => TTi18n::gettext('Rizal'),
														'RO' => TTi18n::gettext('Romblon'),
														'SM' => TTi18n::gettext('Samar'),
														'SG' => TTi18n::gettext('Sarangani'),
														'SQ' => TTi18n::gettext('Siquijor'),
														'SR' => TTi18n::gettext('Sorsogon'),
														'SC' => TTi18n::gettext('South Cotabato'),
														'SL' => TTi18n::gettext('Southern Leyte'),
														'SK' => TTi18n::gettext('Sultan Kudarat'),
														'SU' => TTi18n::gettext('Sulu'),
														'ST' => TTi18n::gettext('Surigao del Norte'),
														'SS' => TTi18n::gettext('Surigao del Sur'),
														'TR' => TTi18n::gettext('Tarlac'),
														'TT' => TTi18n::gettext('Tawi-Tawi'),
														'ZM' => TTi18n::gettext('Zambales'),
														'ZN' => TTi18n::gettext('Zamboanga del Norte'),
														'ZS' => TTi18n::gettext('Zamboanga del Sur'),
														'ZY' => TTi18n::gettext('Zamboanga-Sibugay'),
														),
										'PN' => array( '00' => '--'),
										'PL' => array( '00' => '--'),
										'PT' => array( '00' => '--'),
										'PR' => array( '00' => '--'),
										'QA' => array( '00' => '--'),
										'RE' => array( '00' => '--'),
										'RO' => array( '00' => '--'),
										'RU' => array( '00' => '--'),
										'RW' => array( '00' => '--'),
										'SH' => array( '00' => '--'),
										'KN' => array( '00' => '--'),
										'LC' => array( '00' => '--'),
										'PM' => array( '00' => '--'),
										'VC' => array( '00' => '--'),
										'WS' => array( '00' => '--'),
										'SM' => array( '00' => '--'),
										'ST' => array( '00' => '--'),
										'SA' => array( '00' => '--'),
										'SN' => array( '00' => '--'),
										'CS' => array( '00' => '--'),
										'SC' => array( '00' => '--'),
										'SL' => array( '00' => '--'),
										'SG' => array( '00' => '--'),
										'SK' => array( '00' => '--'),
										'SI' => array( '00' => '--'),
										'SB' => array( '00' => '--'),
										'SO' => array( '00' => '--'),
										'ZA' => array(
														'00' => '--',
														'MP' => TTi18n::gettext('Mpumalanga'),
														'GP' => TTi18n::gettext('Gauteng'),
														'NW' => TTi18n::gettext('North West'),
														'LP' => TTi18n::gettext('Limpopo'),
														'FS' => TTi18n::gettext('Free State'),
														'WC' => TTi18n::gettext('Western Cape'),
														'ZN' => TTi18n::gettext('Kwa-Zulu Natal'),
														'EC' => TTi18n::gettext('Eastern Cape'),
														'NC' => TTi18n::gettext('Northern Cape'),
														),
										'GS' => array( '00' => '--'),
										'ES' => array( '00' => '--'),
										'LK' => array( '00' => '--'),
										'SD' => array( '00' => '--'),
										'SR' => array( '00' => '--'),
										'SJ' => array( '00' => '--'),
										'SZ' => array( '00' => '--'),
										'SE' => array( '00' => '--'),
										'CH' => array( '00' => '--'),
										'SY' => array( '00' => '--'),
										'TW' => array( '00' => '--'),
										'TJ' => array( '00' => '--'),
										'TZ' => array( '00' => '--'),
										'TH' => array( '00' => '--'),
										'TL' => array( '00' => '--'),
										'TG' => array( '00' => '--'),
										'TK' => array( '00' => '--'),
										'TO' => array( '00' => '--'),
										'TT' => array( '00' => '--'),
										'TN' => array( '00' => '--'),
										'TR' => array( '00' => '--'),
										'TM' => array( '00' => '--'),
										'TC' => array( '00' => '--'),
										'TV' => array( '00' => '--'),
										'UG' => array( '00' => '--'),
										'UA' => array( '00' => '--'),
										'AE' => array( '00' => '--'),
										'GB' => array( '00' => '--'),
										'UM' => array( '00' => '--'),
										'UY' => array( '00' => '--'),
										'UZ' => array( '00' => '--'),
										'VU' => array( '00' => '--'),
										'VE' => array( '00' => '--'),
										'VN' => array( '00' => '--'),
										'VG' => array( '00' => '--'),
										'VI' => array( '00' => '--'),
										'WF' => array( '00' => '--'),
										'EH' => array( '00' => '--'),
										'YE' => array( '00' => '--'),
										'ZM' => array( '00' => '--'),
										'ZW' => array( '00' => '--'),
										);
				break;
			case 'district':
				$retval = array(
										'US' => array(
													'AL' => array( '00' => TTi18n::gettext('--Other--') ),
													'AK' => array( '00' => TTi18n::gettext('--Other--') ),
													'AZ' => array( '00' => TTi18n::gettext('--Other--') ),
													'AR' => array( '00' => TTi18n::gettext('--Other--') ),
													'CA' => array( '00' => TTi18n::gettext('--Other--') ),
													'CO' => array( '00' => TTi18n::gettext('--Other--') ),
													'CT' => array( '00' => TTi18n::gettext('--Other--') ),
													'DE' => array( '00' => TTi18n::gettext('--Other--') ),
													'DC' => array( '00' => TTi18n::gettext('--Other--') ),
													'FL' => array( '00' => TTi18n::gettext('--Other--') ),
													'GA' => array( '00' => TTi18n::gettext('--Other--') ),
													'HI' => array( '00' => TTi18n::gettext('--Other--') ),
													'ID' => array( '00' => TTi18n::gettext('--Other--') ),
													'IL' => array( '00' => TTi18n::gettext('--Other--') ),
													'IN' => array( 'ALL' => TTi18n::gettext('--Other--') ),
													'IA' => array( '00' => TTi18n::gettext('--Other--') ),
													'KS' => array( '00' => TTi18n::gettext('--Other--') ),
													'KY' => array( '00' => TTi18n::gettext('--Other--') ),
													'LA' => array( '00' => TTi18n::gettext('--Other--') ),
													'ME' => array( '00' => TTi18n::gettext('--Other--') ),
													'MD' => array( 'ALL' => TTi18n::gettext('--Other--') ),
													'MA' => array( '00' => TTi18n::gettext('--Other--') ),
													'MI' => array( '00' => TTi18n::gettext('--Other--') ),
													'MN' => array( '00' => TTi18n::gettext('--Other--') ),
													'MS' => array( '00' => TTi18n::gettext('--Other--') ),
													'MO' => array( '00' => TTi18n::gettext('--Other--') ),
													'MT' => array( '00' => TTi18n::gettext('--Other--') ),
													'NE' => array( '00' => TTi18n::gettext('--Other--') ),
													'NV' => array( '00' => TTi18n::gettext('--Other--') ),
													'NH' => array( '00' => TTi18n::gettext('--Other--') ),
													'NM' => array( '00' => TTi18n::gettext('--Other--') ),
													'NJ' => array( '00' => TTi18n::gettext('--Other--') ),
													'NY' => array(
																'NYC' => TTi18n::gettext('New York City'),
																'Yonkers' => TTi18n::gettext('Yonkers')
															),
													'NC' => array( '00' => TTi18n::gettext('--Other--') ),
													'ND' => array( '00' => TTi18n::gettext('--Other--') ),
													'OH' => array( '00' => TTi18n::gettext('--Other--') ),
													'OK' => array( '00' => TTi18n::gettext('--Other--') ),
													'OR' => array( '00' => TTi18n::gettext('--Other--') ),
													'PA' => array( '00' => TTi18n::gettext('--Other--') ),
													'RI' => array( '00' => TTi18n::gettext('--Other--') ),
													'SC' => array( '00' => TTi18n::gettext('--Other--') ),
													'SD' => array( '00' => TTi18n::gettext('--Other--') ),
													'TN' => array( '00' => TTi18n::gettext('--Other--') ),
													'TX' => array( '00' => TTi18n::gettext('--Other--') ),
													'UT' => array( '00' => TTi18n::gettext('--Other--') ),
													'VT' => array( '00' => TTi18n::gettext('--Other--') ),
													'VA' => array( '00' => TTi18n::gettext('--Other--') ),
													'WA' => array( '00' => TTi18n::gettext('--Other--') ),
													'WV' => array( '00' => TTi18n::gettext('--Other--') ),
													'WI' => array( '00' => TTi18n::gettext('--Other--') ),
													'WY' => array( '00' => TTi18n::gettext('--Other--') ),
													),
										);
				break;
			case 'industry':
				//2007 NAICS
				$retval = array(
										0  => TTi18n::gettext('- Please Choose -'),
										72  => TTi18n::gettext('Accommodation and Food Services'),
										561 => TTi18n::gettext('Administrative and Support Services'),
										11  => TTi18n::gettext('Agriculture, Forestry, Fishing and Hunting'),
										71  => TTi18n::gettext('Arts, Entertainment and Recreation'),
										23  => TTi18n::gettext('Construction'),
										518 => TTi18n::gettext('Data Processing, Hosting and Related Services'),
										61  => TTi18n::gettext('Educational Services'),
										52  => TTi18n::gettext('Finance and Insurance'),
										91  => TTi18n::gettext('Government/Public Administration'),
										62  => TTi18n::gettext('Health Care and Social Assistance'),
										51  => TTi18n::gettext('Information and Cultural Industries'),
										544  => TTi18n::gettext('Information Technology Software'),
										55  => TTi18n::gettext('Management of Companies and Enterprises'),
										31  => TTi18n::gettext('Manufacturing'),
										21  => TTi18n::gettext('Mining and Oil and Gas Extraction'),
										512 => TTi18n::gettext('Motion Picture and Sound Recording Industries'),
										54  => TTi18n::gettext('Professional, Scientific and Technical Services'),
										511 => TTi18n::gettext('Publishing Industries (except Internet'),
										53  => TTi18n::gettext('Real Estate and Rental and Leasing'),
										44  => TTi18n::gettext('Retail Trade'),
										517 => TTi18n::gettext('Telecommunications'),
										48  => TTi18n::gettext('Transportation and Warehousing'),
										22  => TTi18n::gettext('Utilities'),
										562 => TTi18n::gettext('Waste Management and Remediation Services'),
										41  => TTi18n::gettext('Wholesale Trade'),
										99  => TTi18n::gettext('Other'),
									);
				break;
			case 'password_policy_type':
				$retval = array(
										0 => TTi18n::gettext('Disabled'),
										1 => TTi18n::gettext('Enabled'),
									);
				break;
			case 'password_minimum_strength':
				$retval = array(
										1 => TTi18n::gettext('Low'), //1-2 is low
										3 => TTi18n::gettext('Medium'), //3-4 is medium
										5 => TTi18n::gettext('High'), //5+ is high
									);
				break;
			case 'password_minimum_permission_level':
				$pcf = new PermissionControlFactory();
				$retval = $pcf->getOptions('level');
				break;
			case 'ldap_authentication_type':
				$retval = array(
										0 => TTi18n::gettext('Disabled'),
										1 => TTi18n::gettext('Enabled - w/Local Fallback'),
										2 => TTi18n::gettext('Enabled - LDAP Only')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-product_edition' => TTi18n::gettext('Product Edition'),
										'-1030-name' => TTi18n::gettext('Name'),
										'-1040-short_name' => TTi18n::gettext('Short Name'),
										'-1050-business_number' => TTi18n::gettext('Business Number'),

										'-1140-address1' => TTi18n::gettext('Address 1'),
										'-1150-address2' => TTi18n::gettext('Address 2'),
										'-1160-city' => TTi18n::gettext('City'),
										'-1170-province' => TTi18n::gettext('Province/State'),
										'-1180-country' => TTi18n::gettext('Country'),
										'-1190-postal_code' => TTi18n::gettext('Postal Code'),
										'-1200-work_phone' => TTi18n::gettext('Work Phone'),
										'-1210-fax_phone' => TTi18n::gettext('Fax Phone'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'name',
								'city',
								'province',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								'country',
								'province',
								'postal_code'
								);
				break;
		}
		
		return $retval;
	}

	function _getVariableToFunctionMap($data = null) {
		
		$variable_function_map = array(
			'id' => 'ID',
			'parent_id' => 'Parent',
			'status_id' => 'Status',
			'status' => FALSE,
			'product_edition_id' => 'ProductEdition',
			'product_edition' => FALSE,
			'industry_id' => 'Industry',
			'industry' => FALSE,
			'name' => 'Name',
			'business_number' => 'BusinessNumber',
			'originator_id' => 'OriginatorID',
			'data_center_id' => 'DataCenterID',
			'short_name' => 'ShortName',
			'address1' => 'Address1',
			'address2' => 'Address2',
			'city' => 'City',
			'country' => 'Country',
			'province' => 'Province',
			'postal_code' => 'PostalCode',
			'work_phone' => 'WorkPhone',
			'fax_phone' => 'FaxPhone',
			'admin_contact' => 'AdminContact',
			'billing_contact' => 'BillingContact',
			'support_contact' => 'SupportContact',
			'enable_second_last_name' => 'EnableSecondLastName',
			'other_id1' => 'OtherID1',
			'other_id2' => 'OtherID2',
			'other_id3' => 'OtherID3',
			'other_id4' => 'OtherID4',
			'other_id5' => 'OtherID5',

			'password_policy_type_id' => 'PasswordPolicyType',
			'password_minimum_permission_level' => 'PasswordMinimumPermissionLevel',
			'password_minimum_strength' => 'PasswordMinimumStrength',
			'password_minimum_length' => 'PasswordMinimumLength',
			'password_minimum_age' => 'PasswordMinimumAge',
			'password_maximum_age' => 'PasswordMaximumAge',

			'ldap_authentication_type_id' => 'LDAPAuthenticationType',
			'ldap_host' => 'LDAPHost',
			'ldap_port' => 'LDAPPort',
			'ldap_bind_user_name' => 'LDAPBindUserName',
			'ldap_bind_password' => 'LDAPBindPassword',
			'ldap_base_dn' => 'LDAPBaseDN',
			'ldap_bind_attribute' => 'LDAPBindAttribute',
			'ldap_user_filter' => 'LDAPUserFilter',
			'ldap_login_attribute' => 'LDAPLoginAttribute',
			'deleted' => 'Deleted',
		);

		return $variable_function_map;
	}

	function getUserDefaultObject() {
		if ( is_object($this->user_default_obj) ) {
			return $this->user_default_obj;
		} else {
			$udlf = new UserDefaultListFactory();
			$udlf->getByCompanyId( $this->getID() );
			if ( $udlf->getRecordCount() == 1 ) {
				$this->user_default_obj = $udlf->getCurrent();
				return $this->user_default_obj;
			}

			return FALSE;
		}
	}

	function getUserObject( $user_id ) {
		if ( $user_id == '' AND $user_id <= 0 ) {
			return FALSE;
		}

		if ( isset($this->user_obj[$user_id]) AND is_object($this->user_obj[$user_id]) ) {
			return $this->user_obj[$user_id];
		} else {
			$ulf = new UserListFactory();
			$ulf->getById( $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				$this->user_obj[$user_id] = $ulf->getCurrent();

				return $this->user_obj[$user_id];
			}
		}

		return FALSE;
	}

	function getParent() {
		return $this->data['parent_id'];
	}
	function setParent($id) {
		$id = trim($id);

		$clf = new CompanyListFactory();

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'parent',
															$clf->getByID($id),
															TTi18n::gettext('Parent Company is invalid')
															) ) {
			$this->data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return $this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getProductEdition() {
		if ( isset($this->data['product_edition_id']) ) {
			return (int)$this->data['product_edition_id'];
		}

		return FALSE;
	}
	function setProductEdition($val) {
		$val = trim($val);

		$key = Option::getByValue($val, $this->getOptions('product_edition') );
		if ($key !== FALSE) {
			$val = $key;
		}

		if ( $this->Validator->inArrayKey(	'product_edition',
											$val,
											TTi18n::gettext('Incorrect Product Edition'),
											$this->getOptions('product_edition')) ) {

			$this->data['product_edition_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}
        
          /**Fl ADDED FOR CHILD FUND CFOR REQUIREMENTS**
         * Newly added column `epf_no` **/
	function getEpfNo() {
		return $this->data['epf_number'];
	}
        function setEpfNo($val) {
		$val = trim($val);

		if 	(	$val == ''
				OR $this->Validator->isLength(		'epf_number',
                                                                        $val,
                                                                        TTi18n::gettext('Business Number is too short or too long'),
                                                                        2,
                                                                        200) ) {

			$this->data['epf_number'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getName() {
		return $this->data['name'];
	}
	function setName($name, $force = FALSE) {
		$name = trim($name);

		if 	(	$this->Validator->isLength(		'name',
												$name,
												TTi18n::gettext('Name is too short or too long'),
												2,
												100) ) {

			global $config_vars;
			if ( $force == FALSE AND isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] == $this->getId() AND getTTProductEdition() > 10 ) {
				//Don't change company name
			} else {
				$this->data['name'] = $name;
				$this->setNameMetaphone( $name );
			}

			return TRUE;
		}

		return FALSE;
	}
	function getNameMetaphone() {
		if ( isset($this->data['name_metaphone']) ) {
			return $this->data['name_metaphone'];
		}

		return FALSE;
	}
	function setNameMetaphone($value) {
		$value = metaphone( trim($value) );

		if 	( $value != '' ) {
			$this->data['name_metaphone'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getIndustry() {
		if ( isset($this->data['industry_id']) ) {
			return $this->data['industry_id'];
		}

		return FALSE;
	}
	function setIndustry($value) {
		$value = trim($value);

		if ( $value == 0
				OR $this->Validator->inArrayKey(	'industry_id',
													$value,
													TTi18n::gettext('Incorrect Industry'),
													$this->getOptions('industry')) ) {

			$this->data['industry_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getBusinessNumber() {
		if ( isset($this->data['business_number']) ) {
			return $this->data['business_number'];
		}

		return FALSE;
	}
	function setBusinessNumber($val) {
		$val = trim($val);

		if 	(	$val == ''
				OR $this->Validator->isLength(		'business_number',
												$val,
												TTi18n::gettext('Business Number is too short or too long'),
												2,
												200) ) {

			$this->data['business_number'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getOriginatorID() {
		if ( isset($this->data['originator_id']) ) {
			return $this->data['originator_id'];
		}

		return FALSE;
	}
	function setOriginatorID($val) {
		$val = trim($val);

		if 	(	$val == ''
				OR $this->Validator->isLength(	'originator_id',
												$val,
												TTi18n::gettext('Originator ID is too short or too long'),
												2,
												200) ) {

			//Typo in SQL file, go with it for now.
			$this->data['originator_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getDataCenterID() {
		if ( isset($this->data['data_center_id']) ) {
			return $this->data['data_center_id'];
		}

		return FALSE;
	}
	function setDataCenterID($val) {
		$val = trim($val);

		if 	(	$val == ''
				OR $this->Validator->isLength(	'data_center_id',
												$val,
												TTi18n::gettext('Data Center ID is too short or too long'),
												2,
												200) ) {

			$this->data['data_center_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}


	function getShortName() {
		if ( isset($this->data['short_name']) ) {
			return $this->data['short_name'];
		}

		return FALSE;
	}
	function setShortName($name) {
		$name = trim($name);

		if 	(	$this->Validator->isLength(		'short_name',
												$name,
												TTi18n::gettext('Short name is too short or too long'),
												2,
												15) ) {

			$this->data['short_name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress1() {
		if ( isset($this->data['address1']) ) {
			return $this->data['address1'];
		}

		return FALSE;
	}
	function setAddress1($address1) {
		$address1 = trim($address1);

		if 	(
				$address1 == ''
				OR (
				$this->Validator->isRegEx(		'address1',
												$address1,
												TTi18n::gettext('Address1 contains invalid characters'),
												$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address1',
													$address1,
													TTi18n::gettext('Address1 is too short or too long'),
													2,
													250) ) ) {

			$this->data['address1'] = $address1;

			return TRUE;
		}

		return FALSE;
	}

	function getAddress2() {
		if ( isset($this->data['address2']) ) {
			return $this->data['address2'];
		}

		return FALSE;
	}
	function setAddress2($address2) {
		$address2 = trim($address2);

		if 	(	$address2 == ''
				OR (
					$this->Validator->isRegEx(		'address2',
													$address2,
													TTi18n::gettext('Address2 contains invalid characters'),
													$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address2',
													$address2,
													TTi18n::gettext('Address2 is too short or too long'),
													2,
													250) ) ) {

			$this->data['address2'] = $address2;

			return TRUE;
		}

		return FALSE;

	}

	function getCity() {
		if ( isset($this->data['city']) ) {
			return $this->data['city'];
		}

		return FALSE;
	}
	function setCity($city) {
		$city = trim($city);

		if 	(	$this->Validator->isRegEx(		'city',
												$city,
												TTi18n::gettext('City contains invalid characters'),
												$this->city_validator_regex)
				AND
					$this->Validator->isLength(		'city',
													$city,
													TTi18n::gettext('City name is too short or too long'),
													2,
													250) ) {

			$this->data['city'] = $city;

			return TRUE;
		}

		return FALSE;
	}

	function getCountry() {
		if ( isset($this->data['country']) ) {
			return $this->data['country'];
		}

		return FALSE;
	}
	function setCountry($country) {
		$country = trim($country);

		if ( $this->Validator->inArrayKey(		'country',
												$country,
												TTi18n::gettext('Invalid Country'),
												$this->getOptions('country') ) ) {

			$this->data['country'] = $country;

			return TRUE;
		}

		return FALSE;
	}

	function getProvince() {
		if ( isset($this->data['province']) ) {
			return $this->data['province'];
		}

		return FALSE;
	}
	function setProvince($province) {
		$province = trim($province);

		Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__,10);

		$options_arr = $this->getOptions('province');
		if ( isset($options_arr[$this->getCountry()]) ) {
			$options = $options_arr[$this->getCountry()];
		} else {
			$options = array();
		}

		if ( $this->Validator->inArrayKey(		'province',
												$province,
												TTi18n::gettext('Invalid Province/State'),
												$options ) ) {

			$this->data['province'] = $province;

			return TRUE;
		}

		return FALSE;
	}

	function getPostalCode() {
		if ( isset($this->data['postal_code']) ) {
			return $this->data['postal_code'];
		}

		return FALSE;
	}
	function setPostalCode($postal_code) {
		$postal_code = strtoupper( $this->Validator->stripSpaces($postal_code) );

		if 	(
				$postal_code == ''
				OR
				(
					$this->Validator->isPostalCode(	'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
													$this->getCountry(), $this->getProvince() )
				AND
					$this->Validator->isLength(		'postal_code',
													$postal_code,
													TTi18n::gettext('Postal/ZIP Code is too short or too long'),
													1,
													10)
				)
				) {

			$this->data['postal_code'] = $postal_code;

			return TRUE;
		}

		return FALSE;
	}

	function getLongitude() {
		if ( isset($this->data['longitude']) ) {
			return (float)$this->data['longitude'];
		}

		return FALSE;
	}
	function setLongitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'longitude',
											$value,
											TTi18n::gettext('Longitude is invalid')
											) ) {
			$this->data['longitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLatitude() {
		if ( isset($this->data['latitude']) ) {
			return (float)$this->data['latitude'];
		}

		return FALSE;
	}
	function setLatitude($value) {
		$value = trim((float)$value);

		if (	$value == 0
				OR
				$this->Validator->isFloat(	'latitude',
											$value,
											TTi18n::gettext('Latitude is invalid')
											) ) {
			$this->data['latitude'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getWorkPhone() {
		if ( isset($this->data['work_phone']) ) {
			return $this->data['work_phone'];
		}

		return FALSE;
	}
	function setWorkPhone($work_phone) {
		$work_phone = trim($work_phone);

		if 	(	$this->Validator->isPhoneNumber(		'work_phone',
														$work_phone,
														TTi18n::gettext('Work phone number is invalid')) ) {

			$this->data['work_phone'] = $work_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getFaxPhone() {
		if ( isset($this->data['fax_phone']) ) {
			return $this->data['fax_phone'];
		}

		return FALSE;
	}
	function setFaxPhone($fax_phone) {
		$fax_phone = trim($fax_phone);

		if 	(	$fax_phone == ''
				OR
				$this->Validator->isPhoneNumber(		'fax_phone',
														$fax_phone,
														TTi18n::gettext('Fax phone number is invalid')) ) {

			$this->data['fax_phone'] = $fax_phone;

			return TRUE;
		}

		return FALSE;
	}

	function getAdminContact() {
		if ( isset($this->data['admin_contact']) ) {
			return $this->data['admin_contact'];
		}

		return FALSE;
	}
	function setAdminContact($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( !empty($id)
				AND 	$this->Validator->isResultSetWithRows(	'admin_contact',
																$ulf->getByID($id),
																TTi18n::gettext('Contact User is invalid')
																) ) {

			$this->data['admin_contact'] = $id;

			return TRUE;
		}

		return FALSE;

	}

	function getBillingContact() {
		if ( isset($this->data['billing_contact']) ) {
			return $this->data['billing_contact'];
		}

		return FALSE;
	}
	function setBillingContact($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( !empty($id)
				AND $this->Validator->isResultSetWithRows(	'billing_contact',
															$ulf->getByID($id),
															TTi18n::gettext('Contact User is invalid')
															) ) {

			$this->data['billing_contact'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSupportContact() {
		if ( isset($this->data['support_contact']) ) {
			return $this->data['support_contact'];
		}

		return FALSE;
	}
	function setSupportContact($id) {
		$id = trim($id);

		$ulf = new UserListFactory();

		if ( !empty($id)
				AND $this->Validator->isResultSetWithRows(	'support_contact',
															$ulf->getByID($id),
															TTi18n::gettext('Contact User is invalid')
															) ) {

			$this->data['support_contact'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID1() {
		if ( isset($this->data['other_id1']) ) {
			return $this->data['other_id1'];
		}

		return FALSE;
	}
	function setOtherID1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id1',
											$value,
											TTi18n::gettext('Other ID 1 is invalid'),
											1,255) ) {

			$this->data['other_id1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID2() {
		if ( isset($this->data['other_id2']) ) {
			return $this->data['other_id2'];
		}

		return FALSE;
	}
	function setOtherID2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id2',
											$value,
											TTi18n::gettext('Other ID 2 is invalid'),
											1,255) ) {

			$this->data['other_id2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID3() {
		if ( isset($this->data['other_id3']) ) {
			return $this->data['other_id3'];
		}

		return FALSE;
	}
	function setOtherID3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id3',
											$value,
											TTi18n::gettext('Other ID 3 is invalid'),
											1,255) ) {

			$this->data['other_id3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID4() {
		if ( isset($this->data['other_id4']) ) {
			return $this->data['other_id4'];
		}

		return FALSE;
	}
	function setOtherID4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id4',
											$value,
											TTi18n::gettext('Other ID 4 is invalid'),
											1,255) ) {

			$this->data['other_id4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getOtherID5() {
		if ( isset($this->data['other_id5']) ) {
			return $this->data['other_id5'];
		}

		return FALSE;
	}
	function setOtherID5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'other_id5',
											$value,
											TTi18n::gettext('Other ID 5 is invalid'),
											1,255) ) {

			$this->data['other_id5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultCurrency() {
		$culf = new CurrencyListFactory();
		$culf->getByCompanyIdAndDefault( $this->getId(), TRUE );
		if ($culf->getRecordCount() == 1 ) {
			return $culf->getCurrent()->getId();
		}

		return FALSE;
	}

	function getEnableAddCurrency() {
		if ( isset($this->add_currency) ) {
			return $this->add_currency;
		}

		return FALSE;
	}
	function setEnableAddCurrency($bool) {
		$this->add_currency = $bool;

		return TRUE;
	}

	function getEnableAddPermissionGroupPreset() {
		if ( isset($this->add_permission_group_preset) ) {
			return $this->add_permission_group_preset;
		}

		return FALSE;
	}
	function setEnableAddPermissionGroupPreset($bool) {
		$this->add_permission_group_preset = $bool;

		return TRUE;
	}

	function getEnableAddStation() {
		if ( isset($this->add_station) ) {
			return $this->add_station;
		}

		return FALSE;
	}
	function setEnableAddStation($bool) {
		$this->add_station = $bool;

		return TRUE;
	}

	function getEnableAddPayStubEntryAccountPreset() {
		if ( isset($this->add_pay_stub_entry_account_preset) ) {
			return $this->add_pay_stub_entry_account_preset;
		}

		return FALSE;
	}
	function setEnableAddPayStubEntryAccountPreset($bool) {
		$this->add_pay_stub_entry_account_preset = $bool;

		return TRUE;
	}

	function getEnableAddCompanyDeductionPreset() {
		if ( isset($this->add_company_deduction_preset) ) {
			return $this->add_company_deduction_preset;
		}

		return FALSE;
	}
	function setEnableAddCompanyDeductionPreset($bool) {
		$this->add_company_deduction_preset = $bool;

		return TRUE;
	}

	function getEnableAddUserDefaultPreset() {
		if ( isset($this->add_user_default_preset) ) {
			return $this->add_user_default_preset;
		}

		return FALSE;
	}
	function setEnableAddUserDefaultPreset($bool) {
		$this->add_user_default_preset = $bool;

		return TRUE;
	}

	function getEnableSecondLastName(){
		if ( isset($this->data['enable_second_last_name']) ) {
			return $this->data['enable_second_last_name'];
		}

		return FALSE;
	}

	function setEnableSecondLastName($bool){
		$this->data['enable_second_last_name'] = $this->toBool($bool);

		return TRUE;
	}

	function getEnableAddRecurringHolidayPreset() {
		if ( isset($this->add_recurring_holiday_preset) ) {
			return $this->add_recurring_holiday_preset;
		}

		return FALSE;
	}
	function setEnableAddRecurringHolidayPreset($bool) {
		$this->add_recurring_holiday_preset = $bool;

		return TRUE;
	}

	function isLogoExists() {
		return file_exists( $this->getLogoFileName() );
	}

	function getLogoFileName( $company_id = NULL, $include_default_logo = TRUE, $primary_company_logo = FALSE ) {

		//Test for both jpg and png
		$base_name = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR .'logo';
		if ( file_exists( $base_name.'.jpg') ) {
			$logo_file_name = $base_name.'.jpg';
		} elseif ( file_exists( $base_name.'.png') ) {
			$logo_file_name = $base_name.'.png';
		} else {
			if ( $include_default_logo == TRUE ) {
				//Check for primary company logo first, so branding gets carried over automatically.
				if ( $company_id != PRIMARY_COMPANY_ID ) {
					$logo_file_name = $this->getLogoFileName( PRIMARY_COMPANY_ID );
				} else {
					if ( $primary_company_logo == TRUE AND defined('TIMETREX_API') AND TIMETREX_API === TRUE ) {
						//Only display login logo on the login page, not the top right logo once logged in, as its not the proper size.
						$logo_file_name = Environment::getImagesPath().'timetrex_logo_flex_login.png';
					} else {
						$logo_file_name = Environment::getImagesPath().'timetrex_logo_wbg_small2.jpg';
					}
				}
			} else {
				return FALSE;
			}
		}

		//Debug::Text('Logo File Name: '. $logo_file_name .' Include Default: '. (int)$include_default_logo .' Primary Company Logo: '. (int)$primary_company_logo, __FILE__, __LINE__, __METHOD__,10);
		return $logo_file_name;
	}

        
        
        	function getLogoFileWithoutPath( $company_id = NULL, $include_default_logo = TRUE, $primary_company_logo = FALSE ) {
                        
                   $file_name = 'logo';
		//Test for both jpg and png
		$base_name = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR .$file_name;
		if ( file_exists( $base_name.'.jpg') ) {
			$logo_file_name = $file_name.'.jpg';
		} elseif ( file_exists( $base_name.'.png') ) {
			$logo_file_name = $file_name.'.png';
		} else {
			if ( $include_default_logo == TRUE ) {
				//Check for primary company logo first, so branding gets carried over automatically.
				if ( $company_id != PRIMARY_COMPANY_ID ) {
					$logo_file_name = $this->getLogoFileName( PRIMARY_COMPANY_ID );
				} else {
					if ( $primary_company_logo == TRUE AND defined('TIMETREX_API') AND TIMETREX_API === TRUE ) {
						//Only display login logo on the login page, not the top right logo once logged in, as its not the proper size.
						$logo_file_name = Environment::getImagesPath().'timetrex_logo_flex_login.png';
					} else {
						$logo_file_name = Environment::getImagesPath().'timetrex_logo_wbg_small2.jpg';
					}
				}
			} else {
				return FALSE;
			}
		}

		//Debug::Text('Logo File Name: '. $logo_file_name .' Include Default: '. (int)$include_default_logo .' Primary Company Logo: '. (int)$primary_company_logo, __FILE__, __LINE__, __METHOD__,10);
		return $logo_file_name;
	}
        
        
        
	function cleanStoragePath( $company_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->getCompany();
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;

		if ( $dir != '' ) {
			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
			}
		}

		return TRUE;
	}

	function getStoragePath( $company_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->getID();
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'company_logo'. DIRECTORY_SEPARATOR . $company_id;
	}


	//Send company data to TimeTrex server so auto update notifications are correct
	//based on geographical region.
	//This shouldn't be called unless the user requests auto update notification.
	function remoteSave() {
		$ttsc = new TimeTrexSoapClient();

		if ( ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL OR $ttsc->isUpdateNotifyEnabled() == TRUE )
				AND PRODUCTION == TRUE
				AND DEMO_MODE == FALSE ) {
			$ttsc->sendCompanyData( $this->getId() );
			$ttsc->sendCompanyVersionData( $this->getId() );

			return TRUE;
		}

		return FALSE;
	}

	/*
		Pasword Policy functions
	*/
	function getPasswordPolicyType() {
		if ( isset($this->data['password_policy_type_id']) ) {
			return $this->data['password_policy_type_id'];
		}

		return FALSE;
	}
	function setPasswordPolicyType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'password_policy_type_id',
											$type,
											TTi18n::gettext('Incorrect Password Policy type'),
											$this->getOptions('password_policy_type')) ) {

			$this->data['password_policy_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordMinimumPermissionLevel() {
		if ( isset($this->data['password_minimum_permission_level']) ) {
			return $this->data['password_minimum_permission_level'];
		}

		return FALSE;
	}
	function setPasswordMinimumPermissionLevel($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'password_minimum_permission_level',
											$type,
											TTi18n::gettext('Incorrect minimum permission level'),
											$this->getOptions('password_minimum_permission_level')) ) {

			$this->data['password_minimum_permission_level'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordMinimumStrength() {
		if ( isset($this->data['password_minimum_strength']) ) {
			return $this->data['password_minimum_strength'];
		}

		return FALSE;
	}
	function setPasswordMinimumStrength($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'password_minimum_strength',
											$type,
											TTi18n::gettext('Invalid password strength'),
											$this->getOptions('password_minimum_strength')) ) {

			$this->data['password_minimum_strength'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordMinimumLength() {
		if ( isset($this->data['password_minimum_length']) ) {
			return $this->data['password_minimum_length'];
		}

		return FALSE;
	}
	function setPasswordMinimumLength($type) {
		$type = trim($type);

		if (
			$this->Validator->isNumeric(	'password_minimum_length',
											$type,
											TTi18n::gettext('Password minimum length must only be digits'))
			) {

			$this->data['password_minimum_length'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordMinimumAge() {
		if ( isset($this->data['password_minimum_age']) ) {
			return $this->data['password_minimum_age'];
		}

		return FALSE;
	}
	function setPasswordMinimumAge($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		if (
					$this->Validator->isNumeric(	'password_minimum_age',
													$value,
													TTi18n::gettext('Minimum age must only be digits'))

												) {
			$this->data['password_minimum_age'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPasswordMaximumAge() {
		if ( isset($this->data['password_maximum_age']) ) {
			return $this->data['password_maximum_age'];
		}

		return FALSE;
	}
	function setPasswordMaximumAge($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		if (
				$this->Validator->isNumeric(	'password_maximum_age',
												$value,
												TTi18n::gettext('Maximum age must only be digits'))
												) {
			$this->data['password_maximum_age'] = $value;

			return TRUE;
		}

		return FALSE;
	}


	/*
		LDAP Authentication functions
	*/
	function getLDAPAuthenticationType() {
		if ( isset($this->data['ldap_authentication_type_id']) ) {
			return $this->data['ldap_authentication_type_id'];
		}

		return FALSE;
	}
	function setLDAPAuthenticationType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'ldap_authentication_type_id',
											$type,
											TTi18n::gettext('Incorrect LDAP authentication type'),
											$this->getOptions('ldap_authentication_type')) ) {

			$this->data['ldap_authentication_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPHost() {
		if ( isset($this->data['ldap_host']) ) {
			return $this->data['ldap_host'];
		}

		return FALSE;
	}
	function setLDAPHost($value) {
		$value = trim($value);

		if 	(	$value == ''
				OR
				$this->Validator->isLength(		'ldap_host',
												$value,
												TTi18n::gettext('LDAP server host name is too short or too long'),
												2,
												100) ) {

			$this->data['ldap_host'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPPort() {
		if ( isset($this->data['ldap_port']) ) {
			return $this->data['ldap_port'];
		}

		return FALSE;
	}
	function setLDAPPort($value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );

		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
		if (
				$value == ''
				OR (
					$this->Validator->isNumeric(	'ldap_port',
													$value,
													TTi18n::gettext('LDAP port must only be digits'))
					)
												) {
			$this->data['ldap_port'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPBindUserName() {
		if ( isset($this->data['ldap_bind_user_name']) ) {
			return $this->data['ldap_bind_user_name'];
		}

		return FALSE;
	}
	function setLDAPBindUserName($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_bind_user_name',
												$value,
												TTi18n::gettext('LDAP bind user name is too long'),
												0,
												100) ) {

			$this->data['ldap_bind_user_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPBindPassword() {
		if ( isset($this->data['ldap_bind_password']) ) {
			return $this->data['ldap_bind_password'];
		}

		return FALSE;
	}
	function setLDAPBindPassword($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_bind_password',
												$value,
												TTi18n::gettext('LDAP bind password is too long'),
												0,
												100) ) {

			$this->data['ldap_bind_password'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPBaseDN() {
		if ( isset($this->data['ldap_base_dn']) ) {
			return $this->data['ldap_base_dn'];
		}

		return FALSE;
	}
	function setLDAPBaseDN($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_base_dn',
												$value,
												TTi18n::gettext('LDAP base DN is too long'),
												0,
												250) ) {

			$this->data['ldap_base_dn'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPBindAttribute() {
		if ( isset($this->data['ldap_bind_attribute']) ) {
			return $this->data['ldap_bind_attribute'];
		}

		return FALSE;
	}
	function setLDAPBindAttribute($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_bind_attribute',
												$value,
												TTi18n::gettext('LDAP bind attribute is too long'),
												0,
												100) ) {

			$this->data['ldap_bind_attribute'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPUserFilter() {
		if ( isset($this->data['ldap_user_filter']) ) {
			return $this->data['ldap_user_filter'];
		}

		return FALSE;
	}
	function setLDAPUserFilter($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_user_filter',
												$value,
												TTi18n::gettext('LDAP user filter is too long'),
												0,
												250) ) {

			$this->data['ldap_user_filter'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLDAPLoginAttribute() {
		if ( isset($this->data['ldap_login_attribute']) ) {
			return $this->data['ldap_login_attribute'];
		}

		return FALSE;
	}
	function setLDAPLoginAttribute($value) {
		$value = trim($value);

		if 	(	$this->Validator->isLength(		'ldap_login_attribute',
												$value,
												TTi18n::gettext('LDAP login attribute is too long'),
												0,
												100) ) {

			$this->data['ldap_login_attribute'] = $value;

			return TRUE;
		}

		return FALSE;
	}


	function Validate() {
		global $config_vars;

		//Don't allow the primary company to be deleted.
		if ( $this->getDeleted() == TRUE
				AND isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] == $this->getID() ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Unable to delete the primary company'));
		}
																																												$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); }
		return TRUE;
	}

	function preSave() {

		if ( $this->isNew() == TRUE ) {
			$this->is_new = TRUE;
		}

		if ( $this->getIndustry() == FALSE ) {
			$this->setIndustry(0);
		}

		return TRUE;
	}
	function postSave() {
		$this->removeCache( $this->getId() );

		$this->remoteSave();

		if ( $this->getDeleted() == FALSE ) {
			//Add base currency for this new company.
			if ( $this->getEnableAddCurrency() == TRUE ) {
				$clf = new CurrencyListFactory();
				$clf->getByCompanyId( $this->getId() );
				if ( $clf->getRecordCount() == 0 ) {
					Debug::text('Adding Default Currency', __FILE__, __LINE__, __METHOD__,9);

					$cf = new CurrencyFactory();
					$country_to_currency_map_arr = $cf->getOptions('country_currency');

					if ( isset($country_to_currency_map_arr[$this->getCountry()]) ) {
						$base_currency = $country_to_currency_map_arr[$this->getCountry()];
						Debug::text('Found Base Currency For Country: '. $this->getCountry() .' Currency: '. $base_currency , __FILE__, __LINE__, __METHOD__,9);
					} else {
						Debug::text('DID NOT Find Base Currency For Country: '. $this->getCountry() .' Using default USD.', __FILE__, __LINE__, __METHOD__,9);
						$base_currency = 'USD';
					}

					$cf->setCompany( $this->getId() );
					$cf->setStatus( 10 );
					$cf->setName( $base_currency );
					$cf->setISOCode( $base_currency );

					$cf->setConversionRate( '1.000000000' );
					$cf->setAutoUpdate( FALSE );
					$cf->setBase( TRUE );
					$cf->setDefault( TRUE );

					if ( $cf->isValid() ) {
						$currency_id = $cf->Save();
					}
				}
			}

			if ( $this->getEnableAddPermissionGroupPreset() == TRUE ) {
				Debug::text('Adding Preset Permission Groups', __FILE__, __LINE__, __METHOD__,9);

				$pf = new PermissionFactory();
				$pf->StartTransaction();

				if ( $this->getProductEdition() == 20 ) {
					$preset_flags = array(
										'job' => 1,
										'invoice' => 1,
										'document' => 1,
										);
				} else {
					$preset_flags = array();
				}

				$preset_options = $pf->getOptions('preset');
				$preset_level_options = $pf->getOptions('preset_level');
				$i=0;
				foreach( $preset_options as $preset_id => $preset_name ) {
					$pcf = new PermissionControlFactory();
					$pcf->setCompany( $this->getId() );
					$pcf->setName( $preset_name );
					$pcf->setDescription( '' );
					$pcf->setLevel( $preset_level_options[$preset_id] );
					if ( $pcf->isValid() ) {
						$pcf_id = $pcf->Save(FALSE);

						if ( $i == 0 ) { //Regular employee
							$regular_employee_pcf_id = $pcf_id;
						}

						$pf->applyPreset($pcf_id, $preset_id, $preset_flags );
					}

					$i++;
				}
				$pf->CommitTransaction();
			}

			if ( $this->getEnableAddStation() == TRUE ) {
				Debug::text('Adding Default Station', __FILE__, __LINE__, __METHOD__,9);

				//Enable punching in from all stations
				$sf = new StationFactory();
				$sf->setCompany( $this->getId() );

				$sf->setStatus( 20 );
				$sf->setType( 10 );
				$sf->setSource( 'ANY' );
				$sf->setStation( 'ANY' );
				$sf->setDescription( 'All stations' );

				$sf->setGroupSelectionType( 10 );
				$sf->setBranchSelectionType( 10 );
				$sf->setDepartmentSelectionType( 10 );

				if ( $sf->isValid() ) {
					$sf->Save();
				}
			}

			if ( $this->getEnableAddPayStubEntryAccountPreset() == TRUE ) {
				Debug::text('Adding Pay Stub Entry Account Presets', __FILE__, __LINE__, __METHOD__,9);
				PayStubEntryAccountFactory::addPresets( $this->getId() );
			}

			if ( $this->getEnableAddCompanyDeductionPreset() == TRUE ) {
				Debug::text('Adding Company Deduction Presets', __FILE__, __LINE__, __METHOD__,9);
				CompanyDeductionFactory::addPresets( $this->getId() );
			}

			if ( $this->getEnableAddRecurringHolidayPreset() == TRUE ) {
				Debug::text('Adding Recurring Holiday Presets', __FILE__, __LINE__, __METHOD__,9);
				RecurringHolidayFactory::addPresets( $this->getId(), $this->getCountry() );
			}

			if ( $this->getEnableAddUserDefaultPreset() == TRUE ) {
				//User Default settings, always do this last.
				$udf = new UserDefaultFactory();
				$udf->setCompany( $this->getID() );
				$udf->setCity( $this->getCity() );
				$udf->setCountry( $this->getCountry() );
				$udf->setProvince( $this->getProvince() );
				$udf->setWorkPhone( $this->getWorkPhone() );

				$udf->setLanguage( 'en' );
				$udf->setDateFormat( 'd-M-y' );
				$udf->setTimeFormat( 'g:i A' );
				$udf->setTimeUnitFormat( 10 );
				$udf->setItemsPerPage( 25 );
				$udf->setStartWeekDay( 0 );

				//$udf->setPolicyGroup( $user_data['policy_group_id'] );

				if ( isset($regular_employee_pcf_id) ) {
					$udf->setPermissionControl( $regular_employee_pcf_id );
				}

				if ( isset($currency_id) ) {
					$udf->setCurrency( $currency_id );
				}

				$upf = new UserPreferenceFactory();
				$udf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone() ) );
				Debug::text('Time Zone: '. $udf->getTimeZone(), __FILE__, __LINE__, __METHOD__,9);

				$udf->setEnableEmailNotificationException( TRUE );
				$udf->setEnableEmailNotificationMessage( TRUE );
				$udf->setEnableEmailNotificationHome( TRUE );

				if ( $udf->isValid() ) {
					Debug::text('Adding User Default settings...', __FILE__, __LINE__, __METHOD__,9);

					$udf->Save();
				}
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			$ulf = new UserListFactory();
			$ulf->getByCompanyId( $this->getID() );
			if ( $ulf->getRecordCount() > 0 ) {
				$ulf->StartTransaction();
				foreach( $ulf as $u_obj ) {
					Debug::text('Deleting User ID: '. $u_obj->getId() , __FILE__, __LINE__, __METHOD__,9);
					$u_obj->setDeleted( TRUE );
					if ( $u_obj->isValid() ) {
						$u_obj->Save();
					}
				}
				$ulf->CommitTransaction();
			}
		}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			//Disable this for now, as if a master administrator is editing other companies it will cause an error.
			//$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}
	
	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
						case 'ldap_authentication_type':
						case 'password_policy_type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'product_edition':
							$data[$variable] = Option::getByKey( $this->getProductEdition(), $this->getOptions( $variable ) );
							break;
						case 'industry':
							$data[$variable] = Option::getByKey( $this->getIndustry(), $this->getOptions( $variable ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTDebug::addEntry( $this->getId(), $log_action, TTi18n::getText('Company Information'), NULL, $this->getTable(), $this );
	}

}
?>
