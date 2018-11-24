<?php

namespace App\Rest\Event;

/**
 * Class regrouping all events in the module "Rest"
 *
 * @author Dahiorus
 */
class Events
{
    const DELETE_ANNOUNCEMENT_EVENT = "coloc_matching.announcement.deleted";

    const USER_REGISTERED_EVENT = "coloc_matching.user.registered";

    const ENTITY_VISITED_EVENT = "coloc_matching.entity_visited";

    const INVITATION_CREATED_EVENT = "coloc_matching.invitation_created";

}
