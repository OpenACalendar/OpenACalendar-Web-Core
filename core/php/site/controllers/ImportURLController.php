<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportURLModel;
use repositories\ImportURLRepository;
use site\forms\ImportURLEditForm;
use repositories\builders\ImportURLResultRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\EventRepository;
use repositories\GroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLController {

	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();

		$iurlRepository = new ImportURLRepository();
		$this->parameters['importurl'] =  $iurlRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['importurl']) {
			return false;
		}
		
		
		if ($this->parameters['importurl']->getGroupId()) {
			$gr = new GroupRepository();
			$this->parameters['group'] = $gr->loadById($this->parameters['importurl']->getGroupId());
		}
		
		return true;

	}

	function show($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}

		$irr = new ImportURLResultRepositoryBuilder();
		$irr->setImportURL($this->parameters['importurl']);
		$this->parameters['hasLogEntries'] = (boolean)(count($irr->fetchAll()) > 0);

		return $app['twig']->render('site/importurl/show.html.twig',$this->parameters);
	}

	function log($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		$irr = new ImportURLResultRepositoryBuilder();
		$irr->setImportURL($this->parameters['importurl']);
		$this->parameters['results'] = $irr->fetchAll();
			
		
		return $app['twig']->render('site/importurl/log.html.twig',$this->parameters);
	}

	function edit($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		
		
		$form = $app['form.factory']->create(new ImportURLEditForm(), $this->parameters['importurl']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$iRepository = new ImportURLRepository();
				$iRepository->edit($this->parameters['importurl'], userGetCurrent());
				
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		
		
		return $app['twig']->render('site/importurl/edit.html.twig',$this->parameters);
	}

	function enable($slug,Request $request, Application $app) {
		global $WEBSESSION;

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if ($this->parameters['importurl']->getIsEnabled() && !$this->parameters['importurl']->getIsExpired()) {
			die ('NO'); // TODO
		}
		
		$iRepository = new ImportURLRepository();
		$this->parameters['clashimporturl'] = $iRepository->loadClashForImportUrl($this->parameters['importurl']);
		if ($this->parameters['clashimporturl']) {
			return $app['twig']->render('site/importurl/enable.clash.html.twig',$this->parameters);
		}
		
		if (isset($_POST['enable']) && $_POST['enable'] == 'yes' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
				$iRepository->enable($this->parameters['importurl'], userGetCurrent());
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
		}
		
		return $app['twig']->render('site/importurl/enable.html.twig',$this->parameters);
	}

	function disable($slug,Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if (!$this->parameters['importurl']->getIsEnabled()) {
			die ('NO'); // TODO
		}
		
		if (isset($_POST['disable']) && $_POST['disable'] == 'yes' && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
				$iRepository = new ImportURLRepository();
				$iRepository->disable($this->parameters['importurl'], userGetCurrent());
				
				$erb = new EventRepositoryBuilder();
				$erb->setAfterNow();
				$erb->setIncludeDeleted(false);
				$erb->setImportURL($this->parameters['importurl']);
				$eventRepository = new EventRepository();
				foreach($erb->fetchAll() as $event) {
					$eventRepository->delete($event, userGetCurrent());
				}
				
				
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
		}
		
		return $app['twig']->render('site/importurl/disable.html.twig',$this->parameters);
	}
}

