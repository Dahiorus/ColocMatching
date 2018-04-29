<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * Invitation query filter class
 *
 * @SWG\Definition(definition="InvitationFilter")
 *
 * @author Dahiorus
 */
class InvitationFilter implements Searchable
{
    /**
     * @var string
     *
     * @SWG\Property(description="The source FQCN")
     */
    private $invitableClass;

    /**
     * @var integer
     *
     * @SWG\Property(description="The source (announcement or group) identifier")
     */
    private $invitableId;

    /**
     * @var integer
     *
     * @SWG\Property(description="The recipient identifier")
     */
    private $recipientId;

    /**
     * @var boolean
     *
     * @SWG\Property(description="Invitations with message only")
     */
    private $hasMessage = false;

    /**
     * @var array
     *
     * @SWG\Property(description="Source types", @SWG\Items(type="string"))
     */
    private $sourceTypes = array ();

    /**
     * @var string
     *
     * @SWG\Property(description="The invitation status")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Created at 'since' filter", format="date-time")
     */
    private $createdAtSince;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Created at 'until' filter", format="date-time")
     */
    private $createdAtUntil;


    public function getInvitableClass()
    {
        return $this->invitableClass;
    }


    public function setInvitableClass(?string $invitableClass)
    {
        $this->invitableClass = $invitableClass;

        return $this;
    }


    public function getInvitableId()
    {
        return $this->invitableId;
    }


    public function setInvitableId(?int $invitableId)
    {
        $this->invitableId = $invitableId;

        return $this;
    }


    public function getRecipientId()
    {
        return $this->recipientId;
    }


    public function setRecipientId(?int $recipientId)
    {
        $this->recipientId = $recipientId;

        return $this;
    }


    public function hasMessage()
    {
        return $this->hasMessage;
    }


    public function setHasMessage(?bool $hasMessage)
    {
        $this->hasMessage = $hasMessage;

        return $this;
    }


    public function getSourceTypes()
    {
        return $this->sourceTypes;
    }


    public function setSourceTypes(array $sourceTypes = null)
    {
        $this->sourceTypes = $sourceTypes;

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


    public function getCreatedAtSince()
    {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null)
    {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    public function getCreatedAtUntil()
    {
        return $this->createdAtUntil;
    }


    public function setCreatedAtUntil(\DateTime $createdAtUntil = null)
    {
        $this->createdAtUntil = $createdAtUntil;

        return $this;
    }


    public function buildCriteria() : Criteria
    {
        $criteria = Criteria::create();

        if (!empty($this->invitableClass))
        {
            $criteria->andWhere(Criteria::expr()->eq("invitableClass", $this->invitableClass));

            if (!empty($this->invitableId))
            {
                $criteria->andWhere(Criteria::expr()->eq("invitableId", $this->invitableId));
            }
        }

        if ($this->hasMessage)
        {
            $criteria->andWhere(Criteria::expr()->neq("message", null));
        }

        if (!empty($this->status))
        {
            $criteria->andWhere(Criteria::expr()->eq("status", $this->status));
        }

        if (!empty($this->sourceTypes))
        {
            $criteria->andWhere(Criteria::expr()->in("sourceType", $this->sourceTypes));
        }

        if (!empty($this->createdAtSince))
        {
            $criteria->andWhere(Criteria::expr()->gte("createdAt", $this->createdAtSince));
        }

        if (!empty($this->createdAtUntil))
        {
            $criteria->andWhere(Criteria::expr()->lte("createdAt", $this->createdAtUntil));
        }

        return $criteria;
    }
}