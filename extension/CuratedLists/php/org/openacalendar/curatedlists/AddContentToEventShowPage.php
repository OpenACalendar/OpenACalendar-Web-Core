<?php

namespace org\openacalendar\curatedlists;

use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;
use Silex\Application;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AddContentToEventShowPage extends \BaseAddContentToEventShowPage {
	/** @var Application */
	protected $app;
	protected $parameters;

	function __construct($parameters, Application $app)
	{
		$this->parameters = $parameters;
		$this->app = $app;

		$app['currentUserActions']->set("org.openacalendar.curatedlists","eventEditCuratedLists",
			$app['currentUserActions']->has("org.openacalendar","curatedListGeneralEdit")
			&& !$this->parameters['event']->getIsDeleted());
		// not && !$this->parameters['event']->getIsCancelled() because if cancelled want to remove from lists

	}

	public function  getParameters()
	{
		$parameters = array();

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder();
		$curatedListRepoBuilder->setContainsEvent($this->parameters['event']);
		$curatedListRepoBuilder->setIncludeDeleted(false);
		$parameters['curatedLists'] = $curatedListRepoBuilder->fetchAll();

		return $parameters;

	}


	public function getTemplatesAtEnd() {
		return array('site/event/show.curatedlists.html.twig');
	}
}
