<?php

declare(strict_types=1);

use Filament\Panel;
use SphinxCodeStudio\FilamentMobileNavigation\Enums\MenuPosition;
use SphinxCodeStudio\FilamentMobileNavigation\MobileNavigationPlugin;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\Dashboard;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\LeadResource;

it('has production defaults', function (): void {
    $plugin = MobileNavigationPlugin::make();

    expect($plugin->getId())->toBe('filament-mobile-navigation')
        ->and($plugin->getItems())->toBeNull()
        ->and($plugin->getMaxItems())->toBe(4)
        ->and($plugin->getMenuPosition())->toBe(MenuPosition::End)
        ->and($plugin->hidesOriginalMobileNavigationTrigger())->toBeTrue();
});

it('accepts explicit resource and page classes in order', function (): void {
    $plugin = MobileNavigationPlugin::make()->items([
        Dashboard::class,
        LeadResource::class,
    ]);

    expect($plugin->getItems())->toBe([
        Dashboard::class,
        LeadResource::class,
    ]);
});

it('registers independently with a panel', function (): void {
    $adminPlugin = MobileNavigationPlugin::make()->items([Dashboard::class]);
    $crmPlugin = MobileNavigationPlugin::make()->items([LeadResource::class]);
    $admin = Panel::make()->id('admin')->plugin($adminPlugin);
    $crm = Panel::make()->id('crm')->plugin($crmPlugin);

    expect($admin->getPlugin('filament-mobile-navigation'))->toBe($adminPlugin)
        ->and($crm->getPlugin('filament-mobile-navigation'))->toBe($crmPlugin)
        ->and($adminPlugin->getItems())->toBe([Dashboard::class])
        ->and($crmPlugin->getItems())->toBe([LeadResource::class]);
});

it('validates max items', function (): void {
    MobileNavigationPlugin::make()->maxItems(0);
})->throws(InvalidArgumentException::class);
