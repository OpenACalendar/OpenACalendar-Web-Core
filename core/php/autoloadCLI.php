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


///////////////////////// TWIG
$dirs = array();
foreach($CONFIG->extensions as $extensionName) {
	// Carefully ordered so extensions are first in list.
	// And in config, later extensions listed can overwrite earlier extensions.
	array_unshift($dirs,  APP_ROOT_DIR.'/extension/'.$extensionName.'/theme/default/templates');
	if ($CONFIG->isSingleSiteMode) array_unshift($dirs,  APP_ROOT_DIR.'/extension/'.$extensionName.'/theme/default/templatesSingleSite');
}
// default templates are last
// a special cache dir of our own ... because cli and web stuff may run as different users. 
// This way we can make sure each cache folder has the right perms for that user
if ($CONFIG->isSingleSiteMode) $dirs[] = APP_ROOT_DIR.'/core/theme/default/templatesSingleSite';
$dirs[] = APP_ROOT_DIR.'/core/theme/default/templates';
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
	$twig->addGlobal('COPYRIGHT_YEARS', COPYRIGHT_YEARS);
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
// We do NOT use $app->register(new Silex\Provider\SwiftmailerServiceProvider()); here because that 
// sets up some options that make sense for web but not for CLI like Spooling.
$app['mailer'] =  function ($c) {
	global $CONFIG;
	$transport = Swift_SmtpTransport::newInstance($CONFIG->SMTPHost, $CONFIG->SMTPPort);
	$transport->setUsername($CONFIG->SMTPUsername);
	$transport->setPassword($CONFIG->SMTPPassword);
	$transport->setEncryption($CONFIG->SMTPEncyption);
	return Swift_Mailer::newInstance($transport);
};

