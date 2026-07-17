<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Properties;
use Illuminate\Http\Request;

/**
 * API público (autenticado por token api_clients / middleware tour.api) para que
 * sistemas externos consuman los VEHÍCULOS en general — tengan o no tour virtual.
 * Soporta filtros por marca, modelo, año y color, además de un endpoint de
 * opciones (facets) para construir los dropdowns de filtrado del lado consumidor.
 */
class VehicleApiController extends Controller
{
    /**
     * Estados que NO se exponen al exterior.
     */
    private const HIDDEN_STATUSES = ['inactive'];

    /**
     * GET /api/v1/vehicles
     * Listado paginado y filtrable de vehículos.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 50));

        $query = $this->baseQuery()
            ->with('category:id,name,slug')
            ->withCount(['scenes' => function ($s) {
                $s->where('status', '1');
            }]);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->input('sort'));

        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => collect($paginator->items())->map(function ($v) {
                return $this->formatVehicleCard($v);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/vehicles/{id}
     * Detalle completo de un vehículo: especificaciones, galería y escenas del tour.
     */
    public function show($id)
    {
        $vehicle = $this->baseQuery()
            ->with(['category:id,name,slug', 'images', 'scenes'])
            ->withCount(['scenes' => function ($s) {
                $s->where('status', '1');
            }])
            ->find($id);

        if (!$vehicle) {
            return response()->json(['message' => 'Vehículo no encontrado o no disponible.'], 404);
        }

        $data = $this->formatVehicleCard($vehicle);

        // Galería de imágenes adicionales (URLs absolutas)
        $data['images'] = $vehicle->images->map(function ($img) {
            return [
                'url'        => route('file', $img->image),
                'caption'    => $img->caption,
                'is_primary' => (bool) $img->is_primary,
            ];
        })->values();

        // Escenas del tour virtual (si tiene)
        $data['scenes'] = $vehicle->scenes
            ->filter(fn($scene) => (string) $scene->status === '1')
            ->map(function ($scene) {
                $thumbRef = $scene->image_ref ?: $scene->image;
                return [
                    'id'        => $scene->id,
                    'title'     => $scene->title,
                    'type'      => $scene->type,
                    'thumbnail' => $thumbRef ? route('file', $thumbRef) : null,
                ];
            })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/vehicles/filters
     * Opciones disponibles para construir los filtros (marca, modelo, año, color…).
     * Acepta marca/modelo como parámetros para narrowing en cascada.
     */
    public function filters(Request $request)
    {
        $brand = $request->input('brand');
        $model = $request->input('model');

        // Marcas: todas
        $brands = $this->baseQuery()
            ->whereNotNull('brand')->where('brand', '!=', '')
            ->distinct()->orderBy('brand')->pluck('brand');

        // Modelos: narrowed por marca si se envía
        $models = $this->baseQuery()
            ->when($brand, fn($q) => $q->where('brand', $brand))
            ->whereNotNull('model')->where('model', '!=', '')
            ->distinct()->orderBy('model')->pluck('model');

        // Años: narrowed por marca+modelo si se envían
        $years = $this->baseQuery()
            ->when($brand, fn($q) => $q->where('brand', $brand))
            ->when($model, fn($q) => $q->where('model', $model))
            ->whereNotNull('year')->where('year', '!=', '')
            ->distinct()->orderByDesc('year')->pluck('year');

        // Colores: todos
        $colors = $this->baseQuery()
            ->whereNotNull('color')->where('color', '!=', '')
            ->distinct()->orderBy('color')->pluck('color');

        // Otros facets útiles
        $transmissions = $this->distinctValues('transmission');
        $fuelTypes     = $this->distinctValues('fuel_type');
        $conditions    = $this->distinctValues('condition');

        // Rango de precios
        $priceStats = $this->baseQuery()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return response()->json([
            'brands'        => $brands->values(),
            'models'        => $models->values(),
            'years'         => $years->map(fn($y) => (int) $y)->values(),
            'colors'        => $colors->values(),
            'transmissions' => $transmissions,
            'fuel_types'    => $fuelTypes,
            'conditions'    => $conditions,
            'price_range'   => [
                'min' => $priceStats && $priceStats->min_price !== null ? (float) $priceStats->min_price : null,
                'max' => $priceStats && $priceStats->max_price !== null ? (float) $priceStats->max_price : null,
            ],
        ]);
    }

    /* ───────────────────────── Helpers ───────────────────────── */

    /**
     * Query base: solo vehículos visibles al exterior.
     */
    private function baseQuery()
    {
        return Properties::query()
            ->vehicles()
            ->whereNotIn('status', self::HIDDEN_STATUSES);
    }

    /**
     * Valores distintos de una columna sobre la query base.
     */
    private function distinctValues(string $column)
    {
        return $this->baseQuery()
            ->whereNotNull($column)->where($column, '!=', '')
            ->distinct()->orderBy($column)->pluck($column)->values();
    }

    /**
     * Aplica los filtros del request a la query.
     */
    private function applyFilters($query, Request $request): void
    {
        // Filtros multi-valor (aceptan valor único o lista separada por coma / array)
        foreach (['brand', 'model', 'color', 'transmission', 'fuel_type', 'condition'] as $field) {
            $this->applyInFilter($query, $field, $request->input($field));
        }

        // Año: exacto (multi) o rango
        $this->applyInFilter($query, 'year', $request->input('year'));
        if ($request->filled('year_min')) {
            $query->where('year', '>=', (int) $request->input('year_min'));
        }
        if ($request->filled('year_max')) {
            $query->where('year', '<=', (int) $request->input('year_max'));
        }

        // Rango de precio
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        // Solo con tour virtual
        if ($request->boolean('has_tour')) {
            $query->whereHas('scenes', fn($s) => $s->where('status', '1'));
        }

        // Categoría
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Búsqueda de texto libre
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('plate', 'like', "%{$q}%");
            });
        }
    }

    /**
     * Aplica un filtro IN aceptando: array, string separada por coma, o valor único.
     */
    private function applyInFilter($query, string $column, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $values = is_array($value) ? $value : explode(',', (string) $value);
        $values = array_values(array_filter(array_map('trim', $values), fn($v) => $v !== ''));
        if (!empty($values)) {
            $query->whereIn($column, $values);
        }
    }

    /**
     * Ordenamiento.
     */
    private function applySort($query, ?string $sort): void
    {
        switch ($sort) {
            case 'price_asc':  $query->orderBy('price'); break;
            case 'price_desc': $query->orderByDesc('price'); break;
            case 'year_desc':  $query->orderByDesc('year'); break;
            case 'year_asc':   $query->orderBy('year'); break;
            default:
                $query->orderByDesc('is_featured')->orderByDesc('id');
        }
    }

    /**
     * Formatea la tarjeta de un vehículo. URLs absolutas listas para <img src>.
     */
    private function formatVehicleCard(Properties $v): array
    {
        $hasTour = ($v->scenes_count ?? 0) > 0;
        $title   = trim(($v->brand ?? '') . ' ' . ($v->model ?? '') . ' ' . ($v->year ?? ''));

        return [
            'id'              => $v->id,
            'name'            => $v->name,
            'title'           => $title !== '' ? $title : $v->name,
            'code'            => $v->code,
            'brand'           => $v->brand,
            'model'           => $v->model,
            'year'            => $v->year !== null ? (int) $v->year : null,
            'color'           => $v->color,
            'mileage_km'      => $v->mileage_km,
            'fuel_type'       => $v->fuel_type,
            'transmission'    => $v->transmission,
            'condition'       => $v->condition,
            'doors'           => $v->doors,
            'passengers'      => $v->passengers,
            'engine_cc'       => $v->engine_cc,
            'drivetrain'      => $v->drivetrain,
            'plate'           => $v->plate,
            'price'           => $v->price !== null ? (float) $v->price : null,
            'currency'        => $v->currency,
            'formatted_price' => $v->formatted_price,
            'description'     => $v->description,
            'location'        => $v->location,
            'status'          => $v->status,
            'status_name'     => $v->status_name,
            'main_image'      => $v->image_url, // accessor: route('file', image) o placeholder
            'category'        => $v->category ? [
                'id'   => $v->category->id,
                'name' => $v->category->name,
                'slug' => $v->category->slug,
            ] : null,
            'has_virtual_tour' => $hasTour,
            'scenes_count'     => $v->scenes_count ?? 0,
            'tour_url'         => $hasTour ? route('virtual-tour', $v->id) : null,
            'share_url'        => $v->share_url,
        ];
    }
}
