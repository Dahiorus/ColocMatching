<?php

namespace ColocMatching\CoreBundle\Form\Type\Group;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupDtoForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("name", TextType::class, array ("required" => true));

        $builder->add("description", TextareaType::class, array ("required" => false));

        $builder->add("budget", NumberType::class, array ("required" => false));

        $builder->add("status", ChoiceType::class,
            array (
                "required" => false,
                "choices" => array ("opened" => Group::STATUS_OPENED, "closed" => Group::STATUS_CLOSED),
                "empty_data" => Group::STATUS_OPENED));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ('data_class' => GroupDto::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'group';
    }

}