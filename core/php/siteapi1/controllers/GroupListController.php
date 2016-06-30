<?php

namespace siteapi1\controllers;

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
class GroupListController {

	
	function json(Request $request, Application $app) {
		
		$groupRepoBuilder = new GroupRepositoryBuilder($app);
		$groupRepoBuilder->setSite($app['currentSite']);

        if (isset($_GET['titleSearch']) && trim($_GET['titleSearch'])) {
            $groupRepoBuilder->setTitleSearch($_GET['titleSearch']);
        }

		if (isset($_GET['search']) && trim($_GET['search'])) {
			$groupRepoBuilder->setFreeTextsearch($_GET['search']);
		}
		
		if (isset($_GET['includeDeleted'])) {
			if (in_array(strtolower($_GET['includeDeleted']),array('yes','on','1'))) {
				$groupRepoBuilder->setIncludeDeleted(true);
			} else if (in_array(strtolower($_GET['includeDeleted']),array('no','off','0'))) {
				$groupRepoBuilder->setIncludeDeleted(false);
			}
		}
		
		$out = array();
		
		foreach($groupRepoBuilder->fetchAll() as $group) {
			$out[] = array(
					'slug'=>$group->getSlug(),
					'title'=>$group->getTitle(),
				);
		}
		
		$response = new Response(json_encode(array('data'=>$out)));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
				
	}	

	
	
}

