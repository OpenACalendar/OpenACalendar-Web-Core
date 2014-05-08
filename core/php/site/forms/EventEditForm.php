<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class EventEditForm extends AbstractType{

	protected $timeZoneName;
	/** @var SiteModel **/
	protected $site;
	
	function __construct(SiteModel $site, $timeZoneName) {
		$this->site = $site;
		$this->timeZoneName = $timeZoneName;
	}

	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('summary', 'text', array(
			'label'=>'Summary',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus')
		));
		
		$builder->add('description', 'textarea', array(
			'label'=>'Description',
			'required'=>false
		));
		
		$builder->add('url', 'url', array(
			'label'=>'Web Page URL',
			'required'=>false
		));
		
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteIn($this->site);
		$countries = array();
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getId()] = $country->getTitle();
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', 'choice', array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
		));
		
		$timezones = array();
		// Must explicetly set name as key otherwise Symfony forms puts an ID in, and that's no good for processing outside form
		foreach($this->site->getCachedTimezonesAsList() as $timezone) {
			$timezones[$timezone] = $timezone;
		}		// TODO if current timezone not in list add it now
		$builder->add('timezone', 'choice', array(
			'label'=>'Time Zone',
			'choices' => $timezones,
			'required' => true,
		));
			
				
		if ($this->site->getIsFeatureVirtualEvents()) {
			
			//  if both are an option, user must check which one.
			if ($this->site->getIsFeaturePhysicalEvents()) {
			
				$builder->add("is_virtual",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Is event accessible online?'
						)
					);
			}
			
		}

		
		if ($this->site->getIsFeaturePhysicalEvents()) {
			
			// if both are an option, user must check which one.
			if ($this->site->getIsFeatureVirtualEvents()) {
				
				$builder->add("is_physical",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Does the event happen at a place?'
						)
					);
				
			}

		}
		
		$years = array( date('Y'), date('Y')+1 );
		
		$builder->add('start_at', 'datetime' ,array(
				'label'=>'Start Date & Time',
				'date_widget'=> 'single_text',
				'date_format'=>'d/M/y',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'years' => $years,
				'attr' => array('class' => 'dateInput'),
				'required'=>true
			));

		$builder->add('end_at', 'datetime' ,array(
				'label'=>'End Date & Time',
				'date_widget'=> 'single_text',
				'date_format'=>'d/M/y',
				'model_timezone' => 'UTC',
				'view_timezone' => $this->timeZoneName,
				'years' => $years,
				'attr' => array('class' => 'dateInput'),
				'required'=>true
			));
				
		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			global $CONFIG;
			$form = $event->getForm();
			$myExtraFieldStart = $form->get('start_at')->getData();
			$myExtraFieldEnd = $form->get('end_at')->getData();
			// Validate end is not the same as start
			if ($myExtraFieldStart == $myExtraFieldEnd) {
				$form['end_at']->addError(new FormError("The end can not be the same as the start!"));
			}
			// Validate end is after start?
			if ($myExtraFieldStart > $myExtraFieldEnd) {
				$form['start_at']->addError(new FormError("The end can not be after the start!"));
			}
			// validate not to far in future
			$max = \TimeSource::getDateTime();
			$max->add(new \DateInterval(("P".$CONFIG->eventsCantBeMoreThanYearsInFuture."Y")));
			if ($myExtraFieldStart > $max) {
				$form['start_at']->addError(new FormError("The event can not be more than ".
						($CONFIG->eventsCantBeMoreThanYearsInFuture > 1 ? $CONFIG->eventsCantBeMoreThanYearsInFuture." years"  : "a year" ).
						" in the future."));
			}
			if ($myExtraFieldEnd > $max) {
				$form['end_at']->addError(new FormError("The event can not be more than ".
						($CONFIG->eventsCantBeMoreThanYearsInFuture > 1 ? $CONFIG->eventsCantBeMoreThanYearsInFuture." years"  : "a year" ).
						" in the future."));			}
			// validate not to far in past
			$min = \TimeSource::getDateTime();
			$min->sub(new \DateInterval(("P".$CONFIG->eventsCantBeMoreThanYearsInPast."Y")));
			if ($myExtraFieldStart < $min) {
				$form['start_at']->addError(new FormError("The event can not be more than ".
						($CONFIG->eventsCantBeMoreThanYearsInPast > 1 ? $CONFIG->eventsCantBeMoreThanYearsInPast." years"  : "a year" ).
						" in the past."));
			}
			if ($myExtraFieldEnd < $min) {
				$form['end_at']->addError(new FormError("The event can not be more than ".
						($CONFIG->eventsCantBeMoreThanYearsInPast > 1 ? $CONFIG->eventsCantBeMoreThanYearsInPast." years"  : "a year" ).
						" in the past."));
			}
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);	
	}
	
	public function getName() {
		return 'EventEditForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}


