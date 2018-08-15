<?php

namespace App\Core\Form\Type\Filter;

use App\Core\Form\Type\Filter\Pageable\PageRequestType;
use App\Core\Repository\Filter\AbstractPageableFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPageableFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("pageable", PageRequestType::class, array ("required" => false));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("data_class", AbstractPageableFilter::class);
    }

}
