<?php

namespace App\Core\Validator;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\PictureDto;
use App\Core\Exception\InvalidFormException;
use App\Core\Form\Type\PictureDtoForm;
use App\Core\Repository\Filter\Searchable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * DTO form validator utility
 *
 * @author Dahiorus
 */
class FormValidator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    public function __construct(LoggerInterface $logger, FormFactoryInterface $formFactory)
    {
        $this->logger = $logger;
        $this->formFactory = $formFactory;
    }


    /**
     * Validates the data in the form
     *
     * @param mixed $object The object to process. Can be null.
     * @param array $data The data to validate
     * @param string $formClass The FormType class
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param array $options Form options
     *
     * @return mixed
     * @throws InvalidFormException
     */
    public function validateForm($object, array $data, string $formClass, bool $clearMissing,
        array $options = array ())
    {
        $this->logger->debug("Validating data",
            array ("object" => $object, "data" => $this->filterDataToLog($data), "clearMissing" => $clearMissing));

        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->formFactory->create($formClass, $object, $options);

        if (!$form->submit($data, $clearMissing)->isValid())
        {
            $this->logger->error("Submitted data is invalid",
                array ("clearMissing" => $clearMissing, "data" => $this->filterDataToLog($data),
                    "errors" => $form->getErrors(true, false)));

            throw new InvalidFormException($formClass, $form->getErrors(true));
        }

        $this->logger->debug("Submitted data is valid",
            array ("object" => $object, "clearMissing" => $clearMissing, "options" => $options));

        return $object;
    }


    /**
     * Validates the data in the entity form
     *
     * @param AbstractDto $dto The DTO to process
     * @param array $data The data to validate
     * @param string $formClass The FormType class
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param array $options Form options
     *
     * @return AbstractDto
     * @throws InvalidFormException
     */
    public function validateDtoForm(AbstractDto $dto, array $data, string $formClass, bool $clearMissing,
        array $options = array ()) : AbstractDto
    {
        /** @var AbstractDto $validDto */
        $validDto = $this->validateForm($dto, $data, $formClass, $clearMissing, $options);

        return $validDto;
    }


    /**
     * Validates the file in the picture form
     *
     * @param PictureDto $picture The picture to process
     * @param File $file The file to validate
     * @param string $dataClass The Picture instance class
     *
     * @return PictureDto
     * @throws InvalidFormException
     */
    public function validatePictureDtoForm(PictureDto $picture, File $file, string $dataClass) : PictureDto
    {
        /** @var PictureDto $validPicture */
        $validPicture = $this->validateForm($picture, array ("file" => $file), PictureDtoForm::class, true,
            array ("data_class" => $dataClass));

        return $validPicture;
    }


    /**
     * Validates the data in the filter form
     *
     * @param string $formClass The filter form class
     * @param Searchable $filter The search filter
     * @param array $data The data to validate
     * @param array $options The form options
     *
     * @return Searchable
     * @throws InvalidFormException
     */
    public function validateFilterForm(string $formClass, Searchable $filter, array $data,
        array $options = array ()) : Searchable
    {
        /** @var Searchable $validFilter */
        $validFilter = $this->validateForm($filter, $data, $formClass, false, $options);

        return $validFilter;
    }


    /**
     * Filters the data to log (such as password value)
     *
     * @param array $data The data to filter
     *
     * @return array The filtered data
     */
    private function filterDataToLog(array $data)
    {
        return array_map(function ($elt) use ($data) {
            $name = strtolower(array_search($elt, $data));

            return (strpos($name, "password") === false) ? $elt : "********";
        }, $data);
    }

}
