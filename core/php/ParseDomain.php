<?php
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseDomain {

    /** @var Application */
    protected $app;

	protected $currentDomain;

    public function __construct(Application $application, $currentDomain) {
        $this->currentDomain = $currentDomain;
        $this->app = $application;
    }
	
	public function isCoveredByCookies() {
		$matchAgainst = $this->stripPort($this->app['config']->webCommonSessionDomain);
		$bit = substr($this->stripPort($this->currentDomain), -strlen($matchAgainst));
		if (strtolower($bit) == strtolower($matchAgainst)) {
			return true;
		}
		
		return false;
	}
	
	protected function stripPort($in) {
		$bits = explode(":",$in,2);
		return $bits[0];
	}
	
	
}