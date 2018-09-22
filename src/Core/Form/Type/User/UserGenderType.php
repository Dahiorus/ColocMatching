<?php

namespace App\Core\Form\Type\User;

use App\Core\Entity\User\UserGender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserGenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            "choices" => array (
                "male" => UserGender::MALE,
                "female" => UserGender::FEMALE),
        ));
    }


    public function getBlockPrefix()
    {
        return "user_gender";
    }


    public function getParent()
    {
        return ChoiceType::class;
    }

}
