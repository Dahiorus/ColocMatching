<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\Announcement\Housing;
use ColocMatching\CoreBundle\Repository\Filter\HousingFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HousingFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("types", ChoiceType::class, array ("choices" =>
            array (
                "apartment" => Housing::TYPE_APARTMENT,
                "house" => Housing::TYPE_HOUSE,
                "studio" => Housing::TYPE_STUDIO),
            "required" => false, "multiple" => true));
        $builder->add("roomCount", NumberType::class, array ("required" => false));
        $builder->add("bedroomCount", NumberType::class, array ("required" => false));
        $builder->add("bathroomCount", NumberType::class, array ("required" => false));
        $builder->add("surfaceAreaMin", NumberType::class, array ("required" => false));
        $builder->add("surfaceAreaMax", NumberType::class, array ("required" => false));
        $builder->add("roomMateCount", NumberType::class, array ("required" => false));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => HousingFilter::class));
    }


    public function getBlockPrefix()
    {
        return "housing_filter";
    }

}

