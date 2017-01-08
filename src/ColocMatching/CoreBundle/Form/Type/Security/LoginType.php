<?php

namespace ColocMatching\CoreBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class LoginType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('_username', TextType::class, array (
            	'description' => 'Username',
            	'required' => true
            ))
			->add('_password', PasswordType::class, array (
            	'description' => 'Password',
            	'required' => true
            ));
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'credentials';
	}
}