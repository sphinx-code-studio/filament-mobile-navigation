<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Navigation;

use BackedEnum;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use SphinxCodeStudio\FilamentMobileNavigation\Contracts\HasMobileNavigationIcon;
use Throwable;

final class NavigationResolver
{
    public const FALLBACK_ICON = Heroicon::OutlinedSquare2Stack;

    /**
     * @param  array<class-string>|null  $configuredClasses
     */
    public function resolve(Panel $panel, ?array $configuredClasses, int $maxItems): ResolvedNavigation
    {
        $groups = $this->resolveGroups($panel->getNavigation());
        $allItems = $this->flatten($groups);
        $bottomItems = $configuredClasses === null
            ? $this->resolveAutomaticItems($groups, $maxItems)
            : $this->resolveConfiguredItems($allItems, $configuredClasses, $maxItems);

        $bottomKeys = array_fill_keys(array_map(
            static fn (ResolvedNavigationItem $item): string => $item->key,
            $bottomItems,
        ), true);

        $hasMore = false;
        $moreActive = false;
        foreach ($allItems as $item) {
            if (($item->url !== null) && ! isset($bottomKeys[$item->key])) {
                $hasMore = true;
                $moreActive = $moreActive || $item->active;
            }
        }

        return new ResolvedNavigation($bottomItems, $groups, $hasMore, $moreActive);
    }

    /**
     * @param  array<NavigationGroup>  $groups
     * @return array<ResolvedNavigationGroup>
     */
    private function resolveGroups(array $groups): array
    {
        $resolved = [];

        foreach ($groups as $group) {
            $items = $this->resolveItems($group->getItems());

            if ($items === []) {
                continue;
            }

            $resolved[] = new ResolvedNavigationGroup(
                label: $group->getLabel() ?: null,
                icon: $group->getIcon(),
                items: $items,
            );
        }

        return $resolved;
    }

    /**
     * @param  array<NavigationItem>|Arrayable<int, NavigationItem>  $items
     * @return array<ResolvedNavigationItem>
     */
    private function resolveItems(array|Arrayable $items): array
    {
        $resolved = [];

        if ($items instanceof Arrayable) {
            $items = $items->toArray();
        }

        foreach ($items as $item) {
            if (! $item instanceof NavigationItem) {
                continue;
            }

            if (! $item->isVisible()) {
                continue;
            }

            $children = $this->resolveItems($item->getChildItems());
            $url = $item->getUrl();

            if (($url === null) && ($children === [])) {
                continue;
            }

            $active = $item->isActive() || $this->childrenAreActive($children);
            $icon = $this->resolveIcon($item, $active);

            $resolved[] = new ResolvedNavigationItem(
                key: $item->getKey(),
                label: $item->getLabel(),
                url: $url,
                icon: $icon,
                badge: $item->getBadge(),
                badgeColor: $item->getBadgeColor(),
                active: $active,
                openInNewTab: $item->shouldOpenUrlInNewTab(),
                children: $children,
            );
        }

        return $resolved;
    }

    /** @param array<ResolvedNavigationItem> $children */
    private function childrenAreActive(array $children): bool
    {
        foreach ($children as $child) {
            if ($child->active) {
                return true;
            }
        }

        return false;
    }

    private function resolveIcon(NavigationItem $item, bool $active): string|BackedEnum|Htmlable
    {
        $key = $item->getKey();

        if (class_exists($key) && is_subclass_of($key, HasMobileNavigationIcon::class)) {
            try {
                $mobileIcon = $key::getMobileNavigationIcon();

                if ($mobileIcon !== null) {
                    return $mobileIcon;
                }
            } catch (Throwable) {
                // A user-defined optional override must never break panel navigation.
            }
        }

        return ($active ? $item->getActiveIcon() : null)
            ?? $item->getIcon()
            ?? self::FALLBACK_ICON;
    }

    /**
     * @param  array<ResolvedNavigationGroup>  $groups
     * @return array<ResolvedNavigationItem>
     */
    private function flatten(array $groups): array
    {
        $items = [];

        foreach ($groups as $group) {
            $this->appendItems($items, $group->items);
        }

        return $items;
    }

    /**
     * @param  array<ResolvedNavigationItem>  $target
     * @param  array<ResolvedNavigationItem>  $items
     */
    private function appendItems(array &$target, array $items): void
    {
        foreach ($items as $item) {
            $target[] = $item;
            $this->appendItems($target, $item->children);
        }
    }

    /**
     * @param  array<ResolvedNavigationItem>  $allItems
     * @param  array<class-string>  $configuredClasses
     * @return array<ResolvedNavigationItem>
     */
    private function resolveConfiguredItems(array $allItems, array $configuredClasses, int $maxItems): array
    {
        $byKey = [];
        foreach ($allItems as $item) {
            $byKey[$item->key] ??= $item;
        }

        $resolved = [];
        $seen = [];

        foreach ($configuredClasses as $class) {
            if (isset($seen[$class]) || ! isset($byKey[$class])) {
                continue;
            }

            $item = $byKey[$class];
            if ($item->url === null) {
                continue;
            }

            $seen[$class] = true;
            $resolved[] = $item;

            if (count($resolved) >= $maxItems) {
                break;
            }
        }

        return $resolved;
    }

    /**
     * @param  array<ResolvedNavigationGroup>  $groups
     * @return array<ResolvedNavigationItem>
     */
    private function resolveAutomaticItems(array $groups, int $maxItems): array
    {
        $resolved = [];

        foreach ($groups as $group) {
            foreach ($group->items as $item) {
                if ($item->url === null) {
                    continue;
                }

                $resolved[] = $item;

                if (count($resolved) >= $maxItems) {
                    return $resolved;
                }
            }
        }

        return $resolved;
    }
}
