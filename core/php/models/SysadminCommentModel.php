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
class SysadminCommentModel {
	
	protected $id;
	protected $comment;

	/** @var DateTime **/
	protected $created_at;

	protected $user_account_username;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->comment = $data['comment'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_username = $data['user_account_username'];
	}

	/**
	 * @param mixed $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
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
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return DateTime
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return mixed
	 */
	public function getUserAccountUsername()
	{
		return $this->user_account_username;
	}



}
