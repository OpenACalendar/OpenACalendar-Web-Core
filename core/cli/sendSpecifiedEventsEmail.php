<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * To Call script, call with 
 *  - 1st param; ini file
 *  - 2nd param; enviroment eg Test
 * 
 * Sample INI.
 * 
 * Use Headers:
 * [Common]
 * [EnvironmentTest]
 * [EnvironmentReal]
 * 
 * Use Fields under any header: 
 * Subject="Events in Scotland"
 * SiteID=1
 * FromEmail=james@jarofgreen.co.uk
 * FromName=James
 * TimeZone=Europe/London
 * (Next 2 must be relative to the INI file, not absolute paths!)
 * IntroTXTFile = intro.txt
 * IntroHTMLFile = intro.html
 * To=hello@jarofgreen.co.uk
 * 
 * To send events from now to the end of a set calendar month:
 * Year=2013
 * Month=12
 * 
 * To send events for the next number of days:
 * Days=31
 *
 * Optional filters:
 * AreaID=1
 *
 * Can also optionally list child areas between intro and events:
 *
 * ListChildAreas=true
 * ListChildAreasIntro=Browse events in:
 * ListChildAreasWithNoEvents=true
 *
 * By using different environments and different headers in the ini file you can 
 * do stuff like sending a test email to yourself before sending the real email 
 * to others.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 */

use repositories\SiteRepository;
use repositories\builders\VenueRepositoryBuilder;
use repositories\AreaRepository;

// ######################################################### Set up Enviroment, Check
if (!isset($argv[1])) die("CONFIG?\n");
if (!isset($argv[2])) die("Environment?\n");
$environment = $argv[2];
# change to directory of the ini file so all paths to intro files can be relative to that.
if (substr($argv[1], 0,1) == '/') {
	$configDataDir = dirname($argv[1]);
} else {
	$configDataDir = getcwd().'/'.dirname($argv[1]);
}
$thisconfig = new IniConfigWithEnvironment($environment, $argv[1]);

foreach(array('SiteID','Subject','FromEmail','FromName','TimeZone','IntroTXTFile','IntroHTMLFile') as $key) {
	if (!$thisconfig->hasValue($key)) {
		die("NO CONFIG ". $key." ?\n");
	}
}

// ######################################################### Load site, build query
$siteRepository = new SiteRepository();
$site = $siteRepository->loadById($thisconfig->get('SiteID'));
if (!$site) die("NO SITE?\n");
$calendar = new \RenderCalendar();
$calendar->getEventRepositoryBuilder()->setSite($site);
$calendar->getEventRepositoryBuilder()->setIncludeDeleted(false);

// ######################################################### Set Start and End
$start = \TimeSource::getDateTime();
$start->setTime(0, 0, 0);
$end =  \TimeSource::getDateTime();
$end->setTime(0, 0, 0);
if ($thisconfig->hasValue("Month") && $thisconfig->hasValue("Year")) {
	// Options for setting end; by a set month and year
	if ($thisconfig->get('Month') == 12) {
		$end->setDate($thisconfig->get('Year')+1, 1, 1);
	} else {
		$end->setDate($thisconfig->get('Year'), $thisconfig->get('Month')+1, 1);
	}
	$end->sub(new \DateInterval('PT1S'));
} else if ($thisconfig->hasValue("Days")) {
	// Options for setting end; by a number of days
	$end->add(new \DateInterval('P'.$thisconfig->get("Days").'D'));
} else {
	// Options for setting end; default to 30 days
	$end->add(new \DateInterval('P30D'));
}
$calendar->setStartAndEnd($start, $end);

// ######################################################### Filters?
$area = null;
if ($thisconfig->hasValue('AreaID')) {
	$repo = new repositories\AreaRepository();
	$area = $repo->loadById($thisconfig->get('AreaID'));
	if ($area) {
		$calendar->getEventRepositoryBuilder()->setArea($area);
	} else {
		die("Area not loaded!\n");
	}
}

// ######################################################### Get Data
$calendar->getEventRepositoryBuilder()->setIncludeAreaInformation(true);

$calData = $calendar->getData();


$childAreas = array();
if ($thisconfig->getBoolean('ListChildAreas', false)) {
	$areaRepoBuilder = new \repositories\builders\AreaRepositoryBuilder();
	$areaRepoBuilder->setSite($site);
	$areaRepoBuilder->setIncludeDeleted(false);

	if ($area) {
		$areaRepoBuilder->setParentArea($area);
	} else {
		$areaRepoBuilder->setNoParentArea(true);
	}
	$childAreas = array();
	$areaRepository = new AreaRepository();
	foreach($areaRepoBuilder->fetchAll() as $area) {
		$areaRepository->updateFutureEventsCache($area);
		if ($thisconfig->getBoolean('ListChildAreasWithNoEvents', false) || $area->getCachedFutureEvents() > 0) {
			$childAreas[] = $area;
		}
	}
}

// ######################################################### Build Email Content, show user.
configureAppForSite($site);

$messageText = $app['twig']->render('email/sendSpecifiedEventsEmail.cli.txt.twig', array(
	'data'=>$calData,
	'currentSite'=>$site,
	'currentTimeZone'=>$thisconfig->get('TimeZone'),
	'intro'=>  file_get_contents($configDataDir.'/'.$thisconfig->get('IntroTXTFile')),
	'listChildAreas'=>$thisconfig->getBoolean('ListChildAreas'),
	'listChildAreasIntro'=>$thisconfig->get('ListChildAreasIntro'),
	'childAreas'=>$childAreas,
));

$messageHTML = $app['twig']->render('email/sendSpecifiedEventsEmail.cli.html.twig', array(
	'data'=>$calData,
	'currentSite'=>$site,
	'currentTimeZone'=>$thisconfig->get('TimeZone'),
	'intro'=> file_get_contents($configDataDir.'/'.$thisconfig->get('IntroHTMLFile')),
	'listChildAreas'=>$thisconfig->getBoolean('ListChildAreas'),
	'listChildAreasIntro'=>$thisconfig->get('ListChildAreasIntro'),
	'childAreas'=>$childAreas,
));

if ($CONFIG->isDebug) file_put_contents('/tmp/sendEventsEmail.txt', $messageText);
if ($CONFIG->isDebug) file_put_contents('/tmp/sendEventsEmail.html', $messageHTML);

print $messageHTML."\n";
print $messageText."\n";
print "Will Send To ".$thisconfig->get('To')." in a few seconds ...\n\n\n";
sleep(10);

// ######################################################### Send
print "Sending to ".$thisconfig->get('To')." ...\n\n\n";

$message = \Swift_Message::newInstance();
$message->setSubject($thisconfig->get('Subject'));
$message->setFrom(array($thisconfig->get('FromEmail') => $thisconfig->get('FromName')));
$message->setTo($thisconfig->get('To'));
$message->setBody($messageText);
$message->addPart($messageHTML,'text/html');
$app['mailer']->send($message);

print "Done, Sent To: ".$thisconfig->get('To')."\n\n\n";


