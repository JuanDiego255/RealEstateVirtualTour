<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'image', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Auto-generate slug from name
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($sector) {
            if (empty($sector->slug)) {
                $sector->slug = Str::slug($sector->name);
            }
        });

        static::updating(function ($sector) {
            if ($sector->isDirty('name')) {
                $sector->slug = Str::slug($sector->name);
            }
        });
    }

    /**
     * Get the categories for this sector
     */
    public function categories()
    {
        return $this->hasMany('App\Category', 'sector_id');
    }
}
