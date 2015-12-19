<?php



namespace models;


use Symfony\Component\HttpFoundation\Response;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventDraftModel {

	protected $id;
	protected $site_id;
	protected $slug;
	protected $event_id;
	protected $was_existing_event_id;
	protected $details = array();
	protected $user_account_id;
	protected $not_duplicate_events = array();


	protected $created_at;
	protected $updated_at;

	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->event_id = $data['event_id'];
		$this->was_existing_event_id = $data['was_existing_event_id'];
		$this->details = (array)json_decode($data['details']);
		$this->user_account_id = $data['user_account_id'];
		$this->not_duplicate_events = explode(",", $data['not_duplicate_events']);
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->updated_at = new \DateTime($data['updated_at'], $utc);
	}


	/**
	 * @return mixed
	 */
	public function getDetails()
	{
		return $this->details;
	}

	/**
	 * @return mixed
	 */
	public function getDetailsForDataBase()
	{
		$out = array();
		foreach($this->details as $k=>$v) {
			if ($v instanceof \DateTime) {
				$out[$k] = $v->format('c');
			} else {
				$out[$k] = $v;
			}
		}
		return $out;
	}

	/**
	 * @param mixed $details
	 */
	public function setDetails($details)
	{
		$this->details = $details;
	}

	public function setDetailsValue($key, $value) {
		$this->details[$key] = $value;
	}

	public function unsetDetailsValue($key) {
		unset($this->details[$key]);
	}

	public function getDetailsValue($key) {
		return isset($this->details[$key]) ? $this->details[$key] : null;
	}

	public function getDetailsValueAsDateTime($key) {
		if (isset($this->details[$key])) {
			return $this->details[$key] instanceof \DateTime  ? $this->details[$key] : new \DateTime($this->details[$key], new \DateTimeZone('UTC'));
		}
	}


	public function getDetailsValueForCustomField(EventCustomFieldDefinitionModel $customField) {
		$key = 'event.custom.' . $customField->getKey();
		return isset($this->details[$key]) ? $this->details[$key] : null;
	}

	public function hasDetailsValue($key) {
		return isset($this->details[$key]) && $this->details[$key];
	}

	public function hasDetailsValueForCustomField(EventCustomFieldDefinitionModel $customField) {
		$key = 'event.custom.' . $customField->getKey();
		return isset($this->details[$key]) && $this->details[$key];
	}

	/**
	 * @return mixed
	 */
	public function getEventId()
	{
		return $this->event_id;
	}

	/**
	 * @param mixed $event_id
	 */
	public function setEventId($event_id)
	{
		$this->event_id = $event_id;
	}

	/**
	 * @return mixed
	 */
	public function getWasExistingEventId()
	{
		return $this->was_existing_event_id;
	}

	/**
	 * @param mixed $was_existing_event_id
	 */
	public function setWasExistingEventId($was_existing_event_id)
	{
		$this->was_existing_event_id = $was_existing_event_id;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getSiteId()
	{
		return $this->site_id;
	}

	/**
	 * @param mixed $site_id
	 */
	public function setSiteId($site_id)
	{
		$this->site_id = $site_id;
	}

	/**
	 * @return mixed
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @param mixed $slug
	 */
	public function setSlug($slug)
	{
		$this->slug = $slug;
	}


	/**
	 * @return mixed
	 */
	public function getUserAccountId()
	{
		return $this->user_account_id;
	}

	/**
	 * @param mixed $user_account_id
	 */
	public function setUserAccountId($user_account_id)
	{
		$this->user_account_id = $user_account_id;
	}

	/**
	 * @return array
	 */
	public function getNotDuplicateEvents()
	{
		return $this->not_duplicate_events;
	}

	/**
	 * @return mixed
	 */
	public function getNotDuplicateEventsForDatabase()
	{
		return implode(",", $this->not_duplicate_events);
	}

	public function addNotDuplicateEvents($slugs) {
		$new = false;
		foreach($slugs as $slug) {
			if (trim($slug) && !in_array(trim($slug), $this->not_duplicate_events)) {
				$this->not_duplicate_events[] = trim($slug);
				$new = true;
			}
		}
		return $new;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return mixed
	 */
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}



}

