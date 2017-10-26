<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\VenueRepository;
use sysadmin\forms\ActionWithCommentForm;
use sysadmin\ActionParser;
use repositories\builders\SysadminCommentRepositoryBuilder;
use repositories\SysAdminCommentRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository($app);
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$vr = new VenueRepository($app);
		$this->parameters['venue'] = $vr->loadBySlug($this->parameters['site'], $slug);
		if (!$this->parameters['venue']) {
			$app->abort(404);
		}

		$this->parameters['venueisduplicateof'] = $this->parameters['venue']->getIsDuplicateOfId() ? $vr->loadById($this->parameters['venue']->getIsDuplicateOfId()) : null;

	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
		$form = $app['form.factory']->create( ActionWithCommentForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);


				$redirect = false;

				if ($data['comment']) {
					$scr = new SysAdminCommentRepository($app);
					$scr->createAboutVenue($this->parameters['venue'], $data['comment'], $app['currentUser']);
					$redirect = true;
				}


				if ($action->getCommand() == 'delete' && !$this->parameters['venue']->getIsDeleted()) {
					$vr = new VenueRepository($app);
					$vr->delete($this->parameters['venue'],  $app['currentUser']);
					$redirect = true;

				} else if ($action->getCommand() == 'undelete' && $this->parameters['venue']->getIsDeleted()) {
					$this->parameters['venue']->setIsDeleted(false);
					$vr = new VenueRepository($app);
					$vr->undelete($this->parameters['venue'],  $app['currentUser']);
					$redirect = true;

				} else if ($action->getCommand() == 'isduplicateof') {
					$vr = new VenueRepository($app);
					$originalVenue = $vr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalVenue && $originalVenue->getId() != $this->parameters['venue']->getId()) {
						$vr->markDuplicate($this->parameters['venue'], $originalVenue, $app['currentUser']);
						$redirect = true;
					}

                } else if ($action->getCommand() == 'slughuman') {

                    $this->parameters['venue']->setSlugHuman($action->getParam(0));
                    $vr = new VenueRepository($app);
                    $vr->editSlugHuman($this->parameters['venue']);
                    $redirect = true;

				} else if ($action->getCommand() == 'purge' && $app['config']->sysAdminExtraPurgeVenuePassword && $app['config']->sysAdminExtraPurgeVenuePassword == $action->getParam(0)) {

					$vr = new VenueRepository($app);
					$vr->purge($this->parameters['venue']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/venue/');

				}

				if ($redirect) {
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/venue/'.$this->parameters['venue']->getSlug());

				}
			}
		}
		
		$this->parameters['form'] = $form->createView();


		$sacrb = new SysadminCommentRepositoryBuilder($app);
		$sacrb->setVenue($this->parameters['venue']);
		$this->parameters['comments'] = $sacrb->fetchAll();
			
		
		return $app['twig']->render('sysadmin/venue/index.html.twig', $this->parameters);		
	
	}
	
	
}


