<?php

namespace org\openacalendar\meetup;

use import\ImportHandlerBase;
use models\ImportedEventModel;
use models\ImportResultModel;
use repositories\ImportedEventRepository;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportMeetupHandler extends ImportHandlerBase {

	/**
	 * It is important this runs before ImportMeetupHandler in Core, as in Core we try to map URL to ICAL data. We prefer using their API if we can.
	 */
	public function getSortOrder() {
		return 2000;
	}

	protected $eventId;
	protected $groupName;

	public function canHandle() {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		$urlBits = parse_url($this->importRun->getRealURL());

		// If you are about to edit the code below stop right there!
		// TODO refactor it to use MeetupURLParser class instead and update that instead.
		if (in_array(strtolower($urlBits['host']), array('meetup.com','www.meetup.com')) && $appKey) {
			
			$bits = explode("/", $urlBits['path']);
			
			if (count($bits) <= 3) {
				$this->groupName = $bits[1];
				return true;
			} else if (count($bits) > 3 && $bits[2] == 'events') {
				$this->eventId = $bits[3];
				return true;
			}
			
		}
		
		return false;
	}

	protected $countNew, $countExisting, $countSaved, $countInPast, $countToFarInFuture, $countNotValid;

	
	public function handle() {
		$this->countNew = 0;
		$this->countExisting = 0;
		$this->countSaved = 0;
		$this->countInPast = 0;
		$this->countToFarInFuture = 0;
		$this->countNotValid = 0;

		$iurlr = new ImportResultModel();
		$iurlr->setIsSuccess(true);
		$iurlr->setMessage("Meetup data found");
		
		try {
			if ($this->eventId) {
				$meetupData = $this->getMeetupDataForEventID($this->eventId);
				if ($meetupData) {
					$this->processMeetupData($meetupData);
				}
			} else if ($this->groupName) {
				foreach($this->getMeetupDatasForGroupname($this->groupName) as $meetupData) {
					$this->processMeetupData($meetupData);
				}
			}
		} catch (ImportURLMeetupHandlerAPIError $err) {
			$iurlr->setIsSuccess(false);
			$iurlr->setMessage("Meetup API error: ". $err->getCode()." ".$err->getMessage());
		}

		$iurlr->setNewCount($this->countNew);
		$iurlr->setExistingCount($this->countExisting);
		$iurlr->setSavedCount($this->countSaved);
		$iurlr->setInPastCount($this->countInPast);
		$iurlr->setToFarInFutureCount($this->countToFarInFuture);
		$iurlr->setNotValidCount($this->countNotValid);
		return $iurlr;
		
	}	
	
	protected function getMeetupDataForEventID($id) {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		// Avoid Throttling
		sleep(1);

        try {
            $request = $this->importRun->getGuzzle()->createRequest("GET", "https://api.meetup.com/2/event/".$id."?sign=true&key=".$appKey."&fields=timezone&text_format=plain");
            $response = $this->importRun->getGuzzle()->send($request);

            if ($response->getStatusCode() == 200) {
                $data =  $response->json();
                if (isset($data['code']) && $data['code']) {
                    if ($data['code'] == 'not_authorized') {
                        throw new ImportURLMeetupHandlerAPIError("API Key is not working",1);
                    } else if ($data['code'] == 'throttled') {
                        sleep(15);
                        throw new ImportURLMeetupHandlerAPIError("Our Access has been throttled",1);
                    } else if ($data['code'] == 'blocked') {
                        throw new ImportURLMeetupHandlerAPIError("Our Access has been blocked temporarily because throttling failed",1);
                    }
                }
                return $data;
            } else {
                throw new ImportURLMeetupHandlerAPIError("Non 200 response - got ".$response->getStatusCode(), 1);
            }
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            throw new ImportURLMeetupHandlerAPIError( "Got Exception " . $e->getMessage(), 1 );
        }
		
	}
	
	protected function getMeetupDatasForGroupname($groupName) {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		// Avoid Throttling
		sleep(1);

		$url = "https://api.meetup.com/2/events/?sign=true&key=".$appKey.
			"&fields=timezone&text_format=plain&group_urlname=".
			str_replace(array("&","?"), array("",""),$groupName);


        try {
            $request = $this->importRun->getGuzzle()->createRequest( "GET", $url );
            $response = $this->importRun->getGuzzle()->send( $request );

            if ( $response->getStatusCode() == 200 ) {
                $data = $response->json();
                if ( isset( $data['code'] ) && $data['code'] ) {
                    if ( $data['code'] == 'not_authorized' ) {
                        throw new ImportURLMeetupHandlerAPIError( "API Key is not working", 1 );
                    } else if ( $data['code'] == 'throttled' ) {
                        sleep( 15 );
                        throw new ImportURLMeetupHandlerAPIError( "Our Access has been throttled", 1 );
                    } else if ( $data['code'] == 'blocked' ) {
                        throw new ImportURLMeetupHandlerAPIError( "Our Access has been blocked temporarily because throttling failed", 1 );
                    }
                }
                if ( isset( $data['results'] ) && is_array( $data['results'] ) ) {
                    return $data['results'];
                }
            } else {
                throw new ImportURLMeetupHandlerAPIError( "Non 200 response - got " . $response->getStatusCode(), 1 );
            }

            return array();
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            throw new ImportURLMeetupHandlerAPIError( "Got Exception " . $e->getMessage(), 1 );
        }
	}

    protected function processMeetupData($meetupData) {
        $start = new \DateTime('', new \DateTimeZone('UTC'));
        $start->setTimestamp($meetupData['time'] / 1000);
        if (isset($meetupData['duration']) && $meetupData['duration']) {
            $end = new \DateTime('', new \DateTimeZone('UTC'));
            $end->setTimestamp($meetupData['time'] / 1000);
            $end->add(new \DateInterval("PT".($meetupData['duration'] / 1000)."S"));
        } else {
            $end = clone $start;
            $end->add(new \DateInterval("PT3H"));
        }
        if ($start && $end && $start <= $end) {

            $importedEventRepo = new \repositories\ImportedEventRepository($this->app);
            $id = "event_".$meetupData['id']."@meetup.com";
            $importedEvent = $importedEventRepo->loadByImportIDAndIdInImport($this->importRun->getImport()->getId() ,$id);

            $changesToSave = false;
            if (!$importedEvent) {
                if ($meetupData['status'] != 'cancelled') {
                    ++$this->countNew;
                    $importedEvent = new ImportedEventModel();
                    $importedEvent->setIdInImport($id);
                    $importedEvent->setImportId($this->importRun->getImport()->getId());
                    $this->setImportedEventFromMeetupData($importedEvent, $meetupData);
                    $changesToSave = true;
                }
            } else {
                ++$this->countExisting;
                if ($meetupData['status'] == 'cancelled') {
                    if (!$importedEvent->getIsDeleted()) {
                        $importedEvent->setIsDeleted(true);
                        $changesToSave = true;
                    }
                } else {
                    $changesToSave = $this->setImportedEventFromMeetupData($importedEvent, $meetupData);
                    // if was deleted, undelete
                    if ($importedEvent->getIsDeleted()) {
                        $importedEvent->setIsDeleted(false);
                        $changesToSave = true;
                    }
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
	
	protected function setImportedEventFromMeetupData(ImportedEventModel $importedEvent, $meetupData) {
		$changesToSave = false;
		if (isset($meetupData['description'])) {
			$description =  $meetupData['description'];
			if ($importedEvent->getDescription() != $description) {
				$importedEvent->setDescription($description);
				$changesToSave = true;
			}
		}
		$start = new \DateTime('', new \DateTimeZone('UTC'));
		$start->setTimestamp($meetupData['time'] / 1000);
		if (isset($meetupData['duration']) && $meetupData['duration']) {
			$end = new \DateTime('', new \DateTimeZone('UTC'));
			$end->setTimestamp($meetupData['time'] / 1000);
			$end->add(new \DateInterval("PT".($meetupData['duration'] / 1000)."S"));
		} else {
			$end = clone $start;
			$end->add(new \DateInterval("PT3H"));
		}
		if (!$importedEvent->getStartAt() || $importedEvent->getStartAt()->getTimeStamp() != $start->getTimeStamp()) {
			$importedEvent->setStartAt($start);
			$changesToSave = true;
		}
		if (!$importedEvent->getEndAt() || $importedEvent->getEndAt()->getTimeStamp() != $end->getTimeStamp()) {
			$importedEvent->setEndAt($end);
			$changesToSave = true;
		}
		if ($importedEvent->getTitle() != $meetupData['name']) {
			$importedEvent->setTitle($meetupData['name']);
			$changesToSave = true;
		}
		if ($importedEvent->getUrl() != $meetupData['event_url']) {
			$importedEvent->setUrl($meetupData['event_url']);
			$changesToSave = true;
		}
		if ($importedEvent->getTimezone() != $meetupData['timezone']) {
			$importedEvent->setTimezone($meetupData['timezone']);
			$changesToSave = true;
		}
		if ($importedEvent->getTicketUrl() != $meetupData['event_url']) {
			$importedEvent->setTicketUrl($meetupData['event_url']);
			$changesToSave = true;
		}
        if (isset($meetupData['venue']) && isset($meetupData['venue']['lon']) && $meetupData['venue']['lon'] != $importedEvent->getLng()) {
            $importedEvent->setLng($meetupData['venue']['lon']);
            $changesToSave = true;
        }
        if (isset($meetupData['venue']) && isset($meetupData['venue']['lat']) && $meetupData['venue']['lat'] != $importedEvent->getLat()) {
            $importedEvent->setLat($meetupData['venue']['lat']);
            $changesToSave = true;
        }
		return $changesToSave;
	}
	
}




class ImportURLMeetupHandlerAPIError extends \Exception {

}


