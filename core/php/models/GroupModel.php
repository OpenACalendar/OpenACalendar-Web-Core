<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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
	protected $is_duplicate_of_id;
	protected $media_group_slugs;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->url = $data['url'];
		$this->description = $data['description'];
		$this->twitter_username = $data['twitter_username'];
		$this->is_deleted = $data['is_deleted'];
		$this->is_duplicate_of_id = $data['is_duplicate_of_id'];
		$this->media_group_slugs = isset($data['media_group_slugs']) ? $data['media_group_slugs'] : null;
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
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 
                            'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u', 
                            'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T'
                            );
		$extraSlug = strtr( trim($this->title), $unwanted_array );
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($extraSlug)));
		// Do it twice to get ---'s turned to -'s to.
		$extraSlug = str_replace("--", "-", $extraSlug);
		$extraSlug = str_replace("--", "-", $extraSlug);
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


	public function setTitleIfDifferent($title) {
		if ($this->title != $title) {
			$this->title = $title;
			return true;
		}
		return false;
	}


	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function setDescriptionIfDifferent($description) {
		if ($this->description != $description) {
			$this->description = $description;
			return true;
		}
		return false;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setUrlIfDifferent($url) {
		if ($this->url != $url) {
			$this->url = $url;
			return true;
		}
		return false;
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

	public function setIsDuplicateOfId($is_duplicate_of_id)
	{
		$this->is_duplicate_of_id = $is_duplicate_of_id;
	}

	public function getIsDuplicateOfId()
	{
		return $this->is_duplicate_of_id;
	}

	/**
	 * @return boolean
	 */
	public function hasMediaSlugs()
	{
		return (bool)$this->media_group_slugs;
	}


	/**
	 * @return mixed
	 */
	public function getMediaSlugsAsList($maxCount = 1000)
	{
		$out = array();
		if ($this->media_group_slugs) {
			foreach(explode(",",$this->media_group_slugs) as $slug) {
				if ($slug && !in_array($slug, $out)) {
					$out[] = $slug;
				}
				if (count($out) == $maxCount) {
					return $out;
				}
			}
		}
		return $out;
	}


	
}


