<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Form\Type\AddressType;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementFilterType extends AbstractFilterType {

    const DATE_FORMAT = "dd/MM/yyyy";


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("address", AddressType::class, array ("required" => false));

        $builder->add("rentPriceStart", NumberType::class, array ("required" => false));

        $builder->add("rentPriceEnd", NumberType::class, array ("required" => false));

        $builder->add("types", TextType::class, array ("required" => false));

        $builder->add("startDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("startDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("endDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("endDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("withPictures", BooleanType::class, array ("required" => false));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => AnnouncementFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'announcement_filter';
    }

}