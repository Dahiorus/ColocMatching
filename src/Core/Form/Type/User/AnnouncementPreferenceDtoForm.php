<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\AnnouncementPreferenceDto;
use App\Core\Form\Type\Announcement\AnnouncementTypeType;
use App\Core\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementPreferenceDtoForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("address", TextType::class, array ("required" => false));

        $builder->add("rentPriceStart", NumberType::class, array ("required" => false));

        $builder->add("rentPriceEnd", NumberType::class, array ("required" => false));

        $builder->add("types", AnnouncementTypeType::class, array (
            "required" => false,
            "multiple" => true));

        $builder->add("startDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text"));

        $builder->add("startDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text"));

        $builder->add("endDateAfter", DateType::class,
            array ("required" => false, "widget" => "single_text"));

        $builder->add("endDateBefore", DateType::class,
            array ("required" => false, "widget" => "single_text"));

        $builder->add("withPictures", BooleanType::class, array ("required" => false));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => AnnouncementPreferenceDto::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "announcement_preference";
    }

}