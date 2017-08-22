<?php

namespace ColocMatching\CoreBundle\Form\Type\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("message", TextareaType::class, array ("required" => false));

        parent::buildForm($builder, $options);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => Invitation::class));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "invitation";
    }
}
