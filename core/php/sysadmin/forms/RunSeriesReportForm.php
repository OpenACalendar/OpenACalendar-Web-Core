<?php

namespace sysadmin\forms;


use BaseSeriesReport;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunSeriesReportForm extends AbstractType{

	/** @var  BaseSeriesReport */
	protected $report;

	protected $timeZoneName = "Europe/London";

    /** @var Application */
    protected $app;

    function __construct(Application $app, $report)
	{
        $this->app = $app;
		$this->report = $report;
	}


	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder
			->add('output', ChoiceType::class, array(
				'expanded' => true,
				'choices' => array('htmlTable' => 'Table in Web Browser', 'csv' => 'Download CSV'),
				'data' => 'htmlTable',
			));

		if ($this->report->getHasFilterTime()) {


			$builder->add('start_at', DateTimeType::class ,array(
				'label'=>'Start Date & Time',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'required'=>false
			));

			$builder->add('end_at', DateTimeType::class ,array(
				'label'=>'End Date & Time',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'required'=>false
			));

		}

		if ($this->report->getHasFilterSite()) {

			$builder->add('site_id', IntegerType::class ,array(
				'label'=>'Site ID',
				'required'=>false,
				'data'=> ($this->app['config']->isSingleSiteMode ? $this->app['config']->singleSiteID : null),
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
