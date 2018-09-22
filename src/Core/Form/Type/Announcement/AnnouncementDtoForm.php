<?php

namespace App\Core\Form\Type\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\Entity\Announcement\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementDtoForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("title", TextType::class, array ("required" => true));
        $builder->add("type", ChoiceType::class,
            array (
                "required" => true,
                "choices" => array (
                    "rent" => Announcement::TYPE_RENT,
                    "sublease" => Announcement::TYPE_SUBLEASE,
                    "sharing" => Announcement::TYPE_SHARING)));
        $builder->add("description", TextareaType::class, array ("required" => false));
        $builder->add("location", TextType::class, array ("required" => true));
        $builder->add("rentPrice", NumberType::class, array ("required" => true));
        $builder->add("startDate", DateType::class,
            array ("required" => true, "widget" => "single_text"));
        $builder->add("endDate", DateType::class,
            array ("required" => false, "widget" => "single_text"));
        $builder->add("status", ChoiceType::class, array ("choices" =>
            array (
                "enabled" => Announcement::STATUS_ENABLED,
                "filled" => Announcement::STATUS_FILLED,
                "disabled" => Announcement::STATUS_DISABLED),
            "required" => false,
            "empty_data" => Announcement::STATUS_ENABLED));
        $builder->add("housingType", HousingTypeType::class, array ("required" => false));
        $builder->add("roomCount", NumberType::class, array ("required" => false));
        $builder->add("bedroomCount", NumberType::class, array ("required" => false));
        $builder->add("bathroomCount", NumberType::class, array ("required" => false));
        $builder->add("surfaceArea", NumberType::class, array ("required" => false));
        $builder->add("roomMateCount", NumberType::class, array ("required" => false));

        parent::buildForm($builder, $options);
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => AnnouncementDto::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "announcement";
    }

}