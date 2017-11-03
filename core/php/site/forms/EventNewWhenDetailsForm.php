<?php

namespace site\forms;

use models\CountryModel;
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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewWhenDetailsForm extends AbstractType {

    /** @var Application */
    protected $app;


	public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->app = $options['app'];

        $crb = new CountryRepositoryBuilder($options['app']);
		$crb->setSiteIn($options['app']['currentSite']);
		$countries = $crb->fetchAll();
		if (count($countries) > 1) {
			$countriesForSelect = array();
			foreach($countries as $country) {
				$countriesForSelect[$country->getTitle()] = $country->getId();
			}
			$builder->add('country_id', ChoiceType::class, array(
				'label'=>'Country',
				'choices' => $countriesForSelect,
				'required' => true,
				'data' => $options['countryModel']->getId(),
                'choices_as_values' => true,
			));
		} else if (count($countries) == 1) {
			$this->defaultCountry = $countries[0];
			$builder->add('country_id', HiddenType::class, array(
				'data' => $options['countryModel']->getId(),
			));
		}


		$timezones = array();
		// Must explicetly set name as key otherwise Symfony forms puts an ID in, and that's no good for processing outside form
		foreach($options['app']['currentSite']->getCachedTimezonesAsList() as $timezone) {
			$timezones[$timezone] = $timezone;
		}
		if (count($timezones) != 1) {
			$builder->add('timezone', ChoiceType::class, array(
				'label'=>'Time Zone',
				'choices' => $timezones,
				'required' => true,
                'choices_as_values' => true,
			));
		} else {
			$timezone = array_pop($timezones);
			$builder->add('timezone', HiddenType::class, array(
				'data' => $timezone,
			));
		}

		$years = array( date('Y'), date('Y')+1 );

		$data = null;
		if ($options['newEventDraftModel']->hasDetailsValue('event.start_at')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('event.start_at');
		} else if ($options['newEventDraftModel']->hasDetailsValue('event.start_end_freetext.start')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('event.start_end_freetext.start');
		} else if ($options['newEventDraftModel']->hasDetailsValue('incoming.event.start_at')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('incoming.event.start_at');
		}

		$startOptions = array(
			'label'=>'Start',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' => $options['timeZoneName'],
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true,
			'data' => $data,
		);
		if ($options['app']['config']->formWidgetTimeMinutesMultiples > 1) {
			$startOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$options['app']['config']->formWidgetTimeMinutesMultiples) {
				$startOptions['minutes'][] = $i;
			}
		}
		$builder->add('start_at', DateTimeType::class ,$startOptions);

		$data = null;
		if ($options['newEventDraftModel']->hasDetailsValue('event.end_at')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('event.end_at');
		} else if ($options['newEventDraftModel']->hasDetailsValue('event.start_end_freetext.end')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('event.start_end_freetext.end');
		} else if ($options['newEventDraftModel']->hasDetailsValue('incoming.event.end_at')) {
			$data = $options['newEventDraftModel']->getDetailsValueAsDateTime('incoming.event.end_at');
		}

		$endOptions = array(
			'label'=>'End',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' =>  $options['timeZoneName'],
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true,
			'data' => $data,
		);
		if ($options['app']['config']->formWidgetTimeMinutesMultiples > 1) {
			$endOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$options['app']['config']->formWidgetTimeMinutesMultiples) {
				$endOptions['minutes'][] = $i;
			}
		}
		$builder->add('end_at', DateTimeType::class ,$endOptions);



		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraFieldStart = $form->get('start_at')->getData();
			$myExtraFieldEnd = $form->get('end_at')->getData();
			// Validate end is after start?
			if ($myExtraFieldStart > $myExtraFieldEnd) {
				$form['start_at']->addError(new FormError("The start can not be after the end!"));
			}
			// validate not to far in future
			$max = \TimeSource::getDateTime();
			$max->add(new \DateInterval(("P".$this->app['config']->eventsCantBeMoreThanYearsInFuture."Y")));
			if ($myExtraFieldStart > $max) {
				$form['start_at']->addError(new FormError("The event can not be more than ".
					($this->app['config']->eventsCantBeMoreThanYearsInFuture > 1 ? $this->app['config']->eventsCantBeMoreThanYearsInFuture." years"  : "a year" ).
					" in the future."));
			}
			if ($myExtraFieldEnd > $max) {
				$form['end_at']->addError(new FormError("The event can not be more than ".
					($this->app['config']->eventsCantBeMoreThanYearsInFuture > 1 ? $this->app['config']->eventsCantBeMoreThanYearsInFuture." years"  : "a year" ).
					" in the future."));			}
			// validate not to far in past
			$min = \TimeSource::getDateTime();
			$min->sub(new \DateInterval(("P".$this->app['config']->eventsCantBeMoreThanYearsInPast."Y")));
			if ($myExtraFieldStart < $min) {
				$form['start_at']->addError(new FormError("The event can not be more than ".
					($this->app['config']->eventsCantBeMoreThanYearsInPast > 1 ? $this->app['config']->eventsCantBeMoreThanYearsInPast." years"  : "a year" ).
					" in the past."));
			}
			if ($myExtraFieldEnd < $min) {
				$form['end_at']->addError(new FormError("The event can not be more than ".
					($this->app['config']->eventsCantBeMoreThanYearsInPast > 1 ? $this->app['config']->eventsCantBeMoreThanYearsInPast." years"  : "a year" ).
					" in the past."));
			}
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);
	}

	public function getName() {
		return 'EventNewWhenDetailsForm';
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'app' => null,
            'newEventDraftModel' => null,
            'timeZoneName' => null,
            'countryModel' => null,
        ));
    }

}


