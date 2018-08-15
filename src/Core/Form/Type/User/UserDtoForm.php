<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserConstants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDtoForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("email", EmailType::class, array ("required" => true));
        $builder->add("plainPassword", PasswordType::class, array (
            "required" => false,
            "documentation" => array ("format" => "password")
        ));
        $builder->add("firstName", TextType::class, array ("required" => true));
        $builder->add("lastName", TextType::class, array ("required" => true));
        $builder->add("type", ChoiceType::class,
            array (
                "choices" => array ("search" => UserConstants::TYPE_SEARCH, "proposal" => UserConstants::TYPE_PROPOSAL),
                "required" => false));
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => UserDto::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "user";
    }

}
