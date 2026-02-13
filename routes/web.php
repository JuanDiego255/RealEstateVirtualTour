<?php

use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    Route::get('/admin', 'HomeController@index')->name('home');
    Route::get('/scene/{id}', 'SceneController@index')->name('config');
    Route::get('/profile', 'UserController@index')->name('profil');
    Route::get('/ubahPassword', 'PasswordController@index')->name('ubahPassword');

    Route::get('/dataScene', 'SceneController@dataScene')->name('dataScene');
    Route::get('/dataHotspot', 'SceneController@dataHotspot')->name('dataHotspot');

    Route::post('/changePassword', 'PasswordController@store')->name('changePassword');
    Route::post('/addScene', 'SceneController@store')->name('addScene');
    Route::post('/addHotspot', 'HotspotController@store')->name('addHotspot');

    // Rutas para subida de video en chunks (archivos grandes)
    Route::post('/upload-video-chunk', 'SceneController@uploadVideoChunk')->name('uploadVideoChunk');
    Route::post('/complete-video-upload', 'SceneController@completeVideoUpload')->name('completeVideoUpload');
    Route::post('/cancel-video-upload', 'SceneController@cancelVideoUpload')->name('cancelVideoUpload');

    Route::get('/showScene/{id}', 'SceneController@show')->name('showScene');
    Route::get('/showHotspot/{id}', 'HotspotController@show')->name('showHotspot');

    Route::put('/editScene/{id}', 'SceneController@update')->name('editScene');
    Route::put('/editHotspot/', 'HotspotController@update')->name('editHotspot');
    Route::put('/editprofile/{id}', 'UserController@update')->name('editProfil');
    Route::put('/setFScene/{id}', 'SceneController@status')->name('changeFScene');

    Route::delete('/delUser/{id}', 'UserController@destroy')->name('delProfil');
    Route::delete('/delScene/{id}', 'SceneController@destroy')->name('delScene');
    Route::delete('/delHotspot/{id}', 'HotspotController@destroy')->name('delHotspot');

    // Rutas para polígonos de escenas (marcadores de terreno)
    Route::get('/scene/{sceneId}/polygons', 'ScenePolygonController@index')->name('scenePolygons');
    Route::post('/polygon', 'ScenePolygonController@store')->name('addPolygon');
    Route::put('/polygon/{id}', 'ScenePolygonController@update')->name('editPolygon');
    Route::delete('/polygon/{id}', 'ScenePolygonController@destroy')->name('delPolygon');

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
    Route::get('/scene-config/{type}/{id}', 'SceneController@index')->name('configTyped')
        ->where('type', 'property|vehicle');
});

// Public routes
Route::get('/', [SectorController::class, 'index'])->name('welcome');
Route::get('/sector/{slug}', [SectorController::class, 'show'])->name('sector.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('virtual-tour/{id}', 'SceneController@pannellum')->name('virtual-tour');
