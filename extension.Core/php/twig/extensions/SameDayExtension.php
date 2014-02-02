<?php

namespace twig\extensions;

use Silex\Application;

/**
 * Takes two DateTime objects.
 * Returns true if they are on the same day.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
class SameDayExtension extends \Twig_Extension
{
    protected $app;	
    protected $container;

    public function __construct(Application $app = null)
    {
        $this->app = $app;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            'sameday' => new \Twig_Function_Method($this, 'sameday'),
        );
    }

    public function sameday($date1,$date2) {
		if(get_class($date1) != 'DateTime') return false;
		if(get_class($date2) != 'DateTime') return false;
		return $date1->format('dmYe') == $date2->format('dmYe');
		
    }

    public function getName()
    {
        return 'jarofgreen_wikicalendar_sameday';
    }
}


