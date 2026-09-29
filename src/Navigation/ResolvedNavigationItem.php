<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Navigation;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

final readonly class ResolvedNavigationItem
{
    /**
     * @param  string|array<string>|null  $badgeColor
     * @param  array<ResolvedNavigationItem>  $children
     */
    public function __construct(
        public string $key,
        public string $label,
        public ?string $url,
        public string|BackedEnum|Htmlable|null $icon,
        public ?string $badge,
        public string|array|null $badgeColor,
        public bool $active,
        public bool $openInNewTab,
        public array $children = [],
    ) {}
}
