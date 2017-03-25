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
class CountryModel {

	protected $id;
	protected $title;
	protected $two_char_code;
	protected $timezones;
	protected $max_lat;
	protected $max_lng;
	protected $min_lat;
	protected $min_lng;
    protected $cached_future_events_in_site;


	protected $site_is_in;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->title = $data['title'];
		$this->two_char_code = $data['two_char_code'];
		$this->timezones = $data['timezones'];
		$this->site_is_in = isset($data['site_is_in']) ? $data['site_is_in'] : false;
		$this->max_lat = $data['max_lat'];
		$this->max_lng = $data['max_lng'];
		$this->min_lat = $data['min_lat'];
		$this->min_lng = $data['min_lng'];
        $this->cached_future_events_in_site = isset($data['cached_future_events_in_site']) ? $data['cached_future_events_in_site'] : null;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTwoCharCode() {
		return $this->two_char_code;
	}

	public function setTwoCharCode($two_char_code) {
		$this->two_char_code = $two_char_code;
	}
	
	public function getTimezones() {
		return $this->timezones;
	}
	
	public function getTimezonesAsList() {
		return explode(",", $this->timezones);
	}

	public function setTimezones($timezones) {
		$this->timezones = $timezones;
	}

		
	public function getSiteIsIn() {
		return $this->site_is_in;
	}

    public function getHasBounds() {
        return (boolean)$this->max_lat;
    }

	public function getMaxLat() {
		return $this->max_lat;
	}

	public function getMaxLng() {
		return $this->max_lng;
	}

	public function getMinLat() {
		return $this->min_lat;
	}

	public function getMinLng() {
		return $this->min_lng;
	}

    /**
     * @return integer
     */
    public function getCachedFutureEventsInSite() {
        return $this->cached_future_events_in_site;
    }

    /**
     * @param integer $cached_future_events_in_site
     */
    public function setCachedFutureEventsInSite(int $cached_future_events_in_site ) {
        $this->cached_future_events_in_site = $cached_future_events_in_site;
    }


	
}

