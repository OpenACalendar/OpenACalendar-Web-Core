<?php


namespace org\openacalendar\curatedlists\models;

use org\openacalendar\curatedlists\repositories\CuratedListRepository;

use models\UserAccountModel;
use Slugify;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $title;
	protected $description;
	protected $created_at;
	protected $is_deleted;
    protected $cached_future_events;

	/** secondary attributes **/
	protected $is_event_in_list;
	protected $is_group_in_list;
	protected $event_in_list_via_group_id;

	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->description = $data['description'];
        $this->cached_future_events = $data['cached_future_events'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);	
		$this->is_event_in_list = isset($data['is_event_in_list']) ? (boolean)$data['is_event_in_list'] : false;
		$this->is_group_in_list = isset($data['is_group_in_list']) ? (boolean)$data['is_group_in_list'] : false;
		$this->event_in_list_via_group_id = isset($data['event_in_list_via_group_id']) ? $data['event_in_list_via_group_id'] : null;
		$this->is_deleted = $data['is_deleted'];
	}
	
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
		return $this;
	}

	public function getSlug() {
		return $this->slug;
	}

    public function getSlugForUrl() {
        global $app;
        $slugify = new Slugify($app);
        $extraSlug = $slugify->process($this->title);
        return $this->slug.($extraSlug?"-".$extraSlug:'');
    }
	
	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
		
	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
		return $this;
	}
	
	public function canUserEdit(UserAccountModel $user = null) {
		if (!$user) {
			return false;
		}
		
		$curatedListRepo = new CuratedListRepository();
		if ($curatedListRepo->canUserEditCuratedList($user, $this)) {
			return true;
		}
		
		return false;
	}

	/**
	 * TODO should be called getIsEventInList (Capital L)
	 */
	public function getIsEventInlist() {
		return $this->is_event_in_list;
	}

	public function getIsGroupInList() {
		return $this->is_group_in_list;
	}



	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}

	/**
	 * @return int
	 */
	public function getEventInListViaGroupId()
	{
		return $this->event_in_list_via_group_id;
	}

	/**
	 * @return boolean
	 */
	public function isEventInListViaGroup()
	{
		return $this->event_in_list_via_group_id != null;
	}


    public function getCachedFutureEvents()
    {
        return $this->cached_future_events;
    }

    /**
     * @param mixed $cached_future_events
     */
    public function setCachedFutureEvents($cached_future_events)
    {
        $this->cached_future_events = $cached_future_events;
    }

	
}

