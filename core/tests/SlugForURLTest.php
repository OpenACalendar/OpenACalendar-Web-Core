<?php

use models\GroupModel;
use models\AreaModel;
use models\CuratedListModel;
use models\EventModel;
use models\VenueModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SlugForUrlTest extends \PHPUnit_Framework_TestCase {

	function dataForTestSet() {
		return array(
				array(1,'cat','1-cat'),
				array(2,'@cat','2-cat'),
				array(3,'cat dog','3-cat-dog'),
				array(4,'','4'),
				array(5,'café','5-cafe'),
				array(6,'cafe meetup - bob\'s group','6-cafe-meetup-bobs-group'),
			);
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet1($slug, $text, $result) {
		$area = new AreaModel();
		$area->setSlug($slug);
		$area->setTitle($text);
		$this->assertEquals($result, $area->getSlugForUrl());
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet2($slug, $text, $result) {
		$group = new GroupModel();
		$group->setSlug($slug);
		$group->setTitle($text);
		$this->assertEquals($result, $group->getSlugForUrl());
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet3($slug, $text, $result) {
		$venue = new VenueModel();
		$venue->setSlug($slug);
		$venue->setTitle($text);
		$this->assertEquals($result, $venue->getSlugForUrl());
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet4($slug, $text, $result) {
		$event = new EventModel();
		$event->setSlug($slug);
		$event->setSummary($text);
		$this->assertEquals($result, $event->getSlugForUrl());
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet5($slug, $text, $result) {
		$curatedlist = new CuratedListModel();
		$curatedlist->setSlug($slug);
		$curatedlist->setTitle($text);
		$this->assertEquals($result, $curatedlist->getSlugForUrl());
	}
	
}

