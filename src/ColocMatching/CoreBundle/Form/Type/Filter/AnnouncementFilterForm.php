<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
        $builder->add("createdAtSince", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("status", TextType::class, array ("required" => false));
        $builder->add("housingFilter", HousingFilterForm::class, array ("required" => false));
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
