<?php

namespace twig\extensions;

use Silex\Application;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
use models\TagHistoryModel;


/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TypeCheckExtension extends \Twig_Extension
{
    protected $app;

    public function __construct(Application $app = null)
    {
        $this->app = $app;
    }

	public function getTests() {
		return array(
			new \Twig_SimpleTest('eventhistory',function($item) {  return $item instanceof EventHistoryModel; }),
			new \Twig_SimpleTest('grouphistory',function($item) {  return $item instanceof GroupHistoryModel; }),
			new \Twig_SimpleTest('venuehistory',function($item) {  return $item instanceof VenueHistoryModel; }),
			new \Twig_SimpleTest('areahistory',function($item) {  return $item instanceof AreaHistoryModel; }),
			new \Twig_SimpleTest('taghistory',function($item) {  return $item instanceof TagHistoryModel; }),
		);
	}

    public function getName()
    {
        return 'jarofgreen_wikicalendar_typecheck';
    }
}
