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
class EventCustomFieldDefinitionModel
{

	protected $id;
	protected $site_id;
	protected $key;
	protected $label;
	protected $extension_id;
	protected $is_active;
	protected $type;



	public function setFromDataBaseRow($data)
	{
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->key = $data['key'];
		$this->label = $data['label'];
		$this->extension_id = $data['extension_id'];
		$this->is_active = $data['is_active'];
		$this->type = $data['type'];
	}

	/**
	 * @return mixed
	 */
	public function getExtensionId()
	{
		return $this->extension_id;
	}

	/**
	 * @param mixed $extension_id
	 */
	public function setExtensionId($extension_id)
	{
		$this->extension_id = $extension_id;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param mixed $key
	 */
	public function setKey($key)
	{
		if ($this->isKeyValid($key)) {
			$this->key = $key;
		} else {
			throw new \Exception("Key not allowed!");
		}
	}

	/**
	 * @return mixed
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param mixed $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return mixed
	 */
	public function getSiteId()
	{
		return $this->site_id;
	}

	/**
	 * @param mixed $site_id
	 */
	public function setSiteId($site_id)
	{
		$this->site_id = $site_id;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function getIsActive()
	{
		return $this->is_active;
	}

	/**
	 * @param mixed $is_active
	 */
	public function setIsActive($is_active)
	{
		$this->is_active = $is_active;
	}




	public static function  isKeyValid($key) {
		return preg_match('/^[\w]+$/', $key);
	}


}