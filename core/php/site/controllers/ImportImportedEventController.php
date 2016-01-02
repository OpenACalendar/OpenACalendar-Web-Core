<?php

namespace site\controllers;

use repositories\builders\filterparams\EventFilterParams;
use repositories\ImportedEventRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\EventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportImportedEventController extends ImportController {



	protected function buildEvent($slug, $id, Request $request, Application $app) {
		if (!parent::build($slug, $request, $app)) {
			return false;
		}

		$repo = new ImportedEventRepository();
		$this->parameters['importedEvent'] = $repo->loadByImportIDAndId($this->parameters['import']->getId(), $id);
		if (!$this->parameters['importedEvent']) {
			return false;
		}


		return true;
	}

	function index($slug,$id, Request $request, Application $app) {
		if (!$this->buildEvent($slug, $id, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}

		if ($this->parameters['importedEvent']->hasReoccurence()) {

			$this->parameters['importedEventReoccurs'] = true;

			$this->parameters['eventListFilterParams'] = new EventFilterParams();
			// set some defaults that are different from normal
			$this->parameters['eventListFilterParams']->setIncludeDeleted(true);
			$this->parameters['eventListFilterParams']->setFromNow(false);
			// now carry on ...
			$this->parameters['eventListFilterParams']->set($_GET);
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setSite($app['currentSite']);
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
			// Technically we should be able to do this ... but to keep the UI simple, lets not.
			//if ($app['currentUser'])) {
			//	$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
			//}
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setImportedEvent($this->parameters['importedEvent']);

			$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();

			$this->parameters['event'] = null;

		} else {

			$this->parameters['importedEventReoccurs'] = false;
			$this->parameters['eventListFilterParams'] = null;
			$this->parameters['events'] = null;

			$eventRepo = new EventRepository();
			$this->parameters['event'] = $eventRepo->loadByImportedEvent($this->parameters['importedEvent']);

		}


		return $app['twig']->render('site/importimportedevent/index.html.twig',$this->parameters);
	}
}

