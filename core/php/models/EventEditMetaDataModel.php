<?php

namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventEditMetaDataModel extends \BaseEditMetaDataModel
{

	protected $createdFromNewEventDraftID;

	/**
	 * @return mixed
	 */
	public function getCreatedFromNewEventDraftID()
	{
		return $this->createdFromNewEventDraftID;
	}

	/**
	 * @param mixed $createdFromNewEventDraftID
	 */
	public function setCreatedFromNewEventDraftID($createdFromNewEventDraftID)
	{
		$this->createdFromNewEventDraftID = $createdFromNewEventDraftID;
	}



}
