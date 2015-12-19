<?php

namespace site\controllers;

use Silex\Application;
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
		
		$mr = new MediaRepository();
		$this->parameters['media'] = $mr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['media']) {
			return false;
		}
		
		if ($this->parameters['media']->getIsDeleted()) {
			return false;
		}
		
		
		return true;
	}
	
	function show($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Media does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			if ($request->request->get('action') == 'makeSiteLogo' && $app['currentUserCanAdminSite']) {
				$app['currentSite']->setLogoMediaId($this->parameters['media']->getId());
				$siteProfileMediaRepository = new SiteProfileMediaRepository();
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
	
}


