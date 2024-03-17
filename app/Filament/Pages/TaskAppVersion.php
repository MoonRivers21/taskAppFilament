<?php

namespace App\Filament\Pages;

use Awcodes\FilamentVersions\Providers\Contracts\VersionProvider;

class TaskAppVersion implements VersionProvider
{
    public function getName(): string
    {
        return 'AppVersion';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}
