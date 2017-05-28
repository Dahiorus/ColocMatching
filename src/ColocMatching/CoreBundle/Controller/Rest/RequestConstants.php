<?php

namespace ColocMatching\CoreBundle\Controller\Rest;

use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

interface RequestConstants {

    const DEFAULT_PAGE = 1;

    const DEFAULT_LIMIT = 20;

    const DEFAULT_ORDER = PageableFilter::ORDER_ASC;

    const DEFAULT_SORT = "id";

}