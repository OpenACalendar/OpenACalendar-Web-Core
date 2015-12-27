<?php

namespace org\openacalendar\curatedlists\repositories\builders\filterparams;

use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListFilterParams {

    function __construct() {
        $this->curatedListRepositoryBuilder = new CuratedListRepositoryBuilder();
        $this->curatedListRepositoryBuilder->setLimit(100);
    }


    protected $curatedListRepositoryBuilder;

    public function getCuratedListRepositoryBuilder() {
        return $this->curatedListRepositoryBuilder;
    }


    // ############################### params

    protected $freeTextSearch = null;
    protected $withFutureEventsOnly = false;

    public function set($data) {
        if (isset($data['curatedListListFilterDataSubmitted'])) {

            // Free Text Search
            if (isset($data['freeTextSearch']) && trim($data['freeTextSearch'])) {
                $this->freeTextSearch = $data['freeTextSearch'];
            }

            // Future Events Only
            if (isset($data['withFutureEventsOnly']) && $data['withFutureEventsOnly'] == '1') {
                $this->withFutureEventsOnly = true;
            }


        }

        // apply to search
        if ($this->freeTextSearch) {
            $this->curatedListRepositoryBuilder->setFreeTextsearch($this->freeTextSearch);
        }

        $this->curatedListRepositoryBuilder->setIncludeFutureEventsOnly($this->withFutureEventsOnly);
    }

    public function getFreeTextSearch() {
        return $this->freeTextSearch;
    }

    /**
     * @return boolean
     */
    public function isWithFutureEventsOnly()
    {
        return $this->withFutureEventsOnly;
    }





}


