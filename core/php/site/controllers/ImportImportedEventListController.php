<?php

namespace site\controllers;

use repositories\builders\filterparams\ImportedEventFilterParams;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportImportedEventListController extends ImportController {


	function index($slug,Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}


		$this->parameters['importedEventListFilterParams'] = new ImportedEventFilterParams();
		$this->parameters['importedEventListFilterParams']->set($_GET);
		$this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->setImport($this->parameters['import']);

		$this->parameters['importedEvents'] = $this->parameters['importedEventListFilterParams']->getImportedEventRepositoryBuilder()->fetchAll();

		return $app['twig']->render('site/importimportedeventlist/index.html.twig',$this->parameters);
	}
}

