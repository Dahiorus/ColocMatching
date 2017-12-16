<?php

namespace ColocMatching\CoreBundle\Form\Type\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("title", TextType::class, array ("required" => true));

        $builder->add("type", ChoiceType::class,
            array (
                "required" => true,
                "choices" => array (
                    "rent" => Announcement::TYPE_RENT,
                    "sublease" => Announcement::TYPE_SUBLEASE,
                    "sharing" => Announcement::TYPE_SHARING)));

        $builder->add("description", TextareaType::class, array ("required" => false));

        $builder->add("location", AddressType::class,
            array ("required" => true, "entity" => $options["location_data"]));

        $builder->add("rentPrice", NumberType::class, array ("required" => true));

        $builder->add("startDate", DateType::class,
            array ("required" => true, "widget" => "single_text", "format" => "Y-m-d"));

        $builder->add("endDate", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => "Y-m-d"));

        $builder->add("status", ChoiceType::class, array ("choices" =>
            array (
                "enabled" => Announcement::STATUS_ENABLED,
                "filled" => Announcement::STATUS_FILLED,
                "disabled" => Announcement::STATUS_DISABLED),
            "required" => false,
            "empty_data" => Announcement::STATUS_ENABLED));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => Announcement::class, "location_data" => null));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "announcement";
    }

}