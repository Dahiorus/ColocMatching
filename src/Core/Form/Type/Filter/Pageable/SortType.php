<?php

namespace App\Core\Form\Type\Filter\Pageable;

use App\Core\Repository\Filter\Pageable\Order;
use App\Core\Repository\Filter\Pageable\Sort;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("property", TextType::class, array ("required" => true));
        $builder->add("direction", ChoiceType::class, array (
            "required" => false,
            "choices" => array (
                "asc" => Order::ASC,
                "desc" => Order::DESC
            ),
            "empty_data" => "asc"
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("data_class", Sort::class);
    }


    public function getBlockPrefix()
    {
        return "sort";
    }

}