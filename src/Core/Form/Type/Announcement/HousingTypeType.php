<?php

namespace App\Core\Form\Type\Announcement;

use App\Core\Entity\Announcement\HousingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HousingTypeType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("choices" => array (
            "apartment" => HousingType::APARTMENT,
            "house" => HousingType::HOUSE,
            "studio" => HousingType::STUDIO)
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "housing_type";
    }
}