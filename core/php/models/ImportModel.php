<?php


namespace models;

use repositories\ImportRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $group_id;
	protected $country_id;
	protected $area_id;
	protected $title;
	protected $url;
	protected $url_canonical;
	protected $is_enabled;
	protected $is_manual_events_creation;
	protected $expired_at;
	protected $created_at;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->group_id = $data['group_id'];
		$this->country_id = $data['country_id'];
		$this->area_id = $data['area_id'];
		$this->title = $data['title'];
		$this->url = $data['url'];
		$this->url_canonical = $data['url_canonical'];
		$this->is_enabled = $data['is_enabled'];
		$this->is_manual_events_creation = $data['is_manual_events_creation'];
		$utc = new \DateTimeZone("UTC");
		$this->expired_at = $data['expired_at'] ? new \DateTime($data['expired_at'], $utc) : null;
		$this->created_at = new \DateTime($data['created_at'], $utc);	
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

	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	public function getGroupId() {
		return $this->group_id;
	}

	public function setGroupId($group_id) {
		$this->group_id = $group_id;
		return $this;
	}

	public function getTitle() {
		return $this->title ? $this->title : $this->url;
	}

	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function getUrl() {
		return $this->url;
	}
	
	public function getUrlCanonical() {
		return $this->url_canonical;
	}

	public function setUrl($url) {
		$this->url = $url;
		$p = new \ParseURL($url);
		$this->url_canonical = $p->getCanonical();
		return $this;
	}

	public function getIsEnabled() {
		return $this->is_enabled;
	}

	public function setIsEnabled($enabled) {
		$this->is_enabled = $enabled;
		return $this;
	}

	public function getExpiredAt() {
		return $this->expired_at;
	}
	
	public function getIsExpired() {
		return (boolean)$this->expired_at;
	}

	public function setExpiredAt($expired_at) {
		$this->expired_at = $expired_at;
		return $this;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
		return $this;
	}
	
	public function isShouldExpireNow() {
		global $CONFIG, $app;
		$r = new ImportRepository($app);
		$lastEdit = $r->getLastEditDateForImportURL($this);
		return $lastEdit->getTimeStamp() < (\TimeSource::time() - $CONFIG->importExpireSecondsAfterLastEdit);
	}

	public function getCountryId() {
		return $this->country_id;
	}

	public function setCountryId($country_id) {
		$this->country_id = $country_id;
	}
	
	public function getAreaId() {
		return $this->area_id;
	}

	public function setAreaId($area_id) {
		$this->area_id = $area_id;
	}

	/**
	 * @param mixed $is_manual_events_creation
	 */
	public function setIsManualEventsCreation($is_manual_events_creation)
	{
		$this->is_manual_events_creation = $is_manual_events_creation;
	}

	/**
	 * @return mixed
	 */
	public function getIsManualEventsCreation()
	{
		return $this->is_manual_events_creation;
	}

    public function guessATitleIfMissing() {
        if (!$this->title && $this->url) {
            $this->title = $this->url;
        }
    }


}

