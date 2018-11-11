<?php

namespace App\Core\Entity\Alert;

use App\Core\Entity\AbstractEntity;
use App\Core\Entity\User\User;
use App\Core\Repository\Filter\Searchable;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Dahiorus
 *
 * @ORM\Entity(repositoryClass="App\Core\Repository\Alert\AlertRepository")
 * @ORM\Table(
 *   name="alert",
 *   indexes={
 *     @ORM\Index(name="IDX_ALERT_USER", columns={ "user_id" }),
 *     @ORM\Index(name="IDX_ALERT_FILTER_CLASS", columns={ "filter_class" }),
 *     @ORM\Index(name="IDX_ALERT_NOTIFICATION_TYPE", columns={ "notification_type" })
 * })
 * @ORM\EntityListeners({
 *   "App\Core\Listener\CacheDriverListener",
 *   "App\Core\Listener\UpdateListener"
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="alerts")
 */
class Alert extends AbstractEntity
{
    const DATE_INTERVAL_FORMAT = DateIntervalType::FORMAT;

    /**
     * @var string
     * @ORM\Column(nullable=false, length=255)
     */
    private $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(nullable=false, length=20)
     */
    private $notificationType;

    /**
     * @var string
     * @ORM\Column(nullable=false, length=1024)
     */
    private $filter;

    /**
     * @var string
     * @ORM\Column(nullable=false, length=80)
     */
    private $filterClass;

    /**
     * @var int
     * @ORM\Column(nullable=false, options={ "default"=10 })
     */
    private $resultSize = 10;

    /**
     * @var \DateInterval
     * @ORM\Column(type="dateinterval", nullable=false)
     */
    private $searchPeriod;

    /**
     * @var string
     * @ORM\Column(nullable=false, length=10)
     */
    private $status;


    public function __construct(User $user, string $filterClass, string $filter)
    {
        if (!is_subclass_of($filterClass, Searchable::class))
        {
            throw new \InvalidArgumentException("The filterClass parameter must be a subclass of " . Searchable::class);
        }

        $this->user = $user;
        $this->filterClass = $filterClass;
        $this->filter = $filter;
        $this->status = AlertStatus::ENABLED;
    }


    public function __toString() : string
    {
        $searchPeriod = empty($this->searchPeriod) ? null : $this->searchPeriod->format(self::DATE_INTERVAL_FORMAT);

        return parent::__toString() . "[name=" . $this->name . ", notificationType=" . $this->notificationType
            . ", filterClass=" . $this->filterClass . ", filter=" . $this->filter . ", resultSize=" . $this->resultSize
            . ", searchPeriod=" . $searchPeriod . ", status=" . $this->status . "]";
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }


    public function getUser()
    {
        return $this->user;
    }


    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }


    public function getNotificationType()
    {
        return $this->notificationType;
    }


    public function setNotificationType(string $notificationType)
    {
        $this->notificationType = $notificationType;

        return $this;
    }


    public function getFilter() : string
    {
        return $this->filter;
    }


    public function setFilter(string $filter)
    {
        $this->filter = $filter;

        return $this;
    }


    public function getFilterClass()
    {
        return $this->filterClass;
    }


    public function setFilterClass(string $filterClass)
    {
        $this->filterClass = $filterClass;

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


    public function getSearchPeriod()
    {
        return $this->searchPeriod;
    }


    public function setSearchPeriod(\DateInterval $searchPeriod = null)
    {
        $this->searchPeriod = $searchPeriod;

        return $this;
    }


    public function getStatus() : string
    {
        return $this->status;
    }


    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }

}
