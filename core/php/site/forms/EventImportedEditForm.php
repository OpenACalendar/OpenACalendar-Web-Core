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
class EventImportedEditForm extends AbstractType{

	protected $timeZoneName;
	/** @var SiteModel **/
	protected $site;
	
	function __construct(SiteModel $site, $timeZoneName) {
		$this->site = $site;
		$this->timeZoneName = $timeZoneName;
	}

	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
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
			
			// if both are an option, user must check which one.
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
	
}