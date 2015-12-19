<?php

namespace twig\extensions;

use Silex\Application;


/**
 * Takes Link. Returns a shortened form to show to user.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LinkInfoExtension extends \Twig_Extension
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
            'linkinfo' => new \Twig_Filter_Method($this, 'linkinfo', array()),
        );
    }

    public function linkinfo($text) {
		$data = parse_url($text);
		return isset($data['host']) ? $data['host'] : $text;
    }

    public function getName()
    {
        return 'jarofgreen_wikicalendar_linkinfo';
    }
}