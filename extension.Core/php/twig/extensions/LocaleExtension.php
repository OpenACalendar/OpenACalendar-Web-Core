<?php

namespace twig\extensions;

use Silex\Application;

/**
 * With help from https://matt.drollette.com/2012/07/user-specific-timezones-with-symfony2-and-twig-extensions/ - Thanks!
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LocaleExtension extends \Twig_Extension
{
    protected $app;
    protected $timezone;

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
            'tolocaltime' => new \Twig_Filter_Method($this, 'formatDatetime', array('is_safe' => array('html'))),
            'tolocaltimeformatted' => new \Twig_Filter_Method($this, 'formatDatetimeAsFormat', array('is_safe' => array('html'))),
            'tolocaltimeformatted12or24hourclock' => new \Twig_Filter_Method($this, 'formatDatetimeAsFormat1224HourClock', array('is_safe' => array('html'))),
        );
    }

    public function formatDatetime($date, $timezone)
    {

        if (!$date instanceof \DateTime) {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
            } else {
                $date = new \DateTime($date);
            }
        } else {
			$date = clone $date;
		}

        if (!$timezone instanceof \DateTimeZone) {
            $timezone = new \DateTimeZone($timezone);
        }
		
        $date->setTimezone($timezone);

		return $date;
    }


    public function formatDatetimeAsFormat($date, $format=null, $timezone = null) {
		return $this->formatDatetime($date, $timezone)->format($format);
    }
	
    public function formatDatetimeAsFormat1224HourClock($date, $format12=null, $format24=null, $clock12hour=true, $timezone = null) {
		if ($clock12hour) {
			return $this->formatDatetime($date, $timezone)->format($format12);
		} else {
			return $this->formatDatetime($date, $timezone)->format($format24);
		}
    }

    public function getName()
    {
        return 'jarofgreen_wikicalendar_locale';
    }
}