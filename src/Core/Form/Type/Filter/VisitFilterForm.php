<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Repository\Filter\VisitFilter;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisitFilterForm extends AbstractPageableFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("visitorId", NumberType::class, array ("required" => false));
        $builder->add("visitedClass", TextType::class, array ("required" => false));
        $builder->add("visitedId", NumberType::class, array ("required" => false));
        $builder->add("visitedAtSince", DateTimeType::class, array ("required" => false));
        $builder->add("visitedAtUntil", DateTimeType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => VisitFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "visit_filter";
    }

}
