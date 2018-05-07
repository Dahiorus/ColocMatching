<?php

namespace ColocMatching\CoreBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('_username', TextType::class, array ("required" => true, "constraints" => new NotBlank()));
        $builder->add('_password', PasswordType::class, array (
            "required" => true, "constraints" => new NotBlank(),
            "documentation" => array ("format" => "password")
        ));
    }


    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return "credentials";
    }

}
