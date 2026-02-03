<?php

use App\Http\Controllers\PropertiesController;
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

    //Rutas para redes sociales seccion
    Route::post('property/store', [PropertiesController::class, 'store'])->name('addProperty');
    Route::put('/property/update/{id}', [PropertiesController::class, 'update']);
    Route::get('/property', [PropertiesController::class, 'indexAdmin'])->name('property');
    Route::delete('/delete/property/{id}', [PropertiesController::class, 'destroy']);
 
});

Route::get('/', 'SceneController@frontendIndex')->name('welcome');
Route::get('virtual-tour/{id}', 'SceneController@pannellum')->name('virtual-tour');