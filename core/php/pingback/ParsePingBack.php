<?php

namespace pingback;
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParsePingBack {


	/** @return PingBack */
	static function parseFromData($data) {

		$sourceURL = null;
		$targetURL = null;

		$doc = new \DOMDocument();
		if (!$doc->loadXML($data)) {
			// TODO
			return;
		}


		$x = $doc->getElementsByTagName("param");
		$sourceURL = $x->item(0)->getElementsByTagName("value")->item(0)->getElementsByTagName("string")->item(0)->textContent;
		$targetURL = $x->item(1)->getElementsByTagName("value")->item(0)->getElementsByTagName("string")->item(0)->textContent;

		return new PingBack($sourceURL, $targetURL);

	}

}
