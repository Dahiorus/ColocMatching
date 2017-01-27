<?php

namespace ColocMatching\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ColocMatching\CoreBundle\Form\DataTransformer\BooleanTypeToBooleanTransformer;

/**
 * Class representing a boolean form type to avoid using CheckboxType
 * which does not work with JSON data
 *
 * @author brondon.ung
 */
class BooleanType extends AbstractType {

    const VALUE_FALSE = 0;

    const VALUE_TRUE = 1;


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer(new BooleanTypeToBooleanTransformer());
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array ('compound' => false));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'boolean';
    }

}