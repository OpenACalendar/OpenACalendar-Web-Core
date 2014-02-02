<?php

namespace repositories;

use models\EventModel;
use models\EventHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventHistoryRepository {

	
	public function loadByEventAndtimeStamp(EventModel $event, $timestamp) {
		global $DB;
		$stat = $DB->prepare("SELECT event_history.* FROM event_history ".
				"WHERE event_history.event_id =:id AND event_history.created_at =:cat");
		$stat->execute(array( 'id'=>$event->getId(), 'cat'=>date("Y-m-d H:i:s",$timestamp) ));
		if ($stat->rowCount() > 0) {
			$event = new EventHistoryModel();
			$event->setFromDataBaseRow($stat->fetch());
			return $event;
		}
	}
	
}


