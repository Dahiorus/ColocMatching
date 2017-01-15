<?php

namespace ColocMatching\CoreBundle\Form\Type\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add("title", TextType::class, array (
				"description" => "Announcement title",
				"required" => true
			))
			->add("description", TextareaType::class, array(
				"description" => "Announcement description",
				"required" => false
			))
			->add("location", AddressType::class, array (
				"description" => "Announcement location",
				"required" => true
			))
			->add("minPrice", NumberType::class, array (
				"description" => "Announcement minimum price",
				"required" => true
			))
			->add("maxPrice", NumberType::class, array (
					"description" => "Announcement maximum price",
					"required" => false
			))
			->add("startDate", DateType::class, array (
				"description" => "Announcement start date",
				"required" => true,
				"widget" => "single_text",
				"format" => "dd/MM/yyyy"
			))
			->add("endDate", DateType::class, array (
				"description" => "Announcement end date",
				"required" => false,
				"widget" => "single_text",
				"format" => "dd/MM/yyyy"
			));
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => Announcement::class,
		));
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'announcement';
	}
}