<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($sub) {
            if (empty($sub->slug)) {
                $sub->slug = static::generateUniqueSlug($sub->name, $sub->category_id);
            }
        });

        static::updating(function ($sub) {
            if ($sub->isDirty('name')) {
                $sub->slug = static::generateUniqueSlug($sub->name, $sub->category_id, $sub->id);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function properties()
    {
        return $this->hasMany(Properties::class, 'subcategory_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function generateUniqueSlug(string $name, int $categoryId, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug)->where('category_id', $categoryId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('slug', $slug)->where('category_id', $categoryId);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }
}
