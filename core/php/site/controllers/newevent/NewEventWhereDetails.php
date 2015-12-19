<?php

namespace site\controllers\newevent;


use models\AreaModel;
use models\EventModel;
use models\VenueEditMetaDataModel;
use models\VenueModel;
use repositories\AreaRepository;
use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use repositories\CountryRepository;
use repositories\VenueRepository;
use site\forms\EventNewWhatDetailsForm;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventWhereDetails extends BaseNewEvent
{

	protected $MODE_SPLASH = 1;
	protected $MODE_VENUE = 2;
	protected $MODE_AREA = 3;
	protected $MODE_NEWVENUE = 4;

	protected function getCurrentMode() {
		if ($this->draftEvent->getDetailsValue('where.mode')) {
			return $this->draftEvent->getDetailsValue('where.mode');
		} else {
			return $this->MODE_SPLASH;
		}
	}

	function processIsAllInformationGathered() {

		if ($this->draftEvent->getDetailsValue('area.id') || $this->draftEvent->getDetailsValue('venue.id') || $this->draftEvent->getDetailsValue('event.newvenue') || $this->draftEvent->getDetailsValue('event.noareavenue')) {
			$this->isAllInformationGathered = true;
		}

	}

	function getTitle() {
		return 'Where';
	}

	function getStepID() {
		return 'where';
	}

	public function canJumpBackToHere() {
		return true;
	}

	function onThisStepGetViewName() {
		if ($this->getCurrentMode() == $this->MODE_SPLASH) {
			return 'site/eventnew/eventDraft.where.form.html.twig';
		} else if ($this->getCurrentMode() == $this->MODE_VENUE) {
			return 'site/eventnew/eventDraft.where.venue.form.html.twig';
		} else if ($this->getCurrentMode() == $this->MODE_NEWVENUE) {
			return 'site/eventnew/eventDraft.where.newvenue.form.html.twig'; // TODO site/eventnew/eventDraft.where.venue.new.form.html.twig
		} else if ($this->getCurrentMode() == $this->MODE_AREA) {
			return 'site/eventnew/eventDraft.where.area.form.html.twig';
		}
	}


	function onThisStepGetViewJavascriptName() {
		if ($this->getCurrentMode() == $this->MODE_SPLASH) {
			return '';
		} else if ($this->getCurrentMode() == $this->MODE_VENUE) {
			return 'site/eventnew/eventDraft.where.venue.javascript.html.twig';
		} else if ($this->getCurrentMode() == $this->MODE_NEWVENUE) {
			return 'site/eventnew/eventDraft.where.venue.new.javascript.html.twig';
		} else if ($this->getCurrentMode() == $this->MODE_AREA) {
			return 'site/eventnew/eventDraft.where.area.javascript.html.twig';
		}
	}


	function onThisStepSetUpPageView() {
		if ($this->getCurrentMode() == $this->MODE_SPLASH) {
			return array();
		} else if ($this->getCurrentMode() == $this->MODE_VENUE) {
			return $this->getVenueSearchData();
		} else if ($this->getCurrentMode() == $this->MODE_NEWVENUE) {
			$out = array('areas'=>array(),'fieldAreaObject'=>null,'childAreas'=>null,'areaSearchRequired'=>false,'titleRequired'=>false);

			$countryRepository = new CountryRepository();
			$out['country'] = $countryRepository->loadById($this->draftEvent->getDetailsValue('event.country_id'));

			$areaRepository = new AreaRepository();
			$out['doesCountryHaveAnyNotDeletedAreas'] = $areaRepository->doesCountryHaveAnyNotDeletedAreas( $this->site, $out['country']);

			$out['fieldTitle'] = $this->draftEvent->getDetailsValue('venue.title');
			if (!trim($out['fieldTitle'])) {
				$out['titleRequired'] = true;
			}
			$out['fieldAddress'] = $this->draftEvent->getDetailsValue('venue.address');
			$out['fieldAddressCode'] = $this->draftEvent->getDetailsValue('venue.address_code');
			$out['fieldArea'] = $this->draftEvent->getDetailsValue('venue.field_area_search_text');
			$out['fieldDescription'] = $this->draftEvent->getDetailsValue('venue.description');

			$area = $this->draftEvent->getDetailsValue('area.id') ? $areaRepository->loadById($this->draftEvent->getDetailsValue('area.id')) : null;

			if (!$area && $out['fieldArea']) {
				$areaRepoBuilder = new AreaRepositoryBuilder();
				$areaRepoBuilder->setSite($this->site);
				$areaRepoBuilder->setCountry($out['country']);
				$areaRepoBuilder->setIncludeDeleted(false);
				$areaRepoBuilder->setFreeTextSearch($out['fieldArea']);
				$areaRepoBuilder->setLimit(500);
				$out['areas'] = $areaRepoBuilder->fetchAll();
				if (count($out['areas']) == 1) {
					$area =  $out['areas'][0];
				} else if (count($out['areas'] > 0)) {
					$out['areaSearchRequired'] = true;
				}
			}

			if ($area) {
				$out['fieldAreaObject'] = $area;
				$out['fieldArea'] = $area->getTitle();
				$out['fieldAreaSlug'] = $area->getSlug();
			}

			// We don't want user to be prompted to fill out form first go round, so check for this flag
			if ($this->draftEvent->getDetailsValue('where.setthisnewvenue.submitted')) {
				$out['childAreas'] = $this->getChildAreasForArea($area, 100);
				if (count($out['childAreas']) > 0) {
					$out['areaSearchRequired'] = true;
				}
			}

			return $out;
		} else if ($this->getCurrentMode() == $this->MODE_AREA) {
			$out = array('searchAreas'=>$this->request->get('searchAreas'), 'areas'=>array());

			$countryRepository = new CountryRepository();
			$out['country'] = $countryRepository->loadById($this->draftEvent->getDetailsValue('event.country_id'));

			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($this->site);
			$areaRepoBuilder->setCountry($out['country']);
			$areaRepoBuilder->setIncludeDeleted(false);
			// If no user input sent, and incoming area, start on that.
			if ($this->request->get('action') != 'searchAreas' && $this->draftEvent->getDetailsValue('incoming.area.id')) {
				$areaRepository = new AreaRepository();
				$area = $areaRepository->loadById($this->draftEvent->getDetailsValue('incoming.area.id'));
				if ($area) {
					$out['searchAreas'] = $area->getTitle();
				}
			}
			// Now search by area if we have it.
			if ($out['searchAreas']) {
				$areaRepoBuilder->setFreeTextSearch($out['searchAreas']);
			}
			$areaRepoBuilder->setLimit(500);
			$out['areas'] = $areaRepoBuilder->fetchAll();

			return $out;
		}
	}


	function onThisStepProcessPage()
	{

		// Firstly, do we change mode?

		if ($this->request->request->get('action') == 'setvenue') {
			$this->draftEvent->setDetailsValue('where.mode', $this->MODE_VENUE);
			return true;
		}

		if ($this->request->request->get('action') == 'setnewvenue') {
			$this->draftEvent->setDetailsValue('where.mode', $this->MODE_NEWVENUE);

			$venueModel = new VenueModel();
			$venueModel->setSiteId($this->site->getId());
			$venueModel->setCountryId($this->draftEvent->getDetailsValue('event.country_id'));
			$venueModel->setTitle($this->request->request->get('fieldTitle'));
			$venueModel->setAddress($this->request->request->get('fieldAddress'));
			$venueModel->setAddressCode($this->request->request->get('fieldAddressCode'));

			if ($this->request->request->get('fieldAreaSlug') && $this->request->request->get('fieldAreaSlug') != -1) {
				$areaRepo = new AreaRepository();
				$area = $areaRepo->loadBySlug($this->site, $this->request->request->get('fieldAreaSlug'));
				if ($area) {
					$venueModel->setAreaId($area->getId());
				}
			}

			foreach($this->application['extensions']->getExtensionsIncludingCore() as $extension) {
				$extension->addDetailsToVenue($venueModel);
			}

			$this->draftEvent->setDetailsValue('venue.title', $venueModel->getTitle());
			$this->draftEvent->setDetailsValue('venue.address', $venueModel->getAddress());
			$this->draftEvent->setDetailsValue('venue.address_code', $venueModel->getAddressCode());
			$this->draftEvent->setDetailsValue('venue.field_area_search_text', $this->request->request->get('fieldAreaSearchText'));
			$this->draftEvent->setDetailsValue('venue.area_id', $venueModel->getAreaId());

			return true;
		}

		if ($this->request->request->get('action') == 'setarea') {
			// User may have been setting venue and realised they didn't know it. Clear data to make sure it's kept clean.
			$this->draftEvent->unsetDetailsValue('event.newvenue');
			$this->draftEvent->unsetDetailsValue('venue.title');
			$this->draftEvent->unsetDetailsValue('venue.address');
			$this->draftEvent->unsetDetailsValue('venue.address_code');
			$this->draftEvent->unsetDetailsValue('venue.description');
			$this->draftEvent->unsetDetailsValue('venue.field_area_search_text');
			$this->draftEvent->unsetDetailsValue('venue.area_id');
			// Do we ask user for area or not?
			$countryRepository = new CountryRepository();
			$country = $countryRepository->loadById($this->draftEvent->getDetailsValue('event.country_id'));
			$areaRepository = new AreaRepository();
			if ($areaRepository->doesCountryHaveAnyNotDeletedAreas($this->site, $country)) {
				$this->draftEvent->setDetailsValue('where.mode', $this->MODE_AREA);
			} else {
				$this->draftEvent->setDetailsValue('event.noareavenue', true);
				$this->isAllInformationGathered = true;
			}
			return true;
		}

		// Secondly, any thing actually set?

		if ($this->request->request->get('action') == 'setthisarea') {
			$ar = new AreaRepository();
			$area = $ar->loadBySlug($this->site, $this->request->request->get('area_slug'));
			if ($area) {
				$this->draftEvent->setDetailsValue('area.id', $area->getId());
				$this->draftEvent->setDetailsValue('area.title', $area->getTitle());
				$this->isAllInformationGathered = true;
				return true;
			}
		}

		if ($this->request->request->get('action') == 'setthisvenue') {
			$vr = new VenueRepository();
			$venue = $vr->loadBySlug($this->site, $this->request->request->get('venue_slug'));
			if ($venue) {
				$this->draftEvent->setDetailsValue('venue.id', $venue->getId());
				$this->draftEvent->setDetailsValue('venue.title', $venue->getTitle());
				$this->draftEvent->setDetailsValue('venue.address', $venue->getAddress());
				$this->draftEvent->setDetailsValue('venue.address_code', $venue->getAddressCode());
				$this->isAllInformationGathered = true;
				return true;
			}
		}

		if ($this->request->request->get('action') == 'setthisnewvenue') {


			$venueModel = new VenueModel();
			$venueModel->setSiteId($this->site->getId());
			$venueModel->setCountryId($this->draftEvent->getDetailsValue('event.country_id'));
			$venueModel->setTitle($this->request->request->get('fieldTitle'));
			$venueModel->setAddress($this->request->request->get('fieldAddress'));
			$venueModel->setAddressCode($this->request->request->get('fieldAddressCode'));
			$venueModel->setDescription($this->request->request->get('fieldDescription'));

			$areaRepo = new AreaRepository();

			// Slightly ackward we have to set Area ID on venue object, then when extensions have done we need to reload the area object again.
			if ($this->request->request->get('fieldAreaSlug') && $this->request->request->get('fieldAreaSlug') != -1) {
				$area = $areaRepo->loadBySlug($this->site, $this->request->request->get('fieldAreaSlug'));
				if ($area) {
					$venueModel->setAreaId($area->getId());
				}
			}
			if ($this->request->request->get('fieldChildAreaSlug') && $this->request->request->get('fieldChildAreaSlug') != -1) {
				$areaChild = $areaRepo->loadBySlug($this->site, $this->request->request->get('fieldChildAreaSlug'));
				if ($areaChild) {
					$area = $areaChild;
					$venueModel->setAreaId($areaChild->getId());
				}
			}

			foreach($this->application['extensions']->getExtensionsIncludingCore() as $extension) {
				$extension->addDetailsToVenue($venueModel);
			}
			$area = null;
			if ($venueModel->getAreaId() && (!$area || $area->getId() != $venueModel->getAreaId())) {
				$area = $areaRepo->loadById($venueModel->getAreaId());
			}

			$this->draftEvent->setDetailsValue('venue.title', $venueModel->getTitle());
			$this->draftEvent->setDetailsValue('venue.address', $venueModel->getAddress());
			$this->draftEvent->setDetailsValue('venue.address_code', $venueModel->getAddressCode());
			$this->draftEvent->setDetailsValue('venue.description', $venueModel->getDescription());
			if ($venueModel->hasLatLng()) {
				$this->draftEvent->setDetailsValue('venue.lat', $venueModel->getLat());
				$this->draftEvent->setDetailsValue('venue.lng', $venueModel->getLng());
			} else {
				$this->draftEvent->setDetailsValue('venue.lat', null);
				$this->draftEvent->setDetailsValue('venue.lng', null);
			}
			$this->draftEvent->setDetailsValue('venue.field_area_search_text', $this->request->request->get('fieldAreaSearchText'));
			if ($area) {
				$this->draftEvent->setDetailsValue('area.id', $area->getId());
				$this->draftEvent->setDetailsValue('area.title', $area->getTitle());
			} else {
				$this->draftEvent->setDetailsValue('area.id', null);
				$this->draftEvent->setDetailsValue('area.title', null);
			}

			// are we done? if user has selected -1 for "none" or there are no child areas. oh, and title needed
			if ($this->request->request->get('fieldChildAreaSlug') == -1 && trim($venueModel->getTitle())) {
				$this->draftEvent->setDetailsValue('event.newvenue',true);
				$this->isAllInformationGathered = true;
			} else if (count($this->getChildAreasForArea($area, 1)) == 0 && trim($venueModel->getTitle())) {
				$this->draftEvent->setDetailsValue('event.newvenue',true);
				$this->isAllInformationGathered = true;
			}

			$this->draftEvent->setDetailsValue('where.setthisnewvenue.submitted', true);

			return true;
		}

		if ($this->request->request->get('action') == 'setnoareavenue') {
			$this->draftEvent->setDetailsValue('event.noareavenue', true);
			$this->isAllInformationGathered = true;
			return true;
		}


	}

	function stepDoneGetViewName()
	{
		return 'site/eventnew/eventDraft.where.preview.html.twig';
	}

	function addDataToEventBeforeSave(EventModel $eventModel) {
		$this->addDataToEventBeforeCheck($eventModel);

		if ($this->draftEvent->getDetailsValue('event.newvenue')) {
			$venueModel = new VenueModel();
			$venueModel->setSiteId($this->site->getId());
			$venueModel->setCountryId($this->draftEvent->getDetailsValue('event.country_id'));
			$venueModel->setTitle($this->draftEvent->getDetailsValue('venue.title'));
			$venueModel->setAddress($this->draftEvent->getDetailsValue('venue.address'));
			$venueModel->setAddressCode($this->draftEvent->getDetailsValue('venue.address_code'));
			$venueModel->setDescription($this->draftEvent->getDetailsValue('venue.description'));
			if ($this->draftEvent->getDetailsValue('venue.lat')) {
				$venueModel->setLat($this->draftEvent->getDetailsValue('venue.lat'));
				$venueModel->setLng($this->draftEvent->getDetailsValue('venue.lng'));
			}
			if ($this->draftEvent->getDetailsValue('area.id')) {
				$venueModel->setAreaId($this->draftEvent->getDetailsValue('area.id'));
			}

			foreach($this->application['extensions']->getExtensionsIncludingCore() as $extension) {
				$extension->addDetailsToVenue($venueModel);
			}

			$vee = new VenueEditMetaDataModel();
			$vee->setUserAccount($this->application['currentUser']);
			// TODO $vee->setFromRequest();

			$venueRepository = new VenueRepository();
			$venueRepository->createWithMetaData($venueModel, $this->site, $vee);

			$eventModel->setVenueId($venueModel->getId());
		}
	}

	function addDataToEventBeforeCheck(EventModel $eventModel) {
		if ($this->draftEvent->getDetailsValue('area.id')) {
			$eventModel->setAreaId($this->draftEvent->getDetailsValue('area.id'));
		}

		if ($this->draftEvent->getDetailsValue('venue.id')) {
			$eventModel->setVenueId($this->draftEvent->getDetailsValue('venue.id'));
		}

	}


	function onThisStepAddAJAXCallData() {
		$out = array();

		if ($this->request->request->get('action') == 'searchAreas') {
			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($this->site);
			$areaRepoBuilder->setIncludeDeleted(false);
			if ($this->request->get('searchAreas')) {
				$areaRepoBuilder->setFreeTextSearch($this->request->get('searchAreas'));
			}
			$areaRepoBuilder->setLimit(500);
			$out['areas'] = array();
			foreach($areaRepoBuilder->fetchAll() as $area) {
				$out['areas'][] = array(
					'slug' => $area->getSlug(),
					'title' => $area->getTitle(),
					'parent1title' => $area->getParent1Title(),
					'minLat' => $area->getMinLat(),
					'maxLat' => $area->getMaxLat(),
					'minLng' => $area->getMinLng(),
					'maxLng' => $area->getMaxLng(),
				);
			}
		}

		if ($this->request->request->get('action') == 'searchVenues') {
			$dataVenueSearch = $this->getVenueSearchData();

			$out['venueSearchDone'] = $dataVenueSearch['venueSearchDone'];
			$out['searchAreaSlug'] = $dataVenueSearch['searchAreaSlug'];
			$out['venues'] = array();
			$out['areas'] = array();

			foreach($dataVenueSearch['venues'] as $venue) {
				$out['venues'][] = array(
					'slug'=>$venue->getSlug(),
					'title'=>$venue->getTitle(),
					'address'=>$venue->getAddress(),
					'addresscode'=>$venue->getAddressCode(),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
				);
			}
			foreach($dataVenueSearch['areas'] as $area) {
				$out['areas'][] = array(
					'slug'=>$area->getSlug(),
					'title'=>$area->getTitle(),
					'parent1title'=>$area->getParent1Title(),
				);
			}
		}

		if ($this->request->request->get('action') == 'setthisnewvenue') {
			if ($this->request->get('fieldArea')) {
				$areaRepoBuilder = new AreaRepositoryBuilder();
				$areaRepoBuilder->setSite($this->site);
				$areaRepoBuilder->setIncludeDeleted(false);
				$areaRepoBuilder->setFreeTextSearch($this->request->get('fieldArea'));
				$areaRepoBuilder->setLimit(500);
				$out['areas'] = array();
				foreach ($areaRepoBuilder->fetchAll() as $area) {
					$out['areas'][] = array(
						'slug' => $area->getSlug(),
						'title' => $area->getTitle(),
						'parent1title' => $area->getParent1Title(),
					);
				}
			}
			$out['fieldAreaSlug'] = $this->request->request->get('fieldAreaSlug');
		}

		return $out;
	}



	protected function getVenueSearchData() {
		$out = array(
			'searchFieldsSubmitted'=>($this->request->request->get('action') == 'searchVenues'),
			'searchTitle'=>$this->request->request->get('searchTitle'),
			'searchAddress'=>$this->request->request->get('searchAddress'),
			'searchArea'=>$this->request->request->get('searchArea'),
			'searchAreaSlug'=>$this->request->request->get('searchAreaSlug'),
			'searchAddressCode'=>$this->request->request->get('searchAddressCode'),
			'searchAreaObject'=>null,
			'venues'=>array(),
			'areas'=>array(),
			'venueSearchDone'=>false
		);

		$countryRepository = new CountryRepository();
		$out['country'] = $countryRepository->loadById($this->draftEvent->getDetailsValue('event.country_id'));
		$areaRepository = new AreaRepository();
		$out['doesCountryHaveAnyNotDeletedAreas'] = $areaRepository->doesCountryHaveAnyNotDeletedAreas( $this->site, $out['country']);

		if ($out['doesCountryHaveAnyNotDeletedAreas']) {
			// Area search
			if ($out['searchArea']) {
				$arb = new AreaRepositoryBuilder();
				$arb->setIncludeDeleted(false);
				$arb->setIncludeParentLevels(1);
				$arb->setSite($this->site);
				$arb->setCountry($out['country']);
				$arb->setFreeTextSearch($out['searchArea']);
				$out['areas'] = $arb->fetchAll();
				if (count($out['areas']) == 1 && !$out['searchAreaSlug']) {
					$out['searchAreaSlug'] = $out['areas'][0]->getSlug();
					$out['searchAreaObject'] = $out['areas'][0];
				}

				// has user selected a area and is it still in search results? If so select it.
				if (!$out['searchAreaObject'] && $out['searchAreaSlug'] && intval($out['searchAreaSlug'])) {
					foreach($out['areas'] as $area) {
						if ($area->getSlug() == $out['searchAreaSlug']) {
							$out['searchAreaObject'] = $area;
						}
					}
				}
			}
		}

		// If user has not added any search fields. and the event is in a area. let's search by area by default.
		if (!$out['searchFieldsSubmitted'] && !$out['searchAreaObject'] && $this->draftEvent->getDetailsValue('incoming.area.id')) {
			$areaRepository = new AreaRepository();
			$area = $areaRepository->loadById($this->draftEvent->getDetailsValue('incoming.area.id'));
			if ($area) {
				$out['searchAreaObject'] = $area;
				$out['searchArea'] = $area->getTitle();
				$out['searchAreaSlug'] = $area->getSlug();
			}
		}


		if ($out['searchAddressCode'] || $out['searchAddress'] || $out['searchTitle'] || $out['searchAreaObject']) {
			$vrb = new VenueRepositoryBuilder();
			$vrb->setSite($this->site);
			$vrb->setCountry($out['country']);
			$vrb->setIncludeDeleted(false);
			if ($out['searchTitle']) {
				$vrb->setFreeTextSearchTitle($out['searchTitle']);
			}
			if ($out['searchAddress']) {
				$vrb->setFreeTextSearchAddress($out['searchAddress']);
			}
			if ($out['searchAddressCode']) {
				$vrb->setFreeTextSearchAddressCode($out['searchAddressCode']);
			}
			if ($out['searchAreaObject']) {
				$vrb->setArea($out['searchAreaObject']);
			}
			$vrb->setLimit(500);
			$out['venues'] = $vrb->fetchAll();
			$out['venueSearchDone'] = true;
		}


		return $out;
	}

	protected function getChildAreasForArea(AreaModel $areaModel=null, $limit=500) {
		$arb = new AreaRepositoryBuilder();
		$arb->setSite($this->site);
		$arb->setIncludeDeleted(false);
		if ($areaModel) {
			$arb->setParentArea($areaModel);
		} else {
			$arb->setNoParentArea(true);
		}
		$arb->setLimit($limit);
		return $arb->fetchAll();
	}

}

