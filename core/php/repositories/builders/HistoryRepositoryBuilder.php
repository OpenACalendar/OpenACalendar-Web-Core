<?php


namespace repositories\builders;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\VenueModel;
use models\TagModel;
use models\ImportModel;
use models\UserAccountModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
use models\TagHistoryModel;
use models\ImportHistoryModel;
use models\API2ApplicationModel;
use repositories\builders\config\HistoryRepositoryBuilderConfig;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HistoryRepositoryBuilder {

    protected $app;

	/** @var \repositories\builders\config\HistoryRepositoryBuilderConfig  */
	protected $historyRepositoryBuilderConfig;

	function __construct(Application $application, HistoryRepositoryBuilderConfig $historyRepositoryBuilderConfig = null)
	{
        $this->app = $application;
		$this->historyRepositoryBuilderConfig = $historyRepositoryBuilderConfig ? $historyRepositoryBuilderConfig : new HistoryRepositoryBuilderConfig();
	}

	/**
	 * @return \repositories\builders\config\HistoryRepositoryBuilderConfig
	 */
	public function getHistoryRepositoryBuilderConfig()
	{
		return $this->historyRepositoryBuilderConfig;
	}

	public function getIncludeEventHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeEventHistory();
	}

	public function setIncludeEventHistory($includeEventHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeEventHistory($includeEventHistory);
	}

	public function getIncludeGroupHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeGroupHistory();
	}

	public function setIncludeGroupHistory($includeGroupHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeGroupHistory($includeGroupHistory);
	}

	public function getIncludeTagHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeTagHistory();
	}

	public function setIncludeTagHistory($includeTagHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeTagHistory($includeTagHistory);
	}

	public function getIncludeVenueHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeVenueHistory();
	}

	public function setIncludeVenueHistory($includeVenueHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeVenueHistory($includeVenueHistory);
	}
	
	public function getIncludeAreaHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeAreaHistory();
	}

	public function setIncludeAreaHistory($includeAreaHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeAreaHistory($includeAreaHistory);
	}

	public function getIncludeImportURLHistory() {
		return $this->historyRepositoryBuilderConfig->getIncludeImportURLHistory();
	}

	public function setIncludeImportURLHistory($includeImportURLHistory) {
		$this->historyRepositoryBuilderConfig->setIncludeImportURLHistory($includeImportURLHistory);
	}

	public function setSince($since) {
		$this->historyRepositoryBuilderConfig->setSince($since);
	}

	
	public function setSite(SiteModel $site) {
		$this->historyRepositoryBuilderConfig->setSite($site);
	}


	
	public function setGroup(GroupModel $group) {
		$this->historyRepositoryBuilderConfig->setGroup($group);
	}

	public function setEvent(EventModel $event) {
		$this->historyRepositoryBuilderConfig->setEvent($event);
	}

	public function setVenue(VenueModel $venue) {
		$this->historyRepositoryBuilderConfig->setVenue($venue);
	}

	public function setTag(TagModel $tag) {
		$this->historyRepositoryBuilderConfig->setTag($tag);
	}

	
	public function setVenueVirtualOnly($value) {
		$this->historyRepositoryBuilderConfig->setVenueVirtualOnly($value);
	}

	public function setNotUser(UserAccountModel $notUser) {
		$this->historyRepositoryBuilderConfig->setNotUser($notUser);
	}

	public function setAPI2Application(API2ApplicationModel $api2app) {
		$this->historyRepositoryBuilderConfig->setAPI2Application($api2app);
	}
	
	public function fetchAll() {

		$results = array();
		
	
		/////////////////////////// Events History
		
		if ($this->historyRepositoryBuilderConfig->getIncludeEventHistory()) {
			$where = array();
			$joins = array();
			$params = array();

			if ($this->historyRepositoryBuilderConfig->getEvent()) {
				$where[] = 'event_information.id=:event';
				$params['event'] = $this->historyRepositoryBuilderConfig->getEvent()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getGroup()) {
				// We use a seperate table here so if event is in 2 groups and we select events in 1 group that isn't the main group only, 
				// the normal event_in_group table still shows the main group.
				$joins[] =  " JOIN event_in_group AS event_in_group_select ON event_in_group_select.event_id = event_information.id ".
					"AND event_in_group_select.removed_at IS NULL AND event_in_group_select.group_id = :group_id ";
				$params['group_id'] = $this->historyRepositoryBuilderConfig->getGroup()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'event_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getVenue()) {
				$where[] = 'event_information.venue_id = :venue';
				$params['venue'] = $this->historyRepositoryBuilderConfig->getVenue()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' event_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'event_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'event_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getArea()) {

				$areaids = array( $this->historyRepositoryBuilderConfig->getArea()->getId() );

				$this->statAreas = $this->app['db']->prepare("SELECT area_id FROM cached_area_has_parent WHERE has_parent_area_id=:id");
				$this->statAreas->execute(array('id'=>$this->historyRepositoryBuilderConfig->getArea()->getId()));
				while($d = $this->statAreas->fetch()) {
					$areaids[] = $d['area_id'];
				}

				$joins[] = " LEFT JOIN venue_information ON  event_information.venue_id = venue_information.id ";
				$where[] = ' (event_information.area_id IN ('.  implode(",", $areaids).')  OR venue_information.area_id IN ('.  implode(",", $areaids).') ) ';

			}

			if ($this->historyRepositoryBuilderConfig->getVenueVirtualOnly()) {
				// we check both on an OR, that way we get both
				// a) events that were not virtual and became virtual, we get their full history
				// b) events that were virtual and now aren't, we get some of their history
				$where[] = " ( event_information.is_virtual = '1' OR event_history.is_virtual = '1' )";
			}
			
			$sql = "SELECT event_history.*, group_information.title AS group_title,  group_information.id AS group_id,  event_information.slug AS event_slug, user_account_information.username AS user_account_username FROM event_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = event_history.user_account_id ".
					" LEFT JOIN event_information ON event_information.id = event_history.event_id ".
					" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
					" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
					implode(" ",$joins).
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY event_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);

			while($data = $stat->fetch()) {
				$eventHistory = new EventHistoryModel();
				$eventHistory->setFromDataBaseRow($data);
				$results[] = $eventHistory;
			}
		}
		
		/////////////////////////// Group History

		if ($this->historyRepositoryBuilderConfig->getIncludeGroupHistory()) {
			$where = array();
			$params = array();
			
			if ($this->historyRepositoryBuilderConfig->getEvent() && $this->historyRepositoryBuilderConfig->getEvent()->getGroupId()) {
				$where[] = 'group_information.id=:group';
				$params['group'] = $this->historyRepositoryBuilderConfig->getEvent()->getGroupId();
			} else if ($this->historyRepositoryBuilderConfig->getGroup()) {
				$where[] = 'group_information.id =:group';
				$params['group'] = $this->historyRepositoryBuilderConfig->getGroup()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'group_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' group_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'group_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'group_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}
			
			$sql = "SELECT group_history.*, group_information.slug AS group_slug, user_account_information.username AS user_account_username FROM group_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = group_history.user_account_id ".
					" LEFT JOIN group_information ON group_information.id = group_history.group_id ".
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY group_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);
			
			while($data = $stat->fetch()) {
				$groupHistory = new GroupHistoryModel();
				$groupHistory->setFromDataBaseRow($data);
				$results[] = $groupHistory;
			}
			
		}
		
		/////////////////////////// Venue History

		if ($this->historyRepositoryBuilderConfig->getIncludeVenueHistory()) {
			$where = array();
			$params = array();
			
			if ($this->historyRepositoryBuilderConfig->getEvent() && $this->historyRepositoryBuilderConfig->getEvent()->getVenueId()) {
				$where[] = 'venue_information.id=:venue';
				$params['venue'] = $this->historyRepositoryBuilderConfig->getEvent()->getVenueId();
			} else if ($this->historyRepositoryBuilderConfig->getVenue()) {
				$where[] = 'venue_information.id=:venue';
				$params['venue'] = $this->historyRepositoryBuilderConfig->getVenue()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'venue_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' venue_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'venue_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'venue_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}
			
			$sql = "SELECT venue_history.*, venue_information.slug AS venue_slug, user_account_information.username AS user_account_username FROM venue_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = venue_history.user_account_id ".
					" LEFT JOIN venue_information ON venue_information.id = venue_history.venue_id ".
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY venue_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);
			
			while($data = $stat->fetch()) {
				$venueHistory = new VenueHistoryModel();
				$venueHistory->setFromDataBaseRow($data);
				$results[] = $venueHistory;
			}
			
		}
		
		/////////////////////////// Area History

		if ($this->historyRepositoryBuilderConfig->getIncludeAreaHistory()) {
			$where = array();
			$params = array();
			$joins = array();
			
			if ($this->historyRepositoryBuilderConfig->getArea()) {

				// Will this produce dupes? No evidence so far but there was a note in EventRepositoryBuilder that said so.

				$joins[] = " LEFT JOIN cached_area_has_parent ON cached_area_has_parent.area_id = area_information.id ";
				$where[] = ' (area_information.id =:area OR cached_area_has_parent.has_parent_area_id =:area )';
				$params['area'] = $this->historyRepositoryBuilderConfig->getArea()->getId();
			}

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'area_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' area_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'area_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'area_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}
			
			$sql = "SELECT area_history.*, area_information.slug AS area_slug, user_account_information.username AS user_account_username FROM area_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = area_history.user_account_id ".
					" LEFT JOIN area_information ON area_information.id = area_history.area_id ".
					implode(" ", $joins).
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY area_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);
			
			while($data = $stat->fetch()) {
				$areaHistory = new AreaHistoryModel();
				$areaHistory->setFromDataBaseRow($data);
				$results[] = $areaHistory;
			}
			
		}
		
		/////////////////////////// Tags History

		if ($this->historyRepositoryBuilderConfig->getIncludeTagHistory()	) {
			$where = array();
			$params = array();
			

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'tag_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' tag_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'tag_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'tag_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}
			
			$sql = "SELECT tag_history.*, tag_information.slug AS tag_slug, user_account_information.username AS user_account_username FROM tag_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = tag_history.user_account_id ".
					" LEFT JOIN tag_information ON tag_information.id = tag_history.tag_id ".
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY tag_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);
			
			while($data = $stat->fetch()) {
				$tagHistory = new TagHistoryModel();
				$tagHistory->setFromDataBaseRow($data);
				$results[] = $tagHistory;
			}
			
		}
		
		
		
		/////////////////////////// Import URL History

		if ($this->historyRepositoryBuilderConfig->getIncludeImportURLHistory()) {
			$where = array();
			$params = array();
			

			if ($this->historyRepositoryBuilderConfig->getSite()) {
				$where[] = 'import_url_information.site_id =:site';
				$params['site'] = $this->historyRepositoryBuilderConfig->getSite()->getId();
			}
			
			

			if ($this->historyRepositoryBuilderConfig->getGroup()) {
				$where[] = 'import_url_information.group_id =:group';
				$params['group'] = $this->historyRepositoryBuilderConfig->getGroup()->getId();
			}
			
			
			
			if ($this->historyRepositoryBuilderConfig->getSince()) {
				$where[] = ' import_url_history.created_at >= :since ';
				$params['since'] = $this->historyRepositoryBuilderConfig->getSince()->format("Y-m-d H:i:s");
			}
			
			if ($this->historyRepositoryBuilderConfig->getNotUser()) {
				$where[] = 'import_url_history.user_account_id != :userid ';
				$params['userid'] = $this->historyRepositoryBuilderConfig->getNotUser()->getId();
			}
			
			if ($this->historyRepositoryBuilderConfig->getApi2app()) {
				$where[] = 'import_url_history.api2_application_id  = :api2app';
				$params['api2app'] = $this->historyRepositoryBuilderConfig->getApi2app()->getId();
			}
			
			$sql = "SELECT import_url_history.*, import_url_information.slug AS import_url_slug, ".
					"user_account_information.username AS user_account_username ".
					" FROM import_url_history ".
					" LEFT JOIN user_account_information ON user_account_information.id = import_url_history.user_account_id ".
					" LEFT JOIN import_url_information ON import_url_information.id = import_url_history.import_url_id ".
					($where ? " WHERE ".implode(" AND ", $where) : "").
					" ORDER BY import_url_history.created_at DESC LIMIT ".$this->historyRepositoryBuilderConfig->getLimit();

			//var_dump($sql); var_dump($params);
			
			$stat = $this->app['db']->prepare($sql);
			$stat->execute($params);
			
			while($data = $stat->fetch()) {
				$tagHistory = new ImportHistoryModel();
				$tagHistory->setFromDataBaseRow($data);
				$results[] = $tagHistory;
			}
			
		}
		
		

		////////////////////// Others!
		foreach($this->app['extensions']->getExtensions() as $ext) {
			$results = array_merge($results, $ext->getHistoryRepositoryBuilderData($this->historyRepositoryBuilderConfig));
		}

		////////////////////// Finally sort & truncate

		$usort = function($a, $b) {
			if ($a->getCreatedAtTimeStamp() == $b->getCreatedAtTimeStamp()) {
				return 0;
			} else if ($a->getCreatedAtTimeStamp() > $b->getCreatedAtTimeStamp()) {
				return -1;
			} else {
				return 1;
			}
		};
		
		usort($results, $usort);
		
		return array_slice($results, 0, $this->historyRepositoryBuilderConfig->getLimit());
		
	}
		
		
	
}


