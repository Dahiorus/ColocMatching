<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\AnnouncementPreference;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AnnouncementPreferenceType extends AbstractType {

    const DATE_FORMAT = "dd/MM/yyyy";


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("address", AddressType::class, array ("required" => false));

        $builder->add("rentPriceStart", NumberType::class, array ("required" => false));

        $builder->add("rentPriceEnd", NumberType::class, array ("required" => false));

        $builder->add("types", ChoiceType::class,
            array (
                "required" => false,
                "choices" => array (
                    "rent" => Announcement::TYPE_RENT,
                    "sublease" => Announcement::TYPE_SUBLEASE,
                    "sharing" => Announcement::TYPE_SHARING),
                "multiple" => true));

        $builder->add("startDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("startDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("endDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("endDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));

        $builder->add("withPictures", BooleanType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => AnnouncementPreference::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "announcement_preference";
    }

}