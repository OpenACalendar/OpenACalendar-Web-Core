<?php

namespace org\openacalendar\meetup;

use import\ImportURLHandlerBase;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLExpandShortenerHandler extends ImportURLHandlerBase
{

	public function getSortOrder() {
		return 100000;
	}

	public function isStopAfterHandling() {
		return false;
	}

	protected $newFeedURL;

	public function canHandle()
	{
		global $CONFIG;
		$urlBits = parse_url($this->importURLRun->getRealURL());

		if (in_array(strtolower($urlBits['host']), array('meetu.ps'))) {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->importURLRun->getRealUrl());
			curl_setopt($ch, CURLOPT_USERAGENT, 'OpenACalendar from ican.openacalendar.org, install '.$CONFIG->webIndexDomain);
			curl_exec($ch);
			$response = curl_getinfo( $ch );
			curl_close($ch);
			if ($response['http_code'] == 301 || $response['http_code'] == 302) {
				$this->newFeedURL = $response['redirect_url'];
				return true;
			}

		}

		return false;
	}

	public function getNewFeedURL() { return $this->newFeedURL; }

	public function handle() {
		if ($this->newFeedURL) {
			$this->importURLRun->setRealUrl($this->newFeedURL);
		}
	}

}
