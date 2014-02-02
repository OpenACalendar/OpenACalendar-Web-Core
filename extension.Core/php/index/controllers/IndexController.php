<?php

namespace index\controllers;

use Silex\Application;
use index\forms\CreateForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\ContactSupportModel;
use repositories\SiteRepository;
use Symfony\Component\Form\FormError;
use repositories\builders\SiteRepositoryBuilder;
use repositories\CountryRepository;
use repositories\ContactSupportRepository;
use repositories\SiteQuotaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class IndexController {
	
	function index(Application $app) {
		global $WEBSESSION, $CONFIG;
		
		$sites = array();
		$repo  = new SiteRepository();
		if (isset($_COOKIE['sitesSeen'])) {
			foreach(explode(",",$_COOKIE['sitesSeen']) as $siteID) {
				if (intval($siteID) > 0) {
					$site = $repo->loadById($siteID);
					if ($site && !$site->getIsClosedBySysAdmin() && $site->getSlug() != $CONFIG->siteSlugDemoSite) {
						$sites[$site->getId()] = $site;
					}
				}
			}
		}
		
		if (userGetCurrent()) {
			$srb = new SiteRepositoryBuilder();
			$srb->setUserInterestedIn(userGetCurrent());
			foreach($srb->fetchAll() as $site) {
				$sites[$site->getId()] = $site;
			}
			
			return $app['twig']->render('index/index/index.loggedin.html.twig', array(
				'sites'=>$sites,
			));
		} else {
			return $app['twig']->render('index/index/index.loggedout.html.twig', array(
				'sites'=>$sites,
			));
		}
		
	}
	
	function myTimeZone(Application $app) {
		global $CONFIG;
		
		return $app['twig']->render('index/index/myTimeZone.html.twig', array(
			));
	}
	
	function create(Request $request, Application $app) {
		global $CONFIG;
		$siteRepository = new SiteRepository();
				
		$form = $app['form.factory']->create(new CreateForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			$data = $form->getData();
			
			$site = $siteRepository->loadBySlug($data['slug']);
			if ($site) {
				$form->addError(new FormError('That address is already taken'));
			}
			
			if ($form->isValid()) {
				
				$site = new SiteModel();
				$site->setSlug($data['slug']);
				$site->setTitle($data['title']);
				if ($data['read'] == 'public') {
					$site->setIsListedInIndex(true);
					$site->setIsWebRobotsAllowed(true);
				} else {
					$site->setIsListedInIndex(false);
					$site->setIsWebRobotsAllowed(false);
				}
				if ($data['write'] == 'public') {
					$site->setIsAllUsersEditors(true);
					$site->setIsRequestAccessAllowed(false);
				} else {
					$site->setIsAllUsersEditors(false);
					$site->setIsRequestAccessAllowed(true);
				}
				$site->setIsFeatureCuratedList($CONFIG->newSiteHasFeatureCuratedList);
				$site->setIsFeatureImporter($CONFIG->newSiteHasFeatureImporter);
				$site->setIsFeatureMap($CONFIG->newSiteHasFeatureMap);
				$site->setIsFeatureVirtualEvents($CONFIG->newSiteHasFeatureVirtualEvents);
				$site->setIsFeaturePhysicalEvents($CONFIG->newSiteHasFeaturePhysicalEvents);
				$site->setIsFeatureGroup($CONFIG->newSiteHasFeatureGroup);
				$site->setPromptEmailsDaysInAdvance($CONFIG->newSitePromptEmailsDaysInAdvance);
				
				$countryRepository = new CountryRepository();
				$siteQuotaRepository = new SiteQuotaRepository();
				
				$siteRepository->create(
							$site, 
							userGetCurrent(), 
							array( $countryRepository->loadByTwoCharCode("GB") ), 
							$siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode)
						);
				
				return $app->redirect("http://".$site->getSlug().".".$CONFIG->webSiteDomain);
			}
		}
		
		
		return $app['twig']->render('index/index/create.html.twig', array(
			'form'=>$form->createView(),
		));
		
	}
	
	function contact(Application $app) {
		global $WEBSESSION, $FLASHMESSAGES;
		
		
		if (isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {

			$contact = new ContactSupportModel();
			$contact->setSubject($_POST['subject']);
			$contact->setMessage($_POST['message']);
			$contact->setEmail($_POST['email']);
			if (userGetCurrent()) {
				$contact->setUserAccountId(userGetCurrent()->getId());
			}
			$contact->setIp($_SERVER['REMOTE_ADDR']);
			$contact->setBrowser($_SERVER['HTTP_USER_AGENT']);			
			if (isset($_POST['url']) && $_POST['url']) {
				$contact->setIsSpamHoneypotFieldDetected(true);
			}
			
			$contactSupportRepository = new ContactSupportRepository();
			$contactSupportRepository->create($contact);
			
			if (!$contact->getIsSpam()) {
				$contact->sendEmailToSupport($app, userGetCurrent());
			}
			
			$FLASHMESSAGES->addMessage('Your message has been sent');
			return $app->redirect('/contact');
		}
		
		
		return $app['twig']->render('index/index/contact.html.twig', array(
			));
		
	}
	
	function about(Application $app) {
		
		return $app['twig']->render('index/index/about.html.twig', array());
		
	}
	
	
	function terms(Application $app) {
		
		return $app['twig']->render('index/index/terms.html.twig', array());
		
	}
	
	
	function privacy(Application $app) {
		
		return $app['twig']->render('index/index/privacy.html.twig', array());
		
	}
	
	
	function credits(Application $app) {
		
		return $app['twig']->render('index/index/credits.html.twig', array());
		
	}
	
	
	
        function discover(Application $app) {

		$srb = new SiteRepositoryBuilder();
		$srb->setIsListedInIndexOnly(true);
		$sites = $srb->fetchAll();

                return $app['twig']->render('index/index/discover.html.twig', array(
				'sites'=>$sites
			));

        }

	
}


