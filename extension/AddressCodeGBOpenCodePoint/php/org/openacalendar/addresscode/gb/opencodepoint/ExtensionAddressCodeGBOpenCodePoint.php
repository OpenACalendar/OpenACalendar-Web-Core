<?php

namespace org\openacalendar\addresscode\gb\opencodepoint;

use models\VenueModel;
use models\UserAccountModel;
use repositories\CountryRepository;

/**
 *
 * @package org.openacalendar.addresscode.gb.opencodepoint
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionAddressCodeGBOpenCodePoint extends \BaseExtension {
	
	public function getId() {
		return 'org.openacalendar.addresscode.gb.opencodepoint';
	}
	
	public function getTitle() {
		return "Address Codes for GB from Open Code Point";
	}
	
	public function getDescription() {
		return "Translates postcodes to lat and lng";
	}

	public function addDetailsToVenue(VenueModel $venue) {

		if ($venue->getAddressCode() && (!$venue->getLat() || !$venue->getLng())) {

			$cr = new CountryRepository($this->app);
			$gb = $cr->loadByTwoCharCode("GB");
			if ($venue->getCountryId() == $gb->getId()) {

                $ourdir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'..'.
                                   DIRECTORY_SEPARATOR.'data');

                $dataAdaptor = new \JMBTechnologyLimited\OSData\CodePointOpen\FileDataAdaptor($ourdir);
                $service = new \JMBTechnologyLimited\OSData\CodePointOpen\CodePointOpenService($dataAdaptor);
                $postCode = $service->getPostcode($venue->getAddressCode());

				if ($postCode) {
					$venue->setLat($postCode->getLat());
					$venue->setLng($postCode->getLng());
				}
			}
		}
		
	}
	
}

