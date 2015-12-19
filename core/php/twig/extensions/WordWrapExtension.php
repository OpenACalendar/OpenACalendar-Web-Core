<?php

namespace twig\extensions;

use Silex\Application;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
*/
class WordWrapExtension extends \Twig_Extension
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
            'wordwrap' => new \Twig_Filter_Method($this, 'wordwrap', array('is_safe' => array('html'))),
        );
    }

    public function wordwrap($data, $width=75, $break="\n", $cut=false)
    {
		// TODO this is not UTF-8 safe!
		return wordwrap($data, $width, $break, $cut);
    }

    public function getName()
    {
        return 'jarofgreen_wikicalendar_wordrap';
    }
}

