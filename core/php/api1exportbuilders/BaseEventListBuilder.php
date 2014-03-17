<?php
namespace api1exportbuilders;

use repositories\builders\EventRepositoryBuilder;
use models\SiteModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseEventListBuilder  extends BaseBuilder {
	
	protected $eventRepositoryBuilder;
	
	protected $events = array();

	
	public function __construct(SiteModel $site = null, $timeZone = null, $title = null) {
		parent::__construct($site, $timeZone, $title);
		$this->eventRepositoryBuilder = new EventRepositoryBuilder();
		if ($site) $this->eventRepositoryBuilder->setSite($site);
	}

	abstract public function addEvent(EventModel $event);

	
	public function build() {	
		foreach($this->eventRepositoryBuilder->fetchAll() as $event) {
			$this->addEvent($event);
		}
	}
		
	public function getEventRepositoryBuilder() { return $this->eventRepositoryBuilder; }


	
}

