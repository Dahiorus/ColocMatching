<?php

namespace ColocMatching\CoreBundle\Tests\Validator\Constraint;

use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

abstract class AbstractValidatorTest extends AbstractServiceTest
{
    /**
     * Gets an instance of ConstraintValidatorInterface to test
     *
     * @return ConstraintValidatorInterface
     */
    protected abstract function getValidatorInstance() : ConstraintValidatorInterface;


    /**
     * Initializes the validator
     *
     * @param string $expectedMessage The expected constraint violation message
     *
     * @return ConstraintValidatorInterface
     */
    protected function initValidator(?string $expectedMessage)
    {
        $builder = $this->createPartialMock(ConstraintViolationBuilder::class, array ("addViolation"));
        /** @var \PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createPartialMock(ExecutionContext::class, array ("buildViolation"));

        if (!empty($expectedMessage))
        {
            $builder->expects(self::once())->method("addViolation");
            $context->expects(self::once())->method("buildViolation")
                ->with($expectedMessage)
                ->willReturn($builder);
        }
        else
        {
            $context->expects($this->never())->method("buildViolation");
        }

        $validator = $this->getValidatorInstance();
        /** @var ExecutionContext $context */
        $validator->initialize($context);

        return $validator;
    }
}