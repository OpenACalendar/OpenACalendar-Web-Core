<?php

namespace site\forms;

use models\NewEventDraftModel;
use Silex\Application;
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
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewWhenDetailsForm extends AbstractType {

	protected $timeZoneName;

	/** @var SiteModel **/
	protected $site;

	protected $formWidgetTimeMinutesMultiples;

	/** @var  ExtensionManager */
	protected $extensionManager;


	/** @var  NewEventDraftModel */
	protected $eventDraft;

	protected $defaultCountry;

	function __construct($timeZoneName, Application $application, NewEventDraftModel $newEventDraftModel ) {
		$this->site = $application['currentSite'];
		$this->formWidgetTimeMinutesMultiples = $application['config']->formWidgetTimeMinutesMultiples;
		$this->timeZoneName = $timeZoneName;
		$this->extensionManager = $application['extensions'];
		$this->eventDraft = $newEventDraftModel;
	}

	protected $customFields;

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$crb = new CountryRepositoryBuilder();
		$crb->setSiteIn($this->site);
		$this->defaultCountry = null;
		$defaultCountryID = null;
		$countries = $crb->fetchAll();
		if (count($countries) > 1) {
			$countriesForSelect = array();
			foreach($countries as $country) {
				$countriesForSelect[$country->getId()] = $country->getTitle();
				if ($this->defaultCountry == null && in_array($this->timeZoneName, $country->getTimezonesAsList())) {
					$this->defaultCountry = $country;
					$defaultCountryID = $country->getId();
				}
			}
			$builder->add('country_id', 'choice', array(
				'label'=>'Country',
				'choices' => $countriesForSelect,
				'required' => true,
				'data' => $defaultCountryID,
			));
		} else if (count($countries) == 1) {
			$this->defaultCountry = $countries[0];
			$builder->add('country_id', 'hidden', array(
				'data' => $this->defaultCountry->getId(),
			));
		}


		$timezones = array();
		// Must explicetly set name as key otherwise Symfony forms puts an ID in, and that's no good for processing outside form
		foreach($this->site->getCachedTimezonesAsList() as $timezone) {
			$timezones[$timezone] = $timezone;
		}
		if (count($timezones) != 1) {
			$builder->add('timezone', 'choice', array(
				'label'=>'Time Zone',
				'choices' => $timezones,
				'required' => true,
			));
		} else {
			$timezone = array_pop($timezones);
			$builder->add('timezone', 'hidden', array(
				'data' => $timezone,
			));
		}

		$years = array( date('Y'), date('Y')+1 );

		$data = null;
		if ($this->eventDraft->hasDetailsValue('event.start_at')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('event.start_at');
		} else if ($this->eventDraft->hasDetailsValue('event.start_end_freetext.start')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('event.start_end_freetext.start');
		} else if ($this->eventDraft->hasDetailsValue('incoming.event.start_at')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('incoming.event.start_at');
		}

		$startOptions = array(
			'label'=>'Start',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' => $this->timeZoneName,
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true,
			'data' => $data,
		);
		if ($this->formWidgetTimeMinutesMultiples > 1) {
			$startOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$this->formWidgetTimeMinutesMultiples) {
				$startOptions['minutes'][] = $i;
			}
		}
		$builder->add('start_at', 'datetime' ,$startOptions);

		$data = null;
		if ($this->eventDraft->hasDetailsValue('event.end_at')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('event.end_at');
		} else if ($this->eventDraft->hasDetailsValue('event.start_end_freetext.end')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('event.start_end_freetext.end');
		} else if ($this->eventDraft->hasDetailsValue('incoming.event.end_at')) {
			$data = $this->eventDraft->getDetailsValueAsDateTime('incoming.event.end_at');
		}

		$endOptions = array(
			'label'=>'End',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' =>  $this->timeZoneName,
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true,
			'data' => $data,
		);
		if ($this->formWidgetTimeMinutesMultiples > 1) {
			$endOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$this->formWidgetTimeMinutesMultiples) {
				$endOptions['minutes'][] = $i;
			}
		}
		$builder->add('end_at', 'datetime' ,$endOptions);



		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			global $CONFIG;
			$form = $event->getForm();
			$myExtraFieldStart = $form->get('start_at')->getData();
			$myExtraFieldEnd = $form->get('end_at')->getData();
			// Validate end is after start?
			if ($myExtraFieldStart > $myExtraFieldEnd) {
				$form['start_at']->addError(new FormError("The start can not be after the end!"));
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
		return 'EventNewWhenDetailsForm';
	}

	public function getDefaultOptions(array $options) {
		return array(
		);
	}

	/**
	 * @return mixed
	 */
	public function getCustomFields()
	{
		return $this->customFields;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultCountry()
	{
		return $this->defaultCountry;
	}



}


