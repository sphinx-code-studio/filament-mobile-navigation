<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures;

use BackedEnum;
use SphinxCodeStudio\FilamentMobileNavigation\Contracts\HasMobileNavigationIcon;

final class Dashboard {}

final class LeadResource {}

final class TaskResource {}

final class ClientResource {}

final class HiddenPage {}

final class UnauthorizedResource {}

final class UnregisteredResource {}

final class MobileIconPage implements HasMobileNavigationIcon
{
    public static function getMobileNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-device-phone-mobile';
    }
}
