<?php


namespace repositories;

use models\ImportedEventModel;
use models\EventModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportedEventIsEventRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function createLink(ImportedEventModel $importedEvent, EventModel $event) {
		$stat = $this->app['db']->prepare("INSERT INTO imported_event_is_event (imported_event_id, event_id, created_at) ".
				"VALUES (:imported_event_id, :event_id, :created_at)");
		$stat->execute(array(
			'imported_event_id'=>$importedEvent->getId(),
			'event_id'=>$event->getId(),
			'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
		));
	}
	
	
	
}
