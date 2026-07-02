<?php

namespace App\Actions\Mobile;

class SetupCapacitorConfigAction
{
    public function execute(): array
    {
        $capacitorConfig = [
            'appId' => 'com.erp.pos.retail',
            'appName' => 'ERP POS Retail',
            'webDir' => 'public',
            'bundledWebRuntime' => false,
            'server' => [
                'androidScheme' => 'https',
            ],
            'plugins' => [
                'LocalNotifications' => [
                    'smallIcon' => 'ic_stat_notification',
                    'iconColor' => '#488AFF',
                ],
                'SplashScreen' => [
                    'launchShowDuration' => 0,
                    'launchAutoHide' => true,
                ],
            ],
        ];

        $pwaManifest = [
            'name' => 'ERP POS Retail System',
            'short_name' => 'ERP POS',
            'description' => 'Integrated POS, Inventory & Accounting System',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'theme_color' => '#000000',
            'background_color' => '#ffffff',
            'icons' => [
                [
                    'src' => '/img/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/img/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
            'categories' => ['business', 'productivity'],
        ];

        return [
            'capacitor_config' => $capacitorConfig,
            'pwa_manifest' => $pwaManifest,
            'message' => 'Capacitor & PWA configuration ready',
        ];
    }
}
