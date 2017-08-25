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
class InvitationFilter extends PageableFilter implements Searchable {

    /**
     * @var integer
     *
     * @SWG\Property(description="The identifier of the source (announcement or group)")
     */
    private $invitableId;

    /**
     * @var integer
     *
     * @SWG\Property(description="The identifier of the recipient")
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
     * @SWG\Property(description="The status of the invitation")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Created at 'since' filter", format="datetime")
     */
    private $createdAtSince;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="Created at 'until' filter", format="datetime")
     */
    private $createdAtUntil;


    public function getInvitableId() {
        return $this->invitableId;
    }


    public function setInvitableId(?int $invitableId) {
        $this->invitableId = $invitableId;
    }


    public function getRecipientId() {
        return $this->recipientId;
    }


    public function setRecipientId(?int $recipientId) {
        $this->recipientId = $recipientId;
    }


    public function hasMessage() {
        return $this->hasMessage;
    }


    public function setHasMessage(?bool $hasMessage) {
        $this->hasMessage = $hasMessage;
    }


    public function getSourceTypes() {
        return $this->sourceTypes;
    }


    public function setSourceTypes(array $sourceTypes = null) {
        $this->sourceTypes = $sourceTypes;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus(?string $status) {
        $this->status = $status;
    }


    public function getCreatedAtSince() {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null) {
        $this->createdAtSince = $createdAtSince;
    }


    public function getCreatedAtUntil() {
        return $this->createdAtUntil;
    }


    public function setCreatedAtUntil(\DateTime $createdAtUntil = null) {
        $this->createdAtUntil = $createdAtUntil;
    }


    public function buildCriteria() : Criteria {
        $criteria = Criteria::create();

        if ($this->hasMessage) {
            $criteria->andWhere(Criteria::expr()->neq("message", null));
        }

        if (!empty($this->status)) {
            $criteria->andWhere(Criteria::expr()->eq("status", $this->status));
        }

        if (!empty($this->sourceTypes)) {
            $criteria->andWhere(Criteria::expr()->in("sourceType", $this->sourceTypes));
        }

        if (!empty($this->createdAtSince)) {
            $criteria->andWhere(Criteria::expr()->gte("createdAt", $this->createdAtSince));
        }

        if (!empty($this->createdAtUntil)) {
            $criteria->andWhere(Criteria::expr()->lte("createdAt", $this->createdAtUntil));
        }

        return $criteria;
    }
}