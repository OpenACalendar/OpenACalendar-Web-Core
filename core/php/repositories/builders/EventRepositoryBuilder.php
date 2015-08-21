<?php

namespace repositories\builders;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\TagModel;
use models\VenueModel;
use models\UserAccountModel;
use models\CountryModel;
use org\openacalendar\curatedlists\models\CuratedListModel;
use models\ImportURLModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRepositoryBuilder extends BaseRepositoryBuilder {
	
	protected $orderBy = " start_at ";
	protected $orderDirection = " ASC ";

	public  function setOrderByCreatedAt($newestFirst = true) {
		$this->orderBy = " created_at ";
		$this->orderDirection = ($newestFirst ? " DESC " : " ASC ");
	}

	public  function setOrderByStartAt($newestFirst = true) {
		$this->orderBy = " start_at ";
		$this->orderDirection = ($newestFirst ? " DESC " : " ASC ");
	}

	protected $freeTextSearch;

	public function setFreeTextsearch($freeTextSearch) {
		$this->freeTextSearch = $freeTextSearch;
	}
	


	/** @var UserAccountModel **/
	protected $userAccount;
	protected $userAccountIncludeAll = false;
	protected $userAccountIncludePrivate = false;
	protected $userAccountIncludeAttending = true;
	protected $userAccountIncludeWatching = true;
	
	public function setUserAccount(UserAccountModel $user, $includeAll = false, $includePrivate = false, $userAccountIncludeAttending=true, $userAccountIncludeWatching=true) {
		if ($userAccountIncludeAttending || $userAccountIncludeWatching) {
			$this->userAccount = $user;
			$this->userAccountIncludeAll = $includeAll;
			$this->userAccountIncludePrivate = $includePrivate;
			$this->userAccountIncludeAttending = $userAccountIncludeAttending;
			$this->userAccountIncludeWatching = $userAccountIncludeWatching;
		}
	}


	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	/** @var GroupModel **/
	protected $group;
	
	public function setGroup(GroupModel $group) {
		$this->group = $group;
	}

	/** @var CountryModel **/
	protected $country;
	
	public function setCountry(CountryModel $country) {
		$this->country = $country;
	}

	/** @var AreaModel **/
	protected $area;
	
	public function setArea(AreaModel $area) {
		$this->area = $area;
	}

	/** @var VenueModel **/
	protected $venue;
	
	public function setVenue(VenueModel $venue) {
		$this->venue = $venue;
	}
	
	protected $venueVirtualOnly = false;
	
	public function setVenueVirtualOnly($value) {
		$this->venueVirtualOnly = $value;
	}

	

	/** @var ImportURLModel **/
	protected $importURL;
	
	public function setImportURL(ImportURLModel $importURL) {
		$this->importURL = $importURL;
	}



	/** @var ImportedEventModel **/
	protected $importedEvent;

	/**
	 * @param \repositories\builders\ImportedEventModel $importedEvent
	 */
	public function setImportedEvent($importedEvent)
	{
		$this->importedEvent = $importedEvent;
	}



	
	/** @var CuratedListModel **/
	protected $curatedList;

	protected $curatedListInformation = false;
	
	public function setCuratedList(CuratedListModel $curatedList, $curatedListInformation = false) {
		$this->curatedList = $curatedList;
		$this->curatedListInformation = $curatedListInformation;
	}
	
	
	/** @var TagModel **/
	protected $tag;
	
	public function setTag(TagModel $tag) {
		$this->tag = $tag;
	}
	
	
	/** @var \DateTime **/
	protected $after;
	
	public function setAfter(\DateTime $a) {
		$this->after = $a;
		return $this;
	}
	
	public function setAfterNow() {
		$this->after = \TimeSource::getDateTime();
		return $this;
	}
	
	/** @var \DateTime **/
	protected $before;
	
	public function setBefore(\DateTime $b) {
		$this->before = $b;
		return $this;
	}

	/** @var \DateTime **/
	protected $startAfter;
	
	public function setStartAfter(\DateTime $a) {
		$this->startAfter = $a;
		return $this;
	}

	/** @var \DateTime **/
	protected $endBefore;

	public function setBeforeNow() {
		$this->endBefore = \TimeSource::getDateTime();
		return $this;
	}
	
	/** @var \DateTime **/
	protected $start;
	
	public function setStart(\DateTime $b) {
		$this->start = $b;
		return $this;
	}
	
	/** @var \DateTime **/
	protected $end;
	
	public function setEnd(\DateTime $b) {
		$this->end = $b;
		return $this;
	}

	
	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}

	protected $include_cancelled = true;

	public function setIncludeCancelled($value) {
		$this->include_cancelled = $value;
	}
	
	protected $include_venue_information= false;

	public function setIncludeVenueInformation($value) {
		$this->include_venue_information = $value;
	}
	
	protected $include_area_information= false;

	public function setIncludeAreaInformation($value) {
		$this->include_area_information = $value;		
	}
	
	protected $must_have_lat_lng = false;


	/** @var UserAccountModel  */
	protected $editedByUser = null;

	/**
	 * @param UserAccountModel $editedByUser
	 */
	public function setEditedByUser(UserAccountModel $editedByUser)
	{
		$this->editedByUser = $editedByUser;
	}



	protected $include_country_information= false;

	/**
	 * @param boolean $include_country_information
	 */
	public function setIncludeCountryInformation($include_country_information)
	{
		$this->include_country_information = $include_country_information;
	}



	public function setMustHaveLatLng($must_have_lat_lng) {
		$this->must_have_lat_lng = $must_have_lat_lng;
		if ($must_have_lat_lng) {
			$this->include_venue_information = true;
		}
	}

	
	protected $include_imported = true;

	public function setIncludeImported($value) {
		$this->include_imported = $value;
	}
	
	protected $event_recur_set_id;
	
	public function setInSameRecurEventSet(EventModel $event) {
		$this->event_recur_set_id = $event->getEventRecurSetId();
	}
	
	protected $includeEventsFromClosedSites = false;
	
	public function setIncludeEventsFromClosedSites($includeEventsFromClosedSites) {
		$this->includeEventsFromClosedSites = $includeEventsFromClosedSites;
	}


	protected $includeMediasSlugs = false;

	/**
	 * @param boolean $includeMediasSlugs
	 */
	public function setIncludeMediasSlugs($includeMediasSlugs)
	{
		$this->includeMediasSlugs = $includeMediasSlugs;
	}


	
	protected function build() {
		global $DB;

		$this->select[] = 'event_information.*';
		$this->select[] = " group_information.title AS group_title ";
		$this->select[] = " group_information.id AS group_id ";
		$this->joins[] = " LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ";
		$this->joins[] = " LEFT JOIN group_information ON group_information.id = event_in_group.group_id ";
		
		$joinsVenueInfoSQL = " LEFT JOIN venue_information ON venue_information.id = event_information.venue_id ";
		
		if ($this->site) {
			$this->where[] =  " event_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->group) {
			// We use a seperate table here so if event is in 2 groups and we select events in 1 group that isn't the main group only, 
			// the normal event_in_group table still shows the main group.
			$this->joins[] =  " JOIN event_in_group AS event_in_group_select ON event_in_group_select.event_id = event_information.id ".
					"AND event_in_group_select.removed_at IS NULL AND event_in_group_select.group_id = :group_id ";
			$this->params['group_id'] = $this->group->getId();
		}
		
		if ($this->country) {
			$this->where[] =  " event_information.country_id = :country_id ";
			$this->params['country_id'] = $this->country->getId();
		}
		
		if ($this->area) {
			
			// We were doing
			// $this->joins[] = " LEFT JOIN cached_area_has_parent ON cached_area_has_parent.area_id = venue_information.area_id";
			// $this->where[] =  " (venue_information.area_id = :area_id OR  cached_area_has_parent.has_parent_area_id = :area_id )";
			// but then we got duplicates
			
			$areaids = array( $this->area->getId() );
			
			$this->statAreas = $DB->prepare("SELECT area_id FROM cached_area_has_parent WHERE has_parent_area_id=:id");
			$this->statAreas->execute(array('id'=>$this->area->getId()));
			while($d = $this->statAreas->fetch()) {
				$areaids[] = $d['area_id'];
			}
			
			$this->joins[] = $joinsVenueInfoSQL;
			$this->where[] =  " (venue_information.area_id IN (".  implode(",", $areaids).") ".
					"OR event_information.area_id IN (".  implode(",", $areaids).")) ";
		}
		
		if ($this->venue) {
			$this->where[] =  " event_information.venue_id = :venue_id ";
			$this->params['venue_id'] = $this->venue->getId();
		}

		if ($this->importURL) {
			$this->joins[] = " LEFT JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id ";
			$this->joins[] = " LEFT JOIN imported_event ON imported_event.id = imported_event_is_event.imported_event_id ";
			$this->where[] =  " (imported_event.import_url_id = :import_url_id OR event_information.import_url_id = :import_url_id )";
			$this->params['import_url_id'] = $this->importURL->getId();
		}

		if ($this->importedEvent) {
			$this->joins[] = " JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id AND imported_event_is_event.imported_event_id = :imported_event_id ";
			$this->params['imported_event_id'] = $this->importedEvent->getId();
		}

		if (!$this->site && !$this->group) {
			$this->joins[] = " JOIN site_information ON event_information.site_id = site_information.id ";
			$this->select[] = " site_information.slug AS site_slug ";
			if (!$this->includeEventsFromClosedSites) {
				$this->where[] = " site_information.is_closed_by_sys_admin = '0' ";
			}
		}
		
		if ($this->curatedList) {
			$this->joins[] = " LEFT JOIN event_in_curated_list ON event_in_curated_list.event_id = event_information.id ".
				" AND event_in_curated_list.removed_at IS NULL AND event_in_curated_list.curated_list_id = :curated_list";
			$this->joins[] =  " LEFT JOIN  ( SELECT event_in_group.event_id, MAX(event_in_group.group_id) AS group_id".
				" FROM event_in_group ".
				" JOIN group_in_curated_list ON group_in_curated_list.group_id = event_in_group.group_id ".
				" WHERE group_in_curated_list.curated_list_id = :curated_list AND group_in_curated_list.removed_at IS NULL AND event_in_group.removed_at IS NULL ".
				" GROUP BY event_in_group.event_id ".
				") AS event_in_group_in_curated_list ON event_in_group_in_curated_list.event_id = event_information.id ";

			$this->where[] = " ( event_in_curated_list.curated_list_id IS NOT NULL OR event_in_group_in_curated_list.event_id IS NOT NULL )";
			$this->params['curated_list'] = $this->curatedList->getId();

			if ($this->curatedListInformation) {
				$this->joins[] = " LEFT JOIN group_information AS group_information_cl ON group_information_cl.id = event_in_group_in_curated_list.group_id ";
				$this->select[] = " (CASE WHEN event_in_curated_list.event_id IS NULL THEN 0 ELSE 1 END) AS is_event_in_curated_list ";
				$this->select[] = " group_information_cl.id AS in_curated_list_group_id ";
				$this->select[] = " group_information_cl.slug AS in_curated_list_group_slug ";
				$this->select[] = " group_information_cl.title AS in_curated_list_group_title ";
			}
		}
		
		if ($this->end) {
			$this->where[] = ' event_information.end_at = :end';
			$this->params['end'] = $this->end->format("Y-m-d H:i:s");			
		} else if ($this->after) {
			$this->where[] = ' event_information.end_at > :after';
			$this->params['after'] = $this->after->format("Y-m-d H:i:s");
		} else if ($this->endBefore) {
			$this->where[] = ' event_information.end_at < :before';
			$this->params['before'] = $this->endBefore->format("Y-m-d H:i:s");
		}
		
		if ($this->start) {
			$this->where[] = ' event_information.start_at = :start';
			$this->params['start'] = $this->start->format("Y-m-d H:i:s");
		} else if ($this->before) {
			$this->where[] = ' event_information.start_at < :before';
			$this->params['before'] = $this->before->format("Y-m-d H:i:s");
		} else if ($this->startAfter) {
			$this->where[] = ' event_information.start_at > :startAfter';
			$this->params['startAfter'] = $this->startAfter->format("Y-m-d H:i:s");
		}
		
		if (!$this->include_deleted) {
			$this->where[] = " event_information.is_deleted = '0' ";
		}

		if (!$this->include_cancelled) {
			$this->where[] = " event_information.is_cancelled = '0' ";
		}
		
		if (!$this->include_imported) {
			$this->where[] = " event_information.import_url_id is null ";
		}
		
		if ($this->userAccount) {
			// user at event. we want info on this always for the extra selects, so outside if statement
			$this->joins[] = "  LEFT JOIN user_at_event_information ON user_at_event_information.event_id = event_information.id ".
					"AND user_at_event_information.user_account_id = :user_account_id ";
			$this->select[] = " user_at_event_information.is_plan_attending AS user_is_plan_attending ";
			$this->select[] = " user_at_event_information.is_plan_maybe_attending AS user_is_plan_maybe_attending ";
			if (!$this->userAccountIncludeAll) {
				$w = array();
				if ($this->userAccountIncludeWatching) {
					$w[] = "  event_information.id IN (SELECT event_information.id FROM event_information ".
						// site
						" LEFT JOIN user_watches_site_information ON  user_watches_site_information.site_id = event_information.site_id ".
						"AND user_watches_site_information.user_account_id = :user_account_id AND user_watches_site_information.is_watching='1' ".
						// group
						"  LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL ".
						" LEFT JOIN user_watches_group_information ON user_watches_group_information.group_id = event_in_group.group_id ".
							"AND user_watches_group_information.user_account_id = :user_account_id AND user_watches_group_information.is_watching='1' ".
						// area
						" LEFT JOIN venue_information ON venue_information.id = event_information.venue_id ".
						" LEFT JOIN cached_area_has_parent ON ( venue_information.area_id = cached_area_has_parent.area_id OR event_information.area_id = cached_area_has_parent.area_id) ".
						" LEFT JOIN user_watches_area_information ON ( ".
							"user_watches_area_information.area_id = event_information.area_id OR user_watches_area_information.area_id = venue_information.area_id ".
							" OR user_watches_area_information.area_id = cached_area_has_parent.has_parent_area_id ".
						") ".
						"AND user_watches_area_information.user_account_id = :user_account_id AND user_watches_area_information.is_watching='1' ".

						// where
						" WHERE user_watches_site_information.is_watching='1' OR user_watches_group_information.is_watching='1'  OR user_watches_area_information.is_watching='1'".
						" )  ";
				}
				if ($this->userAccountIncludeAttending) {
					if ($this->userAccountIncludePrivate) {
						$w[] = " user_at_event_information.is_plan_attending = '1' ";
						$w[] = " user_at_event_information.is_plan_maybe_attending = '1' ";
					} else {
						$w[] = " (user_at_event_information.is_plan_attending = '1' AND user_at_event_information.is_plan_public  = '1' )";
						$w[] = " (user_at_event_information.is_plan_maybe_attending = '1' AND user_at_event_information.is_plan_public  = '1' )";
					}
				}
				$this->where[] = "  (  ".  implode(" OR ", $w).") ";
			}
			$this->params['user_account_id'] = $this->userAccount->getId();			
		}
		
		if ($this->include_venue_information || $this->include_area_information || $this->must_have_lat_lng) {
			if (!in_array($joinsVenueInfoSQL, $this->joins)) {
				$this->joins[] = $joinsVenueInfoSQL;
			}
			if ($this->include_venue_information) {
				$this->select[] = "  venue_information.lng AS venue_lng";
				$this->select[] = "  venue_information.lat AS venue_lat";
				$this->select[] = "  venue_information.title AS venue_title";
				$this->select[] = "  venue_information.slug AS venue_slug";
				$this->select[] = "  venue_information.description AS venue_description";
				$this->select[] = "  venue_information.address AS venue_address";
				$this->select[] = "  venue_information.address_code AS venue_address_code";
			}
			if ($this->include_area_information) {
				$this->joins[] = " LEFT JOIN area_information ON area_information.id = event_information.area_id OR area_information.id = venue_information.area_id";
				$this->select[] = "  area_information.title AS area_title";
				$this->select[] = "  area_information.slug AS area_slug";
				$this->select[] = "  area_information.id AS area_information_id"; // we already have a area_id so called something different
			}
			if ($this->must_have_lat_lng) {
				$this->where[] = " venue_information.lat IS NOT NULL ";
				$this->where[] = " venue_information.lng IS NOT NULL ";
			}
		}

		if ($this->include_country_information) {
				$this->joins[] = " LEFT JOIN country ON country.id = event_information.country_id  ";
			$this->select[] = "  country.two_char_code AS country_two_char_code";
			$this->select[] = "  country.title AS country_title";
		}

		if ($this->venueVirtualOnly) {
			$this->where[] = " event_information.is_virtual = '1' ";
		}
		
		if ($this->event_recur_set_id) {
			$this->where[] =  " event_information.event_recur_set_id = :event_recur_set_id ";
			$this->params['event_recur_set_id'] = $this->event_recur_set_id;
		}	
		
		if ($this->tag) {
			$this->joins[] = "  JOIN event_has_tag ON event_has_tag.event_id = event_information.id AND  event_has_tag.tag_id = :tag_id AND event_has_tag.removed_at IS NULL";
			$this->params['tag_id'] = $this->tag->getId();	
		}
		
		if ($this->freeTextSearch) {
			$this->where[] =  '(CASE WHEN event_information.summary IS NULL THEN \'\' ELSE event_information.summary END)   || '.
					'\' \' || '.
					'(CASE WHEN event_information.description IS NULL THEN \'\' ELSE event_information.description END) || '.
					'\' \' || '.
					'(CASE WHEN group_information.title IS NULL THEN \'\' ELSE group_information.title END)'.
					' ILIKE :free_text_search ';
			$this->params['free_text_search'] = "%".strtolower($this->freeTextSearch)."%";
		}

		if ($this->includeMediasSlugs) {
			$this->select[] = "  (SELECT  array_to_string(array_agg(media_information.slug), ',') FROM media_information ".
				" JOIN media_in_event ON media_information.id = media_in_event.media_id ".
				" WHERE media_information.deleted_at IS NULL AND media_information.is_file_lost='0' ".
				" AND media_in_event.removal_approved_at IS NULL AND media_in_event.event_id = event_information.id ".
				" GROUP BY event_information.id ) AS media_event_slugs ";
			$this->select[] = "  (SELECT  array_to_string(array_agg(media_information.slug), ',') FROM media_information ".
				" JOIN media_in_group ON media_information.id = media_in_group.media_id ".
				" JOIN event_in_group ON event_in_group.group_id = media_in_group.group_id ".
				" WHERE media_information.deleted_at IS NULL AND media_information.is_file_lost='0' ".
				" AND media_in_group.removal_approved_at IS NULL ".
				" AND event_in_group.removal_approved_at IS NULL AND event_in_group.event_id = event_information.id ".
				" GROUP BY event_information.id ) AS media_group_slugs ";
			$this->select[] = "  (SELECT  array_to_string(array_agg(media_information.slug), ',') FROM media_information ".
				" JOIN media_in_venue ON media_information.id = media_in_venue.media_id ".
				" WHERE media_information.deleted_at IS NULL AND media_information.is_file_lost='0' ".
				" AND media_in_venue.removal_approved_at IS NULL AND media_in_venue.venue_id = event_information.venue_id ".
				" GROUP BY event_information.venue_id ) AS media_venue_slugs ";
		}

		if ($this->editedByUser) {
			$this->where[] = " event_information.id IN (SELECT event_id FROM event_history WHERE user_account_id = :editedByUser) ";
			$this->params['editedByUser'] = $this->editedByUser->getId();
		}
	}
	
	protected function buildStat() {
		global $DB;


				
		$sql = "SELECT ".  implode(",", $this->select)." FROM event_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY  ".$this->orderBy." ".$this->orderDirection .( $this->limit > 0 ? " LIMIT ". $this->limit : "");

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new EventModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

	public function fetchCount() {

		$this->buildStart();

		$this->select = array('event_information.id');

		$this->build();
		$this->buildStat();

		return $this->stat->rowCount();

	}

}

