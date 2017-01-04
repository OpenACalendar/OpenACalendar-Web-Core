<?php

namespace org\openacalendar\facebook;

use import\ImportHandlerBase;
use models\ImportedEventModel;
use models\ImportResultModel;
use repositories\ImportedEventRepository;

/**
 *
 * @package org.openacalendar.facebook
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportFacebookHandler extends ImportHandlerBase {
	
	protected $eventId;


	
	
	public function canHandle() {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
		$appID = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));
		$appSecret = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
		$userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));
		
		$urlBits = parse_url($this->importRun->getRealURL());
		
		if ($urlBits['host']== 'facebook.com' || $urlBits['host']== 'www.facebook.com')  {
			
			$bits =  explode("/",$urlBits['path']);
			
			if ($bits[1] == 'events' && $bits[2] && $appID && $appSecret && $userToken) {
				$this->eventId = $bits[2];
				return true;
			}
			
		}
		
		return false;
	}

	protected $countNew, $countExisting, $countSaved, $countInPast, $countToFarInFuture, $countNotValid;

    protected $facebook;

	public function handle() {
		$this->countNew = 0;
		$this->countExisting = 0;
		$this->countSaved = 0;
		$this->countInPast = 0;
		$this->countToFarInFuture = 0;
		$this->countNotValid = 0;
		
		$extension = $this->app['extensions']->getExtensionById('org.openacalendar.facebook');
		$appID = $this->app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));
		$appSecret = $this->app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
		$userToken = $this->app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));

        $this->facebook = new \Facebook\Facebook([
            'app_id' => $appID,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.7',
            'default_access_token' => $userToken,
        ]);

		$iurlr = new ImportResultModel();
		$iurlr->setIsSuccess(true);
		$iurlr->setMessage("Facebook data found");
				
		if ($this->eventId && $appID && $appSecret && $userToken) {
		
			try {
				$fbData = $this->getFBDataForEventID($this->eventId);
				if ($fbData) {
					$this->processFBData($this->eventId, $fbData);
				}
			} catch (\Facebook\Exceptions\FacebookResponseException $err) {
				$iurlr->setIsSuccess(false);
				$iurlr->setMessage("Facebook API error: ". $err->getCode()." ".$err->getMessage());
			} catch (\Facebook\Exceptions\FacebookSDKException $err) {
				$iurlr->setIsSuccess(false);
				$iurlr->setMessage("Facebook API error: ". $err->getCode()." ".$err->getMessage());
			}
		
		}

		$iurlr->setNewCount($this->countNew);
		$iurlr->setExistingCount($this->countExisting);
		$iurlr->setSavedCount($this->countSaved);
		$iurlr->setInPastCount($this->countInPast);
		$iurlr->setToFarInFutureCount($this->countToFarInFuture);
		$iurlr->setNotValidCount($this->countNotValid);
		return $iurlr;
		
	}	
	
	protected function getFBDataForEventID($id) {

		$url = '/' . strval($this->eventId);

        try {
            $response = $this->facebook->get( $url );
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return null;
        }
        $graphObject = $response->getGraphNode();

        // This tests 2 things
        // 1) Can some events not have a start and a end time set? Saw comments that suggested this.
        // 2) How do we know this FB event is an event? It could be group or a page.
        if ( $graphObject->getField( 'start_time' ) ) {

            return array(
                'name'         => $graphObject->getField( 'name' ),
                'description'  => $graphObject->getField( 'description' ),
                'ticket_uri'   => $graphObject->getField( 'ticket_uri' ),
                'start_time'   => $graphObject->getField( 'start_time' ),
                'end_time'     => $graphObject->getField( 'end_time' ),
                'timezone'     => $graphObject->getField( 'timezone' ),
                'is_date_only' => $graphObject->getField( 'is_date_only' ),
                'url'          => 'https://www.facebook.com/events/' . $id,
                'lat'         => ($graphObject->getField('place') && $graphObject->getField('place')->getField('location') ? $graphObject->getField('place')->getField('location')->getField('latitude') : null),
                'lng'         => ($graphObject->getField('place') && $graphObject->getField('place')->getField('location') ? $graphObject->getField('place')->getField('location')->getField('longitude') : null),
            );

        }

	}
	
	protected function processFBData($id, $fbData) {
        $start = clone $fbData['start_time'];
        $start->setTimezone(new \DateTimeZone('UTC'));
        if ($fbData['end_time']) {
            $end = clone $fbData['end_time'];
            $end->setTimezone(new \DateTimeZone('UTC'));
        } else {
            $end = clone $start;
        }
		if ($start && $end && $start <= $end) { 

            $importedEventRepo = new \repositories\ImportedEventRepository($this->app);
            $importedEvent = $importedEventRepo->loadByImportIDAndIdInImport($this->importRun->getImport()->getId() ,$id);

            $changesToSave = false;
            if (!$importedEvent) {
                ++$this->countNew;
                $importedEvent = new ImportedEventModel();
                $importedEvent->setIdInImport($id);
                $importedEvent->setImportId($this->importRun->getImport()->getId());
                $this->setImportedEventFromFBData($importedEvent, $fbData);
                $changesToSave = true;
            } else {
                ++$this->countExisting;
                $changesToSave = $this->setImportedEventFromFBData($importedEvent, $fbData);
                // if was deleted, undelete
                if ($importedEvent->getIsDeleted()) {
                    $importedEvent->setIsDeleted(false);
                    $changesToSave = true;
                }
            }
            if ($changesToSave && $this->countSaved < $this->app['config']->importLimitToSaveOnEachRunImportedEvents) {
                ++$this->countSaved;

                if ($importedEvent->getId()) {
                    if ($importedEvent->getIsDeleted()) {
                        $importedEventRepo->delete($importedEvent);
                    } else {
                        $importedEventRepo->edit($importedEvent);
                    }
                } else {
                    $importedEventRepo->create($importedEvent);
                }
            }

            $this->importRun->markImportedEventSeen($importedEvent);
		}
	}
	
	protected function setImportedEventFromFBData(ImportedEventModel $importedEvent, $fbData) {
		$changesToSave = false;
		if ($importedEvent->getDescription() != $fbData['description']) {
			$importedEvent->setDescription($fbData['description']);
			$changesToSave = true;
		}

        $start = clone $fbData['start_time'];
        $start->setTimezone(new \DateTimeZone('UTC'));
        if ($fbData['end_time']) {
            $end = clone $fbData['end_time'];
            $end->setTimezone(new \DateTimeZone('UTC'));
        } else {
            $end = clone $start;
        }
		if ($fbData['is_date_only']) {
			$start->setTime(0,0,0);
			$end->setTime(23, 59, 59);
		}
		if (!$importedEvent->getStartAt() || $importedEvent->getStartAt()->getTimeStamp() != $start->getTimeStamp()) {
			$importedEvent->setStartAt($start);
			$changesToSave = true;
		}
		if (!$importedEvent->getEndAt() || $importedEvent->getEndAt()->getTimeStamp() != $end->getTimeStamp()) {
			$importedEvent->setEndAt($end);
			$changesToSave = true;
		}
		if ($importedEvent->getTitle() != $fbData['name']) {
			$importedEvent->setTitle($fbData['name']);
			$changesToSave = true;
		}
		if ($importedEvent->getUrl() != $fbData['url']) {
			$importedEvent->setUrl($fbData['url']);
			$changesToSave = true;
		}
		if ($importedEvent->getTimezone() != $fbData['timezone']) {
			$importedEvent->setTimezone($fbData['timezone']);
			$changesToSave = true;
		}
		if ($importedEvent->getTicketUrl() != $fbData['ticket_uri']) {
			$importedEvent->setTicketUrl($fbData['ticket_uri']);
			$changesToSave = true;
		}
        if (isset($fbData['lng']) && $fbData['lng'] != $importedEvent->getLng()) {
            $importedEvent->setLng($fbData['lng']);
            $changesToSave = true;
        }
        if (isset($fbData['lat']) && $fbData['lat'] != $importedEvent->getLat()) {
            $importedEvent->setLat($fbData['lat']);
            $changesToSave = true;
        }
		return $changesToSave;
	}
	
}

