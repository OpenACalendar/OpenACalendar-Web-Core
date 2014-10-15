<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserGroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserGroupRepositoryBuilder  extends BaseRepositoryBuilder {


	/** @var SiteModel **/
	protected $site;

	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	protected $index_only = false;

	/**
	 * @param boolean $index_only
	 */
	public function setIndexOnly($index_only)
	{
		$this->index_only = $index_only;
	}



	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}


	protected function build() {
		global $DB;

		if ($this->site) {
			$this->joins[] = " JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id ".
				" AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL";
			$this->params['site_id'] = $this->site->getId();
		}

		if (!$this->include_deleted) {
			$this->where[] = " user_group_information.is_deleted = '0' ";
		}

		if ($this->index_only) {
			$this->where[] = " user_group_information.is_in_index = '1' ";
		}

	}

	protected function buildStat() {
		global $DB;

		$sql = "SELECT user_group_information.* FROM user_group_information ".
			implode(" ", $this->joins).
			($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
			" ORDER BY user_group_information.title ASC ";

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}



	public function fetchAll() {

		$this->buildStart();
		$this->build();
		$this->buildStat();

		$results = array();
		while($data = $this->stat->fetch()) {
			$userGroup = new UserGroupModel();
			$userGroup->setFromDataBaseRow($data);
			$results[] = $userGroup;
		}
		return $results;

	}

}

