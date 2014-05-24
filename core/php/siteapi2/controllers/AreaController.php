<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\AreaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaController {
	
	/** @var \models\AreaModel **/
	protected $area;

	protected function build($slug, Request $request, Application $app) {

		$repo = new AreaRepository();
		$this->area = $repo->loadBySlug($app['currentSite'], $slug);
		if (!$this->area) {
			return false;
		}
		
		return true;
	}
	


	public function infoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}
		
		$out = array(
			'area'=>array(
				'slug'=>$this->area->getSlug(),
				'slugForURL'=>$this->area->getSlugForUrl(),
				'title'=>$this->area->getTitle(),
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
		if ($ourRequest->hasGetOrPost('title')) {
			$newValue = $ourRequest->getGetOrPostString('title', '');
			if ($newValue && $newValue != $this->area->getTitle()) {
				$edits = true;
				$this->area->setTitle($newValue);
			}
		}
		
		if ($edits) {
			$repo = new AreaRepository();
			$repo->edit($this->area, $app['apiUser']);
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

