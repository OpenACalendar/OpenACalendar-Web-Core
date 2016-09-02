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
class UserAccountResetModel {
	
	protected $user_account_id;
	protected $access_key;
	protected $created_at;
	protected $reset_at;


	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getAccessKey() {
		return $this->access_key;
	}

	public function setAccessKey($access_key) {
		$this->access_key = $access_key;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getResetAt() {
		return $this->reset_at;
	}

	public function setResetAt($reset_at) {
		$this->reset_at = $reset_at;
	}

	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->access_key = $data['access_key'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = $data['created_at'] ? new \DateTime($data['created_at'], $utc) : null;
		$this->reset_at = $data['reset_at'] ? new \DateTime($data['reset_at'], $utc) : null;
	}
	
	public function getIsAlreadyUsed() {
		return (boolean)$this->reset_at;
	}
	
	public function sendEmail($app, UserAccountModel $user) {
		global $CONFIG;

		$message = \Swift_Message::newInstance();
		$message->setSubject("Reset your account on ".$CONFIG->installTitle);
		$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
		$message->setTo($user->getEmail());

		configureAppForThemeVariables();

		$messageText = $app['twig']->render('email/userResetEmail.txt.twig', array(
			'user'=>$user,
			'code'=>$this->access_key,
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/userResetEmail.txt', $messageText);
		$message->setBody($messageText);

		$messageHTML = $app['twig']->render('email/userResetEmail.html.twig', array(
			'user'=>$user,
			'code'=>$this->access_key,
		));
		if ($CONFIG->isDebug) file_put_contents('/tmp/userResetEmail.html', $messageHTML);
		$message->addPart($messageHTML,'text/html');

        if ($CONFIG->actuallySendEmail) {
            $app['mailer']->send($message);
        }
	}
}

