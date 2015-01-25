<?php


namespace org\openacalendar\contact\models;


/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ContactSupportModel {

	protected $id;
	protected $subject;
	protected $message;
	protected $email;
	protected $user_account_id;
	protected $ip;
	protected $browser;
	protected $created_at;
	protected $is_spam_manually_detected = false;
	protected $is_spam_honeypot_field_detected = false;
	
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->subject = $data['subject'];
		$this->message = $data['message'];
		$this->email = $data['email'];
		$this->user_account_id = $data['user_account_id'];
		$this->ip = $data['ip'];
		$this->browser = $data['browser'];
		$this->created_at = $data['created_at'];
		$this->is_spam_honeypot_field_detected = $data['is_spam_honeypot_field_detected'];
		$this->is_spam_manually_detected = $data['is_spam_manually_detected'];
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getIp() {
		return $this->ip;
	}

	public function setIp($ip) {
		$this->ip = $ip;
	}

	public function getBrowser() {
		return $this->browser;
	}

	public function setBrowser($browser) {
		$this->browser = $browser;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}

	public function getIsSpamManuallyDetected() {
		return $this->is_spam_manually_detected;
	}

	public function setIsSpamManuallyDetected($is_spam_manually_detected) {
		$this->is_spam_manually_detected = $is_spam_manually_detected;
	}

	public function getIsSpamHoneypotFieldDetected() {
		return $this->is_spam_honeypot_field_detected;
	}

	public function setIsSpamHoneypotFieldDetected($is_spam_honeypot_field_detected) {
		$this->is_spam_honeypot_field_detected = $is_spam_honeypot_field_detected;
	}

	public function getIsSpam() {
		return $this->is_spam_honeypot_field_detected || $this->is_spam_manually_detected;
	}

	public function sendEmailToSupport($app, $userFrom = null) {
		global $CONFIG;

		if ($CONFIG->contactEmail) {
		
			$message = \Swift_Message::newInstance();
			$message->setSubject("Contact Message From ".$CONFIG->siteTitle." : ".$this->subject);
			$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
			$message->setTo($CONFIG->contactEmail);
			$message->setReplyTo(array($this->email => $this->email));

			configureAppForThemeVariables();

			$messageText = $app['twig']->render('email/contactSupport.txt.twig', array(
				'contact'=>$this,
				'userFrom'=>$userFrom,
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/contactSupport.txt', $messageText);
			$message->setBody($messageText);

			$messageHTML = $app['twig']->render('email/contactSupport.html.twig', array(
				'contact'=>$this,
				'userFrom'=>$userFrom,
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/contactSupport.html', $messageHTML);
			$message->addPart($messageHTML,'text/html');

			if (!$CONFIG->isDebug) $app['mailer']->send($message);

		}
	}
	
	
}


