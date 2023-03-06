<?php

/**
 * This file demonstrates the central functions.
 *
 * @file-name 		:functions.php
 * @author 			:Deepika Patel <deepika.patel@ucertify.com>
 * @create_on 		:30-March-2018
 * @last_update_on 	:12 July 2020
 * @package 		:uclib.
 */

include_once UCLIB.'lists/ENUM.php';
/**
 *Check if an email address is valid.
*@param string $email The email address to check.
*@param bool $ignore_number Optional. Whether to ignore numbers or not. Default is false.
*@return mixed Returns the email address if valid, false otherwise.
 */
function isValidEmail($email, $ignore_number = false)
{
	$is_valid = false;
	// Check if ignoring numbers
	if ($ignore_number) {
		// Split the email into chunks separated by '||'
		$email_chunk = explode('||', $email);
		 // If the second chunk exists and has a length of 5, it is valid
		if ($email_chunk[1] && strlen($email_chunk[1]) == 5) {
			$is_valid = $email;
			 // If it matches the format of a number (with optional plus sign) up to 20 digits, it is valid
		} elseif (preg_match('/^[+][1-9][0-9]{0,20}$/', $email)) { // Validate as number too (max 20 digit).
			$is_valid = $email;
		} else {
			 // Otherwise, validate it as an email address using filter_var()
			$is_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
		}
	} else {
		$is_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	return $is_valid;
}

/**
* Check if the given string is a valid phone number.
*@param string $str The phone number to be checked.
*@return int Returns 1 if the phone number is valid, otherwise returns 0.
 */
function checkPhoneNo($str)
{
	$check_plus = substr($str, 0, 1);
	$trim_mbl   = removeStrSpace($str);
	$check_num  = is_numeric($trim_mbl);
	return ($check_plus == '+' && $check_num && strlen($str) < 17 && strlen($str) > 10 && !isValidEmail($str)) ? 1 : 0;
}

/**
*Sanitize an email address.
*@param string $email The email address to be sanitized.
*@return string The sanitized email address.
*/
function sanitizeEmail($email)
{
	// Use PHP's filter_var function with FILTER_SANITIZE_EMAIL flag to sanitize email.
	return filter_var($email, FILTER_SANITIZE_EMAIL);
}
/**
 * Sanitizes a string by removing multiple spaces, stripping tags, and escaping special characters.
 *
 * @param string $str The string to be sanitized.
 * @return string The sanitized string.
 */
function sanitizeString($str)
{
	// Remove multiple spaces
	$str = preg_replace('/\s+/', ' ', $str); //remove multple space;
	// Strip tags from the string
	// Replace single quotes with backticks and escape special characters
	$str = trim(filter_var(strip_tags(str_replace("'", '`', $str)), FILTER_SANITIZE_ADD_SLASHES)); 
	return $str;
}
/**
 * Generates a UUID / unique ID of specified length
 *
 * @param int $len The desired length of the ID (defaults to 8)
 * @param string $salt The salt to use for generating the ID (defaults to 'ucertify')
 * @return string The generated unique ID
 */
function gen_uuid($len = 8, $salt = 'ucertify')
{
	$hex 		= md5($salt . uniqid('', true));
	$pack 		= pack('H*', $hex);
	$tmp  		= base64_encode($pack);
	$uid  		= preg_replace('/[^A-Za-z0-9]/', '', $tmp);
	$len  		= max(4, min(128, $len));
	$uid_length = strlen($uid);

	while ($uid_length < $len) {
		$uid .= gen_uuid(22);
	}

	return substr($uid, 0, $len);
}

/**
 * Retrieves user request data from $_GET and $_POST.
 * 
 * @param array $data The data array to populate with request values.
 * @param bool $isStrip Whether to strip tags from request values or not (defaults to true).
 * @return array The populated data array.
 */
function getUserRequest($data = array(), $isStrip = true)
{
	 // Retrieve values from $_GET
	if (isset($_GET)) {
		foreach ($_GET as $k => $v) {
			if (is_array($v)) {
				$data[$k] = $v;
			} else {
				if ($isStrip) {
					$data[$k] = trim(strip_all_tags_uc($v));
				} else {
					$data[$k] = trim($v);
				}
			}
		}
	}
	// Retrieve values from $_POST
	if (isset($_POST)) {
		foreach ($_POST as $k => $v) {
			if (is_array($v)) {
				$data[$k] = $v;
			} else {
				$data[$k] = trim($v);
			}
		}
	}
	return $data;
}

/**
 * It is the function that has been to find difference between two dates in diffrent units like month,year and days.
 *
 * @param string $interval denotes interval.
 * @param string $datefrom denotes datefrom.
 * @param string $dateto denotes dateto.
 * @param string $using_timestamps denotes using_timestamps.
 * @return string as whole response of function.
 */
function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{
	/*
	$interval can be:
	yyyy - Number of full years.
	q - Number of full quarters.
	m - Number of full months.
	y - Difference between day numbers.
	(eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".).
	d - Number of full days.
	w - Number of full weekdays.
	ww - Number of full weeks.
	h - Number of full hours.
	n - Number of full minutes.
	s - Number of full seconds (default).
	*/

	if (!$using_timestamps) {
		$datefrom = strtotime($datefrom, 0);
		$dateto   = strtotime($dateto, 0);
	}
	$difference = $dateto - $datefrom; // Difference in seconds.

	switch ($interval) {

		case 'yyyy':
			$years_difference = floor($difference / 31536000);
			if (mktime(date('H', $datefrom), date('i', $datefrom), date('s', $datefrom), date('n', $datefrom), date('j', $datefrom), date('Y', $datefrom) + $years_difference) > $dateto) {
				$years_difference--;
			}
			if (mktime(date('H', $dateto), date('i', $dateto), date('s', $dateto), date('n', $dateto), date('j', $dateto), date('Y', $dateto) - ($years_difference + 1)) > $datefrom) {
				$years_difference++;
			}
			$datediff = $years_difference;
			break;

		case 'q': // Number of full quarters.
			$months_difference   = 0;
			$quarters_difference = floor($difference / 8035200);
			while (mktime(date('H', $datefrom), date('i', $datefrom), date('s', $datefrom), date('n', $datefrom) + ($quarters_difference * 3), date('j', $dateto), date('Y', $datefrom)) < $dateto) {
				$months_difference++;
			}
			$quarters_difference--;
			$datediff = $quarters_difference;
			break;

		case 'm':
			$months_difference = floor($difference / 2678400);
			while (mktime(date('H', $datefrom), date('i', $datefrom), date('s', $datefrom), date('n', $datefrom) + ($months_difference), date('j', $dateto), date('Y', $datefrom)) < $dateto) {
				$months_difference++;
			}
			$months_difference--;
			$datediff = $months_difference;
			break;

		case 'y':
			$datediff = date('z', $dateto) - date('z', $datefrom);
			break;

		case 'd':
			$datediff = round($difference / 86400);
			break;

		case 'w':
			$days_difference  = floor($difference / 86400);
			$weeks_difference = floor($days_difference / 7); // Complete weeks.
			$first_day        = date('w', $datefrom);
			$days_remainder   = floor($days_difference % 7);
			$odd_days         = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder.
			if ($odd_days > 7) { // Sunday.
				$days_remainder--;
			}
			if ($odd_days > 6) { // Saturday.
				$days_remainder--;
			}
			$datediff = ($weeks_difference * 5) + $days_remainder;
			break;

		case 'ww':
			$datediff = floor($difference / 604800);
			break;

		case 'h':
			$datediff = floor($difference / 3600);
			break;

		case 'n':
			$datediff = floor($difference / 60);
			break;

		default:
			$datediff = $difference;
			break;
	}

	return $datediff;
}

/**
 * Converts a string into a comma-separated list of values, removing extra spaces and newlines.
 *
 * @param string $str The string to convert.
 * @return string The comma-separated list of values.
 */
function strIntoComma($str = '')
{
	$f = preg_replace('/(\r\n|\r|\n|\t)+/', ',', $str);     			// replace tab and new line into comma.
	$f = preg_replace('/\s+/', ' ', $f);                    			// replace extra space into single space.
	$f = trim(implode(',', array_filter(explode(',', $f)))); 	// replace extra comma (blank array) in single comma.
	return preg_replace('/\s*,\s*/', ',', $f);              			// trim before and after spacing with string.
}

/**
*Generates a list of dates between a given start and end date.
*@param string $start Start date in 'Y-m-d' format
*@param string $end End date in 'Y-m-d' format
*@param string $format Date format for the output list, defaults to 'Y-m-d'
*@return array List of dates between the start and end date
 */
function getDatesListFromRange($start, $end, $format = 'Y-m-d')
{

	$array    = array();
	$interval = new DateInterval('P1D');
	$realEnd  = new DateTime($end);
	$realEnd->add($interval);

	$period = new DatePeriod(new DateTime($start), $interval, $realEnd);

	foreach ($period as $date) {
		$date_list[] = $date->format($format);
	}

	return $date_list;
}

/**
 * Convert an XML string to a nested array. 
 * @param string $xml The XML string to convert.
 * @return array The resulting nested array. 
 */
function xml2array($xml)
{
	$xmlary = array();

	if ((strlen($xml) < 256) && is_file($xml)) {
		$xml = file_get_contents_uc($xml);
	}

	$ReElements   = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*?)<(\/\s*\1\s*)>)/s';
	$ReAttributes = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

	preg_match_all($ReElements, $xml, $elements);

	foreach ($elements[1] as $ie => $xx) {
		$xmlary[$ie]['name'] = $elements[1][$ie];
		$attributes 		   = trim($elements[2][$ie]);
		if ($attributes) {
			preg_match_all($ReAttributes, $attributes, $att);
			foreach ($att[1] as $ia => $xx) {
				// all the attributes for current element are added here.
				$xmlary[$ie]['attributes'][$att[1][$ia]] = $att[2][$ia];
			}
		}

		// get text if it's combined with sub elements.
		$cdend = strpos($elements[3][$ie], '<');
		if ($cdend > 0) {
			$xmlary[$ie]['text'] = substr($elements[3][$ie], 0, $cdend - 1);
		}

		if (preg_match($ReElements, $elements[3][$ie])) {
			$xmlary[$ie]['elements'] = xml2array($elements[3][$ie]);
		} elseif (isset($elements[3][$ie])) {
			$xmlary[$ie]['text'] = $elements[3][$ie];
		}
		$xmlary[$ie]['closetag'] = $elements[4][$ie];
	}

	return $xmlary;
}

/**
 * This function that is used to convert xml into array.
 *
 * @param string $xml denotes xml string.
 * @return array as response of the function.
 */
function xmltoArray($xml)
{
	$objXML = new clsXml_Array();
	return $objXML->parse($xml);
}

/**
 * This is the function that has been used for xml conversion to array.
 */
class clsXml_Array
{
	/**
	 * It is the output.
	 *
	 * @var $arrOutput as arrOutput.
	 */
	public $arrOutput = array();

	/**
	 * It is for the parser.
	 *
	 * @var $resParser as resParser.
	 */
	public $resParser;

	/**
	 * It is for the string xml data.
	 *
	 * @var $strXmlData as strXmlData.
	 */
	public $strXmlData;

	/**
	 * This function is used to parse str into xml.
	 *
	 * @param string $strInputXML is the strInputXML.
	 * @return array as response of the function.
	 */
	public function parse($strInputXML)
	{

		$this->resParser = xml_parser_create();
		xml_set_object($this->resParser, $this);
		xml_set_element_handler($this->resParser, 'tagOpen', 'tagClosed');

		xml_set_character_data_handler($this->resParser, 'tagData');

		$this->strXmlData = xml_parse($this->resParser, $strInputXML);

		if (!$this->strXmlData) {
			return array();
			/*
			die(sprintf(
					'XML error: %s at line %d',
					xml_error_string(xml_get_error_code($this->resParser)),
					xml_get_current_line_number($this->resParser)
				));
				*/
		}

		xml_parser_free($this->resParser);

		return $this->arrOutput;
	}

	/**
	 * This function has been used to open tag.
	 *
	 * @param array $parser denotes parser.
	 * @param array $name is the name.
	 * @param array $attrs is the attrs.
	 */
	public function tagOpen($parser, $name, $attrs)
	{
		foreach ($attrs as $key => $val) {
			$new_attrs[strtolower($key)] = $val;
		}
		$tag = array(
			'name'       => strtolower($name),
			'attributes' => $new_attrs,
		);
		array_push($this->arrOutput, $tag);
	}

	/**
	 * This function has been used for tag data.
	 *
	 * @param array $parser denotes parser.
	 * @param array $tagData is the tagData.
	 */
	public function tagData($parser, $tagData)
	{
		if (trim($tagData)) {
			if (isset($this->arrOutput[count_uc($this->arrOutput) - 1]['text'])) {
				$this->arrOutput[count_uc($this->arrOutput) - 1]['text'] .= $tagData;
			} else {
				$this->arrOutput[count_uc($this->arrOutput) - 1]['text'] = $tagData;
			}
		}
	}

	/**
	 * This function has been used for tag closed.
	 *
	 * @param array $parser denotes parser.
	 * @param array $name is the name.
	 */
	public function tagClosed($parser, $name)
	{
		$this->arrOutput[count_uc($this->arrOutput) - 2]['elements'][] = $this->arrOutput[count_uc($this->arrOutput) - 1];
		array_pop($this->arrOutput);
	}
}

/**
 * This function has been used to convert array to xml.
 *
 * @param string $mainTag denotes mainTag.
 * @param string $ary denotes ary.
 * @param string $id is the id.
 * @return string as overall response of the function.
 */
function array2xml($mainTag, $ary, $id = '')
{
	if (is_numeric($mainTag)) {
		$mainTag = 'child';
	}
	if (!is_Array($id)) {
		$ids[] = $id;
	} else {
		$ids = $id;
	}
	$xml = "<$mainTag ";
	foreach ($ids as $i) {
		if ($i <> '') {
			if (isset($ary[$i])) {
				$xml .= ' ' . $i . '="' . $ary[$i] . '" ';
			} else {
				$xml .= ' ' . $i . '="" ';
			}
		}
	}
	$xml .= ">\n";
	foreach ($ary as $k => $v) {
		if (is_array($v)) {
			$xml .= array2xml($k, $v);
		} else {
			if (!strpos('<[@#$%&=:;\\/,>"$/' . "'", $v)) {
				$xml .= "<$k>$v</$k>\n";
			} else {
				$xml .= "<$k><![CDATA[$v]]></$k>\n";
			}
		}
	}
	$xml .= "</$mainTag>";
	return $xml;
}

/**
 * Shorten a string to a specified length.
 *
 * @param string $str The string to shorten.
 * @param int $length The maximum length of the shortened string.
 * @param int $direction The direction from which to shorten the string. 1 for left, 2 for right, 0 for both sides.
 * @param bool $remove_single_quote Whether or not to remove single quotes from the string.
 * @return string The shortened string.
*/
function short_string($str, $length = 80, $direction = 0, $remove_single_quote = true)
{
	// Remove single quotes from string if specified
	if ($remove_single_quote) {
	// Replace double quotes with spaces
		$str = str_replace("'", ' ', strip_all_tags_uc($str));
	}
	$str = str_replace('"', ' ', $str);
	// Shorten string if longer than specified length
	if (strlen($str) > $length) {
		if ($direction == 1) {
			$str = substr($str, 0, $length - 3) . '...';
		} elseif ($direction == 2) {
			$str = '...' . substr($str, ($length - 3) * -1);
		} else {
			if (strpos($str, '&quot;') !== false) {
				$str = substr($str, 0, $length - 3) . '...';
			} else {
				$str = substr($str, 0, ($length / 2) - 2) . '...' . substr($str, (($length / 2) - 2) * -1);
			}
		}
	}
	return $str;
}

/**
 * Find a substring within a string between two given substrings and update the starting index.
 *
 * @param string $str denotes str.
 * @param string $fstr denotes fstr.
 * @param string $lstr is the lstr.
 * @param string $i is the i.
 * @return string as overall response of the function.
 */
function findtext($str, $fstr, $lstr, $i = 0)
{
	return findtextnew($str, $fstr, $lstr, $i);
}

/**
 * This function has been used to find text (new function : to find between substrings).
 *
 * @param string $str denotes str.
 * @param string $fstr denotes fstr.
 * @param string $lstr is the lstr.
 * @param string $i is the i.
 * @return string as overall response of the function.
 */
function findtextnew($str, $fstr, $lstr, &$i)
{
	$namepos = 0;
	$endpos  = 0;
	$lenstr  = 0;

	$retstr = '';
	$lenstr = strlen($fstr);
	if (strlen($str) <= 0) {
		$i = 0;
		return false;
	}
	$namepos = false;
	if ($str && $i <= strlen($str)) {
		$namepos = strpos(strtolower($str), strtolower($fstr), $i);
	}

	if ($namepos === false) {
		$retstr = '';
	} else {
		$endpos = strpos(strtolower($str), strtolower($lstr), $namepos + $lenstr);
		if ($endpos === false) {
			$endpos = strlen($str) + 1;
		}
		$retstr = trim(substr($str, $namepos + $lenstr, $endpos - $lenstr - $namepos));
	}
	$i = $namepos + $lenstr;
	return $retstr;
}

/**
 * Extracts the value of the specified attribute from an HTML/XML tag.
 *
 * @param string $strmle The input string containing the tag.
 * @param string $tag The name of the tag to extract the attribute from.
 * @param string $attribute The name of the attribute to extract.
 * @param int $i The index to start searching for the tag.
 * @return string The value of the specified attribute, or an empty string if the attribute is not found.
 */
function getAttribute($strmle, $tag, $attribute, &$i)
{
	$z        = 0;
	$startstr = '<' . $tag;
	$endstr   = '>';
	// Ensure attribute is surrounded by spaces for more accurate matching.
	$attribute = ' ' . $attribute;
	// Find the tag and extract the attribute from it.
	$str       = ' ' . findtextnew($strmle, $startstr, $endstr, $i);
	// Fix potential JSON encoding issues.
	$str 	   = str_replace('"{"', '\'{"', $str);
	$str 	   = str_replace('"}"', '"}\'', $str);
    // Check if the attribute value is surrounded by quotes.
	if ($str <> '') {
		$str = findtextnew($str . '>', $attribute, $endstr, $z);
		if (substr($str, 0, 1) == '=') {
			$str   = substr($str, 1);
			$quote = substr($str, 0, 1);
			if (($quote == "'") || ($quote == '"')) {
				$z   = 0;
				$str = findtextnew($str, $quote, $quote, $z);
			} else {
				$stpos = strpos($str, ' ');
				if ($stpos > 0) {
					$str = substr($str, 0, $stpos);
				}
			}
         // If the attribute value is not surrounded by quotes, try to extract it again.	
		} elseif (substr($str, 0, 1) !== '=' && strpos($str, $attribute) !== false) {
			$str   = findtextnew($str . '>', $attribute, $endstr, $z);
			$str   = substr($str, 1);
			$quote = substr($str, 0, 1);
			if (($quote == "'") || ($quote == '"')) {
				$z   = 0;
				$str = findtextnew($str, $quote, $quote, $z);
			} else {
				$stpos = strpos($str, ' ');
				if ($stpos > 0) {
					$str = substr($str, 0, $stpos);
				}
			}
		} else {
	     // Attribute not found, return empty string.
			$str = '';
		}
	}
	return $str;
}

/**
 * Remove comments and specific tags from a string.
 *
 * @param string $str The input string.
 * @return string The modified string.
 */
function removecomments($str)
{
	// Remove HTML comments
	$str = removephrase($str, '<!--', '-->');
	// Remove PHP tags
	$str = removephrase($str, '<?', '>');
	// Remove specific custom tags
	$str = removephrase($str, '<uc:iref>', '</uc:iref>');
	return $str;
}

/**
 * Removes a specified phrase from a given string.
 * 
 * @param string $str The string to remove the phrase from.
 * @param string $keystart The starting phrase to remove.
 * @param string $keyend The ending phrase to remove.
 * @return string The resulting string with the phrase removed.
 */
function removephrase($str, $keystart, $keyend)
{
	$len    = strlen($str);
	$curpos = 0;

	for ($i = 1; $i < 1000; $i++) {
		$curpos = strpos($str, $keystart);

		if ($curpos === false) {
			$i = 1001;
		} else {
			$endpos = strpos($str, $keyend, $curpos);
			if ($endpos > 0) {
				$start = trim(substr($str, 0, $curpos), " \t");
				$end   = trim(substr($str, $endpos + strlen($keyend)), " \t");
				if (strpos(' .?!', substr($end, 0, 1)) === false) {
					$end = ' ' . $end;
				}
				$str = $start . $end;
			} else {
				$i = 1001;
			}
		}
		$len = strlen($str);
	}
	return $str;
}

/**
 * Find and get the Smarty template or retrieves an instance of the Smarty template engine with appropriate settings and assigns common global variables.
 *
 * @param string $templatedir denotes templatedir.
 * @param string $basedir denotes basedir.
 * @param string $usesitename is the usesitename.
 * @return object as overall response of the function.
 */
function getSmartyTemplate($templatedir = 'layout/templates', $basedir = false, $usesitename = true)
{
	global $website;
	global $meta;
	global $isSecure;

	if (!$basedir && defined('SITE_ABSPATH')) {
		$basedir = SITE_ABSPATH;
	}
	include_once UCLIB . 'templateengines/smarty_3.1/libs/Smarty.class.php';
	include_once UCLIB . 'templateengines/smarty_3.1/libs/SmartyBC.class.php';

	$smarty                  = new SmartyBC();
	$smarty->left_delimiter  = '<{';
	$smarty->right_delimiter = '}>';

	if (defined('SITE_NAME') && $usesitename) {
		$smarty->template_dir = $basedir . SITE_NAME . $templatedir;
	} else {
		$smarty->template_dir = $basedir . $templatedir;
	}

	$smarty->compile_dir = $basedir . 'templates_c/';
	$smarty->config_dir  = $basedir . 'configs/';
	$smarty->cache_dir   = $basedir . 'cache/';

	if (defined('SITE_URL')) {
		$smarty->assign('SITE_URL', SITE_URL);
		$smarty->assign('site_url', SITE_URL);
	}

	if (defined('SITE_SECURE_URL')) {
		$smarty->assign('SITE_SECURE_URL', SITE_SECURE_URL);
	}

    if (defined('APP_FOLDER')) {
        $smarty->assign('APP_FOLDER', APP_FOLDER);
    }

	if (defined('SITE_SHORT_URL')) {
		$smarty->assign('SITE_SHORT_URL', SITE_SHORT_URL);
	}

	if (defined('SITE_LINK_URL')) {
		$smarty->assign('SITE_LINK_URL', SITE_LINK_URL);
	}

	if (defined('SITE_THEME_URL')) {
		$smarty->assign('SITE_THEME_URL', SITE_THEME_URL);
	}

	if (defined('THEME_NAME')) {
		$smarty->assign('THEME_NAME', THEME_NAME);
	}

	if (defined('DEVICE')) {
		$smarty->assign('DEVICE', DEVICE);
	}
	if (defined('THEME_SUFFIX')) {
		$smarty->assign('THEME_SUFFIX', THEME_SUFFIX);
	}

	if (defined('SITE_THEME_DIR')) {
		$smarty->assign('SITE_THEME_DIR', SITE_THEME_DIR);
	}

	if (defined('SITE_NAME')) {
		$smarty->assign('SITE_NAME', SITE_NAME);
	}
	if (defined('IS_DATA_DUMP')) {
		$smarty->assign('IS_DATA_DUMP', IS_DATA_DUMP);
	}

	if (isset($website['currencycode'])) {
		$smarty->assign('currencycode', $website['currencycode']);
	}
	$smarty->assign('isSecure', $isSecure);
	$smarty->assign('meta', $meta);
	return $smarty;
}

/**
 * Converts a date string to the format 'mm/dd/yyyy'.
 *
 * @param string $str The date string to convert.
 * @return string The formatted date string
 */
function strtodate($str = '')
{
	if (strlen($str) == 0) {
		$str = date('YmdHis');
	}
    // Check if the date string is already in the correct format
	if (strpos($str, '/', 0) === false) {
        // If not, format the date string as 'yyyy/mm/dd'
		$dt = substr($str, 4, 2) . '/' . substr($str, 6, 2) . '/' . substr($str, 0, 4);
	} else {
	    // Otherwise, use the date string as is	
		$dt = $str;
	}

	return $dt;
}

/**
 * Converts a date string in the format MM/DD/YYYY to a string in the format YYYYMMDD000000.
 *
 * @param string $date The date to convert. If empty, the current date and time will be used.
 * @return string The date string in the format YYYYMMDD000000.
 */
function datetostr($date = '')
{
	if ($date == '') {
		$dt = date('YmdHis');
	} else {
		if (strpos($date, '/', 0) > 0) {
			$dt = substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2) . '000000';
		} else {
			$dt = $date;
		}
	}

	return $dt;
}

/**
 * This function has been used to convert date.
 * Returns the date that is a certain number of days after a given date.
 * If no date is specified, defaults to the current date.
 * @param string $date The starting date (format: 'YYYYMMDD').
 * @param int $dayafter The number of days after the starting date.
 * @return string The resulting date (format: 'YYYYMMDD000000').
 */
function dayafter($date, $dayafter = 0)
{
	// If no date is specified, use the current date
	if ($date == 0) {
		$yr  = date('Y');
		$mon = date('m');
		$day = date('d');
	}
    // Calculate the resulting date
	$day = $day + $dayafter;
	$mon = $mon + 0;
	$yr  = $yr + 0;

	if ($day <= 0) {
		$day = '1';
		$mon--;
	}

	if ($mon <= 0) {
		$mon = '1';
		$yr--;
	}

	if ($day <= 9) {
		$day = '0' . $day;
	}

	if ($mon <= 9) {
		$mon = '0' . $mon;
	}
	$dt = $yr . $mon . $day . '000000';
	return $dt;
}

/**
 * Generates a random string with specified length and type.
 *
 * @param int $a_digit The length of the random string.
 * @param int $a_rantype The type of random string: 1 for alphanumeric, 2 for alphabetic, 3 for numeric.
 * @return string The generated random string.
 */
function f_random($a_digit = 5, $a_rantype = 1)
{
	$l_offset = 87;   // Ascii offset require to get alpha vlue.
	$l_add    = 0;    // offset to get only alpha value.
	$i        = 0;    // starting for loop.

	// Determine the maximum value for random number generation based on rantype.
	if ($a_rantype == 1) {
		$maxno = 35;
	} else {
		if ($a_rantype == 2) {
			$maxno = 25;
			$l_add = 10;
		} else {
			$maxno = 9;
		}
	}

	$rannum = '';

	for ($i == 0; $i < $a_digit; $i++) {
		$lnum = rand_uc(1, $maxno);
		$lnum = $lnum + $l_add;

		if ($lnum < 10) {
			$ran = $lnum;
		} else {
			$ran = chr($lnum + $l_offset);
		}

		$rannum = $rannum . $ran;
	}
	return $rannum;
}

/**
 * Redirects to a given URL with an optional message and delay time.
 *
 * @param string $url The URL to redirect to.
 * @param int $time The delay time in seconds before redirecting (default is 3 seconds).
 * @param string $message The message to display (default is an empty string).
 * @return void
 */
function redirect_header($url, $time = 3, $message = '')
{
	$url = preg_replace('/&amp;/i', '&', htmlspecialchars($url, ENT_QUOTES));
	echo '
		<html>
		<head>
		<title>".SITE_URL."</title>
		<meta http-equiv="Content-Type" content="text/html; charset=' . _CHARSET . '" />
		<meta http-equiv="Refresh" content="' . $time . '; url=' . $url . '" />
		<style type="text/css">
				body {background-color : #fcfcfc; font-size: 12px; font-family: Trebuchet MS,Verdana, Arial, Helvetica, sans-serif; margin: 0px;}
				.redirect {width: 70%; margin: 110px; text-align: center; padding: 15px; border: #e0e0e0 1px solid; color: #666666; background-color: #f6f6f6;}
				.redirect a:link {color: #666666; text-decoration: none; font-weight: bold;}
				.redirect a:visited {color: #666666; text-decoration: none; font-weight: bold;}
				.redirect a:hover {color: #999999; text-decoration: underline; font-weight: bold;}
		</style>
		</head>
		<body>
		<div align="center">
		<div class="redirect">
		  <span style="font-size: 16px; font-weight: bold;">' . $message . '</span>
          <hr style="height: 3px; border: 3px #E18A00 solid; width: 95%;" />
		  <p>' . sprintf('redirecting to %s. Please wait...', $url) . '</p>
		</div>
        </div>
		</body>
		</html>';
	exit();
}

/**
 * Format date in given format
 *
 * @param string $dt Date string to format
 * @param string $fmt Format string (default: 'd-M-y')
 * @return string Formatted date string
 */
function f_date($dt, $fmt = 'd-M-y')
{
   // Extract date/time components from input string
	$yr  = substr($dt, 0, 4);
	$mon = substr($dt, 4, 2);
	$day = substr($dt, 6, 2);
	$hrs = substr($dt, 8, 2);
	$min = substr($dt, 10, 2);
	$sec = substr($dt, 12, 2);
    // Format date using the extracted components
	return date($fmt, mktime($hrs, $min, $sec, $mon, $day, $yr));
}

/**
 * Trims whitespace characters from the beginning and end of a string.
 *
 * @param string $str The input string to remove CDATA tags from
 * @return string The resulting string with CDATA tags removed
 */
function trimWhiteSpace($str)
{
	return trim($str);
}

/**
 * Removes CDATA tags from a given string
 *
 * @param string $str The input string to remove CDATA tags from
 * @return string The resulting string with CDATA tags removed
 */
function removeCDATA($str)
{
    // Check if the string contains CDATA tags
	if (strpos($str, 'CDATA[') !== false) {
        // Remove different variants of CDATA tags from the string
		$str = str_replace('<![CDATA[', '', $str);
		$str = str_replace(']]>', '', $str);
		$str = str_replace('[CDATA[', '', $str);
		$str = str_replace(']]', '', $str);
	}
    // Return the resulting string
	return $str;
}

/**
 * Removes invalid characters from XML content. 
 * @param string $content The XML content to validate.
 * @return string The validated XML content.
 */
function validXMLChr($content)
{
	// used with few palces, also check for duplicate function.
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace('�', "'", $content);
	$content = str_replace('�', "'", $content);
	$content = str_replace('�', '"', $content);
	$content = str_replace('�', '"', $content);
	$content = str_replace('�', '-', $content);
	$content = str_replace('�', ' ', $content);
	$content = str_replace('�', ' ', $content);
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace("\t", ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.
	$content = str_replace('', '-', $content); 	// there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	 // there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	 // there is invisible chr, do not remove.
	$content = str_replace('', ' ', $content);	// there is invisible chr, do not remove.

	return $content;
}

/**
 * Removes high ASCII characters from the input string and replaces them with spaces.
 * @param string $content The input string to sanitize.
 * @return string The sanitized string with high ASCII characters replaced with spaces.
 */
function removeHighAscii($content)
{
	// Replace all characters with an ASCII value between 128 and 254 with a space.
	for ($i = 128; $i < 255; $i++) {
		$content = str_replace(chr($i), ' ', $content);
	}
	return $content;
}

/**
 * Deletes all files in the given directory that match the specified pattern.
 *
 * @param string $path The directory to search for files in.
 * @param string $match   The pattern to match file names against.
 * @return bool Returns `true` if all files were successfully deleted, `false` otherwise.
 */
function remfiles($path, $match)
{
	$success = true;
	$files   = glob($path . $match);

	foreach ($files as $file) {
		if (is_file($file)) {
			$isdel = unlink($file);
			if (!$isdel) {
				$success = false;
			}
		}
	}

	return $success;
}

/**
 * Write data to a file
 *
 * @param string $file The file path to write to
 * @param string $data The data to write to the file
 * @return bool True on success, false on failure
 */
function writeFile($file, $data)
{
	$f_num   = fopen($file, 'w');
	$success = fwrite($f_num, $data);
	fclose($f_num);
	return $success;
}

/**
 * This function has been used to return unique number.
 * Generates a unique ID based on the current time and optionally a random number.
 *
 * @param int  $ctime   The current time as a Unix timestamp. If not provided or 0, the current time will be used.
 * @param int  $base    The numeric base to use for the encoding. Defaults to 36.
 * @param bool $is_rand Whether to include a random number in the unique ID. Defaults to true.
 * @param int  $size    The desired size of the unique ID. Defaults to 8.
 * @return string The generated unique ID.
 */
function uc_unique($ctime = 0, $base = 36, $is_rand = true, $size = 8)
{
	$offset = 1293868800;
	if ($ctime <= 0) {
		$ctime = time();
	}

	if ($is_rand) {
		list($usec, $sec) = explode(' ', microtime());
		$usec             = round($usec * 100000);
		$unique           = str_pad(dec2string($ctime - $offset, $base) . substr(str_pad(dec2string($usec, $base), 2, '0', STR_PAD_LEFT), -2), $size, '0', STR_PAD_LEFT);
	} else {
		$unique = str_pad(dec2string($ctime - $offset, $base), $size, '0', STR_PAD_LEFT);
	}

	return $unique;
}

/**
 * Encodes an integer using base 64 and returns a string of a specified size.
 *
 * @param int $i The integer to encode.
 * @param int $size The desired size of the resulting string. Default is 2.
 * @return string The base 64 encoded string.
 */
function GUID64_encode($i, $size = 2)
{
	return str_pad(dec2string($i, 64), $size, '0', STR_PAD_LEFT);
}

/**
 * Decodes a base-64 encoded GUID string into a decimal number.
 *
 * @param string $s The base-64 encoded GUID string to decode.
 * @return int The decimal number representation of the GUID.
 */
function GUID64_decode($s)
{
    // Convert the string to a decimal number using base-64 encoding.
	return string2dec($s, 64);
}

/**
 * This function has been used to reverse unique no..
 * Decode a unique string generated with uc_unique.
 * @param string  $string   The unique string to decode.
 * @param integer $base     The number base to use for decoding the string. Default is 36.
 * @param boolean $is_rand  Whether the unique string includes random characters. Default is true.
 * @param integer $size     The number of characters in the unique string. Default is 8.
 * 
 * @return mixed  Returns the decoded timestamp (and random characters, if applicable) as a string or integer.
 */
function uc_unique_rev($string, $base = 36, $is_rand = true, $size = 8)
{
	// Split the timestamp and random characters (if applicable)
	if ($is_rand) {
		$rand = substr($string, -2);
		$time = substr($string, 0, strlen($string) - 2);
	} else {
		$time = $string;
	}
	// Trim leading zeros and whitespace from the timestamp
	$time = ltrim($time, '0');
	$time = trim($time);
	// Decode the timestamp and add the Unix timestamp offset
	if ($is_rand) {
		return (string2dec($time, $base) + 1293868800) . '-' . string2dec($rand, $base);
	} else {
		return (string2dec($time, $base) + 1293868800);
	}
}

/**
 * Converts a decimal number to a string representation in the given base.
 *
 * @param string $decimal The decimal number to convert.
 * @param int $base The base to use for the string representation.
 * @param bool $firstalpha Whether to include the first alphabetical characters in the charset.
 * @return string|false The string representation of the decimal number in the given base, or false if an error occurred.
 */
function dec2string($decimal, $base, $firstalpha = false)
{
	global $error;
	$string = null;

	$base = (int) $base;
    // Define the character set based on the given base.
	if ($base == 64) {
		$charset = '0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz()';
	}elseif ($base == 32) {
		$charset = '0123456789abcdefghijklmnopqrstuvwxyz';
	} elseif ($base < 2 | $base > 62 | $base == 10) {
		exit;
	} else {
        // Use the default character set.
		$charset = '0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
	}

	// maximum character string is 36 characters.
	// strip off excess characters (anything beyond $base).
	$charset = substr($charset, 0, $base);

	if (!preg_match('(^[0-9]{1,62}$)', trim($decimal))) {
		$error['dec_input'] = 'Value must be a positive integer with < 50 digits';
		return false;
	}

	do {
		// get remainder after dividing by BASE.
		$remainder = bcmod($decimal, $base);

		$char   = substr($charset, $remainder, 1);   // get CHAR from array.
		$string = "$char$string";                     // prepend to output.

		$decimal = bcdiv(bcsub($decimal, $remainder), $base);
	} while ($decimal > 0);

	return $string;
}

/**
 * Convert a string in a given base to a decimal number.
 *
 * @param string $string The input string.
 * @param int $base The base of the input string.
 * @return string|false The decimal number as a string on success, false on failure.
 */
function string2dec($string, $base)
{
	global $error;
	$decimal = 0;

	$base = (int) $base;
	if ($base == 64) {
		$charset = '0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz()';
	}elseif ($base == 32) {
		$charset = '0123456789abcdefghijklmnopqrstuvwxyz';
	} elseif ($base < 2 | $base > 62 | $base == 10) {
		// 'BASE must be in the range 2-9 or 11-36'.
		exit;
	} else {
		$charset = '0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
	}

	// maximum character string is 36 characters.
	// strip off excess characters (anything beyond $base).
	$charset = substr($charset, 0, $base);

	$string = trim($string);
	if (empty($string)) {
		$error[] = 'Input string is empty';
		return false;
	}

	do {
		$char   = substr($string, 0, 1);    // extract leading character.
		$string = substr($string, 1);       // drop leading character.
		$pos 	= strpos($charset, $char);  // get offset in $charset.

		if ($pos === false) {
			$error[] = "Illegal character ($char) in INPUT string";
			return false;
		}

		$decimal = bcadd(bcmul($decimal, $base), $pos);
	} while ($string <> null);

	return $decimal;
}

/**
 * Returns the current Unix timestamp with microseconds
 *
 * @return float The current Unix timestamp with microseconds
 */
function getMicroTime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float) $usec + (float) $sec);
}

/**
 * Returns the current time in the specified time zone.
 *
 * @param string $time_zone The time zone to use, default is 'Europe/London'.
 * @param string $format The date/time format to return, default is 'Y-m-d H:i:s'.
 * @return string The current time in the specified time zone, formatted according to the specified format.
 */
function getTimeByTimezone($time_zone = 'Europe/London', $format = 'Y-m-d H:i:s')
{
	try {
		$country_time = new DateTime('now', new DateTimeZone($time_zone));
	} catch (Exception $e) {
		$country_time = new DateTime('now', new DateTimeZone('America/New_York'));
	}
	return $country_time->format($format);
}

/**
 * Sets the assessment date and time based on a specified timezone option.
 *
 * @param string $date The date in the format 'Y-m-d'.
 * @param string $time The time in the format 'H:i' or 'H:i:s'.
 * @param string $timezone_option The timezone option to use, specified as a time offset or timezone string.
 *
 * @return string The assessment date and time in the format 'Y-M-d H:i:s'.
 */
function setAssessmentDateTime($date, $time, $timezone_option)
{
	if (isAssessmentNewTimeFormat($time)) {
		return date('Y-M-d H:i:s', strtotime($date . ' ' . $time));
	} else {
		return date('Y-M-d H:i:s', strtotime($date . ' ' . $timezone_option));
	}
}

/**
 * Get the end date based on the start date, end date or number of days.
 *
 * @param string $start_date The start date in string format ('Y-m-d').
 * @param string $end_date The end date in string format ('Y-m-d').
 * @param bool|int $days The number of days to add to the start date.
 *
 * @return string The end date in the format 'Y-m-d', or an empty string if no date is found.
 */
function getEndDate($start_date = '', $end_date = '', $days = false)
{
	$end_date = ($end_date == '01-Jan-70') ? '' : $end_date;
	if (!empty($start_date) && empty($end_date)) {
		return convertDateYMD('', '>', $start_date);
	} elseif (!empty($start_date) && !empty($end_date)) {
		return "*Between '" . convertDateYMD($start_date, "' and '", $end_date) . "'";
	} elseif ($days) {
		$end_date = (!empty($end_date)) ? $end_date : date('Ymd', strtotime($days . ' days'));
		return convertDateYMD($start_date, '<', $end_date);
	} else {
		return '';
	}
}

/**
 * Check if the given date has already passed based on the given timezone.
 *
 * @param string $new_date The date to be checked in string format ('Y-m-d H:i:s').
 * @param string $time_zone The timezone identifier in which to check the date.
 *
 * @return bool True if the given date has already passed, false otherwise or if an exception occurred.
 */
function isDatePassed($new_date, $time_zone = 'Europe/London')
{
	if ($new_date) {
		try {
			$country_time = new DateTime('now', new DateTimeZone($time_zone));
			$get_datetime = $country_time->format('Y-m-d H:i:s');
			return strtotime($get_datetime) > strtotime($new_date);
		} catch (Exception $e) {
			return false;
		}
	}
	return false;
}

/**
 * This is the function that has been used to join paths.
 *
 * @return string as whole response of function.
 */
function joinPaths()
{
	$args  = func_get_args();
	$paths = array();
	foreach ($args as $arg) {
		$paths = array_merge_uc($paths, (array) $arg);
	}

	$paths2 = array();
	foreach ($paths as $i => $path) {
		$path = trim($path, '/');
		if (strlen($path)) {
			$paths2[] = $path;
		}
	}
	$result = join('/', $paths2); // If first element of old path was absolute, make this one absolute also.
	if (strlen($paths[0]) && substr($paths[0], 0, 1) == '/') {
		return '/' . $result;
	}
	return $result;
}

/**
 * Add a protocol to a URL if it does not already have one.
 * @param string $url The URL to add a protocol to.
 * @param string|false $protocol The protocol to add to the URL. If false, the function will attempt to determine the protocol automatically.
 * @return string The URL with the protocol added.
 */
function addProtocol($url, $protocol = false)
{
	$pre      = '';
	$starturl = substr($url, 0, 8);
	if (strripos($starturl, 'http') === false) {
        // Determine the protocol based on the current request environment.
		if ((isset($_GET['protocol']) && strtolower($_GET['protocol']) == 'https:') || isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
			$pre = 'https:';
		} else {
			$pre = 'http:';
		}
	}
	return $pre . $url;
}

/**
 * Recursive version of print_r that returns the result as an associative array.
 * 
 * @param mixed $in The input variable to print.
 * 
 * @return array The result as an associative array.
 */
function rprint_r($in)
{
	$lines = explode("\n", trim($in));
	if (trim($lines[0]) != 'Array') {
		return $in;
	} else {
		if (preg_match('/(\s{5,})\(/', $lines[1], $match)) {
			$spaces        = $match[1];
			$spaces_length = strlen($spaces);
			$lines_total   = count_uc($lines);
			for ($i = 0; $i < $lines_total; $i++) {
				if (substr($lines[$i], 0, $spaces_length) == $spaces) {
					$lines[$i] = substr($lines[$i], $spaces_length);
				}
			}
		}
		array_shift($lines);
		array_shift($lines);
		array_pop($lines);
		$in = implode("\n", $lines);
		preg_match_all('/^\s{4}\[(.+?)\] \=\> /m', $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		$pos          = array();
		$previous_key = '';
		$in_length    = strlen($in);
		foreach ($matches as $match) {
			$key         = $match[1][0];
			$start       = $match[0][1] + strlen($match[0][0]);
			$pos[$key] = array($start, $in_length);
			if ($previous_key != '') {
				$pos[$previous_key][1] = $match[0][1] - 1;
			}
			$previous_key = $key;
		}
		$ret = array();
		foreach ($pos as $key => $where) {
			$ret[$key] = rprint_r(substr($in, $where[0], $where[1] - $where[0]));
		}
		return $ret;
	}
}


/**
 * Outputs the given variable for debugging purposes only if the server name is 'localhost'.
 * It is print_r with <pre> tags in it running on only local server *
 * @param mixed $a The variable to output.
 * @param bool $isDie Whether to terminate the script after outputting the variable.
 * @param bool $isPre Whether to wrap the output in a <pre> tag for better readability.
 *
 * @return void
 */
function __ucd($a, $isDie = 1, $isPre = 1)
{
	if ($_SERVER['SERVER_NAME'] == 'localhost') {
		if ($isPre) {
			echo '<pre>';
		}
		print_r($a);
		if ($isDie) {
			die;
		}
	}
}

/**
 * Convert a string to an associative array
 *
 * @param string $str The string to convert
 * @param string $sep_1 The separator used to split the string into key-value pairs
 * @param string $sep_2 The separator used to split each key-value pair
 * @return array The resulting associative array
 */
function str2Assoc($str, $sep_1 = ',', $sep_2 = '|')
{
	$arr  = explode($sep_1, $str);
	$item = array();
	foreach ($arr as $key => $value) {
		$v             = explode($sep_2, $value);
		$item[$v[0]] = $v[1];
	}
	return $item;
}

/**
 * Check if a variable is set and not empty, and optionally compare it to a given value.
 *
 * @param mixed $v The variable to check.
 * @param mixed $val The value to compare $v to (optional).
 * @return bool Returns true if $v is set and not empty (and optionally equal to $val), false otherwise.
 */
function ucIsset($v, $val = false)
{
	$ret = isset($v) && $v <> '';
	if ($val && $ret) {
		return $v == $val;
	}
	return $ret;
}

/**
 * This is the function that has been used to return haystack.
 * Searches for the occurrence of a string within a comma-separated list of strings.
 * @param string $needle   The string to search for.
 * @param string $str      The comma-separated list of strings to search in.
 * @param string $delimiter The delimiter used to separate the strings.
 * @return bool True if the string is found in the list, false otherwise.
 */
function ucInStr($needle, $str, $delimiter = ',')
{
	// Split the string into an array using the delimiter.
	$haystack = explode($delimiter, $str);
	// Trim each string in the array.
	$haystack = array_map('trim', $haystack);
	// Check if the needle exists in the haystack.
	return in_array($needle, $haystack);
}

/**
 * Create a table row from an array of data.
 *
 * @param mixed $arr     The data to be used to create the table row. Can be an array or a string.
 * @param bool  $thead   Whether the row is a table header row.
 * @param bool  $istr    Whether to wrap the row in <tr> tags.
 * @return string The HTML string representing the table row.
 */
function ucTblStr($arr, $thead = false, $istr = false)
{
	if ($arr === 1) {
		return "<table border='1' cellpadding='10'>";
	}

	if ($arr === 2) {
		return '</table>';
	}

	$str   = '<tr>';
	$strc  = '</tr>';
	$td[0] = '<td>';
	$td[1] = '</td>';
	$th[0] = '<th>';
	$th[1] = '</th>';

	if ($thead) {
		$td = $th;
	}

	if (is_string($arr)) {
		$ret = $td[0] . $arr . $td[1];
		if ($istr) {
			return $str . $ret . $strc;
		}
		return $ret;
	}

	foreach ($arr as $key => $value) {
		$tdstr = $td[0] . $value . $td[1];
		$str  .= $tdstr;
	}

	return $str . $strc;
}

/**
 * This is the function that has been used to return array by uc items.
 * Returns an array of values from an array of associative arrays *
 * @param array $items Array of associative arrays
 * @param string $item Key name to retrieve from each associative array
 * @return array Array of values for the given key name
 */
function ucItemsByArray($items, $item)
{
	$ret = array();
	foreach ($items as $k => $v) {
		$ret[] = $v[$item];
	}
	return $ret;
}


/**
 * Returns a string between two delimiters in a larger string.
 * 
 * @param string $string The larger string to search in.
 * @param string $start The starting delimiter.
 * @param string $end The ending delimiter.
 * @return string The substring between the two delimiters.
 */
function get_string_between($string, $start, $end)
{
    // Add a space before the string to ensure proper searching.
	$string = ' ' . $string;
    // Find the starting delimiter.
	$ini    = strpos($string, $start);
	if ($ini == 0) {
		return '';
	}
    // Find the ending delimiter and calculate the length of the substring.
	$ini += strlen($start);
	$len  = strpos($string, $end, $ini) - $ini;
    // Return the substring between the two delimiters.
	return substr($string, $ini, $len);
}

/**
 * Return an associative array indexed by a specific item in a nested array.
 *
 * @param array $arr The array to traverse.
 * @param string $item The key to use as the index.
 * @param string|false $val_str A comma-separated list of values to include in the output.
 * @return array An associative array indexed by the specified key.
 */
function ucArrayByItem($arr, $item, $val_str = false)
{
	$ret = array();
	foreach ($arr as $k => $v) {
		$i = $v[$item];
		if ($val_str) {
			$val   = explode(',', $val_str);
			$v_arr = array();
			foreach ($val as $vk => $vv) {
				$v_arr[$vv] = $v[$vv];
			}
			$ret[$i] = $v_arr;
		} else {
			$ret[$i] = $v;
		}
	}
	return $ret;
}

/**
 * Convert a string to its hexadecimal representation.
 *
 * @param string $string The input string to convert.
 * @return string The hexadecimal representation of the input string.
 */
function strToHex($string)
{
	$hex 	= '';
	$length = strlen($string);
	for ($i = 0; $i < $length; $i++) {
		$ord     = ord($string[$i]);
		$hexCode = dechex($ord);
		$hex    .= substr('0' . $hexCode, -2);
	}
	return $hex;
}


if (!function_exists('hex2bin')) {
	/**
	 * Converts a hexadecimal string to its binary representation.
     *
     * @param string $hex The hexadecimal string to convert.
     * @return string The binary representation of the input.
     */
	function hex2bin($hex)
	{
		$bin = pack('H*', $hex);
		return $bin;
	}
}

/**
 * Generates an authentication token using AES-256-CBC encryption.
 *
 * @param string $string The string to encrypt and include in the token.
 * @param string $secret_key The secret key to use for encryption.
 * @return string The generated authentication token.
 */
function generateAuthToken($string, $secret_key)
{
    // Define encryption method and generate an initialization vector.
	$method = 'AES-256-CBC';
	$iv     = strToHex(gen_uuid(16));
    // Encrypt the input string using the secret key and initialization vector.
	$e      = openssl_encrypt($string, $method, hex2bin($secret_key), 0, hex2bin($iv));
    // Combine the initialization vector and encrypted string, and encode as base64.
	$token  = $iv . bin2hex(base64_decode($e));
    // Insert hyphens every 8 characters for readability.
	return implode('-', str_split($token, 8));
}

/**
 * Extracts a string from an encrypted token generated using generateAuthToken function.
 * @param string $encrypt_string The encrypted token string to extract from.
 * @param string $secret_key The secret key used to encrypt the token.
 * @return string|null The decrypted string or null if the token is invalid.
 */
function extractAuthToken($encrypt_string, $secret_key)
{
    // Define the encryption method and remove any dashes from the encrypted string.
	$method         = 'AES-256-CBC';
    // Extract the initialization vector and encrypted data from the token.
	$encrypt_string = str_replace('-', '', $encrypt_string);
	$iv             = substr($encrypt_string, 0, 32);
	$e              = base64_encode(hex2bin(substr($encrypt_string, 32)));
    // Decrypt the encrypted data using the secret key and initialization vector.
	$string         = openssl_decrypt($e, $method, hex2bin($secret_key), 0, hex2bin($iv));
    // Return the decrypted string or null if the token is invalid.
	return $string;
}

/**
 * Merge two arrays, optionally recursively.
 *
 * @param array $arr1 The first array to merge
 * @param array $arr2 The second array to merge
 * @param bool|int $recursive Whether to merge the arrays recursively
 * @return array The merged array
 */
function array_merge_uc($arr1, $arr2, $recursive = false)
{
	if (!is_array($arr1)) {
		$arr1 = array();
	}
	if (!is_array($arr2)) {
		$arr2 = array();
	}
	if ($recursive == 1) {
		return array_merge_recursive($arr1, $arr2);
	} elseif ($recursive > 1) {
		return array_replace_recursive($arr1, $arr2);
	} else {
		return array_merge($arr1, $arr2);
	}
}

/**
 * Returns all the keys or specific keys of an array, optionally filtered by value.
 *
 * @param array $arr The input array.
 * @param mixed $value The value to filter keys by. If set, only keys with this value will be returned.
 * @return array The array keys.
 */
function array_keys_uc($arr, $value = false)
{
	if (!is_array($arr)) {
		$arr = array();
	}

	if ($arr && $value) {
		return array_keys($arr, $value);
	} else {
		return array_keys($arr);
	}
}

/**
 * Removes duplicate values from an array.
 *
 * @param array $arr The input array.
 * @return array The array with duplicate values removed.
 */
function array_unique_uc($arr)
{
	if (!is_array($arr)) {
		$arr = array();
	}
	return array_unique($arr);
}

/**
 * Counts the number of elements in an array.
 *
 * @param array $arr The array to count elements in.
 * @return int The number of elements in the array.
 */
function count_uc($arr)
{
	if (!is_array($arr)) {
		return 0;
	}
	return count($arr);
}

/**
 * Returns a new array with the keys changed to the value of the specified key in the original array
 *
 * @param array $arr The original array to be modified
 * @param string $key The key to use as the new array key
 * @return array The new array with keys changed to the value of $key
 */
function array_change_key_uc($arr, $key)
{

	$result = array();
	if (!is_array($arr)) {
		return $result;
	}
	foreach ($arr as $val) {
		$result[$val[$key]] = $val;
	}
	return $result;
}

/**
 * Sorts a multidimensional array by a specified key
 *
 * @param array $data The array to be sorted
 * @param string $key The key to sort by
 * @param bool $reverse_sort Whether to sort in reverse order
 */
function multi_array_sort(&$data, $key, $reverse_sort = false)
{
	$sort_array   = array();
	$return_array = array();
    // Check for null data and add debug trace
	if (function_exists('addDebugTrace') && !is_array($data)) {
		addDebugTrace('null_data');
	}
	if(!is_array($data)) {
		reset($data);

		foreach ($data as $k => $v) {
			$sort_array[$k] = strtolower($v[$key]);
		}
        // Sort the array based on the key
		!empty($reverse_sort) ? arsort($sort_array) : asort($sort_array);
		foreach ($sort_array as $k => $v) {
			$return_array[$k] = $data[$k];
		}
        // Set the sorted array back to the original data array
		$data = $return_array;
	}
}

/**
 * Obfuscates a given link by encoding it using base64 and rawurlencode.
 *
 * @param string $temp The link to be obfuscated.
 * @return string The obfuscated link.
 */
function obfuscate_link($temp)
{
	$temp = base64_encode($temp);
	$link = rawurlencode($temp);
	return $link;
}

/**
 * Converts a number of seconds into a human-readable string
 *
 * @param string $link denotes link.
 * @return string as overall response of the functions.
 */
function unobfuscate_link($link)
{
	$temp = rawurldecode($link);
	$temp = base64_decode($temp);
	return $temp;
}

/**
 * This is the function that has been used to convert seconds to human.
 *
 * @param string $seconds denotes seconds.
 * @param string $is_short denotes is_short.
 * @param string $return_index denotes return_index.
 * @return string as overall response of the functions.
 */
function seconds2human($seconds, $is_short = true, $return_index = '')
{
	$s          = $seconds % 60;
	$m          = floor(($seconds % 3600) / 60);
	$h          = floor(($seconds % 86400) / 3600);
	$d          = floor(($seconds % 2592000) / 86400);
	$M          = floor($seconds / 2592000);
	$time_array = array();

	if ($M > 0) {
		$time_array['M']  = $M;
		$time_array['M'] .= $is_short ? 'm' : 'months';
	}

	if ($d > 0) {
		$time_array['d']  = $d;
		$time_array['d'] .= $is_short ? 'd' : 'days';
	}

	if ($h > 0) {
		$time_array['h']  = $h;
		$time_array['h'] .= $is_short ? 'h' : 'hours';
	}

	if ($m > 0) {
		$time_array['m']  = $m;
		$time_array['m'] .= $is_short ? 'm' : 'minutes';
	}

	if ($s > 0) {
		$time_array['s']  = $s;
		$time_array['s'] .= $is_short ? 's' : 'seconds';
	}

	if ($return_index) {
		$time_str = $time_array[$return_index];
	} else {
		$time_str = implode(', ', $time_array);
	}
	return $time_str;
}

/**
 * Returns a human-readable string representing the time elapsed since the provided timestamp
 *
 * @param int $time Unix timestamp to calculate time elapsed from
 * @return string Human-readable string representing time elapsed
 */
function friendlyDate($time)
{
    // Convert negative time to positive
	$time   = abs(time() - $time); // @surya: convert negative value into positive.
	// Tokenize time units for conversion
	$tokens = array(
		31536000 => 'year',
		2592000  => 'month',
		604800   => 'week',
		86400    => 'day',
		3600     => 'hour',
		60       => 'minute',
		1        => 'second',
	);
	 // Loop through tokens to find the largest time unit that can be used
	foreach ($tokens as $unit => $text) {
        // Skip this iteration if the time is less than the current unit
		if ($time < $unit) {
			continue;
		}
        // Calculate the number of units of the current type and round the result
		if ($time > 2592000) {
			$numberOfUnits = round($time / $unit, 1);
		} else {
			$numberOfUnits = round($time / $unit, 0);
		}
        // Return the formatted time string
		return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
	}
}

/**
 * Format a given ISBN number into a standard format with hyphens.
 *
 * @param string $isbn The ISBN number to format.
 * @return string The formatted ISBN number.
 */
function formatISBN($isbn)
{
	// Check if ISBN is valid or already formatted.
	if (!$isbn || strpos($isbn, '-') > 0) {
		return $isbn;
	}
	// Format the ISBN with hyphens.
	$formatedISBN = substr($isbn, 0, 3) . '-' . substr($isbn, 3, 1) . '-' . substr($isbn, 4, 5) . '-' . substr($isbn, 9, 3) . '-' . substr($isbn, 12, 1);
	return $formatedISBN;
}

/**
 * Adds files from a directory to a ZIP archive.
 *
 * @param string $filePath The path of the directory containing the files to be added to the archive.
 * @param string $output_archive The path of the ZIP archive to create.
 * @param string|false $output_zip_name The name of the ZIP archive to be downloaded, or false to use the default name.
 * @param bool $download Whether to download the ZIP archive.
 * @param bool $remove Whether to remove the original files after adding them to the archive.
 * @return void
 */
function addToArchive($filePath, $output_archive, $output_zip_name = false, $download = false, $remove = true)
{
	$zip = new ZipArchive();
	$zip->open($output_archive, ZipArchive::CREATE);
	$files = scandir($filePath);
	unset($files[0]);
	unset($files[1]);

	foreach ($files as $file) {
		$content = file_get_contents_uc($filePath . $file);
		$zip->addFromString($file, $content);
		if ($remove) {
			unlink($filePath . $file);
		}
	}

	$zip->close();

	if ($download && file_exists($output_archive)) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . basename($output_zip_name));
		header('Content-length: ' . filesize($output_archive));
		readfile($output_archive);

		if (file_exists($filePath)) {
			rmdir($filePath);
		}

		unlink($output_archive);
	}

	if ($remove && file_exists($filePath)) {
		rmdir($filePath);
	}
}

/**
 * Returns the date after a specified number of days after a given date.
 *
 * @param string $date The date to start from in YYYY-MM-DD format.
 * @param int $daysafter The number of days after the starting date to return (default 0).
 * @return string The resulting date in YYYY-MM-DD format.
 */
function dateafter($date, $daysafter = 0)
{
	return date('Y-m-d', strtotime('+' . $daysafter . ' days', strtotime($date)));
}

/**
 * Check if the given timezone observes Daylight Saving Time (DST).
 *
 * @param string $tzId The timezone identifier (e.g. "America/New_York").
 * @param int|false $time_zone_offset The timezone offset from GMT in hours.
 * @return bool|int Returns true if the timezone observes DST, false if not. If an error occurs, returns false.
 */
function timezoneDoesDST($tzId, $time_zone_offset = false)
{
	try {
        // Set the timestamp to the current time plus the offset if provided.
		$timestamp  = time();
		$timestamp += (($time_zone_offset) * 3600);
        // Create a DateTime object with the given timezone and date.
		$date1      = date('d-M-Y', $timestamp);
		$date       = new DateTime($date1 . ' ' . $tzId);
        // Get the timezone's DST status.
		$tz         = $date->format('I');
		return $tz;
	} catch (Exception $e) {
        // If an error occurs, return false.
		return false;
	}
}

/**
 * Calculates the time difference between two time values
 *
 * @param string $start_time The starting time value
 * @param string $end_time The ending time value
 * @param string $format The output format for the time difference, defaults to '%H:%I:%S'
 *
 * @return string The time difference in the specified format.
 */
function get_time_difference($start_time, $end_time, $format = '%H:%I:%S')
{
	$start_time = substr($start_time, 0, 5);
	$end_time   = substr($end_time, 0, 5);
	if (strtotime($start_time) > strtotime($end_time)) {
		$end_t            = '24:00';
		$start_t          = '00:00';
		$start            = new DateTime($start_time);
		$end              = new DateTime($end_t);
		$diff             = $start->diff($end);
		$first_toal       = $diff->format('%H:%I');
		$start            = new DateTime($start_t);
		$end              = new DateTime($end_time);
		$diff             = $start->diff($end);
		$second_total_hrs = $diff->format('%H');
		$second_total_min = $diff->format('%I');
		$third            = date('H.i', strtotime('+ ' . $second_total_hrs . ' hours', strtotime($first_toal)));
		$final            = date('H.i', strtotime('+ ' . $second_total_min . ' minutes', strtotime($third)));
		return $final;
	} else {
		$start = new DateTime($start_time);
		$end   = new DateTime($end_time);
		$diff  = $start->diff($end);
		return $diff->format($format);
	}
}

/**
*Convert a given time to UTC time
*@param int $time The time to be converted
*@param array $time_zone_dst An array of time zone DST information
*@param int $time_zone The time zone offset in minutes
*@return int The converted UTC time
*/
function convertToUTC($time, $time_zone_dst, $time_zone = false)
{
	// Convert the given time to UTC time
	$utc = $time - ($time_zone * 60);
	if (timezoneDoesDST($time_zone_dst[$time_zone], $time_zone)) {
		$utc -= 60; // utc time is in minutes, if daylight saving time is on then subtract 60 mins to make it utc.
	}
	return $utc;
}

/**
 * Generates an access token for a client with the given ID and email.
 *
 * @param string $client_id The client ID.
 * @param string $email The client email.
 * @param string|false $suffix_str An optional string to add as a suffix to the token.
 * @param int $key_type The type of key to use for the token (default is 1).
 * @return array An array containing the access token and status message.
 */
function generateAccessToken($client_id, $email, $suffix_str = false, $key_type = 1)
{
	$issued_time    	   = time();
	$request['iss'] 	   = $_SERVER['SERVER_NAME'];
	$request['jti'] 	   = $client_id;
	$request['aud'] 	   = $email;
	$request['key_type']   = $key_type;
	$request['iat'] 	   = $issued_time;
	$request['exp'] 	   = $issued_time + (60 * 60 * 1); // 60 minute.
	$token['access_token'] = JWT::encode($request, JWTKEY);
	$token['status']       = 'Success';

	if ($suffix_str) {
		$suffix_split           = '|~'; // Adding a unique suffix spliter, so that can be split later.
		$token['access_token'] .= $suffix_split . $suffix_str;
	}
	return $token;
}

/**
 * Verify the access token using JWT decode
 *
 * @param string $token denotes token.
 * @return string as overall response of the functions.
 */
function verifyAccessToken($token)
{
	return JWT::decode($token, JWTKEY, array('HS256'));
}

/**
 * This is the function that has been used to refresh access token.
 *
 * @param string $token The access token to be verified
 * @return object|bool Returns the decoded token object on success or false on failure
 */
function refreshAccessToken($old_token)
{
	$old_token_detail = verifyAccessToken($old_token);
	if ($old_token_detail['status'] == 'Expired') {
		$token = generateAccessToken($old_token_detail['jti'], JWTKEY);
	} else {
		$token['status'] = 'InvalidOldToken';
		$token['msg']    = 'Old token is invalid';
	}
	return $token;
}


class FRIENDLY_GUID_TYPE
{
	const ALPHA_NUMARIC = 1;
	const ALPHA 		= 2;
	const NUMERIC     	= 3;
}

/**
 * Converts a GUID to a more friendly format by replacing confusing characters.
 *
 * @param string $guid The GUID to convert.
 * @return string The converted GUID.
 */
function friendlyguid($guid)
{
	$guid   = strtoupper($guid);
	$r['I'] = 'L';
	$r['1'] = 'L';
	$r['O'] = 'X';
	$r['Q'] = 'U';
	$r['0'] = 'X';
	$r['5'] = 'W';
	$r['S'] = 'W';
	$r['8'] = 'H';
	$r['B'] = 'H';
	$r['2'] = 'M';
	$r['K'] = 'H';
	foreach ($r as $k => $v) {
		$guid = str_replace($k, $v, $guid);
	}
	return $guid;
}

/**
*Get the user's IP address.
*@return string User's IP address or "UNKNOWN" if unable to detect.
*/ 

function getUserIP()
{
	$ipaddress = 'UNKNOWN';
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	}
	return $ipaddress;
}

/**
 * This is the function that has been used to get columns of array.
*Get an array of values from a specific column in a multi-dimensional array with uppercase column names.
*@param array $array The input array.
*@param string $column The column to retrieve the values from.
*@return array The resulting array of values from the specified column.
*/
function array_column_uc($array, $column)
{
	$result = array();
	if (!is_array($array) || !$column) {
		return $result;
	}
	foreach ($array as $val) {
		$result[] = $val[$column];
	}
	return $result;
}

/**
 * This is the function that has been used to get bin from IP.
*Convert binary representation of IP address to string
*@param string $bin Binary representation of IP address
*@return string Returns string representation of IP address
*/
function bin2ip($bin)
{
	// Check if the binary representation of IP address is IPv6 and remove trailing zeroes
	if (substr($bin, 5, 1) == chr(0)) {
		$bin = substr($bin, 0, 4);
	}
	// Convert binary to string representation of IP address
	$ip = inet_ntop(($bin));
	return $ip;
}

/**
 * Removes whitespace characters from a string.
 * @param string $str The input string to process.
 * @return string The input string with all whitespace characters removed.
 */
function removeStrSpace($str = true)
{
	if (!$str || is_array($str)) {
		return $str;
	}
	$str = trim(preg_replace('/\s/', '', $str));
	return $str;
}

/**
 * Validate a JSON string and return the decoded result.
 * @param string $string The JSON string to validate.
 * @param string $error A reference to a variable that will contain the error message if the JSON is invalid.
 *
 * @return mixed The decoded JSON data if valid, or false if invalid.
 */
function json_validate($string, &$error = '')
{
	// decode the JSON data.
	$result = json_decode($string);

	// Check for possible JSON errors.
	switch (json_last_error()) {
		case JSON_ERROR_NONE:
			$error = ''; 	// JSON is valid.
			break;			// No error has occurred.
		case JSON_ERROR_DEPTH:
			$error = 'The maximum stack depth has been exceeded.';
			break;
		case JSON_ERROR_STATE_MISMATCH:
			$error = 'Invalid or malformed JSON.';
			break;
		case JSON_ERROR_CTRL_CHAR:
			$error = 'Control character error, possibly incorrectly encoded.';
			break;
		case JSON_ERROR_SYNTAX:
			$error = 'Syntax error, malformed JSON.';
			break;
			// PHP >= 5.3.3.
		case JSON_ERROR_UTF8:
			$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
			break;
			// PHP >= 5.5.0.
		case JSON_ERROR_RECURSION:
			$error = 'One or more recursive references in the value to be encoded.';
			break;
			// PHP >= 5.5.0.
		case JSON_ERROR_INF_OR_NAN:
			$error = 'One or more NAN or INF values in the value to be encoded.';
			break;
		case JSON_ERROR_UNSUPPORTED_TYPE:
			$error = 'A value of a type that cannot be encoded was given.';
			break;
		default:
			$error = 'Unknown JSON error occured.';
			break;
	}

	if ($error !== '') {
		return false;
	}

	return $result;
}

/**
 * Resize an image and save it to the specified path.
 *
 * @param string $imgObject The image file path.
 * @param string $savePath The path where the resized image will be saved.
 * @param string $image_type The type of the image, e.g. 'jpg', 'jpeg', 'png', etc.
 * @param string $imgName The name of the resized image file.
 * @param int $imgMaxWidth The maximum width of the resized image.
 * @param int $imgMaxHeight The maximum height of the resized image.
 * @param int $imgQuality The quality of the resized image, from 0 to 100.
 *
 * @return void
 */
function resizeImage($imgObject, $savePath, $image_type, $imgName, $imgMaxWidth, $imgMaxHeight, $imgQuality)
{
	if (function_exists('mime_content_type')) {
		$mimetype = mime_content_type($imgObject);
		if ($mimetype == 'image/png') {
			$from_png = 1;
		}
	}
	if (!$from_png && ($image_type == 'jpg' || $image_type == 'jpeg')) {
		$source = @imagecreatefromjpeg($imgObject);
	} else {
		$from_png = 1;
	}
	if ($from_png == 1 || !$source) {
		$source = @imagecreatefrompng($imgObject);
	}
	list($imgWidth, $imgHeight) = getimagesize($imgObject);

	$imgAspectRatio = $imgWidth / $imgHeight;
	if ($imgMaxWidth / $imgMaxHeight > $imgAspectRatio) {
		$imgMaxWidth = $imgMaxHeight * $imgAspectRatio;
	} else {
		$imgMaxHeight = $imgMaxWidth / $imgAspectRatio;
	}
	$image_p = @imagecreatetruecolor($imgMaxWidth, $imgMaxHeight);
	@imagecopyresampled($image_p, $source, 0, 0, 0, 0, $imgMaxWidth, $imgMaxHeight, $imgWidth, $imgHeight);
	@imagejpeg($image_p, $savePath . $imgName, $imgQuality);
	unset($source);
	unset($image_p);
}

/**
*Find the key from a string
*@param string $str The input string to find the key from
*@param string &$key The variable to store the found key
*@return string The value found after the key
*/
function uf_findkey($str, &$key)
{
	$startpos = strpos($str, ':');

	if (strpos(trim(substr($str, 0, $startpos)), '*') === false) {
		$key = explode('-', strtolower(trim(substr($str, 0, $startpos))));
	} else {
		$key    = explode('*', strtolower(trim(substr($str, 0, $startpos))));
		$key[2] = 1;
	}
	$value = substr($str, $startpos + 1);
	return $value;
}

/**
*Encode an array to UTF-8 encoded JSON string
*@param array $inArray The array to encode
*@return string The encoded JSON string
*/
function utf8json($inArray)
{
    // Initialize an empty array to store encoded values	
	$newArray = array();
	// Return an empty array if the input is not an array
	if (!is_array($inArray)) {
		return $newArray;
	}
	// Set a recursion limit to avoid infinite recursion
	static $depth = 0;

	// Safety recursion limit.
	$depth++;
	// Return false if recursion limit is exceeded
	if ($depth >= '150000') {
		return false;
	}

	// Step through inArray.
	foreach ($inArray as $key => $val) {
	// Encode array elements recursively
		if (is_array($val)) {
			// recurse on array elements.
			$newArray[$key] = utf8json($val);
		} elseif (!is_object($val)) {
			// encode string values.
		// Recurse on array elements
			$newArray[$key] = utf8_encode($val);

		} else {
			$newArray[$key] = $val;
		}
	}

	return $newArray;
}

/**
 * Function to get contentGUIDs based on filters
 *
 * @param {array} course coverage
 * @param {array} seq2coverage
 * @param {string} content_type(s) default is q,u,f i.e. question , quiz and facts
 * @param {string} content_subtype default is fales
 * @param {string} parent_guid
 * @param {string} testno  i.e test number assign to GUID like 1= test A, 2 = Test B -13 = Lab
 * @param {string} level content level like 1 = chapter, 2 = topics etc default is false
 * @param {int} demo is content assign to demo default is -1 i.e. dont care wheater true or false
 * @param {int} visible is content mark as visible default is 1 i.e only visible contne t
 * @returns array list of GUIDS
 */

function getContentsGUIDs($coverage, $seq2coverage, $content_type = 'q,u,f', $content_subtype = false, $parent_guid = false, $testnos = -1, $level = false, $demo = -1, $visible = 1)
{
	$is_quiz     = 0;
	$is_exercise = 0;
	$guids       = array();
	$subtype 	 = array();

	if (!is_array($seq2coverage)) {
		return $guids;
	}

	$testno = array();
	if ($demo == '') {
		$demo = -1;
	}

	if ($visible == '') {
		$visible = 1;
	}

	if ($parent_guid == '') {
		$parent_guid = false;
	}

	if ($testnos == '') {
		$testnos = -1;
	} elseif ($testnos == -4) {
		if ($demo <= 0) {
			$demo = 1;
		}
		$testnos = -1;
	} elseif ($testnos == -3) {
		$content_type = 'q,u'; // @Deepika: added u also for make compatible (assigned as u).
		$testnos      = -1;
		$is_quiz      = 1;
	} elseif ($testnos == -7) {
		$content_type = 'q,u'; // @Deepika: added u also for make compatible (assigned as u).
		$testnos      = -1;
		$is_exercise  = 1;
	}

	if ($testnos <> '-1') {
		$testno = explode(',', $testnos);
	}

	if ($content_subtype == '') {
		$content_subtype = false;
	}

	if ($content_type) {
		$type = explode(',', $content_type);
	}

	if ($content_subtype) {
		$subtype = explode(',', $content_subtype);
	}
	if ($parent_guid) {
		$pguids = explode(',', $parent_guid);
	}
	if ($level) {
		$l = explode(',', $level);
	}

	if ($demo <= 0) {
		$demo_type = array(0, 1, 2);
	} elseif ($demo == 1) {
		$demo_type = array(1);
	} elseif ($demo == 2) {
		$demo_type = array(1, 2);
	}

	$clsV = new clsVisiblity();
	foreach ($seq2coverage as $guid) {
		if (!is_array($coverage[$guid]['v'])) {
			$coverage[$guid]['v'] = $clsV->visiblity2array($coverage[$guid]['v']);
		}

		// if not is array first convert it into array.
		if ($visible == '-2') {
			$shouldadd = true;
		} else {
			$shouldadd = $coverage[$guid]['h'];
		}

		if ($visible <> '-1' && $clsV->isVisible($coverage[$guid]['v'], $visible) == false) {
			$shouldadd = false;
		}

		// If we are getting data for flashcard that is 25, 10. subtype 10 = glossary then check condition e:test_no should be -1 that is glossary is removed from flashcard.
		// But if we are only getting data for glossary ignore this condition and give all glosary.
		if ($shouldadd && !empty($coverage[$guid]['v']['s']) && $content_type == 'f' && $coverage[$guid]['u'] == 10 && $coverage[$guid]['e'] == -1) {
			if (in_array(25, $subtype)) {
				$shouldadd = false;
			}
		}

		if ($shouldadd && $content_type && !in_array($coverage[$guid]['t'], $type)) {
			$shouldadd = false;
		}

		if ($shouldadd && ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['e'] == -3) && $testno && !in_array($coverage[$guid]['e'], $testno)) {
			$shouldadd = false;
		}

		if ($shouldadd && $is_quiz && $coverage[$guid]['t'] == 'q' && $coverage[$guid]['e'] <> -3) {
			$shouldadd = false;
		}

		if ($shouldadd && $is_exercise && ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['t'] == 'u')) {
			if (!empty($coverage[$guid]['v']['s']) && empty($coverage[$guid]['v']['z'])) {
				if ($coverage[$guid]['e'] <> -1) {
					$shouldadd = false;
				}
			} else {
				if ($coverage[$guid]['e'] == -3) {
					$shouldadd = false;
				}
			}
		}

		if ($shouldadd && $level && !in_array($coverage[$guid]['l'], $l)) {
			$shouldadd = false;
		}

		if ($shouldadd && $content_subtype !== false && !in_array($coverage[$guid]['u'], $subtype)) {
			$shouldadd = false;
		}

		if ($shouldadd && $demo <> 0) {
			if (!in_array($coverage[$guid]['d'], $demo_type)) {
				$shouldadd = false;
			}
			if ($demo == 1 && $shouldadd && ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['u'])) {
				if ($visible >= 1) {
					// Deal like new way.
					$shouldadd = !empty($coverage[$guid]['v']['s']) && empty($coverage[$guid]['v']['z']) ? $coverage[$guid]['v']['b'] : $coverage[$guid]['v']['p'];
				}
			}
		}

		if ($shouldadd && $parent_guid) {
			$pids = explode(',', implode(',', $coverage[$guid]['p']));
			if (!array_intersect($pguids, $pids)) {
				$shouldadd = false;
			} else {
				// changing condition for -13 and -14 lab type and ignoreing setting for e vsibiltiy $shouldadd = $coverage[ $guid ]['v']['e'] as it was incorrectly filtering if we provide parent guid and if we do not provid parent guids
				if (in_array('-13', $testno) || in_array('-14', $testno) && $testno && $coverage[$guid]['t'] == 'q') {
					if (!in_array('-13', $testno) && $coverage[$guid]['e'] == -13) {
						$shouldadd = 0;
					}
					if (!in_array('-14', $testno) && $coverage[$guid]['e'] == -14) {
						$shouldadd = 0;
					}
				} elseif ((in_array('-1', $testno) || !$testno) && ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['e'] == -3 || $coverage[$guid]['u'])) {
					if ($visible >= 1 && $demo <= 0) {
						if ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['t'] == 'u') {
							// Deal like new way. We are still checking e in old way for exercise
							$shouldadd = !empty($coverage[$guid]['v']['s']) && empty($coverage[$guid]['v']['z']) ? $coverage[$guid]['v']['b'] : $coverage[$guid]['v']['e'];

							if ($shouldadd) {
								if (!in_array('-13', $testno) && $coverage[$guid]['e'] == -13) {
									$shouldadd = 0;
								}
								if (!in_array('-14', $testno) && $coverage[$guid]['e'] == -14) {
									$shouldadd = 0;
								}
							}
						}
					}
				}
			}
		}

		if ($shouldadd && $testno) {
			if ($shouldadd && ($coverage[$guid]['t'] == 'q' || $coverage[$guid]['u'])) {
				if ($visible >= 1) {
					// Deal like new way.
					$shouldadd = !empty($coverage[$guid]['v']['s']) && empty($coverage[$guid]['v']['z']) ? $coverage[$guid]['v']['b'] : $coverage[$guid]['v']['p'];
				}
			}
		}

		if ($shouldadd) {
			$guids[] = $guid;
		}
	}

	return $guids;
}

/**
 * Remove elements from an array based on keys present in another array.
 *
 * @param array $config_setting denotes config_setting.
 * @param array $coverage is the coverage.
 * @param array $seq2coverage is the seq2coverage.
 * @return array as whole response of function.
 */
function getDisabledGuids($config_setting, $coverage, $seq2coverage)
{
	$invisible 		 = array();
	$addon_invisible = array();

	if (!$config_setting || !is_array($config_setting)) {
		return $invisible;
	}

	$parent_guids = array();
	$off_array    = array(-1);
	foreach ($config_setting as $o => $v) {
		if (is_array($v)) {
			foreach ($v as $guid => $sdata) {
				if ($o == 'b' && strlen($guid) == 5 && in_array($sdata['v'], $off_array)) {
					$all_contents = array();
					if ($coverage[$guid]['t'] == 's') {
						$parent_guids[] = $guid;
					}
					$invisible[$guid] = $guid;
				} elseif ($o == 'b') { // Deepika: checking f,q,u off for on chapter.
					if ($coverage[$guid]['t'] == 's') {
						$pguid = $guid;
					}
					if (isset($sdata['f']) && in_array($sdata['f']['v'], $off_array)) {
						$addon_invisible['f'][] = $pguid;
					}
					if (isset($sdata['u']) && in_array($sdata['u']['v'], $off_array)) {
						$addon_invisible['u'][] = $pguid;
					}
					if (isset($sdata['q']) && in_array($sdata['q']['v'], $off_array)) {
						$addon_invisible['q'][] = $pguid;
					}
				} elseif (isset($sdata['v']) && in_array($sdata['v'], $off_array) && $guid <> '0' && $guid < 99) {
					$invisible[$guid] = $guid;
				}
			}
		}
	}

	if ($parent_guids) {
		$content = getContentsGUIDs($coverage, $seq2coverage, false, false, implode(',', $parent_guids), -1, false, -1, -2);
		if ($content) {
			$all_contents = array_flip($content);
			$invisible    = $invisible + $all_contents; // @Deepika: for maintain index (-2,-4).
		}
	}
	if ($addon_invisible) {
		$addon_content = array();
		if (isset($addon_invisible['f'])) { // Flashcard
			$addon_content = getContentsGUIDs($coverage, $seq2coverage, 'f', false, implode(',', $addon_invisible['f']), -1, false, -1, -2);
			$addon_content = array_flip($addon_content);
			$invisible     = $invisible + $addon_content;
		}
		if (isset($addon_invisible['u'])) { // Quiz
			$addon_content = getContentsGUIDs($coverage, $seq2coverage, 'u', false, implode(',', $addon_invisible['u']), -3, false, -1, -2);
			$addon_content = array_flip($addon_content);
			$invisible     = $invisible + $addon_content;
		}

		if (isset($addon_invisible['q'])) { // Exercise
			$addon_content = getContentsGUIDs($coverage, $seq2coverage, 'q', false, implode(',', $addon_invisible['q']), '0', false, 0, -2);
			$addon_content = array_flip($addon_content);
			$invisible     = $invisible + $addon_content;
		}
	}
	return $invisible;
}

/**
 * This function eliminate invisible guids from the question list
 *
 * @param array   $ques is the ques.
 * @param array   $invisible is the invisible.
 * @param boolean $only_array is the boolean.
 * @return array as response of the function.
 */
function removeInvisible($ques, $invisible, $only_array = false)
{
	$newlist = array();
	if ($only_array) {
		foreach ($ques as $guid) {
			if (!isset($invisible[$guid])) {
				$newlist[] = $guid;
			}
		}
	} else {
		foreach ($ques as $guid => $val) {
			if (!isset($invisible[$guid])) {
				$newlist[$guid] = $val;
			}
		}
	}
	return $newlist;
}

/**
 * Deletes a given content GUID from a coverage array.
 *
 * @param array $coverage The coverage array to modify.
 * @param string $content_guid The content GUID to remove from the coverage.
 *
 * @return array The modified coverage array.
 */
function delete_from_coverage($coverage, $content_guid)
{
	// If the content GUID is empty, return the original coverage array.
	if ($content_guid == '') {
		return $coverage;
	}
	$newcvstr     = array();
	$s            = 0;
	$totalrow     = count_uc($coverage);
	$insert       = false;
	$inserted     = false;
	$parent_level = 0;
	foreach ($coverage as $cvstr) {
		if (strlen($content_guid) == 5 && strpos($cvstr, $content_guid . '|') > 0) {
		} else {
			$newcvstr[] = $cvstr;
			$s++;
		}
	}
	return $newcvstr;
}

/**
* Sets answer sequence for an array of answers.
 * 
 * @param array $answers The array of answers to set sequence for.
 * @param int $total_answers The total number of answers.
 * @param int $correct_answers The total number of correct answers.
 * @param int $ansSeqType The type of answer sequence to use.
 * @return array The updated array of answers with sequence.
 */
function setAnswerSeq($answers, &$total_answers = 0, &$correct_answers = 0, $ansSeqType = 0)
{
	$i               = 0;
	$total_answers   = 0;
	$correct_answers = 0;

	if (is_array($answers)) {
		foreach ($answers as $akey => $a) {
			$total_answers++;
			if ($a['is_correct'] >= 1) {
				$correct_answers++;
			}

			$answer                      = $a['answer'];
			$answers[$akey]['seq_str'] = getAnswerSeqStr($i + 1, $ansSeqType);
			$aotpos                      = strpos($answer, '<!$');
			if ($aotpos === false) {
				$aotpos = strpos($answer, '<seq no=');
				if ($aotpos === false) {
					$answers[$akey]['seq_tag'] = '<seq no="' . chr(97 + $i) . '" />';
				} else {
					$aotendpos                   = strpos($answer, '/>');
					$answers[$akey]['seq_tag'] = substr($answer, $aotpos, $aotendpos - $aotpos + 2);
				}
			} else {
				$answers[$akey]['seq_tag'] = substr($answer, $aotpos, 5);
			}
			$i++;
		}
	} else {
		$answers = array();
	}
	return $answers;
}

/**
 *Returns the answer sequence string based on the given parameters.
 *
 * @param string $seq is the seq.
 * @param string $type is the type.
 * @param string $flip is the flip.
 * @return array as response of the function.
 */
function getAnswerSeqStr($seq, $type = 0, $flip = false)
{
	if ($type == 1) {
		return $seq;
	} elseif ($type == 2) {
		$ansSeq0[1]  = 'I';
		$ansSeq0[2]  = 'II';
		$ansSeq0[3]  = 'III';
		$ansSeq0[4]  = 'IV';
		$ansSeq0[5]  = 'V';
		$ansSeq0[6]  = 'VI';
		$ansSeq0[7]  = 'VII';
		$ansSeq0[8]  = 'VIII';
		$ansSeq0[9]  = 'IX';
		$ansSeq0[10] = 'X';
		if ($flip) {
			$rAnsSeq0 = array_flip($ansSeq0);
			return $rAnsSeq0[$seq];
		}
		return $ansSeq0[$seq];
	} else {
		$ansSeq0[1]  = 'A';
		$ansSeq0[2]  = 'B';
		$ansSeq0[3]  = 'C';
		$ansSeq0[4]  = 'D';
		$ansSeq0[5]  = 'E';
		$ansSeq0[6]  = 'F';
		$ansSeq0[7]  = 'G';
		$ansSeq0[8]  = 'H';
		$ansSeq0[9]  = 'I';
		$ansSeq0[10] = 'J';
		return $ansSeq0[$seq];
	}
}

/**
 * This class is used for cls visibility.
 */
class clsVisiblity
{
	/*
	position|detail|code|default
	0 none
	1 book        (b)          default 1 
	2 flashcard   (f)          default 1
	3 excersize   (e)          default 1
	4 practice    (p)          default 1
	5 assignment  (a)          default 1
	6 movable     (m)          default 1
	7 borrow      (r)          default 0
	8 borrowing-approved (l)   default 0
	9 knowledge-check (x)      default 0
	10 not defined(y)          default 0
	11 not defined(z)          default 0
	12 version    (s)          default 0

	If [s] = version is 1 then [b] is visible for all type of content and [z] is_draft.
	default value for not visible "0"
	default value for default visible "0)"
	*/

	/**
	 * This is the va.
	 *
	 * @var $va is the va.
	 */
	public $va = false;

	/**
	 * This is the vt.
	 *
	 * @var $vt is the vt.
	 */
	public $vt = 0;

	/**
	 * This is the maxn.
	 *
	 * @var $maxn is the maxn.
	 */
	public $maxn = 0;

	/**
	 * It is the magic function that has been used to set values of the property.
	 */
	public function __construct()
	{
		$this->va   = explode(',', 'b,f,e,p,a,m,r,l,x,y,z,s');
		$this->vt   = count_uc($this->va);
		$this->maxn = 64 * 64;
	}

	/**
	 * It is the m,agic function that has been used to set values of the property.
	 *
	 * @param array $visiblity is the visibility.
	 * @return array as overall response of the function.
	 */
	public function visiblity2array($visiblity)
	{
		if (is_array($visiblity)) {
			// nothing to do already in array().
			return $visiblity;
		}
		$vdata = array();
		for ($i = 0; $i < $this->vt; $i++) {
			$vdata[$this->va[$i]] = 0;
			if ($visiblity < 0) {
				return $vdata;
			}
		}
		$vno = GUID64_decode($visiblity);
		if ($vno > $this->maxn) {
			$vno = $this->maxn;
		}
		for ($i = 0; $i < $this->vt; $i++) {
			$mask = 1 << $i;
			$val  = $vno & $mask;
			if ($val == pow(2, $i)) {
				$t = 1;
			} else {
				$t = 0;
			}
			$vdata[$this->va[$i]] = $t;
		}
		return $vdata;
	}

	/**
	 * It is the m,agic function that has been used to convert array into visibility.
	 *
	 * @param array $vdata is the vdata.
	 * @return array as overall response of the function.
	 */
	public function array2Visiblity($vdata)
	{
		// @pete speed is slow we need to make it faster.
		if (!is_array($vdata)) {
			if ($vdata == -1 || $vdata === '0' || $vdata === 0) {
				return 0;
			} elseif ($vdata == '' || $vdata == '*') {
				return 1;
			} else {
				return $vdata;
			}
		}
		$vno = 0;
		for ($i = 0; $i < $this->vt; $i++) {
			if (isset($vdata[$this->va[$i]]) && $vdata[$this->va[$i]]) {
				$mask = 1 << $i;
				$vno  = $vno | $mask;
			}
		}
		$visiblity = GUID64_encode($vno, 2);
		return $visiblity;
	}

	/**
	 * It is the m,agic function that has been used to convert array into not visibility.
	 *
	 * @param array  $vdata is the vdata.
	 * @param string $content_type is the content_type.
	 * @param string $visiblity is the visiblity.
	 * @return array as overall response of the function.
	 */
	public function array2NOTVisiblity($vdata, $content_type = 'f', $visiblity = '1')
	{
		if (count_uc($vdata) <= 0) {
			if ($content_type == 'f') {
				$vdata[] = 'b';
			} elseif ($content_type == 'q' || $content_type == 'u') {
				$vdata[] = 'p';
			} elseif ($content_type == 's') {
				$vdata[] = 's';
			}
		}
		$vcode = $this->visiblity2array($visiblity);
		foreach ($vdata as $v) {
			$vcode[$v] = 0;
		}
		return $this->array2Visiblity($vcode);
	}

	/**
	 * It is the m,agic function that has been used to check is visible.
	 *
	 * @param array  $vdata is the vdata.
	 * @param string $vtype is the vtype.
	 * @return array as overall response of the function.
	 */
	public function isVisible($vdata, $vtype = '')
	{
		if (!is_array($vdata)) {
			if ($vdata == 0) {
				return 0;
			} else {
				return 1;
			}
		}

		if ($vtype == '-1') {
			return 1;
		}

		// If z value is 1 then content is in the draft then don't show it.
		if (($vdata['s'] == 1 && $vdata['z'] == 1) && $vtype <> -2) {
			return 0;
		}

		if ($vtype == '' || $vtype == '1') {
			if ($vdata['b'] + $vdata['f'] + $vdata['e'] + $vdata['p'] + $vdata['a'] + $vdata['x'] + $vdata['y'] + $vdata['z'] >= 1) {
				return 1;
			}
		} else {
			$vt  = explode(',', $vtype);
			$ret = 1;
			foreach ($vt as $v) {
				if (isset($vdata[$v]) && $vdata[$v] == 0 && $vdata['s'] == 0) {
					$ret = 0;
				}
				return $ret;
			}
		}

		return 0;
	}
}

/**
*Get the default grade formula in a JSON-encoded string or as an array
*@param bool $is_array Flag to determine if the formula should be returned as an array
*@return string|array The default grade formula in a JSON-encoded string or as an array, based on the $is_array flag
*/
function get_grade_formula_default($is_array = false)
{
	$gt = '{"f":"t","n":{"h":{"n":"Homework","w":15},"e":{"n":"Exercise","w":25},"q":{"n":"Quiz","w":10},"t":{"n":"Test","w":40},"0":{"n":"Other","w":10}}}';
	if ($is_array) {
		return json_decode_uc($gt, true);
	}
	return $gt;
}

/**
 * Returns the default grade scale as a JSON string or array.
 * @param bool $is_array If true, returns the grade scale as an associative array. Default is false.
 * 
 * @return string|array Returns the default grade scale as a JSON string or array, depending on the value of $is_array.
 */
function get_grade_scale_default($is_array = false)
{
	$gs = '{"f":"p","s":{"A":{"max":100,"min":93},"A-":{"max":92,"min":90},"B+":{"max":89,"min":87},"B":{"max":86,"min":83},"B-":{"max":82,"min":80},"C+":{"max":79,"min":77},"C":{"max":76,"min":73},"C-":{"max":72,"min":70},"D+":{"max":69,"min":67},"D":{"max":63,"min":66},"D-":{"max":62,"min":60},"F":{"max":59,"min":0}},"m":95}';
	if ($is_array) {
		return json_decode_uc($gs, true);
	}
	return $gs;
}

/**
 * It is the function that has been used to get default config detail.
*Returns the default course configuration*@return string The default course configuration in JSON format
*/
function get_course_config_default()
{
	$cf = '{"ps":'.DEFAULT_PASSING_SCORE.',"ta":120,"tq":50,"nobj":0,"mins":0,"maxs":1000}';
	return $cf;
}

/**
 * Returns the default coverage for a course, in JSON format.
 *
 * @param string $chapter_guid The GUID of the chapter to include in the coverage.
 * @return string The default coverage, in JSON format.
 */
function get_coverage_default($chapter_guid = '')
{
    // Initialize variables
	$fc  = '';
	$seq = 0;
    // If a chapter GUID is provided, add it to the coverage
	if ($chapter_guid <> '') {
		$seq++;
		$fc = ',"1|' . $seq . '|' . $chapter_guid . '|s|1|0|0|1)"';
	}
    // Add the default coverage data
	$seq1 = $seq + 1;
	$seq2 = $seq + 2;
	$fc   = $fc . ',"1|' . $seq1 . '|00001|x|0|0|0|1","2|' . $seq2 . '|00002|x|0|0|0|1)"';
	$cv   = '{"ver":1,"data":["level|sequence|content_guid|content_type|content_subtype|demo|test|visibility"' . $fc . ']}';
    // Return the coverage as a JSON string
	return $cv;
}

/**
 * Returns the name of a test based on its type and optional details.
 *
 * @param int $ttype The type of test.
 * @param string $detail Optional details about the test.
 * @param bool $isShort Whether to return the short version of the test name.
 * @param array $testName Optional array of custom test names.
 * @return string The name of the test.
 */
function getTestName($ttype, $detail = '', $isShort = false, $testName = array())
{
	global $l;
	$testList[0]   = $l['custom_test_txt'];
	$testList[-1]  = $l['exercise'];
	$testList[-2]  = $l['post_assessment_txt']	;
	$testList[-3]  = $l['interactive_quiz_lbl'];
	$testList[-4]  = $l['pre_assess'];
	$testList[-5]  = $l['prepengine'];
	$testList[-6]  = $l['adaptive_test'];
	$testList[-7]  = $l['interactive_quiz_lbl'];
	$testList[-9]  = $l['assignment_label'];
	$testList[-10] = $l['sp_knowledge'];
	$testList[-11] = $l['lab_small'];
	$testList[-12] = $l['assignment_label'];
	$testList[-13] = $l['lab_small'];
	$testList[-21] = $l['custom'];

	$testShortList[-3] = $l['quiz'];
	$testShortList[-4] = $l['pre_assess'];
	$testShortList[-6] = $l['adaptive'];
	$testShortList[-9] = $l['assignment_label'];

	if (isset($testName[$ttype]) && $testName[$ttype] <> '') {
		$tstr = $testName[$ttype];
	} else {
		if ($ttype >= 1) {
			if ($isShort) {
				$tstr = $l['test'] . ' ' . chr($ttype + 64);
			} else {
				$tstr = $l['practice_test'] . ' ' . chr($ttype + 64);
			}
		} elseif (isset($testList[$ttype])) {
			if ($isShort && isset($testShortList[$ttype])) {
				$tstr = $testShortList[$ttype];
			} else {
				$tstr = $testList[$ttype];
			}
		} else {
			$tstr = $l['custom_test_txt'];
		}
	}

	if ($detail != '') {
		if (strlen($detail) > 35) {
			$detail = substr($detail, 0, 32) . '...';
		}
		$tstr .= ": $detail";
	}
	return $tstr;
}

/**
*Pads the given number with leading zeros until it reaches the given length.
*@param string $n The number to pad with leading zeros.
*@param int $a The desired length of the resulting string.
*@return string The padded string.
*/
function zfill($n, $a)
{
	return str_repeat('0', max(0, $a - strlen($n))) . $n;
}

/**
*Returns a value within a specific range, or a default value if not set
*@param mixed $var The value to check
*@param mixed $min The minimum value of the range
*@param mixed $max The maximum value of the range
*@param mixed $default The default value to return if $var is not set
*@return mixed The value within the specified range or the default value
*/
function get_range($var, $min, $max, $default)
{
	if (!isset($var)) {
		$var = $default;
	}
	$var = min($max, max($min, $var));
	return $var;
}

/**
*Returns a default value if the primary value is empty, else returns the primary value.
*@param mixed $primary_value The primary value to be checked for empty.
*@param mixed $default_value The default value to be returned if primary value is empty.
*@param mixed $second_value An optional secondary value to be used if primary value is empty.
*@return mixed The primary value if not empty, otherwise returns the default value or the secondary value.
*/
function get_default($primary_value, $default_value, $second_value = 0)
{
	if (!$primary_value && $second_value) {
		$primary_value = $second_value;
	}
	if (!$primary_value) {
		$primary_value = $default_value;
	}
	return $primary_value;
}

/**
 * Replaces constants in a string with their corresponding values.
 *
 * @param string $ls_online The string to search and replace.
 * @param bool $nolinebreak Optional. If true, removes line breaks from the string.
 * @return string The string with constants replaced by their corresponding values.
 */
function replaceconst($ls_online, $nolinebreak = false)
{
	global $gs_app_path;

	$ls_online = trim($ls_online);
	$ls_online = replacelinebreak($ls_online, $nolinebreak);
	$ls_online = replaceimage($ls_online);

	$searchstr  = '%prepenginepath%';
	$replacestr = $gs_app_path;
	$ls_online  = str_replace($searchstr, $replacestr, $ls_online);

	$searchstr  = '%prepkitpath%';
	$replacestr = $searchstr . MEDIA_URL;
	$ls_online  = str_replace($searchstr, $replacestr, $ls_online);

	return $ls_online;
}

/**
 * Replaces images in a string with secure URLs, if necessary.
 *
 * @param string $str The string to replace images in.
 * @return string The string with replaced images.
 */
function replaceimage($str)
{
	global $_ISCONTENTSECURE;
	preg_match_all("/<img.*?src=[\"'](.+?)[\"'].*?>/", $str, $imgs);
	if ($imgs) {
		$imgs = array_unique($imgs[1]);
		foreach ($imgs as $src) {
			if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0 || strpos($src, '//') === 0) {
			} else {
				if ($_ISCONTENTSECURE) {
					$str = str_replace($src, '//digital-onlinelib.s3.amazonaws.com/' . $src, $str);
				} else {
					$str = str_replace($src, MEDIA_URL . $src, $str);
				}
			}
		}
	}
	return $str;
}

/**
 * Replace line break tags and syntax tags with proper <pre> tags for syntax highlighting
 *
 * @param string $str is the str.
 * @param string $nolinebreak is the nolinebreak.
 * @return string as response of the function.
 */
function replacelinebreak($str, $nolinebreak = false)
{
	$ls_online = trim($str);
	preg_match_all('/\<uc:syntax num=.*?\>/', $ls_online, $matches, PREG_SET_ORDER);
	foreach ($matches as $val) {
		$r   = $val[0];
		$int = filter_var($r, FILTER_SANITIZE_NUMBER_INT);
		$by  = '<pre class="prettyprint linenums:' . $int . ' linenums">';
		if (strstr($r, 'console')) {
			$by = '<pre class="prettyprint black linenums:' . $int . ' linenums">';
		}
		$ls_online = str_replace($r, $by, $ls_online);
	}
	$ls_online = str_replace('<uc:syntax>', '<pre class="prettyprint linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax console>', '<pre class="prettyprint black linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax console="">', '<pre class="prettyprint black linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax command>', '<pre class="prettyprint cmd linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax command="">', '<pre class="prettyprint cmd linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax white>', '<pre class="prettyprint white linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax white="">', '<pre class="prettyprint white linenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax nonum>', '<pre class="prettyprint">', $ls_online);
	$ls_online = str_replace('<uc:syntax nonum="">', '<pre class="prettyprint">', $ls_online);
	$ls_online = str_replace('<uc:syntax console nonum>', '<pre class="prettyprint black">', $ls_online);
	$ls_online = str_replace('<uc:syntax console="" nonum="">', '<pre class="prettyprint black">', $ls_online);
	$ls_online = str_replace('<uc:syntax nonum white>', '<pre class="prettyprint white">', $ls_online);
	$ls_online = str_replace('<uc:syntax nonum="" white="">', '<pre class="prettyprint white">', $ls_online);
	$ls_online = str_replace('<uc:syntax command nonum>', '<pre class="prettyprint cmd">', $ls_online);
	$ls_online = str_replace('<uc:syntax command="" nonum="">', '<pre class="prettyprint cmd">', $ls_online);
	$ls_online = str_replace('<uc:syntax hidelinenums>', '<pre class="prettyprint linenums hidelinenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax hidelinenums="">', '<pre class="prettyprint linenums hidelinenums">', $ls_online);
	$ls_online = str_replace('<uc:syntax console="" hidelinenums="">', '<pre class="prettyprint black hidelinenums prettyprintReplica">', $ls_online);
	$ls_online = str_replace('<uc:syntax nonum="" console="">', '<pre class="prettyprint black hidelinenums">', $ls_online);
	$ls_online = str_replace('</uc:syntax>', '</pre>', $ls_online);
	$ls_online = str_replace('linenums">' . "\r", 'linenums">', $ls_online);
	$ls_online = str_replace('linenums">' . "\n", 'linenums">', $ls_online);

	$p_found = 0;

	$tagfound = $p_found + strpos(strtolower($ls_online), '<br>') + strpos(strtolower($ls_online), '<br/>');
	if ($tagfound > 0) {
	} else {
		if (!$nolinebreak) {
			$ls_online = str_replace("\r\n", "\n", $ls_online);
			$ls_online = str_replace("\r", "\n", $ls_online);
			$ls_online = str_replace("\r\n", "\n", $ls_online);

			$ls_online = str_replace("\r", '', $ls_online);
			$ls_online = str_replace("\n<t", '<t', $ls_online);
			$ls_online = str_replace("\n</t", '</t', $ls_online);

			$ls_online = str_replace("\n<li>", '<li>', $ls_online);
			$ls_online = str_replace("<li>\n", '<li>', $ls_online);

			$ls_online = str_replace("\n</li>", '</li>', $ls_online);
			$ls_online = str_replace("</li>\n", '</li>', $ls_online);
			$ls_online = str_replace("</ul>\n", '</ul>', $ls_online);
			$ls_online = str_replace("\n</ul>", '</ul>', $ls_online);

			$ls_online = str_replace("</ol>\n", '</ol>', $ls_online);
			$ls_online = str_replace("\n</ol>", '</ol>', $ls_online);

			$ls_online = str_replace("\n", '<BR>', $ls_online);
			$ls_online = str_replace('</li><BR>', '</li>', $ls_online);
		}
	}
	$searchStr  = '<!lc>';
	$replacestr = "\n";
	$ls_online  = str_replace($searchStr, $replacestr, $ls_online);

	$ls_online = str_replace($searchStr, $replacestr, $ls_online);
	$ls_online = str_replace($searchStr, $replacestr, $ls_online);

	return $ls_online;
}

/**
 * Converts a byte to an array of bits, using a given default array as a template.
 *
 * @param int $byte The byte to convert.
 * @param array $defarray The default array to use as a template.
 * @param int $len The length of the byte in bytes (default: 1).
 * @return array The resulting array of bits.
 */
function byte2array($byte, $defarray, $len = 1)
{
    // Convert the byte to binary and pad it to the correct length
	$bin = decbin((int) $byte);
	$bin = strrev(str_pad($bin, 8 * $len, '0', STR_PAD_LEFT));
	$str = str_split($bin);
	$i   = 0;
	$options = array();
	if(is_array($defarray)) {
		foreach ($defarray as $name => $def) {
			$options[$name] = $str[$i];
			$i++;
		}
	}
	
	return $options;
}

function arrayFilterBasedOnKey($convert_array , $target_array, $seprator = ', '){
	$options =array();
	if(!is_array($convert_array)){
		return $options;
	}
	foreach($convert_array as $key=>$val){
		if($val == 1 &&  isset($target_array[$key])){
			$options[$key] = $target_array[$key];
		}
	}

	if($seprator) {
		$options = implode($seprator,$options);
	}
	return $options;
}
/**
 * This function is used to convert array into byte.
 *
 * @param string $option is the option.
 * @param array  $defarray is the defarray.
 * @param string $len is the len.
 * @return string as response of the function.
 */
function array2byte($option, $defarray, $len = 1)
{
	$ary = array_merge_uc($defarray, $option);
	foreach ($ary as $k => $v) {
		if ($v <> '1') {
			$ary[$k] = '0';
		} else {
			$ary[$k] = 1;
		}
	}
	$str = '';
	foreach ($defarray as $name => $value) {
		$str .= $ary[$name];
	}
	$str = strrev(str_pad($str, 8 * $len, '0', STR_PAD_RIGHT));
	$bin = bindec($str);
	return $bin;
}

/**
 * This function is used to get ddl data.
 *
 * @param array  $array is the array.
 * @param string $idcolumn is the idcolumn.
 * @param string $datacolumn is the datacolumn.
 * @param string $ddl is the ddl.
 * @return array as response of the function.
 */
function get_ddl_data_uc($array, $idcolumn, $datacolumn, $ddl = 1)
{
	$list                = array();
	$is_multiple_columns = strpos($datacolumn, ',');
	foreach ($array as $d) {
		if ($d[$idcolumn] <> '') {
			if ($is_multiple_columns) {
				$idindex = $d[$idcolumn];
				unset($d[$idcolumn]);
				if ($ddl > 1) {
					$list[$idindex] = $d;
				} else {
					$list[$idindex] = implode(' ', $d);
				}
			} else {
				$list[$d[$idcolumn]] = $d[$datacolumn];
			}
		} else {
			$list[] = $d[$datacolumn];
		}
	}
	return $list;
}

/**
*Get FlashCard content subtypes.
*@param bool $ret_array Whether to return an array or a comma-separated string.
*@return mixed An array or a comma-separated string of content subtypes.
*/
function getFlashCardContentSubtypes($ret_array = false)
{
	// remvoe type 46, changed it to 10 in db
	$type = '10,25';
	if ($ret_array) {
		return explode(',', $type);
	}
	return $type;
}

/**
 * This function is used to extract array.
 * @param array $arr The input array
 * @param string $indexes A comma-separated list of indexes to extract
 * @param bool $ignore_blank Whether to ignore blank values or not
 * @return array The resulting array with extracted values
 */
function extractArray($arr, $indexes = '', $ignore_blank = false)
{
	$result = array();
	if ($indexes) {
		$index_arr = explode(',', $indexes);
		foreach ($index_arr as $index) {
			if ($ignore_blank) {
				if ($arr[$index] <> '') {
					$result[$index] = $arr[$index];
				}
			} else {
				$result[$index] = $arr[$index];
			}
		}
		return $result;
	}
	return $arr;
}

/**
 * This function is used to unset array data.
 * @param array $arr The input array
 * @param string $indexes A comma-separated list of indexes to be unset
 * @return array The input array with specified indexes unset
 */
function unsetArrayData(array $arr, string $indexes)
{
	$result    = array();
	$index_arr = explode(',', $indexes);
	foreach ($index_arr as $index) {
		if (array_key_exists($index, $arr)) {
			unset($arr[$index]);
		}
		$result = $arr;
	}
	return $result;
}

/**
 * Returns the file extension of a given filename
 * @param string $filename The filename to extract extension from
 * @return string|void The file extension, or void if the input is empty or invalid
 */
function findexts($filename)
{
	if (!$filename || $filename == '' || is_array($filename)) {
		return;
	}
	$filename = strtolower($filename);
	$exts     = mb_split('[/\\.]', $filename);
	$n        = count_uc($exts) - 1;
	$exts     = $exts[$n];
	return $exts;
}

/**
 * Get the file extension and name from a file path.
 * @param string $file_name The file path.
 *
 * @return array An array containing the file extension and name.
 */
function get_file_extension($file_name)
{
	$str['ext']  = findexts($file_name);
	$str['name'] = substr($file_name, 0, strlen($file_name) - strlen($str['ext']) - 1);
	return $str;
}

/**
 * A function to Get Sequence List of Guid.
 * Initialises variable $start_slide with $coverage guid[s] and $slide_level with $coverage guid[l] plus 1.
 * Takes a loop for seq2coverage and checks if seq is greater than $start_slide and
 * $coverage guid[l] is equal to $slide_level then checks $coverage guid[t] equal to s then
 * assigns guid value to pslide.
 * Else checks if $coverage guid[t] value is equal to f and $content guid[content_subtype] is
 * greater than or equal to 9 or equal to 45 assigns guid value to pslide and lastly checks for value equal to u and
 * and then return $pslide.
 *
 * Get sequence list based on coverage and seq2coverage array
 *
 * @param string $guid The guid of the starting slide
 * @param array $coverage An array containing information about the coverage of each slide
 * @param array $seq2coverage An array mapping sequences to slide guids
 * @return array An array of slide guids
 */
function getSeqList($guid, $coverage, $seq2coverage)
{
	if (!is_array($seq2coverage)) {
		return array();
	}
	global $course;
	$i           = 0;
	$start_slide = $coverage[$guid]['s'];
	$slide_level = $coverage[$guid]['l'] + 1;
	$i           = 1;
	$pslide[]    = $guid;
	foreach ($seq2coverage as $seq => $guid) {
		if (($seq > $start_slide) && ($coverage[$guid]['l'] >= $slide_level)) {
			if ($coverage[$guid]['t'] == 's' || $coverage[$guid]['t'] == 'a') {
				$pslide[] = $guid;
			} elseif ($coverage[$guid]['t'] == 'f' && ($coverage[$guid]['u'] <= 10 || ($coverage[$guid]['u'] >= 45 && $coverage[$guid]['u'] <> 46))) {
				$pslide[] = $guid;
			}
		} elseif ($seq > $start_slide && $coverage[$guid]['l'] < $slide_level) {
			return $pslide;
			exit;
		}
	}
	return $pslide;
}

/**
 * This function is used to get order type.
 *
 * @return array as overall response of the function.
 */
function getOrderType()
{
	$order_book_type['o'] = 'Order';
	$order_book_type['q'] = 'Quote';
	$order_book_type['m'] = 'MoneyRequest';
	$order_book_type['r'] = 'Refund';
	$order_book_type['n'] = 'Payment Received/Given';
	$order_book_type['l'] = 'ShoppngCart Log';
	$order_book_type['u'] = 'Undefined/Other';
	$order_book_type['i'] = 'In Process';
	$order_book_type['d'] = 'Eval Copy';
	$order_book_type['f'] = 'Instructor';
	$order_book_type['v'] = 'Invoice';
	$order_book_type['y'] = 'Royalty Report From Reseller';
	$order_book_type['c'] = 'Cash Memo';
	$order_book_type['1'] = 'Bill me later: Enroll';
	$order_book_type['2'] = 'Payment Received: Enroll';
	$order_book_type['3'] = 'Bill me later: Voucher';
	$order_book_type['4'] = 'Payment Received: Voucher';
	$order_book_type['5'] = 'Bill me later: Course Access Link';
	$order_book_type['6'] = 'Payment through Royalty/Sale';
	$order_book_type['7'] = 'Student will pay:Enroll';
	$order_book_type['9'] = 'Student will pay: Course Access Link';

	return $order_book_type;
}

/**
 * Calculate the net price and discount information based on the given data.
 *
 * @param array $data is the input array.
 * @return array as overall response of the function.
 */
function calcPrice($data)
{
	if(!isset($data['price']))
	{
		return $data;
	}
	// array with key as price and discount.
	$data['discount']         = min($data['discount'], $data['price']);
	$data['discount_amount']  = 0;
	$data['discount_percent'] = 0;
	$data['price']            = max(0, $data['price']);

	if ($data['price']) {
		if ($data['discount'] > 1) {
			$data['discount_amount']  = $data['discount'];
			$data['discount_percent'] = round($data['discount'] * 100 / $data['price'], 2);
		} elseif ($data['discount'] < 1 && $data['discount'] > 0) {
			$data['discount_amount']  = round($data['price'] * $data['discount'], 2);
			$data['discount_percent'] = round($data['discount'] * 100, 2);
		} elseif ($data['discount'] == 0) {
			$data['discount_amount']  = $data['discount'];
			$data['discount_percent'] = $data['discount'];
		}
	}
	$data['net_price'] = round($data['price'] - $data['discount_amount'], 2);
	return $data;
}

/**
 * Extracts license information from an array of licenses.
 * @param array|string $licences The array of licenses or license string to extract from.
 * @param bool $is_byte Whether the license is in byte format or not.
 * @param bool $full Whether to return the full license information.
 *
 * @return array|string The extracted license information.
 */
function extractLicence($licences, $is_byte = true, $full = false)
{
	if ($is_byte) {
		global $_product_license_byte;
		$licences = byte2array($licences, $_product_license_byte, 1);
	}

	if (!is_array($licences)) {
		$licences = array();
	}

	$new_licences = array_filter($licences);
	if ($full) {
		global $_product_licence;
		foreach ($new_licences as $k => $v) {
			$lic[$k] = $_product_licence[$k];
		}
		$new_licences = implode(' + ', array_filter($lic));
	}
	return $new_licences;
}

/**
 * Searches for a string between two other strings in a given string
 * @param string $str The string to search in
 * @param string $fstr The starting string
 * @param string $lstr The ending string
 * @param int &$i The index of the starting position of the found string
 *
 * @return string|false The string between the starting and ending strings, or false if not found
 */
function findtextnew_withouttrim($str, $fstr, $lstr, &$i = 0)
{
	$namepos = 0;
	$endpos  = 0;
	$lenstr  = 0;

	$retstr = false;
	$lenstr = strlen($fstr);
	if (strlen($str) <= 0) {
		$i = 0;
		return false;
	}
	$namepos = strpos(strtolower($str), strtolower($fstr), $i);
	if ($namepos === false) {
		$i      = 0;
		$retstr = false;
	} else {
		$endpos = strpos(strtolower($str), strtolower($lstr), $namepos + $lenstr);
		if ($endpos === false) {
			$endpos = strlen($str) + 1;
		}
		$retstr = substr($str, $namepos + $lenstr, $endpos - $lenstr - $namepos);
	}
	if ($retstr) {
		$i = $namepos + $lenstr;
	}

	return $retstr;
}

/**
 * This function is used to find content image.
 *
 * @param string $str is the str.
 * @param string $maxloop is the maxloop.
 * @param string $all is the all.
 * @param string $xml is the xml.
 * @param string $is_find_alt is the is_find_alt.
 * @return array as overall response of the function.
 */
function findContentImage($str, $maxloop = 15, $all = true, $xml = false, $is_find_alt = false)
{
	$imgarray = array();
	$i        = 1;
	$k        = 1;
	$maxlen   = strlen($str);

	// Need to avoid from content or find better solution for finding image.
	$str = str_replace('<b>', '""', $str);
	$str = str_replace('<\/b>', '""', $str);
	$str = str_replace('alt=\"', 'alt="', $str);

	if ($is_find_alt) {
		$imgSrcs 	= array();
		$figCaption = array();
		$imgText 	= array();

		libxml_use_internal_errors(true);
		$a = new DOMDocument();
		$a->loadHTML($str);

		$error_log = error_get_last();

		foreach ($a->getElementsByTagName('img') as $item) {
			$imgSrc = $item->getAttribute('src');
			$imgAlt = $item->getAttribute('alt');
			if ($imgSrc != '') {
				$imgarray[$imgSrc]['img'] = $imgSrc;
				array_push($imgSrcs, $imgSrc);
				if (!$imgAlt || $imgAlt == '') {
					$imgarray[$imgSrc]['alt'] = '';
				}
				if ($imgAlt != '') {
					$count     = strlen($imgAlt);
					$lastIndex = strripos($imgAlt, '\\');
					if ($lastIndex == ($count - 1)) {
						$imgAlt = substr_replace($imgAlt, '', $lastIndex);
					}
					$imgAlt                       = str_replace('\"', '"', $imgAlt);
					$imgarray[$imgSrc]['alt'] = $imgAlt;
				}
			}
		}

		if (preg_match_all('/<img[\s\S]*?(?=<img)|<img[\s\S]*?(?=$)/i', $str, $images)) {
			foreach ($images[0] as $value) {
				preg_match('/imgtext="(.*?)"|imgtext="(.*?)"/i', $value, $imgtext);
				preg_match('/<uc\:caption([\s\S]*?)<\/uc\:caption>|<uc\:caption([\s\S]*?)caption>/i', $value, $caption);
				preg_match('/<figcaption([\s\S]*?)<figcaption>|<figcaption([\s\S]*?)caption>/i', $value, $figcaption);
				if (!preg_match('/type="table"|type=\'table\'|type=\\\"table\\\"/', $caption[0])) {
					$caption = preg_replace('/<uc\:caption(.*?)>/', '', $caption[0]);
					$caption = str_replace('<\/uc:caption>', '', $caption);
				}
				if (end($imgtext)) {
					array_push($imgText, end($imgtext));
				} else {
					array_push($imgText, '');
				}
				if ($figcaption || $caption) {
					$figcaption = preg_replace('/<figcaption(.*?)>/', '', $figcaption[0]);
					$figcaption = str_replace('<\/figcaption>', '', $figcaption);
					array_push($figCaption, $caption . $figcaption);
				} else {
					array_push($figCaption, '');
				}
			}
		}

		foreach ($imgSrcs as $key => $value) {
			$imgarray[$value]['imgText']   = $imgText[$key];
			$imgarray[$value]['uccaption'] = $figCaption[$key];
			$imgarray[$value]['error'] 	   = substr_count($error_log['message'], 'loadHTML()') ? $error_log['message'] : false;
		}
	} else {
		while ($i > 0 && $i < $maxlen) {
			$node = findtextnew($str, '<img', '>', $i);
			$i   += 10;
			if (strlen($node) <= 0) {
				$i = -1;
			} else {
				$z    = 0;
				$node = '<img ' . $node . ' >';
				$id   = getAttribute($node, 'img', 'src', $z);
				if (strlen($id) > 0) {
					$imgarray[$id] = $id;
				}
			}
			$k++;
			if ($k > $maxloop) {
				$i = -1;
			}
		}
	}
	$i = 1;
	$k = 1;
	if ($all) {
		while ($i > 0 && $i < $maxlen) {
			$node = findtextnew($str, '<sme', '>', $i);
			$i   += 10;
			if (strlen($node) <= 0) {
				$i = $maxlen + 1;
			} else {
				$z    = 0;
				$node = '<sme ' . $node . ' >';
				$id   = getAttribute($node, 'sme', 'id', $z);
				if (strlen($id) > 0) {
					$imgarray[$id] = strtolower($id);
				}
			}
			$k++;
			if ($k > $maxloop) {
				$i = -1;
			}
		}
		$i = 1;
		$k = 1;
		while ($i > 0 && $i < $maxlen) {
			$node = findtextnew($str, 'backpic=', '>', $i);
			$i   += 10;
			if (strlen($node) <= 0) {
				$i = $i + 2;
			} else {
				$imgarray[$node] = $node;
				$i                 = $i + strlen($node);
				if ($i > $maxlen) {
					$i = 0;
				}
			}
			$k++;
			if ($k > $maxloop) {
				$i = -1;
			}
		}
		$i = 1;
		$k = 1;
		while ($i > 0 && $i < $maxlen) {
			$node = findtextnew($str, 'bgimg=\"', '\"', $i);
			if (strlen($node) <= 0) {
				$i = $i + 2;
			} else {
				$imgarray[$node] = $node;
				$i                 = $i + strlen($node);
				if ($i > $maxlen) {
					$i = 0;
				}
			}
			$k++;
			if ($k > $maxloop) {
				$i = -1;
			}
		}
		// Getting images from matchlist xml.
		if (preg_match_all('/<matchlist([\s\S]*?)<\/matchlist>/i', $str, $match)) {
			$match = $match[1];
			$size  = count_uc($match);
			for ($i = 0; $i < $size; $i++) {
				preg_match_all('/\[\*(.*?)(png|jpg|jpeg|gif)\]/i', $match[$i], $matchImages);
				foreach ($matchImages[0] as $imgName) {
					$imgName              = preg_replace('/\[\*|\]/', '', $imgName);
					$imgarray[$imgName] = $imgName;
				}
			}
		}
		// Getting images of video thumbnail (player tag).
		if (preg_match_all('/<player([\s\S]*?)>/i', $str, $match)) {
			$match = $match[1];
			$size  = count_uc($match);
			for ($i = 0; $i < $size; $i++) {
				$match[$i] = preg_replace("/'/", '"', $match[$i]);
				$match[$i] = preg_replace('/\\\"/', '"', $match[$i]);
				$match[$i] = preg_replace('/\s*=\s*/', '=', $match[$i]);
				if (preg_match('/type="video"|type=\\\"video\\\"/i', $match[$i])) {
					if (preg_match('/preview/i', $match[$i]) && (!preg_match('/preview=""/i', $match[$i]))) {
						preg_match('/preview="(.*?)"/i', $match[$i], $imgName);
						$imgName              = $imgName[1];
						$imgarray[$imgName] = $imgName;
					} elseif (preg_match('/asset/i', $match[$i]) && preg_match('/\.mp4/i', $match[$i])) {
						$match[$i]          = preg_match('/asset="(.*?)"/i', $match[$i], $imgName);
						$imgExt               = explode('.', $imgName[1]);
						$imgName              = preg_replace('/' . end($imgExt) . '/', 'png', $imgName[1]);
						$imgarray[$imgName] = $imgName;
					}
				}
			}
		}

		// getting image from common xmls.
		if ($xml) {
			$i = 1;
			$k = 1;
			while ($i > 0 && $i < $maxlen) {
				$node = findtextnew($str, 'image=\"', '\"', $i);
				if (strlen($node) <= 0) {
					$i = $maxlen + 1;
				} else {
					$nodes = explode(',', $node);
					foreach ($nodes as $n) {
						$imgarray[$n] = $n;
					}
					$i = $i + strlen($node);
					if ($i > $maxlen) {
						$i = 0;
					}
				}
				$k++;
				if ($k > $maxloop) {
					$i = -1;
				}
			}
			$i = 1;
			$k = 1;
			while ($i > 0 && $i < $maxlen) {
				$node = findtextnew($str, 'infoimgs=\"', '\"', $i);
				if (strlen($node) <= 0) {
					$i = $maxlen + 1;
				} else {
					$nodes = explode(',', $node);
					foreach ($nodes as $n) {
						$imgarray[$n] = $n;
					}
					$i = $i + strlen($node);
					if ($i > $maxlen) {
						$i = 0;
					}
				}
				$k++;
				if ($k > $maxloop) {
					$i = -1;
				}
			}
		}
	}
	if (count_uc($imgarray) > 0 && !$is_find_alt) {
		return array_keys_uc($imgarray);
	}
	return $imgarray;
}

/**
 * It is the function that has been used to detect assets.
 *
 * @param array $str denotes str.
 * @param array $maxloop is the maxloop.
 * @return array as whole response of function.
 */
function findAssets($str, $maxloop = 15)
{
	$assetsarray = array();
	$maxlen      = strlen($str);
	$counttype   = array();
	$str         = ' ' . $str . ' ';

	$vimeoObj = new clsVimeo();

	$i = 1;
	$k = 1;
	while ($i > 0 && $i < $maxlen) {
		$node = findtextnew($str, '<player', '>', $i);
		if (strlen($node) <= 0) {
			$i = $maxlen + 1;
		} else {
			$p         = array();
			$z         = 0;
			$node      = '<player ' . $node . ' >';
			$p['type'] = strtolower(getAttribute($node, 'player', 'type', $z));
			if (!isset($p['type']) || $p['type'] == '') {
				$p['type'] = 'quiz';
			}
			$z = 0;

			$p['asset'] = getAttribute($node, 'player', 'asset', $z);
			$z          = 0;

			$p['sub_type'] = strtolower(getAttribute($node, 'player', 'sub_type', $z));

			$z          = 0;
			$p['title'] = getAttribute($node, 'player', 'title', $z);
			if ($p['asset'] == '') {
				$z          = 0;
				$p['asset'] = getAttribute($node, 'player', 'guids', $z);
			}
			if ($p['asset'] == '') {
				$z          = 0;
				$p['asset'] = getAttribute($node, 'player', 'lab_code', $z);
				$p['type']  = 'lab';
			}
			if ($p['type'] == 'lab' && $p['sub_type'] != 'assessment') {
				$p['sub_type'] = 'training';
			}
			if ($p['type'] == 'lab') {
				$type = $p['sub_type'];
			} else {
				$type = $p['type'];
			}
			// @Deepika: save thumbnail for lynda.com videos.
			if ($type == 'integrate' && $p['sub_type'] == 'video') {
				$z              = 0;
				$p['thumbnail'] = getAttribute($node, 'player', 'preview', $z);
				$z              = 0;
				$p['duration']  = getAttribute($node, 'player', 'duration', $z);
				$z              = 0;
				$display        = getAttribute($node, 'player', 'display', $z);
				if ($display <> '') {
					$p['display'] = $display;
				}
				$type = 'video';
			}
			// save video_plus guid in case of video plus.
			if ($p['sub_type'] == 'video_plus') {
				$z              = 0;
				$tmp_group_guid = getAttribute($node, 'player', 'group_guids', $z);
				if ($tmp_group_guid) {
					$p['group_guids'] = $tmp_group_guid;
				}
				$type           = 'video';
				$z              = 0;
				$temp_thumbnail = getAttribute($node, 'player', 'preview', $z);
				if ($temp_thumbnail) {
					$p['thumbnail'] = $temp_thumbnail;
				}
				$z             = 0;
				$temp_duration = getAttribute($node, 'player', 'duration', $z);
				if ($temp_duration) {
					$p['duration'] = $temp_duration;
				}
			}

			if ($type == 'video' && preg_match('/player.vimeo.com/', $p['asset'])) {
				$z              = 0;
				$p['thumbnail'] = getAttribute($node, 'player', 'preview', $z);
				// if already duration is passed no, needs to get from vimeo.
				$z             = 0;
				$temp_duration = getAttribute($node, 'player', 'duration', $z);
				if ($temp_duration) {
					$p['duration']       = $temp_duration;
					$p['duration_label'] = gmdate('G:i:s', (int) $p['duration']);
					if (substr($p['duration_label'], 0, 2) == '0:') {
						$p['duration_label'] = substr($p['duration_label'], 2, strlen($p['duration_label']));
					}
				} else {
					$vData               = $vimeoObj->getDuration($p['asset']);
					$p['duration']       = $vData['duration'];
					$p['duration_label'] = $vData['duration_label'];
				}
				$z          = 0;
				$p['title'] = getAttribute($node, 'player', 'title', $z);
				$z          = 0;
				$type       = 'video';
			}

			$z           = 0;
			$temp_option = getAttribute($node, 'player', 'option', $z);
			if ($temp_option) {
				$temp_option    = html_entity_decode($temp_option);
				$temp_option    = json_decode_uc($temp_option, true);
				$p['thumbnail'] = $temp_option['preview'];
			}

			if ($p['asset'] <> '') {
				$counttype[$type]++;
				$assetsarray['count'] = $counttype;
				unset($p['type']);
				$assetsarray['assets'][$type][] = $p;
			}
		}
		$k++;
		if ($k > $maxloop) {
			$i = $maxlen + 1;
		}
	}

	$i    = 1;
	$k    = 1;
	$node = '';
	while ($i > 0 && $i < $maxlen) {
		$p    = array();
		$node = findtextnew($str, '<iframe', '>', $i);
		if (strlen($node) <= 0) {
			$i = $maxlen + 1;
		} else {
			$z          = 0;
			$node       = '<iframe ' . $node . ' >';
			$type       = 'video';
			$p['asset'] = getAttribute($node, 'iframe', 'src', $z);
			$counttype[$type]++;
			$assetsarray['count']             = $counttype;
			$assetsarray['assets'][$type][] = $p;
		}
		$k++;
		if ($k > $maxloop) {
			$i = $maxlen + 1;
		}
	}
	return $assetsarray;
}

/**
 * Extracts GUIDs from <span> tags in the given string.
 * @param string $str The string to search for <span> tags.
 *
 * @return array An array of GUIDs found in the <span> tags.
 */
function findSpanGuids($str)
{
	// Due to json content.
    // Fix escaped GUIDs and quotes in JSON content
	$str   = str_replace('guid=\\', 'guid=', $str);
	$str   = str_replace('\"', '"', $str);
	$guids = array();
    // Search for <span> tags with a guid attribute
	preg_match_all("/<span.*?guid=[\"'](.+?)[\"'].*?>/", $str, $nodes);
    // Extract GUIDs from matched <span> tags
	if ($nodes) {
		$nodes = array_unique($nodes[1]);
		foreach ($nodes as $guid) {
			$guids[] = $guid;
		}
	}
	return $guids;
}

/**
 * Find all device images in a string.
 * @param string $str The input string to search.
 * @return array An array of device images found in the input string.
 */
function findDevices($str)
{
	// Due to json content.
	// Replace any escaped quotes in the input string.
	$str 	 = str_replace('device_image=\\', 'device_image=', $str);
	$str 	 = str_replace('\"', '"', $str);
	$devices = array();
	// Find all device tags with a device_image attribute.
	preg_match_all("/<device.*?device_image=[\"'](.+?)[\"'].*?>/", $str, $nodes);

	if ($nodes) {
		$nodes = array_unique($nodes[1]);
		foreach ($nodes as $device) {
			$devices[] = $device;
		}
	}
	return $devices;
}

/**
 * Convert test session options from integer to an associative array.
 * @param int $opt The integer value of the options.
 * @return array An associative array of the test session options.
 */
function api_convert_test_session_options2array($opt)
{
	$bin                             = decbin($opt);
	$bin                             = strrev(str_pad($bin, 16, '0', STR_PAD_LEFT));
	$str                             = str_split($bin);
	$options['randomize_items']      = $str[0];
	$options['randomize_options']    = $str[1];
	$options['show_time']            = $str[2];
	$options['is_none_of_the_above'] = $str[3];
	$options['o4']                   = $str[4];
	$options['o5']                   = $str[5];
	$options['can_pause']            = $str[6];
	$options['status_pre']           = $str[7];
	$options['status_post']          = $str[8];
	$options['remove_unseen']        = $str[9];
	$options['save_answers']         = $str[10];
	$options['manual_grading']       = $str[11];
	$options['partial_credit']       = $str[12];
	$options['learing_aid_allowed']  = $str[13];
	$options['allow_printing']       = $str[14];
	$options['is_sample']            = $str[15];
	return $options;
}

/**
 * Convert test session options array to binary format.
 *
 * @param array $ary An associative array of options to be converted.
 *
 * @return int The converted binary format of the options.
 */
function api_convert_test_session_array2options($ary)
{
	$options['randomize_items']      = 0;
	$options['randomize_options']    = 0;
	$options['show_time']            = 0;
	$options['is_none_of_the_above'] = 0;
	$options['o4']                   = 0;
	$options['o5']                   = 0;
	$options['can_pause']            = 0;
	$options['status_pre']           = 0;
	$options['status_post']          = 0;
	$options['remove_unseen']        = 0;
	$options['save_answers']         = 0;
	$options['manual_grading']       = 0;
	$options['partial_credit']       = 0;
	$options['learing_aid_allowed']  = 0;
	$options['allow_printing']       = 0;
	$options['is_sample']            = 0;

	// o4, o5 (concate)
	// 00 = everything
	// 10 = Items review unavailable
	// 01 = Items list unavailable
	// 11 = Result unavailable
	$ary = array_merge_uc($options, $ary);
	foreach ($ary as $k => $v) {
		if ($v <> '1') {
			$ary[$k] = '0';
		} else {
			$ary[$k] = 1;
		}
	}
	$str = $ary['randomize_items'] . $ary['randomize_options'] . $ary['show_time'] . $ary['is_none_of_the_above'] . $ary['o4'] . $ary['o5'] . $ary['can_pause'] . $ary['status_pre'] . $ary['status_post'] . $ary['remove_unseen'] . $ary['save_answers'] . $ary['manual_grading'] . $ary['partial_credit'] . $ary['learing_aid_allowed'] . $ary['allow_printing'] . $ary['is_sample'];
	$str = strrev(str_pad($str, 16, '0', STR_PAD_RIGHT));
	$bin = bindec($str);
	return $bin;
}


/**
 * Convert an assignment schedule options integer to an associative array.
 * @param int $opt The assignment schedule options integer.
 * @return array The assignment schedule options as an associative array.
 */
function api_convert_assignment_schedule_options2array($opt)
{
	if($opt ==='')
	{
		return array();
	}
	$bin                             = decbin($opt);
	$bin                             = strrev(str_pad($bin, 16, '0', STR_PAD_LEFT));
	$str                             = str_split($bin);
	$options['randomize_items']      = $str[0];
	$options['randomize_options']    = $str[1];
	$options['is_none_of_the_above'] = $str[3];
	$options['can_pause']            = $str[6];
	return $options;
}

/**
 * It is the function that has been used to convert test session array into options.
 *
 * @param array $ary denotes ary.
 * @return array as whole response of function.
 */
function api_convert_assignment_schedule_array2options($ary)
{
	if(!isset($ary['randomize_items']) && 
	!isset($ary['randomize_options']) && 
	!isset($ary['is_none_of_the_above']) && 
	!isset($ary['can_pause'])
	) {
		// if none of these 4 value set , return blank
		return '';
	}
	$options['randomize_items']      = 0;
	$options['randomize_options']    = 0;
	$options['show_time']            = 0;
	$options['is_none_of_the_above'] = 0;
	$options['o4']                   = 0;
	$options['o5']                   = 0;
	$options['can_pause']            = 0;
	$options['status_pre']           = 0;
	$options['status_post']          = 0;
	$options['remove_unseen']        = 0;
	$options['save_answers']         = 0;
	$options['manual_grading']       = 0;
	$options['partial_credit']       = 0;
	$options['learing_aid_allowed']  = 0;
	$options['allow_printing']       = 0;
	$options['is_sample']            = 0;

	// o4, o5 (concate)
	// 00 = everything
	// 10 = Items review unavailable
	// 01 = Items list unavailable
	// 11 = Result unavailable
	//currently we are only keeping 4 value in schedule so removing rest of it
	$ary = unsetArrayData($ary,'show_time,status_pre,status_post,remove_unseen,save_answers,manual_grading,partial_credit,learing_aid_allowed,allow_printing,is_sample');
	$ary = array_merge_uc($options, $ary);
	foreach ($ary as $k => $v) {
		if ($v <> '1') {
			$ary[$k] = '0';
		} else {
			$ary[$k] = 1;
		}
	}
	$str = $ary['randomize_items'] . $ary['randomize_options'] . $ary['show_time'] . $ary['is_none_of_the_above'] . $ary['o4'] . $ary['o5'] . $ary['can_pause'] . $ary['status_pre'] . $ary['status_post'] . $ary['remove_unseen'] . $ary['save_answers'] . $ary['manual_grading'] . $ary['partial_credit'] . $ary['learing_aid_allowed'] . $ary['allow_printing'] . $ary['is_sample'];
	$str = strrev(str_pad($str, 16, '0', STR_PAD_RIGHT));
	$bin = bindec($str);
	return $bin;
}
/**
 * A function for score to grade.
 *
 * @param string $grade_scale It contains a string of grade.
 * @param string $score It contains a string for.
 * @param string $grade_type It contains a string for type of grade.
 * @return string is the response of the function.
 */
function score2grade($grade_scale, $score, $grade_type = '')
{
	if ($grade_type == '') {
		$grade_type = $grade_scale['f'];
	}
	if ($score >= 0) {
		if (is_array($grade_scale)) {
			if (is_array($grade_scale['s'])) {
				foreach ($grade_scale['s'] as $g => $f) {
					if (($score <= $f['max']) && ($score >= $f['min'])) {
						$grade = $g;
					}
				}
			}
		}
	}
	return $grade;
}

/**
 * Converts a set of results to grades based on a given grade scale.
 *
 * @param array $grade_scale The grade scale to use for converting scores to grades.
 * @param array $results     The set of results to be graded.
 *
 * @return array An array of grades, one for each result in the set.
 */
function result2grade($grade_scale, $results)
{
	$grade = array();
	if (is_array($results)) {
		foreach ($results as $ac => $v) {
			if (is_array($v)) {
				$score = $v['s'];
				$m     = $v['g']; // for manually gradded is gradded or still need to grade it.
			} else {
				$m     = 1;
				$score = $v;
			}
			$grade[$ac]['s'] = $score;
			$grade[$ac]['t'] = $v['m'];
			$grade[$ac]['m'] = $m;
			$grade[$ac]['g'] = 'NA';
			if ($score >= 0) {
				if (is_array($grade_scale) && !empty($grade_scale) && is_array($grade_scale['s'])) {
					foreach ($grade_scale['s'] as $g => $f) {
						if (($score <= $f['max']) && ($score >= ($f['min'] - 35))) {
							$grade[$ac]['g'] = $g;
						}
					}
				}
			}
		}
	}
	return $grade;
}

/**
 * Returns the current operating system from the server.
 *
 * @return string The name of the operating system.
 */
function getCurrentOSFromServer()
{
	$_os_array                          = array();
	$_os_array['/windows phone 8/i']    = 'Windows Phone 8';
	$_os_array['/windows phone os 7/i'] = 'Windows Phone 7';
	$_os_array['/windows nt 6.3/i']     = 'Windows 8.1';
	$_os_array['/windows nt 6.2/i']     = 'Windows 8';
	$_os_array['/windows nt 6.1/i']     = 'Windows 7';
	$_os_array['/windows nt 6.0/i']     = 'Windows Vista';
	$_os_array['/windows nt 5.2/i']     = 'Windows Server 2003/XP x64';
	$_os_array['/windows nt 5.1/i']     = 'Windows XP';
	$_os_array['/windows xp/i']         = 'Windows XP';
	$_os_array['/windows nt 5.0/i']     = 'Windows 2000';
	$_os_array['/windows me/i']         = 'Windows ME';
	$_os_array['/win98/i']              = 'Windows 98';
	$_os_array['/win95/i']              = 'Windows 95';
	$_os_array['/win16/i']              = 'Windows 3.11';
	$_os_array['/macintosh|mac os x/i'] = 'Mac OS X';
	$_os_array['/mac_powerpc/i']        = 'Mac OS 9';
	$_os_array['/linux/i']              = 'Linux';
	$_os_array['/ubuntu/i']             = 'Ubuntu';
	$_os_array['/iphone/i']             = 'iPhone';
	$_os_array['/ipod/i']               = 'iPod';
	$_os_array['/ipad/i']               = 'iPad';
	$_os_array['/android/i']            = 'Android';
	$_os_array['/blackberry/i']         = 'BlackBerry';
	$_os_array['/webos/i']              = 'Mobile';

	$user_agent = false;
	if (!empty($_SERVER['HTTP_USER_AGENT'])) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}

	foreach ($_os_array as $regex => $value) {
		if (preg_match($regex, $user_agent)) {
			$platform = $value;
			break;
		} else {
			$platform = 'Unknown';
		}
	}

	return $platform;
}

/**
 * Encode the given data as JSON with support for encoding Unicode characters
 * @param mixed $data The data to be encoded
 * @param int $options [optional] Bitmask of JSON encoding options
 * @param int $depth [optional] Maximum depth to traverse when encoding
 * @return string|false A JSON encoded string on success, false on failure
 */
function json_encode_uc($data, $options = 0, $depth = 512)
{
	$json = json_encode($data, $options, $depth);
	if ($json !== false) {
		return $json;
	}
	return false;
}

/**
 * Decodes a JSON string and returns the resulting value.
 * @param string $string The JSON string to decode.
 * @param bool $assoc Optional. When true, returned objects will be converted into associative arrays.
 * @param int $depth Optional. User specified recursion depth.
 * @param int $options Optional. Bitmask of JSON decode options.
 *
 * @return mixed The value encoded in JSON or false on failure.
 */
function json_decode_uc($string, $assoc = false, $depth = 512, $options = 0)
{
    // Add debug trace for arrays
	if (function_exists('addDebugTrace') && is_array($string)) {
		addDebugTrace('trace_json_decode_array');
	}
    // Decode the JSON string
	$decoded_json = json_decode($string, $assoc, $depth, $options);
    // Return the decoded JSON or false on failure
	if ($decoded_json !== false) {
		return $decoded_json;
	}
	return false;
}

/**
 * Generate a random number within a given range.
 * @param int|false $min The minimum value of the range (default: 0).
 * @param int|false $max The maximum value of the range (default: getrandmax()).
 *
 * @return int The random number generated within the given range.
 */
function rand_uc($min = false, $max = false)
{
	if (empty($min)) {
		$min = 0;
	}
	if (empty($max)) {
		$max = getrandmax();
	}
	$random = rand($min, $max);
	return $random;
}

/**
 * A function for to strip tags.
 *
 * @param string $string is the string.
 * @return string as response of the function.
 */
function strip_all_tags_uc($string)
{
	$stripped_str = strip_tags($string);
	return $stripped_str;
}

/**
 * Read entire contents of a file into a string
 * @param string $file_name Name of the file to read
 * @param bool $include_path Optional. Search for the file in the include_path. Default is false.
 * @param resource|null $context Optional. A valid context resource created with stream_context_create(). Default is null.
 * @param int $start Optional. The position in bytes to start reading. Default is 0.
 * @return string|false Returns the read data or false on failure.
 */
function file_get_contents_uc($file_name, $include_path = false, $context = null, $start = 0)
{
	try {
		$file_content = @file_get_contents($file_name, $include_path, $context, $start);
		if ($file_content === false) {
			// Handle the error
		}
	} catch (Exception $e) {
		// Handle exception
	}
	return $file_content;
}

/**
 * A function for to get debug trace.
 *
 * @param string $options is the options.
 * @param string $limit is the limit.
 * @return array as response of the function.
 */
function debug_backtrace_uc($options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0)
{
	$back_trace = debug_backtrace($options, $limit);
	return $back_trace;
}

/**
 * This class is used for encryption decryption.
 */
class EncryptionManager
{
	/**
	 * It is the function that has been used to encrypt string.
	 *
	 * @param string $plaintext denotes plaintext.
	 * @param string $key is the key.
	 * @return string as whole response of function.
	 */
	public static function encrypt($plaintext, $key)
	{
		$ivlen          = openssl_cipher_iv_length($cipher = 'AES-128-CBC');
		$iv             = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
		$ciphertext     = base64_encode($iv . /*$hmac.*/ $ciphertext_raw);
		return $ciphertext;
	}

	/**
	 * It is the function that has been used to decrypt string.
	 *
	 * @param string $ciphertext denotes ciphertext.
	 * @param string $key is the key.
	 * @return string as whole response of function. 
	 */
	public static function decrypt($ciphertext, $key)
	{
		$c                  = base64_decode($ciphertext);
		$ivlen              = openssl_cipher_iv_length($cipher = 'AES-128-CBC');
		$iv                 = substr($c, 0, $ivlen);
		$ciphertext_raw     = substr($c, $ivlen/*+$sha2len*/);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
		return $original_plaintext;
	}
}

/**
 * Sanitizes a title, replacing whitespace and a few other characters with dashes. *
 * Limits the output to alphanumeric characters, underscore (_) and dash (-).
 * Whitespace becomes a dash.
 *
 * @since 1.2.0
 *
 * @param string $title     The title to be sanitized.
 * @param string $raw_title Optional. Not used.
 * @param string $context   Optional. The operation for which the string is sanitized.
 * @return string The sanitized title.
 */
function sanitize_title_with_dashes_uc($title)
{
	$title = strip_tags($title);
	// Preserve escaped octets.
	$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
	// Remove percent signs that are not part of an octet.
	$title = str_replace('%', '', $title);
	// Restore octets.
	$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
	$title = strtolower($title);

	// Convert &nbsp, &ndash, and &mdash to hyphens.
	$title = str_replace(array('%c2%a0', '%e2%80%93', '%e2%80%94'), '-', $title);
	// Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
	$title = str_replace(array('&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;'), '-', $title);
	// Convert forward slash to hyphen.
	$title = str_replace('/', '-', $title);

	// Strip these characters entirely.
	$title = str_replace(
		array(
			// Soft hyphens.
			'%c2%ad',
			// &iexcl and &iquest.
			'%c2%a1',
			'%c2%bf',
			// Angle quotes.
			'%c2%ab',
			'%c2%bb',
			'%e2%80%b9',
			'%e2%80%ba',
			// Curly quotes.
			'%e2%80%98',
			'%e2%80%99',
			'%e2%80%9c',
			'%e2%80%9d',
			'%e2%80%9a',
			'%e2%80%9b',
			'%e2%80%9e',
			'%e2%80%9f',
			// &copy, &reg, &deg, &hellip, and &trade.
			'%c2%a9',
			'%c2%ae',
			'%c2%b0',
			'%e2%80%a6',
			'%e2%84%a2',
			// Acute accents.
			'%c2%b4',
			'%cb%8a',
			'%cc%81',
			'%cd%81',
			// Grave accent, macron, caron.
			'%cc%80',
			'%cc%84',
			'%cc%8c',
		),
		'',
		$title
	);

	// Convert &times to 'x'.
	$title = str_replace('%c3%97', 'x', $title);

	// Kill entities.
	$title = preg_replace('/&.+?;/', '', $title);
	$title = str_replace('.', '-', $title);

	$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
	$title = preg_replace('/\s+/', '-', $title);
	$title = preg_replace('|-+|', '-', $title);
	$title = trim($title, '-');

	return $title;
}

/**
 * Retrieve the contents of a URL using cURL.
 * @param string $url The URL to retrieve.
 * @return string The contents of the URL, or false if the request fails.
 */
function file_get_contents_curl($url)
{
    // Initialize a new cURL session.
	$ch = curl_init();
    // Set cURL options.
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
    // Execute the cURL session and retrieve the response.
	$response = curl_exec($ch);
    // Close the cURL session.
	curl_close($ch);
    // Return the response, or false if the request failed.
	return $response;
}

/**
 *Generates a Redis key using a given string value and prefix.
 *
 * @param string $val is the val.
 * @param string $prefix is the prefix.
 * @return string as response of the function.
 */
function create_redis_key($val, $prefix = 'p:')
{
	$key = substr($prefix . md5($val), 0, 31);
	return $key;
}

/**
 * Retrieve the MAC address of the current system.
 *
 * @return string|null The MAC address if it can be found, otherwise null.
 */
function get_sys_mac_address()
{
    // Start output buffering to capture the command output.
	ob_start();

	// Get the ipconfig details using system commond
	system('ipconfig /all');

	// Capture the output into a variable.
	$mycomsys = ob_get_contents();

	// Clean (erase) the output buffer.
	ob_clean();

	$find_mac = 'Physical'; // find the "Physical" & Find the position of Physical text.
	$pmac     = strpos($mycomsys, $find_mac);

	// Get Physical Address.
	$macaddress = substr($mycomsys, ($pmac + 36), 17);

	// Display Mac Address.
	return $macaddress;
}

/**
 * Merge arrays while preserving keys.
 * @param array ...$arrays Variable-length array of arrays to be merged.
 * 
 * @return array Merged array.
 */
function array_merge_preserve(...$arrays)
{
	$response = array();
	foreach ($arrays as $array) {
		if (empty($response)) {
			$response = $array;
		} else {
			$response += $array;
		}
	}
	return $response;
}

/**
 * This is the function that has been used to debug and print data in jigyaasa.
 *
 * @param array $a denotes a.
 * @param array $isDie denotes isDie.
 * @param array $isPre denotes isPre.
 */
function __ucc($a, $isDie = 1, $isPre = 1)
{
	if ($isPre) {
		echo '<pre>';
	}
	print_r($a);
	echo '</pre>';
	if ($isDie) {
		die;
	}
}


/**
 * Generates a new random password.
 *
 * @return string A new password string.
 */
function generateNewPassword()
{
	$numbers_special = '2345679@';
	$lower_chars     = 'abcdefghjkmnpqrtuvwyz';
	$upper_chars     = 'ACDEFGHJKMNPQRTUVWXYZ';
	$numbers         = substr(str_shuffle($numbers_special), 0, 3);
	$lower_letter    = substr(str_shuffle($lower_chars), 0, 4);
	$upper_letter    = substr(str_shuffle($upper_chars), 0, 3);
	$final_password  = $numbers . $lower_letter . $upper_letter;
	return str_shuffle($final_password);
}

/**
 * Returns the LMS platform ID based on the given URL.
 *
 * @param string $url The URL to check.
 *
 * @return string The LMS platform ID or 'unknown' if not found.
 */
function getLMSPlatformIDFromURL( $url ) 
{
	$lms = '';
	if ( strpos( $url, 'lti-ri.imsglobal.org' ) !== false ) { 
		$lms = ENUM_LMS_PLATFORM::IMSGLOBAL;
	} elseif ( strpos( $url, 'blackboard' ) !== false ) { // Blackboard LMS.
		$lms = ENUM_LMS_PLATFORM::BLACKBOARD;
	} elseif ( strpos( $url, 'brightspace' ) !== false) { // D2L LMS.
		$lms = ENUM_LMS_PLATFORM::D2L;
	} elseif ( (strpos( $url, 'canvas.' ) !== false) || (strpos( $url, 'instructure.' ) !== false)) {
		$lms = ENUM_LMS_PLATFORM::CANVAS;
	} elseif ( strpos( $url, 'moodle.' ) !== false) {
		$lms = ENUM_LMS_PLATFORM::MOODLE;
	} 
	elseif ( strpos( $url, 'ucertify.com' ) !== false) {
		$lms = ENUM_LMS_PLATFORM::UCETIFY;
	} 
	return $lms; //Unknown
} 
