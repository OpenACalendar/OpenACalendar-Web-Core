<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);

require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';


date_default_timezone_set('UTC');

error_reporting(E_ALL);

define('VARCHAR_COLUMN_LENGTH_USED', 255);

$EXTENSIONSTOLOAD = array();


spl_autoload_register(function($class) {
	global $EXTENSIONSTOLOAD;
	if (is_array($EXTENSIONSTOLOAD)) {
		foreach($EXTENSIONSTOLOAD as $extensionName) {
			$f = APP_ROOT_DIR.DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.$extensionName.DIRECTORY_SEPARATOR.
				'php'.DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
			if (file_exists($f)) {
				require_once $f;
				return;
			}
		}
	}
	// This is not the same as the main app autoload ... but PhpUnit tries to see if several extensions are there by loading them,
	// and then this code errors because the file is not found. So we have to check if the file exists here.
	$f = APP_ROOT_DIR. DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.
		'php'.DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
	if (file_exists($f)) {
		require_once $f;
		return;
	}
});

// The config file should have extensions set to any extensions who tests will be run for.
// This will then always be used for each test.
// (We have to do this after registering spl_autoload_register otherwise Config won't load!)
$CONFIG = new \Config();
require APP_ROOT_DIR."config.test.php";
$EXTENSIONSTOLOAD = $CONFIG->extensions;


require_once 'BaseAppTest.php';
require_once 'BaseAppWithDBTest.php';


function getUTCDateTime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
	$dt = new \DateTime('', new \DateTimeZone('UTC'));
	$dt->setTime($hour, $minute, $second);
	$dt->setDate($year, $month, $day);
	return $dt;
}

function createKey($minLength = 10, $maxLength = 100) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string ='';
		$length = mt_rand($minLength, $maxLength);
        for ($p = 0; $p < $length; $p++) {
                $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
}
