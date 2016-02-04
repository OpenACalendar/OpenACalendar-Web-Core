<?php


namespace usernotifications\models;

use models\GroupModel;
use models\ImportModel;
use repositories\ImportRepository;

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
	
	function setImport(ImportModel $import) {
		$this->data['import'] = $import->getId();
	}


	function setGroup(GroupModel $group) {
		$this->data['group'] = $group->getId();
	}

	/** @var GroupModel  **/
	var $group;

	/** @var ImportModel  **/
	var $import;
	
	private function loadImportURLIfNeeded() {
        global $app;
		if (!$this->import && property_exists($this->data, 'import') && $this->data->import) {
			$repo = new ImportRepository($app);
			$this->import = $repo->loadById($this->data->import);
		}
	}
	
	public function getNotificationText() {
		$this->loadImportURLIfNeeded();
        // Checking $this->import exists is related to #261 - bad data might exist that doesn't have this set
        // The change from importurl to import in https://github.com/OpenACalendar/OpenACalendar-Web-Core/issues/520 will also cause issues.
        return "An Importer has expired: ".($this->import ? $this->import->getTitle() : null);
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		$this->loadImportURLIfNeeded();
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/import/'.$this->import->getSlug();
	}
	
	
	
}

