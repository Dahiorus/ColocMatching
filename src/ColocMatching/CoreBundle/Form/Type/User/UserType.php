<?php

namespace ColocMatching\CoreBundle\Form\Type\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("email", EmailType::class, array ("description" => "User email", "required" => true))->add(
            "plainPassword", PasswordType::class, array ("description" => "User password", "required" => true))->add(
            "gender", ChoiceType::class,
            array (
                "choices" => array ("male" => UserConstants::GENDER_MALE, "female" => UserConstants::GENDER_FEMALE,
                    "unknown" => UserConstants::GENDER_UNKNOWN), "description" => "User gender", "required" => false,
                "empty_data" => UserConstants::GENDER_UNKNOWN))->add("phoneNumber", TextType::class,
            array ("description" => "User phone number", "required" => false))->add("firstname", TextType::class,
            array ("description" => "User firstname", "required" => true))->add("lastname", TextType::class,
            array ("description" => "User lastname", "required" => true))->add("type", ChoiceType::class,
            array (
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL),
                "description" => "User type", "required" => false, "empty_data" => UserConstants::TYPE_SEARCH))->add(
            "enabled", BooleanType::class, array ("description" => "Enable the user", "required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => User::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "user";
    }

}
