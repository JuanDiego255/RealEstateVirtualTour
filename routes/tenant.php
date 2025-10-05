<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Auth::routes();

    Route::group(['middleware' => 'auth'], function () {

        Route::get('/admin', [HomeController::class, 'index'])->name('home');
        Route::get('/scene/{id}', [SceneController::class, 'index'])->name('config');
        Route::get('/profile', [UserController::class, 'index'])->name('profil');
        Route::get('/ubahPassword', [PasswordController::class, 'index'])->name('ubahPassword');

        Route::get('/dataScene', [SceneController::class, 'dataScene'])->name('dataScene');
        Route::get('/dataHotspot', [SceneController::class, 'dataHotspot'])->name('dataHotspot');

        Route::post('/changePassword', [PasswordController::class, 'store'])->name('changePassword');
        Route::post('/addScene', [SceneController::class, 'store'])->name('addScene');
        Route::post('/addHotspot', [HotspotController::class, 'store'])->name('addHotspot');

        Route::get('/showScene/{id}', [SceneController::class, 'show'])->name('showScene');
        Route::get('/showHotspot/{id}', [HotspotController::class, 'show'])->name('showHotspot');

        Route::put('/editScene/{id}', [SceneController::class, 'update'])->name('editScene');
        Route::put('/editHotspot/', [HotspotController::class, 'update'])->name('editHotspot');
        Route::put('/editprofile/{id}', [UserController::class, 'update'])->name('editProfil');
        Route::put('/setFScene/{id}', [SceneController::class, 'status'])->name('changeFScene');

        Route::delete('/delUser/{id}', [UserController::class, 'destroy'])->name('delProfil');
        Route::delete('/delScene/{id}', [SceneController::class, 'destroy'])->name('delScene');
        Route::delete('/delHotspot/{id}', [HotspotController::class, 'destroy'])->name('delHotspot');

        //Rutas para redes sociales seccion
        Route::post('property/store', [PropertiesController::class, 'store'])->name('addProperty');
        Route::put('/property/update/{id}', [PropertiesController::class, 'update']);
        Route::get('/property', [PropertiesController::class, 'indexAdmin'])->name('property');
        Route::delete('/delete/property/{id}', [PropertiesController::class, 'destroy']);
    });
    //images Tenant
    Route::get('/file/{path}', function ($path) {
        $path = Storage::path($path);
        $path = str_replace('app\\', 'app\\public\\', $path);

        return response()->file($path);
    })->where('path', '.*')->name('file');
    //images tenant
    Route::get('/', [SceneController::class, 'frontendIndex'])->name('welcome');
    Route::get('virtual-tour/{id}', [SceneController::class, 'pannellum'])->name('virtual-tour');
});
