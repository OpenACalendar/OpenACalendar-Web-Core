<?php

namespace import;
use GuzzleHttp\Client;
use models\ImportedEventModel;
use models\ImportModel;
use models\SiteModel;
use models\GroupModel;
use models\CountryModel;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportRun {

    /** @var Application */
    protected $app;

	/** @var Client */
	protected $guzzle;

	/** @var ImportModel **/
	protected $import;
	
	/** @var SiteModel **/
	protected $site;

	/** @var GroupModel **/
	protected $group;
	
	/** @var CountryModel **/
	protected $country;
	
	/** @var AreaModel **/
	protected $area;
	
	protected $realurl;
		
	public static $FLAG_ADD_UIDS = 1;
	public static $FLAG_SET_TICKET_URL_AS_URL = 2;

	protected $flags = array();

	protected $temporaryFileStorage;
	protected $temporaryFileStorageFromTesting;

    function __construct(Application $app, ImportModel $import, SiteModel $site = null) {
        $this->app = $app;
        $this->import = $import;
        $this->realurl = $import->getUrl();
        if ($site) {
            $this->site = $site;
        } else {
            $siteRepo = new SiteRepository($app);
            $this->site = $siteRepo->loadById($import->getSiteId());
        }
        if ($import->getCountryId()) {
            $countryRepo = new CountryRepository($app);
            $this->country = $countryRepo->loadById($import->getCountryId());
        }
        if ($import->getAreaId()) {
            $areaRepo = new AreaRepository($app);
            $this->area = $areaRepo->loadById($import->getAreaId());
        }
        if ($import->getGroupId()) {
            $groupRepository = new GroupRepository($this->app);
            $this->group = $groupRepository->loadById($import->getGroupId());
        }
        $this->guzzle = new Client(array('defaults' => array('headers' => array(  'User-Agent'=> 'OpenACalendar from ican.openacalendar.org, install '.$this->app['config']->webIndexDomain )) ));
    }

	public function getImport() {
		return $this->import;
	}
	
	public function getSite() {
		return $this->site;
	}

	public function getGroup() {
		return $this->group;
	}

	public function getCountry() {
		return $this->country;
	}	

	public function getArea() {
		return $this->area;
	}

	/**
	 * @return Client
	 */
	public function getGuzzle()
	{
		return $this->guzzle;
	}

	public function downloadURLreturnFileName() {
		if ($this->temporaryFileStorageFromTesting) return $this->temporaryFileStorageFromTesting;
		if ($this->temporaryFileStorage) return $this->temporaryFileStorage;
		
		$request = $this->guzzle->createRequest("GET", $this->getRealUrl());
		$response = $this->guzzle->send($request);
		if ($response->getStatusCode() == 200) {
			$this->temporaryFileStorage = tempnam("/tmp", "oacimport");
			file_put_contents($this->temporaryFileStorage, $response->getBody(true));
			return $this->temporaryFileStorage;
		}

		return null;
	}
	
	public function deleteLocallyStoredURL() {
		if ($this->temporaryFileStorage) {
			unlink($this->temporaryFileStorage);
			$this->temporaryFileStorage = null;
		}
	}
	
	public function setTemporaryFileStorageForTesting($temporaryFileStorageFromTesting) {
		$this->temporaryFileStorageFromTesting = $temporaryFileStorageFromTesting;
		return $this;
	}

	public function setFlag($flag) {  $this->flags[$flag] = true; }
	public function hasFlag($flag) { return isset($this->flags[$flag]) && $this->flags[$flag]; }


    public function setRealUrl($realurl)
    {
        $this->realurl = $realurl;
		$this->deleteLocallyStoredURL();
    }

    public function getRealUrl()
    {
        return $this->realurl;
    }	
	
	function __destruct() {
		$this->deleteLocallyStoredURL();
	}

    protected $importedEventsSeenIds = array();

    function markImportedEventSeen(ImportedEventModel $importedEventModel) {
        $this->importedEventsSeenIds[$importedEventModel->getId()] = true;
    }

    function wasImportedEventSeen(ImportedEventModel $importedEventModel) {
        return isset($this->importedEventsSeenIds[$importedEventModel->getId()]) && $this->importedEventsSeenIds[$importedEventModel->getId()];
    }
	
}

