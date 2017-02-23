<?php

namespace ColocMatching\CoreBundle\Form\Type\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnnouncementType extends AbstractType {

    const DATE_FORMAT = "dd/MM/yyyy";


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("title", TextType::class, array ("required" => true));
        
        $builder->add("type", ChoiceType::class, 
            array (
                "required" => true, 
                "choices" => array (
                    "rent" => Announcement::TYPE_RENT, 
                    "sublease" => Announcement::TYPE_SUBLEASE, 
                    "sharing" => Announcement::TYPE_SHARING)));
        
        $builder->add("description", TextareaType::class, array ("required" => false));
        
        $builder->add("location", AddressType::class, array ("required" => true));
        
        $builder->add("minPrice", NumberType::class, array ("required" => true));
        
        $builder->add("maxPrice", NumberType::class, array ("required" => false));
        
        $builder->add("startDate", DateType::class, 
            array ("required" => true, "widget" => "single_text", "format" => self::DATE_FORMAT));
        
        $builder->add("endDate", DateType::class, 
            array ("required" => false, "widget" => "single_text", "format" => self::DATE_FORMAT));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ('data_class' => Announcement::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'announcement';
    }

}