<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\CountryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryListController   {
	
	public function listJson (Request $request, Application $app) {
		
		$crb = new CountryRepositoryBuilder($app);
		$crb->setSiteIn($app['currentSite']);
		
		
		$out = array ('countries'=> array());
		
		foreach($crb->fetchAll() as $country) {
			$out['countries'][] = array(
				'slug'=>$country->getTwoCharCode(),
				'title'=>$country->getTitle(),
			);
		}
		
		return json_encode($out);
		
	}
	
	
}

