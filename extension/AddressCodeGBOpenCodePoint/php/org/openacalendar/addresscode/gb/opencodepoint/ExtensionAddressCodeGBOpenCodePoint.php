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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
	
	
	public function beforeVenueSave(VenueModel $venue, UserAccountModel $user) {

		
		
		if ($venue->getAddressCode() && (!$venue->getLat() || !$venue->getLng())) {
			$cr = new CountryRepository();
			$gb = $cr->loadByTwoCharCode("GB");
			if ($venue->getCountryId() == $gb->getId()) {
				list($lat,$lng) = AddressCodeGBOpenCodePointGet::get($venue->getAddressCode());
				if ($lat && $lng) {
					$venue->setLat($lat);
					$venue->setLng($lng);
				}
			}
		}
		
	}
	
}

