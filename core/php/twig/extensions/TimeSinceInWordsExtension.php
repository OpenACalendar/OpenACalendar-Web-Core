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
class TimeSinceInWordsExtension  extends \Twig_Extension {

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
            'timesinceinwords' => new \Twig_Filter_Method($this, 'timeSinceInWords'),
        );
    }

    public function timeSinceInWords($data)
    {
		$diff = \TimeSource::time() - $data->getTimestamp();
		if ($diff < 0) {
			return "in the future";
		} elseif ($diff == 0) {
			return "now";
		} elseif ($diff == 1) {
			return "1 second ago";
		} elseif ($diff < 60) {
			return $diff." seconds ago";
		} elseif ($diff < 60*2) {
			return "1 minute ago";
		} elseif ($diff < 60*60) {
			return round($diff / 60)." minutes ago";
		} elseif ($diff < 60*60*2) {
			return "1 hour ago";
		} elseif ($diff < 60*60*24) {
			return round($diff / (60*60))." hours ago";
		} elseif ($diff < 60*60*24*2) {
			return "1 day ago";
		} else {
			return round($diff / (60*60*24))." days ago";
		}
		
    }


    public function getName()
    {
        return 'jarofgreen_wikicalendar_timesinceinwords';
    }
}


