<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Support;

use SphinxCodeStudio\FilamentMobileNavigation\Enums\MenuPosition;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigationItem;

final class MenuLayout
{
    /**
     * @param  array<ResolvedNavigationItem>  $items
     * @return array<ResolvedNavigationItem|string>
     */
    public static function arrange(array $items, bool $showMenu, MenuPosition $position): array
    {
        if (! $showMenu) {
            return $items;
        }

        $index = match ($position) {
            MenuPosition::Start => 0,
            MenuPosition::Center => (int) ceil(count($items) / 2),
            MenuPosition::End => count($items),
        };

        array_splice($items, $index, 0, ['menu']);

        return $items;
    }
}
