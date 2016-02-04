<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountRememberMeRepository;

///////////////////////// Redirect to Correct Domain

$parseDomain = new ParseDomain($app, $_SERVER['SERVER_NAME']);
if (!$parseDomain->isCoveredByCookies()) {
	if ($app['config']->isSingleSiteMode) {
		header("Location: ".$app['config']->getWebIndexDomainSecure() . $_SERVER['REQUEST_URI'] );
	} else {
		// Not sure how to improve this; it's hard to work out which domain they were trying to hit.
		header("Location: ".$app['config']->getWebIndexDomainSecure());

	}
	die("REDIRECT!");
}


///////////////////////// Sessions

/** @var WebSession **/
$WEBSESSION = new WebSession($app);
$app['websession'] = $WEBSESSION;
/** @var FlashMessages **/
$FLASHMESSAGES = new FlashMessages($WEBSESSION);
$app['flashmessages'] = $FLASHMESSAGES;
/** @var UserAgent **/
$USERAGENT = new \UserAgent();
$app['userAgent'] = $USERAGENT;




///////////////////////// TWIG
$dirs = array();
foreach($CONFIG->extensions as $extensionName) {
	// Carefully ordered so extensions are first in list.
	// And in config, later extensions listed can overwrite earlier extensions.
	array_unshift($dirs,  APP_ROOT_DIR.'/extension/'.$extensionName.'/theme/default/templates');
	if ($CONFIG->isSingleSiteMode) array_unshift($dirs,  APP_ROOT_DIR.'/extension/'.$extensionName.'/theme/default/templatesSingleSite');
}
// default templates are last
if ($CONFIG->isSingleSiteMode) $dirs[] = APP_ROOT_DIR.'/core/theme/default/templatesSingleSite';
$dirs[] = APP_ROOT_DIR.'/core/theme/default/templates';
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => (count($dirs) == 1 ? $dirs[0] : $dirs),
	'twig.options' => array (
		'cache' => APP_ROOT_DIR.'/cache/templates.web',
	),
	'twig.form.templates'=>array ('forms.html.twig'),
));
unset($dirs);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new \JMBTechnologyLimited\Twig\Extensions\SameDayExtension());
    $twig->addExtension(new \JMBTechnologyLimited\Twig\Extensions\LinkifyExtension(array(
        'callback' => function($url, $caption, $isEmail) {
                if ($isEmail) {
                    return '<a href="mailto:'.$url.'">'.$url.'</a>';
                } else {
                    $bits = parse_url($url);
                    return '<a href="'.$url.'" target="_blank">'.(isset($bits['host']) ? $bits['host'] : $url).'</a>';
                }
            }
    )));
    $twig->addExtension(new twig\extensions\TypeCheckExtension($app));
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    $twig->addExtension(new \JMBTechnologyLimited\Twig\Extensions\LinkInfoExtension());
    $twig->addExtension(new Twig_Extensions_Extension_Date());
    $twig->addExtension(new twig\extensions\EventsCountExtension($app));
	$twig->addGlobal('config', $app['config']);
	$twig->addGlobal('extensions', $app['extensions']);
	$twig->addGlobal('COPYRIGHT_YEARS', COPYRIGHT_YEARS);
    return $twig;
}));

///////////////////////// Mailer
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app['swiftmailer.options'] = array(
	'host' => $CONFIG->SMTPHost,
	'port' => $CONFIG->SMTPPort,
	'username' => $CONFIG->SMTPUsername,
	'password' => $CONFIG->SMTPPassword,
	'encryption' => $CONFIG->SMTPEncyption,
	'auth_mode' => $CONFIG->SMTPAuthMode,
);


///////////////////////// Forms

$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
      'locale' => 'GB',
      'translation.class_path' =>  APP_ROOT_DIR . '/vendor/symfony/src',
      'translator.messages' => array()
));


///////////////////////// Users

function userLogIn(UserAccountModel $user) {
	global $WEBSESSION;
	if (!$user->getIsClosedBySysAdmin()) {
		$WEBSESSION->set('userID', $user->getId());
	}
}

function userLogOut() {
	global $USER_CURRENT, $USER_CURRENT_LOADED, $CONFIG, $WEBSESSION, $app;
	$WEBSESSION->set('userID',null);
	$WEBSESSION->set('sysAdminLastActive',null);
	if (isset($_COOKIE['userID']) && isset($_COOKIE['userKey'])) {
		setcookie("userID","",null,'/',$CONFIG->webCommonSessionDomain,false,true);
		setcookie("userKey","",null,'/',$CONFIG->webCommonSessionDomain,false,true);
		$repo = new UserAccountRememberMeRepository($app);
		$repo->deleteByUserAccountIDAndAccessKey($_COOKIE['userID'], $_COOKIE['userKey']);			
	}
	$USER_CURRENT_LOADED = true;
	$USER_CURRENT = null;
}

/** @var UserAccountModel **/
$USER_CURRENT = null;
$USER_CURRENT_LOADED = false;

/**
 * DEPRECATED This should only be called once, to load into $app['currentUser']. So $USER_CURRENT & $USER_CURRENT_LOADED shouldn't be needed.
 * At some point in future, remove this function and put the logic into code that just writes to $app['currentUser'] only.
 *
 * @return UserAccountModel|null
 */
function userGetCurrent() {
	global $USER_CURRENT, $USER_CURRENT_LOADED, $WEBSESSION, $app;
	if (!$USER_CURRENT_LOADED) {
		if ($WEBSESSION->has('userID') && $WEBSESSION->get('userID') > 0) {
			$uar = new UserAccountRepository($app);
			$USER_CURRENT = $uar->loadByID($WEBSESSION->get('userID'));
			if ($USER_CURRENT && $USER_CURRENT->getIsClosedBySysAdmin()) $USER_CURRENT = null;
		} else if (isset($_COOKIE['userID']) && isset($_COOKIE['userKey'])) {
			$uarmr = new UserAccountRememberMeRepository($app);
			$uarm = $uarmr->loadByUserAccountIDAndAccessKey($_COOKIE['userID'], $_COOKIE['userKey']);
			if ($uarm) {
				$uar = new UserAccountRepository($app);
				$USER_CURRENT = $uar->loadByID($uarm->getUserAccountId());
				if ($USER_CURRENT && $USER_CURRENT->getIsClosedBySysAdmin()) $USER_CURRENT = null;
				if ($USER_CURRENT) {
					userLogIn($USER_CURRENT);
				}
			}
		}
		
		$USER_CURRENT_LOADED = true;
	}	
	return $USER_CURRENT;
}

$app['currentUser'] = userGetCurrent();

$app->before(function () use ($app) {
	$app['twig']->addGlobal('currentUser', $app['currentUser']);
	$app['twig']->addFunction(new Twig_SimpleFunction('getCurrentUserPrivateFeedKey', function () use ($app) {
		$r = new \repositories\UserAccountPrivateFeedKeyRepository($app);
		return $r->getForUser( userGetCurrent());
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
	if ($app['currentUser']) {
		$clock12Hour = $app['currentUser']->getIsClock12Hour() ;
	}
	$app['currentUserClock12Hour'] = $clock12Hour;
	$app['twig']->addGlobal('currentUserClock12Hour', $clock12Hour);
});


