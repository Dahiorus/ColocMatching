<?php

namespace ColocMatching\CoreBundle\Form\Type\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HousingType extends AbstractType {


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("type", ChoiceType::class,
            array (
                "required" => false,
                "choices" => array (
                    "apartment" => Housing::TYPE_APARTMENT,
                    "house" => Housing::TYPE_HOUSE,
                    "studio" => Housing::TYPE_STUDIO)));

        $builder->add("roomCount", NumberType::class, array ("required" => false));

        $builder->add("bedroomCount", NumberType::class, array ("required" => false));

        $builder->add("bathroomCount", NumberType::class, array ("required" => false));

        $builder->add("surfaceArea", NumberType::class, array ("required" => false));

        $builder->add("roomMateCount", NumberType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ('data_class' => Housing::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "housing";
    }

}