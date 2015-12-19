<?php

namespace sysadmin\controllers;

use models\EventCustomFieldDefinitionModel;
use models\EventEditMetaDataModel;
use repositories\EventCustomFieldDefinitionRepository;
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
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventCustomFieldDefinitionController
{

	protected $parameters = array();

	protected function build($siteid, $fieldid, Request $request, Application $app) {
		$this->parameters = array();

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($siteid);

		if (!$this->parameters['site']) {
			$app->abort(404);
		}

		$repo = new EventCustomFieldDefinitionRepository();
		$this->parameters['field'] = $repo->loadBySiteIDAndID($this->parameters['site']->getId(), $fieldid);
		if (!$this->parameters['field']) {
			$app->abort(404);
		}

	}


	function index($siteid, $fieldid, Request $request, Application $app)
	{
		global $CONFIG;

		$this->build($siteid, $fieldid, $request, $app);


		$form = $app['form.factory']->create(new ActionForm());

		if ('POST' == $request->getMethod()) {
			$form->bind($request);


			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);

				if ($action->getCommand() == 'label') {
					$this->parameters['field']->setLabel($action->getParam(0));

					$repo = new EventCustomFieldDefinitionRepository();
					$repo->editLabel($this->parameters['field'], $app['currentUser']);
					return $app->redirect("/sysadmin/site/".$this->parameters['site']->getId()."/eventcustomfielddefinition/".$this->parameters['field']->getId());
				} else if ($action->getCommand() == 'activate') {
					$repo = new EventCustomFieldDefinitionRepository();
					$repo->activate($this->parameters['field'], $app['currentUser']);
					return $app->redirect("/sysadmin/site/".$this->parameters['site']->getId()."/eventcustomfielddefinition/".$this->parameters['field']->getId());
				} else if ($action->getCommand() == 'deactivate') {
					$repo = new EventCustomFieldDefinitionRepository();
					$repo->deactivate($this->parameters['field'], $app['currentUser']);
					return $app->redirect("/sysadmin/site/".$this->parameters['site']->getId()."/eventcustomfielddefinition/".$this->parameters['field']->getId());
				}
			}
		}

		$this->parameters['form'] = $form->createView();


		return $app['twig']->render('sysadmin/eventcustomfielddefinition/index.html.twig', $this->parameters);


	}

}