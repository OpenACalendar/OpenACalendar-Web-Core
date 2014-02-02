<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountRememberMeRepository;

///////////////////////// Redirect to Correct Domain

$parseDomain = new ParseDomain($_SERVER['SERVER_NAME']);
if (!$parseDomain->isCoveredByCookies()) {
	header("Location: http://".$CONFIG->webIndexDomain);
	die("REDIRECT!");
}

///////////////////////// Sessions

/** @var WebSession **/
$WEBSESSION = new WebSession();
/** @var FlashMessages **/
$FLASHMESSAGES = new FlashMessages($WEBSESSION);

///////////////////////// App

$app = new Silex\Application(); 
$app['debug'] = $CONFIG->isDebug;

///////////////////////// LOGGING
if ($CONFIG->logFile) {
	$app->register(new Silex\Provider\MonologServiceProvider(), array(
		'monolog.logfile' => $CONFIG->logFile,
		'monolog.name'=>$CONFIG->siteTitle,
		'monolog.level'=>  \Symfony\Bridge\Monolog\Logger::ERROR,
	));
}

///////////////////////// TWIG
$dirs = array();
foreach($CONFIG->extensions as $extensionName) {
	// Carefully ordered so extensions are first in list.
	// And in config, later extensions listed can overwrite earlier extensions.
	array_unshift($dirs,  APP_ROOT_DIR.'/extension.'.$extensionName.'/templates');
	if ($CONFIG->isSingleSiteMode) array_unshift($dirs,  APP_ROOT_DIR.'/extension.'.$extensionName.'/templatesSingleSite');
}
// default templates are last
if ($CONFIG->isSingleSiteMode) $dirs[] = APP_ROOT_DIR.'/extension.Core/templatesSingleSite';
$dirs[] = APP_ROOT_DIR.'/extension.Core/templates';
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => (count($dirs) == 1 ? $dirs[0] : $dirs),
	'twig.options' => array (
		'cache' => APP_ROOT_DIR.'/cache/templates.web',
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
    $twig->addExtension(new twig\extensions\LinkInfoExtension($app));
	$twig->addGlobal('config', $CONFIG);
	$twig->addGlobal('COPYRIGHT_YEARS', COPYRIGHT_YEARS);
    return $twig;
}));

///////////////////////// Mailer
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app['swiftmailer.options'] = array(
    'host' => 'localhost',
);


///////////////////////// Forms

$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
      'locale' => 'GB',
      'translation.class_path' =>  APP_ROOT_DIR . '/vendor/symfony/src',
      'translator.messages' => array()
));


///////////////////////// Users

$app['debug'] = $CONFIG->isDebug;
function userLogIn(UserAccountModel $user) {
	global $WEBSESSION;
	if (!$user->getIsClosedBySysAdmin()) {
		$WEBSESSION->set('userID', $user->getId());
	}
}

function userLogOut() {
	global $USER_CURRENT, $USER_CURRENT_LOADED, $CONFIG, $WEBSESSION;
	$WEBSESSION->set('userID',null);
	if (isset($_COOKIE['userID']) && isset($_COOKIE['userKey'])) {
		setcookie("userID","",null,'/',$CONFIG->webCommonSessionDomain,false,true);
		setcookie("userKey","",null,'/',$CONFIG->webCommonSessionDomain,false,true);
		$repo = new UserAccountRememberMeRepository();
		$repo->deleteByUserAccountIDAndAccessKey($_COOKIE['userID'], $_COOKIE['userKey']);			
	}
	$USER_CURRENT_LOADED = true;
	$USER_CURRENT = null;
}

/** @var UserAccountModel **/
$USER_CURRENT = null;
$USER_CURRENT_LOADED = false;

function userGetCurrent() {
	global $USER_CURRENT, $USER_CURRENT_LOADED, $WEBSESSION;
	if (!$USER_CURRENT_LOADED) {
		if ($WEBSESSION->has('userID') && $WEBSESSION->get('userID') > 0) {
			$uar = new UserAccountRepository();
			$USER_CURRENT = $uar->loadByID($WEBSESSION->get('userID'));
			if ($USER_CURRENT->getIsClosedBySysAdmin()) $USER_CURRENT = null;
		} else if (isset($_COOKIE['userID']) && isset($_COOKIE['userKey'])) {
			$uarmr = new UserAccountRememberMeRepository();
			$uarm = $uarmr->loadByUserAccountIDAndAccessKey($_COOKIE['userID'], $_COOKIE['userKey']);
			if ($uarm) {
				$uar = new UserAccountRepository();
				$USER_CURRENT = $uar->loadByID($uarm->getUserAccountId());
				if ($USER_CURRENT->getIsClosedBySysAdmin()) $USER_CURRENT = null;
				if ($USER_CURRENT) {
					userLogIn($USER_CURRENT);
				}
			}
		}
		
		$USER_CURRENT_LOADED = true;
	}	
	return $USER_CURRENT;
}

$app->before(function () use ($app) {
	$app['twig']->addGlobal('currentUser', userGetCurrent());
	$app['twig']->addFunction(new Twig_SimpleFunction('getCurrentUserPrivateFeedKey', function () {
		$r = new \repositories\UserAccountPrivateFeedKeyRepository();
		return $r->getForUser(userGetCurrent());
	}));
	$app['twig']->addFunction(new Twig_SimpleFunction('getCSFRToken', function () {
		global $WEBSESSION;
		return $WEBSESSION->getCSFRToken();
	}));
	$app['twig']->addFunction(new Twig_SimpleFunction('getAndClearFlashMessages', function () {
		global $FLASHMESSAGES;
		return $FLASHMESSAGES->getAndClearMessages();
	}));	
	$app['twig']->addFunction(new Twig_SimpleFunction('getAndClearFlashErrors', function () {
		global $FLASHMESSAGES;
		return $FLASHMESSAGES->getAndClearErrors();
	}));		
	# ////////////// 12 or 24 hour clock
	$clock12Hour = true;
	if (userGetCurrent()) {
		$clock12Hour = userGetCurrent()->getIsClock12Hour() ;
	}
	$app['currentUserClock12Hour'] = $clock12Hour;
	$app['twig']->addGlobal('currentUserClock12Hour', $clock12Hour);
});


