<?php

namespace App\Core\Form\Type\Announcement;

use App\Core\Entity\Announcement\AnnouncementType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementTypeType extends AbstractType
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
            "rent" => AnnouncementType::RENT,
            "sublease" => AnnouncementType::SUBLEASE,
            "sharing" => AnnouncementType::SHARING)
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "announcement_type";
    }

}
