<?php

use Illuminate\Support\Facades\Artisan;

it('backup config publishes and lists thelastclicks as application name', function () {
    expect(file_exists(config_path('backup.php')))->toBeTrue();
});

it('backup:run command is registered', function () {
    expect(Artisan::all())->toHaveKey('backup:run');
});

it('backup:clean command is registered', function () {
    expect(Artisan::all())->toHaveKey('backup:clean');
});
