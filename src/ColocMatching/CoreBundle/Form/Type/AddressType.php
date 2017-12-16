<?php

namespace ColocMatching\CoreBundle\Form\Type;

use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType {

    /** @var string */
    private $region;

    /** @var string */
    private $apiKey;


    /**
     * AddressType constructor.
     *
     * @param string $region
     * @param string $apiKey
     */
    public function __construct(string $region, string $apiKey) {
        $this->region = $region;
        $this->apiKey = $apiKey;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer(
            new AddressTypeToAddressTransformer($this->region, $this->apiKey, $options["entity"]));
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("compound" => false, "entity" => null));
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