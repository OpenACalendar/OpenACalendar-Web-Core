<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\MediaModel;
use repositories\MediaRepository;
use repositories\builders\MediaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaListController {
	
	function index(Application $app) {
		

		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$medias = $mrb->fetchAll();
		
		return $app['twig']->render('site/medialist/index.html.twig', array(
				'medias'=>$medias,
			));
		
	}
	
}

