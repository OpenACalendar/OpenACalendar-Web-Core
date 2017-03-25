<?php


namespace repositories;

use models\CountryModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryInSiteRepository {



    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function addCountryToSite(CountryModel $country, SiteModel $site, UserAccountModel $user) {
        $stat = $this->app['db']->prepare("SELECT * FROM country_in_site_information WHERE site_id =:site_id AND country_id =:country_id");
        $stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId() ));
        if ($stat->rowCount() == 1) {
            $stat = $this->app['db']->prepare("UPDATE country_in_site_information SET is_in='1' WHERE site_id =:site_id AND country_id =:country_id");
            $stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId() ));
            // This doesn't work in that if is_in was already 1 it seems to fire anyway. So sometimes you get false positives that a change occurred when it didn't.
            // if ($stat->rowCount() > 0) {
                $this->app['messagequeproducerhelper']->send('org.openacalendar', 'CountryInSiteSaved', array('country_id' => $country->getId(), 'site_id' => $site->getId()));
            //}
        } else {
            $stat = $this->app['db']->prepare("INSERT INTO country_in_site_information (site_id,country_id,is_in,is_previously_in,created_at) VALUES (:site_id,:country_id,'1','1',:created_at)");
            $stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId(), 'created_at'=>$this->app['timesource']->getFormattedForDataBase() ));
            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'CountryInSiteSaved', array('country_id'=>$country->getId(),'site_id'=>$site->getId()));
        }

    }

    public function removeCountryFromSite(CountryModel $country, SiteModel $site, UserAccountModel $user) {
        $stat = $this->app['db']->prepare("UPDATE country_in_site_information SET is_in='0' WHERE site_id =:site_id AND country_id =:country_id");
        $stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId() ));
        // This doesn't quite work in that if is_in was already 0 it seems to fire anyway. So sometimes you get false positives that a change occurred when it didn't.
        // But it helps narrow down some false positives, as if there were no rows the event will not fire.
        if ($stat->rowCount() > 0) {
            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'CountryInSiteSaved', array('country_id' => $country->getId(), 'site_id' => $site->getId()));
        }
    }
	
	public function isCountryInSite(CountryModel $country, SiteModel $site) {
		$stat = $this->app['db']->prepare("SELECT * FROM country_in_site_information WHERE site_id =:site_id AND country_id =:country_id AND is_in= '1'");
		$stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId() ));		
		return ($stat->rowCount() == 1);
	}


    public function updateFutureEventsCache(CountryModel $countryModel, SiteModel $siteModel) {

        $statUpdate = $this->app['db']->prepare("UPDATE country_in_site_information SET cached_future_events=:count WHERE site_id = :site_id AND country_id = :country_id");

        $erb = new EventRepositoryBuilder($this->app);
        $erb->setCountry($countryModel);
        $erb->setSite($siteModel);
        $erb->setIncludeDeleted(false);
        $erb->setIncludeCancelled(false);
        $erb->setAfterNow();
        $count = count($erb->fetchAll());

        $statUpdate->execute(array('count'=>$count,'site_id'=>$siteModel->getId(), 'country_id'=>$countryModel->getId()));

        $countryModel->setCachedFutureEventsInSite($count);
    }
	
}

