<?php

namespace site\controllers;

use repositories\builders\filterparams\ImportedEventFilterParams;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportURLModel;
use repositories\ImportURLRepository;
use site\forms\ImportURLEditForm;
use repositories\builders\ImportURLResultRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\builders\AreaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLImportedEventListController extends ImportURLController {


	function index($slug,Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}


		$this->parameters['importedEventListFilterParams'] = new ImportedEventFilterParams();
		$this->parameters['importedEventListFilterParams']->set($_GET);
		$this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->setImportURL($this->parameters['importurl']);

		$this->parameters['importedEvents'] = $this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->fetchAll();

		return $app['twig']->render('site/importurlimportedeventlist/index.html.twig',$this->parameters);
	}
}

