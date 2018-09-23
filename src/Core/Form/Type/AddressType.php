<?php

namespace App\Core\Form\Type;

use App\Core\Form\DataTransformer\StringToAddressTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /** @var StringToAddressTransformer */
    private $addressTransformer;


    public function __construct(StringToAddressTransformer $addressTransformer)
    {
        $this->addressTransformer = $addressTransformer;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->addressTransformer);
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault("compound", false);
        $resolver->setDefault("invalid_message", "This value is not a valid postal address");
        $resolver->setDefault("documentation", array (
            "type" => "string"
        ));
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix()
    {
        return "address";
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::getParent()
     */
    public function getParent()
    {
        return TextType::class;
    }

}