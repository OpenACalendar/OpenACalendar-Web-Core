<?php


namespace repositories;

use models\CountryModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryRepository {

	
	public function loadByTwoCharCode($code) {
		global $DB;
		$stat = $DB->prepare("SELECT country.* FROM country ".
				" WHERE country.two_char_code =:code ");
		$stat->execute(array( 'code'=> strtoupper($code)));
		if ($stat->rowCount() > 0) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($stat->fetch());
			return $country;
		}
	}
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT country.* FROM country ".
				" WHERE country.id =:id ");
		$stat->execute(array( 'id'=>$id));
		if ($stat->rowCount() > 0) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($stat->fetch());
			return $country;
		}
	}
	
}

