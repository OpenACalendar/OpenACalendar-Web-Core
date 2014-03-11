<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $title;
	protected $url;
	protected $description;
	protected $twitter_username;
	protected $is_deleted;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->url = $data['url'];
		$this->description = $data['description'];
		$this->twitter_username = $data['twitter_username'];
		$this->is_deleted = $data['is_deleted'];
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function getSlugForUrl() {
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($this->title)));
		return $this->slug.($extraSlug?"-".$extraSlug:'');
	}

	public function setSlug($slug) {
		$this->slug = $slug;
	}
	
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function getTwitterUsername() {
		return $this->twitter_username;
	}

	public function setTwitterUsername($twitterUsername) {
		if (substr($twitterUsername,0,1) == '@') {
			$this->twitter_username = substr($twitterUsername, 1);
		} elseif (substr($twitterUsername,0,8) == "twitter@") {
			$this->twitter_username = substr ($twitterUsername, 8);
		} elseif (substr($twitterUsername,0,2) == " @") {
			$this->twitter_username = substr ($twitterUsername, 2);
		} elseif (substr($twitterUsername,0,1) == "#") {
			$this->twitter_username = null;
		} elseif (substr($twitterUsername,0,8) == "Follow @") {
			$this->twitter_username = substr ($twitterUsername, 8);
		} elseif (substr($twitterUsername,0,22) == "http://twitter.com/#!/") {
			$this->twitter_username = substr ($twitterUsername, 22);
		} elseif (substr($twitterUsername,0,23) == "https://twitter.com/#!/") {
			$this->twitter_username = substr ($twitterUsername, 23);
		} elseif (substr($twitterUsername,0,19) == "http://twitter.com/") {
			$this->twitter_username = substr ($twitterUsername, 19);
		} elseif (substr($twitterUsername,0,20) == "https://twitter.com/") {
			$this->twitter_username = substr ($twitterUsername, 20);
		} elseif (substr($twitterUsername,0,23) == "http://www.twitter.com/") {
			$this->twitter_username = substr ($twitterUsername, 23);
		} elseif (substr($twitterUsername,0,12) == "twitter.com/") {
			$this->twitter_username = substr ($twitterUsername, 12);
		} elseif (substr($twitterUsername,0,16) == "www.twitter.com/") {
			$this->twitter_username = substr ($twitterUsername, 16);
		} else {
			$this->twitter_username = $twitterUsername;
		}
		return $this;
	}


	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}


	
}


