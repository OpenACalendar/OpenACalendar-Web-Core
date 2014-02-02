<?php


namespace models;

use repositories\CuratedListRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $title;
	protected $description;
	protected $created_at;

	/** secondary attributes **/
	protected $is_event_in_list;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);	
		$this->is_event_in_list = isset($data['is_event_in_list']) ? $data['is_event_in_list'] : false;
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

	public function getIsEventInlist() {
		return $this->is_event_in_list;
	}


	
}

