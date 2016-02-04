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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueController {
	
	/** @var \models\VenueModel **/
	protected $venue;

	protected function build($slug, Request $request, Application $app) {

		
		$repo = new VenueRepository($app);
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
				'description'=>$this->venue->getDescription(),
				'address'=>$this->venue->getAddress(),
				'address_code'=>$this->venue->getAddressCode(),
				'lat'=>$this->venue->getLat(),
				'lng'=>$this->venue->getLng(),
			),
			
		);
		
		return json_encode($out);
	}


	public function postInfoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}

		$ourRequest = new \Request($request);

		$edits = false;
		if ($ourRequest->hasGetOrPost('title') && $this->venue->setTitleIfDifferent($ourRequest->getGetOrPostString('title', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('description') && $this->venue->setDescriptionIfDifferent($ourRequest->getGetOrPostString('description', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('address') && $this->venue->setAddressIfDifferent($ourRequest->getGetOrPostString('address', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('address_code') && $this->venue->setAddressCodeIfdifferent($ourRequest->getGetOrPostString('address_code', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('lat') && $ourRequest->hasGetOrPost('lng')) {
			if ($this->venue->setLatIfDifferent($ourRequest->getGetOrPostString('lat', ''))) {
				$edits = true;
			}
			if ($this->venue->setLngIfDifferent($ourRequest->getGetOrPostString('lng', ''))) {
				$edits = true;
			}
		}

		if ($edits) {
			$repo = new VenueRepository($app);
			$repo->edit($this->venue, $app['apiUser']);
			$out = array(
				'edited'=>true,
			);
		} else {
			$out = array(
				'edited'=>false,
			);
		}

		return json_encode($out);
	}
}

