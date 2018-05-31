<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Repository\User\ExternalIdentityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A user external identity provider
 *
 * @author Dahiorus
 *
 * @ORM\Entity(repositoryClass=ExternalIdentityRepository::class)
 * @ORM\Table(
 *   name="user_external_identity",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_USER_EXTERNAL_IDENTITY", columns={ "user_id", "provider_name", "external_id" })
 * })
 */
class ExternalIdentity extends AbstractEntity
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="provider_name", type="string")
     */
    private $providerName;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string")
     */
    private $externalId;


    public function __construct(User $user, string $providerName, string $externalId)
    {
        $this->user = $user;
        $this->providerName = $providerName;
        $this->externalId = $externalId;
    }


    public function __toString()
    {
        return parent::__toString() . "[providerName = " . $this->providerName . ", externalId = " . $this->externalId
            . "]";
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


    public function getProviderName()
    {
        return $this->providerName;
    }


    public function setProviderName(?string $providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }


    public function getExternalId()
    {
        return $this->externalId;
    }


    public function setExternalId(?string $externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

}
