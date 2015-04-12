<?php

namespace sysadmin\controllers;

use models\UserAccountModel;
use repositories\builders\NewEventDraftRepositoryBuilder;
use repositories\EventRepository;
use repositories\NewEventDraftRepository;
use repositories\UserAccountRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\MediaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventDraftController
{


	protected $parameters = array();

	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('user'=>null,'eventCreated'=>null, 'eventDupe'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);

		if (!$this->parameters['site']) {
			$app->abort(404);
		}

		$repo = new NewEventDraftRepository();
		$this->parameters['draft'] = $repo->loadBySlugForSite($slug, $this->parameters['site']);

		if (!$this->parameters['draft']) {
			$app->abort(404);
		}

		if ($this->parameters['draft']->getUserAccountId()) {
			$ur = new UserAccountRepository();
			$this->parameters['user'] = $ur->loadByID($this->parameters['draft']->getUserAccountId());
		}

		if ($this->parameters['draft']->getEventId()) {
			$er = new EventRepository();
			$this->parameters['eventCreated'] = $er->loadByID($this->parameters['draft']->getEventId());
		}

		if ($this->parameters['draft']->getWasExistingEventId()) {
			$er = new EventRepository();
			$this->parameters['eventDupe'] = $er->loadByID($this->parameters['draft']->getWasExistingEventId());
		}

	}

	function show($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);

		return $app['twig']->render('sysadmin/neweventdraft/index.html.twig', $this->parameters);

	}

}

