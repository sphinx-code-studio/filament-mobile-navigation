<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Contracts;

use BackedEnum;

interface HasMobileNavigationIcon
{
    public static function getMobileNavigationIcon(): string|BackedEnum|null;
}
