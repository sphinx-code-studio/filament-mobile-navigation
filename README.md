# Filament Mobile Navigation

A Filament-native bottom navigation bar for mobile panels. Choose the Resources and Pages; the package reuses the navigation Filament already resolved for the current panel and user, including labels, URLs, icons, badges, active state, visibility, authorization, localization, groups, and child items.

> Screenshot/demo placeholder: light mode, dark mode, and the More sheet.

## Requirements

- PHP 8.2+
- Laravel 11.28, 12, or 13
- Filament 4 or 5

Both Filament majors are exercised independently in CI. The implementation uses their shared public `Panel::getNavigation()` and `NavigationItem` APIs.

## Installation

```bash
composer require sphinx-code-studio/filament-mobile-navigation
```

No publishing, theme generation, npm install, or application asset build is required.

## Basic usage

Register the plugin on a panel:

```php
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Tasks\TaskResource;
use SphinxCodeStudio\FilamentMobileNavigation\MobileNavigationPlugin;

return $panel
    // Other panel configuration...
    ->plugins([
        MobileNavigationPlugin::make()
            ->items([
                Dashboard::class,
                LeadResource::class,
                TaskResource::class,
                ClientResource::class,
            ]),
    ]);
```

The classes must already belong to that panel and have a visible, accessible Filament navigation item. Missing, hidden, unauthorized, invalid, duplicate, and other-panel-only classes are ignored safely.

## Automatic mode

`items()` is optional:

```php
MobileNavigationPlugin::make()
```

Automatic mode takes the first four visible top-level destinations from Filament's resolved navigation order. Parent-only entries without a URL are not placed on the bottom bar, but remain in More.

Change the limit with:

```php
MobileNavigationPlugin::make()->maxItems(3)
```

`maxItems()` defaults to `4` and caps normal destinations in both modes. With explicit items, the first N classes that resolve successfully are used in the exact supplied order. More does not count toward this limit.

## Menu positions

```php
use SphinxCodeStudio\FilamentMobileNavigation\Enums\MenuPosition;

MobileNavigationPlugin::make()
    ->items([Dashboard::class, LeadResource::class, TaskResource::class, ClientResource::class])
    ->menuPosition(MenuPosition::Center);
```

Available positions are `Start`, `Center`, and `End` (the default). Start and end are logical positions, so RTL layouts remain correct without reversing the developer's semantic item order.

Center inserts More after `ceil(normal item count / 2)`. Four normal items place More in the exact middle; three normal items place it in the right-hand center slot. This is deterministic for even and odd counts.

## Icons

Icon priority is:

1. Optional mobile-specific icon
2. Filament's active/navigation icon
3. `Heroicon::OutlinedSquare2Stack`

Normal Filament configuration works unchanged:

```php
protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;
```

For a mobile-only override, implement the optional contract on a Resource or Page:

```php
use BackedEnum;
use SphinxCodeStudio\FilamentMobileNavigation\Contracts\HasMobileNavigationIcon;

final class LeadResource extends Resource implements HasMobileNavigationIcon
{
    public static function getMobileNavigationIcon(): string | BackedEnum | null
    {
        return Heroicon::OutlinedDevicePhoneMobile;
    }
}
```

## More navigation

More opens a lightweight accessible bottom sheet built from the complete navigation tree Filament resolved for the request. It retains groups, ordering, icons, badges, active states, children, permissions, and visibility.

The full tree intentionally includes destinations already on the bottom bar. This preserves group context and makes More a predictable complete navigation index. The button disappears when no other URL destination exists beyond the bottom items.

The sheet traps focus, restores focus on close, closes on Escape or backdrop click, uses semantic dialog markup, and marks active links with `aria-current="page"`.

## Permissions and visibility

The package does not call Resource or Page metadata methods to invent navigation. It selects from `Panel::getNavigation()`, after Filament has applied panel registration, `shouldRegisterNavigation()`, authorization, visibility, tenant context, custom navigation builders, and translations. Therefore inaccessible classes do not leak into the mobile UI.

## Multiple panels

Each `MobileNavigationPlugin` object owns its configuration and is captured by that panel's render hook. Configure panels independently:

```php
// Admin
MobileNavigationPlugin::make()->items([Dashboard::class, UserResource::class]);

// CRM
MobileNavigationPlugin::make()->items([
    Dashboard::class,
    LeadResource::class,
    ClientResource::class,
    TaskResource::class,
]);
```

There is no global mutable plugin configuration.

## Colors, dark mode, responsive behavior, and RTL

The stylesheet uses Filament's `--primary-*` and `--gray-*` design tokens, the panel's `.dark` state, and logical CSS properties. The bar is hidden at Filament's `lg` breakpoint (`1024px`) and desktop navigation is unchanged. Mobile page content receives bottom padding for the bar and `env(safe-area-inset-bottom)`.

By default, the original Filament mobile sidebar trigger is hidden only while this package renders a usable More button. Account and other topbar controls are untouched. Disable this behavior if desired:

```php
MobileNavigationPlugin::make()
    ->hideOriginalMobileNavigationTrigger(false);
```

This is the only intentional dependency on Filament's CSS hooks: `.fi-topbar-open-sidebar-btn` and `.fi-layout-sidebar-toggle-btn-ctn`. Filament does not currently expose a panel API to remove only the mobile sidebar trigger. All navigation data, render-hook registration, icons, and assets use public APIs.

The breakpoint is intentionally internal in 1.x; the CSS is structured around `--fmn-*` custom properties so a future public breakpoint option can be introduced without changing navigation resolution.

## Filament compatibility

| Filament | Laravel | PHP | Status |
|---|---|---|---|
| 4.x | 11.28 / 12.x | 8.2+ | CI tested |
| 5.x | 12.x / 13.x | 8.2+ | CI tested |

Compatibility is based on public APIs shared by both majors. See [Filament navigation](https://filamentphp.com/docs/5.x/navigation/overview), [render hooks](https://filamentphp.com/docs/5.x/advanced/render-hooks), and [assets](https://filamentphp.com/docs/5.x/advanced/assets).

## How this differs from other bottom-navigation packages

The primary API accepts Resource/Page classes, not duplicated mobile item metadata. The resolver matches those classes against Filament's generated navigation keys. Automatic mode is available, but explicit mode keeps the developer-selected semantic order while inheriting Filament's runtime decisions.

The package does not publish or replace Filament views, require a custom theme, or depend on an application build pipeline.

## Testing

```bash
composer check
```

This runs strict Composer validation, Pint, Larastan, and Pest. The CI dependency matrix validates Filament 4 and 5 independently.

## Contributing

Issues and pull requests are welcome. Add tests for behavior changes, run `composer check`, and avoid dependencies on Filament's internal DOM or unpublished views.

## License

Filament Mobile Navigation is open-source software licensed under the [MIT license](LICENSE).
