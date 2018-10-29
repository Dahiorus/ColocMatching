<?php

namespace App\Core\Form\Type\User;

use App\Core\DTO\User\UserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractUserDtoForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("email", EmailType::class, array ("required" => true));
        $builder->add("firstName", TextType::class, array ("required" => true));
        $builder->add("lastName", TextType::class, array ("required" => true));
        $builder->add("type", UserTypeType::class, array ("required" => true));
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => UserDto::class));
    }
}