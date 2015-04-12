<?php

namespace site\controllers\newevent;


use models\EventModel;
use repositories\CountryRepository;
use site\forms\EventNewWhatDetailsForm;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventPreview extends BaseNewEvent
{



	function getTitle()
	{
		return 'Preview';
	}

	function getStepID()
	{
		return 'preview';
	}


	function processIsAllInformationGathered()
	{

	}

	function onThisStepSetUpPageView() {
		$out = array();

		if ($this->draftEvent->getDetailsValue('event.country_id')) {
			$countryRepository = new CountryRepository();
			$out['country'] = $countryRepository->loadById($this->draftEvent->getDetailsValue('event.country_id'));
		}

		return $out;
	}

}