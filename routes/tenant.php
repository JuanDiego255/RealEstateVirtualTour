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
use App\Http\Controllers\ScenePolygonController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VehicleController;
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

        // Rutas para subida de video en chunks (archivos grandes)
        Route::post('/upload-video-chunk', [SceneController::class, 'uploadVideoChunk'])->name('uploadVideoChunk');
        Route::post('/complete-video-upload', [SceneController::class, 'completeVideoUpload'])->name('completeVideoUpload');
        Route::post('/cancel-video-upload', [SceneController::class, 'cancelVideoUpload'])->name('cancelVideoUpload');

        Route::get('/showScene/{id}', [SceneController::class, 'show'])->name('showScene');
        Route::get('/showHotspot/{id}', [HotspotController::class, 'show'])->name('showHotspot');

        Route::put('/editScene/{id}', [SceneController::class, 'update'])->name('editScene');
        Route::put('/editHotspot/', [HotspotController::class, 'update'])->name('editHotspot');
        Route::put('/editprofile/{id}', [UserController::class, 'update'])->name('editProfil');
        Route::put('/setFScene/{id}', [SceneController::class, 'status'])->name('changeFScene');

        Route::delete('/delUser/{id}', [UserController::class, 'destroy'])->name('delProfil');
        Route::delete('/delScene/{id}', [SceneController::class, 'destroy'])->name('delScene');
        Route::delete('/delHotspot/{id}', [HotspotController::class, 'destroy'])->name('delHotspot');

        // Rutas para polígonos de escenas (marcadores de terreno)
        Route::post('/polygon', [ScenePolygonController::class, 'store'])->name('addPolygon');
        Route::put('/polygon/{id}', [ScenePolygonController::class, 'update'])->name('editPolygon');
        Route::delete('/polygon/{id}', [ScenePolygonController::class, 'destroy'])->name('delPolygon');

        // Propiedades
        Route::post('property/store', [PropertiesController::class, 'store'])->name('addProperty');
        Route::put('/property/update/{id}', [PropertiesController::class, 'update']);
        Route::get('/property', [PropertiesController::class, 'indexAdmin'])->name('property');
        Route::delete('/delete/property/{id}', [PropertiesController::class, 'destroy']);

        // Sectores CRUD
        Route::get('/sectors', [SectorController::class, 'indexAdmin'])->name('sectors');
        Route::post('/sector/store', [SectorController::class, 'store'])->name('addSector');
        Route::put('/sector/update/{id}', [SectorController::class, 'update']);
        Route::delete('/delete/sector/{id}', [SectorController::class, 'destroy']);

        // Categorías CRUD
        Route::get('/categories', [CategoryController::class, 'indexAdmin'])->name('categories');
        Route::post('/category/store', [CategoryController::class, 'store'])->name('addCategory');
        Route::put('/category/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete/category/{id}', [CategoryController::class, 'destroy']);

        // Vehículos CRUD
        Route::get('/vehicles', [VehicleController::class, 'indexAdmin'])->name('vehicles');
        Route::post('/vehicle/store', [VehicleController::class, 'store'])->name('addVehicle');
        Route::put('/vehicle/update/{id}', [VehicleController::class, 'update']);
        Route::delete('/delete/vehicle/{id}', [VehicleController::class, 'destroy']);

        // Scene config typed route for vehicles
        Route::get('/scene-config/{type}/{id}', [SceneController::class, 'index'])->name('configTyped')
            ->where('type', 'property|vehicle');
    });

    //images Tenant
    Route::get('/file/{path}', function ($path) {
        $path = Storage::path($path);
        $path = str_replace('app\\', 'app\\public\\', $path);

        return response()->file($path);
    })->where('path', '.*')->name('file');

    //images tenant
    Route::get('/', [SectorController::class, 'index'])->name('welcome');
    Route::get('/sector/{slug}', [SectorController::class, 'show'])->name('sector.show');
    Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/scene/{sceneId}/polygons', [ScenePolygonController::class, 'index'])->name('scenePolygons');
    Route::get('virtual-tour/{id}', [SceneController::class, 'pannellum'])->name('virtual-tour');
});
