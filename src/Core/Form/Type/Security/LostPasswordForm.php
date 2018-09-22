<?php

namespace App\Core\Form\Type\Security;

use App\Core\Form\Type\User\PasswordWithConfirmationType;
use App\Core\Security\User\LostPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LostPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("token", TextType::class, array ("required" => true));
        $builder->add("newPassword", PasswordWithConfirmationType::class, array (
            "required" => true,
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("data_class", LostPassword::class);
    }

}
