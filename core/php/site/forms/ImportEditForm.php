<?php

namespace site\forms;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use repositories\builders\CountryRepositoryBuilder;
use models\SiteModel;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportEditForm extends AbstractType{

    protected $app;

    /** @var SiteModel **/
	protected $site;
	
	function __construct(Application $application, SiteModel $site) {
        $this->app = $application;
		$this->site = $site;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('title', TextType::class, array(
			'label'=>'Title',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));

		/**
		$builder->add("is_manual_events_creation",
            CheckboxType::class,
			array(
				'required'=>false,
				'label'=>'Do you want to create events manually from this import?'
			)
		);
		 * **/

		$crb = new CountryRepositoryBuilder($this->app);
		$crb->setSiteIn($this->site);
		$countries = array();
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getTitle()] = $country->getId();
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', ChoiceType::class, array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
            'choices_as_values' => true,
		));
		
	}
	
	public function getName() {
		return 'ImportEditForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}



