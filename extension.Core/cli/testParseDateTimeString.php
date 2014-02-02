<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$string = $argv[1];
$timezone = "Europe/London";

print "In: ".$string."\n";
print "Timezone: ".$timezone."\n";

$parse = new ParseDateTimeRangeString($timezone);
$result = $parse->parse($string);	

$dayOfWeekNames = array(
	1=>'Monday',
	2=>'Tuesday',
	3=>'Wednesday',
	4=>'Thursday',
	5=>'Friday',
	6=>'Saturday',
	7=>'Sunday',
);


print "Out Start Day Of Week: ".$dayOfWeekNames[$result->getStart()->format('N')]." ".
		$result->getStart()->format('jS')." ".
		$result->getStart()->format('F')." ".
		$result->getStart()->format('Y')." ".
		$result->getStart()->format('G').":".
		$result->getStart()->format('i')." ".
		"\n";

print "Out End Day Of Week: ".$dayOfWeekNames[$result->getEnd()->format('N')]." ".
		$result->getEnd()->format('jS')." ".
		$result->getEnd()->format('F')." ".
		$result->getEnd()->format('Y')." ".
		$result->getEnd()->format('G').":".
		$result->getEnd()->format('i')." ".
		"\n";

