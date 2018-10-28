<?php

namespace App\Core\Form\Type\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\Entity\Alert\AlertStatus;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Form\Type\Filter\AnnouncementFilterForm;
use App\Core\Form\Type\Filter\GroupFilterForm;
use App\Core\Form\Type\Filter\UserFilterForm;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\GroupFilter;
use App\Core\Repository\Filter\Searchable;
use App\Core\Repository\Filter\UserFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlertDtoForm extends AbstractType
{
    private const FILTER_CLASS_OPTION = "filter_class";

    private $filterFormClasses = array (
        AnnouncementFilter::class => AnnouncementFilterForm::class,
        GroupFilter::class => GroupFilterForm::class,
        UserFilter::class => UserFilterForm::class,
    );


    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formClass = $this->filterFormClasses[ $options[ self::FILTER_CLASS_OPTION ] ];

        $builder->add("name", TextType::class, array ("required" => true));
        $builder->add("notificationType", ChoiceType::class, array (
            "required" => true,
            "choices" => array (
                "email" => NotificationType::EMAIL,
                "push" => NotificationType::PUSH,
                "sms" => NotificationType::SMS
            )
        ));
        $builder->add("searchPeriod", DateIntervalType::class, array (
            "required" => true,
            "widget" => "single_text",
            "with_years" => false,
            "documentation" => array (
                "type" => "string",
                "example" => "P0M2D"
            )
        ));
        $builder->add("status", ChoiceType::class, array (
            "required" => false,
            "choices" => array (
                "enabled" => AlertStatus::ENABLED,
                "disabled" => AlertStatus::DISABLED,
            ),
            "empty_data" => AlertStatus::ENABLED
        ));
        $builder->add("filter", $formClass, array ("required" => true));

        parent::buildForm($builder, $options);
    }


    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array ("data_class" => AlertDto::class));

        // build the 'filter_class' option to get the Searchable form to use in data validation
        $resolver->setRequired(array (self::FILTER_CLASS_OPTION));
        $resolver->addAllowedTypes(self::FILTER_CLASS_OPTION, "string");
        $resolver->addAllowedValues(self::FILTER_CLASS_OPTION, function ($value) {
            $supportedFilters = array_keys($this->filterFormClasses);

            return is_subclass_of($value, Searchable::class) && in_array($value, $supportedFilters, true);
        });
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return "alert";
    }
}