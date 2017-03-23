<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Form\Type\AbstractFilterType;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractFilterType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("type", ChoiceType::class,
            array (
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL),
                "required" => false));

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