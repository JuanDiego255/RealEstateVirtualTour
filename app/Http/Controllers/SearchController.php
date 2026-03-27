<?php

namespace App\Http\Controllers;

use App\Properties;
use App\Sector;
use App\Category;
use App\SearchLog;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Vista principal de búsqueda avanzada
     */
    public function index(Request $request)
    {
        // Cargar opciones para filtros
        $sectors = Sector::where('status', true)
            ->withCount('categories')
            ->orderBy('name')
            ->get();

        $categories = Category::where('status', true)
            ->with('sector')
            ->orderBy('name')
            ->get();

        $propertyTypes = [
            'house' => 'Casa',
            'apartment' => 'Apartamento',
            'land' => 'Lote/Terreno',
            'vehicle' => 'Vehículo',
            'commercial' => 'Comercial',
            'other' => 'Otro',
        ];

        $statuses = [
            'available' => 'Disponible',
            'negotiating' => 'En negociación',
            'reserved' => 'Reservado',
        ];

        // Construir query base
        $query = Properties::query()
            ->with(['category.sector', 'subcategory', 'user'])
            ->published()
            ->available();

        // Aplicar filtros si existen
        $filters = [];
        $hasFilters = false;

        // Término de búsqueda
        if ($request->filled('q')) {
            $search = $request->q;
            $filters['q'] = $search;
            $hasFilters = true;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Filtro por sector
        if ($request->filled('sector_id')) {
            $filters['sector_id'] = $request->sector_id;
            $hasFilters = true;
            $query->inSector($request->sector_id);
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $filters['category_id'] = $request->category_id;
            $hasFilters = true;
            $query->where('category_id', $request->category_id);
        }

        // Filtro por tipo de propiedad
        if ($request->filled('property_type')) {
            $filters['property_type'] = $request->property_type;
            $hasFilters = true;
            $query->where('property_type', $request->property_type);
        }

        // Rango de precios
        if ($request->filled('min_price')) {
            $filters['min_price'] = $request->min_price;
            $hasFilters = true;
            $query->whereRaw("CAST(REPLACE(REPLACE(price, ',', ''), '.', '') AS UNSIGNED) >= ?", [$request->min_price]);
        }

        if ($request->filled('max_price')) {
            $filters['max_price'] = $request->max_price;
            $hasFilters = true;
            $query->whereRaw("CAST(REPLACE(REPLACE(price, ',', ''), '.', '') AS UNSIGNED) <= ?", [$request->max_price]);
        }

        // Filtros para propiedades inmobiliarias
        if ($request->filled('min_rooms')) {
            $filters['min_rooms'] = $request->min_rooms;
            $hasFilters = true;
            $query->whereRaw("CAST(rooms AS UNSIGNED) >= ?", [$request->min_rooms]);
        }

        if ($request->filled('min_bathrooms')) {
            $filters['min_bathrooms'] = $request->min_bathrooms;
            $hasFilters = true;
            $query->whereRaw("CAST(bathrooms AS UNSIGNED) >= ?", [$request->min_bathrooms]);
        }

        if ($request->filled('min_construction')) {
            $filters['min_construction'] = $request->min_construction;
            $hasFilters = true;
            $query->whereRaw("CAST(REPLACE(construction, ',', '') AS UNSIGNED) >= ?", [$request->min_construction]);
        }

        // Ubicación
        if ($request->filled('location')) {
            $filters['location'] = $request->location;
            $hasFilters = true;
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Estado
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
            $hasFilters = true;
            $query->where('status', $request->status);
        }

        // Solo con tour virtual
        if ($request->filled('has_tour') && $request->has_tour) {
            $filters['has_tour'] = true;
            $hasFilters = true;
            $query->withTour();
        }

        // Solo destacadas
        if ($request->filled('featured') && $request->featured) {
            $filters['featured'] = true;
            $hasFilters = true;
            $query->featured();
        }

        // Solo acepta comisión
        if ($request->filled('accepts_commission') && $request->accepts_commission) {
            $filters['accepts_commission'] = true;
            $hasFilters = true;
            $query->acceptsCommission();
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'newest');
        $filters['sort_by'] = $sortBy;

        switch ($sortBy) {
            case 'price_asc':
                $query->orderByRaw("CAST(REPLACE(REPLACE(price, ',', ''), '.', '') AS UNSIGNED) ASC");
                break;
            case 'price_desc':
                $query->orderByRaw("CAST(REPLACE(REPLACE(price, ',', ''), '.', '') AS UNSIGNED) DESC");
                break;
            case 'most_viewed':
                $query->orderBy('views_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Ejecutar query con paginación
        $properties = $query->paginate(12)->appends($request->all());

        // Registrar búsqueda
        if ($hasFilters) {
            SearchLog::logSearch(
                $request->q,
                $filters,
                $properties->total()
            );
        }

        return view('search.index', compact(
            'properties',
            'sectors',
            'categories',
            'propertyTypes',
            'statuses',
            'filters',
            'hasFilters'
        ));
    }
}
