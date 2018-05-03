<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter\Pageable;

use ColocMatching\CoreBundle\Repository\Filter\Pageable\Order;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("page", NumberType::class, array (
            "empty_data" => "1",
            "documentation" => array ("type" => "integer", "example" => "1")
        ));
        $builder->add("size", NumberType::class, array (
            "empty_data" => "20",
            "documentation" => array ("type" => "integer", "example" => "20")
        ));
        $builder->add("sorts", CollectionType::class, array (
            "entry_type" => SortType::class,
            "allow_add" => true,
            "error_bubbling" => false,
            "documentation" => array (
                "type" => "array",
                "items" => array (
                    "type" => "object",
                    "properties" => array (
                        "property" => array ("type" => "string", "example" => "createdAt"),
                        "direction" => array (
                            "type" => "string",
                            "enum" => array (Order::ASC, Order::DESC),
                            "example" => Order::ASC))
                ))
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("data_class", PageRequest::class);
    }


    public function getBlockPrefix()
    {
        return "page_request";
    }

}
