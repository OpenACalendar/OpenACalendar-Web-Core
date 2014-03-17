<?php

namespace twig\extensions;

use Silex\Application;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
class TruncateExtension extends \Twig_Extension
{
    protected $app;

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
            'truncate' => new \Twig_Filter_Method($this, 'truncate'),
        );
    }

    public function truncate($data, $width=75)
    {
		if (mb_strlen($data) > $width) {
			return mb_substr($data, 0, $width)."...";
		} else {
			return $data;
		}
		
		return wordwrap($data, $width, $break, $cut);
    }

    public function getName()
    {
        return 'jarofgreen_wikicalendar_truncate';
    }
}

