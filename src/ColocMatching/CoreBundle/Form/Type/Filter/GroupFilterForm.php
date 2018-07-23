<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupFilterForm extends AbstractPageableFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
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
