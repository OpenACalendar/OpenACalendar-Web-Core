<?php
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseReport {


    /** @var Application */
    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public abstract function getReportTitle();

	public abstract function getExtensionID();

	public abstract function getReportID();

	public abstract function run();


	protected $hasFilterTime = false;

	/**
	 * @return boolean
	 */
	public function getHasFilterTime()
	{
		return $this->hasFilterTime;
	}

	/**
	 * This is INCLUSIVE
	 * @var \DateTime
	 */
	protected $filterTimeStart = null;

	/**
	 * This is INCLUSIVE
	 * @var \DateTime
	 */
	protected $filterTimeEnd = null;

	public function setFilterTime($filterTimeStart, $filterTimeEnd)
	{
		$this->filterTimeStart = $filterTimeStart;
		$this->filterTimeEnd = $filterTimeEnd;
	}

	protected $hasFilterSite = false;

	/**
	 * @return boolean
	 */
	public function getHasFilterSite()
	{
		return $this->hasFilterSite;
	}


	protected $filterSiteId;

	/**
	 * @param mixed $filterSiteId
	 */
	public function setFilterSiteId($filterSiteId)
	{
		$this->filterSiteId = $filterSiteId;
	}


} 
