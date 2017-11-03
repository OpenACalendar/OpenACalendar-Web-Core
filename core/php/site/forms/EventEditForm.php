<?php

namespace site\forms;

use ExtensionManager;
use repositories\SiteFeatureRepository;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class EventEditForm extends \BaseFormWithEditComment {

    /** @var Application */
    protected $app;

    protected $countries = array();
    protected $timezones = array();

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $this->app = $options['app'];
        $siteFeatureRepo = new SiteFeatureRepository($this->app);
        $siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['site'],'org.openacalendar','PhysicalEvents');
        $siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['site'],'org.openacalendar','VirtualEvents');

        $crb = new CountryRepositoryBuilder($this->app);
        $crb->setSiteIn($options['site']);
        foreach($crb->fetchAll() as $country) {
            $this->countries[$country->getTitle()] = $country->getId();
        }
        // TODO if current country not in list add it now

        // Must explicetly set name as key otherwise Symfony forms puts an ID in, and that's no good for processing outside form
        foreach($options['site']->getCachedTimezonesAsList() as $timezone) {
            $this->timezones[$timezone] = $timezone;
        }
        // TODO if current timezone not in list add it now




		$builder->add('summary', TextType::class, array(
			'label'=>'Summary',
			'required'=>true,
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED,
			'attr' => array('autofocus' => 'autofocus')
		));

		$builder->add('description', TextareaType::class, array(
			'label'=>'Description',
			'required'=>false
		));

		$builder->add('url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Information Web Page URL',
			'required'=>false
		));

		$builder->add('ticket_url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Tickets Web Page URL',
			'required'=>false
		));

		if (count($this->countries) != 1) {
			$builder->add('country_id', ChoiceType::class, array(
				'label'=>'Country',
				'choices' => $this->countries,
				'required' => true,
                'choices_as_values' => true,
			));
		} else {
            // Note we must have array_values here - if we don't we are changing a class variable and we re-use that class variable later!
			$countries = array_values($this->countries);
			$countryID = array_shift($countries);
			$builder->add('country_id', HiddenType::class, array(
				'data' => $countryID,
			));
		}


		if (count($this->timezones) != 1) {
			$builder->add('timezone', ChoiceType::class, array(
				'label'=>'Time Zone',
				'choices' => $this->timezones,
				'required' => true,
                'choices_as_values' => true,
			));
		} else {
            // Note we must have array_values here - if we don't we are changing a class variable and we re-use that class variable later!
			$timezones = array_values($this->timezones);
			$timezone = array_pop($timezones);
			$builder->add('timezone', HiddenType::class, array(
				'data' => $timezone,
			));
		}


		if ($siteFeatureVirtualEvents) {

			//  if both are an option, user must check which one.
			if ($siteFeaturePhysicalEvents) {

				$builder->add("is_virtual",
                    CheckboxType::class,
						array(
							'required'=>false,
							'label'=>'Is event accessible online?'
						)
					);
			}

		}


		if ($siteFeaturePhysicalEvents) {

			// if both are an option, user must check which one.
			if ($siteFeatureVirtualEvents) {

				$builder->add("is_physical",
                    CheckboxType::class,
						array(
							'required'=>false,
							'label'=>'Does the event happen at a place?'
						)
					);

			}

		}

		$years = array( date('Y'), date('Y')+1 );

		$startOptions = array(
			'label'=>'Start',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' => $options['timeZoneName'],
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true
		);
		if ($this->app['config']->formWidgetTimeMinutesMultiples > 1) {
			$startOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$application['config']->formWidgetTimeMinutesMultiples) {
				$startOptions['minutes'][] = $i;
			}
		}
		$builder->add('start_at', DateTimeType::class , $startOptions);

		$endOptions = array(
			'label'=>'End',
			'date_widget'=> 'single_text',
			'date_format'=>'d/M/y',
			'model_timezone' => 'UTC',
			'view_timezone' => $options['timeZoneName'],
			'years' => $years,
			'attr' => array('class' => 'dateInput'),
			'required'=>true
		);
		if ($this->app['config']->formWidgetTimeMinutesMultiples > 1) {
			$endOptions['minutes'] = array();
			for ($i = 0; $i <= 59; $i=$i+$application['config']->formWidgetTimeMinutesMultiples) {
				$endOptions['minutes'][] = $i;
			}
		}
		$builder->add('end_at', DateTimeType::class , $endOptions);
        
        foreach($options['customFields'] as $customFieldData) {
            $fieldOptions = $customFieldData['fieldType']->getSymfonyFormOptions($customFieldData['customField']);
            $fieldOptions['mapped'] = false;
            $fieldOptions['data'] = $builder->getData()->getCustomField($customFieldData['customField']);
            $builder->add('custom_' . $customFieldData['customField']->getKey(), $customFieldData['fieldType']->getSymfonyFormType($customFieldData['customField']), $fieldOptions);
        }

		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraFieldStart = $form->get('start_at')->getData();
			$myExtraFieldEnd = $form->get('end_at')->getData();
			// Validate end is not the same as start
			if ($myExtraFieldStart == $myExtraFieldEnd) {
				$form['end_at']->addError(new FormError("The end can not be the same as the start!"));
			}
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
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			if (strpos($form->get("url")->getData(), " ") !== false) {
				$form['url']->addError(new FormError("Please enter a URL"));
			}
			if (strpos($form->get("ticket_url")->getData(), " ") !== false) {
				$form['ticket_url']->addError(new FormError("Please enter a URL"));
			}
            // Country
            if (!in_array($form->get('country_id')->getData(), array_values($this->countries))) {
                $form['country_id']->addError(new FormError("Please select a country"));
            }
            // Timezone
            if (!in_array($form->get('timezone')->getData(), array_values($this->timezones))) {
                $form['timezone']->addError(new FormError("Please select a timezone"));
                // The user will see this error if they try to pass
                // 1) ''
                // 2) A timezone not enabled in this site
                // If they pass
                // 3) made up string that is not a valid timezone
                // the page will crash and they won't see this error. But never mind.
            }
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'EventEditForm';
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'app' => null,
            'site' => null,
            'timeZoneName' => null,
            'customFields' => null,
        ));
    }

	
}


