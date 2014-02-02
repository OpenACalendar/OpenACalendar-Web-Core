<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
use models\SiteModel;
use models\UserAccountModel;

	
///////////////////////// App
$app = new Silex\Application(); 
$app['debug'] = $CONFIG->isDebug;

///////////////////////// LOGGING

$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => $CONFIG->logFile,
	'monolog.name'=>$CONFIG->siteTitle,
	'monolog.level'=>  \Symfony\Bridge\Monolog\Logger::ERROR,
));

///////////////////////// TWIG
$dirs = array();
foreach($CONFIG->extensions as $extensionName) {
	// Carefully ordered so extensions are first in list.
	// And in config, later extensions listed can overwrite earlier extensions.
	array_unshift($dirs,  APP_ROOT_DIR.'/extension.'.$extensionName.'/templates');
	if ($CONFIG->isSingleSiteMode) array_unshift($dirs,  APP_ROOT_DIR.'/extension.'.$extensionName.'/templatesSingleSite');
}
// default templates are last
// a special cache dir of our own ... because cli and web stuff may run as different users. 
// This way we can make sure each cache folder has the right perms for that user
if ($CONFIG->isSingleSiteMode) $dirs[] = APP_ROOT_DIR.'/extension.Core/templatesSingleSite';
$dirs[] = APP_ROOT_DIR.'/extension.Core/templates';
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => (count($dirs) == 1 ? $dirs[0] : $dirs),
	'twig.options' => array (
		'cache' => APP_ROOT_DIR.'/cache/templates.cli'
	),
	'twig.form.templates'=>array ('forms.html.twig'),
));
unset($dirs);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
	global $CONFIG;
	$twig->addExtension(new twig\extensions\LocaleExtension($app));
	$twig->addExtension(new twig\extensions\SameDayExtension($app));
	$twig->addExtension(new twig\extensions\LinkifyExtension($app));
	$twig->addExtension(new twig\extensions\TypeCheckExtension($app));
	$twig->addExtension(new twig\extensions\WordWrapExtension($app));
	$twig->addExtension(new twig\extensions\TruncateExtension($app));
	$twig->addGlobal('config', $CONFIG);
	$twig->addGlobal('currentUserClock12Hour', true);
	return $twig;
}));
	

function configureAppForSite(SiteModel $site) {
	global $app;

	# ////////////// Site
	// not sure where this is used, would be good to remove and switch to currentSite
	$app['twig']->addGlobal('site', $site);	
	$app['site'] = $site;
	// this is the proper convention
	$app['twig']->addGlobal('currentSite', $site);	
	$app['currentSite'] = $site;

	# ////////////// Timezone
	$timezone = "";
	if (in_array('Europe/London',$site->getCachedTimezonesAsList())) {
		$timezone = 'Europe/London';
	} else {
		$timezone  = $site->getCachedTimezonesAsList()[0];
	}
	$app['twig']->addGlobal('currentTimeZone', $timezone);	
	$app['currentTimeZone'] = $timezone;
	
}

function configureAppForUser(UserAccountModel $user = null) {
	global $app;

	# ////////////// 12 or 24 hour clock
	$clock12Hour = true;
	if ($user) {
		$clock12Hour = $user->getIsClock12Hour();
	}
	$app['currentUserClock12Hour'] = $clock12Hour;
	$app['twig']->addGlobal('currentUserClock12Hour', $clock12Hour);	
}

///////////////////////// SWIFT MAILER
// Outside Silex. There is no need for it to be inside it and it does some funny spooling shit, so just keep it outside
$SWIFT_MAILER = null;
function getSwiftMailer() {
	global $SWIFT_MAILER;
	if ($SWIFT_MAILER == null) {
		$transport = Swift_SmtpTransport::newInstance('localhost', 25);
		$SWIFT_MAILER = Swift_Mailer::newInstance($transport);
	}
	return $SWIFT_MAILER;
}

