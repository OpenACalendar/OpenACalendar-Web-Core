<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserGroupModel {


	protected $id;
	protected $title;
	protected $description;
	protected $is_deleted = false;
	protected $is_in_index = false;
	protected $is_includes_anonymous = false;
	protected $is_includes_users = false;
	protected $is_includes_verified_users = false;


	public function setFromDataBaseRow($data) {
		$this->id  = $data['id'];
		$this->title  = $data['title'];
		$this->description  = $data['description'];
		$this->is_deleted  = $data['is_deleted'];
		$this->is_in_index  = $data['is_in_index'];
		$this->is_includes_anonymous  = $data['is_includes_anonymous'];
		$this->is_includes_users  = $data['is_includes_users'];
		$this->is_includes_verified_users  = $data['is_includes_verified_users'];
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
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
	 * @param boolean $is_deleted
	 */
	public function setIsDeleted($is_deleted)
	{
		$this->is_deleted = $is_deleted;
	}

	/**
	 * @return boolean
	 */
	public function getIsDeleted()
	{
		return $this->is_deleted;
	}

	/**
	 * @param boolean $is_in_index
	 */
	public function setIsInIndex($is_in_index)
	{
		$this->is_in_index = $is_in_index;
	}

	/**
	 * @return boolean
	 */
	public function getIsInIndex()
	{
		return $this->is_in_index;
	}

	/**
	 * @param boolean $is_includes_anonymous
	 */
	public function setIsIncludesAnonymous($is_includes_anonymous)
	{
		$this->is_includes_anonymous = $is_includes_anonymous;
	}

	/**
	 * @return boolean
	 */
	public function getIsIncludesAnonymous()
	{
		return $this->is_includes_anonymous;
	}

	/**
	 * @param boolean $is_includes_users
	 */
	public function setIsIncludesUsers($is_includes_users)
	{
		$this->is_includes_users = $is_includes_users;
	}

	/**
	 * @return boolean
	 */
	public function getIsIncludesUsers()
	{
		return $this->is_includes_users;
	}

	/**
	 * @param boolean $is_includes_verified_users
	 */
	public function setIsIncludesVerifiedUsers($is_includes_verified_users)
	{
		$this->is_includes_verified_users = $is_includes_verified_users;
	}

	/**
	 * @return boolean
	 */
	public function getIsIncludesVerifiedUsers()
	{
		return $this->is_includes_verified_users;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}



}


