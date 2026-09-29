<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Navigation;

final readonly class ResolvedNavigation
{
    /**
     * @param  array<ResolvedNavigationItem>  $bottomItems
     * @param  array<ResolvedNavigationGroup>  $groups
     */
    public function __construct(
        public array $bottomItems,
        public array $groups,
        public bool $hasMore,
        public bool $moreActive,
    ) {}
}
