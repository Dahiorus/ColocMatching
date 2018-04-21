<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("createdAtSince", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => \IntlDateFormatter::SHORT));
        $builder->add("createdAtUntil", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => \IntlDateFormatter::SHORT));
        $builder->add("type", TextType::class, array ("required" => false));
        $builder->add("hasAnnouncement", BooleanType::class, array ("required" => false));
        $builder->add("hasGroup", BooleanType::class, array ("required" => false));
        $builder->add("status", ChoiceType::class, array (
            "choices" => array (
                "pending" => UserConstants::STATUS_PENDING,
                "enabled" => UserConstants::STATUS_ENABLED,
                "vacation" => UserConstants::STATUS_VACATION,
                "banned" => UserConstants::STATUS_BANNED),
            "required" => false,
            "multiple" => true
        ));
        $builder->add("profileFilter", ProfileFilterType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => UserFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "user_filter";
    }

}
