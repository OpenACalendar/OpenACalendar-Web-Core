<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\GroupRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupListController  {
	
	public function listJson (Request $request, Application $app) {
		
		$grb = new GroupRepositoryBuilder($app);
		$grb->setSite($app['currentSite']);
		
		$ourRequest = new \Request($request);
		$grb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));
		
		$out = array ('groups'=> array());
		
		foreach($grb->fetchAll() as $group) {
			$out['groups'][] = array(
				'slug'=>$group->getSlug(),
				'slugForURL'=>$group->getSlugForUrl(),
				'title'=>$group->getTitle(),
			);
		}
		
		return json_encode($out);
		
	}
	
	
	
}

