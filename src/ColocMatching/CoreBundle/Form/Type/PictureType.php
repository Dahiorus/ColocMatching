<?php

namespace ColocMatching\CoreBundle\Form\Type;

use ColocMatching\CoreBundle\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureType extends AbstractType {

    /**
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('file', FileType::class);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ("data_class" => Picture::class, "allow_extra_fields" => true));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return "document";
    }

}