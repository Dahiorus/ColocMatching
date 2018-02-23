<?php

namespace ColocMatching\CoreBundle\Validator\Constraint;

use ColocMatching\CoreBundle\DTO\AbstractDto;
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
     * UniqueValueValidator constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);
        $criteria = array ();

        foreach ($properties as $property)
        {
            if (!$classMetadata->hasField($property) && !$classMetadata->hasAssociation($property))
            {
                throw new ConstraintDefinitionException(
                    "The property '$entityClass'.'$property' is not mapped by Doctrine, so it cannot be validated for uniqueness");
            }

            $getter = "get" . ucfirst($property);
            $propertyValue = $value->$getter();

            if (is_null($propertyValue) && $constraint->ignoreNull)
            {
                continue;
            }

            $criteria[ $property ] = $propertyValue;
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

}