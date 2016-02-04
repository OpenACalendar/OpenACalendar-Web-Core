<?php

namespace repositories\builders;

use models\EventModel;
use models\SiteModel;
use models\SysadminCommentModel;
use models\UserAccountModel;
use models\VenueModel;
use models\CountryModel;
use models\AreaModel;
use models\GroupModel;
use models\MediaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SysadminCommentRepositoryBuilder extends BaseRepositoryBuilder {
	

	/** @var  UserAccountModel */
	protected $user;

	public function setUser(UserAccountModel $user)
	{
		$this->user = $user;
	}


	/** @var  SiteModel */
	protected $site;

	public function setSite(SiteModel $site)
	{
		$this->site = $site;
	}


	/** @var  EventModel */
	protected $event;

	public function setEvent(EventModel $event)
	{
		$this->event = $event;
	}


	/** @var  GroupModel */
	protected $group;

	public function setGroup(GroupModel $group)
	{
		$this->group = $group;
	}


	/** @var  AreaModel */
	protected $area;

	public function setArea(AreaModel $area)
	{
		$this->area = $area;
	}


	/** @var  VenueModel */
	protected $venue;

	public function setVenue(VenueModel $venue)
	{
		$this->venue = $venue;
	}


	/** @var  MediaModel */
	protected $media;

	public function setMedia(MediaModel $media)
	{
		$this->media = $media;
	}




	protected function build() {

		$this->select[] = 'sysadmin_comment_information.*';
		$this->joins[] = ' LEFT JOIN user_account_information ON user_account_information.id = sysadmin_comment_information.user_account_id ';
		$this->select[] = ' user_account_information.username AS user_account_username';

		if ($this->user) {
			$this->joins[] = "  JOIN sysadmin_comment_about_user ON sysadmin_comment_about_user.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_user.user_account_id = :user_account_id ";
			$this->params['user_account_id'] = $this->user->getId();
		}

		if ($this->site) {
			$this->joins[] = "  JOIN sysadmin_comment_about_site ON sysadmin_comment_about_site.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_site.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}

		if ($this->event) {
			$this->joins[] = "  JOIN sysadmin_comment_about_event ON sysadmin_comment_about_event.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_event.event_id = :event_id ";
			$this->params['event_id'] = $this->event->getId();
		}

		if ($this->group) {
			$this->joins[] = "  JOIN sysadmin_comment_about_group ON sysadmin_comment_about_group.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_group.group_id = :group_id ";
			$this->params['group_id'] = $this->group->getId();
		}

		if ($this->area) {
			$this->joins[] = "  JOIN sysadmin_comment_about_area ON sysadmin_comment_about_area.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_area.area_id = :area_id ";
			$this->params['area_id'] = $this->area->getId();
		}

		if ($this->venue) {
			$this->joins[] = "  JOIN sysadmin_comment_about_venue ON sysadmin_comment_about_venue.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_venue.venue_id = :venue_id ";
			$this->params['venue_id'] = $this->venue->getId();
		}

		if ($this->media) {
			$this->joins[] = "  JOIN sysadmin_comment_about_media ON sysadmin_comment_about_media.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_media.media_id = :media_id ";
			$this->params['media_id'] = $this->media->getId();
		}

	}

	
	protected function buildStat() {

		
		
		$sql = "SELECT " . implode(", ",$this->select) . " FROM sysadmin_comment_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY sysadmin_comment_information.created_at ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $this->app['db']->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
	
		$results = array();
		while($data = $this->stat->fetch()) {
			$sac = new SysadminCommentModel();
			$sac->setFromDataBaseRow($data);
			$results[] = $sac;
		}
		return $results;
		
	}

}

