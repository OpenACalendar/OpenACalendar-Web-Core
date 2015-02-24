<?php

namespace sysadmin\forms;


use oBaseSeriesReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunSeriesByTimeReportForm extends AbstractType{

	/** @var  BaseSeriesReport */
	protected $report;

	protected $timeZoneName = "Europe/London";


	function __construct($report)
	{
		$this->report = $report;

		$this->timeperiodChoices = array(
			"PT1H" => '1 hour',
			"PT4H" => '4 hours',
			"PT12H" => '12 hours',
			"P1D" => '1 day',
			"P7D" => '1 week',
			"P1M" => '1 month',
			"P3M" => '3 months',
			"P6M" => '6 months',
			"P1Y" => '1 year',
		);
	}

	protected $timeperiodChoices;

	public function buildForm(FormBuilderInterface $builder, array $options) {
		global $CONFIG;

		$builder
			->add('output', 'choice', array(
				'expanded' => true,
				'choices' => array('htmlTable' => 'Table in Web Browser', 'csv' => 'Download CSV'),
				'data' => 'htmlTable',
			));

		if ($this->report->getHasFilterSite()) {

			$builder->add('site_id', 'integer' ,array(
				'label'=>'Site ID',
				'required'=>false,
				'data'=> ($CONFIG->isSingleSiteMode ? $CONFIG->singleSiteID : null),
			));
		}

		$builder->add('start_at', 'datetime' ,array(
			'label'=>'Start Date & Time',
			'model_timezone' => 'UTC',
			'view_timezone' => $this->timeZoneName,
			'required'=>true,
			'data'=>new \DateTime("2013-01-01 00:00:00", new \DateTimeZone('UTC')),
		));

		$builder->add('end_at', 'datetime' ,array(
			'label'=>'End Date & Time',
			'model_timezone' => 'UTC',
			'view_timezone' => $this->timeZoneName,
			'required'=>false
		));

		$builder
			->add('timeperiod', 'choice', array(
				'expanded' => true,
				'choices' => $this->timeperiodChoices,
				'data' => "P1M",
			));
	}
	
	public function getName() {
		return 'RunReportForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}
