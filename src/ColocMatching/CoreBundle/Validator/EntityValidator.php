<?php

namespace ColocMatching\CoreBundle\Validator;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\Picture;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\PictureType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;

class EntityValidator {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    public function __construct(LoggerInterface $logger, FormFactoryInterface $formFactory) {
        $this->logger = $logger;
        $this->formFactory = $formFactory;
    }


    /**
     * Validates the data in the form
     *
     * @param mixed $object      The object to process
     * @param array $data        The data to validate
     * @param string $formClass  The FormType class
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param array $options     Form options
     *
     * @throws InvalidFormException
     * @return EntityInterface
     */
    public function validateForm($object, array $data, string $formClass, bool $clearMissing,
        array $options = array ()) {

        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->formFactory->create($formClass, $object, $options);

        if (!$form->submit($data, $clearMissing)->isValid()) {
            $this->logger->error("Submitted data is invalid",
                array ("clearMissing" => $clearMissing, "object" => $object, "data" => $data, "form" => $form));

            throw new InvalidFormException(sprintf("Invalid submitted data in the form '%s'", $formClass),
                $form->getErrors(true));
        }

        $this->logger->debug("Submitted data is valid",
            array ("object" => $object, "data" => $data, "clearMissing" => $clearMissing, "options" => $options));

        return $object;
    }


    /**
     * Validates the data in the entity form
     *
     * @param EntityInterface $entity The entity to process
     * @param array $data             The data to validate
     * @param string $formClass       The FormType class
     * @param bool $clearMissing      Indicates that if missing data are considered as null value
     * @param array $options          Form options
     *
     * @throws InvalidFormException
     * @return EntityInterface
     */
    public function validateEntityForm(EntityInterface $entity, array $data, string $formClass, bool $clearMissing,
        array $options = array ()) : EntityInterface {

        /** @var EntityInterface $validatedEntity */
        $validatedEntity = $this->validateForm($entity, $data, $formClass, $clearMissing, $options);

        return $validatedEntity;
    }


    /**
     * Validates the file in the Document form
     *
     * @param Picture $picture  The picture to process
     * @param File $file        The file to validate
     * @param string $dataClass The Picture instance class
     *
     * @throws InvalidFormException
     * @return Picture
     */
    public function validatePictureForm(Picture $picture, File $file, string $dataClass) : Picture {
        /** @var Picture $validatedPicture */
        $validatedPicture = $this->validateForm($picture, array ("file" => $file), PictureType::class, true,
            array ("data_class" => $dataClass));

        return $validatedPicture;
    }

}