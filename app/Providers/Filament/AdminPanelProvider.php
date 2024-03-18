<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\LaraVersion;
use App\Filament\Pages\TaskAppVersion;
use App\Filament\Resources\TaskResource\Widgets\StatsTodoOverview;
use App\Filament\Resources\TaskResource\Widgets\TaskTodo;
use Awcodes\FilamentVersions\Providers\LaravelVersionProvider;
use Awcodes\FilamentVersions\Providers\PHPVersionProvider;
use Awcodes\FilamentVersions\VersionsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rupadana\ApiService\ApiServicePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('images/logo.png'))
            ->brandName('Task Management App')
            ->id('admin')
            ->path('admin')
            ->registration()
            ->login()
            ->profile(isSimple: false)
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'primary' => Color::Green,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'blue' => Color::Blue,
                'purple' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StatsTodoOverview::make(),
                TaskTodo::make()
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                VersionsPlugin::make()->items([
                    new LaravelVersionProvider(),
                    new PHPVersionProvider(),
                    new TaskAppVersion()
                ])->hasDefaults(false),
                ApiServicePlugin::make(),
                FilamentSpatieRolesPermissionsPlugin::make()
            ]);
    }
}
