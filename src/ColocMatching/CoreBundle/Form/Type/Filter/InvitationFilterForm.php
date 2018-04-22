<?php

namespace ColocMatching\CoreBundle\Form\Type\Filter;

use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Form\Type\BooleanType;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationFilterForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("invitableId", NumberType::class, array ("required" => false));
        $builder->add("recipientId", NumberType::class, array ("required" => false));
        $builder->add("sourceTypes", ChoiceType::class, array ("required" => false,
            "choices" => array (
                "invitable" => Invitation::SOURCE_INVITABLE,
                "search" => Invitation::SOURCE_SEARCH),
            "multiple" => true));
        $builder->add("hasMessage", BooleanType::class, array ("required" => false));
        $builder->add("status", ChoiceType::class, array ("required" => false,
            "choices" => array (
                "waiting" => Invitation::STATUS_WAITING,
                "accepted" => Invitation::STATUS_ACCEPTED,
                "refused" => Invitation::STATUS_REFUSED)));
        $builder->add("createdAtSince", DateTimeType::class, array ("required" => false));
        $builder->add("createdAtUntil", DateTimeType::class, array ("required" => false));
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => InvitationFilter::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "invitation_filter";
    }

}
