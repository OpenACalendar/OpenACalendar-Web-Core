<?php

namespace sysadmin\forms;


use BaseSeriesReport;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunValueReportForm extends AbstractType{

	/** @var  BaseSeriesReport */
	protected $report;

	protected $timeZoneName = "Europe/London";


	function __construct($report)
	{
		$this->report = $report;
	}


	public function buildForm(FormBuilderInterface $builder, array $options) {
		global $CONFIG;

		$builder
			->add('output', 'choice', array(
				'expanded' => true,
				'choices' => array('htmlTable' => 'Table in Web Browser'),
				'data' => 'htmlTable',
			));

		if ($this->report->getHasFilterTime()) {


			$builder->add('start_at', 'datetime' ,array(
				'label'=>'Start Date & Time',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'required'=>false
			));

			$builder->add('end_at', 'datetime' ,array(
				'label'=>'End Date & Time',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'required'=>false
			));

		}

		if ($this->report->getHasFilterSite()) {

			$builder->add('site_id', 'integer' ,array(
				'label'=>'Site ID',
				'required'=>false,
				'data'=> ($CONFIG->isSingleSiteMode ? $CONFIG->singleSiteID : null),
			));
		}
	}
	
	public function getName() {
		return 'RunReportForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}
