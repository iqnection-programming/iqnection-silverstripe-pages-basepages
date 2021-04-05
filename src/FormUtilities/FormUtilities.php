<?php


namespace IQnection\FormUtilities;

use SilverStripe\Forms;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Configurable;

class FormUtilities
{
	use Configurable;

	private static $allowed_referrers = [];

	public static function RequiredFields(&$fields,$requiredFields)
	{
		foreach($requiredFields as $requiredField)
		{
			if ($field = $fields->dataFieldByName($requiredField))
			{
				$field->addExtraClass('required');
				$fields->replaceField($requiredField, $field);
			}
		}
		return Forms\RequiredFields::create($requiredFields);
	}

	public static function SendSSEmail($page=false, $EmailFormTo=false, $post_vars=false,$submission=null,$FromEmail=null, $subject = null)
	{
		$arr_path = explode(".", $_SERVER['HTTP_HOST']);
		$suffix = array_pop($arr_path);
		$domain = array_pop($arr_path).'.'.$suffix;

        if (!$subject)
        {
            $subject = $page->Title." form submission";
        }

		$email = Email::create()
			->setFrom(($FromEmail) ? $FromEmail : "forms@".$domain)
			->setSubject($subject);

		foreach(explode(',',$EmailFormTo) as $to)
		{
			$email->addTo($to);
		}

		$email_body = "<html><body>This is a form submission created by this page on your website:<br /><br />".$_SERVER['HTTP_REFERER']."<br /><br />";
		$email_body .= self::FormDataToArray($post_vars,null,null,$submission);
		$email_body .= "</body></html>";

		$email->setBody($email_body);
		$email->setReplyTo($post_vars['Email']);
		$email->send();
	}

	public static function SendAutoResponder($subject=false,$body=false,$EmailFormTo=false,$FromEmail=null,$submission=null,$post_vars=array(),$includeSubmission=false)
	{
		$arr_path = explode(".", $_SERVER['HTTP_HOST']);
		$suffix = array_pop($arr_path);
		$domain = array_pop($arr_path).'.'.$suffix;

		$email = Email::create()
			->setFrom(($FromEmail) ? $FromEmail : "forms@".$domain)
			->setSubject($subject);
		foreach(explode(',',$EmailFormTo) as $to)
		{
			$email->addTo($to);
		}

		$email_body = "<html><body>";
		$email_body .= $body;
		if ($submission && $includeSubmission)
		{
			$email_body .= '<p>&nbsp;</p><p>&nbsp;</p>';
			$email_body .= self::FormDataToArray($post_vars,null,null,$submission);
		}
		$email_body .= "</body></html>";

		$email->setBody($email_body);
		$email->send();
	}

	public static function FormDataToArray($data, $level=0, $hide_empty=0,$submission)
	{
		$ignore_keys = array(
			"MAX_FILE_SIZE",
			"SecurityID",
			"nospam_codes",
			"url",
			"action_SubmitForm"
		);

		$html = "";
		foreach ($data as $fieldName => $v)
		{
			if(!($hide_empty && !$v) && !(in_array($fieldName,$ignore_keys)))
			{
				$name = trim(preg_replace("/([A-Z]{1}[a-z]{1})/", " \\1", $fieldName));
				$html .= "<br />".str_repeat("&nbsp;", ($level * 4)).'<span style="font-weight:bold;">'.htmlspecialchars($name).': </span>';

				//if (is_array($v))
				//	$html .= self::FormDataToArray($v, $level+1,$hide_empty,$submission);
				//else
				if (!$fieldObject = $submission->obj($fieldName))
				{
					$fieldObject = $submission->has_one($fieldName);
				}
				if ($fieldObject instanceof \SilverStripe\Assets\File)
				{
					$html .= '<a href="'.$fieldObject->getAbsoluteURL().'">'.$fieldObject->getFilename().'</a>';
				}
				else
				{
					$html .= $fieldObject;
				}
			}
		}
		return $html;
	}

	public static function GetCountryName($val)
	{
		if ($val)
		{
			foreach (self::GetCountriesByContinent() as $continent => $countries)
			{
				foreach ($countries as $code => $name)
				{
					if ($code == $val) return $name;
				}
			}
		}
		return "";
	}

	public static function GetCountriesByContinent()
	{
		return array(
			"Africa" => array(
				"DZ" => "Algeria",
				"AO" => "Angola",
				"BJ" => "Benin",
				"BW" => "Botswana",
				"BF" => "Burkina Faso",
				"BI" => "Burundi",
				"CM" => "Cameroon",
				"CV" => "Cape Verde",
				"CF" => "Central African Republic",
				"TD" => "Chad",
				"KM" => "Comoros",
				"CG" => "Congo - Brazzaville",
				"CD" => "Congo - Kinshasa",
				"CI" => "Côte d'Ivoire",
				"DJ" => "Djibouti",
				"EG" => "Egypt",
				"GQ" => "Equatorial Guinea",
				"ER" => "Eritrea",
				"ET" => "Ethiopia",
				"GA" => "Gabon",
				"GM" => "Gambia",
				"GH" => "Ghana",
				"GN" => "Guinea",
				"GW" => "Guinea-Bissau",
				"KE" => "Kenya",
				"LS" => "Lesotho",
				"LR" => "Liberia",
				"LY" => "Libya",
				"MG" => "Madagascar",
				"MW" => "Malawi",
				"ML" => "Mali",
				"MR" => "Mauritania",
				"MU" => "Mauritius",
				"YT" => "Mayotte",
				"MA" => "Morocco",
				"MZ" => "Mozambique",
				"NA" => "Namibia",
				"NE" => "Niger",
				"NG" => "Nigeria",
				"RW" => "Rwanda",
				"RE" => "Réunion",
				"SH" => "Saint Helena",
				"SN" => "Senegal",
				"SC" => "Seychelles",

				"SL" => "Sierra Leone",
				"SO" => "Somalia",
				"ZA" => "South Africa",
				"SD" => "Sudan",
				"SZ" => "Swaziland",
				"ST" => "São Tomé and Príncipe",
				"TZ" => "Tanzania",
				"TG" => "Togo",
				"TN" => "Tunisia",
				"UG" => "Uganda",
				"EH" => "Western Sahara",
				"ZM" => "Zambia",
				"ZW" => "Zimbabwe",
			),
			"Americas" => array(
				"AI" => "Anguilla",
				"AG" => "Antigua and Barbuda",
				"AR" => "Argentina",
				"AW" => "Aruba",
				"BS" => "Bahamas",
				"BB" => "Barbados",
				"BZ" => "Belize",
				"BM" => "Bermuda",
				"BO" => "Bolivia",
				"BR" => "Brazil",
				"VG" => "British Virgin Islands",
				"CA" => "Canada",
				"KY" => "Cayman Islands",
				"CL" => "Chile",
				"CO" => "Colombia",
				"CR" => "Costa Rica",
				"CU" => "Cuba",
				"DM" => "Dominica",
				"DO" => "Dominican Republic",
				"EC" => "Ecuador",
				"SV" => "El Salvador",
				"FK" => "Falkland Islands",
				"GF" => "French Guiana",
				"GL" => "Greenland",
				"GD" => "Grenada",
				"GP" => "Guadeloupe",
				"GT" => "Guatemala",
				"GY" => "Guyana",
				"HT" => "Haiti",
				"HN" => "Honduras",
				"JM" => "Jamaica",
				"MQ" => "Martinique",
				"MX" => "Mexico",
				"MS" => "Montserrat",
				"AN" => "Netherlands Antilles",
				"NI" => "Nicaragua",
				"PA" => "Panama",
				"PY" => "Paraguay",
				"PE" => "Peru",
				"PR" => "Puerto Rico",
				"BL" => "Saint Barthélemy",
				"KN" => "Saint Kitts and Nevis",
				"LC" => "Saint Lucia",
				"MF" => "Saint Martin",
				"PM" => "Saint Pierre and Miquelon",
				"VC" => "Saint Vincent and the Grenadines",
				"SR" => "Suriname",
				"TT" => "Trinidad and Tobago",
				"TC" => "Turks and Caicos Islands",
				"VI" => "U.S. Virgin Islands",
				"US" => "United States",
				"UY" => "Uruguay",
				"VE" => "Venezuela",
			),
			"Asia" => array(
				"AF" => "Afghanistan",
				"AM" => "Armenia",
				"AZ" => "Azerbaijan",
				"BH" => "Bahrain",
				"BD" => "Bangladesh",
				"BT" => "Bhutan",
				"BN" => "Brunei",
				"KH" => "Cambodia",
				"CN" => "China",
				"CY" => "Cyprus",
				"GE" => "Georgia",
				"HK" => "Hong Kong SAR China",
				"IN" => "India",
				"ID" => "Indonesia",
				"IR" => "Iran",
				"IQ" => "Iraq",
				"IL" => "Israel",
				"JP" => "Japan",
				"JO" => "Jordan",
				"KZ" => "Kazakhstan",
				"KW" => "Kuwait",
				"KG" => "Kyrgyzstan",
				"LA" => "Laos",
				"LB" => "Lebanon",
				"MO" => "Macau SAR China",
				"MY" => "Malaysia",
				"MV" => "Maldives",
				"MN" => "Mongolia",
				"MM" => "Myanmar [Burma]",
				"NP" => "Nepal",
				"NT" => "Neutral Zone",
				"KP" => "North Korea",
				"OM" => "Oman",
				"PK" => "Pakistan",
				"PS" => "Palestinian Territories",
				"YD" => "People's Democratic Republic of Yemen",
				"PH" => "Philippines",
				"QA" => "Qatar",
				"SA" => "Saudi Arabia",
				"SG" => "Singapore",
				"KR" => "South Korea",
				"LK" => "Sri Lanka",
				"SY" => "Syria",
				"TW" => "Taiwan",
				"TJ" => "Tajikistan",
				"TH" => "Thailand",
				"TL" => "Timor-Leste",
				"TR" => "Turkey",
				"TM" => "Turkmenistan",
				"AE" => "United Arab Emirates",
				"UZ" => "Uzbekistan",
				"VN" => "Vietnam",
				"YE" => "Yemen",
			),
			"Europe" => array(
				"AL" => "Albania",

				"AD" => "Andorra",
				"AT" => "Austria",
				"BY" => "Belarus",
				"BE" => "Belgium",
				"BA" => "Bosnia and Herzegovina",
				"BG" => "Bulgaria",
				"HR" => "Croatia",
				"CY" => "Cyprus",
				"CZ" => "Czech Republic",
				"DK" => "Denmark",
				"DD" => "East Germany",
				"EE" => "Estonia",
				"FO" => "Faroe Islands",
				"FI" => "Finland",
				"FR" => "France",
				"DE" => "Germany",
				"GI" => "Gibraltar",
				"GR" => "Greece",
				"GG" => "Guernsey",
				"HU" => "Hungary",
				"IS" => "Iceland",
				"IE" => "Ireland",
				"IM" => "Isle of Man",
				"IT" => "Italy",
				"JE" => "Jersey",
				"LV" => "Latvia",
				"LI" => "Liechtenstein",
				"LT" => "Lithuania",
				"LU" => "Luxembourg",
				"MK" => "Macedonia",
				"MT" => "Malta",
				"FX" => "Metropolitan France",
				"MD" => "Moldova",
				"MC" => "Monaco",
				"ME" => "Montenegro",
				"NL" => "Netherlands",
				"NO" => "Norway",
				"PL" => "Poland",
				"PT" => "Portugal",
				"RO" => "Romania",
				"RU" => "Russia",
				"SM" => "San Marino",
				"RS" => "Serbia",
				"CS" => "Serbia and Montenegro",
				"SK" => "Slovakia",
				"SI" => "Slovenia",
				"ES" => "Spain",
				"SJ" => "Svalbard and Jan Mayen",
				"SE" => "Sweden",
				"CH" => "Switzerland",
				"UA" => "Ukraine",
				"SU" => "Union of Soviet Socialist Republics",
				"GB" => "United Kingdom",
				"VA" => "Vatican City",
				"AX" => "Åland Islands",
			),
			"Oceania" => array(
				"AS" => "American Samoa",
				"AQ" => "Antarctica",
				"AU" => "Australia",
				"BV" => "Bouvet Island",
				"IO" => "British Indian Ocean Territory",
				"CX" => "Christmas Island",
				"CC" => "Cocos [Keeling] Islands",
				"CK" => "Cook Islands",
				"FJ" => "Fiji",
				"PF" => "French Polynesia",
				"TF" => "French Southern Territories",
				"GU" => "Guam",
				"HM" => "Heard Island and McDonald Islands",
				"KI" => "Kiribati",
				"MH" => "Marshall Islands",
				"FM" => "Micronesia",
				"NR" => "Nauru",
				"NC" => "New Caledonia",
				"NZ" => "New Zealand",
				"NU" => "Niue",
				"NF" => "Norfolk Island",
				"MP" => "Northern Mariana Islands",
				"PW" => "Palau",
				"PG" => "Papua New Guinea",
				"PN" => "Pitcairn Islands",
				"WS" => "Samoa",
				"SB" => "Solomon Islands",
				"GS" => "South Georgia and the South Sandwich Islands",
				"TK" => "Tokelau",
				"TO" => "Tonga",
				"TV" => "Tuvalu",
				"UM" => "U.S. Minor Outlying Islands",
				"VU" => "Vanuatu",
				"WF" => "Wallis and Futuna",
			),
		);
	}

	public static function GetCountries()
	{
		return array(
		  "US" => "United States",
		  "GB" => "United Kingdom",
		  "AF" => "Afghanistan",
		  "AL" => "Albania",
		  "DZ" => "Algeria",
		  "AS" => "American Samoa",
		  "AD" => "Andorra",
		  "AO" => "Angola",
		  "AI" => "Anguilla",
		  "AQ" => "Antarctica",
		  "AG" => "Antigua And Barbuda",
		  "AR" => "Argentina",
		  "AM" => "Armenia",
		  "AW" => "Aruba",
		  "AU" => "Australia",
		  "AT" => "Austria",
		  "AZ" => "Azerbaijan",
		  "BS" => "Bahamas",
		  "BH" => "Bahrain",
		  "BD" => "Bangladesh",
		  "BB" => "Barbados",
		  "BY" => "Belarus",
		  "BE" => "Belgium",
		  "BZ" => "Belize",
		  "BJ" => "Benin",
		  "BM" => "Bermuda",
		  "BT" => "Bhutan",
		  "BO" => "Bolivia",
		  "BA" => "Bosnia And Herzegowina",
		  "BW" => "Botswana",
		  "BV" => "Bouvet Island",
		  "BR" => "Brazil",
		  "IO" => "British Indian Ocean Territory",
		  "BN" => "Brunei Darussalam",
		  "BG" => "Bulgaria",
		  "BF" => "Burkina Faso",
		  "BI" => "Burundi",
		  "KH" => "Cambodia",
		  "CM" => "Cameroon",
		  "CA" => "Canada",
		  "CV" => "Cape Verde",
		  "KY" => "Cayman Islands",
		  "CF" => "Central African Republic",
		  "TD" => "Chad",
		  "CL" => "Chile",
		  "CN" => "China",
		  "CX" => "Christmas Island",
		  "CC" => "Cocos (Keeling) Islands",
		  "CO" => "Colombia",
		  "KM" => "Comoros",
		  "CG" => "Congo",
		  "CD" => "Congo, The Democratic Republic Of The",
		  "CK" => "Cook Islands",
		  "CR" => "Costa Rica",
		  "CI" => "Cote D'Ivoire",
		  "HR" => "Croatia (Local Name: Hrvatska)",
		  "CU" => "Cuba",
		  "CY" => "Cyprus",
		  "CZ" => "Czech Republic",
		  "DK" => "Denmark",
		  "DJ" => "Djibouti",
		  "DM" => "Dominica",
		  "DO" => "Dominican Republic",
		  "TP" => "East Timor",
		  "EC" => "Ecuador",
		  "EG" => "Egypt",
		  "SV" => "El Salvador",
		  "GQ" => "Equatorial Guinea",
		  "ER" => "Eritrea",
		  "EE" => "Estonia",
		  "ET" => "Ethiopia",
		  "FK" => "Falkland Islands (Malvinas)",
		  "FO" => "Faroe Islands",
		  "FJ" => "Fiji",
		  "FI" => "Finland",
		  "FR" => "France",
		  "FX" => "France, Metropolitan",
		  "GF" => "French Guiana",
		  "PF" => "French Polynesia",
		  "TF" => "French Southern Territories",
		  "GA" => "Gabon",
		  "GM" => "Gambia",
		  "GE" => "Georgia",
		  "DE" => "Germany",
		  "GH" => "Ghana",
		  "GI" => "Gibraltar",
		  "GR" => "Greece",
		  "GL" => "Greenland",
		  "GD" => "Grenada",
		  "GP" => "Guadeloupe",
		  "GU" => "Guam",
		  "GT" => "Guatemala",
		  "GN" => "Guinea",
		  "GW" => "Guinea-Bissau",
		  "GY" => "Guyana",
		  "HT" => "Haiti",
		  "HM" => "Heard And Mc Donald Islands",
		  "VA" => "Holy See (Vatican City State)",
		  "HN" => "Honduras",
		  "HK" => "Hong Kong",
		  "HU" => "Hungary",
		  "IS" => "Iceland",
		  "IN" => "India",
		  "ID" => "Indonesia",
		  "IR" => "Iran (Islamic Republic Of)",
		  "IQ" => "Iraq",
		  "IE" => "Ireland",
		  "IL" => "Israel",
		  "IT" => "Italy",
		  "JM" => "Jamaica",
		  "JP" => "Japan",
		  "JO" => "Jordan",
		  "KZ" => "Kazakhstan",
		  "KE" => "Kenya",
		  "KI" => "Kiribati",
		  "KP" => "Korea, Democratic People's Republic Of",
		  "KR" => "Korea, Republic Of",
		  "KW" => "Kuwait",
		  "KG" => "Kyrgyzstan",
		  "LA" => "Lao People's Democratic Republic",
		  "LV" => "Latvia",
		  "LB" => "Lebanon",
		  "LS" => "Lesotho",
		  "LR" => "Liberia",
		  "LY" => "Libyan Arab Jamahiriya",
		  "LI" => "Liechtenstein",
		  "LT" => "Lithuania",
		  "LU" => "Luxembourg",
		  "MO" => "Macau",
		  "MK" => "Macedonia, Former Yugoslav Republic Of",
		  "MG" => "Madagascar",
		  "MW" => "Malawi",
		  "MY" => "Malaysia",
		  "MV" => "Maldives",
		  "ML" => "Mali",
		  "MT" => "Malta",
		  "MH" => "Marshall Islands",
		  "MQ" => "Martinique",
		  "MR" => "Mauritania",
		  "MU" => "Mauritius",
		  "YT" => "Mayotte",
		  "MX" => "Mexico",
		  "FM" => "Micronesia, Federated States Of",
		  "MD" => "Moldova, Republic Of",
		  "MC" => "Monaco",
		  "MN" => "Mongolia",
		  "MS" => "Montserrat",
		  "MA" => "Morocco",
		  "MZ" => "Mozambique",
		  "MM" => "Myanmar",
		  "NA" => "Namibia",
		  "NR" => "Nauru",
		  "NP" => "Nepal",
		  "NL" => "Netherlands",
		  "AN" => "Netherlands Antilles",
		  "NC" => "New Caledonia",
		  "NZ" => "New Zealand",
		  "NI" => "Nicaragua",
		  "NE" => "Niger",
		  "NG" => "Nigeria",
		  "NU" => "Niue",
		  "NF" => "Norfolk Island",
		  "MP" => "Northern Mariana Islands",
		  "NO" => "Norway",
		  "OM" => "Oman",
		  "PK" => "Pakistan",
		  "PW" => "Palau",
		  "PA" => "Panama",
		  "PG" => "Papua New Guinea",
		  "PY" => "Paraguay",
		  "PE" => "Peru",
		  "PH" => "Philippines",
		  "PN" => "Pitcairn",
		  "PL" => "Poland",
		  "PT" => "Portugal",
		  "PR" => "Puerto Rico",
		  "QA" => "Qatar",
		  "RE" => "Reunion",
		  "RO" => "Romania",
		  "RU" => "Russian Federation",
		  "RW" => "Rwanda",
		  "KN" => "Saint Kitts And Nevis",
		  "LC" => "Saint Lucia",
		  "VC" => "Saint Vincent And The Grenadines",
		  "WS" => "Samoa",
		  "SM" => "San Marino",
		  "ST" => "Sao Tome And Principe",
		  "SA" => "Saudi Arabia",
		  "SN" => "Senegal",
		  "SC" => "Seychelles",
		  "SL" => "Sierra Leone",
		  "SG" => "Singapore",
		  "SK" => "Slovakia (Slovak Republic)",
		  "SI" => "Slovenia",
		  "SB" => "Solomon Islands",
		  "SO" => "Somalia",
		  "ZA" => "South Africa",
		  "GS" => "South Georgia, South Sandwich Islands",
		  "ES" => "Spain",
		  "LK" => "Sri Lanka",
		  "SH" => "St. Helena",
		  "PM" => "St. Pierre And Miquelon",
		  "SD" => "Sudan",
		  "SR" => "Suriname",
		  "SJ" => "Svalbard And Jan Mayen Islands",
		  "SZ" => "Swaziland",
		  "SE" => "Sweden",
		  "CH" => "Switzerland",
		  "SY" => "Syrian Arab Republic",
		  "TW" => "Taiwan",
		  "TJ" => "Tajikistan",
		  "TZ" => "Tanzania, United Republic Of",
		  "TH" => "Thailand",
		  "TG" => "Togo",
		  "TK" => "Tokelau",
		  "TO" => "Tonga",
		  "TT" => "Trinidad And Tobago",
		  "TN" => "Tunisia",
		  "TR" => "Turkey",
		  "TM" => "Turkmenistan",
		  "TC" => "Turks And Caicos Islands",
		  "TV" => "Tuvalu",
		  "UG" => "Uganda",
		  "UA" => "Ukraine",
		  "AE" => "United Arab Emirates",
		  "UM" => "United States Minor Outlying Islands",
		  "UY" => "Uruguay",
		  "UZ" => "Uzbekistan",
		  "VU" => "Vanuatu",
		  "VE" => "Venezuela",
		  "VN" => "Viet Nam",
		  "VG" => "Virgin Islands (British)",
		  "VI" => "Virgin Islands (U.S.)",
		  "WF" => "Wallis And Futuna Islands",
		  "EH" => "Western Sahara",
		  "YE" => "Yemen",
		  "YU" => "Yugoslavia",
		  "ZM" => "Zambia",
		  "ZW" => "Zimbabwe"
		);
	}

	public static function GetStateName($val)
	{
		if ($val)
		{
			foreach (self::GetStates_PlusCanada(true) as $code => $name)
			{
				if ($code == $val) return $name;
			}
		}
		return "";
	}

	public static function GetStates($include_blank=false)
	{
		$states = array(
			"AL" => "Alabama",
			"AK" => "Alaska",
			"AZ" => "Arizona",
			"AR" => "Arkansas",
			"CA" => "California",
			"CO" => "Colorado",
			"CT" => "Connecticut",
			"DE" => "Delaware",
			"DC" => "District of Columbia",
			"FL" => "Florida",
			"GA" => "Georgia",
			"HI" => "Hawaii",
			"ID" => "Idaho",
			"IL" => "Illinois",
			"IN" => "Indiana",
			"IA" => "Iowa",
			"KS" => "Kansas",
			"KY" => "Kentucky",
			"LA" => "Louisiana",
			"ME" => "Maine",
			"MD" => "Maryland",
			"MA" => "Massachusetts",
			"MI" => "Michigan",
			"MN" => "Minnesota",
			"MS" => "Mississippi",
			"MO" => "Missouri",
			"MT" => "Montana",
			"NE" => "Nebraska",
			"NV" => "Nevada",
			"NH" => "New Hampshire",
			"NJ" => "New Jersey",
			"NM" => "New Mexico",
			"NY" => "New York",
			"NC" => "North Carolina",
			"ND" => "North Dakota",
			"OH" => "Ohio",
			"OK" => "Oklahoma",
			"OR" => "Oregon",
			"PA" => "Pennsylvania",
			"PR" => "Puerto Rico",
			"RI" => "Rhode Island",
			"SC" => "South Carolina",
			"SD" => "South Dakota",
			"TN" => "Tennessee",
			"TX" => "Texas",
			"UT" => "Utah",
			"VT" => "Vermont",
			"VA" => "Virginia",
			"WA" => "Washington",
			"WV" => "West Virginia",
			"WI" => "Wisconsin",
			"WY" => "Wyoming"
		);
		if ($include_blank) $states = array_merge(array("" => " "), $states);

		return $states;
	}

	public static function GetStatesBackwards()
	{
		$states = self::GetStates();
		$states_bw = array();
		foreach($states as $initials => $full)
		{
			$states_bw[$full] = $initials;
		}
		return $states_bw;
	}

	public static function GetStates_International()
	{
		return array(
			"International" => "*International*",
			"AL" => "Alabama",
			"AK" => "Alaska",
			"AZ" => "Arizona",
			"AR" => "Arkansas",
			"CA" => "California",
			"CO" => "Colorado",
			"CT" => "Connecticut",
			"DE" => "Delaware",
			"DC" => "District of Columbia",
			"FL" => "Florida",
			"GA" => "Georgia",
			"HI" => "Hawaii",
			"ID" => "Idaho",
			"IL" => "Illinois",
			"IN" => "Indiana",
			"IA" => "Iowa",
			"KS" => "Kansas",
			"KY" => "Kentucky",
			"LA" => "Louisiana",
			"ME" => "Maine",
			"MD" => "Maryland",
			"MA" => "Massachusetts",
			"MI" => "Michigan",
			"MN" => "Minnesota",
			"MS" => "Mississippi",
			"MO" => "Missouri",
			"MT" => "Montana",
			"NE" => "Nebraska",
			"NV" => "Nevada",
			"NH" => "New Hampshire",
			"NJ" => "New Jersey",
			"NM" => "New Mexico",
			"NY" => "New York",
			"NC" => "North Carolina",
			"ND" => "North Dakota",
			"OH" => "Ohio",
			"OK" => "Oklahoma",
			"OR" => "Oregon",
			"PA" => "Pennsylvania",
			"PR" => "Puerto Rico",
			"RI" => "Rhode Island",
			"SC" => "South Carolina",
			"SD" => "South Dakota",
			"TN" => "Tennessee",
			"TX" => "Texas",
			"UT" => "Utah",
			"VT" => "Vermont",
			"VA" => "Virginia",
			"WA" => "Washington",
			"WV" => "West Virginia",
			"WI" => "Wisconsin",
			"WY" => "Wyoming"
		);
	}

	public static function GetCanadianProvinces()
	{
		return array(
			"AB" => "Alberta",
			"BC" => "British Columbia",
			"MB" => "Manitoba",
			"NB" => "New Brunswick",
			"NL" => "Newfoundland and Labrador",
			"NT" => "Northwest Territories",
			"NS" => "Nova Scotia",
			"NU" => "Nunavut",
			"ON" => "Ontario",
			"PE" => "Prince Edward Island",
			"QC" => "Quebec",
			"SK" => "Saskatchewan",
			"YT" => "Yukon"
		);
	}

	public static function GetStates_PlusCanada($include_blank=false)
	{
		$states = array(
			"AL" => "Alabama",
			"AK" => "Alaska",
			"AZ" => "Arizona",
			"AR" => "Arkansas",
			"CA" => "California",
			"CO" => "Colorado",
			"CT" => "Connecticut",
			"DE" => "Delaware",
			"DC" => "District of Columbia",
			"FL" => "Florida",
			"GA" => "Georgia",
			"HI" => "Hawaii",
			"ID" => "Idaho",
			"IL" => "Illinois",
			"IN" => "Indiana",
			"IA" => "Iowa",
			"KS" => "Kansas",
			"KY" => "Kentucky",
			"LA" => "Louisiana",
			"ME" => "Maine",
			"MD" => "Maryland",
			"MA" => "Massachusetts",
			"MI" => "Michigan",
			"MN" => "Minnesota",
			"MS" => "Mississippi",
			"MO" => "Missouri",
			"MT" => "Montana",
			"NE" => "Nebraska",
			"NV" => "Nevada",
			"NH" => "New Hampshire",
			"NJ" => "New Jersey",
			"NM" => "New Mexico",
			"NY" => "New York",
			"NC" => "North Carolina",
			"ND" => "North Dakota",
			"OH" => "Ohio",
			"OK" => "Oklahoma",
			"OR" => "Oregon",
			"PA" => "Pennsylvania",
			"PR" => "Puerto Rico",
			"RI" => "Rhode Island",
			"SC" => "South Carolina",
			"SD" => "South Dakota",
			"TN" => "Tennessee",
			"TX" => "Texas",
			"UT" => "Utah",
			"VT" => "Vermont",
			"VA" => "Virginia",
			"WA" => "Washington",
			"WV" => "West Virginia",
			"WI" => "Wisconsin",
			"WY" => "Wyoming",
			"AB" => "Alberta",
			"BC" => "British Columbia",
			"MB" => "Manitoba",
			"NB" => "New Brunswick",
			"NL" => "Newfoundland and Labrador",
			"NT" => "Northwest Territories",
			"NS" => "Nova Scotia",
			"NU" => "Nunavut",
			"ON" => "Ontario",
			"PE" => "Prince Edward Island",
			"QC" => "Quebec",
			"SK" => "Saskatchewan",
			"YT" => "Yukon"
		);
		if ($include_blank) $states = array_merge(array("" => " "), $states);

		return $states;
	}

	public static function generateCode($word)
	{
		$len = strlen($word);
		$chars = array("@", "#", "*", "$", "!");

		$num1 = intval(substr(md5($word), 5, 1));
		$num1 = $num1 ? $num1 : 3;
		$num2 = intval(substr(md5($word), 8, 1));
		$num2 = $num2 ? $num2 : 6;
		$num3 = intval(substr(md5($word), 12, 1));
		$num3 = $num3 ? $num3 : 9;
		$word = md5($word);
		$word = substr($word, 0, $num1).strtoupper(substr($word, $num1, 1)).substr($word, $num1+1);
		$word = substr($word, 0, $num2).($chars[$num2 % count($chars)]).substr($word, $num2+1);
		$word = substr($word, 0, $num3).strtoupper(substr($word, $num3, 1)).substr($word, $num3+1);

		while (preg_match("/^\d/", $word)) $word = substr($word, 1);

		$i = 0;
		$word = preg_replace_callback("/([a-zA-Z])/",
			function($matches) use (&$word,&$i){
				chr(ord($matches[0]) + ((strlen($word) * ($i++)) % 20));
			}, $word
		);
		$word = preg_replace("/^\W/", chr(preg_match_all("/\d/", $word, $matches) + 97), $word);

		$word = substr($word, 0, 14);
		return ($word);
	}

	public static function referSecurityCheck()
	{
		$refer = $_SERVER['HTTP_REFERER'];
		$referrer = preg_replace('/((?:https?:\/\/)?[^\/]+).*/','$1',$refer);
		foreach(self::Config()->get('allowed_referrers') as $allowed_referrer)
		{
			if (strtolower($referrer) == strtolower($allowed_referrer))
			{
				header('Access-Control-Allow-Origin: '.$allowed_referrer);
				return true;
			}
		}
		$referrer = preg_replace('/(?:https?:\/\/)?([^\/]+).*/','$1',$refer);
		$server = $_SERVER['SERVER_NAME'];
		$cr = explode(".", preg_replace("/^http[s]?:\/\/([^\/]+).*$/i", "\\1", $refer));
		$cs = explode(".", $server);

		$r = $cr[count($cr)-2].".".$cr[count($cr)-1];
		$s = $cs[count($cs)-2].".".$cs[count($cs)-1];
		return ($r == $s);
	}

	public static function validateAjaxCode()
	{
		list($code1, $code2) = explode("|", trim($_REQUEST['nospam_codes']));
		unset($_REQUEST['nospam_codes'], $_POST['nospam_codes']);

		list($x, $time) = explode(".", $code1);

		$time = substr($time, 0, strlen($time)-3);

		$diff = time() - floatval($time);

		if (self::referSecurityCheck() && $diff < (60 * 60 * 8))	// 8 hour timeout
		{
			return (self::generateCode($code1) == $code2);
		}
		return false;
	}

}
