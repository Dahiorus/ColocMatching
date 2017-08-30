<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementFilterType extends AbstractAnnouncementFilterType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("withDescription", BooleanType::class, array ("required" => false));

        $builder->add("withPictures", BooleanType::class, array ("required" => false));

        $builder->add("createdAtSince", DateType::class,
            array ("required" => false, "widget" => "single_text", "format" => \IntlDateFormatter::SHORT));

        $builder->add("status", ChoiceType::class, array ("choices" =>
            array (
                "enabled" => Announcement::STATUS_ENABLED,
                "filled" => Announcement::STATUS_FILLED,
                "disabled" => Announcement::STATUS_DISABLED),
            "required" => false,
            "multiple" => true));

        $builder->add("housingFilter", HousingFilterType::class, array ("required" => false));

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
