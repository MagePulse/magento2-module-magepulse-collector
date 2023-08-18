<?php

declare(strict_types=1);

namespace MagePulse\Collector\Model\Collectors;

interface CollectorInterface
{
    /**
     * Return the data for the report module class
     * @return array
     */
    public function getData():array ;
}
