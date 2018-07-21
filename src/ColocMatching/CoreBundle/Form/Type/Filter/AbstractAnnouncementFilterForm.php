<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use ColocMatching\CoreBundle\Repository\Filter\AbstractAnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAnnouncementFilterForm extends AbstractPageableFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("address", AddressType::class, array ("required" => false));
        $builder->add("rentPriceStart", NumberType::class, array ("required" => false));
        $builder->add("rentPriceEnd", NumberType::class, array ("required" => false));
        $builder->add("types", ChoiceType::class, array ("required" => false,
            "choices" => array (
                "rent" => Announcement::TYPE_RENT,
                "sharing" => Announcement::TYPE_SHARING,
                "sublease" => Announcement::TYPE_SUBLEASE),
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
