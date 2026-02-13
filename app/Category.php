<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'sector_id', 'name', 'slug', 'location', 'facilities',
        'notes', 'features', 'image', 'description', 'status'
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the sector this category belongs to
     */
    public function sector()
    {
        return $this->belongsTo('App\Sector', 'sector_id');
    }

    /**
     * Get the properties for this category
     */
    public function properties()
    {
        return $this->hasMany('App\Properties', 'category_id');
    }

    /**
     * Get the vehicles for this category
     */
    public function vehicles()
    {
        return $this->hasMany('App\Vehicle', 'category_id');
    }
}
