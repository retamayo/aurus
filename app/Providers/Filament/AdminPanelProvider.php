<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stephenjude\FilamentBlog\BlogPlugin;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use TomatoPHP\FilamentBrowser\FilamentBrowserPlugin;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;
use TomatoPHP\FilamentDeveloperGate\FilamentDeveloperGatePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Violet,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                BlogPlugin::make(),
                FilamentGeneralSettingsPlugin::make()
                ->canAccess(fn() => auth()->user()->id === 1)
                ->setSort(3)
                ->setIcon('heroicon-o-cog')
                ->setNavigationGroup('Settings')
                ->setTitle('General Settings')
                ->setNavigationLabel('General Settings'),
                FilamentMediaManagerPlugin::make(),
                // FilamentBrowserPlugin::make()
                // ->hiddenFolders([
                //     base_path('app')
                // ])
                // ->hiddenFiles([
                //     base_path('.env')
                // ])
                // ->hiddenExtensions([
                //     "php"
                // ])
                // ->allowCreateFolder()
                // ->allowEditFile()
                // ->allowCreateNewFile()
                // ->allowCreateFolder()
                // ->allowRenameFile()
                // ->allowDeleteFile()
                // ->allowMarkdown()
                // ->allowCode()
                // ->allowPreview()
                // ->basePath(base_path()),
                FilamentDeveloperGatePlugin::make(),
            ]);
    }
}
