<?php



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ReportDataItemLabelTimeRange  {

	/** @var  \DateTime */
	protected $labelStart;

	/** @var  \DateTime */
	protected $labelEnd;

	protected $data;

	function __construct($labelStart,$labelEnd, $data=null)
	{
		$this->data = $data;
		$this->labelEnd = clone $labelEnd;
		$this->labelStart = clone $labelStart;
	}

	/**
	 * @return null
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return \DateTime
	 */
	public function getLabelEnd()
	{
		return $this->labelEnd;
	}

	/**
	 * @return \DateTime
	 */
	public function getLabelStart()
	{
		return $this->labelStart;
	}

	public function getLabelText() {
		return $this->labelStart->format("Y-m-d H:i:s") . " - " . $this->labelEnd->format("Y-m-d H:i:s");
	}

	/**
	 * @param null $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}




}
