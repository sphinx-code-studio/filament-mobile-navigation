<?php

declare(strict_types=1);

namespace SphinxCodeStudio\FilamentMobileNavigation\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use SphinxCodeStudio\FilamentMobileNavigation\MobileNavigationServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @return array<class-string> */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            MobileNavigationServiceProvider::class,
        ];
    }
}
