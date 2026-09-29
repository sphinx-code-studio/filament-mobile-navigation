<?php

declare(strict_types=1);

use SphinxCodeStudio\FilamentMobileNavigation\Enums\MenuPosition;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigationItem;
use SphinxCodeStudio\FilamentMobileNavigation\Support\MenuLayout;

function layoutItems(int $count): array
{
    return array_map(
        static fn (int $number): ResolvedNavigationItem => new ResolvedNavigationItem(
            key: "item-{$number}",
            label: "Item {$number}",
            url: "/item-{$number}",
            icon: 'heroicon-o-home',
            badge: null,
            badgeColor: null,
            active: false,
            openInNewTab: false,
        ),
        range(1, $count),
    );
}

function layoutKeys(array $entries): array
{
    return array_map(
        static fn (ResolvedNavigationItem|string $entry): string => is_string($entry) ? $entry : $entry->key,
        $entries,
    );
}

it('places the menu at the start', function (): void {
    expect(layoutKeys(MenuLayout::arrange(layoutItems(4), true, MenuPosition::Start)))
        ->toBe(['menu', 'item-1', 'item-2', 'item-3', 'item-4']);
});

it('places the menu in the exact center for an even normal item count', function (): void {
    expect(layoutKeys(MenuLayout::arrange(layoutItems(4), true, MenuPosition::Center)))
        ->toBe(['item-1', 'item-2', 'menu', 'item-3', 'item-4']);
});

it('uses the right-hand center slot for an odd normal item count', function (): void {
    expect(layoutKeys(MenuLayout::arrange(layoutItems(3), true, MenuPosition::Center)))
        ->toBe(['item-1', 'item-2', 'menu', 'item-3']);
});

it('places the menu at the end by default', function (): void {
    expect(layoutKeys(MenuLayout::arrange(layoutItems(4), true, MenuPosition::End)))
        ->toBe(['item-1', 'item-2', 'item-3', 'item-4', 'menu']);
});

it('does not add a menu entry when there are no remaining destinations', function (): void {
    expect(layoutKeys(MenuLayout::arrange(layoutItems(1), false, MenuPosition::End)))
        ->toBe(['item-1']);
});
