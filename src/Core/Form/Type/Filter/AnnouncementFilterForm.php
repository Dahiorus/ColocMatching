<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Form\Type\Announcement\HousingTypeType;
use App\Core\Form\Type\BooleanType;
use App\Core\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementFilterForm extends AbstractAnnouncementFilterForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add("withDescription", BooleanType::class, array ("required" => false));
        $builder->add("withPictures", BooleanType::class, array ("required" => false));
        $builder->add("createdAtSince", DateType::class, array (
            "required" => false,
            "widget" => "single_text"
        ));
        $builder->add("status", TextType::class, array ("required" => false));
        $builder->add("housingTypes", HousingTypeType::class, array (
            "required" => false,
            "multiple" => true
        ));
        $builder->add("roomCount", NumberType::class, array ("required" => false));
        $builder->add("bedroomCount", NumberType::class, array ("required" => false));
        $builder->add("bathroomCount", NumberType::class, array ("required" => false));
        $builder->add("surfaceAreaMin", NumberType::class, array ("required" => false));
        $builder->add("surfaceAreaMax", NumberType::class, array ("required" => false));
        $builder->add("roomMateCount", NumberType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => AnnouncementFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'announcement_filter';
    }

}
