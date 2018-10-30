<?php

namespace App\Core\Form\Type\User;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDtoForm extends AbstractUserDtoForm
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("plainPassword", PasswordWithConfirmationType::class, array (
            "required" => false
        ));
        $builder->add("gender", UserGenderType::class, array ("required" => false));
        $builder->add("phoneNumber", TextType::class, array ("required" => false));
        $builder->add("birthDate", DateType::class, array (
            "required" => false,
            "widget" => "single_text"
        ));
        $builder->add("description", TextareaType::class, array ("required" => false));
        $builder->add("tags", CollectionType::class,
            array (
                "required" => false,
                "entry_type" => TextType::class,
                "allow_add" => true,
                "allow_delete" => true));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault("validation_groups", array ("Default", "Self"));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "user";
    }

}
