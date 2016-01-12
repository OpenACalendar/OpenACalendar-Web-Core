<?php


namespace cliapi1;

use models\CountryModel;
use models\EventModel;
use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\UserAccountModel;
use repositories\CountryRepository;
use repositories\EventRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\GroupRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class CreateEvent {


    /** @var Application */
    protected $app;

	/** @var SiteModel **/
	protected $site;

	/** @var UserModel **/
	protected $user;

	/** @var GroupModel **/
	protected $group;
	
	protected $summary;
	protected $description;
	protected $group_id;
	protected $timezone;
	protected $venue_id;

	/** @var  CountryModel */
	protected $country;

	protected $area_id;
	protected $url;
	protected $is_virtual = false;
	protected $is_physical = true;


	/** @var DateTime **/
	protected $start_at;
	/** @var DateTime **/
	protected $end_at;


    function __construct(Application $app)
    {
        $this->app = $app;
		if ($this->app['config']->isSingleSiteMode) {
			$siteRepo = new SiteRepository();
			$this->site = $siteRepo->loadById($this->app['config']->singleSiteID);
		}
		$countryRepo = new CountryRepository();
		// TODO if we have $this->site, should set $this->country_id & $this->timezone from countries which are enabled
		$this->country = $countryRepo->loadByTwoCharCode("GB");
		$this->timezone = 'Europe/London';
	}
	
	public function setFromJSON($json) {
		if (isset($json->event)) {
			if (isset($json->event->summary)) {
				$this->summary = $json->event->summary;
			}
			if (isset($json->event->description)) {
				$this->description = $json->event->description;
			}
			if (isset($json->event->url)) {
				$this->url = $json->event->url;
			}
			$timezone = new \DateTimeZone($this->timezone);
			if (isset($json->event->start->str)) {
				$this->start_at = new \DateTime($json->event->start->str, $timezone);
			}
			if (isset($json->event->end->str)) {
				$this->end_at = new \DateTime($json->event->end->str, $timezone);
			}
			if (isset($json->event->country) && isset($json->event->country->code) && $json->event->country->code) {
				$countryRepo = new CountryRepository();
				// Delibrately setting NULL on failure so user gets an error message.
				$this->country = $countryRepo->loadByTwoCharCode($json->event->country->code);
				// TODO check allowed in this site
			}
			if (isset($json->event->timezone)) {
				// Delibrately setting NULL on failure so user gets an error message.
				$this->timezone = $this->country && in_array($json->event->timezone, $this->country->getTimezonesAsList()) ? $json->event->timezone : null ;
			}
		}
		if (isset($json->site)) {
			$siteRepo = new SiteRepository();
			if (isset($json->site->id)) {
				$this->site = $siteRepo->loadById($json->site->id);
			}
			if (isset($json->site->slug)) {
				$this->site = $siteRepo->loadBySlug($json->site->slug);
			}
		}
		if (isset($json->user)) {
			$userRepo = new UserAccountRepository();
			if (isset($json->user->email)) {
				$this->user = $userRepo->loadByEmail($json->user->email);
			} else if (isset($json->user->username)) {
				$this->user = $userRepo->loadByUserName($json->user->username);
			}
		} 
		if (isset($json->group)) {
			$groupRepo = new GroupRepository();
			if (isset($json->group->slug) && $this->site) {
				$this->group = $groupRepo->loadBySlug($this->site, $json->group->slug);
			} else if (isset($json->group->id)) {
				$this->group = $groupRepo->loadById($json->group->id);
			}
		} 
	}
	
	protected $errorMessages = array();

	public function getErrorMessages() {
		return $this->errorMessages;
	}

	public function canGo() {
		if (!$this->country) {
			$this->errorMessages[] = 'Country not set!';
		}
		if (!$this->timezone) {
			$this->errorMessages[] = 'Timezone not set!';
		}
		if (!$this->site) {
			$this->errorMessages[] = 'Site not set!';
		}
		if (!$this->start_at) {
			$this->errorMessages[] = 'Start not set!';
		}
		if (!$this->end_at) {
			$this->errorMessages[] = 'End not set!';
		}
		
		if ($this->start_at && $this->end_at) {
			$event = new EventModel();
			$event->setSummary($this->summary);
			$event->setDescription($this->description);
			$event->setUrl($this->url);
			$event->setTimezone($this->timezone);
			$event->setStartAt($this->start_at);
			$event->setEndAt($this->end_at);
			$event->setCountryId($this->country ? $this->country->getId() : null);

			$event->validate();
			$this->errorMessages = array_merge($this->errorMessages, $event->getValidateErrors());
		}
		
		return $this->errorMessages ? false : true;
	}
	
	public function go() {
			
		$event = new EventModel();
		$event->setSummary($this->summary);
		$event->setDescription($this->description);
		$event->setUrl($this->url);
		$event->setTimezone($this->timezone);
		$event->setStartAt($this->start_at);
		$event->setEndAt($this->end_at);
		$event->setCountryId($this->country->getId());
		
		$eventRepo = new EventRepository();
		$eventRepo->create($event, $this->site, $this->user, $this->group);
		
	}


}

