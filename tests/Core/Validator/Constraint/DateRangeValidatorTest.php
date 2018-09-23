<?php

namespace App\Tests\Core\Validator\Constraint;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\User\UserDto;
use App\Core\Validator\Constraint\DateRange;
use App\Core\Validator\Constraint\DateRangeValidator;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateRangeValidatorTest extends AbstractValidatorTest
{
    protected function getValidatorInstance() : ConstraintValidatorInterface
    {
        return new DateRangeValidator();
    }


    /**
     * @test
     */
    public function bothDatesAreValid()
    {
        $announcement = new AnnouncementDto();
        $announcement->setStartDate(new \DateTime());
        $announcement->setEndDate(new \DateTime("+2 months"));

        $constraint = new DateRange();
        $validator = $this->initValidator(null);

        $validator->validate($announcement, $constraint);
    }


    /**
     * @test
     */
    public function endDateIsBeforeStartDate()
    {
        $announcement = new AnnouncementDto();
        $announcement->setStartDate(new \DateTime());
        $announcement->setEndDate(new \DateTime("-2 months"));

        $constraint = new DateRange();
        $validator = $this->initValidator($constraint->message);

        $validator->validate($announcement, $constraint);
    }


    /**
     * @test
     */
    public function endDateIsNull()
    {
        $announcement = new AnnouncementDto();
        $announcement->setStartDate(new \DateTime());

        $constraint = new DateRange();
        $validator = $this->initValidator(null);

        $validator->validate($announcement, $constraint);
    }


    /**
     * @test
     */
    public function validateOtherValueShouldThrowConstraintDefinitionException()
    {
        $this->expectException(ConstraintDefinitionException::class);

        $validator = $this->initValidator(null);

        $validator->validate(new UserDto(), new DateRange());
    }


    /**
     * @test
     */
    public function validateWithOtherConstraintShouldThrowUnexpectedTypeException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $validator = $this->initValidator(null);

        $validator->validate(new AnnouncementDto(), new Blank());
    }

}