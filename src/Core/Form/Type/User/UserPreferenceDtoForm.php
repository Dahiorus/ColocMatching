<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\UserPreferenceDto;
use App\Core\Entity\User\UserGender;
use App\Core\Entity\User\UserType;
use App\Core\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferenceDtoForm extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        $builder->add("ageStart", NumberType::class, array ("required" => false));
        $builder->add("ageEnd", NumberType::class, array ("required" => false));
        $builder->add("withDescription", BooleanType::class, array ("required" => false));
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => UserPreferenceDto::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "user_preference";
    }

}