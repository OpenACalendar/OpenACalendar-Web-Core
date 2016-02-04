<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\AreaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaListController  {

	public function listJson (Request $request, Application $app) {
		
		
		$arb = new AreaRepositoryBuilder($app);
		$arb->setSite($app['currentSite']);
				
		$ourRequest = new \Request($request);
		$arb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));
		
		$out = array ('areas'=> array());
		
		foreach($arb->fetchAll() as $area) {
			$out['areas'][] = array(
				'slug'=>$area->getSlug(),
				'slugForURL'=>$area->getSlugForUrl(),
				'title'=>$area->getTitle(),
			);
		}
		
		return json_encode($out);
		
	}
	
	
}
