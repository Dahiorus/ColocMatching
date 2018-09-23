<?php

namespace App\Core\Validator\Constraint;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Annotation\RelatedEntity;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueValueValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;


    /**
     * UniqueValueValidator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AnnotationReader $annotationReader
     */
    public function __construct(EntityManagerInterface $entityManager, AnnotationReader $annotationReader)
    {
        $this->entityManager = $entityManager;
        $this->annotationReader = $annotationReader;
    }


    public function validate($value, Constraint $constraint)
    {
        if (!($constraint instanceof UniqueValue))
        {
            throw new UnexpectedTypeException($constraint, UniqueValue::class);
        }

        if (!is_array($constraint->properties) && !is_string($constraint->properties))
        {
            throw new UnexpectedTypeException($constraint->properties, "array or string");
        }

        if (!empty($constraint->errorProperty) && !is_string($constraint->errorProperty))
        {
            throw new UnexpectedTypeException($constraint->errorProperty, "string or null");
        }

        if (empty($constraint->properties))
        {
            throw new ConstraintDefinitionException("At least one property has to be specified");
        }

        if (empty($value))
        {
            return;
        }

        if (!($value instanceof AbstractDto))
        {
            throw new ConstraintDefinitionException(
                "The value to validate has to be an instance of " . AbstractDto::class);
        }

        $entityClass = $value->getEntityClass();
        $properties = (array)$constraint->properties;
        $repository = $this->entityManager->getRepository($entityClass);

        $criteria = $this->buildCriteria($value, $properties);

        foreach ($criteria as $property => $propertyValue)
        {
            // filter all NULL values if ignoreNull is configured
            if (is_null($propertyValue) && $constraint->ignoreNull)
            {
                unset($criteria[ $property ]);
            }
        }

        if (empty($criteria))
        {
            return;
        }

        $result = $repository->findBy($criteria);

        if (empty($result))
        {
            return;
        }

        $errorProperty = empty($constraint->errorProperty) ? $properties[0] : $constraint->errorProperty;
        $invalidValue = isset($criteria[ $errorProperty ]) ? $criteria[ $errorProperty ] : $criteria[ $properties[0] ];

        $this->context
            ->buildViolation($constraint->message)
            ->atPath($errorProperty)
            ->setInvalidValue($invalidValue)
            ->setCause($result)
            ->addViolation();
    }


    /**
     * Builds a criteria array for the specified value and the list of properties
     *
     * @param AbstractDto $value The value
     * @param string[] $properties The properties
     *
     * @return array
     */
    private function buildCriteria(AbstractDto $value, array $properties) : array
    {
        $entityClass = $value->getEntityClass();
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);
        $criteria = array ();

        foreach ($properties as $property)
        {
            $reflectionObject = new \ReflectionObject($value);

            try
            {
                $reflectionProperty = $reflectionObject->getProperty($property);
            }
            catch (\Exception $e)
            {
                throw new ConstraintDefinitionException(
                    "The property '$entityClass'::'$property' is unknown");
            }

            /** @var RelatedEntity $relatedEntityAnnotation */
            $relatedEntityAnnotation =
                $this->annotationReader->getPropertyAnnotation($reflectionProperty, RelatedEntity::class);

            // simple attribute without relation to another entity
            if (empty($relatedEntityAnnotation))
            {
                if (!$classMetadata->hasField($property))
                {
                    throw new ConstraintDefinitionException(
                        "The property '$entityClass'::'$property' is not mapped by Doctrine, so it cannot be validated for uniqueness");
                }

                $criteria[ $property ] = $this->getPropertyValue($value, $property);
            }
            else // attribute related to another entity
            {
                $entityProperty = $relatedEntityAnnotation->getTargetProperty();

                if (!$classMetadata->hasAssociation($entityProperty))
                {
                    throw new ConstraintDefinitionException(
                        "The property '$entityClass'::'$property' is not mapped by Doctrine, so it cannot be validated for uniqueness");
                }

                $entityClass = $relatedEntityAnnotation->getTargetClass();
                $entityId = $this->getPropertyValue($value, $property);

                $relatedEntity = $this->entityManager->getRepository($entityClass)->find($entityId);

                $criteria[ $entityProperty ] = $relatedEntity;
            }
        }

        return $criteria;
    }


    /**
     * Calls the specified property getter to get its value from the specified AbstractDto
     *
     * @param AbstractDto $value The AbstractDto
     * @param string $property The property
     *
     * @return mixed
     */
    private function getPropertyValue(AbstractDto $value, string $property)
    {
        $getter = "get" . ucfirst($property);

        try
        {
            $reflectionMethod = new \ReflectionMethod($value, $getter);

            return $reflectionMethod->invoke($value);
        }
        catch (\ReflectionException $e)
        {
            throw new ConstraintDefinitionException(
                "No getter exists for the property '$property' in the class " . get_class($value));
        }
    }

}
