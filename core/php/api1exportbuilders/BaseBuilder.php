<?php
namespace api1exportbuilders;

use repositories\builders\EventRepositoryBuilder;
use models\SiteModel;
use models\EventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseBuilder {

	protected $title;
	
	/** @var SiteModel **/
	protected $site;


	
	protected $localTimeZone;
	
	public function __construct(SiteModel $site = null, $timeZone = null, $title = null) {		
		global $CONFIG;
		$this->site = $site;
		$this->localTimeZone = new \DateTimeZone($timeZone ? $timeZone : "UTC");
		$this->title = $title;
		if ($CONFIG->apiExtraHeader1Html || $CONFIG->apiExtraHeader1Text) {
			$this->addExtraHeader($CONFIG->apiExtraHeader1Html, $CONFIG->apiExtraHeader1Text);
		}
		if ($CONFIG->apiExtraFooter1Html || $CONFIG->apiExtraFooter1Text) {
			$this->addExtraFooter($CONFIG->apiExtraFooter1Html, $CONFIG->apiExtraFooter1Text);
		}
	}

	
	abstract public function getContents();
	abstract public function getResponse();
	
	
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	protected $extraHeaders = array();
	
	public function addExtraHeader($html, $text) {
		$this->extraHeaders[] = new ExportBuilderExtraHeaderOrFooter($html,$text);
	}

	protected $extraFooters = array();

	public function addExtraFooter($html, $text) {
		$this->extraFooters[] = new ExportBuilderExtraHeaderOrFooter($html,$text);
	}
	
}

class ExportBuilderExtraHeaderOrFooter {
	protected $html;
	protected $text;
	function __construct($html, $text) {
		$this->html = $html;
		$this->text = $text;
	}
	public function getHtml() {
		return $this->html;
	}
	public function getText() {
		return $this->text;
	}
}



