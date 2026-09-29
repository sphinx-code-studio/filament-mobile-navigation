<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

final class MobileNavigationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-mobile-navigation');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-mobile-navigation');

        FilamentAsset::register([
            Css::make('mobile-navigation', __DIR__.'/../resources/css/mobile-navigation.css'),
        ], package: 'sphinx-code-studio/filament-mobile-navigation');
    }
}
