<?php

namespace App\Core\DTO\Alert;

use App\Core\DTO\AbstractDto;
use App\Core\Entity\Alert\Alert;
use App\Core\Entity\Alert\AlertStatus;
use App\Core\Entity\Alert\NotificationType;
use App\Core\Repository\Filter\Searchable;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Dahiorus
 *
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_alert", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 * @Hateoas\Relation(
 *   name="user",
 *   href= @Hateoas\Route(name="rest_get_user", absolute=true,
 *     parameters={ "id" = "expr(object.getUserId())" })
 * )
 */
class AlertDto extends AbstractDto
{
    /**
     * Alert name
     * @var string
     * @Assert\NotBlank
     * @Serializer\Expose
     * @SWG\Property(property="name", type="string", example="My alert")
     */
    private $name;

    /**
     * Alert user's identifier
     * @var int
     */
    private $userId;

    /**
     * Alert notification type
     * @var string
     * @Assert\NotBlank
     * @Serializer\Expose
     * @Assert\Choice(choices={ NotificationType::EMAIL, NotificationType::PUSH, NotificationType::SMS }, strict=true)
     * @SWG\Property(property="notificationType", type="string", example="email")
     */
    private $notificationType;

    /**
     * Alert search time interval
     * @var \DateInterval
     * @Serializer\Expose
     * @Assert\NotNull
     * @SWG\Property(property="searchPeriod", type="string", example="P0Y0M3D")
     */
    private $searchPeriod;

    /**
     * Alert state
     * @var string
     * @Serializer\Expose
     * @Assert\Choice(choices={ AlertStatus::ENABLED, AlertStatus::DISABLED }, strict=true)
     * @SWG\Property(property="status", type="string", example="enabled")
     */
    private $status;

    /**
     * Alert search filter
     * @var Searchable
     * @Serializer\Expose
     * @Assert\Valid
     * @SWG\Property(
     *   property="filter", type="object", ref=@Model(type="\App\Core\Repository\Filter\AnnouncementFilter"))
     */
    private $filter;

    /**
     * Alert search result size
     * @var int
     * @Serializer\Expose
     * @Assert\NotNull
     * @SWG\Property(property="resultSize", type="int", example="10")
     */
    private $resultSize;


    public function getEntityClass() : string
    {
        return Alert::class;
    }


    public function __toString() : string
    {
        $searchPeriod = empty($this->searchPeriod) ? null : $this->searchPeriod->format(Alert::DATE_INTERVAL_FORMAT);

        return parent::__toString() . "[name=" . $this->name . ", userId=" . $this->userId
            . ", notificationType=" . $this->notificationType . ", searchPeriod=" . $searchPeriod
            . ", status=" . $this->status . ", resultSize=" . $this->resultSize . "]";
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }


    public function getUserId()
    {
        return $this->userId;
    }


    public function setUserId(?int $userId)
    {
        $this->userId = $userId;

        return $this;
    }


    public function getNotificationType()
    {
        return $this->notificationType;
    }


    public function setNotificationType(?string $notificationType)
    {
        $this->notificationType = $notificationType;

        return $this;
    }


    public function getSearchPeriod()
    {
        return $this->searchPeriod;
    }


    public function setSearchPeriod(\DateInterval $searchPeriod = null)
    {
        $this->searchPeriod = $searchPeriod;

        return $this;
    }


    public function getStatus()
    {
        return $this->status;
    }


    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }


    public function getFilter()
    {
        return $this->filter;
    }


    public function setFilter(Searchable $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }


    public function getResultSize()
    {
        return $this->resultSize;
    }


    public function setResultSize(int $resultSize)
    {
        $this->resultSize = $resultSize;

        return $this;
    }

}
