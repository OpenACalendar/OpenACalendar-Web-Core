<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\AreaRepository;
use repositories\builders\SiteRepositoryBuilder;
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
class AreaController {
	
		
	protected $parameters = array();
	
	protected function build($siteid, $slug, Request $request, Application $app) {
		$this->parameters = array('area'=>null,'parentarea'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
		
		$ar = new AreaRepository();
		$this->parameters['area'] = $ar->loadBySlug($this->parameters['site'], $slug);
		
		if (!$this->parameters['area']) {
			$app->abort(404);
		}
		
		if ($this->parameters['area']->getParentAreaId()) {
			$this->parameters['parentarea'] = $ar->loadById($this->parameters['area']->getParentAreaId());
		}
		
	
	}
	
	function index($siteid, $slug, Request $request, Application $app) {

		$this->build($siteid, $slug, $request, $app);
		
				
		return $app['twig']->render('sysadmin/area/index.html.twig', $this->parameters);		
	
	}
	
	
	
	
}


