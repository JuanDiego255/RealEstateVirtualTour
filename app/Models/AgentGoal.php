<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AgentGoal extends Model
{
    protected $table = 'agent_goals';

    protected $fillable = [
        'user_id', 'company_id', 'month',
        'leads_goal', 'quotes_goal', 'conversions_goal', 'set_by',
    ];

    protected $casts = ['month' => 'date'];

    public function agent()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Company::class);
    }

    public function setBy()
    {
        return $this->belongsTo(\App\User::class, 'set_by');
    }

    /**
     * Obtener métricas reales del agente para el mes de esta meta.
     */
    public function metrics(): array
    {
        $from = Carbon::parse($this->month)->startOfMonth();
        $to   = Carbon::parse($this->month)->endOfMonth();

        $leads = EventLead::where('captured_by_user_id', $this->user_id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $quotes = \App\Models\VehicleQuote::where('captured_by_user_id', $this->user_id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $conversions = EventLead::where('captured_by_user_id', $this->user_id)
            ->where('sale_status', 'converted')
            ->whereBetween('converted_at', [$from, $to])
            ->count();

        return [
            'leads'       => $leads,
            'quotes'      => $quotes,
            'conversions' => $conversions,
            'leads_pct'       => $this->leads_goal > 0 ? round(($leads / $this->leads_goal) * 100) : 0,
            'quotes_pct'      => $this->quotes_goal > 0 ? round(($quotes / $this->quotes_goal) * 100) : 0,
            'conversions_pct' => $this->conversions_goal > 0 ? round(($conversions / $this->conversions_goal) * 100) : 0,
        ];
    }
}
