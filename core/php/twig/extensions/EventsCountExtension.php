<?php

namespace twig\extensions;

use repositories\builders\EventRepositoryBuilder;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventsCountExtension  extends \Twig_Extension {

	protected $app;
	protected $container;

	public function __construct(Application $app = null)
	{
		$this->app = $app;
	}

	public function getFunctions()
	{
		return array();
	}

	public function getFilters()
	{
		return array(
			'futureeventscount' => new \Twig_Filter_Method($this, 'futureEventsCount'),
			'pasteventscount' => new \Twig_Filter_Method($this, 'pastEventsCount'),
		);
	}

	public function futureEventsCount($data)
	{
        global $app;

		$erb = new EventRepositoryBuilder($app);
        $erb->setSite($this->app['currentSite']);
		$erb->setAfterNow();
		$erb->setIncludeCancelled(true);
		$erb->setIncludeDeleted(false);

		if ($data instanceof \models\AreaModel) {
			$erb->setArea($data);
		} else if ($data instanceof \models\GroupModel) {
			$erb->setGroup($data);
		} else if ($data instanceof \models\VenueModel) {
			$erb->setVenue($data);
		} else if ($data instanceof \models\TagModel) {
			$erb->setTag($data);
		}

		return $erb->fetchCount();

	}

	public function pastEventsCount($data)
	{
        global $app;

		$erb = new EventRepositoryBuilder($app);
        $erb->setSite($this->app['currentSite']);
		$erb->setBeforeNow();
		$erb->setIncludeCancelled(true);
		$erb->setIncludeDeleted(false);

		if ($data instanceof \models\AreaModel) {
			$erb->setArea($data);
		} else if ($data instanceof \models\GroupModel) {
			$erb->setGroup($data);
		} else if ($data instanceof \models\VenueModel) {
			$erb->setVenue($data);
		} else if ($data instanceof \models\TagModel) {
			$erb->setTag($data);
		}

		return $erb->fetchCount();

	}


	public function getName()
	{
		return 'jarofgreen_wikicalendar_futureeventsextension';
	}
}


