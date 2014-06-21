<?php

namespace repositories\builders;

use models\SiteModel;
use models\TagModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}
	
	
	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}

	protected function build() {

		if ($this->site) {
			$this->where[] =  " tag_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
	
		
		if (!$this->include_deleted) {
			$this->where[] = " tag_information.is_deleted = '0' ";
		}
	}
	
	protected function buildStat() {
				global $DB;
		
		
		
		$sql = "SELECT tag_information.* FROM tag_information ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY tag_information.title ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($data);
			$results[] = $tag;
		}
		return $results;
		
	}

}

