<?php

namespace ColocMatching\CoreBundle\Form\Type;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterType extends AbstractType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("page", NumberType::class,
            array ("description" => "Page (start from 1)", "required" => false,
                "empty_data" => strval(RequestConstants::DEFAULT_PAGE)));
        
        $builder->add("size", NumberType::class,
            array ("description" => "Maximum results to return", "required" => false,
                "empty_data" => strval(RequestConstants::DEFAULT_LIMIT)));
        
        $builder->add("order", ChoiceType::class,
            array ("description" => "Ordering direction", "required" => false,
                "choices" => array ("asc" => AbstractFilter::ORDER_ASC, "desc" => AbstractFilter::ORDER_DESC),
                "empty_data" => RequestConstants::DEFAULT_ORDER));
        
        $builder->add("sort", TextType::class,
            array ("description" => "Attribute name to sort by", "required" => false,
                "empty_data" => RequestConstants::DEFAULT_SORT));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => AbstractFilter::class));
    }

}