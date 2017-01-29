<?php

namespace ColocMatching\CoreBundle\Controller\Rest;

use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;

interface RequestConstants {

    const DEFAULT_PAGE = 1;

    const DEFAULT_LIMIT = 20;

    const DEFAULT_ORDER = AbstractFilter::ORDER_ASC;

    const DEFAULT_SORT = "id";

}