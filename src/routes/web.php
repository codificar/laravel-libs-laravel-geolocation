<?php
// Settings
require 'settings.php';

// App User
require 'app/user_delivery_old.php'; // Old Routes
require 'app/user_mobility_old.php'; // Old Routes
require 'app/user.php';

// App Provider
require 'app/provider.php';

// Painel
require 'painel/public.php';
require 'painel/admin.php';
require 'painel/corp.php';
require 'painel/user.php';

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/libs/geolocation/lang.trans/{file}', function () {
    
    app('debugbar')->disable();

    $fileNames = explode(',', Request::segment(4));
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__.'/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    return response($content)
            ->header('Content-Type', 'text/javascript');
            
})->name('assets.lang');
