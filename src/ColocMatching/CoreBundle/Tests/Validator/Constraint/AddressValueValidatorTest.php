<?php

namespace ColocMatching\CoreBundle\Tests\Validator\Constraint;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use ColocMatching\CoreBundle\Validator\Constraint\AddressValue;
use ColocMatching\CoreBundle\Validator\Constraint\AddressValueValidator;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class AddressValueValidatorTest extends AbstractValidatorTest
{
    /**
     * @var AddressTypeToAddressTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressTransformer;


    protected function setUp()
    {
        parent::setUp();
        $this->addressTransformer = $this->createMock(AddressTypeToAddressTransformer::class);
    }


    protected function getValidatorInstance() : ConstraintValidatorInterface
    {
        return new AddressValueValidator($this->addressTransformer);
    }


    /**
     * @test
     */
    public function validationOK()
    {
        $value = "50 rue du paradis";
        $this->addressTransformer->method("reverseTransform")->with($value)->willReturn(new Address());

        $constraint = new AddressValue();
        $validator = $this->initValidator(null);
        $validator->validate($value, $constraint);
    }


    /**
     * @test
     */
    public function validationKO()
    {
        $value = "azertyuiop";
        $this->addressTransformer->method("reverseTransform")->with($value)
            ->willThrowException(new TransformationFailedException("Exception from test"));

        $constraint = new AddressValue();
        $validator = $this->initValidator($constraint->message);
        $validator->validate($value, $constraint);
    }
}