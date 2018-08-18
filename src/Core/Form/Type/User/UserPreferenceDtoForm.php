<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\UserPreferenceDto;
use App\Core\Entity\User\ProfileConstants;
use App\Core\Entity\User\UserConstants;
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
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL),
                "required" => false));

        $builder->add("gender", ChoiceType::class,
            array (
                "choices" => array (
                    "male" => ProfileConstants::GENDER_MALE,
                    "female" => ProfileConstants::GENDER_FEMALE),
                "required" => false));

        $builder->add("ageStart", NumberType::class, array ("required" => false));

        $builder->add("ageEnd", NumberType::class, array ("required" => false));

        $builder->add("withDescription", BooleanType::class, array ("required" => false));

        $builder->add("smoker", BooleanType::class, array ("required" => false));

        $builder->add("hasJob", BooleanType::class, array ("required" => false));

        $builder->add("diet", ChoiceType::class,
            array (
                "choices" => array (
                    "meat_eater" => ProfileConstants::DIET_MEAT_EATER,
                    "vegetarian" => ProfileConstants::DIET_VEGETARIAN,
                    "vegan" => ProfileConstants::DIET_VEGAN),
                "required" => false));

        $builder->add("maritalStatus", ChoiceType::class,
            array (
                "choices" => array (
                    "single" => ProfileConstants::MARITAL_SINGLE,
                    "couple" => ProfileConstants::MARITAL_COUPLE),
                "required" => false));

        $builder->add("socialStatus", ChoiceType::class,
            array (
                "choices" => array (
                    "student" => ProfileConstants::SOCIAL_STUDENT,
                    "worker" => ProfileConstants::SOCIAL_WORKER),
                "required" => false));
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