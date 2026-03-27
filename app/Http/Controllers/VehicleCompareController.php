<?php

namespace App\Http\Controllers;

use App\Category;
use App\Sector;
use App\Subcategory;
use App\Vehicle;
use Illuminate\Http\Request;

class VehicleCompareController extends Controller
{
    /**
     * Mostrar la herramienta de comparación de vehículos
     */
    public function index()
    {
        // Obtener el sector automotriz
        $sector = Sector::where('slug', 'sector-automotriz')
            ->orWhere('name', 'like', '%automotriz%')
            ->first();

        if (!$sector) {
            abort(404, 'El sector automotriz no está disponible.');
        }

        // Obtener las sucursales (categorías) del sector automotriz con vehículos activos
        $categories = Category::where('sector_id', $sector->id)
            ->where('status', true)
            ->whereHas('vehicles', function ($q) {
                $q->where('status', true);
            })
            ->with(['subcategories' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        return view('frontend.vehicle-compare', compact('sector', 'categories'));
    }

    /**
     * API: Obtener subcategorías de una categoría
     */
    public function getSubcategories(Category $category)
    {
        $subcategories = $category->subcategories()
            ->where('is_active', true)
            ->whereHas('vehicles', function ($q) {
                $q->where('status', true);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json($subcategories);
    }

    /**
     * API: Obtener vehículos de una categoría (y opcionalmente subcategoría)
     */
    public function getVehicles(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'exclude_ids' => 'nullable|array',
            'search' => 'nullable|string|max:100',
        ]);

        $query = Vehicle::where('category_id', $request->category_id)
            ->where('status', true);

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('exclude_ids')) {
            $query->whereNotIn('id', $request->exclude_ids);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->with('subcategory:id,name')
            ->orderBy('brand')
            ->orderBy('model')
            ->orderBy('year', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $this->buildVehicleName($v->brand, $v->model, $v->year),
                    'brand' => $v->brand,
                    'model' => $v->model,
                    'year' => $v->year,
                    'price' => $v->price,
                    'price_formatted' => '₡' . number_format($v->price, 0, ',', '.'),
                    'image' => $v->image ? route('file', $v->image) : url('images/producto-sin-imagen.PNG'),
                    'subcategory' => $v->subcategory ? $v->subcategory->name : null,
                    'condition' => $v->condition,
                    'condition_label' => $this->getConditionLabel($v->condition),
                ];
            });

        return response()->json($vehicles);
    }

    /**
     * API: Obtener detalle completo de un vehículo para comparación
     */
    public function getVehicleDetail(Vehicle $vehicle)
    {
        $vehicle->load(['category:id,name,slug', 'subcategory:id,name', 'scenes']);

        $hasVirtualTour = $vehicle->scenes->count() > 0;

        return response()->json([
            'id' => $vehicle->id,
            'name' => $this->buildVehicleName($vehicle->brand, $vehicle->model, $vehicle->year),
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => (int) $vehicle->year,
            'color' => $vehicle->color,
            'price' => (float) $vehicle->price,
            'price_formatted' => '₡' . number_format($vehicle->price, 0, ',', '.'),
            'image' => $vehicle->image ? route('file', $vehicle->image) : url('images/producto-sin-imagen.PNG'),
            'category' => $vehicle->category ? $vehicle->category->name : null,
            'subcategory' => $vehicle->subcategory ? $vehicle->subcategory->name : null,

            // Especificaciones técnicas
            'specs' => [
                'mileage_km' => [
                    'value' => (int) $vehicle->mileage_km,
                    'label' => 'Kilometraje',
                    'unit' => 'km',
                    'icon' => 'fa-tachometer-alt',
                    'comparison' => 'lower_better', // menor es mejor
                ],
                'engine_cc' => [
                    'value' => (int) $vehicle->engine_cc,
                    'label' => 'Motor',
                    'unit' => 'cc',
                    'icon' => 'fa-cog',
                    'comparison' => 'neutral',
                ],
                'fuel_tank_capacity' => [
                    'value' => (float) $vehicle->fuel_tank_capacity,
                    'label' => 'Tanque',
                    'unit' => 'L',
                    'icon' => 'fa-gas-pump',
                    'comparison' => 'higher_better', // mayor es mejor
                ],
                'fuel_type' => [
                    'value' => $vehicle->fuel_type,
                    'label' => 'Combustible',
                    'unit' => '',
                    'icon' => 'fa-fire',
                    'comparison' => 'neutral',
                ],
                'doors' => [
                    'value' => (int) $vehicle->doors,
                    'label' => 'Puertas',
                    'unit' => '',
                    'icon' => 'fa-door-open',
                    'comparison' => 'neutral',
                ],
                'passengers' => [
                    'value' => (int) $vehicle->passengers,
                    'label' => 'Pasajeros',
                    'unit' => '',
                    'icon' => 'fa-users',
                    'comparison' => 'higher_better',
                ],
                'transmission' => [
                    'value' => $vehicle->transmission,
                    'label' => 'Transmisión',
                    'unit' => '',
                    'icon' => 'fa-cogs',
                    'comparison' => 'neutral',
                ],
                'drivetrain' => [
                    'value' => $vehicle->drivetrain,
                    'label' => 'Tracción',
                    'unit' => '',
                    'icon' => 'fa-car-side',
                    'comparison' => 'neutral',
                ],
                'tires' => [
                    'value' => $vehicle->tires,
                    'label' => 'Llantas',
                    'unit' => '',
                    'icon' => 'fa-circle',
                    'comparison' => 'neutral',
                ],
                'condition' => [
                    'value' => $vehicle->condition,
                    'label' => 'Condición',
                    'unit' => '',
                    'icon' => 'fa-star',
                    'comparison' => 'neutral',
                    'display_value' => $this->getConditionLabel($vehicle->condition),
                ],
                'plate' => [
                    'value' => $vehicle->plate,
                    'label' => 'Placa',
                    'unit' => '',
                    'icon' => 'fa-id-card',
                    'comparison' => 'neutral',
                ],
            ],

            // Tour virtual
            'has_virtual_tour' => $hasVirtualTour,
            'virtual_tour_url' => $hasVirtualTour ? route('virtual-tour', $vehicle->id) : null,
        ]);
    }

    /**
     * API: Comparar múltiples vehículos
     */
    public function compare(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array|min:2|max:4',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $vehicles = collect();
        foreach ($request->vehicle_ids as $id) {
            $vehicle = Vehicle::find($id);
            if ($vehicle) {
                $vehicles->push($this->getVehicleDetail($vehicle)->getData());
            }
        }

        // Calcular comparaciones (mejor/peor valor para cada especificación)
        $comparison = $this->calculateComparison($vehicles);

        return response()->json([
            'vehicles' => $vehicles,
            'comparison' => $comparison,
        ]);
    }

    /**
     * Calcular cuál vehículo tiene el mejor/peor valor para cada especificación
     */
    private function calculateComparison($vehicles)
    {
        if ($vehicles->count() < 2) {
            return [];
        }

        $comparison = [];
        $specKeys = ['mileage_km', 'engine_cc', 'fuel_tank_capacity', 'doors', 'passengers', 'price'];

        foreach ($specKeys as $key) {
            $values = [];
            foreach ($vehicles as $vehicle) {
                $value = $key === 'price'
                    ? $vehicle->price
                    : ($vehicle->specs->{$key}->value ?? null);

                if ($value !== null && $value !== '' && is_numeric($value)) {
                    $values[$vehicle->id] = (float) $value;
                }
            }

            if (count($values) >= 2) {
                $comparisonType = $key === 'price' ? 'lower_better' :
                    ($vehicles->first()->specs->{$key}->comparison ?? 'neutral');

                if ($comparisonType === 'lower_better') {
                    $comparison[$key] = [
                        'best' => array_keys($values, min($values))[0],
                        'worst' => array_keys($values, max($values))[0],
                    ];
                } elseif ($comparisonType === 'higher_better') {
                    $comparison[$key] = [
                        'best' => array_keys($values, max($values))[0],
                        'worst' => array_keys($values, min($values))[0],
                    ];
                }
            }
        }

        return $comparison;
    }

    /**
     * Construir nombre del vehículo evitando duplicación del año
     */
    private function buildVehicleName($brand, $model, $year)
    {
        $name = trim($brand . ' ' . $model);

        // Solo agregar el año si no está ya incluido en el modelo
        if ($year && !preg_match('/\b' . preg_quote($year, '/') . '\b/', $model)) {
            $name .= ' ' . $year;
        }

        return $name;
    }

    /**
     * Obtener etiqueta de condición
     */
    private function getConditionLabel($condition)
    {
        $labels = [
            'new' => 'Nuevo',
            'used' => 'Usado',
            'certified' => 'Certificado',
            'semi-new' => 'Semi-nuevo',
        ];

        return $labels[$condition] ?? ucfirst($condition ?? 'N/A');
    }

    /**
     * Generar PDF de comparación
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array|min:2|max:4',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $vehicles = Vehicle::whereIn('id', $request->vehicle_ids)
            ->with(['category', 'subcategory'])
            ->get();

        // Devolver vista para impresión
        return view('frontend.vehicle-compare-print', compact('vehicles'));
    }
}
