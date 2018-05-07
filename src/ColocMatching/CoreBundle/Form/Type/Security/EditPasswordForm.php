<?php

namespace ColocMatching\CoreBundle\Form\Type\Security;

use ColocMatching\CoreBundle\Security\User\EditPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("oldPassword", PasswordType::class, array ("required" => true));
        $builder->add("newPassword", PasswordType::class, array ("required" => true));
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