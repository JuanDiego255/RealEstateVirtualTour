<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Company;

class RafflePrize extends Model
{
    protected $fillable = ['company_id','name','emoji','color','quantity','claimed','weight','active'];

    protected $casts = [
        'active'   => 'boolean',
        'quantity' => 'integer',
        'claimed'  => 'integer',
        'weight'   => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('quantity')->orWhereRaw('claimed < quantity');
            });
    }

    public function getRemainingAttribute()
    {
        return $this->quantity === null ? null : max(0, $this->quantity - $this->claimed);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->active && ($this->quantity === null || $this->claimed < $this->quantity);
    }
}
