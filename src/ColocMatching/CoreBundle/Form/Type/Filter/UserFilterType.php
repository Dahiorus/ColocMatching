<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ColocMatching\CoreBundle\Form\Type\BooleanType;

class UserFilterType extends AbstractFilterType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("type", TextType::class, array ("required" => false));
        $builder->add("enabled", BooleanType::class, array ("required" => false));
        $builder->add("profileFilter", ProfileFilterType::class, array ("required" => false));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => UserFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "user_filter";
    }

}