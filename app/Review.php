<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'sale_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Relación con el usuario que da la calificación
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Relación con el usuario que recibe la calificación
     */
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * Relación con la venta (opcional)
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Boot del modelo para actualizar ratings automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->reviewee->updateAverageRating();
        });

        static::updated(function ($review) {
            $review->reviewee->updateAverageRating();
        });

        static::deleted(function ($review) {
            $review->reviewee->updateAverageRating();
        });
    }

    /**
     * Validar que el rating esté entre 1 y 5
     */
    public function setRatingAttribute($value)
    {
        $this->attributes['rating'] = max(1, min(5, (int)$value));
    }

    /**
     * Scope para reviews de un usuario específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('reviewee_id', $userId);
    }

    /**
     * Scope para reviews con comentario
     */
    public function scopeWithComment($query)
    {
        return $query->whereNotNull('comment')->where('comment', '!=', '');
    }
}
