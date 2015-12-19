<?php


namespace usernotifications\models;

use models\GroupModel;
use repositories\GroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupPromptNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UserWatchesGroupPrompt';
	}
	
	function setGroup(GroupModel $group) {
		$this->data['group'] = $group->getId();
	}

	
	/** @var GroupModel  **/
	var $group;
	
	private function loadGroupIfNeeded() {
		if (!$this->group && property_exists($this->data, 'group') && $this->data->group) {
			$repo = new GroupRepository;
			$this->group = $repo->loadById($this->data->group);
		}
	}
	
	public function getNotificationText() {
		$this->loadGroupIfNeeded();
		return "There will soon be no more events in the group: ".$this->group->getTitle();
	}
	
	public function getNotificationURL() {
		global $CONFIG;
		$this->loadGroupIfNeeded();
		return $CONFIG->getWebSiteDomainSecure($this->site->getSlug()).'/group/'.$this->group->getSlugForUrl();		
	}
}

