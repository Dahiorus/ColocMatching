<?php

namespace App\Core\Form\Type\Alert;

use App\Core\Repository\Filter\GroupFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupAlertDtoForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("filter_class", GroupFilter::class);
    }


    public function getParent()
    {
        return AlertDtoForm::class;
    }


    public function getBlockPrefix()
    {
        return "group_alert";
    }

}
