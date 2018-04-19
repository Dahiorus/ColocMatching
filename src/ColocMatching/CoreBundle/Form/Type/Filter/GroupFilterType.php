<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("withDescription", BooleanType::class, array ("required" => false));
        $builder->add("budgetMin", NumberType::class, array ("required" => false));
        $builder->add("budgetMax", NumberType::class, array ("required" => false));
        $builder->add("status", TextType::class, array ("required" => false));
        $builder->add("countMembers", NumberType::class, array ("required" => false));
        $builder->add("withPicture", BooleanType::class, array ("required" => false));

        parent::buildForm($builder, $options);
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
