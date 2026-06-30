<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Properties;
use Illuminate\Http\Request;

/**
 * API público (autenticado por token) para que sistemas externos
 * (ej. el portal Real Estate) consuman los tours virtuales de PROPIEDADES.
 * Los vehículos quedan excluidos vía scopeRealEstate().
 */
class TourApiController extends Controller
{
    /**
     * GET /api/v1/tours
     * Listado paginado y filtrable de tours consumibles.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 50));

        $query = Properties::query()
            ->realEstate()
            ->apiConsumable()
            // debe tener al menos una escena activa para ser un tour visible
            ->whereHas('scenes', function ($s) {
                $s->where('status', '1');
            })
            ->with('category:id,name,slug')
            ->withCount(['scenes' => function ($s) {
                $s->where('status', '1');
            }])
            ->orderByDesc('is_featured')
            ->orderByDesc('id');

        // Búsqueda por texto
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            });
        }

        // Filtro por categoría
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filtro por sector (a través de la categoría)
        if ($sectorId = $request->input('sector_id')) {
            $query->inSector($sectorId);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(function ($p) {
                return $this->formatTourCard($p);
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
     * GET /api/v1/tours/{id}
     * Detalle de un tour con sus escenas y miniaturas.
     */
    public function show($id)
    {
        $property = Properties::query()
            ->realEstate()
            ->apiConsumable()
            ->with(['category:id,name,slug', 'scenes'])
            ->withCount(['scenes' => function ($s) {
                $s->where('status', '1');
            }])
            ->find($id);

        if (!$property) {
            return response()->json([
                'message' => 'Tour no encontrado o no disponible.',
            ], 404);
        }

        $data = $this->formatTourCard($property);

        $data['scenes'] = $property->scenes
            ->filter(function ($scene) {
                // Solo escenas activas (status '1' / 1)
                return (string) $scene->status === '1';
            })
            ->map(function ($scene) {
                $thumbRef = $scene->image_ref ?: $scene->image;
                return [
                    'id'        => $scene->id,
                    'title'     => $scene->title,
                    'type'      => $scene->type,
                    'thumbnail' => $thumbRef ? route('file', $thumbRef) : null,
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Formatea los datos de tarjeta de un tour. URLs absolutas listas para <img src>.
     */
    private function formatTourCard(Properties $p): array
    {
        return [
            'id'              => $p->id,
            'name'            => $p->name,
            'code'            => $p->code,
            'type'            => $p->property_type,
            'type_name'       => $p->property_type_name,
            'description'     => $p->description,
            'price'           => $p->price !== null ? (float) $p->price : null,
            'currency'        => $p->currency,
            'formatted_price' => $p->formatted_price,
            'location'        => $p->location,
            'address'         => $p->address,
            'latitude'        => $p->latitude !== null ? (float) $p->latitude : null,
            'longitude'       => $p->longitude !== null ? (float) $p->longitude : null,
            'main_image'      => $p->image_url, // accessor: route('file', image) o placeholder
            'category'        => $p->category ? [
                'id'   => $p->category->id,
                'name' => $p->category->name,
                'slug' => $p->category->slug,
            ] : null,
            'scenes_count'    => $p->scenes_count ?? $p->scenes()->count(),
            'tour_url'        => route('virtual-tour', $p->id),
            'share_url'       => $p->share_url,
        ];
    }
}
