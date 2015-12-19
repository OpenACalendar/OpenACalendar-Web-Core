<?php


namespace tests\eventcustomfields;
use BaseAppTest;
use models\EventCustomFieldDefinitionModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IsKeyValidTest extends BaseAppTest {

	function testData() {
		return array(
			array('cat',true),
			array('cat ',false),
			array(' cat',false),
			array('cat nip',false),
			array('cat_nip',true),
			array('cat_nip ',false),
			array('Cat_Nip',true),
			array('Cat.Nip',false),
			array('Cat-Nip',false),
			array('Cat=Nip',false),
			array('cat0',true),
			array('cat1',true),
			array('cat2',true),
		);
	}

	/**
	 * @dataProvider testData
	 */
	function testBooleanParam($in, $out) {
		$this->assertEquals($out,EventCustomFieldDefinitionModel::isKeyValid($in));
	}


}