<?php
namespace api1exportbuilders;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use models\EventModel;
use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\AreaModel;
use models\CountryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListICalBuilder extends BaseEventListBuilder  {

	/** @var ICalEventIdConfig */
	protected $iCalEventIdConfig;
	
	public function __construct(Application $app, SiteModel $site = null, $timeZone  = null, $title = null, ICalEventIdConfig $ICalEventIdConfig = null) {
		parent::__construct($app, $site, $timeZone, $title);
		// We go back a month, just so calendars have a bit of the past available.
		$time = \TimeSource::getDateTime();
		$time->sub(new \DateInterval("P30D"));
		$this->eventRepositoryBuilder->setAfter($time);
		$this->iCalEventIdConfig = $ICalEventIdConfig ? $ICalEventIdConfig : new ICalEventIdConfig();

	}

	
	public function getContents() {
        $calendar =  new \Eluceo\iCal\Component\Calendar('-//OpenACalendar//NONSGML OpenACalendar//EN');
        if ($this->site && !$this->app['config']->isSingleSiteMode) {
            $calendar->setName(($this->title ? $this->title .' - ' : '').$this->site->getTitle().' '.$this->app['config']->installTitle);
        } else {
            $calendar->setName(($this->title ? $this->title .' - ' : '').$this->app['config']->installTitle);
        }
        foreach($this->events as $event) {
            $calendar->addComponent($event);
        }
        return $calendar->render();
	}
	
	public function getResponse() {
		$response = new Response($this->getContents());
		$response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
		$response->setPublic();
		$response->setMaxAge($this->app['config']->cacheFeedsInSeconds);
		return $response;				
	}
	
	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {

		$siteSlug = $this->site ? $this->site->getSlug() : $event->getSiteSlug();

        $eventComponent = new \Eluceo\iCal\Component\Event();



		if ($this->iCalEventIdConfig->isSlug()) {
            $eventComponent->setUniqueId($event->getSlug().'@'.$siteSlug.".".$this->app['config']->webSiteDomain);
		} else if ($this->iCalEventIdConfig->isSlugStartEnd()) {
            $eventComponent->setUniqueId($event->getSlug().'-'.
			                                 md5($event->getStartAtInUTC()->format('c').'-'.$event->getEndAtInUTC()->format('c')).
			                                 '@'.$siteSlug.".".$this->app['config']->webSiteDomain);
		}


        $url = $this->app['config']->getWebSiteDomainSecure($siteSlug) .'/event/'.$event->getSlugForUrl();
        $eventComponent->setUrl($url);

		if ($event->getIsDeleted()) {
            $eventComponent->setSummary($event->getSummaryDisplay(). " [DELETED]");
            $eventComponent->setStatus('CANCELLED');
            $eventComponent->setDescription('DELETED');

			//$txt .= $this->getIcalLine('METHOD','CANCEL');
		} else if ($event->getIsCancelled()) {
            $eventComponent->setSummary($event->getSummaryDisplay(). " [DELETED]");
            $eventComponent->setStatus('CANCELLED');
            $eventComponent->setDescription('CANCELLED');

			//$txt .= $this->getIcalLine('METHOD','CANCEL');
		} else {
            $eventComponent->setSummary($event->getSummaryDisplay());

			$description = '';
			foreach($this->extraHeaders as $extraHeader) {
				$description .= $extraHeader->getText()."\n\n";
			}
			$description .= $event->getDescription()."\n".
					//($event->getUrl() ? $event->getUrl()."\n" : '').
					$url."\n".
					"Powered by ".$this->app['config']->installTitle;
			foreach($this->extraFooters as $extraFooter) {
				$description .= "\n".$extraFooter->getText();
			}
            $eventComponent->setDescription($description);

			$descriptionHTML = "<html><body>";
			foreach($this->extraHeaders as $extraHeader) {
				$descriptionHTML .= "<p>".$extraHeader->getHtml()."</p>";
			}
			$descriptionHTML .=	"<p>".str_replace("\r","",str_replace("\n","<br>",htmlentities($event->getDescription())))."</p>";
			//if ($event->getUrl()) $descriptionHTML .= '<p>More info: <a href="'.$event->getUrl().'">'.$event->getUrl().'</a></p>';
			$descriptionHTML .= '<p>More info: <a href="'.$url.'">'.$url.'</a></p>';
			$descriptionHTML .= '<p style="font-style:italic;font-size:80%">Powered by <a href="'.$url.'">'.$this->app['config']->installTitle.'</a>';
			foreach($this->extraFooters as $extraFooter) {
				$descriptionHTML .= "<br>".$extraFooter->getHtml();
			}
			$descriptionHTML .= '</p>';
			$descriptionHTML .= '</body></html>';
            $eventComponent->setDescriptionHTML($descriptionHTML);

			$locationDetails = array();
			if ($event->getVenue() && $event->getVenue()->getTitle()) {
                $locationDetails[] = $event->getVenue()->getTitle();
            }
			if ($event->getVenue() && $event->getVenue()->getAddress()) {
                $locationDetails[] = $event->getVenue()->getAddress();
            }
			if ($event->getArea() && $event->getArea()->getTitle()) {
                $locationDetails[] = $event->getArea()->getTitle();
            }
			if ($event->getVenue() && $event->getVenue()->getAddressCode()) {
                $locationDetails[] = $event->getVenue()->getAddressCode();
            }
			if ($event->getVenue() && $event->getVenue()->getLat() && $event->getVenue()->getLng()) {
                $eventComponent->setLocation(implode(", ", $locationDetails), $event->getVenue()->getTitle(), $event->getVenue()->getLat().",".$event->getVenue()->getLng());
			} else if ($locationDetails) {
                $eventComponent->setLocation(implode(", ", $locationDetails));
            }
		}

        $eventComponent->setDtStart($event->getStartAt());
        $eventComponent->setDtEnd($event->getEndAt());


        if ($event->getUpdatedAt()) {
            $eventComponent->setModified($event->getUpdatedAt());
            // 1469647083 is a magic number - it's the timestamp at the time we introduced this feature.
            // Since we can't have any values less than that, we will reduce SEQUENCE by that to keep SEQUENCE reasonably small.
            $eventComponent->setSequence($event->getUpdatedAt()->getTimestamp() - 1469647083);
        } else {
            $eventComponent->setSequence(0);
        }
		if ($event->getCreatedAt()) {
            $eventComponent->setDtStamp($event->getCreatedAt());
		} else {
            $eventComponent->setDtStamp(new \DateTime('2010-01-01 01:00:00', new \DateTimeZone('UTC')));
		}

		$this->events[] = $eventComponent;
	}

}


