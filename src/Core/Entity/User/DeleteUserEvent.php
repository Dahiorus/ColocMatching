<?php

namespace App\Core\Entity\User;

use App\Core\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *   name="delete_user_event",
 *   indexes={
 *     @ORM\Index(name="IDX_DELETE_USER_EVENT_DATE", columns={"delete_at"})
 * })
 * @ORM\Entity(repositoryClass="App\Core\Repository\User\DeleteUserEventRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="delete_user_events")
 *
 * @author Dahiorus
 */
class DeleteUserEvent extends AbstractEntity
{
    /**
     * @var \DateTime
     */
    protected $lastUpdate; // override to remove the @Column definition

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="date_immutable")
     */
    private $deleteAt;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity=User::class, fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", nullable=false, unique=true)
     */
    private $user;


    public function __construct(User $user)
    {
        $this->user = $user;

        try
        {
            $this->deleteAt = new \DateTimeImmutable("+2 weeks");
        }
        catch (\Exception $e)
        {
            throw new \RuntimeException("Unable to create a DeleteUserEventRepository for [$user]", 0, $e);
        }
    }


    public function __toString()
    {
        return parent::__toString()
            . "[deleteAt = " . $this->deleteAt->format(\DateTime::ISO8601)
            . ", user = " . $this->user
            . "]";
    }


    public function getDeleteAt()
    {
        return $this->deleteAt;
    }


    public function setDeleteAt(\DateTimeImmutable $deleteAt = null)
    {
        $this->deleteAt = $deleteAt;

        return $this;
    }


    public function getUser()
    {
        return $this->user;
    }


    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

}
