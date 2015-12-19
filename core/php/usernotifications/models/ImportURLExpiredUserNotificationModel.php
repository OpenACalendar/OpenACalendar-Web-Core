<?php


namespace usernotifications\models;

use models\GroupModel;
use models\ImportURLModel;
use repositories\ImportURLRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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


	function setGroup(GroupModel $group) {
		$this->data['group'] = $group->getId();
	}

	/** @var GroupModel  **/
	var $group;

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
		// Checking $this->importURL exists is related to #261 - bad data might exist that doesn't have this set
		return "An Importer has expired: ".($this->importURL ? $this->importURL->getTitle() : null);
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		$this->loadImportURLIfNeeded();
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/importurl/'.$this->importURL->getSlug();
	}
	
	
	
}

