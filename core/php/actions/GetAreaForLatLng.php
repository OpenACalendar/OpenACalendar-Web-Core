<?php
namespace actions;

use models\AreaModel;
use models\CountryModel;
use models\SiteModel;
use repositories\builders\AreaRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GetAreaForLatLng {


    protected $app;

    /** @var  SiteModel */
    protected $site;

    function __construct(  $app, SiteModel $site ) {
        $this->app  = $app;
        $this->site = $site;
    }


    public function getArea($lat, $lng, CountryModel $countryModel = null) {


        $areaRepo = new AreaRepositoryBuilder($this->app);
        $areaRepo->setSite($this->site);
        if ($countryModel) {
            $areaRepo->setCountry( $countryModel );
        }
        $areaRepo->setIncludeDeleted(false);
        $areaRepo->setLatLng($lat, $lng);

        $areas = $areaRepo->fetchAll();

        if (count($areas) == 0) {
            return null;
        } else if (count($areas) == 1) {
            return array_pop($areas);
        } else {

            $newAreas = array();

            foreach($areas as $area) {
                if (!$this->isAreaEntirelyBiggerThanOtherArea($area, $areas)) {
                    $newAreas[] = $area;
                }
            }

            if (count($newAreas) == 1) {
                return array_pop($newAreas);
            }

        }

    }

    protected function isAreaEntirelyBiggerThanOtherArea(AreaModel $area, $areas) {
        foreach($areas as $otherArea) {

            if ($otherArea->getId() != $area->getId() 
                && $otherArea->getMaxLat() < $area->getMaxLat()
                && $otherArea->getMaxLng() < $area->getMaxLng()
                && $otherArea->getMinLat() > $area->getMinLat()
                && $otherArea->getMinLng() > $area->getMinLng()
            ) {
                return true;
            }
        }
        return false;
    }

}