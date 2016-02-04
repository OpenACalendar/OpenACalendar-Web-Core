<?php



namespace repositories;

use models\EventModel;
use models\EventHistoryModel;
use models\EventRecurSetModel;
use models\ImportedEventModel;
use models\UserAccountModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetRepository {


    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }


    public function isEventInSetWithNotDeletedFutureEvents(EventModel $event) {

		if (!$event->getEventRecurSetId()) return false;
		
		$stat = $this->app['db']->prepare("SELECT event_information.id FROM event_information ".
				"WHERE event_recur_set_id =:id AND start_at > :start_at AND is_deleted = '0'");
		$stat->execute(array( 
			'id'=>$event->getEventRecurSetId(), 
			'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
			));
		if ($stat->rowCount() > 0) {
			return true;
		}
		
		return false;
		
	}
	
	/** @return \models\EventRecurSetModel **/
	public function getForEvent(EventModel $event) {

		$eventRecurSet = $this->loadForEvent($event);
		if (!$eventRecurSet) {
			
			try {
                $this->app['db']->beginTransaction();
				
				$stat = $this->app['db']->prepare("INSERT INTO event_recur_set (created_at) VALUES (:created_at) RETURNING id");
				$stat->execute(array( 'created_at'=>  $this->app['timesource']->getFormattedForDataBase() ));
				$data = $stat->fetch();
				$eventRecurSet = new EventRecurSetModel();
				$eventRecurSet->setId($data['id']);

				$stat = $this->app['db']->prepare("UPDATE event_information SET event_recur_set_id = :ersi WHERE id = :id");
				$stat->execute(array('ersi'=>$eventRecurSet->getId(), 'id'=>$event->getId()));

                $this->app['db']->commit();
			} catch (Exception $e) {
                $this->app['db']->rollBack();
			}
		}
		
		return $eventRecurSet;
		
	}
	
	
	public function loadForEvent(EventModel $event) {
		if ($event->getEventRecurSetId()) {
			$stat = $this->app['db']->prepare("SELECT event_recur_set.* FROM event_recur_set WHERE id =:id");
			$stat->execute(array( 'id'=>$event->getEventRecurSetId() ));
			if ($stat->rowCount() > 0) {
				$eventRecurSet = new EventRecurSetModel();
				$eventRecurSet->setFromDataBaseRow($stat->fetch());
				return $eventRecurSet;
			}
		}
	}

	public function getForImportedEvent(ImportedEventModel $importedEventModel) {

		$stat = $this->app['db']->prepare("SELECT event_recur_set.* FROM event_recur_set ".
			" JOIN event_information ON event_information.event_recur_set_id = event_recur_set.id ".
			" JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id ".
			" WHERE imported_event_is_event.imported_event_id = :id");

		$stat->execute(array( 'id'=>$importedEventModel->getId()));

		if ($stat->rowCount() > 0) {
			$ers = new EventRecurSetModel();
			$ers->setFromDataBaseRow( $stat->fetch() );
			return $ers;
		}

	}
	
}


