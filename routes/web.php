<?php

use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BolsaController;
use App\Http\Controllers\Admin\SaleController;
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

    //Rutas para propiedades
    Route::post('property/store', [PropertiesController::class, 'store'])->name('addProperty');
    Route::put('/property/update/{id}', [PropertiesController::class, 'update']);
    Route::get('/property', [PropertiesController::class, 'indexAdmin'])->name('property');
    Route::delete('/delete/property/{id}', [PropertiesController::class, 'destroy']);

    // =====================================================
    // NUEVOS MÓDULOS: Suscripciones, Paquetes, Bolsa Inmobiliaria
    // =====================================================

    // --- SECTORES (Solo super_admin) ---
    Route::group(['prefix' => 'admin/sectors', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [SectorController::class, 'index'])->name('admin.sectors.index');
        Route::get('/create', [SectorController::class, 'create'])->name('admin.sectors.create');
        Route::post('/', [SectorController::class, 'store'])->name('admin.sectors.store');
        Route::get('/{sector}/edit', [SectorController::class, 'edit'])->name('admin.sectors.edit');
        Route::put('/{sector}', [SectorController::class, 'update'])->name('admin.sectors.update');
        Route::delete('/{sector}', [SectorController::class, 'destroy'])->name('admin.sectors.destroy');
        Route::post('/{sector}/toggle-status', [SectorController::class, 'toggleStatus'])->name('admin.sectors.toggle-status');
    });

    // --- PAQUETES (Solo super_admin) ---
    Route::group(['prefix' => 'admin/packages', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [PackageController::class, 'index'])->name('admin.packages.index');
        Route::get('/create', [PackageController::class, 'create'])->name('admin.packages.create');
        Route::post('/', [PackageController::class, 'store'])->name('admin.packages.store');
        Route::get('/{package}/edit', [PackageController::class, 'edit'])->name('admin.packages.edit');
        Route::put('/{package}', [PackageController::class, 'update'])->name('admin.packages.update');
        Route::delete('/{package}', [PackageController::class, 'destroy'])->name('admin.packages.destroy');
        Route::post('/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('admin.packages.toggle-status');
        Route::post('/{package}/toggle-featured', [PackageController::class, 'toggleFeatured'])->name('admin.packages.toggle-featured');
    });

    // --- SUSCRIPCIONES (Super admin: todas, Users: solo la suya) ---
    Route::group(['prefix' => 'admin/subscriptions'], function () {
        // Super admin routes
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
            Route::get('/pending', [SubscriptionController::class, 'pending'])->name('admin.subscriptions.pending');
            Route::get('/expiring', [SubscriptionController::class, 'expiring'])->name('admin.subscriptions.expiring');
            Route::get('/create', [SubscriptionController::class, 'create'])->name('admin.subscriptions.create');
            Route::post('/', [SubscriptionController::class, 'store'])->name('admin.subscriptions.store');
            Route::post('/{subscription}/approve', [SubscriptionController::class, 'approve'])->name('admin.subscriptions.approve');
            Route::post('/{subscription}/suspend', [SubscriptionController::class, 'suspend'])->name('admin.subscriptions.suspend');
            Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('admin.subscriptions.cancel');
            Route::post('/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('admin.subscriptions.extend');
            Route::get('/pending-payments', [SubscriptionController::class, 'pendingPayments'])->name('admin.subscriptions.pending-payments');
            Route::post('/payments/{payment}/approve', [SubscriptionController::class, 'approvePayment'])->name('admin.subscriptions.approve-payment');
            Route::post('/payments/{payment}/reject', [SubscriptionController::class, 'rejectPayment'])->name('admin.subscriptions.reject-payment');
        });

        // Routes accessible to all authenticated users
        Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('admin.subscriptions.my');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('admin.subscriptions.show');
        Route::get('/{subscription}/payments', [SubscriptionController::class, 'payments'])->name('admin.subscriptions.payments');
        Route::get('/{subscription}/payments/create', [SubscriptionController::class, 'createPayment'])->name('admin.subscriptions.create-payment');
        Route::post('/{subscription}/payments', [SubscriptionController::class, 'storePayment'])->name('admin.subscriptions.store-payment');
    });

    // Ruta para cuando no hay suscripción activa
    Route::get('/subscription-required', function () {
        return view('admin.subscriptions.required');
    })->name('subscription.required');

    // --- CATEGORÍAS (Con suscripción activa) ---
    Route::group(['prefix' => 'admin/categories', 'middleware' => ['subscription']], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('admin.categories.show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('admin.categories.toggle-status');
        Route::post('/{category}/regenerate-token', [CategoryController::class, 'regenerateToken'])->name('admin.categories.regenerate-token');
    });

    // --- BOLSA INMOBILIARIA (Con suscripción que permite comisiones) ---
    Route::group(['prefix' => 'admin/bolsa', 'middleware' => ['subscription:commission']], function () {
        Route::get('/', [BolsaController::class, 'index'])->name('admin.bolsa.index');
        Route::get('/available', [BolsaController::class, 'available'])->name('admin.bolsa.available');
        Route::get('/my-requests', [BolsaController::class, 'myRequests'])->name('admin.bolsa.my-requests');
        Route::get('/incoming', [BolsaController::class, 'incoming'])->name('admin.bolsa.incoming');

        // Solicitudes de comisión
        Route::get('/request/{property}', [BolsaController::class, 'createRequest'])->name('admin.bolsa.create-request');
        Route::post('/request/{property}', [BolsaController::class, 'storeRequest'])->name('admin.bolsa.store-request');
        Route::get('/negotiation/{commissionRequest}', [BolsaController::class, 'negotiation'])->name('admin.bolsa.negotiation');
        Route::post('/negotiation/{commissionRequest}/message', [BolsaController::class, 'sendMessage'])->name('admin.bolsa.send-message');
        Route::post('/negotiation/{commissionRequest}/accept', [BolsaController::class, 'acceptRequest'])->name('admin.bolsa.accept');
        Route::post('/negotiation/{commissionRequest}/reject', [BolsaController::class, 'rejectRequest'])->name('admin.bolsa.reject');
        Route::post('/negotiation/{commissionRequest}/cancel', [BolsaController::class, 'cancelRequest'])->name('admin.bolsa.cancel');

        // Registro de ventas
        Route::match(['get', 'post'], '/sale/{property}', [BolsaController::class, 'registerSale'])->name('admin.bolsa.register-sale');

        // AJAX
        Route::post('/calculate-commission', [BolsaController::class, 'calculateCommission'])->name('admin.bolsa.calculate');
    });

    // --- VENTAS ---
    Route::group(['prefix' => 'admin/sales', 'middleware' => ['subscription']], function () {
        Route::get('/', [SaleController::class, 'index'])->name('admin.sales.index');
        Route::get('/report', [SaleController::class, 'report'])->name('admin.sales.report');
        Route::get('/stats', [SaleController::class, 'stats'])->name('admin.sales.stats');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('admin.sales.show');
        Route::post('/{sale}/confirm', [SaleController::class, 'confirm'])->name('admin.sales.confirm');
        Route::post('/{sale}/cancel', [SaleController::class, 'cancel'])->name('admin.sales.cancel');
    });

});

// =====================================================
// RUTAS PÚBLICAS
// =====================================================

Route::get('/', 'SceneController@frontendIndex')->name('welcome');
Route::get('virtual-tour/{id}', 'SceneController@pannellum')->name('virtual-tour');

// Enlace compartible de categoría
Route::get('/c/{shareToken}', 'LandingController@category')->name('category.share');

// Archivo desde storage
Route::get('/file/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);
    if (!file_exists($path)) {
        $path = storage_path('app/public/uploads/' . $filename);
    }
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->where('filename', '.*')->name('file');
