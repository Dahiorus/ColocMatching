<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Form\Type\AddressType;
use App\Core\Form\Type\Announcement\AnnouncementTypeType;
use App\Core\Repository\Filter\AbstractAnnouncementFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAnnouncementFilterForm extends AbstractType implements SearchableForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("address", AddressType::class, array ("required" => false));
        $builder->add("rentPriceStart", NumberType::class, array ("required" => false));
        $builder->add("rentPriceEnd", NumberType::class, array ("required" => false));
        $builder->add("types", AnnouncementTypeType::class, array (
            "required" => false,
            "multiple" => true
        ));
        $builder->add("startDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("startDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("endDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("endDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text"));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => AbstractAnnouncementFilter::class));
    }

}
