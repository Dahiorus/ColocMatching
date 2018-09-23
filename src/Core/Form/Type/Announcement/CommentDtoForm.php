<?php

namespace App\Core\Form\Type\Announcement;

use App\Core\DTO\Announcement\CommentDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentDtoForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("message", TextareaType::class, array ("required" => false));
        $builder->add("rate", NumberType::class, array ("required" => false, "empty_data" => strval(0)));

        parent::buildForm($builder, $options);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => CommentDto::class));
    }


    public function getBlockPrefix()
    {
        return "comment";
    }
}