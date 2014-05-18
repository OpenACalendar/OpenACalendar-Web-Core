<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\VenueRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueController {
	
	/** @var \models\VenueModel **/
	protected $venue;

	protected function build($slug, Request $request, Application $app) {

		
		$repo = new VenueRepository();
		$this->venue = $repo->loadBySlug($app['currentSite'], $slug);
		if (!$this->venue) {
			return false;
		}
		
		return true;
		
	}
	


	public function infoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}
		
		$out = array(
			'venue'=>array(
				'slug'=>$this->venue->getSlug(),
				'slugForURL'=>$this->venue->getSlugForUrl(),
				'title'=>$this->venue->getTitle(),
			),
			
		);
		
		return json_encode($out);
	}
	
	
}

