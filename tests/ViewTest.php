<?php

declare(strict_types=1);

use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigationGroup;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigationItem;

it('renders accessible dark-mode-compatible markup', function (): void {
    $item = new ResolvedNavigationItem(
        key: 'dashboard',
        label: 'Dashboard',
        url: '/admin',
        icon: 'heroicon-o-home',
        badge: null,
        badgeColor: null,
        active: true,
        openInNewTab: false,
    );

    $html = view('filament-mobile-navigation::navigation', [
        'entries' => [$item, 'menu'],
        'groups' => [new ResolvedNavigationGroup('Main', null, [$item])],
        'hasMore' => true,
        'moreActive' => false,
        'hideOriginalMobileNavigationTrigger' => true,
    ])->render();

    expect($html)->toContain('<nav')
        ->toContain('aria-current="page"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('x-on:keydown.escape.window')
        ->toContain('fmn-hides-original-trigger');
});
