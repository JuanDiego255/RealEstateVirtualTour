<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentReward extends Model
{
    protected $table = 'agent_rewards';

    protected $fillable = [
        'name', 'description', 'min_conversions', 'max_conversions',
        'reward_type', 'reward_value', 'reward_currency', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'reward_value'    => 'decimal:2',
        'min_conversions' => 'integer',
        'max_conversions' => 'integer',
    ];

    public function grants()
    {
        return $this->hasMany(AgentRewardGrant::class, 'reward_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public static function types(): array
    {
        return [
            'bonus'             => ['label' => 'Bono en efectivo',     'icon' => 'fa-money',       'prefix' => '₡'],
            'commission_percent'=> ['label' => 'Comisión extra (%)',   'icon' => 'fa-percent',     'prefix' => '%'],
            'gift'              => ['label' => 'Regalo/Incentivo',     'icon' => 'fa-gift',        'prefix' => ''],
            'recognition'       => ['label' => 'Reconocimiento',      'icon' => 'fa-trophy',      'prefix' => ''],
            'vacation_day'      => ['label' => 'Día libre',           'icon' => 'fa-calendar-o',  'prefix' => ''],
        ];
    }

    /**
     * ¿Aplica este reward para X conversiones?
     */
    public function appliesTo(int $conversions): bool
    {
        if (!$this->is_active) return false;
        if ($conversions < $this->min_conversions) return false;
        if ($this->max_conversions !== null && $conversions > $this->max_conversions) return false;
        return true;
    }

    /**
     * Obtener la descripción del valor formateado
     */
    public function getFormattedValueAttribute(): string
    {
        $type = self::types()[$this->reward_type] ?? null;
        if (!$type) return '';
        $prefix = $this->reward_type === 'bonus' ? ($this->reward_currency === 'USD' ? '$' : '₡') : $type['prefix'];
        if ($this->reward_type === 'gift' || $this->reward_type === 'recognition' || $this->reward_type === 'vacation_day') {
            return $this->description ?? $this->name;
        }
        return $prefix . number_format($this->reward_value, $this->reward_type === 'commission_percent' ? 1 : 0);
    }
}
