<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ReportDataItem {

	protected $labelText;
	protected $labelID;
	protected $labelURL;

	protected $data;

	function __construct($data, $labelID, $labelText, $labelURL)
	{
		$this->data = $data;
		$this->labelID = $labelID;
		$this->labelText = $labelText;
		$this->labelURL = $labelURL;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return mixed
	 */
	public function getLabelID()
	{
		return $this->labelID;
	}

	/**
	 * @return mixed
	 */
	public function getLabelText()
	{
		return $this->labelText;
	}

	/**
	 * @return mixed
	 */
	public function getLabelURL()
	{
		return $this->labelURL;
	}



}
