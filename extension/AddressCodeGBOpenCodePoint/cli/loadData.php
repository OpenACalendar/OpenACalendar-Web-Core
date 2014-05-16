<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

// We could let autoloader get this, but for that to happen the extension has to be enabled in OAC.
// Let's just manually add this so ppl can import then enable.
require_once APP_ROOT_DIR.'/extension/AddressCodeGBOpenCodePoint/php/org/openacalendar/addresscode/gb/opencodepoint/AddressCodeGBOpenCodePointConvert.php';

/**
 *
 * 
 * @package AddressCodeGBOpenCodePoint
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$opencodepointdir = isset($argv[1]) ? $argv[1] : null;
$ourdir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data');

if (!$opencodepointdir || !is_dir($opencodepointdir)) {
	die("You must pass a directory that contains the csv's from Code Point Open!\n\n");
}

if (!$ourdir || !is_dir($ourdir)) {
	die("Something went wrong finding our dir?\n\n");
}

if ($handleDir = opendir($opencodepointdir)) {
    while (false !== ($entry = readdir($handleDir))) {
		if ($entry != '.' && $entry != '..' && substr($entry, -4) == '.csv') {
			
			
			## Stage 0: set up
			$outputfiles = array();
			
			## Stage 1: loop over each file
			print "Starting ".$entry."\n";
			if (($handle = fopen($opencodepointdir.DIRECTORY_SEPARATOR.$entry, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					
					if (count($data) > 3) {
						
						$postcode = strtoupper(str_replace(" ","",$data[0]));
						list($lat,$lng) = org\openacalendar\addresscode\gb\opencodepoint\AddressCodeGBOpenCodePointConvert::Convert($data[2],$data[3]);
						$postcodeTwoChars = substr($postcode, 0,2);
						
						if (!isset($outputfiles[$postcodeTwoChars])) {
							$outputfiles[$postcodeTwoChars] = fopen($ourdir.DIRECTORY_SEPARATOR.$postcodeTwoChars.".tmp.csv", 'w');
							if (!$outputfiles[$postcodeTwoChars]) {
								die("Could not open output file: ".$ourdir.DIRECTORY_SEPARATOR.$postcodeTwoChars.".tmp.csv\n\n");
							}
						}
						fwrite($outputfiles[$postcodeTwoChars], '"'.$postcode.'",'.$lat.','.$lng."\n");
						
					}
				}
				fclose($handle);
			}

			## Stage 2: close all open files
			print "Cleaning up ".$entry."\n";
			foreach($outputfiles as $key=>$fh) {
				fclose($fh);
			}
			
			## Stage 3: Copy 
			print "Installing ".$entry."\n";
			foreach($outputfiles as $key=>$fh) {
				rename($ourdir.DIRECTORY_SEPARATOR.$key.".tmp.csv",$ourdir.DIRECTORY_SEPARATOR.$key.".csv");
			}
			
			print "Done ".$entry."\n";
			
		}
    }
   closedir($handleDir);
}


