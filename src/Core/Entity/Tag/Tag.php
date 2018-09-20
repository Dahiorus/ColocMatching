<?php

namespace App\Core\Entity\Tag;

use App\Core\Entity\EntityInterface;
use App\Core\Repository\Tag\TagRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a tag on an announcement, user, etc.
 *
 * @author Dahiorus
 *
 * @ORM\Entity(repositoryClass=TagRepository::class)
 * @ORM\Table(
 *   name="tag",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_TAG_VALUE", columns={ "value" })
 *   },
 *   indexes={
 *     @ORM\Index(name="IDX_TAG_VALUE", columns={ "value" })
 * })
 * @ORM\EntityListeners({
 *   "App\Core\Listener\UpdateListener",
 *   "App\Core\Listener\CacheDriverListener"
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="tags")
 */
class Tag implements EntityInterface
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", length=50, nullable=false)
     */
    private $value;


    public function __construct(string $value)
    {
        $this->value = $value;
    }


    public function __toString()
    {
        try
        {
            $reflectionClass = new \ReflectionClass($this);
            $className = $reflectionClass->getShortName();
        }
        catch (\ReflectionException $e)
        {
            $className = get_class($this);
        }

        return $className . "[id = " . $this->id . ", value = '" . $this->value . "'']";
    }


    public function getId()
    {
        return $this->id;
    }


    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }


    public function getValue()
    {
        return $this->value;
    }


    public function setValue(?string $value)
    {
        $this->value = $value;

        return $this;
    }

}
