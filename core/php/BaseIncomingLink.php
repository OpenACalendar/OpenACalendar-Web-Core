<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseIncomingLink {

	public function getTypeExtensionID() {
		return 'org.openacalendar';
	}

	public abstract function getType();

	protected $id;

	protected $reporter_useragent;

	protected $reporter_ip;

	protected $sourceURL;

	protected $targetURL;

	protected $is_verified = false;

	protected $data;

	public function setFromDataBaseRow($data)
	{
		$this->id = $data['id'];
		$this->reporter_useragent = $data['reporter_useragent'];
		$this->reporter_ip = $data['reporter_ip'];
		$this->sourceURL = $data['source_url'];
		$this->targetURL = $data['target_url'];
		$this->is_verified = $data['is_verified'];
		$this->data = $data['data'];
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param boolean $is_verified
	 */
	public function setIsVerified($is_verified)
	{
		$this->is_verified = $is_verified;
	}

	/**
	 * @return boolean
	 */
	public function getIsVerified()
	{
		return $this->is_verified;
	}

	/**
	 * @param mixed $sourceURL
	 */
	public function setSourceURL($sourceURL)
	{
		$this->sourceURL = $sourceURL;
	}

	/**
	 * @return mixed
	 */
	public function getSourceURL()
	{
		return $this->sourceURL;
	}

	/**
	 * @param mixed $targetURL
	 */
	public function setTargetURL($targetURL)
	{
		$this->targetURL = $targetURL;
	}

	/**
	 * @return mixed
	 */
	public function getTargetURL()
	{
		return $this->targetURL;
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
	 * @param mixed $reporter_ip
	 */
	public function setReporterIp($reporter_ip)
	{
		$this->reporter_ip = $reporter_ip;
	}

	/**
	 * @return mixed
	 */
	public function getReporterIp()
	{
		return $this->reporter_ip;
	}

	/**
	 * @param mixed $reporter_useragent
	 */
	public function setReporterUseragent($reporter_useragent)
	{
		$this->reporter_useragent = $reporter_useragent;
	}

	/**
	 * @return mixed
	 */
	public function getReporterUseragent()
	{
		return $this->reporter_useragent;
	}



}
