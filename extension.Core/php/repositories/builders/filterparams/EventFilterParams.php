<?php

namespace repositories\builders\filterparams;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventFilterParams {

	function __construct(EventRepositoryBuilder $erb = null) {
		$this->eventRepositoryBuilder = $erb ? $erb : new EventRepositoryBuilder();
	}

	
	protected $eventRepositoryBuilder;
	
	public function getEventRepositoryBuilder() {
		return $this->eventRepositoryBuilder;
	}

	// ############################### optional controls; turn on and off
	
	protected $hasDateControls = true;
	
	public function getDateControls() {
		return $this->hasDateControls;
	}
	
	public function setHasDateControls($hasDateControls) {
		$this->hasDateControls = $hasDateControls;
	}
	
	protected $hasSpecifiedUserControls = false;
	protected $hasSpecifiedUser = null;
	protected $hasSpecifiedUserIncludePrivate = false;
	
	public function getSpecifiedUserControls() {
		return $this->hasSpecifiedUserControls;
	}

	public function setSpecifiedUserControls($hasSpecifiedUserControls, UserAccountModel $user = null, $includePrivate = false) {
		if ($hasSpecifiedUserControls && $user) {
			$this->hasSpecifiedUserControls = true;
			$this->hasSpecifiedUser = $user;
			$this->hasSpecifiedUserIncludePrivate = $includePrivate;
		} else {
			$this->hasSpecifiedUserControls = false;
		}
		return $this;
	}

	// ############################### params
	
	protected $fromNow = true;
	protected $from;
	protected $include_deleted = false;
	protected $includeSpecifiedUserAttending = true;
	protected $includeSpecifiedUserWatching = true;


	public function set($data) {
		if (isset($data['eventListFilterDataSubmitted'])) {
		
			// From
			if ($this->hasDateControls) {
				$fromNow = isset($data['fromNow']) ? $data['fromNow'] : 0;
				if (!$fromNow) {
					$this->fromNow = false;
					$from = isset($data['from']) ? strtolower(trim($data['from'])) : null;
					if ($from) {
						try {
							$fromDT = new \DateTime($from, new \DateTimeZone('UTC'));
							$fromDT->setTime(0, 0, 0);
							$this->from = $fromDT->format('j F Y');							
						} catch (\Exception $e) {
							// assume it's parse exception, ignore.
						}
					}
				}
			}
			
			// Specified User Controls
			if ($this->hasSpecifiedUserControls) {
				if (isset($data['specifiedUserWhich']) && $data['specifiedUserWhich'] == "AW") {
					$this->includeSpecifiedUserAttending = true;
					$this->includeSpecifiedUserWatching = true;
				} else if (isset($data['specifiedUserWhich']) && $data['specifiedUserWhich'] == "A") {
					$this->includeSpecifiedUserAttending = true;
					$this->includeSpecifiedUserWatching = false;
				}
			}
			
			// Deleted
			if (isset($data['includeDeleted']) && $data['includeDeleted'] == '1') {
				$this->include_deleted = true;
			}
			
		}
		
		// apply to search
		if ($this->fromNow) {
			$this->eventRepositoryBuilder->setAfterNow();
		} else if ($this->from) {
			$this->eventRepositoryBuilder->setAfter($fromDT);
		}
		$this->eventRepositoryBuilder->setIncludeDeleted($this->include_deleted);
		if ($this->hasSpecifiedUserControls) {
			$this->eventRepositoryBuilder->setUserAccount($this->hasSpecifiedUser, false,
					$this->hasSpecifiedUserIncludePrivate, $this->includeSpecifiedUserAttending, $this->includeSpecifiedUserWatching);
		}
		
	}
	
	public function getFrom() {
		return $this->from;
	}
	public function getFromNow() {
		return $this->fromNow;
	}
	public function getIncludeDeleted() {
		return $this->include_deleted;
	}
	public function getIncludeSpecifiedUserAttending() {
		return $this->includeSpecifiedUserAttending;
	}

	public function getIncludeSpecifiedUserWatching() {
		return $this->includeSpecifiedUserWatching;
	}



	
}


