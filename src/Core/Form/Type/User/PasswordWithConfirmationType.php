<?php

namespace App\Core\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordWithConfirmationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            "type" => PasswordType::class,
            "first_name" => "password",
            "second_name" => "confirmPassword",
            "invalid_message" => "The password fields must match",
            "documentation" => array ("format" => "password")));
    }


    public function getBlockPrefix()
    {
        return "password";
    }


    public function getParent()
    {
        return RepeatedType::class;
    }
}