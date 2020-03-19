<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Entity\Group\Group;
use App\Core\Form\Type\BooleanType;
use App\Core\Repository\Filter\GroupFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupFilterForm extends AbstractType implements SearchableForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("withDescription", BooleanType::class, array ("required" => false));
        $builder->add("budgetMin", NumberType::class, array ("required" => false));
        $builder->add("budgetMax", NumberType::class, array ("required" => false));
        $builder->add("status", ChoiceType::class, array (
            "required" => false,
            "choices" => array (
                "opened" => Group::STATUS_OPENED,
                "closed" => Group::STATUS_CLOSED)
        ));
        $builder->add("countMembers", NumberType::class, array ("required" => false));
        $builder->add("withPicture", BooleanType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => GroupFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "group_filter";
    }

}
