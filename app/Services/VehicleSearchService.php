<?php

namespace App\Services;

use App\Properties;
use Illuminate\Support\Facades\Log;

/**
 * Traduce lo que pide el modelo (herramientas) a consultas del catálogo de
 * vehículos (BD local: properties con property_type = vehicle). El bot SOLO
 * afirma lo que devuelve este servicio: nunca inventa precios ni existencias.
 */
class VehicleSearchService
{
    /** Estados que no se muestran nunca. */
    private const HIDDEN = ['inactive'];

    /** Mapa de estado de propiedad → estado de vehículo para el cliente. */
    private const STATUS_MAP = [
        'available'   => 'disponible',
        'negotiating' => 'apartado',
        'reserved'    => 'apartado',
        'sold'        => 'vendido',
        'rented'      => 'vendido',
    ];

    public function __construct(private int $companyId)
    {
    }

    private function base()
    {
        return Properties::query()
            ->vehicles()
            // Limita a la empresa del bot vía la categoría de la propiedad.
            ->whereHas('category', fn($q) => $q->where('company_id', $this->companyId))
            ->whereNotIn('status', self::HIDDEN);
    }

    /**
     * Búsqueda de vehículos. Devuelve resúmenes para el chat.
     */
    public function search(array $filters, int $limit = 3): array
    {
        $query = $this->base();

        $text = trim((string) ($filters['query'] ?? ''));
        $tokens = $this->tokens($text);

        if (!empty($filters['brand']))        $query->where('brand', 'like', '%' . $filters['brand'] . '%');
        if (!empty($filters['model']))        $query->where('model', 'like', '%' . $filters['model'] . '%');
        if (!empty($filters['year_min']))     $query->where('year', '>=', (int) $filters['year_min']);
        if (!empty($filters['year_max']))     $query->where('year', '<=', (int) $filters['year_max']);
        if (!empty($filters['price_min']))    $query->where('price', '>=', (float) $filters['price_min']);
        if (!empty($filters['price_max']))    $query->where('price', '<=', (float) $filters['price_max']);
        if (!empty($filters['transmission'])) $query->where('transmission', 'like', '%' . $filters['transmission'] . '%');
        if (!empty($filters['fuel_type']))    $query->where('fuel_type', 'like', '%' . $filters['fuel_type'] . '%');
        if (!empty($filters['max_mileage']))  $query->whereRaw('CAST(mileage_km AS UNSIGNED) <= ?', [(int) $filters['max_mileage']]);

        if (!empty($tokens)) {
            $query->where(function ($q) use ($tokens) {
                foreach ($tokens as $t) {
                    $q->orWhere('brand', 'like', "%{$t}%")
                        ->orWhere('model', 'like', "%{$t}%")
                        ->orWhere('name', 'like', "%{$t}%")
                        ->orWhere('description', 'like', "%{$t}%");
                }
            });
        }

        $results = $query->orderByDesc('is_featured')->orderByDesc('id')->limit(max(1, $limit))->get();

        // Fallback: si la búsqueda estricta no devolvió nada, aflojar a solo texto/marca.
        if ($results->isEmpty() && (!empty($tokens) || !empty($filters['brand']))) {
            $loose = $this->base();
            $loose->where(function ($q) use ($tokens, $filters) {
                foreach ($tokens as $t) {
                    $q->orWhere('brand', 'like', "%{$t}%")->orWhere('model', 'like', "%{$t}%");
                }
                if (!empty($filters['brand'])) $q->orWhere('brand', 'like', '%' . $filters['brand'] . '%');
            });
            $results = $loose->limit(max(1, $limit))->get();
        }

        $this->logQuery('search_vehicles', $filters, $results->count());

        return $results->map(fn($v) => $this->summary($v))->all();
    }

    /**
     * Ficha completa de un vehículo.
     */
    public function detail(int $id): ?array
    {
        $v = $this->base()->with('images')->find($id);
        $this->logQuery('get_vehicle_detail', ['id' => $id], $v ? 1 : 0);
        if (!$v) {
            return null;
        }

        $data = $this->summary($v);
        $data['description'] = $v->description;
        $data['doors']       = $v->doors;
        $data['passengers']  = $v->passengers;
        $data['engine_cc']   = $v->engine_cc;
        $data['drivetrain']  = $v->drivetrain;
        $data['condition']   = $v->condition;
        $data['color']       = $v->color;
        $data['images']      = $v->images->take(4)->map(fn($img) => route('file', $img->image))->values()->all();

        return $data;
    }

    /**
     * Estado de un vehículo + alternativas si no está disponible.
     */
    public function status(int $id): array
    {
        $v = $this->base()->find($id);
        $this->logQuery('check_vehicle_status', ['id' => $id], $v ? 1 : 0);

        if (!$v) {
            return ['status' => 'no_encontrado', 'alternatives' => []];
        }

        $status = self::STATUS_MAP[$v->status] ?? 'disponible';
        $alternatives = [];

        if ($status !== 'disponible') {
            $alt = $this->base()
                ->where('id', '!=', $v->id)
                ->whereIn('status', ['available', 'negotiating'])
                ->when($v->brand, fn($q) => $q->where('brand', $v->brand))
                ->orderByRaw('ABS(COALESCE(price,0) - ?) asc', [(float) $v->price])
                ->limit(3)->get();
            $alternatives = $alt->map(fn($a) => $this->summary($a))->all();
        }

        return ['status' => $status, 'vehicle' => $this->summary($v), 'alternatives' => $alternatives];
    }

    /**
     * Resumen de un vehículo para el chat.
     */
    private function summary(Properties $v): array
    {
        $title = trim(($v->brand ?? '') . ' ' . ($v->model ?? '') . ' ' . ($v->year ?? ''));
        return [
            'id'           => $v->id,
            'title'        => $title !== '' ? $title : $v->name,
            'brand'        => $v->brand,
            'model'        => $v->model,
            'year'         => $v->year ? (int) $v->year : null,
            'price'        => $v->formatted_price,
            'price_raw'    => $v->price !== null ? (float) $v->price : null,
            'currency'     => $v->currency,
            'mileage_km'   => $v->mileage_km,
            'transmission' => $v->transmission,
            'fuel_type'    => $v->fuel_type,
            'color'        => $v->color,
            'status'       => self::STATUS_MAP[$v->status] ?? 'disponible',
            'location'     => $v->location,
            'main_image'   => $v->image ? route('file', $v->image) : null,
            'tour_url'     => ($v->has_virtual_tour || $v->scenes()->count() > 0) ? route('virtual-tour', $v->id) : null,
        ];
    }

    /* ── Helpers de texto ── */

    private function tokens(string $text): array
    {
        $text = mb_strtolower(trim($text));
        if ($text === '') {
            return [];
        }
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stop = ['de', 'la', 'el', 'un', 'una', 'con', 'para', 'que', 'tienen', 'tenes', 'busco', 'quiero', 'auto', 'carro', 'vehiculo'];
        $tokens = [];
        foreach ($parts as $p) {
            if (mb_strlen($p) < 2 || in_array($p, $stop, true)) {
                continue;
            }
            $tokens[] = $this->stem($p);
        }
        return array_values(array_unique($tokens));
    }

    private function stem(string $word): string
    {
        // Quita plurales simples en español.
        return preg_replace('/(es|s)$/u', '', $word) ?: $word;
    }

    private function logQuery(string $tool, array $args, int $found): void
    {
        Log::channel('whatsapp')->info('tool.' . $tool, [
            'company_id' => $this->companyId,
            'args'       => $args,
            'found'      => $found,
        ]);
    }
}
