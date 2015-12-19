<?php

namespace reports;
use BaseSeriesReport;
use ReportDataItemLabelTimeRange;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SeriesOfSeriesByTimeReport {

	/** @var  BaseSeriesReport */
	protected $report;

	/** @var  \DateTime */
	protected $start;

	/** @var  \DateTime */
	protected $end;

	protected $timeperiod;

	function __construct(BaseSeriesReport $report, \DateTime $start, $end=null, $timeperiod="P1M")
	{
		$this->end = $end ? $end : \TimeSource::getDateTime();
		$this->report = $report;
		$this->start = $start;
		$this->timeperiod = $timeperiod;
	}


	protected function buildTimeSeries() {
		$this->data = array();
		$this->dataKeys = array();

		$currentStart = clone $this->start;
		$interval = new \DateInterval($this->timeperiod);
		$interval1Sec = new \DateInterval("PT1S");


		while($currentStart < $this->end) {
			$currentEnd = clone $currentStart;
			$currentEnd->add($interval);
			$currentEnd->sub($interval1Sec);
			$this->data[] = new ReportDataItemLabelTimeRange($currentStart, $currentEnd);
			$currentStart->add($interval);
		}

	}

	public function run() {
		$this->buildTimeSeries();
		foreach($this->data as $dataPoint) {
			$this->report->setFilterTime($dataPoint->getLabelStart(), $dataPoint->getLabelEnd());
			$this->report->run();
			$reportData = $this->report->getData();
			foreach($reportData as $k=>$v) {
				$this->dataKeys[$k] = $v;
			}
			$dataPoint->setData($reportData);
		}
	}

	protected $data;

	protected $dataKeys;

	public function getData() {
		return $this->data;
	}

	public function getDataKeys() {
		return $this->dataKeys;
	}
} 
