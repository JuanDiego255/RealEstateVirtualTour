<?php

use App\Http\Controllers\CloudConvertWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('api/webhook/cloudconvert', [CloudConvertWebhookController::class, 'handle'])
    ->name('cloudconvert.webhook');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/vehicle-inquiries', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'                => 'required|string|max:150',
        'phone'               => 'required|string|max:30',
        'email'               => 'nullable|email|max:150',
        'vehicle_description' => 'required|string|max:1000',
    ]);

    \Illuminate\Support\Facades\DB::table('vehicle_inquiries')->insert([
        'name'                => $data['name'],
        'phone'               => $data['phone'],
        'email'               => $data['email'] ?? null,
        'vehicle_description' => $data['vehicle_description'],
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Solicitud recibida correctamente'], 201);
})->name('api.vehicle-inquiries');

// Flutter app login: POST /api/login
Route::post('/login', function (\Illuminate\Http\Request $request) {

    $request->validate([
        'login'    => 'required|string',
        'password' => 'required|string',
    ]);

    // Detectar si es email o username
    $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) 
                ? 'email' 
                : 'username';

    $credentials = [
        $field    => $request->login,
        'password'=> $request->password
    ];

    if (!\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $user = \Illuminate\Support\Facades\Auth::user();

    $token = hash_hmac('sha256', $user->email . '|' . $user->id, config('app.key'));
    $user->forceFill(['api_token' => $token])->save();

    $permissions = null;
    if ($user->isAgent()) {
        $perm = $user->modulePermissions;
        $permissions = $perm ? $perm->permissions : [];
    }

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'company_id'  => $user->company_id,
            'permissions' => $permissions,
        ],
    ]);
});

// Flutter app lead form: POST /api/leads
Route::post('/leads', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'      => 'required|string|max:150',
        'phone'     => 'required|string|max:30',
        'email'     => 'nullable|email|max:150',
        'message'   => 'nullable|string|max:1000',
        'tour_type' => 'nullable|string|max:80',
    ]);

    $description = trim(($data['tour_type'] ?? '') . "\n" . ($data['message'] ?? ''));

    \Illuminate\Support\Facades\DB::table('vehicle_inquiries')->insert([
        'name'                => $data['name'],
        'phone'               => $data['phone'],
        'email'               => $data['email'] ?? null,
        'vehicle_description' => $description,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    return response()->json(['success' => true, 'message' => '¡Mensaje enviado! Nos pondremos en contacto pronto.'], 201);
})->name('api.leads');

// ═══════════════════════════════════════════════════════
// PUBLIC EXPLORE API — Flutter app navigation hierarchy
// Sector → Category (Sucursal) → Subcategory → Properties
// ═══════════════════════════════════════════════════════

// Helper: build full file URL for images stored in storage
function apiFileUrl($path): ?string {
    if (!$path) return null;
    return url('/file/' . ltrim($path, '/'));
}

// ── Sectores ─────────────────────────────────────────────
Route::get('/sectors', function () {
    $sectors = \App\Sector::where('status', true)
        ->withCount(['categories' => fn($q) => $q->where('status', true)])
        ->orderBy('sort_order')->orderBy('name')
        ->get()
        ->map(fn($s) => [
            'id'               => $s->id,
            'name'             => $s->name,
            'slug'             => $s->slug,
            'icon'             => $s->icon,
            'description'      => $s->description,
            'image'            => apiFileUrl($s->image),
            'categories_count' => $s->categories_count,
        ]);

    return response()->json($sectors);
})->name('api.sectors');

Route::get('/sectors/{slug}', function (string $slug) {
    $sector = \App\Sector::where('slug', $slug)->where('status', true)->firstOrFail();

    $categories = \App\Category::where('sector_id', $sector->id)
        ->where('status', true)
        ->withCount(['subcategories' => fn($q) => $q->where('is_active', true)])
        ->get()
        ->map(fn($c) => [
            'id'                 => $c->id,
            'name'               => $c->name,
            'slug'               => $c->slug,
            'description'        => $c->description,
            'location'           => $c->location,
            'logo'               => apiFileUrl($c->logo),
            'cover_image'        => apiFileUrl($c->cover_image) ?? apiFileUrl($c->image),
            'contact_phone'      => $c->contact_phone,
            'contact_whatsapp'   => $c->contact_whatsapp,
            'subcategories_count'=> $c->subcategories_count,
        ]);

    return response()->json([
        'id'          => $sector->id,
        'name'        => $sector->name,
        'slug'        => $sector->slug,
        'icon'        => $sector->icon,
        'description' => $sector->description,
        'image'       => apiFileUrl($sector->image),
        'categories'  => $categories,
    ]);
})->name('api.sectors.show');

// ── Sucursales / Categorías ────────────────────────────
Route::get('/categories/{slug}', function (string $slug) {
    $cat = \App\Category::where('slug', $slug)
        ->where('status', true)
        ->with(['sector', 'subcategories' => fn($q) => $q->active()->ordered()->withCount('properties')])
        ->firstOrFail();

    $subcategories = $cat->subcategories->map(fn($sub) => [
        'id'               => $sub->id,
        'name'             => $sub->name,
        'slug'             => $sub->slug,
        'description'      => $sub->description,
        'image'            => apiFileUrl($sub->image),
        'properties_count' => $sub->properties_count,
    ]);

    return response()->json([
        'id'               => $cat->id,
        'name'             => $cat->name,
        'slug'             => $cat->slug,
        'description'      => $cat->description,
        'location'         => $cat->location,
        'address'          => $cat->address,
        'logo'             => apiFileUrl($cat->logo),
        'cover_image'      => apiFileUrl($cat->cover_image) ?? apiFileUrl($cat->image),
        'contact_name'     => $cat->contact_name,
        'contact_phone'    => $cat->contact_phone,
        'contact_whatsapp' => $cat->contact_whatsapp,
        'contact_email'    => $cat->contact_email,
        'website'          => $cat->website,
        'sector'           => $cat->sector ? ['id' => $cat->sector->id, 'name' => $cat->sector->name, 'slug' => $cat->sector->slug] : null,
        'subcategories'    => $subcategories,
    ]);
})->name('api.categories.show');

// ── Publicaciones por subcategoría ────────────────────
Route::get('/categories/{categorySlug}/subcategories/{subcategorySlug}', function (string $categorySlug, string $subcategorySlug) {
    $cat = \App\Category::where('slug', $categorySlug)->where('status', true)->firstOrFail();
    $sub = \App\Subcategory::where('category_id', $cat->id)
        ->where('slug', $subcategorySlug)->where('is_active', true)->firstOrFail();

    $mapProperty = function ($p) {
        $isVehicle = $p->property_type === 'vehicle';
        return [
            'id'               => $p->id,
            'name'             => $isVehicle ? trim("{$p->brand} {$p->model} {$p->year}") : $p->name,
            'property_type'    => $p->property_type,
            'price'            => ($p->currency === 'USD' ? '$' : '₡') . number_format((float)($p->price ?? 0)),
            'currency'         => $p->currency,
            'location'         => $p->location,
            'image'            => $p->image ? apiFileUrl($p->image) : null,
            'has_virtual_tour' => (bool)$p->has_virtual_tour,
            'status'           => $p->status,
            'is_featured'      => (bool)$p->is_featured,
            'web_url'          => url('/propiedad/' . $p->id),
        ];
    };

    $properties = $sub->properties->merge($sub->vehicles)->values()->map($mapProperty);

    return response()->json([
        'subcategory' => [
            'id'          => $sub->id,
            'name'        => $sub->name,
            'slug'        => $sub->slug,
            'description' => $sub->description,
        ],
        'category' => [
            'id'   => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
        ],
        'total'       => $properties->count(),
        'publications' => $properties,
    ]);
})->name('api.subcategories.publications');

// ── Detalle de publicación ─────────────────────────────
Route::get('/properties/{id}', function (int $id) {
    $p = \App\Properties::with(['category.sector', 'subcategory', 'images'])->findOrFail($id);
    $p->incrementViews();

    $isVehicle = $p->property_type === 'vehicle';

    $images = $p->images->sortBy(fn($i) => [$i->is_primary ? 0 : 1, $i->sort_order])
        ->map(fn($i) => apiFileUrl($i->image))
        ->values();

    if ($images->isEmpty() && $p->image) {
        $images = collect([apiFileUrl($p->image)]);
    }

    $data = [
        'id'               => $p->id,
        'name'             => $isVehicle ? trim("{$p->brand} {$p->model} {$p->year}") : $p->name,
        'property_type'    => $p->property_type,
        'description'      => $p->description,
        'price'            => ($p->currency === 'USD' ? '$' : '₡') . number_format((float)($p->price ?? 0)),
        'currency'         => $p->currency,
        'location'         => $p->location,
        'address'          => $p->address,
        'image'            => $p->image ? apiFileUrl($p->image) : ($images->first()),
        'images'           => $images,
        'has_virtual_tour' => (bool)$p->has_virtual_tour,
        'status'           => $p->status,
        'is_featured'      => (bool)$p->is_featured,
        'views_count'      => $p->views_count,
        'web_url'          => url('/propiedad/' . $p->id),
        'category'         => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name, 'slug' => $p->category->slug, 'contact_whatsapp' => $p->category->contact_whatsapp, 'contact_phone' => $p->category->contact_phone] : null,
        'subcategory'      => $p->subcategory ? ['id' => $p->subcategory->id, 'name' => $p->subcategory->name] : null,
    ];

    // Campos específicos según tipo
    if ($isVehicle) {
        $data += [
            'brand'        => $p->brand,
            'model'        => $p->model,
            'year'         => $p->year,
            'color'        => $p->color,
            'mileage_km'   => $p->mileage_km,
            'fuel_type'    => $p->fuel_type,
            'transmission' => $p->transmission,
            'engine_cc'    => $p->engine_cc,
            'doors'        => $p->doors,
            'passengers'   => $p->passengers,
            'condition'    => $p->condition,
            'drivetrain'   => $p->drivetrain,
        ];
    } else {
        $data += [
            'code'               => $p->code,
            'rooms'              => $p->rooms,
            'bathrooms'          => $p->bathrooms,
            'garage'             => $p->garage,
            'construction'       => $p->construction,
            'land'               => $p->land,
            'construction_year'  => $p->construction_year,
            'maintenance'        => $p->maintenance,
        ];
    }

    // Similar properties
    $similar = \App\Properties::published()->available()
        ->where('id', '!=', $p->id)
        ->where('property_type', $p->property_type)
        ->when($p->category && $p->category->sector_id, fn($q) => $q->inSector($p->category->sector_id))
        ->with('category')
        ->limit(6)->get()
        ->map(fn($s) => [
            'id'    => $s->id,
            'name'  => $s->property_type === 'vehicle' ? trim("{$s->brand} {$s->model} {$s->year}") : $s->name,
            'price' => ($s->currency === 'USD' ? '$' : '₡') . number_format((float)($s->price ?? 0)),
            'image' => $s->image ? apiFileUrl($s->image) : null,
            'property_type' => $s->property_type,
        ]);

    $data['similar'] = $similar;

    return response()->json($data);
})->name('api.properties.show');

// ── Búsqueda ──────────────────────────────────────────
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $query = \App\Properties::query()->available()
        ->with(['category.sector', 'subcategory']);

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

    if ($request->filled('sector_id')) {
        $query->inSector($request->sector_id);
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('property_type')) {
        $query->where('property_type', $request->property_type);
    }

    $results = $query
        ->select('id', 'name', 'brand', 'model', 'year', 'property_type', 'price', 'currency', 'image', 'location', 'has_virtual_tour', 'status', 'is_featured')
        ->limit(30)
        ->get()
        ->map(fn($p) => [
            'id'               => $p->id,
            'name'             => $p->property_type === 'vehicle' ? trim("{$p->brand} {$p->model} {$p->year}") : $p->name,
            'property_type'    => $p->property_type,
            'price'            => ($p->currency === 'USD' ? '$' : '₡') . number_format((float)($p->price ?? 0)),
            'location'         => $p->location,
            'image'            => $p->image ? apiFileUrl($p->image) : null,
            'has_virtual_tour' => (bool)$p->has_virtual_tour,
            'is_featured'      => (bool)$p->is_featured,
            'web_url'          => url('/propiedad/' . $p->id),
        ]);

    return response()->json(['total' => $results->count(), 'results' => $results]);
})->name('api.search');

// ── Publicaciones destacadas (para Home) ──────────────
Route::get('/featured', function () {
    $properties = \App\Properties::published()->available()->featured()
        ->with(['category.sector', 'subcategory'])
        ->limit(8)->get()
        ->map(fn($p) => [
            'id'               => $p->id,
            'name'             => $p->property_type === 'vehicle' ? trim("{$p->brand} {$p->model} {$p->year}") : $p->name,
            'property_type'    => $p->property_type,
            'price'            => ($p->currency === 'USD' ? '$' : '₡') . number_format((float)($p->price ?? 0)),
            'location'         => $p->location,
            'image'            => $p->image ? apiFileUrl($p->image) : null,
            'has_virtual_tour' => (bool)$p->has_virtual_tour,
            'category'         => $p->category ? $p->category->name : null,
            'web_url'          => url('/propiedad/' . $p->id),
        ]);

    return response()->json($properties);
})->name('api.featured');

// ═══════════════════════════════════════════════════════
// AUTHENTICATED API — requires valid api_token (auth:api)
// Used by the Flutter agent/kiosk section
// ═══════════════════════════════════════════════════════

Route::middleware('auth:api')->group(function () {

    // ── Perfil del usuario autenticado ────────────────────
    Route::get('/auth/me', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $permissions = null;
        if ($user->isAgent()) {
            $perm = $user->modulePermissions;
            $permissions = $perm ? $perm->permissions : [];
        }
        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'company_id'  => $user->company_id,
            'permissions' => $permissions,
        ]);
    })->name('api.auth.me');

    // ── Logout (invalida el token) ─────────────────────────
    Route::post('/auth/logout', function (\Illuminate\Http\Request $request) {
        $request->user()->forceFill(['api_token' => null])->save();
        return response()->json(['success' => true]);
    })->name('api.auth.logout');

    // ── Kiosko — requiere permiso kiosk.view ──────────────
    Route::middleware('api.permission:kiosk,view')->group(function () {

        // Listar vehículos del kiosko para la empresa del agente
        Route::get('/kiosk/vehicles', function (\Illuminate\Http\Request $request) {
            $user      = $request->user();
            $companyId = $user->isSuperAdmin()
                ? $request->get('company_id', $user->company_id ?? 1)
                : $user->company_id;

            $companyCategoryIds = \App\Category::where('company_id', $companyId)->pluck('id');

            $vehicles = \App\Properties::vehicles()
                ->whereIn('status', ['available', 'reserved', 'negotiating'])
                ->where(function ($q) use ($companyCategoryIds) {
                    $q->whereIn('category_id', $companyCategoryIds)
                      ->orWhereHas('subcategory', fn($sq) => $sq->whereIn('category_id', $companyCategoryIds));
                })
                ->with(['images' => fn($q) => $q->orderBy('is_primary', 'desc')->orderBy('sort_order')])
                ->orderByDesc('is_featured')
                ->orderBy('brand')
                ->get()
                ->map(fn($v) => [
                    'id'           => $v->id,
                    'name'         => trim("{$v->brand} {$v->model} {$v->year}"),
                    'brand'        => $v->brand,
                    'model'        => $v->model,
                    'year'         => $v->year,
                    'price'        => ($v->currency === 'USD' ? '$' : '₡') . number_format((float)($v->price ?? 0)),
                    'price_raw'    => (float)($v->price ?? 0),
                    'currency'     => $v->currency,
                    'fuel_type'    => $v->fuel_type,
                    'transmission' => $v->transmission,
                    'engine_cc'    => $v->engine_cc,
                    'doors'        => $v->doors,
                    'passengers'   => $v->passengers,
                    'mileage_km'   => $v->mileage_km,
                    'color'        => $v->color,
                    'condition'    => $v->condition,
                    'drivetrain'   => $v->drivetrain,
                    'status'       => $v->status,
                    'is_featured'  => (bool)$v->is_featured,
                    'has_spin'     => (bool)$v->spin_active,
                    'image'        => $v->image ? apiFileUrl($v->image) : null,
                    'images'       => $v->images->map(fn($i) => apiFileUrl($i->image))->filter()->values(),
                ]);

            return response()->json(['total' => $vehicles->count(), 'vehicles' => $vehicles]);
        })->name('api.kiosk.vehicles');

        // Detalle de vehículo
        Route::get('/kiosk/vehicles/{id}', function (\Illuminate\Http\Request $request, int $id) {
            $v = \App\Properties::vehicles()
                ->with(['images' => fn($q) => $q->orderBy('is_primary', 'desc')->orderBy('sort_order')])
                ->findOrFail($id);

            $images = $v->images->map(fn($i) => apiFileUrl($i->image))->filter()->values();
            if ($images->isEmpty() && $v->image) {
                $images = collect([apiFileUrl($v->image)]);
            }

            return response()->json([
                'id'           => $v->id,
                'name'         => trim("{$v->brand} {$v->model} {$v->year}"),
                'brand'        => $v->brand,
                'model'        => $v->model,
                'year'         => $v->year,
                'price'        => ($v->currency === 'USD' ? '$' : '₡') . number_format((float)($v->price ?? 0)),
                'price_raw'    => (float)($v->price ?? 0),
                'currency'     => $v->currency,
                'description'  => $v->description,
                'fuel_type'    => $v->fuel_type,
                'transmission' => $v->transmission,
                'engine_cc'    => $v->engine_cc,
                'doors'        => $v->doors,
                'passengers'   => $v->passengers,
                'mileage_km'   => $v->mileage_km,
                'color'        => $v->color,
                'condition'    => $v->condition,
                'drivetrain'   => $v->drivetrain,
                'plate'        => $v->plate,
                'status'       => $v->status,
                'is_featured'  => (bool)$v->is_featured,
                'has_spin'     => (bool)$v->spin_active,
                'image'        => $v->image ? apiFileUrl($v->image) : ($images->first()),
                'images'       => $images,
            ]);
        })->name('api.kiosk.vehicles.show');
    });

    // ── Kiosko — capturar lead (requiere event_dashboard.create_lead) ─────
    Route::middleware('api.permission:event_dashboard,create_lead')->group(function () {
        Route::post('/kiosk/leads', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'name'           => 'required|string|max:255',
                'phone'          => 'required|string|max:20',
                'email'          => 'nullable|email',
                'vehicle_id'     => 'nullable|exists:properties,id',
                'interest_level' => 'nullable|in:low,medium,high,hot',
                'lead_category'  => 'nullable|in:prospect,exploring,comparing,future_interest,referral,returning',
                'notes'          => 'nullable|string|max:1000',
            ]);

            $user = $request->user();
            $lead = \App\Models\EventLead::create([
                'name'                => $data['name'],
                'phone'               => $data['phone'],
                'email'               => $data['email'] ?? null,
                'property_id'         => $data['vehicle_id'] ?? null,
                'company_id'          => $user->company_id,
                'source'              => 'kiosk',
                'interest_level'      => $data['interest_level'] ?? 'medium',
                'lead_category'       => $data['lead_category'] ?? 'prospect',
                'notes'               => $data['notes'] ?? null,
                'captured_by_user_id' => $user->id,
            ]);

            return response()->json(['success' => true, 'lead_id' => $lead->id], 201);
        })->name('api.kiosk.leads.store');
    });

    // ── Cotización — calcular y guardar ──────────────────
    Route::middleware('api.permission:kiosk,view')->group(function () {
        Route::post('/kiosk/quotes/calculate', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'vehicle_price'     => 'required|numeric|min:0',
                'down_payment'      => 'required|numeric|min:0',
                'term_months'       => 'required|integer|in:12,24,36,48,60,72,84',
                'interest_rate'     => 'required|numeric|min:0|max:100',
                'payment_frequency' => 'nullable|in:monthly,annual',
            ]);

            $quote = \App\Models\VehicleQuote::generateQuote(
                (float) $request->vehicle_price,
                (float) $request->down_payment,
                (int)   $request->term_months,
                (float) $request->interest_rate,
                $request->get('payment_frequency', 'monthly')
            );

            return response()->json($quote);
        })->name('api.kiosk.quotes.calculate');

        Route::post('/kiosk/quotes', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'vehicle_id'        => 'nullable|exists:properties,id',
                'customer_name'     => 'required|string|max:255',
                'customer_email'    => 'nullable|email',
                'customer_phone'    => 'required|string|max:20',
                'vehicle_price'     => 'nullable|numeric',
                'down_payment'      => 'nullable|numeric',
                'term_months'       => 'nullable|integer',
                'interest_rate'     => 'nullable|numeric',
                'payment_frequency' => 'nullable|in:monthly,annual',
                'monthly_payment'   => 'nullable|numeric',
                'total_amount'      => 'nullable|numeric',
                'total_interest'    => 'nullable|numeric',
            ]);

            $user             = $request->user();
            $vehiclePrice     = (float)($data['vehicle_price'] ?? 0);
            $downPayment      = (float)($data['down_payment'] ?? 0);
            $paymentFrequency = $data['payment_frequency'] ?? 'monthly';

            if (isset($data['monthly_payment'], $data['total_amount'])) {
                $monthlyPayment     = (float) $data['monthly_payment'];
                $totalAmount        = (float) $data['total_amount'];
                $totalInterest      = (float)($data['total_interest'] ?? 0);
                $downPaymentPercent = $vehiclePrice > 0 ? round(($downPayment / $vehiclePrice) * 100, 2) : 0;
            } else {
                $calc               = \App\Models\VehicleQuote::generateQuote($vehiclePrice, $downPayment, $data['term_months'] ?? 36, $data['interest_rate'] ?? 1, $paymentFrequency);
                $monthlyPayment     = $calc['monthly_payment'];
                $totalAmount        = $calc['total_amount'];
                $totalInterest      = $calc['total_interest'];
                $downPaymentPercent = $calc['down_payment_percent'];
            }

            $quote = \App\Models\VehicleQuote::create([
                'property_id'          => $data['vehicle_id'] ?? null,
                'company_id'           => $user->company_id,
                'customer_name'        => $data['customer_name'],
                'customer_email'       => $data['customer_email'] ?? null,
                'customer_phone'       => $data['customer_phone'],
                'vehicle_price'        => $vehiclePrice,
                'down_payment'         => $downPayment,
                'down_payment_percent' => $downPaymentPercent,
                'term_months'          => $data['term_months'] ?? null,
                'interest_rate'        => $data['interest_rate'] ?? null,
                'monthly_payment'      => $monthlyPayment,
                'total_interest'       => $totalInterest,
                'total_amount'         => $totalAmount,
                'currency'             => 'CRC',
                'payment_frequency'    => $paymentFrequency,
                'captured_by_user_id'  => $user->id,
            ]);

            return response()->json(['success' => true, 'quote_id' => $quote->id], 201);
        })->name('api.kiosk.quotes.store');
    });

    // ── Dashboard del evento ──────────────────────────────
    Route::middleware('api.permission:event_dashboard,view')->group(function () {
        Route::get('/kiosk/dashboard', function (\Illuminate\Http\Request $request) {
            $user      = $request->user();
            $companyId = $user->isSuperAdmin() ? null : $user->company_id;

            $leadsQ  = \App\Models\EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId));
            $quotesQ = \App\Models\VehicleQuote::when($companyId, fn($q) => $q->where('company_id', $companyId));

            return response()->json([
                'total_leads'     => $leadsQ->count(),
                'leads_today'     => (clone $leadsQ)->whereDate('created_at', today())->count(),
                'leads_hot'       => (clone $leadsQ)->where('interest_level', 'hot')->count(),
                'leads_pending'   => (clone $leadsQ)->where('contacted', false)->count(),
                'quotes_today'    => $quotesQ->whereDate('created_at', today())->count(),
            ]);
        })->name('api.kiosk.dashboard');

        Route::get('/kiosk/dashboard/leads', function (\Illuminate\Http\Request $request) {
            $user      = $request->user();
            $companyId = $user->isSuperAdmin() ? null : $user->company_id;

            $leads = \App\Models\EventLead::with(['capturedBy:id,name', 'property:id,brand,model,year,image'])
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn($l) => [
                    'id'             => $l->id,
                    'name'           => $l->name,
                    'phone'          => $l->phone,
                    'email'          => $l->email,
                    'interest_level' => $l->interest_level,
                    'lead_category'  => $l->lead_category,
                    'source'         => $l->source,
                    'contacted'      => (bool)$l->contacted,
                    'contacted_at'   => $l->contacted_at?->toIso8601String(),
                    'contacted_by'   => $l->contacted_by,
                    'notes'          => $l->notes,
                    'created_at'     => $l->created_at->toIso8601String(),
                    'agent'          => $l->capturedBy ? ['id' => $l->capturedBy->id, 'name' => $l->capturedBy->name] : null,
                    'vehicle'        => $l->property ? [
                        'id'    => $l->property->id,
                        'name'  => trim("{$l->property->brand} {$l->property->model} {$l->property->year}"),
                        'image' => $l->property->image ? apiFileUrl($l->property->image) : null,
                    ] : null,
                ]);

            return response()->json(['total' => $leads->count(), 'leads' => $leads]);
        })->name('api.kiosk.dashboard.leads');

        // Marcar lead como contactado
        Route::post('/kiosk/leads/{id}/contacted', function (\Illuminate\Http\Request $request, int $id) {
            if (!$request->user()->canAccessModule('event_dashboard', 'mark_contacted')) {
                return response()->json(['message' => 'Sin permiso para marcar como contactado'], 403);
            }
            $lead = \App\Models\EventLead::findOrFail($id);
            $lead->update([
                'contacted'      => true,
                'contacted_at'   => now(),
                'contacted_by'   => $request->user()->name,
                'follow_up_status' => 'completed',
            ]);
            return response()->json(['success' => true]);
        })->name('api.kiosk.leads.contacted');

        // Detalle de lead con historial de seguimiento
        Route::get('/kiosk/leads/{id}', function (\Illuminate\Http\Request $request, int $id) {
            $user      = $request->user();
            $companyId = $user->isSuperAdmin() ? null : $user->company_id;

            $lead = \App\Models\EventLead::with([
                'capturedBy:id,name',
                'property:id,brand,model,year,image',
                'followups.agent:id,name',
            ])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->findOrFail($id);

            return response()->json([
                'id'             => $lead->id,
                'name'           => $lead->name,
                'phone'          => $lead->phone,
                'email'          => $lead->email,
                'interest_level' => $lead->interest_level,
                'lead_category'  => $lead->lead_category,
                'source'         => $lead->source,
                'contacted'      => (bool)$lead->contacted,
                'contacted_at'   => $lead->contacted_at?->toIso8601String(),
                'contacted_by'   => $lead->contacted_by,
                'notes'          => $lead->notes,
                'created_at'     => $lead->created_at->toIso8601String(),
                'agent'          => $lead->capturedBy ? ['id' => $lead->capturedBy->id, 'name' => $lead->capturedBy->name] : null,
                'vehicle'        => $lead->property ? [
                    'id'    => $lead->property->id,
                    'name'  => trim("{$lead->property->brand} {$lead->property->model} {$lead->property->year}"),
                    'image' => $lead->property->image ? apiFileUrl($lead->property->image) : null,
                ] : null,
                'followups' => $lead->followups->map(fn($f) => [
                    'id'               => $f->id,
                    'action'           => $f->action,
                    'outcome'          => $f->outcome,
                    'notes'            => $f->notes,
                    'next_followup_at' => $f->next_followup_at?->toIso8601String(),
                    'created_at'       => $f->created_at->toIso8601String(),
                    'agent'            => $f->agent ? ['id' => $f->agent->id, 'name' => $f->agent->name] : null,
                ]),
            ]);
        })->name('api.kiosk.leads.show');

        // Agregar seguimiento a lead
        Route::post('/kiosk/leads/{id}/followup', function (\Illuminate\Http\Request $request, int $id) {
            $data = $request->validate([
                'action'           => 'required|in:call,whatsapp,email,meeting,demo,quote_sent,other',
                'outcome'          => 'required|in:no_answer,interested,not_interested,thinking,follow_up_later,converted,lost',
                'notes'            => 'nullable|string|max:1000',
                'next_followup_at' => 'nullable|date',
            ]);

            $user      = $request->user();
            $companyId = $user->isSuperAdmin() ? null : $user->company_id;

            $lead = \App\Models\EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->findOrFail($id);

            $followup = $lead->followups()->create([
                'user_id'          => $user->id,
                'action'           => $data['action'],
                'outcome'          => $data['outcome'],
                'notes'            => $data['notes'] ?? null,
                'next_followup_at' => isset($data['next_followup_at']) ? \Carbon\Carbon::parse($data['next_followup_at']) : null,
            ]);

            if (in_array($data['outcome'], ['converted', 'lost'])) {
                $lead->update(['sale_status' => $data['outcome']]);
            }

            return response()->json(['success' => true, 'followup_id' => $followup->id], 201);
        })->name('api.kiosk.leads.followup');
    });
});

// ═══════════════════════════════════════════════════════
// CRM API — authenticated, requires event_dashboard.view
// ═══════════════════════════════════════════════════════

Route::middleware(['auth:api', 'api.permission:event_dashboard,view'])->group(function () {

    // ── Listar leads CRM ──────────────────────────────────
    Route::get('/crm/leads', function (\Illuminate\Http\Request $request) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $query = \App\Lead::with(['user:id,name', 'vehicle:id,brand,model,year,image'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('source'))   $query->where('source', $request->source);
        if ($request->filled('origin')) {
            if ($request->origin === 'event') $query->fromEvent();
            if ($request->origin === 'agency') $query->fromAgency();
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")
                ->orWhere('email','like',"%$s%")
                ->orWhere('phone','like',"%$s%"));
        }

        $query->orderByRaw("CASE WHEN status IN ('won','lost') THEN 1 ELSE 0 END")
              ->orderBy('next_follow_up','asc')
              ->orderByDesc('created_at');

        $paginated = $query->paginate(25);

        $statsQ = \App\Lead::when($companyId, fn($q) => $q->where('company_id', $companyId));
        $stats = [
            'total'           => (clone $statsQ)->count(),
            'new'             => (clone $statsQ)->where('status','new')->count(),
            'active'          => (clone $statsQ)->whereNotIn('status',['won','lost'])->count(),
            'won'             => (clone $statsQ)->where('status','won')->count(),
            'from_events'     => (clone $statsQ)->fromEvent()->count(),
            'needs_follow_up' => (clone $statsQ)->needsFollowUp()->count(),
        ];

        $mapLead = fn($l) => [
            'id'             => $l->id,
            'name'           => $l->name,
            'phone'          => $l->phone,
            'email'          => $l->email,
            'status'         => $l->status,
            'status_label'   => $l->status_label,
            'priority'       => $l->priority,
            'source'         => $l->source,
            'source_label'   => $l->source_label,
            'interest_type'  => $l->interest_type,
            'next_follow_up' => $l->next_follow_up?->toDateString(),
            'event_lead_id'  => $l->event_lead_id,
            'event_name'     => $l->event_name,
            'created_at'     => $l->created_at->toIso8601String(),
            'agent'          => $l->user ? ['id' => $l->user->id, 'name' => $l->user->name] : null,
            'vehicle'        => $l->vehicle ? [
                'id'    => $l->vehicle->id,
                'name'  => trim("{$l->vehicle->brand} {$l->vehicle->model} {$l->vehicle->year}"),
                'image' => $l->vehicle->image ? apiFileUrl($l->vehicle->image) : null,
            ] : null,
        ];

        return response()->json([
            'leads'        => $paginated->map($mapLead),
            'total'        => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'stats'        => $stats,
        ]);
    })->name('api.crm.leads.index');

    // ── Detalle de lead CRM ───────────────────────────────
    Route::get('/crm/leads/{id}', function (\Illuminate\Http\Request $request, int $id) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $lead = \App\Lead::with([
            'user:id,name',
            'vehicle:id,brand,model,year,image,price,currency',
            'activities' => fn($q) => $q->with('user:id,name')->orderByDesc('activity_at')->limit(30),
        ])
        ->when($companyId, fn($q) => $q->where('company_id', $companyId))
        ->findOrFail($id);

        return response()->json([
            'id'              => $lead->id,
            'name'            => $lead->name,
            'phone'           => $lead->phone,
            'whatsapp'        => $lead->whatsapp,
            'email'           => $lead->email,
            'status'          => $lead->status,
            'status_label'    => $lead->status_label,
            'priority'        => $lead->priority,
            'source'          => $lead->source,
            'source_label'    => $lead->source_label,
            'interest_type'   => $lead->interest_type,
            'budget_min'      => $lead->budget_min,
            'budget_max'      => $lead->budget_max,
            'budget_currency' => $lead->budget_currency ?? 'CRC',
            'notes'           => $lead->notes,
            'requirements'    => $lead->requirements,
            'next_follow_up'  => $lead->next_follow_up?->toDateString(),
            'first_contact_at'=> $lead->first_contact_at?->toIso8601String(),
            'last_contact_at' => $lead->last_contact_at?->toIso8601String(),
            'event_lead_id'   => $lead->event_lead_id,
            'event_name'      => $lead->event_name,
            'created_at'      => $lead->created_at->toIso8601String(),
            'agent'           => $lead->user ? ['id' => $lead->user->id, 'name' => $lead->user->name] : null,
            'vehicle'         => $lead->vehicle ? [
                'id'    => $lead->vehicle->id,
                'name'  => trim("{$lead->vehicle->brand} {$lead->vehicle->model} {$lead->vehicle->year}"),
                'price' => ($lead->vehicle->currency === 'USD' ? '$' : '₡') . number_format((float)($lead->vehicle->price ?? 0)),
                'image' => $lead->vehicle->image ? apiFileUrl($lead->vehicle->image) : null,
            ] : null,
            'activities' => $lead->activities->map(fn($a) => [
                'id'          => $a->id,
                'type'        => $a->type,
                'type_label'  => $a->type_label,
                'subject'     => $a->subject,
                'description' => $a->description,
                'call_result' => $a->call_result,
                'old_status'  => $a->old_status,
                'new_status'  => $a->new_status,
                'activity_at' => $a->activity_at->toIso8601String(),
                'agent'       => $a->user ? ['id' => $a->user->id, 'name' => $a->user->name] : null,
            ]),
        ]);
    })->name('api.crm.leads.show');

    // ── Crear lead CRM ────────────────────────────────────
    Route::post('/crm/leads', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'email'         => 'nullable|email|max:255',
            'whatsapp'      => 'nullable|string|max:20',
            'source'        => 'nullable|in:website,whatsapp,phone,referral,social_media,walk_in,event,kiosk,qr,quote,other',
            'priority'      => 'nullable|in:low,medium,high,urgent',
            'interest_type' => 'nullable|in:buy,rent,sell,other',
            'notes'         => 'nullable|string|max:2000',
            'vehicle_id'    => 'nullable|exists:properties,id',
        ]);

        $user = $request->user();
        $lead = \App\Lead::create([
            'company_id'       => $user->company_id,
            'user_id'          => $user->id,
            'name'             => $data['name'],
            'phone'            => $data['phone'],
            'email'            => $data['email'] ?? null,
            'whatsapp'         => $data['whatsapp'] ?? null,
            'source'           => $data['source'] ?? 'other',
            'priority'         => $data['priority'] ?? 'medium',
            'interest_type'    => $data['interest_type'] ?? 'buy',
            'vehicle_id'       => $data['vehicle_id'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'status'           => 'new',
            'first_contact_at' => now(),
        ]);

        $lead->logActivity('note', [
            'user_id'     => $user->id,
            'subject'     => 'Lead creado desde app móvil',
            'description' => 'Lead creado manualmente desde la aplicación móvil.',
            'activity_at' => now(),
        ]);

        return response()->json(['success' => true, 'lead_id' => $lead->id], 201);
    })->name('api.crm.leads.store');

    // ── Cambiar estado de lead CRM ────────────────────────
    Route::patch('/crm/leads/{id}/status', function (\Illuminate\Http\Request $request, int $id) {
        $data = $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'note'   => 'nullable|string|max:1000',
        ]);
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $lead = \App\Lead::when($companyId, fn($q) => $q->where('company_id', $companyId))->findOrFail($id);
        $lead->changeStatus($data['status'], $data['note'] ?? null);

        return response()->json(['success' => true]);
    })->name('api.crm.leads.status');

    // ── Registrar actividad en lead CRM ───────────────────
    Route::post('/crm/leads/{id}/activity', function (\Illuminate\Http\Request $request, int $id) {
        $data = $request->validate([
            'type'        => 'required|in:call,email,whatsapp,visit,meeting,note,other',
            'subject'     => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'call_result' => 'nullable|in:answered,no_answer,busy,voicemail,callback_requested',
        ]);
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $lead = \App\Lead::when($companyId, fn($q) => $q->where('company_id', $companyId))->findOrFail($id);

        $lead->logActivity($data['type'], [
            'user_id'     => $user->id,
            'subject'     => $data['subject'],
            'description' => $data['description'] ?? null,
            'call_result' => $data['call_result'] ?? null,
            'activity_at' => now(),
        ]);

        return response()->json(['success' => true]);
    })->name('api.crm.leads.activity');

    // ── Agregar EventLead al CRM ──────────────────────────
    Route::post('/event-leads/{id}/add-to-crm', function (\Illuminate\Http\Request $request, int $id) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $eventLead = \App\Models\EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->findOrFail($id);

        // Check duplicate
        $dup = \App\Lead::where('company_id', $user->company_id)
            ->where(function ($q) use ($eventLead) {
                if ($eventLead->phone) $q->orWhere('phone', $eventLead->phone);
                if ($eventLead->email) $q->orWhere('email', $eventLead->email);
            })->first();

        if ($dup) {
            return response()->json([
                'success' => false, 'duplicate' => true,
                'message' => 'Ya existe un lead en el CRM con este contacto.',
                'lead_id' => $dup->id,
            ], 409);
        }

        $priority = match($eventLead->interest_level) {
            'hot' => 'urgent', 'high' => 'high', 'medium' => 'medium', default => 'low',
        };
        $source = match($eventLead->source) {
            'kiosk' => 'kiosk', 'qr' => 'qr', 'compare' => 'event', 'quote' => 'quote', default => 'event',
        };

        $notes = 'Importado desde Dashboard Evento'
            . ($eventLead->event_name ? " ({$eventLead->event_name})" : '')
            . ". Fuente: {$eventLead->source}. Interés: {$eventLead->interest_level}."
            . ($eventLead->notes ? " Notas: {$eventLead->notes}" : '');

        $lead = \App\Lead::create([
            'company_id'       => $user->company_id,
            'user_id'          => $user->id,
            'property_id'      => $eventLead->property_id,
            'name'             => $eventLead->name,
            'email'            => $eventLead->email,
            'phone'            => $eventLead->phone ?? '',
            'status'           => 'new',
            'source'           => $source,
            'priority'         => $priority,
            'interest_type'    => 'buy',
            'notes'            => $notes,
            'first_contact_at' => $eventLead->created_at,
            'event_name'       => $eventLead->event_name,
            'event_lead_id'    => $eventLead->id,
        ]);

        $lead->logActivity('note', [
            'user_id'     => $user->id,
            'subject'     => 'Importado desde app móvil',
            'description' => "EventLead ID: {$eventLead->id} migrado al CRM.",
            'activity_at' => now(),
        ]);

        return response()->json(['success' => true, 'lead_id' => $lead->id], 201);
    })->name('api.crm.event-leads.add');

    // ── Agregar Cotización al CRM ─────────────────────────
    Route::post('/quotes/{id}/add-to-crm', function (\Illuminate\Http\Request $request, int $id) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $quote = \App\Models\VehicleQuote::with('property')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->findOrFail($id);

        if (!$quote->customer_name && !$quote->customer_phone) {
            return response()->json(['success' => false, 'message' => 'Cotización sin datos del cliente'], 422);
        }

        if ($quote->customer_phone) {
            $dup = \App\Lead::where('company_id', $user->company_id)->where('phone', $quote->customer_phone)->first();
            if ($dup) {
                return response()->json([
                    'success' => false, 'duplicate' => true,
                    'message' => 'Ya existe un lead con este teléfono.',
                    'lead_id' => $dup->id,
                ], 409);
            }
        }

        $vehiclePrice = (float)($quote->vehicle_price ?? 0);
        $vehicleName  = $quote->property ? trim("{$quote->property->brand} {$quote->property->model} {$quote->property->year}") : 'N/A';
        $sym          = ($quote->currency ?? 'CRC') === 'USD' ? '$' : '₡';
        $notes = "Lead desde cotización" . ($quote->event_name ? " ({$quote->event_name})" : '')
            . ". Vehículo: {$vehicleName}."
            . " Cuota: {$sym}" . number_format((float)$quote->monthly_payment)
            . ". Prima: {$quote->down_payment_percent}%. Plazo: {$quote->term_months} meses. Tasa: {$quote->interest_rate}%.";

        $lead = \App\Lead::create([
            'company_id'       => $user->company_id,
            'user_id'          => $user->id,
            'property_id'      => $quote->property_id,
            'name'             => $quote->customer_name ?? 'Sin nombre',
            'email'            => $quote->customer_email,
            'phone'            => $quote->customer_phone ?? '',
            'status'           => 'new',
            'source'           => 'quote',
            'priority'         => 'high',
            'interest_type'    => 'buy',
            'budget_min'       => $vehiclePrice * 0.8,
            'budget_max'       => $vehiclePrice * 1.2,
            'budget_currency'  => $quote->currency ?? 'CRC',
            'notes'            => $notes,
            'first_contact_at' => $quote->created_at,
            'event_name'       => $quote->event_name,
        ]);

        $lead->logActivity('note', [
            'user_id'     => $user->id,
            'subject'     => 'Lead creado desde cotización',
            'description' => $notes,
            'activity_at' => now(),
        ]);

        return response()->json(['success' => true, 'lead_id' => $lead->id], 201);
    })->name('api.crm.quotes.add');

    // ── Listado de cotizaciones (tab Cotizaciones en Eventos) ─
    Route::get('/kiosk/quotes', function (\Illuminate\Http\Request $request) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $quotes = \App\Models\VehicleQuote::with(['property:id,brand,model,year,image', 'capturedBy:id,name'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->latest()
            ->paginate(25);

        return response()->json([
            'quotes' => $quotes->map(fn($q) => [
                'id'                   => $q->id,
                'customer_name'        => $q->customer_name,
                'customer_phone'       => $q->customer_phone,
                'customer_email'       => $q->customer_email,
                'vehicle_price'        => (float)$q->vehicle_price,
                'down_payment'         => (float)$q->down_payment,
                'down_payment_percent' => (float)$q->down_payment_percent,
                'term_months'          => $q->term_months,
                'interest_rate'        => (float)$q->interest_rate,
                'monthly_payment'      => (float)$q->monthly_payment,
                'total_amount'         => (float)$q->total_amount,
                'currency'             => $q->currency ?? 'CRC',
                'event_name'           => $q->event_name,
                'vehicle'              => $q->property ? [
                    'id'    => $q->property->id,
                    'name'  => trim("{$q->property->brand} {$q->property->model} {$q->property->year}"),
                    'image' => $q->property->image ? apiFileUrl($q->property->image) : null,
                ] : null,
                'agent'      => $q->capturedBy ? ['id' => $q->capturedBy->id, 'name' => $q->capturedBy->name] : null,
                'created_at' => $q->created_at->toIso8601String(),
            ]),
            'total'        => $quotes->total(),
            'current_page' => $quotes->currentPage(),
            'last_page'    => $quotes->lastPage(),
        ]);
    })->name('api.crm.quotes.index');

    // ── Editar lead ────────────────────────────────────────────────────────────
    Route::patch('/crm/leads/{id}', function (\Illuminate\Http\Request $request, int $id) {
        $user = $request->user();
        $lead = \App\Lead::where('id', $id)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('company_id', $user->company_id))
            ->firstOrFail();

        $data = $request->validate([
            'name'            => 'sometimes|string|max:150',
            'phone'           => 'sometimes|string|max:30',
            'email'           => 'sometimes|nullable|email|max:150',
            'whatsapp'        => 'sometimes|nullable|string|max:30',
            'priority'        => 'sometimes|in:low,medium,high,urgent',
            'interest_type'   => 'sometimes|in:buy,rent,trade',
            'budget_min'      => 'sometimes|nullable|numeric|min:0',
            'budget_max'      => 'sometimes|nullable|numeric|min:0',
            'budget_currency' => 'sometimes|in:CRC,USD',
            'notes'           => 'sometimes|nullable|string|max:2000',
            'requirements'    => 'sometimes|nullable|string|max:2000',
            'next_follow_up'  => 'sometimes|nullable|date',
        ]);

        $lead->update($data);

        return response()->json(['success' => true, 'lead_id' => $lead->id]);
    })->name('api.crm.leads.update');

    // ── Citas de un lead ───────────────────────────────────────────────────────
    Route::get('/crm/leads/{id}/appointments', function (\Illuminate\Http\Request $request, int $id) {
        $user = $request->user();
        $lead = \App\Lead::where('id', $id)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('company_id', $user->company_id))
            ->firstOrFail();

        $appointments = $lead->appointments()
            ->with('user:id,name')
            ->orderBy('starts_at', 'asc')
            ->get();

        return response()->json([
            'appointments' => $appointments->map(fn($a) => [
                'id'          => $a->id,
                'title'       => $a->title,
                'type'        => $a->type,
                'type_label'  => $a->type_label,
                'status'      => $a->status,
                'status_label'=> $a->status_label,
                'starts_at'   => $a->starts_at->toIso8601String(),
                'ends_at'     => $a->ends_at ? $a->ends_at->toIso8601String() : null,
                'location'    => $a->location,
                'description' => $a->description,
                'outcome'     => $a->outcome,
                'outcome_label' => $a->outcome_label,
                'outcome_notes' => $a->outcome_notes,
                'agent'       => $a->user ? ['id' => $a->user->id, 'name' => $a->user->name] : null,
            ]),
        ]);
    })->name('api.crm.lead.appointments');

    // ── Crear cita ─────────────────────────────────────────────────────────────
    Route::post('/crm/appointments', function (\Illuminate\Http\Request $request) {
        $user = $request->user();

        $data = $request->validate([
            'lead_id'     => 'required|integer',
            'title'       => 'required|string|max:200',
            'type'        => 'required|in:property_visit,vehicle_visit,meeting,call,video_call,signing,other',
            'starts_at'   => 'required|date',
            'ends_at'     => 'nullable|date|after:starts_at',
            'location'    => 'nullable|string|max:300',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify lead belongs to same company
        $lead = \App\Lead::where('id', $data['lead_id'])
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('company_id', $user->company_id))
            ->firstOrFail();

        $appointment = \App\Appointment::create([
            'company_id'  => $user->company_id ?? $lead->company_id,
            'user_id'     => $user->id,
            'lead_id'     => $lead->id,
            'title'       => $data['title'],
            'type'        => $data['type'],
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
            'location'    => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => 'scheduled',
            'client_name'  => $lead->name,
            'client_phone' => $lead->phone,
            'client_email' => $lead->email,
        ]);

        $lead->logActivity('meeting', [
            'user_id'     => $user->id,
            'subject'     => "Cita programada: {$appointment->title}",
            'description' => $data['description'] ?? null,
            'activity_at' => now(),
        ]);

        return response()->json(['success' => true, 'appointment_id' => $appointment->id], 201);
    })->name('api.crm.appointments.store');

    // ── Actualizar estado de cita ──────────────────────────────────────────────
    Route::patch('/crm/appointments/{id}/status', function (\Illuminate\Http\Request $request, int $id) {
        $user = $request->user();
        $appointment = \App\Appointment::where('id', $id)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('company_id', $user->company_id))
            ->firstOrFail();

        $data = $request->validate([
            'status'        => 'required|in:confirmed,completed,cancelled,no_show',
            'outcome'       => 'nullable|in:successful,follow_up_needed,not_interested,pending',
            'outcome_notes' => 'nullable|string|max:1000',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $appointment->update([
            'status'               => $data['status'],
            'outcome'              => $data['outcome'] ?? null,
            'outcome_notes'        => $data['outcome_notes'] ?? null,
            'cancellation_reason'  => $data['cancellation_reason'] ?? null,
        ]);

        // Log activity on lead if completed
        if ($data['status'] === 'completed' && $appointment->lead_id) {
            $lead = \App\Lead::find($appointment->lead_id);
            $lead?->logActivity('visit', [
                'user_id'     => $user->id,
                'subject'     => "Cita completada: {$appointment->title}",
                'description' => $data['outcome_notes'] ?? null,
                'activity_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    })->name('api.crm.appointments.status');

    // ── Agenda: próximas citas ─────────────────────────────────────────────────
    Route::get('/crm/agenda', function (\Illuminate\Http\Request $request) {
        $user      = $request->user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $days  = (int) $request->query('days', 14);
        $days  = max(1, min(90, $days));

        $appointments = \App\Appointment::with(['lead:id,name,phone', 'user:id,name'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->where('starts_at', '>=', now()->startOfDay())
            ->where('starts_at', '<=', now()->addDays($days)->endOfDay())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at', 'asc')
            ->get();

        return response()->json([
            'appointments' => $appointments->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'type'         => $a->type,
                'type_label'   => $a->type_label,
                'status'       => $a->status,
                'status_label' => $a->status_label,
                'starts_at'    => $a->starts_at->toIso8601String(),
                'ends_at'      => $a->ends_at ? $a->ends_at->toIso8601String() : null,
                'location'     => $a->location,
                'lead'         => $a->lead ? ['id' => $a->lead->id, 'name' => $a->lead->name, 'phone' => $a->lead->phone] : null,
                'agent'        => $a->user ? ['id' => $a->user->id, 'name' => $a->user->name] : null,
            ]),
        ]);
    })->name('api.crm.agenda');
});

