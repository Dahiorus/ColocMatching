<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Entity\User\UserStatus;
use App\Core\Form\Type\BooleanType;
use App\Core\Form\Type\User\UserGenderType;
use App\Core\Form\Type\User\UserTypeType;
use App\Core\Repository\Filter\UserFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterForm extends AbstractPageableFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("createdAtSince", DateType::class, array (
            "required" => false,
            "widget" => "single_text",
            "documentation" => array ("format" => "date")
        ));
        $builder->add("createdAtUntil", DateType::class, array (
            "required" => false,
            "widget" => "single_text",
            "documentation" => array ("format" => "date")
        ));
        $builder->add("type", UserTypeType::class, array ("required" => false));
        $builder->add("hasAnnouncement", BooleanType::class, array ("required" => false));
        $builder->add("hasGroup", BooleanType::class, array ("required" => false));
        $builder->add("status", ChoiceType::class, array (
            "choices" => array (
                "pending" => UserStatus::PENDING,
                "enabled" => UserStatus::ENABLED,
                "vacation" => UserStatus::VACATION,
                "banned" => UserStatus::BANNED),
            "required" => false,
            "multiple" => true
        ));
        $builder->add("gender", UserGenderType::class, array ("required" => false));
        $builder->add("ageStart", NumberType::class, array ("required" => false));
        $builder->add("ageEnd", NumberType::class, array ("required" => false));
        $builder->add("withDescription", BooleanType::class, array ("required" => false));
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
