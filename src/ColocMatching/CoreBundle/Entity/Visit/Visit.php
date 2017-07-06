<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Visit
 *
 * @ORM\MappedSuperclass
 * @JMS\ExclusionPolicy("ALL")
 */
abstract class Visit implements EntityInterface {

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
     * @ORM\JoinColumn(name="visitor_id", nullable=false)
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


    protected function __construct(User $visitor) {
        $this->visitor = $visitor;
        $this->visitedAt = new \DateTime();
    }


    /**
     * Creates a new instance of visit
     *
     * @param Visitable $visited The visited class
     * @param User $visitor      The visitor
     *
     * @return Visit
     */
    public static function create(Visitable $visited, User $visitor) : Visit {
        if ($visited instanceof Announcement) {
            return new AnnouncementVisit($visited, $visitor);
        }
        else if ($visited instanceof Group) {
            return new GroupVisit($visited, $visitor);
        }
        else if ($visited instanceof User) {
            return new UserVisit($visited, $visitor);
        }

        return null;
    }


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;
    }


    public function getVisitor() : User {
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


    public abstract function getVisited() : Visitable;


    public abstract function setVisited(Visitable $visited = null);
}
