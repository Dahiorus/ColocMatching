<?php

namespace App\Core\Form\Type\User;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type used to register a user
 *
 * @author Dahiorus
 */
class RegistrationForm extends AbstractUserDtoForm
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("plainPassword", PasswordWithConfirmationType::class, array (
            "required" => true
        ));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "registration";
    }

}
