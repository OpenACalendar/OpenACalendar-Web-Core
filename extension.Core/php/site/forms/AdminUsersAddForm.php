<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminUsersAddForm extends AbstractType{

	protected $site;
	
	function __construct(SiteModel $site) {
		$this->site = $site;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('username', 'text', array('label'=>'Username','required'=>true));
		$choices = array('admin'=>'Administrator');
		if (!$this->site->getIsAllUsersEditors()) {
			$choices['edit'] = 'Editor';
		}
		$builder->add('role', 'choice', array('label'=>'Role','required'=>true,'choices'=>$choices));
		
	}
	
	public function getName() {
		return 'AdminAdministratorsAddForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}