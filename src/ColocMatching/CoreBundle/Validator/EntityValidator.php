<?php

namespace ColocMatching\CoreBundle\Validator;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\PictureDto;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\Picture;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\PictureDtoType;
use ColocMatching\CoreBundle\Form\Type\PictureType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * DTO form validator utility
 *
 * @author Dahiorus
 */
class EntityValidator
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
     * @param mixed $object The object to process
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
            array ("object" => $object, "data" => $data, "clearMissing" => $clearMissing));

        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->formFactory->create($formClass, $object, $options);

        if (!$form->submit($data, $clearMissing)->isValid())
        {
            $this->logger->error("Submitted data is invalid",
                array ("clearMissing" => $clearMissing, "object" => $object, "data" => $data, "form" => $form));

            throw new InvalidFormException($formClass, $form->getErrors(true));
        }

        $this->logger->debug("Submitted data is valid",
            array ("object" => $object, "data" => $data, "clearMissing" => $clearMissing, "options" => $options));

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
        /** @var PictureDto $validatedPicture */
        $validatedPicture = $this->validateForm($picture, array ("file" => $file), PictureDtoType::class, true,
            array ("data_class" => $dataClass));

        return $validatedPicture;
    }


    /**
     * Validates the data in the entity form
     *
     * @param EntityInterface $entity The entity to process
     * @param array $data The data to validate
     * @param string $formClass The FormType class
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param array $options Form options
     *
     * @throws InvalidFormException
     * @return EntityInterface
     * @deprecated
     */
    public function validateEntityForm(EntityInterface $entity, array $data, string $formClass, bool $clearMissing,
        array $options = array ()) : EntityInterface
    {
        /** @var EntityInterface $validatedEntity */
        $validatedEntity = $this->validateForm($entity, $data, $formClass, $clearMissing, $options);

        return $validatedEntity;
    }


    /**
     * Validates the file in the Document form
     *
     * @param Picture $picture The picture to process
     * @param File $file The file to validate
     * @param string $dataClass The Picture instance class
     *
     * @throws InvalidFormException
     * @return Picture
     * @deprecated
     */
    public function validatePictureForm(Picture $picture, File $file, string $dataClass) : Picture
    {
        /** @var Picture $validatedPicture */
        $validatedPicture = $this->validateForm($picture, array ("file" => $file), PictureType::class, true,
            array ("data_class" => $dataClass));

        return $validatedPicture;
    }

}
