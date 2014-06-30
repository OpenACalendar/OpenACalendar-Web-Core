<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\TagRepository;
use repositories\builders\filterparams\EventFilterParams;
use site\forms\AdminTagEditForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminTagController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array('currentUserWatchesGroup'=>false);
		
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$tr = new TagRepository();
		$this->parameters['tag'] = $tr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['tag']) {
			return false;
		}
		
		
		return true;
	}
	
	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}
		
			
		$this->parameters['eventListFilterParams'] = new EventFilterParams();
		$this->parameters['eventListFilterParams']->set($_GET);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setTag($this->parameters['tag']);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		if (userGetCurrent()) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
		}
		
		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();
		
		
		return $app['twig']->render('site/admintag/show.html.twig', $this->parameters);
	}
	
	
	function edit($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}
		
				
		$form = $app['form.factory']->create(new AdminTagEditForm(), $this->parameters['tag']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$tagRepository = new TagRepository();
				$tagRepository->edit($this->parameters['tag'], userGetCurrent());
				
				return $app->redirect("/admin/tag/".$this->parameters['tag']->getSlugForUrl());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
			
		
		return $app['twig']->render('site/admintag/edit.html.twig', $this->parameters);
	}
	
	
	function delete($slug, Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}
		
		if ($this->parameters['tag']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($request->request->get('delete') == 'yes' && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			$tagRepository = new TagRepository();
			$tagRepository->delete($this->parameters['tag'], userGetCurrent());
			return $app->redirect("/admin/tag/".$this->parameters['tag']->getSlugForUrl());
		}
		
		return $app['twig']->render('site/admintag/delete.html.twig', $this->parameters);
	
	}
	
	
	function undelete($slug, Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Tag does not exist.");
		}
	
		if (!$this->parameters['tag']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		if ($request->request->get('undelete') == 'yes' && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			$this->parameters['tag']->setIsDeleted(false);
			$tagRepository = new TagRepository();
			$tagRepository->edit($this->parameters['tag'], userGetCurrent());
			return $app->redirect("/admin/tag/".$this->parameters['tag']->getSlugForUrl());
		}
		
		return $app['twig']->render('site/admintag/undelete.html.twig', $this->parameters);
	
	}
	
	
		
}



