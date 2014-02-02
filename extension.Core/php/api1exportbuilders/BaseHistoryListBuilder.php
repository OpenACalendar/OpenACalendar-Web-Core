<?
namespace api1exportbuilders;

use repositories\builders\HistoryRepositoryBuilder;
use models\SiteModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseHistoryListBuilder extends BaseBuilder {
	
	
	/** @var HistoryRepositoryBuilder **/
	protected  $historyRepositoryBuilder;

	protected $histories = array();


	public function __construct(SiteModel $site = null, $timeZone  = null, $title = null) {
		parent::__construct($site, $timeZone, $title);
		$this->historyRepositoryBuilder = new HistoryRepositoryBuilder();
		if ($site) $this->historyRepositoryBuilder->setSite($site);
	}
	
	
	public function addHistory($history) {
		if (is_a($history,'models\EventHistoryModel')) {
			$this->addEventHistory($history);
		} else if (is_a($history,'models\GroupHistoryModel')) {
			$this->addGroupHistory($history);
		} else if (is_a($history,'models\VenueHistoryModel')) {
			$this->addVenueHistory($history);
		} else {
			die(get_class($history));
		}
	}
	
	
	public abstract function addEventHistory(EventHistoryModel $history) ;
	
	public abstract function addGroupHistory(GroupHistoryModel $history) ;
	
	public abstract function addVenueHistory(VenueHistoryModel $history) ;
	
	public function build() {	
		foreach($this->historyRepositoryBuilder->fetchAll() as $event) {
			$this->addHistory($event);
		}
	}
	

}

