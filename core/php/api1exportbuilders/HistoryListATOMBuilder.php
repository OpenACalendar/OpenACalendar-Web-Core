<?php
namespace api1exportbuilders;


use InterfaceHistoryModel;
use models\SiteModel;
use models\EventModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
use models\TagHistoryModel;
use models\ImportHistoryModel;
use repositories\builders\HistoryRepositoryBuilder;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HistoryListATOMBuilder extends BaseHistoryListBuilder {
	use TraitATOM;

	public function __construct(Application $app, SiteModel $site = null, string $timeZone  = null) {
		parent::__construct($app, $site, $timeZone);
	}

	
	public function getContents() {
		$txt = '<?xml version="1.0" encoding="utf-8"?>';
		$txt .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		if ($this->site && !$this->app['config']->isSingleSiteMode) {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					 htmlentities($this->site->getTitle()).' '.
					 htmlentities($this->app['config']->installTitle).
					'</title>'."\n";
		} else {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					 htmlentities($this->app['config']->installTitle).
					'</title>'."\n";
		}
		$txt .= '<id>'.$this->feedURL.'</id>'."\n";
		$txt .= '<link rel="self" href="'.$this->feedURL.'"/>'."\n";
		$txt .= '<updated>'.date("Y-m-d").'T'.date("H:i:s").'Z</updated>';
		$txt .= implode("",$this->histories)."\n";
		$txt .= '</feed>'."\n";		
		return $txt;
	}	



	public function addHistory(InterfaceHistoryModel $history) {
		foreach($this->app['extensions']->getExtensionsIncludingCore() as $ext) {
			/** @var $r InterfaceNewsFeedModel */
			$r = $ext->getNewsFeedModel($history, $this->site);
			if ($r) {
				$txt = '<entry>';
				$txt .= '<id>'.$r->getID().'</id>';
				$txt .= '<link href="'.$r->getURL().'"/>';
				$txt .= '<title>'.  $this->getData($r->getTitle()).'</title>';
				$txt .= '<summary>'.$this->getBigData($r->getSummary()).'</summary>';
				$txt .= '<updated>'.$r->getCreatedAt()->format("Y-m-d")."T".$r->getCreatedAt()->format("H:i:s")."Z</updated>";
				$txt .= '<author><name>'.$this->app['config']->installTitle.'</name></author></entry>'." \r\n";
				$this->histories[] = $txt;
				return;
			}
		}
	}

}

