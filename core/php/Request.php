<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class Request {
	
	/** @var Symfony\Component\HttpFoundation\Request **/
	protected $symfonyRequest;
	
	function __construct(Symfony\Component\HttpFoundation\Request $symfonyRequest) {
		$this->symfonyRequest = $symfonyRequest;
	}
	
	function hasGetOrPost($key) {
		return $this->symfonyRequest->query->has($key) || 
				$this->symfonyRequest->request->has($key);
	}	
	
	function getGetOrPostString($key, $default) {
		if ($this->symfonyRequest->query->has($key)) {
			return $this->symfonyRequest->query->get($key);
		} else if ($this->symfonyRequest->request->has($key)) {
			return $this->symfonyRequest->request->get($key);
		} else {
			return $default;
		}
	}
	
	function getGetOrPostBoolean($key, $default) {
		if ($this->symfonyRequest->query->has($key)) {
			$value = strtolower(trim($this->symfonyRequest->query->get($key)));
			return substr($value,0,2) == 'on'
				|| in_array( substr($value,0,1), array('1','t','y'));
		} else if ($this->symfonyRequest->request->has($key)) {
			$value = strtolower(trim($this->symfonyRequest->request->get($key)));
			return substr($value,0,2) == 'on'
				|| in_array( substr($value,0,1), array('1','t','y'));
		} else {
			return $default;
		}
	}

	
	
	
}
