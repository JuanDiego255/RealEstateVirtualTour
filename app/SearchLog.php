<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = [
        'user_id',
        'search_term',
        'filters',
        'results_count',
        'ip_address',
        'searched_at'
    ];

    protected $casts = [
        'filters' => 'array',
        'searched_at' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registrar una búsqueda
     */
    public static function logSearch($searchTerm, $filters, $resultsCount)
    {
        return self::create([
            'user_id' => auth()->id(),
            'search_term' => $searchTerm,
            'filters' => $filters,
            'results_count' => $resultsCount,
            'ip_address' => request()->ip(),
            'searched_at' => now(),
        ]);
    }
}
