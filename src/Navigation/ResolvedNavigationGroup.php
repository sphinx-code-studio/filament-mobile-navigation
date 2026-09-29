<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Navigation;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

final readonly class ResolvedNavigationGroup
{
    /** @param array<ResolvedNavigationItem> $items */
    public function __construct(
        public ?string $label,
        public string|BackedEnum|Htmlable|null $icon,
        public array $items,
    ) {}
}
