<?php

namespace site\forms;

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


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventImportedEditForm extends AbstractType{


    protected $app;

	protected $timeZoneName;
	/** @var SiteModel **/
	protected $site;

    protected $siteFeaturePhysicalEvents = false;
    protected $siteFeatureVirtualEvents = false;

    function __construct(Application $application, SiteModel $site, $timeZoneName) {
        $this->app = $application;
		$this->site = $site;
		$this->timeZoneName = $timeZoneName;
        $siteFeatureRepo = new SiteFeatureRepository($application);
        $this->siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','PhysicalEvents');
        $this->siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','VirtualEvents');
	}


    protected $customFields;

    public function buildForm(FormBuilderInterface $builder, array $options) {

		$crb = new CountryRepositoryBuilder($this->app);
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
			
				
		if ($this->siteFeatureVirtualEvents) {
			
			// if both are an option, user must check which one.
			if ($this->siteFeaturePhysicalEvents) {
			
				$builder->add("is_virtual",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Is event accessible online?'
						)
					);
			}
			
		}

		
		if ($this->siteFeaturePhysicalEvents) {
			
			// if both are an option, user must check which one.
			if ($this->siteFeatureVirtualEvents) {
				
				$builder->add("is_physical",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Does the event happen at a place?'
						)
					);
				
			}


		}


        $this->customFields = array();
        foreach($this->site->getCachedEventCustomFieldDefinitionsAsModels() as $customField) {
            if ($customField->getIsActive()) {
                $extension = $this->app['extensions']->getExtensionById($customField->getExtensionId());
                if ($extension) {
                    $fieldType = $extension->getEventCustomFieldByType($customField->getType());
                    if ($fieldType) {
                        $this->customFields[] = $customField;
                        $options = $fieldType->getSymfonyFormOptions($customField);
                        $options['mapped'] = false;
                        $options['data'] = $builder->getData()->getCustomField($customField);
                        $builder->add('custom_' . $customField->getKey(), $fieldType->getSymfonyFormType($customField), $options);
                    }
                }
            }
        }
		
	}
	
	public function getName() {
		// This is called EventEditForm and not EventImportedEditForm carefully.
		// We want it to imitate the normal edit form so that all JS that relies on the ID continues to work.
		return 'EventEditForm';
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

}

