<?php

namespace ColocMatching\CoreBundle\Validator;

use ColocMatching\CoreBundle\Entity\Common\Document;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\DocumentType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\FormInterface;

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
     * Gets the FormType for an entity
     *
     * @param string $formClass The FormType class to get
     * @param EntityInterface $entity The entity to process
     * @param array $options Form options
     * @return FormInterface
     */
    public function getFormType(string $formClass, EntityInterface $entity, array $options = array ()): FormInterface {
        return $this->formFactory->create($formClass, $entity, $options);
    }


    /**
     * Validates the data in the entity form
     *
     * @param EntityInterface $entity The entity to process
     * @param array $data The data to validate
     * @param string $formClass The FormType class
     * @param bool $clearMissing Indicates that if missing data are considered as null value
     * @param array $options Form options
     * @throws InvalidFormDataException
     * @return EntityInterface
     */
    public function validateEntityForm(EntityInterface $entity, array $data, string $formClass, bool $clearMissing,
        array $options = array()): EntityInterface {
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->getFormType($formClass, $entity, $options);

        if (!$form->submit($data, $clearMissing)->isValid()) {
            $this->logger->error("Submitted data is invalid",
                array ("clearMissing" => $clearMissing, "entity" => $entity, "data" => $data, "form" => $form));

            throw new InvalidFormDataException(sprintf("Invalid submitted data in the form '%s'", $formClass),
                $form->getErrors(true, true));
        }

        $this->logger->debug("Submitted data is valid",
            array ("entity" => $entity, "data" => $data, "clearMissing" => $clearMissing));

        return $entity;
    }


    /**
     * Validates the file in the Document form
     *
     * @param Document $document The document to process
     * @param File $file The file to validate
     * @param string $dataClass The Document instance class
     * @throws InvalidFormDataException
     * @return Document
     */
    public function validateDocumentForm(Document $document, File $file, string $dataClass): Document {
        /** @var DocumentType */
        $form = $this->getFormType(DocumentType::class, $document, array ("data_class" => $dataClass));

        if (!$form->submit(array ("file" => $file), true)->isValid()) {
            $this->logger->error("Submitted file is invalid",
                array ("document" => $document, "file" => $file, "form" => $form));

            throw new InvalidFormDataException("Invalid submitted data in the Document form",
                $form->getErrors(true, true));
        }

        $this->logger->debug("Submitted document is valid",
            array ("picture" => $document, "file" => $file, "data_class" => $dataClass));

        return $document;
    }

}