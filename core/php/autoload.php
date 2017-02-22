<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

date_default_timezone_set('UTC');

define('COPYRIGHT_YEARS', '2013-2017');

define('VARCHAR_COLUMN_LENGTH_USED', 255);

if (file_exists(APP_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'classmap.php')) {
    require_once APP_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'classmap.php';
}

function autoload($class) {
	global $CONFIG, $CLASSMAP;
    if (isset($CONFIG) && is_array($CLASSMAP) && isset($CLASSMAP[$class])) {
        require_once APP_ROOT_DIR. DIRECTORY_SEPARATOR. $CLASSMAP[$class];
        return;
    }
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
    $f = APP_ROOT_DIR. DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.
        'php'.DIRECTORY_SEPARATOR.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
    if (file_exists($f)) {
        require_once $f;
        return;
    }
}
spl_autoload_register('autoload'); 


$CONFIG = new Config();
require APP_ROOT_DIR."/config.php";



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

///////////////////////// App

$app = new Silex\Application(); 
$app['debug'] = $CONFIG->isDebug;
$app['extensions'] = new ExtensionManager($app);
foreach($CONFIG->extensions as $extensionName) {
	require APP_ROOT_DIR.'/extension/'.$extensionName.'/extension.php';
}
$app['appconfig'] = new appconfiguration\AppConfigurationManager($DB, $CONFIG);
$app['config'] = $CONFIG;
$app['db'] = $DB;
$app['timesource'] = new TimeSource();
$app['messagequeproducerhelper'] = function($app) { return new MessageQueProducerHelper($app); };
$app['extensionhookrunner'] = new ExtensionHookRunner($app);

///////////////////////// LOGGING
if ($CONFIG->logFile) {
    $level = \Symfony\Bridge\Monolog\Logger::ERROR;
    if ($CONFIG->logLevel == 'emergency') {
        $level = \Symfony\Bridge\Monolog\Logger::EMERGENCY;
    } else if ($CONFIG->logLevel == 'alert') {
        $level = \Symfony\Bridge\Monolog\Logger::ALERT;
    } else if ($CONFIG->logLevel == 'critical') {
        $level = \Symfony\Bridge\Monolog\Logger::CRITICAL;
    } else if ($CONFIG->logLevel == 'warning') {
        $level = \Symfony\Bridge\Monolog\Logger::WARNING;
    } else if ($CONFIG->logLevel == 'notice') {
        $level = \Symfony\Bridge\Monolog\Logger::NOTICE;
    } else if ($CONFIG->logLevel == 'info') {
        $level = \Symfony\Bridge\Monolog\Logger::INFO;
    } else if ($CONFIG->logLevel == 'debug') {
        $level = \Symfony\Bridge\Monolog\Logger::DEBUG;
    }
	$app->register(new Silex\Provider\MonologServiceProvider(), array(
		'monolog.logfile' => $CONFIG->logFile,
		'monolog.name'=>$CONFIG->installTitle,
		'monolog.level'=>  $level,
	));
	if ($CONFIG->logToStdError) {
		$app['monolog']->pushHandler(new Monolog\Handler\StreamHandler('php://stderr', $level));
	}
}


function configureAppForThemeVariables(\models\SiteModel $site  = null) {
	global $app;
	$vars = parse_ini_file(APP_ROOT_DIR.'/core/theme/default/variables.ini', false);
	foreach($app['extensions']->getExtensions() as $dir=>$ext) {
		$file = APP_ROOT_DIR.'/extension/'.$dir.'/theme/default/variables.ini';
		if (file_exists($file)) {
			$vars = array_merge($vars, parse_ini_file($file, false));
		}
		$vars = array_merge($vars, $ext->getTemplateVariables($site));
	}
	if (is_array($app['config']->themeVariables) && isset($app['config']->themeVariables['default']) && is_array($app['config']->themeVariables['default']) ) {
		$vars = array_merge($vars, $app['config']->themeVariables['default']);
	}
	$app['twig']->addGlobal('themeVariables', $vars);
}


