<?php

namespace ColocMatching\CoreBundle\Manager;

use ColocMatching\CoreBundle\Entity\Common\Document;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\DocumentType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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
     * Validates the data in the entity form
     *
     * @param EntityInterface $entity The entity to process
     * @param array $data The data to validate
     * @param string $formClass The FormType class
     * @param string $httpMethod The HTTP method to process data
     * @param array $options Form options
     * @throws InvalidFormDataException
     * @return EntityInterface
     */
    public function validateEntityForm(EntityInterface $entity, array $data, string $formClass, string $httpMethod,
        array $options = []): EntityInterface {
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create($formClass, $entity, $options);

        if (!$form->submit($data, $httpMethod != "PATCH")->isValid()) {
            $this->logger->error(
                sprintf("Submitted data is invalid [entity class: '%s', http method: '%s']", get_class($entity),
                    $httpMethod), [ "method" => $httpMethod, "entity" => $entity, "data" => $data, "form" => $form]);

            throw new InvalidFormDataException(sprintf("Invalid submitted data in the form '%s'", $formClass),
                $form->getErrors(true, true));
        }

        $this->logger->debug(
            sprintf("Submitted data is valid [entity class: '%s', http method: '%s']", get_class($entity), $httpMethod),
            [ "data" => $data, "http method" => $httpMethod]);

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
        $form = $this->formFactory->create(DocumentType::class, $document, [ "data_class" => $dataClass]);

        if (!$form->submit([ "file" => $file, true])->isValid()) {
            $this->logger->error(sprintf("Submitted file is invalid [data class: '%s', file: %s]", $dataClass, $file),
                [ "document" => $document, "file" => $file, "form" => $form]);

            throw new InvalidFormDataException("Invalid submitted data in the Document form",
                $form->getErrors(true, true));
        }

        $this->logger->debug(
            sprintf("Submitted Document is valid [document: %s, data class: '%s']", $document, $dataClass),
            [ "picture" => $document, "file" => $file]);

        return $document;
    }

}