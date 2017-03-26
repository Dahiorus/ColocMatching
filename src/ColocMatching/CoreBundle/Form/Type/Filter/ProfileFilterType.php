<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\User\ProfileConstants;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\ProfileFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFilterType extends AbstractType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("gender", TextType::class,
            array ("required" => false, "empty_data" => ProfileConstants::GENDER_UNKNOWN));

        $builder->add("ageStart", NumberType::class, array ("required" => false));

        $builder->add("ageEnd", NumberType::class, array ("required" => false));

        $builder->add("withDescription", BooleanType::class, array ("required" => false));

        $builder->add("smoker", BooleanType::class, array ("required" => false));

        $builder->add("hasJob", BooleanType::class, array ("required" => false));

        $builder->add("diet", TextType::class,
            array ("required" => false, "empty_data" => ProfileConstants::DIET_UNKNOWN));

        $builder->add("maritalStatus", TextType::class,
            array ("required" => false, "empty_data" => ProfileConstants::MARITAL_UNKNOWN));

        $builder->add("socialStatus", TextType::class,
            array ("required" => false, "empty_data" => ProfileConstants::SOCIAL_UNKNOWN));
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