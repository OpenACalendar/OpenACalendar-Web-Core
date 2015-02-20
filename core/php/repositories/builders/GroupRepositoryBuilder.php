<?php

namespace repositories\builders;

use models\SiteModel;
use models\GroupModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupRepositoryBuilder  extends BaseRepositoryBuilder {
	

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

	protected $freeTextSearch;

	public function setFreeTextsearch($freeTextSearch) {
		$this->freeTextSearch = $freeTextSearch;
	}
	
	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
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

		$this->select = array('group_information.*');

		if ($this->site) {
			$this->where[] =  " group_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->event) {
			$this->joins[] =  " JOIN event_in_group AS event_in_group ON event_in_group.group_id = group_information.id ".
					"AND event_in_group.removed_at IS NULL AND event_in_group.event_id = :event_id ";
			$this->params['event_id'] = $this->event->getId();
		}
		
		if ($this->freeTextSearch) {
			$this->where[] =  '(CASE WHEN group_information.title IS NULL THEN \'\' ELSE group_information.title END )  || \' \' || '.
					'(CASE WHEN group_information.description IS NULL THEN \'\' ELSE group_information.description END )'.
					' ILIKE :free_text_search ';
			$this->params['free_text_search'] = "%".strtolower($this->freeTextSearch)."%";
		}
		
		if (!$this->include_deleted) {
			$this->where[] = " group_information.is_deleted = '0' ";
		}

		if ($this->includeMediasSlugs) {
			$this->select[] = "  (SELECT  array_to_string(array_agg(media_information.slug), ',') FROM media_information ".
				" JOIN media_in_group ON media_information.id = media_in_group.media_id ".
				" WHERE media_information.deleted_at IS NULL AND media_information.is_file_lost='0' ".
				" AND media_in_group.removal_approved_at IS NULL AND media_in_group.group_id = group_information.id ".
				" GROUP BY group_information.id ) AS media_group_slugs ";
		}
	}
	
	protected function buildStat() {
				global $DB;
		
		
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM group_information ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY group_information.title ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new GroupModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

}

