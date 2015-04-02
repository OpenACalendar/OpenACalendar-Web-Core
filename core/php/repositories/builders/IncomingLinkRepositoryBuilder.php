<?php

namespace repositories\builders;

use incominglinks\PingBackIncomingLink;
use incominglinks\WebMentionIncomingLink;
use models\SiteModel;
use models\TagModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IncomingLinkRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	protected function build() {

		if ($this->site) {
			$this->where[] =  " incoming_link.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}

	}
	
	protected function buildStat() {
				global $DB;
		
		
		
		$sql = "SELECT incoming_link.* FROM incoming_link ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY incoming_link.created_at ASC  ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			// TODO should be got from extensions properly!
			if ($data['type'] == 'PINGBACK') {
				$il = new PingBackIncomingLink();
			} else {
				$il = new WebMentionIncomingLink();
			}
			$il->setFromDataBaseRow($data);
			$results[] = $il;
		}
		return $results;
		
	}

}

