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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupController extends \site\controllers\GroupController {

	function editCuratedLists($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		if ($this->parameters['group']->getIsDeleted()) {
			die("No"); // TODO
		}

		$clrb = new CuratedListRepositoryBuilder($app);
		$clrb->setSite($app['currentSite']);
		$clrb->setUserCanEdit($app['currentUser']);
		$clrb->setIncludeDeleted(false);
		$clrb->setGroupInformation($this->parameters['group']);
		$this->parameters['curatedListsUserCanEdit'] = $clrb->fetchAll();

		return $app['twig']->render('site/group/edit.curatedlists.html.twig',$this->parameters);

	}

}


