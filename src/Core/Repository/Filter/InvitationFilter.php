<?php

namespace App\Core\Repository\Filter;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation as Serializer;

/**
 * Invitation query filter class
 *
 * @author Dahiorus
 */
class InvitationFilter implements Searchable
{
    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $invitableClass;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $invitableId;

    /**
     * @var integer
     * @Serializer\Type("int")
     */
    private $recipientId;

    /**
     * @var boolean
     * @Serializer\Type("bool")
     */
    private $hasMessage = false;

    /**
     * @var string[]
     * @Serializer\Type("array<string>")
     */
    private $sourceTypes = array ();

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $status;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     */
    private $createdAtSince;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
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


    public function setCreatedAtSince(DateTime $createdAtSince = null)
    {
        $this->createdAtSince = $createdAtSince;

        return $this;
    }


    public function getCreatedAtUntil()
    {
        return $this->createdAtUntil;
    }


    public function setCreatedAtUntil(DateTime $createdAtUntil = null)
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
