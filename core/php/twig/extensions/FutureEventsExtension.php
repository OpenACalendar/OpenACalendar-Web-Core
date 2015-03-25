<?php

namespace twig\extensions;

use repositories\builders\EventRepositoryBuilder;
use Silex\Application;


/**
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
class FutureEventsExtension  extends \Twig_Extension {

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
        );
    }

    public function futureEventsCount($data)
    {

		$erb = new EventRepositoryBuilder();
		$erb->setAfterNow();
		$erb->setIncludeCancelled(true);
		$erb->setIncludeDeleted(false);

		if ($data instanceof \models\AreaModel) {
			$erb->setArea($data);
		}

		return $erb->fetchCount();
		
    }


    public function getName()
    {
        return 'jarofgreen_wikicalendar_futureeventsextension';
    }
}


