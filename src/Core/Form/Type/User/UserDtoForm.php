<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserGender;
use App\Core\Entity\User\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDtoForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("email", EmailType::class, array ("required" => true));
        $builder->add("plainPassword", PasswordType::class, array (
            "required" => false,
            "documentation" => array ("format" => "password")
        ));
        $builder->add("firstName", TextType::class, array ("required" => true));
        $builder->add("lastName", TextType::class, array ("required" => true));
        $builder->add("type", ChoiceType::class,
            array (
                "choices" => array ("search" => UserType::SEARCH, "proposal" => UserType::PROPOSAL),
                "required" => false));
        $builder->add("gender", ChoiceType::class,
            array (
                "choices" => array (
                    "male" => UserGender::MALE,
                    "female" => UserGender::FEMALE),
                "required" => false));
        $builder->add("phoneNumber", TextType::class, array ("required" => false));
        $builder->add("birthDate", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("description", TextareaType::class,
            array ("required" => false));
        $builder->add("tags", CollectionType::class,
            array (
                "required" => false,
                "entry_type" => TextType::class,
                "allow_add" => true,
                "allow_delete" => true));
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => UserDto::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "user";
    }

}
