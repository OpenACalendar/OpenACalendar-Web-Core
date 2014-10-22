<?php

use symfony\form\MagicUrlTypeFixer;

use Symfony\Component\Form\FormEvent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MagicUrlTypeFixerTest extends \PHPUnit_Framework_TestCase {

	function dataForTest1() {
		return array(
			array("google.co.uk", "http://google.co.uk"),
			array("http://google.co.uk", "http://google.co.uk"),
			array("https://google.co.uk", "https://google.co.uk"),
			array("james@example.com", "mailto:james@example.com"),
			array("mailto:james@example.com", "mailto:james@example.com"),
		);
	}

	/**
	 * @dataProvider dataForTest1
	 */
	function test1($in, $out) {
		$urlTypeFixer = new MagicUrlTypeFixer();
		$event = new DummyFormEvent($in);
		$urlTypeFixer->onSubmit($event);
		$this->assertEquals($out, $event->getData());
	}




}

class DummyFormEvent extends Symfony\Component\Form\FormEvent {
	public function __construct( $data)
	{
		$this->data = $data;
	}
}

