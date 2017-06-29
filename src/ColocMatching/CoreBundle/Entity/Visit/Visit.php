<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Visit
 *
 * @ORM\Entity(repositoryClass="ColocMatching\CoreBundle\Repository\Visit\VisitRepository")
 * @ORM\Table(name="visit")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Visit")
 */
class Visit implements EntityInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="Visit id", readOnly=true)
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @JMS\Expose()
     * @SWG\Property(description="The visitor", readOnly=true)
     */
    protected $visitor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="visited_at", type="datetime")
     */
    protected $visitedAt;


    public function __construct(User $visitor) {
        $this->visitor = $visitor;
        $this->vistedAt = new \DateTime();
    }


    public function setId(int $id) {
        $this->id = $id;
    }


    public function getId(): int {
        return $this->id;
    }


    /**
     * @return User
     */
    public function getVisitor(): User {
        return $this->visitor;
    }


    public function setVisitor(User $visitor = null) {
        $this->visitor = $visitor;
        return $this;
    }


    public function getVisitedAt() {
        return $this->visitedAt;
    }


    public function setVisitedAt(\DateTime $visitedAt = null) {
        $this->visitedAt = $visitedAt;
        return $this;
    }

}