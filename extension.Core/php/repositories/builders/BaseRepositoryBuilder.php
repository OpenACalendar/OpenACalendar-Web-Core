<?php

namespace repositories\builders;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseRepositoryBuilder {
	

	protected $limit = 100;
	
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	protected $where = array();
	protected $select = array();
	protected $joins = array();
	protected $params = array();

	protected function buildStart() {
		$this->where = array();
		$this->select = array();
		$this->joins = array();
		$this->params = array();
	}
	
	protected abstract function build();
	
	protected $stat;
	
	protected abstract function buildStat();
	
	public abstract function fetchAll();
	
}

