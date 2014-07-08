<?php

namespace repositories\builders;

use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\EventModel;
use models\MediaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}
	

	/** @var EventModel **/
	protected $event;
	
	public function setEvent(EventModel $event) {
		$this->event = $event;
	}

	/** @var GroupModel **/
	protected $group;
	
	public function setGroup(GroupModel $group) {
		$this->group = $group;
	}

	/** @var VenueModel **/
	protected $venue;
	
	public function setVenue(VenueModel $venue) {
		$this->venue = $venue;
	}

	/** @var EventModel **/
	protected $eventNotIn;

	public function setNotInEvent(EventModel $event) {
		$this->eventNotIn = $event;
	}

	/** @var GroupModel **/
	protected $groupNotIn;
	
	public function setNotInGroup(GroupModel $group) {
		$this->groupNotIn = $group;
	}

	/** @var VenueModel **/
	protected $venueNotIn;
	
	public function setNotInVenue(VenueModel $venue) {
		$this->venueNotIn = $venue;
	}

	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}
	
	protected function build() {

		if ($this->site) {
			$this->where[] =  " media_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->group) {
			$this->joins[] =  " JOIN media_in_group AS media_in_group ON media_in_group.media_id = media_information.id ".
					"AND media_in_group.removed_at IS NULL AND media_in_group.group_id = :group_id ";
			$this->params['group_id'] = $this->group->getId();
		} else if ($this->groupNotIn) {
			$this->joins[] =  " LEFT JOIN media_in_group AS media_in_group ON media_in_group.media_id = media_information.id ".
					"AND media_in_group.removed_at IS NULL AND media_in_group.group_id = :group_id ";
			$this->where[] = " media_in_group.added_at IS NULL ";
			$this->params['group_id'] = $this->groupNotIn->getId();
		}
		
		if ($this->venue) {
			$this->joins[] =  " JOIN media_in_venue AS media_in_venue ON media_in_venue.media_id = media_information.id ".
					"AND media_in_venue.removed_at IS NULL AND media_in_venue.venue_id = :venue_id ";
			$this->params['venue_id'] = $this->venue->getId();
		} else if ($this->venueNotIn) {
			$this->joins[] =  " LEFT JOIN media_in_venue AS media_in_venue ON media_in_venue.media_id = media_information.id ".
					"AND media_in_venue.removed_at IS NULL AND media_in_venue.venue_id = :venue_id ";
			$this->where[] = " media_in_venue.added_at IS NULL ";
			$this->params['venue_id'] = $this->venueNotIn->getId();
		}

		if ($this->event) {
			$this->joins[] =  " JOIN media_in_event AS media_in_event ON media_in_event.media_id = media_information.id ".
				"AND media_in_event.removed_at IS NULL AND media_in_event.event_id = :event_id ";
			$this->params['event_id'] = $this->event->getId();
		} else if ($this->eventNotIn) {
			$this->joins[] =  " LEFT JOIN media_in_event AS media_in_event ON media_in_event.media_id = media_information.id ".
				"AND media_in_event.removed_at IS NULL AND media_in_event.event_id = :event_id ";
			$this->where[] = " media_in_event.added_at IS NULL ";
			$this->params['event_id'] = $this->eventNotIn->getId();
		}
		
		if (!$this->include_deleted) {
			$this->where[] = " media_information.deleted_at IS NULL ";
		}
		
	}
	
	protected function buildStat() {
				global $DB;
		
		
		$sql = "SELECT media_information.* FROM media_information ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY media_information.id ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$media = new MediaModel();
			$media->setFromDataBaseRow($data);
			$results[] = $media;
		}
		return $results;
		
	}

}

