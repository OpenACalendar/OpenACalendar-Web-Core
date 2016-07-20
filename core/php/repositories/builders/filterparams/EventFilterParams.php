<?php

namespace repositories\builders\filterparams;

use models\SiteModel;
use models\GroupModel;
use models\TagModel;
use models\UserAccountModel;
use repositories\AreaRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\CountryInSiteRepository;
use repositories\CountryRepository;
use repositories\GroupRepository;
use repositories\TagRepository;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventFilterParams {

	function __construct(Application $app, EventRepositoryBuilder $erb = null, SiteModel $siteModel = null) {
		$this->app = $app;
		if ($erb) {
			$this->eventRepositoryBuilder = $erb;
		} else {
			$this->eventRepositoryBuilder = new EventRepositoryBuilder($app);
			$this->eventRepositoryBuilder->setLimit(100);
		}
		if ($siteModel) {
			$this->eventRepositoryBuilder->setSite($siteModel);
			$this->siteModel = $siteModel;
		}
	}

	protected  $app;

	/** @var EventRepositoryBuilder */
	protected $eventRepositoryBuilder;

	/** @var  SiteModel */
	protected $siteModel;

	public function getEventRepositoryBuilder() {
		return $this->eventRepositoryBuilder;
	}


    public function isDefaultFilters() {
        return  !$this->freeTextSearch &&
                $this->fromNow &&
                !$this->include_deleted &&
                !$this->tagSearch &&
                !$this->groupSearch &&
                $this->includeSpecifiedUserWatching &&
                $this->includeSpecifiedUserAttending &&
                !$this->countrySearch &&
                !$this->areaSearch;
    }

	// ############################### optional controls; turn on and off
	
	protected $freeTextSearch = null;
	/** @var TagModel  */
	protected $tagSearch = null;
    /** @var GroupModel  */
    protected $groupSearch = null;
    /** @var CountryModel  */
    protected $countrySearch = null;
    /** @var CountryModel  */
    protected $areaSearchLockedToCountry = null;
    /** @var AreaModel  */
    protected $areaSearch = null;
	protected $hasDateControls = true;
	protected $hasTagControl = false;
	protected $hasGroupControl = false;

	public function getDateControls() {
		return $this->hasDateControls;
	}
	
	public function setHasDateControls($hasDateControls) {
		$this->hasDateControls = $hasDateControls;
	}

    /**
     * @return boolean
     */
    public function isHasGroupControl() {
        return $this->hasGroupControl;
    }

    /**
     * @param boolean $hasGroupControl
     */
    public function setHasGroupControl( $hasGroupControl ) {
        $this->hasGroupControl = $hasGroupControl;
    }

	/**
	 * @return boolean
	 */
	public function isHasTagControl() {
		return $this->hasTagControl;
	}

	/**
	 * @param boolean $hasTagControl
	 */
	public function setHasTagControl( $hasTagControl ) {
		$this->hasTagControl = $hasTagControl;
	}


    protected $hasCountryControl = false;
    protected $hasAreaControl = false;

    /**
     * @param boolean $hasAreaControl
     */
    public function setHasAreaControl($hasAreaControl, $areaSearchLockedToCountry = null)
    {
        $this->hasAreaControl = $hasAreaControl;
        $this->areaSearchLockedToCountry = $areaSearchLockedToCountry;
    }

    /**
     * @return boolean
     */
    public function isHasAreaControl()
    {
        return $this->hasAreaControl;
    }

    /**
     * @param boolean $hasCountryControl
     */
    public function setHasCountryControl($hasCountryControl)
    {
        $this->hasCountryControl = $hasCountryControl;
    }

    /**
     * @return boolean
     */
    public function isHasCountryControl()
    {
        return $this->hasCountryControl;
    }



	
	protected $hasSpecifiedUserControls = false;
	protected $hasSpecifiedUser = null;
	protected $hasSpecifiedUserIncludePrivate = false;
	
	public function getSpecifiedUserControls() {
		return $this->hasSpecifiedUserControls;
	}

	public function setSpecifiedUserControls($hasSpecifiedUserControls, UserAccountModel $user = null, $includePrivate = false) {
		if ($hasSpecifiedUserControls && $user) {
			$this->hasSpecifiedUserControls = true;
			$this->hasSpecifiedUser = $user;
			$this->hasSpecifiedUserIncludePrivate = $includePrivate;
		} else {
			$this->hasSpecifiedUserControls = false;
		}
		return $this;
	}

	// ############################### params

    protected $fallBackFromNow = false;
    protected $fallBackFrom;

    /**
     * If setHasDateControls(false), the Fallback sets what will be used in getGetString() instead.
     * @param $fallBackFromNow
     * @param null $fallBackFrom
     */
    public function setFallBackFrom( $fallBackFromNow, $fallBackFrom = null ) {
        $this->fallBackFromNow = $fallBackFromNow;
        $this->fallBackFrom = is_a($fallBackFrom, "DateTime") ? $fallBackFrom->format('j F Y') : $fallBackFrom;
    }

	protected $fromNow = true;
	protected $from;
	protected $include_deleted = false;
	protected $includeSpecifiedUserAttending = true;
	protected $includeSpecifiedUserWatching = true;


	public function set($data) {
		if (isset($data['eventListFilterDataSubmitted'])) {
		
			// From
			if ($this->hasDateControls) {
				$fromNow = isset($data['fromNow']) ? $data['fromNow'] : 0;
				if (!$fromNow) {
					$this->fromNow = false;
					$from = isset($data['from']) ? strtolower(trim($data['from'])) : null;
					if ($from) {
						try {
							$fromDT = new \DateTime($from, new \DateTimeZone('UTC'));
							$fromDT->setTime(0, 0, 0);
							$this->from = $fromDT->format('j F Y');							
						} catch (\Exception $e) {
							// assume it's parse exception, ignore.
						}
					}
				}
			}
			
			// Specified User Controls
			if ($this->hasSpecifiedUserControls) {
				if (isset($data['specifiedUserWhich']) && $data['specifiedUserWhich'] == "AW") {
					$this->includeSpecifiedUserAttending = true;
					$this->includeSpecifiedUserWatching = true;
				} else if (isset($data['specifiedUserWhich']) && $data['specifiedUserWhich'] == "A") {
					$this->includeSpecifiedUserAttending = true;
					$this->includeSpecifiedUserWatching = false;
				}
			}
			
			// Deleted
			if (isset($data['includeDeleted']) && $data['includeDeleted'] == '1') {
				$this->include_deleted = true;
			}
			
			
			// Free Text Search
			if (isset($data['freeTextSearch']) && trim($data['freeTextSearch'])) {
				$this->freeTextSearch = $data['freeTextSearch'];
			}

            // Tag
            if ($this->hasTagControl && isset($data['tagSearchSlug']) && trim($data['tagSearchSlug'])) {
                $tagRepo = new TagRepository($this->app);
                $tag = $tagRepo->loadBySlug($this->siteModel, $data['tagSearchSlug']);
                if ($tag) {
                    $this->tagSearch = $tag;
                }
            }

            // Group
            if ($this->hasGroupControl && isset($data['groupSearchSlug']) && trim($data['groupSearchSlug'])) {
                $groupRepo = new GroupRepository($this->app);
                $group = $groupRepo->loadBySlug($this->siteModel, $data['groupSearchSlug']);
                if ($group) {
                    $this->groupSearch = $group;
                }
            }

            // Country
            if ($this->siteModel && $this->hasCountryControl && isset($data['countrySearchTwoCharCode']) && trim($data['countrySearchTwoCharCode'])) {
                $countryRepo = new CountryRepository($this->app);
                $country = $countryRepo->loadByTwoCharCode($data['countrySearchTwoCharCode']);
                $countryInSiteRepo = new CountryInSiteRepository($this->app);
                if ($countryInSiteRepo->isCountryInSite($country, $this->siteModel)) {
                    $this->countrySearch = $country;
                };
            }

            // Area
            if ($this->siteModel && $this->hasAreaControl && isset($data['areaSearchSlug']) && trim($data['areaSearchSlug'])) {
                $areaRepo = new AreaRepository($this->app);
                $area = $areaRepo->loadBySiteIDAndAreaSlug($this->siteModel->getId(), $data['areaSearchSlug']);
                if ($area) {
                    $this->areaSearch = $area;
                }
            }

		}
		
		// apply to search
		if ($this->fromNow) {
			$this->eventRepositoryBuilder->setAfterNow();
		} else if ($this->from) {
			$this->eventRepositoryBuilder->setAfter($fromDT);
		}
		$this->eventRepositoryBuilder->setIncludeDeleted($this->include_deleted);
		if ($this->hasSpecifiedUserControls) {
			$this->eventRepositoryBuilder->setUserAccount($this->hasSpecifiedUser, false,
					$this->hasSpecifiedUserIncludePrivate, $this->includeSpecifiedUserAttending, $this->includeSpecifiedUserWatching);
		}
		if ($this->freeTextSearch) {
			$this->eventRepositoryBuilder->setFreeTextsearch($this->freeTextSearch);
		}
		if ($this->tagSearch) {
			$this->eventRepositoryBuilder->setTag($this->tagSearch);
		}
		if ($this->groupSearch) {
			$this->eventRepositoryBuilder->setGroup($this->groupSearch);
		}
        if ($this->countrySearch) {
            $this->eventRepositoryBuilder->setCountry($this->countrySearch);
        }
        if ($this->areaSearch) {
            $this->eventRepositoryBuilder->setArea($this->areaSearch);
        }
	}

    public function getGetString() {

        if ($this->isDefaultFilters()) {
            return '';
        }

        $out = array('eventListFilterDataSubmitted=1');


        // DATE
        if ($this->hasDateControls) {
            if ( $this->fromNow ) {
                $out[] = 'fromNow=1';
            } else if ($this->from) {
                $out[] = 'from=' . $this->from;
            }
        } else {
            if ( $this->fallBackFromNow ) {
                $out[] = 'fromNow=1';
            } else if ($this->fallBackFrom) {
                $out[] = 'from=' . $this->fallBackFrom;
            }
        }


        // USER CONTROLS
        if ($this->hasSpecifiedUserControls) {
            if ($this->includeSpecifiedUserAttending && $this->includeSpecifiedUserWatching) {
                $out[] = 'specifiedUserWhich=AW';
            } else {
                $out[] = 'specifiedUserWhich=A';
            }
        }

        // DELETED
        if ($this->include_deleted) {
            $out[] = 'includeDeleted=1';
        }

        // FREE TEXT
        if ($this->freeTextSearch) {
            $out[]  = 'freeTextSearch='.urlencode($this->freeTextSearch);
        }

        // TAG
        if ($this->hasTagControl && $this->tagSearch) {
            $out[] = 'tagSearchSlug='.urlencode($this->tagSearch->getSlug());
        }

        // GROUP
        if ($this->hasGroupControl && $this->groupSearch) {
            $out[] = 'groupSearchSlug='.urlencode($this->groupSearch->getSlug());
        }

        // COUNTRY
        if ($this->hasCountryControl && $this->countrySearch) {
            $out[] = 'countrySearchTwoCharCode='.urlencode($this->countrySearch->getTwoCharCode());
        }

        // AREA
        if ($this->hasAreaControl && $this->areaSearch) {
            $out[] = 'areaSearchSlug='.urlencode($this->areaSearch->getSlug());
        }

        return implode('&',$out);
    }

    public function getHumanTextRepresentation() {
        $out = array();

        if ($this->hasDateControls) {
            if ( !$this->fromNow && $this->from) {
                $out[] = 'from ' . $this->from;
            }
        }

        // USER CONTROLS
        if ($this->hasSpecifiedUserControls) {
            if ($this->includeSpecifiedUserAttending && $this->includeSpecifiedUserWatching) {
                $out[] = 'attending or watching';
            } else {
                $out[] = 'attending';
            }
        }

        // DELETED
        if ($this->include_deleted) {
            $out[] = 'show deleted';
        }

        // FREE TEXT
        if ($this->freeTextSearch) {
            $out[]  = 'free text search: '.$this->freeTextSearch;
        }

        // TAG
        if ($this->hasTagControl && $this->tagSearch) {
            $out[] = 'tag:'. $this->tagSearch->getTitle();
        }

        // GROUP
        if ($this->hasGroupControl && $this->groupSearch) {
            $out[] = 'group: '.$this->groupSearch->getTitle();
        }

        // Country
        if ($this->hasGroupControl && $this->countrySearch) {
            $out[] = 'country: '.$this->countrySearch->getTitle();
        }

        // Area
        if ($this->hasAreaControl && $this->areaSearch) {
            $out[] = 'area: '.$this->areaSearch->getTitle();
        }

        return implode(", ",$out);
    }
	
	public function getFrom() {
		return $this->from;
	}
	public function getFromNow() {
		return $this->fromNow;
	}

	/**
	 * @param boolean $fromNow
	 */
	public function setFromNow($fromNow)
	{
		$this->fromNow = $fromNow;
	}


	public function getIncludeDeleted() {
		return $this->include_deleted;
	}

	/**
	 * @param boolean $include_deleted
	 */
	public function setIncludeDeleted($include_deleted)
	{
		$this->include_deleted = $include_deleted;
	}



	public function getIncludeSpecifiedUserAttending() {
		return $this->includeSpecifiedUserAttending;
	}

	public function getIncludeSpecifiedUserWatching() {
		return $this->includeSpecifiedUserWatching;
	}

	public function getFreeTextSearch() {
		return $this->freeTextSearch;
	}

	/**
	 * @return TagModel
	 */
	public function getTagSearch() {
		return $this->tagSearch;
	}

    /**
     * @return GroupModel
     */
    public function getGroupSearch() {
        return $this->groupSearch;
    }

    /**
     * @return \repositories\builders\filterparams\AreaModel
     */
    public function getAreaSearch()
    {
        return $this->areaSearch;
    }

    /**
     * @return \repositories\builders\filterparams\CountryModel
     */
    public function getCountrySearch()
    {
        return $this->countrySearch;
    }


    /**
     * @return \repositories\builders\filterparams\CountryModel
     */
    public function getAreaSearchLockedToCountry()
    {
        return $this->areaSearchLockedToCountry;
    }

}


