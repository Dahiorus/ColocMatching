<?php

namespace ColocMatching\CoreBundle\Form\Type\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationDtoForm extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("message", TextareaType::class, array ("required" => false));
        $builder->add("status", ChoiceType::class, array (
            "required" => false,
            "choices" => array (
                "waiting" => Invitation::STATUS_WAITING,
                "accepted" => Invitation::STATUS_ACCEPTED,
                "refused" => Invitation::STATUS_REFUSED
            ),
            "empty_data" => Invitation::STATUS_WAITING));
        $builder->add("sourceType", ChoiceType::class, array (
            "required" => true,
            "choices" => array (
                "invitable" => Invitation::SOURCE_INVITABLE,
                "search" => Invitation::SOURCE_SEARCH)));

        parent::buildForm($builder, $options);
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => InvitationDto::class));
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "invitation";
    }
}
