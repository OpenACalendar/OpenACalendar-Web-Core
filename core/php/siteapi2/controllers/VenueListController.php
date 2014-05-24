<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueListController 
{
	
	public function listJson (Request $request, Application $app) {
		
		$vrb = new VenueRepositoryBuilder();
		$vrb->setSite($app['currentSite']);
				
		$ourRequest = new \Request($request);
		$vrb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));
		
		$out = array ('venues'=> array());
		
		foreach($vrb->fetchAll() as $venue) {
			$out['venues'][] = array(
				'slug'=>$venue->getSlug(),
				'slugForURL'=>$venue->getSlugForUrl(),
				'title'=>$venue->getTitle(),
			);
		}
		
		return json_encode($out);		
		
		
	}
	
}


