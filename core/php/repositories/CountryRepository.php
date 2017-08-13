<?php


namespace repositories;

use models\CountryModel;
use models\SiteModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryRepository {


    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function loadByTwoCharCode(string $code) {
		$stat = $this->app['db']->prepare("SELECT country.* FROM country ".
				" WHERE country.two_char_code =:code ");
		$stat->execute(array( 'code'=> strtoupper($code)));
		if ($stat->rowCount() > 0) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($stat->fetch());
			return $country;
		}
	}
	
	public function loadById(int $id) {
		$stat = $this->app['db']->prepare("SELECT country.* FROM country ".
				" WHERE country.id =:id ");
		$stat->execute(array( 'id'=>$id));
		if ($stat->rowCount() > 0) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($stat->fetch());
			return $country;
		}
	}
	
	/**
	 * This will return one country only. It is intended for sites with one country only.
	 **/
	public function loadBySite(SiteModel $site) {
		$stat = $this->app['db']->prepare("SELECT country.* FROM country ".
				" JOIN country_in_site_information ON country_in_site_information.country_id = country.id AND country_in_site_information.is_in = '1' ".
				" WHERE country_in_site_information.site_id=:id ");
		$stat->execute(array( 'id'=>$site->getId()));
		if ($stat->rowCount() > 0) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($stat->fetch());
			return $country;
		}
	}
	
	
}

