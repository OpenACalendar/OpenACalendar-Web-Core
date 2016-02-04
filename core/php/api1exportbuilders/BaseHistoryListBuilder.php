<?php
namespace api1exportbuilders;

use InterfaceHistoryModel;
use repositories\builders\HistoryRepositoryBuilder;
use models\SiteModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
use models\TagHistoryModel;
use models\ImportHistoryModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseHistoryListBuilder extends BaseBuilder {
	
	
	/** @var HistoryRepositoryBuilder **/
	protected  $historyRepositoryBuilder;

	protected $histories = array();


	public function __construct(Application $app, SiteModel $site = null, $timeZone  = null, $title = null) {
		parent::__construct($app, $site, $timeZone, $title);
		$this->historyRepositoryBuilder = new HistoryRepositoryBuilder($this->app);
		$this->historyRepositoryBuilder->getHistoryRepositoryBuilderConfig()->setLimit(100);
		if ($site) $this->historyRepositoryBuilder->setSite($site);
	}
	
	
	public abstract function addHistory(InterfaceHistoryModel $history);

	public function build() {	
		foreach($this->historyRepositoryBuilder->fetchAll() as $event) {
			$this->addHistory($event);
		}
	}
	

}

