<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);


require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';

date_default_timezone_set('UTC');

error_reporting(E_ALL);

define('VARCHAR_COLUMN_LENGTH_USED', 255);

function autoload($class) {
	global $CONFIG;
	if (isset($CONFIG)) {
		foreach($CONFIG->extensions as $extensionName) {
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
}
spl_autoload_register('autoload'); 


$CONFIG = new Config();
require __DIR__."/../../config.test.php";
$CONFIG->isDebug = true;

$EXTENSIONHOOKRUNNER = new ExtensionHookRunner();

/** @var PDO **/
$DB = new PDO('pgsql:host='.$CONFIG->databaseHost.';dbname='.$CONFIG->databaseName, $CONFIG->databaseUser, $CONFIG->databasePassword);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$DB->exec("SET CLIENT_ENCODING TO 'utf8'");
$DB->exec("SET NAMES 'utf8'");


function createKey($minLength = 10, $maxLength = 100) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string ='';
		$length = mt_rand($minLength, $maxLength);
        for ($p = 0; $p < $length; $p++) {
                $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
}

function getNewTestDB() {
	global $DB;
	foreach(explode(";", file_get_contents(__DIR__."/../sql/destroy.sql")) as $line) {
		if (trim($line)) {
			$DB->query($line.';');
		}
	}
	db\migrations\MigrationManager::upgrade(false);
	return $DB;
}

function getNewTestApp() {
	$app = new Silex\Application();

	return $app;
}

function getSiteQuotaUsedForTesting() {
	global $DB, $CONFIG;
	$siteQuotaRepository = new repositories\SiteQuotaRepository();
	return $siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode);
}

function addCountriesToTestDB() {
	global $DB;
	$statInsert = $DB->prepare("INSERT INTO country (two_char_code,title,timezones,max_lat,max_lng,min_lat,min_lng) ".
		"VALUES (:two_char_code,:title,:timezones,:max_lat,:max_lng,:min_lat,:min_lng)");
	$statInsert->execute(array('two_char_code'=>'GB','title'=>'United Kingdom','timezones'=>'Europe/London','max_lat'=>null, 'max_lng'=>null, 'min_lat'=>null, 'min_lng'=>null));
	$statInsert->execute(array('two_char_code'=>'DE','title'=>'Germany','timezones'=>"Europe/Berlin,Europe/Busingen",'max_lat'=>null, 'max_lng'=>null, 'min_lat'=>null, 'min_lng'=>null));
}

function getUTCDateTime($year=2012, $month=1, $day=1, $hour=0, $minute=0, $second=0) {
	$dt = new \DateTime('', new \DateTimeZone('UTC'));
	$dt->setTime($hour, $minute, $second);
	$dt->setDate($year, $month, $day);
	return $dt;
}


	
