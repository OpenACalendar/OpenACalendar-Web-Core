<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\CuratedListModel;
use repositories\CuratedListRepository;
use repositories\builders\filterparams\EventFilterParams;
use site\forms\CuratedListNewForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListNewController {

	
	
	function newCuratedList(Request $request, Application $app) {
		
		$curatedList = new CuratedListModel();
		
		$form = $app['form.factory']->create(new CuratedListNewForm(), $curatedList);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$clRepository = new CuratedListRepository();
				$clRepository->create($curatedList, $app['currentSite'], $app['currentUser']);
				
				return $app->redirect("/curatedlist/".$curatedList->getSlug());
				
			}
		}
		
		
		return $app['twig']->render('site/curatedlistnew/new.html.twig', array(
				'form'=>$form->createView(),
			));
		
	}
	
}

