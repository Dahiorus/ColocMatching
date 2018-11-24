<?php

namespace App\Core\Form\Type\Alert;

use App\Core\Repository\Filter\AnnouncementFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementAlertDtoForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("filter_class", AnnouncementFilter::class);
    }


    public function getParent()
    {
        return AlertDtoForm::class;
    }


    public function getBlockPrefix()
    {
        return "announcement_alert";
    }

}
