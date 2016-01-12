<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDomainTest extends \BaseAppTest {
	
	
	function isCoveredByCookiesDataProvider() {
		return array(
			array('www.hasacalendar.co.uk','hasacalendar.co.uk',true),
			array('www.hasacalendar.co.uk','hasacalendar.com',false),
			array('hasadevcalendar.co.uk:20135','hasadevcalendar.co.uk',true),
			
		);
	}
	
	/**
	* @dataProvider isCoveredByCookiesDataProvider
	*/ 
	function testIsCoveredByCookies($domain, $cookieDomain, $result) {
		$this->app['config']->webCommonSessionDomain = $cookieDomain;
		$p = new \ParseDomain($this->app, $domain);
		$this->assertEquals($result, $p->isCoveredByCookies());
	}
}
	