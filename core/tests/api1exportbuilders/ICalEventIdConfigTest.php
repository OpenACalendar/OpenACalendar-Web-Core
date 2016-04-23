<?php


namespace tests\api1exportbuilders;

use api1exportbuilders\ICalEventIdConfig;
use BaseAppTest;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ICalEventIdConfigTest extends BaseAppTest {


	public function dataDefault() {
		return array(
			array(null, array()),
			array('an adorable kitten', array()),
			array(null, array('HTTP_USER_AGENT'=>'wget')),
		);
	}

	/**
	 * @dataProvider dataDefault
	 */
	public function testDefault($option, $server) {
		$iCalEventIdConfig = new ICalEventIdConfig($option, $server);
		$this->assertTrue($iCalEventIdConfig->isSlug());
		$this->assertFalse($iCalEventIdConfig->isSlugStartEnd());
	}

	public function dataForTestSlugSetByOption() {
		return array(
			array('slug'),
			array('SLUG'),
			array('   slUG  '),
		);
	}

	/**
	* @dataProvider dataForTestSlugSetByOption
	*/
	public function testSlugSetbyOption($in) {
		$iCalEventIdConfig = new ICalEventIdConfig($in);
		$this->assertTrue($iCalEventIdConfig->isSlug());
		$this->assertFalse($iCalEventIdConfig->isSlugStartEnd());
	}

	public function dataForTestSlugStartEndSetByOption() {
		return array(
			array('slugstartend'),
			array('SLUGSTARTEND'),
			array('   slUGstARTenD  '),
		);
	}

	/**
	* @dataProvider dataForTestSlugStartEndSetByOption
	*/
	public function testSlugStartEndSetbyOption($in) {
		$iCalEventIdConfig = new ICalEventIdConfig($in);
		$this->assertFalse($iCalEventIdConfig->isSlug());
		$this->assertTrue($iCalEventIdConfig->isSlugStartEnd());
	}

	public function testSlugStartEndSetbyUserAgent() {
		$iCalEventIdConfig = new ICalEventIdConfig(null, array('HTTP_USER_AGENT'=>'Google-Calendar-Importer'));
		$this->assertFalse($iCalEventIdConfig->isSlug());
		$this->assertTrue($iCalEventIdConfig->isSlugStartEnd());
	}

}


