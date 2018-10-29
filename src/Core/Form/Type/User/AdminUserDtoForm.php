<?php

namespace App\Core\Form\Type\User;

use App\Core\Entity\User\UserStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserDtoForm extends AbstractUserDtoForm
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("plainPassword", PasswordType::class, array ("required" => true));
        $builder->add("roles", CollectionType::class, array (
            "required" => false,
            "entry_type" => TextType::class,
            "allow_add" => true,
        ));
        $builder->add("status", ChoiceType::class, array (
            "required" => false,
            "choices" => array (
                "pending" => UserStatus::PENDING,
                "enabled" => UserStatus::ENABLED,
                "vacation" => UserStatus::VACATION,
                "banned" => UserStatus::BANNED
            ),
            "empty_data" => UserStatus::PENDING,
        ));
        $builder->remove("type");
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault("validation_groups", array ("Create", "Default"));
    }


    public function getBlockPrefix()
    {
        return "admin_user";
    }

}
