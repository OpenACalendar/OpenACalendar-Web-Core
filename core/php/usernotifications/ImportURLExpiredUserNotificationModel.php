<?php


namespace usernotifications;

use models\GroupModel;
use models\ImportURLModel;
use repositories\ImportURLRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLExpiredUserNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'ImportURLExpired';
	}
	
	function setImportURL(ImportURLModel $importURL) {
		$this->data['importurl'] = $importURL->getId();
	}
	
	/** @var ImportURLModel  **/
	var $importURL;
	
	private function loadImportURLIfNeeded() {
		if (!$this->importURL && property_exists($this->data, 'importurl') && $this->data->importurl) {
			$repo = new ImportURLRepository;
			$this->importURL = $repo->loadById($this->data->importurl);
		}
	}
	
	public function getNotificationText() {
		$this->loadImportURLIfNeeded();
		return "An Importer has expired: ".$this->importURL->getTitle();
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		$this->loadImportURLIfNeeded();
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/importurl/'.$this->importURL->getSlug();
	}
	
	
	
}

