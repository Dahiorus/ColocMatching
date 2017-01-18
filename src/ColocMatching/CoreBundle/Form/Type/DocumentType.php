<?php

namespace ColocMatching\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ColocMatching\CoreBundle\Entity\Common\Document;

class DocumentType extends AbstractType {

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
		$resolver->setDefaults(array (
			"data_class" => Document::class
		));
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix() {
		return "document";
	}
}