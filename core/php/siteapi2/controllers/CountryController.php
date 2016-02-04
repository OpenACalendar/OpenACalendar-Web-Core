<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\CountryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryController {
	
	/** @var \models\CountryModel **/
	protected $country;

	protected function build($slug, Request $request, Application $app) {

		
		$repo = new CountryRepository($app);
		$this->country = $repo->loadByTwoCharCode($slug);
		if (!$this->country) {
			return false;
		}
		
		return true;
		
	}
	


	public function infoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}
		
		$out = array(
			'country'=>array(
				'title'=>$this->country->getTitle(),
			),
			
		);
		
		return json_encode($out);
		
	}
	
	
}

