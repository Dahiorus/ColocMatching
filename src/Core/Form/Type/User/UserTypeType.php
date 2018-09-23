<?php

namespace App\Core\Form\Type\User;

use App\Core\Entity\User\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array (
            "choices" => array (
                "search" => UserType::SEARCH,
                "proposal" => UserType::PROPOSAL)
        ));
    }


    public function getBlockPrefix()
    {
        return "user_type";
    }


    public function getParent()
    {
        return ChoiceType::class;
    }

}
