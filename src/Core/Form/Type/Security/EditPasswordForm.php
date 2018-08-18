<?php

namespace App\Core\Form\Type\Security;

use App\Core\Security\User\EditPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("oldPassword", PasswordType::class, array (
            "required" => true,
            "documentation" => array ("format" => "password")
        ));
        $builder->add("newPassword", PasswordType::class, array (
            "required" => true,
            "documentation" => array ("format" => "password")
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => EditPassword::class));
    }


    public function getBlockPrefix()
    {
        return "edit_password";
    }
}