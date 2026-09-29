<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use SphinxCodeStudio\FilamentMobileNavigation\Enums\MenuPosition;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\NavigationResolver;
use SphinxCodeStudio\FilamentMobileNavigation\Support\MenuLayout;

final class MobileNavigationPlugin implements Plugin
{
    /** @var array<class-string>|null */
    private ?array $items = null;

    private MenuPosition $menuPosition = MenuPosition::End;

    private int $maxItems = 4;

    private bool $hideOriginalMobileNavigationTrigger = true;

    public static function make(): static
    {
        return new self;
    }

    public function getId(): string
    {
        return 'filament-mobile-navigation';
    }

    /** @param array<class-string> $items */
    public function items(array $items): static
    {
        $this->items = array_values($items);

        return $this;
    }

    public function menuPosition(MenuPosition $position): static
    {
        $this->menuPosition = $position;

        return $this;
    }

    public function maxItems(int $maxItems): static
    {
        if ($maxItems < 1) {
            throw new InvalidArgumentException('The mobile navigation must allow at least one normal item.');
        }

        $this->maxItems = $maxItems;

        return $this;
    }

    public function hideOriginalMobileNavigationTrigger(bool $condition = true): static
    {
        $this->hideOriginalMobileNavigationTrigger = $condition;

        return $this;
    }

    /** @return array<class-string>|null */
    public function getItems(): ?array
    {
        return $this->items;
    }

    public function getMenuPosition(): MenuPosition
    {
        return $this->menuPosition;
    }

    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public function hidesOriginalMobileNavigationTrigger(): bool
    {
        return $this->hideOriginalMobileNavigationTrigger;
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => $this->render($panel),
        );
    }

    public function boot(Panel $panel): void {}

    private function render(Panel $panel): string
    {
        if (! Filament::auth()->check()) {
            return '';
        }

        if ($panel->hasTenancy() && (Filament::getTenant() === null)) {
            return '';
        }

        $navigation = app(NavigationResolver::class)->resolve($panel, $this->items, $this->maxItems);

        if ($navigation->bottomItems === []) {
            return '';
        }

        /** @var View $view */
        $view = view()->file(__DIR__.'/../resources/views/navigation.blade.php', [
            'entries' => MenuLayout::arrange($navigation->bottomItems, $navigation->hasMore, $this->menuPosition),
            'groups' => $navigation->groups,
            'hasMore' => $navigation->hasMore,
            'moreActive' => $navigation->moreActive,
            'hideOriginalMobileNavigationTrigger' => $this->hideOriginalMobileNavigationTrigger && $navigation->hasMore,
        ]);

        return $view->render();
    }
}
