<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\IncomingLinkRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IncomingLinkListController {


	function listForSite($siteid, Request $request, Application $app) {


		$sr = new SiteRepository($app);
		$site = $sr->loadById($siteid);

		if (!$site) {
			die("404");
		}

		$ilrb = new IncomingLinkRepositoryBuilder($app);
		$ilrb->setSite($site);
		$incominglinks = $ilrb->fetchAll();

		return $app['twig']->render('sysadmin/incominglinklist/forSite.html.twig', array(
			'site'=>$site,
			'incominglinks'=>$incominglinks,
		));

	}


}


