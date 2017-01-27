<?php

namespace ColocMatching\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;

class AddressType extends AbstractType {


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer(new AddressTypeToAddressTransformer());
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("compound" => false));
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix() {
        return "address";
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getParent()
     */
    public function getParent() {
        return TextType::class;
    }

}