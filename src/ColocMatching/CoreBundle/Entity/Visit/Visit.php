<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Visit
 *
 * @ORM\MappedSuperclass(repositoryClass="ColocMatching\CoreBundle\Repository\Visit\VisitRepository")
 *
 * @author Dahiorus
 */
abstract class Visit extends AbstractEntity
{
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="ColocMatching\CoreBundle\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="visitor_id", nullable=false)
     */
    protected $visitor;

    /**
     * @var Visitable
     */
    protected $visited;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(name="visited_at", type="datetime_immutable")
     */
    protected $createdAt;


    protected function __construct(Visitable $visited, User $visitor)
    {
        $this->visitor = $visitor;
        $this->visited = $visited;
    }


    /**
     * Creates a new instance of visit
     *
     * @param Visitable $visited The visited class
     * @param User $visitor The visitor
     *
     * @return Visit
     */
    public static function create(Visitable $visited, User $visitor) : Visit
    {
        if ($visited instanceof Announcement)
        {
            return new AnnouncementVisit($visited, $visitor);
        }
        else if ($visited instanceof Group)
        {
            return new GroupVisit($visited, $visitor);
        }
        else if ($visited instanceof User)
        {
            return new UserVisit($visited, $visitor);
        }

        throw new \InvalidArgumentException("'" . get_class($visited) . "' not supported");
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


    public function getVisited()
    {
        return $this->visited;
    }


    public function setVisited(Visitable $visited = null)
    {
        $this->visited = $visited;

        return $this;
    }
}
