<?php

namespace App\Core\Form\Type\User;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEditUserDtoForm extends AdminUserDtoForm
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault("validation_groups", array ("Default"));
    }
}