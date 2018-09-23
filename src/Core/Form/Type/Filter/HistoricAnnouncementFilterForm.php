<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoricAnnouncementFilterForm extends AbstractAnnouncementFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("creatorId", NumberType::class, array ("required" => false));
        $builder->add("createdAtSince", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => HistoricAnnouncementFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "historic_announcement_filter";
    }

}
