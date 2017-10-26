<?php

namespace sysadmin\controllers;

use repositories\UserAccountRepository;
use repositories\UserWatchesGroupRepository;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\builders\UserAccountRepositoryBuilder;
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
class GroupController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository($app);
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$gr = new GroupRepository($app);
		$this->parameters['group'] = $gr->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['group']) {
			$app->abort(404);
		}

		$this->parameters['groupisduplicateof'] = $this->parameters['group']->getIsDuplicateOfId() ? $gr->loadById($this->parameters['group']->getIsDuplicateOfId()) : null;

        $sacrb = new SysadminCommentRepositoryBuilder($app);
        $sacrb->setGroup($this->parameters['group']);
        $this->parameters['comments'] = $sacrb->fetchAll();
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
					$scr->createAboutGroup($this->parameters['group'], $data['comment'], $app['currentUser']);
					$redirect = true;
				}


				if ($action->getCommand() == 'delete' && !$this->parameters['group']->getIsDeleted()) {
					$gr = new GroupRepository($app);
					$gr->delete($this->parameters['group'],  $app['currentUser']);
					$redirect = true;
				} else if ($action->getCommand() == 'undelete' && $this->parameters['group']->getIsDeleted()) {
					$this->parameters['group']->setIsDeleted(false);
					$gr = new GroupRepository($app);
					$gr->undelete($this->parameters['group'],  $app['currentUser']);
					$redirect = true;
				} else if ($action->getCommand() == 'isduplicateof') {
					$gr = new GroupRepository($app);
					$originalGroup = $gr->loadBySlug($this->parameters['site'], $action->getParam(0));
					if ($originalGroup && $originalGroup->getId() != $this->parameters['group']->getId()) {
						$gr->markDuplicate($this->parameters['group'], $originalGroup, $app['currentUser']);
						$redirect = true;
					}

                } else if ($action->getCommand() == 'slughuman') {

                    $this->parameters['group']->setSlugHuman($action->getParam(0));
                    $gr = new GroupRepository($app);
                    $gr->editSlugHuman($this->parameters['group']);
                    $redirect = true;

				} else if ($action->getCommand() == 'purge' && $app['config']->sysAdminExtraPurgeGroupPassword && $app['config']->sysAdminExtraPurgeGroupPassword == $action->getParam(0)) {

					$gr = new GroupRepository($app);
					$gr->purge($this->parameters['group']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/');

				}

				if ($redirect) {

					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/'.$this->parameters['group']->getSlug());
				}
			}
		}
		
		$this->parameters['form'] = $form->createView();

		
		return $app['twig']->render('sysadmin/group/index.html.twig', $this->parameters);		
	
	}
	
	
	function watchers($siteid, $slug, Request $request, Application $app) {

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
                    $scr->createAboutGroup($this->parameters['group'], $data['comment'], $app['currentUser']);
                    $redirect = true;
                }

                if ($action->getCommand() == 'add') {

                    $userRepo = new UserAccountRepository( $app );
                    $user     = $userRepo->loadByEmail( $action->getParam( 0 ) );
                    if ( $user ) {
                        $userWatchesGroupRepo = new UserWatchesGroupRepository( $app );
                        $userWatchesGroupRepo->startUserWatchingGroupIfNotWatchedBefore( $user, $this->parameters['group'] );
                        $redirect = true;
                    }

                } elseif ($action->getCommand() == 'addevenifstoppedbefore') {

                    $userRepo = new UserAccountRepository($app);
                    $user = $userRepo->loadByEmail($action->getParam(0));
                    if ($user) {
                        $userWatchesGroupRepo = new UserWatchesGroupRepository($app);
                        $userWatchesGroupRepo->startUserWatchingGroup($user, $this->parameters['group']);
                        $redirect = true;
                    }

                } else if ($action->getCommand() == 'remove') {

                    $userRepo = new UserAccountRepository($app);
                    $user = $userRepo->loadByEmail($action->getParam(0));
                    if ($user) {
                        $userWatchesGroupRepo = new UserWatchesGroupRepository($app);
                        $userWatchesGroupRepo->stopUserWatchingGroup($user, $this->parameters['group']);
                        $redirect = true;
                    }

                }

                if ($redirect) {

                    return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId().'/group/'.$this->parameters['group']->getSlug().'/watchers');
                }
            }
        }

        $this->parameters['form'] = $form->createView();
				
		$uarb = new UserAccountRepositoryBuilder($app);
		$uarb->setWatchesGroup($this->parameters['group']);
		$this->parameters['watchers'] = $uarb->fetchAll();

		
		return $app['twig']->render('sysadmin/group/watchers.html.twig', $this->parameters);		
	
	}
	
	
}


