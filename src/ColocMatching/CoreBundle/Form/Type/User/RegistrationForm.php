<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type used to register a user
 *
 * @author Dahiorus
 */
class RegistrationForm extends UserDtoForm
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("plainPassword", PasswordType::class, array ("required" => true));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "registration";
    }
}