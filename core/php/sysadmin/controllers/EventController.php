<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\CountryRepository;
use repositories\VenueRepository;
use repositories\CuratedListRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null,'venue'=>null,'country'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}

		$er = new EventRepository();
		$this->parameters['event'] = $er->loadBySlug($this->parameters['site'], $slug);

		$this->parameters['eventisduplicateof'] = $this->parameters['event']->getIsDuplicateOfId() ? $er->loadById($this->parameters['event']->getIsDuplicateOfId()) : null;

		if (!$this->parameters['event']) {
			$app->abort(404);
		}
	
		if ($this->parameters['event']->getGroupId()) {
			$gr = new GroupRepository();
			$this->parameters['group'] = $gr->loadById($this->parameters['event']->getGroupId());
		}
		
		if ($this->parameters['event']->getCountryID()) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['event']->getCountryID());
		}
		
		if ($this->parameters['event']->getVenueID()) {
			$cr = new VenueRepository();
			$this->parameters['venue'] = $cr->loadById($this->parameters['event']->getVenueID());
		}
		
	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
			
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);
			
				if ($action->getCommand() == 'addgroup') {
					$gr = new GroupRepository();
					$group = $gr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($group) {
						$gr->addEventToGroup($this->parameters['event'], $group, userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					}
				} else if ($action->getCommand() == 'removegroup') {
					$gr = new GroupRepository();
					$group = $gr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($group) {
						$gr->removeEventFromGroup($this->parameters['event'], $group, userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					}
				} else if ($action->getCommand() == 'maingroup') {
					$gr = new GroupRepository();
					$group = $gr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($group) {
						$gr->setMainGroupForEvent($group, $this->parameters['event'], userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					}
				} else if ($action->getCommand() == 'delete' && !$this->parameters['event']->getIsDeleted()) {
					$er = new EventRepository();
					$er->delete($this->parameters['event'],  userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					
				} else if ($action->getCommand() == 'undelete' && $this->parameters['event']->getIsDeleted()) {
					$this->parameters['event']->setIsDeleted(false);
					$er = new EventRepository();
					$er->edit($this->parameters['event'],  userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					
				} else if ($action->getCommand() == 'addcuratedlist') {
					$clr = new CuratedListRepository();
					$curatedList = $clr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($curatedList) {
						$clr->addEventtoCuratedList($this->parameters['event'], $curatedList, userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/curatedlist/'.$curatedList->getSlug());
					}
					
				} else if ($action->getCommand() == 'removecuratedlist') {
					$clr = new CuratedListRepository();
					$curatedList = $clr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($curatedList) {
						$clr->removeEventFromCuratedList($this->parameters['event'], $curatedList, userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/curatedlist/'.$curatedList->getSlug());
					}

				} else if ($action->getCommand() == 'isduplicateof') {

					$er = new EventRepository();
					$originalEvent = $er->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalEvent) {
						$er->markDuplicate($this->parameters['event'], $originalEvent, userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/event/'.$this->parameters['event']->getSlug());
					}

				}
		
			}
			
		}
		
		$groupRB = new GroupRepositoryBuilder();
		$groupRB->setEvent($this->parameters['event']);
		$this->parameters['groups'] = $groupRB->fetchAll();
		
		$this->parameters['form'] = $form->createView();
			
		
		return $app['twig']->render('sysadmin/event/index.html.twig', $this->parameters);		
	
	}
	
	
}


