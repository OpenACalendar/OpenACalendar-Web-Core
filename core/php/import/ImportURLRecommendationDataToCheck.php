<?php

namespace import;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportURLRecommendationDataToCheck {

	protected $url;

	function __construct( $url ) {
		$this->url = $url;
	}


	/**
	 * @return mixed
	 */
	public function getUrl() {
		return $this->url;
	}



}
