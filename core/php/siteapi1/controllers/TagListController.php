<?php

namespace siteapi1\controllers;

use repositories\builders\TagRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagListController {

	
	function json(Request $request, Application $app) {
		
		$tagRepoBuilder = new TagRepositoryBuilder($app);
		$tagRepoBuilder->setSite($app['currentSite']);
		
		if (isset($_GET['titleSearch']) && trim($_GET['titleSearch'])) {
			$tagRepoBuilder->setTitleSearch($_GET['titleSearch']);
		}
		
		if (isset($_GET['includeDeleted'])) {
			if (in_array(strtolower($_GET['includeDeleted']),array('yes','on','1'))) {
				$tagRepoBuilder->setIncludeDeleted(true);
			} else if (in_array(strtolower($_GET['includeDeleted']),array('no','off','0'))) {
				$tagRepoBuilder->setIncludeDeleted(false);
			}
		}

        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $tagRepoBuilder->setLimit(intval($_GET['limit']));
        } else {
            $tagRepoBuilder->setLimit($app['config']->api1TagListLimit);
        }
		
		$out = array();
		
		foreach($tagRepoBuilder->fetchAll() as $tag) {
			$out[] = array(
					'slug'=>$tag->getSlug(),
					'title'=>$tag->getTitle(),
				);
		}
		
		$response = new Response(json_encode(array('data'=>$out)));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
				
	}	

	
	
}

