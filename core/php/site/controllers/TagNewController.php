<?php

namespace site\controllers;

use Silex\Application;
use site\forms\TagNewForm;
use site\forms\TagEditForm;
use site\forms\EventNewForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\TagModel;
use models\EventModel;
use repositories\TagRepository;


/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class TagNewController {
	
	
	function newTag(Request $request, Application $app) {
		
		$tag = new TagModel();
		
		$form = $app['form.factory']->create(new TagNewForm($request->query->get('title')), $tag);



		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$tagRepository = new TagRepository($app);
				$tagRepository->create($tag, $app['currentSite'], $app['currentUser']);
				
				return $app->redirect("/tag/".$tag->getSlugForUrl());
				
			}
		}
		
		
		return $app['twig']->render('site/tagnew/new.html.twig', array(
				'form'=>$form->createView(),
			));
		
	}
	
}


