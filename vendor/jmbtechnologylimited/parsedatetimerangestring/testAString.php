<?php

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ParseDateTimeRangeString
 * @license https://raw.github.com/JMB-Technology-Limited/ParseDateTimeRangeString/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


require 'src/JMBTechnologyLimited/ParseDateTimeRangeString/ParseDateTimeRangeString.php';
require 'src/JMBTechnologyLimited/ParseDateTimeRangeString/ParseDateTimeRangeStringResult.php';

use JMBTechnologyLimited\ParseDateTimeRangeString\ParseDateTimeRangeString; 

# Input
$string = isset($argv[1]) ? $argv[1] : '';
$timezone = isset($argv[2]) ? $argv[2] : "Europe/London";
		
print "In: ".$string."\n";
print "Timezone: ".$timezone."\n";

# Parse
$parse = new ParseDateTimeRangeString(new \DateTime(), $timezone);
$result = $parse->parse($string);

# Output
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

