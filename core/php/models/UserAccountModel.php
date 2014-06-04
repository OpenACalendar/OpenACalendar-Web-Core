<?php

namespace models;

use repositories\builders\EventRepositoryBuilder;
use repositories\UserAtEventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountModel {
	
	public static function  makeCanonicalEmail($email) {
		return trim(strtolower($email));
	}

	public static function  makeCanonicalUserName($username) {
		return trim(strtolower($username));
	}
	
		protected $id;
		protected $email;
		protected $username;
		protected $password_hash;
		protected $is_email_verified;
		protected $email_verify_code;
		protected $is_editor;
		protected $is_system_admin;
		protected $email_upcoming_events_days_notice = 1;
		/**
		* options 
		* - a attending
		* - m maybe attending or attending (default)
		* - w group/site watching, maybe attending or attending
		* - n none at all
		* @var string
		*/			
		protected $email_upcoming_events = 'n';
		protected $is_email_newsletter = false;
		protected $is_clock_12hour;
		protected $is_closed_by_sys_admin = false;
		protected $closed_by_sys_admin_reason;

		/** secondary attributes **/
		protected $is_site_owner = false;
		protected $is_site_administrator = false;
		protected $is_site_editor = false;
		
		protected $created_at;


		public function setFromDataBaseRow($data) {
			$this->id = $data['id'];
			$this->email = $data['email'];
			$this->username = $data['username'];
			$this->password_hash = $data['password_hash'];
			$this->is_email_verified = $data['is_email_verified'];
			$this->email_verify_code = $data['email_verify_code'];
			$this->is_editor = $data['is_editor'];
			$this->is_system_admin = $data['is_system_admin'];
			$this->is_site_owner = isset($data['is_site_owner']) ? $data['is_site_owner'] : false;
			$this->is_site_administrator = isset($data['is_site_administrator']) ? $data['is_site_administrator'] : false;
			$this->is_site_editor = isset($data['is_site_editor']) ? $data['is_site_editor'] : false;
			$this->email_upcoming_events = $data['email_upcoming_events'];
			$this->email_upcoming_events_days_notice = $data['email_upcoming_events_days_notice'];
			$this->is_email_newsletter = (boolean)$data['is_email_newsletter'] ;
			$this->is_clock_12hour = $data['is_clock_12hour'];
			$this->is_closed_by_sys_admin = $data['is_closed_by_sys_admin'];
			$this->closed_by_sys_admin_reason = $data['closed_by_sys_admin_reason'];
			$utc = new \DateTimeZone("UTC");
			$this->created_at = $data['created_at'] ? new \DateTime($data['created_at'], $utc) : '';
		}
		
		public function getId() {
			return $this->id;
		}

		public function setId($id) {
			$this->id = $id;
		}

		public function getEmail() {
			return $this->email;
		}

		public function setEmail($email) {
			$this->email = $email;
		}

		public function getUsername() {
			return $this->username;
		}

		public function setUsername($username) {
			$this->username = $username;
		}


		public function setPassword($password) {
			global $CONFIG;
			$this->password_hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => $CONFIG->bcryptRounds));
		}

		public function checkPassword($password) {
			global $CONFIG;
			return password_verify($password, $this->password_hash);
		}
		
		public function setPasswordHash($password) {
			$this->password_hash = $password;
		}
	
		public function getPasswordHash() {
			return $this->password_hash;
		}

		
		public function getIsEmailVerified() {
			return $this->is_email_verified;
		}

		public function setIsEmailVerified($is_email_verified) {
			$this->is_email_verified = $is_email_verified;
		}

		public function getEmailVerifyCode() {
			return $this->email_verify_code;
		}

		public function setEmailVerifyCode($email_verify_code) {
			$this->email_verify_code = $email_verify_code;
		}

		
		public function getIsSystemAdmin() {
			return $this->is_system_admin;
		}

		public function setIsSystemAdmin($is_system_admin) {
			$this->is_system_admin = $is_system_admin;
		}
		
		public function getIsEditor() {
			return $this->is_editor;
		}

		public function setIsEditor($is_editor) {
			$this->is_editor = $is_editor;
		}
		
		public function getIsSiteOwner() {
			return $this->is_site_owner;
		}

		public function getIsSiteAdministrator() {
			return $this->is_site_administrator;
		}

		public function getIsSiteEditor() {
			return $this->is_site_editor;
		}

		public function getEmailUpcomingEvents() {
			return $this->email_upcoming_events;
		}

		public function setEmailUpcomingEvents($email_upcoming_events) {
			$this->email_upcoming_events = $email_upcoming_events;
			return $this;
		}

		public function getEmailUpcomingEventsDaysNotice() {
			return min(max($this->email_upcoming_events_days_notice,0),14);
		}

		public function setEmailUpcomingEventsDaysNotice($email_upcoming_events_days_notice) {
			$this->email_upcoming_events_days_notice = $email_upcoming_events_days_notice;
			return $this;
		}


		/**
		 * 
		 * @param \models\EntityManager $em
		 * @return type array(array(), array(), array(), boolean) - upcoming events, other events, user at event data, flag if any to send
		 */
		public function getDataForUpcomingEventsEmail() {
			
			$flag = false;

			$start = \TimeSource::getDateTime();
			$end = \TimeSource::getDateTime();
			if ($this->email_upcoming_events_days_notice > 0) {
				$interval = new \DateInterval("P".$this->email_upcoming_events_days_notice."D");
				$start->add($interval);
				$end->add($interval);
			}
			$start->setTime(0, 0, 0);
			$end->setTime(23, 59, 59);

			$upcomingEvents = array();
			$allEvents = array();
			$userAtEvent = array();
			
			$userAtEventRepo = new UserAtEventRepository();
			$erb = new EventRepositoryBuilder();
			$erb->setAfterNow();
			$erb->setIncludeDeleted(false);
			$erb->setUserAccount($this, false, true);
			foreach ($erb->fetchAll() as $event) {
				$userAtEvent[$event->getId()] = $userAtEventRepo->loadByUserAndEvent($this, $event);
				if ($start->getTimestamp() <= $event->getStartAt()->getTimestamp() &&  $event->getStartAt()->getTimestamp() <= $end->getTimestamp()) {
					$upcomingEvents[] = $event;
					if ($this->email_upcoming_events == 'w') {
						$flag = true;
					} else if ($this->email_upcoming_events == 'a') {
						if ($userAtEvent[$event->getId()] && $userAtEvent[$event->getId()]->getIsPlanAttending()) {
							$flag=true;
						}
					} else if ($this->email_upcoming_events == 'm') {
						if ($userAtEvent[$event->getId()] && ($userAtEvent[$event->getId()]->getIsPlanAttending() || $userAtEvent[$event->getId()]->getIsPlanMaybeAttending())) {
							$flag=true;
						}				
					}
				}
				$allEvents[] = $event;				
			}

			return array($upcomingEvents, $allEvents, $userAtEvent, $flag);
			
		}

		public function getIsClock12Hour() {
			return $this->is_clock_12hour;
		}

		public function setIsClock12Hour($clock_12hour) {
			$this->is_clock_12hour = $clock_12hour;
			return $this;
		}

		public function getIsClosedBySysAdmin() {
			return $this->is_closed_by_sys_admin;
		}

		public function getClosedBySysAdminReason() {
			return $this->closed_by_sys_admin_reason;
		}


		/**
		 * Normal emails is routine emails about calendars and events.
		 * Emails about user accounts are seperate from this.
		 * @return type
		 */
		public function getIsCanSendNormalEmails() {
			return $this->is_email_verified && !$this->is_closed_by_sys_admin;
		}
		
		/** 
		 * Can user request access to site? This only looks at attributes of user and not attributes of site. Check both.
		 */
		public function isSiteRequestAccessAllowed() {
			return $this->is_email_verified && $this->is_editor && !$this->is_closed_by_sys_admin;
		}
		
		public function getIsEmailNewsletter() {
			return $this->is_email_newsletter;
		}

		public function setIsEmailNewsletter($is_email_newsletter) {
			$this->is_email_newsletter = $is_email_newsletter;
			return $this;
		}

		public function getCreatedAt() {
			return $this->created_at;
		}


		
}

