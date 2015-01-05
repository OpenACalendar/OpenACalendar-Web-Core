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
	protected $is_deleted;

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


	
}

