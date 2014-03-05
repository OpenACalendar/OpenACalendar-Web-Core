<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

date_default_timezone_set('UTC');

define('COPYRIGHT_YEARS', '2013-2014');

define('VARCHAR_COLUMN_LENGTH_USED', 255);

function autoload($class) {
	global $CONFIG;
	if (isset($CONFIG)) {
		foreach($CONFIG->extensions as $extensionName) {
			$f = APP_ROOT_DIR.DIRECTORY_SEPARATOR.'extension.'.$extensionName.DIRECTORY_SEPARATOR.
					'php'.DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
			if (file_exists($f)) {
				require_once $f;
				return;
			}
		}
	}
	require_once APP_ROOT_DIR. DIRECTORY_SEPARATOR.'extension.Core'.DIRECTORY_SEPARATOR.
			'php'.DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
}
spl_autoload_register('autoload'); 


$CONFIG = new Config();
require APP_ROOT_DIR."/config.php";

$EXTENSIONHOOKRUNNER = new ExtensionHookRunner();

/** @var PDO **/
$DB = new PDO($CONFIG->databaseType.':host='.$CONFIG->databaseHost.';dbname='.$CONFIG->databaseName, $CONFIG->databaseUser, $CONFIG->databasePassword);
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


