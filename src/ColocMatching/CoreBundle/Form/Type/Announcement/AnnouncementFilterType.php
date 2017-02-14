<?php

namespace ColocMatching\CoreBundle\Form\Type\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Form\Type\AbstractFilterType;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementFilterType extends AbstractFilterType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("address", AddressType::class, array ("required" => false));

        $builder->add("minPriceStart", NumberType::class, array ("required" => false));

        $builder->add("minPriceEnd", NumberType::class, array ("required" => false));

        $builder->add("maxPriceStart", NumberType::class, array ("required" => false));

        $builder->add("maxPriceEnd", NumberType::class, array ("required" => false));

        $builder->add("types", ChoiceType::class,
            array ("required" => false,
                "choices" => array ("rent" => Announcement::TYPE_RENT, "sublease" => Announcement::TYPE_SUBLEASE,
                    "sharing" => Announcement::TYPE_SHARING), "multiple" => true));

        $builder->add("startDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => "dd/MM/yyyy"));

        $builder->add("startDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => "dd/MM/yyyy"));

        $builder->add("endDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => "dd/MM/yyyy"));

        $builder->add("endDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => "dd/MM/yyyy"));

        $builder->add("creatorType", ChoiceType::class,
            array ("required" => false,
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL)));

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