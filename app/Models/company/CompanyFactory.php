<?php
namespace App\Models\Company;

use App\Models\Core\CurrencyFactory;
use App\Models\Core\CurrencyListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Environment;
use App\Models\Core\Factory;
use App\Models\Core\Misc;
use App\Models\Core\Option;
use App\Models\Core\PermissionControlFactory;
use App\Models\Core\PermissionFactory;
use App\Models\Core\StationFactory;
use App\Models\Core\TTi18n;
use App\Models\Holiday\RecurringHolidayFactory;
use App\Models\PayStub\PayStubEntryAccountFactory;
use App\Models\Users\UserDefaultFactory;
use App\Models\Users\UserDefaultListFactory;
use App\Models\Users\UserListFactory;
use App\Models\Users\UserPreferenceFactory;
use TimeTrexSoapClient;
use App\Models\Core\TTLog;

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
										10 => ('ACTIVE'),
										20 => ('HOLD'),
										30 => ('CANCELLED')
									);
				break;
			case 'product_edition':
				if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
					$retval[10] = ('Standard');
					$retval[15] = ('Business');
					$retval[20] = ('Professional');
				} elseif ( getTTProductEdition() == TT_PRODUCT_BUSINESS ) {
					$retval[10] = ('Standard');
					$retval[15] = ('Business');
				} else {
					$retval[10] = ('Standard');
				}

				break;
			case 'country':
				$retval = array(
										'CA' => ('Canada'),
										'US' => ('United States'),
										'AF' => ('Afghanistan'),
										'AL' => ('Albania'),
										'DZ' => ('Algeria'),
										'AS' => ('American Samoa'),
										'AD' => ('Andorra'),
										'AO' => ('Angola'),
										'AI' => ('Anguilla'),
										'AQ' => ('Antarctica'),
										'AG' => ('Antigua and Barbuda'),
										'AR' => ('Argentina'),
										'AM' => ('Armenia'),
										'AW' => ('Aruba'),
										'AU' => ('Australia'),
										'AT' => ('Austria'),
										'AZ' => ('Azerbaijan'),
										'BS' => ('Bahamas'),
										'BH' => ('Bahrain'),
										'BD' => ('Bangladesh'),
										'BB' => ('Barbados'),
										'BY' => ('Belarus'),
										'BE' => ('Belgium'),
										'BZ' => ('Belize'),
										'BJ' => ('Benin'),
										'BM' => ('Bermuda'),
										'BT' => ('Bhutan'),
										'BO' => ('Bolivia'),
										'BA' => ('Bosnia and Herzegovina'),
										'BW' => ('Botswana'),
										'BV' => ('Bouvet Island'),
										'BR' => ('Brazil'),
										'IO' => ('British Indian Ocean Territory'),
										'BN' => ('Brunei Darussalam'),
										'BG' => ('Bulgaria'),
										'BF' => ('Burkina Faso'),
										'BI' => ('Burundi'),
										'KH' => ('Cambodia'),
										'CM' => ('Cameroon'),
										'CV' => ('Cape Verde'),
										'KY' => ('Cayman Islands'),
										'CF' => ('Central African Republic'),
										'TD' => ('Chad'),
										'CL' => ('Chile'),
										'CN' => ('China'),
										'CX' => ('Christmas Island'),
										'CC' => ('Cocos (Keeling) Islands'),
										'CO' => ('Colombia'),
										'KM' => ('Comoros'),
										'CG' => ('Congo'),
										'CD' => ('Congo, the Democratic Republic of'),
										'CK' => ('Cook Islands'),
										'CR' => ('Costa Rica'),
										'CI' => ('Cote D\'Ivoire'),
										'HR' => ('Croatia'),
										'CU' => ('Cuba'),
										'CY' => ('Cyprus'),
										'CZ' => ('Czech Republic'),
										'DK' => ('Denmark'),
										'DJ' => ('Djibouti'),
										'DM' => ('Dominica'),
										'DO' => ('Dominican Republic'),
										'EC' => ('Ecuador'),
										'EG' => ('Egypt'),
										'SV' => ('El Salvador'),
										'GQ' => ('Equatorial Guinea'),
										'ER' => ('Eritrea'),
										'EE' => ('Estonia'),
										'ET' => ('Ethiopia'),
										'FK' => ('Falkland Islands (Malvinas)'),
										'FO' => ('Faroe Islands'),
										'FJ' => ('Fiji'),
										'FI' => ('Finland'),
										'FR' => ('France'),
										'GF' => ('French Guiana'),
										'PF' => ('French Polynesia'),
										'TF' => ('French Southern Territories'),
										'GA' => ('Gabon'),
										'GM' => ('Gambia'),
										'GE' => ('Georgia'),
										'DE' => ('Germany'),
										'GH' => ('Ghana'),
										'GI' => ('Gibraltar'),
										'GR' => ('Greece'),
										'GL' => ('Greenland'),
										'GD' => ('Grenada'),
										'GP' => ('Guadeloupe'),
										'GU' => ('Guam'),
										'GT' => ('Guatemala'),
										'GN' => ('Guinea'),
										'GW' => ('Guinea-Bissau'),
										'GY' => ('Guyana'),
										'HT' => ('Haiti'),
										'HM' => ('Heard Island and Mcdonald Islands'),
										'VA' => ('Holy See (Vatican City State)'),
										'HN' => ('Honduras'),
										'HK' => ('Hong Kong'),
										'HU' => ('Hungary'),
										'IS' => ('Iceland'),
										'IN' => ('India'),
										'ID' => ('Indonesia'),
										'IR' => ('Iran, Islamic Republic of'),
										'IQ' => ('Iraq'),
										'IE' => ('Ireland'),
										'IL' => ('Israel'),
										'IT' => ('Italy'),
										'JM' => ('Jamaica'),
										'JP' => ('Japan'),
										'JO' => ('Jordan'),
										'KZ' => ('Kazakhstan'),
										'KE' => ('Kenya'),
										'KI' => ('Kiribati'),
										'KP' => ('Korea, Democratic People\'s Republic of'),
										'KR' => ('Korea, Republic of'),
										'KW' => ('Kuwait'),
										'KG' => ('Kyrgyzstan'),
										'LA' => ('Lao People\'s Democratic Republic'),
										'LV' => ('Latvia'),
										'LB' => ('Lebanon'),
										'LS' => ('Lesotho'),
										'LR' => ('Liberia'),
										'LY' => ('Libyan Arab Jamahiriya'),
										'LI' => ('Liechtenstein'),
										'LT' => ('Lithuania'),
										'LU' => ('Luxembourg'),
										'MO' => ('Macao'),
										'MK' => ('Macedonia, Former Yugoslav Republic of'),
										'MG' => ('Madagascar'),
										'MW' => ('Malawi'),
										'MY' => ('Malaysia'),
										'MV' => ('Maldives'),
										'ML' => ('Mali'),
										'MT' => ('Malta'),
										'MH' => ('Marshall Islands'),
										'MQ' => ('Martinique'),
										'MR' => ('Mauritania'),
										'MU' => ('Mauritius'),
										'YT' => ('Mayotte'),
										'MX' => ('Mexico'),
										'FM' => ('Micronesia, Federated States of'),
										'MD' => ('Moldova, Republic of'),
										'MC' => ('Monaco'),
										'MN' => ('Mongolia'),
										'MS' => ('Montserrat'),
										'MA' => ('Morocco'),
										'MZ' => ('Mozambique'),
										'MM' => ('Myanmar'),
										'NA' => ('Namibia'),
										'NR' => ('Nauru'),
										'NP' => ('Nepal'),
										'NL' => ('Netherlands'),
										'AN' => ('Netherlands Antilles'),
										'NC' => ('New Caledonia'),
										'NZ' => ('New Zealand'),
										'NI' => ('Nicaragua'),
										'NE' => ('Niger'),
										'NG' => ('Nigeria'),
										'NU' => ('Niue'),
										'NF' => ('Norfolk Island'),
										'MP' => ('Northern Mariana Islands'),
										'NO' => ('Norway'),
										'OM' => ('Oman'),
										'PK' => ('Pakistan'),
										'PW' => ('Palau'),
										'PS' => ('Palestinian Territory, Occupied'),
										'PA' => ('Panama'),
										'PG' => ('Papua New Guinea'),
										'PY' => ('Paraguay'),
										'PE' => ('Peru'),
										'PH' => ('Philippines'),
										'PN' => ('Pitcairn'),
										'PL' => ('Poland'),
										'PT' => ('Portugal'),
										'PR' => ('Puerto Rico'),
										'QA' => ('Qatar'),
										'RE' => ('Reunion'),
										'RO' => ('Romania'),
										'RU' => ('Russian Federation'),
										'RW' => ('Rwanda'),
										'SH' => ('Saint Helena'),
										'KN' => ('Saint Kitts and Nevis'),
										'LC' => ('Saint Lucia'),
										'PM' => ('Saint Pierre and Miquelon'),
										'VC' => ('Saint Vincent, Grenadines'),
										'WS' => ('Samoa'),
										'SM' => ('San Marino'),
										'ST' => ('Sao Tome and Principe'),
										'SA' => ('Saudi Arabia'),
										'SN' => ('Senegal'),
										'CS' => ('Serbia and Montenegro'),
										'SC' => ('Seychelles'),
										'SL' => ('Sierra Leone'),
										'SG' => ('Singapore'),
										'SK' => ('Slovakia'),
										'SI' => ('Slovenia'),
										'SB' => ('Solomon Islands'),
										'SO' => ('Somalia'),
										'ZA' => ('South Africa'),
										'GS' => ('South Georgia, South Sandwich Islands'),
										'ES' => ('Spain'),
										'LK' => ('Sri Lanka'),
										'SD' => ('Sudan'),
										'SR' => ('Suriname'),
										'SJ' => ('Svalbard and Jan Mayen'),
										'SZ' => ('Swaziland'),
										'SE' => ('Sweden'),
										'CH' => ('Switzerland'),
										'SY' => ('Syrian Arab Republic'),
										'TW' => ('Taiwan'),
										'TJ' => ('Tajikistan'),
										'TZ' => ('Tanzania, United Republic of'),
										'TH' => ('Thailand'),
										'TL' => ('Timor-Leste'),
										'TG' => ('Togo'),
										'TK' => ('Tokelau'),
										'TO' => ('Tonga'),
										'TT' => ('Trinidad and Tobago'),
										'TN' => ('Tunisia'),
										'TR' => ('Turkey'),
										'TM' => ('Turkmenistan'),
										'TC' => ('Turks and Caicos Islands'),
										'TV' => ('Tuvalu'),
										'UG' => ('Uganda'),
										'UA' => ('Ukraine'),
										'AE' => ('United Arab Emirates'),
										'GB' => ('United Kingdom'),
										'UM' => ('United States Minor Outlying Islands'),
										'UY' => ('Uruguay'),
										'UZ' => ('Uzbekistan'),
										'VU' => ('Vanuatu'),
										'VE' => ('Venezuela'),
										'VN' => ('Viet Nam'),
										'VG' => ('Virgin Islands, British'),
										'VI' => ('Virgin Islands, U.s.'),
										'WF' => ('Wallis and Futuna'),
										'EH' => ('Western Sahara'),
										'YE' => ('Yemen'),
										'ZM' => ('Zambia'),
										'ZW' => ('Zimbabwe'),
									);
				break;
			case 'province':
				$retval = array(
										'CA' => array(
														'AB' => ('Alberta'),
														'BC' => ('British Columbia'),
														'SK' => ('Saskatchewan'),
														'MB' => ('Manitoba'),
														'QC' => ('Quebec'),
														'ON' => ('Ontario'),
														'NL' => ('NewFoundLand'),
														'NB' => ('New Brunswick'),
														'NS' => ('Nova Scotia'),
														'PE' => ('Prince Edward Island'),
														'NT' => ('Northwest Territories'),
														'YT' => ('Yukon'),
														'NU' => ('Nunavut')
														),
										'US' => array(
														'AL' => ('Alabama'),
														'AK' => ('Alaska'),
														'AZ' => ('Arizona'),
														'AR' => ('Arkansas'),
														'CA' => ('California'),
														'CO' => ('Colorado'),
														'CT' => ('Connecticut'),
														'DE' => ('Delaware'),
														'DC' => ('D.C.'),
														'FL' => ('Florida'),
														'GA' => ('Georgia'),
														'HI' => ('Hawaii'),
														'ID' => ('Idaho'),
														'IL' => ('Illinois'),
														'IN' => ('Indiana'),
														'IA' => ('Iowa'),
														'KS' => ('Kansas'),
														'KY' => ('Kentucky'),
														'LA' => ('Louisiana'),
														'ME' => ('Maine'),
														'MD' => ('Maryland'),
														'MA' => ('Massachusetts'),
														'MI' => ('Michigan'),
														'MN' => ('Minnesota'),
														'MS' => ('Mississippi'),
														'MO' => ('Missouri'),
														'MT' => ('Montana'),
														'NE' => ('Nebraska'),
														'NV' => ('Nevada'),
														'NH' => ('New Hampshire'),
														'NM' => ('New Mexico'),
														'NJ' => ('New Jersey'),
														'NY' => ('New York'),
														'NC' => ('North Carolina'),
														'ND' => ('North Dakota'),
														'OH' => ('Ohio'),
														'OK' => ('Oklahoma'),
														'OR' => ('Oregon'),
														'PA' => ('Pennsylvania'),
														'RI' => ('Rhode Island'),
														'SC' => ('South Carolina'),
														'SD' => ('South Dakota'),
														'TN' => ('Tennessee'),
														'TX' => ('Texas'),
														'UT' => ('Utah'),
														'VT' => ('Vermont'),
														'VA' => ('Virginia'),
														'WA' => ('Washington'),
														'WV' => ('West Virginia'),
														'WI' => ('Wisconsin'),
														'WY' => ('Wyoming')
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
														'ACT'	=> ('Australian Capital Territory'),
														'NSW'	=> ('New South Wales'),
														'NT'	=> ('Northern Territory'),
														'QLD'	=> ('Queensland'),
														'SA'	=> ('South Australia'),
														'TAS'	=> ('Tasmania'),
														'VIC'	=> ('Victoria'),
														'WA'	=> ('Western Australia'),
													),
										'AT' => array( '00' => '--'),
										'AZ' => array( '00' => '--'),
										'BS' => array( '00' => '--'),
										'BH' => array( '00' => '--'),
										'BD' => array( '00' => '--'),
										'BB' => array(
														'00' => '--',
														'M' => ('St. Michael'),
														'X' => ('Christ Church'),
														'G' => ('St. George'),
														'J' => ('St. John'),
														'P' => ('St. Philip'),
														'O' => ('St. Joseph'),
														'L' => ('St. Lucy'),
														'S' => ('St. James'),
														'T' => ('St. Thomas'),
														'A' => ('St. Andrew'),
														'E' => ('St. Peter')
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
														'AM' => ('Amazonas'),
														'AN' => ('Antioquia'),
														'AR' => ('Arauca'),
														'AT' => ('Atlantico'),
														'BL' => ('Bolivar'),
														'BY' => ('Boyaca'),
														'CL' => ('Caldas'),
														'CQ' => ('Caqueta'),
														'CS' => ('Casanare'),
														'CA' => ('Cauca'),
														'CE' => ('Cesar'),
														'CH' => ('Choco'),
														'CO' => ('Cordoba'),
														'CU' => ('Cundinamarca'),
														'DC' => ('Distrito Capital'),
														'GN' => ('Guainia'),
														'GV' => ('Guaviare'),
														'HU' => ('Huila'),
														'LG' => ('La Guajira'),
														'MA' => ('Magdalena'),
														'ME' => ('Meta'),
														'NA' => ('Narino'),
														'NS' => ('Norte de Santander'),
														'PU' => ('Putumayo'),
														'QD' => ('Quindio'),
														'RI' => ('Risaralda'),
														'SA' => ('San Andres y Providencia'),
														'ST' => ('Santander'),
														'SU' => ('Sucre'),
														'TO' => ('Tolima'),
														'VC' => ('Valle del Cauca'),
														'VP' => ('Vaupes'),
														'VD' => ('Vichada'),
														),
										'KM' => array( '00' => '--'),
										'CG' => array( '00' => '--'),
										'CD' => array( '00' => '--'),
										'CK' => array( '00' => '--'),
										'CR' => array(
														'00' => '--',
														'AL' => ('Alajuela'),
														'CA' => ('Cartago'),
														'GU' => ('Guanacaste'),
														'HE' => ('Heredia'),
														'LI' => ('Limon'),
														'PU' => ('Puntarenas'),
														'SJ' => ('San Jose'),
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
														'AH' => ('Ahuachapan'),
														'CA' => ('Cabanas'),
														'CH' => ('Chalatenango'),
														'CU' => ('Cuscatlan'),
														'LI' => ('La Libertad'),
														'PA' => ('La Paz'),
														'UN' => ('La Union'),
														'MO' => ('Morazan'),
														'SM' => ('San Miguel'),
														'SS' => ('San Salvador'),
														'SA' => ('Santa Ana'),
														'SV' => ('San Vicente'),
														'SO' => ('Sonsonate'),
														'US' => ('Usulatan')
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
														'AV' => ('Alta Verapaz'),
														'BV' => ('Baja Verapaz'),
														'GT' => ('Chimaltenango'),
														'CQ' => ('Chiquimula'),
														'PR' => ('El Progreso'),
														'ES' => ('Escuintla'),
														'GU' => ('Guatemala'),
														'HU' => ('Huehuetenango'),
														'IZ' => ('Izaqbal'),
														'JA' => ('Jalapa'),
														'JU' => ('Jutiapa'),
														'PE' => ('Peten'),
														'QZ' => ('Quetzaltenango'),
														'QC' => ('Quiche'),
														'RE' => ('Retalhuleu'),
														'SA' => ('Sacatepequez'),
														'SM' => ('San Marcos'),
														'SR' => ('Santa Rosa'),
														'SO' => ('Solola'),
														'SU' => ('Suchitepequez'),
														'TO' => ('Totonicapan'),
														'ZA' => ('Zacapa')
														),
										'GN' => array( '00' => '--'),
										'GW' => array( '00' => '--'),
										'GY' => array( '00' => '--'),
										'HT' => array( '00' => '--'),
										'HM' => array( '00' => '--'),
										'VA' => array( '00' => '--'),
										'HN' => array(
														'00' => '--',
														'AT' => ('Atlantida'),
														'CH' => ('Choluteca'),
														'CL' => ('Colon'),
														'CM' => ('Comayagua'),
														'CP' => ('Copan'),
														'CR' => ('Cortes'),
														'EP' => ('El Paraiso'),
														'FM' => ('Francisco Morazan'),
														'GD' => ('Gracias a Dios'),
														'IN' => ('Intibuca'),
														'IB' => ('Islas de la Bahia'),
														'LP' => ('La Paz'),
														'LE' => ('Lempira'),
														'OC' => ('Ocotepeque'),
														'OL' => ('Olancho'),
														'SB' => ('Santa Barbara'),
														'VA' => ('Valle'),
														'YO' => ('Yoro'),
														),
										'HK' => array( '00' => '--'),
										'HU' => array( '00' => '--'),
										'IS' => array( '00' => '--'),
										'IN' => array(
														'00' => '--',
														'AN' => ('Andaman and Nicobar Islands'),
														'AP' => ('Andhra Pradesh'),
														'AR' => ('Arunachal Pradesh'),
														'AS' => ('Assam'),
														'BR' => ('Bihar'),
														'CH' => ('Chandigarh'),
														'CT' => ('Chhattisgarh'),
														'DN' => ('Dadra and Nagar Haveli'),
														'DD' => ('Daman and Diu'),
														'DL' => ('Delhi'),
														'GA' => ('Goa'),
														'GJ' => ('Gujarat'),
														'HR' => ('Haryana'),
														'HP' => ('Himachal Pradesh'),
														'JK' => ('Jammu and Kashmir'),
														'JH' => ('Jharkhand'),
														'KA' => ('Karnataka'),
														'KL' => ('Kerala'),
														'LD' => ('Lakshadweep'),
														'MP' => ('Madhya Pradesh'),
														'MH' => ('Maharashtra'),
														'MN' => ('Manipur'),
														'ML' => ('Meghalaya'),
														'MZ' => ('Mizoram'),
														'NL' => ('Nagaland'),
														'OR' => ('Orissa'),
														'PY' => ('Pondicherry'),
														'PB' => ('Punjab'),
														'RJ' => ('Rajasthan'),
														'SK' => ('Sikkim'),
														'TN' => ('Tamil Nadu'),
														'TR' => ('Tripura'),
														'UP' => ('Uttar Pradesh'),
														'UL' => ('Uttarakhand'),
														'WB' => ('West Bengal'),
														),
										'ID' => array(
														'00' => '--',
														'BA' => ('Bali'),
														'BB' => ('Bangka-Belitung'),
														'BT' => ('Banten'),
														'BE' => ('Bengkulu'),
														'JT' => ('Central Java'),
														'KT' => ('Central Kalimantan'),
														'ST' => ('Central Sulawesi'),
														'JI' => ('East Java'),
														'KI' => ('East Kalimantan'),
														'NT' => ('East Nusa Tenggara'),
														'GO' => ('Gorontalo'),
														'JA' => ('Jambi'),
														'JK' => ('Jakarta'),
														'LA' => ('Lampung'),
														'MA' => ('Maluku'),
														'MU' => ('North Maluku'),
														'SA' => ('North Sulawesi'),
														'SU' => ('North Sumatra'),
														'RI' => ('Riau'),
														'KR' => ('Riau Islands'),
														'SS' => ('South Sumatra'),
														'SN' => ('South Sulawesi'),
														'KS' => ('South Kalimantan'),
														'SG' => ('Southeast Sulawesi'),
														'JB' => ('West Java'),
														'KB' => ('West Kalimantan'),
														'NB' => ('West Nusa Tenggara'),
														'SR' => ('West Sulawesi'),
														'SB' => ('West Sumatra'),
														'YO' => ('Yogyakarta'),
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
														'AG' => ('Aguascalientes'),
														'BN' => ('Baja California'),
														'BS' => ('Baja California Sur'),
														'CM' => ('Campeche'),
														'CP' => ('Chiapas'),
														'CP' => ('Chihuahua'),
														'CA' => ('Coahuila'),
														'CL' => ('Colima'),
														'DF' => ('Distrito Federal'),
														'DU' => ('Durango'),
														'GJ' => ('Guanajuato'),
														'GR' => ('Guerrero'),
														'HI' => ('Hidalgo'),
														'JA' => ('Jalisco'),
														'MX' => ('Mexico'),
														'MC' => ('Michoacan'),
														'MR' => ('Morelos'),
														'NA' => ('Niyarit'),
														'NL' => ('Nuevo Leon'),
														'OA' => ('Oaxaca'),
														'PU' => ('Puebla'),
														'QE' => ('Queretaro'),
														'QR' => ('Quintana Roo'),
														'SL' => ('San Luis Potosi'),
														'SI' => ('Sinaloa'),
														'SO' => ('Sonora'),
														'TB' => ('Tabasco'),
														'TM' => ('Tamaulipas'),
														'TL' => ('Tlaxcala'),
														'VE' => ('Veracruz-Llave'),
														'YU' => ('Yucatan'),
														'ZA' => ('Zacatecas'),
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
														'BO' => ('Boaco'),
														'CA' => ('Carazo'),
														'CI' => ('Chinandega'),
														'CO' => ('Chontales'),
														'ES' => ('Esteli'),
														'GR' => ('Granada'),
														'JI' => ('Jinotega'),
														'LE' => ('Leon'),
														'MD' => ('Madriz'),
														'MN' => ('Managua'),
														'MS' => ('Masaya'),
														'MT' => ('Matagalpa'),
														'NS' => ('Nueva Segovia'),
														'SJ' => ('Rio San Juan'),
														'RI' => ('Rivas'),
														'AN' => ('Region Autonoma Atlantico Norte'),
														'AS' => ('Region Autonoma Atlantico Sur'),
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
														'BC' => ('Bocas del Toro'),
														'CH' => ('Chiriqui'),
														'CC' => ('Cocle'),
														'CL' => ('Colon'),
														'DR' => ('Darien'),
														'HE' => ('Herrera'),
														'LS' => ('Los Santos'),
														'PN' => ('Panama'),
														'SB' => ('San Blas'),
														'VR' => ('Veraguas'),
														),
										'PG' => array( '00' => '--'),
										'PY' => array( '00' => '--'),
										'PE' => array( '00' => '--'),
										'PH' => array(
														'00' => '--',
														'AB' => ('Abra'),
														'AN' => ('Agusan del Norte'),
														'AS' => ('Agusan del Sur'),
														'AK' => ('Aklan'),
														'AL' => ('Albay'),
														'AQ' => ('Antique'),
														'AP' => ('Apayao'),
														'AU' => ('Aurora'),
														'BS' => ('Basilan'),
														'BA' => ('Bataan'),
														'BN' => ('Batanes'),
														'BT' => ('Batangas'),
														'BG' => ('Benguet'),
														'BI' => ('Biliran'),
														'BO' => ('Bohol'),
														'BK' => ('Bukidnon'),
														'BU' => ('Bulacan'),
														'CG' => ('Cagayan'),
														'CN' => ('Camarines Norte'),
														'CS' => ('Camarines Sur'),
														'CM' => ('Camiguin'),
														'CP' => ('Capiz'),
														'CT' => ('Catanduanes'),
														'CV' => ('Cavite'),
														'CB' => ('Cebu'),
														'CL' => ('Compostela Valley'),
														'NC' => ('Cotabato'),
														'DV' => ('Davao del Norte'),
														'DS' => ('Davao del Sur'),
														'DO' => ('Davao Oriental'),
														'DI' => ('Dinagat Islands'),
														'ES' => ('Eastern Samar'),
														'GU' => ('Guimaras'),
														'IF' => ('Ifugao'),
														'IN' => ('Ilocos Norte'),
														'IS' => ('Ilocos Sur'),
														'II' => ('Iloilo'),
														'IB' => ('Isabela'),
														'KA' => ('Kalinga'),
														'LG' => ('Laguna'),
														'LN' => ('Lanao del Norte'),
														'LS' => ('Lanao del Sur'),
														'LU' => ('La Union'),
														'LE' => ('Leyte'),
														'MG' => ('Maguindanao'),
														'MQ' => ('Marinduque'),
														'MB' => ('Masbate'),
														'MM' => ('Metropolitan Manila'),
														'MD' => ('Misamis Occidental'),
														'MN' => ('Misamis Oriental'),
														'MT' => ('Mountain'),
														'ND' => ('Negros Occidental'),
														'NR' => ('Negros Oriental'),
														'NS' => ('Northern Samar'),
														'NE' => ('Nueva Ecija'),
														'NV' => ('Nueva Vizcaya'),
														'MC' => ('Occidental Mindoro'),
														'MR' => ('Oriental Mindoro'),
														'PL' => ('Palawan'),
														'PM' => ('Pampanga'),
														'PN' => ('Pangasinan'),
														'QZ' => ('Quezon'),
														'QR' => ('Quirino'),
														'RI' => ('Rizal'),
														'RO' => ('Romblon'),
														'SM' => ('Samar'),
														'SG' => ('Sarangani'),
														'SQ' => ('Siquijor'),
														'SR' => ('Sorsogon'),
														'SC' => ('South Cotabato'),
														'SL' => ('Southern Leyte'),
														'SK' => ('Sultan Kudarat'),
														'SU' => ('Sulu'),
														'ST' => ('Surigao del Norte'),
														'SS' => ('Surigao del Sur'),
														'TR' => ('Tarlac'),
														'TT' => ('Tawi-Tawi'),
														'ZM' => ('Zambales'),
														'ZN' => ('Zamboanga del Norte'),
														'ZS' => ('Zamboanga del Sur'),
														'ZY' => ('Zamboanga-Sibugay'),
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
														'MP' => ('Mpumalanga'),
														'GP' => ('Gauteng'),
														'NW' => ('North West'),
														'LP' => ('Limpopo'),
														'FS' => ('Free State'),
														'WC' => ('Western Cape'),
														'ZN' => ('Kwa-Zulu Natal'),
														'EC' => ('Eastern Cape'),
														'NC' => ('Northern Cape'),
														),
										'GS' => array( '00' => '--'),
										'ES' => array( '00' => '--'),
										'LK' => array(
                                                        '00' => ('--'),
                                                        'NC' => ('North Central'),
                                                        'NE' => ('North Eastern'),
                                                        'NW' => ('North Western'),
                                                        'CE' => ('Central'),
                                                        'EA' => ('Eastern'),
                                                        'SA' => ('Southern'),
                                                        'WE' => ('Western'),
                                                        'UV' => ('Uva'),
                                                        'SB' => ('Sabaragamuwa'),
                                                    ),
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
													'AL' => array( '00' => ('--Other--') ),
													'AK' => array( '00' => ('--Other--') ),
													'AZ' => array( '00' => ('--Other--') ),
													'AR' => array( '00' => ('--Other--') ),
													'CA' => array( '00' => ('--Other--') ),
													'CO' => array( '00' => ('--Other--') ),
													'CT' => array( '00' => ('--Other--') ),
													'DE' => array( '00' => ('--Other--') ),
													'DC' => array( '00' => ('--Other--') ),
													'FL' => array( '00' => ('--Other--') ),
													'GA' => array( '00' => ('--Other--') ),
													'HI' => array( '00' => ('--Other--') ),
													'ID' => array( '00' => ('--Other--') ),
													'IL' => array( '00' => ('--Other--') ),
													'IN' => array( 'ALL' => ('--Other--') ),
													'IA' => array( '00' => ('--Other--') ),
													'KS' => array( '00' => ('--Other--') ),
													'KY' => array( '00' => ('--Other--') ),
													'LA' => array( '00' => ('--Other--') ),
													'ME' => array( '00' => ('--Other--') ),
													'MD' => array( 'ALL' => ('--Other--') ),
													'MA' => array( '00' => ('--Other--') ),
													'MI' => array( '00' => ('--Other--') ),
													'MN' => array( '00' => ('--Other--') ),
													'MS' => array( '00' => ('--Other--') ),
													'MO' => array( '00' => ('--Other--') ),
													'MT' => array( '00' => ('--Other--') ),
													'NE' => array( '00' => ('--Other--') ),
													'NV' => array( '00' => ('--Other--') ),
													'NH' => array( '00' => ('--Other--') ),
													'NM' => array( '00' => ('--Other--') ),
													'NJ' => array( '00' => ('--Other--') ),
													'NY' => array(
																'NYC' => ('New York City'),
																'Yonkers' => ('Yonkers')
															),
													'NC' => array( '00' => ('--Other--') ),
													'ND' => array( '00' => ('--Other--') ),
													'OH' => array( '00' => ('--Other--') ),
													'OK' => array( '00' => ('--Other--') ),
													'OR' => array( '00' => ('--Other--') ),
													'PA' => array( '00' => ('--Other--') ),
													'RI' => array( '00' => ('--Other--') ),
													'SC' => array( '00' => ('--Other--') ),
													'SD' => array( '00' => ('--Other--') ),
													'TN' => array( '00' => ('--Other--') ),
													'TX' => array( '00' => ('--Other--') ),
													'UT' => array( '00' => ('--Other--') ),
													'VT' => array( '00' => ('--Other--') ),
													'VA' => array( '00' => ('--Other--') ),
													'WA' => array( '00' => ('--Other--') ),
													'WV' => array( '00' => ('--Other--') ),
													'WI' => array( '00' => ('--Other--') ),
													'WY' => array( '00' => ('--Other--') ),
													),
										);
				break;
			case 'industry':
				//2007 NAICS
				$retval = array(
										0  => ('- Please Choose -'),
										72  => ('Accommodation and Food Services'),
										561 => ('Administrative and Support Services'),
										11  => ('Agriculture, Forestry, Fishing and Hunting'),
										71  => ('Arts, Entertainment and Recreation'),
										23  => ('Construction'),
										518 => ('Data Processing, Hosting and Related Services'),
										61  => ('Educational Services'),
										52  => ('Finance and Insurance'),
										91  => ('Government/Public Administration'),
										62  => ('Health Care and Social Assistance'),
										51  => ('Information and Cultural Industries'),
										544  => ('Information Technology Software'),
										55  => ('Management of Companies and Enterprises'),
										31  => ('Manufacturing'),
										21  => ('Mining and Oil and Gas Extraction'),
										512 => ('Motion Picture and Sound Recording Industries'),
										54  => ('Professional, Scientific and Technical Services'),
										511 => ('Publishing Industries (except Internet'),
										53  => ('Real Estate and Rental and Leasing'),
										44  => ('Retail Trade'),
										517 => ('Telecommunications'),
										48  => ('Transportation and Warehousing'),
										22  => ('Utilities'),
										562 => ('Waste Management and Remediation Services'),
										41  => ('Wholesale Trade'),
										99  => ('Other'),
									);
				break;
			case 'password_policy_type':
				$retval = array(
										0 => ('Disabled'),
										1 => ('Enabled'),
									);
				break;
			case 'password_minimum_strength':
				$retval = array(
										1 => ('Low'), //1-2 is low
										3 => ('Medium'), //3-4 is medium
										5 => ('High'), //5+ is high
									);
				break;
			case 'password_minimum_permission_level':
				$pcf = new PermissionControlFactory();
				$retval = $pcf->getOptions('level');
				break;
			case 'ldap_authentication_type':
				$retval = array(
										0 => ('Disabled'),
										1 => ('Enabled - w/Local Fallback'),
										2 => ('Enabled - LDAP Only')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => ('Status'),
										'-1020-product_edition' => ('Product Edition'),
										'-1030-name' => ('Name'),
										'-1040-short_name' => ('Short Name'),
										'-1050-business_number' => ('Business Number'),

										'-1140-address1' => ('Address 1'),
										'-1150-address2' => ('Address 2'),
										'-1160-city' => ('City'),
										'-1170-province' => ('Province/State'),
										'-1180-country' => ('Country'),
										'-1190-postal_code' => ('Postal Code'),
										'-1200-work_phone' => ('Work Phone'),
										'-1210-fax_phone' => ('Fax Phone'),

										'-2000-created_by' => ('Created By'),
										'-2010-created_date' => ('Created Date'),
										'-2020-updated_by' => ('Updated By'),
										'-2030-updated_date' => ('Updated Date'),
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
															('Parent Company is invalid')
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
											('Incorrect Status'),
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
											('Incorrect Product Edition'),
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
                                                                        ('Business Number is too short or too long'),
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
												('Name is too short or too long'),
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
													('Incorrect Industry'),
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
												('Business Number is too short or too long'),
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
												('Originator ID is too short or too long'),
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
												('Data Center ID is too short or too long'),
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
												('Short name is too short or too long'),
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
												('Address1 contains invalid characters'),
												$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address1',
													$address1,
													('Address1 is too short or too long'),
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
													('Address2 contains invalid characters'),
													$this->address_validator_regex)
				AND
					$this->Validator->isLength(		'address2',
													$address2,
													('Address2 is too short or too long'),
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
												('City contains invalid characters'),
												$this->city_validator_regex)
				AND
					$this->Validator->isLength(		'city',
													$city,
													('City name is too short or too long'),
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
												('Invalid Country'),
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
												('Invalid Province/State'),
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
													('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
													$this->getCountry(), $this->getProvince() )
				AND
					$this->Validator->isLength(		'postal_code',
													$postal_code,
													('Postal/ZIP Code is too short or too long'),
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
											('Longitude is invalid')
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
											('Latitude is invalid')
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
														('Work phone number is invalid')) ) {

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
														('Fax phone number is invalid')) ) {

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
																('Contact User is invalid')
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
															('Contact User is invalid')
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
															('Contact User is invalid')
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
											('Other ID 1 is invalid'),
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
											('Other ID 2 is invalid'),
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
											('Other ID 3 is invalid'),
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
											('Other ID 4 is invalid'),
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
											('Other ID 5 is invalid'),
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
		// $ttsc = new TimeTrexSoapClient();

		// if ( ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL OR $ttsc->isUpdateNotifyEnabled() == TRUE )
		// 		AND PRODUCTION == TRUE
		// 		AND DEMO_MODE == FALSE ) {
		// 	$ttsc->sendCompanyData( $this->getId() );
		// 	$ttsc->sendCompanyVersionData( $this->getId() );

		// 	return TRUE;
		// }

		// return FALSE;
		return TRUE;
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
											('Incorrect Password Policy type'),
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
											('Incorrect minimum permission level'),
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
											('Invalid password strength'),
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
											('Password minimum length must only be digits'))
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
													('Minimum age must only be digits'))

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
												('Maximum age must only be digits'))
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
											('Incorrect LDAP authentication type'),
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
												('LDAP server host name is too short or too long'),
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
													('LDAP port must only be digits'))
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
												('LDAP bind user name is too long'),
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
												('LDAP bind password is too long'),
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
												('LDAP base DN is too long'),
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
												('LDAP bind attribute is too long'),
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
												('LDAP user filter is too long'),
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
												('LDAP login attribute is too long'),
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
											('Unable to delete the primary company'));
		}
        /*
        $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65";
        $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65";
        $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65";
        $obj = new $obj_class;
        $retval = $obj->{$obj_function}();
        if ( $retval !== TRUE ) {
            $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) );
        }
        */
		return TRUE;
	}

		//Don't allow the primary company to be deleted.
	// 	if ( $this->getDeleted() == TRUE
	// 			AND isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] == $this->getID() ) {
	// 		$this->Validator->isTrue(		'company',
	// 										FALSE,
	// 										('Unable to delete the primary company'));
	// 	}
	// 																																											$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); }
	// 	return TRUE;
	// }



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
				foreach( $ulf->rs as $u_obj ) {
					$ulf->data = (array)$u_obj;
					Debug::text('Deleting User ID: '. $ulf->getId() , __FILE__, __LINE__, __METHOD__,9);
					$ulf->setDeleted( TRUE );
					if ( $ulf->isValid() ) {
						$ulf->Save();
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
		return TTLog::addEntry( $this->getId(), $log_action, ('Company Information'), NULL, $this->getTable(), $this );
	}

}
?>
