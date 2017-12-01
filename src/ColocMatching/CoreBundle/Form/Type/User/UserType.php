<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("email", EmailType::class, array ("required" => true));
        $builder->add("firstname", TextType::class, array ("required" => true));
        $builder->add("lastname", TextType::class, array ("required" => true));
        $builder->add("type", ChoiceType::class,
            array (
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL),
                "required" => false));
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => User::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix() {
        return "user";
    }

}
