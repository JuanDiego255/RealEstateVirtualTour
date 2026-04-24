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
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if (!\Illuminate\Support\Facades\Auth::attempt($credentials)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    // Stateless token: deterministic HMAC so no extra schema/package needed
    $token = hash_hmac('sha256', $user->email . '|' . $user->id, config('app.key'));

    return response()->json(['token' => $token, 'user' => ['name' => $user->name, 'email' => $user->email]]);
})->name('api.login');

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
