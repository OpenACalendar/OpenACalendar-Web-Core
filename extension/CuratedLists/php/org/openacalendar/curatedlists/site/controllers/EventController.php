<?php


namespace org\openacalendar\curatedlists\site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController extends \site\controllers\EventController {




	
	function editCuratedLists($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Event does not exist.");
		}

		if ($this->parameters['event']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$clrb = new CuratedListRepositoryBuilder();
		$clrb->setSite($app['currentSite']);
		$clrb->setUserCanEdit($app['currentUser']);
		$clrb->setEventInformation($this->parameters['event']);
		$clrb->setIncludeDeleted(false);
		$this->parameters['curatedListsUserCanEdit'] = $clrb->fetchAll();


		return $app['twig']->render('site/event/edit.curatedlists.html.twig',$this->parameters);

		
	}

	
}


