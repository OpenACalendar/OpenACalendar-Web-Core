<?php

namespace site\controllers;

use models\MediaEditMetaDataModel;
use Silex\Application;
use site\forms\MediaEditForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\GroupModel;
use models\MediaModel;
use repositories\MediaRepository;
use repositories\SiteProfileMediaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();
		
		$mr = new MediaRepository($app);
		$this->parameters['media'] = $mr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['media']) {
			return false;
		}
		
		if ($this->parameters['media']->getIsDeleted()) {
			return false;
		}

		$app['currentUserActions']->set("org.openacalendar","mediaEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","MEDIAS_CHANGE")
			&& !$this->parameters['media']->getIsDeleted());

		return true;
	}
	
	function show($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			if ($request->request->get('action') == 'makeSiteLogo' && $app['currentUserCanAdminSite']) {
				$app['currentSite']->setLogoMediaId($this->parameters['media']->getId());
				$siteProfileMediaRepository = new SiteProfileMediaRepository($app);
				$siteProfileMediaRepository->createOrEdit($app['currentSite'], $app['currentUser']);
				$app['flashmessages']->addMessage("Saved.");
				return $app->redirect("/media/".$this->parameters['media']->getSlug());
			}
		}
		
		
		return $app['twig']->render('site/media/show.html.twig', $this->parameters);
	}
	
	function imageThumbnail($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}
		
		return $this->parameters['media']->getThumbnailResponse($app['config']->mediaBrowserCacheExpiresInseconds);
	}
	
	function imageNormal($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}
		
		return $this->parameters['media']->getNormalResponse($app['config']->mediaBrowserCacheExpiresInseconds);
	}
	
	function imageFull($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}
		
		return $this->parameters['media']->getResponse($app['config']->mediaBrowserCacheExpiresInseconds);		
	}

	function editDetails($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}

		if ($this->parameters['media']->getIsDeleted()) {
			die("No"); // TODO
		}

		$form = $app['form.factory']->create(new MediaEditForm($app), $this->parameters['media']);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$mediaEditMetaDataModel = new MediaEditMetaDataModel();
				$mediaEditMetaDataModel->setUserAccount($app['currentUser']);
				$mediaEditMetaDataModel->setFromRequest($request);

				$mediaRepository = new MediaRepository($app);
				$mediaRepository->editWithMetaData($this->parameters['media'], $mediaEditMetaDataModel);

				return $app->redirect("/media/".$this->parameters['media']->getSlugForUrl());

			}
		}


		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/media/edit.details.html.twig', $this->parameters);

	}
}


