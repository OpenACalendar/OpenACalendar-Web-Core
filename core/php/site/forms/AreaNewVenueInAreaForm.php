<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaNewVenueInAreaForm extends AbstractType{

	/** @var SiteModel **/
	protected $site;
	
	function __construct(SiteModel $site) {
		$this->site = $site;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('title', 'text', array(
			'label'=>'Title',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus')
		));
		
		$builder->add('description', 'textarea', array(
			'label'=>'Description',
			'required'=>false
		));
		
		$builder->add('lat', 'hidden', array());
		$builder->add('lng', 'hidden', array());

	}
	
	public function getName() {
		return 'AreaNewVenueInAreaForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}