<?php

namespace models;
use repositories\UserWatchesSiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteModel {
	
	protected $user_account_id;
	protected $site_id;
	protected $is_watching = false;
	protected $is_was_once_watching = false;
	protected $last_notify_email_sent;
	protected $last_prompt_email_sent;
	protected $created_at; 
	protected $last_watch_started;




	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->site_id = $data['site_id'];
		$this->is_watching = $data['is_watching'];
		$this->is_was_once_watching = $data['is_was_once_watching'];
		$utc = new \DateTimeZone("UTC");
		$this->last_notify_email_sent = $data['last_notify_email_sent'] ? new \DateTime($data['last_notify_email_sent'], $utc) : null;
		$this->last_prompt_email_sent = $data['last_prompt_email_sent'] ? new \DateTime($data['last_prompt_email_sent'], $utc) : null;
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->last_watch_started = new \DateTime($data['last_watch_started'], $utc);
	}
	
	
	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	public function getIsWatching() {
		return $this->is_watching;
	}

	public function setIsWatching($is_watching) {
		$this->is_watching = $is_watching;
	}

	public function getIsWasOnceWatching() {
		return $this->is_was_once_watching;
	}

	public function setIsWasOnceWatching($is_was_once_watching) {
		$this->is_was_once_watching = $is_was_once_watching;
	}

	public function getLastNotifyEmailSent() {
		return $this->last_notify_email_sent;
	}

	public function setLastNotifyEmailSent($last_notify_email_sent) {
		$this->last_notify_email_sent = $last_notify_email_sent;
	}

	public function getLastPromptEmailSent() {
		return $this->last_prompt_email_sent;
	}

	public function setLastPromptEmailSent($last_prompt_email_sent) {
		$this->last_prompt_email_sent = $last_prompt_email_sent;
	}


	public function getSinceDateForNotifyChecking() {
		$dates = array( $this->created_at );
		if ($this->last_notify_email_sent) $dates[] = $this->last_notify_email_sent;
		if ($this->last_watch_started) $dates[] = $this->last_watch_started;
		$date = max($dates);
		
		// TODO safety: only allow it to go back one week.
		
		return $date;
	}

	public function getSinceDateForPromptChecking() {
		$dates = array( $this->created_at );
		if ($this->last_prompt_email_sent) $dates[] = $this->last_prompt_email_sent;
		if ($this->last_watch_started) $dates[] = $this->last_watch_started;
		$date = max($dates);
		
		return $date;
	}

	public function getPromptEmailData(SiteModel $site, EventModel $lastEvent = null) {
		global $CONFIG;
		
		$moreEventsNeeded = false;
		$checkTime = \TimeSource::getDateTime();
		
		if ($lastEvent) {
			$dateInterval = new \DateInterval("P".$site->getPromptEmailsDaysInAdvance()."D");

			$endTimeMinusExtra = clone $lastEvent->getEndAt();
			$endTimeMinusExtra->sub($dateInterval);

			if ($endTimeMinusExtra < $checkTime) {
				// there is a last event and it is before now plus whenever!

				// Now check; have we notified the user of this before?
				$dateSince = $this->getSinceDateForPromptChecking();

				if ($endTimeMinusExtra > $dateSince) {

					// Finally check: has safe gap passed where we only send one email every X days?
					$safeGapDays = max($site->getPromptEmailsDaysInAdvance(), $CONFIG->userWatchesPromptEmailSafeGapDays);
					$nowMinusSafeGap = \TimeSource::getDateTime();
					$nowMinusSafeGap->sub(new \DateInterval("P".$safeGapDays."D"));
					
					if ($dateSince < $nowMinusSafeGap ) {
						
						// Finally we can agree to send an alert!
						$moreEventsNeeded = true;
					}
				}
			}
		}
		
		// TODO when add importing, need to double check this.

		return array(
				'moreEventsNeeded'=>$moreEventsNeeded,
				'checkTime'=>$checkTime
			);
		
	}

	
	public function getSinceDateForGroupPromptChecking(GroupModel $group) {
		$dates = array( $this->created_at );
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$r = $userWatchesSiteRepo->getLastGroupPromptEmailSent($this, $group);
		if ($r) $dates[] = $r;
		if ($this->last_watch_started) $dates[] = $this->last_watch_started;
		$date = max($dates);
		
		return $date;
	}
	
	public function getGroupPromptEmailData(SiteModel $site, GroupModel $group, EventModel $lastEvent = null) {
		global $CONFIG;
		
		$moreEventsNeeded = false;
		$checkTime = \TimeSource::getDateTime();

		if ($lastEvent) {
			$dateInterval = new \DateInterval("P".$site->getPromptEmailsDaysInAdvance()."D");

			$endTimeMinusExtra = clone $lastEvent->getEndAt();
			$endTimeMinusExtra->sub($dateInterval);

			if ($endTimeMinusExtra < $checkTime) {
				// there is a last event and it is before now plus whenever!

				// Now check; have we notified the user of this before?
				$dateSince = $this->getSinceDateForGroupPromptChecking($group);

				if ($endTimeMinusExtra > $dateSince) {
			
					// Finally check: has safe gap passed where we only send one email every X days?
					$safeGapDays = max($site->getPromptEmailsDaysInAdvance(), $CONFIG->userWatchesPromptEmailSafeGapDays);
					$nowMinusSafeGap = \TimeSource::getDateTime();
					$nowMinusSafeGap->sub(new \DateInterval("P".$safeGapDays."D"));
					
					if ($dateSince < $nowMinusSafeGap ) {

						// Finally we can agree to send an alert!
						$moreEventsNeeded = true;
					}
				}
			}
		}
		
		// TODO when add importing, need to double check this.

		return array(
				'moreEventsNeeded'=>$moreEventsNeeded,
				'checkTime'=>$checkTime
			);
		
	}
	
}
	