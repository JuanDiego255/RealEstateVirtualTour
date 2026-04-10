<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentRewardGrant extends Model
{
    protected $table = 'agent_reward_grants';

    protected $fillable = [
        'user_id', 'reward_id', 'month', 'conversions_count', 'leads_count',
        'notes', 'granted_by', 'granted_at',
    ];

    protected $casts = [
        'month'      => 'date',
        'granted_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function reward()
    {
        return $this->belongsTo(AgentReward::class, 'reward_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(\App\User::class, 'granted_by');
    }
}
