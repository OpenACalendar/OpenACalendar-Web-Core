<?php

namespace org\openacalendar\meetup;

use import\ImportURLHandlerBase;
use import\ImportedEventsToEvents;
use models\ImportedEventModel;
use models\ImportURLResultModel;
use repositories\ImportedEventRepository;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLMeetupHandler extends ImportURLHandlerBase {
	
	protected $eventId;
	protected $groupName;

	public function canHandle() {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		$urlBits = parse_url($this->importURLRun->getRealURL());
		
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
	
	/** @var \import\ImportedEventsToEvents **/
	protected $importedEventsToEvents;

	
	public function handle() {
		global $app;

		$this->countNew = 0;
		$this->countExisting = 0;
		$this->countSaved = 0;
		$this->countInPast = 0;
		$this->countToFarInFuture = 0;
		$this->countNotValid = 0;
		
		$this->importedEventsToEvents = new ImportedEventsToEvents();
		$this->importedEventsToEvents->setFromImportURlRun($this->importURLRun);
		
		$iurlr = new ImportURLResultModel();
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
		
		// Now run the thing to make imported events real events!
		$this->importedEventsToEvents->run();

		$iurlr->setNewCount($this->countNew);
		$iurlr->setExistingCount($this->countExisting);
		$iurlr->setSavedCount($this->countSaved);
		$iurlr->setInPastCount($this->countInPast);
		$iurlr->setToFarInFutureCount($this->countToFarInFuture);
		$iurlr->setNotValidCount($this->countNotValid);
		return $iurlr;
		
	}	
	
	protected function getMeetupDataForEventID($id) {
		global $app, $CONFIG;
		
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		// Avoid Throttling
		sleep(1);
		
		$ch = curl_init();      
		curl_setopt($ch, CURLOPT_URL, "https://api.meetup.com/2/event/".$id."?sign=true&key=".$appKey."&fields=timezone");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'OpenACalendar from ican.openacalendar.org, install '.$CONFIG->webIndexDomain);
		$output = curl_exec($ch);
		//$response = curl_getinfo( $ch );
		curl_close($ch);
		
		if ($output) {
			$data =  json_decode($output);
			if (property_exists($data, 'code')) {
				if ($data->code == 'not_authorized') {
					throw new ImportURLMeetupHandlerAPIError("API Key is not working",1);
				} else if ($data->code == 'throttled') {
					sleep(15);
					throw new ImportURLMeetupHandlerAPIError("Our Access has been throttled",1);
				} else if ($data->code == 'blocked') {
					throw new ImportURLMeetupHandlerAPIError("Our Access has been blocked temporarily because throttling failed",1);
				}
			}
			return $data;
		}
		
	}
	
	protected function getMeetupDatasForGroupname($groupName) {
		global $app, $CONFIG;
		
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		// Avoid Throttling
		sleep(1);
		
		$ch = curl_init();      
		curl_setopt($ch, CURLOPT_URL, "https://api.meetup.com/2/events/?sign=true&key=".$appKey.
				"&fields=timezone&group_urlname=".
				str_replace(array("&","?"), array("",""),$groupName));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'OpenACalendar from ican.openacalendar.org, install '.$CONFIG->webIndexDomain);
		$output = curl_exec($ch);
		//$response = curl_getinfo( $ch );
		curl_close($ch);
		
		if ($output) {
			$data =  json_decode($output);
			if (property_exists($data, 'code')) {
				if ($data->code == 'not_authorized') {
					throw new ImportURLMeetupHandlerAPIError("API Key is not working",1);
				} else if ($data->code == 'throttled') {
					sleep(15);
					throw new ImportURLMeetupHandlerAPIError("Our Access has been throttled",1);
				} else if ($data->code == 'blocked') {
					throw new ImportURLMeetupHandlerAPIError("Our Access has been blocked temporarily because throttling failed",1);
				}
			}
			if (property_exists($data, 'results') && is_array($data->results)) {
				return $data->results;
			}
		}

		return array();
	}
	
	protected function processMeetupData($meetupData) {
		global $CONFIG;
		$start = new \DateTime('', new \DateTimeZone('UTC'));
		$start->setTimestamp($meetupData->time / 1000);
		if (property_exists($meetupData, 'duration') && $meetupData->duration) {
			$end = new \DateTime('', new \DateTimeZone('UTC'));
			$end->setTimestamp($meetupData->time / 1000);
			$end->add(new \DateInterval("PT".($meetupData->duration / 1000)."S"));
		} else {
			$end = clone $start;
			$end->add(new \DateInterval("PT3H"));
		}
		if ($start && $end && $start <= $end) { 
			if ($end->getTimeStamp() < \TimeSource::time()) {
				$this->countInPast++;
			} else if ($start->getTimeStamp() > \TimeSource::time()+$CONFIG->importURLAllowEventsSecondsIntoFuture) {
				$this->countToFarInFuture++;
			} else {
		
				$importedEventRepo = new \repositories\ImportedEventRepository();
				$id = "event_".$meetupData->id."@meetup.com";
				$importedEvent = $importedEventRepo->loadByImportURLIDAndImportId($this->importURLRun->getImportURL()->getId() ,$id);

				$changesToSave = false;
				if (!$importedEvent) {
					if ($meetupData->status != 'cancelled') {
						++$this->countNew;
						$importedEvent = new ImportedEventModel();						
						$importedEvent->setImportId($id);
						$importedEvent->setImportUrlId($this->importURLRun->getImportURL()->getId());
						$this->setImportedEventFromMeetupData($importedEvent, $meetupData);							
						$changesToSave = true;
					}
				} else {
					++$this->countExisting;
					if ($meetupData->status == 'cancelled') {
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
				if ($changesToSave && $this->countSaved < $this->limitToSaveOnEachRun) {
					++$this->countSaved;
					$this->importedEventsToEvents->addImportedEvent($importedEvent);

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

			}
		}
	}
	
	protected function setImportedEventFromMeetupData(ImportedEventModel $importedEvent, $meetupData) {
		$changesToSave = false;
		if (property_exists($meetupData, 'description')) {
			$description = str_replace('</p>',"\n\n",$meetupData->description);
			$description = html_entity_decode(strip_tags($description));
			if ($importedEvent->getDescription() != $description) {
				$importedEvent->setDescription($description);
				$changesToSave = true;
			}
		} else {
			$description = '';
		}
		$start = new \DateTime('', new \DateTimeZone('UTC'));
		$start->setTimestamp($meetupData->time / 1000);
		if (property_exists($meetupData, 'duration') && $meetupData->duration) {
			$end = new \DateTime('', new \DateTimeZone('UTC'));
			$end->setTimestamp($meetupData->time / 1000);
			$end->add(new \DateInterval("PT".($meetupData->duration / 1000)."S"));
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
		if ($importedEvent->getTitle() != $meetupData->name) {
			$importedEvent->setTitle($meetupData->name);
			$changesToSave = true;
		}
		if ($importedEvent->getUrl() != $meetupData->event_url) {
			$importedEvent->setUrl($meetupData->event_url);
			$changesToSave = true;
		}
		if ($importedEvent->getTimezone() != $meetupData->timezone) {
			$importedEvent->setTimezone($meetupData->timezone);
			$changesToSave = true;
		}
		if ($importedEvent->getTicketUrl() != $meetupData->event_url) {
			$importedEvent->setTicketUrl($meetupData->event_url);
			$changesToSave = true;
		}
		return $changesToSave;
	}
	
}




class ImportURLMeetupHandlerAPIError extends \Exception {

}


