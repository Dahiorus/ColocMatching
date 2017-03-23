<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\ProfileFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFilterType extends AbstractType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("gender", ChoiceType::class,
            array (
                "choices" => array (
                    "male" => ProfileConstants::GENDER_MALE,
                    "female" => ProfileConstants::GENDER_FEMALE,
                    "unknown" => ProfileConstants::GENDER_UNKNOWN),
                "required" => false,
                "empty_data" => ProfileConstants::GENDER_UNKNOWN));

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
                    "vegan" => ProfileConstants::DIET_VEGAN,
                    "unknown" => ProfileConstants::DIET_UNKNOWN),
                "required" => false,
                "empty_data" => ProfileConstants::DIET_UNKNOWN));

        $builder->add("maritalStatus", ChoiceType::class,
            array (
                "choices" => array (
                    "single" => ProfileConstants::MARITAL_SINGLE,
                    "couple" => ProfileConstants::MARITAL_COUPLE,
                    "unknown" => ProfileConstants::MARITAL_UNKNOWN),
                "required" => false,
                "empty_data" => ProfileConstants::MARITAL_UNKNOWN));

        $builder->add("socialStatus", ChoiceType::class,
            array (
                "choices" => array (
                    "student" => ProfileConstants::SOCIAL_STUDENT,
                    "worker" => ProfileConstants::SOCIAL_WORKER,
                    "unknown" => ProfileConstants::SOCIAL_UNKNOWN),
                "required" => false,
                "empty_data" => ProfileConstants::SOCIAL_UNKNOWN));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => ProfileFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "profile_filter";
    }

}