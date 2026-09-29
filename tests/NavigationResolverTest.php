<?php

declare(strict_types=1);

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\NavigationResolver;
use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigation;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\ClientResource;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\Dashboard;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\HiddenPage;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\LeadResource;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\MobileIconPage;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\TaskResource;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\UnauthorizedResource;
use SphinxCodeStudio\FilamentMobileNavigation\Tests\Fixtures\UnregisteredResource;

function navItem(string $key, string $label, ?string $icon = 'heroicon-o-home'): NavigationItem
{
    return NavigationItem::make($label)
        ->key($key)
        ->icon($icon)
        ->url('/'.strtolower(str_replace(' ', '-', $label)));
}

function resolveNavigation(array $groups, ?array $configured = null, int $maxItems = 4): ResolvedNavigation
{
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getNavigation')->once()->andReturn($groups);

    return app(NavigationResolver::class)->resolve($panel, $configured, $maxItems);
}

it('returns an empty result when Filament has no navigation', function (): void {
    $navigation = resolveNavigation([]);

    expect($navigation->bottomItems)->toBe([])
        ->and($navigation->groups)->toBe([])
        ->and($navigation->hasMore)->toBeFalse()
        ->and($navigation->moreActive)->toBeFalse();
});

it('resolves resources pages and dashboard from Filaments generated keys', function (): void {
    $navigation = resolveNavigation([
        NavigationGroup::make()->items([
            navItem(Dashboard::class, 'Dashboard'),
            navItem(LeadResource::class, 'Leads'),
            navItem(TaskResource::class, 'Tasks'),
        ]),
    ], [TaskResource::class, Dashboard::class]);

    expect(array_column($navigation->bottomItems, 'label'))->toBe(['Tasks', 'Dashboard']);
});

it('deduplicates explicit classes and respects max items', function (): void {
    $navigation = resolveNavigation([
        NavigationGroup::make()->items([
            navItem(Dashboard::class, 'Dashboard'),
            navItem(LeadResource::class, 'Leads'),
            navItem(TaskResource::class, 'Tasks'),
        ]),
    ], [LeadResource::class, LeadResource::class, TaskResource::class, Dashboard::class], 2);

    expect(array_column($navigation->bottomItems, 'label'))->toBe(['Leads', 'Tasks'])
        ->and($navigation->hasMore)->toBeTrue();
});

it('chooses visible top-level items automatically in Filament order', function (): void {
    $navigation = resolveNavigation([
        NavigationGroup::make('Work')->items([
            navItem(LeadResource::class, 'Leads'),
            navItem(TaskResource::class, 'Tasks'),
            navItem(ClientResource::class, 'Clients'),
        ]),
    ], null, 2);

    expect(array_column($navigation->bottomItems, 'label'))->toBe(['Leads', 'Tasks'])
        ->and($navigation->hasMore)->toBeTrue();
});

it('omits hidden unauthorized and unregistered configured destinations', function (): void {
    $hidden = navItem(HiddenPage::class, 'Hidden')->hidden();
    $navigation = resolveNavigation([
        NavigationGroup::make()->items([
            navItem(Dashboard::class, 'Dashboard'),
            $hidden,
            // UnauthorizedResource and UnregisteredResource are absent because Filament did not generate them.
        ]),
    ], [
        Dashboard::class,
        HiddenPage::class,
        UnauthorizedResource::class,
        UnregisteredResource::class,
    ]);

    expect(array_column($navigation->bottomItems, 'label'))->toBe(['Dashboard'])
        ->and($navigation->hasMore)->toBeFalse();
});

it('inherits labels badges colors active state and child relationships', function (): void {
    $child = navItem(TaskResource::class, 'Tasks')->isActiveWhen(fn (): bool => true);
    $parent = navItem(LeadResource::class, 'Leads')
        ->badge('12', 'danger')
        ->childItems([$child]);
    $navigation = resolveNavigation([
        NavigationGroup::make('CRM')->items([$parent]),
    ], [LeadResource::class]);

    $item = $navigation->bottomItems[0];
    expect($item->label)->toBe('Leads')
        ->and($item->badge)->toBe('12')
        ->and($item->badgeColor)->toBe('danger')
        ->and($item->active)->toBeTrue()
        ->and($item->children)->toHaveCount(1)
        ->and($navigation->groups[0]->label)->toBe('CRM');
});

it('uses mobile icon then navigation icon then fallback icon', function (): void {
    $navigation = resolveNavigation([
        NavigationGroup::make()->items([
            navItem(MobileIconPage::class, 'Mobile', 'heroicon-o-home'),
            navItem(LeadResource::class, 'Leads', 'heroicon-o-users'),
            navItem(TaskResource::class, 'Tasks', null),
        ]),
    ]);

    expect($navigation->bottomItems[0]->icon)->toBe('heroicon-o-device-phone-mobile')
        ->and($navigation->bottomItems[1]->icon)->toBe('heroicon-o-users')
        ->and($navigation->bottomItems[2]->icon)->toBe(NavigationResolver::FALLBACK_ICON);
});

it('keeps the complete resolved tree in the more menu', function (): void {
    $navigation = resolveNavigation([
        NavigationGroup::make('CRM')->items([
            navItem(Dashboard::class, 'Dashboard'),
            navItem(LeadResource::class, 'Leads'),
        ]),
    ], [Dashboard::class], 1);

    expect($navigation->hasMore)->toBeTrue()
        ->and($navigation->groups)->toHaveCount(1)
        ->and($navigation->groups[0]->items)->toHaveCount(2);
});

it('marks more active when the current destination is not on the bar', function (): void {
    $active = navItem(LeadResource::class, 'Leads')->isActiveWhen(fn (): bool => true);
    $navigation = resolveNavigation([
        NavigationGroup::make()->items([
            navItem(Dashboard::class, 'Dashboard'),
            $active,
        ]),
    ], [Dashboard::class], 1);

    expect($navigation->hasMore)->toBeTrue()
        ->and($navigation->moreActive)->toBeTrue();
});
