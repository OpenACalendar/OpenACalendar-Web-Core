<?php


namespace usernotifications\models;

use models\GroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesNotifyNotificationModel extends \BaseUserNotificationModel {
	
	function __construct() {
		$this->from_extension_id = 'org.openacalendar';
		$this->from_user_notification_type = 'UserWatchesNotify';
		$this->data = array('content'=>array());
	}
	
	public function getNotificationText() {
		if (count($this->data->content) == 1) {
			return "There are changes to ".$this->data->content[0]->watchedThingTitle;
		} else {
			$out = array();
			foreach ($this->data->content as $content) {
				$out[] = $content->watchedThingTitle;
			}
			return "There are changes to: ".implode(", ",$out);
		}
	}
	
	public function getNotificationURL() {
		// TODO - really bad to store URL in notification, what if site home changes!
		// TODO - what if more than 1 URL !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		return $this->data->content[0]->watchedThingURL;
	}

	public function addContent(\BaseUserWatchesNotifyContent $content) {
		$this->data['content'][] = array(
			'watchedThingTitle'=>$content->getWatchedThingTitle(),
			'watchedThingURL'=>$content->getWatchedThingURL(),
		);
	}
}

