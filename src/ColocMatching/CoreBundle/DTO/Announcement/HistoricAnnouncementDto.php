<?php

namespace ColocMatching\CoreBundle\DTO\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="HistoricAnnouncement",
 *   allOf={
 *     { "$ref"="#/definitions/AbstractAnnouncement" }
 * })
 * @Hateoas\Relation(
 *   name="self",
 *   href= @Hateoas\Route(name="rest_get_historic_announcement", absolute=true,
 *     parameters={ "id" = "expr(object.getId())" })
 * )
 *
 * @author Dahiorus
 */
class HistoricAnnouncementDto extends AbstractAnnouncementDto
{
    /**
     * Announcement creation date time
     * @var \DateTime
     * @Serializer\Expose
     * @Serializer\SerializedName("creationDate")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @SWG\Property(readOnly=true)
     */
    private $creationDate;


    public function __toString() : string
    {
        $creationDate = empty($this->creationDate) ? null : $this->creationDate->format(DATE_ISO8601);

        return parent::__toString() . "[creationDate = " . $creationDate . "]";
    }


    public function getCreationDate()
    {
        return $this->creationDate;
    }


    public function setCreationDate(\DateTime $creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }


    public function getEntityClass() : string
    {
        return HistoricAnnouncement::class;
    }
}