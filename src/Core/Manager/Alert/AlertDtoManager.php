<?php

namespace App\Core\Manager\Alert;

use App\Core\DTO\Alert\AlertDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Alert\Alert;
use App\Core\Form\Type\Alert\AlertDtoForm;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\Alert\AlertDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Alert\AlertRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class AlertDtoManager extends AbstractDtoManager implements AlertDtoManagerInterface
{
    /** @var AlertRepository */
    protected $repository;

    /** @var AlertDtoMapper */
    protected $dtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, AlertDtoMapper $dtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);
        $this->formValidator = $formValidator;
        $this->userDtoMapper = $userDtoMapper;
    }


    public function findByUser(UserDto $user, Pageable $pageable = null)
    {
        $this->logger->debug("Finding a user's alerts", array ("user" => $user, "pageable" => $pageable));

        $userEntity = $this->userDtoMapper->toEntity($user);
        $entities = $this->repository->findByUser($userEntity, $pageable);

        $this->logger->info("User's alert found", array ("count" => count($entities)));

        return $this->buildDtoCollection($entities, $this->repository->countByUser($userEntity), $pageable);
    }


    public function countByUser(UserDto $user) : int
    {
        $this->logger->debug("Counting all user's alerts", array ("user" => $user));

        $userEntity = $this->userDtoMapper->toEntity($user);

        return $this->repository->countByUser($userEntity);
    }


    public function create(UserDto $user, string $filterClass, array $data, bool $flush = true) : AlertDto
    {
        $this->logger->debug("Creating a new alert", array ("user" => $user, "data" => $data, "flush" => $flush));

        /** @var AlertDto $alertDto */
        $alertDto = $this->formValidator->validateDtoForm(new AlertDto(), $data, AlertDtoForm::class, true,
            array ("filter_class" => $filterClass));
        $alertDto->setUserId($user->getId());

        /** @var Alert $alert */
        $alert = $this->dtoMapper->toEntity($alertDto);

        $this->em->persist($alert);
        $this->flush($flush);

        $this->logger->info("Alert created", array ("alert" => $alert));

        return $this->dtoMapper->toDto($alert);
    }


    public function update(AlertDto $alert, array $data, bool $clearMissing, bool $flush = true) : AlertDto
    {
        $this->logger->debug("Updating an existing alert",
            array ("alert" => $alert, "data" => $data, "clearMissing" => $clearMissing, "flush" => $flush));

        $filterClass = get_class($alert->getFilter());
        /** @var AlertDto $alertDto */
        $alertDto = $this->formValidator->validateDtoForm($alert, $data, AlertDtoForm::class, $clearMissing,
            array ("filter_class" => $filterClass));

        /** @var Alert $updatedAlert */
        $updatedAlert = $this->em->merge($this->dtoMapper->toEntity($alertDto));
        $this->flush($flush);

        $this->logger->info("Alert updated", array ("alert" => $updatedAlert));

        return $this->dtoMapper->toDto($updatedAlert);
    }


    protected function getDomainClass() : string
    {
        return Alert::class;
    }

}
