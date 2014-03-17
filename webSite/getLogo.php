<?php
require 'localConfig.php';
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';

use repositories\SiteRepository;
use repositories\MediaRepository;

/**
 *
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$siteRepository = new SiteRepository();
$site = $siteRepository->loadByDomain($_SERVER['SERVER_NAME']);

if (!$site) {
	// 404 TODO
	print "404";
} else if ($site->getIsClosedBySysAdmin()) {
	// TODO
	print "closed";
} else {

	function writeFile($fileName, $mimeType) {
		global $CONFIG;
		$fp = fopen($fileName, 'rb');
		header("Content-Type: ". $mimeType);
		if ($CONFIG->cacheSiteLogoInSeconds > 0) {
			header("Cache-Control: cache");
			header("Expires: ".date("r", time() + $CONFIG->cacheSiteLogoInSeconds));
		}
		fpassthru($fp);
		exit;	
	}

	if ($site->getLogoMediaId()) {
		$mediaRepository = new MediaRepository();
		$media = $mediaRepository->loadByID($site->getLogoMediaId());
		if ($media && !$media->getIsDeleted()) {
			if ($media->writeThumbnailImageToWebBrowser($CONFIG->cacheSiteLogoInSeconds)) {
				// done! We can die happy, our mission achieved!
				die();
			}
		}
	}

	// send standard logo if we can't send another
	writeFile(dirname(__FILE__).'/theme/default/img/logo.png','image/png' );
	
}
