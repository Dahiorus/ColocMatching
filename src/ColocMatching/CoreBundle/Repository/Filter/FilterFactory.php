<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

/**
 * Service for creating filter class instances
 *
 * @author Dahiorus
 */
class FilterFactory {


    public function createUserFilter(int $page, int $limit, string $order, string $sort): UserFilter {
        $filter = new UserFilter();
        $filter->setPage($page)->setSize($limit)->setOrder($order)->setSort($sort);

        return $filter;
    }


    public function createAnnouncementFilter(int $page, int $limit, string $order, string $sort): AnnouncementFilter {
        $filter = new AnnouncementFilter();
        $filter->setPage($page)->setSize($limit)->setOrder($order)->setSort($sort);

        return $filter;
    }

}