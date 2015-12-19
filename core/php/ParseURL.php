<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseURL {

	protected $url;
	
	public function __construct($url) {
		$this->url = $url;
	}
	
	public function getCanonical() {
		$data = parse_url($this->url);
		
		$url = ($data['scheme'] ? $data['scheme'].":":'') . '//';
		if ((isset($data['username']) && $data['username']) || (isset($data['password']) && $data['password'])) {
			$url .= $data['username'] . ":" . $data['password'] . "@";
		}
		$url .= strtolower($data['host']).(isset($data['path'])?$data['path']:'/').'?'.(isset($data['query']) ? $data['query'] : '');
		
		return $url;
	}
	
}



