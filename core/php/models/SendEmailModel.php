<?php

namespace models;

use repositories\builders\EventRepositoryBuilder;
use Silex\Application;
use repositories\SiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendEmailModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $subject;
	protected $send_to;
	protected $introduction;
	protected $event_html;
	protected $event_text;
	protected $days_into_future = 35;
	protected $created_at;
	protected $sent_at;
	protected $discarded_at;
	protected $timezone;

	protected $events;
	
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->subject = $data['subject'];
		$this->send_to = $data['send_to'];
		$this->introduction = $data['introduction'];
		$this->event_html = $data['event_html'];
		$this->event_text = $data['event_text'];
		$this->days_into_future = $data['days_into_future'];
		$this->timezone = $data['timezone'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = $data['created_at'] ? new \DateTime($data['created_at'], $utc) : null;
		$this->sent_at = $data['sent_at'] ? new \DateTime($data['sent_at'], $utc) : null;
		$this->discarded_at = $data['discarded_at'] ? new \DateTime($data['discarded_at'], $utc) : null;
	}	
	
	public function buildEvents(Application $app) {
		global $CONFIG;
		
		$repo = new SiteRepository();
		$site = $repo->loadById($this->site_id);
		
		$start = \TimeSource::getDateTime();
		$end =  \TimeSource::getDateTime();
		$end->add(new \DateInterval("P".($this->days_into_future+1)."D"));

		$calendar = new \RenderCalendar();
		$calendar->setStartAndEnd($start, $end);
		$calendar->getEventRepositoryBuilder()->setSite($site);
		$calendar->getEventRepositoryBuilder()->setIncludeDeleted(true);
		
		$calData = $calendar->getData();
		$this->events = $calendar->getEvents();
		
		$this->event_text = $app['twig']->render('email/sendemail.eventview.calendar.txt.twig', array(
			'data'=>$calData,
			'currentSite'=>$site,
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/sendemail.eventview.calendar.txt', $this->event_text);

		$this->event_html = $app['twig']->render('email/sendemail.eventview.calendar.html.twig', array(
			'data'=>$calData,
			'currentSite'=>$site,
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/sendemail.eventview.calendar.html', $this->event_html);
		
		
	}
	
	public function getEvents() {
		return $this->events;
	}
	
	public function isSent() {
		return (boolean)$this->sent_at;
	}
	
	public function isDiscarded() {
		return (boolean)$this->discarded_at;
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

	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function getSendTo() {
		return $this->send_to;
	}

	public function setSendTo($send_to) {
		$this->send_to = $send_to;
	}

	public function getIntroduction() {
		return $this->introduction;
	}

	public function setIntroduction($introduction) {
		$this->introduction = $introduction;
	}

	public function getEventHTML() {
		return $this->event_html;
	}

	public function setEventHTML($eventHTML) {
		$this->event_html = $eventHTML;
	}

	public function getEventText() {
		return $this->event_text;
	}

	public function setEventText($eventText) {
		$this->event_text = $eventText;
	}

	public function getDaysIntoFuture() {
		return $this->days_into_future;
	}

	public function setDaysIntoFuture($days_into_future) {
		$this->days_into_future = $days_into_future;
	}
	
	public function getTimezone() {
		return $this->timezone;
	}

	public function setTimezone($timezone) {
		$this->timezone = $timezone;
	}

	public function getSendFromName(UserAccountModel $sentBy) {
		global $CONFIG;
		return $CONFIG->siteTitle." on behalf of ".$sentBy->getEmail();
	}

	public function send(Application $app, UserAccountModel $sentBy) {
		global $CONFIG;

		$message = \Swift_Message::newInstance();
		$message->setSubject($this->subject);
		$message->setFrom(array($CONFIG->emailFrom => $this->getSendFromName($sentBy)));
		$message->setTo($this->send_to);

		configureAppForThemeVariables();

		$messageText = $app['twig']->render('email/sendemail.txt.twig', array(
			'currentTimeZone'=>$this->timezone,
			'sendemail'=>$this,
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/sendemail.txt', $messageText);
		$message->setBody($messageText);

		$messageHTML = $app['twig']->render('email/sendemail.html.twig', array(
			'currentTimeZone'=>$this->timezone,
			'sendemail'=>$this
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/sendemail.html', $messageHTML);
		$message->addPart($messageHTML,'text/html');

		if (!$CONFIG->isDebug) $app['mailer']->send($message);
	}
		
	
}
