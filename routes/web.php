<?php

use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\Admin\SectorController as AdminSectorController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BolsaController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\CloudConvertWebhookController;
use App\Http\Controllers\RegisterCompanyController;
use App\Http\Controllers\SpinController;
use App\Models\Spin;
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
Route::post('/webhook/cloudconvert', [CloudConvertWebhookController::class, 'handle']);
Route::get('/spins/test/{spin}/preview', function (\App\Models\Spin $spin) {
    return view('spins.preview', compact('spin'));
})->name('spins.preview');

Route::group(['middleware' => 'auth'], function () {
    Route::post('/spins', [SpinController::class, 'store'])->name('spins.store');

    Route::get('/spins/test', function () {
        return view('spins.test');
    })->name('spins.test');

    Route::post('/spins/test', [SpinController::class, 'store'])
        ->name('spins.test.store');

    // Ver estado rápido (JSON) para confirmar processing/ready y paths
    Route::get('/spins/test/{spin}', function (Spin $spin) {
        return response()->json($spin->toArray());
    })->name('spins.test.status');

    Route::get('/admin', 'HomeController@index')->name('home');
    Route::get('/scene/{id}', 'SceneController@index')->name('config');
    Route::get('/profile', 'UserController@index')->name('profil');
    Route::get('/ubahPassword', 'PasswordController@index')->name('ubahPassword');

    Route::get('/dataScene', 'SceneController@dataScene')->name('dataScene');
    Route::get('/dataHotspot', 'SceneController@dataHotspot')->name('dataHotspot');

    Route::post('/changePassword', 'PasswordController@store')->name('changePassword');
    Route::post('/addScene', 'SceneController@store')->name('addScene');
    Route::post('/addHotspot', 'HotspotController@store')->name('addHotspot');
    Route::post('/addHotspotBatch', 'HotspotController@storeBatch')->name('addHotspotBatch');
    Route::post('/updateSpinSettings', 'SceneController@updateSpinSettings')->name('updateSpinSettings');

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
    Route::delete('/property/{propertyId}/image/{imageId}', [PropertiesController::class, 'destroyImage'])->name('deletePropertyImage');

    // Sectores CRUD (existente)
    Route::get('/sectors', [SectorController::class, 'indexAdmin'])->name('sectors');
    Route::post('/sector/store', [SectorController::class, 'store'])->name('addSector');
    Route::put('/sector/update/{id}', [SectorController::class, 'update']);
    Route::delete('/delete/sector/{id}', [SectorController::class, 'destroy']);

    // Categorías CRUD (existente)
    Route::get('/categories', [CategoryController::class, 'indexAdmin'])->name('categories');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('addCategory');
    Route::put('/category/update/{id}', [CategoryController::class, 'update']);
    Route::delete('/delete/category/{id}', [CategoryController::class, 'destroy']);

    // Scene config typed route (legacy, mantener compatibilidad)
    Route::get('/scene-config/{type}/{id}', 'SceneController@index')->name('configTyped')
        ->where('type', 'property|vehicle');

    // =====================================================
    // NUEVOS MÓDULOS: Suscripciones, Paquetes, Bolsa Inmobiliaria
    // =====================================================

    // --- SECTORES ADMIN (Solo super_admin) ---
    Route::group(['prefix' => 'admin/sectors', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [AdminSectorController::class, 'index'])->name('admin.sectors.index');
        Route::get('/create', [AdminSectorController::class, 'create'])->name('admin.sectors.create');
        Route::post('/', [AdminSectorController::class, 'store'])->name('admin.sectors.store');
        Route::get('/{sector}/edit', [AdminSectorController::class, 'edit'])->name('admin.sectors.edit');
        Route::put('/{sector}', [AdminSectorController::class, 'update'])->name('admin.sectors.update');
        Route::delete('/{sector}', [AdminSectorController::class, 'destroy'])->name('admin.sectors.destroy');
        Route::post('/{sector}/toggle-status', [AdminSectorController::class, 'toggleStatus'])->name('admin.sectors.toggle-status');
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

    // --- CATEGORÍAS ADMIN (Con suscripción activa) ---
    Route::group(['prefix' => 'admin/categories', 'middleware' => ['subscription']], function () {
        Route::get('/', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/{category}', [AdminCategoryController::class, 'show'])->name('admin.categories.show');
        Route::get('/{category}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('admin.categories.toggle-status');
        Route::post('/{category}/regenerate-token', [AdminCategoryController::class, 'regenerateToken'])->name('admin.categories.regenerate-token');
    });

    // --- SUBCATEGORÍAS (Categorías por sucursal, con suscripción activa) ---
    Route::group(['prefix' => 'admin/categories/{category}/subcategories', 'middleware' => ['subscription']], function () {
        Route::get('/', [SubcategoryController::class, 'index'])->name('admin.subcategories.index');
        Route::get('/create', [SubcategoryController::class, 'create'])->name('admin.subcategories.create');
        Route::post('/', [SubcategoryController::class, 'store'])->name('admin.subcategories.store');
        Route::get('/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('admin.subcategories.edit');
        Route::put('/{subcategory}', [SubcategoryController::class, 'update'])->name('admin.subcategories.update');
        Route::delete('/{subcategory}', [SubcategoryController::class, 'destroy'])->name('admin.subcategories.destroy');
        Route::post('/{subcategory}/toggle-status', [SubcategoryController::class, 'toggleStatus'])->name('admin.subcategories.toggle-status');
        Route::get('/{subcategory}/inmuebles', [SubcategoryController::class, 'inmuebles'])->name('admin.subcategories.inmuebles');
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

    // --- USUARIOS (Admin de empresa: solo su empresa, Super admin: todos) ---
    Route::group(['prefix' => 'admin/users', 'middleware' => 'role:company_admin,super_admin'], function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/{targetUser}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{targetUser}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/{targetUser}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::delete('/{targetUser}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });

    // --- NOTIFICACIONES ---
    Route::post('/admin/notifications/mark-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Notificaciones marcadas como leídas');
    })->name('admin.notifications.mark-read');

    // --- EMPRESAS (Solo super_admin) ---
    Route::group(['prefix' => 'admin/companies', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [CompanyController::class, 'index'])->name('admin.companies.index');
        Route::get('/create', [CompanyController::class, 'create'])->name('admin.companies.create');
        Route::post('/', [CompanyController::class, 'store'])->name('admin.companies.store');
        Route::get('/{company}', [CompanyController::class, 'show'])->name('admin.companies.show');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('admin.companies.edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('admin.companies.update');
        Route::post('/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('admin.companies.toggle-status');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('admin.companies.destroy');
    });
});

// =====================================================
// RUTAS PÚBLICAS
// =====================================================
Route::get('/', [SectorController::class, 'index'])->name('welcome');
Route::get('/sector/{slug}', [SectorController::class, 'show'])->name('sector.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/category/{categorySlug}/subcategory/{subcategorySlug}', [CategoryController::class, 'showSubcategory'])->name('subcategory.show');
Route::get('virtual-tour/{id}', 'SceneController@pannellum')->name('virtual-tour');

// Planes y registro de empresa
Route::get('/planes', [RegisterCompanyController::class, 'pricing'])->name('pricing');
Route::get('/registro-empresa', [RegisterCompanyController::class, 'showForm'])->name('register.company');
Route::post('/registro-empresa', [RegisterCompanyController::class, 'register'])->name('register.company.store');

// Enlace compartible de categoría
Route::get('/c/{shareToken}', function ($shareToken) {
    $category = \App\Category::where('share_token', $shareToken)->firstOrFail();
    return redirect()->route('category.show', $category->slug);
})->name('category.share');

// Enlace compartible de publicación
Route::get('/p/{shareToken}', function ($shareToken) {
    $property = \App\Properties::where('share_token', $shareToken)->firstOrFail();
    return redirect()->route('virtual-tour', $property->id);
})->name('property.share');

// API: Búsqueda de publicaciones por sector (para buscador en frontend)
Route::get('/api/search-properties', function (\Illuminate\Http\Request $request) {
    $query = \App\Properties::query()->available();

    if ($request->filled('sector_id')) {
        $query->inSector($request->sector_id);
    }

    if ($request->filled('q')) {
        $search = $request->q;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $results = $query->with('subcategory')
        ->select('id', 'name', 'brand', 'model', 'year', 'property_type', 'price', 'currency', 'image', 'location', 'share_token')
        ->limit(10)
        ->get()
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->property_type === 'vehicle'
                    ? ($p->brand . ' ' . $p->model . ' ' . $p->year)
                    : $p->name,
                'type' => $p->property_type,
                'type_name' => $p->property_type_name ?? $p->property_type,
                'price' => ($p->currency === 'USD' ? '$' : '₡') . number_format($p->price),
                'location' => $p->location,
                'image' => $p->image ? route('file', $p->image) : url('images/producto-sin-imagen.PNG'),
                'url' => route('virtual-tour', $p->id),
            ];
        });

    return response()->json($results);
})->name('api.search-properties');

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
