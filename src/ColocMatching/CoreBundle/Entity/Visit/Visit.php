<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Visit\VisitRepository")
 * @ORM\Table(name="visit")
 *
 * @author Dahiorus
 */
class Visit extends AbstractEntity
{
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(name="visited_at", type="datetime_immutable")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $lastUpdate; // override to remove the @Column definition

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="visitor_id", nullable=false)
     */
    private $visitor;

    /**
     * @var integer
     * @ORM\Column(name="visited_id", type="integer", nullable=false)
     */
    private $visitedId;

    /**
     * @var string
     * @ORM\Column(name="visited_class", type="string", nullable=false)
     */
    private $visitedClass;


    public function __construct(string $visitedClass, int $visitedId, User $visitor)
    {
        $this->visitor = $visitor;
        $this->visitedId = $visitedId;
        $this->visitedClass = $visitedClass;
    }


    public function getVisitor() : User
    {
        return $this->visitor;
    }


    public function setVisitor(User $visitor = null)
    {
        $this->visitor = $visitor;

        return $this;
    }


    public function getVisitedId()
    {
        return $this->visitedId;
    }


    public function setVisited(?int $visitedId)
    {
        $this->visitedId = $visitedId;

        return $this;
    }


    public function getVisitedClass()
    {
        return $this->visitedClass;
    }


    public function setVisitedClass(?string $visitedClass)
    {
        $this->visitedClass = $visitedClass;

        return $this;
    }
}
