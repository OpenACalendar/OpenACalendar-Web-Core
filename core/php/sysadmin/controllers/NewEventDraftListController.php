<?php

namespace sysadmin\controllers;

use repositories\builders\NewEventDraftRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\MediaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventDraftListController
{


	function listForSite($siteid, Request $request, Application $app) {


		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);

		if (!$site) {
			die("404");
		}

		$nedrb = new NewEventDraftRepositoryBuilder();
		$nedrb->setSite($site);
		$drafts = $nedrb->fetchAll();

		return $app['twig']->render('sysadmin/neweventdraftlist/index.html.twig', array(
			'site'=>$site,
			'neweventdrafts'=>$drafts,
		));

	}


}
