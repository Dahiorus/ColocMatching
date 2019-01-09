<?php

namespace App\Core\Form\Type\Invitation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class UserInvitationDtoForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("invitableId", NumberType::class, array (
            "required" => true,
            "documentation" => array (
                "example" => "1"
            )
        ));
    }


    public function getParent()
    {
        return InvitationDtoForm::class;
    }
}
